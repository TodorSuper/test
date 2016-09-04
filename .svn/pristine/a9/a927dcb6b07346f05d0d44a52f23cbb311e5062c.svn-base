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

class Region extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    /**
     * 注册用户
     * Test.Bll.B2bUser.Region.region
     * @param type $params
     */
    public function region($params){
        $rand = mt_rand(10000000,99999999);
        $mobile = '156'.$rand;
        $apiPath = "Bll.B2b.User.Region.region";
        $data = array(
            'mobile' => $mobile,
            'name' =>'朱晓猛',
            'commercial_name'=>'多商家',
            'province'=>'北京市',
            'city'=>'北京市',
            'district'=>'朝阳区',
            'address'=>'融科望京中心B座1803',
            'invite_code'=>'9475',
            'openid'=>'',
            'register_from'=>'WAP',
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->res(NULL,$res['status']);
        
    }

}

?>
