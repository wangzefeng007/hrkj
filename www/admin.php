<?php

	define('ROOT_PATH',dirname(__FILE__));
	//定义项目名称
	define('APP_NAME', 'Admin');
	//配置项目路径
	define('APP_PATH', ROOT_PATH.'/Admin/');
	//开启调试模式
	define('APP_DEBUG', TRUE);
	
	//全局常量配置
	include(ROOT_PATH."/define.config.php");
    //全局接口常量配置
    include(ROOT_PATH."/api.config.php");

	//自定义日志路径
	// define('LOG_PATH',ROOT_PATH.'/tmp/log/');
	
	
	//加载框架文件
	require('./ThinkPHP/ThinkPHP.php');

