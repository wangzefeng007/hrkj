<?php
/**
 * @desc 变更储蓄卡
 * Class changeAction
 */

class changeAction extends baseAction
{
    protected $realNameAuth;
    protected $version = '1.0.1';//版本
    protected $sign_type = 'RSA';//加密方式
    protected $MER_ID = '880100000000003';//裸接口商户号
    protected $charset = 'utf-8';//字符集编码
    protected $verify_type = '0030';//认证类型
    function _initialize() {
        parent::_initialize();
        //实名认证   1：开启
        $this->realNameAuth=1;
    }
    public function add(){
        foreach (getallheaders() as $key => $value) {
            if ($key == 'Us-Id') {
                $this->usid = $value;
            }
        }
        $user = $this->loadModel('userSaler')->getInfoByid($this->usid);
        $_params = $this->get_params(array('card_image', 'bank_no'));
        $this->vaild_params('is_empty', $_params['bank_no'], '银行卡不能为空');
        $this->vaild_params('is_empty',  $_params['card_image'], '手持照片不能为空');
        import("@.Tool.file");
        $data['card_image'] = file::tmp_to_final(trim($_params['card_image']),'image','user','copy');
//        eblog("变更储蓄卡 - data",$data,'add_card_image'.date("Ymd"));
        $cardbin = substr($_params['bank_no'],0,6) ;
        $condition["card_bin"] = array("like", $cardbin."%");
        $bankno = M('card_bin')->where($condition)->field('*')->find();
        if($bankno['card_type'] == '01' || $bankno['card_type'] == '03')
        {
            $data['mobile'] =  $user['mobile'];
            $data['name'] =  $user['name'];
            $data['old_bank_no'] =  $user['bank_no'];
            $data['bank_no'] =  trim($_params['bank_no']);
            $data['bank'] =  $bankno['bank'];
            $data['usid'] =$this->usid;
            $data['status'] = 1;
            $data['addtime'] = time();
            $data['updatetime'] = $data['addtime'];
        }else{
            $this->vaild_params('is_empty',$_params['bank'],'所上传的不是储蓄卡，请重新输入');
        }

        $reqData = array(
            'acc_name'=>trim($user['name']),
            'cert_no'=>trim($user['card_no']),
            'acc_no'=>trim($_params['bank_no']),
            'phone' =>trim($user['mobile'])
        );

        if ($this->realNameAuth){
            if (!$this->realname_verify($reqData)) {
              $this->response(INTERNAL_ERROR,'实名认证失败,请核对数据后再试!');
            }
        }
        $rs = $this->loadModel('changeDebitCard')->add($data);
        if (!$rs){
            eblog("变更储蓄卡 - 储蓄卡信息",$data,'bank_'.date("Ymd"));
            return false;
        }
        return $this->apiOut($data);
    }
    public function get_bank_status(){
        foreach (getallheaders() as $key => $value) {
            if ($key == 'Us-Id') {
                $this->usid = $value;
            }
        }
        $data =array();
        $data = $this->loadModel('changeDebitCard')->getInfo('*',array('usid'=>$this->usid
        ),"id desc");
        if (!empty($data)){
            $data['status_name'] = $this->loadModel('changeDebitCard')->get_status();
        }else{
            $data['list'] = '10000';
        }
        return $this->apiOut($data);
    }
    /*
    * 实名认证接口
    * parma: bank_no:银行卡号, name:姓名,card_no:身份证号
    */
    public function  realname_verify($data = array()) {
        if (empty($data)) {
            return false;
        }

        $serialNo = date('YmdHis', time()) . rand_num(10, true);

        //已提交过的信息,不在发送认证
        /*  $ansData = M('RealnameApi')
             ->where(array('req_data' => $this->json_encode_ex($data),'version'=>'2'))
             ->getField('ans_data');
         eblog("实名认证接收参数集",$ansData,'realname_'.date("Ymd"));
         if ($ansData) {
             return $this->callback($ansData);
         } */

        $apiData = array(
            'serial_no' => $serialNo,
            'req_data' => $this->json_encode_ex($data),
            'version' =>'2',  // 实名新数据版本控制
            'req_time' => time()
        );
        $id=M('RealnameApi')->add($apiData);

        $transDate = date('Ymd');
        $transTime = date('His', time());
        //$url = 'http://119.57.140.199:8880/RealNameAuth/MSBank/realNameAuth';
        $url = trim(C('realname_url'));
        /*
         *@ Version 版本号
         *@ MER_REQ_SEQ 请求流水号
         *@ IPS_CODE 业务编码(固定)
         *@ VERIFY_TYPE 认证类型
         *@ CARD_TYPE 卡类型 0-借记卡 1-贷记卡
         *@ ACC_NO
         */
        $postData = array(
            'acc_name' => trim($data['acc_name']),
            'acc_no' => trim($data['acc_no']),
            'card_type' =>'2',
            'cert_no' => strtoupper($data['cert_no']),
            'ips_code' =>'IP1001',
            'mer_req_seq' => $serialNo,
            'verify_type' => $this->verify_type,
        );
        $ansData = $this->geturl($url, $postData,'realname_public_key','realname_private_key');
        eblog("实名认证接收参数集",$ansData,'realname_'.date("Ymd"));
        if($ansData =='sign_error'){
            return false;
        }
        return $this->callback($ansData);
    }
    /*
        * 实名认证返回结果处理
        */
    function callback($respContent) {
        $data= json_decode($respContent,true);
        $serialNo = empty($data['ReqSerialNo'])?$data['MER_REQ_SEQ']:$data['ReqSerialNo'];
        $reqData=M('RealnameApi')->where(array('serial_no' => $serialNo))->getField('req_data');
        M('RealnameApi')->where(array('req_data' => $reqData,'ans_time'=>0))->save(array('ans_data' => $respContent, 'ans_time' => time()));
        $validateStatus =empty($data['VALIDATE_STATUS'])?'':$data['VALIDATE_STATUS'];
        $old_validateStatus =empty($data['ValidateStatus'])?'':$data['ValidateStatus'];
        if ($validateStatus == '00' || $old_validateStatus =='00') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * [post 提交数据]
     *
     * @param array $data
     * @param string url格式字符串
     * @param public 公共密钥
     * @param private 私密密钥
     * @return string or array
     */
    public function geturl($url, $data = array(),$public='',$private = '') {
        //生成验签以及参数序列化
        $datastring = $this->curldata($data,$private,true);
        $new = array();
        //将key值转换为大写,直接写大写字母会出错
        foreach($datastring as $key => $val){
            $new[strtoupper($key)] = $val;
        }
        // print_r($new);exit;
        $datas = $this->json_encode_ex($new);
        $data_array = array(
            'REQ_DATA'=>urlencode($datas)
        );
        //curl传参
        $result = $this->httpGet($url,$data_array);
        eblog("实名认证getUrl收参数集",$result,'realname_'.date("Ymd"));
        //解析返回数据
        $decresult = urldecode($result);
        //验证返回参数是否符合规则
        $splitdata = json_decode($decresult, true );
        $check = $this->checkSignMsg($splitdata,$public);
        eblog("实名认证sign收参数集",$check,'realname_'.date("Ymd"));
        if ($check) {
            return $decresult;
        } else {
            return 'sign_error';
        }
    }
}