<?php
return array(
	//私钥地址
	'pfxFile' => "./ThinkPHP/Extend/Vendor/Mopay/wsh.pfx",
	//$pubFile = "C:/roysite/PHP/epay.pem";
	'pubFile' => "./ThinkPHP/Extend/Vendor/Mopay/epay.crt",
	//私钥密码
	'pfxpasswd' => "wsh",
	// 'payReqUrl' => "https://trade.mobaopay.uat/cgi-bin/netpayment/pay_gate.cgi",
	'payReqUrl' => "https://trade.mobaopay.com/cgi-bin/netpayment/pay_gate.cgi",
	
	//api版本号
	'apiVersion' => '1.0.0.0',
	
	//平台id
	'platformID' => 'wsh123',
	//商户号
	'merchNo' => '240001510003432'
);
/*
$queryReqUrl = "http://192.168.20.209:9003/cgi-bin/netpayment/pay_gate.cgi";
$refundReqUrl = "http://192.168.20.209:9003/cgi-bin/netpayment/pay_gate.cgi";
*/