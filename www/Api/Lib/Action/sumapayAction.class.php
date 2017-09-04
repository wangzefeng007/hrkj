<?php
/*
*	丰付支付支付接口
*
*/
class sumapayAction extends baseAction
{
	protected $config;
	
	function _initialize()
	{
		parent::_initialize();
		$this->config = C('sumapay');
	}

	//丰付快捷支付流程 1-创建订单
	public function createOrder()
	{
		// $sn = I('sn',$sn);
		// $params['order_sn'] = $sn;
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$pay = $this->pay($params,"sumapay::quick");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$sn = $params['order_sn'];
		$data = array(
			'tradeCode' => 'IFZ1000',	//交易代码
			'requestId' => $params['order_sn'],	// 请求流水编号
			'productId' => $params['order_sn'],	//产品ID
			'productName' => '商品',	//产品名称
			'fund' => $pay['order']['money'],	//产品定价
			'merAcct' => $this->config['merNo'],	//产品供应商的编码，一般为商户代码
			'bizType' => $this->config['bizType'],	//产品业务类型
			'productNumber' => 1,	//产品数量
			'tradeProcess' => $this->config['merNo'],	//商户代码
			'totalBizType' => $this->config['bizType'],	//业务类型
			'totalPrice' => $pay['order']['money'],	//本次支付中所有产品实际需支付的金额
			'passThrough' => '透传信息',	//透传信息
			'goodsDesc' => '商品',	//商品描述
			'userIdIdentity' => $this->usid,	//商户用户唯一标识
			'rePayTimeOut' => '1',	//单位天，0不允许重新支付
			'noticeurl' => $this->config['BACKENDURL'],	//异步后台通知地址
			'encode' => 'GBK',	//报文编码
		);
		$verify = array('requestId','tradeProcess','totalBizType','totalPrice','noticeurl','userIdIdentity','passThrough');
		eblog('','============================创建订单==========================','sumapay_'.date("Ymd"));
		$rs = $this->postsuma($data,$verify);	//与丰付网关通信
		if ($rs['result'] != '00000')
		{
			eblog("丰付支付同步 - {$sn} - 创建订单","请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}",'sumapay_'.date("Ymd"));
			$this->vaild_params('is_empty','',"请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}");
		}
		$res['deposit'] = $rs['bankCardTypeList'][0];
		$res['credit'] = $rs['bankCardTypeList'][1];
		$this->apiOut($res);
	}
	
	//丰付快捷支付流程 1.5-验证绑定
	/* public function validBind($pay)
	{
		$this->vaild_params('is_empty',$pay,'未找到订单信息!');
		$memo = unserialize($pay['payinfo']['memo']);
		$data = array(
			'tradeCode' => 'IFC0001',	//交易代码
			'requestId' => $pay['order']['order_sn'],	// 请求流水编号
			'tradeProcess' => $this->config['merNo'],	//商户代码
			'shortBankAccount' => substr($pay['payinfo']['credit_no'],-4),	//卡号后四位
			'bankCode' => $memo['bankCode'],	//银行代码
			'bankCardType' => $memo['bankCardType'],	//银行卡类型
			'userIdIdentity' => $this->usid,	//商户用户唯一标识
			'encode' => 'GBK',	//报文编码
		);
		$verify = array('requestId','tradeProcess','bankCode','shortBankAccount','bankCardType','userIdIdentity');
		eblog('','==========================验证绑定============================','sumapay_'.date("Ymd"));
		$rs = $this->postsuma($data,$verify);	//与丰付网关通信
		if ($rs['result'] != '00000')
		{
			eblog("丰付支付同步 - {$sn} - 验证绑定","请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}",'sumapay_'.date("Ymd"));
			$this->vaild_params('is_empty','',"请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}");
		}
		$res = $rs['bankCardTypeList'];
		$this->apiOut($res); 
	}*/
	
