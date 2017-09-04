<?php
/*
*	民生银联支付接口
*
*/
class cmbcAction extends baseAction
{
	protected $config;
	
	function _initialize()
	{
		$this->response(INTERNAL_ERROR,'此通道暂停使用,请截图联系客服!');
		parent::_initialize();
		$this->config = C('cmbcpay');
	}

	//民生银联sdk-app客户端访问验证签名
	public function sdk_app()
	{
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$pay = $this->pay($params,"cmbc::sdk");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$data['tn'] = $this->purchase($pay['order']);
		$this->apiOut($data);
	}
	
	//民生银联sdk-Web客户端访问验证签名
	public function sdk_web($order_sn)
	{
		$params['order_sn'] = $order_sn;
		$pay = $this->pay($params,"cmbc::sdk");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$rs = $this->purchase($pay['order']);
		return $rs;
	}

	/*
	*	短信收款流程: [发送短信链接->进入网页支付页面]
	*	1、调用air方法发送订单支付链接，支付链接对应airwap方法
	*	2、airwap方法对应的网页中填写支付信息
	*	3、调用pay方法进行支付信息的入库
	*/
	public function sms_wap()
	{
		$_params = array('order_sn','mobile');
		$params = $this->get_params($_params);
		$this->vaild_params('is_empty',$params['mobile'],'手机号不能为空!');
		$pay = $this->pay($params,"cmbc::sms");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$order = $pay['order'];
		$rs = $this->airsms($params['mobile'],$order['money'],$order['sn']);
		$this->apiOut($rs);	
	}	

	//收款短信下发
	private function airsms($mobile,$money,$sn)
	{
		S("cmbcpay_{$sn}",'1',60*3);	//链接3分钟有效
		$url = "http://{$_SERVER['HTTP_HOST']}/api.php/cmbc/airwap/order_sn/".$sn;
		$url = getShortUrl($url);
		$data = array('money'=>$money,'url'=>$url);
		$sms = $this->parseSms('SMS_AIR',$data);
		return sendsms($mobile,$sms);
	}

	/*
	* 解析短信模版
	*	@param 		string		$sms_type		短信模版类型
	*  @param			array		$data				短信模版内容
	*/
	private function parseSms($sms_type,$data)
	{
		$content = $this->config[$sms_type];
		if (!$content) return false;
		foreach($data as $key=>$value)
		{
			$content = str_replace("##".$key."##",$value,$content);
		}
		return $content;
	}

	//短信收款网页
	public function airwap()
	{
		$title = '移动支付';
		$sn = I('order_sn');
		if (!S("cmbcpay_{$sn}"))
		{
			$params['title'] = $title;
			$params['success'] = 0;
			$params['msg'] = '支付链接已经失效!';
			$this->result($params);
		}
		$order = $this->loadModel('order')->getDetail($sn,"*");
		$user = $this->loadModel('userSaler')->getInfoByid($order['usid'],'name');
		$form = array(
			'action' => $this->config['WAP_PAY_URL'],
			'data' => array(
				'orderNumber' => $this->config['SN_PREFIX'] . $order['sn'],
				'backEndUrl' => $this->config['BACKENDURL'],
				'frontEndUrl' => $this->config['FRONTENDURL'],
				'subMerid' => $this->config['SUBMERID'],
				'orderAmount' => $order['money'] * 100,
			),
		);
		$this->assign('title',$title);
		$this->assign('form',$form);
		$this->assign('sn',$order['sn']);
		$this->assign('money',$order['money']);
		$this->assign('name',$user['name']);
		$this->display('pay/pay_info');
	}
	
	//支付消息反馈
	public function result($params)
	{
		$this->assign('title',$params['title']?$params['title']:'移动支付');
		$this->assign('result',$params['success']?'success':'fail');
		$this->assign('msg',$params['msg']?$params['msg']:'操作失败');
		$this->display('pay/msg_result');
		exit;
	}

