<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单中心列表sql
 */

namespace Com\Common\CommonView;

use System\Base;

class OcSql extends Base {

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
                                    {$this->tablePrefix}oc_b2b_order obo
                            LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend oboe ON obo.op_code = oboe.op_code
                            LEFT JOIN {$this->tablePrefix}sc_store ss ON obo.sc_code = ss.sc_code
                            WHERE
                                    {$where}
                            ORDER BY
                                    {$order}",//订单列表   
            'order_list_2'=>"SELECT
                                    {$fields}
                            FROM
                                    {$this->tablePrefix}oc_b2b_order obo
                            LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend oboe ON obo.op_code = oboe.op_code
                            LEFT JOIN {$this->tablePrefix}spc_active sa on oboe.active_code = sa.active_code
                            LEFT JOIN {$this->tablePrefix}sc_store ss ON obo.sc_code = ss.sc_code
                            LEFT JOIN {$this->tablePrefix}uc_merchant  um ON ss.merchant_id = um.id
                            WHERE
                                    {$where}
                            ORDER BY
                                    {$order}",//订单列表
            'order_customer_list'=>"SELECT
                                    {$fields}
                            FROM
                                    {$this->tablePrefix}uc_customer uc
                            LEFT JOIN 
                                    {$this->tablePrefix}uc_member um ON uc.uc_code = um.uc_code
                            LEFT JOIN 
                                    {$this->tablePrefix}oc_b2b_order obo ON obo.uc_code = uc.uc_code
                            WHERE
                                    {$where}
                            GROUP BY 
                                    {$group}
                            ORDER BY
                                    {$order}",//BOSS订单客户统计
            'order_item_list'=>"SELECT
                                    {$fields}
                            FROM
                                    {$this->tablePrefix}oc_b2b_order_goods obog
                            LEFT JOIN 
                                    {$this->tablePrefix}oc_b2b_order obo ON obog.b2b_code = obo.b2b_code
                            WHERE
                                    {$where}
                            GROUP BY 
                                    {$group}
                            ORDER BY
                                    {$order}",//BOSS订单客户统计
            'account_list'=>"SELECT 
                                    {$fields}
                            FROM
                                    {$this->tablePrefix}oc_b2b_order od
                            LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend ex ON od.op_code = ex.op_code
                            WHERE
                                    {$where}  AND order_status not in ('MERCHCANCEL','CANCEL') 
                                    {$group} 
                            ORDER BY               
                                    {$order} ",

        );
            
        
        return $sqls[$sql_flag];
    }
       

}

?>
