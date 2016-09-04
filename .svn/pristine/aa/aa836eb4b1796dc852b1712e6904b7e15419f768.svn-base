<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户相关模块
 */

namespace Com\Common\User;
use System\Base;

class User extends Base {

	private $_rule	=	null; # 验证规则列表

    public function __construct() {
        parent::__construct();
	}

	/**
	 *
	 * 验证用户组的类型, 目前只支持商家验证
	 *
	 * checkUserType
	 *
	 * Com.Common.User.User.checkUserType
	 *
	 * data.uc_code, data.type
	 *
	 * @access public
	 *
	 * @return array
	 *
	 */

	public function checkUserType($data) {

		$this->_rule = array(
			array('uc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('type', array(MERCHANT_GROUP) , PARAMS_ERROR, MUST_CHECK, 'in'),
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		switch($data['type']) { 

		case MERCHANT_GROUP:
			return $this->res( $this->getMerchantType($data['uc_code']) );

		default:
			return $this->res(null);
		}

	}

	/**
	 *
	 * 验证商户--关联商户表查询
	 *
	 * getMerchantType 
	 * 
	 * @param mixed $uc_code 
	 * @access private
	 * @return void
	 */
	private function getMerchantType($uc_code) {
		$where = array(
			"u.uc_code" => $uc_code,
			"u.group_id" => MERCHANT_GROUP, 
			"um.status" => 'ENABLED', # 激活
		);
		
		$merchant = D( "UcUser" )->alias( 'u' )->field('um.id,um.status as merchant_status,u.status as user_status,u.group_id')
						->join( 'left join __UC_MERCHANT__ um on u.uc_code = um.uc_code' )
						->where( $where )->find();
		
		if(isset($merchant['id'])) {
			$merchant['group_id'] = MERCHANT_GROUP;
			return $merchant;
		}else{
			return null;
		}

	}

	/**
	 * api登录接口,验证登录账号和密码的正确性
	 * login 
	 * Com.Common.User.User.login
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */

	public function login($data) {
		$this->_rule = array(
			array('username', 'require' , PARAMS_ERROR, MUST_CHECK ),  # 用户名		* 必须字段
			array('password', 'require' , PARAMS_ERROR, MUST_CHECK ),  # 密码		* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		$sysName = $this->_request_sys_name;
		switch($sysName) {
			case B2B:
			case POP:
			default:
				return $this->res();
		}

	}


}








?>
