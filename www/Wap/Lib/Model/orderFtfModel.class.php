<?php
class orderFtfModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function createSn()
	{
		return 'OF'.substr(date("YmdHis"), -12).rand(100000,999999);
	}
	
	/*
	*	付款后流程
	*	@todo	此流程太过复杂，计算步骤太多，后续需更改为异步方式
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
				
		//按通道更新商户总额
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
		
		//无通道更新商户总额
		$user_saler_account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$order['usid']));
		$total = 0.00;
		if (!$user_saler_account_total)
		{
			
			$data = array('usid'=>$order['usid'],
									'ftf_total'=>$order['money'],
									'total_usable'=>$order['money'],
									'normal_usable'=>$order['money'],
									'normal_total'=>$order['money']);
			$rs = D('userSalerAccountTotal')->add($data);
		}
		else
		{
			$data = array('ftf_total'=>$user_saler_account_total['ftf_total']+$order['money'],
									'total_usable'=>$user_saler_account_total['total_usable']+$order['money'],
									'normal_usable'=>$user_saler_account_total['normal_usable']+$order['money'],
									'normal_total'=>$user_saler_account_total['normal_total']+$order['money']);
			$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$order['usid']));
		}
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
		$data['type'] = INCOME_FTF;
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
		
		//增加流水
		$data = array();
		$data['usid'] = $order['usid'];
		$data['usicid'] = $rs;
		$data['type'] = 1;
		$data['money'] = $order['money'];
		$data['total'] = $user_saler_account_total['total_usable']+
									$order['money'];
		$data['remark'] = '当面收款';
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		$this->commit();
		return true;
	}
}