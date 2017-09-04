<?php
class userSalerModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	检查手机号是否注册
	*/
	function checkRegister($mobile)
	{
		$rs = $this->getInfo('id',array('mobile'=>$mobile))?true:false;
		return $rs;
	}
	
	/*
	*	检查用户状态
	*/
	function checkStatus($usid)
	{
		$rs = $this->getInfo('id,status',array('id'=>$usid));
		switch ($rs['status']) {
			case '-1':
				$err_msg = '您的账号已被冻结,请联系客服!';
				break;
//			case '0':
//				$err_msg = '您的资料正在审核,请耐心等待!';
//				break;
			case '2':
				$err_msg = '您的资料还未完善,请先完善资料!';
                break;
            case '3':
                $err_msg = '您的资料审核失败,请先修改资料!';
				break;
		}
		return $err_msg;
	}

	/*
	*	用户费率级别信息
	*/
	function getLevelfee($usid)
	{
		$field = array('level_fee'=>'*');
		$join = array('level_fee','lfid','id');			
		$where = array('id'=>$usid);
		return $this->getJoinInfo($field,$join,$where);				
	}
	
	/*
	*	获取用户可升级的费率级别信息
	*/
	function getLevelfeeUp($usid)
	{
		$user_level = $this->getLevelfee($usid);
		$where = array();
		$where['fee_upgrade'] = array('GT',$user_level['fee_upgrade']);
		$where['status'] = 1;
		$where['is_update'] = 1;
		$rs = D('levelFee')->getList("*",$where," fee_upgrade ASC");
		return $rs['list'];
	}
	
	/*
	*	获取上级商户信息
	*	@param			int			$usid				商户id
	*	@param			bool		$skip_ecdb	是否跳过同级用户
	*/
	function getParent($usid,$skip_ecdb = false)
	{
		
		$parent = array();
		$levels = D('levelFee')->getLevels();
		$user = $this->getInfo('pid,lfid,depth_pid',array('id'=>$usid));
		$pids = explode("-",$user['depth_pid']);
		$fields = array('user_saler'=>'id,pid,mobile,name,lfid,province_id,city_id,area_id','level_fee'=>'level,type as lf_type,share_rate');
		$join = array('level_fee','lfid','id');
		$where = array('user_saler.id'=>array('in',$pids));
		if ($skip_ecdb) $where['lfid'] = array('neq',$user['lfid']);
		//必须逆向排序
		$parent = $this->getJoinList($fields,$join,$where,"INSTR('".$user['depth_pid']."',".C('DB_PREFIX')."user_saler.id) desc");

		$rs = array();
		//避免出现上级商户同级的情况
		if ($skip_ecdb)
		{			
			$level_flag = array();
			foreach($parent['list'] as $value)
			{
				$lfid = intval($value['lfid']);
				if (in_array($lfid,$level_flag,true)) continue;
				$level_flag[] = $lfid;
				$rs[] = $value;
			}
		}
		else
		{
			$rs = $parent['list'];
		}
		return $rs;		
	}
	
	/*
	*	获取下级商户等级信息
	*	@param			string			$under				是否所有下级(0-仅直属下级,1-遍历所有下级)
	*/
	function getChildLevels($usid,$under = 1 )
	{
		static $child = array();
		static $levels = array();
		if (empty($levels))
		{
			$rs = D('levelFee')->getList('id,name');
			foreach($rs['list'] as $value)
			{
				$levels[$value['id']] = $value;
			}
		}

		$rs = D('userSaler')->getList('id,lfid,status,pid,depth_pid',array('pid'=>$usid));
		if ($rs['list'])
		{
			foreach($rs['list'] as $value)
			{
				$value['level'] = $levels[$value['lfid']];
				$child[] = $value;
				if ($under == 1)
				{
					$this->getChildLevels($value['id']);
				}
			}
		}				
		return $child;
	}
	
	/*
	*	获取下级商户账户信息
	*/
	function getChildAccount($usid,$lfid)
	{
		$rs = $this->getlist('id as usid,name,mobile',array('pid'=>$usid,'lfid'=>$lfid),'id desc',true);
		if ($rs['list'])
		{
			$child =$ids = array();
			foreach($rs['list'] as $value)
			{
				$ids[] = $value['usid'];
				$child[] = $value;
			}
			$ids = implode(',',$ids);
			$account = D('accountUserSalerCash')->field('usid,sum(money) as account')->where(array('usid'=>array('IN',$ids)))->group('usid')->select();
			$account = reset_array_key($account,'usid');
			$split = D('accountUserSalerIncome')->field("from_usid,SUM(money) AS money")->where(array('usid'=>$usid,'from_usid'=>array('IN',$ids),'type'=>3))->group('from_usid')->select();
			$split = reset_array_key($split,'from_usid');
			foreach($child as $key=>$value)
			{
				$child[$key]['account'] = isset($account[$value['usid']])?$account[$value['usid']]['account']:0.00;
				$child[$key]['split'] = isset($split[$value['usid']])?$split[$value['usid']]['money']:0.00;
			}
		}
		$rs['list'] = $child;
		return $rs;
	}
	
	/*
	*	检测用户支付限额
	*/
	function chkPayLimit($order,$ptid,$lfid)
	{
		//仅对收款类型风控
		if (strtolower(substr($order['sn'],0,2)) == 'of')
		{
			//第一笔订单不风控
			$where = array(
				'usid' => $order['usid'],
				'status' => 1,
			);
			$rs = D('OrderFtf')->where($where)->find();
			
			if ($rs)
			{
				$limit = $this->getPayLimit($ptid,$lfid);
				if ($order['money'] < $limit['min'] && $limit['min'] > 0)
				{
					return "错误!单笔金额不能低于 {$limit['min']}元";
				}
				elseif ($order['money'] > $limit['max'] && $limit['min'] > 0)
				{
					return "错误!单笔金额不能高于 {$limit['max']}元";
				}
				if ($limit['day_max'] > 0)
				{
					$where = array(
						'usid' => $order['usid'],
						'status' => 1,
						'ptid' => $ptid,
					);
					$where['addtime'][] = array('egt',strtotime(date('Y-m-d')));
					$where['addtime'][] = array('lt',strtotime(date('Y-m-d')) + 3600*24);
					$day_money = D('OrderFtf')->where($where)->sum('money');
					$day_money = $day_money?$day_money:0;
					$day_limit_money = $limit['day_max'] - $day_money;
					if ($order['money'] > $day_limit_money)
					{
						return "错误!今日额度只剩 {$day_limit_money}元";
					}
				}
			}
		}
		return $err_msg;		
	}
	
	/*
	*	获取用户支付额度
	*/
	function getPayLimit($ptid,$lfid=0)
	{
		// $where = array('usid'=>$usid,'ptid'=>$ptid,'status'=>1);
		// $user_limit = D('userSalerAccountLimit')->getInfo('min,day_min,max,day_max',$where);
		// if ($user_limit) return $user_limit;
		$where = array('lfid'=>$lfid,'ptid'=>$ptid);
		$limit = D('levelPayLimit')->getInfo('min,max,day_min,day_max',$where);
		if ($limit) return $limit;
		$pt_limit = D('payType')->getInfoByid($ptid,'min,max,day_min,day_max');
		return $pt_limit;		
	}
	/*
	*	检测实时上下限额度限制
	*/
	function chkRealtimeLimit($usid,$ptid,$ctid,$lfid=0,$money)
	{
		if ($ptid != -1)
		{
			$pay_type = D('payType')->getInfoByid($ptid,'id,name');
			if (!$pay_type)
			{
				return '支付方式不存在';
			}
		}
		
		$cash_type = D('cashType')->getInfoByid($ctid,'id,name,fee,max,min');
		if (!$cash_type)
		{
			return '无效的提现方式';
		}
		
		$cash = D('levelCashFee')->getCashfee($lfid,$ptid,$ctid);
        eblog("微信支付 - fee_rate",'lfid='.$lfid.',ptid='.$ptid.',ctid'.$ctid,'wxpay_'.date("Ymd"));
		if (!$cash)
		{
			return '提现通道未开放';
		}
		
		if ($cash_type)
		{
			$limit['min'] = $cash_type['min'];
			$limit['max'] = $cash_type['max'];
		}
		if ($cash)
		{
			$limit['min'] = $cash['min']>0?$cash['min']:$limit['min'];
			$limit['max'] = $cash['max']>0?$cash['max']:$limit['max'];
			$limit['day_max'] = $cash['day_max']>0?$cash['day_max']:0;
		}
		/*
		if ($limit['min']>0 && $money < $limit['min'])
		{
			return '提现金额不能低于'.$limit['min'].'元，请重新填写';
		}
		if ($limit['max']>0 && $money > $limit['max'])
		{
			return '提现金额不能高于'.$limit['max'].'元，请重新填写';
		}
		*/
		if ($limit['day_max']>0)
		{
			$where = array(
				'usid' => $usid,
				'ptid' => $ptid,
				'ctid' => $ctid,
			);
			$where['addtime'][] = array('egt',strtotime(date('Y-m-d')));
			$where['addtime'][] = array('lt',strtotime(date('Y-m-d')) + 3600*24);
			$day_cash = D('accountUserSalerCash')->where($where)->sum('money');
			$day_cash = $day_cash?$day_cash:0;
			$day_limit_cash = $limit['day_max'] - $day_cash;
			
			//if ($money > $day_limit_cash)
			//{
			//	return '当日可提现额度只剩'.$day_limit_cash.'元，请重新填写';
			//}
		}
		
		$cash['pt_name'] = $pay_type['name'];
		$cash['ct_name'] = $cash_type['name'];
		return $cash;
	}
	/*
	*	检测提现上下限额度限制
	*/
	function chkCashLimit($usid,$ptid,$ctid,$lfid=0,$money)
	{
		if ($ptid != -1)
		{
			$pay_type = D('payType')->getInfoByid($ptid,'id,name');
			if (!$pay_type)
			{
				return '支付方式不存在';
			}
		}
		
		$cash_type = D('cashType')->getInfoByid($ctid,'id,name,fee,max,min');
		if (!$cash_type)
		{
			return '无效的提现方式';
		}
		
		$cash = D('levelCashFee')->getCashfee($lfid,$ptid,$ctid);
		if (!$cash)
		{
			return '提现通道未开放';
		}
		
		if ($cash_type)
		{
			$limit['min'] = $cash_type['min'];
			$limit['max'] = $cash_type['max'];
		}
		if ($cash)
		{
			$limit['min'] = $cash['min']>0?$cash['min']:$limit['min'];
			$limit['max'] = $cash['max']>0?$cash['max']:$limit['max'];
			$limit['day_max'] = $cash['day_max']>0?$cash['day_max']:0;
		}
		eblog("用户提现",'提现金额不能低于'.$limit['min'].'元，请重新填写','userSalerModel_'.date("Ymd"));
		if ($limit['min']>0 && $money < $limit['min'])
		{
			return '提现金额不能低于'.$limit['min'].'元，请重新填写';
		}
		if ($limit['max']>0 && $money > $limit['max'])
		{
			return '提现金额不能高于'.$limit['max'].'元，请重新填写';
		}
		if ($limit['day_max']>0)
		{
			$where = array(
				'usid' => $usid,
				'ptid' => $ptid,
				'ctid' => $ctid,
			);
			$where['addtime'][] = array('egt',strtotime(date('Y-m-d')));
			$where['addtime'][] = array('lt',strtotime(date('Y-m-d')) + 3600*24);
			$day_cash = D('accountUserSalerCash')->where($where)->sum('money');
			$day_cash = $day_cash?$day_cash:0;
			$day_limit_cash = $limit['day_max'] - $day_cash;
			
			if ($money > $day_limit_cash)
			{
				return '当日可提现额度只剩'.$day_limit_cash.'元，请重新填写';
			}
		}
		
		$cash['pt_name'] = $pay_type['name'];
		$cash['ct_name'] = $cash_type['name'];
		return $cash;
	}
    
	/*
	*	获取提现上下限限制
	*/
	function getCashLimit($usid,$ptid,$ctid,$lfid=0)
	{
		$where = array('usid'=>$usid,'ptid'=>$ptid,'ctid'=>$ctid,'status'=>1);
		$user_limit = D('userSalerCashLimit')->getInfo('min,day_min,max,day_max',$where);
		if ($user_limit) return $user_limit;
		$where = array('lfid'=>$lfid,'ptid'=>$ptid);
		$level_limit = D('levelCashFee')->getInfo('min,max,day_min,day_max',$where);
		if ($level_limit) return $level_limit;
		$ct_limit = D('cashType')->getInfoByid($ctid,'min,max,day_min,day_max');
		return $ct_limit;		
	}
    
    /*
	*	获取某种提现方式的提现限制
	*/
	function getCashDayLimit($usid,$ctid,$lfid=0)
	{
		$where = array('usid'=>$usid,'ctid'=>$ctid,'status'=>1);
		$user_limit = D('userSalerCashLimit')->getInfo('max(day_max) AS day_max',$where);
        
		if ($user_limit) return $user_limit;
		$where = array('lfid'=>$lfid);
		$level_limit = D('levelCashFee')->getInfo('max(day_max) as day_max',$where);
		if ($level_limit) return $level_limit;
		$ct_limit = D('cashType')->getInfoByid($ctid,'day_max');
		return $ct_limit;		
	}

	

}
