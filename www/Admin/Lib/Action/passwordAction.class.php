<?php
class passwordAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$this->display();
	}
	
	public function save()
	{
		$id = session('admin.id');
		$_params = $this->get_params(array('password_old','password','password2'));
		$this->vaild_params('is_empty',$_params['password_old'],'请填写旧密码！');
		$this->vaild_params('is_empty',$_params['password'],'请填写新密码！');
		$this->vaild_params('compare',array($_params['password'],$_params['password2'],'=='),'再次密码输入不一致');
		
		$where = array();
		$where['id'] = $id;
		$rs = $this->loadModel('admin')->getInfo('password',$where);
		$this->vaild_params('compare',array($rs['password'],md5($_params['password_old']),'=='),'旧密码不正确');
		
		$data['password'] = md5($_params['password']);
		$data['addtime'] = time();
		$rs = $this->loadModel('admin')->update($data,array('id'=>$id));
		$this->ajaxOut($rs,'index');		
	}
}
