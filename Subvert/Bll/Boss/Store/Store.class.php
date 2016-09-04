<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | Boss版登陆
 */

namespace Bll\Boss\Store;

use System\Base;
class Store extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }


    /**
     * @api  Boss版设置页面接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Store.index
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function index($params){
        // 店铺信息
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $store_res = $this->invoke($apiPath,$params);
        if($store_res['status'] != 0){
            return $this->endInvoke(NULL,$store_res['status'],'',$store_res['message']);
        }

        // 获取用户信息   主要获得权限
        $params['user_type'] = UC_USER_MERCHANT;
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $user_res = $this->invoke($apiPath,$params);
        if($user_res['status'] != 0){
            $this->endInvoke(null,$user_res['status'],'',$user_res['message']);
        }


        //  用户设置信息
        $apiPath = "Base.UserModule.User.User.getOptions";
        $options_res = $this->invoke($apiPath,$params);
        if($options_res['status'] != 0){
            return $this->endInvoke(NULL,$options_res['status'],'',$options_res['message']);
        }

        // 版本信息
        $apiPath = "Base.UserModule.User.App.getVersion";
        $version_res = $this->invoke($apiPath,$params);
        if($version_res['status'] != 0){
            return $this->endInvoke(NULL,$version_res['status'],'',$version_res['message']);
        }

        $res = array(
            'sc_code'=>$store_res['response']['sc_code'],
            'head'   =>$store_res['response']['logo'],      # 头像 等pop权限角色图片有再换
            'linkman'=>$store_res['response']['linkman'],
            'min_money'=>$store_res['response']['min_money'],
            'push_msg'=>$options_res['response']['push_msg'],
            'prompt_sound'=> $options_res['response']['prompt_sound'],
            'show_img'=>$options_res['response']['show_img'],
            'phone'=>C('CALL_NUMBER'),
            'type'=> '超级管理员',                          # 角色 等pop权限角色有再换
            'show_update'=>$version_res['response']['show_update'],
            );

        return $this->endInvoke($res);

    }

    /**
     * @api  Boss版获取店铺信息
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Store.getmsg
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function getmsg($params){
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }

        $data = array(
            'sc_code'=>$res['response']['sc_code'],
            'logo'=>$res['response']['logo'],
            'name'=>$res['response']['name'],
            'linkman'=>$res['response']['linkman'],
            'phone'=>$res['response']['phone'],
            'address'=>$res['response']['address'],
            'store_desc'=>$res['response']['store_desc'],
            'min_money' => $res['response']['min_money'],
            );

        return $this->endInvoke($data);
    }



    /**
     * @api  Boss版修改配置
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Store.set
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-22
     * @apiSampleRequest On
     */

    public function set($params){

        try{
            // 修改用户手机推送配置
            D()->startTrans();
            $apiPath = "Base.UserModule.User.User.updateOptions";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                $this->endInvoke(null,$res['status'],'',$res['message']);
            }

            $commit_res = D()->commit();

            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',4053);
            }

            // 查出用户手机推送配置
            $apiPath = "Base.UserModule.User.User.getOptions";
            $options = $this->invoke($apiPath,$params);
            if($options['status'] != 0){
                $this->endInvoke(null,$options['status'],'',$options['message']);
            }

            $data['_output'] = array(
                'push_msg' => $options['response']['push_msg'],
                'prompt_sound' => $options['response']['prompt_sound'],
                'show_img'=>$options['response']['show_img'],
            );

            return $this->res($data);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4053);
        }
   }


    /**
     * @api  Boss版初始化接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Store.init
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-22
     * @apiSampleRequest On
     */
    public function init($params){
        $apiPath = "Base.UserModule.User.App.getPatchVersion";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }

        return $this->endInvoke($res['response']);
    }


    /**
     * @api  Boss版检查更新接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Store.checkUpdate
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-22
     * @apiSampleRequest On
     */
    public function checkUpdate($params){
        $apiPath = "Base.UserModule.User.App.getVersion";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }

        $data = array(
            'version'=>$res['response']['version'],
            'down_url'=>$res['response']['down_url'],
            'update_type'=>$res['response']['update_type'],
            'content'=>$res['response']['content'],
            );
        // 判断是否更新
        if($params['version'] != $res['response']['version']){
            $data['need_update'] = 'YES';
        }else{
            $data['need_update'] = 'NO';
        }
        
        return $this->endInvoke($data);
    }


}

?>