<?php
class orderShopAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		// $_params = $this->get_params(array('status','keytype','keyword','starttime','endtime','show'));
		$_params = $this->get_params(array('status','keytype','keyword','starttime','endtime','sn','show'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['sn']) $where['sn'] = $_params['sn'];
		$_params['status'] = ($_params['status']!='')?intval($_params['status']):'';
		if ($_params['status'] != '') $where['status'] = $_params['status'];
		if ($_params['show'] == 'up') $where['upid'] = array('gt',0);
		$order = "id desc";
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");
		}
		$data = $this->loadModel('orderShop')->getList("*",$where,$order,true);
		foreach($data['list'] as &$value)
		{
			$value['goods'] = unserialize($value['goods']);
			if ($value['goods']) 
			{
				foreach ( $value['goods'] as $g_k => $g_v) 
				{
					$value['goods'][$g_k]['thumb'] = imgCheck($g_v['thumb']);
				}
			}
		}
		$this->assign('data',$data);
		$this->assign('_params',$_params);
		$this->display();
	}
	
	public function lists()
	{
		$where = array();	
		if (is_empty(I('sn'))) $where['sn'] = I('sn');
		if (is_empty(I('us_name'))) $where['us_name'] = I('us_name');
		if (is_empty(I('up_name'))) $where['up_name'] = I('up_name');
	
		$list = D('orderShop')->getList($where);
		$this->assign('where',$where);
		$this->assign('list',$list);
		$this->assign('pay_status_desc',status_desc('PAY_STATUS'));
		$this->display();
	}
	
	/*
	*	订单详情
	*/
	public function detail()
	{
		$osid = intval(I('id'));
		//$order = D('orderShop')->detail($osid);
		$order = $this->loadModel('orderShop')->getInfoByid($osid);
		$order['status_desc'] = status_desc('ORDER_STATUS',$order['status']);
		$order['pay_status_desc'] = status_desc('PAY_STATUS',$order['status_pay']);
		$order['send_status_desc'] = status_desc('SEND_STATUS',$order['status_send']);
		$order['goods'] = unserialize($order['goods']);
		if ($order['goods']) 
		{
			foreach ( $order['goods'] as $g_k => $g_v) 
			{
				$order['goods'][$g_k]['thumb'] = imgCheck($g_v['thumb']);
			}
		}
		$express = getExpress($order['logistics'],$order['logistics_no']);
		$this->assign('express',$express['data']);
		$this->assign('order',$order);
		$this->display();
	}
}
