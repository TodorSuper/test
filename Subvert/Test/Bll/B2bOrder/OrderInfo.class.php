<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单列表相关的操作 测试
 */

namespace Test\Bll\B2bOrder;

use System\Base;

class OrderInfo extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * 
     * 订单列表
     * pop  显示子订单（无论支付还是未支付）
     * b2b  未支付显示总订单    已支付显示子订单信息 
     * cms  未支付显示总订单    已支付显示子订单信息
     *  
     * sc_code    店铺编码  pop平台的
     * uc_code    用户编码  用户平台的(weixin  app)
     * b2b_code   订单编码
     * start_time 订单下单开始时间
     * end_time   订单下单结束时间
     * username   用户名
     * status  订单状态  组合状态
     * 
     * Test.Bll.B2bOrder.OrderInfo.lists
     * @param type $params
     */
    public function lists($params){
        $apiPath = "Bll.B2b.Order.OrderInfo.lists";
        $params  = array(
            'uc_code'=>'1230000002770',
        );
        $list_res = $this->invoke($apiPath, $params);
        return $this->res($list_res['response'],$list_res['status']);
    }
    /*
     * Test.Bll.B2bOrder.OrderInfo.operate
     * */
    public function operate($params) {

        $params = array(
            'status'   => 'COMPLETE',
            'b2b_code' => '12200001294',
            'sc_code'  => '1020000000026'
            //'uc_code'  => '1120000000104'
        );
        $api = 'Bll.Pop.Order.OrderInfo.operate';
        $res = $this->invoke($api,$params);
        return $res;
    }
    
    /**
     * 订单详情
     * Test.Bll.B2bOrder.OrderInfo.get
     * @param type $params
     */
    public function get($params){
       $apiPath = "Bll.B2b.Order.OrderInfo.get";
       $params = array(
           'b2b_code' => '12200001279',
       );
       $order_info_res = $this->invoke($apiPath, $params);
       return $this->res($order_info_res['response'],$order_info_res['status']);
    }
    
}

?>
