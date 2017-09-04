<?php 
class qrpayAction extends baseAction
{
	function index()
    {
    	//在微信浏览器中打开    	
    	$this->assign('usid',$this->usid);
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {    	
	    	$code = I('code');
	        //$sn = I('order_sn');
	        Vendor('Wxpay.WxPayPubHelper');        
	        $jsApi = new JsApi_pub();
	
	        if (!$code)
	        {
	            $url = $jsApi->createOauthUrlForCode(urlencode("http://wallet.huirongpay.com/api.php?m=qrpay&a=index&usid=".$this->usid));
	            //echo $url;die();
	            header("Location: ".$url); 
	            exit;
	        }
	        else
	        {
	            $jsApi->setCode($code);
	            $openid = $jsApi->getOpenId();
	        }	   
	        //echo $openid.'-----'.$_GET['usid'];     
	        $this->assign('title','微信扫描');
	        $this->assign('paytype','微信扫描');
	        $this->assign('openid',$openid);
	        $this->display('weixin');
	        
        }
        else 
        {
        	$this->assign('title','支付宝扫描');
        	$this->assign('paytype','支付宝扫描');
	        $this->assign('openid',$openid); 
	        $this->display();       
        }
    }
	function wap_pay()
    {
		$code = I('code');
        $sn = I('order_sn');
        Vendor('Wxpay.WxPayPubHelper');        
        $jsApi = new JsApi_pub();

        if (!$code)
        {
            $url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL."order_sn/".$sn);
            header("Location: ".$url); 
            exit;
        }else
        {
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }

		$order = $this->pay($sn,"wxpay::wap",0);
		if (!$order)
		{
            $this->err_msg("数据提交失败，请稍后再试！",0);
		}

        $unifiedOrder = new UnifiedOrder_pub();
        $unifiedOrder->setParameter("sub_openid",$openid);//
        $unifiedOrder->setParameter("body","商品");//商品描述
        $unifiedOrder->setParameter("out_trade_no",$sn);//商户订单号 
        $unifiedOrder->setParameter("total_fee",$order['money']*100);//总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		// $unifiedOrder->setParameter("sub_mch_id",WxPayConf_pub::SUB_MCHID);//
		// $unifiedOrder->setParameter("sub_appid",WxPayConf_pub::SUB_APPID);//


        $prepay_id = $unifiedOrder->getPrepayId();
        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getParameters();
       
