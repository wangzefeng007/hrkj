<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo htmlspecialchars($shopInfo['name']);?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="description" content="" />
<meta name="keywords" content="" />
<link rel="stylesheet" href="__WAP__/css/shop.css">
<link rel="stylesheet" href="__WAP__/css/page.css">
<script type="text/javascript" src="__WAP__/plugins/zepto/zepto.min.js"></script>
<script type="text/javascript" src="__WAP__/js/spin.min.js"></script>
</head>
<body>
	<header>
		<div class="button"></div>
		<div class="title">店铺首页</div>
		<div class="button"></div>
	</header>
	<div class="sign_bar"><img src="<?php echo (($shopInfo["background"])?($shopInfo["background"]):'__WAP__/images/shop_pic.jpg'); ?>"></div>
	<div class="info_bar">
		<div class="logo"><img src="<?php echo (($shopInfo["logo"])?($shopInfo["logo"]):'__WAP__/images/shop_logo.png'); ?>"></div>
		<div class="info">
			<div class="name"><a href="<?php echo U('shopInfo',array('usid'=>$usid));?>"><?php echo htmlspecialchars($shopInfo['name']);?></a></div>
			<div class="phone">
				<img src="__WAP__/images/icon_phone.png" />
				<?php echo ($shopInfo["mobile"]); ?>
			</div>
		</div>
	</div>
	<div class="goods_sort_bar">
		<a class="button primary" href="#">最新商品</a>
		<!--<a class="button default" href="#">价格排行<img src="__WAP__/images/icon_arrow_up.png"></a>
		<a class="button default" href="#">销量排行<img src="__WAP__/images/icon_arrow_down.png"></a>-->
	</div>
	<div class="goods_block_bar">
		<?php if(is_array($goodsList)): foreach($goodsList as $key=>$vo): ?><a class="item" href="<?php echo U('goods', array('gid'=>$vo['id'], 'usid'=>$usid));?>">
				<div class="img"><img src="<?php echo (($vo["thumb"])?($vo["thumb"]):'__WAP__/images/img_goods.jpg'); ?>" /></div>
				<div class="info">
					<div class="name"><?php echo htmlspecialchars($vo['name']);?></div>
					<div class="price">￥<?php echo ($vo["price"]); ?></div>
				</div>
			</a><?php endforeach; endif; ?>
	</div>
	<div class="paging_bar">
		<a class="default" href="javascript:void(0);" id="more">更多</a>
	</div>
</body>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
var baseurl = "http://new.weishanghui.me/";
var wxData = {
	"imgUrl": baseurl + "<?php echo (($shopInfo["background"])?($shopInfo["background"]):'__WAP__/images/shop_pic.jpg'); ?>",
	"link": baseurl + "<?php echo U('index',array('usid'=>$usid));?>",
	"desc":"<?php echo htmlspecialchars($shopInfo['name']);?>",
	"title":"微商部落-手机开店很简单，分享就能赚大钱"
};
wx.config({
	debug: false,
	appId: '<?php echo ($signPackage["appId"]); ?>',
	timestamp: '<?php echo ($signPackage["timestamp"]); ?>',
	nonceStr: '<?php echo ($signPackage["nonceStr"]); ?>',
	signature: '<?php echo ($signPackage["signature"]); ?>',
    jsApiList: [
		'onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ'
	]
});
</script>
<script type="text/javascript" src="http://www.boruicx.com/weixin/js/weixin.js"></script>
<script type="text/javascript">
var opts = {
		lines: 15, // The number of lines to draw
		length: 5, // The length of each line
		width: 2, // The line thickness
		radius: 12, // The radius of the inner circle
		corners: 1, // Corner roundness (0..1)
		rotate: 0, // The rotation offset
		direction: 1, // 1: clockwise, -1: counterclockwise
		color: '#000000', // #rgb or #rrggbb or array of colors
		speed: 1.5, // Rounds per second
		trail: 60, // Afterglow percentage
		shadow: false, // Whether to render a shadow
		hwaccel: false, // Whether to use hardware acceleration
		className: 'spinner', // The CSS class to assign to the spinner
		zIndex: 2e9, // The z-index (defaults to 2000000000)
		top: '50%', // Top position relative to parent
		left: '50%', // Left position relative to parent
		color: 'black'
};
var spinner = null;

$(function(){
	// 调整店标高度
	var $logo = $(".info_bar .logo img");
	var w = $logo.width();
	$logo.height(w-8);
	// 调整图片高度
	$(".goods_block_bar .item .img img").each(function(e){
		$(this).height($(this).width());
	});
	
	var p = 1;
	var flag = 1;	//末页标记,当达到末页的时候标记为0;
	$('#more').click(function(){
		p += 1;
		if (flag) goodsMore();
	});
	$(window).on("scroll", function(){
		var scrollTop = $(this).scrollTop();
		var scrollHeight = $(document).height();
		var windowHeight = $(this).height();
		if(scrollTop + windowHeight == scrollHeight){
			p += 1;
			if (flag) goodsMore();
		}
	});
	function goodsMore()
	{
		var usid = '<?php echo ($usid); ?>';
		spinner = new Spinner(opts).spin(document.getElementsByTagName('body')[0]);
		$.post("<?php echo U('goodsMore');?>", {usid:usid,p:p},
			function(data){
				spinner.stop();
				if (data)
				{
					$(".goods_block_bar").append(data);
				}
				else
				{
					$('#more').hide();	//没有更多商品,隐藏更多按钮
					flag = 0;
				}
		 });
	}
});
</script>
<script type="text/javascript" src="__WAP__/js/common.js"></script>
</html>