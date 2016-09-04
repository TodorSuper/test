<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 资金账户风险控制类
 */

namespace Base\CtuModule\Account;
use System\Base;

class Balance extends Base {

	private $_rule	=	null; # 验证规则列表

    public function __construct() {
		parent::__construct();
	}
	

	/**
	 * 增加账户余额风险检测
	 * Base.CtuModule.Account.Balance.incrControl
	 * 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function incrControl($data) {
		# 前置检查(数据安全检查, 操作权限检查)
		$preCheck = $this->preCheck($data['tc_code'], $data['type']);
		if($preCheck !== 0 ) {
			return $this->res('', $preCheck);
		}

		$where =array(
			'adv_code' => $data['oc_code']
		);

		# 判断是否存在该订单
		$select = D('OcAdvance')->field('amount,status')->where($where)->find();
		if($select['amount'] === null) {
			return $this->res('', 2532);
		}

		if( bccomp($data['amount'], $select['amount'], 2) !== 0 ) {
			return $this->res('', 2526);  # 金额不一致
		}

		if( $select['status'] == 'PAY' ) {
			return $this->res('', 2548);  # 已支付
		}
 
		# 判断该订单是否创建过交易流水
		$where = array(
			"oc_code" => $data['oc_code'],
			"operator_type" => $data['operator_type'],
			'type' => $data['type'],
		);
		$find = D('TcTransactionInfo')->field("id")->where($where)->find();
		if($find) {
			return $this->res(null, 2547);
		}
		return $this->res(true);
	}

	/**
	 * 减少账户可用余额风险控制
	 * Base.CtuModule.Account.Balance.descControl
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function descControl($data) {
		# 前置检查(数据安全检查, 操作权限检查)
		$preCheck = $this->preCheck($data['tc_code'], $data['type']);
		if($preCheck !== 0 ) {
			return $this->res('', $preCheck);
		}

		$where =array(
			'b2b_code' => $data['oc_code']
		);

		$select = D('OcB2bOrder')->field('real_amount,pay_status')->where($where)->find();
		if($select['real_amount'] === null) {

			return $this->res('', 2532);
		}

		if( bccomp($data['amount'], $select['real_amount'], 2) !== 0 ) {
			return $this->res('', 2526);  # 金额不一致
		}

		if( $select['status'] == 'PAY' ) {
			return $this->res('', 2548);  # 订单已经付款, 余额操作失败
		}
 
		# 判断该订单是否创建过交易流水
		$where = array(
			"oc_code" => $data['oc_code'],
			"operator_type" => $data['operator_type'],
			'type' => $data['type'],
		);
		$find = D('TcTransactionInfo')->field("id")->where($where)->find();
		if($find) {
			return $this->res(null, 2547);
		}
		return $this->res(true);

	}


	/**
	 * 冻结资金账户余额
	 * Base.CtuModule.Account.Balance.freezeControl
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function freezeControl($data) {
		if($this->checkStatus() == false) { # 判断是否进行CTU监控
			return $this->res(true);
		}
		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),					#  操作类型				* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 前置检查(数据安全检查, 操作权限检查)
		$preCheck = $this->preCheck($data['tc_code'], $data['type']);
		if($preCheck !== 0 ) {
			return $this->res('', $preCheck);
		}

		# 线性检查
		$linearityCheck = $this->linearityCheck($data);
		if($linearityCheck['status'] !== 0) {
			return $this->res('', $linearityCheck['status']);
		}
		
		return $this->res(true);
	}

	/**
	 * 冻结资金账户余额
	 * Base.CtuModule.Account.Balance.freezeControl
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function freezeBackControl($data) {

		if($this->checkStatus() == false) { # 判断是否进行CTU监控
			return $this->res(true);
		}
		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),					#  操作类型				* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 前置检查(数据安全检查, 操作权限检查)
		$preCheck = $this->preCheck($data['tc_code'], $data['type']);
		if($preCheck !== 0 ) {
			return $this->res('', $preCheck);
		}

		# 线性检查
		return $this->res(true);
	}

	/**
	 * 冻结资金账户余额
	 * Base.CtuModule.Account.Balance.freezeControl
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function unfreezeControl($data) {
		if($this->checkStatus() == false) { # 判断是否进行CTU监控
			return $this->res(true);
		}
		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),					#  操作类型				* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 前置检查(数据安全检查, 操作权限检查)
		$preCheck = $this->preCheck($data['tc_code'], $data['type']);
		if($preCheck !== 0 ) {
			return $this->res('', $preCheck);
		}

		# 线性检查
		return $this->res(true);
	}

	/**
	 * 创建资金账户风险检测
	 * addControl 
	 * Base.CtuModule.Account.Balance.addControl
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function addControl($data) {
		if($this->checkStatus() == false) { # 判断是否进行CTU监控
			return $this->res(true);
		}
		$this->_rule = array(
			array('code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 账户存在检查
		$accountInfo = $this->getMerchantTcCode($data['code']);
		if($accountInfo !== false) {
			return $this->res('', 1010);
		}

		return $this->res(true);
	}

	/**
	 * 获取商户的账户编码
	 * getMerchantTcCode 
	 * @param mixed $uc_code 
	 * @access public
	 * @return tc_code
	 */
	private function getMerchantTcCode($uc_code) {
		$where = array(
			'code' => $uc_code,
		);
		$res = D('TcMainAccount')->field('code')->where($where)->find();
		return isset($res['code']) ? $res['code'] : false;
	}


