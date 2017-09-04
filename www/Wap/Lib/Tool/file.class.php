<?php
/*
*	文件上传处理类
*/
class file
{
	/*
	*	获取临时文件路径
	*	@param		string		$key		缓存键值
	*/
	static function get_tmpfile($key)
	{
		$config = C('upload');
		$file = $config['tmproot'].S($key);
		if (!is_file($file)) return false;
		return $file;
	}

	/*
	*	设置临时文件缓存键值
	*	@param		string		$file			文件路径
	*	@param		int			$expries	失效时间,默认30分钟
	*/
	static function set_tmpfile($file,$expries = 1800)
	{
		$key = md5($file.rand(1000,9999));
		S($key,$file,$expries);
		return $key;
	}
	
	/*
	*	临时文件入库
	*	@param			string		$filekey		文件key值
	*	@param			string		$type			文件类型
	*	@param			string		$tag			文件标签	
	*/
	static function tmp_to_final($filekey,$type,$tag)
	{
		$config = C('upload');
		$tmp_file = self::get_tmpfile($filekey);
		if (!$tmp_file)
		{
			return false;
		}
	
		$config = $config[$type];
		$path = $config['path'].$tag;
		if (!is_dir($path))
		{
			mkdir($path);
		}
		$filename = get_filename($tmp_file);
		return rename($tmp_file,$path."/".$filename);
		
		
	}
}