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
	*	@paran			int			$lsid			代销等级id
	*	@param			array		$goods		订单商品信息,格式 array(upid=>array(array('gsid'=>'','price'=>'','num'=>'','upid'=>'','name'=>'','gpid'=>'')))
	*	@param			array		$receiver	订单收货人信息 ，array('name'=>'','mobile'=>'','province'=>'','city'=>'','area'=>'','address'=>'','message'=>'','weixin'=>'');
	*/
	function createOrder($usid,$lsid,$goods,$receiver)
	{
		$orders = array();
		$this->startTrans(); 		
		foreach($goods as $upid=>$item)
		{
			$sn = $this->createOrderSn();
			$money = $commission = 0.00;
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
					$rate = D('goodsCommission')->getRate($lsid,$g['gpid']);
					$commission += ($goods_money*$rate);
				}
			}
			$order = $receiver;
			$order['sn'] = $sn;
			$order['money'] = $money;
			$order['commission'] = $commission;
			$order['usid'] = $usid;
			$order['upid'] = $upid;
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
		$fields = "usid,sn,money,name,mobile,address,weixin,message,logistics,logistics_no,status,addtime,paytime,sendtime";
		$where = array('sn'=>$sn);
		$order = $this->getInfo($fields,$where);
		if (!$order) return false;
		$goods  = D('orderShopGoods')->getList('gsid,name,price,num',array('sn'=>$sn));
		$order['goods'] = $goods['list'];
		return $order;
	}
	
	//订单付费
	public function pay($order,$payinfo)
	{
		$time = time();
		$this->startTrans();
		$data = array('status'=>1,'paytime'=>$time);
		$rs = D('orderPaylog')->update($data,array('order_sn'=>$payinfo['order_sn']));
		if (!$rs)
		{
			$this->rollback();
			return false;
		}
		//更新订单信息
		$data = array();
		$data['status'] = 1;
		$data['status_pay'] = 1;
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
			$data['type'] = 1;			
			$data['status'] = 1;		
			$data['money'] = $order['money'];
			$data['unfreezetime'] = $time;
			
			//收入明细
			$rs = D('accountUserSalerIncome')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			
			//更新商户总额
			$rs = D('userSalerAccount')->change($order['usid'],$payinfo['ptid'],$data['money']);
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
			$data['type'] = 4;			
			$data['status'] = 0;		
			$data['money'] = $order['commission'] - $split['count'];
			$rs = D('accountUserSalerIncome')->add($data);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			//更新商户总额
			$rs = D('userSalerAccount')->change($order['usid'],$payinfo['ptid'],$data['money']);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			
			//佣金分润收入入库
			foreach($split['list'] as $value)
			{
				$data = array(); 
				$data = $value;
				$data['order_money'] = $order['money'];
				$data['status'] = 0;
				$data['addtime'] = $time;
				//收入明细插入
				$rs = D('accountUserSalerIncome')->add($data);
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				//分润收入汇总更新
				$user_account_split = D('userSalerAccountSplit')->getInfo('id',array('usid'=>$value['usid']));
				$data = array('usid'=>$value['usid'],'money'=>$value['money']);
				$rs = $user_account_split?D('userSalerAccountSplit')->add($data):D('userSalerAccountSplit')->update($data,array('usid'=>$value['usid']));
				if (!$rs)
				{
					$this->rollback();
					return false;
				}
				
			}

			// 供应商收入
			$data = array();
			$data['upid'] = $order['upid'];
			$data['up_name'] = $order['up_name'];
			$data['order_sn'] = $order['sn'];
			$data['order_money'] = $order['money'];
			$data['money'] = $order['money']-$order['commission']-$order['rake'];
			$data['status'] = 0;
			$data['addtime'] = $time;
			$rs = D('accountUserProviderIncome')->add($data_provider);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}
			$rs = D('userProviderModel')->change($data['upid'],$data['money']);
			if (!$rs)
			{
				$this->rollback();
				return false;
			}

			//平台收入
			$data = array();
			$data['type'] = 1;
			$data['order_sn'] = $order['sn'];
			$data['order_money'] = $order['money'];
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
		
		$parent = D('userSaler')->getParent($order['usid'],true);
		if ($parent)
		{
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
