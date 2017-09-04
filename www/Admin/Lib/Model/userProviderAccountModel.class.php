<?php
class userProviderAccountModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	汇总金额变更
	*/
	function change($upid,$money = 0,$cash_money = 0)
	{
		$data = array();
		$data['upid'] = $upid;
		$user_account = $this->getInfo("*",array('ptid'=>$ptid,'usid'=>$usid));		
		$data['money'] += $money;
		$data['cash_money'] += $cash_money;
		$rs = false;
		if (!$user_account)
		{			
			$rs = $this->add($data);
		}
		else
		{
			$rs = $this->update($data,array('id'=>$user_account['id']));
		}
		return $rs;
	}
}