<?php 
$config = array();

// 自动生成代码配置
//供应商
/*
$config['user_provider'] = array(
	'Controller'=>'userProvider',				//控制器名称
	'Model'=>'userProvider',						//模型名称
	'Table'=>'user_provider',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'供应商商户管理',						//视图标题
			'subtitle'=>''	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加供应商',				//操作项名称
					'href'=>'{:U("userProvider/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('登录帐号','姓名','电话','状态','添加时间'),			//列表标题
				'data'=>array('username','name','tel','status|status_desc="STATUS",###','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			),
			'form'=>array(				//表单数据
				'action'=>'',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',					//表单所采用的模版
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'登录帐号',			//表单字段描述信息
						'name'=>'username',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'姓名',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'电话',			//表单字段描述信息
						'name'=>'tel',		//input名称
						'type'=>'text'			//input类型		
					)				
				)
			)
		),
		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'供应商商户管理',
			'subtitle'=>'添加/编辑供应商',
			'form'=>array(				//表单数据
				'action'=>'{:U("userProvider/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'登录帐号',			//表单字段描述信息
						'name'=>'username',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写登录帐号！'
					),
					array(
						'desc'=>'登录密码',			//表单字段描述信息
						'name'=>'password',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写登录密码！'
					),
					array(
						'desc'=>'姓名',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写姓名！'
					),
					array(
						'desc'=>'联系电话',			//表单字段描述信息
						'name'=>'tel',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请联系电话！'
					),
					array(
						'desc'=>'身份证号码',			//表单字段描述信息
						'name'=>'card_no',		//input名称
						'type'=>'text',			//input类型		
					),
					array(
						'desc'=>'银行名称',			//表单字段描述信息
						'name'=>'bank',		//input名称
						'type'=>'text',			//input类型		
					),
					array(
						'desc'=>'银行帐号',			//表单字段描述信息
						'name'=>'bank_no',		//input名称
						'type'=>'text',			//input类型		
					),
					array(
						'desc'=>'状态',			//表单字段描述信息
						'name'=>'status',		//input名称
						'type'=>'radio',			//input类型
						'choices'=>array('0'=>'未开启','1'=>'已开启'),				//	可选项		
						'required'=>'请选择状态！'
					)
				)
			)
		)
	)	
);


//商品分类管理
$config['goods_category'] = array(
	'Controller'=>'goodsCategory',				//控制器名称
	'Model'=>'goodsCategory',						//模型名称
	'Table'=>'goods_category',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'商品分类管理',						//视图标题
			'subtitle'=>'分类列表'	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加商品分类',				//操作项名称
					'href'=>'{:U("goodsCategory/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('分类名称','状态','添加时间'),			//列表标题
				'data'=>array('name','status|status_desc="STATUS",###','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			),
			'form'=>array(				//表单数据
				'action'=>'',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',					//表单所采用的模版
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text'			//input类型		
					),
			
				)
			)
		),
		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'商品分类管理',
			'subtitle'=>'添加/编辑商品分类',
			'form'=>array(				//表单数据
				'action'=>'{:U("goodsCategory/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'分类名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写分类名称！'
					),
					array(
						'desc'=>'状态',			//表单字段描述信息
						'name'=>'status',		//input名称
						'type'=>'radio',			//input类型
						'choices'=>array('0'=>'未开启','1'=>'已开启'),				//	可选项		
						'required'=>'请选择状态！'
					)
				)
			)
		)
	)	
);

//支付方式管理
$config['pay_type'] = array(
	'Controller'=>'payType',				//控制器名称
	'Model'=>'payType',						//模型名称
	'Table'=>'pay_type',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'支付方式管理',						//视图标题
			'subtitle'=>'支付方式列表'	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加支付方式',				//操作项名称
					'href'=>'{:U("payType/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('名称','支付下限','支付上限','状态','添加时间'),			//列表标题
				'data'=>array('name','min','max','status|status_desc="STATUS",###','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			),
			'form'=>array(				//表单数据
				'action'=>'',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',					//表单所采用的模版
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'支付方式名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text'			//input类型		
					),
			
				)
			)
		),
		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'支付方式管理',
			'subtitle'=>'添加/编辑支付方式',
			'form'=>array(				//表单数据
				'action'=>'{:U("payType/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'支付方式名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写分类名称！'
					),
					array(
						'desc'=>'支付下限',			//表单字段描述信息
						'name'=>'min',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写支付下限！'
					),
					array(
						'desc'=>'支付上限',			//表单字段描述信息
						'name'=>'max',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写支付上限！'
					),
					array(
						'desc'=>'状态',			//表单字段描述信息
						'name'=>'status',		//input名称
						'type'=>'radio',			//input类型
						'choices'=>array('0'=>'未开启','1'=>'已开启'),				//	可选项		
						'required'=>'请选择状态！'
					)
				)
			)
		)
	)	
);

//提现方式管理
$config['cash_type'] = array(
	'Controller'=>'cashType',				//控制器名称
	'Model'=>'cashType',						//模型名称
	'Table'=>'cash_type',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'提现方式管理',						//视图标题
			'subtitle'=>'提现方式列表'	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加提现方式',				//操作项名称
					'href'=>'{:U("cashType/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('名称','提现下限','提现上限','状态','添加时间'),			//列表标题
				'data'=>array('name','min','max','status|status_desc="STATUS",###','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			),
			'form'=>array(				//表单数据
				'action'=>'',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',					//表单所采用的模版
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'提现方式名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text'			//input类型		
					),
			
				)
			)
		),
		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'提现方式管理',
			'subtitle'=>'添加/编辑提现方式',
			'form'=>array(				//表单数据
				'action'=>'{:U("cashType/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'提现方式名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写分类名称！'
					),
					array(
						'desc'=>'提现下限',			//表单字段描述信息
						'name'=>'min',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写提现下限！'
					),
					array(
						'desc'=>'提现上限',			//表单字段描述信息
						'name'=>'max',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写提现上限！'
					),
					array(
						'desc'=>'状态',			//表单字段描述信息
						'name'=>'status',		//input名称
						'type'=>'radio',			//input类型
						'choices'=>array('0'=>'未开启','1'=>'已开启'),				//	可选项		
						'required'=>'请选择状态！'
					)
				)
			)
		)
	)	
);

//费用级别管理
$config['level_fee'] = array(
	'Controller'=>'levelFee',				//控制器名称
	'Model'=>'levelFee',						//模型名称
	'Table'=>'level_fee',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'费用级别管理',						//视图标题
			'subtitle'=>'费用级别列表'	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加费用级别',				//操作项名称
					'href'=>'{:U("levelFee/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('名称','状态','添加时间'),			//列表标题
				'data'=>array('name','status|status_desc="STATUS",###','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			),
			
		),
		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'费用级别管理',
			'subtitle'=>'添加/编辑费用级别',
			'form'=>array(				//表单数据
				'action'=>'{:U("levelFee/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'费用级别名称',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写分类名称！'
					),
					array(
						'desc'=>'状态',			//表单字段描述信息
						'name'=>'status',		//input名称
						'type'=>'radio',			//input类型
						'choices'=>array('0'=>'未开启','1'=>'已开启'),				//	可选项		
						'required'=>'请选择状态！'
					)
				)
			)
		)
	)	
);

//代销用户
$config['user_saler'] = array(
	'Controller'=>'userSaler',				//控制器名称
	'Model'=>'userSaler',						//模型名称
	'Table'=>'user_saler',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'销售商户管理',						//视图标题
			'subtitle'=>''	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加销售',				//操作项名称
					'href'=>'{:U("userSaler/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('登录帐号','姓名','电话','状态','添加时间'),			//列表标题
				'data'=>array('username','name','tel','status|status_desc="STATUS",###','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			),
			'form'=>array(				//表单数据
				'action'=>'',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',					//表单所采用的模版
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'登录帐号',			//表单字段描述信息
						'name'=>'username',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'姓名',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'电话',			//表单字段描述信息
						'name'=>'tel',		//input名称
						'type'=>'text'			//input类型		
					)				
				)
			)
		),
		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'销售商户管理',
			'subtitle'=>'添加/编辑销售',
			'form'=>array(				//表单数据
				'action'=>'{:U("userSaler/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'登录帐号',			//表单字段描述信息
						'name'=>'username',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写登录帐号！'
					),
					array(
						'desc'=>'登录密码',			//表单字段描述信息
						'name'=>'password',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写登录密码！'
					),
					array(
						'desc'=>'姓名',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写姓名！'
					),
					array(
						'desc'=>'上级商户',			//表单字段描述信息
						'name'=>'pid',		//input名称
						'type'=>'select',			//input类型		
						'databind'=>array(									//数据绑定
							'method'=>array('add','edit'),
							'datamodel'=>array(
								'model'=>'userSaler',
								'method'=>'getList',
								'params'=>'"","id,name"'
							)
						)
					),
					array(
						'desc'=>'联系电话',			//表单字段描述信息
						'name'=>'tel',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请联系电话！'
					),
					array(
						'desc'=>'身份证号码',			//表单字段描述信息
						'name'=>'card_no',		//input名称
						'type'=>'text',			//input类型		
					),
					array(
						'desc'=>'银行名称',			//表单字段描述信息
						'name'=>'bank',		//input名称
						'type'=>'text',			//input类型		
					),
					array(
						'desc'=>'银行帐号',			//表单字段描述信息
						'name'=>'bank_no',		//input名称
						'type'=>'text',			//input类型		
					),
					array(
						'desc'=>'状态',			//表单字段描述信息
						'name'=>'status',		//input名称
						'type'=>'radio',			//input类型
						'choices'=>array('0'=>'未开启','1'=>'已开启'),				//	可选项		
						'required'=>'请选择状态！'
					)
				)
			)
		)
	)	
);

//模块管理
$config['module'] = array(
	'Controller'=>'module',				//控制器名称
	'Model'=>'module',						//模型名称
	'Table'=>'module',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'模块管理',						//视图标题
			'subtitle'=>''	,										//子标题
			'action'=>array(			//操作项
				array(
					'title'=>'添加模块',				//操作项名称
					'href'=>'{:U("module/add")}'				//链接
				)
			),					
			'list'=>array(					//数据列表
				'title'=>array('模块名称','module','action','添加时间'),			//列表标题
				'data'=>array('name','module','action','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			)
		),	

		'save'=>array(
			'tpl'=>'view.html',
			'title'=>'添加管理菜单模块',
			'subtitle'=>'添加/编辑销售',
			'form'=>array(				//表单数据
				'action'=>'{:U("module/save")}',				//表单提交地址
				'method'=>'post',			//表单方法类型 get or post
				'tpl'=>'edit',				//表单所采用的模版类型
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'模块名',			//表单字段描述信息
						'name'=>'name',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写模块名！'
					),
					array(
						'desc'=>'父模块',			//表单字段描述信息
						'name'=>'pid',		//input名称
						'type'=>'select',			//input类型		
						'databind'=>array(									//数据绑定
							'method'=>array('add','edit'),
							'datamodel'=>array(
								'model'=>'module',
								'method'=>'getList',
								'params'=>'"","id,name"'
							)
						)
					),
					array(
						'desc'=>'module',			//表单字段描述信息
						'name'=>'module',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写module！'
					),
					array(
						'desc'=>'action',			//表单字段描述信息
						'name'=>'action',		//input名称
						'type'=>'text',			//input类型		
						'required'=>'请填写action'
					),
					array(
						'desc'=>'是否菜单显示',			//表单字段描述信息
						'name'=>'is_menu',		//input名称
						'type'=>'radio',			//input类型	
						'choices'=>array('0'=>'不显示','1'=>'显示')
					),
					array(
						'desc'=>'排序值',			//表单字段描述信息
						'name'=>'sort',		//input名称
						'type'=>'text'			//input类型		
					)
				)
			)
		)
	)	
);
*/

