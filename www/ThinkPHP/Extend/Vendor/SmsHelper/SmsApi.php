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
    public function sendSMS($mobile, $content,  $extno = '') {
        //接口参数
        $postArr = array(
            'sn' => $this->config['api_sn'],
            'pwd' => strtoupper(MD5($this->config['api_sn'].$this->config['api_pwd'])),
            'content' => '【'.$this->config['api_com_name'].'】'.$content,
            'mobile' => $mobile, 
            'extno' => $extno
        ); 
        $result = $this->curlPost($this->config['api_send_url'], $postArr);
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
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    private function curlPost($url, $postFields) {
        $postFields = http_build_query($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields); 
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
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