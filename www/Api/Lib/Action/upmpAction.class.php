<?php
/**
 *@desc	银联APP支付接口
 **/
class upmpAction extends baseAction
{
	protected $config;
	
	function _initialize()
	{
		$this->config = C('upmppay');
	}

	//银联sdk-app客户端访问验证签名
	public function sdk_app()
	{
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$pay = $this->pay($params,"upmp::sdk");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$data['tn'] = $this->purchase($pay['order']);
		$this->apiOut($data);
		// $this->response(REQUEST_SUCCESS,$data);
	}
	
	//银联sdk-Web客户端访问验证签名
	public function sdk_web($order_sn)
	{
		$params['order_sn'] = $order_sn;
		$pay = $this->pay($params,"upmp::sdk");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$rs = $this->purchase($pay['order']);
		return $rs;
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
		$data = $params;
		$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));
		$data['ptid'] = $pay_type['id'];
		$data['pt_name'] = $pay_type['name'];
		$data['addtime'] = time();
		$paylog = $this->loadModel('orderPaylog')->getInfo('id',array('order_sn'=>$order['sn']));
		$rs = !$paylog?$this->model['orderPaylog']->add($data):$this->model['orderPaylog']->update($data,array('id'=>$paylog['id']));
		if (!$rs) return false;
		return array('order'=>$order,'payinfo'=>$data);
	}

	//验证签名,并向upmp客户端提交支付请求
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
		
		$url_get = $this->config['PAY_URL'];		//支付地址
		
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
			echo "$errstr ($errno)<br />\n";
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
				// $data = array();
				// $data['upmp_sn'] = $resp['tn'];
				// $rs = M('upmp')->where(array('ordernum'=>$order['sn']))->save($data);
				return $resp['tn'];
				// $this->response(REQUEST_SUCCESS,$resp['tn']);
			}
			else
			{
				$msg = $resp['respMsg'];
				$this->response(INTERNAL_ERROR,"银联反馈信息:{$msg}");
			}
		}else 
		{
			// 服务器应答签名验证失败
			$this->response(INTERNAL_ERROR,"提交网关应答失败！");
		}
	}

	/**
	 * 异步通知地址
	 */
	public function notice($sn='')
	{
//		$resp = array();
/*	
$resp['orderTime'] = '20140926153848';
$resp['settleDate'] = '0926';
$resp['orderNumber'] = 'No14092692142037';
$resp['exchangeRate'] = '0';
$resp['signature'] = 'eba69bc5b2c23333bd6baf9ac2ef99cb';
$resp['settleCurrency'] = '156';
$resp['signMethod'] = 'MD5';
$resp['transType'] = '01';
$resp['respCode'] = '00';
$resp['charset'] = 'UTF-8';
$resp['sysReserved'] = '{traceTime=0926153848&acqCode=03035510&traceNumber=022830}';
$resp['version'] = '1.0.0';
$resp['settleAmount'] = '1';
$resp['transStatus'] = '00';
$resp['reqReserved'] = '透传信息';
$resp['merId'] = '303430148160024';
$resp['qn'] = '201409261538480228307';
*/	
		
		$resp = $_POST;
		$sn = $sn?$sn:$resp['orderNumber'];
		$sn = str_replace($this->config['SN_PREFIX'],'',$sn);

		eblog('','=======================================','upmppay_'.date("Ymd"));
		eblog("银联APP支付异步接收参数集",$this->_request(),'upmppay_'.date("Ymd"));
		if ($resp['transStatus'] == '00' || UPMPDEBUG === true)
		{
			//验证签名
			if ($result == $hmac || UPMPDEBUG === true)
			{
				$order = $this->loadModel('order')->getDetail($sn);
				if(!$order)
				{
					eblog("银联APP异步 - {$sn}",'订单不存在','upmppay_'.date("Ymd"));
					return false;
				}
				$is_pay = $this->loadModel('order')->checkPay($order);
				if($is_pay)
				{
					eblog("银联APP异步 - {$sn}",'订单已支付','upmppay_'.date("Ymd"));
					return false;
				}
				
				$payinfo = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
				$rs =$this->loadModel('order')->pay($order,$payinfo);
				if ($rs)
				{
					eblog("银联APP异步 - {$sn}",'订单支付成功','upmppay_'.date("Ymd"));
					//发送异步交易查询,并发送相关消息
					A('Api://processMsg')->msg('TradeMsg',$sn);
				}
				
				if (UPMPDEBUG === true) return $rs;
			}
			else
			{
				eblog("银联APP异步 - {$sn}",'签名验证失败','upmppay_'.date("Ymd"));
			}
		}
	}
}
