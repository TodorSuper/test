<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 资金账户相关订单
 */

namespace Base\OrderModule\Cash;

use System\Base;

class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 创建资金账户相关订单
     * Base.OrderModule.Cash.Order.addCash
     * @param type $params
	 */
	public function addCash($data) {
        $this->startOutsideTrans();  # 必须开始事务
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),				# 用户编码			* 必须字段	
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),				# 店铺编码			* 必须字段
            array('tc_code', 'require', PARAMS_ERROR, MUST_CHECK),				# 账户编码			* 必须字段
            array('money', 'currency',	PARAMS_ERROR, MUST_CHECK),				# 提现金额			* 必须字段
			array('operator_ip_addr', 'require' , PARAMS_ERROR, MUST_CHECK),	# 操作者ip地址		* 必须字段
			array('remark', 'require' , PARAMS_ERROR, ISSET_CHECK),				# 备注信息			非必须
		);
        if (!$this->checkInput($this->_rule, $data)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
		# 生成提现单据
		$codeData = array(
			"busType" => OC_ORDER,
			"preBusType" => OC_CASH_ORDER_APPLY,
			"codeType" => SEQUENCE_CASH_ORDER
		);

		$accountCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $codeData);
		if( $accountCode['status'] !== 0) {
			return $this->res('', 2500); # 生成编码失败
		}
		# 创建订单
		$ocData = array(
			'uc_code'=> $data['uc_code'],
			'sc_code'=> $data['sc_code'],
			'tc_code'=> $data['tc_code'],
			'oc_code'=> $accountCode['response'],
			'merchant_mark' =>$data['merchant_mark'],
			'version'=> 1,
			'status' => 'UNCHECKED',
			'create_time' => NOW_TIME,
			'update_time' => NOW_TIME,
			'money' => $data['money'],
		);
		
		$insert = D('OcCash')->add($ocData);
		if(!$insert) {
			return $this->res('', 6011);
		}

		# 冻结余额
		$freezeData = array(
			'tc_code' => $data['tc_code'],
			'oc_code'=> $accountCode['response'],
			'operator_uc_code'=> $data['uc_code'],
			'operator_ip_addr'=> $data['operator_ip_addr'],
			'money' => $data['money'],
			'remark' => $data['merchant_mark']
		);
		$createFreeze = $this->invoke('Base.TradeModule.Account.Balance.freeze', $freezeData);
		if($createFreeze['status'] !== 0) {
			return $this->res('', 6012);
		}

		return $this->res(true);
	}

	/**
	 * 更新提现单状态
	 * Base.OrderModule.Cash.Order.updateCashOrder
	 * @access public
	 * @return void
	 */
	public function updateCashOrder($data) {
		$this->startOutsideTrans(); # 开启事务
        $this->_rule = array(
            array('oc_code', 'require', PARAMS_ERROR, MUST_CHECK),				# 订单编码			* 必须字段	
            array('status', array('CHECKED','REJECT','CONFIRM'), PARAMS_ERROR, MUST_CHECK, 'in'),	# 订单状态			* 必须字段
			array('check_mark', 'require' , PARAMS_ERROR, ISSET_CHECK),			# 审核备注			 必须字段
		);
        if (!$this->checkInput($this->_rule, $data)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
		}

		# 获取订单信息
		$order = D('OcCash')->field('sc_code,money')->where(['oc_code'=>$data['oc_code']])->find();

		# 获取店铺信息
		$store = $this->invoke('Base.StoreModule.Basic.Store.get', ['sc_code'=>$order['sc_code']]);
		if($store['status'] !== 0 || !$store['response']['tc_code']) {
			return $this->res('', 6019);
		}

		$where = array(
			'oc_code' => $data['oc_code'],
			'sc_code' => $order['sc_code'],
			'status' => ['eq', 'UNCHECKED']
		);

		# 修改订单状态
		$update = D('OcCash')->where($where)->save(['status'=>$data['status'], 'check_mark'=>$data['check_mark']]);

		if($update !== 1) {
			$this->res('', 6018);
		}
		
		# 记录数据版本(通用) 
		$dataVersion = array(
			'mainTable' => 'OcCash',
			'versionTable' => 'OcCashVersion',
			'where' => array('oc_code'=>$data['oc_code']),
		);

		$updateversion = $this->invoke('Com.Common.DataVersion.Mysql.add', $dataVersion);
		if($updateversion['status'] !== 0 ) {
			return $this->res($updateversion['response'], $updateversion['status']);
		}

		# 金额冻结或驳回 
		$balanceData = [
			'tc_code' => $store['response']['tc_code'],
			'oc_code'=> $data['oc_code'],
			'operator_uc_code'=> $data['operator_uc_code'],
			'operator_ip_addr'=> $data['operator_ip_addr'],
			'money' => $order['money'],
			'remark' => $data['check_mark'],
		];
		$unfreeze = $this->invoke('Base.TradeModule.Account.Balance.unfreeze', $balanceData);
		if($unfreeze['status'] === 0) {
			return $this->res(true);
		}else {
			return $this->res('', 6020);
		}

	}
}













