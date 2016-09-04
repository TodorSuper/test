<?php
namespace Test\Base\OrderB2b;

use System\Base;

class OrderAction extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    /*
     * 订单状态测试
     * Test.Base.OrderB2b.OrderAction.orderAc
     */
    public function orderAc() {

        $api = 'Base.OrderModule.B2b.OrderAction.orderActionUp';

        /*
        $param = array(
            'uc_code'     =>'1120000000104',
            'b2b_code'    => '12200001294',
            'pay_method'  => 'WEIXIN',
            'status'      => 'UNPAY',
            'order_statsu'=> 'UNCONFIRM',
            'pay_status'  => 'UNPAY',
            'ship_status' => 'UNSHIP'
        );
        */
        /*
        $param = array(
                'uc_code'     =>'1120000000104',
                'b2b_code'    => '12200001294',
                'pay_method'  => 'WEIXIN',
                'status'      => 'UNPAY'
        );
        */
        $param = array(
            'uc_code'     =>'1120000000104',
            'b2b_code'    => '12200001294',
            'pay_method'  => 'WEIXIN',
            'status'      => 'UNPAY',
            'order_statsu'=> array('POP'=>OC_ORDER_ORDER_STATUS_UNCONFIRM,'B2B'=>OC_ORDER_ORDER_STATUS_COMPLETE),
            'pay_status'  => 'UNPAY',
            'ship_status' => 'UNSHIP'
        );
        $res = $this->invoke($api,$param);
        return $this->res($res);
    }

}