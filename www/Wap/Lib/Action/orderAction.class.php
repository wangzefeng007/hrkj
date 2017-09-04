<?php
class orderAction extends baseAction
{
	public function index(){
		//$usid = $this->usid;
		$status = I('status',-1);
		$mobile = I('mobile');
		$m = M('order_shop');
		$active =array(
			$status => 'active',
		);
		$where = array();
		if ($status != -1) {
			$where['status'] = $status;
		}
		if ($mobile) {
			$where['mobile'] = $mobile;
		}
		
		$rs = $m->where($where)->select();
		foreach ($rs as $k => $v) {
			$lists[$k] = $v;
			$lists[$k]['status_name'] = C('ORDER_STATUS_BUY.'.$v['status']);
			$lists[$k]['goods_list'] = unserialize($v['goods']);
			if ($lists[$k]['goods_list']) {
				foreach ( $lists[$k]['goods_list'] as $g_k => $g_v) {
					$lists[$k]['goods_list'][$g_k]['thumb'] = imgCheck($g_v['thumb']);
				}
			}
		}
		$this->assign('lists',$lists);
		$this->assign('mobile',$mobile);
		$this->assign('active',$active);
		//$this->assign('usid', $usid);
		$this->display(); // 输出模板
    }

	//订单详情
	public function detail(){
		$sn = I('sn');

		$m = M('order_shop');
		$where = array(
			'sn' => $sn,
		);
		$rs = $m->where($where)->find();
		$rs['status_name'] = C('ORDER_STATUS_BUY.'.$rs['status']);
		$rs['goods_list'] = unserialize($rs['goods']);
		if ($rs['goods_list']) {
			foreach ( $rs['goods_list'] as $g_k => $g_v) {
				$rs['goods_list'][$g_k]['thumb'] = imgCheck($g_v['thumb']);
			}
		}
		$saler = M('user_saler')->field('name,mobile')->find($rs['usid']);
		
		//var_dump($saler);
		$this->assign('order',$rs);
		$this->assign('saler',$saler);
		$this->display(); // 输出模板
    }
	//查看物流
	public function express(){
		$sn = I('sn');

		$m = M('order_shop');
		$where = array(
			'sn' => $sn,
		);
		$rs = $m->where($where)->find();
		$rs['status_name'] = C('ORDER_STATUS_BUY.'.$rs['status']);
		$rs['goods_list'] = unserialize($rs['goods']);
		if ($rs['goods_list']) {
			foreach ( $rs['goods_list'] as $g_k => $g_v) {
				$rs['goods_list'][$g_k]['thumb'] = imgCheck($g_v['thumb']);
			}
		}
		
		$this->assign('order',$rs);
		$this->display(); // 输出模板
    }
}
