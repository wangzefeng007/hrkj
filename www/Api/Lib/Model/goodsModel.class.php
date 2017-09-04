<?php
class goodsModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	供应商添加商品
	*/
	function add_from_saler($usid,$data)
	{
		$this->startTrans();
		$gid = $this->add($data);
		if (!$gid)
		{
			$this->rollback();
			return false;
		}
		$data = array('usid'=>$usid,'gid'=>$gid);
		$rs = D('goodsSaler')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		$this->commit();
		return true;
	}
}