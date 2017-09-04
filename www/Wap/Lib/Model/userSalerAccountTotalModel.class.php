<?php
class userSalerAccountTotalModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	分润与佣金提现
	*	@param			float		$money		提现金额
	*	@param			int			$type			提现类型  2 分润提现 3 佣金提现
	*/
	function splitcash($usid,$us_name,$ct_info,$money,$account)
	{
		$this->startTrans();
		$time = time();
		
		//支出明细插入
		$data = array();
		$data['usid'] = $this->usid;
		$data['us_name'] = $us_name;
		$data['ct_id'] = $ct_info['id'];
		$data['ct_name'] = $ct_info['name'];
		$data['money'] = $money;
		$data['type'] = CASH_SPLIT;
		$data['fee_static'] = $ct_info['fee'];
		$data['addtime'] = $time;
		$rs = D('accountUserSalerCash')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//插入流水
		$data = array();
		$data['usid'] = $usid;
		$data['usicid'] = $rs;
		$data['type'] = 2;
		$data['money'] = 0-$money;
		$data['total'] = $account['total_usable']-$money;
		$data['remark'] = '分润提现';
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//更新分润可用余额
		$data = array('split_usable'=>$account['split_usable']-$money,'total_usable'=>$account['total_usable']-$money);
		$rs = $this->update($data,array('usid'=>$usid));
		if (!$rs)
		{
			$this->rollback();
			return true;
		}

		$this->commit();
		return true;		
	}
	
	/*
	*	佣金提现
	*	@param			float		$money		提现金额
	*	@param			int			$type			提现类型  2 分润提现 3 佣金提现
	*/
	function commissioncash($usid,$us_name,$ct_info,$money,$account)
	{
		$this->startTrans();
		$time = time();
		
		//支出明细插入
		$data = array();
		$data['usid'] = $this->usid;
		$data['us_name'] = $us_name;
		$data['ct_id'] = $ct_info['id'];
		$data['ct_name'] = $ct_info['name'];
		$data['money'] = $money;
		$data['type'] = CASH_COMMISSION;
		$data['fee_static'] = $ct_info['fee'];
		$data['time'] = $time;
		$rs = D('accountUserSalerCash')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//插入流水
		$data = array();
		$data['usid'] = $usid;
		$data['usicid'] = $rs;
		$data['type'] = 2;
		$data['money'] = 0-$money;
		$data['total'] = $account['ftf_usable']+
									$account['shop_usable']+
									$account['split_usable']+
									$account['commission_usable']-
									$money;
		$data['remark'] = '分润提现';
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//更新分润可用余额
		$data = array('commission_usable'=>$account['commission_usable']-$money);
		$rs = $this->update($data,array('usid'=>$usid));
		if (!$rs)
		{
			$this->rollback();
			return true;
		}

		$this->commit();
		return true;		
	}
	
	

}