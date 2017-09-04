<?php
$name = 'a:1:{i:0;a:8:{s:4:"gsid";s:1:"1";s:5:"price";s:6:"100.00";s:3:"num";i:1;s:4:"usid";s:1:"1";s:4:"upid";s:1:"0";s:4:"name";s:12:"变形金刚";s:3:"gid";s:1:"1";s:5:"thumb";s:0:"";}}';
print_r(unserialize($name)); 
exit;

define('ROOT_PATH',dirname(__FILE__));
require ROOT_PATH."/conf_auto.php";
// $config = array();

//表单模版
$form_tpl = array();
//搜索框文本类型
$form_tpl['search']['text'] = '##desc##:<input type="text" name="##name##" value="{$where.##name##}">'."\n";

//搜索时间
$form_tpl['search']['time'] = '<label>##desc##：</label>
				<input onfocus="HS_setDate(this)" class="text_s rm" type="text" placeholder="开始时间" name="starttime" value="{$_params.starttime}" />
				<label>—</label>
				<input onfocus="HS_setDate(this)" class="text_s lfm" type="text" placeholder="结束时间" name="endtime" value="{$_params.endtime}" />'."\n";
				
$form_tpl['edit']['text'] = "
									<div class='control-group'>
										<label class='control-label' for='inputText1'>##desc##</label>
										<div class='controls'>
											<input data-rule-minlength='1' data-rule-required='true' id='validation_name' name='##name##' placeholder='##desc##' type='text' value='{\$info.##name##}'/>
										</div>
									</div>\n"; 
$form_tpl['edit']['radio'] = "
									<div class='control-group'>
										<label class='control-label' for='inputText1'>##desc##</label>
										<div class='controls'>
											##radio##
										</div>
									</div>\n"; 
$form_tpl['edit']['select'] = "
									<div class='control-group'>
										<label class='control-label' for='inputText1'>##desc##</label>
										<div class='controls'>
											<select name='##name##'>
												<option value=''>请选择
												<foreach name='##name##.list' item='item'>
												##options##
												</foreach>
											</select>
										</div>
									</div>\n"; 									

$indent ="\t\t";
									
//处理表单数据模版
$handle_form['search']['var'] = 	$indent."if (!empty(I('##name##'))) \$where['##name##'] = I('##name##');\n";

$handle_form['edit']['var'] = $indent."\$##name## = I('##name##','');\n";			//变量赋值		
$handle_form['edit']['vaild'] = $indent."\$this->vaildform('is_empty',\$##name##,'##require##');\n";	//验证输入
$handle_form['edit']['data'] = $indent."\$data['##name##'] = \$##name##;\n";	//入库封装							
									


$skip_controller = array('module','pay_type','level_fee');									

/* 自动生成 */

//控制器模版文件
$ControllerTpl = 	file_get_contents(ROOT_PATH."/Admin/Lib/Action/fatherAction.class.php");

//模型模版文件
$ModelTpl = file_get_contents(ROOT_PATH."/Admin/Lib/Model/fatherModel.class.php");

//视图模型目录
$ViewTpl = ROOT_PATH."/Admin/Tpl/father/";

define("ACTION_PATH",ROOT_PATH."/Admin/Lib/Action/");
define("MODEL_PATH",ROOT_PATH."/Admin/Lib/Model/");
define("TPL_PATH",ROOT_PATH."/Admin/Tpl/");

