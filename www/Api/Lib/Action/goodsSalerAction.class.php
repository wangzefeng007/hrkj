<?php
class goodsSalerAction extends baseAction
{

	/*
	*	添加商品
	*	@todo		商品缩略图生成与图片尺寸检测
	*/
	public function add()
	{		
		$name = I('name');
		$desc = I('desc');
		$price = I('price');
		$stock = intval(I('stock'));
		$img = I('img');
		$gcid = intval(I('gcid'));
		$status = I('status',1);
		$thumb = get_tmpfile(I('thumb'))?get_tmpfile(I('thumb')):'';
		$this->vaild_params('is_empty',$name,'请填写商品名称');
		$this->vaild_params('is_empty',$desc,'请填写商品描述');
		$this->vaild_params('is_numeric',$price,'请填写合法的商品价格');
		$this->vaild_params('is_empty',$stock,'请填写商品库存');
		$this->vaild_params('is_empty',$img,'请上传商品图片');
		if ($gcid)
		{
			$this->vaild_params(array($this->loadModel('goodsCategory'),'checkGcid'),$gcid,'您选择的分类不在');
		}
		$data = array();
		$data['usid'] = $this->usid;
		$data['name'] = trim($name);
		$data['desc'] = trim($desc);
		$data['price'] = $price;
		$data['stock'] = $stock;
		$data['img'] = '';
		$data['gcid'] = $gcid;
		$data['status'] = $status;
		$data['thumb'] = $thumb;
		$data['addtime'] = time();
		foreach($img as $value)
		{
			$file = get_tmpfile($value);
			if (!$file) continue;
			$data['img'] = $file.",";
		}
		$this->vaild_params('is_empty',$data['img'],'请上传商品图片');
		$data['img'] = substr($data['img'],0,-1);
		$rs = $this->loadModel('goodsSaler')->add($data);
		$this->apiOut($rs,false);
	}
	
	//
	//	获取店铺商品接口
	//
	public function lists()
	{
		$status = intval(I('status'));
		$where = array('usid'=>$this->usid);
		if ($status) $where['status'] = $status;
		$field = 'id,name,desc,price,stock,thumb,img,status,addtime';
		$data = $this->loadModel('goodsSaler')->getList($field,$where,' id desc',true);
		if (!$data['list'])
		{
			$this->apiOut(false);
		}
		$data['list'] = format_struct('goods',$data['list'],true);
		return $this->apiOut($data);
	}

}