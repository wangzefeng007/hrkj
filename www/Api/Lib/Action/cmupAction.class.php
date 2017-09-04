<?php
/*
*	民生银联支付接口
*
*/
class cmupAction extends baseAction
{
	protected $config;
	
	function _initialize()
	{
		parent::_initialize();
		$this->config = C('cmuppay');
	}

	//民生银联sdk-app客户端访问验证签名
	public function sdk_app()
	{
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$pay = $this->pay($params,"cmup::sdk");
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
		$pay = $this->pay($params,"cmup::sdk");
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
		$pay = $this->pay($params,"cmup::sms");
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
		S("cmuppay_{$sn}",'1',60*3);	//链接3分钟有效
		$url = "http://{$_SERVER['HTTP_HOST']}/api.php/cmup/airwap/order_sn/".$sn;
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
		if (!S("cmuppay_{$sn}"))
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

	//验证签名,并向cmup客户端提交支付请求
	private function purchase($order)
	{
		//调试模式
		if (CMUPDEBUG === true)
		{
			$this->notice($order['sn']);
			$this->response(PARAMS_ERROR,"调试模式,订单支付成功!");
		}
		
		$orderNo = substr($order['sn'],6,6);
		$batchNo = substr($order['sn'],-6);
		$data = array(
			"merNo"		 => $this->config['merNo'],	//商户号
			"terNo"		 => $this->config['terNo'],	//终端号
			"orderNo"		 => $orderNo,	//商户订单号
			"batchNo"		 => $batchNo,	//批次号
			"backUrl"		 => $this->config['BACKENDURL']."sn/{$order['sn']}/",	//异步通知URL
			"transAmt"		 => format_num($order['money']*100,12),		//支付金额，单位为分
		);
		
		//数据签名
		ksort($data);
		$arr = array();
		foreach($data as $k => $v)
		{
			$arr[] = "{$k}={$v}";
		}
		$str = implode('&',$arr);
		$str .= "&{$this->config['security_key']}";
		$data['signature'] = hash('sha256', $str);
		
		$url_get = $this->config['APP_PAY_URL'];		//支付地址
		
		// dump($str);
		// dump($data);
		// dump($url_get);
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
			$out = "POST {$url['path']}?{$url['query']} HTTP/1.1\r\n";
			$out .= "Host: {$url['host']}\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);
			while (!feof($fp)) {
				$res .= fgets($fp, 128);
			}
			fclose($fp);
		}
		// dump($res);
		// exit;
		$n = strpos($res,'{"merNo"');'}';
		$res = substr($res,$n);
		$res = substr($res,0,-7);
		$res = json_decode ($res,true);
		
		eblog('',"=================数据提交日志 {$order['sn']}======================",'cmuppay_'.date("Ymd"));
		eblog("民生银联 - 提交参数 - data",$data,'cmuppay_'.date("Ymd"));
		eblog("民生银联 - 网关应答 - res",$res,'cmuppay_'.date("Ymd"));
		// 商户的业务逻辑
		if ($res["respCode"]== '0000')
		{
			return $res['tx'];
		}
		else 
		{
			$msg = $res['respDesc'];
			eblog("民生银联 - 错误信息",$msg,'cmuppay_'.date("Ymd"));
			$this->response(INTERNAL_ERROR,"民生银联反馈信息:{$msg}");
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
		eblog('','=======================================','cmuppay_'.date("Ymd"));
		eblog("民生银联支付异步接收参数集 - post",$this->_post(),'cmuppay_'.date("Ymd"));
		eblog("民生银联支付异步接收参数集 - get",$this->_get(),'cmuppay_'.date("Ymd"));
		if (I('sn'))
		{
			echo 'succeed';
		}
		$sn = I('sn',$sn);
		if ($sn == '' && $sn)
		{
			$notice_debug = 1;	//人工模式
			eblog("民生银联异步 - 人工post完成订单状态",'','cmuppay_'.date("Ymd"));
		}
		else
		{
			$_params = $this->_post();
			
			//验证签名
			$sign = $_params['signature'];
			unset($_params['signature']);
			$signkey = $this->config['security_key'];

			// 验签数据组装
			ksort($_params);
			$arr = array();
			foreach($_params as $k => $v)
			{
				if ($v) $arr[] = "{$k}={$v}";
			}
			$str = implode('&',$arr);
			$str .= "&{$this->config['security_key']}";
			$result = hash('sha256', $str);
			
			$resp = $_params;

			eblog("民生银联异步 - 验签参数 str",$str,'cmuppay_'.date("Ymd"));
			eblog("民生银联异步 - 验签结果 result",$result,'cmuppay_'.date("Ymd"));
		}


		if ($result == $sign || CMUPDEBUG === true || $notice_debug == 1)	//验签成功
		{
			if ($resp['respCode'] == '0000' || CMUPDEBUG === true || $notice_debug == 1)	//支付成功
			{
				$order = $this->loadModel('order')->getDetail($sn);
				if(!$order)
				{
					eblog("民生银联异步 - {$sn}",'订单不存在','cmuppay_'.date("Ymd"));
					return false;
				}
				$is_pay = $this->loadModel('order')->checkPay($order);
				if($is_pay)
				{
					eblog("民生银联异步 - {$sn}",'订单已支付','cmuppay_'.date("Ymd"));
					return false;
				}
				
				$payinfo = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
				$memo = array(
					'merNo' => $resp['merNo'],
					'origQryId' => $resp['origQryId'],
				);
				$payinfo['memo'] = serialize($memo);
				$rs =$this->loadModel('order')->pay($order,$payinfo);
				if ($rs)
				{
					eblog("民生银联异步 - {$sn}",'订单支付成功','cmuppay_'.date("Ymd"));
					//发送异步交易查询,并发送相关消息
					A('Api://processMsg')->msg('TradeMsg',$sn);
				}
				
				if (CMUPDEBUG === true) return $rs;
			}
		}
		else
		{
			eblog("民生银联异步 - {$sn}",'签名验证失败','cmuppay_'.date("Ymd"));
		}
	}
	
	/**
	 * 提现代付接口
	 */
	public function send_money()
	{
		$id = I('id');
		$type = I('type',0);	//0-查询,1-打款,2-二次清算
		$this->vaild_params('is_empty',$id,'错误!请选择需要处理的项目!');
		// if ($type == 2) $this->vaild_params('is_empty','','二次清算功能尚未开通,请稍后再试!');

		if ($type) {
			$url = $this->config['T0_PAY_URL'];
			
			if ($type == 2)
			{
				$type_name = '二次清算';
				$where = array(
					'id' => array('in',$id),
					'status' => CASH_STATUS_SENDFAIL,
				);
			}
			else
			{
				$type_name = '打款';
				$where = array(
					'id' => array('in',$id),
					'status' => array(array('eq',CASH_STATUS_UNSEND),array('eq',CASH_STATUS_SUBMITFAIL), 'or'),
				);
			}
			
			$fields = array();
			$fields['account_user_saler_cash'] = '*';
			$fields['user_saler'] = 'name,card_no,mobile,bank_no,bank,bank_type,bank_address';
			$fields['pay_type'] = 'name as pt_name';
			$join[] = array('user_saler','usid','id');
			$join[] = array('pay_type','ptid','id');

			$rs = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc');
			if ($rs['list'])
			{
				$data = array(
					'status' => CASH_STATUS_PROCESS,
					'dispostime' => time(),
				);
				$operate = $this->loadModel('accountUserSalerCash')->update($data,$where);
				$this->vaild_params('is_empty',$operate,'处理状态写入失败!请稍后再试!');
			}
		}
		else
		{
			$url = $this->config['T0_QUERY_URL'];

			$type_name = '查询';
			$where = array(
				'id' => array('in',$id),
				'status' => array(array('eq',CASH_STATUS_INPAY),array('eq',CASH_STATUS_SENDFAIL), 'or'),
			);
			$rs = $this->loadModel('accountUserSalerCash')->getList('*',$where,'id desc');
		}
		$this->vaild_params('is_empty',$rs['list'],"错误!没有符合条件的{$type_name}项目!!");
		
		eblog("==============================民生联机代付日志=============================",'','cmbc_money_'.date("Ymd"));
		eblog("民生联机代付 - {$type_name} - 操作id",$id,'cmbc_money_'.date("Ymd"));
		$arr = reset_array_key($rs['list'],'id','id');
		$valid_id = implode(',',$arr);
		eblog("民生联机代付 - {$type_name} - 有效id",$valid_id,'cmbc_money_'.date("Ymd"));
		// eblog("民生联机代付 - {$type_name} - 操作记录集",$rs['list'],'cmbc_money_'.date("Ymd"));
		eblog("民生联机代付 - {$type_name} - 提交url",$url,'cmbc_money_'.date("Ymd"));
		foreach ($rs['list'] as $val)
		{
			if ($type == 2)
			{
				$sn = $this->createSn('CR');
			}
			else
			{
				$sn = $val['re_sn']?$val['re_sn']:$val['sn'];
			}
			if ($type)
			{
				$data = array(
					'merNo' => $this->config['merNo'],
					'terNo' => $this->config['terNo'],
					'orderNo' => $sn,
					'transAmt' => $val['real_money'] * 100,
					'accNo' => $val['bank_no'],
					'accName' => $val['name'],
					'bankName' => $val['bank'],
					'bankType' => $val['bank_type'],
					'remark' => '',
					'resv' => '',
				);
			}
			else
			{
				$data = array(
					'merNo' => $this->config['merNo'],
					'terNo' => $this->config['terNo'],
					'orderNo' => $sn,
				);
			}
		eblog("--------------------{$type_name}--------------------",'','cmbc_money_'.date("Ymd"));
			eblog("民生联机代付 - {$type_name} - {$sn} 提交参数",$data,'cmbc_money_'.date("Ymd"));
			
			$i = 0;	//重试次数
			do
			{
				$result = $this->post_data($url,$data);
			}
			while (!$result && $i++ < 3);
			
			if ($result)
			{
				$_data = array(
					'msg' => '',
				);
				if ($type == 2) $_data['re_sn'] = $sn;
					
				if ($result['respCode']=='0071')
				{
					$_data['status'] =  CASH_STATUS_INPAY;
				}
				elseif ($result['respCode']=='0000')
				{
					$_data['status'] =  CASH_STATUS_SEND;
				}
				elseif ($result['respCode']!='')
				{
					$_data['status'] =  $type?CASH_STATUS_SUBMITFAIL:CASH_STATUS_SENDFAIL;
					$_data['msg'] =  "{$result['respCode']}-{$result['respDesc']}";
				}
				$_data['dispostime'] = time();
				eblog("民生联机代付 - {$type_name} - {$sn} 银行反馈结果",$result,'cmbc_money_'.date("Ymd"));
				eblog("民生联机代付 - {$type_name} - {$sn} 后台写入数据",$_data,'cmbc_money_'.date("Ymd"));
				$rs =  $this->loadModel('accountUserSalerCash')->update($_data,array('id'=>$val['id']));;
			}
			else
			{
				// echo '代付服务器连接失败!';
				$_data = array(
					'msg' => "通信失败! 请稍后重试{$type_name}!",
				);
				$_data['status'] =  $type?CASH_STATUS_SUBMITFAIL:CASH_STATUS_SENDFAIL;
				$_data['dispostime'] = time();
				eblog("民生联机代付 - {$type_name} - {$sn} 通信失败",$_data['msg'],'cmbc_money_'.date("Ymd"));
				eblog("民生联机代付 - {$type_name} - {$sn} 后台写入数据",$_data,'cmbc_money_'.date("Ymd"));
				$rs =  $this->loadModel('accountUserSalerCash')->update($_data,array('id'=>$val['id']));;
			}
		}
		$this->response(REQUEST_SUCCESS,'操作成功,相关交易状态已变更!请核对!');
		// $this->apiOut(true);
	}
	//代付接口提交post
	public function post_data($url,$data)
	{
		//数据签名
		ksort($data);
		$arr = array();
		foreach($data as $k => $v)
		{
			if ($v) $arr[] = "{$k}={$v}";
		}
		$str = implode('&',$arr);
		$str .= "&{$this->config['security_key']}";
		$data['signature'] = hash('sha256', $str);
		
		$post_data = http_build_query($data);  
		$url = parse_url($url);
		$url['port'] = $url['port']?$url['port']:'80';
		$fp = fsockopen("{$url['host']}", $url['port'], $errno, $errstr, 30);
		if (!$fp) {
			// echo "$errstr ($errno)<br />\n";	//无法访问主机报错
			return false;
		} else {
			$out = "POST {$url['path']} HTTP/1.1\r\n";
			$out .= "Host: {$url['host']}\r\n";
			$out .= "Content-type:application/x-www-form-urlencoded\r\n";
			$out .= "Content-length:".strlen($post_data)."\r\n";  
			$out .= "Connection: Close\r\n\r\n";
			$out .= "{$post_data}";
			fwrite($fp, $out);
			while (!feof($fp)) {
				$res .= fgets($fp, 128);
			}
			fclose($fp);
		}

		$n = strpos($res,'{"');'}';
		$res = substr($res,$n);
		$res = substr($res,0,-7);
		
		if ($res)
		{
			$data = json_decode($res, true);
			$signature = $data['signature'];
			unset($data['signature']);
			// 数据签名
			ksort($data);
			$arr = array();
			foreach($data as $k => $v)
			{
				if($v) $arr[] = "{$k}={$v}";
			}
			$str = implode('&',$arr);
			$str .= "&{$this->config['security_key']}";
			$result = hash('sha256', $str);
			
			if ($result == $signature || 1)
			{
				return $data;
			}
			else
			{
				// echo '验签失败!';
				return false;
			}
		}
		else
		{
			// echo '代付服务器应答失败!';
			return false;
		}
	}
}
