<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 基础实现类
 */

namespace Base\TradeModule\Account;
use System\Base;

class Factory extends Base {

	private $_rule	=	null; # 验证规则列表

    public function __construct() {
        parent::__construct();
	}
	/**
	 * 减少可用余额
	 * Base.TradeModule.Account.Factory.desc
	 * @access public
	 * @return void
	 */
	public function desc($data) {
		$action  = $data['action'];
		$changeType = $data['changeType'];
		
		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;

		# 获取操作句柄[包含账户的操作权限]
		$control = $data['oc_code'].':'.$changeType;
		$handle = $this->getAccontHandle($control, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)(二次封装)
		if( !is_object($handle) ) {
			return $this->res('', $handle); # 获取操作句柄失败
		}

		# 更新账户余额
		$update =  $this->updateAccountBalance($data, $action, $changeType);
		if($update['status'] !== 0) {
			return $this->res('', $update['status']);
		}

		# 关闭操作句柄
		$handle->closeHandle($tcCode);

		return $this->res(true);
	}

	/**
	 * 增加指定金额到资金账户中
	 * Base.TradeModule.Account.Factory.incr
	 * @access public
	 * @return void
	 */
	public function incr($data) {
		$action  = $data['action'];
		$changeType = $data['changeType'];
		
		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;

		# 获取操作句柄[包含账户的操作权限]
		$control = $data['oc_code'].':'.$changeType;
		$handle = $this->getAccontHandle($control, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)(二次封装)
		if( !is_object($handle) ) {
			return $this->res('', $handle); # 获取操作句柄失败
		}
		
		# 更新账户余额
		$update =  $this->updateAccountBalance($data, $action, $changeType);
		if($update['status'] !== 0) {
			return $this->res('', $update['status']);
		}
		
		# 关闭操作句柄
		$handle->closeHandle($tcCode);

		return $this->res(true);
	}

	
	/**
	 * 创建资金账户
	 * Base.TradeModule.Account.Factory.add
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function add($data) {

		# 生成主账户编码
		$tcMainAccountCodeData = array(
			"busType" => TC_ACCOUNT,
			"preBusType" => $data['preBusType'],  # 帐户二级编码
			"codeType" => SEQUENCE_ACCOUNT
		);

		$accountCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $tcMainAccountCodeData);
		if( $accountCode['status'] !== 0 ) {
			return $this->res('', 2500); # 生成编码失败
		}

		# 拼装账户数据
		$tcMainAccountData = array(
			'tc_code' => $accountCode['response'],
			'code' => $data['code'],
			'free_balance' => '0.00',
			'freeze_balance' =>'0.00',
			'total_balance'=>'0.00',
			'account_type' => $data['accountType'], # 帐户类型
			'ext1' => $data['ext1'],		# 扩展字段
			'status' => $data['status'] ? $data['status'] : "ENABLED"
		);
		$otherData = array(
			'update_time' => NOW_TIME,
			'create_time' => NOW_TIME,
			'version' => 1,
		);

		# 生成安全效验码
		$safeCodeData = array(
			"tc_code" => $tcMainAccountData['tc_code'],
			"code" => $tcMainAccountData['code'],
			"free_balance" => $tcMainAccountData['free_balance'],
			"freeze_balance" => $tcMainAccountData['freeze_balance'],
			"total_balance" => $tcMainAccountData['total_balance'],
			"account_type" => $tcMainAccountData['account_type'],
			"status" => $tcMainAccountData['status'],
		);
		$safeCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkSafeCode', $safeCodeData);
		if($safeCode['status'] !== 0) {
			return $this->res('', 2501); # 安全码生成失败
		}

		$tcMainAccountData['safe_code'] = $safeCode['response'];
		$tcMainAccountCodeData = array_merge($tcMainAccountData, $otherData);

		# 插入数据
		$res = D('TcMainAccount')->data($tcMainAccountCodeData)->add();
		if($res >= 1) {
			return $this->res($tcMainAccountData['tc_code']);
		}else {
			return $this->res(null, 5002); # 资金账户生成失败
		}
		
	}
	/**
	 * 添加对资金账户余额操作的异步任务
	 * addBalanceAsyncTask
	 * Base.TradeModule.Account.Balance.addBalanceAsyncTask
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function addBalanceAsyncTask($data) {
		# 定义异步任务的型
		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  订单编码				* 必须字段
			array('operator_uc_code', 'require' , PARAMS_ERROR, MUST_CHECK),	    #  操作者用户编码		* 必须字段
			array('operator_ip_addr', 'require' , PARAMS_ERROR, MUST_CHECK),		#  操作者ip地址			* 必须字段
			array('money', 'require' , PARAMS_ERROR, MUST_CHECK),					#  增加的金额			* 必须字段
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),					#  操作类型(参考常量表) * 必须字段
			array('remark', 'require' , PARAMS_ERROR, ISSET_CHECK),					#  备注信息				  非必须
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$api = $this->parseApiName($data['type']);
		if($api === false) {
			return $this->res('', 2524);
		}
		$taskData = array(
			'unique_str' => $data['oc_code'],
			'params' => json_encode($data),
			'api' => $api,
			'type' => $data['type'],
			'run_time' => NOW_TIME,
			'allow_err_num' => 0,
		);
		
		$task = $this->invoke('Com.Crontab.Scan.Create.addTask', $taskData);
		
		if($task['status'] !== 0) {
			return $this->res('', $task['status']);
		}

		return $this->res($task['response']);

	}

	/**
	 * 根据操作类型转换对应要调用的api名称
	 * parseApiName 
	 * @param mixed $type 
	 * @access private
	 * @return api_name
	 */
	private function parseApiName($type) {
		switch($type) {
		case TC_ACCOUNT_INCR_BY_B2B_ORDER:
			return 'Base.TradeModule.Account.Balance.incr';

		case TC_ACCOUNT_INCR_BY_P2P_CASH_BACK:
			return 'Base.TradeModule.Account.Balance.freezeBack';

		case TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE:
			return 'Base.TradeModule.Account.Balance.freeze';

		case TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE:
			return 'Base.TradeModule.Account.Balance.unfreeze';

		case TC_ACCOUNT_DESC_NONE:
			return 'Base.TradeModule.Account.Balance.desc';

		default: 
			return false;
		}	
	}




