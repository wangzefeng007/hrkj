<?php
class orderUpgradeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
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
		
		//分润
		$split = $this->split($order);
		$data = array();
		$data['usid'] = $split['usid'];
		$data['us_name'] = $split['us_name'];
		$data['order_sn'] = $order['sn'];
		$data['order_type'] = 3;
		$data['type'] = 3;
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'];
		$data['order_money'] = $order['money'];
		$data['money'] = $split['money'];
		$data['status'] = 1;
		$data['addtime'] = $time;
		$data['unfreezetime'] = $time;
		$rs = D('accountUserSalerIncome')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		$rs = D('userSalerAccountSplit')->change($split['usid'],$split['money']);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//平台收入
		$data = array();
		$data['type'] = 2;
		$data['order_sn'] = $order['sn'];
		$data['order_money'] = $order['money'];
		$data['money'] = $order['money'] - $split['money'];
		$data['addtime'] = $time;
		$rs = D('accountPlatformIncome')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}

		$this->commit();
		return true;
	}
	
		/*
	*	订单分润计算
	*	@param 			array			$order		订单信息
	*	@return				array			$split			
	*/
	private function split($order)
	{
		$split = array();
		
		$parent = D('userSaler')->getParent($order['usid'],true);
		if ($parent)
		{
			//第一个上级商户获取分润
			$split['usid'] = $parent[0]['usid'];
			$split['money'] = $order['money']*$order['lf_rate'];
		}
		return $split;		
	}
}