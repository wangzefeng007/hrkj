<?php

/**
 * 短信发送模块
 * @author Administrator
 *
 */
class SMSHelper
{
	public static $session_id = '';
	public static $ReturnString = '';
	public static $activeid = '';

	/**
	 * 发送短信接口
	 * @param	String	$mobile		手机号码，多个用英文逗号分隔
	 * @param	String	$content	短信内容
	 * @return 	Boolean		是否成功
	 */
	public static function sendSMS($mobile, $content)
	{
		// self::$session_id = " ";
		// self::$ReturnString = " ";
		// self::$activeid = " ";
		
		$UserID = '882846';
		$Account = 'yunfu';
		// $Password = 'B9CAE43A25854E16D84B389E74FCA7673B57BA0A';
		$Password = 'E595AA12E2A6CAE8B1D5462BFED7173E9C799D95';
		
		// $content = '尊敬的用户，您正在重设密码，验证码1234';
		$content_gb = iconv('UTF-8','GB2312',$content.'【'.SMS_NAME.'】');

		$res = self::httpPost( "/LANZGateway/Login.asp","UserID={$UserID}&Account={$Account}&Password={$Password}","",0);
		
		if ($res['ErrorNum'] == 0)
		{
			$res = self::httpPost( "/LANZGateway/SendSMS.asp","SMSType=1&Phone={$mobile}&Content={$content_gb}&ActiveID=".self::$activeid,self::$session_id,1);
			if ($res['ErrorNum'] == 0)
			{
				$flag = true;
				// eblog('短信通道-发送成功 - mobile',$mobile,'sms_succeed_'.date("Ymd"));
				// eblog('短信通道-发送成功 - content',$content,'sms_succeed_'.date("Ymd"));
				// eblog('短信通道-发送成功 - res',$res,'sms_succeed_'.date("Ymd"));
				// eblog('====================================================================','','sms_succeed_'.date("Ymd"));
			}
			else
			{
				eblog('短信通道-发送失败 - mobile',$mobile,'sms_error_'.date("Ymd"));
				eblog('短信通道-发送失败 - content',$content,'sms_error_'.date("Ymd"));
				eblog('短信通道-发送失败 - res',$res,'sms_error_'.date("Ymd"));
				eblog('====================================================================','','sms_error_'.date("Ymd"));
			}
			$res = self::httpPost( "/LANZGateway/Logoff.asp","ActiveID=".self::$activeid,self::$session_id,2);
			if ($res['ErrorNum'] != 0)
			{
				eblog('短信通道-退出失败 - mobile',$mobile,'sms_error_'.date("Ymd"));
				eblog('短信通道-退出失败 - content',$content,'sms_error_'.date("Ymd"));
				eblog('短信通道-退出失败 - res',$res,'sms_error_'.date("Ymd"));
				eblog('====================================================================','','sms_error_'.date("Ymd"));
			}
		}
		else
		{
			eblog('短信通道-登录失败 - mobile',$mobile,'sms_error_'.date("Ymd"));
			eblog('短信通道-登录失败 - content',$content,'sms_error_'.date("Ymd"));
			eblog('短信通道-登录失败 - res',$res,'sms_error_'.date("Ymd"));
			eblog('====================================================================','','sms_error_'.date("Ymd"));
		}
		return $flag?$flag:false;
	}

	public static function httpPost($sURL,$aPostVars,$sessid,$nMaxReturn)
	{
		$srv_ip = '219.136.252.188';//你的目标服务地址或频道.
		$srv_port = 80;
		$url = $sURL; //接收你post的URL具体地址 
		$fp = '';
		$resp_str = '';
		$errno = 0;
		$errstr = '';
		$timeout = 300;
		$post_str = $aPostVars;//要提交的内容.
		$fp = stream_socket_client($srv_ip.":80",$errno,$errstr,$timeout); 
		// $fp = fsockopen($srv_ip,$srv_port,$errno,$errstr,$timeout);
		if (!$fp)
		{
			// echo('fp fail');
		}

		$content_length = strlen($post_str);
		$post_header = "POST $url HTTP/1.1\r\n";
		$post_header .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$post_header .= "User-Agent: MSIE\r\n";
		$post_header .= "Host: ".$srv_ip."\r\n";
		$post_header .= "Cookie: ".$sessid."\r\n";
		$post_header .= "Content-Length: ".$content_length."\r\n";
		$post_header .= "Connection: close\r\n\r\n";
		$post_header .= $post_str."\r\n\r\n";

		// echo $post_header;
		fwrite($fp,$post_header);

		$inheader = 1;
		while(!feof($fp))
		{
			$resp_str .= fgets($fp,4096);//返回值放入$resp_str
			if ($inheader && ($resp_str == "\n" || $resp_str == "\r\n"))
			{
				$inheader = 0;     
			}     
			if ($inheader == 0) 
			{
				// echo $resp_str;     
			}
		} 

		// echo $resp_str;
		// echo "\r\n";
		$str = substr( $resp_str,strpos($resp_str,"<?xml"));
		$xml = simplexml_load_string($str);
		$res = json_decode(json_encode($xml),true);
		// echo $xml;

		if($nMaxReturn==0)
		{
			self::$session_id = substr( $resp_str,strpos($resp_str,"Set-Cookie: ")+12,45);
			//echo self::$session_id;
			if( substr( $resp_str,strpos($resp_str,"<ErrorNum>")+10,strpos($resp_str,"</ErrorNum>") -strpos($resp_str,"<ErrorNum>")-10) ==0)
			{
				self::$activeid = substr( $resp_str,strpos($resp_str,"<ActiveID>")+10,strpos($resp_str,"</ActiveID>") -strpos($resp_str,"<ActiveID>")-10);
			}

		}
		else
		{
			if( substr( $resp_str,strpos($resp_str,"<ErrorNum>")+10,strpos($resp_str,"</ErrorNum>") -strpos($resp_str,"<ErrorNum>")-10) ==0)
			{
				// echo "\r\n";
				// echo "操作成功";
			}
			else
			{
				// echo "\r\n";
				// echo substr( $resp_str,strpos($resp_str,"<ErrorNum>")+10,strpos($resp_str,"</ErrorNum>") -strpos($resp_str,"<ErrorNum>")-10);//处理返回值.
				self::$ReturnString = substr( $resp_str,strpos($resp_str,"<ErrorNum>")+10,strpos($resp_str,"</ErrorNum>") -strpos($resp_str,"<ErrorNum>")-10);
			} 
		}  
		fclose($fp);
		return $res;
	}
}
