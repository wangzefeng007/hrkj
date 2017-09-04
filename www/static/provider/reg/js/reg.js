$(function(){
	
	var w = document.documentElement.clientWidth || document.body.clientWidth || document.body.scrollWidth || window.innerWidth;
	var h = document.documentElement.clientHeight || document.body.clientHeight || document.body.scrollHeight || window.innerHeight;
	
	// 登录面板布局定位
	$(".menu_bar").height(parseInt($(".menu_bar").height())>=(h-200)?$(".menu_bar").height():(h-130));
	$(".input_bar").css("width", (parseInt($(".content_bar").innerWidth()) - parseInt($(".menu_bar").outerWidth()) - 80) + "px").fadeIn(500);
});