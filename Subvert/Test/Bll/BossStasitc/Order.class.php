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
class Order extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 展示首页
     * Test.Bll.BossStasitc.Order.all
     * @param type $params
     */

    public function all($params){
    	$apiPath = "Bll.Boss.Stasitc.Order.all";
        // $apiPath = "Base.OrderModule.Boss.Order.all";
        $params = array(
            'sc_code' =>'1010000000077',
            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }


    /**
     * 月度统计
     * Test.Bll.BossStasitc.Order.month
     * @param type $params
     */
    public function month($params){

        $apiPath = "Bll.Boss.Stasitc.Order.month";
        // $apiPath = "Base.OrderModule.Boss.Order.month";
        $params = array(
            'sc_code' =>'1010000000077',
            'year'=>'2016',
            // 'sort'=>'MONTH_DESC',
            // 'month'=>'12',
            // 'day'=>'3',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }

}























 ?>