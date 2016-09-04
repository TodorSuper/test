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

class Prompt extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }


    /*
     * 添加提示消息
     * Test.Base.OrderB2b.Prompt.add
     */

    public function add($params){
        $data = array(
            'sc_code'=>'1010000000077',
            // 'uc_code'=>'1210000000338',
            'uc_code'=>'1210000000431',
            'unpay'=>array('-',3),
            'ship'=>array('+',1),
            );

        // $apiPath = "Base.OrderModule.B2b.Prompt.add";
        // $apiPath = "Bll.B2b.User.User.personCenter";
        // $apiPath = "Base.OrderModule.B2b.Prompt.get";
        $apiPath = "Base.OrderModule.B2b.Prompt.check";
        $res = $this->invoke($apiPath,$data);
        return $this->endInvoke($res['response']);
    }
    

    

}

?>
