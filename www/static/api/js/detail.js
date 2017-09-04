$(function(){
	numChk();
	
	// 增加按钮
	$("#btn_plus").click(function(){
		$("#number").val(parseInt($("#number").val()) + 1);
		numChk();
	});
	
	// 减少按钮
	$("#btn_minus").click(function(){
		var num = parseInt($("#number").val());
		if(num>=2)
		{
			$("#number").val(num - 1);
		}
		numChk();
	});
	
	// 数量输入框
	$("#number").change(function(){
		var num = parseInt($("#number").val());
		stock=parseInt($("#stock").text());
		if(num<=0) $("#number").val(1);
		else if(isNaN(num)) num=1;
		$("#number").val(num);
		numChk();
	});
	
	function numChk(){
		num=number.value;
		if(num < 1) num = 1;
		stock=parseInt($("#stock").text());
		if (num>stock) num = stock;
		num=parseInt(num);
		number.value = num;
	}
	// 幻灯片
	$(".img_bar img").each(function(e){
		$(".marquee_bar_container").append('<img src="' + $(this).attr("src") + '" />');
		$(".marquee_bar .marquee_btn").append('<span>●</span>');
	});
	$(".marquee_bar_container").data("current", 0).data("count", $(".marquee_bar_container img").size());
	$($(".marquee_bar .marquee_btn span").get(0)).css("color", "white");
	// 触发图片浏览器
	$(".marquee_bar_container img").each(function(e){
		$(this).click(function(){
			$("#img_view_bar").css("display", "block");
		});
	});
	
	// 幻灯片焦点变换
	function changeFocus(i)
	{
		$(".marquee_bar .marquee_btn span").each(function(){
			$(this).css("color", "#838383");
		});
		$($(".marquee_bar .marquee_btn span").get(i)).css("color", "white");
	}
	
	// 定时轮播
	var loopMarqueeControl = null;
	function loopMarquee(time)
	{
		loopMarqueeControl = setInterval(function()
		{
			var current = parseInt($(".marquee_bar_container").data("current")) - 1;
			if(Math.abs(current) >= parseInt($(".marquee_bar_container").data("count")))current = 0;
			$($(".marquee_bar_container img").get(0)).animate({
				"margin-left": current + "00%"
			}, 500, "swing");
			$(".marquee_bar_container").data("current", current);
			changeFocus(Math.abs(current));
		}, time);
	};
	loopMarquee(4000);
	
	var startX = 0;
	var endX = 0;
	$("#marquee_bar").on('touchmove', function(e) {
		endX = parseInt(e.touches[0].pageX);
	    // 判断用户滑动意向
	    if (Math.abs(endX - startX)>=5) {
	    	e.preventDefault();// 阻止浏览器默认事件，重要 
		}
	}, false);
	$("#marquee_bar").on('touchstart', function(e){
		startX = parseInt(e.touches[0].pageX);
	}, false);
	
	// 幻灯片 - 左滑动
	$("#marquee_bar").swipeLeft(function(e){ // .on("swipeleft",function(){
		clearInterval(loopMarqueeControl);		// 清除轮播计划
		var current = parseInt($(".marquee_bar_container").data("current")) - 1;
		if(Math.abs(current) >= parseInt($(".marquee_bar_container").data("count")))current = 0;
		$($(".marquee_bar_container img").get(0)).animate({
			"margin-left": current + "00%"
		}, 500, "swing");
		$(".marquee_bar_container").data("current", current);
		changeFocus(Math.abs(current));
		loopMarquee(4000);		// 启动轮播计划
	});
	// 幻灯片 - 右滑动
	$("#marquee_bar").swipeRight(function(e){ // .on("swiperight",function(){
		clearInterval(loopMarqueeControl);		// 清除轮播计划
		var current = parseInt($(".marquee_bar_container").data("current")) + 1;
		if(current >= 1)current = - parseInt($(".marquee_bar_container").data("count")) + 1;
		$($(".marquee_bar_container img").get(0)).animate({
			"margin-left":  current + "00%"
		}, 500, "swing");
		$(".marquee_bar_container").data("current", current);
		changeFocus(Math.abs(current));
		loopMarquee(4000);		// 启动轮播计划
	});
	
	
	// 图片浏览器
	$(".img_bar img").each(function(e){
		$(".img_view_bar_container").append('<img src="' + $(this).attr("src") + '" />');
	});
	$(".img_view_bar_container").data("current", 0).data("count", $(".img_view_bar_container img").size());
	var obj = document.getElementById('img_view_bar');
	obj.addEventListener('touchmove', function(event) {
	     // 如果这个元素的位置内只有一个手指的话
	    if (event.targetTouches.length == 1) {
	    	event.preventDefault();// 阻止浏览器默认事件，重要 
		}
	}, false);
	// 关闭图片浏览器
	$(".img_view_bar_container img").each(function(e){
		$(this).click(function(){
			$("#img_view_bar").css("display", "none");
		});
	});
	$(".img_view_bar_bg").click(function(){
		$("#img_view_bar").css("display", "none");
	});
	$(".img_view_bar_container").click(function(){
		$("#img_view_bar").css("display", "none");
	});
	// 图片浏览器 - 左按钮
	$(".img_view_bar_btn_left").click(function(){
		var current = parseInt($(".img_view_bar_container").data("current")) - 1;
		if(Math.abs(current) >= parseInt($(".img_view_bar_container").data("count")))current = 0;
		$($(".img_view_bar_container img").get(0)).animate({
			"margin-left": current + "00%"
		}, 500, "swing");
		$(".img_view_bar_container").data("current", current);
	});
	// 图片浏览器 - 右按钮
	$(".img_view_bar_btn_right").click(function(){
		var current = parseInt($(".img_view_bar_container").data("current")) + 1;
		if(current >= 1)current = - parseInt($(".img_view_bar_container").data("count")) + 1;
		$($(".img_view_bar_container img").get(0)).animate({
			"margin-left":  current + "00%"
		}, 500, "swing");
		$(".img_view_bar_container").data("current", current);
	});
	
	// 幻灯片 - 左滑动
	$("#img_view_bar").swipeLeft(function(e){ // .on("swipeleft",function(){
		var current = parseInt($(".img_view_bar_container").data("current")) - 1;
		if(Math.abs(current) >= parseInt($(".img_view_bar_container").data("count")))current = 0;
		$($(".img_view_bar_container img").get(0)).animate({
			"margin-left": current + "00%"
		}, 500, "swing");
		$(".img_view_bar_container").data("current", current);
	});
	// 幻灯片 - 右滑动
	$("#img_view_bar").swipeRight(function(e){ // .on("swiperight",function(){
		var current = parseInt($(".img_view_bar_container").data("current")) + 1;
		if(current >= 1)current = - parseInt($(".img_view_bar_container").data("count")) + 1;
		$($(".img_view_bar_container img").get(0)).animate({
			"margin-left":  current + "00%"
		}, 500, "swing");
		$(".img_view_bar_container").data("current", current);
	});
});


