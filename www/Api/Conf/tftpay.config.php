<?php
$wap_host = "http://new.weishanghui.me/"; //服务器地址
return Array(
	"TFTPAY_MER_ID_1"			=>	"00033169",                                     //商户id,8位
	"TFTPAY_MER_ID_2"			=>	"00033220",                                     //商户id,8位
	
	"TFTPAY_CODE_PAY"		=>	"ORD001",                                              //交易代码-支付
	"TFTPAY_CODE_PAY_2"		=>	"PAY003",                                              //交易代码-直联无卡支付
	"TFTPAY_CODE_STATUS"	=>	"ORD003",                                              //交易代码-查询订单支付状态
	"TFTPAY_CODE_DETAIL"	=>	"ORD004",                                              //交易代码-查询订单明细
	"TFTPAY_CODE_REFUND"	=>	"ORD002",                                              //交易代码-建立退款订单
	"TFTPAY_GATE_URL"		=>	"https://www.tftpay.com/middleWeb/webHconn",		   //服务器端地址
	"TFTPAY_PRIKEY_PATH_1"	=>	"./ThinkPHP/Extend/Vendor/TFT/cer/00033169.pfx",		   //私钥文件
	"TFTPAY_PRIKEY_PATH_2"	=>	"./ThinkPHP/Extend/Vendor/TFT/cer/00033220.pfx",		   //私钥文件
	"TFTPAY_PRIKEY_PASS_1"	=>	"Mba2li",        									   //私钥密码
	"TFTPAY_PRIKEY_PASS_2"	=>	"uWGfgq",        									   //私钥密码
	"TFTPAY_PUBKEY_PATH"	=>	"./ThinkPHP/Extend/Vendor/TFT/cer/cacert.pem",         //公钥文件
	"TFTPAY_CHARSET"		=>	"UTF-8",                                               //编码
	"TFTPAY_RETURN_URL"		=>	$wap_host."indes.php/tftpay/payReturn",	//返回地址，同步
	"TFTPAY_NOTIFY_URL"		=>	$wap_host."indes.php/tftpay/payNotify",	//返回地址,异步
	"TFTPAY_REFUND_RETURN_URL"	=>	"https://xxxxxxxx/retUrl.php",                     //退款返回地址，同步
	"TFTPAY_REFUND_NOTIFY_URL"	=>	"https://xxxxxxxx/notUrl.php",                     //退款返回地址,异步
	"TFTPAY_PAY_METHOD"		=>	"0",                                                   //默认支付方式
	"TFTPAY_CHK_METHOD"		=>	"1",                                                   //签名方式
	"TFTPAY_MER_BUS_TYPE"	=>	"30",                                                  //商户业务类型 10-普通商品,30-综合购物
	"TFTPAY_PAY_TYPE"		=>	0,                                                     //付款类型
	"TFTPAY_WAP_HOST"=> $wap_host,	//wap网页支付主机地址

	"SMS_FTF"=>"腾付通支付提示: 您尾数##credit_no##的银行卡，将进行##money##元的消费支付，如确认是您本人发起的真实交易请求，请点击链接 ##url## 或回复验证码 ##code##完成支付（5分钟内有效），如非本人交易请忽略，谨防诈骗。",
	"SMS_WAP"=>"腾付通支付提示:您尾数##credit_no##的银行卡，进行金额##money##元的支付交易，支付验证码为##code##，请勿向任何人提供该验证码，谨防诈骗。"
);
