<?php
class orderFtfAction extends baseAction
{

	public function lists()
	{
		$status = I('status','');
		$_params = $this->get_params(array('search','starttime','endtime'));
		$where = array('usid'=>$this->usid);
		if ($status === '')
		{
			$where['status'] = array(array('eq',1),array('eq',-1), 'or');
		}
		else
		{
			$where['status'] = $status;
		}

		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);

		$rs = $this->loadModel('orderFtf')->getList('sn,pt_name,status,money,total,addtime',$where,'id desc',true);
		
		if ($rs['list'])
		{
			$total_money = $this->loadModel('orderFtf')->where(array('usid'=>$this->usid,'status'=>'1'))->sum('money');
			$rs['total_money'] = $total_money?$total_money:0;
		}
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	public function add()
	{
		$debug = 0 ;//0正常 ，1测试
		if($debug)
		{
			$usids = array(164,62329,93360); //相应的用户ID
			//$this->vaild_params('is_empty',$open,$this->usid.'---');	
			if(!in_array($this->usid, $usids))
			{
				$this->vaild_params('is_empty',$open,'系统维护中，请稍后再试!');			
			}		
		
		}
        $h=intval(date('G'));
//        if($h > 21 || $h < 8){
//            $this->vaild_params('is_empty',$open,'22:00到8:00无法进行交易!');
//        }
		$verify = I('verify',0); //首笔验证订单
//		$chk = I('chk',0);
//		$this->vaild_params('is_empty',$chk,'请升级到新版本!');
        $err_msg = $this->loadModel('userSaler')->checkStatus($this->usid);
        if ($err_msg)
        {
            $this->response(PARAMS_ERROR,$err_msg);
        }
		$data = $this->get_params(array('money','message'));
		if ($data['money']!='0.01'){
			$this->vaild_params('is_empty',$data['money'],'请填写收款金额');
			//$this->vaild_params('is_empty',($data['money']%100),'收款金额不能为整百');
			$this->vaild_params('is_empty',($data['money']%1000%111),'收款金额后三位不能相同'); 
		}
		//限额判断
        $user = $this->loadModel('userSaler')->getInfoByid($this->usid);
        $today = strtotime(date("Y-m-d",time()));
        $field = array(
            'pay_type'=>'id as ptid,name as pt_name,api',
            'level_pay_limit'=>'lfid,min,max,day_min,day_max',
        );
        $join = array();
        $join[] = array('level_pay_limit','id','ptid');
        $where = array(
            'pay_type.status' => 1,
            'pay_type.is_show' => 1,
            'level_pay_limit.lfid' => $user['lfid'],
        );
        $pt_info = $this->loadModel('payType')->getJoinList($field,$join,$where,'sort desc,ptid asc');
        if ($pt_info['list'])
        {
            foreach($pt_info['list'] as $key => $val)
            {
                if ($val['day_max'] > 0)
                {
                    $where = array(
                        'paytime' =>array('egt',$today),
                        'usid' => $this->usid,
                        'status' => 1,
                    );
//                    $where['addtime'][] = array('egt',strtotime(date('Y-m-d')));
//                    $where['addtime'][] = array('lt',strtotime(date('Y-m-d')) + 3600*24);
                    $day_money = D('OrderFtf')->where($where)->sum('money');
                    $day_money = $day_money?$day_money:0;
                    $limit = $val['day_max'] - $day_money;//当日剩余额度
                    $open ='';
                    //eblog("订单生成前 -usid= {$user['id']} userlfid= {$user['lfid']}- 当日最大额度= {$val['day_max']}- 当日剩余额度= {$limit}-当日已付款= {$day_money}-",'订单待生成','creatOrder_'.date("Ymd"));
                    if (($limit-$data['money'])<0){
                        $this->vaild_params('is_empty',$open,'您已超过当日限额!');
                    }
                }
            }
        }

//		$data['desc'] = '验证支付';
		$data['usid'] = $this->usid;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['sn']	= $this->loadModel('orderFtf')->createSn($this->usid);
		$data['addtime'] = time();

		$account = $this->loadModel('userSalerAccountTotal')->getInfo('normal_usable',array('usid'=>$this->usid));
		$data['total'] = $account['normal_usable']?$account['normal_usable']:0;
		
		$rs = $this->model['orderFtf']->add($data);
		$response = $rs?array('sn'=>$data['sn']):false;

		$this->apiOut($response);
	}
	
	public function info()
	{
		$sn = I('sn');
		$this->vaild_params('is_empty',$sn,'请填写要查询的订单号');
		$fields = 'usid,sn,money,desc,status,addtime,paytime';
		
		$order = $this->loadModel('orderFtf')->getInfo($fields,array('sn'=>$sn));
		
		if ($order['usid'] != $this->usid)
		{
			$this->response(DATA_EMPTY,'您无法访问该订单');
		}
		unset($order['usid']);
		$this->apiOut($order);	
	}

}
