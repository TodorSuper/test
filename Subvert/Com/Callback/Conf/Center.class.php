<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 配置中心
 */

namespace Com\CallBack\Conf;

use System\Base;

class Center extends Base {

	private $_rule = null; # 验证规则列表

	public function __construct() {
		parent::__construct();
		$this->auth = require_once CONF_PATH.'getway/main.php';
	}


	public function test() {
	//	$res['_output'] = array(
	//		'code' => '23123',
	//		'hello' => '284'
	//	);
		return $this->res($res);
	}
	/**
	 * 返回api网关的所有配置信息
	 * Com.Callback.Conf.Center.getwayAuth
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */

	public function getwayAuth($data) {
		return $this->res($this->auth);
	}

	/**
	 * 调试模式下匹配规则和返回数据
	 * Com.Callback.Conf.Center.debugCheck 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function debugCheck($data) {
		$route = $this->getApiRoute($data['api']);
		if($route === true) {
			return;
		}

		$_output = $route['token']['_output']['W'];

		# 都为空则正常
		//L($route);L($_output);return;
		if(count($data) == 0 && count($_output) == 0) {
			return;
		}

		# 任何一个为空则异常
		foreach($data['data'] as $k=>$v) {
			if(!in_array($k, $_output)) {
				throw new \Exception("检测到接口返回参数中的_output['{$k}']没有在api网关的配置中说明.");
			}
		}

		foreach($_output as $v) {
			if(!isset($data['data'][$v])) {
				throw new \Exception("检测到api网关配置中的_output['token']['{$v}'], 不存在返回值的_output中.");
			}
		}
		return;
	}

	private function getApiRoute($api) {
		# 分割api
		$cut = explode('.', $api);
		$rootfile = '/Conf/getway/'.strtolower($cut[1]);
		$rootapi = $api;
		# 读取配置节点
		$confRoot = $this->auth[strtoupper($cut[1])]['api'];
		if(!$confRoot) {
			return true;
		}


		# 按段匹配规则
		if( isset($confRoot[$api]) ) {
			$route = $confRoot[$api];
		}else {

			for($i = 4; $i >= 1; $i--) {
				$cut[$i] = '*';
				$api = implode('.', $cut);
				//L($api);
				if( isset($confRoot[$api]) ) {
					$route = $confRoot[$api];
					break;
				}
			}
		}

		if(!$route) {
			$conf = strtolower($cut[1]);
			throw new \Exception("检测到接口{$rootapi}在路由配置文件{$rootfile}.php中的配置不存在.");
		}

		return $route ? $route : [];
	}

}

?>
