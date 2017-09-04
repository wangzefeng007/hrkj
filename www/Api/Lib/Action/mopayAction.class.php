<?php
class mopayAction extends baseAction
{
	protected $config;
	
	function _initialize()
	{
		$this->config = C('mopay');
	}
	
	/*
	*	建立订单  localhost.wsh.com/api.php?m=mopay&a=order
	*/
	function order()
	{
		Vendor('Mopay.MobaoPay');
		$sn = I('order_sn');
		
		$this->vaild_params('is_empty',$sn,'缺少订单号');
		$order = $this->loadModel('order')->getDetail($sn);
		$this->vaild_params('is_empty',$order,'该订单不存在');
		$this->vaild_params(array($this->model['order'],'checkPay'),array($order),'该订单已经付款',false);
		
		//$sn ='4466454';
		$cMbPay = new MbPay($this->config['pfxFile'], $this->config['pubFile'], $this->config['pfxpasswd'], $this->config['payReqUrl']);
		$data = array();
		$data['apiName'] = 'WEB_PAY_B2C';
		$data['apiVersion'] = $this->config['apiVersion'];
		$data['platformID'] = $this->config['platformID'];
		$data['merchNo'] = $this->config['merchNo'];
		$data['orderNo'] = $sn;
		$data['tradeDate'] = date('Ymd');
		$data['amt'] = $order['money'];
		// $data['merchUrl'] = 'http://'.$_SERVER['HTTP_HOST'].'/api.php';
		$data['merchUrl'] = 'http://eb.project.boruicx.com/api.php';
		$data['merchParam'] = '';
		//$data['merchParam'] = '';
		$data['tradeSummary'] = '支付测试';	
		if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['merchUrl']))
		{
			$data['merchUrl'] = iconv("GBK","UTF-8", $data['merchUrl']);
		}
		
		if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['merchParam']))
		{
		
			$data['merchParam'] = iconv("GBK","UTF-8", $data['merchParam']);
		}
		
		if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['tradeSummary']))
		{
			$data['tradeSummary'] = iconv("GBK","UTF-8", $data['tradeSummary']);
		}
		//echo $cMbPay->mobaopayOrder($data);
		//exit;
		$str_to_sign = $cMbPay->prepareSign($data);
		$sign = $cMbPay->sign($str_to_sign);
		$sign = urlencode($sign);
		$data['sigMessage'] = $sign;
		$data['tradeNo'] = $sn;
		$this->apiOut($data);
	}
	
	function notice()
	{
		eblog('mopay',$this->_request());
		/*
		$params = $this->get_params(array('apiName','notifyTime','tradeAmt','merchNo','merchParam','orderNo','tradeDate','accNo','accDate','orderStatus','signMsg'));
		Vendor('Mopay.MobaoPay');
		// 请求数据赋值
		$data = "";
		$data['apiName']=$params["apiName"];
		$data['notifyTime']=$params["notifyTime"];
		$data['tradeAmt']=$params["tradeAmt"];
		$data['merchNo']=$params["merchNo"];
		$data['merchParam']=$params["merchParam"];
		$data['orderNo']=$params["orderNo"];
		$data['tradeDate']=$params["tradeDate"];
		$data['accNo']=$params["accNo"];
		$data['accDate']=$params["accDate"];
		$data['orderStatus']=$params["orderStatus"];
		$data['signMsg']=$params["signMsg"];
		
		
		// 验证签名
		$cMbPay = new MbPay($this->config['pfxFile'], $this->config['pubFile'], $this->config['pfxpasswd']);
		$str_to_sign = $cMbPay->prepareSign($data);
		if ($cMbPay->verify($str_to_sign, $data['signMsg']) )
		{
			if ($data['orderStatus'] == "1")
				echo "pay success";
			else
				echo "pay error";
			return true;
		}
		else
		{
			print_r("verify error");
			return false;
		}
		*/
		
		
	}
}
