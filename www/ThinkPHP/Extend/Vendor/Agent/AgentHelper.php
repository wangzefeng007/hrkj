<?php

/**
 * 代理处理模块
 * @author Administrator
 *
 */
class AgentHelper
{
	/**
	 * 代理端关键字
	 * @var unknown
	 */
	 public static $CLIENT_KEYSWORD = array(
			
			'320x320',
			'240x320',
			'176x220',
			'^lct',
			'android',
			'alcatel',
			'benq',
			'blackberry',
			'blazer',
			'bird',
			'cldc',
			'coolpad',
			'docomo',
			'ericsson',
			'foma',
			'gionee',
			'haier',
			'helio',
			'hiptop',
			'hosin',
			'htc',
			'huawei',
			'lenovo',
			'lg',
			'longcos',
			'iemobile',
			'iphone',
			'ipod',
			'jig browser',
			'meizu',
			'midp',
			'mobile',
			'mot',
			'motorola',
			'netfront',
			'nexusone',
			'nokia',
			'novarra',
			'openwave',
			'opera mini',
			'opera mobi',
			'palm',
			'palmsource',
			'panasonic',
			'pantech',
			'philips',
			'phone',
			'pieplus',
			'portalmmm',
			'samsung',
			'series',
			'sie-',
			'sgh',
			'sharp',
			'sony',
			'spice',
			'symbian',
			'techfaith',
			'ucweb',
			'up.browser',
			'up.link',
			'wap',
			'webos',
			'windows ce',
			'xda',
			'zte-',
	);
	
	/**
	 * 判断代理端是否为移动端
	 * @return boolean
	 */
	public static function isMobile()
	{
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
		{
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset($_SERVER['HTTP_VIA']))
		{
			// 找不到为flase,否则为true
			return stristr(strtolower($_SERVER['HTTP_VIA']), 'wap')?true:false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if(preg_match("/(" . implode ( '|', self::$CLIENT_KEYSWORD ) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
			{
				return true;
			}
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset($_SERVER['HTTP_ACCEPT']))
		{
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml')!== false) && (strpos($_SERVER ['HTTP_ACCEPT'], 'text/html') === false || (strpos ( $_SERVER ['HTTP_ACCEPT'], 'vnd.wap.wml' ) < strpos ( $_SERVER ['HTTP_ACCEPT'], 'text/html' ))))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 获取浏览器类型
	 *
	 * @return String 浏览器名称
	 */
	public static function getBrowser()
	{
		$browser = "";
		if(preg_match('/chrome/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "chrome";
		} 
		else if (preg_match('/msie 11/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie11";
		} 
		else if (preg_match('/msie 10/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie10";
		} 
		else if (preg_match('/msie 9/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie9";
		} 
		else if (preg_match('/msie 8/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie8";
		} 
		else if (preg_match('/msie 7/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie7";
		} 
		else if (preg_match('/msie 6/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie6";
		} 
		else if (preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "ie";
		} 
		else if (preg_match('/firefox/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "firefox";
		} 
		else if (preg_match('/safari/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "safari";
		} 
		else if (preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"]))
		{
			$browser = "opera";
		} 
		else
		{
			$browser = "other";
		}
		return $browser;
	}
}