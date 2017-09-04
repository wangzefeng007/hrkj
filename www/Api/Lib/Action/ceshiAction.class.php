<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/9
 * Time: 18:13
 */
set_time_limit(0);
class ceshiAction  extends baseAction
{
    public function alicun(){
        //如果存在上级商户
        $user=D('userSaler')->getInfoByid(100368);
        if($user['pid'])
        {
            // 用户注册完成后,其上级商户是否满足推广升级条件,检测/执行
            $usinfo = D('userSaler')->getInfo('*',array('id'=>$user['pid']));
            $where = array(
                'pid' => $user['pid'],
                'status' => array(array('eq',0),array('eq',1),'or'),
            );
            $count_child = D('userSaler')->where($where)->count();
            eblog("推广升级流程 - 上级商户资料 - usinfo",$usinfo,'child_upgrade_'.date("Ymd"));
            eblog("推广升级流程 - 统计直属商户数 - count_child",$count_child,'child_upgrade_'.date("Ymd"));
            if($usinfo)
            {
                $us_lf = D('levelFee')->getInfo('*',array('id'=>$usinfo['lfid']));
                $where = array(
                    'level' => array('gt',$us_lf['level']),		//有更高的费率等级
                    'is_update' => 1,							//该等级允许升级
                    'status' => 1,								//该等级未关闭
                    'child_upgrade' => array('gt',0),				//推广升级条件大于0
                    'child_upgrade' => array('elt',$count_child),	//推荐人数满足升级条件
                );
                $lf_upable = D('levelFee')->getList('*',$where,'level desc');var_dump($where,$lf_upable);
                eblog("推广升级流程 - 用户等级 - us_lf",$us_lf,'child_upgrade_'.date("Ymd"));
                if ($lf_upable['list'])
                {
                    eblog("推广升级流程 - 可升等级 - lf_upable",$lf_upable,'child_upgrade_'.date("Ymd"));
                    $rs_up = D('orderUpgrade')->autoUpgrade($usinfo,$lf_upable['list'][0],"C","推广人数达到{$count_child}>={$lf_upable['list'][0]['child_upgrade']}");
                    eblog("推广升级流程 - 是否升级成功 - rs_up",$rs_up,'child_upgrade_'.date("Ymd"));
                    if(!$rs_up)
                    {
                        eblog("推广升级报错 - 商户资料 - usinfo",$usinfo,'error_autoUp_'.date("Ymd"));
                        eblog("推广升级报错 - 可升等级 - lf_upable",$lf_upable,'error_autoUp_'.date("Ymd"));
                    }
                }
                else
                {
                    eblog("推广升级流程 - 可升等级",'暂未满足升级条件','child_upgrade_'.date("Ymd"));
                }
            }
        }
    }
    public function demo(){
        $ansData = json_decode($_REQUEST, true);
        var_dump($_REQUEST['reqJson']);
        eblog("民生扫码支付异步接收参数集",'json='.$_REQUEST['reqJson'],'scan_'.date("Ymd"));
        eblog("民生扫码支付异步接收参数集",'ansData='.$ansData,'scan_'.date("Ymd"));
        return  $ansData;exit;
    }
    public function add(){
        $privateKey = "1234567812345678";
        $iv 	= "1234567812345678";
        $data 	= "OF170816091609748178|9900|9850|0|D0|500234199404027036|6222021001143273214|曾勇|支付宝支付";
        //$CONTENT = mcrypt_encrypt(MCRYPT_BLOWFISH_COMPAT, $privateKey, $data, MCRYPT_MODE_CBC,$iv);
        //AES/CBC/PKCS5Padding加密
//        $CONTENT = $this->AesEncrypt($data,$privateKey);
//        echo $CONTENT;
        //echo(base64_encode($CONTENT));
        echo '<br/>';
        $CONTENT = $this->AesDecrypt("psDP0bajQAe4M6tzUWg/17wS0avC2OGYc84JV5q1U+MzBS1wSSvBr2XweBAE700iMfgHiS+JAs/RmwfCpFEE2PYiSL8yl0bpjunk2yVc/VJPYwj/RN+zHvSAHqL0r/MR",$privateKey);
        echo $CONTENT;
exit;
    /*
     * 机构号：00000000
     *加密秘钥：1234567812345678
     *签名密钥:  8765432187654321
     *     MD5(ORG_CODE +CONTENT(密文) +签名密钥)
     */

        $ORG_CODE = "00000000";
        $SIGN = "857d878441e1c3c99d7c7fda7532809b";
        $contentStr = MD5($ORG_CODE.$CONTENT .$SIGN);
        $postData = array(
            'ORG_CODE' => $ORG_CODE,
            'CONTENT' => $data,
            'SIGN' => $SIGN
        );
        $ansData = $this->httpGet("http://test.qzhmpay.com/hrqbdf/payment.do", $postData);
        var_dump($ansData);exit;
    }

