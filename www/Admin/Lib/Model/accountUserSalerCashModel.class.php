<?php
class accountUserSalerCashModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	//提现
	function cash($data)
	{
		$this->startTrans();
		$rs = D('accountUserSalerCash')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		$where = array();
		$where['usid'] = $data['usid'];
		$where['ptid'] = $data['ptid'];
		$rs = D('userSalerAccount')->where($where)->setDec('money',$data['money']);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		$rs = D('userSalerAccount')->where($where)->setInc('cash_money',$data['money']);
		if (!$rs)
		{
			$this->rollback();
			return false;	
		}
		$this->commit();
		return true;
	}
}