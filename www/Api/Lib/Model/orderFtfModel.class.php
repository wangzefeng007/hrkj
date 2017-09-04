<?php
set_time_limit(0);
class orderFtfModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}

    public function createSn($usid)
    {

        return 'OF'.substr(date("YmdHis"), -12).str_pad(substr($usid, -3),3,0,STR_PAD_LEFT).rand(100,999);
    }

	/*
	*	付款后流程
	*	@todo	此流程太过复杂，计算步骤太多，后续需更改为异步方式
	*/
	public function pay($order)
	{
		$time = time();
		$this->startTrans();
        //支付结果插入
       /*
        $data = array('order_sn'=>$order['sn'],'addtime'=>$time);
        if (!D('orderPayresult')->add($data))
        {
            eblog("支付结果插入111 - usinfo",$data,'error_payback_'.date("Ymd"));
            $this->rollback();
            return false;
        }
        */
//		$data = array('status'=>1,'paytime'=>$time,'memo'=>$payinfo['memo']);
//		$rs = D('orderPaylog')->update($data,array('id'=>$payinfo['id']));
//		if (!$rs)
//		{
//            eblog("付款完成后支付结果插入 - usinfo",$data,'error_payback_'.date("Ymd"));
//			$this->rollback();
//			return false;
//		}

		//按通道更新商户总额
		$user_account = D('userSalerAccount')->getInfo("*",array('ptid'=>$order['ptid'],'usid'=>$order['usid']));
		$data = array();
		$data['ptid'] = $order['ptid'];
		$data['money'] = $user_account['money']+$order['money'];
		$data['total_money'] = $user_account['total_money']+$order['money'];		
		if (!$user_account)
		{
			$data['usid'] = $order['usid'];
			$rs = D('userSalerAccount')->add($data);
		}
		else
		{
			$rs = D('userSalerAccount')->update($data,array('id'=>$user_account['id']));
		}
		if (!$rs)
		{
            eblog("付款完成后按通道更新商户总额 - usinfo",$data,'error_payback_'.date("Ymd"));
//			$this->rollback();
//			return false;
		}
		
		//无通道更新商户总额
		$user_saler_account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$order['usid']));
		$total = 0.00;
		if (!$user_saler_account_total)
		{
			
			$data = array('usid'=>$order['usid'],
									'ftf_total'=>$order['money'],
									'total_usable'=>$order['money'],
									'normal_usable'=>$order['money'],
									'normal_total'=>$order['money']);
			$rs = D('userSalerAccountTotal')->add($data);
		}
		else
		{
			$data = array('ftf_total'=>$user_saler_account_total['ftf_total']+$order['money'],
									'total_usable'=>$user_saler_account_total['total_usable']+$order['money'],
									'normal_usable'=>$user_saler_account_total['normal_usable']+$order['money'],
									'normal_total'=>$user_saler_account_total['normal_total']+$order['money']);
			$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$order['usid']));
		}
		if (!$rs)
		{
            eblog("付款完成后无通道更新商户总额 - usinfo",$order,'error_payback_'.date("Ymd"));
//			$this->rollback();
//			return false;
		}
		
		//更新订单信息
		$data = array();
		$data['status'] = 1;
//		$data['ptid'] = $payinfo['ptid'];
//		$data['pt_name'] = $payinfo['pt_name'];
		$data['paytime'] = $time;
		$data['total'] = $user_saler_account_total['total_usable']+$order['money'];
		$rs = $this->update($data,array('sn'=>$order['sn']));
		if (!$rs)
		{
            eblog("付款完成后更新订单信息 - usinfo",$data,'error_payback_'.date("Ymd"));
			$this->rollback();
			return false;
		}

		//增加收入记录
		$data = array();
		$data['usid'] = $order['usid'];
		$data['us_name'] = $order['us_name'];
		$data['order_sn'] = $order['sn'];
		$data['order_type'] = 2;
		$data['type'] = INCOME_FTF;
		$data['ptid'] = $order['ptid'];
		$data['pt_name'] = $order['pt_name'];
		$data['order_money'] = $order['money'];
		$data['money'] = $order['money'];
		$data['total'] = $user_saler_account_total['total_usable']+$order['money'];
		$data['status'] = 1;
		$data['addtime'] = $time;
		$data['unfreezetime'] = $time;
		$rs = D('accountUserSalerIncome')->add($data);
		if (!$rs)
		{
            eblog("付款完成后增加收入记录 - usinfo",$data,'error_payback_'.date("Ymd"));
//			$this->rollback();
//			return false;
		}
		
		//增加流水
		$data = array();
		$data['usid'] = $order['usid'];
		$data['usicid'] = $rs;
		$data['order_sn'] = $order['sn'];
		$data['type'] = 1;
		$data['money'] = $order['money'];
		$data['total'] = $user_saler_account_total['total_usable']+$order['money'];
		// $data['remark'] = '当面收款';
		$data['remark'] = $order['pt_name'];
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
            eblog("付款完成后增加流水失败 - usinfo",$data,'error_payback_'.date("Ymd"));
//			$this->rollback();
//			return false;
		}
		$this->commit();
		return true;
	}
}
