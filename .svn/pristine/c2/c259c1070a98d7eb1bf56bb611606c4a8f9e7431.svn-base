<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺商品相关模块
 */

namespace Test\Base\StoreModule;

use System\Base;

class BasicStore extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 店铺标准库列表
     * Test.Base.StoreModule.BasicStore.lists
     * @param type $params
     */
    public function lists($params) {
        // $apiPath = "Base.StoreModule.Basic.Store.lists";
        $apiPath = "Bll.B2b.Store.Lists.lists";
        $data = array(
            'is_show'=>'YES',
            'is_page'=>'YES',
            );
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }


}

?>
