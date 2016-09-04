<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 资金账户操作转发层
 */

namespace Base\TradeModule\Account;
use System\Base;

class Balance extends Base {

	private $_rule	=	null; # 验证规则列表

    public function __construct() {
        parent::__construct();
	}

	/**
	 * 增加指定金额到资金账户中
	 *
	 * incr
	 * Base.TradeModule.Account.Balance.incr
	 * @access public
	 * @return void
	 */
	public function incr($data) {
		static $_typeMap = array(
			TC_ACCOUNT_PREPAYMENT => "Base.TradeModule.Account.PrePayment.incr",
		);

		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$accountType = $this->getTypeByTcCode($data['tc_code']);
		$api = $_typeMap[$accountType];
		if(!$api) {
			return $this->res(null, 3001); # 不支持该类型的操作
		}
		$do = $this->invoke($api, $data);
		return $this->res($do['response'], $do['status']);
	}

	/**
	 * 减少可用余额
	 * desc
	 * Base.TradeModule.Account.Balance.desc
	 * @access public
	 * @return void
	 */
	public function desc($data) {
		static $_typeMap = array(
			TC_ACCOUNT_PREPAYMENT => "Base.TradeModule.Account.PrePayment.desc",
		);

		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$accountType = $this->getTypeByTcCode($data['tc_code']);
		$api = $_typeMap[$accountType];
		if(!$api) {
			return $this->res(null, 3001); # 不支持该类型的操作
		}
		$do = $this->invoke($api, $data);
		return $this->res($do['response'], $do['status']);
	}


	/**
	 * 根据tcCode获取账户类型
	 * getTypeByTcCode 
	 * @param mixed $tcCode 
	 * @access public
	 * @return void
	 */
	private function getTypeByTcCode($tcCode) {
		$where = array(
			'tc_code' => $tcCode
		);
		$type = D('TcMainAccount')->field('account_type')->where($where)->find();
		return $type['account_type'];
	}

	/*
		创建资金账户
		外部事务控制, api内部只返回调用结果和异常抛出
		Base.TradeModule.Account.Balance.add
	 */

	public function add($data) {
		static $_typeMap = array(
			TC_ACCOUNT_PREPAYMENT => "Base.TradeModule.Account.PrePayment.add",
		);

		$this->_rule = array(
			array('accountType', array(TC_ACCOUNT_PREPAYMENT) , PARAMS_ERROR, MUST_CHECK, 'in'),
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$add = $this->invoke($_typeMap[$data['accountType']], $data);
		return $this->res($add['response'], $add['status']);
	}

}

?>
