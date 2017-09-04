<?php
class moduleModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	function getTree($pid = 0,$params = array())
	{
		static $module = array();
		$where = $params;
		$where['pid'] = $pid;
		$rs = $this->getList("*",$where,'sort desc');
		if ($rs['list'])
		{
			foreach($rs['list'] as $value)
			{
				$module[] = $value;
				$this->getTree($value['id'],$params);
			}
		}
		return $module;
	}
}