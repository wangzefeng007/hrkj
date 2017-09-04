<?php
class xwjrAction extends baseAction
{
	
	//获取小微金融分类列表
	public function classList(){
		$where['status'] = 1;
		$order['sort'] = 'desc';
		$rs = $this->loadModel('xwjrClass')->getList('*',$where,$order);
		$this->apiOut($rs);
	}

	//新增小微金融条目
	public function add(){
		$_params = $this->get_params(array('name','tel','type'));
		$this->vaild_params('is_empty',$_params['name'],'姓名不能为空！');
		$this->vaild_params('is_empty',$_params['tel'],'电话不能为空！');
		$this->vaild_params('is_empty',$_params['type'],'类别不能为空！');

		$data = $_params;
		$data['usid']  = $this->usid;
		$data['addtime']  = time();

		$rs = $this->loadModel('xwjr')->add($data);
		$this->apiOut($rs,false);
	}

	
}
