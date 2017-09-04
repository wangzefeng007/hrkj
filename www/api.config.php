<?php
/*
*	接口全局常量
*/
//微信扫码接口
define('WXPAY_SCAN_URL','http://172.16.102.101:8880/SinglePay/scancode');
//阿里扫码接口
define('ALIPAY_SCAN_URL','http://172.16.102.101:8880/SinglePay/scancode');
//代付接口
define('SINGLE_PAY_URL','http://172.16.102.101:8880/SinglePay/pay');
//银联快捷支付接口
define('PAY_URL', "http://wsbl.ksowifi.com/bestpay.php/upmp/appApi/");
//实名认证接口
define('REAL_NAME_URL', "http://www.qzhmpay.com/iPayInterface/odBusiness.do");
//短信接口
define('API_SMS_URL', "http://211.147.239.62:9050/cgi-bin/sendsms");
//极光推送接口
define('API_JIGUANG_URL', "https://api.jpush.cn/v3/push");
define('WXPAY_SANPAY_URL','http://192.168.1.131:8036/scanpay-api/api/pay/payment');
//$config = array(
//    "WXPAY_SCAN_URL"=>"http://172.16.102.101:8880/SinglePay/scancode",//微信扫码接口
//    "ALIPAY_SCAN_URL"=>"http://172.16.102.101:8880/SinglePay/scancode",//阿里扫码接口
//    "SINGLE_PAY_URL"=>"http://172.16.102.101:8880/SinglePay/pay",//代付接口
//    "PAY_URL"=> "http://wsbl.ksowifi.com/bestpay.php/upmp/appApi/",//银联快捷支付接口
//    "REAL_NAME_URL"=> "http://172.16.102.101:10087/iPayInterface/odBusiness.do",//实名认证接口
//    "API_SMS_URL"=> "http://211.147.239.62:9050/cgi-bin/sendsms", //短信接口
//    "API_JIGUANG_URL"=> "https://api.jpush.cn/v3/push", //极光推送接口
//);
//return $config;