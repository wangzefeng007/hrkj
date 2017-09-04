<?php
class sumapayModel extends baseModel
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
	*	丰付支付 专属流程 - 支付金额归入分润
	*	@todo	此流程太过复杂，计算步骤太多，后续需更改为异步方式
	*/
	public function pay($order,$payinfo)
	{
		$time = time();
		$this->startTrans();
        //支付结果插入
        $data = array('order_sn'=>$order['sn'],'addtime'=>$time);
        if (!D('orderPayresult')->add($data))
        {
            $this->rollback();
            return false;
        }
        
        
		$data = array('status'=>1,'paytime'=>$time,'memo'=>$payinfo['memo']);
		$rs = D('orderPaylog')->update($data,array('id'=>$payinfo['id']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
				
		//按通道更新商户总额 - 丰付专属流程 取消通道写入!
		/* $user_account = D('userSalerAccount')->getInfo("*",array('ptid'=>$payinfo['ptid'],'usid'=>$order['usid']));
		$data = array();
		$data['ptid'] = $payinfo['ptid'];
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
			$this->rollback();
			return false;
		} */
		
		//无通道更新商户总额 - 丰付专属流程 交易金额写入普通分润金额!
		$user_saler_account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$order['usid']));
		$total = 0.00;
		if (!$user_saler_account_total)
		{
			
			$data = array('usid'=>$order['usid'],
									'split_total'=>$order['money'],
									'total_usable'=>$order['money'],
									'split_usable'=>$order['money'],
									'normal_split_total'=>$order['money']);
			$rs = D('userSalerAccountTotal')->add($data);
		}
		else
		{
			$data = array('split_total'=>$user_saler_account_total['split_total']+$order['money'],
									'total_usable'=>$user_saler_account_total['total_usable']+$order['money'],
									'split_usable'=>$user_saler_account_total['split_usable']+$order['money'],
									'normal_split_total'=>$user_saler_account_total['normal_split_total']+$order['money']);
			$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$order['usid']));
		}
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//更新订单信息
		$data = array();
		$data['status'] = 1;
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'].'-转分润';
		$data['paytime'] = $time;
		$data['total'] = $user_saler_account_total['total_usable']+$order['money'];
		$rs = D('orderFtf')->update($data,array('sn'=>$order['sn']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}

		//增加收入记录
		$data = array();
		$data['usid'] = $order['usid'];
		$data['from_usid'] = $order['usid'];
		$data['us_name'] = $order['us_name'];
		$data['order_sn'] = $order['sn'];
		$data['order_type'] = 2;
		$data['type'] = INCOME_SPLIT;	//由于订单金额转入分润,type改成普通分润
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'].'-转分润';
		$data['order_money'] = $order['money'];
		$data['money'] = $order['money'];
		$data['total'] = $user_saler_account_total['total_usable']+$order['money'];
		$data['status'] = 1;
		$data['addtime'] = $time;
		$data['unfreezetime'] = $time;
		$rs = D('accountUserSalerIncome')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
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
		$data['remark'] = $payinfo['pt_name'].'-转分润';
		$data['addtime'] = $time;
		$rs = D('accountUserSalerWater')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		$this->commit();
		
		return true;
	}
}
