<?php
class paySettingAction extends baseAction
{
	public function pay()
	{
		//$where = array('status'=>1);
		$where = array('status'=>1,'is_show'=>1);
		$rs = $this->loadModel('payType')->getList('id,name,api',$where);
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	public function cash()
	{
		$where = array('status'=>1);
		$rs = $this->loadModel('cashType')->getList('id,name,fee',$where);
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	//升级的支付方式
	public function pay_upgrade()
	{
		//$where = array('status'=>1);
		$where = array('status'=>1,'is_show'=>1,'id'=>array('in',array(8))); 
		$rs = $this->loadModel('payType')->getList('id,name,api',$where);
		foreach($rs['list'] as $key =>$value){
			if ($value['id']==8){
				$rs['list'][$key]['name']='银联支付';
			} 
		}
		
		$response = $rs['list']?$rs:false;
		
		$this->apiOut($response);
	}
}