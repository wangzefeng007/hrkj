<?php
/**
 * @desc 费用级别管理
 * Class levelFeeAction
 */
class levelFeeAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = $_params['name'];
		$data = $this->loadModel('levelFee')->getList('*',$where,'level desc',true);
		$this->assign('data',$data);
		$this->assign('_params',$_params);		
		$this->display();
	}
	
	
	public function add()
	{
		$pay_type = $this->loadModel('payType')->getList('id,name',array('status'=>1));
		$cash_type = $this->loadModel('cashType')->getList('id,name,fee',array('status'=>1));
		$this->assign('pay_type',$pay_type);
		$this->assign('cash_type',$cash_type);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$pay_type = $this->loadModel('payType')->getList('id,name');
		$cash_type = $this->loadModel('cashType')->getList('id,name,fee');
		$this->assign('pay_type',$pay_type);
		$this->assign('cash_type',$cash_type);
		
		$level = $this->loadModel('levelFee')->getInfoByid($id);
		$fee = $this->loadModel('levelCashFee')->getList('*',array('lfid'=>$id),'ptid asc,pcid asc');
		$level['fee'] = $split = $commission = array();
		foreach($fee['list'] as $value)
		{
			if ($value['ptid']== -1)
			{
				$split[$value['ctid']] = $value;
			}
			elseif ($value['ptid'] == -2)
			{
				$commission[$value['ctid']] = $value;
			}
			else
			{
				$level['fee'][] = $value;
			}
		}

		$this->assign('level',$level);
		$this->assign('split',$split);
		$this->assign('commission',$commission);
		
		
		$pay_limit = $this->loadModel('levelPayLimit')->getList("*",array('lfid'=>$id));
		$pay_limit = reset_array_key($pay_limit['list'],'ptid');
		
		$this->assign('pay_limit',$pay_limit);
		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('level','name','fee_upgrade','deposit','split_rate','status','ptid','ctid','fee_rate','fee_static_rate','is_update','is_show','child_upgrade','ftf_upgrade','share_rate','own_rate','type','plugin','max','day_max','limit_status','limit','pay_min','pay_max','pay_ptid','pay_day_max','fee_static_min','fee_static_max','fee_static'));
		
		$_params['level'] = intval($_params['level']);
		$this->vaild_params('is_empty',$_params['name'],'请填写等级名称！');
//		$this->vaild_params('compare',array($_params['level'],0,'>'),'等级序号必须大于0！');
		// $this->vaild_params('is_empty',$_params['status'],'请选择状态！');
		// $this->vaild_params('compare',array($params['fee_upgrade'],0,'>'),'升级费用必须大于0！');
		// $this->vaild_params('compare',array($_params['split_rate'],0),'升级分成比例必须在0-1之间！');
		// $this->vaild_params('is_empty',$_params['deposit'],'请填写风险保证金！');
		foreach($_params['ptid'] as $key=>$value)
		{
			$this->vaild_params('is_empty',$_params['ctid'][$key],'请填写提现方式');
			$this->vaild_params('is_empty',$_params['fee_rate'][$key],'请填写提现费率');
			// $this->vaild_params('is_empty',$_params['fee_static_rate'][$key],'请填写手续费费率');
			if (!$_params['fee_static'][$key])
			{
				$ctinfo = $this->loadModel('cashType')->getInfoByid($_params['ctid'][$key],'fee');
				$_params['fee_static'][$key] = $ctinfo['fee'];
			}
			$cash_setting[] = array(
				'ptid'=>$value,
				'ctid'=>$_params['ctid'][$key],
				'fee_rate'=>$_params['fee_rate'][$key],
				'fee_static'=>$_params['fee_static'][$key],
				// 'fee_static_rate'=>$_params['fee_static_rate'][$key],
				// 'fee_static_min'=>$_params['fee_static_min'][$key],
				// 'fee_static_max'=>$_params['fee_static_max'][$key],
				'max'=>$_params['max'][$key],
				'day_max'=>$_params['day_max'][$key],
				'limit_status'=>$_params['limit_status']?(int) $_params['limit_status'][$value][$_params['ctid'][$key]]:0
			);
		}
		
		$time = time();
		$data = array();
		$data['level'] = $_params['level'];
		$data['name'] = $_params['name'];
		$data['fee_upgrade'] = $_params['fee_upgrade'];
		$data['child_upgrade'] = $_params['child_upgrade'];
		$data['ftf_upgrade'] = $_params['ftf_upgrade'];
		$data['deposit'] = $_params['deposit'];
		$data['split_rate'] = $_params['split_rate'];
		$data['status'] = intval($_params['status']);
		$data['is_show'] = intval($_params['is_show']);
		$data['is_update'] = intval($_params['is_update']);
		$data['share_rate'] = !empty($_params['share_rate'])?$_params['share_rate']:0;
		$data['own_rate'] = !empty($_params['own_rate'])?$_params['own_rate']:0;
		$data['type'] = intval($_params['type']);
		$data['addtime'] = time();
// dump($data);
		$lfid = 0;	
		if ($id>0)
		{
			$rs = $this->loadModel('levelFee')->getList("*",array('level'=>$data['level'],'id'=>array('neq',$id)));
			$this->vaild_params('is_empty',!$rs['list'],'等级序号已存在,请重新输入');

			$rs = $this->loadModel('levelFee')->update($data,array('id'=>$id));			
			if ($rs) $lfid=$id;
		}
		else
		{
			$rs = $this->loadModel('levelFee')->getList("*",array('level'=>$data['level']));
			$this->vaild_params('is_empty',!$rs['list'],'等级序号已存在,请重新输入');
			
			$rs = $this->loadModel('levelFee')->add($data);
			$lfid = $rs;
		}
		if (!$rs)
		{
			$this->ajaxOut($rs);
		}
		
		if ($lfid)
		{
			$rs = $this->loadModel('levelCashFee')->level_cash_fee($lfid,$cash_setting);
			// dump($_params['pay_max']);
			foreach($_params['pay_ptid'] as $ptid)
			{
				$pay_limit = $this->loadModel('levelPayLimit')->getInfo('id',array('lfid'=>$lfid,'ptid'=>$ptid));
				$data = array(
					'ptid'=>$ptid,
					'min'=>$_params['pay_min'][$ptid],
					'max'=>$_params['pay_max'][$ptid],
					'day_max'=>$_params['pay_day_max'][$ptid],
				);
				
				if (!$pay_limit)
				{
					$data['lfid'] = $lfid;
					$this->loadModel('levelPayLimit')->add($data);
				}
				else
				{
					$this->loadModel('levelPayLimit')->update($data,array('id'=>$pay_limit['id']));
				}
			}
		}
		$this->ajaxOut($rs,'levelFee/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('levelFee')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'levelFee/index');
	}
}
