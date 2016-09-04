<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | Boss 个人信息
 */

namespace Bll\Boss\User;

use System\Base;
class User extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



   /**
    * @api  Boss版账号设置
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.options
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-22
    * @apiSampleRequest On
    */

   public function options($params){

        // 获取用户信息   主要获得权限 
        $params['user_type'] = UC_USER_MERCHANT;
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $user_res = $this->invoke($apiPath,$params);
        if($user_res['status'] != 0){
            $this->endInvoke(null,$user_res['status'],'',$user_res['message']);
        }

        // 获取店铺信息
        $data['sc_code'] = $params['sc_code'];
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $store_res = $this->invoke($apiPath,$data);
        if($store_res['status'] != 0){
            $this->endInvoke(null,$store_res['status'],'',$store_res['message']);
        }

        $data = array(
            'sc_code'=>$store_res['response']['sc_code'],
            'head'=>$store_res['response']['logo'],
            'linkman'=>$store_res['response']['linkman'],
            'phone'=>$store_res['response']['phone'],
            'type'=>'超级管理员',                            # 先给死
            'power'=>'业务',                                 # 先给死
            );

        return $this->endInvoke($data);
   }



   /**
    * @api  Boss版修改密码
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.changePwd
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-22
    * @apiSampleRequest On
    */

   public function changePwd($params){
        // 密码规则为:6-16位数字、英文及下划线组合
        $rule = "/^\w{6,16}$/";
        if(!preg_match($rule, $params['ori_pwd'])){
            return $this->endInvoke(NULL,4046);
        }
        if(!preg_match($rule, $params['new_pwd'])){
            return $this->endInvoke(NULL,4046);
        }
        if(!preg_match($rule, $params['confirm_pwd'])){
            return $this->endInvoke(NULL,4046);
        }

        // 可以单独为数字或英文，不可单独为下划线；
        $rule = "/^_{6,16}$/";
        if(preg_match($rule, $params['ori_pwd'])){
            return $this->endInvoke(NULL,4047);
        }
        if(preg_match($rule, $params['new_pwd'])){
            return $this->endInvoke(NULL,4047);
        }
        if(preg_match($rule, $params['confirm_pwd'])){
            return $this->endInvoke(NULL,4047);
        }

        // 两次密码不能一致
        if($params['ori_pwd'] == $params['new_pwd']){
            return $this->endInvoke(NULL,4048);
        }
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.Basic.changePwd";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',4014);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4014);
        }
   }


   /**
    * @api  Boss校验原密码
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.checkPwd
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-22
    * @apiSampleRequest On
    */
    public function checkPwd($params){
        $apiPath = "Base.UserModule.User.Basic.checkPwd";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            $this->endInvoke(null,$res['status'],'',$res['message']);
        }
        return $this->endInvoke($res['response']);
    }


   /**
    * @api  Boss版修改手机下一步 主要验证手机,验证码,对不对
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.getPhone
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-22
    * @apiSampleRequest On
    */

    public function getPhone($params){

       // 判断验证码是否一致
        $code_key  = \Library\Key\RedisKeyMap::getCode($params['phone']);
        $redis = R();
        $code = $redis->get($code_key);
        if($params['code'] != $code || empty($params['code']) || empty($code)){
            return $this->endInvoke(NULL,4049);
        }

        // 检验电话是否正确
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $user_res = $this->invoke($apiPath,$params);
        if($user_res['status'] != 0){
            return $this->endInvoke(null,$user_res['status'],'',$user_res['message']);
        }

        $numbers = $user_res['response']['phone'];

        if($params['phone'] != $numbers){
            return $this->endInvoke(NULL,4050);
        }

        $redis->delete($code_key);                   # 删除相应的缓存
        return $this->endInvoke(true);

    }
   



   /**
    * @api  Boss版修改手机发送验证码
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.sendVerification
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-22
    * @apiSampleRequest On
    */

   public function sendVerification($params){

        $check_code = mt_rand(1000, 9999);
        $message = "尊敬的用户，您的验证码是：$check_code, 我们会为您提供更优质的服务，非常感谢您的加入！";
        $data = array(
            'sys_name'=>BOSS,
            'numbers' =>$params['phone'],
            'message' =>$message,
        );

        $apiPath = "Com.Common.Message.Sms.send"; 
        $send_res = $this->invoke($apiPath,$data);

        if($send_res['status'] != 0){
            $this->endInvoke(null,$send_res['status'],'',$send_res['message']);
        }
        
        // 存入缓存
        $code_key  = \Library\Key\RedisKeyMap::getCode($params['phone']);
        $redis = R();
        $redis->set($code_key,$check_code);

        return $this->endInvoke($res);

   }


   /**
    * @api  Boss版修改手机修改完成
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.changePhone
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-22
    * @apiSampleRequest On
    */  

   public function changePhone($params){

        // 输入的不是手机号
        $rule = "/^1[34578]\d{9}$/";
        if(!preg_match($rule, $params['phone'])){
            return $this->endInvoke(NULL,4056);
        }

        // 判断验证码是否一致
        $code_key  = \Library\Key\RedisKeyMap::getCode($params['phone']);
        $redis = R();
        $code = $redis->get($code_key);
        if($params['code'] != $code || empty($params['code']) || empty($code) ){
            return $this->endInvoke(NULL,4049);
        }

        // 获取以前的手机号
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $user_res = $this->invoke($apiPath,$params);
        if($user_res['status'] != 0){
            $this->endInvoke(null,$user_res['status'],'',$user_res['message']);
        }

        $numbers = $user_res['response']['phone'];

        if($params['phone'] == $numbers){
            return $this->endInvoke(NULL,4052);
        }

        try{
            D()->startTrans();
            $data['uc_code'] = $params['uc_code'];
            $data['data']['phone']  = $params['phone'];
            $data['data']['uc_code'] = $params['uc_code'];
            
            // 更改商铺的手机号
            $apiPath = "Base.StoreModule.Basic.Store.update";
            $store_res = $this->invoke($apiPath,$data);
            if($store_res['status'] != 0){
                $this->endInvoke(null,$store_res['status'],'',$store_res['message']);
            }
            
            // 更新用户的手机号
            $data = array();
            $data['uc_code'] = $params['uc_code'];
            $data['mobile']  = $params['phone'];
            $data['merchant_id'] = $store_res['response']['merchant_id'];
            $apiPath = "Base.UserModule.User.Basic.update";
            $res = $this->invoke($apiPath,$data);
            if($res['status'] != 0){
                $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',4051);
            }

            // 给以前手机发送短信
            $message = "您的粮人网绑定手机号码已成功更改，若非本人操作请联系粮人网客服电话".C('CALL_NUMBER');
            $before_data = array(
                'sys_name'=>BOSS,
                'numbers' =>$numbers,
                'message' =>$message,
            );

            $apiPath = "Com.Common.Message.Sms.send"; 
            $send_res = $this->invoke($apiPath,$before_data);

            if($send_res['status'] != 0){
                $this->endInvoke(null,$send_res['status'],'',$send_res['message']);
            }

            // 给现在手机发送信息
            $message = "您的本机号码已成功绑定粮人网，感谢使用【粮人网】";
            $before_data = array(
                'sys_name'=>BOSS,
                'numbers' =>$params['phone'],
                'message' =>$message,
            );

            $apiPath = "Com.Common.Message.Sms.send"; 
            $send_res = $this->invoke($apiPath,$before_data);

            if($send_res['status'] != 0){
                $this->endInvoke(null,$send_res['status'],'',$send_res['message']);
            }

            $redis->delete($code_key);                   # 删除相应的缓存
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4051);
        }
   }



   /**
    * @api  Boss退出接口
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.User.logout
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-21
    * @apiSampleRequest On
    */

   public function logout($params){
        return $this->endInvoke(true);
   }



}

?>