	/**
	 * 冻结部分余额
	 * incr 
	 * Base.TradeModule.Account.Balance.freeze
	 * @access public
	 * @return void
	 */
	
	public function freeze($data) {
		# 确定当前的操作类型;
		$action  = TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE;
		$changeType = TC_ACCOUNT_DESC;
		
		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;

		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  订单编码				* 必须字段
			array('operator_uc_code', 'require' , PARAMS_ERROR, MUST_CHECK),	    #  操作者用户编码		* 必须字段
			array('operator_ip_addr', 'require' , PARAMS_ERROR, MUST_CHECK),		#  操作者ip地址			* 必须字段
			array('money', 'require' , PARAMS_ERROR, MUST_CHECK),					#  增加的金额			* 必须字段
			array('remark', 'require' , PARAMS_ERROR, ISSET_CHECK),					#  备注信息				非必须
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 开启外部事务模式
		$this->startOutsideTrans();

		# 风控分析(权限检查, 安全检查, 线性检查)
		$ctuInfo = $this->invoke('Base.CtuModule.Account.Balance.freezeControl', $data);
		if($ctuInfo['status'] !== 0) {
			return $this->res('', $ctuInfo['status']);
		}

		# 获取操作句柄[包含账户的操作权限]
		$control = $data['oc_code'].':'.$changeType;
		$handle = $this->getAccontHandle($control, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)(二次封装)
		if( !is_object($handle) ) {
			return $this->res('', $handle); # 获取操作句柄失败
		}
		# 更新账户余额
		$update =  $this->updateAccountBalance($data, $action, $changeType, 'addFreezeData');
		if($update['status'] !== 0) {
			return $this->res('', $update['status']);
		}
		# 关闭操作句柄
		$handle->closeHandle($tcCode);

		return $this->res(true);
	}

