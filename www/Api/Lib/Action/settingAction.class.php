<?php
class settingAction extends baseAction
{
	/*
	*	获取各类文本协议(注册协议,禁售协议,等)接口
	*/
	function getContent()
	{
		$skey = I('skey');

		$this->vaild_params('is_empty',$skey,'skey不能为空!');
		$where = array('skey' => $skey);
		$rs = $this->loadModel('settingContent')->getInfo('*',$where);

		return $this->apiOut($rs);
	}

	/*
	*	获取银行列表接口
	*/
	function bankList()
	{
		$list = $this->loadModel('settingBank')->getList();
		return $this->apiOut($list);
	}

	
}
