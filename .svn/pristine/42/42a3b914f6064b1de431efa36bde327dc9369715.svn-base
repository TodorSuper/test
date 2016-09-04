<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 创建相关支付任务
 */

namespace Base\TradeModule\Pay;
use System\Base;

class Task extends Base {

    public function __construct() {
        parent::__construct();
    }

	/**
	 * 创建支付任务
	 * Base.TradeModule.Pay.Task.createPayTask
	 * @access public
	 * @return void
	 */
	public function createPayTask($data) {
		$this->_rule = array(							
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),		# 订单编码
			array('op_code', 'require' , PARAMS_ERROR, MUST_CHECK),		# 付款单编码
			array('total_fee', 'require' , PARAMS_ERROR, MUST_CHECK),	# 支付金额
			array('getway', 'require' , PARAMS_ERROR, MUST_CHECK),		# 支付方式
			array('open_id', 'require' , PARAMS_ERROR, ISSET_CHECK),	# 微信需要字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		
		$data['gateway'] = $data['getway'];  # 网关字段映射
		
		$amount =  $data['total_fee'] * 100;
		if(bccomp($amount, intval($amount) , 2)) {
			return $this->res(null, 2554); # 金额不匹配
		}else {
			$data['total_fee'] = $amount;
		}
		
		switch($data['gateway']) {
		case WEIXIN_JSAPI_PAY:
			$res = $this->invoke('Base.PayCenter.Task.PayTask.Create', $data);
			$res['response'] = json_encode($res['response'], JSON_UNESCAPED_UNICODE);
			break;
		case ALIPAY_WAP:
			$data['notify_url'] = C('CASHIER_URL_CONF.ALIPAY_NOTIFY');
			$res = $this->invoke('Base.PayCenter.Task.PayTask.Create', $data);
			break;
		}
		if($res['status'] !== 0) {
			return $this->res('', 2544); # 创建支付任务失败
		}
		#$res['response'] = htmlspecialchars($res['response']); # 调试断点
		return $this->res($res['response']);
	}

	/**
	 * 创建alipay-wap支付任务
	 * createAlipayWap 
	 * @param mixed $data 
	 * @access private
	 * @return void
	 */
	private function createAlipayWap(&$data) {
		$url = C('CASHIER_URL_CONF');
		
		$table = $this->rpc($url['ALIPAY_WAP_TASK'], $data); # 创建支付表单
		return $table;
	}
	/**
	 * 创建微信支付任务
	 * createWxJsPay 
	 * @access private
	 * @return void
	 */
	private function createWxJsPay(&$data) {
		$url = C('CASHIER_URL_CONF');
		$table = $this->rpc($url['PAY_TASK'], $data); # 创建支付表单
		$table = json_decode($table, true);
		return $table['js'];
	}

	/**
	 * 获取银行对账单
	 * Base.TradeModule.Pay.Task.getPayStatement 
	 * @access public
	 * @return void
	 */
	public function getPayStatement($data) {
		
		$this->_rule = array(										
			array('statement_time', 'require' , PARAMS_ERROR, MUST_CHECK),		# 账单时间   20150817
			array('statement_type', 'require' , PARAMS_ERROR, MUST_CHECK),		# 账单类型	 ALL 全部
			array('pay_type', 'require' , PARAMS_ERROR, MUST_CHECK),			# 支付方式
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		if($data['pay_type'] == WEIXIN) {
			$data['statement_time'] = date('Ymd', strtotime($data['statement_time']));
			$data['sign'] = $this->mkCashierSign($data); # 创建签名
			$url = C('CASHIER_URL_CONF'); 
			$file = $this->rpc($url['STATEMENT'], $data); # 创建支付表单
			$status = empty($file)? 2546: 0;
			return $this->res($file, $status);
		}else {
			return $this->res('', 2545);
		}
	}
    
}

?>