	/**
	 * 释放冻结的金额到余额中
	 * freezeBack
	 * Base.TradeModule.Account.Balance.freezeBack
	 * @access public
	 * @return void
	 */
	
	public function freezeBack($data) {
		# 确定当前的操作类型;
		$action  = TC_ACCOUNT_INCR_BY_P2P_CASH_BACK;
		$changeType = TC_ACCOUNT_INCR;
		
		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;

		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  订单编码				* 必须字段
			array('operator_uc_code', 'require' , PARAMS_ERROR, MUST_CHECK),	    #  操作者用户编码		* 必须字段
			array('operator_ip_addr', 'require' , PARAMS_ERROR, MUST_CHECK),		#  操作者ip地址			* 必须字段
			array('money', 'require' , PARAMS_ERROR, MUST_CHECK),					#  增加的金额			* 必须字段
			array('remark', 'require' , PARAMS_ERROR, ISSET_CHECK),					#  备注信息				非必须
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 开启外部事务模式
		$this->startOutsideTrans();

		# 风控分析(权限检查, 安全检查, 线性检查)
		$ctuInfo = $this->invoke('Base.CtuModule.Account.Balance.freezeBackControl', $data);
		if($ctuInfo['status'] !== 0) {
			return $this->res('', $ctuInfo['status']);
		}
		
		# 获取操作句柄[包含账户的操作权限]
		$control = $data['oc_code'].':'.$changeType;
		$handle = $this->getAccontHandle($control, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)(二次封装)
		if( !is_object($handle) ) {
			return $this->res('', $handle); # 获取操作句柄失败
		}
		# 更新账户余额
		$update =  $this->updateAccountBalance($data, $action, $changeType, 'updateFreezeData');
		if($update['status'] !== 0) {
			return $this->res('', $update['status']);
		}
		# 关闭操作句柄
		$handle->closeHandle($tcCode);

		return $this->res(true);
	}
	
	/**
	 * 释放冻结的金额到余额中
	 * unfreeze
	 * Base.TradeModule.Account.Balance.unfreeze
	 * @access public
	 * @return void
	 */
	
	public function unfreeze($data) {
		# 确定当前的操作类型;
		$action  = TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE;
		$changeType = TC_ACCOUNT_DESC;
		
		# 封装上下文参数
		$tcCode = $data['tc_code'];
		$data['type'] = $action;

		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  用户编码				* 必须字段
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),					#  订单编码				* 必须字段
			array('operator_uc_code', 'require' , PARAMS_ERROR, MUST_CHECK),	    #  操作者用户编码		* 必须字段
			array('operator_ip_addr', 'require' , PARAMS_ERROR, MUST_CHECK),		#  操作者ip地址			* 必须字段
			array('money', 'require' , PARAMS_ERROR, MUST_CHECK),					#  增加的金额			* 必须字段
			array('remark', 'require' , PARAMS_ERROR, ISSET_CHECK),					#  备注信息				非必须
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 开启外部事务模式
		$this->startOutsideTrans();

		# 风控分析(权限检查, 安全检查, 线性检查)
		$ctuInfo = $this->invoke('Base.CtuModule.Account.Balance.unfreezeControl', $data);
		if($ctuInfo['status'] !== 0) {
			return $this->res('', $ctuInfo['status']);
		}
		
		# 获取操作句柄[包含账户的操作权限]
		$control = $data['oc_code'].':'.$changeType;
		$handle = $this->getAccontHandle($control, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)(二次封装)
		if( !is_object($handle) ) {
			return $this->res('', $handle); # 获取操作句柄失败
		}
		# 更新账户余额
		$update =  $this->updateAccountBalance($data, $action, $changeType, 'updateFreezeData');
		if($update['status'] !== 0) {
			return $this->res('', $update['status']);
		}
		# 关闭操作句柄
		$handle->closeHandle($tcCode);

