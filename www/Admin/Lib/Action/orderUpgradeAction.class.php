<?php
class orderUpgradeAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$_params = $this->get_params(array('starttime','endtime','keytype','keyword','status','export'));
		$where = array();
		if(empty($_params['status'])){
		    $_params['status'] = 1;
		}
		if(empty($_params['starttime'])){
		    $_params['starttime'] = date('Y-m-d');
		}
		if(empty($_params['endtime'])){
		    $_params['endtime'] = date('Y-m-d');
		}
		
		if ($_params['status']!='') $where['status'] = $_params['status'];
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		$fields = array();
		$fields['order_upgrade'] = "*";
		$fields['account_user_saler_income'] = "money as income_money,us_name as income_us_name";
		$fields['user_saler'] = "mobile as income_us_mobile";
		$join[] = array('account_user_saler_income','sn','account_user_saler_income.order_sn');
		$join[] = array('user_saler','account_user_saler_income.usid','id');
		//print_r($join);exit;
		// $data = $this->loadModel('orderUpgrade')->getJoinList($fields,$join,$where," id DESC ",true);
		if ($_params['export'])
		{
			//表格下载
			import("@.Tool.export");
			if (method_exists('export',$_params['export']))
			{
				$_title = array('升级帐号','升级商户','订单号','升级费用','风险金','升级代理类型','受益人','受益人手机号','受益百分比','受益金额','付款状态','提交时间');				
				$data = $this->loadModel('orderUpgrade')->getJoinList($fields,$join,$where," id DESC ");
				$_data = array();
				foreach($data['list'] as $key=>$value)
				{
					$_data[$key][] = $value['us_mobile'];
					$_data[$key][] = $value['us_name'];
					$_data[$key][] = $value['sn'];
					$_data[$key][] = $value['money']>0?$value['money']-$value['deposit']:$value['money'];
					$_data[$key][] = $value['deposit'];
					$_data[$key][] = $value['lf_name_new'];
					$_data[$key][] = $value['income_us_name'];
					$_data[$key][] = $value['income_us_mobile'];
					$_data[$key][] = $value['lf_rate'];
					$_data[$key][] = $value['income_money'];
					$_data[$key][] = ($value['status'] == 1)?'已支付':'未支付';
					$_data[$key][] = date('Y-m-d H:i:s',$value['addtime']);
					
				}
				export::$_params['export']($_title,$_data,'升级记录');
				exit;				
			}
		}
		else
		{
			$data = $this->loadModel('orderUpgrade')->getJoinList($fields,$join,$where," id DESC ",true);
		}
		$this->assign('data',$data);
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('levels',$levels['list']);
		$this->assign('_params',$_params);		
		$this->display();
	}

	

}
