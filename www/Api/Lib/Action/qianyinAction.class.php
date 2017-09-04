<?php
/*
*	牵引APP支付接口
*
*/
class qianyinAction extends baseAction
{
	protected $config;
	private $parameter = array();
	function _initialize()
	{
		$this->config = C('upacp');
	}

	//银联sdk-app客户端访问验证签名
	public function sdk_app()
	{
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$pay = $this->pay($params,"upacp::sdk");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$data = $this->purchase($pay['order']);
		M('order_paylog')->where(array('order_sn'=>$params['order_sn']))->save(array('bank_ord_id'=>$data['tn']));
		if($data['respCode'] == '00')
		{
			$this->response(REQUEST_SUCCESS,$data['respMsg'],$data['tn']);
		}
		else 
		{
			$this->response($data['respCode'],$data['respMsg'],$data['tn']);
		}

		//$data['tn'] = $this->purchase($pay['order']);

		//$this->apiOut($data);
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
		//print_r($pay);
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
        $rs =  $this->loadModel('orderFtf')->update($data,array('sn'=>$order['sn']));
        if (!$rs) return false;
		//print_r($order);
		return array('order'=>$order,'payinfo'=>$data);
	}

	//验证签名,并向upmp客户端提交支付请求
	private function purchase($order)
	{
		//调试模式
		if (QYDEBUG === true)
		{
			$this->notice($order['sn']);
			$this->response(PARAMS_ERROR,"调试模式,订单支付成功!");
		}
		//print_r($order);
		$this->parameter = array(
            //'accNo' => $data['accNo'],
            'backUrl' => $this->config['BACKENDURL'], //通知URL
            //'cardNumberLock'=> 1,
            'merId' => $this->config['MERID'], //账户标识
            'orderId' => $order['sn']? $order['sn'] : date('YmdHis'), //商户订单号  order_trade_no
            //'frontEndUrl'		=> $data['front'] ? $data['front'] : '', //返回URL
            'txnAmt' => $order['money'] ? $order['money'] * 100 : 1, //订单金额  
            'txnTime' => $order['addtime'] ? date('YmdHis',$order['addtime']) : date('YmdHis'), //订单金额
        );

        $dataJson = json_encode($this->parameter);
        //echo $dataJson.'<br>';
        $reqContent = base64_encode($dataJson);

        //echo $reqContent.'<br>';
        $signature = MD5($reqContent . $this->config['SECRET']);
        //SDK请求地址和数据
        $sdk_uri = $this->config['PAY_URL'];



        $url = $sdk_uri . '/qyapi/v3/mer/gettn?signature=' . $signature . '&reqContent=' . urlencode($reqContent);

        //构造自动提交的表单
        //echo $url.'<br>';
        $html_data = curl_get($url); 
        //echo $html_data.'<br>';

        $arrContent = explode("&respContent=", $html_data);

        $respContent = urldecode($arrContent[1]);


        $data = base64_decode($respContent);
        
        $dataOrder = json_decode($data, true);
        //$jsonData = json_decode($reqContent, true) ;
        //echo $data;
		//print_r($dataOrder);
        return $dataOrder;
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
		$respContent = $_REQUEST['respContent'];
        $data = base64_decode($respContent);
        $data = json_decode($data, true);
		
		$transStatus = $data['resultCode'];
        $sn = $data['orderId'];
		eblog('','=======================================','qianyin_'.date("Ymd"));
		eblog("银联APP支付异步接收参数集",$this->_request(),'qianyin_'.date("Ymd"));
		if ($transStatus == '000000000' || QYDEBUG === true)
		{
			//验证签名
			$sign = $_REQUEST['signature'];
			$signkey = $this->config['SECRET'];

			// 验签数据组装
			$str = $_REQUEST['respContent'] . $signkey;
			$result = md5($str);
			if ($result == $sign || QYDEBUG === true)
			{
				$order = $this->loadModel('order')->getDetail($sn);
				if(!$order)
				{
					eblog("银联APP异步 - {$sn}",'订单不存在','qianyin_'.date("Ymd"));
					return false;
				}
				$is_pay = $this->loadModel('order')->checkPay($order);
				if($is_pay)
				{
					eblog("银联APP异步 - {$sn}",'订单已支付','qianyin_'.date("Ymd"));
					return false;
				}
				
				$payinfo = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
				$rs =$this->loadModel('order')->pay($order,$payinfo);
				eblog("银联APP异步 - {$sn}--order",$order,'qianyin_'.date("Ymd"));
				eblog("银联APP异步 - {$sn}--payinfo",$payinfo,'qianyin_'.date("Ymd"));
				if ($rs)
				{
					eblog("银联APP异步 - {$sn}",'订单支付成功','qianyin_'.date("Ymd"));
					//发送异步交易查询,并发送相关消息
					M('order_paylog')->where(array('order_sn'=>$sn))->save(array('query_id'=>$data['queryId']));
					A('Api://processMsg')->msg('TradeMsg',$sn);
				}
				else 
				{
					eblog("银联APP异步 - {$sn}",'订单支付成功----','upmppay_'.date("Ymd"));
				}
				$order_type  =  $this->loadModel('order')->getOrderType($sn);
				if($order_type == 2)
				{
					$this->cash($payinfo['ptid'],$order['money'],$order['usid']);
				}
				if (QYDEBUG === true) return $rs;
			}
			else
			{
				eblog("银联APP异步 - {$sn}",'签名验证失败','qianyin_'.date("Ymd"));
			}
		}
	}
	public function cash($ptid,$money,$usid,$ctid = 10)
	{
		
		$user_account = $this->loadModel('userSalerAccount')->getInfo("money",array('ptid'=>$ptid,array('usid'=>$usid)));	
		//$this->vaild_params('compare',array($user_account['money'],$data['money'],'>='),'您的余额不足，无法提现');
		$this->usinfo = $this->loadModel('userSaler')->getInfoByid($usid);	
		$lfid = $this->usinfo['lfid'];
		eblog("银联APP异步 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'qianyin_'.date("Ymd"));
		//提现通道限额检测
		$result = $this->loadModel('userSaler')->chkRealtimeLimit($usid,$ptid,$ctid,$lfid,$money);
		if (!is_array($result))
		{
			eblog("银联APP异步 - ",'提现通道限额检测'.$result,'qianyin_'.date("Ymd"));
			return false;
			$this->response(DATA_EMPTY,$result);
		}
		else
		{
			$cash = $result;
		}
		eblog("银联APP异步 - ",'提现数据'.$result,'qianyin_'.date("Ymd"));
		
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
		eblog("银联APP异步 - 提现数据--data",$data,'qianyin_'.date("Ymd"));
		$rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
		if ($rs_cash)
		{
			//发送异步交易查询,并发送相关消息
			eblog("银联APP异步 - ",'提现数据写入成功','qianyin_'.date("Ymd"));
			$this->single_pay($rs_cash['real_money'],$rs_cash['sn'],$this->usinfo);
			A('Api://processMsg')->msg('CashMsg',$rs_cash);
		}
		eblog("银联APP异步 - 提现数据--rs_cash",$rs_cash,'qianyin_'.date("Ymd"));
		$rs = $rs_cash?true:false;
		return $rs;
		//S($lock,null);
		//$this->apiOut($rs,false);
	}
	public function  single_pay($money,$serialNo,$dataUser = array()) {          
		
		//$serialNo = date('YmdHis', time()) . rand_num(10, true);
        $url = SINGLE_PAY_URL;
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
        eblog("银联APP异步 - 代付数据",$postData,'single_pay_'.date("Ymd"));
        $ansData = $this->httpGet($url, $postData); 
        return $this->callback($ansData);
    }
	/*
     * 代付返回结果处理
     */
    function callback($respContent) {
        $data = json_decode($respContent, true);
        $serialNo = $data['SEQ'];   
        eblog("银联APP异步 - 代付返回数据",$data,'single_pay_'.date("Ymd"));     
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
