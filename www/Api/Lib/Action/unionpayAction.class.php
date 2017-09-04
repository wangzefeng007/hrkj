<?php
/**
 *@desc	 银联APP支付接口 （云天）
 */
class unionpayAction extends baseAction
{
	protected $config;
	protected $charset = 'utf-8';//字符集编码
	protected $sign_type = 'RSA';//加密方式
	protected $merno = 'huirongqianbao';//商户号
	function _initialize()
	{
		$this->config = C('upacp');
		$private_key = $this->config['UNION_PRI_KEY'];
		//公钥由平台提供
		$public_key = $this->config['UNION_PUBLIC_KEY'];
		
		$pemPriKey = chunk_split($private_key, 64, "\n");
		$pemPriKey = "-----BEGIN RSA PRIVATE KEY-----\n".$pemPriKey."-----END RSA PRIVATE KEY-----\n";
		
		$pemPubKey = chunk_split($public_key, 64, "\n");
		$pemPubKey = "-----BEGIN PUBLIC KEY-----\n".$pemPubKey."-----END PUBLIC KEY-----\n";
		
		// $this->priKey = openssl_get_privatekey($pemPriKey);
		// $this->pubKey = openssl_get_publickey($pemPubKey);
		$this->priKey = $pemPriKey;
		$this->pubKey = $pemPubKey;
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
	public function wap_pay($order_sn)
	{
	    $bankid = I('bank_id');
        $params['order_sn'] = $order_sn;
		 $pay = $this->pay($params,"unionpay::wap");
		 if (!$pay){
			$this->apiOut(false);
		 } 
		  $userinfo = $this->userinfo($pay['order']);
		  $requestData =array(
		      'merno' =>$this->merno,
		      'paychannelcode'=>'t0',
		      'money' =>strval($pay['order']["money"] * 100),
		      //'money'=>'2500',
		      'body' =>'银联快捷支付',
		      'trans_id' =>$pay['order']["sn"],
		      'notify_url' =>$this->getDomain().'/api.php/unionpay/notices',
		      'fronturl' =>$this->getDomain().'/wap.php/pay/success',		      
		      'ver' => '1.0.5',
		      'userrate'=>'0',//费率暂定
		      'bankname' =>$userinfo['bankname'],
		      'banknum' =>trim($userinfo['banknum']),
		      'idcard' =>trim($userinfo['idcard']),
		      'name' =>trim($userinfo['name']),
		);
		$fee = $this->getfee($pay);		
		if($fee){
		    $requestData['userfee'] = strval($fee['fee_rate']*$requestData['money'] + $fee['fee_static']);
		}
		$paybanknum = $this->loadModel('userBank')->getinfo('bank_no',array('id'=>$bankid,'status'=>'1')); 
		if($paybanknum){
		    $requestData['paybanknum'] = trim($paybanknum['bank_no']);
		}
		  eblog("云天银联快捷支付请求 ",$requestData,'unionpay_'.date("Ymd"));
        $url = $this->config['PAY_URL'];
        $member_info = array(
            'usid'=>$pay['order']['usid'],
            'us_name' =>$pay['order']['us_name']
        );
        $log_data =array();
        $log_data['nid'] = $pay['order']["sn"];
        $log_data['form_contents'] = serialize($requestData);
        $this->addpaylog($log_data,$member_info);
        $this->getUrl($url,$requestData,'UPACP_PUBLIC_KEY','UPACP_PRIVATE_KEY');		
	}
	
	public function getfee($pay){
	    $usinfo = $this->loadModel('userSaler')->getInfoByid($pay['order']['usid']);	
		$lfid = $usinfo['lfid'];
		//eblog("银联WAP支付异步 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'upacp_'.date("Ymd"));
		//提现通道限额检测
		$result = $this->loadModel('userSaler')->chkRealtimeLimit($pay['order']['usid'],$pay['payinfo']['ptid'],10,$lfid,$pay['order']['money']);		
		if (!is_array($result))
		{
			//eblog("银联WAP支付异步 - ",'提现通道限额检测'.$result,'upacp_'.date("Ymd"));
			return false;
			//$this->response(DATA_EMPTY,$result);
		}
	    return $result;
	}
	
    public function userinfo($pay=array()){      
        $data = array();
        if(empty($pay['usid'])){
            $this->response(USER_EMPTY,'用户不能为空');
        }
        $usid = $pay['usid'];
        $info = $this->loadModel('userSaler')->getInfoByid($usid);
        if(empty($info)){
            $this->response(DATA_EMPTY,'查无该用户');
        }
        $bank_name = $this->loadModel('settingBank')->getInfoByid($info['bank']);
        if(empty($bank_name)){
            $this->response(BANK_EMPTY,'该用户还没绑定银行卡');
        }
        $result['bankname'] = $bank_name['name'];
        $result['banknum'] = $info['bank_no'];
        $result['idcard'] = $info['card_no'];
        $result['name'] = $info['name'];
        return $result;
    }
	//创建支付信息
	private function pay($params,$apiname,$is_api=1)
	{
		$this->vaild_params('is_empty',$params['order_sn'],'订单号不能为空');
		$order = $this->loadModel('order')->getDetail($params['order_sn']);
        if ($order['status']==1) {$this->err_msg("该订单已支付，请勿重复支付！",$is_api);}
		$data = $params;
		$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));
		$data['ptid'] = $pay_type['id'];
		$data['pt_name'] = $pay_type['name'];
        //更新支付渠道
        $rs =  D('orderFtf')->update($data,array('id'=>$order['id']));
//        D('orderFtf')->query("UPDATE  rrg_order_ftf SET pt_name ='".$data['pt_name']."',ptid = ".$data['ptid']." WHERE  id = ".$order['id']);
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
	public function notices($sn='')
	{ 
	    $resp = $_POST;	    
	    $s = $this->decrypt($resp['data'],'UPACP_PRIVATE_KEY',512,11);
	    //插入返回记录
	    $backurl = $this->_request();
	    $url = $backurl['_URL_']['0'].'/'.$backurl['_URL_']['1'];
	    $this->addbacklog($url,$resp,$s);
	    $data = json_decode($s,true);
	    eblog("云天银联快捷支付异步接收参数集",$_POST,'unionpay_'.date("Ymd"));
	    $sn = $sn?$sn:$data['out_trade_no'];
	    $sn = str_replace($this->config['SN_PREFIX'],'',$sn);
	    eblog('','=======================================','unionpay_'.date("Ymd"));
	    eblog("云天银联快捷支付异步接收参数集",$this->_request(),'unionpay_'.date("Ymd"));	
	    if ($data['status'] == '2')
	    {
	        $data['appid'] = $this->config['APPID'];
	        $data['appcode'] = $this->config['APPCODE'];
	        $result = $this->datatourl($data,true,true,false,true);
	        //验证签名
	        if($data['sign'] != strtoupper($result)){
	            eblog("云天银联快捷支付异步 - {$sn}",'签名验证失败','unionpay_'.date("Ymd"));
				echo '2';exit;	            
	        }
            $order = $this->loadModel('order')->getDetail($sn);
            if(!$order)
            {
	                eblog("云天银联快捷支付异步 - {$sn}",'订单不存在','unionpay_'.date("Ymd"));
					echo '2';exit;	                
            }
            $is_pay = $this->loadModel('order')->checkPay($order);
            if($is_pay)
            {
                eblog("云天银联快捷支付异步 - {$sn}",'订单已支付','unionpay_'.date("Ymd"));
				echo '2';exit;                
            }
            #判断状态为0的时候修改订单信息 3为处理中
            $dataFTF = array('status'=>1);
            $rsFTF = D('orderFtf')->update($dataFTF,array('sn'=>$sn,'status'=>'0'));
            #失败处理
            if(!$rsFTF){
                eblog("云天银联快捷支付异步 - {$sn}",'重复订单号','unionpay_'.date("Ymd"));
                echo '2';exit;
            }
            $rs =$this->loadModel('order')->pay($order);
            eblog("订单交易结果：",$rs,'unionpay_'.date("Ymd"));
            if ($rs)
            {              
                eblog("云天银联快捷支付异步 - {$sn}",'订单支付成功','unionpay_'.date("Ymd"));
                //发送异步交易查询,并发送相关消息
                $cash = $this->cash($order['ptid'],$order['money'],$order['usid']);
                A('Api://processMsg')->msg('TradeMsg',$sn);
                //echo '1';exit;
                 if($cash){
                    echo '1';exit;
                } 
            }
	        
	    }elseif($data['status'] == '1'){
	        eblog("云天银联快捷支付异步 - {$sn}",'入账成功，出账失败','unionpay_'.date("Ymd"));
	        echo '2';exit;
	    }
	    else
	    {
	        eblog("云天银联快捷支付异步 - {$sn}",'respCode != 00','unionpay_'.date("Ymd"));
			echo '2';exit;
	    }
	    echo '2';exit;
	}
	
