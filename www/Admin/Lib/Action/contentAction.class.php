<?php
/**
 * @desc 内容设置
 * Class contentAction
 */
class contentAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$_params = $this->get_params(array('name'));
		$where = array(
			'status' => 1,
		);
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('settingContent')->getList('*',$where,'id asc',true);
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
		$rs = $this->loadModel('settingContent')->getInfoByid($id);
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('skey','stitle','svalue'));
		//var_dump($_params['svalue']);
		//exit;
		$this->vaild_params('is_empty',$_params['skey'],'编号 不能为空!');
		$this->vaild_params('is_empty',$_params['stitle'],'标题 不能为空!');
		
		if (!$id)
		{
			$rs = $this->loadModel('settingContent')->getInfo('*',array('skey'=>$_params['skey']));
			$this->vaild_params('is_empty',$rs,'编号已经存在!',false);
		}
		$data = array();
		$data = $_params;
		$rs = !$id?$this->loadModel('settingContent')->add($data):$this->loadModel('settingContent')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('settingContent')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
