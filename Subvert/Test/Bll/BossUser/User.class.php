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
class User extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Boss版账号设置
     * Test.Bll.BossUser.User.options
     * @param type $params
     */

    public function options($params){
        $apiPath = "Bll.Boss.User.User.options";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1210000000372',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }


    /**
     * Boss版修改密码
     * Test.Bll.BossUser.User.changePwd
     * @param type $params
     */

    public function changePwd($params){
        $apiPath = "Bll.Boss.User.User.changePwd";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1210000000372',
            'ori_pwd' => '1234567',
            'new_pwd' => '123456',
            'confirm_pwd' => '123456', 
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }


    /**
     * Boss版修改手机发送验证码
     * Test.Bll.BossUser.User.sendVerification
     * @param type $params
    */

    public function sendVerification($params){

        $apiPath = "Bll.Boss.User.User.sendVerification";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1210000000372',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);

    }


    /**
     * Boss版修改手机（下一步）
     * Test.Bll.BossUser.User.getPhone
     * @param type $params
     */

    public function getPhone($params){

        $apiPath = "Bll.Boss.User.User.getPhone";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1210000000372',
            'code' => '1234',
            'phone' => '18511099264',
            'check_code' => '1234',   # token session 获得
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);

    }



    /**
     * Boss版修改手机（完成）
     * Test.Bll.BossUser.User.changePhone
     * @param type $params
     */

    public function changePhone($params){

        $apiPath = "Bll.Boss.User.User.changePhone";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1120000000103',
            'code' => '1234',
            'phone' => '18511099264',
            'check_code'=>'1234',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);

    }


    /**
     * Boss版系统设置列表
     * Test.Bll.BossUser.User.versonList
     * @param type $params
     */

    public function versonList($param){
        $apiPath = "Bll.Boss.User.User.versonList";
        $params = array(

            );
        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }



}




 ?>