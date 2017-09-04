<?php
class moduleAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		// $data = $this->loadModel('module')->getList("*",array(),'id desc',true);
		$module = $this->loadModel('module')->getTree();
		// dump($module);
		$this->assign('module',$module);	
		$this->display();
	}
	
	
	public function add()
	{
		$module = $this->loadModel('module')->getTree(0,array('status'=>1));
		$this->assign('module',$module);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$info = $this->loadModel('module')->getInfoByid($id);
		$module = $this->loadModel('module')->getTree(0,array('status'=>1));
		$this->assign('module',$module);
		$this->assign('info',$info);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$pid_level = I('pid_level');
		$_params = $this->get_params(array('name','module','action','params','is_menu'));
		$this->vaild_params('is_empty',$_params['name'],'请填写名称！');
		// $this->vaild_params('is_empty',$_params['module'],'请填写模块名！');
		// $this->vaild_params('is_empty',$_params['action'],'请填写操作！');
		
		$temp = explode(',',$pid_level);
		$data = $_params;
		$data['pid'] = $temp[0];
		$data['level'] = $temp[1];
		$data['addtime'] = time();
		$rs = !$id?$this->loadModel('module')->add($data):$this->loadModel('module')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'module/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('module')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'module/index');
	}
}
