<?php
return array(
	// 'APP_PAY_URL' => "http://13570812759.oicp.net/mposadmin/api/transMsg/gettn", 	//手机APP支付网关地址
	'APP_PAY_URL' => "https://payment.chinacardpos.com/mpos/api/transMsg/gettn", 	//手机APP支付网关地址
	// 'WAP_PAY_URL' => "", 	//短信支付网关地址

	'T0_PAY_URL' => 'https://payment.chinacardpos.com/mpos/api/transMsg/reqT0Pay', 	//代付接口地址
	'T0_QUERY_URL' => 'https://payment.chinacardpos.com/mpos/api/transMsg/queryT0PayInfo', 	//代付查询接口地址
	'BACKENDURL' => HOST."/api.php/cmup/notice/",	//异步通知URL
	// 'FRONTENDURL' => HOST."/api.php/cmup/result/",	//前端返回URL
	'FRONTENDURL' => HOST."/api.php/cmup/front_notice/",	//前端返回URL
    'merNo' => "350350057340001",	// 商户号
    'terNo' => "00000801",	// 终端号
    'security_key' => "E093D30AAFFB28FFC4B6A8781F5059FC",	// 商户密钥
	"SMS_AIR"=> COMP_SHORT."提示：您正在进行金额为##money##元的支付交易，请点击链接 ##url## ，继续支付，非本人交易请忽略。",	//短信收款消息模版
);
