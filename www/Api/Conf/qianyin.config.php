<?php
return array(
    // Request vars
	'PAY_URL' => "https://mpay.sinoqy.com:2070", 	//手机APP支付网关地址
	// 'PAY_URL' => "http://pay.vm-zlw.com:8006/bestpay.php/upmp/appApi/", 	//手机APP支付网关地址
	'BACKENDURL' => HOST."/api.php/qianyin/notice/",	//异步通知URL
	'FRONTENDURL' => HOST."/api.php/qianyin/result/",	//前端返回URL
	//'MERID' => "805920100000041",	 		//子账户标识
	//'SECRET' 		=> 'Q8dfaa2cyn9JoOADVHtVTTuU17W1x0al',
	'MERID' => "605920100000068",	 		//子账户标识
	'SECRET' 		=> '3RHk31aLJTYy4Ir04WvVI7ZRtUtnedIJ',
	'SN_PREFIX' => "HR",	 		//订单号前缀
);
