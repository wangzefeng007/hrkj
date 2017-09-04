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
        
        $account = D('userSalerAccountTotal')->getInfo("*",array('usid'=>$this->usid,'split_usable'=>array('egt',$money)),'id desc',true);
        if (!$account)
        {
            $this->rollback();
			return false;
        }
		
		$sn = $this->createSn('CS');
		//支出明细插入
		$data = array();
		$data['usid'] = $this->usid;
		$data['us_name'] = $us_name;
		$data['ptid'] = -1;
		$data['pt_name'] = '分润提现';
		$data['ctid'] = $ct_info['id'];
		$data['ct_name'] = $ct_info['name'];
		$data['money'] = $money;
		$data['type'] = CASH_SPLIT;
		$data['fee_static'] = $ct_info['fee_static'];
		$data['total'] = $account['total_usable']-$money;
		$data['real_money'] = $money - $ct_info['fee_static'];
		$data['addtime'] = $time;
		$data['sn'] = $sn;
		$cash = $data;
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
		$data['order_sn'] = $sn;
		$data['type'] = 2;
		$data['money'] = 0-$money;
		$data['real_money'] = $money - $ct_info['fee_static'];
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
			return false;
		}

		$this->commit();
		return $cash;		
	}
	/*
	*	商户提现(无分润)
	*	@param			float		$money		提现金额
	*	@param			int			$type			提现类型  2 分润提现 3 佣金提现
	*/
	function businessCash($usid,$us_name,$ct_info,$money,$account)
	{
		$this->startTrans();
		$time = time();
        
        $account = D('userSalerAccountTotal')->getInfo("*",array('usid'=>$this->usid,'business_usable'=>array('egt',$money)),'id desc',true);
        if (!$account)
        {
            $this->rollback();
			return false;
        }
		
		$sn = $this->createSn('CB');
		//支出明细插入
		$data = array();
		$data['usid'] = $this->usid;
		$data['us_name'] = $us_name;
		$data['ptid'] = -2;
		$data['pt_name'] = '商户提现';
		$data['ctid'] = $ct_info['id'];
		$data['ct_name'] = $ct_info['name'];
		$data['money'] = $money;
		$data['type'] = CASH_SPLIT;
		$data['fee_static'] = $ct_info['fee_static'];
		$data['total'] = $account['total_usable']-$money;
		$data['real_money'] = $money - $ct_info['fee_static'];
		$data['addtime'] = $time;
		$data['sn'] = $sn;
		$cash = $data;
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
		$data['order_sn'] = $sn;
		$data['type'] = 2;
		$data['money'] = 0-$money;
		$data['real_money'] = $money - $ct_info['fee_static'];
		$data['total'] = $account['total_usable']-$money;
		$data['remark'] = '商户提现';
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//更新商户可用余额
		$data = array('normal_usable'=>$account['normal_usable']-$money,'total_usable'=>$account['total_usable']-$money);
		$rs = $this->update($data,array('usid'=>$usid));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}

		$this->commit();
		return $cash;		
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
		$account = D('userSalerAccountTotal')->getInfo("*",array('usid'=>$this->usid,'commission_usable'=>array('egt',$money)),'id desc',true);
        if (!$account)
        {
            $this->rollback();
			return false;
        }
        
        $sn = $this->createSn('CC');
		//支出明细插入
		$data = array();
		$data['usid'] = $this->usid;
		$data['us_name'] = $us_name;
		$data['ctid'] = $ct_info['id'];

		$data['ct_name'] = $ct_info['name'];
		$data['money'] = $money;
		$data['type'] = CASH_COMMISSION;
		$data['fee_static'] = $ct_info['fee_static'];
		$data['real_money'] = $money - $ct_info['fee_static'];
		$data['addtime'] = $time;

		$data['sn'] = $sn;
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
		$data['order_sn'] = $sn;
		$data['type'] = 2;
		$data['money'] = 0-$money;
		$data['real_money'] = $money - $ct_info['fee_static'];
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
	
	
	/*
	*	提现单号
	*/
	private function createSn($type)
	{
		return $type.substr(date("YmdHis"), -12).rand(100000,999999);
	}
}