	//丰付快捷支付流程 2-发送验证码
	public function sendCode()
	{
		if (SUMADEBUG === true)
		{
			$this->vaild_params('is_empty','',"测试流程,请直接提交支付!");
		}
		$first = I('first',1);
		$validDate = I('validDate');
		$_params = array('order_sn','name','mobile','credit_no','credit_cvv','card_no');
		$params = $this->get_params($_params);
		
		//验证用户后台填入信息是否一致
		if ($this->usinfo['name'] != $params['name'] || $this->usinfo['card_no'] != $params['card_no'])
		{
			$this->vaild_params('is_empty','',"错误!您填写的 姓名/身份证 与后台填写的资料不一致!");
		}
		
		$memo['bankCode'] = I('bankCode');
		$memo['bankCardType'] = I('bankCardType');
		$sn = $params['order_sn'];
		$params['credit_year'] = "20".substr($validDate,-2);
		$params['credit_month'] = substr($validDate,0,2);
		$validDate = substr($params['credit_year'],-2).$params['credit_month'];
		if ($first)
		{
			$data = array(
				'tradeCode' => 'IFY0001',	//交易代码
				'requestId' => $params['order_sn'],	// 请求流水编号
				'requestOrderId' => $params['order_sn'],	//产品ID
				'tradeProcess' => $this->config['merNo'],	//商户代码
				'idType' => '0',	//证件类型, 0-身份证
				'idNumber' => $params['card_no'],	//证件号
				'name' => $params['name'],	//姓名
				'mobilePhone' => $params['mobile'],	//预留手机号
				'bankAccount' => $params['credit_no'],	//银行账户
				'bankCode' => $memo['bankCode'],	//银行代码
				'bankCardType' => $memo['bankCardType'],	//银行卡类型
				'userIdIdentity' => $this->usid,	//商户用户唯一标识
				'encode' => 'GBK',	//报文编码
			);
			if ($memo['bankCardType'])
			{
				$data['validDate'] = $validDate;	//信用卡有效期
				$data['cvnCode'] = $params['credit_cvv'];	//信用卡CVN码
			}
			$verify = array('requestId','requestOrderId','tradeProcess','idType','idNumber','name','mobilePhone','bankCode','bankAccount','bankCardType','validDate','cvnCode','userIdIdentity');
		}
		else
		{
			// $data = array(
				// 'tradeCode' => 'IFY0002',	//交易代码
				// 'requestId' => $params['order_sn'],	// 请求流水编号
				// 'requestOrderId' => $params['order_sn'],	//产品ID
				// 'tradeProcess' => $this->config['merNo'],	//商户代码
				// 'bindId' => '',	//绑定号
				// 'userIdIdentity' => $this->usid,	//商户用户唯一标识
				// 'encode' => 'GBK',	//报文编码
			// );
			// $verify = array('requestId','requestOrderId','tradeProcess','bindId','userIdIdentity','passThrough');
		}
		eblog('','==========================发送验证码============================','sumapay_'.date("Ymd"));
		$rs = $this->postsuma($data,$verify);	//与丰付网关通信
		if ($rs['result'] != '00000')
		{
			eblog("丰付支付同步 - {$sn} - 发送验证码","请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}",'sumapay_'.date("Ymd"));
			$this->vaild_params('is_empty','',"请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}");
		}
		$memo['randomValidateId'] = $rs['randomValidateId'];
		$memo['tradeId'] = $rs['tradeId'];
		$params['memo'] = serialize($memo);
		$pay = $this->pay($params,"sumapay::quick");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$this->apiOut(true);
	}
	
