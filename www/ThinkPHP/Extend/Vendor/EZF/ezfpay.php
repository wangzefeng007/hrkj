<?php
/**
 * @name	ezfpay支付接口类
 * @desc	
 */
 	
class send {
	var $parameter;       //参数数组
	var $signkey;
	var $url;

	//构造函数
	function send($parameter) {
		$this->parameter = $parameter;
		
		//支付密钥(必填): 需在支付平台进行设置,可登录商户管理系统进行维护,用于上送商户支付及下传支付结果加密
		$this->signkey = $parameter['signkey'];
		
		//支付请求URL(必填)
		$this->url = $parameter['payUrl'];
	}

	//APP支付签名
	function getSign() {
		$sign_src = "version=".$this->parameter['version']."&charset=".$this->parameter['charset']
			."&signMethod=".$this->parameter['signMethod']."&transType=".$this->parameter['transType']
			."&merId=".$this->parameter['merId']."&backEndUrl=".$this->parameter['backEndUrl']
			."&frontEndUrl=".$this->parameter['frontEndUrl']."&orderTime=".$this->parameter['orderTime']
			."&orderNumber=".$this->parameter['orderNumber']."&orderAmount=".$this->parameter['orderAmount']
			."&orderCurrency=".$this->parameter['orderCurrency']."&defaultBankNumber=".$this->parameter['defaultBankNumber']
			."&merReserved1=".$this->parameter['merReserved1']."&subMerid=".$this->parameter['subMerid']
			."&".md5($this->signkey);

		$sign = $this->sign($sign_src);
		return $sign;
	}

	//wap支付签名(带子账号)
	function getSignWapSub() {
		$sign_src = "version=".$this->parameter['version']."&charset=".$this->parameter['charset']
			."&signMethod=".$this->parameter['signMethod']."&payType=".$this->parameter['payType']
			."&transType=".$this->parameter['transType']."&merId=".$this->parameter['merId']
			."&backEndUrl=".$this->parameter['backEndUrl']."&frontEndUrl=".$this->parameter['frontEndUrl']
			."&orderTime=".$this->parameter['orderTime']."&orderNumber=".$this->parameter['orderNumber']
			."&orderAmount=".$this->parameter['orderAmount']."&orderCurrency=".$this->parameter['orderCurrency']
			."&defaultBankNumber=".$this->parameter['defaultBankNumber']."&customerIp=".$this->parameter['customerIp']
			."&merReserved1=".$this->parameter['merReserved1']."&merReserved2=".$this->parameter['merReserved2']
			."&merReserved3=".$this->parameter['merReserved3']."&merSiteIP=".$this->parameter['merSiteIP']
			."&gateWay=".$this->parameter['gateWay']."&terType=".$this->parameter['terType']."&agentAmount=".$this->parameter['agentAmount']
		    ."&".md5($this->signkey);

		$sign = $this->sign($sign_src);
		return $sign;
	}

	function sign($src) {
		return md5($src);
	}

	/* //wap支付签名(无子账号)
	function getSignWap() {
		$sign_src = "version=".$this->parameter['version']."&charset=".$this->parameter['charset']
			."&signMethod=".$this->parameter['signMethod']."&payType=".$this->parameter['payType']
			."&transType=".$this->parameter['transType']."&merId=".$this->parameter['merId']
			."&backEndUrl=".$this->parameter['backEndUrl']."&frontEndUrl=".$this->parameter['frontEndUrl']
			."&orderTime=".$this->parameter['orderTime']."&orderNumber=".$this->parameter['orderNumber']
			."&orderAmount=".$this->parameter['orderAmount']."&orderCurrency=".$this->parameter['orderCurrency']
			."&defaultBankNumber=".$this->parameter['defaultBankNumber']."&customerIp=".$this->parameter['customerIp']
			."&merReserved1=".$this->parameter['merReserved1']."&merReserved2=".$this->parameter['merReserved2']
			."&merReserved3=".$this->parameter['merReserved3']."&merSiteIP=".$this->parameter['merSiteIP']
            ."&gateWay=".$this->parameter['gateWay']."&".md5($this->signkey);

		$sign = $this->sign($sign_src);
		return $sign;
	}

	function sign($src) {
		return md5($src);
	} */


}

//验证返回数据的正确性
function verify($result, $signkey){
	$str = substr($result, 0, -38);
	$str = $str."&".md5($signkey);
	$qid = "";
	$respCode = "";
	$respMsg = "";
	$arr = explode("&",$result); 
	$count = count($arr);
	for($i = 0;$i < $count; $i++){
		$resu = explode("=",$arr[$i]); 
		if($resu[0] == "qid"){
			$qid = $resu[1];
		}
		if($resu[0] == "respCode"){
			$respCode = $resu[1];
		}
		if($resu[0] == "respMsg"){
			$respMsg = $resu[1];
		}
	}
	// echo "{\"respCode\":\"".$respCode."\",\"qid\":\"".$qid."\",\"respMsg\":\"".$respMsg."\"}";

	if(md5($str) == (substr($result, -32)) ){
		// 签名校验通过
		$arr = array(
			'respCode' => $respCode,
			'qid' => $qid,
			'respMsg' => urldecode($respMsg),
		);
		return $arr;
	}
	return false;
}

/**
 * 后台交易 HttpClient通信
 * @param unknown_type $params
 * @param unknown_type $url
 * @return mixed
 */
function sendHttpRequest($params, $url) {
	$opts = getRequestParamString ( $params );
	
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
			'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
	) );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );
	
	/**
	 * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
	 */
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	
	// 运行cURL，请求网页
	$html = curl_exec ( $ch );
	// close cURL resource, and free up system resources
	curl_close ( $ch );
	return $html;
}

/**
 * 组装报文
 *
 * @param unknown_type $params        	
 * @return string
 */
function getRequestParamString($params) {
	$params_str = '';
	foreach ( $params as $key => $value ) {
		$params_str .= ($key . '=' . (!isset ( $value ) ? '' : urlencode( $value )) . '&');
	}
	return substr ( $params_str, 0, strlen ( $params_str ) - 1 );
}

?>
