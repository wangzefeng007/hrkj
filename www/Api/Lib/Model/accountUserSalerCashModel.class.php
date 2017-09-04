<?php
class accountUserSalerCashModel extends baseModel
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     *    提现
     */
    function cash($cash, $lfid)
    {
        $time = time();
        $this->startTrans();
        if (!$cash['sn']) $cash['sn'] = $this->createSn('CN');
        //更新无通道总额
        $account_total = D('userSalerAccountTotal')->getInfo('*', array('usid' => $cash['usid']));
        $total = 0.00;
        if (!$account_total) {
            eblog("提现数据--data", $cash, 'AccountUserSalerCash_' . date("Ymd"));
            eblog("提现数据--lfid", $lfid, 'AccountUserSalerCash_' . date("Ymd"));
            $this->rollback();
            return false;
        } else {
            $data = array('total_usable' => $account_total['total_usable'] - $cash['money'],
                'normal_usable' => $account_total['normal_usable'] - $cash['money']);
            $rs = D('userSalerAccountTotal')->update($data, array('usid' => $cash['usid']));
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        //添加提现订单
        $count = D('accountUserSalerCash')->where(array('usid' => $cash['usid'], 'ctid' => $cash['ctid']))->count();

        $cash['real_money'] = round($cash['money'] - $cash['fee_static'] - $cash['fee_rate'] * $cash['money'], 2);
        $cash['total'] = $account_total['total_usable'] - $cash['money'];
        $cash['count'] = $count + 1;    //结算次数
        $data = $cash;
        unset($data['us_mobile']);
        $rs = D('accountUserSalerCash')->add($data);
        if (!$rs) {
            eblog("提现数据--accountUserSalerCash", $data, 'AccountUserSalerCash_' . date("Ymd"));
            $this->rollback();
            return false;
        }
        $_usicid = $rs;

        //增加流水
        $data = array();
        $data['usid'] = $cash['usid'];
        $data['usicid'] = $_usicid;
        $data['order_sn'] = $cash['sn'];
        $data['type'] = 2;
        $data['money'] = (0 - $cash['money']);
        $data['total'] = $account_total['total_usable'] - $cash['money'];
        $data['real_money'] = $cash['real_money'];
        // $data['remark'] = '普通提现';
        $data['remark'] = $cash['ct_name'];
        $data['cash_fee'] = $cash['fee_rate'];
        $data['addtime'] = $time;
        $rs = D('accountUserSalerWater')->add($data);
        if (!$rs) {
            eblog("提现数据--accountUserSalerWater", $data, 'AccountUserSalerCash_' . date("Ymd"));
            $this->rollback();
            return false;
        }
        //更新分通道总额
        // $account = D('userSalerAccount')->getInfo('money,cash_money',array('usid'=>$cash['usid'],'ptid'=>$cash['ptid']));
        $data = array(
            'money' => array('exp', 'money-' . $cash['money']),
            'cash_money' => array('exp', 'cash_money+' . $cash['money']),
        );
        $rs = D('userSalerAccount')->update($data, array('usid' => $cash['usid'], 'ptid' => $cash['ptid']));
        if (!$rs) {
            $this->rollback();
            return false;
        }

        //分润计算
        $split = $this->newSplit($cash, $lfid);

        if ($split['count'] > 0) {
            foreach ($split['list'] as $value) {
                //收入明细插入
                $data = array();
                $data['usid'] = $value['usid'];
                $data['us_name'] = $value['us_name'];
                $data['order_sn'] = $cash['sn'];
                $data['money'] = $value['money'];
                $data['from_usid'] = $cash['usid'];
                $data['order_money'] = $cash['money'];
                $data['type'] = INCOME_SPLIT;
                $data['status'] = INCOME_STATUS_NORMAL;
                $data['ptid'] = $cash['ptid'];
                $data['pt_name'] = $cash['pt_name'];
                $data['addtime'] = $time;
                $rs = D('accountUserSalerIncome')->add($data);
                if (!$rs) {
                    eblog("分润计算--newSplit", $data, 'accountUserSalerIncome_' . date("Ymd"));
                    $this->rollback();
                    return false;
                }
                $usicid = $rs;
                //更新无通道总额
                $account_total = D('userSalerAccountTotal')->getInfo('*', array('usid' => $value['usid']));
                $total = 0.00;
                if (!$account_total) {
                    $data = array('total_usable' => $value['money'],
                        'split_usable' => $value['money'],
                        'split_total' => $value['money'],
                        'normal_split_total' => $value['money'],
                        'usid' => $value['usid']
                    );
                    $rs = D('userSalerAccountTotal')->add($data);
                } else {
                    $data = array('total_usable' => $account_total['total_usable'] + $value['money'],
                        'split_usable' => $account_total['split_usable'] + $value['money'],
                        'split_total' => $account_total['split_total'] + $value['money'],
                        'normal_split_total' => $account_total['normal_split_total'] + $value['money'],
                    );
                    $rs = D('userSalerAccountTotal')->update($data, array('usid' => $value['usid']));

                }
                if (!$rs) {
                    eblog("提现数据--userSalerAccountTotal", $data, 'AccountUserSalerCash_' . date("Ymd"));
//                    $this->rollback();
//                    return false;
                }
                //增加流水
                $data = array();
                $data['usid'] = $value['usid'];
                $data['usicid'] = $usicid;
                $data['order_sn'] = $cash['sn'];
                $data['type'] = 1;
                $data['money'] = $value['money'];
                $data['total'] = $account_total['total_usable'] + $value['money'];
                $data['remark'] = '普通分润收入';
                $data['addtime'] = $time;
                if(strstr($value['money'], '0.00'))continue;
                $rs = D('accountUserSalerWater')->add($data);
                if (!$rs) {
                    eblog("提现数据--accountUserSalerWater-list", $data, 'AccountUserSalerCash_' . date("Ymd"));
//                    $this->rollback();
//                    return false;
                }
            }
        }


        //平台成本费率
        $platform_cost_fee = D('platformCostFee')->getFee($cash['ptid']);

        //平台收入
        $data = array();
        $data['type'] = PLATFORM_INCOME_CASH;
        $data['order_sn'] = $cash['sn'];
        $data['order_money'] = $cash['money'];
        $data['uscid'] = $_usicid;
        $data['ptid'] = $cash['ptid'];
        $data['ctid'] = $cash['ctid'];
        $data['fee_money'] = $cash['money'] * $cash['fee_rate'];
        $data['split'] = 0 - $split['count'];
        $data['money'] = $data['fee_money'] + $data['split'] + $cash['fee_static'];
        $data['usid'] = $cash['usid'];
        $data['us_name'] = $cash['us_name'];
        $data['us_mobile'] = $cash['us_mobile'];
        $data['lfid'] = $lfid;
        $data['lf_fee_rate'] = $cash['fee_rate'];
        $data['lf_fee_static'] = $cash['fee_static'];
        $data['cost_rate'] = $platform_cost_fee[$cash['ptid']][$cash['ctid']]['fee_rate'];
        $data['cost_static'] = 0 - $platform_cost_fee[$cash['ptid']][$cash['ctid']]['fee_static'];
        $data['cost_money'] = 0 - $cash['money'] * $data['cost_rate'];
        $data['income_money'] = $data['money'] + $data['cost_money'] + $data['cost_static'];
        $data['addtime'] = $time;
        $rs = D('accountPlatformIncome')->add($data);
        if (!$rs) {
            eblog("提现数据--accountPlatformIncome", $data, 'AccountUserSalerCash_' . date("Ymd"));
            $this->rollback();
            return false;
        }

        $this->commit();
        return $cash;
    }

    /*
    *	订单分润计算
    *	@param 			array			$cash			订单信息
    *	@param				int				$lfid			提现人等级id
    *	@return				array			$split			分润费用	array('count'=>分润总额,'list'=>分润详情)
    */
    private function split($cash, $lfid)
    {
        eblog("订单分润计算--split_start", $cash, 'split_' . date("Ymd"));
        $split = array('count' => 0);
        $parent = D('userSaler')->getParent($cash['usid'], true);//获取所有上级用户列表
        if (!$parent) {
            return false;
        }
        $user_fee_rate = array('fee_rate' => $cash['fee_rate']);//用户本人费率
        foreach ($parent as $value) {
            //普通商户分润计算
            if ($value['lf_type'] == LEVEL_FEE_NORMAL) {
                $fee_rate = D('levelCashFee')->getCashfee($value['lfid'], $cash['ptid'], $cash['ctid']);
                $diff = $fee_rate['fee_rate'] - $user_fee_rate['fee_rate'];
                if ($diff >= 0) continue;
                $money = abs($diff) * $cash['money'];
            } else {
                //代理商户分润计算
                $money = $cash['money'] * $value['share_rate'];
            }
//            //跳过金额为0
//            $money =  (float)substr(sprintf("%.3f",$money),0,-1);
//            if ($money<=0) continue;
            if (!isset($split['list'][$value['id']])) {
                $split['list'][$value['id']] = array('usid' => $value['id'], 'us_name' => $value['name'], 'money' => $money);
            } else {
                $split['list'][$value['id']]['money'] += $money;
            }
            $split['count'] += $money;
            $user_fee_rate = $fee_rate;
        }
        eblog("订单分润计算--split_end", $cash, 'split_' . date("Ymd"));
        return $split;
    }
    /*
  * 作者：王泽锋
  * 添加时间：2017.08.23
  *	新分润计算方法
  */
    public function newSplit($cash,$lfid){
        //1.获取用户的所有上级
        eblog("订单分润计算--newSplit_start", $cash, 'newSplit_' . date("Ymd"));
        $user=D('userSaler')->getInfoByid($cash['usid']);
        $pid = explode("-",$user['depth_pid']);
        $parentList = array();
        foreach ($pid as $key=>$value){
            $parent = D('userSaler')->getInfo('id,name,lfid',array('id'=>$value));
            $parentList[$key]['usid']=$parent['id'];
            $parentList[$key]['us_name']=$parent['name'];
            $parentList[$key]['lfid']=$parent['lfid'];
        }
        if (empty($parentList)){
            return false;
        }
        //2.该支付渠道对应的各级别费率列表
        foreach ($parentList as $key=>$value){
            $fee_rate = D('levelCashFee')->getCashfee($value['lfid'], $cash['ptid'], $cash['ctid']);
            $parentList[$key]['fee_rate'] = $fee_rate['fee_rate'];
            //3.用户层级对应关系表 level和lfid 对应关系查询
            $parentList[$key]['level'] = D('levelFee')->getLevel($value['lfid']);
        }
        //4.得到能分到分润的用户最小集合（用户id和费率差）
        $user_level = D('levelFee')->getLevel($user['lfid']);
        $split = array('count' => 0);
        $parentList = array_reverse($parentList);
        //用户最小集合
        foreach ($parentList as $key=>$value){
            if ($value['level']<=$user_level)continue;
            $split['list'][$value['usid']] = $value;
            $user_level = $value['level'];
        }
        $fee_rate = D('levelCashFee')->getCashfee($user['lfid'], $cash['ptid'], $cash['ctid']);
        foreach ($split['list'] as $key=>$value){
            //费率差
            $rate_diff = $fee_rate['fee_rate'] - $value['fee_rate'];
            $split['list'][$value['usid']]['rate_diff'] = $rate_diff;
            //5. 循环分润用户集合，计算用户分润。
            $money = $rate_diff*$cash['money'];
            $split['list'][$value['usid']]['money'] = $money;
            $split['count'] += $money;
            $fee_rate['fee_rate'] = $value['fee_rate'];
        }
        eblog("订单分润计算--newSplit_end", $cash, 'newSplit_' . date("Ymd"));
        return $split;
    }

    function getTodayCash($usid, $ptid, $ctid)
    {
        $where = array('usid' => $usid, 'ptid' => $ptid, 'ctid' => $ctid);
        $this->where($where)->sum('money');
    }


    /*
    *	提现单号
    */
    private function createSn($type = 'CN')
    {
        return $type . substr(date("YmdHis"), -12) . rand(100000, 999999);
    }
}