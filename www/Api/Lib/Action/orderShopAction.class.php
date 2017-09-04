<?php
class orderShopAction extends baseAction
{
	public function lists()
	{
		$status = I('status',-1);
		$where = array('usid'=>$this->usid);
		if ($status>=0)
		{
			$status = explode(",",$status);
			
			$where['status'] = array('in',$status);
		}
		$rs = $this->loadModel('orderShop')->getList('sn,status,money,addtime,goods',$where,'id desc',true);
		foreach($rs['list'] as &$value)
		{
			$value['goods'] = unserialize($value['goods']);
		}
		$response = $rs['list']?$rs:false;
		$this->apiOut($response);
	}

	/*
	*	添加店铺订单接口
	*/
	public function add()
	{
		$err_msg = $this->loadModel('userSaler')->checkStatus($this->usid);
		if ($err_msg)
		{
			$this->response(PARAMS_ERROR,$err_msg);
		}
		$field = array('name','mobile','province','city','area','address','message','weixin');
		$receiver = $this->get_params($field);
		
		$this->vaild_params('is_empty',$receiver['name'],'请填写购买人姓名');
		$this->vaild_params('is_empty',$receiver['mobile'],'请填写联系电话');
		$this->vaild_params('is_empty',$receiver['province'],'请选择收货省份');
		$this->vaild_params('is_empty',$receiver['city'],'请选择配收货城市');
		$this->vaild_params('is_empty',$receiver['area'],'请选择配收货地区');
		$this->vaild_params('is_empty',$receiver['address'],'请选择收货详细地址');
		$this->vaild_params('is_empty',$receiver['city'],'请选择配货的城市');
		$this->vaild_params('is_empty',I('goods'),'请选择要购买的商品');
		$_goods = json_decode(htmlspecialchars_decode(stripslashes(I('goods'))),true);
		$this->vaild_params('eq',array($_goods,false),'goods参数不合法',false);
		
		$order = array();
		foreach($_goods as $goods)
		{
			if (!$goods['gsid'] || !$goods['num']) 
			{
				$this->response(PARAMS_ERROR,'goods参数不合法');
			}
			$field = array('goods_saler'=>'usid,id as gsid','goods'=>'id as gid,thumb,name,price,stock,status,upid,commission_rate');
			$join = array('goods','gid','id','inner join');
			$where = array('goods_saler.id'=>$goods['gsid']);
			$goods_info = $this->loadModel('goodsSaler')->getJoinInfo($field,$join,$where);
			
			if (!$goods_info)
			{
				$this->response(DATA_EMPTY,"您购买的商品不存在或已下架，请重新选择商品");
			}
			if ($goods_info['status'] != 1)
			{
				$this->response(DATA_EMPTY,'您购买的商品'.$goods_info['name']."已下架，请重新选择商品");
			}
			if ($goods_info['stock']<$goods['num'])
			{
				$this->response(DATA_EMPTY,'您购买的商品'.$goods_info['name']."库存不足,目前最多能购买".$goods_info['stock']."，无法购买");
			}
			$upid = $goods_info['upid'];

			$data = array('gsid'=>$goods_info['gsid'],
									'price'=>$goods_info['price'],
									'num'=>$goods['num'],
									'usid'=>$goods_info['usid'],
									'upid'=>$upid,
									'name'=>$goods_info['name'],
									'gid'=>$goods_info['gid'],
									'thumb'=>$goods_info['thumb'],
									'commission_rate'=>$goods_info['commission_rate']);

			if ($upid>0)
			{
				$upinfo = $this->loadModel('userProvider')->getInfoByid($upid,'platform_rate');

				if ($upinfo)
				{
					$data['platform_rate'] = $upinfo['platform_rate'];
				}	
			}
			else			
			{
				$data['platform_rate'] = 0.0000;
			}

			
			$order[$upid][] = $data;
		}	

		$orders = $this->loadModel('orderShop')->createOrder($this->usid,$this->usinfo,$this->usinfo['lsid'],$order,$receiver);
		$response = $orders?array('sn'=>$orders):false;
		$this->apiOut($orders);		
	}
	
	//订单详情
	public function info()
	{
		$sn = I('sn');
		$this->vaild_params('is_empty',$sn,'请填写要查询的订单号');
		$order = $this->loadModel('orderShop')->info($sn);
		if ($order['usid'] != $this->usid)
		{
			$this->response(DATA_EMPTY,'您无法访问该订单');
		}
		unset($order['usid']);
		return $this->apiOut($order);
	}
	

	
}