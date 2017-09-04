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
	*	比较两个用户的等级
	*/
	function compareLevel($user,$other)
	{
		if ($user['lfid'] == $other['lfid'])
		{
			return true;
		}
		$rs = D('levelFee')->getList('id,fee_upgrade,level',array('id',array($user['lfid'],$other['lfid'])));
		$levels = reset_array_key($rs['list'],'id');

		if ($levels[$user['lfid']]['level']>=$levels[$other['lfid']]['level']) 
		{
			return false;
		}
		else
		{
			return true;
		}
		
	}

}
