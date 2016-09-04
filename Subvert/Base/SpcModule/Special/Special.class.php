<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |促销规则
 */


namespace Base\SpcModule\Special;

use System\Base;


class Special extends Base
{
	public $specialTable = array('spc_code', 'sic_code', 'special_type', 'discount', 'special_price', 'create_time', 'update_time', 'status');	

	public function __construct(){
		parent::__construct();
	}

	/**
	 * Base.SpcModule.Special.Special.add
	 * @param [type] $params [description]
	 */
	public function add($params){

		$this->startOutsideTrans();
		$this->_rule = array(
				array('itemPrice', 'require', PARAMS_ERROR, MUST_CHECK),
				array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('special_price', 'require', PARAMS_ERROR, MUST_CHECK),
				array('special_type', 'require', PARAMS_ERROR, MUST_CHECK),
				array('discount', 'require', PARAMS_ERROR, ISSET_CHECK),
			);

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
		    return $this->res($this->getErrorField(), $this->getCheckError());
		}

		//特价折扣检测
		$params['discount'] = empty($params['discount']) ? 10 : floatval($params['discount']);
		if ($params['discount'] < 0 || $params['discount'] > 10) {
			return $this->res('', 7037);
		}

		//防止类型不对，做类型转化
		switch ($params['special_type']) {
			case 'REBATE':
				$params['special_price'] = floatval($params['itemPrice'])*floatval($params['discount'])/10;
				break;
			case 'FIXED':
				$params['special_price'] = floatval($params['special_price']);
				break;
		}

		//特价不能大于商品价格，或者小于0
		if ($params['special_price'] >= $params['itemPrice'] || $params['special_price'] < 0) {
			return $this->res('', 7031);
		}

		$specialData = array();
		foreach ($params as $key => $value) {
			if (!in_array($key, $this->specialTable)) {
				continue;
			}

			if (!empty($value)) {
				$specialData["$key"] = $value;
			}else{
				return $this->res('', 18);
			}

		}
		
		$specialData['create_time'] = NOW_TIME;
		$specialData['update_time'] = NOW_TIME;

	    $insert = D('SpcSpecial')->add($specialData);
	    if(!$insert) {
	        return $this->res('', 7030);
	    }

		return $this->res(true);

	}

	/**
	 * Base.SpcModule.Special.Special.update
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function update($params){
		$this->startOutsideTrans();
		$this->_rule = array(
			array('itemPrice', 'require', PARAMS_ERROR, MUST_CHECK),
		    array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),
		    array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK),
			array('special_price', 'require', PARAMS_ERROR, MUST_CHECK),
			array('special_type', 'require', PARAMS_ERROR, MUST_CHECK),
			array('discount', 'require', PARAMS_ERROR, ISSET_CHECK),
		);

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
		    return $this->res($this->getErrorField(), $this->getCheckError());
		}

		//特价折扣检测
		$params['discount'] = empty($params['discount']) ? 10 : floatval($params['discount']);
		if ($params['discount'] < 0 || $params['discount'] > 10) {
			return $this->res('', 7035);
		}

		//防止类型不对，做类型转化
		switch ($params['special_type']) {
			case 'REBATE':
				$params['special_price'] = floatval($params['itemPrice'])*floatval($params['discount'])/10;
				break;
			case 'FIXED':
				$params['special_price'] = floatval($params['special_price']);
				break;
		}

		//特价不能大于商品价格，或者小于0
		if ($params['special_price'] >= $params['itemPrice'] || $params['special_price'] < 0) {
			return $this->res('', 7031);
		}
		
		//插入数据
		$specialData = array();
		foreach ($params as $key => $value) {
			if (!in_array($key, $this->specialTable)) {
				continue;
			}

			if (!empty($value)) {
				$specialData["$key"] = $value;
			}else{
				return $this->res('', 18);
			}

		}

		$specialData['update_time'] = NOW_TIME;

		$where['spc_code'] = $params['spc_code'];
		$update = D('SpcSpecial')->where($where)->save($specialData);
		if(!$update) {
		    return $this->res('', 7016);
		}

		return $this->res(true);
	}

}