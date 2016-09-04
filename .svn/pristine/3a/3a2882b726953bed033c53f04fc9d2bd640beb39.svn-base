<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangren.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangren.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 配置api-getway信息
 */

# 配置网关的注册信息
$auth = array(

	# boss - client
	'BOSS' => array (
		'salt' => 'e1f223373ffe55dc18b2e788b77459eb52767021',
		'allow_ip' => array(),
		'deny_ip' => array(),
		'route' => 'boss',
		'check_login_input' => ['uc_code','sc_code'],	# _input中定义了参数, 在网关无法取到的时候会返回给客户端未登录的错误信息
		'token_api' => ['Bll.Boss.User.Login.Login'],
		'api_server' => 'http://api.liangrenwang.com'
	),
	# boss - client
);

# 循环加载包含, 请勿修改下面代码
foreach($auth as $k=>$v) {
	if( !isset($v['salt']) || !isset($v['route']) ) {
		return -1;
	}
	require_once CONF_PATH.'getway/'.$v['route'].'.php';
	$auth[$k]['api'] = $route;
}

return $auth;
