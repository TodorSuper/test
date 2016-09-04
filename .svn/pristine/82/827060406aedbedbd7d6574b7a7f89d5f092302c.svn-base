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

namespace Test\Base\OrderCenter;

use System\Base;

class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Test.Base.OrderCenter.Order.remit
     * @param type $params
     */
    public function remit($params){
        $_SERVER['HTTP_USER_AGENT'] = CMS;
        try{
            D()->startTrans();
            $apiPath = "Base.OrderModule.Center.Order.remit";
            $params = array(
                'b2b_codes'=>array(
                    12300003703,
                    12300003704,
                    12300003709,
                    21300003710,
                ),
            );
            $res = $this->invoke($apiPath,$params);
            print_r($res);
            D()->commit();
        } catch (\Exception $ex) {
          echo $ex->getMessage();
        }
    }

}

?>
