<?php
/**
 * @desc APP更新
 * Class versionAction
 */
class versionAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('version')->getList('*',$where,'id desc',true);
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
		$rs = $this->loadModel('version')->getInfoByid($id);
		if (strstr($rs['channel_id'],'1')){
            $rs['channel_id']=1;
        }elseif (strstr($rs['channel_id'],'2')){
            $rs['channel_id']=2;
        }elseif (strstr($rs['channel_id'],'3')){
            $rs['channel_id']=3;
        }elseif (strstr($rs['channel_id'],'4')){
            $rs['channel_id']=4;
        }elseif (strstr($rs['channel_id'],'5')){
            $rs['channel_id']=5;
        }elseif (strstr($rs['channel_id'],'6')){
            $rs['channel_id']=56;
        }
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('os','version_code','version_name','version_desc','status','down_url','channel_id','is_update'));
		$this->vaild_params('is_empty',$_params['version_code'],'版本编码 不能为空!');
		$this->vaild_params('is_empty',$_params['version_name'],'版本名称 不能为空!');
		$this->vaild_params('is_empty',$_params['down_url'],'下载链接 不能为空!');
        $_params['version_code'] = trim($_params['version_code']);
        $_params['version_name'] = trim($_params['version_name']);
        $_params['down_url'] = trim($_params['down_url']);
		if ($_params['os']==1){
            $_params['channel_id']=implode(',',$_params['channel_id']);
        }elseif ($_params['os']==2){
           unset($_params['channel_id']);
        }
		$data = array();
		$data = $_params;
		$rs = !$id?$this->loadModel('version')->add($data):$this->loadModel('version')->update($data,array('id'=>$id));
		$this->ajaxOut($rs,'index');
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('version')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
