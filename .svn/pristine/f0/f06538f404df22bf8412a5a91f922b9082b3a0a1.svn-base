<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 促销效果
 */

namespace Test\Base\SpcModuleCenter;

use System\Base;

class Effect extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 促销效果查询列表
     * Test.Base.SpcModuleCenter.Effect.lists
     * @param type $params
     */
    public function lists($params) {
        // $apiPath = "Base.SpcModule.Center.Effect.lists";      # BASE层商品促销信息
        // $apiPath = "Bll.Pop.Spc.Effect.lists";                # BLL 层商品促销信息
        $apiPath = "Base.SpcModule.Center.Effect.export";        # BASE层商品促销信息导出    导出必须要type
        $data = array(
        	'sc_code'=>1020000000026,
            // 'salesman_id'=>2,                                 #obo中加入salesman_id
            'spc_type'=>array(SPC_TYPE_SPECIAL),                        # 促销类型
            // 'customer'=>'天台',                                  #客户名
            // 'type'=>'all',                                       #全部导出
            'type'=>'detail',                                   #导出详细
            // 'order'=>'all_number',                              #按数量排序
            // 'order'=>'all_price',                               #按价钱排序
            // 'start_time'=>1440275208                            #开始时间
            // 'end_time'=> 1441296000                             #结束时间
            // 'name' =>'东古蒸',                                  #商品名称
            // 'name'=>'306',                                      #商品编码 sic
            // 'start_time' => '2015-08-28',
            // 'end_time' => '2015-09-04',
            // 'salesman_id' => '',
            // 'customer' => '东洋',
            // 'name' => '12200000229',
            // 'export_flag' => 'effect',
            // 'type' => 'all',
        	);

        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }



}







 ?>