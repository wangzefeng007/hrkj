<?php
class levelSalerFeeRelationModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	建立分销等级与费率等级绑定关系
	*/
	function level_saler_fee_bind($lsid,$lfids = array())
	{
		$id = array();
		foreach($lfids as $value)
		{
			$data = array('lsid'=>$lsid,'lfid'=>$value);
			$rs = $this->add($data);
			if (!$rs) return false;
			$id[] = $rs;
		}	
		
		//删除之前已设置的记录
		$rs = $this->del(array('lsid'=>$lsid,'id'=>array(' not in ',$id)));
		return ($rs === false)?false:true;
	}

	/*
	* 获取代销等级绑定的费用等级
	*/
	function getLevelfee($lsid)
	{
		$fields = array('level_fee'=>'id,name');
		$join = array('level_fee','lfid','id');
		$where = array('lsid'=>$lsid);
		$level_fee = $this->getJoinList($fields,$join,$where);
		return $level_fee['list'];
	}	
}