<?php
/**
 *@desc	 银联APP支付接口 （模拟通道）测试不可上传.
 */
class upacpAction extends baseAction
{
    protected $config;

    function _initialize()
    {
        $this->config = C('upacp');
    }

    //银联sdk-app客户端访问验证签名
    public function sdk_pay()
    {
        $_params = array('order_sn');
        $params = $this->get_params($_params);
        $order_sn = $params['order_sn'];
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$order_sn));
        $this->assign('title','微信扫描');
        $this->assign('paytype','微信扫描');
        $this->assign('storename',$this->usinfo['business_name']?$this->usinfo['business_name']:'汇融钱包');
        $this->assign('order_sn',$order_sn);
        $this->assign('money',$order['money']);
        $this->assign('usid',$this->usid);
        $this->assign('openid','121343432423422');
        $this->display('upacp');
        exit;
        $pay = $this->pay($params,"upmp::sdk");
        if (!$pay)
        {
            $this->apiOut(false);
        }
        $data['tn'] = $this->purchase($pay['order']);
        $this->apiOut($data);
        // $this->response(REQUEST_SUCCESS,$data);
    }
    public function result(){
        $this->display('result');
    }
    //银联sdk-Web客户端访问验证签名
    public function wap_pay($order_sn)
    {
        $order = $this->loadModel('orderFtf')->getInfo('*',array('sn'=>$order_sn));
        $this->assign('title','微信扫描');
        $this->assign('paytype','微信扫描');
        $this->assign('storename',$this->usinfo['business_name']?$this->usinfo['business_name']:'汇融钱包');
        $this->assign('order_sn',$order_sn);
        $this->assign('money',$order['money']);
        $this->assign('usid',$this->usid);
        $this->assign('openid','121343432423422');
        $this->display('upacp');

        exit;
//		$params['order_sn'] = $order_sn;
//		$pay = $this->pay($params,"upacp::wap");
//		if (!$pay)
//		{
//			$this->apiOut(false);
//		}
//		//Vendor('Upacp.SDKConfig');
//		Vendor('Upacp.acp_service');
//        $requestData = array(
//
//			//以下信息非特殊情况不需要改动
//			'version' => '5.0.0',                 //版本号
//			'encoding' => 'utf-8',				  //编码方式
//			'txnType' => '01',				      //交易类型
//			'txnSubType' => '01',				  //交易子类
//			'bizType' => '000201',				  //业务类型
//			'frontUrl' =>  'http://wallet.huirongpay.com/wap.php/pay/success',  //前台通知地址
//			'backUrl' => 'http://wallet.huirongpay.com/api.php/upacp/notice',	  //后台通知地址
//			'signMethod' => '01',	              //签名方法
//			'channelType' => '08',	              //渠道类型，07-PC，08-手机
//			'accessType' => '0',		          //接入类型
//			'currencyCode' => '156',	          //交易币种，境内商户固定156
//
//			//TODO 以下信息需要填写
//			'merId' => SDK_MCHID,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
//			'orderId' => $pay['order']["sn"],	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
//			'txnTime' => date('YmdHis',$pay['order']["addtime"]),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
//			'txnAmt' => $pay['order']["money"] * 100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
//			//'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
//
//			//TODO 其他特殊用法请查看 special_use_purchase.php
//		);
//		//print_r($requestData);
//		AcpService::sign ( $requestData );
//		//print_r($requestData);
//		$uri = SDK_FRONT_TRANS_URL;
//		$html_form = AcpService::createAutoFormHtml( $requestData, $uri );
//		echo $html_form;
    }
    //创建测试支付信息
    public function monipay()
    {
        $usid=I('usid',0,'floatval');
        $order_sn = I('order_sn');

        $order = $this->loadModel('order')->getDetail($order_sn);
        if ($this->loadModel('order')->checkPay($order)){
            $this->ajaxReturn(array(
                'status' => 102,
                'desc' => '该订单已经付款！'
            ));
        }
//        if(empty($usid))
//        {
//            $this->ajaxReturn(array(
//                'status' => 104,
//                'desc' => '用户不能为空！'
//            ));
//        }
        if(empty($order_sn))
        {
            $this->ajaxReturn(array(
                'status' => 105,
                'desc' => '订单号不能为空！'
            ));
        }
        $data['order_sn'] = $order['sn'];
        $pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","","upacp::sdk")));
        $data['ptid'] = $pay_type['id'];
        $data['pt_name'] = $pay_type['name'];
//        $rs = D('orderFtf')->query("UPDATE  rrg_order_ftf SET pt_name ='".$data['pt_name']."',ptid = ".$data['ptid']." WHERE  id = ".$order['id']);
        $rs =  D('orderFtf')->update($data,array('id'=>$order['id']));
        if (!$rs) return false;
        $data['tn'] = $this->purchase($order);
        $this->apiOut($data);

    }
    //创建支付信息
    private function pay($params,$apiname)
    {
        $this->vaild_params('is_empty',$params['order_sn'],'订单号不能为空');
        $order = $this->loadModel('order')->getDetail($params['order_sn']);
        $this->vaild_params(array($this->model['order'],'checkPay'),array($order),'该订单已经付款',false);

        if ($order['money']<0.01)
        {
            $this->response(DATA_EMPTY,'订单金额少于0.01元');
        }
        $data = $params;
        $pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","",$apiname)));
        $data['ptid'] = $pay_type['id'];
        $data['pt_name'] = $pay_type['name'];
        $rs =  D('orderFtf')->update($data,array('sn'=>$order['sn']));
        if (!$rs) return false;
        return array('order'=>$order,'payinfo'=>$data);
    }

    //验证签名,并向upmp客户端提交支付请求
    private function purchase($order)
    {
        //测试订单调试模式
        if (CMBCDEBUG === false)
        {
            $res = $this->notice($order['sn']);
            if ($res){
                $this->ajaxReturn(array(
                    'status' => REQUEST_SUCCESS,
                    'desc' => '订单支付成功！'
                ));
            }else{

                $this->ajaxReturn(array(
                    'status' => INTERNAL_ERROR,
                    'desc' => '订单支付失败！'
                ));
            }
        }
        $data = array(
            "backEndUrl"		 => $this->config['BACKENDURL'],	//异步通知URL
            "frontEndUrl"		 => $this->config['FRONTENDURL'],//前端返回URL
            "subMerid"			 => $this->config['SUBMERID'],			//子账户标识
            "orderNumber"		 => $this->config['SN_PREFIX'].$order['sn'],	//商户订单号
            "orderAmount"		 => $order['money']*100,		//支付金额，单位为分
        );

        $url_get = $this->config['PAY_URL'];		//支付地址

        // $ch = curl_init();
        // curl_setopt($ch,CURLOPT_URL,$url_get);
        // curl_setopt($ch,CURLOPT_POST,1);
        // curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        // curl_setopt($ch,CURLOPT_ENCODING ,'gzip'); //加入gzip解析
        // curl_setopt($ch,CURLOPT_FOLLOWLOCATION ,1); //加入重定向处理
        // ob_start();
        // curl_exec($ch);
        // $httpCode = curl_getinfo($ch,CURLINFO_FILETIME);
        // $res = ob_get_contents();
        // ob_end_clean();
        // curl_close($ch);

        $url_get .= '?';
        foreach ($data as $k => $v)
        {
            $url_get.="{$k}={$v}&";
        }
        $url = parse_url($url_get);
        $url['port'] = $url['port']?$url['port']:'80';
        $fp = fsockopen("{$url['host']}", $url['port'], $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            $out = "GET {$url['path']}?{$url['query']} HTTP/1.1\r\n";
            $out .= "Host: {$url['host']}\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $res .= fgets($fp, 128);
            }
            fclose($fp);
        }

        $n = strpos($res,'{"result"');'}';
        $res = substr($res,$n);
        $res = substr($res,0,-7);
        $res = json_decode ($res,true);

        // 商户的业务逻辑
        if ($res["result"]['code'] == '10000')
        {
            $resp = $res['data'];
            // 提交网关应答成功
            if ($resp['respCode'] == '00')
            {
                // $data = array();
                // $data['upmp_sn'] = $resp['tn'];
                // $rs = M('upmp')->where(array('ordernum'=>$order['sn']))->save($data);
                return $resp['tn'];
                // $this->response(REQUEST_SUCCESS,$resp['tn']);
            }
            else
            {
                $msg = $resp['respMsg'];
                $this->response(INTERNAL_ERROR,"银联反馈信息:{$msg}");
            }
        }else
        {
            // 服务器应答签名验证失败
            $this->response(INTERNAL_ERROR,"提交网关应答失败！");
        }
    }

    /**
     * 异步通知地址
     */
    public function notice($sn='')
    {
        $resp = $_POST;
        $resp['orderId'] = $sn;
        $order = $this->loadModel('order')->getDetail($sn);
        $resp['respCode'] = '00';
        $resp['usid'] =$order['usid'];
        eblog("银联WAP支付异步接收参数集",$_POST,'upacp_'.date("Ymd"));
        $sn = $sn?$sn:$resp['orderId'];
        $sn = str_replace($this->config['SN_PREFIX'],'',$sn);

        eblog('','=======================================','upacp_'.date("Ymd"));
        eblog("银联WAP支付异步接收参数集",$this->_request(),'upacp_'.date("Ymd"));
        Vendor('Upacp.acp_service');
        $order = $this->loadModel('order')->getDetail($sn);
        if(!$order)
        {
            eblog("银联WAP支付异步 - {$sn}",'订单不存在','upacp_'.date("Ymd"));
            return false;
        }
        $is_pay = $this->loadModel('order')->checkPay($order);
        if($is_pay)
        {
            eblog("银联AWAP支付异步 - {$sn}",'订单已支付','upacp_'.date("Ymd"));
            return false;
        }
        #判断状态为0的时候修改订单信息 3为处理中
        $dataFTF = array('status'=>1);
        $rsFTF = D('orderFtf')->update($dataFTF,array('sn'=>$sn,'status'=>'0'));
        #失败处理
        if(!$rsFTF){
            eblog("银联WAP支付异步 - {$sn}",'重复订单号','scan_'.date("Ymd"));
            echo '00';
            return false;
        }
        $rs =$this->loadModel('order')->pay($order);
        if ($rs)
        {
            eblog("银联WAP支付异步 - {$sn}",'订单支付成功','upacp_'.date("Ymd"));
            //发送异步交易查询,并发送相关消息
            $cash = $this->cash($order['ptid'],$order['money'],$order['usid']);
            return $cash;
            A('Api://processMsg')->msg('TradeMsg',$sn);
        }

    }
    public function cash($ptid,$money,$usid,$ctid = 10)
    {

        $user_account = $this->loadModel('userSalerAccount')->getInfo("money",array('ptid'=>$ptid,array('usid'=>$usid)));
        //$this->vaild_params('compare',array($user_account['money'],$data['money'],'>='),'您的余额不足，无法提现');
        $this->usinfo = $this->loadModel('userSaler')->getInfoByid($usid);
        $lfid = $this->usinfo['lfid'];
        eblog("银联WAP支付异步 - ",'提现数据'.$ptid.'--'.$money.'--'.$usid.'--'.$lfid,'upacp_'.date("Ymd"));
        //提现通道限额检测
        $result = $this->loadModel('userSaler')->chkRealtimeLimit($usid,$ptid,$ctid,$lfid,$money);
        if (!is_array($result))
        {
            eblog("银联WAP支付异步 - ",'提现通道限额检测'.$result,'upacp_'.date("Ymd"));
            return false;
            $this->response(DATA_EMPTY,$result);
        }
        else
        {
            $cash = $result;
        }
        eblog("银联WAP支付异步 - ",'提现数据'.$result,'upacp_'.date("Ymd"));

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
        eblog("银联WAP支付异步 - 提现数据--data",$data,'upacp_'.date("Ymd"));
        $rs_cash = $this->loadModel('accountUserSalerCash')->cash($data,$lfid);
        if ($rs_cash)
        {
            //发送异步交易查询,并发送相关消息
            eblog("银联WAP支付异步 - ",'提现数据写入成功','upacp_'.date("Ymd"));
            //模拟代付成功，直接返回支付成功，更新结算时间状态
            $where = array();
            $where['status'] = 0;
            $where['sn'] = $rs_cash['sn'];
            $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
//            $this->single_pay($rs_cash['real_money'],$rs_cash['sn'],$this->usinfo);
            A('Api://processMsg')->msg('CashMsg',$rs_cash);
        }
        eblog("银联WAP支付异步 - 提现数据--rs_cash",$rs_cash,'upacp_'.date("Ymd"));
        $rs = $rs_cash?true:false;
        return $rs;
    }
    public function  single_pay($money,$serialNo,$dataUser = array()) {

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
        eblog("银联WAP支付异步 - 代付数据",$postData,'upacp_'.date("Ymd"));
        $ansData = $this->httpGet($url, $postData);
        return $this->callback($ansData);
    }
    /*
     * 代付返回结果处理
     */
    function callback($respContent) {
        $data = json_decode($respContent, true);
        $serialNo = $data['SEQ'];
        eblog("银联WAP支付异步 - 代付返回数据",$data,'upacp_'.date("Ymd"));
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
