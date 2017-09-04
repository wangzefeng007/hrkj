<?php
/*
*	商品管理类
*/
class goodsAction extends baseAction
{

	/*
	*	添加商品
	*	@todo		商品缩略图生成与图片尺寸检测
	*/
	public function add()
	{		

		$_params = $this->get_params(array('name','desc','price','stock','img','gcid','status','thumb'));
		
		$this->vaild_params('is_empty',$_params['name'],'请填写商品名称');
		$this->vaild_params('is_empty',$_params['desc'],'请填写商品描述');
		$this->vaild_params('is_numeric',$_params['price'],'请填写合法的商品价格');
		$this->vaild_params('is_empty',$_params['stock'],'请填写商品库存');
		$this->vaild_params('is_empty',$_params['img'],'请上传商品图片');
		$_params['img'] = explode(",",$_params['img']);
		$this->vaild_params('is_empty',$_params['img'],'请上传商品图片');
		// $this->vaild_params(array($this->loadModel('goodsCategory'),'checkGcid'),$_params['gcid'],'您选择的分类不在');
		
		$data = $_params;
		import("@.Tool.file");
		$data['thumb'] = file::tmp_to_final($data['thumb'],'image','goods','copy');
		$imgs = array();
		foreach($data['img'] as $value)
		{
			if (empty($value)) continue;
			$imgs[] = file::tmp_to_final($value,'image','goods');
		
		}

		$data['img'] = implode(",",$imgs);
		$data['usid'] = $this->usid;
		$data['status'] = GOODS_SALE;
		$data['addtime'] = time();
		$rs = $this->loadModel('goods')->add_from_saler($this->usid,$data);
		$this->apiOut($rs,false);
	}
	
	/*
	*	编辑商品
	*	@todo		商品缩略图生成与图片尺寸检测
	*/
	public function edit()
	{		
		$gid = intval(I('gid'));
		$this->vaild_params('is_empty',$gid,'请传入合法的商品id');
		
		$_params = $this->get_params(array('name','desc','price','stock','img','gcid','status','thumb'));
		
		$this->vaild_params('is_empty',$_params['name'],'请填写商品名称');
		$this->vaild_params('is_empty',$_params['desc'],'请填写商品描述');
		$this->vaild_params('is_numeric',$_params['price'],'请填写合法的商品价格');
		$this->vaild_params('is_empty',$_params['stock'],'请填写商品库存');
		$this->vaild_params('is_empty',$_params['img'],'请上传商品图片');
		$_params['img'] = explode(",",$_params['img']);
		$this->vaild_params('is_empty',$_params['img'],'请上传商品图片');
		
		$fields = array('goods_saler'=>'usid,gid','goods'=>'upid');
		$join = array('goods','gid','id');
		$goods = $this->loadModel('goodsSaler')->getJoinInfo($fields,$join,array('usid'=>$this->usid));
		$this->vaild_params('is_empty',$goods,'商品不存在，您无法编辑该商品');
		$this->vaild_params('eq',array($goods['upid'],0),'该商品为供应商商品，无法编辑');
		
		$data = $_params;
		import("@.Tool.file");
		$data['thumb'] = file::tmp_to_final($data['thumb'],'image','goods','copy');
		$imgs = array();
		foreach($data['img'] as $value)
		{
			$imgs[] = file::tmp_to_final($value,'image','goods');
		
		}

		$data['img'] = implode(",",$imgs);
		$data['addtime'] = time();
		$rs = $this->loadModel('goods')->update($data,array('id'=>$gid));
		$this->apiOut($rs,false);
	}
	
	/*
	*	删除商品
	*/
	public function delete()
	{
		// $gid = intval(I('gid'));
		$gid = explode(",",I('gid'));
		$this->vaild_params('is_empty',$gid,'请传入合法的商品id');
		$rs = 	$this->loadModel('goodsSaler')->del(array('usid'=>$this->usid,'gid'=>array('in',$gid)));
		if ($rs>0)
		{
			$this->loadModel('goods')->del(array('id'=>array('in',$gid),'upid'=>0));
		}
		$rs = ($rs === false)?false:true;
		$this->apiOut($rs,false);
	}
	
