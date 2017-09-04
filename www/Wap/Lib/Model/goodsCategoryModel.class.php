<?php
class goodsCategoryModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	function checkGcid($gcid)
	{
		return $this->getInfo('id',array('id'=>$gcid,'status'=>1));
	}
	
		/*
	*	获取商品所有分类信息
	*/
	public function get_categories($pid = 0)
	{
		static $categories = array();
		$category = $this->getList('*',array('pid'=>$pid,'status'=>1));
		$category = $category['list'];
		if ($category)
		{
			foreach($category as $value)
			{
				$categories[] = $value;
				$this->get_categories($value['id']);
			}
		}
		return $categories;
	}
}