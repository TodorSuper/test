<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\Boss;

use System\Base;
class OrderInfo extends Base {

	/**
	 * Test.Bll.Boss.OrderInfo.testCashier
	 * @access public
	 * @return void
	 */
	public function testCashier($data) {
		// $data = array(
		// 	'oc_code' => '12200004',
		// 	'op_code' => '12200004',
		// 	'total_fee' => 0.01,
		// 	'getway' => WEIXIN_JSAPI_PAY,
		// 	'open_id' => 'oOqT0twu_GGLijOSQunBRsrwnSB8',
		// );
		// $res = $this->invoke("Base.TradeModule.Pay.Task.createPayTask", $data);
		// L($res);
		// return $this->endInvoke($res);
        $data = array(
            'version'=>'110',
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'down_url'=>'http://cdn.liangren.com/app/liangren_boss_1.1.0%28110%29_signed.apk',
            );
        D('AppVersion')->where(array('device'=>'ANDROID'))->add($data);
	}
	/*
    * APP订单列表接口
    * Test.Bll.Boss.OrderInfo.lists
    */
    public function lists() {
    	$params = array(
    		'sc_code' => '1010000000077',
    		'status'  => 'ALL',
    		'sort'   => 'CREATE_TIME_DESC'
    	);
    	$res = $this->invoke('Bll.Boss.Order.OrderInfo.lists',$params);
    	return $this->endInvoke($res);
    }
    /*
    * Test.Bll.Boss.OrderInfo.operator
    * APP订单状态操作接口
    */
    public function operator() {
    	$params = array(
    		'sc_code' => '1020000000026',
    		'b2b_code'=> '12200003608',
    		'status'  => 'SHIPPED',
    		'sender'  => '张三',
    		'sender_mobile' => '18911900442'
    	
    	);
    	$res = $this->invoke('Bll.Boss.Order.OrderInfo.operator',$params);
    	return $this->endInvoke($res);
    }
     /*
    * Test.Bll.Boss.OrderInfo.get
    * APP订单详情
    */
    public function get($params) {
    	$params = array(
    		'sc_code' => '1010000000077',
    		'b2b_code' => '11100019716',
    	);
    	$data = $this->invoke('Bll.Boss.Order.OrderInfo.get',$params);
    	return $this->res($data);
    	return $this->endInvoke($data);
    }
     /*
    * Test.Bll.Boss.OrderInfo.accountSearch
    * 账期列表
    */
    public function accountSearch($params) {
    	$params = array(
    		'sc_code' => '1010000000077',
    		'pageSize' => 1,
    		'pageNumber' =>20,
    	);
    	$data = $this->invoke('Bll.Boss.Order.OrderInfo.accountSearch',$params);
    	return $this->endInvoke($data);
    }	
    /*
    * 账期详情
    * Test.Bll.Boss.OrderInfo.accountInfo
    */
    public function accountInfo($params) {
    	$params = array(
    		'uc_code' => '1210000000427',
    		'pageNumber' => 1,
    		'pageSize' => 2,
    	);
    	$data = $this->invoke('Bll.Boss.Order.OrderInfo.accountInfo',$params);
    	return $this->endInvoke($data);
    }	



    /**
     * 订单改价
     * Test.Bll.Boss.OrderInfo.changePrice
     */

    public function changePrice($params){
        $params = array (
          'info' => 
          array (
            0 => 
            array (
              'sic_code' => '12200002922',
              'spc_code' => '12210000294',
              'goods_price' => '9',
            ),
          ),
          'b2b_code' => '12300008551',
          'sc_code' => '1010000000077',
        );
        $apiPath = "Bll.Boss.Order.OrderInfo.changePrice";
        $data = $this->invoke($apiPath,$params);
        return $this->endInvoke($data);
    }

    /**
     * 订单改价获取详情
     * Test.Bll.Boss.OrderInfo.orderGoods
     */
    public function orderGoods($params){
        $params = array(
            'b2b_code' => '12300008546',
            'sc_code' => '1010000000077',
            );
        $apiPath = "Bll.Boss.Order.OrderInfo.orderGoods";
        $data = $this->invoke($apiPath,$params);
        return $this->endInvoke($data);
    }


    /**
     * 获取今日订单
     * Test.Bll.Boss.OrderInfo.getOrders
     */

    public function getOrders($params){
        $params = array(
            'sc_code' => '1010000000077',
            // 'date'=>'1449041372',
            // 'pageNumber'=>1,
            // 'pageSize'=>2,
            );
        $apiPath = "Bll.Boss.Order.OrderInfo.getOrders";
        $data = $this->invoke($apiPath,$params);
        return $this->endInvoke($data);
    }

    
}
