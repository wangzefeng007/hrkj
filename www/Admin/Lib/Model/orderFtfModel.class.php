<?php
class orderFtfModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function createSn($usid)
	{
        return 'OF'.substr(date("YmdHis"), -12).str_pad(substr($usid, -3),3,0,STR_PAD_LEFT).rand(100,999);
	}
	
	/*
	*	付款后流程
	*/
	public function pay($order,$payinfo)
	{
		$time = time();
		$this->startTrans();
		$data = array('status'=>1,'paytime'=>$time);
		$rs = D('orderPaylog')->update($data,array('id'=>$payinfo['id']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		//更新订单信息
		$data = array();
		$data['status'] = 1;
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'];
		$data['paytime'] = $time;
		$rs = $this->update($data,array('sn'=>$order['sn']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//增加收入记录
		$data = array();
		$data['usid'] = $order['usid'];
		$data['us_name'] = $order['us_name'];
		$data['order_sn'] = $order['sn'];
		$data['order_type'] = 2;
		$data['type'] = 2;
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'];
		$data['order_money'] = $order['money'];
		$data['money'] = $order['money'];
		$data['status'] = 1;
		$data['addtime'] = $time;
		$data['unfreezetime'] = $time;
		$rs = D('accountUserSalerIncome')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//更新商户总额
		$user_account = D('userSalerAccount')->getInfo("*",array('ptid'=>$payinfo['ptid'],'usid'=>$order['usid']));
		$data = array();
		$data['ptid'] = $payinfo['ptid'];
		$data['money'] = $user_account['money']+$order['money'];		
		if (!$user_account)
		{
			$data['usid'] = $order['usid'];
			$rs = D('userSalerAccount')->add($data);
		}
		else
		{
			$rs = D('userSalerAccount')->update($data,array('id'=>$user_account['id']));
		}
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		$this->commit();
		return true;
	}
}