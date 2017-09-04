<?php
/*
 *  民生扫码支付接口
 *  提交、异步通知地址
 */
class notifyAction extends baseAction
{
    public $ORG_CODE = "00000000";	//机构号
    public $KEY = "1T3m9FJF71UAw0ZM";	//加密秘钥
    public $SIGN_KEY = "kY8D98Ex946MRt46";//签名密钥
    function wap_pay()
    {
        $code = I('code');
        $sn = I('order_sn');
        Vendor('Wxpay.WxPayPubHelper');
        $jsApi = new JsApi_pub();

        if (!$code)
        {
            $url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL."order_sn/".$sn);
            header("Location: ".$url);
            exit;
        }else
        {
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }

        $order = $this->pay($sn,"wxpay::wap",0);
        if (!$order)
        {
            $this->err_msg("数据提交失败，请稍后再试！",0);
        }

        $unifiedOrder = new UnifiedOrder_pub();
        $unifiedOrder->setParameter("sub_openid",$openid);//
        $unifiedOrder->setParameter("body","商品");//商品描述
        $unifiedOrder->setParameter("out_trade_no",$sn);//商户订单号 
        $unifiedOrder->setParameter("total_fee",$order['money']*100);//总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
        // $unifiedOrder->setParameter("sub_mch_id",WxPayConf_pub::SUB_MCHID);//
        // $unifiedOrder->setParameter("sub_appid",WxPayConf_pub::SUB_APPID);//


        $prepay_id = $unifiedOrder->getPrepayId();
        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getParameters();

