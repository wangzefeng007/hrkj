<?php
class userAction extends baseAction
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
    /*
    *	注册接口
    *
    */
    public function register()
    {
        $data = array();
        $type = intval(I('type',1));
        $mobile = I('mobile');//用户手机号
        $password = I('password');
        $code = I('code');
        $invite_mobile = I('invite_mobile');//推荐人手机号
        $usid = $this->usid;
        $lfid = intval(I('lfid'));
        $lfid=13;//待审核员工
        $this->vaild_params('is_mobile',$mobile,'请输入手机号');
        $this->vaild_params('is_empty',$password,'请输入密码');
        $this->vaild_params('is_empty',$code,'请输入手机验证码');
        $this->vaild_params('eq',array($code,S($mobile.'_reg_code')),'注册验证码错误');
        $this->loadModel('userSaler');
        $this->loadModel('levelFee');
        $this->vaild_params(array($this->model['userSaler'],'checkRegister'),$mobile,'该手机号码已注册！',false);
        if ($type == 1)
        {
//			$this->vaild_params('is_mobile',$invite_mobile,'请输入推荐人手机号');
            if($invite_mobile){
                $parent = $this->loadModel('userSaler')->getInfo('id,depth_pid,name',array('mobile'=>$invite_mobile));
            }
            if(empty($parent['id'])){
                $parent['id']=1560;//无推荐人，默认选中汇融钱包为最高级
            }
            $this->vaild_params('is_empty',$parent['id'],'推荐人不存在！');
            $data['pid'] = $parent['id'];
            $data['depth_pid'] = empty($parent['depth_pid'])?$parent['id']:$parent['depth_pid']."-".$parent['id'];
            $level_fee = $this->model['levelFee']->getLowest('id');
            $data['lfid'] = $level_fee['id'];
        }
        elseif ($type == 2)
        {
            $this->vaild_params('is_empty',$usid,'缺少上级商户ID');
            $this->vaild_params('is_empty',$lfid,'请选择用户等级');
            $level_i = $this->model['levelFee']->getInfoByid($lfid);
            $level_p = $this->model['userSaler']->getLevelfee($usid);
            $this->vaild_params('is_empty',$level_i,'级别不存在！');
            $this->vaild_params('is_empty',$level_p,'您当前级别无法发展新用户！');
            if ($level_p['upgrade_fee']<$level_i['upgrade_fee'])
            {
                $this->response(INTERNAL_ERROR,'费率级别不合法，无法设置比自己级别更高的下级用户！');
            }
            $parent = $this->model['userSaler']->getInfo('id,depth_pid,name',array('id'=>$usid));
            $data['pid'] = $usid;
            // $data['depth_pid'] = $parent['depth_pid']."-".$parent['id'];
            $data['depth_pid'] = empty($parent['depth_pid'])?$parent['id']:$parent['depth_pid']."-".$parent['id'];
            $data['lfid'] = $lfid;
        }


        $data['mobile'] = $mobile;
        $data['password'] = $password;
        $data['invite_mobile'] = $invite_mobile;
        $data['addtime'] = time();
        // $data['status_data'] = serialize(format_struct('user_status_data','',0,0));
        $data['status'] = 2;
        $rs = $this->model['userSaler']->add($data);
        if($rs)
        {
            $smsmsg = $parent['name']."您好！您推荐的".$mobile."成功注册成为汇融钱包的商户。赶紧让他完善资料通过审核吧，他刷卡你赚钱哟。";
            sendsms($invite_mobile,$smsmsg);
        }
        /*if ($rs)
        {
            Vendor('MsgCenter.MsgCenter');

            //注册成功后--发送异步消息 通知用户
            $msg = array();
            $msg['type'] = 'reg';
            $msg['target'] = array (
                'mobile' => $mobile,
            );
            $msg['params'] = array (
                'mobile' => $mobile,
            );
            MsgCenter::send($msg['target'],$msg['type'],$msg['params']);
            // if (TASK_MSG) send_task('MsgTask','sendMsg',$msg);

            //注册成功后--发送异步消息 通知上级商户
            $msg = array();
            $msg['type'] = 'regSuperior';
            $msg['target'] = array (
                'mobile' => $invite_mobile,
            );
            $msg['params'] = array (
                'mobile' => $mobile,
                'name' => $parent['name'],
            );
            MsgCenter::send($msg['target'],$msg['type'],$msg['params']);
            // if (TASK_MSG) send_task('MsgTask','sendMsg',$msg);
        }*/
        $this->apiOut($rs,false);
    }

    /*
    *	登录接口
    */
    public function login()
    {
        $mobile = I('mobile');
        $password = I('password');
        $this->vaild_params('is_mobile',$mobile,'请填写登录手机号');
        $this->vaild_params('is_empty',$password,'请填写登录密码');
        $user = $this->loadModel('userSaler')->getInfo("*",array('mobile'=>$mobile));

        if (!$user || ($password != $user['password']))
        {
            $this->response(DATA_EMPTY,'帐号不存在或密码错误');
        }
        if ($user['status'] == -1)
        {
            $this->response(DATA_EMPTY,'您的帐号暂未通过审核或被冻结，请联系管理员');
        }

        $_params = $this->get_params(array('machine_code','os'));
        // $this->vaild_params('is_empty',$_params['machine_code'],'请传入机器码');

        //二次认证流程
        /* if ($_params['machine_code'])	//判断登录机器码
        {
            if ($user['machine_code'])	//判断用户机器码
            {
                if ($_params['machine_code'] != $user['machine_code'])	//判断用户机器码与登录机器码是否一致
                {
                    $user['verify_ag'] = $user['status'] == 1?1:0;	//是否二次认证 1-需要二次认证,0-无需二次认证
                }
            }
            else
            {
                $data['machine_code'] = $_params['machine_code'];	//用户机器码不存在,写入登录机器码
            }
        } */

        $data['os'] = $_params['os'];
        $data['logintime'] = time();
        $rs = $this->loadModel('userSaler')->update($data,array('id'=>$user['id']));

        $data['usid'] = $user['id'];
        $data['ip'] = I('server.REMOTE_ADDR');
        //$login_id = $this->loadModel('userSalerLogin')->add($data);

        $access_token = $this->createAccesstoken($user['id'],$user['mobile']);
        if (!$access_token)
        {
            $this->apiOut(false);
        }

        $field = array(
            'pay_type'=>'id as ptid,name as pt_name,api',
            'level_pay_limit'=>'lfid,min,max,day_min,day_max',
        );
        $join = array();
        $join[] = array('level_pay_limit','id','ptid');
        $where = array(
            'pay_type.status' => 1,
            'level_pay_limit.lfid' => $user['lfid'],
        );
        $day_max = 0;
        $pt_info = $this->loadModel('payType')->getJoinList($field,$join,$where,'sort desc,ptid asc');
        if ($pt_info['list'])
        {
            foreach ($pt_info['list'] as $val)
            {
                if ($val['day_max']) $day_max += $val['day_max'];
            }
        }

        $response = array();
        $user['level'] = $this->loadModel('levelFee')->getInfoByid($user['lfid']);
        $user['auth_file'] = format_struct('user_auth_file',unserialize($user['profile']));
        $user['status_data'] = format_struct('user_status_data',unserialize($user['status_data']),0,0);
        // $user['pt_info'] = $pt_info['list'];
        $user['day_max'] = $day_max;
        $user['login_id'] = $login_id;	//登录标识id

        //$is_pay = D('OrderFtf')->where(array('usid' => $user['id'],'status' => 1,))->find();
        //$user['is_pay'] = $is_pay?1:0;
        $user['is_pay'] = 1;
        // if ($user['mobile'] == '18900007777')
        // {
        // $user['is_pay'] = 0;
        // }
        //查找推荐人姓名
        if($user['pid'] != 0 ){
            $invite_result = $this->loadModel('userSaler')->getInfo("name,mobile",array('id'=>$user['pid']));
        }
        //弹窗提醒
        if($user['status'] == 1){
            $LOGIN_HINT_TITLE =   LOGIN_HINT_TITLE;
            $LOGIN_HINT_MSG = LOGIN_HINT_MSG;
            $LOGIN_HINT_BTN = LOGIN_HINT_BTN;
        }elseif ($user['status'] == 0){
            $LOGIN_HINT_TITLE =  LOGIN_HINT_TITLE_1;
            $LOGIN_HINT_MSG = LOGIN_HINT_MSG_1;
            $LOGIN_HINT_BTN = LOGIN_HINT_BTN_1;
        }else{
            $LOGIN_HINT_TITLE =  LOGIN_HINT_TITLE_2;
            $LOGIN_HINT_MSG = LOGIN_HINT_MSG_2;
            $LOGIN_HINT_BTN = LOGIN_HINT_BTN_2;
        }
        $user['invite_name'] = empty($invite_result['name'])?'':$invite_result['name'];
        $user['invite_mobile'] = empty($invite_result['mobile'])?'':$invite_result['mobile'];
        $response['access_token'] = $access_token;
        $response['profile'] = format_struct('user_profile',$user);
        //银行名称
        $BankName = $this->loadModel('settingBank')->getInfo("name",array('id'=>$response['profile']['bank']));
        $response['profile']['bank_real_name'] = $BankName['name'];

        $response['global'] = array(
            'share_title' => SHARE_TITLE,
            'share_msg' => SHARE_MSG,
            'share_url' => SHARE_URL.'/invite_mobile/'.$user['mobile'],
            'op_tel' => APP_OP_TEL,
            'op_qq' => APP_OP_QQ,
            'op_weixin' => APP_OP_WEIXIN,
            'hint' => LOGIN_HINT,
            'hint_title' =>$LOGIN_HINT_TITLE,
            'hint_msg' =>$LOGIN_HINT_MSG,
            'hint_btn' =>$LOGIN_HINT_BTN,
            'risk_pact_link' => RISK_PACT_LINK,
        );
        $this->apiOut($response);
    }


    /**
     *	上传/修改用户头像
     */
    public function headpic()
    {
        $_params = $this->get_params(array('headpic'));
        $this->vaild_params('is_empty',$_params['headpic'],'请选择要上传的图片');
        $data = array();
        import("@.Tool.file");
        $data['headpic'] = file::tmp_to_final($_params['headpic'],'image','user');
        $rs = $this->loadModel('userSaler')->update($data,array('id'=>$this->usid));
        $rs = $rs?$data:false;
        $this->apiOut($data);
    }

    /**
     *	完善个人资料
     */
    public function profile()
    {
        //上传类型
        $type = I('type',0);	//上传类型: 1-上传文字资料, 2-上传照片, 3-上传视频, 4-风险承诺书
//        $this->vaild_params('is_empty',$type,'请选择上传类型');
        $params_arr_1 = array('name','card_no','bank','bank_name','bank_no');
        $params_arr_2 = array('card_front','card_back','card_hand');
        $params_arr_3 = array('video');
        // $params_arr_4 = array('risk_pact');
        switch ($type)
        {
            case 1 :
                $params_arr = $params_arr_1;
                $flag = 'info';
                break;
            case 2 :
                $params_arr = $params_arr_2;
                $flag = 'img';
                break;
            case 3 :
                $params_arr = $params_arr_3;
                $flag = 'video';
                break;
            // case 4 :
            // $params_arr = $params_arr_4;
            // $flag = 'risk_pact';
            // break;
        }
        $_params = $this->get_params($params_arr);
        foreach($_params as $value)
        {
            $this->vaild_params('is_empty',$value,'资料填写不完整');
        }
        //读取当前用户信息
        $user =  D('userSaler')->getInfo('*',array('id'=>$this->usid));

        $data = array();
        $auth_file = array();
        switch ($type)
        {
            case 1 :
                $data['name'] = $_params['name'];
                $data['card_no'] = $_params['card_no'];
                $data['bank'] = $_params['bank'];
                $data['bank_name'] = $_params['bank_name'];
                $data['bank_no'] = $_params['bank_no'];
                $data['bank_address'] = '';
                //$data['bank_type'] = $_params['bank_type'];
                $data['bank_type'] = '000000000000';
                $result =  D('userSaler')->getInfo('id',array('card_no'=> $data['card_no'],'id'=>array('neq',$this->usid)));
                if ($result){
                    $this->response(INTERNAL_ERROR,'该身份证已存在，请重新输入！');
                }
                $reqData = array(
                    'acc_name'=>$_params['name'],
                    'cert_no'=>$_params['card_no'],
                    'acc_no'=>$_params['bank_no'],
                    'phone' =>$user['mobile']
                );
                if ($this->realNameAuth){
                    if (!$this->realname_verify($reqData)) {
                        $this->response(INTERNAL_ERROR,'实名认证失败,请核对数据后再试!');
                    }
                }
                break;
            case 2 :
                $auth_file['card_front'] = $_params['card_front'];
                $auth_file['card_back'] = $_params['card_back'];
                $auth_file['card_hand'] = $_params['card_hand'];
                // $auth_file['bank_hand'] = $_params['bank_hand'];
                // $auth_file['bank_back'] = $_params['bank_back'];
                break;
            case 3 :
                $auth_file['video'] = $_params['video'];
                break;
            // case 4 :
            // $auth_file['risk_pact'] = $_params['risk_pact'];
            // break;
        }

        $user['status_data'] = format_struct('user_status_data',unserialize($user['status_data']),0,0);
        $user['status_data'][$flag] = 1;
        foreach ($user['status_data'] as $val)
        {
            if (!$val)
            {
                $data['status'] = 2;
                break;
            }
            $data['status'] = 0;
        }
        import("@.Tool.file");
        if ($auth_file)
        {
            foreach($auth_file as $key=>&$value)
            {
                $type = ($key=='video')?'video':'image';
                $value = file::tmp_to_final($value,$type,'user');
                $auth_file[$key] = $value;
            }
        }

        //视频转码
        // send_task('fileTask','videoTrans',ROOT_PATH.$auth_file['video']);	//异步视频转码
        if (strtolower(substr(PHP_OS, 0, 3)) != 'win' && $auth_file['video']) {				//win系统不执行转码
            $res = $this->videoTrans(ROOT_PATH.$auth_file['video']);	//同步视频转码
        }
        $data['status_data'] = serialize($user['status_data']);
        if ($auth_file)
        {
            $arr = unserialize($user['profile']);
            foreach($auth_file as $key => $val)
            {
                $arr[$key] = $val;
            }
            $data['profile'] = serialize($arr);
        }
        $rs = $this->loadModel('userSaler')->update($data,array('id'=>$this->usid));

        if ($rs)
        {
            //读取当前用户信息
            $user =  D('userSaler')->getInfo('*',array('id'=>$this->usid));

            //如果用户资料已经完善,则判断上级商户是否满足自动升级
            if ($data['status'] == 0)
            {
                //如果用户完善资料，短信提醒用户审核中可以进行小额支付
                $smsmsg = $user['name']."您好！您目前的账户正在审核中，当前您可以进行小额体验支付，赶紧试试吧！客服电话400-699-8890";
                sendsms($user['mobile'],$smsmsg);
                eblog("",'============================用户推广升级检测日志==========================','child_upgrade_'.date("Ymd"));
                eblog("推广升级流程 - 当前完善资料用户 - user",$user,'child_upgrade_'.date("Ymd"));
                //如果存在上级商户
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
                        $lf_upable = D('levelFee')->getList('*',$where,'level desc');
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
            $access_token = $this->createAccesstoken($user['id'],$user['mobile']);
            if (!$access_token)
            {
                $this->apiOut(false);
            }
            $response = array();
            $user['level'] = $this->loadModel('levelFee')->getInfoByid($user['lfid']);
            $user['auth_file'] = format_struct('user_auth_file',unserialize($user['profile']));
            $user['status_data'] = format_struct('user_status_data',unserialize($user['status_data']),0,0);
            $response['access_token'] = $access_token;
            $response['profile'] = format_struct('user_profile',$user);
        }

        $this->apiOut($response);
    }

    /*
    *	注册短信下发
    * @todo 	1 、手机号码合法性验证
    *					2、短信正式下发
    */
    public function appRegsms()
    {
        $Os = I('Os');	//1-安卓，2-苹果
        $mobile = I('mobile');//注册手机号
        $reg_time = I('reg_time');//请求时间（格式：20170821144650）
        $invite_mobile = I('invite_mobile');//推荐人手机号
        $resule_sign = I('sign');//签名值
        $Key ='8765432187djhjg';//加盐密钥
        $ip = get_client_ip();
        $mingwen = $Os.$mobile.$reg_time.$invite_mobile.$Key;//明文拼接（Os+mobile+reg_time+invite_mobile+key）
        $SIGN = strtoupper(md5($mingwen));//计算md5,转化为大写
        eblog("注册短信下发",'IP='.$ip.',mingwen='.$mingwen.',SIGN='.$SIGN,'regsms_'.date("Ymd"));
        if ($Os==1&&$Os==2){
            eblog("注册短信下发-Os不一致",'mingwen='.$mingwen,'regsms_'.date("Ymd"));
            $this->response(PARAMS_ERROR,'参数错误！');
        }
        $this->vaild_params('is_mobile',$mobile,'请填写正确的手机号码！');
        $this->vaild_params('is_empty',$reg_time,'参数错误!');
        $this->vaild_params('is_empty',$resule_sign,'参数错误!');

        if ($resule_sign !==$SIGN){
            eblog("注册短信下发-签名值不一致",'mingwen='.$mingwen,'regsms_'.date("Ymd"));
            $this->response(PARAMS_ERROR,'参数错误！');
        }
        $now = strtotime(date("YmdHis",time()));
        $regtime = strtotime($reg_time);
        if (abs($now - $regtime) > 60*5){
            eblog("注册短信下发-请求时间超时",'mingwen='.$mingwen,'regsms_'.date("Ymd"));
            $this->response(99997,'参数错误！');
        }

        eblog("注册短信下发",'客户端ip:'.$ip.',Os:'.$Os.'手机号:'.$mobile.',推荐人手机号:'.$invite_mobile,'regsms_'.date("Ymd"));
//        $rs = true;
//        if ($rs)
//        {
//            $sms_verify = randcode(4);
//            session('sms_verify',$sms_verify);
//            $data = array('sms_verify'=>$sms_verify);
//        }
//        //print_r($data);
//        $this->apiOut($data);
        //添加验证手机注册状况判定
        $this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$mobile,'该手机号码已注册！',false);

        $str = substr($mobile,0,3);
        if ($str == '170')  // 源代码|| $str == '177'，177号段不可注册
        {
            $this->response(INTERNAL_ERROR,'系统暂不支持当前号段注册');
        }

        //$this->vaild_params('is_empty',!S($mobile.'_sms_lock'),'60秒间隔未到,请稍后再试');
        if (!$Os)
        {
            //非客户端提交,检查系统验证码
            $sms_verify = I('sms_verify');	//系统验证码
            $this->vaild_params('is_empty',$sms_verify,'系统验证码不能为空!');
            $this->vaild_params('eq',array($sms_verify,session('sms_verify')),'系统验证码错误!');
        }

        $this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$mobile,'该手机号码已注册！',false);
        $code = randcode(4);
        $sms = str_replace("##code##",$code,C('regsms'));
        $ip = get_client_ip();
        eblog("老注册短信下发",'客户端IP:'.$ip.'手机号：'.$mobile.',短信内容：'.$sms,'sms_'.date("Ymd"));
        $rs = S($mobile.'_sms_lock','1',60);
        $rs = S($mobile.'_reg_code',$code,60000);
        $rs = sendsms($mobile,$sms);
        $data = array();
        if ($rs)
        {
            $sms_verify = randcode(4);
            session('sms_verify',$sms_verify);
            $data = array('sms_verify'=>$sms_verify);
        }
        //print_r($data);
        $this->apiOut($data);
    }
    /*
  *	注册短信下发 防攻击
  * @todo 	1 、手机号码合法性验证
  *					2、短信正式下发
  */
    public function duanxinuiwer()
    {
        $is_app = I('is_app',0);	//是否客户端提交
        $recode = I('recode');
        $mobile = I('mobile');
        $invite_mobile = I('invite_mobile');
        $this->vaild_params('is_empty',$recode,'图片验证码不能为空!');
        eblog("新用户注册",'is_app:'.$is_app.',mobile:'.$mobile.',invite_mobile:'.$invite_mobile,'new_reg_'.date("Ymd"));
        if ($is_app==0){
            $this->vaild_params('is_mobile',$invite_mobile,'请输入推荐人手机号');
            if($invite_mobile){
                $parent = $this->loadModel('userSaler')->getInfo('id,depth_pid,name',array('mobile'=>$invite_mobile));
                if(empty($parent['id'])){
                    $this->vaild_params('is_empty',$parent['id'],'参数错误');
                }
            }
        }

        $verify = session('verify');
        unset($_SESSION['verify']);
        if ($recode != $verify) {
            $this->response(INTERNAL_ERROR,'验证码错误！');
            exit;
        }
        $str = substr($mobile,0,3);
        //添加验证手机注册状况判定
        $this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$mobile,'该手机号码已注册！',false);
        if ($str == '170')  // 源代码|| $str == '177'，177号段不可注册
        {
            $this->response(INTERNAL_ERROR,'系统暂不支持当前号段注册');
        }
        //$this->vaild_params('is_empty',!S($mobile.'_sms_lock'),'60秒间隔未到,请稍后再试');
        if (!$is_app)
        {
            //非客户端提交,检查系统验证码
            $sms_verify = I('sms_verify');	//系统验证码
            $this->vaild_params('is_empty',$invite_mobile,'推荐人不能为空!');
            $this->vaild_params('is_empty',$sms_verify,'系统验证码不能为空!');
            $this->vaild_params('eq',array($sms_verify,session('sms_verify')),'系统验证码错误!');
        }
        $this->vaild_params('is_mobile',$mobile,'请填写正确的手机号码！');
        //$this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$mobile,'该手机号码已注册！',false);
        $code = randcode(4);
        $sms = str_replace("##code##",$code,C('regsms'));
        $rs = S($mobile.'_sms_lock','1',60);
        $rs = S($mobile.'_reg_code',$code,60000);
        $ip = get_client_ip();
        eblog("新注册短信下发",'客户端IP:'.$ip.'手机号：'.$mobile.',短信内容：'.$sms,'new_sms_'.date("Ymd"));
        $rs = sendsms($mobile,$sms);
        //$data = array();
        if ($rs)
        {
            $sms_verify = randcode(4);
            session('sms_verify',$sms_verify);
            $data = array('sms_verify'=>$sms_verify);
        }
        //print_r($data);
        $this->apiOut($data);
    }

    /*
    *	重置密码
    */
    public function resetpwd()
    {
        $_params = $this->get_params(array('mobile','code','password'));

        $this->vaild_params('is_mobile',$_params['mobile'],'请输入手机号');
        $this->vaild_params('is_empty',$_params['password'],'请输入密码');
        $this->vaild_params('is_empty',$_params['code'],'请输入手机验证码');
        $this->vaild_params('eq',array($_params['code'],S($_params['mobile'].'_resetpwd_code')),'验证码错误');
        $data = array('password'=>$_params['password']);
        $rs = $this->loadModel('userSaler')->update($data,array('mobile'=>$_params['mobile']));
        // _resetpwd_code
        S($_params['mobile'].'_resetpwd_code',NULL);
        $this->apiOut($rs);
    }

    /*
    *	重设密码短信下发
    */
    public function appResetpwdsms()
    {

        $Os = I('Os');	//1-安卓，2-苹果
        $mobile = I('mobile'); //注册手机号
        $reg_time = I('reg_time');//请求时间（格式：20170821144650）
        $resule_sign = I('sign');//签名值
        $Key ='8765432187djhjg';//加盐密钥
        $mingwen = $Os.$mobile.$reg_time.$Key;//明文拼接（Os+mobile+reg_time+invite_mobile+key）
        $SIGN = strtoupper(md5($mingwen));//计算md5,转化为大写
        $ip = get_client_ip();
        eblog("重设密码短信下发",'IP='.$ip.',mingwen='.$mingwen.',SIGN='.$SIGN,'pwdsms_'.date("Ymd"));
        if ($Os==1&&$Os==2){
            eblog("重设密码短信下发-Os不一致",'mingwen='.$mingwen,'pwdsms_'.date("Ymd"));
            $this->response(PARAMS_ERROR,'参数错误！');
        }
        $this->vaild_params('is_mobile',$mobile,'请填写正确的手机号码！');
        $this->vaild_params('is_empty',$reg_time,'参数错误!');
        $this->vaild_params('is_empty',$resule_sign,'参数错误!');

        if ($resule_sign !==$SIGN){
            eblog("重设密码短信下发-签名值不一致",'mingwen='.$mingwen,'pwdsms_'.date("Ymd"));
            $this->response(PARAMS_ERROR,'参数错误！');
        }
        $now = strtotime(date("YmdHis",time()));
        $regtime = strtotime($reg_time);
        if (abs($now - $regtime) > 60*5){
            eblog("重设密码短信下发-请求时间超时",'mingwen='.$mingwen,'pwdsms_'.date("Ymd"));
            $this->response(99997,'参数错误！');
        }
        eblog("忘记密码短信下发",',mobile:'.$mobile,'pwdsms_'.date("Ymd"));
        $this->vaild_params('is_mobile',$mobile,'参数错误！');
        $this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$mobile,'该手机号码尚未注册！');
        $code = randcode(4);
        $sms = str_replace("##code##",$code,C('resetpwdsms'));
        $rs = S($mobile.'_resetpwd_code',$code,60000);
        $rs = sendsms($mobile,$sms);
        $this->apiOut($rs,false);
    }

    /*
    *	用户账户信息,按通道获取
    */
    public function account()
    {
        //读取相应等级的结算费率
        $lfid = intval(I('lfid'));
        $this->vaild_params('is_empty',$lfid,'缺少参数,lfid');
        $rs = $this->loadModel('levelFee')->getRate($lfid);
        $rs_fee_rate = $rs['list'];


        $pay_type = $this->loadModel('payType')->getInfo("id,name",array("api"=>str_replace("Action","","qrcode::pay")));


        $today = strtotime(date('Y-m-d'));
        $mapAccount['usid'] = $this->usid;
        $mapAccount['ptid'] = $pay_type['id'];//11;

        $totalMoney = $this->loadModel('userSalerAccount')->where($mapAccount)->find();//field('SUM(money) as money')->select();
        if($totalMoney)
        {
            $total['business_total'] = $totalMoney['total_money'];
        }



        $mapFtf['usid'] = $this->usid;
        $mapFtf['status'] = 1;//array('gt',0);
        $mapFtf['paytime'] = array('gt',$today);
        $mapFtf['ptid'] = $pay_type['id'];;

        $todayMoney = $this->loadModel('orderFtf')->where($mapFtf)->sum('money');//field('SUM(money) as money')->select();
        //print_r($todayMoney);
        if($totalMoney)
        {
            $total['business_usable'] = $totalMoney['money'] - $todayMoney;
        }
        else
        {
            $total['business_usable'] = $totalMoney['money'];
        }

        $dataTotal = array('business_total'=>$total['business_total'],
            'business_usable'=>$total['business_usable']);
        $rs = D('userSalerAccountTotal')->update($dataTotal,array('usid'=>$this->usid));




        //读取当前用户的结算通道和金额
        //$paytype = $this->loadModel('userSalerAccount')->getAccount($this->usid);
        $fields = 'shop_total,ftf_total,split_total,split_usable,business_total,business_usable,commission_total,commission_usable';
        $total = $this->loadModel('userSalerAccountTotal')->getInfo($fields,array('usid'=>$this->usid));
        if (!$total) $total = array('shop_total'=>0.00,'ftf_total'=>0.00,'split_total'=>0.00,'split_usable'=>0.00,'business_total'=>0.00,'business_usable'=>0.00,'commission_total'=>0.00,'commission_usable'=>0.00);
        /*
        //费率通道整合
        $paytype[] = array(
            'ptid' => '-1',
            'pt_name' => '分润',
            'pc_name' => '',
            'money' => 0,
            'cash_money' => 0,
            'total_money' => 0,
        );
        $arr = reset_array_key($paytype,'ptid');
        // dump($arr);
        // dump($rs_fee_rate);
        if ($arr)
        {
            foreach ($arr as $key => $val)
            {
                $arr[$key]['fee_rate'] = array();
            }
        }
        if ($rs_fee_rate)
        {
            foreach ($rs_fee_rate as $key => $val)
            {
                if ($arr[$val['ptid']])
                {
                    if ($val['ptid'] == -1)
                    {
                        //分润通道,只保留手续费最低的通道(通常最低是T+1)
                        if (!$arr[$val['ptid']]['fee_rate'][0] || $arr[$val['ptid']]['fee_rate'][0]['fee_static']>$val['fee_static'])
                        {
                            $arr[$val['ptid']]['fee_rate'][0] = $val;
                        }
                    }
                    else
                    {
                        $arr[$val['ptid']]['fee_rate'][] = $val;
                    }
                }
            }
        }
        */


        $paytype = array();//array_values($arr);

        $rs =array('paytype'=>$paytype,'total'=>$total);
        $this->apiOut($rs);
    }

    /*
    *	用户账户信息总额，不区分通道
    */
    public function accountTotal()
    {
        $rs = $this->loadModel('userSalerAccountTotal')->getInfo('*',array('usid'=>$this->usid));
        $this->apiOut($rs);
    }

    /*
    *	统计下级商户数量
    */
    public function childLevelsCount()
    {
        $user = $this->usinfo;
        $rs_lf = D('levelFee')->getInfo('*',array('id'=>$user['lfid']));
        $rs = D('levelFee')->getList('id,name',array('level'=>array('elt',$rs_lf['level'])),'level desc');
        foreach($rs['list'] as $value)
        {
            $levels[$value['id']] = $value;
        }

        //统计直接下级用户数
        $where = array(
            'pid' => $this->usid,
        );
        $rs_direct = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);

        //统计间接下级用户数
        $str = $user['depth_pid']?"{$user['depth_pid']}-{$user['id']}-%":"{$user['id']}-%";
        $where = array(
            'depth_pid' => array('like', "{$str}"),
        );
        $rs_indirect = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);

        $direct = reset_array_key($rs_direct['list'],'lfid','count');
        $indirect = reset_array_key($rs_indirect['list'],'lfid','count');

        $data = array();
        if ($levels)
        {
            $data['total'] = 0;
            foreach ($levels as $key => $val)
            {
                $count = (int)($direct[$key] + $indirect[$key]);
                $data['list'][] = array(
                    'lfid' => $val['id'],
                    'lf_name' => $val['name'],
                    'direct_count' => (int)$direct[$key],
                    'indirect_count' => (int)$indirect[$key],
                    'count' => $count,
                );
                $data['total'] += $count;
            }
        }
        $this->apiOut($data);
    }
    /*
    *	统计下级商户数量（总数，已认证，未认证）
    */
    public function childLevelsCounts()
    {
        $user = $this->usinfo;
        $rs_lf = D('levelFee')->getInfo('*',array('id'=>$user['lfid']));
        $rs = D('levelFee')->getList('id,name',array('level'=>array('elt',$rs_lf['level'])),'level desc');
        foreach($rs['list'] as $value)
        {
            $levels[$value['id']] = $value;
        }

        //统计直接下级用户数
        $where = array(
            'pid' => $this->usid,
        );
        $rs_direct = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);

        //统计间接下级用户数
        $str = $user['depth_pid']?"{$user['depth_pid']}-{$user['id']}-%":"{$user['id']}-%";
        $where = array(
            'depth_pid' => array('like', "{$str}"),
        );
        $rs_indirect = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);
        //统计直接下级已认证用户数
        $where = array(
            'pid' => $this->usid,
            'status' => 1,
        );
        $rs_certifiedDirect = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);
        //统计间接下级已认证用户数
        $str = $user['depth_pid']?"{$user['depth_pid']}-{$user['id']}-%":"{$user['id']}-%";
        $where = array(
            'depth_pid' => array('like', "{$str}"),
            'status' => 1,
        );
        $rs_certifiedIndirect = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);
        //直接下级人数
        $direct = reset_array_key($rs_direct['list'],'lfid','count');
        //间接下级人数
        $indirect = reset_array_key($rs_indirect['list'],'lfid','count');
        //直接下级已认证人数
        $certifiedDirect = reset_array_key($rs_certifiedDirect['list'],'lfid','count');
        ///间接下级已认证人数
        $certifiedIndirect = reset_array_key($rs_certifiedIndirect['list'],'lfid','count');
        $data = array();
        if ($levels)
        {
            $data['count'] = 0;
            $data['certifiedcount'] = 0;
            $data['unverifiedcount'] = 0;
            foreach ($levels as $key => $val)
            {
                $count = (int)($direct[$key] + $indirect[$key]);
                $certifiedcount = (int)($certifiedDirect[$key] + $certifiedIndirect[$key]);
                $unverifiedcount = $count-$certifiedcount;
                $data['count'] += $count;
                $data['certifiedcount'] += $certifiedcount;
                $data['unverifiedcount'] += $unverifiedcount;
            }
        }
        $this->apiOut($data);
    }
    /*
	*	统计已认证下级商户数量
	*/
    public function CertifiedLevelsCount()
    {
        $user = $this->usinfo;
        $rs_lf = D('levelFee')->getInfo('*',array('id'=>$user['lfid']));
        $rs = D('levelFee')->getList('id,name',array('level'=>array('elt',$rs_lf['level'])),'level desc');
        foreach($rs['list'] as $value)
        {
            $levels[$value['id']] = $value;
        }

        //统计直接下级用户数
        $where = array(
            'pid' => $this->usid,
            'status' =>1,
        );
        $rs_direct = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);

        //统计间接下级用户数
        $str = $user['depth_pid']?"{$user['depth_pid']}-{$user['id']}-%":"{$user['id']}-%";
        $where = array(
            'depth_pid' => array('like', "{$str}"),
            'status' =>1,
        );
        $rs_indirect = D('userSaler')->group('lfid')->getList('count(*) as count,lfid',$where);

        $direct = reset_array_key($rs_direct['list'],'lfid','count');
        $indirect = reset_array_key($rs_indirect['list'],'lfid','count');

        $data = array();
        if ($levels)
        {
            $data['total'] = 0;
            foreach ($levels as $key => $val)
            {
                $count = (int)($direct[$key] + $indirect[$key]);
                $data['list'][] = array(
                    'lfid' => $val['id'],
                    'lf_name' => $val['name'],
                    'direct_count' => (int)$direct[$key],
                    'indirect_count' => (int)$indirect[$key],
                    'count' => $count,
                );
                $data['total'] += $count;
            }
        }
        $this->apiOut($data);
    }
    /*
    *	下级商户等级信息统计
    */
    public function childLevels()
    {
        $under = I('under',1);
        $rs = $this->loadModel('userSaler')->getChildLevels($this->usid,$under);
        if (!$rs) $this->apiOut($rs);
        $childs = array();
        foreach($rs as $value)
        {
            if (!isset($childs[$value['lfid']]))
            {
                $childs[$value['lfid']]['lfid'] = $value['lfid'];
                $childs[$value['lfid']]['count'] = 1;
                $childs[$value['lfid']]['lf_name'] = $value['level']['name']?$value['level']['name']:'未知等级';
            }
            else
            {
                $childs[$value['lfid']]['count'] += 1;
            }
        }
        $data['list'] = array_values($childs);
        $this->apiOut($data);
    }

    /*
    *	直属下级商户信息列表
    */
    public function childInfoList()
    {
        $lfid = intval(I('lfid'));
        $this->vaild_params('is_empty',$lfid,'请传入等级ID');
        $fields = "id,mobile,name,lfid,status,addtime";
        $rs = $this->loadModel('userSaler')->getList($fields,array('pid'=>$this->usid,'lfid'=>$lfid),'addtime desc',true);
        if ($rs['list'])
        {
            foreach ($rs['list'] as $key => &$val)
            {
                $val['status'] = status_desc('USER_SALER_STATUS',$val['status']);
                // $val['name'] = hide_name($val['name']);
            }
        }
        $rs = $rs['list']?$rs:false;
        $this->apiOut($rs);
    }
    /*
    *@desc	已认证直属下级商户信息列表
    */
    public function CertifiedInfoList()
    {
        $lfid = intval(I('lfid'));
        $this->vaild_params('is_empty',$lfid,'请传入等级ID');
        $fields = "id,mobile,name,lfid,status,addtime";
        $rs = $this->loadModel('userSaler')->getList($fields,array('pid'=>$this->usid,'lfid'=>$lfid,'status' =>1),'addtime desc',true);
        if ($rs['list'])
        {
            foreach ($rs['list'] as $key => &$val)
            {
                $val['status'] = status_desc('USER_SALER_STATUS',$val['status']);
                $val['mobile'] =  $val['mobile']?substr($val['mobile'],0,3)."****".substr($val['mobile'],7,4):'';
                // $val['name'] = hide_name($val['name']);
            }
        }
        $rs = $rs['list']?$rs:false;
        $this->apiOut($rs);
    }
    /*
    *@desc	未认证直属下级商户信息列表
    */
    public function unverifiedInfoList()
    {
        $user = $this->usinfo;
        $fields = "id,mobile,name,lfid,status,addtime";
        $str = $user['depth_pid']?"{$user['depth_pid']}-{$user['id']}-%":"{$user['id']}-%";
        $where = array(
            'depth_pid' => array('like', "{$str}"),
            'status' => array('neq',1),
        );

        $rs = $this->loadModel('userSaler')->getList($fields,$where,'addtime desc',true);
        if ($rs['list'])
        {
            foreach ($rs['list'] as $key => &$val)
            {
                $val['status'] = status_desc('USER_SALER_STATUS',$val['status']);
                $val['mobile'] =  $val['mobile']?substr($val['mobile'],0,3)."****".substr($val['mobile'],7,4):'';
            }
        }
        $rs = $rs['list']?$rs:false;
        $this->apiOut($rs);
    }

    /*
    *	下级商户资金信息
    */
    public function childAccount()
    {
        $lfid = intval(I('lfid'));
        $this->vaild_params('is_empty',$lfid,'请传入等级ID');
        $rs = $this->loadModel('userSaler')->getChildAccount($this->usid,$lfid);
        if ($rs['list'])
        {
            foreach ($rs['list'] as $key => &$val)
            {
                $val['name'] = substr($val['name'],0,3) . strcpy('*',strlen($val['name'])/3-1);
            }
        }
        $rs = $rs['list']?$rs:false;
        $this->apiOut($rs);
    }



    /*
    *	用户升级接口
    */
    public function upgrade()
    {
        $err_msg = $this->loadModel('userSaler')->checkStatus($this->usid);
        if ($err_msg)
        {
            $this->response(PARAMS_ERROR,$err_msg);
        }

        $lfid = intval(I('lfid'));
        $this->vaild_params('is_empty',$lfid,'请选择要升级的等级');

        $this->loadModel('userSaler');
        $this->loadModel('orderUpgrade');
        $upgrade_level = $this->model['userSaler']->getLevelfeeUp($this->usid);
        $this->vaild_params('is_empty',$upgrade_level,'您当前已是最高级别，无法进行升级！');

        $upgrade_level = reset_array_key($upgrade_level,'id');
        $upgrade = $upgrade_level[$lfid];
        $this->vaild_params('is_empty',$upgrade,'无法升级当前级别');

        //跨级升级流程
        /* $temp = array(
            'fee_upgrade' => 0,
            'deposit' => 0,
            // 'name' => '',
        );
        foreach ($upgrade_level as $key => $val)
        {
            if ($val['level']<=$upgrade['level'])
            {
                $temp['fee_upgrade'] += $val['fee_upgrade'];
                $temp['deposit'] += $val['deposit'];
                // $temp['name'] .= '+'.$val['name'];
            }
        }
        $upgrade['fee_upgrade'] = $temp['fee_upgrade'];
        $upgrade['deposit'] = $temp['deposit'];
        // $upgrade['name'] = substr($temp['name'],1);
        unset($temp);
        // dump($upgrade); */


        $lf_old = $this->loadModel('levelFee')->getInfoByid($this->usinfo['lfid'],'id,name');
        $data = array();
        $data['sn'] = $this->createUpgradeSn();
        $data['usid'] = $this->usid;
        $data['us_name'] = $this->usinfo['name'];
        $data['us_mobile'] = $this->usinfo['mobile'];
        $data['lfid_old'] = $this->usinfo['lfid'];
        $data['lf_name_old'] = $lf_old['name'];
        $data['lfid_new'] = $lfid;
        $data['lf_rate'] = $upgrade['split_rate'];
        $data['lf_name_new'] = $upgrade['name'];
        $data['money'] = $upgrade['fee_upgrade'] + $upgrade['deposit'];
        $data['deposit'] = $upgrade['deposit'];	//风险保证金
        $data['addtime'] = time();

        $rs = false;
        $order = $this->model['orderUpgrade']->getInfo("id,sn",array('usid'=>$this->usid,'lfid'=>$lfid,'status'=>0));
        if ($order)
        {
            $rs = $this->model['orderUpgrade']->update($data,array('id'=>$order['id']));
        }
        else
        {
            $data['status'] = 0;
            $rs = $this->model['orderUpgrade']->add($data);
        }
        $sn = $data['sn'];
        $response = ($rs === false) ? false:array('sn'=>$sn);
        $this->apiOut($response);
    }


    /*
    *	生成accesstoken
    */
    private function createAccesstoken($usid,$mobile)
    {
        $access_token = MD5(C('SYSCODE').$user['id'].$user['mobile'].rand(1000,9999).time());
        $key = 'access_token_usid_'.$usid;
        return S($key,$access_token)?S($key):false;
    }

    /*
    *	用户升级订单号生成
    */
    private function createUpgradeSn()
    {
        return 'ou'.substr(date("YmdHis"), -12).rand(100000,999999);
    }


    /*
    *	视频转码
    */
    function videoTrans($video)
    {
        if (!is_file($video)) {
            return false;
            eblog("视频转码 - 失败",'文件不存在','tmpfile');
        }
        $trans_video = $video.".flv";
        $ffmpeg = ROOT_PATH."/Cli/tool/ffmpeg/ffmpeg -i ";
        $params = " -ab 128 -acodec libmp3lame -ac 1 -ar 22050 -r 29.97 -qscale 6 -y ";
        $cmd = $ffmpeg.$video.$params.$trans_video.'>/dev/null 2>&1';
        $arr = array();
        exec($cmd,$arr);
        return !is_file($trans_video)?false:true;
    }

    /*
     * 银行卡列表
     */
    function userBank(){
        $where = array('usid'=>$this->usid,'status'=>1);
        $rs = $this->loadModel('userBank')->getList($this->usid);
        foreach($rs as $key=>$value){
            $rs[$key]['bank_img']=C('APP_SITE').$value['bank_img'];
            $rs[$key]['bank_no']=substr($value['bank_no'],0,4).' **** '.substr($value['bank_no'],-4);
        }
        $this->apiOut($rs);
    }

    /*
     * 添加银行卡
     */
    function bankAdd(){
        $_params = $this->get_params(array('bank_id', 'bank_no','name','idcard','phone'));
        foreach ($_params as $value) {
            $this->vaild_params('is_empty', $value, '资料填写不完整');
        }
        $bankNo=I('bank_no');
        $rs = $this->loadModel('userBank')->getInfo('id,status',array('bank_no'=>$bankNo,'usid'=>$this->usid));
        $this->vaild_params('compare',array(1,$rs['status'],'<>'),'该卡已绑定过,重复绑卡!');

        if ($rs['status']==-1){
            //已删除的卡重新绑定,跳过实名认证
            $rs = $this->loadModel('userBank')->update(array('bank_id'=>$_params['bank_id'],'phone'=>$_params['phone'],'status'=>1,'addtime'=>time(),'deltime'=>null),array('id'=>$rs['id']));
        }else{
            // 实名认证
            $reqData = array(
                'acc_name'=>$_params['name'],
                'cert_no'=>$_params['idcard'],
                'acc_no'=>$_params['bank_no']
            );
            if ($this->realNameAuth){
                if (!$this->realname_verify($reqData)) {
                    $this->response(INTERNAL_ERROR,'实名认证失败,请核对数据后再试!');
                }
            }
            $_params['usid']=$this->usid;
            $_params['addtime']=time();
            $_params['status']=1;
            $rs = $this->loadModel('userBank')->add($_params);
        }
        $this->apiOut($rs, false);
    }

    /*
     * 删除银行卡
     */
    public function bankDel(){
        $id=I('id');
        $where=array('id'=>$id,'usid'=>$this->usid);
        $rs = $this->loadModel('userBank')->getInfo('id',$where);
        $this->vaild_params('is_empty',$rs,'删除失败,选择的银行卡不存在或已删除!');

        $rs = $this->loadModel('userBank')->update(array('status'=>-1,'deltime'=>time()),$where);
        $this->apiOut($rs, false);
    }
    /**
     * 创建订单号
     */
    public function create_order_no() {
        return date('YmdHis', time()) . rand_num(10, true);
    }

    /**
     *@desc	 返回用户信息
     */
    public function getUserInfo()
    {
        foreach (getallheaders() as $key => $value) {
            if ($key == 'Access-Token') {
                $access_token = $value;
            } elseif ($key == 'Us-Id') {
                $usid = $value;
            }
        }
        $this->vaild_params('is_empty',$usid,'缺少参数,usid');
        $user =  D('userSaler')->getInfo('*',array('id'=>$usid));
        $access_token = $this->createAccesstoken($user['id'],$user['mobile']);
        if (!$access_token)
        {
            $this->apiOut(false);
        }
        $field = array(
            'pay_type'=>'id as ptid,name as pt_name,api',
            'level_pay_limit'=>'lfid,min,max,day_min,day_max',
        );
        $join = array();
        $join[] = array('level_pay_limit','id','ptid');
        $where = array(
            'pay_type.status' => 1,
            'level_pay_limit.lfid' => $user['lfid'],
        );
        $day_max = 0;
        $pt_info = $this->loadModel('payType')->getJoinList($field,$join,$where,'sort desc,ptid asc');
        if ($pt_info['list'])
        {
            foreach ($pt_info['list'] as $val)
            {
                if ($val['day_max']) $day_max += $val['day_max'];
            }
        }
        $response = array();
        $user['level'] = $this->loadModel('levelFee')->getInfoByid($user['lfid']);
        $user['auth_file'] = format_struct('user_auth_file',unserialize($user['profile']));
        $user['status_data'] = format_struct('user_status_data',unserialize($user['status_data']),0,0);
        // $user['pt_info'] = $pt_info['list'];
        $user['day_max'] = $day_max;
        $user['login_id'] = $login_id;	//登录标识id
        $user['is_pay'] = 1;
        if($user['pid'] != 0 ){
            $invite_result = $this->loadModel('userSaler')->getInfo("name,mobile",array('id'=>$user['pid']));
        }
        //弹窗提醒
        switch ($user['status']) {
            case 0:
                $LOGIN_HINT_TITLE =  LOGIN_HINT_TITLE_1;
                $LOGIN_HINT_MSG = LOGIN_HINT_MSG_1;
                $LOGIN_HINT_BTN = LOGIN_HINT_BTN_1;
                $response['msg'] = '您好！您目前的账户正在审核中，当前您可以进行小额体验支付，赶紧试试吧！';
                break;
            case 1:
                $LOGIN_HINT_TITLE =   LOGIN_HINT_TITLE;
                $LOGIN_HINT_MSG = LOGIN_HINT_MSG;
                $LOGIN_HINT_BTN = LOGIN_HINT_BTN;
                $response['msg'] = '您好！您目前的账户已审核通过了！';
                break;
            case 2:
                $LOGIN_HINT_TITLE =  LOGIN_HINT_TITLE_2;
                $LOGIN_HINT_MSG = LOGIN_HINT_MSG_2;
                $LOGIN_HINT_BTN = LOGIN_HINT_BTN_2;
                if ($user['check_num']>0)
                $response['msg'] = '您好！您目前的账户审核未通过，请按要求更改相应信息！';
                break;
            case -1:
                $LOGIN_HINT_TITLE =  LOGIN_HINT_TITLE_2;
                $LOGIN_HINT_MSG = LOGIN_HINT_MSG_2;
                $LOGIN_HINT_BTN = LOGIN_HINT_BTN_2;
                $response['msg'] = '您好！您目前的账户已冻结！';
                break;
        }
        $user['invite_name'] = empty($invite_result['name'])?'':$invite_result['name'];
        $user['invite_mobile'] = empty($invite_result['mobile'])?'':$invite_result['mobile'];

        $response['access_token'] = $access_token;
        $response['profile'] = format_struct('user_profile',$user);
        //银行名称
        $BankName = $this->loadModel('settingBank')->getInfo("name",array('id'=>$response['profile']['bank']));
        $response['profile']['bank_real_name'] = $BankName['name'];

        $response['global'] = array(
            'share_title' => SHARE_TITLE,
            'share_msg' => SHARE_MSG,
            'share_url' => SHARE_URL.'/invite_mobile/'.$user['mobile'],
            'op_tel' => APP_OP_TEL,
            'op_qq' => APP_OP_QQ,
            'op_weixin' => APP_OP_WEIXIN,
            'hint' => LOGIN_HINT,
            'hint_title' =>$LOGIN_HINT_TITLE,
            'hint_msg' =>$LOGIN_HINT_MSG,
            'hint_btn' =>$LOGIN_HINT_BTN,
            'risk_pact_link' => RISK_PACT_LINK,
        );
        eblog("完善资料数据--","用户信息".$user,'mobile_'.date("Ymd"));
        $this->apiOut($response);
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
     * json 中文不转码
     */
    function json_encode_ex($value) {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $str = json_encode($value);
            $str = preg_replace_callback(
                "#\\\u([0-9a-f]{4})#i",
                function ($matchs) {
                    return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
                },
                $str
            );
            return $str;
        } else {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
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
    /*
     * 城市列表
     */
    public function cityList(){
        $region = M('region_zip')->order('province_zipcode ASC')->select();
        //dump($region);
        $list = $data = array();
        foreach ($region as $k => $v)
        {
            //$data[$v['province_zipcode']]  = array
            $data[$v['province_zipcode']]['province'] = $v['province_name'];
            $data[$v['province_zipcode']]['city'][]['city_name'] = $v['city_name'];

        }
        //echo gettype($data);
        //dump($data);

        $data=array_values($data);
        $list = array('list' => $data) ;//echo gettype($list);
        //echo json_encode($this->object_array($data));
        $this->apiOut($list);
    } /*
     * 联行号列表
     */
    public function banknoList(){
        $_params = $this->get_params(array('bankname', 'keyword', 'city'));
        //$_params = $this->get_params(array('bankname', 'keyword'));
        foreach ($_params as $value) {
            $this->vaild_params('is_empty', $value, '资料填写不完整');
        }
        $condition["mech_fullname"] = array("like", "%".$_params['bankname']."%".$_params['city']."%");
        $condition["mech_simplename"] = array("like", "%".$_params['keyword']."%");
        $bankno = M('bank_number')->where($condition)->field(array('bank_number'=>'bank_no','mech_simplename'=>'bank_name'))->select();        //dump($region);
        //print_r(M('bank_number'));
        //echo gettype($data);
        //dump($data);
        //$data=array_values($data);
        $list = array('list' => $bankno) ;//echo gettype($list);
        //echo json_encode($this->object_array($data));
        $this->apiOut($list);
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
