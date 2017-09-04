<?php
class baseAction extends Action
{
	//公共参数
	public $common = array();
	
	//model
	public $model = array();	
	
	//usid
	public $usid;
	public $usinfo = array();
	
	public function _initialize()
	{
		$this->setUsid();
		$this->setUserinfo();
        $this->checkAccessToken();
		// dump($this->usid);
		// dump($this->usinfo);
	}
	
	public function setCommon()
	{
		$this->vaild_params('is_empty',I('machine_code'),'请传入机器码');
		$this->common['macine_code'] = I('machine_code');
		$this->common['machine_info'] = I('machine_info');
		$this->common['version'] = I('version');
		$this->common['os'] = I('os');
		$this->common['sign'] = I('sign');
		$this->common['timestamp'] = I('timestamp');
	}
	
	public function setUsid()
	{
		$this->usid = intval(I('usid'));
	}
	
	public function setUserinfo()
	{
		if (!$this->usid) return false;
		$this->usinfo = $this->loadModel('userSaler')->getInfoByid($this->usid);		
	}
	
    public function checkAccessToken()
    {
        if (TOKENDEBUG === true) return true;
        $skips = array('user/register', 'user/login', 'user/resetpwd', 'user/regsms', 'user/resetpwdsms', 'cmbc/*', 'cmup/*', 'ezfpay/*', 'processMsg/*', 'setting/*', 'sumapay/*', 'upmp/*', 'wxpay/*');
        // if (false !== stripos(strtolower(MODULE_NAME).'/*',MODULE_NAME)) return true;
        $action = strtolower(MODULE_NAME . '/' . ACTION_NAME);
        if (in_array($action, $skips) || in_array(strtolower(MODULE_NAME) . '/*', $skips)) return true;
        $this->vaild_params('is_empty', $this->usid, '请先登录客户端');
        $access_token = I('access_token');
        $this->vaild_params('is_empty', $access_token, '请先登录客户端');
        $key = 'access_token_usid_' . $this->usid;
        $this->vaild_params('eq', array($access_token, S($key)), '登录失效，请重新登录');
    }
	
	/*
	*	参数验证
	*	@param		callback		$call			调用函数名
	*	@param		array			$params	调用函数的参数名
	*	@param		string			$msg			提示信息
	*	@param		bool			$rule			验证规则
	*/
	public function vaild_params($call,$params,$msg='',$rule = true)
	{
		if (!is_callable($call))
		{
			throw new Exception($call." can not callable!");
		}
		$params = !is_array($params)?array($params):$params;
		if (call_user_func_array($call,$params) == $rule)
		{
			return true;
		}
		else
		{
			$this->response(PARAMS_ERROR,$msg);
		}
	}
	
	/*
	*	接口响应输出
	*	@param			int		$result_code	响应代码
	*	@param			string	$result_msg		接口响应信息
	*	@param			array	$data					接口数据
	*/
	public function response($result_code,$result_msg,$data = array(),$link='')
	{
		$response = array();
		$response['result']['code'] = $result_code;
		$response['result']['msg'] = $result_msg;
		$response['link'] = $link;
		array_values_to_string($data);
		$response['data'] = (empty($data))?((object) null):$data;
		echo json_encode($response);
		exit;
	}
	
