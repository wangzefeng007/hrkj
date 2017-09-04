<?php
/**
 * 微信H5支付帮助类
 * @author roothai
 *
 */ 
class WxPayNewHelper {
	private $parameters; // cft 参数
	private $unifiedorder_api='https://api.mch.weixin.qq.com/pay/unifiedorder';//统一下单接口
	private $orderquery_api='https://api.mch.weixin.qq.com/pay/orderquery';//查询订单接口
	private $closeorder_api='https://api.mch.weixin.qq.com/pay/closeorder';
	private $refund_api='https://api.mch.weixin.qq.com/secapi/pay/refund';
	private $refundquery_api='https://api.mch.weixin.qq.com/pay/refundquery';
	private $downloadbill_api='https://api.mch.weixin.qq.com/pay/downloadbill';
	private $mktcashredadvance_api='https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
	public function __construct() {
	}
	/**
	 * 设置参数
	 * @param unknown $parameter
	 * @param unknown $parameterValue
	 */
	public function setParameter($parameter, $parameterValue) {
		$this->parameters [trimString ( $parameter )] = trimString ( $parameterValue );
	}
	/**
	 * 
	 * @param unknown $parameter
	 */
	public function getParameter($parameter) {
		return $this->parameters [$parameter];
	}
	/**
	 * 服务商统一下单接口
	 * @throws SDKRuntimeException
	 */
	public function service_unifiedorder(){
	    try{
	        if(!self::getParameter('appid')||!self::getParameter('mch_id')||!self::getParameter('sub_mch_id')){
	            throw new SDKRuntimeException ( "配置文件为空！");
	        }
	        $nativeObj ["appid"] = self::getParameter('appid');
	        $nativeObj ["sub_appid"] = self::getParameter('sub_appid');
	        $nativeObj ["mch_id"] = self::getParameter('mch_id');
	        $nativeObj ["sub_mch_id"] = self::getParameter('sub_mch_id');
	        // 			$nativeObj ["device_info"] = C('NewWeiXin.DEVICEINFO');
	        $nativeObj ["nonce_str"] = create_noncestr();
	        $nativeObj ["body"] = $this->parameters['body'];
	        // 			$nativeObj ["detail"] = $this->parameters['detail'];
	        // 			$nativeObj ["attach"] = $this->parameters['attach'];
	        $nativeObj ["out_trade_no"] = $this->parameters['out_trade_no'];
	        //按照分来计算
	        // 			$nativeObj ["fee_type"] =intval($this->parameters['fee_type']);
	        $nativeObj ["total_fee"] = intval($this->parameters['total_fee']);
	        $nativeObj ["spbill_create_ip"] = get_client_ip();
	        // 			$nativeObj ["time_start"] = $this->parameters['time_start'];
	        // 			$nativeObj ["time_expire"] = $this->parameters['time_expire'];
	        // 			$nativeObj ["goods_tag"] = $this->parameters['goods_tag'];
	        $nativeObj ["notify_url"] = self::getParameter('notify_url');
	        $nativeObj ["trade_type"] = 'JSAPI';
	        // 			$nativeObj ["product_id"] = $this->parameters['product_id'];
	        $nativeObj ["openid"] = $this->parameters['openid'];
	        $nativeObj ["sub_openid"] = $this->parameters['sub_openid'];
	        $nativeObj ["sign"] = $this->get_biz_sign($nativeObj);
	        ksort($nativeObj); 
			dump($nativeObj);
	        $nativeStr='<xml>'.xml2string('', $nativeObj).'</xml>';
	        logger($nativeStr);
			Vendor('HttpCurl.HttpCurl');  
	        $http=new HttpCurl();
	        //$http->post($this->unifiedorder_api, $nativeStr);
	       	//print_r($http);
	        if ($http->post($this->unifiedorder_api, $nativeStr)) {
	            //返回预支付数组
	            return $http->data;//(array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	        } else {
	            throw new SDKRuntimeException ( $http->data."预订单无响应！");
	        }
	    }catch(SDKRuntimeException $e){
	        exit( $e->errorMessage () );
	    }
	}
	/**
	 * 统一下单
	 */
	public function unifiedorder(){
		try{
			if(!self::getParameter('appid')||!self::getParameter('mch_id')||!self::getParameter('notify_url')){
				throw new SDKRuntimeException ( "配置文件为空！");
			}
			$nativeObj ["appid"] = self::getParameter('appid');
			$nativeObj ["mch_id"] = self::getParameter('mch_id');
// 			$nativeObj ["device_info"] = C('NewWeiXin.DEVICEINFO');
			$nativeObj ["nonce_str"] = create_noncestr();
			$nativeObj ["body"] = $this->parameters['body'];
// 			$nativeObj ["detail"] = $this->parameters['detail'];
// 			$nativeObj ["attach"] = $this->parameters['attach'];
			$nativeObj ["out_trade_no"] = $this->parameters['out_trade_no'];
			//按照分来计算
// 			$nativeObj ["fee_type"] =intval($this->parameters['fee_type']);
			$nativeObj ["total_fee"] = intval($this->parameters['total_fee']);
			$nativeObj ["spbill_create_ip"] = get_client_ip();
// 			$nativeObj ["time_start"] = $this->parameters['time_start'];
// 			$nativeObj ["time_expire"] = $this->parameters['time_expire'];
// 			$nativeObj ["goods_tag"] = $this->parameters['goods_tag'];
			$nativeObj ["notify_url"] = self::getParameter('notify_url');
			$nativeObj ["trade_type"] = 'JSAPI';
// 			$nativeObj ["product_id"] = $this->parameters['product_id'];
			$nativeObj ["openid"] = $this->parameters['openid'];
			$nativeObj ["sign"] = $this->get_biz_sign($nativeObj);
			ksort($nativeObj);
			$nativeStr='<xml>'.xml2string('', $nativeObj).'</xml>';
//			logger($nativeStr);
			$http=new \Util\HttpCurl();
			if ($http->post($this->unifiedorder_api, $nativeStr)) {
				//返回预支付数组
				return $http->data;//(array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			} else {
				throw new SDKRuntimeException ( $http->data."预订单无响应！");
			}
		}catch(SDKRuntimeException $e){
			exit( $e->errorMessage () );
		}
	}
	// 生成jsapi支付请求json
	/*
	 * "C('WeiXin.APPID')" : "wxf8b4f85f3a794e77", //公众号名称，由商户传入 "timeStamp" : "189026618", //时间戳这里随意使用了一个值 "nonceStr" : "adssdasssd13d", //随机串 "package" : "bank_type=WX&body=XXX&fee_type=1&input_charset=GBK&notify_url=http%3a%2f %2fwww.qq.com&out_trade_no=16642817866003386000&partner=1900000109&spbill_create_i p=127.0.0.1&total_fee=1&sign=BEEF37AD19575D92E191C1E4B1474CA9", //扩展字段，由商户传入 "C('WeiXin.SIGNTYPE')" : "SHA1", //微信签名方式:sha1 "paySign" : "7717231c335a05165b1874658306fa431fe9a0de" //微信签名
	 */
	public function create_biz_package($package) {
		try {
			$nativeObj ["appId"] = self::getParameter('appid');
			$nativeObj ["timeStamp"] = strval(time());
			$nativeObj ["nonceStr"] = create_noncestr ();
			$nativeObj ['package']='prepay_id='.$package;
			$nativeObj ["signType"] = self::getParameter('signtype');
			$nativeObj ["paySign"] = $this->get_biz_sign ( $nativeObj );
			return $nativeObj ;
		} catch ( SDKRuntimeException $e ) {
			exit( $e->errorMessage () );
		}
	}
	/**
	 * 生成H5支付的串
	 * @param unknown $bizObj
	 * @throws SDKRuntimeException
	 * @return string
	 */
	public function get_biz_sign($bizObj){
		try {	
			if(!self::getParameter('partnerkey')){
				throw new SDKRuntimeException ( "没有密钥！");
			}
			ksort ( $bizObj );
			foreach ($bizObj as $k => $v){
				if (null != $v && "null" != $v && "sign" != $k) {
					$bizString .= $k . "=" . $v . "&";
				}
			}
			$bizString.='key='.self::getParameter('partnerkey');
			return strtoupper(md5($bizString));
		} catch ( SDKRuntimeException $e ) {
			die ( $e->errorMessage () );
		}
	}
}
use Exception;
class SDKRuntimeException extends Exception {
	public function errorMessage() {
		return $this->getMessage ();
	}
}

?>