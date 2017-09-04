<?php
return array(
	//配置数据库连接
    'DB_TYPE'   => 'mysql',
    //   'DB_HOST'   => '192.168.1.10',
    'DB_HOST'   => '110.80.9.86',
    'DB_NAME'   => 'ipay',
    'DB_USER'   => 'ipay',
    'DB_PWD'    => 'ipay',
	'DB_PREFIX' => 'rrg_',
	'SYSCODE' => '32TkAVJgfrOqBtw1Yxinx3uzjixfzAeq',				//密码加密串
	'URL_UPLOADER' => './Uploads/',			//图片上传路径
	'DEFAULT_THEME'         => '',	// 默认模板主题名称 
	'URL_MODEL'             => 1,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
		// 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式，提供最好的用户体验和SEO支持
	
);
