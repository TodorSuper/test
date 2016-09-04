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

class AppSql extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * BOSS中心列表sql
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
          	'app_list'=>"SELECT
          	                         {$fields}
          	                 FROM
          	                         {$this->tablePrefix}app_version
          	                 WHERE
          	                         {$where}
          	                 ORDER BY
          	                         {$order}",//订单列表

            'patch_list' => "SELECT
                                    {$fields}
                            From 
                                    {$this->tablePrefix}app_patch
                            WHERE
                                    {$where}
                            ORDER BY 
                                    {$order}",

        );
        return $sqls[$sql_flag];
    }
       

}