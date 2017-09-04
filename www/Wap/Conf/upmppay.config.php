<?php
return array(
    // Request vars
	'PAY_URL' => "http://wsbl.ksowifi.com/bestpay.php/upmp/appApi/", 	//手机APP支付网关地址
	// 'PAY_URL' => "http://pay.vm-zlw.com:8006/bestpay.php/upmp/appApi/", 	//手机APP支付网关地址
	'BACKENDURL' => HOST."api.php/upmp/notice/",	//异步通知URL
	'FRONTENDURL' => HOST."api.php/upmp/result/",	//前端返回URL
	'SUBMERID' => "00001",	 		//子账户标识
	'SN_PREFIX' => "WSBL",	 		//订单号前缀
);
