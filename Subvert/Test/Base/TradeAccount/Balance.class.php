<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | debug api
 */

namespace Test\Base\TradeAccount;
use System\Base;
use System\Logs;

class Balance extends Base {

	/**
	 * 设置字段在数据库中的属性为不可读
	 * testPrivate 
	 * @access public
	 * @return void
	 */
	public function testPrivate($data) {

		$this->setPraviteField(array(
			'remark'=> FIELD_WRITE_DENY
		));

		D('Sequence')->where('type=9')->save($data);

	}

	/*
	 * Test.Base.TradeAccount.Balance.test_add
	*/
	public function test_add($data) {
//		$params = array();
//		$params['uc_code'] = "1";
		try{
			D()->startTrans();
			$data = array(
				'code' => "201510131945",
				'pCode' => "2015",
				'accountType' => TC_ACCOUNT_PREPAYMENT
			);

			$res = $this->invoke('Base.TradeModule.Account.Balance.add', $data);

			if($res['status'] === 0 ){
				D()->commit();
			}else {
				L($res);
//				throw new \Exception($res['response']);
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			D()->rollback();
			L($res);
			throw $e;
		}
	}

	public function test_updateStatus($params) {
		try{
			D()->startTrans();
			$params = array(
				'uc_code' => 11,
				'pre_bus_type' => POP_CODE_SC,
				'data' => ['phone'=>"18625696323"]
			);
			$res = $this->invoke('Base.StoreModule.Basic.Store.add', $params);
			if($res['status'] === 0 ){
				D()->commit();
			}else {
				throw new \Exception($res['response']);
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			D()->rollback();
			L($res);
			throw $e;
		}

	}
	
	# Test.Base.TradeAccount.Balance.test_incr
	public function test_incr($data) { # 增加资金账户


		$data = array(
			'tc_code' => '1230000000109',
			'oc_code' => '1034567890',
			'operatorType' => 'TEST',
			'operatorIp' => '192.168.0.1',
			'operatorName' => 'SYS',
			'amount' => 50,
			'remark' => 'HEELO WORLD2',
		);

		try{
			D()->startTrans();
			$res = $this->invoke('Base.TradeModule.Account.Balance.incr', $data);
			if($res['status'] === 0 ){
				D()->commit();
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			L($e->getMessage());
			D()->rollback();
			L($res);
		}
		
	}

	public function test_freeze($params) { # 冻结余额
		C('CTU_CHECK', false);
		$data = array(
			'uc_code' =>'1120000000101',
			'oc_code'=>23,
			'operator_uc_code'=> 12,
			'operator_ip_addr'=> '192.168.11.108',
			'money' => 100,
			'remark' => 'test'
		);

		try{
			D()->startTrans();
			$res = $this->invoke('Base.TradeModule.Account.Balance.freeze', $data);
			if($res['status'] === 0 ){
				D()->commit();
			}else {
				throw new \Exception($res['response']);
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			D()->rollback();
			if( empty($res) ) {
				L($e->getMessage());
			}else {
				L($res);
			}
		}
		
	}


	public function test_unfreeze($params) { # 释放冻结金额
		C('CTU_CHECK', false);
		$data = array(
			'uc_code' =>'1120000000101',
			'oc_code'=>'10200000117',
			'operator_uc_code'=> 12,
			'operator_ip_addr'=> '192.168.11.108',
			'money' => 13.12,
			'remark' => 'test'
		);

		try{
			D()->startTrans();
			$res = $this->invoke('Base.TradeModule.Account.Balance.unfreeze', $data);
			if($res['status'] === 0 ){
				D()->commit();
			}else {
				throw new \Exception($res['response']);
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			D()->rollback();
			if( empty($res) ) {
				L($e->getMessage());
			}else {
				L($res);
			}
		}
		
	}


	public function test_freezeBack($params) { # 释放冻结金额到可用余额
		$data = array(
			'uc_code' =>88,
			'oc_code'=>3,
			'operator_uc_code'=> 12,
			'operator_ip_addr'=> '192.168.11.108',
			'money' => 100,
			'remark' => 'test'

		);

		try{
			D()->startTrans();
			$res = $this->invoke('Base.TradeModule.Account.Balance.freezeBack', $data);
			exit;
			if($res['status'] === 0 ){
				D()->commit();
			}else {
				throw new \Exception($res['response']);
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			D()->rollback();
			if( empty($res) ) {
				L($e->getMessage());
			}else {
				L($res);
			}
		
		}
	}
		
	# Test.Base.TradeAccount.Balance.test_desc
	public function test_desc($params) { # 释放冻结金额
		$data = array(
			'tc_code' => '1230000000109',
			'oc_code' => '12100045899',
			'operatorType' => 'TEST',
			'operatorIp' => '192.168.0.1',
			'operatorName' => 'SYS',
			'amount' => 0.03,
			'remark' => 'HEELO WORLD',
		);

		try{
			D()->startTrans();
			$res = $this->invoke('Base.TradeModule.Account.Balance.desc', $data);
			if($res['status'] === 0 ){
				D()->commit();
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			L($e->getMessage());
			D()->rollback();
			L($res);
		}

	}

	public function test_addBalanceAsyncTask($params) { # 释放冻结金额
		$data = array(
			'uc_code' =>11,
			'oc_code'=>4,
			'operator_uc_code'=> 12,
			'operator_ip_addr'=> '192.168.11.108',
			'money' => 100,
			'remark' => 'test',
			'type' => TC_ACCOUNT_INCR_BY_B2B_ORDER
		);

		try{
			D()->startTrans();
			$res = $this->invoke('Base.TradeModule.Account.Balance.addBalanceAsyncTask', $data);
			if($res['status'] === 0 ){
				D()->commit();
			}else {
				throw new \Exception($res['response']);
			}
			return $this->res($res['response'], $res['status']);

		}catch(\Exception $e) {
			D()->rollback();
			if( empty($res) ) {
				L($e->getMessage());
			}else {
				L($res);
			}
		}
		
	}

	public function test_task($data) {
		$getTransListres = $this->invoke('Com.Crontab.Scan.Run.runTask', $data);
		L($res);
	}


}

?>
