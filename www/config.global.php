<?php
//全局配置项
$config = array(
	'STATUS'=>array(
		'0'=>'未启用',
		'1'=>'已启用'
	),
	'ORDER_STATUS'		=> array(
		ORDER_WAIT_PAY =>  '待付款',
		ORDER_WAIT_SEND =>  '已付款待发货',
		ORDER_WAIT_AFFIRM =>  '已发货待确认收货',
		ORDER_FINISH =>  '订单完成',
		ORDER_RETURN =>  '退货',
		ORDER_CLOSE =>  '订单关闭'
	),
	'PAY_STATUS'	=> array(
		PAY_UNPAY=>"未付款",
		PAY_PAY=>"已付款",
		PAY_RETURN=>"退款中",
		PAY_RETURN_FINISH=>"退款完成"	
	),
	'SEND_STATUS'=>array(
		SEND_UNSEND=>"未发货",
		SEND_SEND=>"已发货",
		SEND_RECEIVE=>"已收货",
		SEND_RETURN=>"退货中",
		SEND_RETURN_FINSH=>"退货完成"
	),
	'GOODS_STATUS'=>array(
		GOODS_UNSALE=>'下架',
		GOODS_SALE=>'销售中',
		GOODS_WAIT_AUDIT=>'待审核',
		GOODS_AUDIT_FAIL=>'审核失败'
	),
	'ORDER_TYPE'=>array(
		ORDER_SHOP=>'店铺收入',
		ORDER_FTF=>'当面收款',
		ORDER_UPGRADE=>'用户升级',
	),
	'INCOME_TYPE'=>array(
		INCOME_SHOP=>'店铺收入',
		INCOME_FTF=>'当面收款',
		INCOME_SPLIT=>'普通分润',
		INCOME_COMMISSION=>'佣金收入',
		INCOME_COMMISSION_SPLIT=>'佣金分润',
		INCOME_UPGRADE_SPLIT=>'升级分润',
		INCOME_SALE_BACK=>'分销返现'
	),
	'USER_SALER_STATUS'=>array(
		USER_SALER_NORMAL=>'已开通',
		USER_SALER_UNAUDIT=>'待审核',
		USER_SALER_PROFILE_MISS=>'资料未完善',
		USER_SALER_FREEZE=>'已冻结',
        USER_SALER_NOPASS=>'审核不通过'
	),
	'CASH_STATUS'=>array(
		CASH_STATUS_UNSEND=>'未结算',
		CASH_STATUS_SEND=>'已结算',
		CASH_STATUS_PROCESS=>'处理中',
		CASH_STATUS_INPAY=>'出账中',
		CASH_STATUS_SENDFAIL=>'结算失败',
		CASH_STATUS_SUBMITFAIL=>'提交失败',
	),
	'PLATFORM_INCOME'=>array(
		PLATFORM_INCOME_PROVIDER=>'供应商销售抽成 ',
		PLATFORM_INCOME_UPGRADE=>'用户升级 ',
		PLATFORM_INCOME_CASH=>'用户提现'
	),
);

return $config;
