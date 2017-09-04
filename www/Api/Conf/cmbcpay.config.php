<?php
return array(
    // Request vars
	'APP_PAY_URL' => "http://wsbl.ksowifi.com/bestpay.php/cmbc/appApi/", 	//手机APP支付网关地址
	'WAP_PAY_URL' => "http://wsbl.ksowifi.com/bestpay.php/cmbc/wapApi/", 	//短信支付网关地址
	'BACKENDURL' => HOST."/api.php/cmbc/notice/",	//异步通知URL
	// 'FRONTENDURL' => HOST."/api.php/cmbc/result/",	//前端返回URL
	'FRONTENDURL' => HOST."/api.php/cmbc/front_notice/",	//前端返回URL
    'security_key' => "zPhdhY4cl0K88yxYKnXq9YbV2Phm8KYY",	// 商户密钥
	'SUBMERID' => "00011",	 		//子账户标识
	'SN_PREFIX' => "YF",	 		//订单号前缀
	"SMS_AIR"=> COMP_SHORT."提示：您正在进行金额为##money##元的支付交易，请点击链接 ##url## ，继续 支付，非本人交易请忽略。",	//短信收款消息模版
);
