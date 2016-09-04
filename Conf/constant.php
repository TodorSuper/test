<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 常量文件
 */

#================= 系统级别常量定义 ===================

// 自动验证的验证条件定义 
	
define( 'MUST_CHECK', 1 );			# 必须存在且验证
define( 'HAVEING_CHECK', 0 );		# 存在则验证
define( 'ISSET_CHECK', 2 );			# 不为空时就验证

// 调用系统定义
define( 'B2B', 'B2B' );				# 终端用户平台标识
define( 'POP', 'POP' );				# 商家平台标识
define('CMS', 'CMS');                           #cms管理平台
define('BOSS','BOSS');				# boss平台


//移动类型
define('IOS', 'IOS');
define('ANDROID','ANDROID');

//版本类型
define('VERSION','VERSION');        # 版本
define('PATCH','PATCH');		    # 补丁

// 字段操作权限类型定义
define('FIELD_WRITE_DENY', 1);		# 禁止写
define('FIELD_WRITE_ALLOW', 2);		# 允许写
define('FIELD_NOT_EMPTY', 3); # 不为空

// 框架用常量
define('CREATE_DG_OBJECT', 1 );		# 创建DG记录对象
define('GET_DG_OBJECT', 2 );		# 获取所有DG记录对象
define('SUB_DG_OBJECT', 3 );		# 写入DG执行过程对象
define('SQL_DG_OBJECT', 4 );		# 写入DG数据对象
define('DG_CALL_OBJECT',  5 );		# 写入DG请求对象
define('DG_RES_OBJECT',   6 );		# 写入DG返回对象


#================= 常用自动验证错误提示定义 ===================

defined( 'PARAMS_ERROR' )     or define( 'PARAMS_ERROR', 5 ); # 参数错误 ==> error.php => error[4]

#================= 用户编码相关常量 ===================

defined( 'UC_USER' )     or define( 'UC_USER', 1 ); # 用户中心用户

defined( 'UC_USER_MERCHANT' )     or define( 'UC_USER_MERCHANT', 1 ); # 商户
defined( 'UC_USER_SUB_ACCOUNT' )     or define( 'UC_USER_SUB_ACCOUNT', 4); # 商户
defined( 'UC_USER_MERMBER' )     or define( 'UC_USER_MERMBER', 2 ); # 会员
defined( 'UC_USER_CMS' )     or define( 'UC_USER_CMS', 3 ); # 会员
defined( 'UC_USER_FEEDBACK' )     or define( 'UC_USER_FEEDBACK', 5 ); # 反馈

#================= 自增类型 ===================

defined( 'SEQUENCE_ORDER' )     or define( 'SEQUENCE_ORDER', 1 ); # 订单

defined( 'SEQUENCE_USER' )     or define( 'SEQUENCE_USER', 2 ); # 用户

defined( 'SEQUENCE_ACCOUNT' )     or define( 'SEQUENCE_ACCOUNT', 3 ); # 资金账户

defined( 'SEQUENCE_TRADE_NO' )     or define( 'SEQUENCE_TRADE_NO', 4 ); # 交易流水

defined( 'SEQUENCE_CRONTAB_TASK' )     or define( 'SEQUENCE_CRONTAB_TASK', 5 ); # 计划任务

defined( 'SEQUENCE_ITEM' )     or define( 'SEQUENCE_ITEM', 6 ); # 商品编码

defined( 'SEQUENCE_STORE_ITEM' )     or define( 'SEQUENCE_STORE_ITEM', 7 ); # 商家商品编码

defined( 'SEQUENCE_POP' )     or define( 'SEQUENCE_POP', 8 ); # POP平台自增

defined( 'SEQUENCE_CASH_ORDER' )     or define( 'SEQUENCE_CASH_ORDER', 9 ); # 资金相关单号

defined( 'SEQUENCE_INVITE' )     or define( 'SEQUENCE_INVITE', 10 ); # 邀请码 初始化 6 位数 100000

defined( 'SEQUENCE_SPC' )     or define( 'SEQUENCE_SPC', 11 ); # 促销中心模块  初始化 10000000

defined( 'SEQUENCE_FC' )     or define( 'SEQUENCE_FC', 12 ); # 财务中心自增编码

defined( 'SEQUENCE_REMIT' )     or define( 'SEQUENCE_REMIT', 13 ); # 汇款码编码  初始化  1



#================= 子系统来源名称定义 ===================

defined( 'B2B_WEIXIN_SERVER' )     or define( 'B2B_WEIXIN_SERVER', 'b2b_weixin' ); # 终端用户微信平台

defined( 'CMS_PC_SERVER' )     or define( 'CMS_PC_SERVER', 'cms_pc' ); # 后台系统

defined( 'POP_PC_SERVER' )     or define( 'POP_PC_SERVER', 'pop_pc' ); # 商家管理系统

#================= 用户组常量定义 ===================

defined( 'MERCHANT_GROUP' )     or define( 'MERCHANT_GROUP', 1 ); # 商户组

