<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | cms用户相关模块
 */

namespace Bll\Cms\User;
use System\Base;

class User extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    /**
     * 修改密码
     * Bll.Cms.User.User.rePassword
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function rePassword($params){
        try {
            D()->startTrans();
            $apiPath = 'Base.UserModule.User.Basic.changePwd';
            $params['check_ori_pwd'] = 'YES';
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke(NULL, $res['status']);
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (Exception $e) {
            D()->rollback();
            return $this->endInvoke(NULL, 8);
        }
    }

    /**
     * Bll.Cms.User.User.getQrcode
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getQrcode($params){
        $apiPath = 'Base.UserModule.User.User.getQrcode';
        $res = $this->invoke($apiPath, $params);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return $this->endInvoke($res['response']);
    }

    /**
     * 生成用户编码
     * Bll.Cms.User.User.mkCode
     * @param array $data   用户数据
     * @param integer $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @return integer   成功时返回  自增id
     */
    public function mkCode($params){
         try{
             D()->startTrans();
             $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
             $data = array(
                'busType'=>UC_USER,
                'preBusType'=>UC_USER_CMS,
                'codeType'=>SEQUENCE_USER,
            );
             $code_res = $this->invoke($apiPath, $data);
             D()->commit();
             return $this->res($code_res['response']);
         } catch (\Exception $ex) {
             D()->rollback();
             return $this->res(NULL,10002,'','生成用户编码失败');
         }        
    }

    /**
     * Bll.Cms.User.User.platformSaleman
     * [platformSaleman description]
     * @return [type] [description]
     */
    public function platformSaleman($params){
        $apiPath = 'Base.UserModule.User.User.platformSaleman';
        $res = $this->invoke($apiPath, $params);
        $uc_codes = array_column($res['response']['lists'],'uc_code');
        if($uc_codes){
            $apiPath = 'Base.UserModule.User.User.userNum';
            $data = $this->invoke($apiPath,$uc_codes);
            foreach($res['response']['lists'] as $key=>$val){
                $res['response']['lists'][$key]['num']=$data['response'][$val['uc_code']]['num'];
            }
        }
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
       
        return $this->endInvoke($res['response']);
    }

    /**
     * Bll.Cms.User.User.platformSeller
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function platformSeller($params){
        
        $arr = array();
        $apiPath = 'Base.UserModule.Customer.Customer.platformList';
        $platformSellerRes = $this->invoke($apiPath, $params);
        if ($platformSellerRes['status'] != 0) {
            return $this->endInvoke(NULL, $platformSellerRes['status']);
        }
        $arr['platformSeller'] = $platformSellerRes['response'];

        $platformSalemanApi = 'Base.UserModule.User.User.platformSaleman';
        $data            = array();
        $data['is_page'] = 'NO';
        $res = $this->invoke($platformSalemanApi, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        $platformSalemanList = $res['response'];
        $platformSalemanList = changeArrayIndex($platformSalemanList, 'invite_code');
        if (!empty($arr['platformSeller']['lists'])) {
            foreach ($arr['platformSeller']['lists'] as $key => $value) {
                if ($platformSalemanList[$value['invite_code']]) {

                    $arr['platformSeller']['lists'][$key]['platformSaleman'] = $platformSalemanList[$value['invite_code']]['real_name'];
                }else{
                    $arr['platformSeller']['lists'][$key]['platformSaleman'] = '火星人';
                }
            }
        }
        $arr['platformSaleman'] = $platformSalemanList;
        return $this->endInvoke($arr);
    }
    /*
    * 删除用户
    * Bll.Cms.User.User.deleteSeller
    */
    public function deleteSeller($params) {
        try{
            D()->startTrans();
             $apiPath = "Base.UserModule.User.User.deleteSeller";
             $data = array(
                'uc_code'=>$params['uc_code'],
            );
            $code_res = $this->invoke($apiPath, $data);
       
            if ($code_res['status']==0 ) {
                D()->commit();
            } else {
                return $this->endInvoke(null,4015);
            }
            return $this->endInvoke($code_res['response']);
         } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4015);
         }    
    }
    /**
     * 设置平台业务员状态
     * Bll.Cms.User.User.setPlatformSaleman
     * @param [type] $params [description]
     */
    public function setPlatformSaleman($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.User.setPlatformSaleman";
            
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke(NULL, $res['status']);
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            D()->rollback();
            return $this->endInvoke(NULL, 8);
        }      
    }

    /**
     * 设置业务员邀请码
     * Bll.Cms.User.User.setPlatInviteCode
     * @param [type] $params [description]
     */
    public function setPlatInviteCode($params){
        
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.User.setPlatInviteCode";
            
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke(NULL, $res['status']);
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 8);
        }     
    } 
    /**
     * Bll.Cms.User.User.seller  "sc_code":"1010000000075"
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function seller($params){

        $apiPath = 'Base.UserModule.Customer.Customer.cmsPopCustomer';
        $customerRes = $this->invoke($apiPath, $params);
        if ($customerRes['status'] != 0) {
            return $this->endInvoke(NULL, $customerRes['status']);
        }
        // var_dump($customerRes);
        $storeApiPath = "Base.StoreModule.Basic.Store.lists";
        $store_res    = $this->invoke($storeApiPath);
        if ($store_res['status'] != 0) {
            return $this->endInvoke(NULL, $store_res['status']);
        }
        $storesInfo   = $store_res['response'];
        $customerRes['response']['storesInfo'] = $storesInfo;
        return $this->endInvoke($customerRes['response']);
    }

    /**
     * Bll.Cms.User.User.export  "sc_code":"1010000000075"
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function export($params){
        $apiPath = 'Base.UserModule.Customer.Customer.export_pop';
        $customerRes = $this->invoke($apiPath, $params);
        if ($customerRes['status'] != 0) {
            return $this->endInvoke(NULL, $customerRes['status']);
        }
        return $this->endInvoke($customerRes['response']);
    }

    /**
     * Bll.Cms.User.User.export  "sc_code":"1010000000075"
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function plat_export($params){
        $apiPath = 'Base.UserModule.Customer.Customer.plat_export';
        $customerRes = $this->invoke($apiPath, $params);
        if ($customerRes['status'] != 0) {
            return $this->endInvoke(NULL, $customerRes['status']);
        }
        return $this->endInvoke($customerRes['response']);
    }

    /**
     * Bll.Cms.User.User.setTerminal
     * @param  [type] $params [description]
     * @return [type]         [description]
     */

    public function setTerminal($params){
        $apiPath = 'Base.UserModule.User.User.setTerminal';
        $res = $this->invoke($apiPath,$params);
        if($res['status']!==0){
            return $this->endInvoke('',2011);
        }
        return $this->endInvoke(true);
    }
    /**
     * 获取downlist列表
     * Bll.Cms.User.User.getDownList
     * @param  [type] $params [description]
     * @return [return]         [List]
     */
    public function getDownList($params){
        $apiPath = "Com.DataCenter.Download.DownList.getDownlist";
        $list_res = $this->invoke($apiPath, $params);
        return $this->endInvoke($list_res['response']);
    }
    /**
     * 获取商家的列表
     * Base.StoreModule.Basic.User.getMerchantInfo
     * @return [return]         [List]
     */
    public function getMerchantLists($params){
       // $apiPath = "Base.StoreModule.Basic.User.getMerchantLists";
        $apiPath =  "Base.StoreModule.Basic.Store.lists";
        $list_res = $this->invoke($apiPath, $params);
        if(0 !== $list_res['status']){
            return $this->endInvoke('查询用户列表失败');
        }

        return $this->endInvoke($list_res['response']);
    }

    public function login($params) {
        $apiPath =  "Base.UserModule.User.User.login";
        $list_res = $this->invoke($apiPath, $params);
        if(0 !== $list_res['status']){
            return $this->endInvoke('登录失败');
        }
        return $this->endInvoke($list_res['response']);
    }
   
}

?>
