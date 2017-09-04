<?php

/***************************************************************************
 * 

 * 
 **************************************************************************/

/**
 * 易宝支付
 * @author Administrator
 *
 */
class YeepayCommon
{
	/**
	 * 错误编码
	 * @var String
	 */
	public static $errCode = '';
	
	/**
	 * 错误信息
	 * @var String
	 */
	public static $errMsg = '';
	
	public static $merchantKey;
	
	// 支付请求函数
	public static function getReqHmacString(Array $bizArray)
	{
		$merchantKey = self::$merchantKey;
		$str = "";
		foreach($bizArray as $key => $value){
			$str = $str.$value;
		}
		eblog('易宝支付HmacStr',$str.";key:".$merchantKey,'yeepay');
		return self::HmacMd5($str, $merchantKey);
	}
	
	/**
	 * 
	 * @param array $bizArray
	 * @param String $url
	 * @return Ambigous <>|multitype:unknown |boolean
	 */
	public static function eposSale(Array $bizArray, String $url)
	{
		if (EBDEBUG) return true;
		$actionURL = $url;
		$merchantKey = self::$merchantKey;
	
		// 调用签名函数生成签名串
		$ReqHmacString = self::getReqHmacString($bizArray);

		// 组成请求串
		$actionHttpString = HttpClient::buildQueryString($bizArray)."&pr_NeedResponse=1"."&hmac=".$ReqHmacString;

		// 发起支付请求
		$pageContents = HttpClient::quickPost($actionURL,$actionHttpString);
		
		// 记录收到的提交结果
		$result = explode("\n",$pageContents);
		eblog('易宝支付请求串',$actionHttpString,'yeepay');
		eblog('易宝支付反馈消息',$result,'yeepay');
		for($index=0;$index<count($result);$index++)
		{
			$result[$index] = trim($result[$index]);
			if (strlen($result[$index]) == 0) {
				continue;
			}
			
			$aryReturn= explode("=",$result[$index]);
			$sKey= $aryReturn[0];
			$sValue	= $aryReturn[1];
			
			if($sKey=="r0_Cmd")
			{
				$r0_Cmd	= $sValue;
			}
			elseif($sKey=="r1_Code")
			{
				$r1_Code= $sValue;
			}
			elseif($sKey=="p1_MerId")
			{
				$p1_MerId= $sValue;
			}
			elseif($sKey=="r2_TrxId")
			{
				$r2_TrxId=$sValue;
			}
			elseif ($sKey=="r3_Amt")
			{
				$r3_Amt=$sValue;
			}
			elseif ($sKey=="r4_Cur")
			{
				$r4_Cur=$sValue;
			}
			elseif ($sKey=="r5_Pid")
			{
				$r5_Pid=$sValue;
			}
			elseif($sKey=="r6_Order")
			{
				$r6_Order=$sValue;
			}
			elseif ($sKey=="ro_BankOrderId")
			{
				$ro_BankOrderId=$sValue;
			}
			elseif ($sKey=="r7_BType")
			{
				$r7_BType=$sValue;
			}
			elseif ($sKey=="r8_MP")
			{
				$r8_MP=$sValue;
			}
			elseif ($sKey=="r_SystemsSign")
			{
				$r_SystemsSign=$sValue;
			}
			elseif ($sKey=="r_SystemsCustomerSign")
			{
				$r_SystemsCustomerSign=$sValue;
			}
			elseif ($sKey=="rp_PayDate")
			{
				$rp_PayDate=$sValue;
			}
			elseif ($sKey=="ru_Trxtime")
			{
				$ru_Trxtime=$sValue;
			}
			elseif ($sKey=="rw_RefundRequestID")
			{
				$rw_RefundRequestID=$sValue;
			}
			elseif ($sKey=="rx_CreateTime")
			{
				$rx_CreateTime=$sValue;
			}
			elseif ($sKey=="ry_FinshTime")
			{
				$ry_FinshTime=$sValue;
			}
			elseif ($sKey=="rz_RefundAmount")
			{
				$rz_RefundAmount=$sValue;
			}
			elseif ($sKey=="rb_PayStatus")
			{
				$rb_PayStatus=$sValue;
			}
			elseif ($sKey=="rc_RefundCount")
			{
				$rc_RefundCount=$sValue;
			}
			elseif ($sKey=="rd_RefundAmt")
			{
				$rd_RefundAmt=$sValue;
			}
			elseif($sKey =="errorMsg")
			{
				$errorMsg=$sValue;
			}
			elseif($sKey == "hmac")
			{
				$hmac = $sValue;
			} 
			else
			{
				return $result[$index];
			}
		}
	
		// 进行校验码检查 取得加密前的字符串
		$sbOld="";
		$sbOld = $sbOld.$r0_Cmd;
		$sbOld = $sbOld.$r1_Code;
		$sbOld = $sbOld.$p1_MerId;
		$sbOld = $sbOld.$r2_TrxId;
		$sbOld = $sbOld.$r3_Amt;
		$sbOld = $sbOld.$r4_Cur;
		$sbOld = $sbOld.$r5_Pid;
		$sbOld = $sbOld.$r6_Order;
		$sbOld = $sbOld.$ro_BankOrderId;
		$sbOld = $sbOld.$r7_BType;
		$sbOld = $sbOld.$r8_MP;
		$sbOld = $sbOld.$r_SystemsSign;
		$sbOld = $sbOld.$r_SystemsCustomerSign;
		$sbOld = $sbOld.$rp_PayDate;
		$sbOld = $sbOld.$ru_Trxtime;
		$sbOld = $sbOld.$rw_RefundRequestID;
		$sbOld = $sbOld.$rx_CreateTime;
		$sbOld = $sbOld.$ry_FinshTime;
		$sbOld = $sbOld.$rz_RefundAmount;
		$sbOld = $sbOld.$rb_PayStatus;
		$sbOld = $sbOld.$rc_RefundCount;
		$sbOld = $sbOld.$rd_RefundAmt;
		
		$sNewString = self::HmacMd5($sbOld,$merchantKey);
	
		// 校验码正确
		if($sNewString==$hmac)
		{
			if($r1_Code=="1") // 请求成功
			{
				return array(
						'orderNum'	=> $r6_Order,
						'code'		=> $r1_Code,
						'fee'		=> $r3_Amt,
						'tid'		=> $r2_TrxId,
						'bankOrderId'	=> $ro_BankOrderId,
						'status'	=> $rb_PayStatus,
				);
			}
			elseif($r1_Code=="50") // 订单不存在
			{
				self::$errCode = 50;
				self::$errMsg = '订单不存在!';
				return false;
			}
			elseif($r1_Code=="66") // 订单金额过小
			{
				self::$errCode = 66;
				self::$errMsg = '订单金额过小!';
				return false;
			}
			elseif($r1_Code=="30001")
			{
				self::$errCode = 30001;
				self::$errMsg = '支付卡密无效!';
				return false;
			}
			elseif($r1_Code=="3002")
			{
				self::$errCode = 3002;
				self::$errMsg = '创建订单异常!';
				return false;
			}
			elseif($r1_Code=="3003")
			{
				self::$errCode = 3003;
				self::$errMsg = '创建交易异常!';
				return false;
			}
			elseif($r1_Code=="3006")
			{
				self::$errCode = 3006;
				self::$errMsg = '银行错误信息：' . iconv('GBK', 'UTF-8', $errorMsg);
				return false;
			}
			elseif($r1_Code=="3008")
			{
				self::$errCode = 3008;
				self::$errMsg = '卡号规则不符合!';
				return false;
			}
			elseif($r1_Code=="81205")
			{
				self::$errCode = 81205;
				self::$errMsg = '验证码已无效!';
				return false;
			}
			elseif($r1_Code=="81206")
			{
				self::$errCode = 81206;
				self::$errMsg = '验证码校验失败!';
				return false;
			}
			elseif($r1_Code=="81207")
			{
				self::$errCode = 81207;
				self::$errMsg = '验证码不存在!';
				return false;
			}
			elseif($r1_Code=="81208")
			{
				self::$errCode = 81208;
				self::$errMsg = '交易失败，订单存在未完成的验证码，请稍候重试!';
				return false;
			}
			elseif($r1_Code=="80203")
			{
				self::$errCode = 80203;
				self::$errMsg = '单笔消费超限额!';
				return false;
			}
			elseif($r1_Code=="80204")
			{
				self::$errCode = 80204;
				self::$errMsg = '单日累计消费超限额!';
				return false;
			}
			elseif($r1_Code>="8036" && $r1_Code<="8043")
			{
				self::$errCode = $r1_Code;
				self::$errMsg = '输入的信用卡相关信息有误，请检查后重新支付!';
				return false;
			}
			elseif($r1_Code=="-100")
			{
				self::$errCode = -100;
				self::$errMsg = '未知错误!';
				return false;
			}
			else
			{
				self::$errCode = $r1_Code;
				self::$errMsg = '请检查后重新测试支付!';
				return false;
			}
		}
		else
		{
			self::$errCode = 0;
			self::$errMsg = '交易签名无效!';
			return false;
		}
	}
	
