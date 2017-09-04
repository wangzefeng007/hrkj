<?php
class orderShopModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	创建订单		
	*	需要事务支撑，数据库必须使用innodb引擎
	*	@param			int			$usid			代销人员id
	*	@param			array		$usinfo			代销人员信息
	*	@paran			int			$lsid			代销等级id
	*	@param			array		$goods		订单商品信息,格式 array(upid=>array(array('gsid'=>'','price'=>'','num'=>'','upid'=>'','name'=>'','gpid'=>'')))
	*	@param			array		$receiver	订单收货人信息 ，array('name'=>'','mobile'=>'','province'=>'','city'=>'','area'=>'','address'=>'','message'=>'','weixin'=>'');
	*/
	function createOrder($usid,$usinfo,$lsid,$goods,$receiver)
	{
		$orders = array();
		$this->startTrans(); 		
		foreach($goods as $upid=>$item)
		{
			$sn = $this->createOrderSn();
			$money = $commission = $rake = 0.00;
			foreach($item as $g)
			{
				$data = $g;
				$data['os_sn'] = $sn;				
				$rs = D('orderShopGoods')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				$goods_money = $g['price']*$g['num'];
				$money += $goods_money;
				if ($upid>0)
				{
					// $rate = D('goodsCommission')->getRate($lsid,$g['gpid']);
					$commission += ($goods_money*$g['commission_rate']);
					$rake += ($goods_money*$g['platform_rate']);
				}
			}
			$order = $receiver;
			$order['sn'] = $sn;
			$order['money'] = $money;
			$order['commission'] = $commission;
			$order['rake'] = $rake;
			$order['usid'] = $usid;
			$order['us_name'] = $usinfo['name'];
			$order['upid'] = $upid;
			$order['goods'] = serialize($item);
			$order['addtime'] = time();
			$osid = $this->add($order);
			if (!$osid)
			{
				$this->rollback();
				return false;
			}
			$rs = D('orderShopGoods')->update(array('osid'=>$osid),array('os_sn'=>$sn));
			if ($rs === false)
			{
				$this->rollback();
				return false;
			}
			$orders[] = $sn;
		}
		$this->commit();
		return $orders;
	}
	
	//订单详细信息
	public function info($sn)
	{
		$fields = "usid,sn,money,name,mobile,province,city,area,address,weixin,message,logistics,logistics_fee,logistics_no,status,addtime,paytime,sendtime";
		$where = array('sn'=>$sn);
		$order = $this->getInfo($fields,$where);
		if (!$order) return false;
		$goods  = D('orderShopGoods')->getList('gsid,name,price,num,thumb',array('os_sn'=>$sn));
		$order['goods'] = $goods['list'];
		return $order;
	}
	
	//订单付费
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
		$data = array('status'=>1,'paytime'=>$time);
		$rs = D('orderPaylog')->update($data,array('order_sn'=>$payinfo['order_sn']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//订单商品库存减少与销量增加
		$order['goods'] = unserialize($order['goods']);
		foreach($order['goods'] as $value)
		{
			$goods = D('goods')->getInfoByid($value['gid'],'sale_count,stock');
			$data = array('sale_count'=>$goods['sale_count']+$value['num'],'stock'=>$goods['stock']-$value['num']);
			$rs = D('goods')->update($data,array('id'=>$value['gid']));
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
		}

		//更新订单信息
		$data = array();
		$data['status'] = ORDER_WAIT_SEND;
		$data['status_pay'] = PAY_PAY;
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'];
		$data['paytime'] = $time;
		$rs = $this->update($data,array('sn'=>$order['sn']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		
		//增加收入记录
		$data = array();
		$data['usid'] = $order['usid'];
		$data['us_name'] = $order['us_name'];
		$data['order_sn'] = $order['sn'];
		$data['order_type'] = 1;		
		$data['ptid'] = $payinfo['ptid'];
		$data['pt_name'] = $payinfo['pt_name'];
		$data['order_money'] = $order['money'];
		$data['addtime'] = $time;
		if (!$order['upid'])
		{
			$data['type'] = INCOME_SHOP;			
			$data['status'] = INCOM_STATUS_NORMAL;		
			$data['money'] = $order['money'];
			$data['unfreezetime'] = $time;
			
			//收入明细
			$rs = D('accountUserSalerIncome')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			$usicid = $rs;
			
			//分通道更新商户总额
			$rs = D('userSalerAccount')->income($order['usid'],$payinfo['ptid'],$data['money']);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			
			//无通道更新商户总额
			$user_saler_account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$order['usid']));
			$total = 0.00;
			if (!$user_saler_account_total)
			{
				
				$data = array('usid'=>$order['usid'],
										'shop_total'=>$order['money'],
										'total_usable'=>$order['money'],
										'normal_usable'=>$order['money'],
										'normal_total'=>$order['money']);
				$rs = D('userSalerAccountTotal')->add($data);
			}
			else
			{
				$data = array('shop_total'=>$user_saler_account_total['shop_total']+$order['money'],
										'total_usable'=>$user_saler_account_total['total_usable']+$order['money'],
										'normal_usable'=>$user_saler_account_total['normal_usable']+$order['money'],
										'normal_total'=>$user_saler_account_total['normal_total']+$order['money']);
				$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$order['usid']));
			}
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			
			//增加流水
			$data = array();
			$data['usid'] = $order['usid'];
			$data['usicid'] = $usicid;
			$data['type'] = 1;
			$data['money'] = $order['money'];
			$data['total'] = $user_saler_account_total['total_usable']+
										$order['money'];
			$data['remark'] = '店铺收款';
			$data['addtime'] = $time;
			$rs = D('accountUserSalerWater')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
		}
		else
		{
			//分润计算
			$split = $this->split($order);
			
			//代销商佣金收入
			$data['type'] = INCOME_COMMISSION;			
			$data['status'] = INCOME_STATUS_FREEZE;
			$data['from_upid'] = $order['upid'];	
			$commission_money = $data['money'] = $order['commission'] - $order['rake'] - $split['count'];
			$rs = D('accountUserSalerIncome')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			$usicid  = $rs;			
			
			
			//无通道更新商户总额,佣金尚未结算时，总额不变更
			/*
			$user_saler_account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$order['usid']));
			$total = 0.00;
			if (!$user_saler_account_total)
			{
				
				$data = array('usid'=>$order['usid'],
										'total_usable'=>$commission_money,
										'commission_total'=>$commission_money);
										//佣金待订单结算后统计为可用
										// 'commission_usable'=>$commission_money);
				$rs = D('userSalerAccountTotal')->add($data);
			}
			else
			{
				$data = array('total_usable'=>$user_saler_account_total['total_usable']+$commission_money,
										'commission_total'=>$user_saler_account_total['commission_total']+$commission_money);
										//佣金待订单结算后统计为可用
										// 'commission_usable'=>$user_saler_account_total['commission_usable']+$commission_money);
				$rs = D('userSalerAccountTotal')->update($data,array('usid'=>$order['usid']));
			}
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			*/
			
			
			//增加流水，佣金待结算后产生流水
			/*
			$data = array();
			$data['usid'] = $order['usid'];
			$data['usicid'] = $usicid;
			$data['type'] = 1;
			$data['money'] = $commission_money;
			$data['status'] = 0;
			//总额不变，佣金未到期
			$data['total'] = $user_saler_account_total['total_usable'];
										// $commission_money;
			$data['remark'] = '代销佣金';
			$data['addtime'] = $time;
			$rs = D('accountUserSalerWater')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			*/
			
			//更新商户总额
			/*
			$rs = D('userSalerAccount')->change($order['usid'],$payinfo['ptid'],$data['money']);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			*/
			
			//佣金分润收入入库
			foreach($split['list'] as $value)
			{
				//收入明细插入
				$data = array(); 
				$data['usid'] = $value['usid'];
				$data['us_name'] = $value['us_name'];
				$data['money'] = $value['money'];
				$data['from_upid'] = $order['upid'];
				$data['from_usid'] = $order['usid'];
				$data['order_money'] = $order['money'];
				$data['type'] = INCOME_COMMISSION_SPLIT;
				$data['status'] = INCOME_STATUS_FREEZE;
				$data['ptid'] = $payinfo['ptid'];
				$data['pt_name'] = $payinfo['pt_name'];
				$data['addtime'] = $time;				
				$rs = D('accountUserSalerIncome')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				// $usicid  = $rs;
				
				//增加流水，佣金分润待结算后产生流水
				/*
				$user_saler_account_total = D('userSalerAccountTotal')->getInfo('*',array('usid'=>$value['usid']));
				$data = array();
				$data['usid'] = $order['usid'];
				$data['usicid'] = $usicid;
				$data['type'] = 1;
				$data['money'] = $value['money'];
				$data['status'] = 0;
				//总额不变，佣金未到期
				$data['total'] = $user_saler_account_total['total_usable'];
											// $commission_money;
				$data['remark'] = '代销佣金分润';
				$data['addtime'] = $time;
				$rs = D('accountUserSalerWater')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				*/
				
			}

			// 供应商收入
			$data = array();
			$data['upid'] = $order['upid'];
			$data['up_name'] = $order['up_name'];
			$data['order_sn'] = $order['sn'];
			$data['order_money'] = $order['money'];
			$data['money'] = $order['money']-$order['commission'];
			$data['status'] = INCOME_STATUS_FREEZE;
			$data['ptid'] = $payinfo['ptid'];
			$data['pt_name'] = $payinfo['pt_name'];
			$data['addtime'] = $time;
			$rs = D('accountUserProviderIncome')->add($data);
			if (!$rs)
			{
			
				$this->rollback();		
				return false;
			}
			


			//平台收入
			$data = array();
			$data['type'] = PLATFORM_INCOME_COMMISSION;
			$data['order_sn'] = $order['sn'];
			$data['order_money'] = $order['money'];
			$data['ptid'] = $payinfo['ptid'];
			$data['money'] = $order['rake'];
			$data['addtime'] = $time;
			$rs = D('accountPlatformIncome')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}	
		}
		$this->commit();
		return true;
	}
	
	/*
	*	订单分润计算
	*	@param 			array			$order		订单信息
	*	@return				array			$split			分润费用	array('count'=>分润总额,'list'=>分润详情)
	*/
	private function split($order)
	{
		$split = array('count'=>0);
		
		//通过费用级别获取上级商户
		$parent = D('userSaler')->getParent($order['usid'],true);
		if ($parent)
		{
			foreach($parent as &$value)
			{
				$level_saler = D('levelSalerFeeRelation')->getLevelSalerBylfid($value['lfid']);
				$value['level'] = $level_saler['sort'];
			}
		
			if (!isset($order['goods']))
			{
				$goods  = D('orderShopGoods')->getList('gsid,name,price,num',array('sn'=>$order['sn']));
				$order['goods'] = $goods['list'];
			}
			$user_saler_level = D('userSaler')->getLevelfee($order['usid']);
			foreach($order['goods'] as $goods)
			{
				foreach($parent as $value)
				{
					$level_diff = abs($user_saler_level['level']-$value['level']);
					$money = $goods['price']*$goods['num']*$level_diff*0.01;
					if (!isset($split['list'][$value['id']]))
					{
						$split['list'][$value['id']] = array('usid'=>$value['id'],'us_name'=>$value['name'],'money'=>$money);
					}
					else
					{
						$split['list'][$value['id']]['money'] += $money;
					}
					$split['count'] += $money;
				}
			}
		}
		return $split;
	
		
	}
	
	
	private function createOrderSn()
	{
		return 'os'.substr(date("YmdHis"), -12).rand(100000,999999);
	}
}