	/**
	 * 更改资金账户状态风险检测
	 * updateStatusControl 
	 * Base.CtuModule.Account.Balance.updateStatusControl
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function updateStatusControl($data) {
		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),					#  操作类型				* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
			
		# 前置检查(数据安全检查, 操作权限检查)
		$preCheck = $this->preCheck($data['tc_code'], $data['type']);
		if($preCheck !== 0 ) {
			return $this->res('', $preCheck);
		}
	
		# 线性检查
		return $this->res(true);
	}
	
	/**
	 * 获取CTU控制开关
	 * checkStatus 
	 * @access private
	 * @return void
	 */
	private function checkStatus() {
		return C('CTU_CHECK');
	}


	/**
	 * 前置检查
	 * preCheck 
	 * 
	 * @param mixed $ucCode 
	 * @access private
	 * @return void
	 */
	private function preCheck($tcCode, $type) {
		$where = array('tc_code'=>$tcCode);
		$res = D('TcMainAccount')->field('tc_code,code,free_balance,freeze_balance,total_balance,account_type,safe_code,status')->where($where)->find();
		$dataSafeCheck = $this->dataSafeCheck($res); # 数据安全检查
		if($dataSafeCheck !== 0)  {
			return $dataSafeCheck;
		}
 		
		$accessCheck = $this->accessCheck($res, $type); # 操作权限检查
		return $accessCheck;
	}

	/**
	 * 数据安全检查
	 * dataSafeCheck 
	 * 
	 * @param mixed $ucCode 
	 * @access private
	 * @return void
	 */
	private function dataSafeCheck($safeData) {
		$safeData2 = $safeData['safe_code'];
		unset($safeData['safe_code']);
		$safeCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkSafeCode', $safeData);
		if($safeCode['response'] !== $safeData2) {
			return 2509;
		}else {
			return 0;
		}
		
	}

	/**
	 * 操作权限检查
	 * accessCheck 
	 * 
	 * @param mixed $ucCode 
	 * @access private
	 * @return void
	 */
	private function accessCheck($data, $type) {
		if( empty($type) ) {
			return 5;
		}

		if( !isset($data['tc_code']) ){ #　账户不存在
			return 2503;
		}
		switch ($type) {
		case TC_ACCOUNT_INCR_BY_B2B_ORDER: # 增加账户金额 系统禁用级别时增加失败
			if($data['status'] != 'SYS_DISABLED') {
				return 0;
			}else {
				return 2512;
			}
		case TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE: # 申请提现
			if($data['status'] == 'ENABLED' || $data['status'] == 'SYS_ENABLED') {
				return 0;
			}else {
				return 2516;
			}
		case TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE: # 提现成功释放余额 最高禁用级别下会释放失败
			if($data['status'] != 'SYS_DISABLED') { 
				return 0;
			}else {
				return 2520;
			}
		
		case TC_ACCOUNT_INCR_BY_P2P_CASH_BACK: # 申请被驳回 释放到可用金额 最高禁用级别下会释放失败
			if($data['status'] != 'SYS_DISABLED') { 
				return 0;
			}else {
				return 2521;
			}

		case TC_ACCOUNT_DESC_NONE: # 减少部分可用余额
			if($data['status'] != 'SYS_DISABLED') {
				return 0;
			}else {
				return 2523;
			}

		case TC_ACCOUNT_CMS_DISABLED: # 管理员禁用
			if($data['status'] == 'ENABLED' || $data['status'] == 'SYS_ENABLED') {
				return 0;
			}else {
				return 2504;
			}
		case TC_ACCOUNT_CMS_ENABLED: # 管理员启用
			if($data['status'] == 'DISABLED') {
				return 0;
			}else {
				return 2505;
			}

		case TC_ACCOUNT_SYS_DISABLED: # 系统禁用
			if($data['status'] == 'SYS_ENABLED') {
				return 0;
			}else {
				return 2506;
			}

		case TC_ACCOUNT_SYS_ENABLED: # 系统启用
			if($data['status'] == 'SYS_DISABLED') {
				return 0;
			}else {
				return 2507;
			}
		case TC_ACCOUNT_DESC_BY_PREPAYMENT:
			if($data['status'] != 'SYS_DISABLED' || $data['status'] != 'DISABLED') {
				return 0;
			}else {
				return 2523;
			}

		case TC_ACCOUNT_INCR_BY_PREPAYMENT:
			if($data['status'] != 'SYS_DISABLED') {
				return 0;
			}else {
				return 2512;
			}

		default:
			return 2508;

		}

	}


