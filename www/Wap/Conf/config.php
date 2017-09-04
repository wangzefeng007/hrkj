<?php
$config = array(
	'URL_MODEL'       => 0,	//路由模式 0-普通模式
	'URL_HTML_SUFFIX'       => '',	//关闭伪静态
	'TMPL_FILE_DEPR' 	=> '_',		// 模板文件MODULE_NAME与ACTION_NAME之间的分割符
	//'DEFAULT_GROUP'         => 'home',  // 默认分组
	'DEFAULT_MODULE'        => 'web', // 默认模块名称
	'DEFAULT_ACTION'        => 'index', // 默认操作名称
	'ORDER_STATUS_BUY' => array(
		'0' => '待付款',
		'1' => '待发货',
		'2' => '待签收',
		'3' => '待评价',
	),
);
//模版设置
$config['TMPL_PARSE_STRING']['__WAP__'] = "/static/wap";
$config['TMPL_PARSE_STRING']['__STATIC__'] = "/static";

$config['psize'] = 20;
//注册下发短信
$config['regsms'] = "尊敬的用户，欢迎您注册，注册验证码##code##";

//接口响应结构体
$config['struct']['user_profile'] = array('id','mobile','name','card_no','bank','bank_name','bank_no','bank_address','status','status_shop','level','auth_file');
$config['struct']['user_auth_file'] = array('card_front','card_back','card_hand','video');
$config['struct']['user_account'] = array('ptid','pt_name','pcid','pc_name','money','cash_money','total_money');
$config['struct']['goods'] = array('id','upid','name','desc','price','stock','thumb','img','status','addtime','wap','sale_count');


//支付接口相关配置
//易宝支付配置
$config['yeepay'] = include(dirname(__FILE__)."/yeepay.config.php");
//EZF支付配置
$config['ezfpay'] = include(dirname(__FILE__)."/ezfpay.config.php");
//银联UPMP支付配置
$config['upmppay'] = include(dirname(__FILE__)."/upmppay.config.php");

//文件上传相关配置
$config['upload'] = include(dirname(__FILE__)."/upload.config.php");



return array_merge(include(ROOT_PATH."/config.php"),$config);
