<?php
class shopAction extends baseAction
{
	function index()
	{
		$_params = $this->get_params(array('starttime','endtime','lfid','keytype','keyword','status'));
		$where = array();
		if ($_params['status']!='') $where['status'] = $_params['status'];
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['lfid']) $where['lfid'] = $_params['lfid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		
		$fields = array();
		$fields['user_saler_shop'] = 'id,usid,name,status,addtime';
		$fields['user_saler'] = 'status as status_real,name as us_name,mobile,lfid';
		$fields['level_fee'] = 'name AS lf_name';
		$join[] = array('user_saler','usid','id');
		$join[] = array('level_fee','user_saler.lfid','id');		
		$data = $this->loadModel('userSalerShop')->getJoinList($fields,$join,$where," id DESC ",true);
		$this->assign('data',$data);
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('levels',$levels['list']);
		$this->assign('_params',$_params);
		
		$this->display();
	}
	
	/*
	*	店铺状态更新开启
	*/
	public function update()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));		
		$status = intval(I('status'));
		$rs = $this->loadModel('userSalerShop')->update(array('status'=>$status),array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'shop/index');
	}
	
	public function detail()
	{
		$id = intval(I('id'));
		
		$fields = array();
		$fields['user_saler_shop'] = 'id,usid,name,status,addtime';
		$fields['user_saler'] = 'status as status_real,name as us_name,mobile,lfid';
		$fields['level_fee'] = 'name AS lf_name';
		$join[] = array('user_saler','usid','id');
		$join[] = array('level_fee','user_saler.lfid','id');
		$where = array('user_saler_shop.id'=>$id);	
		$shop = $this->loadModel('userSalerShop')->getJoinInfo($fields,$join,$where);
		
		$where = array('usid'=>$shop['usid']);
		$_params = $this->get_params(array('name','keytype','keyword'));
		if ($_params['name']) $where['name'] = array('like',"%".$_params['name']."%");
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}

		$fields = array();
		$fields['goods_saler'] = 'usid,gid';
		$fields['goods'] = '*';
		$join[] = array('goods','gid','id');
		$where = array('goods_saler.usid'=>$shop['usid']);	
		$goods = $this->loadModel('goodsSaler')->getJoinList($fields,$join,$where,'id desc',true);
		$this->assign('shop',$shop);
		$this->assign('goods',$goods);
		$this->display();
	}
	
	/*
	*	删除商品
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));			
		$rs = $this->loadModel('goods')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'shop/index');
	}
	

}