        $this->js_call($jsApiParameters);
    }

 	//微信扫码支付接口
	public function scan_pay()
	{
		$wxdebug = WXDEBUG;
		$sn = I('order_sn');	
		$order = $this->pay($sn,"wxpay::scan"); 
		if (!$order)
		{
            $this->err_msg("数据提交失败，请稍后再试！");
		}
		
		$order = $this->loadModel('order')->getDetail($sn);
		if ($order)
		{
			$url = 'http://119.57.140.199:8880/SinglePay/scancode';
			$postData = array(
				'ORDER_ID' => $order['sn'],		
				'ORDER_AMT' => $order['money'] * 100,
				'PAY_CHANNEL' => 1,				
				'ORDER_NAME' => '微信支付'            
			);  
			eblog("微信支付 - POST数据",$postData,'wxpay_'.date("Ymd"));
			$ansData = $this->httpGet($url, $postData); 
			eblog("微信支付 - 返回数据",$ansData,'wxpay_'.date("Ymd"));
			$result=json_decode($ansData,true);
			//$result['returnMsg']=urldecode($result['returnMsg']); 
			if ($result['RES_CODE'] == '00')
			{ 
				$userInfo = $this->loadModel('userSaler')->getInfo('*',array('id'=>$this->usid));
				$data['share_title']='扫一扫,轻松付款';
				$data['share_desc']='商户:'.$userInfo['name'].'向您发起一笔金额为￥'.$order['money'].'的收款';
				$data['price']=$order['money'];
				$data['url'] = getQrcodePayUrl(array('amount'=>$order['money'],'url'=>$result['QRCODE_URL']),1);
				$this->apiOut($data);
			}
			else
			{
				$this->response(PARAMS_ERROR,"错误:".$result['QRCODE_URL']);
			} 
			$this->apiOut($data);
		}
		else
		{
			eblog("微信支付- {$sn}",'订单不存在','wxpay_'.date("Ymd"));
			$this->apiOut($data);
		}	
	}
	
 	//获取微信收款网页地址
	public function pay_url()
	{
		$sn = I('order_sn');
		S("wxpay_{$sn}",'1',60*3);	//链接3分钟有效
		$this->vaild_params('is_empty',$sn,'订单号不能为空!');
		$data['url'] = 'http://'.$_SERVER['HTTP_HOST'] . U('wxpay/wx_wap',array('order_sn'=>$sn));
		// $data['url'] = HOST . U('wxpay/wx_wap',array('order_sn'=>$sn));
		$this->apiOut($data);	
	}
	
 	//微信收款网页
	public function wx_wap()
	{
		$sn = I('order_sn');
		if (!S("wxpay_{$sn}"))
		{
			$params['msg'] = '支付已经失效!';
			$this->result($params);
		}
		$order = $this->loadModel('order')->getDetail($sn,"*");
		$user = $this->loadModel('userSaler')->getInfoByid($order['usid'],'name');
		$form = array(
			'action' => U('wxpay/wap_pay'),
			'data' => array(
				'order_sn' => $sn,
			),
		);
		$this->assign('title','微信支付');
		$this->assign('form',$form);
		$this->assign('sn',$order['sn']);
		$this->assign('money',$order['money']);
		$this->assign('name',$user['name']);
		$this->display('pay/pay_info');
	}
	
	//支付消息反馈
	public function result($params)
	{
		$this->assign('title',$params['title']?$params['title']:'微信支付');
		$this->assign('result',$params['success']?'success':'fail');
		$this->assign('msg',$params['msg']?$params['msg']:'操作失败');
		$this->display('pay/msg_result');
		exit;
	}

	//错误信息反馈
	public function err_msg($msg,$is_api=1)
	{
		if ($is_api)
		{
			$this->response(PARAMS_ERROR,$msg);
		}
		else
		{
			$this->result(array('msg'=>$msg));
		}
	}

	//创建支付信息
	private function pay($sn,$apiname,$is_api=1)
	{
		if (!$sn) $this->err_msg("订单号不能为空",$is_api);
        $order = $this->loadModel('order')->getDetail($sn);
 		if (!$order) $this->err_msg("错误!订单不存在!",$is_api);
        if($this->loadModel('order')->checkPay($order))
        {
            $this->err_msg("该订单已支付，请勿重复支付！",$is_api);
        }
		if ($order['money']<0.01) $this->err_msg("订单金额少于0.01元!",$is_api);
 		$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));

		//通道限额检测
		$err_msg = $this->loadModel('userSaler')->chkPayLimit($order,$pay_type['id'],$this->usinfo['lfid']);
		if ($err_msg)
		{
			$this->response(DATA_EMPTY,$err_msg);
		}
		
		$data = array();
		$data['ptid'] = $pay_type['id'];
		$data['pt_name'] = $pay_type['name'];
       // $data['ptid'] = 6;
        // $data['pt_name'] = '微信支付';
        $data['order_sn'] = $order['sn'];
        $data['usid'] = $order['usid'];
        $data['money'] = $order['money'];
        $data['addtime'] = time();
        $paylog = $this->loadModel('orderPaylog')->getInfo('id',array('order_sn'=>$order['sn']));
		$rs = !$paylog?$this->model['orderPaylog']->add($data):$this->model['orderPaylog']->update($data,array('id'=>$paylog['id']));
		if (!$rs) return false;
		return $order;
	}

	/*
    *   付款成功通知
    */
    public function payNotice($sn='')
    {
		Vendor('Wxpay.WxPayPubHelper');  
		$notify = new Notify_pub();        
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
		$notify->saveData($xml);

		$wxdebug = WXDEBUG;
		$sn = I('sn',$sn);
		if ($sn == '' && $sn)
		{
			$wxdebug = true;	//人工模式
			$_params = $this->_request();
			eblog("微信异步 - {$sn} - 人工post数据",$_params,'wxpay_'.date("Ymd"));
		}
		else
		{
				
			eblog('','=======================================','wxpay_'.date("Ymd"));
			eblog("微信异步接收参数集",$notify->data,'wxpay_'.date("Ymd"));
			// eblog("微信异步接收参数集-xml",$xml,'wxpay_'.date("Ymd"));
			$sn = $notify->data['out_trade_no'];
		}
		
        if($notify->checkSign() == FALSE && $wxdebug != true)
		{
 			eblog("微信异步 - {$sn}",'签名验证失败','wxpay_'.date("Ymd"));
            // eblog('wxpay:fail',$xml);
        }
        else
        {
            if ($notify->data['result_code'] == 'SUCCESS' || $wxdebug === true)
			{
				$order = $this->loadModel('order')->getDetail($sn);
				if ($order)
				{
					if ($this->loadModel('order')->checkPay($order))
					{
						eblog("微信异步 - {$sn}",'订单已支付','wxpay_'.date("Ymd"));
						// eblog('wxpay:already pay',$order['sn']); 
					}
					else 
					{
						$paylog = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
						$rs = $this->loadModel('order')->pay($order,$paylog);
						eblog("微信异步 - {$sn}",'订单支付成功','wxpay_'.date("Ymd"));
						A('Api://processMsg')->msg('TradeMsg',$sn);
					}
				}
				else
				{
					eblog("微信异步 - {$sn}",'订单不存在','wxpay_'.date("Ymd"));
				}
			}
			else
			{
				eblog("微信异步 - {$sn}",'订单支付失败','wxpay_'.date("Ymd"));
			}
        }
    }
    
    private function js_call($params)
    { 
        echo "<script type='text/javascript'>
                //调用微信JS api 支付
                function jsApiCall()
                {
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest',
                        ".$params.",
                        function(res){
                            WeixinJSBridge.log(res.err_msg);
                        }
                    );
                }

                function callpay()
                {
                    if (typeof WeixinJSBridge == 'undefined'){
                        if( document.addEventListener ){
                            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                        }else if (document.attachEvent){
                            document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
                            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                        }
                    }else{
                        jsApiCall();
                    }
                }
                callpay();
            </script>";
    }
	
	//提交支付订单
	public function getOrder() {
		$orderSn = I('order_sn');
		$this->vaild_params('is_empty', $orderSn, '请填写要查询的订单号');
		$order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));

		if (empty($order)) {
			$this->set_code(8);
			$this->set_msg('没有该订单');
			$this->send();
		}
		$userInfo = $this->loadModel('userSaler')->getInfo('*',array('id'=>$this->usid));
		$result['url'] = 'http://bq.980buy.com/api.php?c=wxpay&a=wx_qrcode&order_sn=' . $orderSn;
		$result['share_title']='扫一扫,轻松付款';
		$result['share_desc']='商户:'.$userInfo['name'].'向您发起一笔金额为￥'.$order['money'].'的收款';
		$this->apiOut($result);
	}
	
    public function qrcode(){
    	//在微信浏览器中打开
    	$this->assign('usid',$this->usid);
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {    	
	    	$code = I('code');
	        //$sn = I('order_sn');
	        Vendor('Wxpay.WxPayPubHelper');        
	        $jsApi = new JsApi_pub();
	
	        if (!$code)
	        {
	            $url = $jsApi->createOauthUrlForCode("http://wallet.huirongpay.com/scancode/api.php?m=qrpay&a=qrcode&usid=".$this->usid);
	            
	            header("Location: ".$url); 
	            exit;
	        }
	        else
	        {
	            $jsApi->setCode($code);
	            $openid = $jsApi->getOpenId();
	        }	   
	        //echo $openid;     
	        $this->assign('title','微信扫描');
	        $this->assign('paytype','微信扫描');
	        $this->assign('openid',$openid);
	        $this->display('weixin');
	        
        }
        else 
        {
        	$this->assign('title','支付宝扫描');
        	$this->assign('paytype','支付宝扫描');
	        $this->assign('openid',$openid); 
	        $this->display();       
        }
		//$orderSn = I('order_sn');
        //$order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));  
		//$this->assign('order_sn',$order['sn']);
        
        
	}
	public function wxpay(){
		$money=I('money',0,'floatval');
		$usid=I('usid',0,'floatval');
		$openid=I('openid');
		eblog("微信一码付 - 数据:","$money--$usid--$openid",'wxpay_qrpay_'.date("Ymd"));
		if(empty($usid) || empty($openid))
		{
			$this->ajaxReturn(array(
				'status' => 104,
				'desc' => '用户不能为空！'
			));
		}
		if(empty($money))
		{
			$this->ajaxReturn(array(
				'status' => 104,
				'desc' => '金额不能为空！'
			));
		}
		$this->usinfo = $this->loadModel('userSaler')->getInfoByid($usid);
		$data['desc'] = '一码付';
		$data['usid'] = $usid;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['sn']	= $this->loadModel('orderFtf')->createSn($usid);
		$data['addtime'] = time();

		$account = $this->loadModel('userSalerAccountTotal')->getInfo('normal_usable',array('usid'=>$usid));
		$data['total'] = $account['normal_usable']?$account['normal_usable']:0;
		
		$rs = $this->model['orderFtf']->add($data);
		//$response = $rs?array('sn'=>$data['sn']):false;  

		
		
		if($money){
			$amount=$money*100;
			
			$url = 'http://119.57.140.199:8880/SinglePay/publicNo';
	        $postData = array(
	            'ORDER_ID' => $data['sn'],
	            'ORDER_AMT' => $amount,
	            'PAY_CHANNEL' => 1,
	            'ORDER_NAME' => '测试',
				'USER_ID' => $openid
	        );  
	        eblog("微信一码付 - 提交数据",$postData,'wxpay_qrpay_'.date("Ymd"));
	        $ansData = $this->httpGet($url, $postData); 
	        //return $this->callback($ansData);
	        eblog("微信一码付 - 返回数据",$ansData,'wxpay_qrpay_'.date("Ymd"));
	        $packageinfo = json_decode($ansData);
	        //print_r($packageinfo);
	        $wx = $packageinfo->WX_JSAPI;
	        //echo $wx.'<br>';
	        $wx_jsapi = json_decode($packageinfo->WX_JSAPI);
			$this->ajaxReturn(array(
					'status' => 1,
					'desc' => $wx_jsapi
			));
			
		} else {
			$this->ajaxReturn(array(
					'status' => 101,
					'desc' => '收款金额必须大于0'
			));
		}		
        
	}
	public function weixin(){
		$code = I('code');
        //$sn = I('order_sn');
        Vendor('Wxpay.WxPayPubHelper');        
        $jsApi = new JsApi_pub();

        if (!$code)
        {
            $url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL."usid/".$this->usid);
            header("Location: ".$url); 
            exit;
        }
        else
        {
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }
		//$orderSn = I('order_sn');
        //$order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));  
		//$this->assign('order_sn',$order['sn']);
        $this->assign('usid',$this->usid);
        $this->display();
	}
	public function alipay(){
		$orderSn = I('order_sn');
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));  
		$this->assign('order_sn',$order['sn']);
        $this->assign('money',$order['money']);
        $this->display();
	}
	public function wx_qrcode(){
		$orderSn = I('order_sn');
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));  
		$this->assign('order_sn',$order['sn']);
        $this->assign('money',$order['money']);
        $this->display();
	}
	
	public function cashier_receive(){
		$money=I('money',0,'floatval');
		$usid=I('usid',0,'floatval');
		$openid=I('openid');
		
		if(empty($usid) || empty($openid))
		{
			$this->ajaxReturn(array(
				'status' => 104,
				'desc' => '用户不能为空！'
			));
		}
		if(empty($money))
		{
			$this->ajaxReturn(array(
				'status' => 104,
				'desc' => '金额不能为空！'
			));
		}
		
		$data['desc'] = '验证支付';
		$data['usid'] = $usid;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['sn']	= $this->loadModel('orderFtf')->createSn($usid);
		$data['addtime'] = time();

		$account = $this->loadModel('userSalerAccountTotal')->getInfo('normal_usable',array('usid'=>$usid));
		$data['total'] = $account['normal_usable']?$account['normal_usable']:0;
		
		$rs = $this->model['orderFtf']->add($data);
		$response = $rs?array('sn'=>$data['sn']):false;
		
		$order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));   
		if($money){
			$amount=$money*100;
			//$wxPayHelper = new \Vendor\Weixin\WxPayNewHelper();
			Vendor('Weixin.WxPayNewHelper');        
			$wxPayHelper=new WxPayNewHelper();
			$wxPayHelper->setParameter ( "appid", 'wx128762b258232239');
			$wxPayHelper->setParameter ( "mch_id", '1273224501');
			$wxPayHelper->setParameter ( "notify_url", 'http://wap.9580buy.com/pay/indes.php/WechatNotify/asyncCashier');
			$wxPayHelper->setParameter ( "signtype", 'MD5');
			$wxPayHelper->setParameter ( "partnerkey", 'qch3uxh3t0e66sop4k3r41f8v40ss05p');
			$wxPayHelper->setParameter ( "body", '人人购微信支付收款');
			$wxPayHelper->setParameter ( "out_trade_no", $order['sn']);
			$wxPayHelper->setParameter ( "total_fee", $amount );
			$wxPayHelper->setParameter ( "fee_type", "CNY" );
			$wxPayHelper->setParameter ( "goods_tag", '' ); // $params['total_fee']
			//$wxPayHelper->setParameter ( "sub_openid", $this->_openid ); // $params['total_fee']
			$wxPayHelper->setParameter ( "sub_mch_id", '1308518701');//1278083801
			$wxPayHelper->setParameter ( "sub_appid", 'wx1bcf92ddcd2497f1');
			$xmlstr = $wxPayHelper->service_unifiedorder (); 
			if ($xmlstr) {
				$xmlobj = ( array ) simplexml_load_string ( $xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA );
				// var_dump($xmlobj['prepay_id']);
				dump($xmlobj);
				DIE();
				if ($xmlobj ['return_code'] == 'SUCCESS') {
					$package = $xmlobj['prepay_id'];
					//echo  $package;
					M('testing')->add(array('test'=>$package,'dates'=>date("Y-m-d H:i:s")));
				} else {
					logger ( '获取预支付订单失败,！' . $xmlstr . ':订单：' . $ordersn );
					$this->ajaxReturn(array(
							'status' => 104,
							'desc' => '接口获取预支付订单解析失败'.$xmlobj['prepay_id']
					));
				}
			} else {
				// logger ( '获取预支付订单失败！' . $ordersn );
				$this->ajaxReturn(array(
						'status' => 105,
						'desc' => '接口获取预支付订单失败'
				));
			}
			$packageinfo = $wxPayHelper->create_biz_package($package);
			M('testing')->add(array('test'=>$packageinfo,'dates'=>date("Y-m-d H:i:s")));
			$this->ajaxReturn(array(
					'status' => 1,
					'desc' => $packageinfo
			));
			$this->assign('package', $packageinfo);
		} else {
			$this->ajaxReturn(array(
					'status' => 101,
					'desc' => '收款金额必须大于0'
			));
		}

	}
	public function cash($ptid,$money,$usid,$ctid = 10)
	{
		
		$user_account = $this->loadModel('userSalerAccount')->getInfo("money",array('ptid'=>$ptid,array('usid'=>$usid)));	
		//$this->vaild_params('compare',array($user_account['money'],$data['money'],'>='),'您的余额不足，无法提现');
		$this->usinfo = $this->loadModel('userSaler')->getInfoByid($usid);	
		$lfid = $this->usinfo['lfid'];
		eblog("银联APP异步 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'test_'.date("Ymd"));
		//提现通道限额检测
		$result = $this->loadModel('userSaler')->chkRealtimeLimit($usid,$ptid,$ctid,$lfid,$money);
		if (!is_array($result))
		{
			eblog("银联APP异步 - ",'提现通道限额检测'.$result,'test_'.date("Ymd"));
			return false;
			$this->response(DATA_EMPTY,$result);
		}
		else
		{
			$cash = $result;
		}
		eblog("银联APP异步 - ",'提现数据'.$result,'test_'.date("Ymd"));
		
		$data['ptid'] = $ptid;
		$data['ctid'] = $ctid;
		$data['money'] = $money;
		$data['pt_name'] = $cash['pt_name'];
		$data['ct_name'] = $cash['ct_name'];
		$data['usid'] = $usid;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['fee_rate'] = $cash['fee_rate'];
		$data['fee_static'] = $cash['fee_static'];
		$data['status'] = 0;
		$data['addtime'] = time();
		$data['type'] = CASH_NORMAL;
		eblog("银联APP异步 - 提现数据--data",$data,'test_'.date("Ymd"));
		$rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
		if ($rs_cash)
		{
			//发送异步交易查询,并发送相关消息
			eblog("银联APP异步 - ",'提现数据写入成功','test_'.date("Ymd"));
			$this->single_pay($rs_cash['real_money'],$rs_cash['sn'],$this->usinfo);
			A('Api://processMsg')->msg('CashMsg',$rs_cash);
		}
		eblog("银联APP异步 - 提现数据--rs_cash",$rs_cash,'test_'.date("Ymd"));
		$rs = $rs_cash?true:false;
		return $rs;
		//S($lock,null);
		//$this->apiOut($rs,false);
	}
	public function  single_pay($money,$serialNo,$dataUser = array()) {          
		
		//$serialNo = date('YmdHis', time()) . rand_num(10, true);		 
        
        $url = 'http://119.57.140.199:8880/SinglePay/pay';
        $postData = array(
            'trainID' => $serialNo,
            'cardNo' => $dataUser['card_no'],
            'bankNo' => $dataUser['bank_no'],
            'accBankName' => $dataUser['name'],
			'accBankTypeNo' => $dataUser['bank_type'],
            'transAmt' => $money * 100,
            'busiType' => 0,
            'accType' => 1,
            'resv' => '预留'            
        );  
        eblog("银联APP异步 - 代付数据",$postData,'test_'.date("Ymd"));
        $ansData = $this->httpGet($url, $postData); 
        return $this->callback($ansData);
    }
	/*
     * 代付返回结果处理
     */
    function callback($respContent) {
        $data = json_decode($respContent, true);
        $serialNo = $data['SEQ'];   
        eblog("银联APP异步 - 代付返回数据",$data,'test_'.date("Ymd"));     
        if($data['RES_CODE'] == '00')
        {
        	if ($serialNo)
			{
				$where = array();
				$where['status'] = 0;
				$where['sn'] = $serialNo;
				//更新状态
				$rs = $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
		
			}
			
			
        	return true;
        }
        else 
        {
        	if ($serialNo)
			{
				$where = array();
				$where['status'] = -1;
				$where['sn'] = $serialNo;
				//更新状态
				$rs = $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
		
			}
        	//eblog("银联APP异步 - ",'代付返回数据','single_pay_'.date("Ymd"));
        	return false;
        }        
    }
	/**
     * curl方法
     * @param $url
     * @return mixed
     */
	function httpGet($url, $postData='') {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        if(!empty($postData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;

    } 
}
