var imgUrl = "";
var lineLink = "";
var descContent = '微商汇-史上最牛的移动电商赚钱平台';
var shareTitle = '微商汇-史上最牛的移动电商赚钱平台';
var appid = '00000001';

function shareFriend()
{
	WeixinJSBridge.invoke('sendAppMessage', {
		"appid" : appid,
		"img_url" : imgUrl,
		"img_width" : "200",
		"img_height" : "200",
		"link" : lineLink,
		"desc" : descContent,
		"title" : shareTitle
	}, function(res) {
		if(res.err_msg=="send_app_msg:ok");
	});
}

function shareTimeline()
{
	WeixinJSBridge.invoke('shareTimeline', {
		"img_url" : imgUrl,
		"img_width" : "200",
		"img_height" : "200",
		"link" : lineLink,
		"desc" : descContent,
		"title" : shareTitle
	}, function(res) {
		if(res.err_msg=="share_timeline:ok");
	});
}

function shareWeibo() {
	WeixinJSBridge.invoke('shareWeibo', {
		"content" : descContent,
		"url" : lineLink
	}, function(res) {
		if(res.err_msg=="share_timeline:ok");
	});
}

// 当微信内置浏览器完成内部初始化后会触发WeixinJSBridgeReady事件。
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	// 发送给好友
	WeixinJSBridge.on('menu:share:appmessage', function(argv) {
		shareFriend();
	});
	// 分享到朋友圈
	WeixinJSBridge.on('menu:share:timeline', function(argv) {
		shareTimeline();
	});
	// 分享到微博
	WeixinJSBridge.on('menu:share:weibo', function(argv) {
		shareWeibo();
	});
}, false);
