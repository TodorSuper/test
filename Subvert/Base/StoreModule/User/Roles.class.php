<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangxuemei <wangxuemei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |pop商家子账户相关的操作
 */
namespace Base\StoreModule\User;
use System\Base;
class Roles extends Base
{
    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 添加角色
     * Base.StoreModule.User.Roles.add
     */
    public function add($params){
        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('privs_id', 'require', PARAMS_ERROR, MUST_CHECK), //权限id
            array('uc_code','require',PARAMS_ERROR, MUST_CHECK)//当前登录用户编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $api = "Base.StoreModule.User.SubAccount.getUserRoles";
        $info = $this->invoke($api,['uc_code'=>$params['uc_code']]);

        if($info['status'] !== 0){
            return $this->res('非法用户',5518); # 安全码生成失败
        }

        $data['name']       = $params['name'];
        $data['privs_id']   = $params['privs_id'];
        $data['uc_code']    = $params['uc_code'];
        $data['status']     = 1;
        $data['create_time']    = NOW_TIME;
        $data['sc_code']        = $info['response']['sc_code'];
        $data['main_uc_code']   = $info['response']['main_uc_code'];

        $res = D('ScRoles')->data($data)->add();
        if(!$res){
            return $this->res('添加角色失败',5520);

        }
        return $this->res($res);

    }

    /**
     *查询角色列表
     * Base.StoreModule.User.Roles.lists
     */
    public function lists($params)
    {
        $this->_rule = array(
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //状态
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //当前用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;

        $api = "Base.StoreModule.User.SubAccount.findOne";
        $sba = $this->invoke($api,['uc_code'=>$params['uc_code']]);
        if($sba['status'] != 0){
            return $this->res('',5525);
        }

        $field[] = 'id,name,privs_id,main_uc_code,sc_code,status,create_time,update_time';
        $where['status'] = $params['status'];
        $where['sc_code'] = $sba['response']['sc_code'];
        $where['main_uc_code'] = $sba['response']['main_uc_code'];
        $lists = D('ScRoles')->where($where)->field($field)->order('id desc')->page($page,$pageNumber)->select();
        $roles_num = D('ScSubAccount')->field('count(roles_id) as roles_num ,roles_id')->where(['sc_code'=>$sba['response']['sc_code']])->group('roles_id')->select();//查询商户对应角色数量

        $total_num =  D('ScRoles')->where($where)->count();
        $res = array(
            'total_num'=> $total_num,
            'roles_num'=> $roles_num,
            'lists' => $lists,
            'page' => $page,
            'page_number' => $pageNumber,
        );
        return $this->res($res);
    }

    /**
     *查询单个角色信息
     * Base.StoreModule.User.Roles.findRoles
     */

    public function findRoles ($params) {
        $this->_rule = array(
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $field = 'roles.id,roles.name';
        $where['uc_code'] = $params['uc_code'];
        $response = D('ScSubAccount')->field($field)->alias('account')
            ->join("{$this->tablePrefix}sc_roles roles ON account.roles_id=roles.id",'LEFT')
            ->where($where)
            ->find();

        return $this->res($response);
    }



}