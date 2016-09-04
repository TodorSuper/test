<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 商家中心列表sql
 */

namespace Com\Common\CommonView;

use System\Base;

class ScSql extends Base {

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
    public function sqls($fields, $where, $order, $group, $having, $sql_flag, $other) {

        //而外参数
        if (!empty($other)) {
            extract($other);
        }
        //列表sql  开发者往里面写sql就可以
        $sqls = array(
            'stardand_list' => "SELECT
                                 {$fields} 
                                FROM
                                        {$this->tablePrefix}ic_item ii
                                LEFT JOIN {$this->tablePrefix}category_end ce ON ii.category_end_id = ce.id
                                WHERE
                                        {$where} order by {$order}", //标准库商品列表
                                                
            // 'store_items' => "SELECT
            //                     {$fields}
            //                     FROM
            //                             {$this->tablePrefix}ic_item ii
            //                     LEFT JOIN {$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code
            //                     WHERE  {$where} order by {$order}", //商家商品列表
            'store_items' => "SELECT
                                {$fields}
                                FROM
                                        {$this->tablePrefix}ic_store_item isi
                                LEFT JOIN {$this->tablePrefix}ic_item ii ON ii.ic_code = isi.ic_code
                                LEFT JOIN {$this->tablePrefix}ic_item_class cl ON ii.class_id = cl.id
                                WHERE  {$where} order by {$order}", //商家商品列表
            'goods_items' => "SELECT
                                {$fields}
                                FROM
                                        {$this->tablePrefix}ic_item ii
                                LEFT JOIN {$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code
                                LEFT JOIN {$this->tablePrefix}sc_store ss ON isi.sc_code = ss.sc_code
                                WHERE  {$where} order by {$order}", //商家商品列表
            'carousel_list'=>"select {$fields} from {$this->tablePrefix}sc_carousel where {$where} order by {$order}",

            'statistic_list'=>"SELECT
                               {$fields}
                          FROM
                               {$this->tablePrefix}sc_store ss
                          LEFT JOIN
                               {$this->tablePrefix}uc_merchant  um ON ss.merchant_id=um.id
                          LEFT JOIN
                                {$this->tablePrefix}oc_b2b_order obo ON ss.sc_code=obo.sc_code
                          WHERE
                                {$where}
                          GROUP BY {$group}

                         order by {$order}",

            'store_list'=>"SELECT
                          {$fields}
                          FROM
                          {$this->tablePrefix}sc_store ss
                          LEFT JOIN
                          {$this->tablePrefix}uc_merchant um ON ss.merchant_id=um.id
                          LEFT JOIN {$this->tablePrefix}uc_customer uc ON ss.sc_code=uc.sc_code
                          WHERE
                          {$where} ",
            'store_item_export' => "SELECT
                                {$fields}
                                FROM
                                {$this->tablePrefix}ic_store_item isi
                                LEFT JOIN
                                {$this->tablePrefix}sc_store ss ON isi.sc_code=ss.sc_code
                                WHERE {$where}
                                GROUP BY {$group}",

            'sub_account_list'=>"SELECT
                          {$fields}
                          FROM
                          {$this->tablePrefix}sc_sub_account sa
                          LEFT JOIN
                          {$this->tablePrefix}uc_user us ON sa.uc_code=us.uc_code
                           LEFT JOIN
                          {$this->tablePrefix}sc_store ss ON sa.uc_code=ss.uc_code
                           LEFT JOIN
                          {$this->tablePrefix}sc_roles sr ON sa.roles_id=sr.id
                          WHERE
                          {$where}
                          order by {$order}
            ",
            'cms_item_export'=>"SELECT 
                              {$fields}
                              FROM
                                {$this->tablePrefix}ic_store_item isi
                              LEFT JOIN
                                {$this->tablePrefix}sc_store sc ON isi.sc_code=sc.sc_code
                              LEFT JOIN
                                {$this->tablePrefix}ic_item ii ON ii.ic_code=isi.ic_code
                                WHERE
                          {$where}  
                          order by {$order}

            ",
            'store_lists'=>"SELECT
                              {$fields}
                          FROM
                              {$this->tablePrefix}sc_store ss
                          LEFT JOIN
                              {$this->tablePrefix}uc_merchant um ON ss.merchant_id=um.id
                          WHERE
                              {$where}
                          order by 
                              {$order}",

            'platform_items' => "SELECT
                                      {$fields}
                                 FROM
                                      {$this->tablePrefix}ic_store_item isi
                                 LEFT JOIN
                                      {$this->tablePrefix}ic_item ii ON ii.ic_code = isi.ic_code
                                 LEFT JOIN
                                      {$this->tablePrefix}sc_store ss ON isi.sc_code = ss.sc_code
                                 WHERE
                                      {$where}
                                 ORDER BY
                                      {$order}",
        );
        return $sqls[$sql_flag];
    }

}

?>
