<?php
/*
*	定义全局常量
*/

//定义本站域名
define('DOMAIN','wallet.huirongpay.com');			//服务器域名
define('HOST','http://wallet.huirongpay.com');	//服务器host
define('WAP_DOWN','http://wallet.huirongpay.com/wap.php/reg/download');	//下载页地址
define('APP_SITE','http://wallet.huirongpay.com');  //项目域名 
define('PJ_NAME','汇融钱包');	//项目名称
define('COMPANY','汇融钱包公司');	//公司名称
define('COMP_SHORT','汇融钱包');	//公司简称
define('SMS_NAME','汇融钱包');	//短信后缀
define('SHARE_TITLE','汇融钱包');	//分享页title
define('SHARE_MSG','汇融钱包-刷卡实时到账最低0.32%，分润当天秒结。世界在身边，支付在指尖。');	//分享页msg
define('SHARE_URL','http://wallet.huirongpay.com/wap.php/reg/index');	//分享页/二维码链接
//define('RISK_PACT_LINK','http://huipay.9580buy.com/down/risk_pact.doc');	//风险承诺书下载地址

//APP意见反馈参数
define('APP_OP_TEL','400-699-8890');	//
define('APP_OP_QQ','33000000');	//
define('APP_OP_WEIXIN','j000000');	//

//登录提示弹窗
define('LOGIN_HINT',1);	//登录消息框 0-不弹窗,1-弹窗
// define('LOGIN_HINT_TITLE','汇融钱包通知');	//消息框标题
define('LOGIN_HINT_TITLE','汇融友情提示');	//消息框标题
define('LOGIN_HINT_TITLE_2','汇融钱包警示');	//消息框标题
#define('LOGIN_HINT_MSG','接到银行通知，建设银行2017年6月23日21:30—6月24日19:00，农业银行2017年6月24日21:30—6月24日22:00，光大银行2017年6月24日00:00—6月24日23:59将进行系统维护届时使用相应银行的结算卡将无法到账，请知悉。');
#define('LOGIN_HINT_MSG','交易已经恢复，给您带来的不便,敬请谅解!');    //消息框内容
#define('LOGIN_HINT_MSG','钱包交易出款已恢复，未到账的会在2个工作日补款，对您造成不便，敬请谅解！');
#define('LOGIN_HINT_MSG','尊敬的用户，汇融钱包银联支付暂时限制单笔最高1万，明天恢复大额，请知晓。客服QQ:2371258649  客服热线：4006998890');
define('LOGIN_HINT_MSG','交易不停、分润不止！银联支付开通大额单笔3万，支持花呗、扫码支付。世界在身边、支付在指尖，欢迎体验！客服时间：9:00-18:00，客服QQ：2371258649 客服热线：4006998890');
define('LOGIN_HINT_MSG_2','汇融钱包公告：近期扫码支付大额已上线，祝您刷卡愉快！');	//消息框内容2
define('LOGIN_HINT_BTN','好的');	//消息框按钮文字
//define('LOGIN_HINT_BTN','我知道了，我不会这么做');	//消息框按钮文字
define('LOGIN_HINT_BTN_2','我知道了，我不会这么做');	//消息框按钮文字

//自定义常量
define('DATA_IMAGE','99995');			//交易失败
define('DATA_EXIST','99996');			//数据为空
define('DATA_EMPTY','99997');			//数据为空
define('PARAMS_ERROR','99998');		//参数错误
define('INTERNAL_ERROR','99999');	//服务器内部错误
define('REQUEST_SUCCESS','10000');	//请求成功

//订单相关常量
define("ORDER_WAIT_PAY",0);			//待付款
define("ORDER_WAIT_SEND",1);		//已付款待发货
define("ORDER_WAIT_AFFIRM",2);	//已发货待确认收货
define("ORDER_FINISH",3);	//订单完成
define("ORDER_RETURN",4);				//退货
define("ORDER_CLOSE",5);				//订单关闭
define("PAY_UNPAY",0);					//未付款
define("PAY_PAY",1);							//已付款
define("PAY_RETURN",2);					//退款中
define("PAY_RETURN_FINISH",3);	//退款完成
define("SEND_UNSEND",0);				//未发货
define("SEND_SEND",1);					//已发货
define("SEND_RECEIVE",2);				//已收货
define("SEND_RETURN",3);				//退货中
define("SEND_RETURN_FINSH",4);	//退货完成

//商品状态相关常量
define('GOODS_UNSALE',0);
define('GOODS_SALE',1);
define('GOODS_WAIT_AUDIT',-1);
define('GOODS_AUDIT_FAIL',-2);

// 订单类型
define('ORDER_SHOP',1);							//店铺交易
define('ORDER_FTF',2);								//当面交易
define('ORDER_UPGRADE',3);			//用户升级

// 收入类型
define('INCOME_SHOP',1);							//店铺交易
define('INCOME_FTF',2);								//当面交易
define('INCOME_SPLIT',3);								//商户交易分润
define('INCOME_COMMISSION',4);				//佣金收入
define('INCOME_COMMISSION_SPLIT',5);	//佣金分润
define('INCOME_UPGRADE_SPLIT',6);			//升级分润
define('INCOME_SALE_BACK',7);					//分销返现

//收入状态
define('INCOME_STATUS_FREEZE',0);		//冻结
define('INCOME_STATUS_NORMAL',1);	//正常可提取
define('INCOME_STATUS_REFUND',-1);	//已退款

//提现类型
define('CASH_NORMAL',1);				//普通提现
define('CASH_SPLIT',2);						//分润提现
define('CASH_COMISSION',3);			//佣金提现

//提现状态
define('CASH_STATUS_UNSEND',0);		//未结算
define('CASH_STATUS_SEND',1);				//已结算
define('CASH_STATUS_PROCESS',2);		//处理中
define('CASH_STATUS_INPAY',3);		//出账中，即第三方通道处理中
define('CASH_STATUS_SENDFAIL',-1);	//结算失败
define('CASH_STATUS_SUBMITFAIL',-2);	//提交失败

//商户状态
define('USER_SALER_NORMAL',1);	//	正常状态
define('USER_SALER_UNAUDIT',0);	//	未审核
define('USER_SALER_PROFILE_MISS',2);	//	资料未完善
define('USER_SALER_FREEZE',-1);	//	冻结状态
define('USER_SALER_NOPASS',3);	//	审核不通过

//平台收入类型
define('PLATFORM_INCOME_COMMISSION',1);	//佣金分成
define('PLATFORM_INCOME_UPGRADE',2);			//升级分成
define('PLATFORM_INCOME_CASH',3);			//提现交易所得
// define('PLATFORM_INCOME_NORMAL',3);			//普通交易分成

//费用级别类型
define('LEVEL_FEE_NORMAL',0);	//普通商户
define('LEVEL_FEE_PROXY',1);		//代理商户
define('LEVEL_FEE_CITY',2);			//市代
define('LEVEL_FEE_AREA',3);			//区代

