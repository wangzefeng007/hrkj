$(function(){
	
	var w = document.documentElement.clientWidth || document.body.clientWidth || document.body.scrollWidth || window.innerWidth;
	var h = document.documentElement.clientHeight || document.body.clientHeight || document.body.scrollHeight || window.innerHeight;
	
	// 登录面板布局定位
	$(".panel_login").css("margin-top", h / 2 - $(".panel_login").height() / 2 + 40).fadeIn(0);
	$(".body_bg img").css("height", h);
	$(".panel_login_input_bg").css("height", $(".panel_login_input").height() - 140);
});