	// 校验支付结果
	public static function verifyCallback(Array $bizArray,$callBackHmac)
	{
		$merchantKey = self::$merchantKey;
		
		$callBackString = "";
		$callBackStringLog = "";
		foreach($bizArray as $key => $value)
		{
			$callBackString .= $value;
			$callBackStringLog .= $key . "=" . $value . "&";
		}
		
		$newLocalHmac = self::HmacMd5( $callBackString , $merchantKey );
		if ($newLocalHmac == $callBackHmac)
		{
			// self::logurl("callBack页面回调成功，交易信息正常!","回调参数串:".$callBackStringLog."LocalHmac(".$$newLocalHmac.") == ResponseHmac(".$callBackHmac.")!");
			return true;
		}
		else
		{
			// echo "交易信息被篡改！</br>newLocalHmac=".$newLocalHmac."</br>callBackHmac=".$callBackHmac;
			// self::logurl("callBack页面回调成功，但交易信息被篡改!","回调参数串:".$callBackStringLog."LocalHmac(".$newLocalHmac.") != ResponseHmac(".$callBackHmac.")!");
			return false;
		}
	}
	
	// 生成hmac的函数
	public static function HmacMd5($data,$key)
	{
		//$logdata = $data;
		//$logkey = $key;
	
		// 需要配置环境支持iconv，否则中文参数不能正常处理
		$key = iconv("GBK", "UTF-8", $key);
		$data = iconv("GBK", "UTF-8", $data);
		
		$b = 64; // byte length for md5
		
		if (strlen($key) > $b)
		{
			$key = pack("H*",md5($key));
		}
		
		$key=str_pad($key, $b, chr(0x00));
		$ipad=str_pad('', $b, chr(0x36));
		$opad=str_pad('', $b, chr(0x5c));
		$k_ipad=$key ^ $ipad ;
		$k_opad=$key ^ $opad;
	
		//$log_hmac = md5($k_opad . pack("H*",md5($k_ipad . $data)));
		//self::loghmac($logdata,$logkey,$log_hmac);
		return md5($k_opad . pack("H*",md5($k_ipad . $data)));
	}
	
