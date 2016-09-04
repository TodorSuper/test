<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 
 */

namespace Com\Crontab\Order;
use System\Base;

class Account extends Base {

	public function __construct() {
		parent::__construct();
    }

    /**
     * Com.Crontab.Order.Account.sendCustomerSms
     * @return [type] [description]
     */
	public function sendCustomerSms(){

		$where = array(
			'pay_type'    => 'TERM',
			'pay_status'  => 'UNPAY',
			'ship_status' => 'SHIPPED',
			);
		$field = "sc_code,uc_code,pay_type,pay_status,real_amount,b2b_code,ext4,ext5,create_time";
		$orderAccout = D('OcB2bOrder')->where($where)->field($field)->select();
		if (empty($orderAccout) || $orderAccout === false) {
			return ;
		}
		// var_dump($orderAccout);
		$weekAccout   = array();
		$mouthAccount = array();
		$count = 1;
		foreach ($orderAccout as $key => $value) {
			if ($value['ext4'] === 'TERM_PERIOD') {
				$gap_time    = $value['ext5']*86400;
				$accountDate = date('Ymd', $value['create_time']+$gap_time);
				$now_date    = date('Ymd', NOW_TIME+172800);
				$now_day     = date('d', NOW_TIME+172800);
				if ($now_date != $accountDate) {
					continue;
				}

				if ($weekAccout[$value['uc_code']]) {
					$weekAccout[$value['uc_code']]['orderNum'] += 1;
					$weekAccout[$value['uc_code']]['price']    += $value['real_amount']; 
					$weekAccout[$value['uc_code']]['date']     = $now_day;
					$weekAccout[$value['uc_code']]['uc_code']  = $value['uc_code'];
					$weekAccout[$value['uc_code']]['sc_code']  = $value['sc_code'];
					$weekAccout[$value['uc_code']]['b2b_code'] = $value['b2b_code'];
				}else{
					$weekAccout[$value['uc_code']]['now_day']  = $now_day;
					$weekAccout[$value['uc_code']]['orderNum'] = 1;
					$weekAccout[$value['uc_code']]['price']    = $value['real_amount'];
					$weekAccout[$value['uc_code']]['date']     = $now_day;
					$weekAccout[$value['uc_code']]['uc_code']  = $value['uc_code'];
					$weekAccout[$value['uc_code']]['sc_code']  = $value['sc_code'];
					$weekAccout[$value['uc_code']]['b2b_code'] = $value['b2b_code'];
				}
			}else{
				$lastDate    = date('Ymd', strtotime(date('Y-m-01', strtotime(date('Y-m-d'))) . ' +1 month -1 day'));
				$accountDate = date('Ymd', NOW_TIME+172800);
				if ($accountDate != $lastDate) {
					continue;
				}

				if ($mouthAccount[$value['uc_code']]) {
					$mouthAccount[$value['uc_code']]['orderNum'] += 1;
					$mouthAccount[$value['uc_code']]['price']    += $value['real_amount']; 
					$mouthAccount[$value['uc_code']]['date']     = $now_day;
					$mouthAccount[$value['uc_code']]['uc_code']  = $value['uc_code']; 
					$mouthAccount[$value['uc_code']]['sc_code']  = $value['sc_code'];
					$mouthAccount[$value['uc_code']]['b2b_code']  = $value['b2b_code']; 

				}else{
					$mouthAccount[$value['uc_code']]['orderNum'] = 1;
					$mouthAccount[$value['uc_code']]['price']    = $value['real_amount'];
					$mouthAccount[$value['uc_code']]['date']     = $now_day; 
					$mouthAccount[$value['uc_code']]['uc_code']  = $value['uc_code']; 
					$mouthAccount[$value['uc_code']]['sc_code']  = $value['sc_code']; 
					$mouthAccount[$value['uc_code']]['b2b_code']  = $value['b2b_code']; 
				}
			}
			
		}

		if (!empty($weekAccout)) {
			$uc_codeArr = array_keys($weekAccout);
			$apiPath = "Base.UserModule.Customer.Customer.getAll";
			$sc_code = array_column($weekAccout, 'sc_code')[0];

			$data = array(
				'sc_code' => $sc_code,
				'uc_code' => $uc_codeArr
				);
			$customerArrRes = $this->invoke($apiPath, $data);

			$customerArr = changeArrayIndex($customerArrRes['response'], 'uc_code');
			// var_dump($customerArrRes,$customerArr);die();
			foreach ($weekAccout as $key => $value) {
				if ($customerArr[$value['uc_code']]) {
					$this->goAccountCustomerMessage($value['orderNum'], $value['date'], $value['price'], $customerArr[$value['uc_code']]['mobile']);
				}
			}

		}

		if (!empty($mouthAccount)) {
			$uc_codeArr = array_keys($weekAccout);
			$apiPath = "Base.UserModule.Customer.Customer.getAll";
			$sc_code = array_column($mouthAccount, 'sc_code')[0];
			$data = array(
				'sc_code' => $sc_code,
				'uc_code' => $uc_codeArr
				);
			$customerArrRes = $this->invoke($apiPath, $data);
			$customerArr = changeArrayIndex($customerArrRes['response'], 'uc_code');
			foreach ($mouthAccount as $key => $value) {
				if ($customerArr[$value['uc_code']]) {
					$this->goAccountCustomerMessage($value['orderNum'], $value['date'], $value['price'], $customerArr[$value['uc_code']]['mobile']);
				}
			}
		}

	}

