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
*	检查手机号是否正确
*	@
*/
function is_mobile($phonenumber)
{
	if(preg_match("/^1[34578]{1}\d{9}$/",$phonenumber)){  
		return true;
	}else{  
		return false; 
	} 
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
*	@param		bool		$default	默认值 
*/
function format_struct($struct,$data,$traversal = false,$default = '')
{
	$stand = C('struct');
	$stand = $stand[$struct];
	if (!$stand) return false;
	$rs = array();
	if (!$traversal)
	{
		foreach($stand as $k)
		{
			$rs[$k] = isset($data[$k])?$data[$k]:$default;
		}
	}
	else
	{
		foreach($data as $key=>$value)
		{
			foreach($stand as $k)
			{
				$rs[$key][$k] = isset($value[$k])?$value[$k]:$default;
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
	$url = $arr[0]['url_short']?$arr[0]['url_short']:$url;
	return $url;
}


//发送短信
function sendsms($mobile,$content)
{
	//测试环境不发送短信
 	if (SMSDEBUG) return true;
	Vendor('SmsXuanwu.SmsApi');
	$clapi  = new SmsApi();
    $ip = get_client_ip();
    eblog("短信下发",'客户端IP:'.$ip.'手机号：'.$mobile.',短信内容：'.$content,'sms_'.date("Ymd"));
	$rs = $clapi->sendSMS($mobile, $content,true);
	/*$rs = $clapi->execResult($rs);*/
	if($rs == '0'){
		return true;
	}else{
		return false;
	}
}

//极光推送
function jpush($receive='all',$content='',$content_message='')
{
	//调用推送,并处理
	Vendor('Jpush.jpush');//调用 极光 接口
	$pushObj = new jpush();
	$result = $pushObj->push($receive,$content,$m_type='',$m_txt='',$m_time='86400',$content_message);
}

//fsock数据提交
function fsock_post($url,$data)
{
	$url = parse_url($url);
	$url['port'] = $url['port']?$url['port']:'80';
	if ($url['scheme'] == 'https')
	{
		$host = "ssl://{$url['host']}";
		$url['port'] = ($url['port']!='80')?$url['port']:'443';
	}
	else
	{
		$host = "http://{$url['host']}";
	}
	$data = http_build_query($data);
	$fp = fsockopen("{$host}", $url['port'], $errno, $errstr, 30);
	if (!$fp) {
		// echo "$errstr ($errno)<br />\n";	//无法访问主机报错
		return false;
	} else {
		$out = "POST {$url['path']} HTTP/1.1\r\n";
		$out .= "Host: {$url['host']}\r\n";
		// $out .= "Content-type:text/html;charset=gbk\r\n";
		$out .= "Content-type:application/x-www-form-urlencoded\r\n";
		$out .= "Content-length:".strlen($data)."\r\n";  
		$out .= "Connection: Close\r\n\r\n";
		$out .= "{$data}";
		fwrite($fp, $out);
		while (!feof($fp)) {
			$res .= fgets($fp, 128);
		}
		fclose($fp);
	}
	return $res;
}

//curl数据提交
function curl_post($url,$data)
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch,CURLOPT_ENCODING ,'gzip'); //加入gzip解析
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION ,1); //加入重定向处理
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER ,0); //不验证ssl
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST ,0); //不验证ssl
	ob_start();
	curl_exec($ch);
	$httpCode = curl_getinfo($ch,CURLINFO_FILETIME);
	$res = ob_get_contents();
	ob_end_clean();
	curl_close($ch);
	return $res;
}

//html数据提交
function html_post($url,$data,$disp = 0)
{
	if ($disp)
	{
		$html = '<div style="display:none;"><form action="'.$url.'" method="post" name="payform" id="payform">';
	}
	else
	{
		$html = '<div><form action="'.$url.'" method="post" name="payform" id="payform">';
	}
	if ($data)
	{
		foreach ($data as $key => $val)
		{
			$html .= '<input name="'.$key.'" type="text"  value="'.$val.'">';
		}
	}
	if (!$disp) $html .= '<input name="submit" type="submit" value=" 提   交 ">';
	$html .= '</form></div>';
	if ($disp) $html .= '<script>document.getElementById("payform").submit();</script>';
	$html = str_replace('"',"'",$html);
	echo $html;
}

