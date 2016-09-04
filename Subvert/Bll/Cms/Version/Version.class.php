<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: nielei <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | cms用户相关模块
 */

namespace Bll\Cms\Version;
use System\Base;

class Version extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    

   /**
    * @api  CMS获取版本列表
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.versionList
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-21
    * @apiSampleRequest On
    */

   public function versionList($params){
        $apiPath = "Base.UserModule.User.App.lists";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(null,$res['status']);
        }
        return $this->endInvoke($res['response']);
   }


   /**
    * @api  CMS版本添加
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.versionAdd
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */

   public function versionAdd($params){

        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.App.add";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                return $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,5021);
        }
   }
   
    
   /**
    * @api  CMS版本添加
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.versionEdit
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */

   public function versionEdit($params){

        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.App.edit";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                return $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,5022);
        }
   }


   /**
    * @api  CMS版获取
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.versionGet
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */

   public function versionGet($params){
        $apiPath = "Base.UserModule.User.App.getVersion";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(null,$res['status']);
        }
        return $this->endInvoke($res['response']);

   }



   /**
    * @api  CMS补丁添加
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.addPatch
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */

   public function addPatch($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.App.addPatch";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                return $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,5023);
        }
   }


   /**
    * @api  CMS补丁修改
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.editPatch
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */

   public function editPatch($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.User.App.editPatch";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                return $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,5024);
        }
   }



   /**
    * @api  CMS订单列表
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.patchLists
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */


   public function patchLists($params){
        $apiPath = "Base.UserModule.User.App.patchLists";
        $res = $this->invoke($apiPath,$params);
        if($res['stauts'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }

        //通过ids 查询版本
        $ids = array_column($res['response']['lists'],'id');
        if(!empty($ids)){
            $apiPath = "Base.UserModule.User.App.getVersions";
            $versions = $this->invoke($apiPath, array('patch_ids'=>$ids));
            if($versions['status'] != 0){
                return $this->endInvoke(NULL,$versions['status']);
            }


            // 数组重排
            $temp = array();
            foreach ($versions['response'] as $k => $v) {
                $temp[$v['patch_id']][] = $v;
            }

            foreach ($res['response']['lists'] as $k => &$v) {
                $v['versions'] = $temp[$v['id']];
            }
            unset($v);
        }


        return $this->endInvoke($res['response']);
   }


   /**
    * @api  CMS补丁获取
    * @apiVersion 1.0.1
    * @apiName Bll.Cms.Version.Version.getPatch
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-12-03
    * @apiSampleRequest On
    */

   public function getPatch($params){
        $apiPath = "Base.UserModule.User.App.getPatch";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }

        // 获取版本
        $map['patch_ids'][] = $res['response']['id'];
        $apiPath = "Base.UserModule.User.App.getVersions";
        $versions = $this->invoke($apiPath, $map);
        if($versions['status'] != 0){
            return $this->endInvoke(NULL,$versions['status']);
        }

        $res['response']['versions'] = $versions['response'];

        return $this->endInvoke($res['response']);
   }





}

?>