		return $this->res(true);
	}
	/**
	 * 申请提现操作
	 * 添加冻结记录信息
	 * 后置操作函数
	 * addFreezeData 
	 * @access private
	 * @return void
	 */
	private function addFreezeData(&$data) {
		# 写入冻结金额记录表
		$freezeData = array(
			'tc_code' => $data['tc_code'],
			'oc_code' => $data['oc_code'],
			'tc_trade_no' => $data['trade_no'],
			'freeze_money' => $data['money'],
			'status' => TC_ACCOUNT_CASH_APPLY,
			'create_time' => NOW_TIME,
			'update_time' => NOW_TIME
		);
		
		$insert = D('TcAccountFreezeAction')->add($freezeData);
		if($insert <= 0 ) {
			return $this->res('', 2518);
		}

		return $this->res(true);
	}

	/**
	 * 更新冻结记录表信息
	 * 后置操作函数
	 * updateFreezeData
	 * @access private
	 * @return void
	 */

	private function updateFreezeData(&$data) {
		$where = array(
			'oc_code' => $data['oc_code'],
			'tc_code' => $data['tc_code'],
			'status' => TC_ACCOUNT_CASH_APPLY,  # 前置更新条件是必须处于申请状态
			'freeze_money' => $data['money']
		);
		if($data['type'] == TC_ACCOUNT_INCR_BY_P2P_CASH_BACK) {
			$save = array('status' => TC_ACCOUNT_CASH_CLOSE);

		}elseif($data['type'] == TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE) {
			$save = array('status' => TC_ACCOUNT_CASH_PASS);
		}

		$save['update_time'] = NOW_TIME;

		# 写入数据
		$update = D('TcAccountFreezeAction')->where($where)->save($save);
		if($update !== 1) {
			return $this->res('', 2522);
		}

		return $this->res(true);
	}

	/**
	 * 核心方法, 更新账户余额
	 * updateAccountBalance 
	 * @param mixed $data  调用传参
	 * @param mixed $action 操作类型
	 * @param mixed $changeType 变更类型
	 * @param mixed $afterFuntion 完成后回调方法
	 * @access private 内部方法
	 * @return void
	 */
	private function updateAccountBalance($data, $action, $changeType, $afterFuntion = '') {

		$tcCode = $data['tc_code'];
		$amount = $data['amount'];
		if(!bccomp($amount, 0, 2)) {  # 余额是否为0
			return $this->res2('', 2538);
		}

		# 从主库查询账户数据并且加悲观锁
		$accountInfo = D('TcMainAccount')->where(array('tc_code'=>$tcCode))->master()->lock(true)->select();
		$accountInfo = $accountInfo[0];

		# 解析操作类型
		$actionData = $this->parseAction($action, $amount, $accountInfo);
		if($actionData['status'] !== 0 ) {
			return $this->res2('', $actionData['status']);
		}

		# 修改金额
		$where = $actionData['response']['where'];
		$where['tc_code'] = $tcCode; # 索引字段
		$save = $actionData['response']['save'];
		$save['update_time'] = NOW_TIME;

		$update = D('TcMainAccount')->where($where)->save($save);
		if($update !== 1) {
			return $this->res2('', 2515);
		}

		# 生成账户流水编码
		$tcMainAccountCodeData = array(
			"busType" => FLOW_NO,
			"preBusType" => TC_ACCOUNT_TRADE_NO,
			"codeType" => SEQUENCE_TRADE_NO
		);
		
		$accountCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $tcMainAccountCodeData);
		if( $accountCode['status'] !== 0) {
			return $this->res2('', 2500); # 生成编码失败
		}

		# 记录交易流水信息
		$data2 = array(
			'tc_code' => $accountInfo['tc_code'],
			'trade_no' => $accountCode['response'],
			'change_type' => $changeType,
			'before_money' => $accountInfo['free_balance'],
			'before_freeze' => $accountInfo['freeze_balance'],
			'create_time' => NOW_TIME,
			'last_money' => $actionData['response']['last_money'],
			'last_freeze' => $actionData['response']['last_freeze'],
			'code' => $accountInfo['code'],
			'safe_code' => '',
			'operator_type' => $data['operator_type']
		);
		$data = array_merge($data, $data2);
		$save = D('TcTransactionInfo')->add($data);
		if($save <= 0) {
			return $this->res2('', 25); # 交易流水写入失败
		}
		# 清场动作
		$doOther = $this->doOther($tcCode);
		if($doOther['status'] !== 0 ) {
			return $this->res2($doOther['response'], $doOther['status']);
		}
		# 判断后置操作
		if( !empty($afterFuntion) ) {
		$call  = $this->$afterFuntion($data);
			return $this->res2('', $call['status']);
		}else {
			return $this->res2(true);
		}
	}
	
	/**
	 * 解析余额操作常量
	 * parseAction 
	 * 
	 * @param mixed $action 
	 * @param mixed $changeType 
	 * @access private
	 * @return 返回操作表达式
	 */
	private function parseAction($action, $money, $accountInfo) {
		switch($action) {
		case TC_ACCOUNT_INCR_BY_B2B_ORDER : # 订单支付  free_balance + 
			$data = array(
				'where' => array(
					'free_balance' =>array('eq', $accountInfo['free_balance'])
				),
				'save' => array(
					'free_balance' => array('+', $money),
					'total_balance' => array('+', $money),
				),
				'last_money' => bcadd( $money , $accountInfo['free_balance'] , 2),
				'last_freeze' => $accountInfo['freeze_balance'],
			);
			return $this->res2($data);
		case  TC_ACCOUNT_INCR_BY_PREPAYMENT: # 订单支付  free_balance + 
			$data = array(
				'where' => array(
					'free_balance' =>array('eq', $accountInfo['free_balance'])
				),
				'save' => array(
					'free_balance' => array('+', $money),
					'total_balance' => array('+', $money),
				),
				'last_money' => bcadd( $money , $accountInfo['free_balance'] , 2),
				'last_freeze' => $accountInfo['freeze_balance'],
			);
			return $this->res2($data);	
		case TC_ACCOUNT_DESC_BY_P2P_CASH_FREEZE: # 提现申请冻结 free_balance - , freeze_balance +
			$data = array(
				'where' => array(
					'free_balance' =>array('eq', $accountInfo['free_balance']),
					'freeze_balance' => array('eq', $accountInfo['freeze_balance']),
					'free_balance' => array('egt', $money),
				),
				'save' => array(
					'free_balance' => array('-', $money),
					'freeze_balance' => array('+', $money)
				),
				'last_money' => bcsub( $accountInfo['free_balance'] , $money,  2),
				'last_freeze' => bcadd( $accountInfo['freeze_balance'], $money, 2 ),
			);
			return $this->res2($data);

		case TC_ACCOUNT_DESC_BY_P2P_CASH_UNFREEZE: # 提现成功释放 freeze_balance -
			$data = array(
				'where' => array(
					'freeze_balance' => array('eq', $accountInfo['freeze_balance']),
					'freeze_balance' => array('egt', $money),
				),
				'save' => array(
					'freeze_balance' => array('-', $money)
				),
				'last_money' => $accountInfo['free_balance'] ,
				'last_freeze' => bcsub( $accountInfo['freeze_balance'], $money, 2 ),
			);
			return $this->res2($data);

		case TC_ACCOUNT_INCR_BY_P2P_CASH_BACK: # 提现驳回冻结金额释放 freeze_balance -, free_balance +
			$data = array(
				'where' => array(
					'freeze_balance' => array('eq', $accountInfo['freeze_balance']),
					'freeze_balance' => array('egt', $money),
				),
				'save' => array(
					'freeze_balance' => array('-', $money),
					'free_balance' => array('+', $money)
				),
				'last_money' => bcadd( $accountInfo['free_balance'], $money, 2 ),
				'last_freeze' => bcsub( $accountInfo['freeze_balance'], $money, 2 ),
			);
			return $this->res2($data);

		case TC_ACCOUNT_DESC_NONE :  # 减少部分可用余额  free_balance -
			$data = array(
				'where' => array(
					'free_balance' => array('eq', $accountInfo['free_balance']),
					'free_balance' => array('egt', $money),
				),
				'save' => array(
					'free_balance' => array('-', $money)
				),
				'last_money' => bcsub( $accountInfo['free_balance'], $money, 2 ),
				'last_freeze' => $accountInfo['freeze_balance'],
			);
			return $this->res2($data);

		case TC_ACCOUNT_DESC_BY_PREPAYMENT:  # 减少部分可用余额  free_balance -
			$data = array(
				'where' => array(
					'free_balance' => array('eq', $accountInfo['free_balance']),
					'free_balance' => array('egt', $money),
				),
				'save' => array(
					'free_balance' => array('-', $money)
				),
				'last_money' => bcsub( $accountInfo['free_balance'], $money, 2 ),
				'last_freeze' => $accountInfo['freeze_balance'],
			);
			return $this->res2($data);
		default:
			return $this->res2('', 2514);
		}
	}
	
	/**
	 * 更新资金账户的状态信息
	 *
	 * updateStatus 
	 * Base.TradeModule.Account.Balance.updateStatus
	 * 
	 * @access public
	 * @return boolean
	 */
	public function updateStatus($data) {
		$this->startOutsideTrans();  # 外部事务模式
		$this->_rule = array(
			array('tc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 风险检测
		$checkAccount = $this->invoke('Base.CtuModule.Account.Balance.updateStatusControl', $data);
		if($checkAccount['status'] !== 0) {
			return $this->res('', $checkAccount['status']);
		}
		
		# 获取账户的操作句柄
		$tcCode = $data['tc_code'];
		$type = $data['type'];
		$handle = $this->getAccontHandle($tcCode, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)
		
		if( !is_object($handle) ) {
			return $this->res('', $handle); # 获取操作句柄失败
		}

		# 更改账户状态
		$ACCOUNT = D('TcMainAccount');
		$save = array(
			'status' => $type,
			'update_time' => NOW_TIME
		);
		$update = $ACCOUNT->where( array('tc_code'=>$tcCode) )->save($save);
		
		if($update !== 1) {
			return $this->res($type, 2511);
		}
		
		# 清理和记录数据现场
		$doOther = $this->doOther($tcCode);
		if($doOther['status'] !== 0 ) {
			return $this->res($doOther['response'], $doOther['status']);
		}
		
		# 释放账户操作句柄, 线程异常退出时则key超时前无法操作资金账户
		$handle->closeHandle($tcCode);

		return $this->res(true);
		
	}

	/**
	 * 做一些资金账户操作完毕后的清场动作 内部调用
	 * doOther
	 * 
	 * @access private
	 * @return void
	 */
	private function doOther($tcCode) {
		
		# 记录数据版本(通用) 
		$dataVersion = array(
			'mainTable' => 'TcMainAccount',
			'versionTable' => 'TcMainAccountVersion',
			'where' => array('tc_code'=>$tcCode),
		);

		$updateversion = $this->invoke('Com.Common.DataVersion.Mysql.add', $dataVersion);
		if($updateversion['status'] !== 0 ) {
			return $this->res2($updateversion['response'], $updateversion['status']);
		}
		
		# 生成新的安全码并且写入(通用)
		$safeData = array(
			'safeTable' => 'TcMainAccount',
			'where' => array('tc_code'=>$tcCode),
			'useFields' => 'tc_code,code,free_balance,freeze_balance,total_balance,account_type,status',
			'updateField' => 'safe_code',
		);

		$safeCode = $this->invoke('Com.Tool.Code.CodeGenerate.saveSafeCode', $safeData);

		if($safeCode['status'] !== 0) {
			return $this->res2($safeCode['response'], $safeCode['status']); # 安全码生成失败
		}

		return $this->res2(true);

	}



	/**
	 * 获取账号的操作权限
	 *
	 * getAccontHandle 
	 * 
	 * @param mixed $ucCode 
	 * @param mixed $type 
	 * @access private
	 * @return max 成功返回操作句柄 失败返回错误号
	 */
	private function getAccontHandle($tcCode, $expires) {
		# 获取操作句柄
		$handle = $this->invoke('Com.Tool.Handle.Redis.createHandle', array('uniqueString'=>$tcCode, 'expires'=> $expires));
		if($handle['status'] === 0) {
			return $handle['response'];  # return obj
		}else {
			return $handle['status'];
		}

	}
}

?>