    public function  single_pay($money,$serialNo,$dataUser = array()) {

        //$serialNo = date('YmdHis', time()) . rand_num(10, true);

        $url = SINGLE_PAY_URL;
        $postData = array(
            'trainID' => "201607131632570040",//订单号
            'cardNo' => "650000111122223219",//身份证号
            'bankNo' => "6222083002005503379",//银行卡号
            'accBankTypeNo' => $dataUser['bank_type'],
            'transAmt' => $money * 100,
            'busiType' => 0,
            'accType' => 1,
            'resv' => '预留'
        );
        $fee = $this->getfee($pay);
        if($fee){
            $requestData['userfee'] = strval($fee['fee_rate']*$requestData['money'] + $fee['fee_static']);
        }
        eblog("银联APP异步 - 代付数据",$postData,'test_'.date("Ymd"));
        $ansData = $this->httpGet($url, $postData);
        return $this->callback($ansData);
    }
    public function getfee($pay){
        $usinfo = $this->loadModel('userSaler')->getInfoByid($pay['order']['usid']);
        $lfid = $usinfo['lfid'];
        //eblog("银联WAP支付异步 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'upacp_'.date("Ymd"));
        //提现通道限额检测
        $result = $this->loadModel('userSaler')->chkRealtimeLimit($pay['order']['usid'],$pay['payinfo']['ptid'],10,$lfid,$pay['order']['money']);
        if (!is_array($result))
        {
            //eblog("银联WAP支付异步 - ",'提现通道限额检测'.$result,'upacp_'.date("Ymd"));
            return false;
            //$this->response(DATA_EMPTY,$result);
        }
        return $result;
    }
    public function prent(){
        $cash['ptid'] = 10;
        $cash['fee_rate']=0.005;
        $cash['money'] =10000;
        $cash['ctid'] =  10;
        $split = array('count' => 0);
        $parent = D('userSaler')->getParent(100368, true);
        if(empty($parent)){
            return false;
        }
        $user_fee_rate = array('fee_rate' => $cash['fee_rate']);
        foreach ($parent as $value) {
            //普通商户分润计算
            if ($value['lf_type'] == LEVEL_FEE_NORMAL) {
                $fee_rate = D('levelCashFee')->getCashfee($value['lfid'], $cash['ptid'], $cash['ctid']);
                $diff = $fee_rate['fee_rate'] - $user_fee_rate['fee_rate'];
                if ($diff >= 0) continue;//上级费率减去下级费率大于等于零的跳过不计算分润。
                $money = abs($diff) * $cash['money'];
            } else {
                //代理商户分润计算
                $money = $cash['money'] * $value['share_rate'];
            }
            $money =  (float)substr(sprintf("%.3f",$money),0,-1);
            if ($money<=0) continue;
            if (!isset($split['list'][$value['id']])) {
                $split['list'][$value['id']] = array('usid' => $value['id'], 'us_name' => $value['name'], 'money' => $money);
            } else {
                $split['list'][$value['id']]['money'] += $money;
            }
            $split['count'] += $money;
            $user_fee_rate = $fee_rate;
        }
        var_dump($split);exit;
    }
      /*
       * 作者：王泽锋
       * 添加时间：2017.08.23
       *	新分润计算方法
       */
    public function newSplit(){
        //1.获取用户的所有上级
        $cash['ptid'] = 10;
        $cash['fee_rate']=0.005;
        $cash['money'] =10000;
        $cash['ctid'] =  10;
        $user=D('userSaler')->getInfoByid(100368);
        $pid = explode("-",$user['depth_pid']);
        $parentList = array();
        foreach ($pid as $key=>$value){
            $parent = D('userSaler')->getInfo('id,name,lfid',array('id'=>$value));
            $parentList[$key]['usid']=$parent['id'];
            $parentList[$key]['us_name']=$parent['name'];
            $parentList[$key]['lfid']=$parent['lfid'];
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
        foreach ($parentList as $key=>$value){
            if ($value['level']<=$user_level)continue;
            $split['list'][$value['usid']] = $value;
            $user_level = $value['level'];
        }
        $fee_rate = D('levelCashFee')->getCashfee($user['lfid'], $cash['ptid'], $cash['ctid']);
        foreach ($split['list'] as $key=>$value){
            $rate_diff = $fee_rate['fee_rate'] - $value['fee_rate'];
            $split['list'][$value['usid']]['rate_diff'] = $rate_diff;
            //5. 循环分润用户集合，计算用户分润。
            $money = $rate_diff*$cash['money'];
            $split['list'][$value['usid']]['money'] = $money;
            $split['count'] += $money;
            $fee_rate['fee_rate'] = $value['fee_rate'];
        }
        var_dump($split);exit;
    }
    /*
     * 代付返回结果处理
     */
    function callback($respContent) {
        $data = json_decode($respContent, true);
        $serialNo = $data['SEQ'];
        eblog("银联APP异步 - 代付返回数据",$data,'test_'.date("Ymd"));
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
    function httpGet($url, $postData=''){
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