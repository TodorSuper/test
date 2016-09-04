<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 商家中心列表sql
 */

namespace Com\Common\CommonView;

use System\Base;

class SpcSql extends Base{
    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 促销中心列表sql
     * @param type $fields
     * @param type $where
     * @param type $order
     * @param type $group
     * @param type $having
     * @param type $sql_flag
     * @param type $other
     * @return type
     */
    public function sqls($fields, $where, $order, $group, $having, $sql_flag, $other){
        
        //而外参数
        if (!empty($other)) {
            extract($other);
        }

        //列表sql  开发者往里面写sql就可以
        $sqls=array(
            'spc_list'=>"SELECT
                               {$fields}
                          FROM
                               {$this->tablePrefix}spc_list sl
                          LEFT JOIN
                               {$this->tablePrefix}ic_store_item  isi ON sl.sic_code=isi.sic_code
                          LEFT JOIN
                                {$this->tablePrefix}ic_item ii ON isi.ic_code=ii.ic_code
                          LEFT JOIN
                                {$this->tablePrefix}category_end ce ON ii.category_end_id = ce.id
                          WHERE
                                  {$where}
                         order by {$order}",

            'effect_lists' => " SELECT 
                                        {$fields}
                                FROM 
                                        {$this->tablePrefix}oc_b2b_order_goods obog
                                LEFT JOIN 
                                        {$this->tablePrefix}spc_list sl ON sl.spc_code = obog.spc_code
                                LEFT JOIN
                                        {$this->tablePrefix}oc_b2b_order obo ON obog.b2b_code = obo.b2b_code
                                WHERE
                                        {$where}
                                GROUP BY 
                                        {$group}
                                ORDER BY 

                                        {$order}",
            
            'spc_customer' => "SELECT 
                                        {$fields}
                                FROM 
                                        {$this->tablePrefix}spc_customer 
        
                                WHERE
                                        {$where}
                                ORDER BY 
                                        {$order}
                                ",
            'spc_detail' =>   "SELECT 
                                    {$fields}
                                FROM 
                                        {$this->tablePrefix}oc_b2b_order obo
                                LEFT JOIN 
                                        {$this->tablePrefix}spc_customer scu ON obo.spc_code = scu.spc_code  AND scu.uc_code = obo.uc_code
                                WHERE
                                        {$where}
                                ORDER BY 
                                        {$order}
                                ",

            'customer_lists' => "SELECT
                                        {$fields}
                                 FROM 
                                        {$this->tablePrefix}spc_customer sc
                                 LEFT JOIN 
                                        {$this->tablePrefix}uc_customer uc ON sc.uc_code = uc.uc_code
                                 LEFT JOIN 
                                        {$this->tablePrefix}sc_salesman ss ON uc.salesman_id = ss.id
                                 WHERE 
                                        {$where}
                                 ORDER BY 
                                        {$order}",
            'commodity_lists' => "SELECT
                                        {$fields}
                                  FROM
                                        {$this->tablePrefix}spc_commodity
                                  WHERE
                                        {$where}
                                  ORDER BY
                                        {$order}",
            'active_lists'    =>  "SELECT
                                        {$fields}
                                    FROM
                                         {$this->tablePrefix}spc_active
                                    WHERE
                                         {$where}
                                     ORDER BY
                                          {$order}",
            'coupon_lists'     => "SELECT
                                          {$fields}
                                     FROM
                                          {$this->tablePrefix}spc_active_coupon_relation sacr
                                    LEFT JOIN
                                          {$this->tablePrefix}spc_active_coupon sac on sacr.bat_code = sac.bat_code
                                    WHERE
                                          {$where}
                                     ORDER BY
                                          {$order}",
            'coupon_lists_full_back'     => "SELECT
                                          {$fields}
                                     FROM
                                          {$this->tablePrefix}spc_active_coupon_full_relation sacfr
                                     LEFT JOIN
                                          {$this->tablePrefix}spc_active_full_back safb on sacfr.full_back_id=safb.id
                                    LEFT JOIN
                                          {$this->tablePrefix}spc_active_coupon sac on sacfr.bat_code = sac.bat_code
                                    WHERE
                                          {$where}
                                     ORDER BY
                                          {$order}",
            'cms_active_info'   => "SELECT
                                          {$fields}
                                        FROM
                                           {$this->tablePrefix}uc_member_coupon umc
                                        LEFT JOIN
                                            {$this->tablePrefix}uc_member um on umc.uc_code = um.uc_code
                                         LEFT JOIN
                                            {$this->tablePrefix}spc_active_coupon sac on umc.bat_code = sac.bat_code
                                        LEFT JOIN
                                              {$this->tablePrefix}spc_active sa on umc.active_code = sa.active_code
                                        WHERE
                                             {$where}
                                         ORDER BY
                                             {$order}",


        );
        return $sqls[$sql_flag];
    }
}