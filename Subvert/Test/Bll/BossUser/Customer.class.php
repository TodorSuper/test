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

namespace Test\Bll\BossUser;

use System\Base;
class Customer extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 展示首页
     * Test.Bll.BossUser.Customer.get
     * @param type $params
     */

    public function get($params){
    	$apiPath = "Bll.Boss.User.Customer.get";
        $params = array(
            'uc_code' =>'1210000001046',
            'sc_code'=> '1010000000081',
            'month'=>1,
            'year'=>2016,
            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }




}























 ?>