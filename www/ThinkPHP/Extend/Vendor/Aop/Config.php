<?php
/**
* 	配置账号信息
*/
define('ALIPAYAOP_APP_ID', "2016102002255068");
define('ALIPAYAOP_APP_PRIVATE_KEY', "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAMXDWt1r5fLseBQqU1nmlVRKXycRlGz9tdh9/bbTp3Xl35Z8Jlg0uD65VfrFVQkKapo1DgaldCC3g2098xwN80Wsp3/6UP8EdQpbzWivZNKfdAWcsZpFAZUY02FyTmkBPQCYVUh3KTqeLo5e+aAuerXi8opiCZtDOB79rRNit3hLAgMBAAECgYBtxso6nPlRBniFYRVRkxCTcJEvI7ALbC40FYPvp7+OT2L0qseyMNmRX9ndUQqp8RFJtmepwtAalSOsUTYQ/aFqsZu/nwPksgb3pOl1G0Tm2Uh7HlCnz4rKIDlPxcckyHsVE+K/aq4zllOHefCDdR3NZKqlD1WWhdCNEkAWK17eIQJBAPkVDLkGJR1vz+eFy32wdx4iVb8UsWEnwMrtEy1rRdbDOP+aW9wTeiNxLs4C3VdXCiBOIBbH5kXBvG6WsTLJRfECQQDLQW5qTPF2g8qkzKC27735FHZGrWWHSeV0FFeM4GrIzX7I4sI7m/lnwMYJQUJhzyVHiUZ7kX5qRLftL3mBNTX7AkAEIpVTfQagtnp9HXuDWqqflXJW+cD4G/DXbZgj6OP/7L3FgIqF3MzqSba9NM735DoGz0U5G5h3EABQ1a+baDwBAkBwi9WY9OwLZOSTpl8jjNLTljn73s3GQOvV/+GOt0FJ2Ufn8m/809whUEQYIdOhFzOcTe4CREkMjOqPYmdUXWN7AkEAgRaN1S3cpKQvul3PZU6cP0ReKRg9pTdslDBoeywilryroDNbwwIiWA5iQwgJI5U9MREibdxl4oKY2pmUuPFTzg==");
define('ALIPAYAOP_APP_PRIVATE_KEY_FILE_PATH', '/home/html/www/ThinkPHP/Extend/Vendor/Aop/key/hrkj_rsa_private_key_pkcs8.pem');

define('ALIPAYAOP_ALIPAY_PUBLIC_KEY', "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB");
define('ALIPAYAOP_GATEWAY_URL', "https://openapi.alipay.com/gateway.do");
define('ALIPAYAOP_CHARSET', "GBK");
class Config
{
	const 	alipay_public_key_file =  "/home/html/www/ThinkPHP/Extend/Vendor/Aop/key/alipay_rsa_public_key.pem";
	const	merchant_private_key_file = "/home/html/www/ThinkPHP/Extend/Vendor/Aop/key/hrqb_rsa_private_key.pem";
	const	merchant_public_key_file = "/home/html/www/ThinkPHP/Extend/Vendor/Aop/key/hrqb_rsa_public_key.pem";		
	const	charset = "GBK";
	const	gatewayUrl = "https://openapi.alipay.com/gateway.do";
	const	app_id = "2016102002255068" ;
}
$config = array (
		'alipay_public_key_file' => dirname ( __FILE__ ) . "/key/alipay_rsa_public_key.pem",
		'merchant_private_key_file' => dirname ( __FILE__ ) . "/key/hrqb_rsa_private_key.pem",
		'merchant_public_key_file' => dirname ( __FILE__ ) . "/key/hrqb_rsa_public_key.pem",		
		'charset' => "GBK",
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		'app_id' => "2016102002255068" 
);
//print_r($config);
