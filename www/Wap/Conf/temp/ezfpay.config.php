<?php
return array(
    // Request vars
	'EZFMER_PAY_URL' => "http://test.ezf123.com/jspt/payment/mptrade", 	//此处为模拟联调支付地址，正式投产时需使用清算平台统一分配的地址
	'EZFMER_BACKENDURL' => "http://wsbl.ksowifi.com/api.php/ezfpay/notice/",	//异步通知URL
	'EZFMER_FRONTENDURL' => "http://wsbl.ksowifi.com/api.php?m=ezfpay&a=result",	//前端返回URL
	'VERSION' => "1.0.0",	// 支付版本
	'CHARSET' => "UTF-8",			//字符集UTF-8或者GBK
	'SIGNMETHOD' => "MD5",	 		//加密方式
	'MERID' => "10001",		//此处为模拟联调商户号，正式投产时需使用清算平台分配的商户号
	'SIGNKEY' => "8888888888888",	//此处为模拟联调密钥，正式投产时需使用清算平台分配的密钥
	// 'ORDERAMOUNT' => "300",	//支付金额，单位为分
);