	// 记录请求URL到日志
	public static function logurl($title,$content)
	{
		/*
		$logName = C('YEEPAY_LOG_NAME');
		$james=fopen($logName,"a+");
		date_default_timezone_set(PRC);
		fwrite($james,"\r\n".date("Y-m-d H:i:s,A")." [".$title."]   ".$content."\n");
		fclose($james);
		*/
	}
	
	// 记录生成hmac时的日志信息
	public static function loghmac($str,$merchantKey,$hmac)
	{
		/*
		$logName = C('YEEPAY_LOG_NAME');
		$merchantKey = self::$merchantKey;
		$james=fopen($logName,"a+");
		date_default_timezone_set(PRC);
		fwrite($james,"\r\n".date("Y-m-d H:i:s,A")."  [构成签名的参数:]".$str."  [商户密钥:]".$merchantKey."   [本地HMAC:]".$hmac);
		fclose($james);
		*/
	}
}


/*
 * @Description 易宝支付EPOS范例
* @V3.0
* @Author  wenhua.cheng
*/
class HttpClient
{
	// Request vars
	var $host;
	var $port;
	var $path;
	var $method;
	var $postdata = '';
	var $cookies = array();
	var $referer;
	var $accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
	var $accept_encoding = 'gzip';
	var $accept_language = 'en-us';
	var $user_agent = 'Incutio HttpClient v0.9';
	
	// Options
	var $timeout = 20;
	var $use_gzip = true;
	var $persist_cookies = true;
	 