	 /**
     * 账期小B发短信
     */
    private function goAccountCustomerMessage($num, $date, $price, $mobile){
        $message = "您有{$num}笔账期订单，于本月{$date}日到期，请尽快完成付款；付款总额：￥{$price}。";
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }

     /**
     * 账期渠道发短信
     */
    private function goAccountSalesmanMessage($num, $date, $price, $mobile){
    	$message = "您有{$num}笔账期订单，于本月{$date}日结算；结算总额：￥{$price}。";
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }

    /**
     * Com.Crontab.Order.Account.sendSalesmanSms
     * 账期给渠道发短信
     * @return [type] [description]
     */
    public function sendSalesmanSms(){
    	$where = array(
			'pay_type'    => 'TERM',
			'pay_status'  => 'UNPAY',
			'ship_status' => 'SHIPPED',
    		);
    	$field = "uc_code,sc_code,real_amount,b2b_code,ext4,ext5,create_time,salesman_id";
    	$orderAccout = D('OcB2bOrder')->where($where)->field($field)->select();
    	if (empty($orderAccout) || $orderAccout === false) {
    		return ;
    	}

    	$weekAccout   = array();
    	$mouthAccount = array();
    	$count = 1;
    	foreach ($orderAccout as $key => $value) {
    		if ($value['ext4'] === 'TERM_PERIOD') {
    			$gap_time    = $value['ext5']*86400;
    			$accountDate = date('Ymd', $value['create_time']+$gap_time);
    			$now_date    = date('Ymd', NOW_TIME+86400);
    			$now_day     = date('d', NOW_TIME+86400);
    			if ($now_date != $accountDate) {
    				continue;
    			}
    			// var_dump($value);
    			if ($weekAccout[$value['salesman_id']]) {
					$weekAccout[$value['salesman_id']]['orderNum']    += 1;
					$weekAccout[$value['salesman_id']]['price']       += $value['real_amount']; 
					$weekAccout[$value['salesman_id']]['date']        = $now_day;
					$weekAccout[$value['salesman_id']]['uc_code']     = $value['uc_code'];
					$weekAccout[$value['salesman_id']]['salesman_id'] = $value['salesman_id'];
					$weekAccout[$value['salesman_id']]['sc_code']     = $value['sc_code'];

    			}else{
					$weekAccout[$value['salesman_id']]['orderNum']    = 1;
					$weekAccout[$value['salesman_id']]['price']       = $value['real_amount'];
					$weekAccout[$value['salesman_id']]['date']        = $now_day;
					$weekAccout[$value['salesman_id']]['uc_code']     = $value['uc_code'];
					$weekAccout[$value['salesman_id']]['salesman_id'] = $value['salesman_id'];
					$weekAccout[$value['salesman_id']]['sc_code']     = $value['sc_code'];
    			}
    		}else{
    			$lastDate    = date('Ymd', strtotime(date('Y-m-01', strtotime(date('Y-m-d'))) . ' +1 month -1 day'));
    			$accountDate = date('Ymd', NOW_TIME+86400);
    			if ($accountDate != $lastDate) {
    				continue;
    			}

    			if ($mouthAccount[$value['salesman_id']]) {
					$mouthAccount[$value['salesman_id']]['orderNum']    += 1;
					$mouthAccount[$value['salesman_id']]['price']       += $value['real_amount']; 
					$mouthAccount[$value['salesman_id']]['date']        = $now_day;
					$mouthAccount[$value['salesman_id']]['uc_code']     = $value['uc_code']; 
					$mouthAccount[$value['salesman_id']]['salesman_id'] = $value['salesman_id']; 
					$mouthAccount[$value['salesman_id']]['sc_code'] = $value['sc_code']; 

    			}else{
					$mouthAccount[$value['salesman_id']]['orderNum']    = 1;
					$mouthAccount[$value['salesman_id']]['price']       = $value['real_amount'];
					$mouthAccount[$value['salesman_id']]['date']        = $now_day; 
					$mouthAccount[$value['salesman_id']]['uc_code']     = $value['uc_code']; 
					$mouthAccount[$value['salesman_id']]['salesman_id'] = $value['salesman_id']; 
					$mouthAccount[$value['salesman_id']]['sc_code'] = $value['sc_code']; 

    			}
    		}
    		
    	}
    	// var_dump($weekAccout);
    	// die();

    	if (!empty($weekAccout)) {
			$salesmanArr = array_keys($weekAccout);

			$apiPath     = "Base.UserModule.Customer.Salesman.getAll";
    		$data = array(
    			'salesman_id' => $salesmanArr
    			);
    		$salesmanArrRes = $this->invoke($apiPath, $data);
    		$salesmanArr = changeArrayIndex($salesmanArrRes['response'], 'id');
    		foreach ($weekAccout as $key => $value) {
    			if ($salesmanArr[$value['salesman_id']]) {
    				$this->goAccountSalesmanMessage($value['orderNum'], $value['date'], $value['price'], $salesmanArr[$value['salesman_id']]['mobile']);
    			}
    		}
    		$sc_code = array_column($weekAccout, 'sc_code')[0];
    		$data = array(
    			'sc_code' => $sc_code,
    			'sms_type' => 'ALL_BALANCE',
    			);
    		$apiPath = 'Base.StoreModule.Basic.Sms.getLinkManInfo';
    		$res = $this->invoke($apiPath, $data);
    		$linkMans = $res['response'];
    		foreach ($weekAccout as $key => $val) {
    			
    			foreach ($linkMans as $value) {
    				$this->goAccountSalesmanMessage($val['orderNum'], $val['date'], $val['price'], $value['phone']);
    			}
    		}
    		

    	}

    	if (!empty($mouthAccount)) {
			$salesmanArr = array_keys($mouthAccount);
			$apiPath     = "Base.UserModule.Customer.Salesman.getAll";
    		$data = array(
    			'salesman_id' => $salesmanArr
    			);
    		$salesmanArrRes = $this->invoke($apiPath, $data);
    		$salesmanArr = changeArrayIndex($salesmanArrRes['response'], 'id');
    		foreach ($mouthAccount as $key => $value) {
    			if ($salesmanArr[$value['salesman_id']]) {
    				$this->goAccountSalesmanMessage($value['orderNum'], $value['date'], $value['price'], $salesmanArr[$value['salesman_id']]['mobile']);
    			}
    		}
    		$sc_code = array_column($weekAccout, 'sc_code')[0];
    		$data = array(
    			'sc_code' => $sc_code,
    			'sms_type' => 'ALL_BALANCE',
    			);
			$apiPath  = 'Base.StoreModule.Basic.Sms.getLinkManInfo';
			$res      = $this->invoke($apiPath, $data);
			$linkMans = $res['response'];
    		foreach ($weekAccout as $key => $val) {
    			
    			foreach ($linkMans as $value) {
    				$this->goAccountSalesmanMessage($val['orderNum'], $val['date'], $val['price'], $value['phone']);
    			}
    		}
    	}
    }

}