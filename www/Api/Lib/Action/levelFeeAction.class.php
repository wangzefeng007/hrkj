<?php
class levelFeeAction extends baseAction
{
	/*
	*	费用等级列表
	*/
	public function lists()
	{
		$rs = $this->loadModel('levelFee')->getList('id,name,level,fee_upgrade,child_upgrade,ftf_upgrade,split_rate,deposit,is_show,is_update',array('status'=>1),'level asc');
		$this->apiOut($rs);
	}
	
	/*
	*	等级结算费率详细信息
	*/
	public function rate()
	{
		$lfid = intval(I('lfid'));
		$this->vaild_params('is_empty',$lfid,'缺少参数,lfid');
		$rs = $this->loadModel('levelFee')->getRate($lfid);
		if (!$rs['list']) $rs = false;
		$this->apiOut($rs);
	}
	
	/*
	*	等级结算费率详细信息
	*/
	public function getUpInfo()
	{
		$lfid = intval(I('lfid'));
		$this->vaild_params('is_empty',$lfid,'缺少参数,lfid');
		$up_info = $this->loadModel('levelFee')->getInfo('id,name,fee_upgrade,child_upgrade,ftf_upgrade,split_rate,deposit,is_show,is_update',array('id'=> $lfid));

		$field = array(
			'pay_type'=>'id as ptid,name as pt_name,api',
			'level_pay_limit'=>'lfid,min,max,day_min,day_max',
		);
		$join = array();
		$join[] = array('level_pay_limit','id','ptid');	
		$where = array(
			'pay_type.status' => 1,
			'pay_type.is_show' => 1,
			'level_pay_limit.lfid' => $lfid,
		);
		$pt_info = $this->loadModel('payType')->getJoinList($field,$join,$where,'sort desc,ptid asc');
		if ($pt_info['list'])
		{
			foreach($pt_info['list'] as $key => $val)
			{
				if ($val['day_max'] > 0)
				{
					$where = array(
						'usid' => $this->usid,
						'status' => 1,
						'ptid' => $val['ptid'],
					);
					$where['addtime'][] = array('egt',strtotime(date('Y-m-d')));
					$where['addtime'][] = array('lt',strtotime(date('Y-m-d')) + 3600*24);
					$day_money = D('OrderFtf')->where($where)->sum('money');
					$day_money = $day_money?$day_money:0;
					$limit = $val['day_max'] - $day_money;
				}
				else
				{
					$limit = $val['day_max'];
				}
				$pt_info['list'][$key]['day_limit']	=	$limit;	//当日剩余额度
				
				$field = array(
					'cash_type'=>'id as ctid,name as ct_name',
					'level_cash_fee'=>'fee_rate,fee_static,min,max',
				);
				$join = array();
				$join[] = array('level_cash_fee','id','ctid');	
				$where = array(
					'cash_type.status' => 1,
					'level_cash_fee.limit_status' => 0,
					'level_cash_fee.lfid' => $lfid,
					'level_cash_fee.ptid' => $val['ptid'],
				);
				$ct_info = $this->loadModel('cashType')->getJoinList($field,$join,$where);
				if ($ct_info['list'])
				{
					$pt_info['list'][$key]['ct_info'] = $ct_info['list'];
				}
				else
				{
					$pt_info['list'][$key]['ct_info'] = array();
				}
			}
			$up_info['pt_info'] = $pt_info['list'];
		}
		else
		{
			$up_info['pt_info'] = array();
		}
		$rs['list'] = $up_info?$up_info:false;
		$this->apiOut($rs);
	}
    /*
*	等级结算费率详细信息
*/
    public function getNewUpInfo()
    {
        $level = intval(I('level'));
        $level = strval($level) ;
        if ($level!='0')
        $this->vaild_params('is_empty',$level,'缺少参数,level');
        $up_info = $this->loadModel('levelFee')->getInfo('id,name,fee_upgrade,child_upgrade,ftf_upgrade,split_rate,deposit,is_show,is_update',array('level'=> $level));
        if ($level=='0'){
            $up_info['grade']= 0;
        }elseif($level>2){
            $up_info['grade']= 1;
        }else{
            $up_info['grade']= 2;
        }
        $field = array(
            'pay_type'=>'id as ptid,name as pt_name,api',
            'level_pay_limit'=>'lfid,min,max,day_min,day_max',
        );
        $join = array();
        $join[] = array('level_pay_limit','id','ptid');
        $where = array(
            'pay_type.status' => 1,
            'pay_type.is_show' => 1,
            'level_pay_limit.lfid' => $up_info['id'],
        );
        $pt_info = $this->loadModel('payType')->getJoinList($field,$join,$where,'sort desc,ptid asc');
        if ($pt_info['list'])
        {
            foreach($pt_info['list'] as $key => $val)
            {
                if ($val['day_max'] > 0)
                {
                    $where = array(
                        'usid' => $this->usid,
                        'status' => 1,
                        'ptid' => $val['ptid'],
                    );
                    $where['addtime'][] = array('egt',strtotime(date('Y-m-d')));
                    $where['addtime'][] = array('lt',strtotime(date('Y-m-d')) + 3600*24);
                    $day_money = D('OrderFtf')->where($where)->sum('money');
                    $day_money = $day_money?$day_money:0;
                    $limit = $val['day_max'] - $day_money;
                }
                else
                {
                    $limit = $val['day_max'];
                }
                $pt_info['list'][$key]['day_limit']	=	$limit;	//当日剩余额度

                $field = array(
                    'cash_type'=>'id as ctid,name as ct_name',
                    'level_cash_fee'=>'fee_rate,fee_static,min,max',
                );
                $join = array();
                $join[] = array('level_cash_fee','id','ctid');
                $where = array(
                    'cash_type.status' => 1,
                    'level_cash_fee.limit_status' => 0,
                    'level_cash_fee.lfid' => $up_info['id'],
                    'level_cash_fee.ptid' => $val['ptid'],
                );
                $ct_info = $this->loadModel('cashType')->getJoinList($field,$join,$where);
                if ($ct_info['list'])
                {
                    $pt_info['list'][$key]['ct_info'] = $ct_info['list'];
                }
                else
                {
                    $pt_info['list'][$key]['ct_info'] = array();
                }
            }
            $up_info['pt_info'] = $pt_info['list'];
        }
        else
        {
            $up_info['pt_info'] = array();
        }
        $rs['list'] = $up_info?$up_info:false;
        $this->apiOut($rs);
    }
}
