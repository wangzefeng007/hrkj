<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/17
 * Time: 17:59
 */
class bannerAction extends baseAction
{
    /*
	*	默认入口
	*/
    public function index()
    {
        $_params = $this->get_params(array('url'));
        $where = array();
        if ($_params['url']) $where['url'] = array('like',"%{$_params['url']}%");
        $data = $this->loadModel('banner')->getList('*',$where,'id asc',true);
        $this->assign('data',$data);
        $this->assign('_params',$_params);
        $this->display();
    }
    /*
    *	添加banner数据
    */
    public function add()
    {
        if (I('submit')) $this->save();
        $this->display('view');
    }
    /**
     *@desc	 编辑banner数据
     */
    public function edit()
    {
        $id = intval(I('id'));
        $rs = $this->loadModel('banner')->getInfoByid($id);
        $this->assign('rs',$rs);
        $this->display('view');
    }
    /**
     *@desc	 保存banner数据
     */
    public function save()
    {
        $data = array();
        if ($_FILES['images']['size']>0){
            import('ORG.Net.UploadFile');
            $config = $_FILES['images'];
            $config['url'] = '/Upload/banner/';
            $path = ROOT_PATH.$config['url'];
            $upload = new UploadFile();
            $upload->maxSize  = $config['size'];
            $upload->allowExts  = $config['type'];
            $upload->savePath =  $path;
            $rs = $upload->upload();
            if (!$rs) return false;
            $info = $upload->getUploadFileInfo();
            $data['img_url'] =$config['url'].$info[0]['savename'];
        }
        $id = intval(I('id'));
        $_params = $this->get_params(array('url','sort','default'));
        $this->vaild_params('is_empty',$_params['url'],'链接不能为空!');
        $this->vaild_params('is_empty',$_params['sort'],'排序不能为空!');
        $data['url'] = $_params['url'];
        $data['sort'] = $_params['sort'];
        $data['default'] = $_params['default'];
        $data['addtime'] = time();
        $rs = !$id?$this->loadModel('banner')->add($data):$this->loadModel('banner')->update($data,array('id'=>$id));
        $this->ajaxOut($rs,'index');
    }
    /**
     *@desc	 删除banner数据
     */
    public function delete()
    {
        $ids = is_array(I('id'))?I('id'):array(I('id'));
        $data = $this->loadModel('banner')->getList('img_url',array('id'=>array('IN',$ids)));
        foreach ($data['list'] as $value){
            $pic = ROOT_PATH.$value['img_url'];
            if (file_exists($pic)){
                $res = unlink($pic);
            }
        }
        $rs = $this->loadModel('banner')->del(array('id'=>array('IN',$ids)));
        $this->ajaxOut($rs,'index');
    }
    /**
     *@desc 	处理临时上传的图片/视频
     */
    public function imageUp($images)
    {
        import("@.Tool.file");
        if ($images)
        {
            if ($images['video']) $this->vaild_params('is_empty',strpos($images['video'],'.mp4'),'视频必须是mp4格式！');
            foreach($images as $k => $v)
            {
                if (strpos($v,'tmp'))
                {
                    $type = ($k=='video')?'video':'image';
                    $images[$k] = file::tmp_to_final(str_replace('/Upload/tmp/','',$v),$type,'user');
                    //异步文件转码
                    if ($k == 'video') send_task('fileTask','videoTrans',ROOT_PATH.$images[$k]);
                }
            }
        }
        return serialize($images);
    }
}