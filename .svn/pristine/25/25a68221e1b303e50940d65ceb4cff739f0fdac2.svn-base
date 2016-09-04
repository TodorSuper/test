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

class Task extends Base {

	/**
	 * Test.Base.TradeAccount.Task.testQueue 
	 * @access public
	 * @return void
	 */
	public function testQueue() {
		$data = R()->get('a');
		D('UcUser')->find(1);
		if(!$data) {
			R()->set('a', 100);
		}
		return $this->res($data);
	}

/**
 *	Test.Base.TradeAccount.Task.test_createPayTask
 */
	public function test_createPayTask($data) { # 创建支付任务
		$data = array(
			'oc_code' =>'12200001294',
			'oc_type'=> OC_B2B_GOODS_ORDER,
			'total_fee'=> 12,
			'pay_by'=> WEIXIN_JSAPI_PAY,
			'ext1' => 'o8JbLjuzycmXKPH5EWwp6cDxah6A',
		);

		$res = $this->invoke('Base.TradeModule.Pay.Task.createPayTask', $data);
		return $this->res($res);
	}
	
	# Test.Base.TradeAccount.Task.test
	public function test() {
//		$res = Logs::info("api.call.push_queue", Logs::LOG_FLAG_NORMAL, ["hello"]);
		$a = D('UcUser')->find(1);
		for($i; $i < 15; $i++) {
			$a['timer'] = $i;
			$b[] = $a;
		}
		$c['a'] = $b;
		$c['b'] = $b;
		D()->startTrans();
//		throw new \Exception('1111');
//		$res = D('UcUser')->find(1);
//		D()->commit();
//		D()->rollback();
		return $this->res($c);
	}
}
