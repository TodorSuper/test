<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单中心列表sql
 */

namespace Com\Common\CommonView;

use System\Base;

class BicSql extends Base {

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
          	'order_list'=>"SELECT
          	                         {$fields}
          	                 FROM
          	                         {$this->tablePrefix}oc_b2b_order 
          	                 WHERE
          	                         {$where}
          	                 ORDER BY
          	                         {$order}",//订单列表
            'user_list'=>"SELECT
                               {$fields}
                           FROM
                           {$this->tablePrefix}uc_member
                           WHERE
                           {$where}
                           ORDER BY
                           {$order} ",//买家列表
            'store_list'=>"SELECT
                               {$fields}
                           FROM
                           {$this->tablePrefix}sc_store
                           WHERE
                           {$where}
                           GROUP BY
                           {$group}
                           ORDER BY
                           {$order} ",//买家列表
            'advance_list' => "SELECT
                               {$fields}
                               FROM
                               {$this->tablePrefix}tc_main_account tma 
                               LEFT JOIN {$this->tablePrefix}uc_customer uc ON tma.code = uc.uc_code
                               LEFT JOIN {$this->tablePrefix}sc_store ss ON ss.sc_code = uc.sc_code
                               LEFT JOIN {$this->tablePrefix}uc_merchant um ON ss.merchant_id = um.id
                               WHERE
                               {$where}
                               ORDER BY
                               {$order} ",//买家列表 
            'item_list' => "SELECT
                               {$fields}
                               FROM
                               {$this->tablePrefix}ic_store_item_status 
                               WHERE
                               {$where}
                               ORDER BY
                               {$order} ",//买家列表 

        );
            
        
        return $sqls[$sql_flag];
    }
       

}