<?php
class UserProviderModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	检查手机号是否注册
	*/
	function checkRegister($username)
	{
		$rs = $this->getInfo('id',array('username'=>$username))?true:false;
		return $rs;
	}
}
