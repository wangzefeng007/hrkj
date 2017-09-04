<?php

/**
 * 数据校验模块
 * @author Administrator
 *
 */
class VerifyHelper
{
	/**
	 * 错误信息：不能为空
	 * @var unknown
	 */
	const ERROR_REQUIRE = '必须';
	/**
	 * 错误信息：含有特殊字符，SQL注入危险
	 * @var unknown
	 */
	const ERROR_SQL = '不能包含特殊字符和空格';
	/**
	 * 错误信息：长度超过最大限制
	 * @var unknown
	 */
	const ERROR_MAX_LENGTH = '长度不能超过#length#位';
	/**
	 * 错误信息：长度低于最小限制
	 * @var unknown
	 */
	const ERROR_MIN_LENGTH = '长度不能少于#length#位';
	/**
	 * 错误信息：数字格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_NUMBER = '必须为数字';
	/**
	 * 错误信息：邮箱格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_EMAIL = '电子邮箱地址格式错误';
	/**
	 * 错误信息：字母格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_LETTER = '只能包含英文字母';
	/**
	 * 错误信息：变量格式错误，只能包含数字和字母、下划线
	 * @var unknown
	 */
	const ERROR_TYPE_VAR = '只能包含字母或数字';
	/**
	 * 错误信息：邮政编码格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_ZIPCODE = '格式错误';
	/**
	 * 错误信息：手机号码格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_MOBILE = '格式错误';
	/**
	 * 错误信息：IP地址格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_IP = '格式错误';
	/**
	 * 错误信息：身份证格式错误
	 * @var unknown
	 */
	const ERROR_TYPE_IDCARD = '格式错误';
	
	/**
	 * 格式类型：数字
	 * @var unknown
	 */
	const TYPE_NUMBER = 'number';
	/**
	 * 格式类型：邮箱
	 * @var unknown
	 */
	const TYPE_EMAIL = 'email';
	/**
	 * 格式类型：字母
	 * @var unknown
	 */
	const TYPE_LETTER = 'letter';
	/**
	 * 格式类型：数字和字母
	 * @var unknown
	 */
	const TYPE_VAR = 'var';
	/**
	 * 格式类型：邮政编码
	 * @var unknown
	 */
	const TYPE_ZIPCODE = 'zipcode';
	/**
	 * 格式类型：手机号码
	 * @var unknown
	 */
	const TYPE_MOBILE = 'mobile';
	/**
	 * 格式类型：IP地址
	 * @var unknown
	 */
	const TYPE_IP = 'ip';
	/**
	 * 格式类型：身份证格式
	 * @var unknown
	 */
	const TYPE_IDCARD = 'idcard';
	
	/**
	 * 表单数据校验函数
	 * 
	 * @param	Array	$data	需要校验的数据，结构如下
	 * 
	 * @return mixed
	 */
	public static function verifyForm($data)
	{
		$rs = true;
		$err = array();
		
		if($data&&is_array($data)&&count($data)>0)
		{
			foreach($data as $key=>$i)
			{
				$value	= $i['value'];
				$id		= $i['id'];
				
				if($i['require']&&!$value)
				{
					$err[] = array('id'=>$id, 'msg'=>self::ERROR_REQUIRE);
					$rs = false;
				}
				else if($i['nonsql']&&preg_match('/(\s|=|!|\?|\+|-|<|>)/i', $value))
				{
					$err[] = array('id'=>$id, 'msg'=>self::ERROR_SQL);
					$rs = false;
				}
				else if($i['maxlength']&&intval(strlen($value))>intval($i['maxlength']))
				{
					$err[] = array('id'=>$id, 'msg'=>preg_replace('/#length#/i', $i['maxlength'], self::ERROR_MAX_LENGTH));
					$rs = false;
				}
				else if($i['minlength']&&intval(strlen($value))<intval($i['minlength']))
				{
					$err[] = array('id'=>$id, 'msg'=>preg_replace('/#length#/i', $i['minlength'], self::ERROR_MIN_LENGTH));
					$rs = false;
				}
				else
				{
					switch(strtolower(@$i['type']))
					{
						case self::TYPE_NUMBER:
							if(!is_numeric($value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_NUMBER);
								$rs = false;
							}
							break;
						case self::TYPE_EMAIL:
							if(!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/i', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_EMAIL);
								$rs = false;
							}
							break;
						case self::TYPE_LETTER:
							if(!preg_match('/^[a-zA-Z]+$/i', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_LETTER);
								$rs = false;
							}
							break;
						case self::TYPE_VAR:
							if(!preg_match('/^[a-zA-Z0-9]+$/i', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_VAR);
								$rs = false;
							}
							break;
						case self::TYPE_ZIPCODE:
							if(!preg_match('/^\d{6}$/', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_ZIPCODE);
								$rs = false;
							}
							break;
						case self::TYPE_MOBILE:
							if(!preg_match('/^1[34578]{1}\d{9}$/', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_MOBILE);
								$rs = false;
							}
							break;
						case self::TYPE_IP:
							if(!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_IP);
								$rs = false;
							}
							break;
						case self::TYPE_IDCARD:
							if(!preg_match('/^\d{15}(\d{2}\w{1})?$/', $value))
							{
								$err[] = array('id'=>$id, 'msg'=>self::ERROR_TYPE_IDCARD);
								$rs = false;
							}
							break;
						default:
							break;
					}
				}
			}
		}
		return array('rs'=>$rs, 'error'=>$err);
	}
}