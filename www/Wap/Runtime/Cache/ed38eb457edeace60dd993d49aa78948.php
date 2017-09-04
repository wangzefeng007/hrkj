<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html lang="en">
<head>
<title><?php echo PJ_NAME;?>注册页</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="description" content="" />
<meta name="keywords" content="" />
<style type="text/css">
/* ------------------------------------------------ 头部 ------------------------------------ */
@media screen and (max-width: 380px) {
	html{
		font-size: 10px;
	}
}
@media screen and (min-width: 380px) and (max-width:500px) {
	html{
		font-size: 12px;
	}
}
@media screen and (min-width: 500px) and (max-width:650px) {
	html{
		font-size: 13px;
	}
}
@media screen and (min-width: 650px) {
	html{
		font-size: 14px;
	}
}
header{
	background: #38c3ec;
	color: white;
	text-align: center;
	margin: 0px;
	padding: 0px;
	display: table;
	width: 100%;
}
header .button, .title{
	display: table-cell;
	height: 50px;
	overflow: hidden;
	margin: 0px;
	padding: 0px;
	vertical-align: middle;
}
header .button{
	width: 27%;
	height: 50px;
}
header .title{
	width: 46%;
	font-size: 1.8rem;
	margin-top: 0px;
	line-height: 50px;
}
header .button .back{
	overflow: hidden;
	display: table-cell;
	text-decoration: none;
	color: white;
	font-size: 1.6rem;
	padding: 2px 0px 0px 0px;
}
header .button .back img{
	border: none;
	width: 26px;
	height: auto;
	padding: 0px 0px 0px 10px;
	vertical-align: middle;
}
/* ------------------------------------------------ 头部 ------------------------------------ */
body{
	background: #f2f2f2;
	margin: 0px;
	padding: 0px;
	font-family:"微软雅黑", Arial, helvetica,"华文黑体", "方正黑体简体";
}
.item{
	padding: 0px;
	margin: 0px;
	text-align: center;
	position: relative;
}
.item img{
	padding: 0px;
	margin: 0px auto;
	border: none;
	display: block;
	width: 100%;
}
.top_btn_bar{
	position: absolute;
	display: block;
	width: 96%;
	left: 2%;
	bottom: 3.5%;
	overflow: hidden;
	height: 9%;
}
.bottom_btn_bar{
	position: absolute;
	display: block;
	width: 96%;
	left: 2%;
	top: 25%;
	overflow: hidden;
	height: 23%;
}
.android{
	display: block;
	width: 48%;
	margin: 0px 1%;
	float: left;
	height: 100%;
	background: url("__WAP__/images/btn_android.png") no-repeat;
	background-size: contain;
}
.android_hover{
	display: block;
	width: 48%;
	margin: 0px 1%;
	float: left;
	height: 100%;
	background: url("__WAP__/images/btn_android_hover.png") no-repeat;
	background-size: contain;
}
.ios{
	display: block;
	width: 48%;
	margin: 0px 1%;
	float: left;
	height: 100%;
	background: url("__WAP__/images/btn_ios.png") no-repeat;
	background-size: contain;
}
.ios_hover{
	display: block;
	width: 48%;
	margin: 0px 1%;
	float: left;
	height: 100%;
	background: url("__WAP__/images/btn_ios_hover.png") no-repeat;
	background-size: contain;
}

