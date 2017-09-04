<?php
class goodsCategoryAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = $_params['name'];
		$data = $this->loadModel('goodsCategory')->getList('*',$where,'id desc',true);
		$this->assign('data',$data);
		$this->assign('_params',$_params);		
		$this->display();
	}
	
	
	public function add()
	{
		$pid = intval(I('pid'));
		$category_tree = $this->loadModel('goodsCategory')->getTreeWithCache();
		$this->assign('pid',$pid);
		$this->assign('category_tree',$category_tree);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$category = $this->loadModel('goodsCategory')->getInfoByid($id);
		$category_tree = $this->loadModel('goodsCategory')->getTreeWithCache();
		$this->assign('pid',$category['pid']);
		$this->assign('category',$category);
		$this->assign('category_tree',$category_tree);
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','pid','sort'));
		$_params['sort'] = $_params['sort']?$_params['sort']:0;
		$this->vaild_params('is_empty',$_params['name'],'请填写等级名称！');
		$this->vaild_params('is_numeric',$_params['sort'],'排序值必须为数字');
		$data = $_params;
		if ($_params['pid'])
		{
			$parent = $this->loadModel('goodsCategory')->getInfoByid($_params['pid']);
			$this->vaild_params('is_empty',$parent,'您选择的上级分类不存在或已被删除！');
			$data['level'] = $parent['level']+1;
		}
		$data['addtime'] = time();
		if ($id>0)
		{
			$rs = $this->loadModel('goodsCategory')->update($data,array('id'=>$id));
			$data['id'] = $id;
			$this->loadModel('goodsCategory')->updateTreeCache($id,$data);
		}
		else
		{	
			$rs = $this->loadModel('goodsCategory')->add($data);
			$this->loadModel('goodsCategory')->updateTreeCache($rs,$data);
		}
		
		$this->ajaxOut($rs,'goodsCategory/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('goodsCategory')->del(array('id'=>array('IN',$ids)));
		if ($rs)
		{
			foreach($ids as $id)
			{
				$this->model['goodsCategory']->updateTreeCache($id,'','remove');
			}
		}
		$this->ajaxOut($rs,'goodsCategory/index');
	}
}
