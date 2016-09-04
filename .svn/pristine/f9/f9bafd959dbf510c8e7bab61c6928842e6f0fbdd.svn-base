<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b地址模块
 */

namespace Bll\B2b\User;
use System\Base;

class Address extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    /**
     * 添加地址
     * Bll.B2b.User.Address.add
     * @param type $params
     */
    public function add($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Address.Address.add";
            $add_res = $this->invoke($apiPath, $params);
            if($add_res['status'] != 0){
                return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4004);
        }
    }
    
    /**
     * 添加地址
     * Bll.B2b.User.Address.update
     * @param type $params
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Address.Address.update";
            $add_res = $this->invoke($apiPath, $params);
            if($add_res['status'] != 0){
                return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4006);
        }
    }
    
     /**
     * 删除地址
     * Bll.B2b.User.Address.delete
     * @param type $params
     */
    public function delete($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Address.Address.delete";
            $add_res = $this->invoke($apiPath, $params);
            if($add_res['status'] != 0){
                return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4035);
        }
    }
    
    /**
     * 获取地址详细信息
     * Bll.B2b.User.Address.get
     * @param type $params
     */
    public function get($params){
        $apiPath = "Base.UserModule.Address.Address.get";
        $get_res = $this->invoke($apiPath, $params);
        if($get_res['status'] != 0){
            return $this->endInvoke(null,$get_res['status']);
        }
        $address_info = $get_res['response'];
        //获取区列表
        $data = array(
            'pid'=>33,
        );
        $apiPath = "Com.Tool.Region.Region.getAreaBuyPid";
        $area_res = $this->invoke($apiPath,$data);
        $address_info['district_list'] = $area_res['response'];
        return $this->endInvoke($address_info);
    }
    
    /**
     * 地址列表
     * Bll.B2b.User.Address.lists
     * @param type $params
     */
    public function lists($params){
        $apiPath = "Base.UserModule.Address.Address.lists";
        $list_res = $this->invoke($apiPath, $params);
        if($list_res['status'] != 0){
            return $this->endInvoke(NULL,$list_res['status']);
        }
        return $this->endInvoke($list_res['response']['lists']);
    }

}

?>
