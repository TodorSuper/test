<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b注册模块
 */

namespace Test\Bll\B2bUser;
use System\Base;

class Coupon extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 优惠券列表
     * Test.Bll.B2bUser.Coupon.lists
     * @param type $params
     */
    public function lists($params){

        $apiPath = "Bll.B2b.User.Coupon.lists";
        $data = array(
            'uc_code' => '1220000005398',
            'coupon_status' => array("ENABLE","INVALID"),
            'type'       =>'usable',
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->endInvoke($res['response']);
        
    }



}

?>
