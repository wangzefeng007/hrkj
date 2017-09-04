<?php
	
class uploadAction extends Action{

	//编辑器配置自定义上传类
	/* public function imageUp(){
		import('ORG.Net.UploadFile');

		$config = array(
			'maxSize'           =>  2 * 1024*1024,
			'allowExts'         =>  array('jpg', 'png', 'gif'),  // 允许上传的文件后缀
			'autoSub'           =>  true,        // 启用子目录保存文件
			'subType'           =>  'date',      // 子目录创建方式 可以使用hash date custom
        	'dateFormat'        =>  'Ym',
		);
		$upload = new UploadFile($config);
        $editorId = $_GET['editorid'];
		if($upload->upload(C('URL_UPLOADER_TMP'))){
			$info = $upload->getUploadFileInfo();
            $url = $info[0]['savename'];
            echo "<script>parent.UM.getEditor('".$editorId."').getWidgetCallback('image')('".$url."','SUCCESS')</script>";
		}else{
            $msg = $upload->getErrorMsg();
            echo "<script>parent.UM.getEditor('".$editorId."').getWidgetCallback('image')('','".$msg."')</script>";
		}
	} */

	public function upload() {
        if (!empty($_FILES)) {
            //如果有文件上传 上传附件
            $this->_upload();
        }
    }
	
	public function upload1() {
        if (!empty($_FILES)) {
            //如果有文件上传 上传附件
            $this->_upload1();
        }
    }

    // 文件上传
    protected function _upload() {
        //$tempFile = $_FILES['Filedata']['tmp_name'];
        //$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
        //$name = date('YmdHis')."_".rand(1000,9999).'.'.getExt($_FILES['Filedata']['name']);
        //$targetFile =  str_replace('//','/',$targetPath) . $name;
        //move_uploaded_file($tempFile,$targetFile);
        //$_REQUEST['folder']."/$name ";    
    
        import('@.ORG.UploadFile');
        //导入上传类
        $upload = new UploadFile();
        //设置上传文件大小
        $upload->maxSize            = 10 * 1024*1024;
        //设置上传文件类型
        $upload->allowExts          = explode(',', 'jpg,gif,png,jpeg,mp4');
        //设置附件上传目录
        $upload->savePath           = C('URL_UPLOADER_TMP');
        //设置需要生成缩略图，仅对图像文件有效
        $upload->thumb              = true;
        // 设置引用图片类库包路径
        $upload->imageClassPath     = '@.ORG.Image';
        //设置需要生成缩略图的文件后缀
        $upload->thumbPrefix        = 's_';  //生产1张缩略图
        //设置缩略图最大宽度
        $upload->thumbMaxWidth      = '300';
        //设置缩略图最大高度
        $upload->thumbMaxHeight     = '300';
        //设置上传文件规则
        $upload->saveRule           = 'uniqid';
        //删除原图
        $upload->thumbRemoveOrigin  = false;
        if (!$upload->upload()) {
            //捕获上传异常
            //$this->error($upload->getErrorMsg());
            echo '0';
        } else {
            //取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();
            import('@.ORG.Image');
            //给m_缩略图添加水印, Image::water('原文件名','水印图片地址')
            $water = APP_PATH.'Tpl/water.png' | "";
            Image::water($uploadList[0]['savepath'] . 'm_' . $uploadList[0]['savename'], $water);
            $src = $uploadList[0]['savename'];
            echo $src;          
        }
    }
	
	protected function _upload1() { 
        import('@.ORG.UploadFile');
        //导入上传类
        $upload = new UploadFile();
        //设置上传文件大小
        $upload->maxSize            = 10 * 1024*1024;
        //设置上传文件类型
        $upload->allowExts          = explode(',', 'jpg,gif,png,jpeg,mp4');
        //设置附件上传目录
        $upload->savePath           = "./Upload/bank_img/";
        //设置需要生成缩略图，仅对图像文件有效
        $upload->thumb              = true;
        // 设置引用图片类库包路径
        $upload->imageClassPath     = '@.ORG.Image';
        //设置需要生成缩略图的文件后缀
        $upload->thumbPrefix        = 's_';  //生产1张缩略图
        //设置缩略图最大宽度
        $upload->thumbMaxWidth      = '300';
        //设置缩略图最大高度
        $upload->thumbMaxHeight     = '300'; 
        //删除原图
        $upload->thumbRemoveOrigin  = false;
        if (!$upload->upload()) {
            //捕获上传异常
            //$this->error($upload->getErrorMsg());
            echo '0';
        } else {
            //取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();
            import('@.ORG.Image');
            //给m_缩略图添加水印, Image::water('原文件名','水印图片地址')
            $water = APP_PATH.'Tpl/water.png' | "";
            Image::water($uploadList[0]['savepath'] . 'm_' . $uploadList[0]['savename'], $water);
            $src = $uploadList[0]['savename'];
            echo $src;          
        }
    }

    //删除上传的图片
    public function imgdel(){
        $file = I('file');
        if($file){
            $file_delete = C('URL_UPLOADER_TMP').$file;         //合成原图路径
            $file_delete2 = C('URL_UPLOADER_TMP')."s_".$file;   //合成缩略图路径
            if (file_exists($file_delete)) {
                $result = unlink ($file_delete);
            }
            if (file_exists($file_delete2)) {
                $result = unlink ($file_delete2);
            }
            $this->ajaxReturn(1, "删除成功", 1);            
        }        
    }

}
?>