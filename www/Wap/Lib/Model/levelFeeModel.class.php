<?php
class levelFeeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	检查级别id是否存在
	*/
	function checkId($id)
	{
		return $this->getInfoByid($id,'id')?true:false;
	}
	
	/*
	*	获取最低级别信息
	*/
	function getLowest($field = '*')
	{
		return $this->field($field)->where(array('status'=>'1'))->order('fee_upgrade')->find();
	}
	
	/*
	*	获取某个等级的详细费率信息
	*/
	public function getRate($lfid)
	{
		$field = array('level_cash_fee'=>'ptid,ctid,fee_rate,fee_static,max,min',
								'pay_type'=>'name as pt_name',
								'cash_type'=>'name as ct_name');
		$join = array();
		$join[] = array('pay_type','ptid','id');	
		$join[] = array('cash_type','ctid','id');
		$where = array('lfid'=>$lfid);
		return D('levelCashFee')->getJoinList($field,$join,$where);
	}
	
	
	public function getLevels()
	{
		$levels = array();
		$rs = $this->getList("id,level");		
		foreach($rs['list'] as $value)
		{
			$levels[$value['id']] = $value['level'];
		}
		return $levels;
	}

}
