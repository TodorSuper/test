<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关模块
 */

namespace Test\Base\OrderB2b;

use System\Base;

class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * 创建b2b订单
     * Test.Base.OrderB2b.Order.add
     * @param type $params
     */
    public function add($params) {
//        $this->_rule = array(
//            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
//            array('sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家商品编码
//            array('goods_number', 'require', PARAMS_ERROR, HAVEING_CHECK), //购买数量
//            array('goods_number', 0, PARAMS_ERROR, HAVEING_CHECK, 'gt'), //购买数量
//            array('cart_ids', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function'), //购物车id
//            array('address_id', 'require', PARAMS_ERROR, MUST_CHECK), //收货地址id
//            array('buy_from', array(OC_B2B_APP, OC_B2B_WEIXIN), PARAMS_ERROR, MUST_CHECK, 'in'), //购买来源  必须是  app 或者  微信
//            array('is_cart', array('YES', 'NO'), PARAMS_ERROR, MUST_CHECK, 'in'), //是否是购物车购买
//        );
        $apiPath = "Base.OrderModule.B2b.Order.add";
        //非购物车参数
//        $data = array(
//            'uc_code' => '1120000000104',
//            'sic_code' => '12200000040',
//            'goods_number' => '2',
////           'cart_ids'=>'',
//            'address_id' => '18',
//            'buy_from' => OC_B2B_WEIXIN,
//            'remark' =>array('1020000000026'=>'我要发顺丰'),
//            'is_cart' => 'NO',
//            'pay_method' => PAY_METHOD_ONLINE_WEIXIN,
//        );

  //购物车参数
        $sic_codes = array('12100004279');
//        $sic_codes = array('12200000260');
        $data = array(
            'uc_code' => '1210000001434',
//            'sic_code' => '12200000005',
//            'goods_number' => '2',
           'sic_codes'=>$sic_codes,
            'address_id' => '1418',
            'buy_from' => OC_B2B_WEIXIN,
            'is_cart' => 'YES',
            'pay_type'=>PAY_TYPE_TERM,
            'ship_method'=>SHIP_METHOD_PICKUP,
            'term_method'=>PAY_TYPE_TERM_PERIOD,
            'period_day'=>10,
        );
        $order_res = $this->invoke($apiPath, $data);
        return $this->res($order_res['response'], $order_res['status'], '', $order_res['message']);
    }

    /**
     * 展示即将下单的信息
     * Test.Base.OrderB2b.Order.showOrderInfo
     * @param type $params
     */
    public function showOrderInfo($params) {
        // $apiPath = "Base.OrderModule.B2b.Order.showOrderInfo";
        $apiPath = "Bll.B2b.Order.Order.showOrderInfo";
                //非购物车参数
//        $data = array(
//            'uc_code' => '1120000000104',
//            'sic_code' => '12200000005',
//            'goods_number' => '2',
////           'cart_ids'=>'',
//            'address_id' => '18',
//            'buy_from' => OC_B2B_WEIXIN,
//            'is_cart' => 'NO',
//        );

        //购物车参数
        $sic_codes = array('12200002090','12200006515');
        $data = array(
            'uc_code' => '1210000000390',
//            'sic_code' => '12200000005',
//            'goods_number' => '2',
           'sic_codes'=>$sic_codes,
            'address_id' => '56',
            'buy_from' => OC_B2B_WEIXIN,
            'is_cart' => 'YES',
        );
        $order_res = $this->invoke($apiPath, $data);
        return $this->res($order_res['response'], $order_res['status'], '', $order_res['message']);
    }

    /**
     * 验证商品是否允许购买
     * Test.Base.OrderB2b.Order.isAllowBuy
     * @param type $params
     */
    public function isAllowBuy($params) {
        $apiPath = 'Base.OrderModule.B2b.Order.isAllowBuy';
          $data = array(
            'uc_code' => '1120000000104',
            'sic_code' => '12200000005',
            'goods_number' => '200',
//           'cart_ids'=>'',
            'address_id' => '18',
            'buy_from' => OC_B2B_WEIXIN,
            'is_cart' => 'NO',
        );

        //购物车参数
//        $cart_ids = array(1,6);
//        $data = array(
//            'uc_code' => '1120000000104',
////            'sic_code' => '12200000005',
////            'goods_number' => '2',
//           'cart_ids'=>$cart_ids,
//            'address_id' => '18',
//            'buy_from' => OC_B2B_WEIXIN,
//            'is_cart' => 'YES',
//        );
        $order_res = $this->invoke($apiPath, $data);
        return $this->res($order_res['response'], $order_res['status'], '', $order_res['message']);
    }
    
     /**
     * 验证商品是否允许购买
     * Test.Base.OrderB2b.Order.changeOpcode
     * @param type $params
     */
    public function changeOpcode($params){
        D()->startTrans();
        $apiPath = "Base.OrderModule.B2b.Order.changeOpcode";
        $data = array(
            'op_code'=>'12200002642',
        );
        $res = $this->invoke($apiPath,$data);
        D()->commit();
        print_r($res);
        
    }
    
    /**
     * Test.Base.OrderB2b.Order.umengTest
     * @param type $params
     */
    public function umengTest($params){
        $res = $this->pushAppMessage(ANDROID, 'AoHEW5vTFNbYGdN31ChujCfcJF4Gw8IJKH1s3dazywa6', UMENG_BOSS_UNSHIP, BOSS);
        print_r($res);
    }






}

?>
