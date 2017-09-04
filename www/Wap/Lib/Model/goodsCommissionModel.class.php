<?php
class goodsCommissionModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	获取某个商品的佣金
	*	@param		int 		$lsid		代销等级id
	*	@param		int		$gpid		代销商品id	
	*/
	function getRate($lsid,$gpid)
	{
		$where = array('lsid'=>$lsid,'gpid'=>$gpid);
		$rs = $this->getInfo('rate',$where);
		return $rs['rate'];
	}
	
}