defined( 'MERMBER_GROUP' )     or define( 'MERMBER_GROUP', 2 ); # 会员组

defined( 'SUB_ACCOUNT_GROUP' )     or define( 'SUB_ACCOUNT_GROUP', 4 ); # 商户子账户组

#================= TC交易中心常用常量配置 ===================

//资金账户类型定义
defined( 'TC_ACCOUNT_MERCHANT' )     or define( 'TC_ACCOUNT_MAIN_MERCHANT', 'MERCHANT' ); # 商户资金帐户
defined( 'TC_ACCOUNT_PREPAYMENT' )     or define( 'TC_ACCOUNT_PREPAYMENT', 'PREPAYMENT' ); # 买家和商户的绑定帐户


// 账户编码类型定义
defined( 'TC_ACCOUNT' )     or define( 'TC_ACCOUNT', 1 ); # 提现账户

// 账户二级编码类型定义
defined( 'TC_ACCOUNT_MAIN_MERCHANT' )     or define( 'TC_ACCOUNT_MAIN_MERCHANT', 1 ); # 商家资金账户
defined( 'TC_ACCOUNT_NUM_PREPAYMENT' )     or define( 'TC_ACCOUNT_NUM_PREPAYMENT', 2 ); # 预付款资金账户

// 流水类型定义
defined( 'FLOW_NO' )     or define( 'FLOW_NO', 1); # 流水号

// 账户流水二级编码定义
defined( 'TC_ACCOUNT_TRADE_NO' )     or define( 'TC_ACCOUNT_TRADE_NO', 1 ); # 账户资金流水

// 资金账户操作类型设定 (资金账户状态设定)
defined( 'TC_ACCOUNT_CMS_ENABLED' )     or define( 'TC_ACCOUNT_CMS_ENABLED', 'CMS_ENABLED' ); # 后台管理员激活
defined( 'TC_ACCOUNT_CMS_DISABLED' )     or define( 'TC_ACCOUNT_CMS_DISABLED', 'DISABLED' ); # 后台管理员禁用
defined( 'TC_ACCOUNT_SYS_DISABLED' )     or define( 'TC_ACCOUNT_SYS_DISABLED', 'SYS_DISABLED' ); # 系统禁用账户
defined( 'TC_ACCOUNT_SYS_ENABLED' )     or define( 'TC_ACCOUNT_SYS_ENABLED', 'SYS_ENABLED' ); # 系统启用账户
defined( 'TC_ACCOUNT_INCR_BY_B2B_ORDER' )     or define( 'TC_ACCOUNT_INCR_BY_B2B_ORDER', 'B2B_CASH' ); # b2b 现金支付订单
defined( 'TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE' )     or define( 'TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE', 'P2P_CASH_FREEZE' ); # p2p 申请提现冻结金额
defined( 'TC_ACCOUNT_INCR_BY_P2P_CASH_BACK' )     or define( 'TC_ACCOUNT_INCR_BY_P2P_CASH_BACK', 'P2P_CASH_BACK' ); # 驳回提现申请 释放冻结金额
defined( 'TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE' )     or define( 'TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE', 'P2P_CASH_UNFREEZE' ); # p2p 提现成功释放冻结的金额
defined( 'TC_ACCOUNT_DESC_NONE' )     or define( 'TC_ACCOUNT_DESC_NONE', 'DESC_NONE' ); # 减少部分可用余额, 无业务标识
defined( 'UC_ACCOUNT_POP_ENABLE' )     or define( 'UC_ACCOUNT_POP_ENABLE', 'ENABLE' ); # 后台管理员激活
defined( 'UC_ACCOUNT_POP_DISABLED' )     or define( 'UC_ACCOUNT_POP_DISABLED', 'DISABLED' ); # 后台管理员激活

//预付款余额操作类型设定
defined( 'TC_ACCOUNT_INCR_BY_PREPAYMENT' )     or define( 'TC_ACCOUNT_INCR_BY_PREPAYMENT', 'INCR_PREPAYMENT' ); # 预付款充值
defined( 'TC_ACCOUNT_DESC_BY_PREPAYMENT' )     or define( 'TC_ACCOUNT_DESC_BY_PREPAYMENT', 'DESC_PREPAYMENT' ); # 预付款扣款


// 资金账户余额的变动类型定义
// 资金账户余额的变动类型定义
defined( 'TC_ACCOUNT_INCR' )     or define( 'TC_ACCOUNT_INCR', 'INCR' ); # 后台管理员激活
defined( 'TC_ACCOUNT_DESC' )     or define( 'TC_ACCOUNT_DESC', 'DESC' ); # 后台管理员激活

// 冻结金额记录状态变化定义
defined( 'TC_ACCOUNT_CASH_APPLY' )     or define( 'TC_ACCOUNT_CASH_APPLY', 'APPLY' ); # 提现申请
defined( 'TC_ACCOUNT_CASH_CLOSE' )     or define( 'TC_ACCOUNT_CASH_CLOSE', 'CLOSE' ); # 提现驳回
defined( 'TC_ACCOUNT_CASH_PASS' )     or define( 'TC_ACCOUNT_CASH_PASS', 'PASS' ); # 提现通过

