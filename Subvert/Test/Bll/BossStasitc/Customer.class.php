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
class Customer extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 客户统计
     * Test.Bll.BossStasitc.Customer.all
     * @param type $params
     */

    public function all($params){
    	$apiPath = "Bll.Boss.Stasitc.Customer.all";
        $params = array(
            'sc_code' =>'1010000000077',
            'year'=>2016,
            'month'=>1,
            'sort'=>'AMOUNT_DESC',
            'pageNumber'=>1,
            'pageSize'=>20,
            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }




}























 ?>