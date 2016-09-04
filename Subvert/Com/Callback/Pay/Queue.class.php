<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 支付队列回调接口
 */

namespace Com\CallBack\Pay;

use System\Base;

class Queue extends Base {

	private $_rule = null; # 验证规则列表

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 支付回调, 只支持队列调用
	 * Com.Callback.Pay.Queue.byPayCenter
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function byPayCenter($message) {
		$data = $message['message'];

		$this->_rule = array(
			array('op_code', 'require', PARAMS_ERROR, MUST_CHECK), 
			array('oc_code', 'require', PARAMS_ERROR, MUST_CHECK),  
			array('total_fee', 'require', PARAMS_ERROR, MUST_CHECK), 
			array('pay_time', 'require', PARAMS_ERROR, MUST_CHECK),		# 支付时间
			array('trade_no', 'require', PARAMS_ERROR, MUST_CHECK),		# 交易流水号
			array('pay_by', 'require', PARAMS_ERROR, MUST_CHECK),		# 支付方式
		);

		if (!$this->checkInput($this->_rule, $data)) { # 自动校验
			return $this->res($this->getErrorField(), $this->getCheckError());
		}

		if(empty($data) || $data['total_fee'] == 0) {
			return $this->res('', 6900); # 失败
		}

		$first_num = substr($data['op_code'], 0, 1);
		if($first_num == 1 || $first_num == 2) {
		}

		switch($first_num) {
		case OC_ORDER:
			$api = 'Bll.B2b.Pay.PayBack.exeByPayGetway';
			break;
		case OC_ADVANCE_ORDER:
			$api = 'Bll.B2b.Pay.PayBack.exeByPayGetway';
			break;
		default:
			return $this->res(null, 2552);
		}

		# 记录交易凭证
		$voucherData = array(
			'oc_code' => $data['oc_code'],
			'op_code' => $data['op_code'],
			'pay_time' => $data['pay_time'],
			'price' => $data['total_fee'],
			'pay_by'=> $data['pay_by'],
			'pay_status' => TC_PAY_VOUCHER_PAY,
			'pay_no' => $data['trade_no'],
			'pay_type' => $data['bank_type'],
			'pay_remark' => $data['attach'] ? $data['attach'] : '',
			'pay_task_status' =>  'UNDO',  # 未执
		);

		$call = $this->invoke("Base.TradeModule.Pay.Voucher.add", $voucherData);
		if($call['status'] !== 0) {
			return $this->res('', 6901); # 凭证写入失败
		}

		try{
			# 开启事务
			D()->startTrans();
	
			$voucherData = array(
				'op_code' => $data['op_code'],
			);

			$call = $this->invoke("Base.TradeModule.Pay.Voucher.update", $voucherData);
			if($call['status'] !== 0) {
				return $this->res('', 6905); # 凭证更新失败
			}

			$res = $this->invoke($api, $data);
			if($res['status'] !== 0) {
				return $this->res('', 2553); # 订单支付失败
			}

			# 提交事务
			$commit = D()->commit();
			if($commit == false) {
				D()->rollback();
				return $this->res('', 6904);
			}

		}catch(\Exception $e) {
			DG(['pay_error'=> ($e->getMessage())], SUB_DG_OBJECT);
			D()->rollback();
			return $this->endInvoke('', 6904);
		}

		if( $res['status'] === 0  ) {
			return $this->res(true);
		}

	}
}

?>
