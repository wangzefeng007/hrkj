<?php
/**
 * @desc 提醒设置
 * Class riskAction
 */
class remindAction extends baseAction
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
    *	添加提醒数据
    */
    public function add()
    {
        if (I('submit')) $this->save();
        $this->display('view');
    }
    /**
     *@desc	 编辑提醒数据
     */
    public function edit()
    {
        $id = intval(I('id'));
        $rs = $this->loadModel('banner')->getInfoByid($id);
        $this->assign('rs',$rs);
        $this->display('view');
    }
    /**
     *@desc	 保存提醒数据
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
     *@desc	 删除提醒数据
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
}