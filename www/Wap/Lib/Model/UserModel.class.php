<?php
class userProviderModel extends ebModel
{
	/*
	*	获取供应商基本信息
	*/
	public function getBaseInfo($upid)
	{
		return M('user_provider')->where(array('id'=>$upid))->find();
	}
}