	/*
	*	获取店铺商品接口
	*/
	public function lists()
	{
		$status = intval(I('status',1));
		$where = array('usid'=>$this->usid);
		if ($status) $where['goods.status'] = $status;
		$field = array();
		$field['goods'] = 'id,upid,name,desc,price,stock,thumb,img,status,addtime';
		$join = array('goods','gid','id','inner join');
		$data = $this->loadModel('goodsSaler')->getJoinList($field,$join,$where,' id desc',true);
		if (!$data['list'])
		{
			return $this->apiOut(false);
		}
		$server = $this->_server();
		$host = $server['HTTP_HOST'];
		foreach($data['list'] as $key=>$value)
		{
			$data['list'][$key]['wap'] = "http://".$host."/wap.php/web/goods/gid/".$value['id']."/usid/".$this->usid; 
		}
		$data['list'] = format_struct('goods',$data['list'],true);
		return $this->apiOut($data);
	}
	
	/*
	*	获取单个商品详情接口
	*/
	public function info()
	{
		$gid = intval(I('gid'));
		$status = intval(I('status',1));
		$where = array('id'=>$gid);
		if ($status) $where['status'] = $status;
		$data = $this->loadModel('goods')->getInfo('*',$where);
		if (!$data)
		{
			return $this->apiOut(false);
		}
		return $this->apiOut($data);
	}

	/*
	*	供应商商品列表	
	*/
	public function provider()
	{
		$_params = $this->get_params(array('upid','gcid','keyword','order_type','order_sort'));
		$where = array('status'=>GOODS_SALE);
		$where['upid'] = $_params['upid']?$_params['upid']:array('gt',0);
		if ($_params['gcid']) $where['gcid'] = $_params['gcid'];
		if ($_params['keyword']) $where['name'] = array('like','%'.$_params['keyword'].'%');
		
		$fields = 'id,upid,name,desc,price,stock,sale_count,thumb,img,status,addtime,commission_rate';
		$order = ($_params['order_type'] && $_params['order_sort'])?($_params['order_type']." ".$_params['order_sort']):'id desc';
		$goods = $this->loadModel('goods')->getList($fields,$where,$order,true);
		$goods_saler = $this->loadModel('goodsSaler')->getList('usid,gid',array('usid'=>$this->usid));
		$goods_saler = reset_array_key($goods_saler['list'],'gid');
		$upid = array();
		foreach($goods['list'] as $key=>$value)
		{
			$goods['list'][$key]['is_add'] = isset($goods_saler[$value['id']])?1:0;
			$upid[] = $value['upid'];
		}
		$user_provider = $this->loadModel('userProvider')->getList('id,platform_rate',array('id'=>array('in',$upid)));
		$up_rate =reset_array_key($user_provider['list'],'id');
		foreach($goods['list'] as $key=>$value)
		{
			$commission = $value['price']*($value['commission_rate']-$up_rate[$value['upid']][$platform_rate]);
			$goods['list'][$key]['commission'] = $commission;
			unset($goods['list'][$key]['commission_rate']);
		}
		$goods['add_count'] = count($goods_saler);
		$this->apiOut($goods);
	}
	
	/*
	*	从供应商提供的商品中添加店铺商品
	*/
	public function add_from_provider()
	{
		$gid = I('gid');
		$gid = explode(",",$gid);
		$goods_saler = $this->loadModel('goodsSaler')->getList('id,gid',array('usid'=>$this->usid,'gid'=>array('IN',$gid)));
		$goods_saler_exist = reset_array_key($goods_saler['list'],'gid');
		$goods = $this->loadModel('goods')->getList('id,upid',array('status'=>GOODS_SALE,'id'=>array('in',$gid)));

		if ($goods['list'])
		{
			foreach($goods['list'] as $value)
			{
				if (isset($goods_saler_exist[$value['id']])) continue;
				$data = array('usid'=>$this->usid,'upid'=>$value['upid'],'gid'=>$value['id']);
				$rs = $this->loadModel('goodsSaler')->add($data);
			}
		}
		// $this->vaild_params('is_empty',$rs,'您已经添加过该商品');
		$this->apiOut($rs,false);
	}


	
	/*
	* 商品分类
	*/
	public function category()
	{
		$categories = $this->loadModel('goodsCategory')->get_categories();
		$this->apiOut($categories);
	}
	

}
