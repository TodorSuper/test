<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 基础用户信息相关模块
 */

namespace Bll\Pop\User;
use System\Base;

class Basic extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    
    /**
     * 修改用户密码
     * Bll.Pop.User.Basic.changePwd
     * @param type $check_ori_pwd
     * @param type $ori_pwd
     * @param type $new_pwd
     * @param type $confirm_pwd
     * @param type $uc_code
     * @return array
     */
    public function changePwd($params){
        try {
			D()->startTrans();
            $apiPath     =  "Base.UserModule.User.Basic.changePwd";
            $change_res  = $this->invoke($apiPath, $params);
            if($change_res['status'] != 0){
                return $this->endInvoke(NULL,$change_res['status']);
            }
            $res = D()->commit();
            if(FALSE  === $res){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke($change_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4014);
        }        
    }

    /**
     * 修改手机号
     * Bll.Pop.User.Basic.updateMobile
     * @param type $uc_code
     * @param type $mobile
     * @return array
     */

    public function updateMobile ($params) {
        try{
            D()->startTrans();
            //修改用户
            $userData['uc_code'] = $params['uc_code'];
            $userData['mobile'] = $params['mobile'];
            $apiPath = "Base.UserModule.User.Basic.update";
            $change_res  = $this->invoke($apiPath, $userData);
            if($change_res['status'] != 0){
                return $this->endInvoke(NULL,$change_res['status']);
            }

            //获取个人信息
            $userInfo['uc_code'] = $params['uc_code'];
            $user = $this->invoke('Base.UserModule.User.Basic.getBasicUserInfo', $userInfo);

            if($user['response']['group_id'] != UC_USER_SUB_ACCOUNT){
            //修改商铺
            $storeData['uc_code'] = $params['uc_code'];
            $storeData['data']['phone'] = $params['mobile'];
            $changeStore_res  = $this->invoke('Base.StoreModule.Basic.Store.update', $storeData);
            if($changeStore_res['status'] != 0){
                return $this->endInvoke(NULL,$changeStore_res['status']);
            }
            //修改merchant

            $merchantData['uc_code'] = $changeStore_res['response']['merchant_id'];
            $merchantData['userType'] = UC_USER_MERCHANT;
            $merchantData['data']['phone'] = $params['mobile'];
            $merchantRes = $this->invoke('Base.UserModule.User.User.update', $merchantData);
            if($merchantRes['status'] != 0){
                return $this->endInvoke(NULL,$merchantRes['status']);
            }
            }
            $res = D()->commit();
            if(FALSE  === $res){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke($change_res['response']);

        }catch (\Exception $ex){
            $ex->getMessage();
            D()->rollback();
            return $this->endInvoke(NULL,4014);
        }
    }

    /**
     * 获取基本信息
     * Bll.Pop.User.Basic.getBasicUserStoreInfo
     * @param type $uc_code
     * @param type $sc_code
     * @return array
     */

    public function getBasicUserStoreInfo ($params) {

        //获取用户信息
        $userData['uc_code'] = $params['uc_code'];
        $userInfo = $this->invoke('Base.UserModule.User.Basic.getBasicUserInfo', $userData);

        //获取sc_code
        $subAccountInfo = $this->invoke('Base.StoreModule.User.SubAccount.findOne', $userData);
        //获取商户信息
        $storeData['sc_code'] = $subAccountInfo['response']['sc_code'];
        $storeInfo = $this->invoke('Base.StoreModule.Basic.User.getMerchanInfo', $storeData);

        //获取角色
        $userData['status'] = '1';
        $roles = $this->invoke('Base.StoreModule.User.Roles.findRoles', $userData);

        //拼接信息
        if($userInfo['response']['group_id'] == UC_USER_MERCHANT){
            $userInfo['response']['real_name'] = $storeInfo['response']['linkman'];
        }
        $userInfo['response']['logo'] = $storeInfo['response']['logo'];
        $userInfo['response']['sc_name'] = $storeInfo['response']['name'];
        $userInfo['response']['roles_name'] = $roles['response']['name'];
        return $this->res($userInfo['response'],$userInfo['status']);
    }

    /**
     * 更改手机号发送短信
     * Bll.Pop.User.Basic.sendNote
     * @param type $uc_code
     * @param type $sc_code
     * @return array
     */

    public function sendNote ($params) {
        //验证短信发送次数
        $count = \Library\Key\RedisKeyMap::getCount($params['uc_code'],$params['mobile']);
        $redis = R();

        empty($redis->get($count)) ? $redis->setex($count,300,1) : $redis->setex($count,300,$redis->get($count) + 1);

        if($redis->get($count) > 5) {
            if($redis->get($count) <= 6){
                $redis->expire($count, 600);
            }
            return $this->endInvoke(NULL,5524);
        }


        # 发送通知用户短信
        $data = array(
            'sys_name'=>CMS,
        );
        $userData['uc_code'] = $params['uc_code'];
        $userInfo = $this->invoke('Base.UserModule.User.Basic.getBasicUserInfo', $userData);
        if($userInfo['response']['group_id'] == UC_USER_MERCHANT){
            //获取sc_code
            $subAccountInfo = $this->invoke('Base.StoreModule.User.SubAccount.findOne', $userData);
            //获取商户信息
            $storeData['sc_code'] = $subAccountInfo['response']['sc_code'];
            $storeInfo = $this->invoke('Base.StoreModule.Basic.User.getMerchanInfo', $storeData);
            $userInfo['response']['real_name'] = $storeInfo['response']['linkman'];
        }

        $mobile = $params['mobile'];
        $name = $userInfo['response']['real_name'];
        $code = $params['code'];
        $data['numbers'] = [$mobile];
        $data['message'] = "亲爱的{$name}，您的校验码为：{$code}";
        $this->push_queue('Com.Common.Message.Sms.send', $data, 0 ); # 发送短信通知
        return $this->endInvoke(true);
    }

}

?>