//店铺交易订单列表
/*
$config['order_shop'] = array(
	'Controller'=>'orderShop',				//控制器名称
	'Model'=>'orderShop',						//模型名称
	'Table'=>'order_shop',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'店铺交易订单',						//视图标题
			'subtitle'=>'订单列表'	,										//子标题
	
			'form'=>array(				//表单数据
				'action'=>'{:U("orderShop/index")}',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',				//表单所采用的模版类型	search or edit
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'订单号',			//表单字段描述信息
						'name'=>'sn',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'代销商户',			//表单字段描述信息
						'name'=>'us_name',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'供应商',			//表单字段描述信息
						'name'=>'up_name',		//input名称
						'type'=>'text'			//input类型		
					),

				)
			),	
			'list'=>array(					//数据列表
				'title'=>array('订单号','订单金额','佣金','代销商户','供应商','支付通道','订单状态','添加时间','付款时间'),			//列表标题
				'data'=>array('sn','money','commission','us_name','up_name','pc_name','status|status_desc="ORDER_STATUS",###','addtime|date="Y-m-d H:i:s",###','paytime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			)
		)
	)	
);

//当面交易订单
$config['order_ftf'] = array(
	'Controller'=>'orderFtf',				//控制器名称
	'Model'=>'orderFtf',						//模型名称
	'Table'=>'order_ftf',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'当面交易订单',						//视图标题
			'subtitle'=>'订单列表'	,										//子标题
	
			'form'=>array(				//表单数据
				'action'=>'{:U("orderFtf/index")}',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',				//表单所采用的模版类型	search or edit
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'订单号',			//表单字段描述信息
						'name'=>'sn',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'商户名',			//表单字段描述信息
						'name'=>'us_name',		//input名称
						'type'=>'text'			//input类型		
					),
					array(
						'desc'=>'商户电话号码',			//表单字段描述信息
						'name'=>'us_mobile',		//input名称
						'type'=>'text'			//input类型		
					)
				)
			),	
			'list'=>array(					//数据列表
				'title'=>array('订单号','交易金额','商户名','商户电话','交易方式','支付通道','状态','添加时间','付款时间'),			//列表标题
				'data'=>array('sn','money','us_name','us_mobile','pt_name','pc_name','status|status_desc="PAY_STATUS",###','addtime|date="Y-m-d H:i:s",###','paytime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			)
		)
	)	
);

//代销用户收入明细
$config['account_user_saler_income'] = array(
	'Controller'=>'accountUserSalerIncome',				//控制器名称
	'Model'=>'accountUserSalerIncome',						//模型名称
	'Table'=>'account_user_saler_income',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'代销用户收入明细',						//视图标题
			'subtitle'=>''	,										//子标题
			'form'=>array(
				'action'=>'{:U("accountUserSalerIncome/index")}',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',	
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'商户名',			//表单字段描述信息
						'name'=>'us_name',		//input名称
						'type'=>'text',			//input类型		
						
					),
					array(
						'desc'=>'订单号',			//表单字段描述信息
						'name'=>'sn',		//input名称
						'type'=>'text',			//input类型		
						
					),
				)	
			),				
			'list'=>array(					//数据列表
				'title'=>array('订单号','商户名','金额','收入来源','支付方式','支付通道','入账时间'),			//列表标题
				'data'=>array('sn','us_name','money','type','pt_name','pc_name','addtime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			)
		)
	)	
);

//代销用户提取明细
$config['account_user_saler_income'] = array(
	'Controller'=>'accountUserSalerCash',				//控制器名称
	'Model'=>'accountUserSalerCash',						//模型名称
	'Table'=>'account_user_saler_cash',
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'index.html',		//视图模型文件			
			'title'=>'代销用户提现明细',						//视图标题
			'subtitle'=>''	,										//子标题
			'form'=>array(
				'action'=>'{:U("accountUserSalerCash/index")}',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',	
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'商户名',			//表单字段描述信息
						'name'=>'us_name',		//input名称
						'type'=>'text',			//input类型		
						
					),

				)	
			),				
			'list'=>array(					//数据列表
				'title'=>array('商户名','金额','手续费','交易费','实得金额','提现方式','提现通道','状态','申请时间','下发时间'),			//列表标题
				'data'=>array('us_name','money','fee_rate','fee_static','fee_get','ct_name','pc_name','status','addtime|date="Y-m-d H:i:s",###','dispostime|date="Y-m-d H:i:s",###')			//显示数据的字段名
			)
		)
	)	
);
*/
//升级订单
$config['user_saler_account'] = array(
/*
	'Controller'=>'userSalerAccount',				//控制器名称
	'Model'=>'userSalerAccount',						//模型名称
	'Method'=>array(
		'params'=>"'starttime','endtime','ptid','keytype','keyword'",
		
	),
	*/
	'View'=>array(						//视图情况
		'lists'=>array(					//控制器方法名
			'tpl'=>'lists.html',		//视图模型文件			
			'title'=>'收益',						//视图标题
			// 'subtitle'=>'列表'	,										//子标题
	
			'form'=>array(				//表单数据
				'action'=>'{:U("userSalerAccount/lists")}',				//表单提交地址
				'method'=>'get',			//表单方法类型 get or post
				'tpl'=>'search',				//表单所采用的模版类型	search or edit
				'inputs'=>array(		//表单的信息
					array(
						'desc'=>'开始时间',			//表单字段描述信息
						'name'=>'starttime',		//input名称
						'type'=>'time',			//input类型		
					),
					array(
						'desc'=>'结束时间',			//表单字段描述信息
						'name'=>'starttime',		//input名称
						'type'=>'time',			//input类型		
					),
					array(
						'desc'=>'支付方式',			//表单字段描述信息
						'name'=>'ptid',		//input名称
						'type'=>'select'			//input类型	
						'databind'=>array(									//数据绑定
							'datamodel'=>array(
								'model'=>'payType',
								'method'=>'getList',
								'params'=>"id,name"
							)
						)						
					),
					array(
						'desc'=>'商户',			//表单字段描述信息
						'name'=>'keytype',		//input名称
						'type'=>'select'			//input类型
						'choices'=>array(
							'mobile'=>'手机号码',
							'name'=>'姓名'
						)		
					)
				)
			),	
			'list'=>array(					//数据列表
				'title'=>array('商户','帐号','支付方式','结算总额','商户分润','升级分润','佣金分润','分销佣金','分销返现'),			//列表标题
				'data'=>array('name','mobile','pt_name','money','normal_split_money','upgrade_split_money','commission_split_money','commission_money','saler_back_money')			//显示数据的字段名
			)
		)
	)	
);
