<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b注册模块
 */

namespace Test\Bll\B2bUser;
use System\Base;

class User extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 个人中心
     * Test.Bll.B2bUser.User.userCenter
     * @param type $params
     */
    public function userCenter($params){

        $params = array(
            'sc_code'=>1020000000026,
            'uc_code'=>1230000000720,
            // 'type'=>'ship',
            // 'sys_name'=>'B2B',
            // 'start_time'=>'1446886348'
            // 'openid'=>'o7lz9v1fUpiFH1eZMxiwDB1TnpSc',
            );
        // $apiPath = "Base.OrderModule.B2b.Statistic.bubble";
        $apiPath = "Bll.B2b.User.User.userCenter";
        // $apiPath = "Bll.B2b.User.User.look";
        // $apiPath = "Bll.B2b.User.User.autoLogin";
        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
        
    }

}

?>
