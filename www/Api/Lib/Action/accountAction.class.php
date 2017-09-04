<?php
class accountAction extends baseAction
{
	//账单列表(收款,提现)
	public function accountList()
	{
		$where = array('usid'=>$this->usid);
		$fields = "id,order_sn,type,money,total,real_money,remark,cash_fee,addtime,status";
		$rs = $this->loadModel('accountUserSalerWater')->getList($fields,$where," addtime DESC ",true);
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	//分润列表
	public function splitList()
	{
		$type = I('type');
		$this->vaild_params('is_empty',$type,'错误,分润类型不能为空!');
		
		$_params = $this->get_params(array('search','starttime','endtime'));
		$where = array('usid'=>$this->usid);
		
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);

		$where['type'] = $type;
		if ($type == 3)
		{
			if ($_params['search'])
			{
				$where['user_saler.mobile'] = array('like',"%{$_params['search']}%");
			}
			$fields = array();
			$fields['account_user_saler_income'] = 'id,money,addtime,status';
			$fields['user_saler'] = 'name as us_name,mobile as us_mobile,lfid';
			$join[] = array('user_saler','from_usid','id');
			$rs = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'addtime desc',true);

			$fields = "id,name";
			$rs_lf = $this->loadModel('levelFee')->getList($fields,'',"id",false);
			$rs_lf = reset_array_key($rs_lf['list'],'id','name');
			
			if ($rs['list'])
			{
				foreach ($rs['list'] as $key => &$val)
				{
					$val['lf_name'] = $rs_lf[$val['lfid']];
					$val['us_name'] = hide_name($val['us_name']);
				}
			}
		}
		elseif ($type == 6)
		{
			if ($_params['search'])
			{
				$where['order_upgrade.us_mobile'] = array('like',"%{$_params['search']}%");
			}
			$fields = array();
			$fields['account_user_saler_income'] = 'id,money,addtime,status';
			$fields['order_upgrade'] = 'us_name,us_mobile,lfid_new as lfid,money as upgrade_money,lf_name_old,lf_name_new';
			$join[] = array('order_upgrade','order_sn','sn');
			$rs = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'addtime desc',true);

