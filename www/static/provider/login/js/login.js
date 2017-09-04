$(function(){
	
	var w = document.documentElement.clientWidth || document.body.clientWidth || document.body.scrollWidth || window.innerWidth;
	var h = document.documentElement.clientHeight || document.body.clientHeight || document.body.scrollHeight || window.innerHeight;
	
	// 登录面板布局定位
	$(".panel_login").css("margin-top", h / 2 - $(".panel_login").height() / 2).fadeIn(800);
});