<?php
class userSalerAccountModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getAccount($usid)
	{
		$fields = array('pay_type'=>'id as ptid,name as pt_name',
									'pay_channel'=>'name as pc_name');
		$join = array('pay_channel','pcid','id');							
		$rs = D('payType')->getJoinList($fields,$join,array('status'=>1));
		$pay_type = reset_array_key($rs['list'],'ptid');

		$rs = $this->getList('ptid,money,cash_money',array('usid'=>$usid));
		$account = reset_array_key($rs['list'],'ptid');
		foreach($pay_type as $key=>$value)
		{
			if (isset($account[$key]))
			{
				$pay_type[$key]['money'] = $account[$key]['money'];
				$pay_type[$key]['cash_money'] = $account[$key]['cash_money'];
			}
			else
			{
				$pay_type[$key]['money'] = 0.00;
				$pay_type[$key]['cash_money'] = 0.00;
			}			
		}		
		return array_values($pay_type);
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
