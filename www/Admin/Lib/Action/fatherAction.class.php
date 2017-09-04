<?php
class ##Controller##Action extends baseAction
{

	public function lists()
	{
		$_params = $this->get_params(array(##params##));
		$where = array();
		if ($_params['status']!='') $where['status'] = $_params['status'];
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['lfid']) $where['lfid'] = $_params['lfid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		
		$fields = array();
		$fields['user_saler'] = 'id,name,mobile,lfid,addtime,lastlogintime,status';
		$fields['level_fee'] = 'name AS lf_name';
		$join = array('level_fee','lfid','id');
		$data = $this->loadModel('userSaler')->getJoinList($fields,$join,$where," id DESC ",true);
		$this->assign('data',$data);
		
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('levels',$levels['list']);
		$this->assign('_params',$_params);
		
		$this->display();
	}	
	
	public function lists()
	{
		$where = array();	
##listsData##	
		$list = D('##Model##')->getList($where);
		$this->assign('where',$where);
		$this->assign('list',$list);
		$this->display();
	}
	
	public function add()
	{
##addData##
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
##editData##
		$info = D('##Model##')->getInfo($id);
		$this->assign('info',$info);
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$data = array();
##saveData##
		$data['addtime'] = time();
		$rs = false;
		if ($id>0)
		{
			$rs = D('##Model##')->update($data,array('id'=>$id));
		}
		else
		{
			$rs = D('##Model##')->add($data);
		}
		if ($rs !== false)
		{
			$this->ajaxReturn(array('href'=>U('##Controller##/index')),'操作成功！',REQUEST_SUCCESS);
		}
		else
		{
			$this->ajaxReturn(array('href'=>U('##Controller##/index')),'操作失败',INTERNAL_ERROR);
		}
		
	}
	
	public function delete()
	{
		$id = intval(I('id'));
		$this->vaildform('compare',array($id,0),'请选择要删除的项！');
		$rs = D('##Model##')->delete(array('id'=>$id));
		if ($rs !== false)
		{
			$this->ajaxReturn(array('href'=>U('##Controller##/index')),'操作成功！',REQUEST_SUCCESS);
		}
		else
		{
			$this->ajaxReturn(array('href'=>U('##Controller##/index')),'操作失败',INTERNAL_ERROR);
		}
	}
}
