<?php 

/*
*	检查一个字符串或数组是否为空
*	@
*/
function is_empty($str)
{
	return empty($str)?false:true;
}

/*
*	
*	比较函数,$oper为比较运算符
*/
function compare($num1,$num2,$oper=">")
{
	switch ($oper) {
		case '==':
			$result = ($num1==$num2);
			break;
		case '===':
			$result = ($num1===$num2);
			break;
		case '<':
			$result = ($num1<$num2);
			break;
		case '<=':
			$result = ($num1<=$num2);
			break;
		case '!=':
			$result = ($num1!=$num2);
			break;
		case '<>':
			$result = ($num1<>$num2);
			break;
		case '>=':
			$result = ($num1>=$num2);
			break;
		case '>':
			$result = ($num1>$num2);
			break;
		default:
			$result = false;
			break;
	}
	return $result;
}

/*
*	检查两个指是否相等
*/
function eq($arg1,$arg2)
{
	return ($arg1 == $arg2)?true:false;
}

/*
*	获取目录下的文件名
*/
function get_filename($url)
{
	$filename = '';
	$arr = explode("/",$url);
	$arr_count = count($arr);
	if ($arr_count<2)
	{
		$filename = $url;
	}
	else
	{
		$filename = $arr[$arr_count-1];
	}
	return $filename;
}

/*
*	格式化响应结构体
*	@param		string		$struct		结构体名称
*	@param		array		$data			数据
*	@param		bool		$traversal	是否遍历 
*/
function format_struct($struct,$data,$traversal = false)
{
	$stand = C('struct');
	$stand = $stand[$struct];
	if (!$stand) return false;
	$rs = array();
	if (!$traversal)
	{
		foreach($stand as $k)
		{
			$rs[$k] = isset($data[$k])?$data[$k]:'';
		}
	}
	else
	{
		foreach($data as $key=>$value)
		{
			foreach($stand as $k)
			{
				$rs[$key][$k] = isset($value[$k])?$value[$k]:'';
			}
		}
	}
	return $rs;
}

//生成短链接
function getShortUrl($url)
{
	$gate = 'http://api.t.sina.com.cn/short_url/shorten.json';
	$appkey = '4070656144';
	$rs = "{$gate}?source={$appkey}&url_long={$url}";
	$arr = json_decode(file_get_contents($rs), true);
	return $arr[0]['url_short'];
}

//发送短信
function sendsms($mobile,$content)
{
	Vendor('SMS.SMSHelper');
	return SMSHelper::sendSMS($mobile, $content);
}

/*
*	随机码生成
*	@param			int 		$length		长度
*/
function randcode($length)
{
	if (SMSDEBUG) return 5555;
	$start = pow(10,($length-1));
	$end = pow(10,$length)-1;
	return rand($start,$end);
}

//判断相应图片是否存在,不存在则赋值为空
function imgCheck($url,$default='')
{
	if (is_file($url) || is_file(ROOT_PATH.$url) ) {
		return $url;
	}
	else {
		return $default;
	}
}






	
	

