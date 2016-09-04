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

namespace Test\Base\StoreModuleOrder;

use System\Base;

class Operation extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 促销效果查询列表
     * Test.Base.StoreModuleOrder.Operation.add
     * @param type $params
     */
    public function add($params) {
        // $apiPath = "Base.StoreModule.Order.Operation.add";
        // $apiPath = "Base.StoreModule.Order.Operation.update";
        $apiPath = "Base.StoreModule.Order.Operation.check";    
        $data = array(
            'sc_code'=>1010000000077,
            'uc_code'=>1210000000335,
            'sys_name'=>'B2B',
            'type'=>'TERM_UNPAY',
        	);

        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }



}







 ?>