<?php



class SmsApi {
    public function __construct() {
        include("sms_config.php");
        $this->config = $sms_config;
    }

    /**
     * 发送短信
     *
     * @param string $mobile 手机号码
     * @param string $content 短信内容 
     * @param string $extno 扩展码，可选
     */
    public function sendSMS($mobile, $content,  $msgtype = 1) {
        //接口参数        
        $arg = 'username='.$this->config['api_username'].'&password='.$this->config['api_password'];
        $arg .= '&to='.$mobile.'&text='.urlencode(mb_convert_encoding($content,'gb2312','utf-8' ));
        $arg .= '&subid=&msgtype='.$msgtype ;
        $url = $this->config['api_send_url'].'?'.$arg;
//        eblog("短信发送 - ",$url,'sms_'.date("Ymd"));
        $result = $this->httpGet($url);
        return $result;
    }
    

    /**
     * 处理返回值
     *
     */
    public function execResult($result) {
        $result = preg_split("/[,\r\n]/", $result);
        return $result;
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
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
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

    //魔术获取
    public function __get($name) {
        return $this->$name;
    }

    //魔术设置
    public function __set($name, $value) {
        $this->$name = $value;
    }
}

?>