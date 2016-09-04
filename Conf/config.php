<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 系统全局配置文件
 */
$config = array(
    'UC_AUTH_KEY' => 'y?Ewc7lkd5}"Ubh~t3iOWFe6=>&|SG9z*^]C;81L',
	'SAFE_CODE_KEY' => 'dsDS@$^&FEWddfttedf@#!&*)()wdwwwe',
    'SINGN_KEY' => '_#@DU^^&JGK_((*&gjGH',
	'SMS_MODEL' => '【粮人网】',	# 短信内容前面必须带上该配置内容

    'ENV'=>array(
            'production'=>1,
            'develop'=>2,
            'test'=>3,
            'pre_release'=>4,
        ),
	'DEFAULT_PAGE_NUMBER'=>20,				# 默认分页展示条数
	'CTU_CHECK' => true,					# 开启风控扫描
	
	#========== wdog配置 =============
	'DG_OPEN' => true,						# 开启DG记录
	'DG_TREE' => false,						# 记录树状调用栈, 默认为平级栈
	'DG_LOCAL_URL' => 'http://api.debug.com',# wdog的本地调用地址
	// 在该环境变量下记录到本地wdog production:生产, develop:开发环境, test:测试环境, pre_release:预发环境
	'DG_LOCAL_ENV' => 'test',

	#========== 导出配置 =============
	'EXPORT_LIMIT_NUMS'=>10000,		# 导出数据的边界线  大于 1000 条用异步 ，小于 1000 条用同步
	'SINGLE_EXPORT_NUMS'=>2000,     # 单次导出的数据量
	#========== aliyun OSS 文件云上传 调用配置 =============
	'OSS_KEYID' => 'gz6BYSn7tsm9iu8B', // key
	'OSS_KEYSECRET' => 'vMZFZ29gkjwlCpIxKFyCb1vWGjRx95', // 密匙
	'OSS_BUCKET' => 'ypt', // 存储对象名称
	'OSS_ALIYUN_DOMAIN' => 'cdn.liangren.com', // 阿里云图片服务器绑定域名
	'OSS_ALIYUN_IMG_DISPOSE_DOMAIN' => 'img.liangren.com', // 阿里云图片处理服务器绑定域名
	'CHANNEL_QRCODE_URL'=>'http://sp.liangren.com/',  # 渠道用户地址前缀
	'CASHIER_URL_CONF' =>  array(
		'ALIPAY_NOTIFY' => 'http://cashier.liangren.com/static/alipay_notify.html',
	),
	# invoke 执行的时候进行远程调用的配置信息
	'RPC_INVOKE' => array(
		'CONTAINER' => array( # 允许的容器段配置
			'paycenter'=> array(
				'host' => 'http://cashier.liangren.com/Home/Index/call',
			),
		),
	),
	
	#========== 微信服务号相关配置 =========================
	'WEIXIN_CONF'=>array(
		'TOOKEN_URL' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential',
		'USER_INFO_URL' => 'https://api.weixin.qq.com/cgi-bin/user/info?lang=zh_CN',
		'TPL_MSG_URL' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?',
		'GET_OPEN_ID_URL' => 'https://open.weixin.qq.com/connect/oauth2/authorize?',
		'GET_OPEN_ID_URL2' => 'https://api.weixin.qq.com/sns/oauth2/access_token?',
		'SEND_SVC_URL' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send?',
		'GET_USER_GROUP' =>'https://api.weixin.qq.com/cgi-bin/groups/get?',
		'MOVE_USERS_GROUP' => 'https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?',
		'APPID' => 'wx8f733bdf133262d3',
		'APPSECRET' => '5418a7252ddac9e32f4ba087138f5fb2',
	),
	'DEFAULT_PROVINCE' =>'北京市', //默认城市
	'DEFAULT_CITY' =>'北京市', //默认城市
	'DEFULT_SC_CODE' => '1020000000026'  , # 请在此配置好余氏的店铺编码
    'DEFAULT_WEIXIN_URL'=>'http://sp.liangren.com/',
	'CALL_NUMBER' => '400-815-5577', # 客服电话
        'BOSS'=>array(
            'ANDROID'=>array(
                'APP_KEY'=>'561de4dce0f55ab67b004519', //app 的 appkey
                'APP_MASTER_SECRET'=>'dxzgna2r4kbenslkou1fm16jfrmt9qao', //app 的  app_master_secret
                ),
            'IOS'=>array(
                'APP_KEY'=>'5624bbdc67e58e92d2002f61', //app 的 appkey
                'APP_MASTER_SECRET'=>'kimac9ofuyb2oclepypsj8uibugdlppx', //app 的  app_master_secret
                ),
        ),
# 民生银行接口配置参数
		'BANK_PARAMS' => array(
			'gateway' => 'CMBC_BAL',
			'typeCode' => 1,
			'weixin' => '1820014210000931',
			'limitAmount' => 50000.00,//50000.00,
			'ucpay' => '21201501200053039817',
		),
'BANK_PARAMS_CMB' => array(
		'gateway' => 'CMB_BAL',
		'typeCode' => 0,
	),
#========== aliyun OSS ram sts 文件上传配置 =============
    'OSS'=>array(
      'MOBILE'=>array(
        'OSS_KEYID' => 'je5e6ibzWEiPenxr', // key
        'OSS_KEYSECRET' => 'LTpnvG1rkzqL7nG27846t8Uzi4L8iz', // 密匙
        'OSS_BUCKET' => 'ypt', // 存储对象名称
        'OSS_ALIYUN_DOMAIN' => 'cdn.liangren.com', // 阿里云图片服务器绑定域名
    'OSS_ALIYUN_IMG_DISPOSE_DOMAIN' => 'cdn.liangren.com', // 阿里云图片处理服务器绑定域名
        'OSS_ARN'=>'acs:ram::1742533927067007:role/mobile',
        'OSS_NODE'=>'cn-hangzhou',
        'OSS_DURATION'=>'3600',
      ),
    ),


'STATIC_DOMAIN'=>'http://static.liangren.com/b2b/',

        # 先锋支付相关配置
        'UC_PAY_DATA' => array(
                'uc_pay_num' => 5, //能够使用先锋支付 需要支付成功的次数
                'uc_pay_amount' => 1000, //能够使用先锋支付 需要支付的最小金额
                'uc_pay_number' => 1, //每次支付的计数
                'gateway' => 'UCPAY_DIRECT',
                'use_pay_amount' => 350, //订单支付显示金额
                ),
        'TEXT_INVITE_COE'=>'5614',
        'LIANGREN_SC_CODE'=>'1010000000077',
        #招商银行网关调用
        'BANK_PARAMS_CMB' => array(
		    'gateway' => 'CMB_BAL',
		    'typeCode' => 0,
	     ),
);
include CONF_PATH.'connection_'.ENV.'.php';
return array_merge($config,$connect);