	public function cash($ptid,$money,$usid,$ctid = 10)
	{
	
	    $user_account = $this->loadModel('userSalerAccount')->getInfo("money",array('ptid'=>$ptid,array('usid'=>$usid)));
	    //$this->vaild_params('compare',array($user_account['money'],$data['money'],'>='),'您的余额不足，无法提现');
	    $this->usinfo = $this->loadModel('userSaler')->getInfoByid($usid);
	    $lfid = $this->usinfo['lfid'];
	    eblog("银联WAP支付异步 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'unionpay_'.date("Ymd"));
	    //提现通道限额检测
	    $result = $this->loadModel('userSaler')->chkRealtimeLimit($usid,$ptid,$ctid,$lfid,$money);
	    if (!is_array($result))
	    {
	        eblog("银联WAP支付异步 - ",'提现通道限额检测'.$result,'unionpay_'.date("Ymd"));
	        return false;
	        $this->response(DATA_EMPTY,$result);
	    }
	    else
	    {
	        $cash = $result;
	    }
	    eblog("银联WAP支付异步 - ",'提现数据'.$result,'unionpay_'.date("Ymd"));
	
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
	    $data['status'] = 1;
	    $data['addtime'] = time();
	    $data['type'] = CASH_NORMAL;
	    eblog("银联WAP支付异步 - 提现数据--data",$data,'unionpay_'.date("Ymd"));
	    $rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
	    if ($rs_cash)
	    {
	        //发送异步交易查询,并发送相关消息
	        eblog("银联WAP支付异步 - ",'提现数据写入成功','unionpay_'.date("Ymd"));	        
	        A('Api://processMsg')->msg('CashMsg',$rs_cash);
	    }
	    eblog("银联WAP支付异步 - 提现数据--rs_cash",$rs_cash,'unionpay_'.date("Ymd"));
	    $rs = $rs_cash?true:false;
	    return $rs;
	    //S($lock,null);
	    //$this->apiOut($rs,false);
	}
    
    /**
     * [post 提交数据]
     *
     * @param array $data
     * @param string url格式字符串
     * @param public 公共密钥
     * @param private 私密密钥
     * @return string or array
     */
    public function geturl($url, $data = array(),$public='',$private = '') {
        //将key值转换为大写,直接写大写字母会出错
         
        ksort($data);//对签名参数据排序
        $datas = $this->json_encode_ex($data);
        $rsa_json = $this->getSignMsgs($datas,$public);
        $data_array = array(
            'url' =>stripslashes($url),
            'data'=>urlencode($rsa_json)
        );
        $url = $url.'/?data='.urlencode($rsa_json);        
        echo "<script language=\"javascript\">";
        echo "location.href=\"$url\"";
        echo "</script>";
        //echo $this->buildForm($url, urlencode($rsa_json));        
        //$this->response(REQUEST_SUCCESS, $data_array);
    }
    /**
     * @json 需加密的参数
     * @string 公钥
     * @bool 是否MD5
     * return:
     *      base64加密的密文
     */
    public function getSignMsgs($params,$public='',$md5=false) {
        $signMsg = "";
        //$params_str = $this->datatourl($params,true,$md5);//生成http地址栏，不需要urlencode
    
        switch ($this->sign_type) {
            case 'RSA' :
                $signMsg = $this->encrypt($params, $public,'512','11');              
                break;
            case 'MD5' :
            default :
                $signMsg = strtolower(md5($params));
                break;
        }
        return $signMsg;
    }
    /**
     * @ 分段加密 rsa需512位
     * @json 需加密的json参数
     * @string 公钥加密
     * @return Base64
     */
   public function decrypt($data,$private,$keyLength,$reserveSize){
        $originalData = base64_decode($data);
        $crypto = '';
        $length = strlen($originalData);
        $keyByteSize = $keyLength/8;
        $size = $keyByteSize - $reserveSize;
        /* //获取公钥
        $priv_key = file_get_contents($this->config[$private]);  */      
        $encryptData = '';
        $pkeyid = openssl_pkey_get_private ($this->priKey);//判断公钥是否是可用的
        // 分段解密
        for ($i = 0; $i < $length; $i += $keyByteSize) {
            // block大小: decryptBlock 或 剩余字节数
            $inputLen = $length - $i;
            if ($inputLen > $keyByteSize) {
                $inputLen = $keyByteSize;
            }
            $data = substr($originalData,$i,$keyByteSize);
            // 得到分段解密结果
            openssl_private_decrypt ($data, $encryptData, $pkeyid);//公钥解密
            $crypto .= $encryptData;
        }
        
        return $crypto;
    }
    /**
     * @ 分段加密 rsa需512位
     * @json 需加密的json参数
     * @string 公钥加密
     * @return Base64
     */
    function encrypt($originalData,$public,$keyLength,$reserveSize){

        $crypto = '';
        //获取公钥
        $encryptData = '';
        $pkeyid = openssl_pkey_get_public ($this->pubKey);//判断公钥是否是可用的
        $ss = strlen($originalData); //明文字节数组
        $keyByteSize = $keyLength/8; //密钥字节数
        $size = $keyByteSize - $reserveSize;// 加密块大小=密钥字节数-padding填充字节数
        //分段加密
        for($i=0;$i<=$ss;$i+=$size){
            $inputLen = $ss-$i;
            if($inputLen > $size){
                $inputLen = $size;
            }
            $data = substr($originalData, $i, $size);
            //openssl_sign ($data, $encryptData, $pkeyid, OPENSSL_ALGO_SHA1 );
            openssl_public_encrypt ($data, $encryptData, $pkeyid);//公钥加密
            $crypto .= $encryptData;//拼接密文
        }
        openssl_free_key ( $pkeyid );
        $crypto = base64_encode($crypto);
        return $crypto;   
    }
    /**
     * json 中文不转码
     */
    function json_encode_ex($value) {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $str = json_encode($value);
            $str = preg_replace_callback(
                "#\\\u([0-9a-f]{4})#i",
                function ($matchs) {
                    return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
                },
                $str
            );
            return $str;
        } else {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
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
