$(function () {
	$(".downbtn").click(function () {
		if (isWeiXin()) {
			$("#shade1,#useBrowser").show();
			return false;
		}
	});
	$("#shade1,#useBrowser").click(function () {
		$("#shade1,#useBrowser").hide();
	});
	$("#closeLayer").click(function () {
		$("#appIntro,#closeLayer").hide();
		return false;
	});
});

 
function isWeiXin(){ 
	var ua = window.navigator.userAgent.toLowerCase(); 
	if(ua.match(/MicroMessenger/i) == 'micromessenger'){ 
		return true; 
	}else{ 
		return false; 
	} 
}