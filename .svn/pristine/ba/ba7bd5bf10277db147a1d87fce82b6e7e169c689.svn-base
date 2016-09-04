<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop提现查询数据导出
 */

namespace Bll\Pop\Balance;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Tc';
    }

    /**
     * Bll.Pop.Balance.Export.export
     * @param type $params
     * @return type
     */
    public function  export($params){
        $params['key'] == 'wait' ? ($params['type'] = ['status' => ['eq', 'APPLY']]) : ( $params['key'] == 'ok' ? ($params['type'] = ['status' => ['eq', 'PASS']]) : $params['type'] = ['status' => ['in', ['APPLY', 'PASS']]]);
        $params['callback_api']='Com.Callback.Export.TcExport.cashList';
        $apiPath  = "Base.TradeModule.Account.Export.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }
}

?>
