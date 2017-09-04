<?php

/**
 * Uploadify上传类库
 * @author Administrator
 *
 */
class UploadifyCommon
{
	/**
	 * 上传文件保存
	 * 
	 * @param	String	$savePath	文件保存目录
	 * @param	String	$saveName	文件保存名称
	 * @param	int		$fileSize	文件大小限制（KB）
	 * @param	int		$fileNameLen	文件名称长度限制
	 * @param	Array	$extArr		文件允许上传的扩展格式
	 * @param	Boolean	$isOverride	是否允许覆盖
	 * 
	 * @return 	mixed	上传结果
	 */
	public static function upload($savePath, $saveName, $fileSize, $fileNameLen, $extArr, $isOverride)
	{
		/* 上传文件保存目录（相对于当前目录） */
		if(substr(trim($savePath), -1, 1)!='/'){
			$savePath = $savePath . '/';
		}
		if(!file_exists($savePath))
		{
			return array("rs"=>false, "msg"=>"文件保存的目录不存在！");
		}
		
		// 获取php.ini文件中上传大小控制
		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE)
		{
			return array("rs"=>false, "msg"=>"上传的文件超过php.ini配置大小！");
		}
		
		/* 上传的文件信息 */
		$uploadFile = "Filedata";
		$uploadFileName = $_FILES[$uploadFile]['name'];
		$uploadFileInfo = pathinfo($uploadFileName);
		$uploadFileExt = $uploadFileInfo["extension"];
		
		
		if (!isset($_FILES[$uploadFile])) {
			return array("rs"=>false, "msg"=>"上传文件丢失！请重新上传！");
		} else if (!isset($_FILES[$uploadFile]["tmp_name"]) || !@is_uploaded_file($_FILES[$uploadFile]["tmp_name"])) {
			return array("rs"=>false, "msg"=>"上传失败！请重新上传！");
		} else if (!isset($uploadFileName)) {
			return array("rs"=>false, "msg"=>"上传失败！请重新上传！");
		}
		
		// 程序上传文件大小控制
		$max_file_size_in_bytes = 1024 * $fileSize;	// bytes单位
		$tip = "{$fileSize}KB";
		$file_size = @filesize($_FILES[$uploadFile]["tmp_name"]);
		if ($file_size&&$file_size>$max_file_size_in_bytes)
		{
			return array("rs"=>false, "msg"=>"上传的文件大小超过{$tip}！");
		}
		if ($file_size<=0||!$file_size)
		{
			return array("rs"=>false, "msg"=>"不能上传空文件！");
		}
		
		// 文件名规范检测
		$MAX_FILENAME_LENGTH = $fileNameLen;
		$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				//允许的文件名字符
		$file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($uploadFileName));
		if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH)
		{
			return array("rs"=>false, "msg"=>"上传的文件名超过指定长度:{$MAX_FILENAME_LENGTH}！");
		}
		
		// 文件存在处理
		if (file_exists($savePath . $saveName . $uploadFileExt)) {
			if($isOverride){
				unlink($savePath . $saveName . $uploadFileExt);
			}else{
				return array("rs"=>false, "msg"=>"文件已存在！");
			}
		}
		
		// 判断文件后缀名是否满足白名单
		// $extArr = array('xls', 'jpg', 'png', 'gif');
		if (!$extArr||!is_array($extArr)||count($extArr)<1) {
			return array("rs"=>false, "msg"=>"文件类型数组不能为空！");
		}
		foreach ($extArr as $key=>$i){
			$extArr[$key] = strtolower($extArr[$key]);
		}
		if(!in_array(strtolower($uploadFileExt), $extArr))
		{
			return array("rs"=>false, "msg"=>"文件类型不符合要求！");
		}
		
		// 保存文件
		if (!@move_uploaded_file($_FILES[$uploadFile]["tmp_name"], $savePath . $saveName . "." . $uploadFileExt))
		{
			return array("rs"=>false, "msg"=>"文件保存失败！");
		}
		else
		{
			return array(
				'rs'=>true,
				'uploadName'	=>	$file_name,
				'newName'		=>	$saveName . '.' . $uploadFileExt,
				'extension'		=>	$uploadFileExt, 
				'fullName'		=>	$savePath . $saveName . '.' . $uploadFileExt
			);
		}
	}
}