        $this->js_call($jsApiParameters);
    }

    //微信扫码支付接口
    public function scan_pay()
    {
        $wxdebug = WXDEBUG;
        $sn = I('order_sn');
        $order = $this->pay($sn,"wxpay::scan");
        if (!$order)
        {
            $this->err_msg("数据提交失败，请稍后再试！");
        }

        $order = $this->loadModel('order')->getDetail($sn);
        if ($order)
        {
            if ($this->loadModel('order')->checkPay($order))
            {
                eblog("测试通道 - {$sn}",'订单已支付','test_'.date("Ymd"));
                // eblog('wxpay:already pay',$order['sn']);
            }
            else
            {

                $paylog = $this->loadModel('orderPaylog')->getInfo("*",array('order_sn'=>$sn));
                $rs = $this->loadModel('order')->pay($order,$paylog);
                if($rs)
                {
                    eblog("测试通道 - {$sn}",'订单支付成功','test_'.date("Ymd"));
                    A('Api://processMsg')->msg('TradeMsg',$sn);
                    $this->cash(6,$order['money'],$order['usid']);
                }
            }
        }
        else
        {
            eblog("测试通道 - {$sn}",'订单不存在','test_'.date("Ymd"));
        }

        $this->err_msg("模拟支付成功！");
        exit;
        Vendor('Wxpay.WxPayPubHelper');
        $sn = I('order_sn');
        $order = $this->pay($sn,"wxpay::scan");
        if (!$order)
        {
            $this->err_msg("数据提交失败，请稍后再试！");
        }
        $unifiedOrder = new UnifiedOrder_pub();
        $unifiedOrder->setParameter("body","商品");	//商品描述
        $unifiedOrder->setParameter("out_trade_no",$sn);//商户订单号
        $unifiedOrder->setParameter("total_fee",$order['money']*100);//总金额
        $unifiedOrder->setParameter("spbill_create_ip",$this->_server("REMOTE_ADDR"));//终端IP
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
        $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
        $unifiedOrder->setParameter("sub_mch_id",WxPayConf_pub::SUB_MCHID);//
        $unifiedOrder->setParameter("sub_appid",WxPayConf_pub::SUB_APPID);//
        $prepay_id = $unifiedOrder->getPrepayId();
        $result = $unifiedOrder->result;
        if ($result['return_code'] == 'SUCCESS')
        {
            if ($result['result_code'] == 'SUCCESS')
            {
                $data['share_title']='扫一扫,轻松付款';
                $data['share_desc']='商户:'.$userInfo['name'].'向您发起一笔金额为￥'.$order['money'].'的收款';
                $data['price']=$order['money'];
                $data['url'] = $result['code_url'];
                $this->apiOut($data);
            }
            else
            {
                $this->response(PARAMS_ERROR,"错误!{$result['err_code']}-{$result['err_code_des']}!");
            }
        }
        else
        {
            $this->response(PARAMS_ERROR,"错误!{$result['return_msg']}!");
        }
    }

    //获取微信收款网页地址
    public function pay_url()
    {
        $sn = I('order_sn');
        S("wxpay_{$sn}",'1',60*3);	//链接3分钟有效
        $this->vaild_params('is_empty',$sn,'订单号不能为空!');
        $data['url'] = 'http://'.$_SERVER['HTTP_HOST'] . U('wxpay/wx_wap',array('order_sn'=>$sn));
        // $data['url'] = HOST . U('wxpay/wx_wap',array('order_sn'=>$sn));
        $this->apiOut($data);
    }

    //微信收款网页
    public function wx_wap()
    {
        $sn = I('order_sn');
        if (!S("wxpay_{$sn}"))
        {
            $params['msg'] = '支付已经失效!';
            $this->result($params);
        }
        $order = $this->loadModel('order')->getDetail($sn,"*");
        $user = $this->loadModel('userSaler')->getInfoByid($order['usid'],'name');
        $form = array(
            'action' => U('wxpay/wap_pay'),
            'data' => array(
                'order_sn' => $sn,
            ),
        );
        $this->assign('title','微信支付');
        $this->assign('form',$form);
        $this->assign('sn',$order['sn']);
        $this->assign('money',$order['money']);
        $this->assign('name',$user['name']);
        $this->display('pay/pay_info');
    }

    //支付消息反馈
    public function result($params)
    {
        $this->assign('title',$params['title']?$params['title']:'微信支付');
        $this->assign('result',$params['success']?'success':'fail');
        $this->assign('msg',$params['msg']?$params['msg']:'操作失败');
        $this->display('pay/msg_result');
        exit;
    }

    //错误信息反馈
    public function err_msg($msg,$is_api=1)
    {
        if ($is_api)
        {
            $this->response(PARAMS_ERROR,$msg);
        }
        else
        {
            $this->result(array('msg'=>$msg));
        }
    }

    //创建支付信息
    private function pay($sn,$apiname,$is_api=1)
    {
        if (!$sn) $this->err_msg("订单号不能为空",$is_api);
        $order = $this->loadModel('order')->getDetail($sn);
        if (!$order) $this->err_msg("错误!订单不存在!",$is_api);
        if($this->loadModel('order')->checkPay($order))
        {
            $this->err_msg("该订单已支付，请勿重复支付！",$is_api);
        }
        if ($order['money']<0.01) $this->err_msg("订单金额少于0.01元!",$is_api);
        $pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));

        //通道限额检测
        $err_msg = $this->loadModel('userSaler')->chkPayLimit($order,$pay_type['id'],$this->usinfo['lfid']);
        if ($err_msg)
        {
            $this->response(DATA_EMPTY,$err_msg);
        }

        $data = array();
        $data['ptid'] = $pay_type['id'];
        $data['pt_name'] = $pay_type['name'];
        $rs =  D('orderFtf')->update($data,array('sn'=>$order['sn']));
        if (!$rs) return false;
        return $order;
    }

    /*
    *   付款成功通知
    */
    public function scan($sn='')
    {

        $resJson = $_REQUEST['resJson'];
        $resJson = str_replace(" ","+",$resJson);
        eblog("民生扫码支付异步接收REQUEST参数集",$resJson,'scan_'.date("Ymd"));
        $result = json_decode($resJson, true);
        eblog("扫码支付异步接收resJson参数集",'json='.$result,'scan_'.date("Ymd"));
        //支付平台订单号|客户订单号|金额|支付时间|订单状态|备用
        echo "HRKJ";
        $privateKey ='1234567812345678';//加密秘钥
        $Key ='8765432187654321';//签名密钥
        $ORG_CODE = "00000000";//机构号
        $SIGN =  md5($ORG_CODE.$result['CONTENT'].$Key);

        //AES解密s
        $result['CONTENT'] = $this->AesDecrypt($result['CONTENT'],$privateKey);

        //{ORG_CODE=00000000, RES_CODE=0000, VERSION=1.0, RES_MSG=成功, CONTENT=OS2017081510183491810214|OF170815204805959426|1500|20170815103347|2|}
        $result['CONTENT'] = explode("|",$result['CONTENT']);
        $sn = $result['CONTENT'][1];
        $result['MONEY'] = $result['CONTENT'][2];
        $result['Status'] = $result['CONTENT'][5];
        eblog('','=======================================','scan_'.date("Ymd"));
        eblog('扫码支付异步','MONEY='.$result['MONEY'].',RES_CODE='.$result['RES_CODE'].',Status='.$result['Status'].',sn='.$sn,'scan_'.date("Ymd"));
        if ($SIGN!==$result['SIGN']){
            eblog("民生扫码支付异步接收-SIGN不一致",'SIGN='.$SIGN.',RESULT_SIGN'.$result['SIGN'],'scan_'.date("Ymd"));
            $data['msg']  = 'SIGN不一致';
            $data['CODE']  = 99993;
            $this->apiOut($data);
        }
        if ( $result['RES_CODE'] !== '0000')
        {
            eblog("民生扫码支付异步接收参数集",'RES_CODE='.$result['RES_CODE'],'scan_'.date("Ymd"));
            $data = array();
            $data['msg']  = '返回状态出错';
            $data['CODE']  = 99995;
            $this->apiOut($data);
        }

        if ( $result['Status'] != '2'){
            eblog("民生扫码支付异步接收-订单状态错误",'Status ='.$result['Status'],'scan_'.date("Ymd"));
            $data = array();
            $data['msg']  = '订单状态出错';
            $data['CODE']  = 99996;
            $this->apiOut($data);
        }


        //验证签名
        /*
        $sign = $_REQUEST['SIGN'];
        //$signkey = $this->config['SECRET'];
        // 验签数据组装
        $str = $this->ORG_CODE . $_REQUEST['CONTENT'] . $this->SIGN_KEY;
        $result = md5($str);
        if ($result !== $sign) {
            eblog("银联APP异步 - {$sn}",'签名验证失败','scan_'.date("Ymd"));
        }
        */
        $order = $this->loadModel('order')->getDetail($sn);
        if(!$order)
        {
            eblog("民生扫码支付异步 - {$sn}",'订单不存在','scan_'.date("Ymd"));
            $data['msg']  = '订单不存在';
            $data['CODE']  = 99994;
            $this->apiOut($data);
//            return false;
        }
//        $is_pay = $this->loadModel('order')->checkPay($order);
        if($order['status']==1)
        {
            eblog("民生扫码支付异步 - {$sn}",'订单已支付','scan_'.date("Ymd"));
            $data = array();
            $data['msg']  = '订单已支付';
            $data['CODE']  = 99997;
            $this->apiOut($data);
//            echo '00';
//            return false;
        }
        $money = $order['money']*100;
        if ($result['MONEY'] != $money){
            eblog("民生扫码支付异步 - {$sn}",'money='.$money.',ResultMoney='.$result['MONEY'].'订单金额不一致','scan_'.date("Ymd"));
            $data = array();
            $data['msg']  = '订单金额不一致';
            $data['CODE']  = 99998;
            $this->apiOut($data);
        }
        #判断状态为0的时候修改订单信息 1为已支付
        $rsFTF = D('orderFtf')->update( array('status'=>1),array('id'=>$order['id'],'status'=>'0'));
        #失败处理
        if(!$rsFTF){
            eblog("民生扫码支付异步 - {$sn}",'重复通知','scan_'.date("Ymd"));
            $data = array();
            $data['msg']  = '订单重复通知';
            $data['CODE']  = 99999;
            $this->apiOut($data);

//            echo '00';
//            return false;
        }
        $rs =D('orderFtf')->pay($order);
        eblog("民生扫码支付异步- {$sn}--order",$order,'scan_'.date("Ymd"));
        if ($rs)
        {
            eblog("民生扫码支付异步 - {$sn}",'订单支付成功','scan_'.date("Ymd"));
            //发送异步交易查询,并发送相关消息
            A('Api://processMsg')->msg('TradeMsg',$sn);
        }
        else
        {
            eblog("民生扫码支付异步 - {$sn}",'支付失败----','scan_'.date("Ymd"));
//            echo '00';
//            return false;
        }
        //调用代付接口
        $this->cash($order['ptid'],$order['money'],$order['usid']);
        //if (MSSCANDEBUG === true) return $rs;


        //$backData['ORG_CODE'] = '00000000';
        //$backData['RES_CODE'] = '00';
        //$backData['SIGN'] = '1235752135163613';
    }

    private function js_call($params)
    {
        echo "<script type='text/javascript'>
                //调用微信JS api 支付
                function jsApiCall()
                {
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest',
                        ".$params.",
                        function(res){
                            WeixinJSBridge.log(res.err_msg);
                        }
                    );
                }

                function callpay()
                {
                    if (typeof WeixinJSBridge == 'undefined'){
                        if( document.addEventListener ){
                            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                        }else if (document.attachEvent){
                            document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
                            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                        }
                    }else{
                        jsApiCall();
                    }
                }
                callpay();
            </script>";
    }

    //提交支付订单
    public function getOrder() {
        $orderSn = I('order_sn');
        $this->vaild_params('is_empty', $orderSn, '请填写要查询的订单号');
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));

        if (empty($order)) {
            $this->set_code(8);
            $this->set_msg('没有该订单');
            $this->send();
        }
        $userInfo = $this->loadModel('userSaler')->getInfo('*',array('id'=>$this->usid));
        $result['url'] = 'http://bq.980buy.com/api.php?c=wxpay&a=wx_qrcode&order_sn=' . $orderSn;
        $result['share_title']='扫一扫,轻松付款';
        $result['share_desc']='商户:'.$userInfo['name'].'向您发起一笔金额为￥'.$order['money'].'的收款';
        $this->apiOut($result);
    }

    public function wx_qrcode(){
        $orderSn = I('order_sn');
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));
        $this->assign('order_sn',$order['sn']);
        $this->assign('money',$order['money']);
        $this->display();
    }

    public function cashier_receive(){
        $money=I('money',0,'floatval');
        $orderSn = $_REQUEST['order_sn'];
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$orderSn));
        if($money){
            $amount=$money*100;
            //$wxPayHelper = new \Vendor\Weixin\WxPayNewHelper();
            Vendor('Weixin.WxPayNewHelper');
            $wxPayHelper=new WxPayNewHelper();
            $wxPayHelper->setParameter ( "appid", 'wx128762b258232239');
            $wxPayHelper->setParameter ( "mch_id", '1273224501');
            $wxPayHelper->setParameter ( "notify_url", 'http://wap.9580buy.com/pay/indes.php/WechatNotify/asyncCashier');
            $wxPayHelper->setParameter ( "signtype", 'MD5');
            $wxPayHelper->setParameter ( "partnerkey", 'qch3uxh3t0e66sop4k3r41f8v40ss05p');
            $wxPayHelper->setParameter ( "body", '人人购微信支付收款');
            $wxPayHelper->setParameter ( "out_trade_no", $order['sn']);
            $wxPayHelper->setParameter ( "total_fee", $amount );
            $wxPayHelper->setParameter ( "fee_type", "CNY" );
            $wxPayHelper->setParameter ( "goods_tag", '' ); // $params['total_fee']
            //$wxPayHelper->setParameter ( "sub_openid", $this->_openid ); // $params['total_fee']
            $wxPayHelper->setParameter ( "sub_mch_id", '1308518701');//1278083801
            $wxPayHelper->setParameter ( "sub_appid", 'wx1bcf92ddcd2497f1');
            $xmlstr = $wxPayHelper->service_unifiedorder ();
            if ($xmlstr) {
                $xmlobj = ( array ) simplexml_load_string ( $xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA );
                // var_dump($xmlobj['prepay_id']);
                dump($xmlobj);
                DIE();
                if ($xmlobj ['return_code'] == 'SUCCESS') {
                    $package = $xmlobj['prepay_id'];
                    //echo  $package;
                    M('testing')->add(array('test'=>$package,'dates'=>date("Y-m-d H:i:s")));
                } else {
                    logger ( '获取预支付订单失败,！' . $xmlstr . ':订单：' . $ordersn );
                    $this->ajaxReturn(array(
                        'status' => 104,
                        'desc' => '接口获取预支付订单解析失败'.$xmlobj['prepay_id']
                    ));
                }
            } else {
                // logger ( '获取预支付订单失败！' . $ordersn );
                $this->ajaxReturn(array(
                    'status' => 105,
                    'desc' => '接口获取预支付订单失败'
                ));
            }
            $packageinfo = $wxPayHelper->create_biz_package($package);
            M('testing')->add(array('test'=>$packageinfo,'dates'=>date("Y-m-d H:i:s")));
            $this->ajaxReturn(array(
                'status' => 1,
                'desc' => $packageinfo
            ));
            $this->assign('package', $packageinfo);
        } else {
            $this->ajaxReturn(array(
                'status' => 101,
                'desc' => '收款金额必须大于0'
            ));
        }

    }
    public function cash($ptid,$money,$usid,$ctid = 10)
    {

        $user_account = $this->loadModel('userSalerAccount')->getInfo("money",array('ptid'=>$ptid,array('usid'=>$usid)));
        //$this->vaild_params('compare',array($user_account['money'],$data['money'],'>='),'您的余额不足，无法提现');
        $this->usinfo = $this->loadModel('userSaler')->getInfoByid($usid);
        $lfid = $this->usinfo['lfid'];
        eblog("民生扫码支付 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'scan_'.date("Ymd"));
        //提现通道限额检测
        $result = $this->loadModel('userSaler')->chkRealtimeLimit($usid,$ptid,$ctid,$lfid,$money);
        eblog("民生扫码支付 - ",'提现数据'.$result,'scan_'.date("Ymd"));
        if (!is_array($result))
        {
            eblog("民生扫码支付 - ",'提现通道限额检测'.$result,'scan_'.date("Ymd"));
            return false;
            $this->response(DATA_EMPTY,$result);
        }
        else
        {
            $cash = $result;
        }


        $data['ptid'] = $ptid;
        $data['ctid'] = $ctid;
        $data['money'] = $money;
        $data['pt_name'] = $cash['pt_name'];
        $data['ct_name'] = $cash['ct_name'];
        $data['usid'] = $usid;
        $data['us_name'] = $this->usinfo['name'];
        $data['us_mobile'] = $this->usinfo['mobile'];
        $data['fee_rate'] = $cash['fee_rate'];
        $data['fee_static'] = $cash['fee_static'];
        $data['status'] = 0;
        $data['addtime'] = time();
        $data['type'] = CASH_NORMAL;
        eblog("民生扫码支付 - 提现数据--data",$data,'scan_'.date("Ymd"));
        $rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
        if ($rs_cash)
        {
            //发送异步交易查询,并发送相关消息
            eblog("民生扫码支付 - ",'提现数据写入成功','scan_'.date("Ymd"));
            $this->single_pay($rs_cash['real_money'],$rs_cash['sn'],$this->usinfo);
            A('Api://processMsg')->msg('CashMsg',$rs_cash);
        }
        eblog("民生扫码支付 - 提现数据--rs_cash",$rs_cash,'scan_'.date("Ymd"));
        $rs = $rs_cash?true:false;
        return $rs;
        //S($lock,null);
        //$this->apiOut($rs,false);
    }
    public function  single_pay($money,$serialNo,$dataUser = array()) {

        //$serialNo = date('YmdHis', time()) . rand_num(10, true);
        if($money < 0)
        {
            eblog("民生扫码支付- ",'代付金额小于0','single_pay_'.date("Ymd"));
            return false;
        }
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
        eblog("民生扫码支付 - 代付数据",$postData,'single_pay_'.date("Ymd"));
//        $ansData = $this->httpGet($url, $postData);
        $where = array();
        $where['status'] = 0;
        $where['sn'] = $serialNo;
        //更新状态
        $rs = $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
        if (!$rs){
            eblog("修改结算状态失败",'sn='.$serialNo,'single_pay_'.date("Ymd"));
        }
        return true;
//        return $this->callback($ansData);
    }
    /*
     * 代付返回结果处理
     */
    function callback($respContent) {
        $data = json_decode($respContent, true);
        $serialNo = $data['SEQ'];
        eblog("民生扫码支付 - 代付返回数据",$data,'single_pay_'.date("Ymd"));
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
	
