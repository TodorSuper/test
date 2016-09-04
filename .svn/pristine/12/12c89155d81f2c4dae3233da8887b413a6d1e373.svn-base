<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 预付款类型余额操作接口
 */

namespace Base\TradeModule\Account;
use System\Base;

class PrePayment extends Base {

	private $_rule	=	null; # 验证规则列表

    public function __construct() {
        parent::__construct();
	}
	
	/**
	 * 减少可用余额
	 * Base.TradeModule.Account.PrePayment.desc
	 * @access public
	 * @return void
	 */
	public function desc($data) {
		if($data['amount'] <= 0) {
			return $this->res(null, 2549);
		}

		# 确定当前的操作类型
		$action  = TC_ACCOUNT_DESC_BY_PREPAYMENT;
		$changeType = TC_ACCOUNT_DESC;

		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;


		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					
			array('operatorType', 'require' , PARAMS_ERROR, MUST_CHECK),			
			array('operatorName', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('operatorIp', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('amount', 'require' , PARAMS_ERROR, MUST_CHECK),					
			array('remark', 'require' , PARAMS_ERROR, HAVEING_CHECK),
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 开启外部事务模式
		$this->startOutsideTrans();

		# 风控分析(权限检查, 安全检查, 线性检查)
		$ctuData  = array(
			'tc_code' => $data['tc_code'],
			'oc_code' => $data['oc_code'],
			'type' => $action,
			'operator_type' => $data['operatorType'],
			'amount' => $data['amount']
		);
		$ctuInfo = $this->invoke('Base.CtuModule.Account.Balance.descControl', $ctuData);
		if($ctuInfo['status'] !== 0) {
			return $this->res('', $ctuInfo['status']);
		}

		# 调用包装接口
		$callData = array(
			'action' => $action,
			'changeType' => $changeType,
			'tc_code' => $data['tc_code'],
			'oc_code' => $data['oc_code'],
			'operator_type' => $data['operatorType'],
			'operator_name' => $data['operatorName'],
			'operator_ip' => $data['operatorIp'],
			'amount' => $data['amount'],
			'remark' => $data['remark']
		);
		$call = $this->invoke('Base.TradeModule.Account.Factory.desc', $callData);

		if($call['status'] !== 0) {
			return $this->res($call['response'], $call['status']);
		}

		return $this->res(true);
	}

	/*
		创建预付款资金账户
		外部事务控制, api内部只返回调用结果和异常抛出
		Base.TradeModule.Account.PrePayment.add
		CREATE TABLE `16860_tc_main_account` (
			`tc_code` char(30) NOT NULL COMMENT '主账户编码',
			`uc_code` char(30) NOT NULL COMMENT '用户编码',
			`free_balance` decimal(10,2) NOT NULL COMMENT '可用余额',
			`freeze_balance` decimal(10,2) NOT NULL COMMENT '冻结余额',
			`safe_code` char(32) NOT NULL COMMENT '安全效验码',
			`status` varchar(50) NOT NULL COMMENT '账户状态',
			`update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
			`create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
			PRIMARY KEY (`tc_code`),
			KEY `user_code` (`user_code`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户账户表';

		checkInput: 
		0 => array(字段名称, 验证规则, 出错返回码, 验证条件, 附加规则)

	 */

	public function add($data) {
		$this->startOutsideTrans();  # 开启外部事务控制模式

		$this->_rule = array(
			array('code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('pCode', 'require' , PARAMS_ERROR, MUST_CHECK),
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		# 添加账户 风控检测
		$checkAccount = $this->invoke('Base.CtuModule.Account.Balance.addControl', $data);
		if($checkAccount['status'] !== 0) {
			return $this->res('', $checkAccount['status']);
		}

		# 调用包装接口
		$data['ext1'] = $data['pCode'];
		$data['preBusType'] = TC_ACCOUNT_NUM_PREPAYMENT; # 帐户自增类型
		$call = $this->invoke('Base.TradeModule.Account.Factory.add', $data);
		if($call['status'] !== 0) {
			return $this->res($call['response'], $call['status']);
		}

		$res = array(
			'tc_code' => $call['response']
		);
		return $this->res($res);
	}


	/**
	 * 增加指定金额到资金账户中
	 * Base.TradeModule.Account.PrePayment.incr
	 * @access public
	 * @return void
	 */

	public function incr($data) {
	
		if($data['amount'] <= 0) {
			return $this->res(null, 2549);
		}

		# 确定当前的操作类型;
		$action  = TC_ACCOUNT_INCR_BY_PREPAYMENT;
		$changeType = TC_ACCOUNT_INCR;
		
		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;


		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					
			array('operatorType', 'require' , PARAMS_ERROR, MUST_CHECK),			
			array('operatorName', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('operatorIp', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('amount', 'require' , PARAMS_ERROR, MUST_CHECK),					
			array('remark', 'require' , PARAMS_ERROR, HAVEING_CHECK),
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 开启外部事务模式
		$this->startOutsideTrans();

		# 风控分析(权限检查, 安全检查, 线性检查)
		$ctuData  = array(
			'tc_code' => $data['tc_code'],
			'oc_code' => $data['oc_code'],
			'type' => $action,
			'operator_type' => $data['operatorType'],
			'amount' => $data['amount']
		);
		$ctuInfo = $this->invoke('Base.CtuModule.Account.Balance.incrControl', $ctuData);
		if($ctuInfo['status'] !== 0) {
			return $this->res('', $ctuInfo['status']);
		}
	 
		# 调用包装接口
		$callData = array(
			'action' => $action,
			'changeType' => $changeType,
			'tc_code' => $data['tc_code'],
			'oc_code' => $data['oc_code'],
			'operator_type' => $data['operatorType'],
			'operator_name' => $data['operatorName'],
			'operator_ip' => $data['operatorIp'],
			'amount' => $data['amount'],
			'remark' => $data['remark']
		);
		$call = $this->invoke('Base.TradeModule.Account.Factory.incr', $callData);
	
		if($call['status'] !== 0) {
			return $this->res($call['response'], $call['status']);
		}

		return $this->res(true);

	}


}

?>
