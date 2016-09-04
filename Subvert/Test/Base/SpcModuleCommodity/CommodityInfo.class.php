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

class CommodityInfo extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 订货会详情
     * Test.Base.SpcModuleCommodity.CommodityInfo.get
     * @param type $params
     */

    public function get(){

        $apiPath = "Base.SpcModule.Commodity.CommodityInfo.get";
        // $apiPath = "Bll.Pop.Spc.Commodity.get";
        $data = array(
            'spc_code'=>21210000207,
            'order'=>'advance_money',
            );
        $res = $this->invoke($apiPath,$data);
        return $this->res($res['response'], $res['status']);
    }


    /**
     * 订货会详情
     * Test.Base.SpcModuleCommodity.CommodityInfo.lists
     * @param type $params
     */

    public function lists(){

        // $apiPath = "Base.SpcModule.Commodity.CommodityInfo.lists";
        $apiPath = "Bll.Pop.Spc.Commodity.lists";
        $data = array(
            'sc_code'=>1020000000026,
            );
        $res = $this->invoke($apiPath,$data);
        return $this->res($res['response'], $res['status']);
    }



}








 ?>