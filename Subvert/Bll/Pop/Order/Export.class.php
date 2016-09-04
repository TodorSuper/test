<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop订单数据导出
 */

namespace Bll\Pop\Order;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {               
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Bll.Pop.Order.Export.export
     * @param type $params
     * @return type
     */
    public function  export($params){
        $apiPath  = "Base.OrderModule.B2b.Export.export";
        $params['template_call_api'] = "Com.Callback.Export.Template.order";
        $res = $this->invoke($apiPath, $params);


        return $this->res($res['response'],$res['status'],'',$res['message']);

    }
}

?>
