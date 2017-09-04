<?php
class shopAction extends baseAction
{
	/*
	*	添加店铺接口
	*	@todo 图片上传部分待验证
	*/
	function add()
	{
		import("@.Tool.file");
		$name = I('name');
		$weixin = I('weixin');
		$logo = file::tmp_to_final(I('logo'),'image','shop');
		$background = file::tmp_to_final(I('background'),'image','shop');
		$this->vaild_params('is_empty',$name,'请填写店铺名称');
		$this->vaild_params('is_file',ROOT_PATH.$logo,'请上传店铺头像');
		$this->vaild_params('is_file',ROOT_PATH.$background,'请上传店铺背景图');
		$this->loadModel('userSalerShop');
		$shop = $this->model['userSalerShop']->getInfo('id',array('usid'=>$this->usid));
		if ($shop)
		{
			$this->response(DATA_EXIST,'您已经开过店铺了，请勿重复添加');
		}		
		$data = array();
		$data['usid'] = $this->usid;
		$data['name'] = trim($name);
		$data['weixin'] = trim($weixin);
		$data['logo'] = $logo;
		$data['background'] = $background;
		$data['status'] = 1;
		$data['addtime'] = time();
		$rs = $this->model['userSalerShop']->add($data);
		if ($rs)
		{
			$rs = $this->loadModel('userSaler')->update(array('status_shop'=>1),array('id'=>$this->usid));
			$rs = ($rs === false)?false:true;
		}
		$this->apiOut($rs,false);		
	}
	
	/*
	*	编辑店铺接口
	*	@todo 图片上传部分待验证
	*/
	function update()
	{
		import("@.Tool.file");
		$name = I('name');
		$weixin = I('weixin');
		$logo = file::tmp_to_final(I('logo'),'image','shop');
		$background = file::tmp_to_final(I('background'),'image','shop');
		$this->vaild_params('is_empty',$name,'请填写店铺名称');
		$this->vaild_params('is_file',ROOT_PATH.$logo,'请上传店铺头像');
		$this->vaild_params('is_file',ROOT_PATH.$background,'请上传店铺背景图');
		$this->loadModel('userSalerShop');
		$shop = $this->model['userSalerShop']->getInfo('id',array('usid'=>$this->usid));
		if (!$shop)
		{
			$this->response(DATA_EXIST,'您还没开店,请先开店!');
		}		
		$data = array();
		$data['usid'] = $this->usid;
		$data['name'] = trim($name);
		$data['weixin'] = trim($weixin);
		$data['logo'] = $logo;
		$data['background'] = $background;
		$data['status'] = 1;
		$data['uptime'] = time();
		$rs = $this->model['userSalerShop']->update($data,array('usid'=>$this->usid));
		$rs = ($rs === false)?false:true;
		$this->apiOut($rs,false);		
	}

	//获取店铺信息
	function info()
	{
		$fields = array();
		$fields['user_saler'] = "name as us_name,mobile";
		$fields['user_saler_shop'] = "id,name,logo,desc,background,addtime,weixin";
		$join = array('user_saler','usid','id');
		$info = $this->loadModel('userSalerShop')->getJoinInfo($fields,$join,array('usid'=>$this->usid,'status'=>1));
		if ($info)
		{
			$server = $this->_server();
			$host = $server['HTTP_HOST'];
			$url = "http://".$host."/wap.php/web/index/usid/".$this->usid; 
			$info['wap'] = getShortUrl($url); 
		}
		return $this->apiOut($info);
	}
}