// 支付凭证状态定义
defined( 'TC_PAY_VOUCHER_PAY' )     or define( 'TC_PAY_VOUCHER_PAY', 'PAY' ); # 支付状态
defined( 'TC_PAY_VOUCHER_UNPAY' )     or define( 'TC_PAY_VOUCHER_UNPAY', 'UNPAY' ); # 未支付状态

// 查询交易详情类型定义
/*
defined( 'TC_GET_FREE_ADD' )     or define( 'TC_GET_FREE_ADD', '' );		# 增加的余额
defined( 'TC_GET_FREE_DESC' )     or define( 'TC_GET_FREE_DESC', '' );		# 减少的余额
defined( 'TC_GET_FREEZE_BACK' )     or define( 'TC_GET_FREEZE_BACK', '' );	# 提现失败的余额
defined( 'TC_GET_FREEZE' )     or define( 'TC_GET_FREEZE', '' );			# 冻结金额
defined( 'TC_GET_UNFREEZE' )     or define( 'TC_GET_UNFREEZE', '' );		# 解冻金额 */

// 全局支付方式定义
defined( 'WEIXIN_JSAPI_PAY' )     or define( 'WEIXIN_JSAPI_PAY', 'WEIXIN_JSAPI_PAY' ); # 微信 jsapi 支付
defined( 'ALIPAY_WAP' )     or define( 'ALIPAY_WAP', 'ALIPAY_WAP' );				   # 支付宝wap支付
// 对账单类型定义 
defined( 'STATEMENT_TYPE' )     or define( 'STATEMENT_TYPE', 'ALL' ); # 全量账单


#=================  COM通用服务常用常量配置 ===================
defined( 'CRONTAB_SACN_WAIT' )     or define( 'CRONTAB_SACN_WAIT', 'WAIT' ); # 等待执行
defined( 'CRONTAB_SACN_RUN' )     or define( 'CRONTAB_SACN_RUN', 'RUN' ); # 执行中
defined( 'CRONTAB_SACN_ERR' )     or define( 'CRONTAB_SACN_ERR', 'ERR' ); # 执行失败
defined( 'CRONTAB_SACN_ERR_END' )     or define( 'CRONTAB_SACN_ERR_END', 'ERR_END' ); # 多次执行失败
defined( 'CRONTAB_SACN_END' )     or define( 'CRONTAB_SACN_END', 'END' ); # 执行完毕

// 任务编码一级编码
defined( 'CRONTAB_RUN' )     or define( 'CRONTAB_RUN', 1 ); # 定时任务执行

// 任务编码二级编码
defined( 'CRONTAB_SACN_EXE' )     or define( 'CRONTAB_SACN_EXE', 0 ); # 执行扫描任务

#=================  高级视图常量 ===================
define("SQL_IC", 'Ic');  #商品中心sql 标示
define("SQL_SC", 'Sc');  #商家店铺 sql标示
define("SQL_TC", 'Tc');  #交易中心 sql 标示
define("SQL_UC", 'Uc');  #用户中心 sql 标示
define("SQL_OC", 'Oc');  #订单中心 sql 标示
define("SQL_SPC",'Spc'); #促销中心 sql 标示
define("SQL_FC",'Fc'); #财务中心 sql 标示
define('SQL_BIC', 'Bic'); #统计中心cms sql 表示
define("SQL_APP", 'App'); # BOSS系统配置标识

#=================  订单状态常量 ===================
//订单类型定义
defined( 'OC_B2B_GOODS_ORDER' )     or define( 'OC_B2B_GOODS_ORDER', 'B2B_GOODS' ); # B2B商城订单
defined( 'OC_POP_CASH_ORDER' )     or define( 'OC_POP_CASH_ORDER', 'CASH' );		# pop提现单


// 商城订单的订单状态定义
defined( 'OC_ORDER_ORDER_STATUS_UNCONFIRM' )     or define( 'OC_ORDER_ORDER_STATUS_UNCONFIRM', 'UNCONFIRM' ); # 订单状态   未确认
defined( 'OC_ORDER_ORDER_STATUS_COMPLETE' )     or define( 'OC_ORDER_ORDER_STATUS_COMPLETE', 'COMPLETE' ); # 订单状态   交易完成
defined( 'OC_ORDER_ORDER_STATUS_CANCEL' )     or define( 'OC_ORDER_ORDER_STATUS_CANCEL', 'CANCEL' ); # 订单状态   用户取消订单
defined( 'OC_ORDER_ORDER_STATUS_MERCHCANCEL' )     or define( 'OC_ORDER_ORDER_STATUS_MERCHCANCEL', 'MERCHCANCEL' ); # 订单状态   商家取消订单
defined( 'OC_ORDER_ORDER_STATUS_OVERTIMECANCEL' )     or define( 'OC_ORDER_ORDER_STATUS_OVERTIMECANCEL', 'OVERTIMECANCEL' ); # 订单状态   超时取消订单
defined( 'OC_ORDER_ORDER_STATUS_CHECKED' )     or define( 'OC_ORDER_ORDER_STATUS_CHECKED', 'CHECKED' ); # 审核通过



