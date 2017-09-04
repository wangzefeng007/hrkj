<?php
//加载全局数据库配置
$config_db = require(ROOT_PATH.'/config.db.php');
//加载全局参数配置
$config_global = require(ROOT_PATH.'/config.global.php');

$config = array_merge($config_db,$config_global);
//自定义日志
$config['EBLOGPATH'] = ROOT_PATH."/tmp/log/api/";

$config_app = array(
	'DEFAULT_GROUP'         => 'admin',  // 默认分组
	'DEFAULT_MODULE'        => 'base', // 默认模块名称
	'DEFAULT_ACTION'        => 'login', // 默认操作名称
	'URL_UPLOADER' => './Upload/',			//图片上传路径
	'URL_UPLOADER_TMP' => './Upload/tmp/',		//图片临时上传路径
	'LOAD_EXT_CONFIG'	=> 'verify',	//加载扩展文件
	'URL_HTML_SUFFIX'       => '',	//关闭伪静态
);
$config_app['TMPL_PARSE_STRING']['__RES__'] = "/static/admin";	
$config_app['TMPL_PARSE_STRING']['__STATIC__'] = "/static";
$config_app['psize'] = "20";
//文件上传相关配置
$config_app['upload'] = include(dirname(__FILE__)."/upload.config.php");

//异步任务配置
$config_app['task'] = array('server'=>'127.0.0.1','port'=>9501,'timeout'=>'1');

return array_merge($config,$config_app);