			if ($rs['list'])
			{
				foreach ($rs['list'] as $key => &$val)
				{
					$val['upgrade_memo'] = $val['lf_name_old'].'升'.$val['lf_name_new'];
					$val['us_name'] = substr($val['us_name'],0,3) . strcpy('*',strlen($val['us_name'])/3-1);
					$val['us_mobile'] = substr($val['us_mobile'],0,3) . strcpy('*',4) . substr($val['us_mobile'],-4);
				}
			}
		}
		
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	//收入列表(收款,店铺,分润)
	public function income()
	{
		$type = intval($type);
		$where = array('usid'=>$this->usid);
		if ($type) $where['type'] = $type;
		$fields = "id,order_sn,order_type,type,ptid,pt_name,order_money,money,addtime";
		$rs = $this->loadModel('accountUserSalerIncome')->getList($fields,$where," id DESC ",true);
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	//提现列表
	public function cashList()
	{
		$type = I('type');
		$this->vaild_params('is_empty',$type,'错误,提现类型不能为空!');
		
		$_params = $this->get_params(array('starttime','endtime'));
		$where = $this->get_params(array('ptid','ctid','status'));
		$where = array_diff($where,array('',0));
		$where['usid'] = $this->usid;
		$where['type'] = $type;

		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);

		$fields = "id,sn,ctid,ct_name,ptid,pt_name,money,total,real_money,fee_rate,fee_static,status,addtime,dispostime";
		$rs = $this->loadModel('accountUserSalerCash')->getList($fields,$where," id DESC ",true);
		
		if ($rs['list'])
		{
			// foreach ($rs['list'] as $key => &$val)
			// {
				// $val['real_money'] = round($val['money']-$val['fee_static'] - $val['fee_rate']*$val['money'],2);
			// }
			$total_money = $this->loadModel('accountUserSalerCash')->where(array('usid'=>$this->usid,'type'=>$type,'status'=>'1'))->sum('money');
			$rs['total_money'] = $total_money?$total_money:0;
		}
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}
	
	/*
	*	普通提现申请,即分通道提现
	*	@todo 待完善
	*/
	public function cash()
	{
		$lock = 'cash_lock_'.$this->usid;
		if (S($lock))
		{
			$this->response(INTERNAL_ERROR,'操作太过频繁，请稍后再试');
		}
		S($lock,true,5);	
		$data = $this->get_params(array('ptid','ctid','money'));	
		$this->vaild_params('is_empty',$data['ptid'],'请选择您要提现的金额类型');
		$this->vaild_params('is_empty',$data['ctid'],'请选择提现方式');
		$this->vaild_params('is_numeric',$data['money'],'请填写要提现的金额');
		
		$user_account = $this->loadModel('userSalerAccount')->getInfo("money",array('ptid'=>$data['ptid'],array('usid'=>$this->usid)));	
		$this->vaild_params('compare',array($user_account['money'],$data['money'],'>='),'您的余额不足，无法提现');
		
		$lfid = $this->usinfo['lfid'];
		
		//提现通道限额检测
		$result = $this->loadModel('userSaler')->chkCashLimit($this->usid,$data['ptid'],$data['ctid'],$lfid,$data['money']);
		if (!is_array($result))
		{
			$this->response(DATA_EMPTY,$result);
		}
		else
		{
			$cash = $result;
		}

		$data['pt_name'] = $cash['pt_name'];
		$data['ct_name'] = $cash['ct_name'];
		$data['usid'] = $this->usid;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['fee_rate'] = $cash['fee_rate'];
		$data['fee_static'] = $cash['fee_static'];
		$data['status'] = 0;
		$data['addtime'] = time();
		$data['type'] = CASH_NORMAL;
		$rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
		if ($rs_cash)
		{
			//发送异步交易查询,并发送相关消息
			A('Api://processMsg')->msg('CashMsg',$rs_cash);
		}
		$rs = $rs_cash?true:false;
		S($lock,null);
		$this->apiOut($rs,false);
	}
	
	//分润提现
	public function splitCash()
	{
		$h=intval(date('G'));
		//$this->vaild_params('is_empty',$opentime,$h);
		if($h > 21 || $h < 8){
			$this->vaild_params('is_empty',$open,'22:00到8:00无法进行提现!');
		}
		$lock = 'cash_lock_'.$this->usid;
		if (S($lock))
		{
			$this->response(INTERNAL_ERROR,'操作太过频繁，请稍后再试');
		}
		S($lock,true,5);	
		$_params = $this->get_params(array('ctid','money'));
		$this->vaild_params('is_empty',$_params['ctid'],'请选择提现方式');
		$this->vaild_params('is_numeric',$_params['money'],'请填写提现金额');
		
		$account = $this->loadModel('userSalerAccountTotal')->getInfo("*",array('usid'=>$this->usid));
		$this->vaild_params('compare',array($account['split_usable'],$_params['money'],'>='),'您的余额不足，无法提现');
		
		$lfid = $this->usinfo['lfid'];
		
		//提现通道限额检测
		$result = $this->loadModel('userSaler')->chkCashLimit($this->usid,-1,$_params['ctid'],$lfid,$_params['money']);
		if (!is_array($result))
		{
			$this->response(DATA_EMPTY,$result);
		}
		else
		{
			$cash = $result;
		}
		
		$ct_info = $this->loadModel('cashType')->getInfoByid($_params['ctid']);
		$this->vaild_params('is_empty',$ct_info,'无效的提现方式');

		$ct_info['fee_static'] = $cash['fee_static'];
		$rs_cash = $this->loadModel('userSalerAccountTotal')->splitCash($this->usid,$this->usinfo['name'],$ct_info,$_params['money'],$account);
		S($lock,null);
		if($rs_cash)
		{
			$this->single_pay($rs_cash['real_money'],$rs_cash['sn'],$this->usinfo);
			$rs = true ;
		}
		else 
		{
			$rs = false ;
		}			
		
		$this->apiOut($rs,false);		
	}
	/*
	*	普通提现申请,即分通道提现
	*	@todo 待完善
	*/
	public function businessCash()
	{
		$h=intval(date('G'));
		//$this->vaild_params('is_empty',$opentime,$h);
		if($h > 21 || $h < 8){
			$this->vaild_params('is_empty',$open,'22:00到8:00无法进行提现!');
		}
		$lock = 'cash_lock_'.$this->usid;
		if (S($lock))
		{
			$this->response(INTERNAL_ERROR,'操作太过频繁，请稍后再试');
		}
		S($lock,true,5);	
		$data = $this->get_params(array('money'));	
		$pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","","qrcode::pay")));
		$data['ptid'] = $pay_type['id'];
		$data['ctid'] = 10 ;
		$this->vaild_params('is_empty',$data['ptid'],'请选择您要提现的金额类型');
		$this->vaild_params('is_empty',$data['ctid'],'请选择提现方式');
		$this->vaild_params('is_numeric',$data['money'],'请填写要提现的金额');
		
		$user_account = $this->loadModel('userSalerAccountTotal')->getInfo("business_usable",array('usid'=>$this->usid));	
		$this->vaild_params('compare',array($user_account['business_usable'],$data['money'],'>='),'您的余额不足，无法提现');
		
		$lfid = $this->usinfo['lfid'];
		
		//提现通道限额检测
		$result = $this->loadModel('userSaler')->chkCashLimit($this->usid,$data['ptid'],$data['ctid'],$lfid,$data['money']);
		if (!is_array($result))
		{
			$this->response(DATA_EMPTY,$result);
		}
		else
		{
			$cash = $result;
		}

		$data['pt_name'] = $cash['pt_name'];
		$data['ct_name'] = $cash['ct_name'];
		$data['usid'] = $this->usid;
		$data['us_name'] = $this->usinfo['name'];
		$data['us_mobile'] = $this->usinfo['mobile'];
		$data['fee_rate'] = $cash['fee_rate'];
		$data['fee_static'] = $cash['fee_static'];
		$data['status'] = 0;
		$data['addtime'] = time();
		$data['type'] = CASH_NORMAL;
		$rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
		if($rs_cash)
		{
			$this->single_pay($rs_cash['real_money'],$rs_cash['sn'],$this->usinfo,'business');
			A('Api://processMsg')->msg('CashMsg',$rs_cash);
			$rs = true ;
			$business_usable = $user_account['business_usable'] - $data['money'];
			$dataTotal = array('business_usable'=>$business_usable);
			$rsTotal = D('userSalerAccountTotal')->update($dataTotal,array('usid'=>$this->usid));
		}
		else 
		{
			$rs = false ;
		}	
		//$rs = $rs_cash?true:false;
		S($lock,null);
		$this->apiOut($rs,false);
	}
	//佣金提现
	public function commissionCash()
	{
		$lock = 'cash_lock_'.$this->usid;
		if (S($lock))
		{
			$this->response(INTERNAL_ERROR,'操作太过频繁，请稍后再试');
		}
		S($lock,true,5);	
		$_params = $this->get_params(array('ctid','money'));
		$this->vaild_params('is_empty',$_params['ctid'],'请选择提现方式');
		$this->vaild_params('is_numeric',$_params['money'],'请填写提现金额');
		$ct_info = $this->loadModel('cashType')->getInfoByid($_params['ctid']);
		$this->vaild_params('is_empty',$ct_info,'无效的提现方式');
		$this->vaild_params('compare',array($ct_info['max'],$_params['money']),'提现金额超出额度上限，请重新填写');
		$this->vaild_params('compare',array($_params['money'],$ct_info['min']),'提现金额超出额度下限，请重新填写');	
		$account = $this->loadModel('userSalerAccountTotal')->getInfo("*",array('usid'=>$this->usid));
		$this->vaild_params('compare',array($account['commission_usable'],$_params['money'],'>='),'您的余额不足，无法提现');
		$rs = $this->loadModel('userSalerAccountTotal')->commissionCash($this->usid,$this->usinfo['name'],$ct_info,$_params['money'],$account);
		S($lock,null);
		$this->apiOut($rs,false);
	}
    public function test(){
        $url = SINGLE_PAY_URL;var_dump($url);exit;
    }
	public function  single_pay($money,$serialNo,$dataUser = array(),$type = 'split') {          
		
		//$serialNo = date('YmdHis', time()) . rand_num(10, true);
        $url = SINGLE_PAY_URL;
        $postData = array(
            'trainID' => $serialNo,
            'cardNo' => $dataUser['card_no'],
            'bankNo' => $dataUser['bank_no'],
            'accBankName' => $dataUser['name'],
			'accBankTypeNo' => $dataUser['bank_type'],
            'transAmt' => $money * 100,
            'busiType' => 0,
            'accType' => 1,
            'resv' => '预留'            
        );  
        eblog($type."- 代付数据",$postData,'singlepay_'.date("Ymd"));
        $ansData = $this->httpGet($url, $postData); 
        return $this->callback($ansData,$type);
    }
	/*
     * 代付返回结果处理
     */
    function callback($respContent,$type) {
        $data = json_decode($respContent, true);
        $serialNo = $data['SEQ'];          
        eblog($type."- 代付数据",$data,'singlepay_'.date("Ymd"));    
        if($data['RES_CODE'] == '00')
        {
        	if ($serialNo)
			{
				$where = array();
				$where['status'] = 0;
				$where['sn'] = $serialNo;
				//更新状态
				$rs = $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
		
			}
			
			
        	return true;
        }
        else 
        {
        	if ($serialNo)
			{
				$where = array();
				$where['status'] = -1;
				$where['sn'] = $serialNo;
				//更新状态
				$rs = $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
		
			}
        	//eblog("银联APP异步 - ",'代付返回数据','single_pay_'.date("Ymd"));
        	return false;
        }        
    }
	/**
     * curl方法
     * @param $url
     * @return mixed
     */
	function httpGet($url, $postData='') {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        if(!empty($postData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;

    } 
    
}