defined( 'OC_ORDER_PAY_STATUS_UNPAY' )     or define( 'OC_ORDER_PAY_STATUS_UNPAY', 'UNPAY' ); # 订单支付状态   未支付
defined( 'OC_ORDER_PAY_STATUS_PAY' )     or define( 'OC_ORDER_PAY_STATUS_PAY', 'PAY' ); # 订单支付状态   已支付

defined( 'OC_ORDER_SHIP_STATUS_UNSHIP' )     or define( 'OC_ORDER_SHIP_STATUS_UNSHIP', 'UNSHIP' ); # 订单物流状态  未发货
defined( 'OC_ORDER_SHIP_STATUS_SHIPPED' )     or define( 'OC_ORDER_SHIP_STATUS_SHIPPED', 'SHIPPED' ); # 订单物流状态  已发货
defined( 'OC_ORDER_SHIP_STATUS_TAKEOVER' )     or define( 'OC_ORDER_SHIP_STATUS_TAKEOVER', 'TAKEOVER' ); # 订单物流状态  确认收货

#=================  订单组合状态常量 ===================
defined( 'OC_ORDER_GROUP_STATUS_ALL' )     or define( 'OC_ORDER_GROUP_STATUS_ALL', 'ALL' ); #全部订单
defined( 'OC_ORDER_GROUP_STATUS_CANCEL' )     or define( 'OC_ORDER_GROUP_STATUS_CANCEL', 'CANCEL' ); #取消订单
defined( 'OC_ORDER_GROUP_STATUS_UNPAY' )     or define( 'OC_ORDER_GROUP_STATUS_UNPAY', 'UNPAY' ); # 待买家付款
defined( 'OC_ORDER_GROUP_STATUS_UNSHIP')     or define( 'OC_ORDER_GROUP_STATUS_UNSHIP', 'UNSHIP' ); # 待发货
defined( 'OC_ORDER_GROUP_STATUS_UNPAY' )     or define( 'OC_ORDER_GROUP_STATUS_PAY', 'PAY' ); # 待买家付款
defined( 'OC_ORDER_GROUP_STATUS_SHIPPED' )     or define( 'OC_ORDER_GROUP_STATUS_SHIPPED', 'SHIPPED' ); # 已发货
defined( 'OC_ORDER_GROUP_STATUS_TAKEOVER' )     or define( 'OC_ORDER_GROUP_STATUS_TAKEOVER', 'TAKEOVER' ); #  确认收货 
defined( 'OC_ORDER_GROUP_STATUS_COMPLETE' )     or define( 'OC_ORDER_GROUP_STATUS_COMPLETE', 'COMPLETE' ); #  交易成功
defined( 'OC_ORDER_GROUP_STATUS_CHECKED' )     or define( 'OC_ORDER_GROUP_STATUS_CHECKED', 'CHECKED' ); #  审核通过
defined( 'OC_ORDER_GROUP_STATUS_COD_PAY' )     or define( 'OC_ORDER_GROUP_STATUS_COD_PAY', 'COD_PAY' ); #  线下支付
defined( 'OC_ORDER_GROUP_STATUS_TERM_UNPAY' )  or define( 'OC_ORDER_GROUP_STATUS_TERM_UNPAY', 'TERM' ); #  账期待付
defined( 'OC_ORDER_GROUP_STATUS_TRADE' )  or define( 'OC_ORDER_GROUP_STATUS_TRADE', 'TRADE' ); #  账期待付
defined( 'OC_ORDER_GROUP_STATUS_VERIFICATION' ) or define( 'OC_ORDER_GROUP_STATUS_VERIFICATION', 'VERIFICATION');

#================= 订单编码 ===================
defined( 'OC_ORDER' )     or define( 'OC_ORDER', 1 ); # 订单编码
defined( 'OC_ADVANCE_ORDER')		or define('OC_ADVANCE_ORDER', 2);    # 预付款订单编码

defined( 'OC_B2B_ORDER_B2B' )     or define( 'OC_B2B_ORDER_B2B', 1 ); # b2b 订单编码

defined( 'OC_B2B_ORDER_OP' )     or define( 'OC_B2B_ORDER_OP', 2 ); # b2b 订单支付码

defined( 'OC_CASH_ORDER_APPLY' )     or define( 'OC_CASH_ORDER_APPLY', 3 ); # 申请提现单

defined( 'OC_ADVANCE_ORDER_ADV' )     or define( 'OC_ADVANCE_ORDER_ADV', 1 ); # b2b 预付款订单编码
defined( 'OC_ADVANCE_ORDER_OP' ) 	  or define( 'OC_ADVANCE_ORDER_OP', 2);   # b2b 订单支付码

