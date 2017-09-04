<?php
class ezfpayAction extends baseAction
{
	protected $config;
	
	function _initialize()
	{
		$this->config = C('ezfpay');
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '错误!此支付通道已经暂停使用!';
		exit;
	}
	
	/**
	 * wap支付跳转
	 */
	public function wapPay()
	{
		$params['order_sn'] = I('sn');
		$sn = $params['order_sn'];
		$type = I('type',0);	//本函数输出类型 0-以json格式输出,1-以html格式输出
		$pay = $this->pay($params,'ezfpay::notice');
		if (!$pay)
		{
			$this->apiOut(false);
		}
		else
		{
			$params = array(
				"payUrl"			 => $this->config['EZFMER_PAY_URL_WAP'],	//支付地址
				"version"			 => $this->config['VERSION_WAP'],			//版本号
				"charset"			 => $this->config['CHARSET'],			//字符编码
				"signMethod"		 => $this->config['SIGNMETHOD'],		//签名方法
				"merId"				 => $this->config['MERID'],				//商户编号
				"backEndUrl"		 => $this->config['EZFMER_BACKENDURL'],	//异步通知URL
				"frontEndUrl"		 => $this->config['EZFMER_FRONTENDURL'],//前端返回URL
				"signkey"			 => $this->config['SIGNKEY'],
				// "subMerid"			 => $this->config['SUBMERID'],			//子账户标识
				"payType"			 => "B2C",								//支付类型
				"transType"			 => "01",								//交易类型
				"orderCurrency"		 => "156",								//交易币种	
				"defaultBankNumber"	 => "999",								//银行编码
				"customerIp"		 => "",									//持卡人IP(可选)
				"merReserved1"		 => "",									//商户保留域1(可选)
				"merReserved2"		 => "",									//商户保留域2(可选)
				"merReserved3"		 => "",									//商户保留域3(可选)
				"orderTime"			 => date("YmdHis"),						//交易时间
				"orderNumber"		 => $sn,								//商户订单号
				"orderAmount"		 => $pay['order']['money'] * 100,		//支付金额，单位为分
				"agentAmount"		 => $this->config['SUBMERID'].'='.$pay['order']['money'] * 100,		//子账号金额
				"terType"			 => '00',		//终端类型取值：00—PC机网页 01—自助设备 02—手持终端 03—后台服务端 默认值为00 
			);

			Vendor('EZF.ezfpay');
			$send = new send($params);
			$sign = $send->getSignWapSub();
			$params['sign']=$sign ;

			$html = '<div style="display:none;"><form action="'.$params['payUrl'].'" method="post" name="payform" id="payform">';
			if ($params)
			{
				foreach ($params as $key => $val)
				{
					$html .= '<input name="'.$key.'" type="text"  value="'.$val.'">';
				}
			}
			// $html .= '<input name="submit" type="submit" value=" 提   交 ">';
			$html .= '</form></div>';
			$html .= '<script>document.getElementById("payform").submit();</script>';
			$html = str_replace('"',"'",$html);
			if ($type == 0)
			{
				echo str_replace("\\/", '/', json_encode(array('status'=>10000,'info'=>'提交成功', 'message'=>$html)));
			}
			else
			{
				$obj = strtolower(APP_NAME);
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
				echo '<link href="/static/'.$obj.'/css/jquery.loadmask.css" rel="stylesheet" type="text/css" />';
				echo '<script type="text/javascript" src="/static/'.$obj.'/js/jquery-1.7.1.min.js"></script>';
				echo '<script type="text/javascript" src="/static/'.$obj.'/js/jquery.loadmask.js"></script>';
				echo '<script language="javascript">window.onload=function(){$("body").mask("正在连接银联支付网关，请稍后...");$(".loadmask-msg div").css("background-image","url(/static/'.$obj.'/images/loading.gif)")}</script>';
				echo $html;
			}
		}
	}
	/*
	*	建立订单  localhost.wsh.com/api.php?m=ezfpay&a=order
	*/
	function order()
	{
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$sn = $params['order_sn'];
		$pay = $this->pay($params,'ezfpay::notice');
		if (!$pay)
		{
			$this->apiOut(false);
		}
		else
		{
			$params = array(
				"payUrl"			 => $this->config['EZFMER_PAY_URL'],	//支付地址
				"version"			 => $this->config['VERSION'],			//版本号
				"charset"			 => $this->config['CHARSET'],			//字符编码
				"signMethod"		 => $this->config['SIGNMETHOD'],		//签名方法
				"merId"				 => $this->config['MERID'],				//商户编号
				"backEndUrl"		 => $this->config['EZFMER_BACKENDURL'],	//异步通知URL
				"frontEndUrl"		 => $this->config['EZFMER_FRONTENDURL'],//前端返回URL
				"signkey"			 => $this->config['SIGNKEY'],
				"subMerid"			 => $this->config['SUBMERID'],			//子账户标识
				"transType"			 => "01",								//交易类型
				"orderCurrency"		 => "156",								//交易币种	
				"defaultBankNumber"	 => $bank,								//银行编码
				"merReserved1"		 => "",									//商户保留域1
				"orderTime"			 => date("YmdHis"),						//交易时间
				"orderNumber"		 => $sn,								//商户订单号
				"orderAmount"		 => $pay['order']['money'] * 100,		//支付金额，单位为分
			);

			Vendor('EZF.ezfpay');
			$send = new send($params);
			$sign=$send->getSign();
			$params['sign']=$sign ;
			$result = sendHttpRequest($params, $params['payUrl']);
			// dump($result);
			$res = verify($result, $params['signkey']);
			
			if ($res['respCode'] == '00')
			{
				$data['tn'] = $res['qid'];
				$this->apiOut($data);
			}
			else
			{
				$this->response(PARAMS_ERROR,'错误!'.$res["respMsg"]);
			}
		}
	}
	
	
	//创建支付信息
	private function pay($params,$apiname)
	{
		$this->vaild_params('is_empty',$params['order_sn'],'订单号不能为空');
		$order = $this->loadModel('order')->getDetail($params['order_sn']);
		$this->vaild_params('is_empty',$order,'该订单不存在');
		$this->vaild_params(array($this->model['order'],'checkPay'),array($order),'该订单已经付款',false);

		if ($order['money']<0.1)
		{
			$this->response(DATA_EMPTY,'订单金额少于0.1元');
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

	/**
	 * 前端返回页面地址
	 */
	public function result()
	{
		eblog('','=======================================','ezfpay_'.date("Ymd"));
		eblog("EZF前端接收参数集",$this->_request(),'ezfpay_'.date("Ymd"));
		$_params = $this->get_params(array('version','charset','signMethod','payType','transType','merId','orderNumber','qid','orderAmount','payAmount','state','orderCurrency','orderTime','merReserved1','merReserved2','merReserved3'));
		$sn = $_params['orderNumber'];
		
		$order = $this->loadModel('order')->initModel($sn)->getInfo("*",array('sn'=>$sn));
		if(!$order)
		{
			eblog("EZF前端 - {$sn}",'订单不存在','ezfpay_'.date("Ymd"));
			echo "{$sn} - 订单不存在";
			return false;
		}
		if($order['status'] == 1)
		{
			eblog("EZF前端 - {$sn}",'订单已支付','ezfpay_'.date("Ymd"));
			echo "{$sn} - 订单已支付";
			return false;
		}


		//验证签名
		$sign = I('sign');
		$signkey = $this->config['SIGNKEY'];
		Vendor('EZF.ezfpay');

		// 验签数据组装
		$data = $_params;
		foreach ($data as $key => &$val)
		{
			$val = "{$key}={$val}";
		}
		$str = implode('&',$data).'&'.md5($signkey);
		$result = md5($str);

		if ($result == $sign)
		{
			if ($_params['state'] == 1)
			{
				$rs = $this->_paysuccess($order,__METHOD__);
				if ($rs)
				{
					eblog("EZF前端 - {$sn}",'订单支付成功','ezfpay_'.date("Ymd"));
					echo "{$sn} - 订单支付成功";
				}
			}
		}
		else
		{
			echo "{$sn} - 签名验证失败";
			eblog("EZF前端 - {$sn}",'签名验证失败','ezfpay_'.date("Ymd"));
		}
	}
	
	/**
	 * 异步通知地址
	 */
	public function notice()
	{
		eblog('','=======================================','ezfpay_'.date("Ymd"));
		eblog("EZF异步接收参数集",$this->_request(),'ezfpay_'.date("Ymd"));
		$_params = $this->get_params(array('version','charset','signMethod','payType','transType','merId','orderNumber','qid','orderAmount','payAmount','state','orderCurrency','orderTime','merReserved1','merReserved2','merReserved3'));
		$sn = $_params['orderNumber'];
		
		$order = $this->loadModel('order')->initModel($sn)->getInfo("*",array('sn'=>$sn));
		if(!$order)
		{
			eblog("EZF异步 - {$sn}",'订单不存在','ezfpay_'.date("Ymd"));
			return false;
		}
		if($order['status'] == 1)
		{
			eblog("EZF异步 - {$sn}",'订单已支付','ezfpay_'.date("Ymd"));
			return false;
		}


		//验证签名
		$sign = I('sign');
		$signkey = $this->config['SIGNKEY'];
		Vendor('EZF.ezfpay');

		// 验签数据组装
		$data = $_params;
		foreach ($data as $key => &$val)
		{
			$val = "{$key}={$val}";
		}
		$str = implode('&',$data).'&'.md5($signkey);
		$result = md5($str);

		if ($result == $sign)
		{
			if ($_params['state'] == 1)
			{
				$rs = $this->_paysuccess($order,__METHOD__);
				if ($rs)
				{
					eblog("EZF异步 - {$sn}",'订单支付成功','ezfpay_'.date("Ymd"));
				}
			}
		}
		else
		{
			eblog("EZF异步 - {$sn}",'签名验证失败','ezfpay_'.date("Ymd"));
		}
		// dump($result);
		// dump($res);
	}

	//支付成功的手续处理
	private function _paysuccess($order,$apiname){
		if(is_array($order) && $order)
		{
			$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));
			$data['order_sn'] = $order['sn'];
			$data['ptid'] = $pay_type['id'];
			$data['pt_name'] = $pay_type['name'];
			$data['addtime'] = time();
			$paylog = $this->loadModel('orderPaylog')->getInfo('id',array('order_sn'=>$order['sn']));
			$rs = !$paylog?$this->model['orderPaylog']->add($data):$this->model['orderPaylog']->update($data,array('id'=>$paylog['id']));
			if (!$rs) 
			{
				eblog("EZF支付订单数据更新 - {$order['sn']}",'orderPaylog更新失败','ezfpay_'.date("Ymd"));
				return false;
			}
			$payinfo = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$order['sn']));
			$rs =$this->loadModel('order')->pay($order,$payinfo);
			if (!$rs) 
			{
				//echo 'order guanjiangbuzou error';
				eblog("EZF支付订单数据更新 - {$order['sn']}",'关键步骤错误','ezfpay_'.date("Ymd"));
				return false;
			}
			eblog("EZF支付订单数据更新 - {$order['sn']}",'数据更新成功','ezfpay_'.date("Ymd"));
			//echo 'mopay is full success';
			return true;
		}
	}

	/**
	 * 提供给子商户的Api
	 */
	public function wapApi()
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '错误!此接口已停止使用!';
		echo '新提交地址变更为: '.HOST.'/bestpay.php/ezfpay/wapApi/';
		exit;

		$_params = $this->get_params(array('orderNumber','backEndUrl','frontEndUrl','subMerid','orderAmount'));

		if(!trim($_params['orderNumber']))
		{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '错误!订单号 不能为空!';
			exit;
		}
		if(!trim($_params['subMerid']))
		{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '错误!账户标识id 不能为空!';
			exit;
		}
		if($_params['orderAmount']<200)
		{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '错误!订单金额 至少为2元!';
			exit;
		}
		
		$params = array(
			"orderNumber"		 => $_params['orderNumber'],			//商户订单号
			"frontEndUrl"		 => $_params['frontEndUrl'],			//前端返回URL
			"orderAmount"		 => $_params['orderAmount'],			//支付金额，单位为分
			"agentAmount"		 => '1040400002='.$_params['orderAmount'],		//子账号金额
			// "agentAmount"		 => $_params['subMerid'].'='.$_params['orderAmount'],		//子账号金额

			"backEndUrl"		 => HOST."/api.php/ezfpay/noticeApi/",	//异步通知URL(异步通知直接发给平台,再由平台转发给商户)

			"payUrl"			 => $this->config['EZFMER_PAY_URL_WAP'],//支付地址
			"version"			 => $this->config['VERSION_WAP'],		//版本号
			"charset"			 => $this->config['CHARSET'],			//字符编码
			"signMethod"		 => $this->config['SIGNMETHOD'],		//签名方法
			"merId"				 => $this->config['MERID'],				//商户编号
			"signkey"			 => $this->config['SIGNKEY'],
			"payType"			 => "B2C",								//支付类型
			"transType"			 => "01",								//交易类型
			"orderCurrency"		 => "156",								//交易币种	
			"defaultBankNumber"	 => "999",								//银行编码
			"customerIp"		 => "",									//持卡人IP(可选)
			"merReserved1"		 => "",									//商户保留域1(可选)
			"merReserved2"		 => "",									//商户保留域2(可选)
			"merReserved3"		 => "",									//商户保留域3(可选)
			"orderTime"			 => date("YmdHis"),						//交易时间
			"terType"			 => '00',		//终端类型取值：00—PC机网页 01—自助设备 02—手持终端 03—后台服务端 默认值为00 
		);

		Vendor('EZF.ezfpay');
		$send = new send($params);
		$sign = $send->getSignWapSub();
		$params['sign']=$sign;

		//把客户提交参数写入数据库
		$data = array(
			"order_sn"		 => $_params['orderNumber'],			//商户订单号
			"back_url"		 => $_params['backEndUrl'],				//异步通知URL
			"front_url"		 => $_params['frontEndUrl'],			//前端返回URL
			"money"			 => $_params['orderAmount']/100,		//支付金额
			"sub_merid"		 => $_params['subMerid'],				//子账号
			"addtime"		 => time(),								//提交时间
		);
		$rs = $this->loadModel('bsfAccountIncome')->add($data);

		//把客户参数提交给银联
		// $html = '<div style="display:;"><form action="'.$params['payUrl'].'" method="post" name="payform" id="payform">';
		$html = '<div style="display:none;"><form action="'.$params['payUrl'].'" method="post" name="payform" id="payform">';
		if ($params)
		{
			foreach ($params as $key => $val)
			{
				$html .= '<input name="'.$key.'" type="text"  value="'.$val.'">';
			}
		}
		// $html .= '<input name="submit" type="submit" value=" 提   交 ">';
		$html .= '</form></div>';
		$html .= '<script>document.getElementById("payform").submit();</script>';
		$html = str_replace('"',"'",$html);
		
		echo $html;
	}
	
	/**
	 * 异步通知Api,平台接收银联通知,再由平台转发给商户
	 */
	public function noticeApi()
	{
		eblog('','=======================================','bsf_notice_'.date("Ymd"));
		eblog("BSF异步接收参数集",$this->_request(),'bsf_notice_'.date("Ymd"));
		$_params = $this->get_params(array('version','charset','signMethod','payType','transType','merId','orderNumber','qid','orderAmount','payAmount','state','orderCurrency','orderTime','merReserved1','merReserved2','merReserved3'));
		$sn = $_params['orderNumber'];
		


		//验证签名
		$sign = I('sign');
		$signkey = $this->config['SIGNKEY'];
		Vendor('EZF.ezfpay');

		// 验签数据组装
		$data = $_params;
		foreach ($data as $key => &$val)
		{
			$val = "{$key}={$val}";
		}
		$str = implode('&',$data).'&'.md5($signkey);
		$result = md5($str);

		if ($result == $sign)	//验签成功
		{
			$order = $this->loadModel('bsfAccountIncome')->getInfo("*",array('order_sn'=>$sn),array('id'=>'desc'));

			if ($_params['state'] == 1)	//支付成功
			{
				if(!$order)
				{
					eblog("BSF异步 - {$sn}",'订单不存在','bsf_notice_'.date("Ymd"));
					return false;
				}
				elseif ($order['status'] != 1)
				{
					$data['status'] = $_params['state'];
					$data['tn'] = $_params['qid'];
					$data['paytime'] = time();
					$rs = $this->loadModel('bsfAccountIncome')->update($data,array('id'=>$order['id']));
					if ($rs)
					{
						eblog("BSF异步 - {$sn}",'订单支付成功','bsf_notice_'.date("Ymd"));
					}
				}
			}
			
			if ($order['back_url'])
			{
				//把银联消息转发给客户
				$data = $_params;
				$data['sign'] = $sign;
				eblog("BSF通知参数 - {$sn}",$data,'bsf_notice_'.date("Ymd"));
				
				// $html = '<div style="display:;"><form action="'.$order['back_url'].'" method="post" name="payform" id="payform">';
				$html = '<div style="display:none;"><form action="'.$order['back_url'].'" method="post" name="payform" id="payform">';
				if ($data)
				{
					foreach ($data as $key => $val)
					{
						$html .= '<input name="'.$key.'" type="text"  value="'.$val.'">';
					}
				}
				// $html .= '<input name="submit" type="submit" value=" 提   交 ">';
				$html .= '</form></div>';
				$html .= '<script>document.getElementById("payform").submit();</script>';
				$html = str_replace('"',"'",$html);
				
				echo $html;
				
			}
		}
		else
		{
			eblog("BSF异步 - {$sn}",'签名验证失败','bsf_notice_'.date("Ymd"));
		}
		// dump($result);
		// dump($res);
	}
}
