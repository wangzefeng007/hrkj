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
		
		//上级商户收入中插入收入明细
		$split = $this->split($order);

		if ($split)
		{
			$data = array();
			$data['usid'] = $split['usid'];
			$data['from_usid'] = $order['usid'];
			$data['us_name'] = $split['us_name'];
			$data['order_sn'] = $order['sn'];
			$data['order_type'] = 3;
			$data['type'] = INCOME_UPGRADE_SPLIT;
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
			
			$account = D('userSalerAccountTotal')->getInfo("*",array('usid'=>$split['usid']));
			//增加流水
			$data = array();
			$data['usid'] = $split['usid'];
			$data['usicid'] = $rs;
			$data['type'] = 1;
			$data['money'] = $split['money'];
			$data['total'] = $account['total_usable']+
										$split['money'];
			$data['remark'] = '升级分润';
			$data['addtime'] = $time;
			$rs = D('accountUserSalerWater')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			
			//更新上级商户分润汇总			
			$data = array();
			if ($account)
			{
				$data['split_total'] = $account['split_total'] + $split['money'];
				$data['split_usable'] = $account['split_usable'] + $split['money'];
				$data['upgrade_split_total'] = $account['upgrade_split_total'] + $split['money'];
				$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$split['usid']));
			}
			else
			{
				$data['usid'] = $split['usid'];
				$data['split_total'] = $split['money'];
				$data['split_usable'] = $split['money'];
				$data['upgrade_split_total'] = $split['money'];
				$data['total_usable'] = $split['money'];
				$rs = D('userSalerAccountTotal')->add($data);
			}
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
		}
		
		//其他上级商户同时升级
		$parent = D('userSaler')->getParent($order['usid']);
		// dump($parent);
		if ($parent)
		{
			foreach($parent as $value)
			{
				if ($value['lfid'] == $order['lfid_old'])
				{
					$data = $order;
					unset($data['id']);
					$data['status'] = 1;
					$data['paytime'] = $time;
					$data['usid'] = $value['id'];
					$data['from_usid'] = $order['usid'];
					$data['sn'] = 'OUA'.substr($order['sn'],3);
					$rs = $this->add($data);
					if (!$rs)
					{
						$this->rollback();
						return false;
					}
					$rs = D('userSaler')->update(array('lfid'=>$order['lfid_new']),array('id'=>$value['id']));
					if (!$rs)
					{
						$this->rollback();
						return false;
					}
				}
			}
		}
		
		//用户自身等级变更
		$rs = D('userSaler')->update(array('lfid'=>$order['lfid_new']),array('id'=>$order['usid']));
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
		$data['ptid'] = $payinfo['ptid'];
		$data['money'] = ($order['money'] - $split['money'])+$order['deposit'];
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
			$split['usid'] = $parent[0]['id'];
			$split['us_name'] = $parent[0]['name'];
			$split['money'] = ($order['money']-$order['deposit'])*$order['lf_rate'];
		}
		return $split;		
	}
}