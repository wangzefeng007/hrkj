//全局公用函数库
$(function(){
	
	//ajax表单提交
	$('.ajax-form').ajaxForm(function(r){
		if (typeof(r) == 'string') r = eval("("+r+")");
		if (r.status) {
			if(r.status == '10000'){	//添加成功
				if (!r.info && r.data.href) window.location.href = r.data.href;
				else {
					jSuccess(r.info, {
						TimeShown : 400,
						onClosed:function(){
							if (r.data.href) window.location.href = r.data.href;
						}
					});
				}
			}else{
				jError(r.info,{TimeShown : 600}); 
			}
		}
		else if(r.result) {
			if(r.result.code == '10000'){	//添加成功
				if (!r.result.msg && r.link) window.location.href = r.data.href;
				else {
					jSuccess(r.result.msg, {
						TimeShown : 400,
						onClosed:function(){
							if (r.link) window.location.href = r.data.href;
						}
					});
				}
			}else{
				jError(r.result.msg,{TimeShown : 600}); 
			}
		}
	});
	
	
});
