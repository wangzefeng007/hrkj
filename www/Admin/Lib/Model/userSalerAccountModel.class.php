<?php
class userSalerAccountModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getList($usid)
	{
		$field = array('user_saler_account'=>'ptid,pcid,money,cash_money',
								'pay_type'=>'name as pt_name',
								'pay_channel'=>'name as pc_name');
		$join = array();
		$join[] = array('pay_type','user_saler_account.ptid','pay_type.id');	
		$join[] = array('pay_channel','user_saler_account.pcid','pay_channel.id');
		$where = array('usid'=>$usid);
		return $this->getJoinList($field,$join,$where);
	}
	
	/*
	*	汇总金额变更
	*/
	function change($usid,$ptid,$money = 0,$cash_money = 0)
	{
		$data = array();
		$data['usid'] = $usid;
		$data['ptid'] = $ptid;
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
