<?php

/**
 * 数据校验模块
 * @author Administrator
 *
 */
class VerifyCommon
{
	/**
	 * 表单数据校验函数
	 * 
	 * @param	Array	$data	需要校验的数据，结构如下
	 * 
	 * array(
	 * 		array('id'=>'', 'value'=>, 'verify'=>array()),
	 * 		...
	 * )
	 * 
	 * @return mixed
	 */
	public static function verifyForm($data)
	{
		$rs = true;
		$fail = array();
		
		if($data&&is_array($data)&&count($data)>0)
		{
			foreach($data as $key=>$i)
			{
				$value	= $i['value'];
				$id		= $i['id'];
				$verify	= $i['verify'];
				
				if($verify['require']&&!$value)
				{
					$i['failField'] = 'require';
					$i['failMsg'] = '不能为空！';
					$fail[] = $i;
					$rs = false;
				}
				else if($verify['nonsql']&&preg_match('/(\s|=|!|\?|\+|-|<|>)/i', $value))
				{
					$i['failField'] = 'nonsql';
					$i['failMsg'] = '不能包含特殊符号和空格！';
					$fail[] = $i;
					$rs = false;
				}
				else if($verify['maxlength']&&intval(strlen($value))>intval($verify['maxlength']))
				{
					$i['failField'] = 'maxlength';
					$i['failMsg'] = "长度不能超过{$verify['maxlength']}位！";
					$fail[] = $i;
					$rs = false;
				}
				else if($verify['minlength']&&intval(strlen($value))<intval($verify['minlength']))
				{
					$i['failField'] = 'minlength';
					$i['failMsg'] = "长度不能少于{$verify['minlength']}位！";
					$fail[] = $i;
					$rs = false;
				}
				else
				{
					switch(@$verify['type'])
					{
						case 'number':
							if(!is_numeric($value))
							{
								$i['failField'] = 'type';
								$i['failMsg'] = '必须为数字！';
								$fail[] = $i;
								$rs = false;
							}
							break;
						case 'email':
							if(!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $value))
							{
								$i['failField'] = 'type';
								$i['failMsg'] = '必须为正确的电子邮箱地址格式！';
								$fail[] = $i;
								$rs = false;
							}
							break;
						case 'word':
							if(!preg_match('/^[a-zA-Z]+$/', $value))
							{
								$i['failField'] = 'type';
								$i['failMsg'] = '必须为英文字母组合！';
								$fail[] = $i;
								$rs = false;
							}
							break;
						case 'var':
							if(!preg_match('/^[a-zA-Z0-9_]+$/', $value))
							{
								$i['failField'] = 'type';
								$i['failMsg'] = '必须为数字或字母！';
								$fail[] = $i;
								$rs = false;
							}
							break;
						default:
							break;
					}
				}
			}
		}
		return array('rs'=>$rs, 'fail'=>$fail);
	}
}