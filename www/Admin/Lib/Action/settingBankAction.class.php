<?php
/**
 * @desc 银行管理
 * Class settingBankAction
 */
class settingBankAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('settingBank')->getList('*',$where,'id asc',true);
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
		$rs = $this->loadModel('settingBank')->getInfoByid($id);
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{ 
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','img','status'));
		$this->vaild_params('is_empty',$_params['name'],'请填写银行名称！');
		$this->vaild_params('is_empty',$_params['img'],'请上传背景图！');
		
		$data = array();
		$data['name'] = $_params['name'];
		$data['img'] =$_params['img'];
		$data['status'] = intval($_params['status']); 
		$rs = !$id?$this->loadModel('settingBank')->add($data):$this->loadModel('settingBank')->update($data,array('id'=>$id));
		$this->ajaxOut($data,'index');		
	}
	
	//处理临时上传的图片/视频
	public function imageUp($images)
	{
		import("@.Tool.file");
		if ($images)
		{  
			$images = file::tmp_to_final(str_replace('/Upload/bank_img/','',$v),$type,'img'); 
		} 
		return $images;
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('settingBank')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
