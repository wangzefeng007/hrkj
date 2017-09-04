<?php
class ajaxAction extends baseAction
{
	/*
	*	ajax获取支付方式对应的支付通道
	*/
	public function payTypeChannel()
	{
		$ptid = I('ptid');
		$where = array('ptid'=>$ptid);
		$join = array(' eb_pay_channel AS pc ON pcid=pc.id');
		$list = D('payRelationTypeChannel')->lists('pay_relation_type_channel',$where,"pcid,name","1,40","pcid DESC",$join);
		$pay_channel = $list['list'];
		$this->ajaxReturn($pay_channel,'请求成功!',REQUEST_SUCCESS);
	}
}