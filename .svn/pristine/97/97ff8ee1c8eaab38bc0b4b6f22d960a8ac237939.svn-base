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

class Details extends Base {
	/*
	 * Test.Base.TradeAccount.Balance.test_add
	*/
		
	public function test_getTransList($params) { 
		
		$params['uc_code'] = 11;
		$params['page_number'] = 3;

		$res = $this->invoke('Base.TradeModule.Account.Details.getTransList', $params);
		return $this->res($res['response'], $res['status']);
	}

	public function test_getAccountList($params) { 
		$params['page_number'] = 2;
		$res = $this->invoke('Base.TradeModule.Account.Details.getAccontList', $params);
		return $this->res($res['response'], $res['status']);
	}
	
	public function test_getAccountInfo($params) {
		$params['uc_code'] =11;
		$res = $this->invoke('Base.TradeModule.Account.Details.getAccontInfo', $params);
		return $this->res($res['response'], $res['status']);
	}
	
	public function test_getTransInfo($params) {
		$params['trade_no'] = '1020000000108';
		$res = $this->invoke('Base.TradeModule.Account.Details.getTransInfo', $params);
		return $this->res($res['response'], $res['status']);
	}
}

?>