.input_bar{
	margin: 0px;
	padding: 20px 0px;
	display: block;
	overflow: hidden;
	background: #f2f2f2;
}
.input_bar .input_row{
	margin: 0px;
	padding: 6px 16px;
	display: block;
	overflow: hidden;
}
.input_bar .btn_row{
	margin: 0px;
	padding: 8px 16px 12px 16px;
	display: block;
	overflow: hidden;
}
.input_bar .info_row{
	margin: 0px;
	padding: 8px 0px;
	display: block;
	overflow: hidden;
	text-align: center;
	font-size: 1.5rem;
	color: black;
}
.input_bar .tip_row{
	margin: 0px;
	padding: 16px 0px;
	display: block;
	overflow: hidden;
	text-align: center;
	font-size: 1.2rem;
}
.input_bar .text_l{
	background: white;
	border: none;
	width: 97%;
	padding: 0px 0px 0px 3%;
	margin: 0px;
	height: 50px;
	font-size: 16px;
	line-height: auto;
	
	-moz-border-radius: 4px;
	-khtml-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
.input_bar .text_s{
	background: white;
	border: none;
	width: 42%;
	margin: 0px;
	padding: 0px 0px 0px 3%;
	height: 40px;
	line-height: auto;
	font-size: 16px;
	
	-moz-border-radius: 4px;
	-khtml-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
.input_bar .btn_l{
	border: none;
	width: 100%;
	margin: 4px 0px 6px 0px;
	padding: 0px 0px 0px 0px;
	text-align: center;
	height: 40px;
	line-height: 40px;
	font-size: 14px;
	
	-moz-border-radius: 4px;
	-khtml-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
.input_bar .btn_s{
	border: none;
	width: 45%;
	margin: 0px;
	padding: 0px;
	text-align: center;
	height: 40px;
	line-height: 40px;
	font-size: 12px;
	
	-moz-border-radius: 4px;
	-khtml-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
.input_bar .primary{
	background: #38c3ec;
	color: white;
}
.input_bar .right{
	float: right;
}
.input_bar .left{
	float: left;
}
</style>
</head>
<body>
<header>
	<div class="button"><a class="back" href="javascript:window.history.back();"><img src="__WAP__/images/btn_back.png" />返回</a></div>
	<div class="title"><?php echo PJ_NAME;?>注册页</div>
	<div class="button"></div>
</header>
<div class="input_bar">
	<form name="" id="" action="/api.php/user/register" method="post" class="ajaxForm">
		<div class="input_row">
			<input name="mobile" type="text" class="text_l mobile" id="mobile" maxlength="11" placeholder="请输入您的手机号码" />
		</div>
		<div class="input_row">
			<input name="code" type="text" class="text_s left" id="code" maxlength="8" placeholder="请输入验证码" />
			<button type="button" class="btn_s primary right">获取验证码</button>
		</div>
		<div class="input_row">
			<input name="password" type="password" class="text_l" id="password" placeholder="请设置6~16位密码" />			
		</div>
		<div class="info_row">
			<span>请先注册账号，然后下载APP即可直接登陆</span>
		</div>
		<div class="btn_row">
			<button class="primary btn_l">注册</button>
			<button class="primary btn_l" type="button" onclick="javascript:window.location.href='<?php echo WAP_DOWN;?>'">下载APP</button>
			<input name="invite_mobile" type="hidden" id="invite_mobile" value="<?php echo ($invite_mobile); ?>">
		</div>
		<!--<div class="tip_row">
			<span>公司</span><br/>
			<span>电话</span>
		</div>-->
	</form>
</div>
<!-- 
<div class="item"><img src="__WAP__/images/reg/body_02.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_03.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_04.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_05.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_06.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_07.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_08.png" /></div>
<div class="item"><img src="__WAP__/images/reg/body_09.png" /></div>

<div class="item">
	<img src="__WAP__/images/reg/body_10.png" />
	<div class="bottom_btn_bar">
		<a href="#" class="android"></a>
		<a href="#" class="ios"></a>
	</div>
</div>
 -->
</body>
<script type="text/javascript" src="__WAP__/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="__WAP__/js/jquery.form.min.js"></script>
<script type="text/javascript" src="__WAP__/js/public.js"></script>
<script type="text/javascript" src="__WAP__/js/jNotify/jNotify.js"></script>
<link rel="stylesheet" type="text/css" href="__WAP__/js/jNotify/jNotify.css" />
<script type="text/javascript">
$(function(){
	$(document).on("mouseover", ".android", function(){
		$(this).addClass("android_hover").removeClass("android");
	});
	$(document).on("mouseout", ".android", function(){
		$(this).addClass("android").removeClass("android_hover");
	});
	$(document).on("mouseover", ".ios", function(){
		$(this).addClass("ios_hover").removeClass("ios");
	});
	$(document).on("mouseout", ".ios", function(){
		$(this).addClass("ios").removeClass("ios_hover");
	});
});
</script>
<script type="text/javascript">
	var button = $('.btn_s');
	var click = 60;
	var sms_verify = "<?php echo ($sms_verify); ?>";
	button.click(function(){
		//jSuccess(123, {TimeShown : 800});
		//jError(123); 
		var mobile = $('.mobile').val();
		if(click<60){
			jError("还没到60秒！");
			return ;
		}
		//提交数据
		$.ajax({
			type:'get',//可选get
			url:'/api.php/user/regsms',//这里是接收数据的PHP程序
			data:'mobile='+mobile+'&sms_verify='+sms_verify,//传给PHP的数据，多个参数用&连接
			dataType:'Json',//服务器返回的数据类型 可选XML ,Json jsonp script html text等
			success:function(msg){
				if(msg.result.code == '10000'){
					jSuccess(msg.result.msg, {TimeShown : 800});
					sms_verify = msg.data.sms_verify;
					var set= setInterval(function(){
						button.text(click+'秒重新获取');
						button.css('background', '#aaa');
						if(click==0){
							button.text('获取验证码');
							button.css('background', '#38c3ec');
							clearInterval(set);
							click=60;
							return;
						}
						click = click-1;
					}, 1000);
				}else{
					jError(msg.result.msg); 
				}
				//这里是ajax提交成功后，PHP程序返回的数据处理函数。msg是返回的数据，数据类型在dataType参数里定义！
			},
			error:function(){
				jError("提交失败！"); 
			}
		})
	});
	
$(function(){
	//ajax表单提交
	$('.ajaxForm').ajaxForm(function(r){
		if (typeof(r) == 'string') r = eval("("+r+")");
		if(r.result.code == '10000'){	//添加成功

			jSuccess(r.result.msg, {
				TimeShown : 400,
				onClosed:function(){
					url = "<?php echo U('reg/succeed');?>";
					window.location.href = url;
				}
			});

		}
		else{
			jError(r.result.msg,{TimeShown : 600}); 
		}
	});
});
</script>
<script type="text/javascript" src="__WAP__/js/common.js"></script>
</html>