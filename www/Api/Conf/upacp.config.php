<?php
return array(
    // Request vars
	'PAY_URL' => "http://106.14.29.11/sys/api/Payurl", 	//手机APP支付网关地址
	// 'PAY_URL' => "http://pay.vm-zlw.com:8006/bestpay.php/upmp/appApi/", 	//手机APP支付网关地址
	'UPACP_PUBLIC_KEY' => ROOT_PATH.'/Public/upacp/plat_rsa_public_key_512.pem',//公钥加密文件
	'UPACP_PRIVATE_KEY' =>ROOT_PATH.'/Public/upacp/mer_pay_rsa_private_key_512.pem',//私钥加密文件
	'APPCODE' => '2e0b4b81-6475-4c82-8230-1cb6dc48e856',
	'APPID' =>'3d91fc3d-6d41-4804-93d9-711458dfb4d9',
    'UNION_PUBLIC_KEY' =>'MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANBkHl3yObN14MLz1jf0g90yx8XiRkwQczn2gs8tiDelWDF4DrxbJqQg9Ix+p720XL2chpAUGMpX6GTVvDJBJgsCAwEAAQ==',
    'UNION_PRI_KEY' => 'MIIBVAIBADANBgkqhkiG9w0BAQEFAASCAT4wggE6AgEAAkEAyJmybZUNzYMh7LxYHYgq7bJNlAzk/JDQgXJAjsEYIobK2u+uWPX5kcE8bWQcp4iaC68B/LtFDTjZirinBX5cbwIDAQABAkAaE1iNRA3aRAx54HW3yQaDhWTuNMrjRdPEr9xScik3+4CQ9sdnc8XBIEx2blbeEPzqHC9LrzL6DvzeKnE8sE5xAiEA7ebNUuo2vk//az7MbTHC6YU7L7EZJc/6/v2Wi1dliAcCIQDX3HGt6gnuRUV7CRC/8hC55ePHABoA6RnyE4J3x+TeWQIgFWu7vySBq18uo2xzIb3iS/6IFsI+fm4crosM3B0Rck8CIQCuoVWKSAtuqrXSGnxlfJSRf+ztAiUHVY067ROgiaObSQIgIsz3+Aj9HAepaDUP79zWVx1vmW5ECqRyHaupDgOCMVc=',
	'SN_PREFIX' => "YF",	 		//订单号前缀
);