/*
*	状态描述
*/
function status_desc($type = 'STATUS',$status)
{
	$status_arr = C($type);
	if (!$status_arr)
	{
		return false;
	}
	return $status_arr[$status];	
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

/*
*	递归创建目录 
*	必须是绝对目录
*/
function xmkdir($pathurl)
{
	$path = "";
	$str = explode("/",$pathurl);
	foreach($str as $dir)
	{
		if (empty($dir)) continue;
		$path .= (substr($dir,0,2) == "\\\\")?$dir:"/".$dir;
		if (!is_dir($path))
		{
			mkdir($path,0777);
		}
	}
}

/**
*	数组值全部转为字符串
*/
function array_values_to_string(&$array)
{
	foreach($array as $key=>&$value)
	{
		if (is_array($value))
		{
			array_values_to_string($value);
		}
		else
		{
			$value = (string) $value;
		}
	}
	// return $array;
}

/*
*	用数组中的某个值重设数组键值
*	
*/
function reset_array_key($array,$key,$field = '')
{
	$_array = array();
	foreach($array as $value)
	{
		$_array[$value[$key]] = !empty($field)?$value[$field]:$value;
	}
	return $_array;
}

/*
*	日志记录
*/
function eblog($tag,$content,$file='')
{
	if (is_array($content))
	{
		ob_start();
		print_r($content);
		$content = ob_get_contents();
		ob_end_clean();
	}
	if (!is_dir(C('EBLOGPATH'))) xmkdir(C('EBLOGPATH'));
	if ($file)
	{
		$log_file = C('EBLOGPATH').$file.".log";
	}
	else
	{
		$log_file = C('EBLOGPATH').date('Ymd').".log";
	}
		
	$log = "[".date("YmdHis")."] ".$tag.":".$content;
	$f = fopen($log_file,"ab+");
	fwrite($f,$log."\n");
	fclose($f);
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

//字符串复制函数
function strcpy($str,$num) {
	$add='';
	for ($i=1;$i<=$num;$i++) $add.=$str;
	return $add;
}

//数字格式化函数(数值,位数),返回值为字符串
function format_num($num,$n) {
	if ($num==0) {
		$number=0;
		$numlen=1;
		$flag=0;
	}
	else {
		$number=abs($num);
		$numlen=strlen($number);
		if ($num>0) $flag=1; else $flag=-1;
	}
	for ($i=$numlen;$i<$n;$i++) $number="0".$number;
	if ($flag==-1) $number="-".$number;
	return $number;
}

//用户名称加*号
function hide_name($name) {
	$name = substr($name,0,3) . strcpy('*',strlen($name)/3-1);
	return $name;
}

/*
*	发送异步任务请求
*	需swoole支持
*/
/* function send_task($module,$action,$data)
{

	$config = C('task');
	global $msg;
	$msg	= json_encode(array('module'=>$module,'action'=>$action,'params'=>$data));

	$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC); 
	$client->on("connect", function(swoole_client $cli){
		global $msg;
		//任务投递失败直接关闭连接，避免引起阻塞
		if (!$cli->send($msg)) 
		{
			eblog('swoole','send task fail,send msg:'.$msg);
			$cli->close();			
		}	
	});
	$client->on("receive", function(swoole_client $cli, $data){
		$cli->close();
		swoole_event_exit();
	});
	$client->on("error", function(swoole_client $cli){
		exit();
	});
	$client->on("close", function(swoole_client $cli){
	});
	$client->connect($config['server'], $config['port'], $config['timeout']);
} */


function curl_get($url)
{
	
	$ch = curl_init();
	$header = 'Accept-Charset: utf-8';
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


	//$this->postdata = $data;
	$response = curl_exec($ch);
	curl_close ($ch);
	return $response;	
	
	
}

/**
 * 生成随机码
 * @param $length
 */
function rand_num($length = 4, $num = false) {
    // 密码字符集，可任意添加你需要的字符
    $chars = $num ? '0123456789' : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function getQrcodePayUrl($payInfo,$type = 1){
	$newUrl='http://wallet.huirongpay.com/wap.php/qrcode/qrcode?type='.$type.'&price=' . $payInfo['amount'] . '&key=' . base64_encode($payInfo['url']);
	return $newUrl;
}


	
	

