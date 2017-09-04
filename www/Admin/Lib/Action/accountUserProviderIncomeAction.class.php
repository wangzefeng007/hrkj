<?php
class accountUserProviderIncomeAction extends baseAction
{

	
	public function index()
	{
		$_params = $this->get_params(array('starttime','endtime','ptid','keytype','keyword','status'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['status'] != '') 
		{
			$where['status'] = $_params['status'];
			$_params['status'] = intval($_params['status']);
		}
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		$fields = array();
		$fields['account_user_provider_income'] = '*';
		$fields['user_provider'] = 'username,shop_name';
		$join = array('user_provider','upid','id','left join');
		$data = $this->loadModel('accountUserProviderIncome')->getJoinList($fields,$join,$where,'id desc',true);
		$data['money'] = $this->loadModel('accountUserProviderIncome')->sum('money');
		
		$pay_type = $this->loadModel('payType')->getList('id,name');
		$this->assign('pay_type',$pay_type['list']);
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	

}