foreach($config as $kc=>$c)
{
	if (in_array($kc,$skip_controller))
	{
		continue;
	}
	$action_file = ACTION_PATH.$c['Controller']."Action.class.php";
	$model_file = MODEL_PATH.$c['Model']."Model.class.php";
	$view_path = TPL_PATH.$c['Controller'];
	if (!is_dir($view_path))
	{
		mkdir($view_path);
	}

	//控制器文件生成
	$ControllerActionContent = $ControllerTpl;	
	$ControllerActionContent = str_replace("##Controller##",$c['Controller'],$ControllerActionContent);
	$ControllerActionContent = str_replace("##Model##",$c['Model'],$ControllerActionContent);

	//模型文件生成
	$ModelContent = $ModelTpl;	
	$ModelContent = str_replace("##ModelClass##",$c['Model'],$ModelContent);
	$ModelContent = str_replace("##Table##",$c['Table'],$ModelContent);
	
	//视图文件生成
	foreach($c['View'] as $method=>$v)
	{
		$viewFile = file_get_contents($ViewTpl.$v['tpl']);
		$viewFile = str_replace("##title##",$v['title'],$viewFile);	
		$viewFile = str_replace("##subtitle##",$v['subtitle'],$viewFile);	
		//界面操作项
		if ($v['action'])
		{
			$_action = '';
			foreach($v['action'] as $a)
			{
				$_action .= '<a href="'.$a['href'].'" class="btn btn-primary">'.$a['title'].'</a>'."\n";
			}
			$viewFile = str_replace("##action##",$_action,$viewFile);
		}
		
		//表单处理
		if ($v['form'])
		{
			$viewFile = str_replace("##form_action##",$v['form']['action'],$viewFile);
			$viewFile = str_replace("##form_method##",$v['form']['method'],$viewFile);
			$inputs = $handle_inputs = '';
			foreach($v['form']['inputs'] as $i)
			{
				$inputs .= make_input($i,$v['form']['tpl']);
				if ($i['databind'])
				{
					foreach($i['databind']['method'] as $_method)
					{
						$_model = $i['databind']['datamodel'];
						// print_r($_model);
						// exit;
						$databind = '';
						$databind .= $indent."\$".$i['name']." = D('".$_model['model']."')->".$_model['method']."(".$_model['params'].");\n";
						$databind .= $indent."\$this->assign('".$i['name']."',\$".$i['name'].");\n";
						$ControllerActionContent = str_replace("##".$_method."Data##",$databind,$ControllerActionContent);
					}
				}
				
				if ($s = $handle_form[$v['form']['tpl']]['var'])
				{
					$handle_inputs .= str_replace("##name##",$i['name'],$s);
				}
				if (($s = $handle_form[$v['form']['tpl']]['vaild']) && $i['required'])
				{
					
					$tmp = str_replace("##name##",$i['name'],$s);
					$handle_inputs .= str_replace("##require##",$i['required'],$tmp);
				}
				if ($s = $handle_form[$v['form']['tpl']]['data'])
				{
					$handle_inputs .= str_replace("##name##",$i['name'],$s);
				}
			}
			$viewFile = str_replace("##inputs##",$inputs,$viewFile);
			$ControllerActionContent = str_replace("##".$method."Data##",$handle_inputs,$ControllerActionContent);
		}
		else
		{
			$viewFile = str_replace("##inputs##",'',$viewFile);
		}
		
		//数据列
		if ($v['list'])
		{
			$list_title = '';
			foreach($v['list']['title'] as $t)
			{
				$list_title .='<th class="sorting" rowspan="1" colspan="1">'.$t.'</th>'."\n";
			}
			$list_title .='<th class="sorting" rowspan="1" colspan="1">编辑</th>'."\n";
			$viewFile = str_replace("##listTitle##",$list_title,$viewFile);
			
			
			$list_data = '<tr class="odd">';
			foreach($v['list']['data'] as $d)
			{
				$list_data .= '<td class="  sorting_1">{$item.'.$d.'}</td>'."\n";
			}
			$list_data .= '<td class="  sorting_1">';
			$list_data .= '<a href="{:U("'.$c['Controller'].'/edit",array(id=>$item[id]))}">编辑</a>&nbsp;';
			$list_data .= "<a href=\"javascript:void(0)\" onclick=\"confirm_url('确定要删除？','{:U('".$c['Controller']."/delete',array(id=>\$item[id]))}')\">删除</a>\n</td>";
			$list_data .= '</tr>';
			$viewFile = str_replace("##listData##",$list_data,$viewFile);
		}
		file_put_contents($view_path."/".$v['tpl'],$viewFile);
	}
	$ControllerActionContent = str_replace("##action##",'',$ControllerActionContent);
	file_put_contents($action_file,$ControllerActionContent);
	file_put_contents($model_file,$ModelContent);

}

function make_input($i,$tpl_type)
{
	$call = 'make_input_'.$i['type'];
	return call_user_func_array($call,array($i,$tpl_type));
}

/*
*	radio选项
*/
function make_input_radio($i,$tpl_type)
{
	global $form_tpl;
	$html = $form_tpl[$tpl_type]['radio'];
	$html = str_replace("##desc##",$i['desc'],$html);
	$input = '';
	foreach($i['choices'] as $value=>$desc)
	{
		$input .= '<input name="'.$i['name'].'" type="radio" value="'.$value.'" <if condition="$info.'.$i['name'].' eq '.$value.'">checked="true"</if> /> '.$desc."&nbsp;";
	}
	$html = str_replace("##radio##",$input,$html);
	return $html;
}

/*
*	text文本选项
*/
function make_input_text($i,$tpl_type)
{
	global $form_tpl;
	$input = $form_tpl[$tpl_type]['text'];
	$input = str_replace("##desc##",$i['desc'],$input);
	$input = str_replace("##name##",$i['name'],$input);
	return $input;
}

/*
*	select选项
*/
function make_input_select($i,$tpl_type)
{
	global $form_tpl;
	$html = $form_tpl[$tpl_type]['select'];		
	$html = str_replace("##desc##",$i['desc'],$html);
	$html = str_replace("##name##",$i['name'],$html);
	$options = '<option value="{$item.id}" <if condition="$info.'.$i['name'].' eq $item.id">selected="true"</if>>{$item.name}'."\n";
	$html = str_replace("##options##",$options,$html);

	return $html;
	
}





