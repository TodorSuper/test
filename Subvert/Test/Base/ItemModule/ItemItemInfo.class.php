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

namespace Test\Base\ItemModule;

use System\Base;

class ItemItemInfo extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 促销效果查询列表
     * Test.Base.ItemModule.ItemItemInfo.getStoreItem
     * @param type $params
     */
    public function getStoreItem($params) {
        $apiPath = "Base.ItemModule.Item.ItemInfo.getStoreItem";
        $data = array(
            'sic_code'=>12200002089,
            'need_tag'=>'YES',
            'need_category'=>'YES',
        	);

        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }



}







 ?>