#=============== 订单类型 ===================
defined( 'OC_B2B_GOODS_ORDER' )     or define( 'OC_B2B_GOODS_ORDER', 'B2B_GOODS' ); # B2B商城订单
defined( 'OC_B2B_ADV_ORDER' )     or define( 'OC_B2B_ADV_ORDER', 'ADV' ); # B2B预付款订单
defined( 'OC_POP_CASH_ORDER' )     or define( 'OC_POP_CASH_ORDER', 'CASH' );		# pop提现单


#================= 订单排序 ===================
defined( 'OC_ADVANCE_ORDER_ADV' )     or define( 'OC_ADVANCE_ORDER_ADV', 1 ); # b2b 预付款订单编码
defined( 'OC_ADVANCE_ORDER_OP' ) 	  or define( 'OC_ADVANCE_ORDER_OP', 2);   # b2b 订单支付码

#================ 订货会促销编码 =====================
defined( 'CREATE_TIME_DESC') or define('CREATE_TIME_DESC','DESC'); # 订单下单时间降序
defined( 'CREATE_TIME_ASC')  or define('CREATE_TIME_ASC','ASC'); #订单下单时间升序
defined( 'SPC_COMMODITY_CODE') or define('SPC_COMMODITY_CODE',2); # 订货会促销编码
defined( 'SPC_COMMODITY_MEETING')  or define('SPC_COMMODITY_MEETING',1); #订货会编码
#================ 优惠券促销活动编码 =====================
defined( 'SPC_COUPON_CODE') or define('SPC_COUPON_CODE',3); # 优惠券促销编码
defined( 'SPC_COUPON_ACTIVE')  or define('SPC_COUPON_ACTIVE',1); #促销活动编码
defined( 'SPC_COUPON_COUPON')  or define('SPC_COUPON_COUPON',2); #优惠券批次
defined( 'SPC_COUPON_COUPON_CODE')  or define('SPC_COUPON_COUPON_CODE',3); #优惠券编码
#================ 促销商品编码 =====================
defined( 'SPC_CODE' ) or define( 'SPC_CODE', 1); # 促销商品编码
defined( 'SPC_GIFT' ) or define( 'SPC_GIFT', 1); #促销商品列表编码
defined( 'SPC_SPECIAL' ) or define( 'SPC_SPECIAL', 2); #特价商品编码
defined( 'SPC_LADDER' )  or define( 'SPC_LADDER', 3); #阶梯价编码

#=================  标准库商品状态常量 ===================
defined( 'IC_ITEM_PUBLISH' )      or define( 'IC_ITEM_PUBLISH', 'PUBLISH' );  #发布状态
defined( 'IC_ITEM_EDIT' )      or define( 'IC_ITEM_EDIT', 'EDIT' );  #编辑状态
defined( 'IC_ITEM_DISABLE' )      or define( 'IC_ITEM_DISABLE', 'DISABLE' );  #禁用状态

#=================  商家商品状态常量 ===================
defined( 'IC_STORE_ITEM_ON' )      or define( 'IC_STORE_ITEM_ON', 'ON' );  #上架状态
defined( 'IC_STORE_ITEM_OFF' )      or define( 'IC_STORE_ITEM_OFF', 'OFF' );  #编辑状态


#=================  商品业务标示 ===================
defined( 'IC_ITEM' )      or define( 'IC_ITEM', 1 );  #商品标示
defined( 'IC_STANDARD_ITEM' )      or define( 'IC_STANDARD_ITEM', 1 );  #商品预留标示
defined( 'IC_STORE_ITEM' )      or define( 'IC_STORE_ITEM', 2 );  #商品预留标示

#=================  订单购买来源 ===================
defined( 'OC_B2B_WEIXIN' )      or define( 'OC_B2B_WEIXIN', 'WEIXIN' );  #微信购买
defined( 'OC_B2B_APP' )      or define( 'OC_B2B_APP', 'APP' );  #app购买
#=================  pop 相关常量 ===================
// 账户编码类型定义
defined( 'POP_CODE' )     or define( 'POP_CODE', 1 ); # POP平台编码
// 账户二级编码类型定义
defined( 'POP_CODE_SC' )     or define( 'POP_CODE_SC', 0 ); # 店铺编码

#================= 支付方式 ===================