	/**
	 * json 中文不转码
	 */
	function json_encode_ex($value) {
	    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	        
	        $str = json_encode($value);
	        $str = preg_replace_callback(
	            "#\\\u([0-9a-f]{4})#i",
	            function ($matchs) {
	                return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
	            },
	            $str
	        );
	        return $str;
	    } else {
	        return json_encode($value, JSON_UNESCAPED_UNICODE);
	    }
	}
	
	/*
	*	api输出
	*	@param			array		$data		要显示的接口数据或根据数据判断接口显示的结构体
	*	@param			bool		$show	是否显示数据结构体，如果false只显示result部分，不显示data部分
	*/
	public function apiOut($data = array(),$show = true)
	{
		if (!$data)
		{
			$this->response(INTERNAL_ERROR,'查无数据');
		}
		else
		{
			if ($show)
			{
				if (array_key_exists('list',$data) && empty($data['list']))
				{
					$data = array();
				}
				$this->response(REQUEST_SUCCESS,'请求成功',$data);
			}
			else
			{
				$this->response(REQUEST_SUCCESS,'请求成功');
			}
		}
	}

	/*
	*	创建单号
	*/
	public function createSn($type = '')
	{
		return $type.substr(date("YmdHis"), -12).rand(100000,999999);
	}
	
	public function loadModel($model)
	{
		$this->model[$model] = D($model);
		return $this->model[$model];
	}
	
	/*
	*	获取指定的请求参数
	*	@param			array			$field		需要获取的参数的字段名
	*	@param			string			$method		获取类型，可取值_request,_get,_post
	*/
	public function get_params($field,$method = '_request')
	{
		if (!in_array($method,array('_request','_get','_post')))
		{
			throw new Exception('get_params invaild params!');
		}
		$request = $this->$method();
		$data = array();
		foreach($field as $k)
		{
			$data[$k] = isset($request[$k])?htmlspecialchars($request[$k]):'';
		}
		return $data;
	}
	
	/**
	 * [curldata 拼接字符串]
	 *
	 * @param array $params
	 * @param private 私密密钥
	 * @return string url格式化的字符串
	 */
	public function curldata($params = array(),$private='',$type = false) {
	
	    //引入公共的参数
	    if($this->version){
	        $params['version'] = $this->version;
	    }
	    if($this->MER_ID){
	        $params['mer_id'] = $this->MER_ID;
	    }
	    ksort($params);//对签名参数据排序
	    //生成签名
	    $params['SIGN'] = $this->getSignMsg($params,$private);
	    $r = $type==true?$params:$this->datatourl($params);
	    return $r;
	}
	
	/**
	 * checkSignMsg 回调签名验证
	 *
	 * @param array $params
	 * @param public 公共密钥
	 * @return boolean
	 */
	public function checkSignMsg($params = array(),$public='') {
	    if(empty($params)){ return false;}
	    ksort($params); // 对签名参数据排序
	    $sign = isset($params['SIGN']) ? $params['SIGN'] : '';
	    $params_str = $this->datatourl($params,true);
	    switch ($this->sign_type) {
	        case 'RSA' :
	            $cert = file_get_contents (C($public));
	            $pubkeyid = openssl_pkey_get_public ( $cert );
	            $ok = openssl_verify ( $params_str, base64_decode ($sign), $cert, OPENSSL_ALGO_SHA1 );
	            $return = $ok == 1 ? true : false;
	            openssl_free_key ( $pubkeyid );
	            break;
	        case 'MD5' :
	        default :
	            $return = (strtolower(md5($params_str)) == strtolower ($sign)) ? true : false;
	            break;
	    }
	    return $return;
	}
	
	
	/**
	 * 数组转陈url
	 */
	public function datatourl($params = array(),$type = false,$md5= false,$capital = true,$ksort = false) {
	    $str = '';
	    if($ksort){
	        ksort($params);
	    }
	    //需要删除固定的字段
	    if($type)
	    {
	      unset($params['signature']);unset($params['sign']);unset($params['paychannelcode']);unset($params['SIGN']);unset($params['sign_type']);unset($params['sign_version']);
	    }
	    // print_r($params);exit;
	    foreach($params as $key => $val ) {
	        if($capital){
	            if (!is_null($val) && $val!='') {
	                $str .=  strtoupper($key) .'=' . ($type==false?urlencode(urlencode(trim($val))):trim($val)).'&';
	            } 
	        }else{
	            if (!is_null($val) && $val!='') {
	                $str .=  $key .'=' . ($type==false?urlencode(urlencode(trim($val))):trim($val)).'&';
	            }
	        }
	        
	    }
	    //$strs = stripcslashes($str);
	    return $md5? md5(substr($str, 0 ,-1)):substr($str, 0 ,-1) ;
	}
	
	/**
	 * getSignMsg 计算签名
	 *
	 *
	 * @param array $params
	 *        	计算签名数据
	 * @param string $sign_type
	 *        	签名类型--默认是rsa
	 * @param private 私密密钥
	 * @return string $signMsg 返回密文
	 */
	public function getSignMsg($params = array(),$private='',$md5=false) {
	    $signMsg = "";
	    $params_str = $this->datatourl($params,true,$md5);//生成http地址栏，不需要urlencode	    
	    switch ($this->sign_type) {
	        case 'RSA' :
	            $priv_key = file_get_contents(C($private));
	           // echo $priv_key;exit;
	            $pkeyid = openssl_pkey_get_private ($priv_key);
	            openssl_sign ($params_str, $signMsg, $pkeyid, OPENSSL_ALGO_SHA1 );	            
	            openssl_free_key ( $pkeyid );	            
	            $signMsg = base64_encode ( $signMsg );
	            break;
	        case 'MD5' :
	        default :
	            $signMsg = strtolower(md5($params_str));
	            break;
	    }
	    return $signMsg;
	}
	

	
	/**
	 * [createcurl_data 拼接模拟提交数据]
	 *
	 * @param array $pay_params
	 * @return string result
	 */
	public function httpPost($url, $datastring) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url );
	    curl_setopt($ch, CURLOPT_POST, 1 );
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true );
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $datastring );
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	        'Content-Type: application/json',
	        'Content-Length: ' . strlen($datastring))
	    );
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
	    $data = curl_exec($ch);
	    curl_close( $ch );
	    return $data;
	}
    /**
     * post函数
     * @param $url
     * @param array $data
     * @return bool|mixed
     */
    function Curl_Post($url, $data = array())
    {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 定义超时3秒钟
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // POST参数
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        eblog('测试-ORDER_ID:',$data['ORDER_ID'],'alipay');
        // 执行并获取url地址的内容
        $output = curl_exec($ch);
        $errorCode = curl_errno($ch);
        // 释放curl句柄
        curl_close($ch);
        if (0 !== $errorCode) {
            return false;
        }
        return $output;
    }

	protected function buildForm($url, $postData, $charset='utf-8', $method='post') {
	    header("Content-type:json/html; charset={$charset}");
	    $htmlArr = array();
	    $htmlArr[] = '<form id="applyForm" target="_blank" name="form1" action="' . $url . '" method="' . $method . '">';
	    foreach ($postData as $key => $val) {
	        $htmlArr[] = '    <input type="hidden" name="' . $key . '" value="' . $val . '">';
	    }
	    $htmlArr[] ="<input type='submit'/>";
	    $htmlArr[] = '</form>';
	
	   // $htmlArr[] = '<script>document.forms["form1"].submit();</script>';
	
	    return implode('', $htmlArr);
	}
	/**
	 * curl方法
	 * @param $url
	 * @return mixed
	 */
	function httpGet($url, $postData='') {
	    $ch = curl_init();
	    //设置超时
	    curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);	    
	    if(!empty($postData)) {
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
	        curl_setopt($ch, CURLOPT_POST, 1);
	    }
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    //运行curl，结果以jason形式返回
	    $res = curl_exec($ch);
	    curl_close($ch);
	
	    return $res;
	
	}

	//获取用户信息
	public function getDomain() {
	    
	    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
	}
	public function addpaylog($data=array(),$member_info=array()){
	    $data['member_id'] = $member_info['usid'];
	    $data['member_name'] = $member_info['us_name'];
	    $data['status'] = empty($data['status'])?'-2':$data['status'];
	    $data['add_time'] = time();
	    M('payLog')->add($data);
	}
	public function addbacklog($url='',$post=array(),$return=array()){
	    $data = array();
	    $data['return_url'] = $url;
	    $data['return_msg'] = $post;
	    $data['rsa_decrypt_msg'] = serialize($return);
	    $data['add_time'] = time();
	    M('backLog')->add($data);
	    
	}

	//AES加密
    public static function AesEncrypt($plaintext,$key = null)
    {
        $plaintext = trim($plaintext);
        if ($plaintext == '') return '';
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        //PKCS5Padding
        $str = extension_loaded('mbstring') ? mb_strlen($plaintext,'8bit') : strlen($plaintext);
        $padding = $size -$str % $size;
        // 添加Padding
        $plaintext .= str_repeat(chr($padding), $padding);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $key=self::substr($key, 0, mcrypt_enc_get_key_size($module));
        $iv = str_repeat("\0", $size);      //此处蛋碎一地啊，java里面的16个空数组对应的是\0.由于不懂java，这个地方百度了很久，后来是请教主管才搞定的。
        /* Intialize encryption */
        mcrypt_generic_init($module, $key, $iv);
        /* Encrypt data */
        $encrypted = mcrypt_generic($module, $plaintext);
        /* Terminate encryption handler */
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
    }

//    private static function strlen($string){
//        return extension_loaded('mbstring') ? mb_strlen($string,'8bit') : strlen($string);
//    }

    private static function substr($string,$start,$length){
        return extension_loaded('mbstring') ? mb_substr($string,$start,$length,'8bit') : substr($string,$start,$length);
    }
    //AES解密
    public static function AesDecrypt($encrypted, $key = null){
        if ($encrypted == '') return '';
        $ciphertext_dec = base64_decode($encrypted);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $key=self::substr($key, 0, mcrypt_enc_get_key_size($module));
        $iv = str_repeat("\0", 18);    //解密的初始化向量要和加密时一样。
        /* Initialize encryption module for decryption */
        mcrypt_generic_init($module, $key, $iv);
        /* Decrypt encrypted string */
        $decrypted = mdecrypt_generic($module, $ciphertext_dec);
        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        $a = rtrim($decrypted,"\0");
        return rtrim($decrypted,"\0");
    }
}
