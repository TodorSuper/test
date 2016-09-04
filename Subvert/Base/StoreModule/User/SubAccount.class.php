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

class SubAccount extends Base
{
    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 添加注册用户
     *  Base.StoreModule.User.SubAccount.add
     */
    public function add($params)
    {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商户编码
            array('main_uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家主账户编码
            array('roles_id', 'require', PARAMS_ERROR, MUST_CHECK), //角色id
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //判断是否是商家主账户  如果不是验证账户操作身份
        $data['create_time'] = NOW_TIME;
        $data['sc_code'] = $params['sc_code'];
        $data['roles_id'] = $params['roles_id'];
        $data['main_uc_code'] = $params['main_uc_code'];
        $data['uc_code'] = $params['uc_code'];
        $res = D('ScSubAccount')->data($data)->add();
        if ($res) {
            return $this->res($res);
        } else {
            return $this->res('',5501);

        }

    }

    /**
     * 获取用户身份信息
     *Base.StoreModule.User.subAccount.getUserRoles
     */
    public function getUserRoles($params)
    {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码

        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $roles = D('ScSubAccount')->where(['uc_code' => $params['uc_code']])->find();

        if(!$roles){
            return $this->res("没有此角色",5518);
        }

        return $this->res($roles);

    }

    /**
     * 给用户设定角色
     *  Base.StoreModule.User.SubAccount.update
     */
    public function update($params)
    {

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('login_uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //登录用户编码
            array('roles_id', 'require', PARAMS_ERROR, MUST_CHECK), //商户编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['uc_code'] = ['in',[$params['uc_code'],$params['login_uc_code']]];
        $info = D('ScSubAccount')->field('main_uc_code')->where($where)->select();
        if($info[0]['main_uc_code']!= $info[1]['main_uc_code']){
            return $this->res('',5524);
        }
        $data['roles_id'] = $params['roles_id'];
        $where['uc_code'] = $params['uc_code'];
        $res = D('ScSubAccount')->where($where)->data($data)->setField($data);
        return $this->res($res);

    }

    /**
     *查询用户列表
     * Base.StoreModule.User.SubAccount.lists
     */
    public function lists($params)
    {

        $this->_rule = array(
            array('roles_id', 'require', PARAMS_ERROR, ISSET_CHECK), //角色id
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK), //状态
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK), //手机号
            array('username', 'require', PARAMS_ERROR, ISSET_CHECK), //用户名
            array('real_name', 'require', PARAMS_ERROR, ISSET_CHECK), //真实姓名
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK), //当前页码
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK), //每页数据条数
            array('uc_code','require', PARAMS_ERROR, ISSET_CHECK)
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());

        }

        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $data = [];
        if ($params['roles_id']) $data['sa.roles_id'] = $params['roles_id'];

        if ($params['status']) $data['us.status'] = $params['status'];

        if ($params['mobile']) $data['us.mobile'] = $params['mobile'];

        if ($params['username']) $data['us.username'] = $params['username'];

        if ($params['real_name']) $data['us.real_name'] = $params['real_name'];

        $api = "Base.StoreModule.User.SubAccount.findOne";
        $sba = $this->invoke($api,['uc_code'=>$params['uc_code']]);
        if($sba['status'] != 0){
            return $this->res('',5525);
        }
        $data['sa.sc_code'] = $sba['response']['sc_code'];
        $data['sa.main_uc_code'] = $sba ['response']['main_uc_code'];
        $fileConfirm = 'sa.uc_code,sa.id,sa.main_uc_code,us.real_name,us.username,us.mobile,sa.update_time,us.status,sr.name,sa.roles_id,us.create_time,us.login_time,ss.linkman';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $info['center_flag'] = SQL_SC;
        $info['sql_flag'] = 'sub_account_list';
        $info['where'] = $data;
        $info['fields'] = $fileConfirm;
        $info['page'] = $page;
        $info['order'] = "sa.id desc";
        $info['page_number'] = $pageNumber;
        $res = $this->invoke($apiPath, $info);

        return $this->res($res['response']);

    }

    /**
     * 查询指定用户
     * Base.StoreModule.User.SubAccount.findOne
     */
    public function findOne($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码

        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());

        }

        $sba = D('ScSubAccount')->field('sub.uc_code,sub.sc_code,sub.main_uc_code,sub.roles_id,ss.name')->alias('sub')
            ->join("{$this->tablePrefix}sc_store ss ON sub.main_uc_code=ss.uc_code",'LEFT')
            ->where(['sub.uc_code'=>$params['uc_code']])
            ->find();

        return  $this->res($sba);
    }




}