<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | boss系统api调用控制参数
 */

$route = array(
	'Bll.Boss.*.*.*' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
			'_output' => array()
		)
	),
	'Bll.Boss.Order.OrderInfo.accountInfo' => array(
		'token' => array(
			'_input' => ['uc_code'],
		)
	),

	'Bll.Boss.User.Login.Login' => array(
		'token' => array(
			'_output' => array(
				'W' => ['uc_code','sc_code','push_msg','prompt_sound','show_img'],
			),
		),
	),


	'Bll.Boss.User.User.logout' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
			'_output' => array(
				'D'=>['*'],
			),
		),
	),


	//2016-1-4 added 取货码的uc_code  sc_code 数据植入
	'Bll.Boss.Order.OrderInfo.checkPickUpCode' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
		),
	),


	'Bll.Boss.Store.Item.lists' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code','show_img'],
		),

	),

	'Bll.Boss.Stasitc.Order.month' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code','_version'],
		),

	),

	'Bll.Boss.Store.Store.set' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
			'_output' => array(
				'W' => ['push_msg','prompt_sound','show_img'],
			),
		),

	),

	'Bll.Boss.Store.Store.init' => array(
		'token' => array(
		),
	),
    
       'Bll.Boss.Store.Item.getSts' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
		),
           
               'cache' => array(
			'type' => 'LOCAL',
			'expire' => '3000',
		), 

	),


	'Bll.Boss.User.User.changePwd' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
			'_output' => array(
				'D'=>['*'],
			),
		),
	),

	'Bll.Boss.User.Customer.get' => array(
		'token' => array(
			'_input' => ['sc_code'],
		)
	),

	'Bll.Boss.User.Customer.itemLists' => array(
		'token' => array(
			'_input' => ['sc_code'],
		)
	),


);
