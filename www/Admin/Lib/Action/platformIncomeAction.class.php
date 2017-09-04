<?php
class platformIncomeAction extends baseAction
{
	public function index()
	{
		$type = I('type');
		$_params = $this->get_params(array('starttime','endtime','lfid','keytype','keyword','status','sn','ptid','ctid'));
		#默认当天
		if(empty($_params['starttime'])){
		    $_params['starttime'] = date('Y-m-d');
		}
		if(empty($_params['endtime'])){
		    $_params['endtime'] = date('Y-m-d');
		}
		$where = array();		
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['ctid']) $where['ctid'] = $_params['ctid'];
		if ($_params['sn']) $where['order_sn'] = array('like', "%" . $_params['sn'] . "%");
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		if ($type == PLATFORM_INCOME_UPGRADE)
		{
			$where['type'] = PLATFORM_INCOME_UPGRADE;
		}
		elseif ($type == PLATFORM_INCOME_CASH)
		{
			$where['type'] = PLATFORM_INCOME_CASH;
		}
		
		//通道费率成本
		$rs = $this->loadModel('payType')->getList('id,name',array('status'=>1),'sort desc');
		$pay_type = reset_array_key($rs['list'],'id');
		$rs = $this->loadModel('cashType')->getList("*",'','id desc');
		$cash_type = reset_array_key($rs['list'],'id');
		$rs = $this->loadModel('levelFee')->getList("id,name",'','id desc');
		$level_fee = reset_array_key($rs['list'],'id','name');
		
		$sort = I('sort');
		if ($sort)
		{
			$arr = explode(',',$sort);
			$sort = array( $arr[0] => $arr[1] );
		}
		$order = $sort?$sort:'id desc';
		$data = $this->loadModel('accountPlatformIncome')->getList("*",$where,$order,true);
		// dump($cash_type);
		// dump($pay_type);
		$data['money'] = $this->loadModel('accountPlatformIncome')->where($where)->sum('money');
		$data['income_money'] = $this->loadModel('accountPlatformIncome')->where($where)->sum('income_money');
		$data['order_money'] = $this->loadModel('accountPlatformIncome')->where($where)->sum('order_money');
		$this->assign('_params',$_params);
		$this->assign('sort',$sort);
		$this->assign('level_fee',$level_fee);
		$this->assign('cash_type',$cash_type);
		$this->assign('pay_type',$pay_type);
		$this->assign('data',$data);
		if ($type == PLATFORM_INCOME_UPGRADE)
		{
			$this->display('upgrade');
		}
		else
		{
			$this->display();
		}
	}
	
}