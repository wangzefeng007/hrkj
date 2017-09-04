<?php 
	//格式化输出
	function p($str = ''){
		echo "<pre>";
		print_r($str);
		echo "</pre>";
	}
	/**
        * 导出数据为excel表格
        *@param $data    一个二维数组,结构如同从数据库查出来的数组
        *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
        *@param $filename 下载的文件名
        *@examlpe 
        $stu = M ('User');
        $arr = $stu -> select();
        exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
    */
    function exportexcel($data=array(),$title=array(),$filename='report')
	{
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");  
        header("Content-Disposition:attachment;filename=".$filename.".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        //导出xls 开始
        if (!empty($title)){
            foreach ($title as $k => $v) {
                $title[$k]=iconv("UTF-8", "GB2312",$v);
            }
            $title= implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($data)){
            foreach($data as $key=>$val){
                foreach ($val as $ck => $cv) {
                    $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
                }
                $data[$key]=implode("\t", $data[$key]);
                
            }
            echo implode("\n",$data);
        }
    }
	
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
	Vendor('SmsXuanwu.SmsApi');
	$clapi  = new SmsApi();
	return $clapi->sendSMS($mobile, $content,true);
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
function page($count,$psize = '')
{
	import("ORG.Util.Page");
	$psize = ($psize>0)?$psize:C('psize');
	$psize = $psize?$psize:40;
	$page = new Page($count,$psize);
	return $page->show();
}

/*
*	视图模板中输出时间
*/
function vtime($format="Y-m-d",$time)
{
	if (!$time)
	{
		return "-";
	}
	return date($format,$time);
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
*	在当前的URL上进行叠加，仅生成搜索URL
*/
function _U($params = array())
{ 

	$_query = $_GET;
	unset($_query['_URL_']);
	unset($_query['p']);
	$_uri = explode('?',$_SERVER['REQUEST_URI']);
	$url = preg_replace("/\/p\/[0-9]*\//","/",rtrim($_uri[0],"/")."/");

	foreach($_query as $key=>$value)
	{
		if (strpos($url,$key."/") !== false)
		{
			$str = ($value == '')?'':"/".$key.'/'.$value.'/';

			$url = preg_replace("/\/".$key."[^\/]*\/[^\/]*\//",$str,$url);
		}
		else
		{
		$url .= $key.'/'.$value.'/';
		}
	}

	$url = preg_replace("/[^\/]*\/[\s]*\//","",$url);	
	$url = rtrim($_SERVER['HTTP_HOST'].$url,'/').'/';

	if (empty($params)) return "http://".$url;
	foreach($params as $key=>$value)
	{
		if (strpos($url,$key."/") !== false)
		{
			$str = ($value === false)?'':$key.'/'.$value.'/';
			$url = preg_replace("/".$key."[^\/]*\/[^\/]*\//",$str,$url);
		}
		else
		{
			if ($value === false) continue;
			$url .= $key.'/'.$value.'/';
		}
	}

	return "http://".str_replace("//","/",$url);
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

/*	查询物流明细
	logistics 物流名称
	logistics_no 物流单号
*/
function getExpress($logistics,$logistics_no)
{
	if ($logistics && $logistics_no) {
		Vendor('Express.Express','','.class.php');
		$exp = new Express();
		$express = $exp->getorder($logistics,$logistics_no);
	}
	if ($express['status']!='200') $express['data'][1]['context'] = '暂无物流信息';
	return $express;
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

/*
*	弹出消息
*/
function alert_msg($msg,$url = '')
{
	echo '<script type="text/javascript">';
	echo 'alert("'.$msg.'");';
	if ($url)
	{
		echo 'window.location.href="'.$url.'";';
	}
	echo '</script>';
	exit;
	
}	

//字符串复制函数
function strcpy($str,$num) {
	$add='';
	for ($i=1;$i<=$num;$i++) $add.=$str;
	return $add;
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