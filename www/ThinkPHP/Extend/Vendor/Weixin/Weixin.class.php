<?php
namespace Vendor\Weixin;
use Util\SlHttp;
/**
 * 
 * +------------------------------------------------------------+
 * @category MpWeixin 
 * +------------------------------------------------------------+
 * 公众微信平台接口类（需要类库Curl支持）
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @copyright http://www.suncco.com 2013
 * @version 1.0
 *
 * Modified at : 2013-9-16 15:00:33
 *
 */
class Weixin {
	/**
	 * 接口请求成功返回状态码
	 */
	const SUCCESS_CODE = 0;
	
	/**
	 * 消息类型
	 */
	public static $msgTyprArr = array(
		'text'		 => '文本消息',
		'news'		 => '图文消息',
		'event'		 => '事件消息',
		'image'		 => '图片消息',
		'link'		 => '链接消息',
		'location'	 => '地理位置',
		'voice'		 => '语音消息',
		'video'		 => '视频消息',
		'music'		 => '音乐消息',
	);
	
	/**
	 * 回复消息类型
	 */
	public $replyMsgTypeArr = array(
		'text'	 => 'text',
		'news'	 => 'news',
		'music'	 => 'music'
	);
	
	/**
	 * 回复图文消息最大个数
	 */
	const SEND_MAX = 10;
	
	/**
	 * 订阅事件标识
	 */
	const EVENT_SUBSCRIBE = 'subscribe';
	
	/**
	 * 取消订阅事件标识
	 */
	const EVENT_UNSUBSCRIBE = 'unsubscribe';
	
	/**
	 * 自定义菜单点击事件标识
	 */
	const EVENT_CLICK = 'click';
	
	/**
	 * 
	 * 用户已关注时的二维码事件推送标识
	 */
	const EVENT_SCAN = 'scan';
	
	/**
	 * 上报地理位置事件标识
	 */
	const EVENT_LOCATION = 'location';
	
	/**
	 * 数据接收及发送类
	 */
	private $_http = '';
	
	///第三方用户唯一凭证 (针对服务号)
	private $_appid;
	
	///第三方用户唯一凭证密钥，既appsecret(针对服务号)
	private $_secret;
	
	///本地收发消息接口凭证
	private $_token;
	
	///获取access_token的URL地址 
	private static $_get_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
	
	///操作自定义菜单相关接口URL地址
	private static $_menu_url = array(
		'create' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=',	//创建
		'get'	 =>	'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=',		//查询
		'delete' =>	'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='	//删除
	);
	
	///上传多媒体文件URL
	private static $_upload_media_url = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s';
	
	///错误码
	protected $_errorno = 0; 
	
	///其他错误信息
	protected $_errmsg = '';
	
