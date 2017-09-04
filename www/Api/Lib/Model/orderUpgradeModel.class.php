<?php
class orderUpgradeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	付款后流程
	*/
	public function pay($order)
	{
		$time = time();
		$this->startTrans();
//		$data = array('status'=>1,'paytime'=>$time);
//		$rs = D('orderPaylog')->update($data,array('id'=>$payinfo['id']));
//		if (!$rs)
//		{
//			$this->rollback();
//			return false;
//		}
		//更新订单信息
		$data = array();
		$data['status'] = 1;
		$data['ptid'] = $order['ptid'];
		$data['pt_name'] = $order['pt_name'];
		$data['paytime'] = $time;
		$rs = $this->update($data,array('sn'=>$order['sn']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//上级商户收入中插入收入明细
		$split_list = $this->split($order);
		if ($split_list)
		{
			$split_total_money = 0;
			foreach ($split_list as $split)
			{
				$split_total_money += $split['money'];
				$data = array();
				$data['usid'] = $split['usid'];
				$data['from_usid'] = $order['usid'];
				$data['us_name'] = $split['us_name'];
				$data['order_sn'] = $order['sn'];
				$data['order_type'] = 3;
				$data['type'] = INCOME_UPGRADE_SPLIT;
				$data['ptid'] = $order['ptid'];
				$data['pt_name'] = $order['pt_name'];
				$data['order_money'] = $order['money'];
				$data['money'] = $split['money'];
				$data['status'] = 1;
				$data['addtime'] = $time;
				$data['unfreezetime'] = $time;
				$rs = D('accountUserSalerIncome')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				
				$account = D('userSalerAccountTotal')->getInfo("*",array('usid'=>$split['usid']));
				//增加流水
				$data = array();
				$data['usid'] = $split['usid'];
				$data['usicid'] = $rs;
				$data['order_sn'] = $order['sn'];
				$data['type'] = 1;
				$data['money'] = $split['money'];
				$data['total'] = $account['total_usable']+
											$split['money'];
				$data['remark'] = '升级分润';
				$data['addtime'] = $time;
				$rs = D('accountUserSalerWater')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				
				//更新上级商户分润汇总			
				$data = array();
				if ($account)
				{
					$data['split_total'] = $account['split_total'] + $split['money'];
					$data['split_usable'] = $account['split_usable'] + $split['money'];
					$data['upgrade_split_total'] = $account['upgrade_split_total'] + $split['money'];
					$data['total_usable'] = $account['total_usable'] + $split['money'];
					$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$split['usid']));
				}
				else
				{
					$data['usid'] = $split['usid'];
					$data['split_total'] = $split['money'];
					$data['split_usable'] = $split['money'];
					$data['upgrade_split_total'] = $split['money'];
					$data['total_usable'] = $split['money'];
					$rs = D('userSalerAccountTotal')->add($data);
				}
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
			}
		}
		
		//其他上级商户同时升级
		$parent = D('userSaler')->getParent($order['usid']);
		if ($parent)
		{
			$order_new_lf = D('levelFee')->getInfoByid($order['lfid_new']);	//获取目标级别的level等级
			foreach($parent as $value)
			{
				if ($value['level'] < $order_new_lf['level'])
				{
					$data = $order;
					unset($data['id']);
					$data['status'] = 1;
					$data['paytime'] = $time;
					$data['usid'] = $value['id'];
					$data['from_usid'] = $order['usid'];
					$data['sn'] = 'OUA'.substr($order['sn'],3);
					$data['us_name'] = $value['name'];
					$data['us_mobile'] = $value['mobile'];
					$rs = $this->add($data);
					if (!$rs)
					{
						$this->rollback();
						return false;
					}
					$rs = D('userSaler')->update(array('lfid'=>$order['lfid_new']),array('id'=>$value['id']));
					if (!$rs)
					{
						$this->rollback();
						return false;
					}
				}
			}
		}
		
		//用户自身等级变更
		$rs = D('userSaler')->update(array('lfid'=>$order['lfid_new']),array('id'=>$order['usid']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		
		//平台收入
		$data = array();
		$data['type'] = PLATFORM_INCOME_UPGRADE;
		$data['order_sn'] = $order['sn'];
		$data['order_money'] = $order['money'];
		$data['ptid'] = $order['ptid'];
		// $data['money'] = ($order['money'] - $split_total_money)+$order['deposit'];
		$data['money'] = ($order['money'] - $split_total_money);
		
		$data['usid'] = $order['usid'];
		$data['us_name'] = $order['us_name'];
		$data['us_mobile'] = $order['us_mobile'];
		$data['lfid'] = $order['lfid_old'];
		$data['income_money'] = $data['money'];
		
		$data['addtime'] = $time;
		$rs = D('accountPlatformIncome')->add($data);
		if (!$rs)
		{
			$this->rollback();
			return false;
		}

		//更新最后升级时间
		$rs = D('userSaler')->update(array('upgradetime'=>$time),array('id'=>$order['usid']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		$this->commit();
		return true;
	}
	
	/*
	*	订单分润计算
	*	@param 			array			$order		订单信息
	*	@return				array			$split			
	*/
	private function split($order)
	{
		$split = array();
		$usid = $order['usid'];
		$order_new_lf = D('levelFee')->getInfoByid($order['lfid_new']);	//获取目标级别的level等级
		do 
		{
			$parent = D('userSaler')->getParent($usid,true);
			$usid = $parent[0]['id'];
		}
		while ($parent && $parent[0]['level'] < $order_new_lf["level"]);

		if ($parent)
		{
			//第一个费率>=目标级别的上级商户获取分润
			$data['usid'] = $parent[0]['id'];
			$data['us_name'] = $parent[0]['name'];
			$data['money'] = ($order['money']-$order['deposit'])*$order['lf_rate'];
			$split[] = $data;
			$flag = array(
				4 => 1,
				5 => 1,
			);
			foreach($parent as $us)
			{
				if (($us['level'] == 4 && $flag[4]) || ($us['level'] == 5 && $flag[5]))
				{
					$rs_lf = D('levelFee')->getInfo('*',array('level' => $us['level']));
					$data = array();
					$data['usid'] = $us['id'];
					$data['us_name'] = $us['name'];
					$data['money'] = ($order['money']-$order['deposit'])*$rs_lf['split_rate'];
					$split[] = $data;
					$flag[$us['level']] = 0;
				}
			}
	
		}
		return $split;		
	}

	/*
	*	满足条件自动升级
	*	@param 			array			$usInfo			用户信息
	*	@param 			int				$lfid_new		用户即将升到的等级
	*	@param 			string			$type			条件达成类型 F-交易升级,C-推广升级
	*/
	public function autoUpgrade($usInfo,$lf_new,$type,$memo)
	{
		$time = time();
		$this->startTrans();
		$lf_old = D('levelFee')->getInfoByid($usInfo['lfid'],'id,name');
		$data = array();
		$data['sn'] = $this->createAutoUpgradeSn($type);
		$data['usid'] = $usInfo['id'];
		$data['us_name'] = $usInfo['name'];
		$data['us_mobile'] = $usInfo['mobile'];
		$data['lfid_old'] = $usInfo['lfid'];
		$data['lf_name_old'] = $lf_old['name'];
		$data['lfid_new'] = $lf_new['id'];
		$data['lf_name_new'] = $lf_new['name'];
		$data['status'] = 1;
		$data['memo'] = $memo;
		$data['addtime'] = $time;
		$data['paytime'] = $time;
		
		eblog("自动升级流程",'=================================================','auto_upgrade_'.date("Ymd"));
		eblog("自动升级流程 - data",$data,'auto_upgrade_'.date("Ymd"));
		$rs = $this->add($data);
		if (!$rs)
		{
			$this->rollback();
			eblog("自动升级流程 - 报错信息 - 升级主订单写入失败",'====================================','error_autoUp_'.date("Ymd"));
			eblog("自动升级流程 - data",$data,'error_autoUp_'.date("Ymd"));
			return false;
		}
		$order = $data;
		unset($order['memo']);

		//其他上级商户同时升级（） 作者：zf，去掉被动升级
        /*
		$parent = D('userSaler')->getParent($order['usid']);
		if ($parent)
		{
			eblog("自动升级流程 - 被动升级 - 上级商户列表 parent",$parent,'auto_upgrade_'.date("Ymd"));
			$order_new_lf = D('levelFee')->getInfoByid($order['lfid_new']);	//获取目标级别的level等级
			foreach($parent as $value)
			{
				if ($value['level'] < $order_new_lf['level'])
				{
					$data = $order;
					unset($data['id']);
					$data['status'] = 1;
					$data['paytime'] = $time;
					$data['usid'] = $value['id'];
					$data['from_usid'] = $order['usid'];
					$data['sn'] = 'OUA'.substr($order['sn'],3);
					$data['us_name'] = $value['name'];
					$data['us_mobile'] = $value['mobile'];
					$rs = $this->add($data);
					if (!$rs)
					{
						$this->rollback();
						eblog("自动升级流程 - 报错信息 - 被动升级订单写入失败",'====================================','error_autoUp_'.date("Ymd"));
						eblog("自动升级流程 - data",$data,'error_autoUp_'.date("Ymd"));
						return false;
					}
					$rs = D('userSaler')->update(array('lfid'=>$order['lfid_new']),array('id'=>$value['id']));
					if (!$rs)
					{
						$this->rollback();
						eblog("自动升级流程 - 报错信息 - 被动升级用户等级更新失败",'====================================','error_autoUp_'.date("Ymd"));
						eblog("自动升级流程 - order",$order,'error_autoUp_'.date("Ymd"));
						eblog("自动升级流程 - 上级商户value",$value,'error_autoUp_'.date("Ymd"));
						return false;
					}
				}
			}
		}
        */

        //用户自身等级变更
        $rs = D('userSaler')->update(array('lfid'=>$order['lfid_new']),array('id'=>$order['usid']));
        if (!$rs)
        {
            $this->rollback();
            eblog("自动升级流程 - 报错信息 - 主用户等级更新失败",'====================================','error_autoUp_'.date("Ymd"));
            eblog("自动升级流程 - order",$order,'error_autoUp_'.date("Ymd"));
            return false;
        }

        $this->commit();
        return true;
    }
    /*
    *	用户升级订单号生成
    */
	private function createAutoUpgradeSn($type)
	{
		return 'ou'.strtolower($type).date('YmdHis').rand(100,999);
	}
}
