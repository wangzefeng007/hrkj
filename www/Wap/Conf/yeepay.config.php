<?php
return Array(
		
	"YEEPAY_LOG_NAME"		=>	"/vhost/yun/Uploads/business/a.log",									/* 日志文件名称 */
	"YEEPAY_CMD"			=>	"EposSale",											/* 业务类型：信用卡 */
	"YEEPAY_CMD_DEBIT"		=>	"EposDebitSaleNew",									/* 业务类型：借记卡 */
	"YEEPAY_CMD_GET_VERIFY"	=>	"EposVerifyCodeReceive",							/* 业务类型：获取验证码 */
	"YEEPAY_CMD_VERIFY"		=>	"EposVerifySale",									/* 业务类型：验证码消费 */
	"YEEPAY_CMD_QUERY"		=>	"QueryOrdDetail",									/* 业务类型：订单查询 */
	"YEEPAY_CUR"			=>	"CNY",												/* 币种 */
	"YEEPAY_NEED_RESPONSE"	=>	1,													/* 是否需要应答 */
	"YEEPAY_TERMINAL_CODE"	=>	"10012411436001",									/* 终端号 */
	"YEEPAY_MER_ID"			=>	"10012411436",										/* 商户编号 */
	//"YEEPAY_MER_ID"			=>	"10001126856",										/* 商户编号测试号 */
	"YEEPAY_MERCHANT_KEY"	=>	"kp4624P5z2Q04433LB38C7ySI07Fs5K45117e1G1Y9r859564fE2M8P08B2e",		/* 商户密钥 */
	//"YEEPAY_MERCHANT_KEY"	=>	"69cl522AV6q613Ii4W6u8K6XuW8vM1N6bFgyv769220IuYe9u37N4y7rI4Pl",		/* 商户密钥测试号 */
	"YEEPAY_GATE_URL_DEBIT"	=>	"https://www.yeepay.com/app-merchant-proxy/command",				/* 网关地址：借记卡 */
	"YEEPAY_GATE_URL"		=>	"https://www.yeepay.com/app-merchant-proxy/command",				/* 网关地址 */
	"YEEPAY_TEST_ACTION_URL"	=>	"http://tech.yeepay.com:8080/robot/debug.action",				/* 测试网关地址 */
	"YEEPAY_RETURN_URL_DEBIT"	=>	"http://houtai.dlysmc.com/indes.php/yeepay/payReturn",			/* 异步通知：借记卡 */
	"YEEPAY_RETURN_URL"		=>	"http://houtai.dlysmc.com/indes.php/yeepay/payReturn",				/* 异步通知：信用卡 */
	"YEEPAY_WAP_HOST"=>"http://houtai.dlysmc.com/",	//wap网页支付主机地址
	"SMS_AIR"=> "易宝支付提示：您正在进行金额为##money##元的支付交易，请点击链接##url##完成支付，非本人交易请忽略。",	//空中支付短信模版
	"SMS_FTF"=>"易宝支付提示: 您尾数##credit_no##的银行卡，将进行##money##元的消费支付，如确认是您本人发起的真实交易请求，请点击链接 ##url##或回复验证码 ##code##完成支付（5分钟内有效），如非本人交易请忽略，谨防诈骗。",
	"SMS_WAP"=>"易宝支付提示:您尾数##credit_no##的银行卡，进行金额##money##元的支付交易，支付验证码为##code##，请勿向任何人提供该验证码，谨防诈骗。"
);
