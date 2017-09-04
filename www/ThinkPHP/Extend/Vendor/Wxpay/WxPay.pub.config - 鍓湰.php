<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================

	//-------------主商户设置----------------	//配置参数由主商户直接提供
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wxe568c727d466aef9'; //'wx8888888888888888';
	//受理商ID，身份标识
	const MCHID = '1241114402';//'18888887';
	//商户支付密钥Key。商户平台后台设置API密钥
	const KEY = '4d82dbe2e0e54a4ca26bdf7435b8ed06';//'48888888888888888888888888888886';
	
	//-------------子商户设置----------------
	//子商户 MCHID
	const SUB_MCHID = '1313524701';		//商户平台-商户号
	//子商户 APPID
	const SUB_APPID = 'wx10145531d687c443';		//公众平台-APPID


	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = '2322805eccf8d5152815f7d730730818';
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://yunfu.a3banks.com/api.php/wxpay/wap_pay/';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = './cacert/apiclient_cert.pem';
	const SSLKEY_PATH = './cacert/apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = 'http://yunfu.a3banks.com/api.php/wxpay/payNotice';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
	
?>