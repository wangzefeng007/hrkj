<?php
class webAction extends baseAction
{
   public function index()
   {
		$usid = $this->usid;
		$style = I('style',0);
		/*$key        = I('key');
		 $shop       = M('user_saler_shop');
		$saler      = M('user_saler');
		// $goods      = M('goods');
		$shopInfo = $shop->where(array("usid" => $usid))->find();
		$salerInfo = $saler->where(array("id" => $usid))->field('mobile')->find(); */
		
		//获取店铺信息
		$api_shopInfo = A('Api://shop')->info();
		$apiReply = json_decode($api_shopInfo,true);
		if ($apiReply["result"]["code"]=='10000') {
			$shopInfo = $apiReply["data"];
			//判断相应图片是否存在,不存在则赋值为空
			$shopInfo['logo'] = imgCheck($shopInfo['logo']);
			$shopInfo['background'] = imgCheck($shopInfo['background']);
		}

		//获取商品列表
		$api_goodsList = A('Api://goods')->lists();
		$apiReply = json_decode($api_goodsList,true);
		if ($apiReply["result"]["code"]=='10000') {
			$goodsList = $apiReply["data"]["list"];
			foreach ($goodsList as $key => $val) {
				$goodsList[$key]['thumb'] = imgCheck($val['thumb']);
			}
		}

		$this->assign('goodsList',$goodsList);// 赋值数据集
		$this->assign('shopInfo',$shopInfo);
		$this->assign('usid', $usid);
		//var_dump($goodsList);
		$html = 'index';
		if ($style>=1 && $style<=2) {
			$html.="_{$style}";
		}
		$this->display($html); // 输出模板
   }
	
   public function goodsMore()
   {
 		//获取商品列表
		$api_goodsList = A('Api://goods')->lists();
		$apiReply = json_decode($api_goodsList,true);
		if ($apiReply["result"]["code"]=='10000') {
			$goodsList = $apiReply["data"]["list"];
			foreach ($goodsList as $key => $val) {
				$goodsList[$key]['thumb'] = imgCheck($val['thumb']);
			}
		}
		$this->assign('goodsList',$goodsList);
		$this->assign('usid', $this->usid);
		if ($goodsList) $this->display($html);
	}
   
   public function shopInfo(){
		$usid = $this->usid;
		
		//获取店铺信息
		$api_shopInfo = A('Api://shop')->info();
		$apiReply = json_decode($api_shopInfo,true);
		if ($apiReply["result"]["code"]=='10000') {
			$shopInfo = $apiReply["data"];
			//判断相应图片是否存在,不存在则赋值为空
			$shopInfo['logo'] = imgCheck($shopInfo['logo']);
			$shopInfo['background'] = imgCheck($shopInfo['background']);
		}

		$this->assign('shopInfo',$shopInfo);
		$this->display(); // 输出模板
   }

   public function goods(){
   	
   	Vendor('Weixin.jssdk');
   	$jssdk = new JSSDK("wxb6cc57fab9ae5d06", "37a7bf58f449f7363e84894d7943ffee");
   	$this->assign('signPackage',$jssdk->GetSignPackage());
   	
		$usid = $this->usid;
		
		//获取店铺信息
		$api_shopInfo = A('Api://shop')->info();
		$apiReply = json_decode($api_shopInfo,true);
		if ($apiReply["result"]["code"]=='10000') {
			$shopInfo = $apiReply["data"];
			//判断相应图片是否存在,不存在则赋值为空
			$shopInfo['logo'] = imgCheck($shopInfo['logo']);
			$shopInfo['background'] = imgCheck($shopInfo['background']);
		}

		//获取商品详情
		$api_goodsInfo = A('Api://goods')->info();
		$apiReply = json_decode($api_goodsInfo,true);
		if ($apiReply["result"]["code"]=='10000') {
			$goodsInfo = $apiReply["data"];
			$images = explode(',', $goodsInfo['img']);
			if($images) foreach ($images as $key => $val) {
				if (trim($val))
				{
					$images[$key] = imgCheck($val);
				}
				else
				{
					unset($images[$key]);
				}
			}
		}
		$this->assign('images',$images);
		$this->assign('shopInfo',$shopInfo);
		$this->assign('goodsInfo',$goodsInfo);
		$this->assign('usid', $usid);
		$this->display(); // 输出模板
	}
	
