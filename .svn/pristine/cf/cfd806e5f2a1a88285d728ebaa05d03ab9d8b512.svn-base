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
class Login extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 展示首页
     * Test.Bll.BossUser.Login.getVerify
     * @param type $params
     */

    public function getVerify($params){
    	$apiPath = "Bll.Boss.User.Login.getVerify";
        $params = array(
            'sc_code' =>'1020000000026',
            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }



    /**
     * 登录接口
     * Test.Bll.BossUser.Login.login
     * @param type $params
     */

    public function login($params){
        $apiPath = "Bll.Boss.User.Login.Login";
        $params = array(
            'username'     =>'yushi',
            'password'     =>'123456',

            'device_token' => 'AoHEW5vTFNbYGdN31ChujCfcJF4Gw8IJKH1s3dazywa6',

            'verify'       => '123456',
         

            );
        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']); 
    }


}























 ?>