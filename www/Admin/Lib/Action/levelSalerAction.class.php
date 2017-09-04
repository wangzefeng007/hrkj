<?php
class levelSalerAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$_params = $this->get_params(array('name'));
		$where = array();
		if ($_params['name']) $where['name'] = $_params['name'];
		$data = $this->loadModel('levelSaler')->getList('*',$where,'id desc',true);
		foreach($data['list'] as $key=>$value)
		{
			$fields = array('level_fee'=>'id,name');
			$join = array('level_fee','lfid','id');
			$where = array('lsid'=>$value['id']);
			$level_fee = $this->loadModel('levelSalerFeeRelation')->getJoinList($fields,$join,$where);
			$data['list'][$key]['lf_name'] = '';
			if ($level_fee['list'])
			{
				foreach($level_fee['list'] as $v)
				{
					$data['list'][$key]['lf_name'] .= $v['name'].",";
				}
			}
			$data['list'][$key]['lf_name'] = !empty($data['list'][$key]['lf_name'])?substr($data['list'][$key]['lf_name'],0,-1):'';
		}
		$this->assign('data',$data);
		$this->assign('_params',$_params);		
		$this->display();
	}
	
	
	public function add()
	{
		$level_fee = $this->loadModel('levelFee')->getList('id,name',array('status'=>1));
		$this->assign('level_fee',$level_fee['list']);
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$level = $this->loadModel('levelSaler')->getInfoByid($id);
		$level['level_fee'] = $this->loadModel('levelSalerFeeRelation')->getLevelfee($level['id']);
		$level_fee = $this->loadModel('levelFee')->getList('id,name',array('status'=>1));
		foreach($level_fee['list'] as $key=>$value)
		{
			if (in_array($value,$level['level_fee']))
			{
				$level_fee['list'][$key]['in'] = true;
			}
		}
		$this->assign('level',$level);
		$this->assign('level_fee',$level_fee['list']);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','lfid','status','sort'));
		$this->vaild_params('is_empty',$_params['name'],'请填写等级名称！');
		$this->vaild_params('is_empty',$_params['sort'],'请填写级别排序！');
		$this->vaild_params('is_empty',$_params['lfid'],'请选择绑定等级');
		
		if ($this->loadModel('levelSaler')->getInfo('id',array('id'=>array('neq',$id),'sort'=>intval($_params['sort']))))
		{
			$this->response(INTERNAL_ERROR,'级别排序已存在，请重新填写');
		}
		
		$time = time();
		$data = array();
		$data['name'] = $_params['name'];
		$data['status'] = intval($_params['status']);
		$data['sort'] = intval($_params['sort']);
		$data['addtime'] = time();

		$lsid = 0;	
		if ($id>0)
		{
		// dump($data);
			$rs = $this->loadModel('levelSaler')->update($data,array('id'=>$id));			
			if ($rs) $lsid=$id;
		}
		else
		{
			$rs = $this->loadModel('levelSaler')->add($data);
			$lsid = $rs;
		}
		if (!$rs)
		{
			$this->ajaxOut($rs);
		}
		
		if ($lsid)
		{
			$rs = $this->loadModel('levelSalerFeeRelation')->level_saler_fee_bind($lsid,$_params['lfid']);
		}
		$this->ajaxOut($rs,'levelSaler/index');		
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$this->loadModel('levelSalerFeeRelation')->del(array('id'=>array('IN',$ids)));
		$rs = $this->loadModel('levelSaler')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'levelSaler/index');
	}
}
