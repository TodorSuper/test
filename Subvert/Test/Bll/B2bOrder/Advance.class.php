<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 促销效果
 */

namespace Test\Bll\B2bOrder;

use System\Base;

class Advance extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 订货会充值
     * Test.Bll.B2bOrder.Advance.add
     * @param type $params
     */

    public function add(){

        $apiPath = "Bll.B2b.Order.Advance.add";
        $data = array(
            'sc_code' => '1020000000026',
            'uc_code' => '1210000000385',
            'amount' => '100.5',
            'pay_method' => 'REMIT',
            'client_name'=>'尹',
            'operator_ip'=>$_SERVER['REMOTE_ADDR'],
            'bank'=>'CMB',
        );
        $res = $this->invoke($apiPath, $data);
    }


    /**
     * 订货会充值回掉
     * Test.Bll.B2bOrder.Advance.payBack
     * @param type $params
     */

    public function payBack(){

        $apiPath = "Bll.B2b.Order.Advance.payBack";
        $data = array(
            'oc_code'=>'21200002954',
            'amount'=>'10',
            'pay_method'=>'WEIXIN',
        );
        $res = $this->invoke($apiPath, $data);
    }




}








 ?>