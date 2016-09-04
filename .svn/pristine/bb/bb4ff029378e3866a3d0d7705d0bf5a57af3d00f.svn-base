<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop交易明细数据导出
 */

namespace Bll\Pop\Balance;

use System\Base;

class Trans extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Tc';
    }

    /**
     * Bll.Pop.Balance.Trans.export
     * @param type $params
     * @return type
     */
    public function export($params){
        $apiPath  = "Base.TradeModule.Account.Trans.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }
}

?>
