<?php

/**
 * 短信发送模块
 * @author Administrator
 *
 */
class MsgCenter
{
	/*	发送模板消息
	*	@param		array		$target		目的地数组: mobile-手机号,mail-邮箱地址,sms-站内消息地址,weixin-微信号
	*	@param		array		$template	消息模板: status-启动状态,title-消息标题,content-消息正文
	*	@param		array		$params		模板替换参数表
	*/
	public static function send($target,$type,$params='')
	{
		// var_dump($type);
		$template = self::getTpl($type);
		// var_dump($template);
		
		if ($template) {
			foreach ($template as $k => $v)
			{
				if ($v['status'] == 1)
				{
					$title = $v['title'];
					$content = $v['content'];
					if ($params)
					{
						foreach ($params as $k_p => $k_v)
						{
							$content = str_replace("##{$k_p}##",$k_v,$content);
						}
					}
					
					if ($k=="sms" && $target['mobile'])
					{	//调用 短信 接口
						Vendor('SmsHelper.SmsApi');
						$clapi  = new SmsApi();
						$rs = $clapi->sendSMS($target['mobile'], $content,true);
					}
					elseif ($k=="jpush" && $target['mobile'])
					{
						//调用 极光 接口
						Vendor('Jpush.jpush');
						$pushObj = new jpush();
						$receive = array('alias'=>array($target['mobile']));//别名
						// $content_message = json_encode(array('type'=>,'content'=>$content));
						//调用推送,并处理
						$jp_title = self::getType($type);
						$result = $pushObj->push($receive,$content,$jp_title,$m_type='',$m_txt='',$m_time='86400');
						//$pushObj->push($receive,$content,'','','86400',$content_message);
					}
					/* elseif ($k=="mail" && $target['mail'])
					{	//调用 邮件 接口
						//self::sendMail($target['mail'],$title,$content);
						//echo "<br>邮件 收件人:{$target['mobile']}<br>";
						//echo "邮件 标题:{$title}<br>";
						//echo "邮件 正文:{$content}<br>";
					}
					elseif ($k=="msg" && $target['msg'])
					{	//调用 站内消息 接口
						//sendMsg($target['msg'],$title,$content);
					}
					elseif ($k=="weixin" && $target['weixin'])
					{	//调用 微信 接口
						//sendWeixin($target['weixin'],$content);
					} */
				}
			}
		}
	}
	
	public static  function getType($type){
	    $title = '推送';
	    switch ($type){
	        case 'normalSplit':
	            $title = '商户分润';break;
	        default:
	            $title = '推送';break;
	    }
	    return $title;
	}	
	public static function getTpl($type)
	{
		$where = array(
			'type' => $type,
			'status' => 1,
		);
		$rs = M('setting_msg')->where($where)->find();
		return unserialize($rs['params']);
	}
	
	
	public static function sendMail($addr,$title,$content)
	{
		Vendor('Mail.phpmailer');
		
		$mail = new PHPMailer();
		$mail->CharSet = "utf-8";
		$mail->IsSMTP();				//设置采用SMTP方式发送邮件
		$mail->Host = $mail_server;		//设置邮件服务器的地址
		$mail->Port = 25;				//设置邮件服务器的端口，默认为25
		
		$mail->From     = $mail_mail;	//设置发件人的邮箱地址
		$mail->FromName = $mail_name;	//设置发件人的姓名
		$mail->SMTPAuth = true;			//设置SMTP是否需要密码验证，true表示需要
		
		$mail->Username = $mail_id;		//发件人账号
		$mail->Password = $mail_pwd;	//发件人密码
		$mail->Subject = $title;		//设置邮件的标题
		
		$mail->AltBody = "text/html";	// optional, comment out and test
		
		$mail->Body = $content;			//邮件正文
		$mail->IsHTML(true);			//设置内容是否为html类型
		//$mail->WordWrap = 50;			//设置每行的字符数
		//$mail->AddReplyTo($_SESSION['user']['mail'],$_SESSION['user']['name'].' '.$_SESSION['user']['sex']);     //设置回复的收件人的地址
		$mail->AddAddress($addr,"");     //设置收件的地址,名称
		if(!$mail->Send()) {                    //发送邮件
			//echo '发送失败:';
			//MsgUrl ("发送失败");
			//exit;
		} else {
			//session_unregister('shop');
			//MsgUrl ("您好，你所填写的订单资料已经成功发送到我公司。我们会尽快与您取得联系，谢谢！",'./');
		}
	}
	
}
