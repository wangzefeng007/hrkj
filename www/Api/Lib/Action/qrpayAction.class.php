<?php
/*
 *  微信支付宝支付接口
 *  提交、异步通知地址
 */
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
	        $this->assign('storename',$this->usinfo['business_name']?$this->usinfo['business_name']:'汇融钱包');
	        $this->assign('openid',$openid);
	        $this->display('weixin');
	        
        }
        else
        {
        	$auth_code = I('auth_code');
	        //$sn = I('order_sn');
	        
	        if (!$auth_code)
	        {
	            $openauth = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2016102002255068&scope=auth_base&redirect_uri=';
	        	$url = $openauth . urlencode("http://wallet.huirongpay.com/api.php?m=qrpay&a=index&usid=".$this->usid);
	            //echo $url;die();
	            header("Location: ".$url); 
	            exit;
	        }
	        else
	        {
	            Vendor('Aop.Config');
	        	Vendor('Aop.AopClient');
	        	Vendor('Aop.request.AlipaySystemOauthTokenRequest');	        	
	        		        	
	        	$aop = new AopClient ();
				$aop->gatewayUrl = ALIPAYAOP_GATEWAY_URL;
				$aop->appId = ALIPAYAOP_APP_ID;
				$aop->rsaPrivateKey = ALIPAYAOP_APP_PRIVATE_KEY;
				$aop->rsaPrivateKeyFilePath = ALIPAYAOP_APP_PRIVATE_KEY_FILE_PATH;
				$aop->alipayPublicKey = ALIPAYAOP_ALIPAY_PUBLIC_KEY;
				$aop->apiVersion = '1.0';
				$aop->postCharset='UTF-8';
				$aop->format='json';

	        	
	        	$request = new AlipaySystemOauthTokenRequest ();
		//return $AlipaySystemOauthTokenRequest;
				//echo $auth_code;
				$request->setCode ( $auth_code );
				$request->setGrantType ("authorization_code");
				//print_r($request);
				$response = $aop->execute ( $request); 
				//print_r($response); 
				$openid = $response->alipay_system_oauth_token_response->user_id;
	        	//echo $auth_code;
	            //$openid = $this->getUserId($auth_code);
	            //echo $openid;
	        	//$jsApi->setCode($code);
	            //$openid = $jsApi->getOpenId();
	            //$this->display('weixin');
	        }	   
        	$this->assign('title','支付宝扫描');
        	$this->assign('paytype','支付宝扫描');
        	$this->assign('storename',$this->usinfo['business_name']?$this->usinfo['business_name']:'汇融钱包');
	        $this->assign('openid',$openid);
	        $this->display('alipay');
        }
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
		$data['desc'] = '一码付微信';
		$data['usid'] = $usid;
		$data['money'] = $money;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['sn']	= $this->loadModel('orderFtf')->createSn($usid);
		$data['addtime'] = time();

		$account = $this->loadModel('userSalerAccountTotal')->getInfo('normal_usable',array('usid'=>$usid));
		$data['total'] = $account['normal_usable']?$account['normal_usable']:0;
		
		$rs = $this->model['orderFtf']->add($data);
		//$response = $rs?array('sn'=>$data['sn']):false;

		
		
		if($money){
			$amount= $money*100;
			
			$order = $this->pay($data['sn'],"qrcode::pay"); 
			if ($order)
			{
	            $this->ajaxReturn(array(
					'status' => 404,
					'desc' => $order['msg']
				));
			}
			
			$url = 'http://172.16.102.101:8880/SinglePay/publicNo';
	        $postData = array(
	            'ORDER_ID' => $data['sn'],
	            'ORDER_AMT' => $amount,
	            'PAY_CHANNEL' => 1,
	            'ORDER_NAME' => '微信支付',
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
		$money=I('money',0,'floatval');
		$usid=I('usid',0,'floatval');
		$openid=I('openid');
		eblog("支付宝一码付 - 数据:","$money--$usid--$openid",'alipay_qrpay_'.date("Ymd"));
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
		$data['desc'] = '一码付支付宝';
		$data['usid'] = $usid;
		$data['money'] = $money;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['sn']	= $this->loadModel('orderFtf')->createSn();
		$data['addtime'] = time();

		$account = $this->loadModel('userSalerAccountTotal')->getInfo('normal_usable',array('usid'=>$usid));
		$data['total'] = $account['normal_usable']?$account['normal_usable']:0;
		
		$rs = $this->model['orderFtf']->add($data);
		//$response = $rs?array('sn'=>$data['sn']):false;  

		
		
		if($money){
			$amount= $money*100;
			
			$order = $this->pay($data['sn'],"qrcode::pay"); 
			if ($order)
			{
	            $this->ajaxReturn(array(
					'status' => 404,
					'desc' => $order['msg']
				));
			}
			
			$url = 'http://172.16.102.101:8880/SinglePay/publicNo';
	        $postData = array(
	            'ORDER_ID' => $data['sn'],
	            'ORDER_AMT' => $amount,
	            'PAY_CHANNEL' => 0,
	            'ORDER_NAME' => '支付宝支付',
				'USER_ID' => $openid
	        );  
	        eblog("支付宝一码付 - 提交数据",$postData,'alipay_qrpay_'.date("Ymd"));
	        $ansData = $this->httpGet($url, $postData); 
	        //return $this->callback($ansData);
	        eblog("支付宝一码付 - 返回数据",$ansData,'alipay_qrpay_'.date("Ymd"));
	        //$packageinfo = json_decode($ansData);
	        //print_r($packageinfo);
	        //$wx = $packageinfo->WX_JSAPI;
	        //echo $wx.'<br>';
	        //$wx_jsapi = json_decode($packageinfo->WX_JSAPI);
			$this->ajaxReturn(array(
					'status' => 1,
					'desc' => json_decode($ansData)
			));
			
		} else {
			$this->ajaxReturn(array(
					'status' => 101,
					'desc' => '收款金额必须大于0'
			));
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
            return array('msg'=>'该订单已支付，请勿重复支付！');
        	$this->err_msg("该订单已支付，请勿重复支付！",$is_api);
        }
		if ($order['money']<0.01) $this->err_msg("订单金额少于0.01元!",$is_api);
 		$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));

		//通道限额检测
		$err_msg = $this->loadModel('userSaler')->chkPayLimit($order,$pay_type['id'],$this->usinfo['lfid']);
		if ($err_msg)
		{
			return array('msg'=>$err_msg);
			//$this->response(DATA_EMPTY,$err_msg);
		}
		
		$data = array();
		$data['ptid'] = $pay_type['id'];
		$data['pt_name'] = $pay_type['name'];
        $rs =  D('orderFtf')->update($data,array('sn'=>$order['sn']));
        if (!$rs) return false;
        return $order;
	}
	public function getUserId($auth_code) {
		$token = $this->requestToken ( $auth_code );
		// echo "token: " .var_export($token);
		print_r($token);
		exit;
		if (isset ( $token->alipay_system_oauth_token_response )) {
			// 成功返回
			// 示例：array(
			// 'access_token' => 'publicpBfd7aa055c4c34120949e287f84eee84a',
			// 'expires_in' => 500,
			// 're_expires_in' => 300,
			// 'refresh_token' => 'publicpB343643c1f58b415ab9add66c0ea91fd3',
			// )
			$token_str = $token->alipay_system_oauth_token_response->access_token;
			// echo $token_str;
			$user_info = $this->requestUserInfo ( $token_str );
			// var_dump ( $user_info );
			if (isset ( $user_info->alipay_user_userinfo_share_response )) {
				$user_info_resp = $user_info->alipay_user_userinfo_share_response;
				
				// 以下每个字段都需要申请权限，才会返回。
				// 支付宝返回的是GBK编码，所以中文会有乱码
				// 'phone' => '',
				// 'deliver_fullname' => '濞村嘲',
				// 'user_type_value' => '2',
				// 'is_mobile_auth' => 'T',
				// 'user_id' => 'BM7PjM8f8-v6VFqeTlFUqo97w0QKRHRl-OmymTOxsGHnKDWiwQekMHiEi06tEbjgbb',
				// 'gender' => 'm',
				// 'zip' => '',
				// 'cert_type_value' => '0',
				// 'is_licence_auth' => 'F',
				// 'deliver_province' => '瀹?,
				// 'deliver_city' => '钘?,
				// 'is_certified' => 'T',
				// 'deliver_area' => '濮濇笟',
				// 'is_bank_auth' => 'T',
				// 'deliver_mobile' => '1234',
				// 'email' => '213412@vip.qq.com',
				// 'address' => '娑撶痪鐠?99宄般亯鎼存潪娴犺泛D4B鎼?F',
				// 'user_status' => 'T',
				// 'cert_no' => '32142134',
				// 'real_name' => '濞村嘲',
				// 'is_id_auth' => 'T',
				// 'deliver_address_list' =>
				$user_id = $user_info_resp->user_id;
				// $deliver_fullname = iconv("GBK", "UTF-8//IGNORE", $user_info_resp->deliver_fullname);
				//$deliver_fullname = characet ( $user_info_resp->deliver_fullname );
				//$deliver_mobile = $user_info_resp->deliver_mobile;
				//echo $deliver_fullname;
				//writeLog ( $deliver_fullname );
				eblog("支付宝一码付 - 数据:",$deliver_fullname,'alipay_qrpay_'.date("Ymd"));
				return $user_id;
			}
			// print_r($user_info);
			//writeLog ( "user_info" . var_export ( $user_info, true ) );
		} elseif (isset ( $token->error_response )) {
			// 返回了错误信息
			// 如：[code] => 40002
			// [msg] => Invalid Arguments
			// [sub_code] => isv.code-invalid
			// [sub_msg] => 授权码code无效
			eblog("支付宝一码付 - 数据:",$token->error_response->sub_msg,'alipay_qrpay_'.date("Ymd"));
			// 记录错误返回信息
			//writeLog ( $token->error_response->sub_msg );
		}
		eblog("支付宝一码付 - 数据:",var_export ( $token, true ),'alipay_qrpay_'.date("Ymd"));
		//writeLog ( var_export ( $token, true ) );
	}
	public function requestUserInfo($token) {
		Vendor('Aop.request.AlipaySystemOauthTokenRequest');
		Vendor('Aop.request.AlipayUserUserinfoShareRequest');
		$AlipayUserUserinfoShareRequest = new AlipayUserUserinfoShareRequest ();
		// $AlipayUserUserinfoShareRequest->setProdCode ( $token );
		
		$result = $this->aopclient_request_execute ( $AlipayUserUserinfoShareRequest, $token );
		return $result;
	}
	public function requestToken($auth_code) {
		Vendor('Aop.request.AlipaySystemOauthTokenRequest');
		Vendor('Aop.request.AlipayUserUserinfoShareRequest');
		$AlipaySystemOauthTokenRequest = new AlipaySystemOauthTokenRequest ();
		//return $AlipaySystemOauthTokenRequest;
		$AlipaySystemOauthTokenRequest->setCode ( $auth_code );
		$AlipaySystemOauthTokenRequest->setGrantType ( "authorization_code" );
		//return $AlipaySystemOauthTokenRequest;
		$result = $this->aopclient_request_execute ( $AlipaySystemOauthTokenRequest );
		return $result;
	}
	//转换编码
	function characet($data) {
		if (! empty ( $data )) {
			$fileType = mb_detect_encoding ( $data, array (
					'UTF-8',
					'GBK',
					'GB2312',
					'LATIN1',
					'BIG5' 
			) );
			if ($fileType != 'UTF-8') {
				$data = mb_convert_encoding ( $data, 'UTF-8', $fileType );
			}
		}
		return $data;
	}

	/**
	 * 使用SDK执行接口请求
	 * @param unknown $request
	 * @param string $token
	 * @return Ambigous <boolean, mixed>
	 */
	function aopclient_request_execute($request, $token = NULL) {
		global $config;
		Vendor('Aop.Config');
		Vendor('Aop.AopClient');print_r($config);
		$aop = new AopClient (); 
		$aop->gatewayUrl = Config::gatewayUrl;
		$aop->appId = Config::app_id;
		$aop->rsaPrivateKeyFilePath = Config::merchant_private_key_file;
		$aop->apiVersion = "1.0";//return $aop;
		$result = $aop->execute ( $request, $token );
		eblog("支付宝一码付 - 数据:",var_export($result,true),'alipay_qrpay_'.date("Ymd"));
		var_export($result,true);return 1;
		return $result;
	}
	/**
     * curl方法
     * @param $url
     * @return mixed
     */
	function httpGethttpGet($url, $postData='') {
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
