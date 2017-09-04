<?php
class msgAdAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('msgAd')->getList('*',$where,'id asc',true);
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
		$rs = $this->loadModel('msgAd')->getInfoByid($id);
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','url','image','desc','class','width','height','status'));
		$this->vaild_params('is_empty',$_params['name'],'请填写广告名称！');
		$this->vaild_params('is_empty',$_params['image'],'请上传广告图片！');
		$this->vaild_params('is_empty',$_params['width'],'请填写广告宽度！');
		$this->vaild_params('is_empty',$_params['height'],'请填写广告高度！');
		
		if ($_params['image'])
		{
			import("@.Tool.file");
			if (strpos($_params['image'],'tmp'))
			{
				$_params['image'] = file::tmp_to_final(str_replace('/Upload/tmp/','',$_params['image']),'image','ad');
			}
		}
		$data = array();
		$data = $_params;
		$rs = !$id?$this->loadModel('msgAd')->add($data):$this->loadModel('msgAd')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('msgAd')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