//defined( 'PAY_METHOD_OFFLINE_COD' )             or define( 'PAY_METHOD_OFFLINE_COD', 'COD' ); # 货到付款
defined( 'PAY_METHOD_ONLINE_WEIXIN' )       or define( 'PAY_METHOD_ONLINE_WEIXIN', 'WEIXIN' ); # 微信支付
defined( 'PAY_METHOD_ONLINE_ALIPAY' )       or define( 'PAY_METHOD_ONLINE_ALIPAY', 'ALIPAY' ); # 支付宝支付
defined( 'PAY_METHOD_ONLINE_CHINAPAY' )       or define( 'PAY_METHOD_ONLINE_CHINAPAY', 'CHINAPAY' ); # 银联支付
defined( 'PAY_METHOD_ONLINE_REMIT' )       or define( 'PAY_METHOD_ONLINE_REMIT', 'REMIT' ); # 银行汇款
defined( 'PAY_METHOD_ONLINE_YEEPAY' )       or define( 'PAY_METHOD_ONLINE_YEEPAY', 'YEEPAY' ); # 银行卡支付
defined( 'PAY_METHOD_ONLINE_ADVANCE' )       or define( 'PAY_METHOD_ONLINE_ADVANCE', 'ADVANCE' ); # 预付款支付
defined( 'PAY_METHOD_ONLINE_UCPAY' )       or define( 'PAY_METHOD_ONLINE_UCPAY', 'UCPAY' ); # 先锋支付
#================= 银行转账的银行 ===================
defined( 'PAY_METHOD_REMIT_CMB' )       or define( 'PAY_METHOD_REMIT_CMB', 'CMB' ); # 招商银行
defined( 'PAY_METHOD_REMIT_CMBC' )       or define( 'PAY_METHOD_REMIT_CMBC', 'CMBC' ); # 民生银行

#================= 支付类型 ===================
defined( 'PAY_TYPE_COD' )             or define( 'PAY_TYPE_COD', 'COD' ); # 货到付款
defined( 'PAY_TYPE_ONLINE' )             or define( 'PAY_TYPE_ONLINE', 'ONLINE' ); # 立即付款
defined( 'PAY_TYPE_TERM' )             or define( 'PAY_TYPE_TERM', 'TERM' ); # 账期

#================= 账期支付支付方式 ===================
defined( 'PAY_TYPE_TERM_MONTH' )       or define( 'PAY_TYPE_TERM_MONTH', 'TERM_MONTH' ); # 月结
defined( 'PAY_TYPE_TERM_PERIOD' )       or define( 'PAY_TYPE_TERM_PERIOD', 'TERM_PERIOD' ); # 期结
#================= 配送方式 ===================

defined( 'SHIP_METHOD_PICKUP' )             or define( 'SHIP_METHOD_PICKUP', 'PICKUP' ); # 买家自提
defined( 'SHIP_METHOD_DELIVERY' )       or define( 'SHIP_METHOD_DELIVERY', 'DELIVERY' ); # 卖家配送

#================= 促销信息状态常量 ===================
defined( 'SPC_STATUS_DELETE' )             or define( 'SPC_STATUS_DELETE', 'DELETE' ); # 删除状态
defined( 'SPC_STATUS_DRAFT' )              or define( 'SPC_STATUS_DRAFT', 'DRAFT' ); # 草稿状态
defined( 'SPC_STATUS_PREHEAT' )             or define( 'SPC_STATUS_PREHEAT', 'PREHEAT' ); # 预热状态
defined( 'SPC_STATUS_END' )             or define( 'SPC_STATUS_END', 'END' ); # 已结束
defined( 'SPC_STATUS_ON_SALE' )             or define( 'SPC_STATUS_ON_SALE', 'ON_SALE' ); # 促销中
defined( 'SPC_STATUS_PUBLISH' )             or define( 'SPC_STATUS_PUBLISH', 'PUBLISH' ); # 发布中

#================= 促销类型 ===================
defined( 'SPC_TYPE_GIFT' )             or define( 'SPC_TYPE_GIFT', 'REWARD_GIFT' ); # 满赠
defined( 'SPC_TYPE_SPECIAL' )          or define( 'SPC_TYPE_SPECIAL', 'SPECIAL');   # 特价
defined( 'SPC_TYPE_LADDER' )          or define( 'SPC_TYPE_LADDER', 'LADDER');      # 阶梯价

#================= 特价促销类型 ===================
defined( 'SPC_SPECIAL_REBATE' )             or define( 'SPC_SPECIAL_REBATE', 'REBATE' ); # 折扣
defined( 'SPC_SPECIAL_FIXED' )          or define( 'SPC_SPECIAL_FIXED', 'FIXED'); # 一口价=======