	///全局返回码对应的提示信息
	private static $_errors = array(
		'-1' => '系统繁忙',
		'0' => '请求成功',
		'40001' => '获取access_token时AppSecret错误，或者access_token无效',
		'40002' => '不合法的凭证类型',
		'40003' => '不合法的OpenID',
		'40004' => '不合法的媒体文件类型',
		'40005' => '不合法的文件类型',
		'40006' => '不合法的文件大小',
		'40007' => '不合法的媒体文件id',
		'40008' => '不合法的消息类型',
		'40009' => '不合法的图片文件大小',
		'40010' => '不合法的语音文件大小',
		'40011' => '不合法的视频文件大小',
		'40012' => '不合法的缩略图文件大小',
		'40013' => '不合法的APPID',
		'40014' => '不合法的access_token',
		'40015' => '不合法的菜单类型',
		'40016' => '不合法的按钮个数',
		'40017' => '不合法的按钮个数',
		'40018' => '不合法的按钮名字长度',
		'40019' => '不合法的按钮KEY长度',
		'40020' => '不合法的按钮URL长度 ',
		'40021' => '不合法的菜单版本号',
		'40022' => '不合法的子菜单级数',
		'40023' => '不合法的子菜单按钮个数',
		'40024' => '不合法的子菜单按钮类型',
		'40025' => '不合法的子菜单按钮名字长度',
		'40026' => '不合法的子菜单按钮KEY长度',
		'40027' => '不合法的子菜单按钮URL长度',
		'40028' => '不合法的自定义菜单使用用户',
		'40029' => '不合法的oauth_code',
		'40030' => '不合法的refresh_token',
		'40031' => '不合法的openid列表',
		'40032' => '不合法的openid列表长度',
		'40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
		'40035' => '不合法的参数',
		'40038' => '不合法的请求格式',
		'40039' => '不合法的URL长度',
		'40050' => '不合法的分组id',
		'40051' => '分组名字不合法',
		'41001' => '缺少access_token参数',
		'41002' => '缺少appid参数',
		'41003' => '缺少refresh_token参数',
		'41004' => '缺少secret参数',
		'41005' => '缺少多媒体文件数据',
		'41006' => '缺少media_id参数',
		'41007' => '缺少子菜单数据',
		'41008' => '缺少oauth code',
		'41009' => '缺少openid',
		'42001' => 'access_token超时',
		'42002' => 'refresh_token超时',
		'42003' => 'oauth_code超时',
		'43001' => '需要GET请求',
		'43002' => '需要POST请求',
		'43003' => '需要HTTPS请求',
		'43004' => '需要接收者关注',
		'43005' => '需要好友关系',
		'44001' => '多媒体文件为空',
		'44002' => 'POST的数据包为空',
		'44003' => '图文消息内容为空',
		'44004' => '文本消息内容为空',
		'45001' => '多媒体文件大小超过限制',
		'45002' => '消息内容超过限制',
		'45003' => '标题字段超过限制',
		'45004' => '描述字段超过限制',
		'45005' => '链接字段超过限制',
		'45006' => '图片链接字段超过限制',
		'45007' => '语音播放时间超过限制',
		'45008' => '图文消息超过限制',
		'45009' => '接口调用超过限制',
		'45010' => '创建菜单个数超过限制',
		'45015' => '回复时间超过限制',
		'45016' => '系统分组，不允许修改',
		'45017' => '分组名字过长',
		'45018' => '分组数量超过上限',
		'46001' => '不存在媒体数据',
		'46002' => '不存在的菜单版本',
		'46003' => '不存在的菜单数据',
		'46004' => '不存在的用户',
		'47001' => '解析JSON/XML内容错误',
		'48001' => 'api功能未授权',
		'50001' => '用户未授权该api',
		'MP001' => '用户组名称不能为空'
	);
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name __construct
	 * +------------------------------------------------------------+
	 * 构造函数
	 * +------------------------------------------------------------+
	 *
	 * @param string $token  本地收发消息接口凭证
	 * @param string $appid  第三方用户唯一凭证 (针对服务号)
	 * @param string $secret 第三方用户唯一凭证密钥，既appsecret(针对服务号)
	 *
	 */
	public function __construct($token='', $appid = '', $secret = ''){
		$this->_appid = $appid;
		$this->_secret = $secret;
		$this->_token = $token;
// 		$this->jssdk = new \ORG\Weixin\jssdk($appid, $secret);
	}
	/**
	 *  获取摇一摇信息
	 * @param string $ticket
	 * @return Ambigous <boolean, mixed>|boolean
	 */
	public function getShakeInfo($ticket,$res=false){
		$data = json_encode(array('ticket'=>$ticket));
		$json = $this->_post_curl("https://api.weixin.qq.com/shakearound/user/getshakeinfo?access_token=" . $this->_getToken($res),$data);
		if($json['errcode']=='40001'){
			$json = $this->_post_curl("https://api.weixin.qq.com/shakearound/user/getshakeinfo?access_token=" . $this->_getToken(true),$data);
		}
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name createMenu
	 * +------------------------------------------------------------+
	 * 创建自定义菜单
	 * +------------------------------------------------------------+
	 *
	 * @param array $buttons
	 * @return 创建成功则返回true
	 * 
	 * $buttons设置示例：
	 * 	array(
	 * 		//不包含子菜单，个数现在为2~3个
	 *		array(
	 *			'name' => '按钮描述，既按钮名字，不超过16个字节',
	 *			'type' => '按钮类型，目前有click类型',
	 *			'key'  => '按钮KEY值，用于消息接口(event类型)推送，不超过128字节'
	 *		),
	 *		
	 *		//包含子菜单，个数限制应为2~5个
	 *		array(
	 *			'name' => '按钮描述',
	 *			'sub_button' => array(
	 *				array(
	 *					'name' => '按钮描述，不超过40个字节', 
	 *					'type' => '按钮类型，目前有click类型', 
	 *					'key'  => '按钮KEY值'
	 *				)
	 *			)
	 *		)
	 *	)
	 *
	 */
	public function createMenu($buttons){
		
		$datas = array_to_json(array('button' => $buttons));
		$json = $this->_post_curl(self::$_menu_url['create'] . $this->_getToken(), $datas);
		if (self::SUCCESS_CODE == (int) $json['errcode']){
			return true;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	
	/**
	 * 取消当前使用的自定义菜单
	 */
	public function deleteMenu(){
		$json = $this->_get(self::$_menu_url['delete'] . $this->_getToken());
		if (self::SUCCESS_CODE == $json->errcode){
			return true;
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 查询当前使用的自定义菜单结构
	 */
	public function getMenu(){
		$json = $this->_get(self::$_menu_url['get'] . $this->_getToken());
		if (self::SUCCESS_CODE == (int) $json['errcode']){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	/**
	 * 获取所有分组
	 */
	public function getGroupList()
	{
		$json = $this->_get("https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . $this->_getToken());
		if($json['groups']){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	/**
	 * 获取用户所在分组
	 * 
	 * @param string $p_openId	OPENID
	 */
	public function getUserGroup($p_openId)
	{
		$data = json_encode(array('openid'=>$p_openId));
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=" . $this->_getToken(), $data);
		if(empty($json['errcode'])){
			return $json['groupid'];
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	
	/**
	 * 将用户转移到哪个分组
	 * 
	 * @param string $p_openId	OPENID
	 * @param int $p_groupId	分组ID
	 */
	public function modifyUserGroup($p_openId, $p_groupId)
	{
		$data = json_encode(array('openid'=>$p_openId, 'to_groupid'=>$p_groupId));
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=" . $this->_getToken(), $data);
		if(empty($json['errcode'])){
			return true;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	
	/**
	 * 获取所有用户
	 * 
	 * @param string $p_nextOpenid	下一页的首个开头OPENID
	 * @return array 提取到的用户数据
	 */
	public function getFriendList($p_nextOpenid='')
	{
		$json = $this->_get("https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->_getToken()}&next_openid={$p_nextOpenid}");
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	
	/**
	 * 获取关注用户的信息
	 * 
	 * @param string $p_openId	OPENID
	 */
	public function getFriendInfo($p_openId){
		$json = $this->_get("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->_getToken()}&openid={$p_openId}");
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	
	/**
	 * 获取关注用户的信息
	 * 
	 * @param string $p_openId	OPENID
	 */
	public function getFriendHeadimg($p_url, $p_openid){
		$json = $this->_get($p_url);
		if($json == false){
			return false;
		}
		
		$pathinfo = pathinfo($p_url);
		var_dump($pathinfo);exit();
		$path = ROOT . 'Public/Member/headimgurl/';
		$a = file_put_contents($path, $img_data);

	}
	/**
	 * 发送单条消息
	 * @param array $p_openIds		[OPENID1,OPENID12,……]
	 * @param string $p_msgtype		消息类型，暂时只用['text', 'music', 'news']
	 * @param string $p_msg			消息内容
	 * @param string $p_voice		当为MUSIC时，使用此值
	 * @param int  $p_news			当为news时，使用此值取得具体的内容
	 */
	public function sendMessageGroups($group_Id, $p_msgtype, $p_msg, $p_voice, $p_news)
	{
		if($group_Id!=10000){
			$message = array(
					'filter'=>array(
							'is_to_all'=>false,
							'group_id'=>$group_Id
					),
						
			);
		}else{
			$message = array(
					'filter'=>array(
							'is_to_all'=>true,
					),
			
			);
		}
		$message['msgtype'] = $p_msgtype;
		switch($p_msgtype){
			case 'text':
				$message['text']['content'] = $p_msg;
				break;
			default:
				return false;
		}
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $this->_getToken(),json_encode($message,JSON_UNESCAPED_UNICODE) );
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}	
	/**
	 * 发送单条消息
	 * @param array $p_openIds		[OPENID1,OPENID12,……]
	 * @param string $p_msgtype		消息类型，暂时只用['text', 'music', 'news']
	 * @param string $p_msg			消息内容
	 * @param string $p_voice		当为MUSIC时，使用此值
	 * @param int  $p_news			当为news时，使用此值取得具体的内容
	 */
	public function sendMessageUsers($p_openIds, $p_msgtype, $p_msg, $p_voice, $p_news)
	{
		$message = array(
			'touser' => $p_openIds,
			'msgtype' => $p_msgtype
		);
		switch($p_msgtype){
			case 'text':
				$message['text']['content'] = $p_msg;
				break;
// 			case 'music':
// 				$p_voice = (strpos($p_voice, 'http://') !== false ? '' : str_replace('mpweixin.php/', '', DOMAIN_URL)).$p_voice;
// 				$message['music'] = array(
// 					'title'			 => '回复音频',
// 					'description'	 => '',
// 					'musicurl'		 => $p_voice,
// 					'hqmusicurl'	 => $p_voice
// 				);
// 				break;
// 			case 'mpnews':
// 				$media_id = D('Source')->getMediaId($p_msgtype,$p_news,4);
// 				if(!$media_id){
// 					return;
// 				}
// 				$message['mpnews']['media_id'] = $media_id;
// 				break;
			default:
				return false;	
		}
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $this->_getToken(),json_encode($message,JSON_UNESCAPED_UNICODE) );
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	/**
	 * 根据授权code获取OPPID
	 * @param string $code 
	 * @return array|boolean
	 */
	public function getOauth2ByCode($code)
	{
		$json = $this->_get("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->_appid}&secret={$this->_secret}&code={$code}&grant_type=authorization_code");
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	/**
	 * 获取素材库列表 日/1000次
	 * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
	 * @param int $count 返回素材的数量，取值在1到20之间
	 */
	public function getMediaList($type,$offset=0,$count=20){
		$types = array('image','video','voice','news');
		if(!in_array($type,$types)){
			$this->_errorno = '未知类型';
			return false;
		}
		$data = json_encode(array('type'=>$type, 'offset'=>$offset,'count'=>$count));
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . $this->_getToken(), $data);
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
	}
	/**
	 * 删除素材
	 * @param int $media_id 素材的media_id
	 */
	public function delMedia($media_id){
		$data = json_encode(array('media_id'=>$media_id));
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=" . $this->_getToken(), $data);
		if(empty($json['errcode'])){
			return $json;
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
		
	}
	/**
	 * 上传图文消息 
	 * @param int $user_id
	 * @param int $parent_id
	 * @param int $respontype
	 */
	public function addNews($user_id,$respontype,$parent_id){
		$newsArr = D('reply_news')->where(array('user_id'=>$user_id,'respontype'=>$respontype,'parent_id'=>$parent_id))->order("is_cover DESC")->select();
		if(empty($newsArr)){
			return false;
		}
		foreach($newsArr as $val){
			$media_id = D('Source')->addWxImage('',$val['image'],true);
			$message['articles'][] = array(
					"title"			 		=> $val['title'],
					"thumb_media_id" 		=> $media_id,
					"author"		 		=> '',
					"digest"		 		=> '',
					"show_cover_pic" 		=> $val['is_cover'],
					"content"	 	 		=> $val['content'],
					"content_source_url" 	=> $val['url'] ? $val['url'] : U('WxSource/materialNews', 'id=' . $val['id'], true, false, true)
			);
		}
		print_r($message);
		$json = $this->_post_curl("https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=" . $this->_getToken(),json_encode($message));
		print_r($json);
		if(!empty($json['media_id'])){
			return $json['media_id'];
		}else{
			$this->_errorno = $json['errcode'];
			return false;
		}
		return false;
	}
	/**
     * 素材库上传
     *
     * 上传的多媒体文件有格式和大小限制，如下：
     * 图片（image）: 1M，支持JPG格式
     * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）：10MB，支持MP4格式
     * 缩略图（thumb）：64KB，支持JPG格式
     * 媒体文件永久素材
     *
     * @param $type 图片（image）、语音（voice）、视频（video）和缩略图（thumb）360*200
     * @param $filePath 绝对路径
     * @return boolean|string
     */
    final public function addMedia($type,$filePath){
        $filePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))).$filePath;
        $data['media'] ='@'.$filePath;
        $json = $this->_post_curl("http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->_getToken().'&type='.$type, $data);
        print_r($data);
        print_r($json);
        if(empty($json['errcode'])){
        	return $json;
        }else{
        	$this->_errorno = $json['errcode'];
        	return false;
        }
    }
/*--------------------------------------------------------------------------------------*/	
	///通过获取凭证接口获取到access_token 日/2000次
	function _getToken($res=false){
		return $this->jssdk->getAccessToken($res);
		//凭证已设置并有效期未过
		if(!empty($_SESSION['access_token']) && time() < $_SESSION['access_token_expires']){
			return $_SESSION['access_token'];
		}
		try {
			$json = $this->_get(sprintf(self::$_get_token_url, $this->_appid, $this->_secret));
			if ($json['access_token']){
				//设置接口凭证的过期时间
				$_SESSION['access_token'] = $json['access_token'];
				$_SESSION['access_token_expires'] = time() + 3600;
				return $_SESSION['access_token'];
			}else{
				$this->_errorno = $json['errmsg'];
				return false;
			}
		} catch (E $e){
			$e->getMsg();
		}
	}
	
	/**
	 * 使用CURL方式提交数据
	 * @param type $url
	 * @param type $jsonData
	 * @return type
	 */
	protected function _post_curl($url, $jsonData)
	{
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result, true);
	}
	
	///发起POST请求
	protected function _post($url, $data = array()){
		require_once ROOT .'Public/Class/Net/SlHttp.class.php';
		$this->_http = SlHttp::getInstance();
		$this->_http->sendRequest($url, $data, 'post');
        $result = $this->_http->getResultData();
		if(!empty($result)){
			return json_decode($result, true);
		}else{
			$this->_errorno = -1;
			$this->_errmsg = 'http error';
			return false;
		}
	}
	
	///发起GET请求
	protected function _get($url){
		$this->_http = SlHttp::getInstance();
		$this->_http->sendRequest($this->_url . $url, array(), 'get');
        $result = $this->_http->getResultData();
//		var_dump($result);
		if(!empty($result)){
			return json_decode($result, true);
		}else{
			$this->_errorno = -1;
			$this->_errmsg = 'http error';
			return false;
		}
	}
	
	/**
	 * 消息接口签名验证
	 */
	public function checkSignature(){
		if (empty($this->_token)) return false;
		$signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce 	   = $_GET["nonce"];
		
		$tmpArr = array($this->_token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 获取接口请求发生的错误信息
	 */
	public function getError(){
		if (isset(self::$_errors[$this->_errorno])){
			return self::$_errors[$this->_errorno];
		}else{
			return $this->_errmsg ? $this->_errmsg : '未知错误';
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name getMessage
	 * +------------------------------------------------------------+
	 * 获取普通微信用户向公众账号发送的消息：文本、图片、链接、地理位置、事件推送
	 * +------------------------------------------------------------+
	 *
	 * @return array
	 * 
	 * 返回数据说明
	 * ┌─────────┬───────┬─────────────────────┐
	 * │信息类型    │字段	 │描述				   │
	 * ├─────────┼───────┼─────────────────────┤
	 * │	     │id	 │消息id，64位整型	   │
	 * │    公	 ├───────┼─────────────────────┤
	 * │    共	 │from   │发送方帐号(OpenID)	   │
	 * │    字	 ├───────┼─────────────────────┤
	 * │    段	 │time   │消息创建时间(整型)	   │
	 * │         ├───────┼─────────────────────┤
	 * │         │type   │消息类型			   │
	 * ├─────────┼───────┼─────────────────────┤
	 * │location:│x      │地理位置纬度		   │
	 * │地理位置    ├───────┼─────────────────────┤
	 * │消息  	 │y      │地理位置经度		   │
	 * │    	 ├───────┼─────────────────────┤
	 * │         │scale  │地图缩放大小		   │
	 * ├─────────┼───────┼─────────────────────┤
	 * │link：链	 │title  │链接消息标题		   │
	 * │接消息         ├───────┼─────────────────────┤
	 * │    	 │desc   │链接消息描述      		   │
	 * │         ├───────┼─────────────────────┤
	 * │         │url    │链接消息链接        	   │
	 * ├─────────┼───────┼─────────────────────┤
	 * │		 │		 │事件类型			   │
	 * │         │   	 │subscribe:订阅	   	   │
	 * │		 │       │unsubscribe:取消订阅    │
	 * │		 │event  │click:菜单点击		   │
	 * │		 │		 │scan:二维码事件	   │
	 * │event:	 │		 │location:上报地理位置  │
	 * │事件消息    ├───────┼─────────────────────┤
	 * │         │key    │事件KEY值            		   │
	 * │         ├───────┼─────────────────────┤
	 * │         │ticket │二维码的ticket		   │
	 * ├─────────┼───────┼─────────────────────┤
	 * │         │p      │地理位置精度		   │
	 * └─────────┴───────┴─────────────────────┘
	 */
	public function &getMessage(){
		
		$xml = $GLOBALS["HTTP_RAW_POST_DATA"];
		$message = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$msgType = strtolower($message->MsgType);
		$data = array(
			'id'	=> intval($message->MsgId),			//消息id，64位整型
			'from'	=>	strval($message->FromUserName),	//发送方帐号（一个OpenID）
			'to'	=>	strval($message->ToUserName),		//接收者（开发者微信号）
			'time'	=>	strval($message->CreateTime),		//消息创建时间 （整型）
			'type'	=>	strval($msgType)					//消息类型
		);
		
		switch ($msgType){
			//图片消息
			case 'image' : 
				$data['image'] = strval($message->PicUrl);		//图片链接
				$data['mediaid']=strval($message->MediaId);
				break;
			
			//地理位置消息
			case 'location' :
				$data['x']		= strval($message->Location_X);	//地理位置纬度
				$data['y']		= strval($message->Location_Y);	//地理位置经度
				$data['scale']	= strval($message->Scale);		//地图缩放大小
				$data['label']	= strval($message->Label);		//地图缩放大小
				break;
			
			//链接消息
			case 'link' :
				$data['title']	= strval($message->Title);		//消息标题
				$data['desc']	= strval($message->Description);//消息描述
				$data['url']	= strval($message->Url);		//消息链接
				break;
			//语音消息
			case 'voice' : 
				$data['recognition'] = strval($message->Recognition); //语音识别结果，UTF8编码
				$data['mediaid'] = strval($message->MediaID); //语音消息媒体id
				$data['format'] = strval($message->Format); //语音格式：amr
				break;
			//事件推送消息
			case 'event' :
				//事件类型，subscribe(订阅)、unsubscribe(取消订阅)、click(自定义菜单事件)、scan(二维码事件)、location(上报地理位置事件)
				$data['event']	= strtolower($message->Event);
				$data['key']	= strval($message->EventKey);	//事件KEY值
				if($message->Ticket){
					$data['ticket'] = strval($message->Ticket);	//二维码的ticket，可用来换取二维码图片
				}
				
				//上报地理位置事件
				if (self::EVENT_LOCATION == $data['event']){
					$data['x'] = strval($message->Latitude);	//地理位置纬度
					$data['y'] = strval($message->Longitude);	//地理位置经度
					$data['p'] 	 = strval($message->Precision);	//地理位置精度
				}
				break;
			
			//文本消息
			default:
				$data['text']	= strval($message->Content);	//文本消息内容
		}
		
		unset($xml, $message);
		
		return $data;
	}
	
	/**
	 * 响应公众平台接口配置
	 */
	public function response(){
		if (!$this->checkSignature()){
			exit('Signature verification failed.');
		}
		// 网址接入验证
		if (isset($_GET['echostr'])&&!isset($GLOBALS['HTTP_RAW_POST_DATA']))   exit($_GET['echostr']);
		// 是否有数据
		if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])){
			exit('Need data.');
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------------+
	 * @name replyMessage
	 * +------------------------------------------------------------------+
	 * 发送被动响应消息，支持回复文本、图文、图文、语音、视频、音乐
	 * 
	 * 回复设置的消息数据$data的格式：
	 * 文本消息 ： 字符串，长度不超过2048字节
	 * 音乐消息 ：一维数组 array('title' => '标题', 'desc' => '描述', 'url' => '音乐链接', '高质量音乐链接，WIFI环境优先使用该链接播放音乐')
	 * 图文消息 ： 二维数组 array(
	 *     array(
	 *        'title' => '图文消息标题',
	 *        'desc'  => '图文消息描述',
	 *        'image' => '图片链接，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80',
	 *        'url'   => '点击图文消息跳转链接'
	 *     ),
	 *     //更多
	 * )
	 * +------------------------------------------------------------------+
	 * 
	 * @param string $from	发送者
	 * @param string $to	接收者
	 * @param mixed $data   消息内容
	 * @param string $msgType 消息类型
	 * 
	 * @return	返回标准化后的XML代码
	 */
	public function replyMessage($from, $to, $data, $msgType = 'text',$return=false){
		$xml = '<xml>';
		$xml .= '<ToUserName><![CDATA[' . $to . ']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA[' . $from . ']]></FromUserName>';
		$xml .= '<CreateTime>' . time() . '</CreateTime>';
		$xml .= '<MsgType><![CDATA[' . $msgType . ']]></MsgType>';
		
		switch ($msgType){
			//回复音乐消息
			case 'music' :
				$xml .= '<Music>';
				$xml .= '<Title><![CDATA[' . $data['title'] . ']]></Title>';
				$xml .= '<Description><![CDATA[' . $data['desc'] . ']]></Description>';
				$xml .= '<MusicUrl><![CDATA[' . $data['url'] . ']]></MusicUrl>';
				$xml .= '<HQMusicUrl><![CDATA[' . $data['hq'] . ']]></HQMusicUrl>';
				$xml .= '</Music>';
				break;
				
			//回复图片消息
			case 'image' :
				$xml  .= '<Image>';
				$xml  .= '<MediaId><![CDATA[' . $data . ']]></MediaId>';
				$xml  .= '</Image>';
				break;
			
			//回复图文消息
			case 'news' :
				//文本条数，最大10
				$count = count($data);
				$item = '<item>' .
							'<Title><![CDATA[%s]]></Title>' .
							'<Description><![CDATA[%s]]></Description>' .
							'<PicUrl><![CDATA[%s]]></PicUrl>' .
							'<Url><![CDATA[%s]]></Url>' .
						'</item>';
				
				$count = $count <= self::SEND_MAX ? $count : self::SEND_MAX;
				$xml .= '<ArticleCount>' . $count . '</ArticleCount>';
				$xml .= '<Articles>';
				if ($count > 0){
					$idx = 1;
					foreach ($data as $vo){
						if ($idx > $count){
							break;
						}
						$content = sprintf($item, $vo['title'], $vo['desc'], $vo['image'], $vo['url']);
						if(!$vo['image']){
							$content = preg_replace("|<PicUrl>(.+?)</PicUrl>|si", '', $content);
						}
						$xml.=$content;
						$idx ++;
					}
				}else{
					$xml .= sprintf($item, '', '', '', '');
				}
				
				$xml .= '</Articles>';
				break;
				
			//回复文本消息
			default:
				$xml .= '<Content><![CDATA[' . $data . ']]></Content>';
		}
		
		$xml .= '</xml>';
		if($return){
			return $xml;
		}else{
			echo $xml;
			exit;
		}
	}
	/**
	 * 返回标准的xml数据
	 * @param unknown $from
	 * @param unknown $to
	 * @param unknown $data
	 * @param string $msgType
	 * @return string
	 */
	public function ToXmlMessage(){
		return $GLOBALS["HTTP_RAW_POST_DATA"];
	}
	/**
	 * 判断是否为关注后的二维码扫描事件
	 */
	public function isScan(){
		$data = &$this->getMessage();
		$scan = 'event' == $data['type'] && self::EVENT_SCAN == $data['event'];
		unset($data);
		
		return $scan;
	}
	
	/**
	 * 判断是否为首次订阅
	 */
	public function isSubscribe(){
		$data = &$this->getMessage();
		$res = $data && is_array($data) && 'event' == $data['type'] && self::EVENT_SUBSCRIBE == $data['event'];
		unset($data);
		return $res;
	}
	
	/**
	 * 判断是否为取消订阅
	 */
	public function isUnsubscribe(){
		$data = &$this->getMessage();
		$res = $data && is_array($data) && 'event' == $data['type'] && self::EVENT_UNSUBSCRIBE == $data['event'];
		unset($data);
		return $res;
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name subscribe
	 * +------------------------------------------------------------+
	 * 对首次订阅的微信号回复消息（参数使用方法见：replyMessage()）
	 * +------------------------------------------------------------+
	 *
	 * @param mixed $message  消息内容
	 * @param string $msgType 消息类型
	 *
	 */
	public function subscribe($message, $msgType = 'text'){
		$data = $this->getMessage();
		if ($data && is_array($data) && 'event' == $data['type'] && self::EVENT_SUBSCRIBE == $data['event']) {
			$this->replyMessage($data['to'], $data['from'], $message, $msgType);
		}
	}
	
	public function uploadMedia($media, $type = ''){
		
	}
}
?>