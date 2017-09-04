<?php
class goodsAction extends baseAction
{
	function index()
	{

		$_params = $this->get_params(array('starttime','endtime','name','keytype','keyword'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['name']) $where['goods.name'] = array('like',"%".$_params['name']."%");
		$order = "id desc";
		if ($_params['keytype'] && $_params['keyword'])
		{
			$order = $_params['keytype']." ".$$_params['keyword'];
		}
		$fields = array();
		$fields['goods_saler'] = 'usid';
		$fields['goods'] = 'id,name,price,sale_count,stock,status,thumb';
		$fields['user_saler'] = 'name as us_name,mobile';
		$join[] = array('goods','gid','id');
		$join[] = array('user_saler','usid','id');
		$where['goods.upid'] = array('eq',0);
		$data = $this->loadModel('goodsSaler')->getJoinList($fields,$join,$where,$order,true);
		foreach($data['list'] as &$value)
		{
			if ($value['thumb']) $value['thumb'] = imgCheck($value['thumb']);
		}
		$this->assign('data',$data);
		$this->assign('_params',$_params);
		$this->display();
	}
}