#================= 财务模块常量 ===================
defined( 'FC_CODE' ) or define( 'FC_CODE', 1);	# fc_code
defined( 'FP_CODE') or define( 'FP_CODE', 1);	# 财务确认单二级标识
defined( 'FC_THIRD_STATUS_NO_CHECK')          or define( 'FC_THIRD_STATUS_NO_CHECK', 'NO_CHECK'); # 第三方交易明细订单未核对
defined( 'FC_THIRD_STATUS_CHECK')             or define( 'FC_THIRD_STATUS_CHECK', 'CHECK'); # 第三方交易明细订单已核对
defined( 'FC_ACCOUNT_STATUS_NO_ACCOUNT')      or define( 'FC_ACCOUNT_STATUS_NO_ACCOUNT', 'NO_ACCOUNT'); # 未对账
defined( 'FC_ACCOUNT_STATUS_ACCOUNT')         or define( 'FC_ACCOUNT_STATUS_ACCOUNT', 'ACCOUNT'); # 已对账
defined( 'FC_BALANCE_STATUS_NO_BALANCE')      or define( 'FC_BALANCE_STATUS_NO_BALANCE', 'NO_BALANCE'); # 未到账
defined( 'FC_BALANCE_STATUS_YES_BALANCE')     or define( 'FC_BALANCE_STATUS_YES_BALANCE', 'YES_BALANCE'); # 已到账
defined( 'FC_BALANCE_STATUS_BALANCE')         or define( 'FC_BALANCE_STATUS_BALANCE', 'BALANCE' ); # 已结算
defined( 'FC_STATUS_NO')                      or define( 'FC_STATUS_NO', 'NO'); # 未处理
defined( 'FC_STATUS_END')                     or define( 'FC_STATUS_END', 'END'); # 已处理
defined( 'FC_STATUS_CLOSE')                   or define( 'FC_STATUS_CLOSE', 'CLOSE'); # 资金原路退回
defined( 'FC_TYPE_WEIXIN')                    or define( 'FC_TYPE_WEIXIN', 'WEIXIN'); # 微信
defined( 'FC_TYPE_REMIT')                     or define( 'FC_TYPE_REMIT', 'REMIT'); # 银行
defined( 'FC_TYPE_UCPAY')                     or define( 'FC_TYPE_UCPAY', 'UCPAY'); # 先锋支付
defined( 'FC_BANK_TYPE_ACCOUNTED')            or define( 'FC_BANK_TYPE_ACCOUNTED', 'ACCOUNTED'); # 入账 民生银行默认 type=2
defined( 'FC_BANK_TYPE_OUT_ACCOUNT')          or define( 'FC_BANK_TYPE_OUT_ACCOUNT', 'OUT_ACCOUNT'); # 出账 民生银行默认 type=1
defined( 'FC_STATUS_UN_CONFIRM')              or define( 'FC_STATUS_UN_CONFIRM', '1'); #未确认
defined( 'FC_STATUS_ON_CONFIRM')              or define( 'FC_STATUS_ON_CONFIRM', '2'); #已确认
defined( 'FC_STATUS_CONFIRM')                 or define( 'FC_STATUS_CONFIRM', '3'); #已审单
defined( 'FC_F_STATUS_UN_PAYMENT')            or define( 'FC_F_STATUS_UN_PAYMENT', '1'); #未汇总
defined( 'FC_F_STATUS_ON_PAYMENT')            or define( 'FC_F_STATUS_ON_PAYMENT', '2'); #已汇总未付款
defined( 'FC_F_STATUS_PAYMENT')               or define( 'FC_F_STATUS_PAYMENT', '3'); #已汇总已付款
defined( 'FC_STATUS_ON_PAYMENT')              or define( 'FC_STATUS_ON_PAYMENT', '1'); #已汇总未付款
defined( 'FC_STATUS_PAYMENT')                 or define( 'FC_STATUS_PAYMENT', '2'); #已汇总并付款
defined( 'FC_PAY_STATUS_OK')                  or define( 'FC_PAY_STATUS_OK', 'OK'); #付款状态为成功
defined( 'FC_PAY_STATUS_NO')                  or define( 'FC_PAY_STATUS_NO', 'NO'); #付款默认状态
defined( 'FC_PAY_STATUS_FAIL')                or define( 'FC_PAY_STATUS_FAIL', 'FAIL'); #付款状态为失败
defined( 'FC_PAY_PRIVS_NO')                   or define( 'FC_PAY_PRIVS_NO', 'NO'); # 先锋支付状态 默认NO
defined( 'FC_PAY_PRIVS_YES' )                 or define( 'FC_PAY_PRIVS_YES', 'YES'); # 先锋支付状态 默认YES



#================= 订货会模块常量 ===================
defined( 'COMMODITY_STATUS_PUBLISH' )             or define( 'COMMODITY_STATUS_PUBLISH', 'PUBLISH' ); # 发布中
defined( 'COMMODITY_STATUS_END' )             or define( 'COMMODITY_STATUS_END', 'END' ); # 已结束

defined( 'OC_ORDER_ORDER_STATUS_UNCONFIRM' )     or define( 'OC_ORDER_ORDER_STATUS_UNCONFIRM', 'UNCONFIRM' ); # 订单状态   未确认
defined( 'FC_ORDER_CONFIRM_OC_TYPE_GOODS')             or define( 'FC_ORDER_CONFIRM_OC_TYPE_GOODS', 'GOODS' ); # 商品订单
defined( 'FC_ORDER_CONFIRM_OC_TYPE_ADVANCE')             or define( 'FC_ORDER_CONFIRM_OC_TYPE_ADVANCE', 'ADVANCE' ); # 预付款充值订单
#================= 卖家短信类型 ===================
defined( 'SC_SMS_NEW_ORDER' ) or define( 'SC_SMS_NEW_ORDER', 'NEW_ORDER'); # 您的客户 ....请尽快发货
defined( 'SC_SMS_NEW_DELIVERY' ) or define( 'SC_SMS_NEW_DELIVERY', 'NEW_DELIVERY'); # 平台已向您尾号....预计将在下个工作日内到帐
defined( 'SC_SMS_NEW_BALANCE' ) or define( 'SC_SMS_NEW_BALANCE', 'NEW_BALANCE');	# 您的客户 ...结算日期
defined( 'SC_SMS_ALL_BALANCE' ) or define( 'SC_SMS_ALL_BALANCE', 'ALL_BALANCE');	# 您有x笔账期订单
#================= 商家银行账户设置 ===================
defined( 'SC_ACCOUNT_TYPE_ENTERPRISE_ACCOUNT' ) or define( 'SC_ACCOUNT_TYPE_ENTERPRISE_ACCOUNT', 'ENTERPRISE_ACCOUNT'); # 对公账户
defined( 'SC_ACCOUNT_TYPE_PERSONAL_ACCOUNT' )   or define( 'SC_ACCOUNT_TYPE_PERSONAL_ACCOUNT', 'PERSONAL_ACCOUNT'); # 个人账户

