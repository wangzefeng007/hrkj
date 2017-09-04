<?php
class msgOpinionAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('msgOpinion')->getList('*',$where,'id desc',true);
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
		$rs = $this->loadModel('msgOpinion')->getInfoByid($id);
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('title','status','content'));
		$this->vaild_params('is_empty',$_params['content'],'正文 不能为空!');
		
		$data = array();
		$data = $_params;
		if (!$id) $data['addtime'] = time();
		$rs = !$id?$this->loadModel('msgOpinion')->add($data):$this->loadModel('msgOpinion')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('msgOpinion')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
