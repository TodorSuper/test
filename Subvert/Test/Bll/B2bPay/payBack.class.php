<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 创建支付任务
 */

namespace Test\Bll\B2bPay;

use System\Base;

class PayBack extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
    * 创建支付任务
    * Test.Bll.B2bPay.PayBack.exeByPayGetway
    *
    */

    public function exeByPayGetway(){

        try {
            D()->startTrans();
            $apiPath = "Bll.B2b.Pay.PayBack.exeByPayGetway";
            $data = array(
                'oc_code'=>'11300019966',
                'op_code'=>'12300019965',
                'total_fee'=>'1000',
                'pay_by'=>'WEIXIN_JSAPI_PAY',
            );
            $res = $this->invoke($apiPath, $data);
            D()->commit();
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            D()->rollback();
        }

    }







}








 ?>