<?php
return array(
    // Request vars
	'EZFMER_PAY_URL' => "https://www.ezf123.com/jspt/payment/mptrade", 	//手机APP支付网关地址
	'EZFMER_PAY_URL_WAP' => "https://www.ezf123.com/jspt/payment/payment.action", 	//wap支付网关地址
	'EZFMER_BACKENDURL' => HOST."api.php/ezfpay/notice/",	//异步通知URL
	'EZFMER_FRONTENDURL' => HOST."api.php?m=ezfpay&a=result",	//前端返回URL
	'VERSION' => "1.0.0",	// 手机APP支付支付版本
	'VERSION_WAP' => "1.0.4",	// wap支付支付版本
	'CHARSET' => "UTF-8",			//字符集UTF-8或者GBK
	'SIGNMETHOD' => "MD5",	 		//加密方式
	'MERID' => "10404",		//商户号
	'SUBMERID' => "1040400001",	 		//子账户标识
	'SIGNKEY' => "RP8DNZTJTHBJFZWQHLDRPK766J6FH2IIB4L1D1JTYY4U7D688UN8MSTA5NTH",	//签名密钥
	// 'ORDERAMOUNT' => "300",	//支付金额，单位为分
);