#================= umeng推送消息类型常量 ===================
defined( 'UMENG_BOSS_UNSHIP' )             or define( 'UMENG_BOSS_UNSHIP', 'UNSHIP' ); # 代发货提醒

#================= umeng推送消息类型常量 ===================
defined( 'CMS_SC_PLATFROM_USER' )             or define( 'CMS_SC_PLATFROM_USER', '504' ); # 代发货提醒
#================商家标签状态=================
defined( 'SC_LABEL_STATUS_ENABLE' ) or define('SC_LABEL_STATUS_ENABLE', 'ENABLE');
defined( 'SC_LABEL_STATUS_DISABLE' ) or define('SC_LABEL_STATUS_DISABLE', 'DISABLE');
#===============平台订单类型=================
defined( 'OC_ORDER_TYPE_STORE' ) or define('OC_ORDER_TYPE_STORE', 'STORE');  //商品订单
defined( 'OC_ORDER_TYPE_PLATFORM' ) or define('OC_ORDER_TYPE_PLATFORM', 'PLATFORM');//平台订单

#===============优惠活动条件类型=================
defined( 'SPC_ACTIVE_CONDITION_FLAG_REGISTER' ) or define( 'SPC_ACTIVE_CONDITION_FLAG_REGISTER', 'REGISTER' );  # 新用户注册 
defined( 'SPC_ACTIVE_CONDITION_FLAG_FULL_BACK' ) or define( 'SPC_ACTIVE_CONDITION_FLAG_FULL_BACK', 'FULL_BACK' );  # 平台商城订单支付成功
#===============优惠活动规则类型=================
defined( 'SPC_ACTIVE_RULE_FLAG_ONE_TIME' ) or define( 'SPC_ACTIVE_RULE_FLAG_ONE_TIME', 'ONE_TIME' );  	# 一次发放所有优惠券

#===============优惠券状态=================
defined( 'SPC_MEMBER_COUPON_STATUS_ENABLE' ) or define( 'SPC_MEMBER_COUPON_STATUS_ENABLE', 'ENABLE' );  # 可用
defined( 'SPC_MEMBER_COUPON_STATUS_OCCUPY' ) or define( 'SPC_MEMBER_COUPON_STATUS_OCCUPY', 'OCCUPY' );  # 占用
defined( 'SPC_MEMBER_COUPON_STATUS_OVERDUE' ) or define( 'SPC_MEMBER_COUPON_STATUS_OVERDUE', 'OVERDUE' );# 已过期
defined( 'SPC_MEMBER_COUPON_STATUS_INVALID' ) or define( 'SPC_MEMBER_COUPON_STATUS_INVALID', 'INVALID' );# 未生效
defined( 'SPC_MEMBER_COUPON_STATUS_USED' ) or define( 'SPC_MEMBER_COUPON_STATUS_USED', 'USED' );      # 已使用

#===============优惠活动状态=================
defined( 'SPC_ACTIVE_STATUS_PUBLISH' ) or define( 'SPC_ACTIVE_STATUS_PUBLISH', 'PUBLISH' );  # 已上线
defined( 'SPC_ACTIVE_STATUS_DRAFT' ) or define( 'SPC_ACTIVE_STATUS_DRAFT', 'DRAFT' );  # 未上线
defined( 'SPC_ACTIVE_STATUS_PREHEAT' ) or define( 'SPC_ACTIVE_STATUS_PREHEAT', 'PREHEAT' );# 预热中
defined( 'SPC_ACTIVE_STATUS_END' ) or define( 'SPC_ACTIVE_STATUS_END', 'END' );	# 已下线

#===============优惠券时间类型=================
defined( 'SPC_COUPON_TIME_TYPE_DAYS' ) or define( 'SPC_COUPON_TIME_TYPE_DAYS', 'DAYS' );	   			     # 领取若干天内有效
defined( 'SPC_COUPON_TIME_TYPE_TIME_SPAN' ) or define( 'SPC_COUPON_TIME_TYPE_TIME_SPAN', 'TIME_SPAN' );      # 具体的时间