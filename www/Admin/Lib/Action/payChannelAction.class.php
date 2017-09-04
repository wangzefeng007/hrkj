<?php
/**
 * @desc 通道管理
 * Class payChannelAction
 */
class payChannelAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$data = $this->loadModel('payChannel')->getList('*',$where,'id desc',true);
		$this->assign('data',$data);	
		$this->display();
	}
	
	
	public function add()
	{
		$pay_type = $this->loadModel('payType')->getList('id,name',array('status'=>1));
		$cash_type = $this->loadModel('cashType')->getList('id,name',array('status'=>1));
		$this->assign('pay_type',$pay_type);
		$this->assign('cash_type',$cash_type);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$pay = $this->loadModel('payChannel')->getInfoByid($id);
		$this->assign('pay',$pay);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','cost_rate','status'));
		$this->vaild_params('is_empty',$_params['name'],'请填写通道名称！');
		$this->vaild_params('is_empty',$_params['cost_rate'],'请填写成本费率！');
		
		$data = array();
		$data['name'] = $_params['name'];
		$data['cost_rate'] = $_params['cost_rate'];
		$data['status'] = intval($_params['status']);
		$data['addtime'] = time();
		$rs = !$id?$this->loadModel('payChannel')->add($data):$this->loadModel('payChannel')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'payChannel/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('payChannel')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'payChannel/index');
	}
}
