<?php
class settingExpressAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('settingExpress')->getList('*',$where,'id asc',true);
		$this->assign('data',$data);
		$this->assign('_params',$_params);		
		$this->display();
	}
	
	
	public function add()
	{
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$rs = $this->loadModel('settingExpress')->getInfoByid($id);
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','type','status'));
		$this->vaild_params('is_empty',$_params['name'],'快递名称 不能为空!');
		
		$data = array();
		$data = $_params;
		$data['addtime'] = time();
		$rs = !$id?$this->loadModel('settingExpress')->add($data):$this->loadModel('settingExpress')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('settingExpress')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
