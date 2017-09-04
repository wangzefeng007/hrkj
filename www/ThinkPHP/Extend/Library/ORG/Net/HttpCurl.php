<?php
class HttpCurl {
	var $method;
	var $post;
	var $errno;
	var $errstr;
	var $data;

    function __construct() {
		$this->method = 'GET';
		$this->post = '';
		$this->errno = 0;
		$this->errstr = '';
    }
    
	function post($url, $data = array(),$timeout=25,$headers=array()){		
		$this->method = 'POST';
		if(is_array($data)){
			$this->post= http_build_query($data);
		}else{
			$this->post=$data;
		}
		return $this->request($url,$timeout,$headers);
	}

	function get($url,$headers,$data=''){
		if(is_array($data))$urlstr=http_build_query($data);
		$this->method = 'GET';
		return $this->request($url.'?'.$urlstr,'',$headers);
	}
	
	function request($url,$timeout=30,$headers){
		$ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
		if(!empty($this->post)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
			curl_setopt($ch, CURLOPT_POST, 1);
		}
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $this->data = curl_exec($ch);
		$this->errno = curl_errno($ch);
		$this->errstr = curl_error($ch);
		curl_close($ch);
		
		return $this->errno==0;
	}

}