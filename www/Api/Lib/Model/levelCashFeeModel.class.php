<?php
class levelCashFeeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	获取提现费率信息
	*/
	function getCashfee($lfid,$ptid,$ctid)
	{
		// $fields = "fee_rate,fee_static,min,max";
		$where = array('lfid'=>$lfid,'ptid'=>$ptid,'ctid'=>$ctid,'limit_status'=>0);
		return $this->getInfo('',$where);
	}
}