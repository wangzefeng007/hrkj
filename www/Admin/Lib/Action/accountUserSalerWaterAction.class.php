<?php
//资金流水
class accountUserSalerWaterAction extends baseAction
{
	public function lists()
	{
		$_params = $this->get_params(array('starttime','endtime','keytype','keyword','sn'));
		if(empty($_params['starttime'])){
		    $_params['starttime'] = date('Y-m-d');
		}
		if(empty($_params['endtime'])){
		    $_params['endtime'] = date('Y-m-d');
		}
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['sn']) $where['order_sn'] = array('like', "%" . $_params['sn'] . "%");
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where["user_saler.".$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		$fields = array();
		$fields['account_user_saler_water'] = '*';
		$fields['user_saler'] = 'name,mobile';
		$join[] = array('user_saler','usid','id');
		$data = $this->loadModel('accountUserSalerWater')->getJoinList($fields,$join,$where,'id desc',true);
		// $all_data = $this->loadModel('accountUserSalerWater')->getJoinList($fields,$join,$where,'id desc');
		
		// $usids = array();
		// foreach($all_data['list'] as $value)
		// {
			// $usids[] = $value['usid'];
		// }
		// $usids = array_unique($usids);
		// $data['income'] = $this->loadModel('accountUserSalerWater')->where(array('type'=>1,'usid'=>array('in',$usids)))->sum('money');
		// $data['cash'] = $this->loadModel('accountUserSalerWater')->where(array('type'=>2,'usid'=>array('in',$usids)))->sum('money');
		$data['income'] = $this->loadModel('accountUserSalerWater')->where(array_merge($where,array('type'=>1)))->sum('money');
		$data['cash'] = $this->loadModel('accountUserSalerWater')->where(array_merge($where,array('type'=>2)))->sum('money');
		$data['total_usable'] = $this->loadModel('userSalerAccountTotal')->where($where)->sum('total_usable');
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
}