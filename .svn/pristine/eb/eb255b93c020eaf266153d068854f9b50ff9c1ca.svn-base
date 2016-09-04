<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | demo
 */

$route = array(

	# 通过 '*' 通配符做权限控制, 最大控制到第二段(容器段), 权限判断优先级为: 实体 > 功能类 > 组件 > 服务容器 , 如果匹配到则使用匹配到的路由, 不再往下继续匹配
	'Bll.Boss.*.*.*' => array(
		
		# 会话规则配置, 无该配置项, 或为空数组, 则不会做会话保持相关动作
		'token' => array(
			# 会话输入参数, 网关在调用时会自动在调用参数data中植入配置的数据, 植入后的格式: $data = array('uc_code'=>'x','sc_code'=>'x', 'x'=>'x');
			# 如果配置该参数, 则网关必会植入, 异常情况下会直接返回给上层调用系统未登录的错误码, 但不会把调用转发到subvert
			# 如果忽略或为空数组, 则网关不会做会话信息植入操作
			'_input' => ['uc_code','sc_code'],

			# 该参数用以操作会话中的数据
			# 如果忽略或为空数组参数则该次调用不会对会话中的数据产生影响
			'_output'=> array(
				
				# 为 W 则表示更新会话中的数据参数, 不存在则新增
				# 要更新的值必须在接口返回值中定义,  response['_output']['code'] = 'PMSB';
				'W' => ['code'],

				# 为 D 则表示删除会话中的数据参数项
				'D' => ['test_code'],
			),
		),

		# 缓存控制,如果type = QUERY 或 LOCAL 则expire表示过期时间为当前服务器时间加上对应的值(单位:秒)
		# 不配置则表示不做任何缓存
		'cache' => array(
			'type' => 'LOCAL',
			'expire' => '10',
		),   

	),

	'Bll.Boss.User.User.*' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
		),

		'cache' => array(
			'type' => 'QUERY',
			'expire' => '10',
		),
	),

	'Bll.Boss.User.User.login' => array(
		'token' => array(
			'_input' => ['uc_code','sc_code'],
		),

		'cache' => array(
			'type' => 'LOCAL',
			'expire' => '10',
		),
	),
);
