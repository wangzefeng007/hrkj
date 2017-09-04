$(function(){
	
	// 初始化图片宽度、高度
	var $order_img = $(".goods_thumb_size_img");
	$order_img.height($order_img.width());
	
	// 图片延迟加载
	echo.init({
		offset: 100,
		throttle: 250,
		unload: false,
		callback: function (element, op) {
			//console.log(element, 'has been', op + 'ed')
		}
	});
});