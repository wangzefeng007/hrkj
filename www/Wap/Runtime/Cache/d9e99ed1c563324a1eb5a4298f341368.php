<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta content="initial-scale=0.5" name="viewport">
    <meta content="width=640,user-scalable=no" name="viewport">
    <meta content="telephone=no" name="format-detection">
    <title>
    <?php if($type == 1 ): ?>微信扫码支付
    <?php else: ?>
		支付宝扫描支付<?php endif; ?>
    </title>
    <link rel="stylesheet" href="__WAP__/css/common.css">
    <link rel="stylesheet" href="__WAP__/css/wxqrpay.css">
    <script type='text/javascript' src='__WAP__/js/jquery.min.js'></script>
    <script type='text/javascript' src='__WAP__/js/qrcode.js'></script>
</head>
<body>
<div class="container">
	<?php if($type == 1 ): ?><div class="title"><img src="__WAP__/images/icon1.png" alt="">微信支付</div>
    <?php else: ?>
	<div class="title"><img src="__WAP__/images/icon1.png" alt="">支付宝支付</div><?php endif; ?>
    
    <div class="money">¥<?php echo ($price); ?></div>
    <div class="qrcode">
        <div id="qrcode"></div>
    </div>
    <div class="note">
    	长按识别二维码付款    
    </div>
</div>
<div class="copyright">
    <div class="techSupport"></div>
    <div class="hotline"></div>
</div>
</body>
</html>

<script type="text/javascript">
    var qrcode = new QRCode("qrcode", {
        text: "<?php echo ($key); ?>",
        width: 500,
        height: 500,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
</script>