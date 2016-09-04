<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 交易中心列表sql
 */

namespace Com\Common\CommonView;

use System\Base;

class TcSql extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * 用户中心列表sql
     * @param type $fields
     * @param type $where
     * @param type $order
     * @param type $group
     * @param type $having
     * @param type $sql_flag
     * @param type $other
     * @return type
     */
    public function sqls($fields,$where,$order,$group,$having,$sql_flag,$other){
        
        //而外参数
        if(!empty($other)){
            extract($other);
		}
		
		//列表sql  开发者往里面写sql就可以
        $sqls = array(
            'getTransList'=>"select {$fields} from {$this->tablePrefix}tc_transaction_info tci left join {$this->tablePrefix}sc_store ss on tci.tc_code=ss.tc_code left join {$this->tablePrefix}oc_b2b_order oo on tci.oc_code=oo.op_code and ss.sc_code=oo.sc_code where {$where} order by {$order}",
            'getCashList'=>"select {$fields} from {$this->tablePrefix}tc_account_freeze_action tci left join {$this->tablePrefix}sc_store ss on tci.tc_code=ss.tc_code where {$where} order by {$order}",
            'getAccontList'=>"select {$fields} from {$this->tablePrefix}tc_main_account where {$where}",
        );

        return $sqls[$sql_flag];
    }
       

}

?>
