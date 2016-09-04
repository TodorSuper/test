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

namespace Test\Base\SpcModuleCommodity;

use System\Base;

class Customer extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 订货会充值
     * Test.Base.SpcModuleCommodity.Customer.add
     * @param type $params
     */

    public function add(){

         try {
            D()->startTrans();
            $apiPath = "Base.SpcModule.Commodity.Customer.add";
            $data = array(
                'sc_code' => '1020000000026',
                // 'uc_code' => '1210000000443',
                'uc_code' => '1210000000396',
                'advance_money' => '100',
                'type' => 'add',
            );
            $res = $this->invoke($apiPath, $data);
            D()->commit();
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            D()->rollback();
        }

    }


    /**
     * 订货会消费
     * Test.Base.SpcModuleCommodity.Customer.update
     * @param type $params
     */

    public function update(){
         try {
            D()->startTrans();
            $apiPath = "Base.SpcModule.Commodity.Customer.update";
            $data = array(
                'sc_code' => '1020000000026',
                'uc_code' => '1210000000396',
                'advance_money' => '100',
                'type' => 'minus',
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