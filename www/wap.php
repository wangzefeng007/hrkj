<?php
	define('ROOT_PATH',dirname(__FILE__));
	//定义项目名称
	define('APP_NAME', 'Wap');
	//配置项目路径
	define('APP_PATH', ROOT_PATH.'/Wap/');
	//开启调试模式
	define('APP_DEBUG', TRUE);
	
	//全局常量配置
	include(ROOT_PATH."/define.config.php");

	/*
	*	自定义调试
	*	目前开启影响如下，1、短信发送默认成功 2、易宝支付直接返回成功
	*/
	define('SMSDEBUG',FALSE);

	//加载框架文件
	require('./ThinkPHP/ThinkPHP.php');

	