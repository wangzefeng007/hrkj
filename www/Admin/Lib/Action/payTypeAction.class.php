<?php
class payTypeAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$fields = array('pay_type'=>'*','pay_channel'=>'name as pc_name');
		$join = array('pay_channel','pcid','id');
		$data = $this->loadModel('payType')->getJoinList($fields,$join,$where,'sort desc,id desc',true);
		$cash_type = $this->loadModel('cashType')->getList('id,name',array('status'=>1));
		$platform_cost_fee = $this->loadModel('platformCostFee')->getFee();
		$this->assign('cash_type',$cash_type['list']);	
		$this->assign('platform_cost_fee',$platform_cost_fee);	
		$this->assign('data',$data);	
		$this->display();
	}
	
	
	public function add()
	{
		$pay_channel = $this->loadModel('payChannel')->getList('id,name',array('status'=>1));
		$cash_type = $this->loadModel('cashType')->getList('id,name',array('status'=>1));
		$this->assign('cash_type',$cash_type['list']);	
		$this->assign('pay_channel',$pay_channel['list']);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$info = $this->loadModel('payType')->getInfoByid($id);
		$pay_channel = $this->loadModel('payChannel')->getList('id,name',array('status'=>1));
		$cash_type = $this->loadModel('cashType')->getList('id,name',array('status'=>1));
		$platform_cost_fee = $this->loadModel('platformCostFee')->getFee($id);
		// dump($platform_cost_fee);
		$this->assign('cash_type',$cash_type['list']);	
		$this->assign('platform_cost_fee',$platform_cost_fee);	
		$this->assign('pay_channel',$pay_channel['list']);
		$this->assign('info',$info);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$platform_cost = $_REQUEST['platform_cost'];
		$_params = $this->get_params(array('name','sort','pcid','desc','status','is_show','cost_rate'));
		$this->vaild_params('is_empty',$_params['name'],'请填写名称！');
		$this->vaild_params('is_empty',$_params['pcid'],'请选择通道！');
		if ($platform_cost)
		{
			foreach($platform_cost as $val)
			{
				$this->vaild_params('compare',array($val['fee_rate'],1,'<'),'平台成本费率必须小于1');
				$this->vaild_params('compare',array($val['fee_static'],0,'>='),'平台结算成本不能小于0');
			}
		}
		
		$data = $_params;
		$data['addtime'] = time();
		$rs = !$id?$this->loadModel('payType')->add($data):$this->loadModel('payType')->update($data,array('id'=>$id));
		if ($rs)
		{
			$ptid = $id?$id:$rs;
			if ($platform_cost)
			{
				foreach($platform_cost as $key => $val)
				{
					$pcf_id = $val['id'];
					$data = array(
						'ptid' => $ptid,
						'ctid' => $key,
						'fee_rate' => $val['fee_rate'],
						'fee_static' => $val['fee_static'],
					);
					$rs = !$pcf_id?$this->loadModel('platformCostFee')->add($data):$this->loadModel('platformCostFee')->update($data,array('id'=>$pcf_id));
				}
			}
		}

		$this->ajaxOut($rs,'payType/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('payType')->del(array('id'=>array('IN',$ids)));
		$rs = $this->loadModel('platformCostFee')->del(array('ptid'=>array('IN',$ids)));
		$this->ajaxOut($rs,'payType/index');
	}
}
