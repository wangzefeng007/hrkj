<?php
//加载全局数据库配置
$config_db = require(ROOT_PATH.'/config.db.php');
//加载全局参数配置
$config_global = require(ROOT_PATH.'/config.global.php');

$config = array_merge($config_db,$config_global);

//模版设置
$config['TMPL_PARSE_STRING']['__API__'] = "/static/api";

$config['psize'] = 20;
//注册下发短信
$config['regsms'] = "尊敬的用户，欢迎您注册，注册验证码##code##";
$config['resetpwdsms'] = "尊敬的用户，您正在重设密码，验证码##code##";

//接口响应结构体
$config['struct']['user_status_data'] = array('info','img');
// $config['struct']['user_status_data'] = array('info','img','video','risk_pact');
$config['struct']['user_auth_file'] = array('card_front','card_back','card_hand','video','risk_pact');
                                                          //添加'invite_mobile','invite_name'两个字段
$config['struct']['user_profile'] = array('id','mobile','name','invite_mobile','invite_name','headpic','card_no','bank','bank_name','bank_no','bank_address','status','status_shop','status_data','level','auth_file','day_max','is_pay','verify_ag');
$config['struct']['user_account'] = array('ptid','pt_name','pcid','pc_name','money','cash_money','total_money');
$config['struct']['goods'] = array('id','upid','name','desc','price','stock','thumb','img','status','addtime','wap','sale_count');

//自定义日志
$config['EBLOGPATH'] = ROOT_PATH."/tmp/log/api/";

//支付接口相关配置
//千引支付配置
$config['upacp'] = include(dirname(__FILE__)."/qianyin.config.php");
//易宝支付配置
$config['yeepay'] = include(dirname(__FILE__)."/yeepay.config.php");
//腾付通支付配置
$config['tftpay'] = include(dirname(__FILE__)."/tftpay.config.php");
//魔宝支付配置
$config['mopay'] = include(dirname(__FILE__)."/mopay.config.php");
//EZF支付配置
$config['ezfpay'] = include(dirname(__FILE__)."/ezfpay.config.php");
//银联UPMP支付配置
$config['upmppay'] = include(dirname(__FILE__)."/upmppay.config.php");
//民生支付配置
$config['cmbcpay'] = include(dirname(__FILE__)."/cmbcpay.config.php");
//民生银联配置
$config['cmuppay'] = include(dirname(__FILE__)."/cmuppay.config.php");
//丰付支付配置
$config['sumapay'] = include(dirname(__FILE__)."/sumapay.config.php");
//银联快捷支付
$config['upacp'] = include(dirname(__FILE__)."/upacp.config.php");
//扫码支付
$config['scan'] = include(dirname(__FILE__)."/scan.config.php");

//文件上传相关配置
$config['upload'] = include(dirname(__FILE__)."/upload.config.php");

//异步任务配置
$config['task'] = array('server'=>'127.0.0.1','port'=>9501,'timeout'=>'1');


//实名认证配置
$config['realname_auth'] =1;
$config['realname_url'] = 'http://www.qzhmpay.com/iPayInterface/odBusiness.do';
$config['realname_public_key'] = ROOT_PATH.'/Public/Realname/00000000_sc_rsa_public_key_2048.pem';
$config['realname_private_key'] = ROOT_PATH.'/Public/Realname/880100000000003_pkcs8_rsa_private_key_2048.pem';

return $config;