	//购物车添加商品
	public function addCart(){
		$usid = $this->usid;
		$gid   = I('gid');
		$num   = I('num');
		//$price = I('price');
		$goods   = M('goods');
		$rs = $goods->where("id='$gid'")->find();
		$name = $rs['name'];
		$price = $rs['price'];
		if(empty($rs)) $this->ajaxReturn(1, '商品不存在！', 0);
		if($rs['stock']<$num) $this->ajaxReturn(1, "库存不足!", 0);
		if(empty($usid)) $this->ajaxReturn(1, '用户不存在！', 0);
		
		//获取商品详情
		$api_goodsInfo = A('Api://goods')->info();
		$apiReply = json_decode($api_goodsInfo,true);
		if ($apiReply["result"]["code"]=='10000') {
			$goodsInfo = $apiReply["data"];
			$goodsInfo['thumb'] = imgCheck($goodsInfo['thumb']);
		}

			$cart = session('cart');
		if (empty($cart)) $cart = array();
		if ($cart['shop'][$usid]) {
			if ($cart['shop'][$usid]['goods'][$gid]){
				$cart['shop'][$usid]['goods'][$gid]['num']+=$num;
			}
			else {
				$cart['shop'][$usid]['goods'][$gid]['name'] = $goodsInfo['name'];
				$cart['shop'][$usid]['goods'][$gid]['thumb'] = $goodsInfo['thumb'];
				$cart['shop'][$usid]['goods'][$gid]['desc'] = $goodsInfo['desc'];
				$cart['shop'][$usid]['goods'][$gid]['price'] = $goodsInfo['price'];
				$cart['shop'][$usid]['goods'][$gid]['num'] = $num;
			}
		}
		else {
			//获取店铺信息
			$api_shopInfo = A('Api://shop')->info();
			$apiReply = json_decode($api_shopInfo,true);
			if ($apiReply["result"]["code"]=='10000') {
				$shopInfo = $apiReply["data"];
			}

			$cart['shop'][$usid]['name'] = $shopInfo['name'];
			$cart['shop'][$usid]['goods'][$gid]['name'] = $goodsInfo['name'];
			$cart['shop'][$usid]['goods'][$gid]['thumb'] = $goodsInfo['thumb'];
			$cart['shop'][$usid]['goods'][$gid]['desc'] = $goodsInfo['desc'];
			$cart['shop'][$usid]['goods'][$gid]['price'] = $goodsInfo['price'];
			$cart['shop'][$usid]['goods'][$gid]['num'] = $num;
		}
		/* $money = $cart['shop'][$usid]['goods'][$gid]['price'] * $num;
		$cart['shop'][$usid]['goods'][$gid]['g_money'] += $money;
		$cart['shop'][$usid]['s_money'] += $money;
		$cart['t_money'] += $money; */
		
		session('cart',$cart);
		//session('cart',NULL);
		$this->ajaxReturn('', '添加成功!', 10000);
	}

	//购物车
	public function cart(){
		$usid = $this->usid;

		$gid  = I('gid');
		$num  = I('num');
		if ($gid) {  //立即购买
			//获取商品详情
			$api_goodsInfo = A('Api://goods')->info();
			$apiReply = json_decode($api_goodsInfo,true);
			if ($apiReply["result"]["code"]=='10000') {
				$goodsInfo = $apiReply["data"];
				$goodsInfo['thumb'] = imgCheck($goodsInfo['thumb']);
			}
			
			//获取店铺信息
			$api_shopInfo = A('Api://shop')->info();
			$apiReply = json_decode($api_shopInfo,true);
			if ($apiReply["result"]["code"]=='10000') {
				$shopInfo = $apiReply["data"];
			}
			
			$cart = array();
			$cart['shop'][$usid]['name'] = $shopInfo['name'];
			$cart['shop'][$usid]['goods'][$gid]['name'] = $goodsInfo['name'];
			$cart['shop'][$usid]['goods'][$gid]['thumb'] = $goodsInfo['thumb'];
			$cart['shop'][$usid]['goods'][$gid]['desc'] = $goodsInfo['desc'];
			$cart['shop'][$usid]['goods'][$gid]['price'] = $goodsInfo['price'];
			$cart['shop'][$usid]['goods'][$gid]['num'] = $num;

			session('buy',$cart);
			session('is_cart',0);
		}
		else {  //查看购物车
			$cart = session('cart');
			session('is_cart',1);
		}
		
		//var_dump($cart);
		$this->assign('cart',$cart);
		$this->assign('is_cart', session('is_cart'));
		$this->assign('usid', $usid);
		$this->display(); // 输出模板
	}

	//购物车商品数量更新
	public function cart_update() {
		$num_id = I('num_id');
		$num = I('num');
		if (session('is_cart')) {
			$arr = explode("_", $num_id);
			$_SESSION['cart']['shop'][$arr[1]]['goods'][$arr[2]]['num'] = $num;
		}
	}

