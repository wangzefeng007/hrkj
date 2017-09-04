<?php
class userSalerAccountAction extends baseAction
{
	
	//分润收益合计
	public function splitTotal()
	{
		$_params = $this->get_params(array('keytype','keyword','export'));
		$where = array();
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		$fields = array();
		$fields['user_saler_account_total'] = 'id,usid,normal_split_total,upgrade_split_total,commission_split_total,commission_total,saleback_total';
		$fields['user_saler'] = 'name,mobile';
		$join[] = array('user_saler','usid','id');
		// $data = $this->loadModel('userSalerAccountTotal')->getJoinList($fields,$join,$where,'id desc',true);
		if ($_params['export'])
		{
			//表格下载
			import("@.Tool.export");
			if (method_exists('export',$_params['export']))
			{
				$_title = array('商户','账号','总额','商户分润','升级分润','佣金分润','分销佣金','分销返现');				
				$data = $this->loadModel('userSalerAccountTotal')->getJoinList($fields,$join,$where,'id desc');
				$_data = array();
				foreach($data['list'] as $key=>$value)
				{
					$_data[$key][] = $value['name'];
					$_data[$key][] = $value['mobile'];
					$_data[$key][] = $value['normal_split_total'] + $value['upgrade_split_total'] + $value['commission_split_total']+$value['commission_total']+$value['saleback_total'];
					$_data[$key][] = $value['normal_split_total'];
					$_data[$key][] = $value['upgrade_split_total'];
					$_data[$key][] = $value['commission_split_total'];
					$_data[$key][] = $value['commission_total'];
					$_data[$key][] = $value['saleback_total'];
				}
				export::$_params['export']($_title,$_data,'收益列表');
				exit;				
			}
		}
		else
		{
			if(I('normal_split_total')) $sort['normal_split_total'] = I('normal_split_total');
			if(I('upgrade_split_total')) $sort['upgrade_split_total'] = I('upgrade_split_total');
			$order = $sort?$sort:'id desc';
			$data = $this->loadModel('userSalerAccountTotal')->getJoinList($fields,$join,$where,$order,true);
		}

		$this->assign('sort',$sort);
		$this->assign('data',$data);
		$this->assign('_params',$_params);
		$this->display();
	}
	
	//收益明细列表
	public function splitList()
	{
		$_params =  $this->get_params(array('starttime','endtime','ptid','usid','type'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['usid']) $where['usid'] = $_params['usid'];
		if ($_params['type']) $where['type'] = $_params['type'];
		
		$user = $this->loadModel('userSaler')->getInfoByid($_params['usid']);
		$pay_type = $this->loadModel('payType')->getList('id,name');
		$fields = array();
		$fields['account_user_saler_income'] = "id,money,order_money,pt_name,from_usid,addtime";
		$fields['user_saler'] = "name,mobile";
		$join = array('user_saler','from_usid','id');
		$data = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'id desc',true);
		$this->assign('data',$data);
		$this->assign('user',$user);
		$this->assign('_params',$_params);
		$this->assign('pay_type',$pay_type['list']);
		$this->display();
		
		
	}

	public function lists()
	{
		$_params = $this->get_params(array('starttime','endtime','ptid','keytype','keyword'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		$fields = array();
		$fields['user_saler_account'] = '*';
		$fields['user_saler'] = 'name,mobile';
		$fields['pay_type'] = 'name as pt_name';
		$join[] = array('user_saler','usid','id');
		$join[] = array('pay_type','ptid','id');
		$data = $this->loadModel('userSalerAccount')->getJoinList($fields,$join,$where,'id desc',true);
		$data['money'] = $this->loadModel('userSalerAccount')->sum('money');
		// dump($data);
		$pay_type = $this->loadModel('payType')->getList('id,name');
		$this->assign('pay_type',$pay_type['list']);
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	

}
