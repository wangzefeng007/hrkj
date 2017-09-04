<?php
return array(
	'host'=>'http://eb.project.boruicx.com/',
	'tmproot'=>ROOT_PATH.'/Upload/tmp/',		//临时目录
	'tmprule'=>date('Ymd')."/",									//临时目录规则
	'type'=>array('video','image'),
	'tag'=>array('user','goods'),
	'video'=>array(
		'size'=>2196608,				//文件大小
		'path'=>ROOT_PATH.'/Upload/video/'.date('Ymd')."/",
		'exts'=>array('mp4')	
	),
	'image'=>array(
		'size'=>2196608,				//文件大小
		'path'=>ROOT_PATH.'/Upload/image/'.date('Ymd')."/",
		'exts'=>array('jpg','gif','png','jpeg')
	)
);