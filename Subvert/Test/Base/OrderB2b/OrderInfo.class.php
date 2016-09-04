<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单列表相关的操作
 */

namespace Test\Base\OrderB2b;

use System\Base;

class OrderInfo extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Test.Base.OrderB2b.OrderInfo.payBacktoTest
     * @param type $params
     */
    public function payBacktoTest($params){
        $apiPath = "Base.OrderModule.B2b.OrderInfo.payBackto";
//        $data = array(
//            'order_sn' => '12200001294',
//            'pay_time' => NOW_TIME,
//            'pay_type' => '微信支付',
//            'pay_price' => 20,
//            'url_info' => C('DEFAULT_WEIXIN_URL').'OrderInfo/Index/orderid/'.'12200001294'.'.html',
//            'uc_code'  => '1220000000156',
//        );
        
        $data = array (
            'order_sn' => '12200001477',
            'pay_time' => 1438684482,
            'pay_type' => '微信支付',
            'pay_price' => '0.01',
            'url_info' => 'http://b2b.st.com/OrderInfo/Index/orderid/12200001477.html',
            'uc_code' => '1220000000156',
          );
        $res = $this->invoke($apiPath,$data);
        print_r($res);
    }
    
    /**
     * Test.Base.OrderB2b.OrderInfo.payBackTest
     * @param type $params
     */
    public function payBackTest($params){
        $apiPath = "Base.OrderModule.B2b.OrderInfo.payBack";
        $data = array(
            'op_code' => '12200001477',
            'amount'  =>'0.01',
        );
        $res = $this->invoke($apiPath,$data);
        print_r($res);
    }
    
    /**
     * Test.Base.OrderB2b.OrderInfo.listsTest
     * @param type $params
     */
    public function listsTest($params){
        $params = array(
            'uc_code' => 1120000000104,
            'status' =>OC_ORDER_GROUP_STATUS_ALL,
        );
        $apiPath = "Base.OrderModule.B2b.OrderInfo.lists";
        $res = $this->invoke($apiPath,$params);
        print_r($res);
    }
    

}

?>
