<?php
class msgAction extends baseAction
{
	/*
	*	添加意见反馈接口
	*/
	function opinionAdd()
	{
		$data['usid'] = $this->usid;
		$data['title'] = I('title');
		$data['content'] = I('content');
		$data['name'] = I('name');
		$data['mobile'] = I('mobile');
		
		$data['addtime'] = time();


		$this->vaild_params('is_empty',$data['content'],'反馈意见不能为空');
		$this->vaild_params('is_empty',$data['mobile'],'联系方式不能为空');
		$rs = $this->loadModel('msgOpinion')->add($data);

		return $this->apiOut($rs,false);		
	}
	
	/*
	*	获取推送消息接口
	*/
	function pushList()
	{
		$list = $this->loadModel('msgPush')->getList();
		return $this->apiOut($list);
	}

	/*
	*	获取广告列表接口
	*/
	function adList()
	{
		$class = I('class');

		//定义分类名称
		$class_name = array(
			'1' => '首页广告',
		);
		$where = array(
			'status' => 1,
		);
		if ($class) {
			$where['class'] = $class;
		}
		$order = array(
			'class' => 'asc',
			'id' => 'desc',
		);
		$list = $this->loadModel('msgAd')->getList('*',$where,$order);
		foreach ($list['list'] as $k => $v) {
			$list['list'][$k]['class_name'] = $class_name[$v['class']];
		}
		return $this->apiOut($list);
	}
	
	public function pullJpushMsg(){
		$id = intval(I('id'));
		$this->vaild_params('is_empty',$id,'请传入合法的id');
		$rs = $this->loadModel('msgJiGuangPush')->getInfoByid($id);
		$data = array();
		$data['title'] = $rs['title']?$rs['title']:'';
		$data['content'] = $rs['content']?$rs['content']:'';
		return $this->apiOut($data);
	}
}
