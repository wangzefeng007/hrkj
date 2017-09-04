<?php

	define('ROOT_PATH',dirname(__FILE__));
	//定义项目名称
	define('APP_NAME', 'Api');
	//配置项目路径
	define('APP_PATH', ROOT_PATH.'/Api/');
	//开启调试模式
	define('APP_DEBUG', TRUE);
	
	//全局常量配置
	include(ROOT_PATH."/define.config.php");

	/*
	*	自定义调试
	*	目前开启影响如下，1、短信发送默认成功 2、易宝支付直接返回成功
	*/
	define('SMSDEBUG',FALSE);
	define('EBDEBUG',FALSE);
	define('TFTDEBUG',FALSE);
	define('UPMPDEBUG',FALSE);
	define('CMBCDEBUG',FALSE);
	define('CMUPDEBUG',FALSE);
	define('SUMADEBUG',FALSE);
	define('WXDEBUG',FALSE);
	define('QYDEBUG',FALSE);
	define('TOKENDEBUG',TRUE);
	$_GET['m'] = 'qrpay';
	$_GET['a'] = 'index';
	// define('WEBDEBUG',TRUE);	//PC端网页调取支付开关,TRUE-允许,FALSE-不允许
	define('TASK_MSG',FALSE);	//异步短信开关,TRUE-打开,FALSE-关闭
	
	//加载框架文件
	require('./ThinkPHP/ThinkPHP.php');

