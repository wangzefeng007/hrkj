<?php
/**
 * @desc 支付方式
 * Class levelFeeAction
 */
class cashTypeAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$data = $this->loadModel('cashType')->getList("*",array(),'id desc',true);
		$this->assign('data',$data);	
		$this->display();
	}
	
	
	public function add()
	{
		$pay_channel = $this->loadModel('payChannel')->getList('id,name',array('status'=>1));
		$this->assign('pay_channel',$pay_channel['list']);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$info = $this->loadModel('cashType')->getInfoByid($id);
		$pay_channel = $this->loadModel('payChannel')->getList('id,name',array('status'=>1));
		$this->assign('pay_channel',$pay_channel['list']);
		$this->assign('info',$info);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','max','min','fee','desc','status'));
		$this->vaild_params('is_empty',$_params['name'],'请填写名称！');
		
		$data = $_params;
		$data['addtime'] = time();
		$rs = !$id?$this->loadModel('cashType')->add($data):$this->loadModel('cashType')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'cashType/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('cashType')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'cashType/index');
	}
}
