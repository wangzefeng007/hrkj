<?php
class accountUserProviderCashAction extends baseAction
{

	
	public function index()
	{
		$_params = $this->get_params(array('starttime','endtime','ptid','keytype','keyword','status'));
		$where = array();
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}

		
		$data = $this->loadModel('accountUserProviderCash')->getList('*',$where,'id desc',true);
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	

}
