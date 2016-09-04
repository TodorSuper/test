<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 公共列表
 */

namespace Test\Com\CommonCommonView;

use System\Base;

class Lists extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * Test.Com.CommonCommonView.Lists.Lists
     * @param type $params
     * @return type
     */
    public function Lists($params){
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $params = array(
            'sql_flag'=>'address_list',
            'center_flag'=>SQL_UC,
           
        );

        $res = $this->invoke($apiPath, $params);
        $this->res($res['response'],$res['status']);
    }

}

?>