	//丰付快捷支付流程 3-提交支付
	public function payOrder()
	{
		$first = I('first',1);
		$code = I('code');
		$this->vaild_params('is_empty',$code,'验证码不能为空!');
		$_params = array('order_sn');
		$params = $this->get_params($_params);
		$pay = $this->pay($params,"sumapay::quick");
		if (!$pay)
		{
			$this->apiOut(false);
		}
		$sn = $params['order_sn'];
		$memo = unserialize($pay['payinfo']['memo']);
		$validDate = substr($pay['payinfo']['credit_year'],-2).$pay['payinfo']['credit_month'];
		if (SUMADEBUG === true)
		{
			// $rs = $this->loadModel('order')->pay($pay['order'],$pay['payinfo']);
			$rs = $this->loadModel('sumapay')->pay($pay['order'],$pay['payinfo']);	//丰付支付 专属流程 - 支付金额归入分润
			$this->apiOut(true);
		}
		if ($first)
		{
			$data = array(
				'tradeCode' => 'IFZ0001',	//交易代码
				'requestId' => $params['order_sn'],	// 请求流水编号
				'requestOrderId' => $params['order_sn'],	//产品ID
				'tradeProcess' => $this->config['merNo'],	//商户代码
				'bankCode' => $memo['bankCode'],	//银行代码
				'bankCardType' => $memo['bankCardType'],	//银行卡类型
				'bankAccount' => $pay['payinfo']['credit_no'],	//银行账户
				'validDate' => $validDate,	//信用卡有效期
				'cvnCode' => $pay['payinfo']['credit_cvv'],	//信用卡CVN码
				'idType' => '0',	//证件类型, 0-身份证
				'idNumber' => $pay['payinfo']['card_no'],	//证件号
				'name' => $pay['payinfo']['name'],	//姓名
				'mobilePhone' => $pay['payinfo']['mobile'],	//预留手机号
				'isNeedBind' => '0',	//是否需要绑定 0-不绑定,1-绑定
				'userIdIdentity' => $this->usid,	//商户用户唯一标识
				'passThrough' => '透传信息',	//透传信息
				'tradeId' => $memo['tradeId'],	//校验码编号
				'randomValidateId' => $memo['randomValidateId'],	//校验码编号
				'randomCode' => $code,	//短信校验码
				'encode' => 'GBK',	//报文编码
			);
			$verify = array('requestId','requestOrderId','tradeProcess','bankCode','bankAccount','bankCardType','validDate','cvnCode','idType','idNumber','name','mobilePhone','userIdIdentity','passThrough','tradeId');
		}
		else
		{
			// $data = array(
				// 'tradeCode' => 'IFZ0001',	//交易代码
				// 'requestId' => $params['order_sn'],	// 请求流水编号
				// 'requestOrderId' => $params['order_sn'],	//产品ID
				// 'tradeProcess' => $this->config['merNo'],	//商户代码
				// 'bindId' => '',	//绑定号
				// 'userIdIdentity' => $this->usid,	//商户用户唯一标识
				// 'passThrough' => '透传信息',	//透传信息
				// 'randomValidateId' => $memo['randomValidateId'],	//校验码编号
				// 'randomCode' => '',	//短信校验码
				// 'encode' => 'GBK',	//报文编码
			// );
			// $verify = array('requestId','requestOrderId','tradeProcess','bindId','userIdIdentity','passThrough');
		}
		eblog('','==========================提交支付============================','sumapay_'.date("Ymd"));
		$rs = $this->postsuma($data,$verify);	//与丰付网关通信
		if ($rs['result'] != '00000')
		{
			eblog("丰付支付同步 - {$sn} - 提交支付","请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}",'sumapay_'.date("Ymd"));
			$this->vaild_params('is_empty','',"请求失败! {$rs['result']} - {$this->config['error_list'][$rs['result']]}");
		}
		else
		{
			if ($rs['status'] == '2')
			{
				$is_pay = $this->loadModel('order')->checkPay($pay['order']);
				if(!$is_pay)
				{
					$memo['payId'] = $rs['payId'];
					$memo['tradeFee'] = $rs['tradeFee'];
					$pay['payinfo']['memo'] = serialize($memo);
					// $rs = $this->loadModel('order')->pay($pay['order'],$pay['payinfo']);
					$rs = $this->loadModel('sumapay')->pay($pay['order'],$pay['payinfo']);	//丰付支付 专属流程 - 支付金额归入分润
					if ($rs)
					{
						eblog("丰付支付同步 - {$sn} - 提交支付",'订单支付成功','sumapay_'.date("Ymd"));
						//发送异步交易查询,并发送相关消息
						A('Api://processMsg')->msg('TradeMsg',$sn);
					}
				}
				else
				{
					eblog("丰付支付同步 - {$sn} - 提交支付",'订单已支付','sumapay_'.date("Ymd"));
				}
			}
		}
		$this->apiOut(true);
	}
	
