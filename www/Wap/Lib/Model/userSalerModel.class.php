<?php
class userSalerModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	检查手机号是否注册
	*/
	function checkRegister($mobile)
	{
		$rs = $this->getInfo('id',array('mobile'=>$mobile))?true:false;
		return $rs;
	}
	
	/*
	*	用户费率级别信息
	*/
	function getLevelfee($usid)
	{
		$field = array('level_fee'=>'*');
		$join = array('level_fee','lfid','id');			
		$where = array('id'=>$usid);
		return $this->getJoinInfo($field,$join,$where);				
	}
	
	/*
	*	获取用户可升级的费率级别信息
	*/
	function getLevelfeeUp($usid)
	{
		$user_level = $this->getLevelfee($usid);
		$where = array();
		$where['fee_upgrade'] = array('GT',$user_level['fee_upgrade']);
		$where['status'] = 1;
		$rs = D('levelFee')->getList("*",$where," fee_upgrade ASC");
		return $rs['list'];
	}
	
	/*
	*	获取上级商户信息
	*	@param			int			$usid				商户id
	*	@param			bool		$skip_ecdb	是否跳过同级用户
	*/
	function getParent($usid,$skip_ecdb = false)
	{
		
		$parent = array();
		$levels = D('levelFee')->getLevels();
		$user = $this->getInfo('pid,lfid',array('id'=>$usid));
		$pids = array();
		while($user['pid'])
		{		
			$rs = $this->getInfo("id,pid,name,lfid",array('id'=>$user['pid']));
			if (!$rs) break;
			if ($skip_ecdb)
			{
				if ($user['lfid'] == $rs['lfid'])
				{
					$user = array('pid'=>$rs['pid'],'lfid'=>$rs['lfid']);
					continue;
				}
			}
			$rs['level'] = intval($levels[$rs['lfid']]);	
			$parent[] = $rs;
			$user = array('pid'=>$rs['pid'],'lfid'=>$rs['lfid']);			
		}		
		return $parent;		
	}
	
	/*
	*	获取下级商户等级信息
	*/
	function getChildLevels($usid)
	{
		static $child = array();
		static $levels = array();
		if (empty($levels))
		{
			$rs = D('levelFee')->getList('id,name');
			foreach($rs['list'] as $value)
			{
				$levels[$value['id']] = $value;
			}
		}
		
		$rs = D('userSaler')->getList('id,lfid,status',array('pid'=>$usid));
		if ($rs['list'])
		{
			foreach($rs['list'] as $value)
			{
				$value['level'] = $levels[$value['lfid']];
				$child[] = $value;
				$this->getChildLevels($value['id']);
			}
		}				
		return $child;
	}
	
	/*
	*	获取下级商户账户信息
	*/
	function getChildAccount($usid,$lfid)
	{
		$rs = $this->getlist('id as usid,name,mobile',array('pid'=>$usid,'lfid'=>$lfid),'id desc',true);
		$child =$ids = array();
		foreach($rs['list'] as $value)
		{
			$ids[] = $value['usid'];
			$child[] = $value;
		}
		$account = D('userSalerAccountTotal')->getList('usid,total_usable as account',array('usid'=>array('IN',$ids)));
		$account = reset_array_key($account,'usid');
		$split = D('accountUserSalerIncome')->field("from_usid,SUM(money) AS money")->where(array('usid'=>$usid,'from_usid'=>array('IN',$ids),'type'=>3))->group('from_usid')->select();
		$split = reset_array_key($split,'from_usid');
		foreach($child as $key=>$value)
		{
			$child[$key]['account'] = isset($account[$value['id']])?$account[$value['id']]['account']:0.00;
			$child[$key]['split'] = isset($split[$value['id']])?$split[$value['id']]['money']:0.00;
		}
		$rs['list'] = $child;
		return $rs;
	}
	

	

}
