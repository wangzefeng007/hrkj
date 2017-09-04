<?php
class goodsProviderAction extends baseAction
{
	function index()
	{

		$_params = $this->get_params(array('starttime','endtime','name','gcid','status'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['name']) $where['name'] = array('like',"%".$_params['name']."%");
		if ($_params['gcid']) $where['gcid'] = intval($_params['gcid']);
		if ($_params['status'] != '') 
		{
			$where['status'] = intval($_params['status']);
			$_params['status'] = intval($_params['status']);
		}
		$order = "id desc";
		$fields = array();
		$fields['goods'] = '*';
		$fields['user_provider'] = 'username';
		$join = array('user_provider','upid','id');
		$where['goods.upid'] = array('gt',0);
		$data = $this->loadModel('goods')->getJoinList($fields,$join,$where,$order,true);
		$this->assign('data',$data);
		$this->assign('_params',$_params);
		$this->display();
	}
	
	public function audit()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$where = array();
		$where['id'] = array('IN',$ids);
		$where['status'] = GOODS_WAIT_AUDIT;
		$rs = $this->loadModel('goods')->update(array('status'=>GOODS_SALE),$where);
		$this->ajaxOut($rs,'goodsProvider/index');
	}
	
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('goods')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'goodsProvider/index');
	}
}