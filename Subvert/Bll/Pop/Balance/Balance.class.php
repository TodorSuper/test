<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户相关模块
 */

namespace Bll\Pop\Balance;
use System\Base;

class Balance extends Base {

	private $_rule = null; # 验证规则列表

    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
	/**
	 * 获取账户详情
	 * Bll.Pop.Balance.Balance.get
	 * uc_code # 账户编码 必须
	 * page # 分页页码 必须
	 * @return void
	 */
	public function get($data) {
		# 统计近期账户总额
		$startMonth = mktime(0,0,0,date('m'),1,date('Y'));
		$getData = [
			'tc_code'=> $data['tc_code'],
			'start_time'=> $startMonth,
			'end_time'=> NOW_TIME ,
			'type'=> TC_ACCOUNT_INCR_BY_B2B_ORDER,
			];
		$month = $this->invoke("Base.TradeModule.Account.Details.getTotalBalanceByTime", $getData);

		# 获取账户信息
		$info = $this->invoke("Base.TradeModule.Account.Details.getAccontInfo", ['tc_code'=>$data['tc_code']] );

		# 获取最近交易流水
		$getData = [
			'tc_code' => $data['tc_code'],
			'start_time' => $startMonth,
			'end_time' => NOW_TIME,
			'page' => $data['page'],
			'page_number' => 20,
			'where' => array('type'=> ['eq','B2B_CASH'] )
		];
		$list = $this->invoke("Base.TradeModule.Account.Details.getTransList", $getData);
		return $this->res([
			'month'=> $month['response'], 
			'info'=> $info['response'], 
			'list'=> $list['response']
		]);
		
	}

	/**
	 * 获取交易列表
	 * Bll.Pop.Balance.Balance.getList
	 * @access public
	 * @return void
	 */
	public function getList($data) {
		# 获取最近交易流水
        if(isset($data['start_time']) && isset($data['end_time']) ){
            $getData = [
                'tc_code' => $data['tc_code'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'page' => $data['page'],
            ];
        }else{
            $getData = [
                'tc_code' => $data['tc_code'],
                'page' => $data['page'],
            ];
        }
		$getData['where']['tci.type'] = $data['type']['type'];
		$list = $this->invoke("Base.TradeModule.Account.Details.getTransList", $getData);
		return $this->res($list['response']);
		
	
	}
	
	/**
	 * 获取提现列表
	 * Bll.Pop.Balance.Balance.getCashList
	 * @access public
	 * @return void
	 */
	public function getCashList($data) {
		# 获取最近提现记录
		$getData = [
			'tc_code' => $data['tc_code'],
			'start_time' => $data['start_time'],
			'end_time' => $data['end_time'],
			'page' => $data['page'],
			'page_number' => 20,
			];
		$getData['where']['tci.status'] = $data['type']['status'];
		$list = $this->invoke("Base.TradeModule.Account.Details.getCashList", $getData);
		return $this->res($list['response']);
	}

	/**
	 * 获取单条账户信息
	 * Bll.Pop.Balance.Balance.getCashInfo
	 * @access public
	 * @return void
	 */
	public function getCashInfo($data) {
		$info = $this->invoke("Base.TradeModule.Account.Details.getAccontInfo", ['tc_code'=>$data['tc_code']] );
		return $this->res($info['response']);
	}

	/**
	 * 创建提现订单
	 * addCash 
	 * Bll.Pop.Balance.Balance.addCash
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function addCash($data) {
		try{
			D()->startTrans();
			$res = $this->invoke("Base.OrderModule.Cash.Order.addCash", $data);
			if($res['status'] !== 0) {
				return $this->res('', 5510);
			}

			$commit = D()->commit();
			if($commit == false) {
				D()->rollback();
				return $this->res('', 5510);
			}

		}catch(\Exception $e) {
			L($e->getMessage());
			D()->rollback();
			return $this->res('', 5510);
		}

		return $this->res(true);
	}

	/**
	 * 审核提现单
	 * Bll.Pop.Balance.Balance.checkCash 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function checkCash($data) {
		try{
			D()->startTrans();
			$res = $this->invoke("Base.OrderModule.Cash.Order.updateCashOrder", $data);
			if($res['status'] !== 0) {
				return $this->res('', 5511);
			}

			$commit = D()->commit();
			if($commit == false) {
				D()->rollback();
				return $this->res('', 5511);
			}

		}catch(\Exception $e) {
			L($e->getMessage());
			D()->rollback();
			return $this->res('', 5511);
		}

		return $this->res(true);
	}

	/**
	 * 获取提现单明细
	 * Bll.Pop.Balance.Balance.getCashOrderInfo
	 * @access public
	 * @return void
	 */
	public function getCashOrderInfo($data) {
		$invoke = $this->invoke('Base.TradeModule.Account.Details.getCashOrderInfo', $data);
		return $this->res($invoke['response']);
	}

}

?>