	var $persist_referers = true;
	var $debug = false;
	var $handle_redirects = true;
	var $max_redirects = 5;
	var $headers_only = false;
	// Basic authorization variables
	var $username;
	var $password;
	// Response vars
	var $status;
	var $headers = array();
	var $content = '';
	var $errormsg;
	// Tracker variables
	var $redirect_count = 0;
	var $cookie_host = '';
	
	function HttpClient($host, $port=80)
	{
		$this->host = $host;
		$this->port = $port;
	}
	
	function get($path, $data = false) {
		$this->path = $path;
		$this->method = 'GET';
		if ($data) {
			$this->path .= '?'.$this->buildQueryString($data);
		}
		return $this->doRequest();
	}
	
	function post($path, $data) {
		$this->path = $path;
		$this->method = 'POST';
		$this->postdata = $this->buildQueryString($data);
		return $this->doRequest();
	}
	
	function buildQueryString($data)
	{
		$querystring = '';
		if (is_array($data)) {
			// Change data in to postable data
			foreach ($data as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $val2) {
						$querystring .= urlencode($key).'='.urlencode($val2).'&';
					}
				} else {
					$querystring .= urlencode($key).'='.urlencode($val).'&';
				}
			}
			$querystring = substr($querystring, 0, -1);
		} else {
			$querystring = $data;
		}
		return $querystring;
	}
	
	function doRequest()
	{
		// Performs the actual HTTP request, returning true or false depending on outcome
		if (!$fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			// Set error message
			switch($errno) {
				case -3:
					$this->errormsg = 'Socket creation failed (-3)';
				case -4:
					$this->errormsg = 'DNS lookup failure (-4)';
				case -5:
					$this->errormsg = 'Connection refused or timed out (-5)';
				default:
					$this->errormsg = 'Connection failed ('.$errno.')';
					$this->errormsg .= ' '.$errstr;
					$this->debug($this->errormsg);
			}
			return false;
		}
		socket_set_timeout($fp, $this->timeout);
		$request = $this->buildRequest();
		eblog('易宝支付请求',$request,'yeepay');
		// $this->debug('Request', $request);
		fwrite($fp, $request);
		// Reset all the variables that should not persist between requests
		$this->headers = array();
		$this->content = '';
		$this->errormsg = '';
		// Set a couple of flags
		$inHeaders = true;
		$atStart = true;
		// Now start reading back the response
		while (!feof($fp)) {
			$line = fgets($fp, 4096);
			if ($atStart) {
				// Deal with first line of returned data
				$atStart = false;
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$this->errormsg = "Status code line invalid: ".htmlentities($line);
					$this->debug($this->errormsg);
					return false;
				}
				$http_version = $m[1];
				$this->status = $m[2];
				$status_string = $m[3];
				$this->debug(trim($line));
				continue;
			}
			if ($inHeaders) {
				if (trim($line) == '') {
					$inHeaders = false;
					$this->debug('Received Headers', $this->headers);
					if ($this->headers_only) {
						break;
					}
					continue;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				// Deal with the possibility of multiple headers of same name
				if (isset($this->headers[$key])) {
					if (is_array($this->headers[$key])) {
						$this->headers[$key][] = $val;
					} else {
						$this->headers[$key] = array($this->headers[$key], $val);
					}
				} else {
					$this->headers[$key] = $val;
				}
				continue;
			}
			// We're not in the headers, so append the line to the contents
			$this->content .= $line;
		}
		fclose($fp);
		// If data is compressed, uncompress it
		if (isset($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
			$this->debug('Content is gzip encoded, unzipping it');
			$this->content = substr($this->content, 10);
			$this->content = gzinflate($this->content);
		}
		// If $persist_cookies, deal with any cookies
		if ($this->persist_cookies && isset($this->headers['set-cookie']) && $this->host == $this->cookie_host) {
			$cookies = $this->headers['set-cookie'];
			if (!is_array($cookies)) {
				$cookies = array($cookies);
			}
			foreach ($cookies as $cookie) {
				if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
					$this->cookies[$m[1]] = $m[2];
				}
			}
			// Record domain of cookies for security reasons
			$this->cookie_host = $this->host;
		}
		// If $persist_referers, set the referer ready for the next request
		if ($this->persist_referers) {
			$this->debug('Persisting referer: '.$this->getRequestURL());
			$this->referer = $this->getRequestURL();
		}
		// Finally, if handle_redirects and a redirect is sent, do that
		if ($this->handle_redirects) {
			if (++$this->redirect_count >= $this->max_redirects) {
				$this->errormsg = 'Number of redirects exceeded maximum ('.$this->max_redirects.')';
				$this->debug($this->errormsg);
				$this->redirect_count = 0;
				return false;
			}
			$location = isset($this->headers['location']) ? $this->headers['location'] : '';
			$uri = isset($this->headers['uri']) ? $this->headers['uri'] : '';
			if ($location || $uri) {
				$url = parse_url($location.$uri);
				// This will FAIL if redirect is to a different site
				return $this->get($url['path']);
			}
		}
		return true;
	}
	
	function buildRequest() {
		$headers = array();
		$headers[] = "{$this->method} {$this->path} HTTP/1.0";
		$headers[] = "Host: {$this->host}";
		$headers[] = "User-Agent: {$this->user_agent}";
		$headers[] = "Accept: {$this->accept}";
		if ($this->use_gzip) {
			$headers[] = "Accept-encoding: {$this->accept_encoding}";
		}
		$headers[] = "Accept-language: {$this->accept_language}";
		if ($this->referer) {
			$headers[] = "Referer: {$this->referer}";
		}
		// Cookies
		if ($this->cookies) {
			$cookie = 'Cookie: ';
			foreach ($this->cookies as $key => $value) {
				$cookie .= "$key=$value; ";
			}
			$headers[] = $cookie;
		}
		// Basic authentication
		if ($this->username && $this->password) {
			$headers[] = 'Authorization: BASIC '.base64_encode($this->username.':'.$this->password);
		}
		// If this is a POST, set the content type and length
		if ($this->postdata) {
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$headers[] = 'Content-Length: '.strlen($this->postdata);
		}
		$request = implode("\r\n", $headers)."\r\n\r\n".$this->postdata;
		return $request;
	}
	function getStatus() {
		return $this->status;
	}
	function getContent() {
		return $this->content;
	}
	function getHeaders() {
		return $this->headers;
	}
	function getHeader($header) {
		$header = strtolower($header);
		if (isset($this->headers[$header])) {
			return $this->headers[$header];
		} else {
			return false;
		}
	}
	function getError() {
		return $this->errormsg;
	}
	function getCookies() {
		return $this->cookies;
	}
	function getRequestURL() {
		$url = 'http://'.$this->host;
		if ($this->port != 80) {
			$url .= ':'.$this->port;
		}
		$url .= $this->path;
		return $url;
	}
	// Setter methods
	function setUserAgent($string) {
		$this->user_agent = $string;
	}
	function setAuthorization($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	function setCookies($array) {
		$this->cookies = $array;
	}
	// Option setting methods
	function useGzip($boolean) {
		$this->use_gzip = $boolean;
	}
	function setPersistCookies($boolean) {
		$this->persist_cookies = $boolean;
	}
	function setPersistReferers($boolean) {
		$this->persist_referers = $boolean;
	}
	function setHandleRedirects($boolean) {
		$this->handle_redirects = $boolean;
	}
	function setMaxRedirects($num) {
		$this->max_redirects = $num;
	}
	function setHeadersOnly($boolean) {
		$this->headers_only = $boolean;
	}
	function setDebug($boolean) {
		$this->debug = $boolean;
	}
	// "Quick" static methods
	function quickGet($url) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset($bits['port']) ? $bits['port'] : 80;
		$path = isset($bits['path']) ? $bits['path'] : '/';
		if (isset($bits['query'])) {
			$path .= '?'.$bits['query'];
		}
		$client = new HttpClient($host, $port);
		if (!$client->get($path)) {
			return false;
		} else {
			return $client->getContent();
		}
	}
	function quickPost($url, $data) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset($bits['port']) ? $bits['port'] : 80;
		$path = isset($bits['path']) ? $bits['path'] : '/';
		$client = new HttpClient($host, $port);
		if (!$client->post($path, $data)) {
			return false;
		} else {
			return $client->getContent();
		}
	}
	function debug($msg, $object = false) {
		if ($this->debug) {
			print '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>HttpClient Debug:</strong> '.$msg;
			if ($object) {
				ob_start();
				print_r($object);
				$content = htmlentities(ob_get_contents());
				ob_end_clean();
				print '<pre>'.$content.'</pre>';
			}
			print '</div>';
		}
	}
}
