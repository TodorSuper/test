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

class PayTask extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
    * 创建支付任务
    * Test.Bll.B2bPay.PayTask.createPayTask
    *
    */

    public function createPayTask(){

        $apiPath = "Bll.B2b.Pay.PayTask.createPayTask";
        $data = array(
            'uc_code'=>'1210000000375',
            'op_code'=>'22200002990',
            'open_id'=>'oOqT0twJ3BufYlCnarKTg0wp73IM',
            'pay_method'=>'WEIXIN',
            );
        $res = $this->invoke($apiPath, $data);
    }

    /**
     * 创建支付任务
     * Test.Bll.B2bPay.PayTask.testPay
     *
     */
    public function testPay() {

        // $apiPath = "Base.PayCenter.Info.AccountInfo.BankList"; //银行列表
       // $apiPath = "Base.PayCenter.Info.AccountInfo.BindList"; //绑定银行卡列表
        $apiPath = "Base.PayCenter.Task.PayTask.Create";  //先锋认证支付

        $data = array(
            'gateway'=>'UCPAY_DIRECT',
            'uc_code' => '10000038',
        );
        $res = $this->invoke($apiPath, $data);
        return $this->endInvoke($res['response'], $res['status'], $res['message']);
    }

    public function getPayTask($params) {
        $apiPath = "Base.PayCenter.Task.PayTask.Create";  //先锋认证支付


        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'], $res['status'], $res['message']);
    }


}








 ?>