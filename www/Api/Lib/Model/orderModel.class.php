<?php
/*
*	订单综合模型
*/
class orderModel extends baseModel
{
	/*
	*	获取订单信息
	*/
	function getDetail($order_sn,$field = "*")
	{
		$model = $this->initModel($order_sn);
		if (!$model) return false;
		$rs = $model->getInfo($field,array('sn'=>$order_sn));
		return $rs;
	}
	
	/*
	*	订单付款
	*	@param		string		$sn			订单号
	*	@param		int			$oplid	支付id
	*	@todo	付款后流程
	*		1、当面付款订单 ，代销用户收入增加，增加金额为订单金额
	*		2、店铺订单，分两种情况
	*				一、订单为普通订单的，代销用户收入增加，增加金额为订单金额
	*				二、订单为代销订单的，代销用户收入增加，增加加金额为 佣金金额-上级分润-平台抽成
	*														代销用户上级用户收入增加，上级分润为 （佣金金额-平台抽成-代销抽成）*（级别差*1%） 
	*														供应商收入增加，增加金额为 订单金额-佣金金额	
	*														平台收入增加，增加金额为平台抽成
	*		3、升级订单	平台收入增加，增加金额为订单金额*升级比率
	*								上级用户收入增加	
	*/
	function pay($order)
	{
		$model = $this->initModel($order['sn']);
		return $model->pay($order);
	}
	
	/*
	*	检查订单是否付款
	*/
	function checkPay($order)
	{
		$status = isset($order['status_pay'])?$order['status_pay']:$order['status'];
		$rs = ($status == 1)?true:false;
		return $rs;
	}
	
	/*
	*	获取订单类型
	*/
	function getOrderType($sn)
	{
		$_prefix = strtolower(substr($sn,0,2));
		$_typearr  = array('os'=>1,'of'=>2,'ou'=>3);
		return $_typearr[$_prefix];
	}
	
	function initModel($sn)
	{
		$order_type = $this->getOrderType($sn);
		$model = false;
		if ($order_type == 1)
		{
			$model = D('orderShop');
		}
		elseif ($order_type == 2)
		{
			$model = D('orderFtf');
		}
		elseif ($order_type == 3)
		{
			$model = D('orderUpgrade');
		}
		return $model;
	}
}