	/**
	 * 线性检查, 调用转发
	 * linearityCheck 
	 * 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	private function linearityCheck($data) {
		$type = $data['type'];
		switch($type) {
		case TC_ACCOUNT_INCR_BY_B2B_ORDER:		# 支付B2B订单
			return $this->res2( '', $this->checkincr($data)['status'] );

		case TC_ACCOUNT_INCR_BY_P2P_CASH_BACK:
			return $this->res2('', $this->checkBalance($data)['status']);
			
		case TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE: # 申请提现
			return $this->res2('', $this->checkBalance($data)['status']);

		case TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE:
			return $this->res2('', $this->checkBalance($data)['status']);

		case TC_ACCOUNT_DESC_NONE:
			return $this->res2('', $this->checkBalance($data)['status']);

		default:
			return $this->res2('', 2525);
		}
	}

	private function checkincr($data) { # 增加余额时的前置验证
		$where =array(
			'oc_code' => $data['oc_code']
		);
		# 判断是否创建过支付任务
//		$select = D('TcPayTask')->field('sum(trade_price) as amount')->where($where)->select();
//		if($select[0]['amount'] === null) {
//			return $this->res2('', 2528); # 不存在的支付任务
//		}

//		if( bccomp($data['money'], $select[0]['amount'], 2) !== 0 ) { # 支付任务中的金额和订单中的金额不一致
//			return $this->res2('', 2526);
//		}
		
		# 判断是否存在该订单
		$select = D('OcB2bOrder')->field('sum(amount) as amount, pay_status')->where(array('op_code'=>$data['oc_code']))->select();
		if($select[0]['amount'] === null) {
			return $this->res2('', 2532);
		}

		if( bccomp($data['money'], $select[0]['amount'], 2) !== 0 ) { # 和订单中的金额不一致
			return $this->res2('', 2526);
		}
		
//		if($select[0]['pay_status'] !== OC_ORDER_PAY_STATUS_PAY ) {
//			return $this->res2('', 2530);					# 订单未支付
//		}

		# 获取该笔订单的交易凭证
		$find = D('TcPayVoucher')->field('pay_status,price')->where($where)->find();
		if($find === null) {			# 订单的交易凭证不存在
			return $this->res2('', 2531);
		}
		
		if($find['pay_status'] !== TC_PAY_VOUCHER_PAY) {
			return $this->res2('', 2534); # 未支付
		}

		if( bccomp($data['money'], $find['price'], 2) !== 0 ) {
			return $this->res2('', 2533); # 和交易凭证中的金额不一致
		}

		return $this->res2(true);
	}

	private function checkBalance($data) { 
		$where = array(
			'tc_code' => $data['tc_code'],
		);
		$balance = D('TcMainAccount')->field('free_balance,tc_code,freeze_balance')->where($where)->find(); # 获取余额
		if($balance === null || !isset($balance['tc_code']) ) {
			return $this->res2('', 2537);
		}

		$table = $this->tablePrefix."tc_transaction_info";
		$tcCode = $balance['tc_code'];
		$a = TC_ACCOUNT_INCR_BY_B2B_ORDER;
		$b = TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE;
		$c = TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE;
		$d = TC_ACCOUNT_INCR_BY_P2P_CASH_BACK;
		$e = TC_ACCOUNT_DESC_NONE;
		$sql = "select
			(select sum(amount) as money from $table where tc_code='$tcCode' and type='$a' ) as a,
			(select sum(amount) as money from $table where tc_code='$tcCode' and type='$b' ) as b,
			(select sum(amount) as money from $table where tc_code='$tcCode' and type='$c' ) as c,
			(select sum(amount) as money from $table where tc_code='$tcCode' and type='$d' ) as d,
			(select sum(amount) as money from $table where tc_code='$tcCode' and type='$e' ) as e";
		
		$query = D()->query($sql);
		$a = $query[0]['a'];
		$b = $query[0]['b'];
		$c = $query[0]['c'];
		$d = $query[0]['d'];
		$e = $query[0]['e'];
		$freeBalance = $a - $b + $d - $e;
		$freezeBalance = $b - $c - $d;
		if( bccomp($freeBalance, $balance['free_balance']) !== 0 ) {
			return $this->res2('', 2535);
		}

		if( bccomp($freezeBalance, $balance['freeze_balance']) !== 0 ) {
			return $this->res2('', 2536);
		}
		return $this->res2(true);
	}

}

?>
