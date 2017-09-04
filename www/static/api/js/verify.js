$(function(){
	
	/**
	 * 对传值进行有效性校验
	 */
	function verifyValue(verify, value)
	{
		var msg = null;
		
		if(verify.require&&!value)
		{
			msg = '必须！';
		}
		else if(verify.nonsql&&value.match(/(\s|=|!|\?|\+|-|<|>)/i))
		{
			msg = '不能包含特殊字符和空格！';
		}
		else if(verify.maxlength&&(value.toString().length>verify.maxlength))
		{
			msg = '长度不能超过'+verify.maxlength+'位！';
		}
		else if(verify.minlength&&(value.toString().length<verify.minlength))
		{
			msg = '长度不能少于'+verify.minlength+'位！';
		}
		else
		{
			switch(verify.type){
			case 'number':
				if(isNaN(value))
				{
					msg = '必须为数字！';
				};
				break;
			case 'email':
				if(!value.toString().match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/))
				{
					msg = '必须为正确的电子邮件地址格式！';
				};
				break;
			case 'letter':
				if(!value.toString().match(/^[a-zA-Z]+$/))
				{
					msg = '必须为英文字母！';
				};
				break;
			case 'var':
				if(!value.toString().match(/^[a-zA-Z0-9]+$/))
				{
					msg = '必须为数字或字母！';
				};
				break;
			case 'zipcode':
				if(!value.toString().match(/^\d{6}$/))
				{
					msg = '邮政编码错误！';
				};
				break;
			case 'mobile':
				if(!value.toString().match(/^1[34578]{1}\d{9}$/))
				{
					msg = '手机号码错误！';
				};
				break;
			case 'ip':
				if(!value.toString().match(/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/))
				{
					msg = 'IP地址格式错误！';
				};
				break;
			case 'idcard':
				if(!value.toString().match(/^\d{15}(\d{2}\w{1})?$/))
				{
					msg = '身份证格式错误！';
				};
				break;
			default:
				break;
			}
		}
		return msg;
	}
	
	$('.verify-form').submit(function(){
		
		var success = true;
		$('.verify-form input[verify]').each(function(i){
			
			var verify = {};
			if($(this).attr('verify')){
				verify = eval('(' + $(this).attr('verify') + ')');
			}
			var value = $(this).val();
			var id = $(this).attr('id');
			var tid = verify.display;
			
			var msg = verifyValue(verify, value);
			if(msg)verify.fail(id, tid, msg);
			
			success = success&&!msg;
			if(($('.verify-form input[verify]').size()-1)==i&&success)
			{
				$('.verify-form').submit();
			}
		});
		return false;
	});
	
	$('.verify-form-ajax').submit(function(){

		var success = true;
		$('.verify-form-ajax input[verify]').each(function(i){
			
			var verify = {};
			if($(this).attr('verify')){
				verify = eval('(' + $(this).attr('verify') + ')');
			}
			var value = $(this).val();
			var id = $(this).attr('id');
			var tid = verify.display;

			var msg = verifyValue(verify, value);
			if(msg)verify.fail(id, tid, msg);
			
			success = success&&!msg;
			if(($('.verify-form-ajax input[verify]').size()-1)==i&&success)
			{
				verify.success($('.verify-form-ajax').serialize());
			}
		});
		return false;
	});
});