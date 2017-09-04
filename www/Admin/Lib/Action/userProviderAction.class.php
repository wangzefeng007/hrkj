<?php
class userProviderAction extends baseAction
{

	
	public function index()
	{
		$_params = $this->get_params(array('starttime','endtime','lfid','keytype','keyword','status'));
		$where = array();
		if ($_params['status']!='') $where['status'] = $_params['status'];
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['lfid']) $where['lfid'] = $_params['lfid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		

		$data = $this->loadModel('userProvider')->getList("*",$where," id DESC ",true);
		$this->assign('data',$data);
		$this->assign('_params',$_params);
		
		$this->display();
	}
	
	/*
	*	用户审核
	*/
	public function audit()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$audit_value = intval(I('audit_value'));
		$rs = $this->loadModel('userProvider')->update(array('status'=>$audit_value),array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'userProvider/lists');
	}
	
	public function add()
	{
		
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$user = $this->loadModel('userProvider')->getInfoByid($id);
		$this->assign('user',$user);
		$this->display('view');
	}
	
	/*
	*	保存用户信息
	*	@todo 1、图片上传部分 
	*/
	public function save()
	{		
		$_params = $this->get_params(array('id','username','password','shop_name','platform_rate','commission_rate','status'));
		$this->vaild_params('is_empty',$_params['username'],'请填写登录帐号');
		
		$this->vaild_params('is_empty',$_params['shop_name'],'请填写店铺名称');
		if (!$_params['id']) $this->vaild_params(array($this->loadModel('userProvider'),'checkRegister'),$_params['username'],'该用户名已注册',false);
		$data = $_params;
		
		$id = intval($_params['id']);
		if ($id>0)
		{
			if (!$data['password']) 
			{
				unset($data['password']);
			}
			else
			{
				$data['password'] = md5($data['password']);
			}
			$rs = $this->loadModel('userProvider')->update($data,array('id'=>$id));
		}
		else
		{
			unset($data['id']);
			$this->vaild_params('is_empty',$_params['password'],'请填写密码');
			$data['password'] = md5($data['password']);
			$data['addtime'] = time();
			$rs = $this->loadModel('userProvider')->add($data);
			$id = $rs;
		}
		$this->ajaxOut($rs,'userProvider/index');
		
	}
	
	public function delete()
	{
		$id = is_array(I('id'))?I('id'):array(intval(I('id')));
		$this->vaild_params('is_empty',$id,'请选择要删除的项！');
		$rs = $this->loadModel('userProvider')->del(array('id'=>array('IN',$id)));
		$this->ajaxOut($rs,'userProvider/index');
	}
}
