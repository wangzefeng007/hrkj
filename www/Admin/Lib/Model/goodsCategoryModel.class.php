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
	*	获取分类树
	*/
	function getTree($pid = 0)
	{
		static $tree = array();
		$category = $this->getList("*",array('pid'=>$pid),'sort desc');
		if ($category['list'])
		{
			foreach($category['list'] as $value)
			{
				$tree[$value['id']] = $value;
				$this->getTree($value['id']);
			}
		}
		return $tree;
	}
	
	/*
	*	设置分类树缓存
	*/
	function setTreeCache()
	{
		$tree = $this->getTree();
		// dump($tree);
		S('goodsCategoryTree',$tree);
	}
	
	/*
	*	通过缓存获取分类树
	*/
	function getTreeWithCache()
	{
		if (!S('goodsCategoryTree'))
		{
			$this->setTreeCache();	
		}
		return S('goodsCategoryTree');		
	}
	
	/*
	*	更新分类树缓存
	*/
	function updateTreeCache($id,$data,$action = 'update')
	{
		$cache = $this->getTreeWithCache();
		if ($action == 'update')
		{
			$cache[$id] = $data;
		}
		elseif ($action=='remove')
		{
			unset($cache[$id]);
		}
		S('goodsCategoryTree',$cache);
	}
}