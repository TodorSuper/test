<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户列表sql
 */

namespace Com\Common\CommonView;
use System\Base;

class UcSql extends Base  {

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
            'address_list'  =>  "SELECT
                                        {$fields}
                                 FROM
                                        {$this->tablePrefix}uc_address
                                 WHERE
                                        {$where}
                                 ORDER BY
                                        {$order} ", //地址列表
            'cart_list'     =>  "SELECT
                                        {$fields}
                                 FROM
                                        {$this->tablePrefix}uc_cart uc
                                 LEFT JOIN {$this->tablePrefix}ic_store_item isi ON uc.sic_code = isi.sic_code
                                 LEFT JOIN {$this->tablePrefix}ic_item ii ON isi.ic_code = ii.ic_code where {$where} order by {$order}",  //购物车列表
            'customer_list' =>  "SELECT
                                            {$fields}
                                    FROM
                                            {$this->tablePrefix}uc_customer uc
                                    LEFT JOIN {$this->tablePrefix}sc_channel sh ON uc.channel_id = sh.id
                                    LEFT JOIN {$this->tablePrefix}sc_salesman ss ON uc.salesman_id = ss.id

                                    WHERE
                                            {$where}
                                    ORDER BY
                                            {$order}",
            'salesman_list'=>  "SELECT
                                            {$fields}
                                    FROM
                                            {$this->tablePrefix}sc_salesman
                                    WHERE
                                            {$where} order by {$order}", 
            'channel_list'=>  "SELECT
                                            {$fields}
                                    FROM
                                            {$this->tablePrefix}sc_channel
                                    WHERE
                                            {$where} order by {$order}", 
            'down_list' => "SELECT
                                {$fields}
                            FROM
                                {$this->tablePrefix}downlist
                            WHERE
                                {$where}
                            ORDER BY 
                                {$order}
                            ",
            'cms_platformSaleman' => "SELECT
                                            {$fields}
                                    FROM
                                            {$this->tablePrefix}ac_principal ac
                                    LEFT JOIN {$this->tablePrefix}uc_user uc ON uc.id = ac.uid
                                    LEFT JOIN {$this->tablePrefix}uc_salesman us ON uc.uc_code = us.uc_code
                                    WHERE
                                            {$where}
                                    ORDER BY
                                            {$order}",
            'cms_platform_customer' => "SELECT
                                            {$fields}
                                    FROM
                                            {$this->tablePrefix}uc_member um
                                    LEFT JOIN {$this->tablePrefix}uc_user uc ON um.uc_code = uc.uc_code
                                    LEFT JOIN {$this->tablePrefix}uc_salesman us ON um.invite_code = us.invite_code
                                    WHERE
                                            {$where}
                                    ORDER BY
                                            {$order}",
            'cms_POP_customer' => "SELECT
                                            {$fields}
                                    FROM
                                            {$this->tablePrefix}uc_member um
                                    LEFT JOIN {$this->tablePrefix}uc_customer uc ON um.uc_code = uc.uc_code

                                    LEFT JOIN {$this->tablePrefix}sc_store ss ON uc.sc_code = ss.sc_code

                                    WHERE
                                            {$where}
                                    ORDER BY
                                            {$order}",
            'platformCartLists'  =>  "SELECT
                                        {$fields}
                                 FROM
                                        {$this->tablePrefix}sc_store ss
                                 LEFT JOIN 
                                        {$this->tablePrefix}uc_cart uc ON uc.sc_code = ss.sc_code
                                 LEFT JOIN 
                                        {$this->tablePrefix}ic_store_item isi ON uc.sic_code = isi.sic_code
                                 LEFT JOIN 
                                        {$this->tablePrefix}ic_item ii ON isi.ic_code = ii.ic_code 
                                 WHERE 
                                        {$where} 
                                 ORDER BY 
                                        {$order}",  //购物车列表
            'feedback_list' => "SELECT
                                    {$fields}
                                FROM
                                    {$this->tablePrefix}uc_problem up
                                LEFT JOIN 
                                    {$this->tablePrefix}uc_feedback uf ON uf.feedback_code = up.feedback_code
                                LEFT JOIN 
                                    {$this->tablePrefix}uc_member um ON um.uc_code = up.uc_code
                                WHERE
                                    {$where}
                                ORDER BY
                                    {$order}",
        );
            
        
        return $sqls[$sql_flag];
    }
       

}

?>
