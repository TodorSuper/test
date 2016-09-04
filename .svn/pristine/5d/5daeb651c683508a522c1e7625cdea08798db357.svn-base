<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 品牌
 */

namespace Test\Base\ItemModuleBrand;

use System\Base;

class Brand extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 促销效果查询列表
     * Test.Base.ItemModuleBrand.Brand.brands
     * @param type $params
     */
    public function brands($params) {
        $apiPath = "Base.ItemModule.Brand.Brand.brands";

        $data = array(
            'sc_code'=>1020000000026,
            'status'=>"ON",
            'stock_gt'=>'YES',
        	);

        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }



}







 ?>