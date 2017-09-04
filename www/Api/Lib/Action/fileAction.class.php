<?php
set_time_limit(0);
class fileAction extends baseAction
{
	protected $config = array();
	

	public function upload()
	{
		import("@.Tool.file");
		$type = I('type');
		$config = C('upload');
		$this->vaild_params('in_array',array($type,$config['type']),'type参数不合法');
		if ($upload = $this->init_upload($type))
		{

			$file = $config['tmprule'].$upload;
			$rs = file::set_tmpfile($file);
			$this->apiOut(array('url'=>$rs));
		}
		else
		{
			$this->apiOut(false);
		}
	}
	
	private function init_upload($type)
	{
		$config = C('upload');
		$path = $config['tmproot'].$config['tmprule'];
		if (!is_dir($path)) mkdir($path);
		import('ORG.Net.UploadFile');
		$config = $config[$type];
		$upload = new UploadFile();
		$upload->maxSize  = $config['size'];
		$upload->allowExts  = $config['ext'];
		$upload->savePath =  $path;
		$rs = $upload->upload();
		if (!$rs) return false;
		$info = $upload->getUploadFileInfo();
		return $info[0]['savename'];
	}
}