	//与丰付网关通信
	public function postsuma($data,$verify)
	{
		eblog("丰付支付同步 - {$data['requestId']} - 请求参数",$data,'sumapay_'.date("Ymd"));
		if ($data)
		{
			foreach ($data as $key => $val)
			{
				$data[$key] = mb_convert_encoding( $val,'GBK','auto');
			}
		}
		$str = '';
		if ($verify)
		{
			foreach ($verify as $val)
			{
				$str .= $data[$val];
			}
		}
		$data['mersignature'] = $this->HmacMd5($str, $this->config['merKey']);
		$url = $this->config['QUICK_PAY_URL'];
		$res = curl_post($url,$data);
		// $res = fsock_post($url,$data);
		// $res = html_post($url,$data,1);
		$res = mb_convert_encoding( $res,'utf-8','GBK');
		$res = json_decode($res,true);
		eblog("丰付支付同步 - {$data['requestId']} - 服务器应答",$res,'sumapay_'.date("Ymd"));
		return $res;
	}
	
	//数据签名
	function HmacMd5($data, $key) {
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing(NOTE: Hacked means written)

		//需要配置环境支持iconv，否则中文参数不能正常处理
		$key = iconv("GBK", "UTF-8", $key);
		$data = iconv("GBK", "UTF-8", $data);
		
		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*", md5($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5($k_opad . pack("H*", md5($k_ipad . $data)));
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
		$paylog = $this->loadModel('orderPaylog')->getInfo('*',array('order_sn'=>$order['sn']));
		$rs = !$paylog?$this->model['orderPaylog']->add($data):$this->model['orderPaylog']->update($data,array('id'=>$paylog['id']));
		if (!$rs) return false;
		return array('order'=>$order,'payinfo'=>$paylog);
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
	public function notice()
	{
		echo 'success';
		$_params = $this->_post();
		if ($_params)
		{
			foreach ($_params as $key => $val)
			{
				$_params[$key] = mb_convert_encoding( $val,'utf-8','GBK');;
			}
		}

		eblog('','=======================================','sumapay_'.date("Ymd"));
		eblog("丰付支付支付异步接收参数集 - post",$_params,'sumapay_'.date("Ymd"));
		eblog("丰付支付支付异步接收参数集 - get",$this->_get(),'sumapay_'.date("Ymd"));

		
		//验证签名
		$verify = array('requestId','payId','fiscalDate','description');
		$str = '';
		if ($verify)
		{
			foreach ($verify as $val)
			{
				$str .= mb_convert_encoding($_params[$val],'gb2312','auto');
			}
		}
		$result = $this->HmacMd5($str, $this->config['merKey']);
		$sign = $_params['resultSignature'];
		
		eblog("丰付支付异步 - 验签参数 str",$str,'sumapay_'.date("Ymd"));
		eblog("丰付支付异步 - 验签结果 result",$result,'sumapay_'.date("Ymd"));

		if ($result == $sign || SUMADEBUG === true)	//验签成功
		{
			$sn = I('sn',$_params['requestId']);
			$resp = $_params;

			if ($resp['status'] == '2' || SUMADEBUG === true)	//支付成功
			{
				$order = $this->loadModel('order')->getDetail($sn);
				if(!$order)
				{
					eblog("丰付支付异步 - {$sn}",'订单不存在','sumapay_'.date("Ymd"));
					return false;
				}
				$is_pay = $this->loadModel('order')->checkPay($order);
				if($is_pay)
				{
					eblog("丰付支付异步 - {$sn}",'订单已支付','sumapay_'.date("Ymd"));
					return false;
				}
				#判断状态为0的时候修改订单信息 3为处理中
				$dataFTF = array('status'=>1);
				$rsFTF = D('orderFtf')->update($dataFTF,array('sn'=>$sn,'status'=>'0'));
				#失败处理
				if(!$rsFTF){
				    eblog("丰付支付异步 - {$sn}",'重复订单号','sumapay_'.date("Ymd"));
				    echo '00';
				    return false;
				}				
				$payinfo = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
				// $rs =$this->loadModel('order')->pay($order,$payinfo);
				$rs =$this->loadModel('sumapay')->pay($order,$payinfo);	//丰付支付 专属流程 - 支付金额归入分润
				if ($rs)
				{
					eblog("丰付支付异步 - {$sn}",'订单支付成功','sumapay_'.date("Ymd"));
					//发送异步交易查询,并发送相关消息
					A('Api://processMsg')->msg('TradeMsg',$sn);
				}
				
				if (SUMADEBUG === true) return $rs;
			}
		}
		else
		{
			eblog("丰付支付异步 - {$sn}",'签名验证失败','sumapay_'.date("Ymd"));
		}
	}
}
