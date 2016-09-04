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

class Cart extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 购物车列表
     * Test.Bll.B2bUser.Cart.lists
     * @param type $params
     */
    public function lists($params){

        $params = array(
            // 'sc_code'=>1020000000026,
            'uc_code'=>1210000000309,
            );

        $apiPath = "Bll.B2b.User.Cart.platformLists";
        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
        
    }

}

?>