	//购物车商品删除
	public function del_cart() {
		$del_id = I('del_id');
		if (session('is_cart')) {
			$arr = explode("_", $del_id);
			unset ($_SESSION['cart']['shop'][$arr[1]]['goods'][$arr[2]]);
			if (count($_SESSION['cart']['shop'][$arr[1]]['goods']) == 0) {
				unset ($_SESSION['cart']['shop'][$arr[1]]);
			}
			if (count($_SESSION['cart']['shop']) == 0) {
				unset ($_SESSION['cart']);
			}
		}
	}
	//结算购物车,进入收货信息
	public function address(){
		//$cart_id = I('cart_id');
		//$cart_num = I('cart_num');
		$cart_id = $_REQUEST['cart_id'];
		$cart_num = $_REQUEST['cart_num'];
		$is_cart = I('is_cart');
		
		$goods_arr = array();
		foreach ($cart_id as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				$gsinfo = $this->loadModel('goodsSaler')->getInfo('id',array('gid'=>$k2,'usid'=>$this->usid));
				$goods_arr[] = array(
					
					// 'gsid' => $k2,
					'gsid' => $gsinfo['id'],
					'num' => (int)$cart_num[$k1][$k2],
				);
			}
		}
		$goods = json_encode($goods_arr);
		//var_dump($goods);
		$this->assign('goods', $goods);
		$this->assign('usid',$this->usid);
		$this->display();
	}
	
	//结算页面
	public function payment(){
		$sn = I('sn');
		
		//获取订单详情
		$api_orderInfo = A('Api://orderShop')->info();
		$apiReply = json_decode($api_orderInfo,true);
		if ($apiReply["result"]["code"]=='10000') {
			$orderInfo = $apiReply["data"];
		}
		$this->assign('orderInfo', $orderInfo);
		$this->assign('sn', $sn);
		$this->display();
	}

	 //支付跳转
	 public function gotoPay(){
	 	$sn = I('sn');
	 	$usid = I('usid');
	 	$payType = I('payType');
		if (!$sn){
			$this->is_error('缺少订单号参数');
		}
		
		//清除购物车中已购买的商品
		if (session('is_cart')) {
			session('cart',NULL);
		}
		else {
			session('buy',NULL);
		}
		session('is_cart',NULL);
		
		/*header('Content-Type:application/json; charset=utf-8');*/
		if(strtolower($payType)=='alipay'){
			$this->is_error('尚未开通!');
		}
		elseif(strtolower($payType)=='yeepay'){
			$data['url'] = U('yeepay/airwap',array('order_sn'=>$sn));
			$data['url'] = str_replace('wap.php','api.php',$data['url']);
			$this->ajaxReturn($data, '提交成功！', 10000);
		}
		elseif(strtolower($payType)=='baidu'){
			$this->is_error('尚未开通!');
		}
		elseif(strtolower($payType)=='upmppay') {
			$upmp_sn = R('Api://upmp/sdk_web',array($sn));
			if (!$upmp_sn) $this->is_error('错误!tn获取失败!');
			$paylog = M('orderPaylog')->where(array('order_sn'=>$sn))->find();
			$paydata = urlencode(base64_encode("tn=" . $upmp_sn . ",resultURL=" . urlEncode('http://'.$_SERVER['HTTP_HOST'].U('web/index', array('usid'=>$paylog['usid'])).'&pay=') . ",usetestmode=" . 'false'));
			$url='uppay://uppayservice/?style=token&tn='.$upmp_sn.'&paydata='.$paydata;
			$data['url'] = $url;
			$this->ajaxReturn($data, '验证成功！', 10000);
		}
		elseif(strtolower($payType)=='ezfpay') {
			// R('Api://ezfpay/wapPay', array($sn));
			R('Api://ezfpay/wapPay');
		}
        elseif(strtolower($payType)=='weixin'){
			$data['url'] = U('wxpay/pay',array('order_sn'=>$sn));
			$data['url'] = str_replace('wap.php','api.php',$data['url']);
			$this->ajaxReturn($data, '提交成功！', 10000);
		}
	 }


	//注册协议,联系我们,等显示
	public function content(){
		$skey = I('id');
		if ($skey)
		{
			$m   = M('setting_content');
			$rs = $m->where("skey='{$skey}'")->find();
		}
		else
		{
			$rs['stitle']='错误';
			$rs['svalue']='请传入id';
		}
		if (!$rs)
		{
			$rs['stitle']='错误';
			$rs['svalue']='没有相关内容';
		}
		$this->assign('rs', $rs);
		$this->display();
	}


	
}
