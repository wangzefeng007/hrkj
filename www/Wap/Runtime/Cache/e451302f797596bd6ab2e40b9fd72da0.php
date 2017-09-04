<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo ($rs["stitle"]); ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="description" content="" />
<meta name="keywords" content="" />
<link rel="stylesheet" href="__WAP__/css/shop.css">
<script type="text/javascript" src="__WAP__/plugins/zepto/zepto.min.js"></script>
</head>
<body style="padding:10px 20px">
<?php echo (htmlspecialchars_decode($rs["svalue"])); ?>
</body>
</html>