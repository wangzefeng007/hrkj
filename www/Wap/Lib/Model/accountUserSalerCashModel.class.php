<?php
class accountUserSalerCashModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	*	提现
	*	@todo 1、提现分润计算
	*/
	function cash($cash,$lfid)
	{
		$time = time();
		$this->startTrans();
		$rs = D('accountUserSalerCash')->add($cash);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		$_usicid = $rs;		
		//更新无通道总额
		$account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$cash['usid']));
		$total = 0.00;
		if (!$account_total)
		{
			$this->rollback();
			return false;
		}
		else
		{
			$data = array('total_usable'=>$account_total['total_usable']-$cash['money'],
									'normal_usable'=>$account_total['normal_usable']-$cash['money']);
			$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$cash['usid']));
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
		}
				
		//增加流水
		$data = array();
		$data['usid'] = $cash['usid'];
		$data['usicid'] = $_usicid;
		$data['type'] = 2;
		$data['money'] = (0-$cash['money']);
		$data['total'] = $account_total['total_usable']-$cash['money'];
		$data['remark'] = '普通提现';
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}		
		//更新分通道总额
		$account = D('userSalerAccount')->getInfo('money,cash_money',array('usid'=>$cash['usid'],'ptid'=>$cash['ptid']));
		$data = array('money'=>$account['money']-$cash['money'],'cash_money'=>$account['money']+$cash['money']);
		$rs = D('userSalerAccount')->update($data,array('usid'=>$cash['usid'],'ptid'=>$cash['ptid']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//分润计算
		$split = $this->split($cash,$lfid);
		if ($split['count']>0)
		{
			foreach($split['list'] as $value)
			{
				//收入明细插入
				$data = array(); 
				$data['usid'] = $value['usid'];
				$data['us_name'] = $value['us_name'];
				$data['money'] = $value['money'];
				$data['from_usid'] = $cash['usid'];
				$data['order_money'] = $cash['money'];
				$data['type'] = INCOME_SPLIT;
				$data['status'] = INCOME_STATUS_NORMAL;
				$data['ptid'] = $cash['ptid'];
				$data['pt_name'] = $cash['pt_name'];
				$data['addtime'] = $time;				
				$rs = D('accountUserSalerIncome')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}				
				$usicid = $rs;		
				//更新无通道总额
				$account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$value['usid']));
				$total = 0.00;
				if (!$account_total)
				{
					$data = array('total_usable'=>$value['money'],
											'split_usable'=>$value['money'],
											'split_total'=>$value['money'],
											'normal_split_total'=>$value['money'],
											'usid'=>$value['usid']
											);
					$rs = D('userSalerAccountTotal')->add($data);
				}
				else
				{
					$data = array('total_usable'=>$account_total['total_usable']+$value['money'],
											'split_usable'=>$account_total['split_usable']+$value['money'],
											'split_total'=>$account_total['split_total']+$value['money'],
											'normal_split_total'=>$account_total['normal_split_total']+$value['money'],
											);
					$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$value['usid']));
					
				}
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				//增加流水
				$data = array();
				$data['usid'] = $value['usid'];
				$data['usicid'] = $usicid;
				$data['type'] = 1;
				$data['money'] = $value['money'];
				$data['total'] = $account_total['total_usable']+$value['money'];
				$data['remark'] = '普通分润收入';
				$data['addtime'] = $time;
				$rs = D('accountUserSalerWater')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}						
			}
		}
		
		//平台收入

		$data = array();
		$data['type'] = PLATFORM_INCOME_NORMAL;
		// $data['order_sn'] = $order['sn'];
		// $data['order_money'] = $order['money'];
		$data['uscid'] = $_usicid;
		$data['ptid'] = $cash['ptid'];
		$data['ctid'] = $cash['ctid'];
		$data['money'] = $cash['money']*$cash['fee_rate']-$split['count']+$cash['fee_static'];
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
	*	@param 			array			$cash			订单信息
	*	@param				int				$lfid			提现人等级id
	*	@return				array			$split			分润费用	array('count'=>分润总额,'list'=>分润详情)
	*/
	private function split($cash,$lfid)
	{
		$split = array('count'=>0);		
		$parent = D('userSaler')->getParent($cash['usid'],true);
		if ($parent)
		{
			$user_fee_rate = array('fee_rate'=>$cash['fee_rate']);
			foreach($parent as $value)
			{
				$fee_rate = D('levelCashFee')->getCashfee($value['lfid'],$cash['ptid'],$cash['ctid']);
				$diff = $fee_rate['fee_rate'] - $user_fee_rate['fee_rate'];
				if ($diff>=0) continue;
				$money = abs($diff)*$cash['money'];
				if (!isset($split['list'][$value['id']]))
				{
					$split['list'][$value['id']] = array('usid'=>$value['id'],'us_name'=>$value['name'],'money'=>$money);
				}
				else
				{
					$split['list'][$value['id']]['money'] += $money;
				}
				$split['count'] += $money;
				$user_fee_rate = $fee_rate;
			}

		}
		return $split;
	
		
	}
}