	//创建支付信息
	private function pay($params,$apiname)
	{
		$this->vaild_params('is_empty',$params['order_sn'],'订单号不能为空');
		$order = $this->loadModel('order')->getDetail($params['order_sn']);
		$this->vaild_params(array($this->model['order'],'checkPay'),array($order),'该订单已经付款',false);

		if ($order['money']<0.01)
		{
			$this->response(DATA_EMPTY,'订单金额少于0.01元');
		}
		$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));
		
		//通道限额检测
		$err_msg = $this->loadModel('userSaler')->chkPayLimit($order,$pay_type['id'],$this->usinfo['lfid']);
		if ($err_msg)
		{
			$this->response(DATA_EMPTY,$err_msg);
		}
		
		$data = $params;
		$data['ptid'] = $pay_type['id'];
		$data['pt_name'] = $pay_type['name'];
		$data['addtime'] = time();
		$paylog = $this->loadModel('orderPaylog')->getInfo('id',array('order_sn'=>$order['sn']));
		$rs = !$paylog?$this->model['orderPaylog']->add($data):$this->model['orderPaylog']->update($data,array('id'=>$paylog['id']));
		if (!$rs) return false;
		return array('order'=>$order,'payinfo'=>$data);
	}

	//验证签名,并向cmbc客户端提交支付请求
	private function purchase($order)
	{
		//调试模式
		if (CMBCDEBUG === true)
		{
			$this->notice($order['sn']);
			$this->response(PARAMS_ERROR,"调试模式,订单支付成功!");
		}

		$data = array(
			"backEndUrl"		 => $this->config['BACKENDURL'],	//异步通知URL
			"frontEndUrl"		 => $this->config['FRONTENDURL'],//前端返回URL
			"subMerid"			 => $this->config['SUBMERID'],			//子账户标识
			"orderNumber"		 => $this->config['SN_PREFIX'].$order['sn'],	//商户订单号
			"orderAmount"		 => $order['money']*100,		//支付金额，单位为分
		);
		
		$url_get = $this->config['APP_PAY_URL'];		//支付地址
		
		// $ch = curl_init();
		// curl_setopt($ch,CURLOPT_URL,$url_get);
		// curl_setopt($ch,CURLOPT_POST,1);
		// curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		// curl_setopt($ch,CURLOPT_ENCODING ,'gzip'); //加入gzip解析
		// curl_setopt($ch,CURLOPT_FOLLOWLOCATION ,1); //加入重定向处理
		// ob_start();
		// curl_exec($ch);
		// $httpCode = curl_getinfo($ch,CURLINFO_FILETIME);
		// $res = ob_get_contents();
		// ob_end_clean();
		// curl_close($ch);
		
		$url_get .= '?';
		foreach ($data as $k => $v)
		{
			$url_get.="{$k}={$v}&";
		}
		$url = parse_url($url_get);
		$url['port'] = $url['port']?$url['port']:'80';
		$fp = fsockopen("{$url['host']}", $url['port'], $errno, $errstr, 30);
		if (!$fp) {
			// echo "$errstr ($errno)<br />\n";
			$this->response(INTERNAL_ERROR,"订单提交失败,请稍后再试!");
		} else {
			$out = "GET {$url['path']}?{$url['query']} HTTP/1.1\r\n";
			$out .= "Host: {$url['host']}\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);
			while (!feof($fp)) {
				$res .= fgets($fp, 128);
			}
			fclose($fp);
		}
		
		$n = strpos($res,'{"result"');'}';
		$res = substr($res,$n);
		$res = substr($res,0,-7);
		$res = json_decode ($res,true);
		
		// 商户的业务逻辑
		if ($res["result"]['code'] == '10000')
		{
			$resp = $res['data'];
			// 提交网关应答成功
			if ($resp['respCode'] == '00')
			{
				return $resp['tn'];
			}
			else
			{
				$msg = $resp['respMsg'];
				$this->response(INTERNAL_ERROR,"民生银联反馈信息:{$msg}");
			}
		}else 
		{
			// 服务器应答签名验证失败
			$this->response(INTERNAL_ERROR,"提交网关应答失败！");
		}
	}

	/**
	 * 前端通知地址
	 */
	public function front_notice()
	{
		$title = '移动支付';
		$params['title'] = $title;
		$params['success'] = 1;
		$params['msg'] = '操作成功!';
		$this->result($params);
	}
	/**
	 * 异步通知地址
	 */
	public function notice($sn='')
	{
		eblog('','=======================================','cmbcpay_'.date("Ymd"));
		eblog("民生银联支付异步接收参数集",$this->_post(),'cmbcpay_'.date("Ymd"));

		$_params = $this->get_params(array('signature','respContent'),'_post');
		
		//验证签名
		$sign = $_params['signature'];
		$signkey = $this->config['security_key'];

		// 验签数据组装
		$str = $_params['respContent'] . $signkey;
		$result = md5($str);

		eblog("民生银联异步 - 验签参数 str",$str,'cmbcpay_'.date("Ymd"));
		eblog("民生银联异步 - 验签结果 result",$result,'cmbcpay_'.date("Ymd"));

		if ($result == $sign || CMBCDEBUG === true)	//验签成功
		{
			$data_str = base64_decode($_params['respContent']);
			$resp = json_decode($data_str,1);
			eblog("民生银联异步 - 通知参数详情 resp",$resp,'cmbcpay_'.date("Ymd"));
			
			$sn = CMBCDEBUG?I('sn',$sn):$resp['orderId'];
			$sn = str_replace($this->config['SN_PREFIX'],'',$sn);

			if ($resp['resultCode'] == '000000000' || CMBCDEBUG === true)	//支付成功
			{
				$order = $this->loadModel('order')->getDetail($sn);
				if(!$order)
				{
					eblog("民生银联异步 - {$sn}",'订单不存在','cmbcpay_'.date("Ymd"));
					return false;
				}
				$is_pay = $this->loadModel('order')->checkPay($order);
				if($is_pay)
				{
					eblog("民生银联异步 - {$sn}",'订单已支付','cmbcpay_'.date("Ymd"));
					return false;
				}
				
				$payinfo = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
				$rs =$this->loadModel('order')->pay($order,$payinfo);
				if ($rs)
				{
					eblog("民生银联异步 - {$sn}",'订单支付成功','cmbcpay_'.date("Ymd"));
					//发送异步交易查询,并发送相关消息
					A('Api://processMsg')->msg('TradeMsg',$sn);
				}
				
				if (CMBCDEBUG === true) return $rs;
			}
		}
		else
		{
			eblog("民生银联异步 - {$sn}",'签名验证失败','cmbcpay_'.date("Ymd"));
		}
	}
}
