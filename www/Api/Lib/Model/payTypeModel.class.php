<?php
class payTypeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	通过api获取支付id
	*	@param 		string		$api		api名称，格式yeepayAction::air
	*/	
	function getInfoByApi($api)
	{
		$api = str_replace("Action","",$api);
		return $this->getInfo("*",array('api'=>$api));
		
	}
}