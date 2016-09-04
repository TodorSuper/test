<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\BossStasitc;

use System\Base;
class Item extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 热销
     * Test.Bll.BossStasitc.Item.hot
     * @param type $params
     */

    public function hot($params){
    	$apiPath = "Bll.Boss.Stasitc.Item.hot";
        // $apiPath = "Base.OrderModule.Boss.Order.hot";
        $params = array(
            'sc_code' =>'1020000000026',
            'year'=>2015,
            'month'=>9,
            'sort'=>'ORDERS_DESC',
            // 'number'=>5,
            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }




}























 ?>