<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 支付凭证操作类
 */

namespace Base\TradeModule\Pay;
use System\Base;

class Voucher extends Base {
	private $_rule	=	null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
	
	/**
	 * 添加支付凭证
	 * Base.TradeModule.Pay.Voucher.add
	 * @access public
	 * @return void
	 */
	public function add($data) {
		$this->_rule = array(											# 字段类型参考数据库字段注释
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),	
			array('op_code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('pay_time', 'require' , PARAMS_ERROR, MUST_CHECK),	
			array('price', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('pay_by', 'require' , PARAMS_ERROR, MUST_CHECK),		
			array('pay_status', 'require' , PARAMS_ERROR, MUST_CHECK),	
			array('pay_no', 'require' , PARAMS_ERROR, MUST_CHECK),		
			array('pay_remark', 'require' , PARAMS_ERROR, ISSET_CHECK),	
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
	
		$check = $this->checkVoucher($data);
		if($check) {
			if($check['pay_task_status'] === 'OK') {
				return $this->endInvoke('exception: pay_task_status eq ok 1!');			# 中断执行
			}else {
				return $this->res(true);  # 如果存在则继续后续执行逻辑
			}
		}

		try{
			$data['create_time'] = NOW_TIME;
			$res = D('TcPayVoucher')->add($data);
			if($res <= 0 )  {
				return $this->res(null, 2541);
			}else {
				return $this->res(true);
			}
		}catch(\Exception $e) {
			$check = $this->checkVoucher($data);

			if($check['pay_task_status'] === 'OK') {   # 防止产生竟态写入, retry
				return $this->endInvoke('exception: pay_task_status eq ok 2!');			# 中断执行

			}elseif($check['pay_task_status'] === 'UNDO') {
				return $this->res(true);

			}else {
				throw $e; # 抛到上一层

			}
		}
	}	

	private function checkVoucher(&$data) {
		$where = array(
			'oc_code'=>$data['oc_code'], 
			'pay_by'=>$data['pay_by'],
		);
		$find = D('TcPayVoucher')->field('pay_task_status')->where($where)->find();
		return $find;
	}

	/**
	 * Base.TradeModule.Pay.Voucher.update
	 * @access public
	 * @return void
	 */
	public function update($data) {
		$this->_rule = array(						
			array('op_code', 'require' , PARAMS_ERROR, MUST_CHECK),
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		$where = array(
			'op_code'=>$data['op_code'],
		);
		$update = D('TcPayVoucher')->where($where)->save(['pay_task_status'=>'OK']);
		if($update <= 0 ) {
			return $this->res('', 2542);
		}

		return $this->res(true);
	}

	/**
	 * 获取在线支付明细
	 * Base.TradeModule.Pay.Voucher.getInfo 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function getInfo($data) {
		$this->_rule = array(						
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),  # 订单编号
			array('pay_by', 'require' , PARAMS_ERROR, MUST_CHECK),   # 支付类型
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$find = D('TcPayVoucher')->field('pay_data', true)->where(['op_code'=>$data['oc_code'],'pay_by'=>$data['pay_by'] ])->find();
		return $this->res($find);
	}


	public function getPayNo($data) {
		$this->_rule = array(						
			array('oc_code', 'require' , PARAMS_ERROR, MUST_CHECK),  # 订单编号
			array('pay_by', 'require' , PARAMS_ERROR, MUST_CHECK),   # 支付类型
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$find = D('TcPayVoucher')->field('pay_no')->where(['op_code'=>$data['oc_code'],'pay_by'=>$data['pay_by'] ])->find();
		return $this->res($find);
	}
}
?>
