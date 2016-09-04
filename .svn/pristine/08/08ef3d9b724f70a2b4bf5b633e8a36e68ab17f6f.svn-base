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

namespace Base\UserModule\User;

use System\Base;

class App extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }


    /**
     * @api  Boss查看用户设置
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.getVersion
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function getVersion($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('device', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('id', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        // 如果为空则获取 装置类型
        if(empty($params['device']) && empty($params['id']) ){
            $apiPath = "Base.UserModule.User.User.getDevice";
            $device  = $this->invoke($apiPath,$params);
            if($device['status'] != 0){
                return $this->res(NULL,$device['status'],'',$device['message']);
            }
            $params['device'] = $device['response']['device'];
        }

        !empty($params['device']) && $where['device'] = $params['device'];
        !empty($params['id'])     && $where['id']     = $params['id'];
        $where['status'] = "ENABLE";

        $version = D('AppVersion')->where($where)->order('id desc')->find();
        if($version == FALSE){
            return $this->res(NULL,4044);
        }
        return $this->res($version);
    }


    /**
     * @api  CMS获取版本列表
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.lists
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */


    public function lists($params){

        $this->_rule = array(
            array('version', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('update_type', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('version_order','require', PARAMS_ERROR, ISSET_CHECK),
            array('is_page',array('YES','NO'),PARAMS_ERROR, ISSET_CHECK,'in'),
            array('device','require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // where 条件拼接
        $where['status'] = "ENABLE";
        !empty($params['version']) && $where['version'] = array('like',"%".$params['version']."%");
        !empty($params['type']) && $where['type'] = $params['type'];
        !empty($params['device']) && $where['device'] = $params['device'];
        !empty($params['update_type']) && $where['update_type'] = $params['update_type'];
        !empty($params['start_time']) && empty($params['end_time']) && $where['create_time'] = array('gt',$params['start_time']);
        !empty($params['end_time']) && empty($params['start_time']) && $where['create_time'] = array('lt',$params['end_time']);
        !empty($params['end_time']) && !empty($params['start_time']) && $where['create_time'] = array('between',array($params['start_time'],$params['end_time']));
        $order = empty($params['version_order']) ? 'id desc' : $version_order;

        if($params['is_page'] == "NO"){
            $res = D('AppVersion')->where($where)->order($order)->select();
            return $this->res($res);
        }


        $fields = "id,version,update_type,down_url,show_img,device,content,show_update,clear_cache,status,create_time,type";
        $params['fields'] = $fields;
        $params['order'] = $order;
        $params['where'] = $where;
        $params['center_flag'] = SQL_APP;
        $params['sql_flag'] = 'app_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);   

        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        return $this->res($list_res['response']);

    }


    /**
     * @api  CMS添加版本信息
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.add
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('version', 'require', PARAMS_ERROR, MUST_CHECK),     # 版本号
            array('down_url', 'require', PARAMS_ERROR, MUST_CHECK),    # 下载地址
            array('update_type', 'require', PARAMS_ERROR, MUST_CHECK), # 更新类型
            array('device', 'require', PARAMS_ERROR, MUST_CHECK),      # 设备类型
            array('show_update','require', PARAMS_ERROR, MUST_CHECK),  # 是否展示更新
            array('clear_cache','require', PARAMS_ERROR, MUST_CHECK),  # 是否清除缓存
            array('content','require', PARAMS_ERROR, MUST_CHECK),      # 更新内容
            array('type','require', PARAMS_ERROR, MUST_CHECK),         # APP类型
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $data = array(
            'version'=>$params['version'],
            'down_url'=>$params['down_url'],
            'update_type'=>$params['update_type'],
            'device'=>$params['device'],
            'show_update'=>$params['show_update'],
            'clear_cache'=>$params['clear_cache'],
            'content'=>$params['content'],
            'type'=>$params['type'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>'ENABLE',
            );
        $data = D('AppVersion')->add($data);
        if($data = FALSE){
            return $this->res(NULL,5021);
        }
        return $this->res($data);
    }


    /**
     * @api  CMS编辑版本信息
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.edit
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function edit($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('id', 'require', PARAMS_ERROR, MUST_CHECK),
            array('version', 'require', PARAMS_ERROR, ISSET_CHECK),     # 版本号
            array('down_url', 'require', PARAMS_ERROR, ISSET_CHECK),    # 下载地址
            array('update_type', 'require', PARAMS_ERROR, ISSET_CHECK), # 更新类型
            array('device', 'require', PARAMS_ERROR, ISSET_CHECK),      # 设备类型
            array('show_update','require', PARAMS_ERROR, ISSET_CHECK),  # 是否展示更新
            array('clear_cache','require', PARAMS_ERROR, ISSET_CHECK),  # 是否清除缓存
            array('content','require', PARAMS_ERROR, ISSET_CHECK),      # 更新内容
            array('type','require', PARAMS_ERROR, ISSET_CHECK),         # APP类型
            array('status','require', PARAMS_ERROR, ISSET_CHECK),       # 状态   
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        !empty($params['version']) && $data['version']         = $params['version'];
        !empty($params['down_url']) && $data['down_url']       = $params['down_url'];
        !empty($params['update_type']) && $data['update_type'] = $params['update_type'];
        !empty($params['device']) && $data['device']           = $params['device'];
        !empty($params['show_update']) && $data['show_update'] = $params['show_update'];
        !empty($params['clear_cache']) && $data['clear_cache'] = $params['clear_cache'];
        !empty($params['content']) && $data['content']         = $params['content'];
        !empty($params['type']) && $data['type']               = $params['type'];
        !empty($params['status']) && $data['status']           = $params['status'];
        $data['update_time'] = NOW_TIME;

        $res = D('AppVersion')->where(array('id'=>$params['id']))->save($data);
        if($res == FALSE){
            return $this->res(NULL,5022);
        }
        return $this->res($res);
    }


    /**
     * @api  CMS补丁添加
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.addPatch
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function addPatch($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('patch_version', 'require', PARAMS_ERROR, MUST_CHECK),                 # 补丁版本 
            array('patch_url', 'require', PARAMS_ERROR, MUST_CHECK),                     # 补丁下载地址
            array('content', 'require', PARAMS_ERROR, MUST_CHECK),                       # 修补内容
            array('device', array(IOS,ANDROID), PARAMS_ERROR, MUST_CHECK,'in'),          # 设备类型
            array('type', array(BOSS,'SHOP'),PARAMS_ERROR, MUST_CHECK , 'in'),           # 所属类型
            array('version_ids','checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),# 需要补丁的版本号ID
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = array(
            'patch_version' =>$params['patch_version'],
            'patch_url'     =>$params['patch_url'],
            'content'       =>$params['content'],
            'device'        =>$params['device'],
            'type'          =>$params['type'],
            'update_time'   =>NOW_TIME,
            'create_time'   =>NOW_TIME,
            );

        $res = D('AppPatch')->add($data);
        if($res == FALSE){
            return $this->res(NULL,5023);
        }

        // 添加版本关联表
        $this->_relation($params['version_ids'],$res);
            
        return $this->res(true);
    }


    /**
     * @api  CMS补丁修改
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.editPatch
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function editPatch($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('id', 'require', PARAMS_ERROR, MUST_CHECK),                             # 补丁ID
            array('patch_version', 'require', PARAMS_ERROR, ISSET_CHECK),                 # 补丁版本 
            array('patch_url', 'require', PARAMS_ERROR, ISSET_CHECK),                     # 补丁下载地址
            array('content', 'require', PARAMS_ERROR, ISSET_CHECK),                       # 修补内容
            array('device', array(IOS,ANDROID), PARAMS_ERROR, ISSET_CHECK,'in'),          # 设备类型
            array('type', array(BOSS),PARAMS_ERROR, ISSET_CHECK , 'in'),                   # 所属类型
            array('version_ids','checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),# 需要补丁的版本号
            array('status','require', PARAMS_ERROR, ISSET_CHECK),                         # 状态   
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 查看对应补丁版本号
        $check['patch_id'] = $params['id'];
        $check['status'] = 'ENABLE';
        $res = D('VersionPatchRelation')->where($check)->select();
        if(empty($res)){
            return $this->res(NULL,5024);
        }
        $version_ids = array_column($res,'version_id');

        // 补丁版本号不同则更新中间表
        if(!empty(array_diff($params['version_ids'],$version_ids)) || !empty(array_diff($version_ids,$params['version_ids'])) || empty($params['version_ids'])){
            $map['patch_id'] = $params['id'];
            $temp['status'] = 'DISABLE';
            $relation_res = D('VersionPatchRelation')->where($map)->save($temp);
            if($relation_res == FALSE){
                return $this->res(NULL,5026);
            }

            // 添加版本关联表
            empty($params['status']) && $this->_relation($params['version_ids'],$params['id']);
        }
        

        !empty($params['patch_version']) && $data['patch_version'] = $params['patch_version'];
        !empty($params['patch_url']) && $data['patch_url'] = $params['patch_url'];
        !empty($params['content']) && $data['content'] = $params['content'];
        !empty($params['device']) && $data['device'] = $params['device'];
        !empty($params['type']) && $data['type'] = $params['type'];
        !empty($params['status']) && $data['status'] = $params['status'];
        $data['update_time'] = NOW_TIME;
        $where['id'] = $params['id'];
        $patch_res = D('AppPatch')->where($where)->save($data);
        if($patch_res == FALSE){
            return $this->res(NULL,5024);
        }

        return $this->res(true);
    }



    /**
     * @api  增加版本补丁中间表
     * @apiVersion 1.0.1
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    private function _relation($version_ids,$patch_id){
        foreach ($version_ids as $k => $v) {
            $relation = array(
                'patch_id'=>$patch_id,
                'version_id'=>$v,
                'update_time'=>NOW_TIME,
                'create_time'=>NOW_TIME,
                'status'=>'ENABLE',
                );
            $relation_res = D('VersionPatchRelation')->add($relation);
            if($relation_res == FALSE){
                return $this->res(NULL,5025);
            }
        }

    }



    /**
     * @api  CMS补丁列表
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.patchLists
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function patchLists($params){

        $this->_rule = array(
            array('patch_version', 'require', PARAMS_ERROR, ISSET_CHECK),                # 补丁版本 
            array('content', 'require', PARAMS_ERROR, ISSET_CHECK),                      # 补丁内容
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),                   # 搜索开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),                     # 搜索结束时间
            array('version_id','require', PARAMS_ERROR, ISSET_CHECK),                    # 版本号
            array('type', array(BOSS,'SHOP'),PARAMS_ERROR, ISSET_CHECK , 'in'),          # 所属类型
            array('order','require', PARAMS_ERROR, ISSET_CHECK),                         # 排序
            array('device','require', PARAMS_ERROR, ISSET_CHECK),                        # 设备类型
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }


        if(!empty($params['version_id'])){
            $map['version_id'] = $params['version_id'];
            $map['status'] = 'ENABLE';
            $patch_ids = D('VersionPatchRelation')->where($map)->select();
            $patch_ids = array_column($patch_ids,'patch_id');
            if(empty($patch_ids)){
                return $this->res(true);
            }else{
                $where['id'] = array('in',$patch_ids);
            }
        }

        // where 条件组装
        $where['status'] = 'ENABLE';
        !empty($params['patch_version']) && $where['patch_version'] = $params['patch_version'];
        !empty($params['type']) && $where['type'] = $params['type'];
        !empty($params['device']) && $where['device'] = $params['device'];
        !empty($params['content'])       && $where['content'] = "%".$params['content']."%";
        !empty($params['start_time']) && empty($params['end_time']) && $where['create_time'] = array('egt',$params['start_time']);
        !empty($params['end_time']) && empty($params['start_time']) && $where['create_time'] = array('elt',$params['end_time']);
        !empty($params['end_time']) && !empty($params['start_time']) && $where['create_time'] = array('between',array($params['start_time'],$params['end_time']));
        $order = empty($params['order']) ? 'id desc' : $params['order'];

        $fields = "id,patch_version,content,create_time,patch_url,type,device";
        $params['fields'] = $fields;
        $params['order'] = $order;
        $params['where'] = $where;
        $params['center_flag'] = SQL_APP;
        $params['sql_flag'] = 'patch_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);   

        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }

        return $this->res($list_res['response']);
    }


    /**
     * @api  CMS根据补丁ID获取版本ID
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.getVersions
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function getVersions($params){
        $this->_rule = array(
            array('patch_ids','checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), # 补丁号ID
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['vpr.patch_id'] = array('in',$params['patch_ids']);
        $where['vpr.status']   = 'ENABLE';
        $where['av.status']    = 'ENABLE';
        $res = D('VersionPatchRelation')->alias('vpr')
                                        ->join("{$this->tablePrefix}app_version av ON av.id = vpr.version_id",'LEFT')
                                        ->where($where)
                                        ->select();
        return $this->res($res);
    }


    /**
     * @api  CMS获取单条补丁
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.getPatch
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */

    public function getPatch($params){
        $this->_rule = array(
            array('id','require', PARAMS_ERROR, MUST_CHECK), # 补丁号ID
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['id'] = $params['id'];
        $where['status'] = 'ENABLE';
        $res = D('AppPatch')->where($where)->find();
        if($res == FALSE){
            return $this->res(NULL,5027);
        }
        return $this->res($res);
    }



    /**
     * @api  APP 获取版本信息 与获取补丁信息
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.App.getPatchVersion
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-12-3
     * @apiSampleRequest On
     */


    public function getPatchVersion($params){
        $this->_rule = array(
            array('device', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('version', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('patch_id', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取最新版本
        $where['status'] = "ENABLE";
        $where['device'] = $params['device'];
        $version = D('AppVersion')->field('version,down_url,update_type,show_img as img,clear_cache,content')->where($where)->order('id desc')->find();
        $version['need_update'] = ($params['version'] != $version['version'] ) ? 'YES' : 'NO';

        //获取对应版本ID
        $where['version'] = $params['version'];
        $version_id = D('AppVersion')->where($where)->getField('id');

        //获取对应版本 最新补丁
        $map = array(
            'vpr.version_id'=>$version_id,
            'vpr.status'    =>"ENABLE",
            'ap.status'     =>"ENABLE",
            'ap.device'    =>$params['device'],
            );
        $fields = "ap.id,ap.patch_version,ap.patch_url,ap.content";
        $patch = D('VersionPatchRelation')->alias('vpr')
                                          ->field($fields)
                                          ->join("{$this->tablePrefix}app_patch AS ap ON ap.id = vpr.patch_id")
                                          ->where($map)
                                          ->order('ap.id desc')
                                          ->find();
        if(empty($patch)){
            $version['patch_need_update'] = 'NO';
            $version['patch_version']     = "";
            $version['patch_url']         = "";
            $version['patch_content']     = '';
        }else{
            $version['patch_need_update'] = ($params['patch_id'] == $patch['patch_version']) ? 'NO' : 'YES';
            $version['patch_version']     = $patch['patch_version'];
            $version['patch_url']         = $patch['patch_url'];
            $version['patch_content']     = $patch['content'];
        }

        // 如果图片为空转化为数组
        if(empty($version['img']) || !is_array($version['img'])){
            $version['img'] = array();
        }

        return $this->res($version);
    }

}

?>
