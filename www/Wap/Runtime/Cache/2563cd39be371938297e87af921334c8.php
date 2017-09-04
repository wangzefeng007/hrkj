<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo SHARE_TITLE;?></title>
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<link href="__WAP__/css/style.css" rel="stylesheet">
</head>
<style>
.xzy{ background-size:100%; background:#fff; position:relative;}
.xzy .img{ width:100%; display:block;}
.xzy .img img{width:100%; display:block;}
.xzy .xzan{ width:100%; padding-bottom: 30px; overflow:hidden; position:absolute; z-index:100; left:0; top:28%;}
.android{  width:36.25%; float:left; margin-left:8.5%;}
.ios{ width:36.25%; float:right; margin-right:8.5%;}
.zcy{
	text-align: center;
}
.tsyc{ width:100%; height:100%;  top:0; left:0; z-index:10; opacity:0.8; -moz-opacity:0.8; background-color:#fff; position:absolute;}
.mask{
	display: none;
	width: 100%; height:100%;
	position: fixed;
	left: 0px;
	top: 0px;
	padding: 0px;
	margin: 0px;
	z-index: 1000;
}
.mask img{
	width: 100%;
	border: none;
	padding: 0px;
	margin: 0px;
}

</style>
<body>

<div class="xzy">
	<div class="img"><img src="__WAP__/images/wg01.jpg"></div>
	<div class="xzan">
   		<div class="zcy"><a href="<?php echo U('reg',array('invite_mobile'=>$invite_mobile));?>"><img style="width:80%" src="__WAP__/images/wg04.png"></a></div>
	</div>
</div>

</body>
</html>