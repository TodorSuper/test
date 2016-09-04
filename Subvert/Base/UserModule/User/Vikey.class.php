<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: liaoxianwen <liaoxianwen@yunputong.com >
 * +---------------------------------------------------------------------
 * | 加密狗安全模块 Vikey
 */

namespace Base\UserModule\User;
use System\Base;

class Vikey extends Base {
    public function __consturct() {
        parent::__construct();
    }

    /**
     * @api {post} 列表
     * @apiVersion 1.0.0
     * @apiName Base.UserModule.User.Vikey.add
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     * @param $params
     * @permission public
     */
    public function lists($params) {
        //验证参数
        $rules = array(
            array('fields', 'require', PARAMS_ERROR, MUST_CHECK), // 查询的字段
            array('condition', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), // 查询的条件
        );
        if (!$this->checkInput($rules, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $result = D('vikey_binding')->field($params['fields'])->where($params['condition'])->select();
        return $this->res($result);
    }

    /**
     * @api {post} 绑定加密狗
     * @apiVersion 1.0.0
     * @apiName Base.UserModule.User.Vikey.add
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function add($params) {
        $rules = array(
            array('vikey_hid', 'require', PARAMS_ERROR, MUST_CHECK), // vikey硬件设备号
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编码
            array('security_str', 'require', PARAMS_ERROR, MUST_CHECK), // 加密字符串
            array('sub_vikey_str', 'require', PARAMS_ERROR, MUST_CHECK), // 截取的部分

        );
        if (!$this->checkInput($rules, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $vikeyData = [
            'vikey_hid' => $params['vikey_hid'],
            'uc_code' => $params['uc_code'],
            'security_str' => $params['security_str'],
            'sub_vikey_str' => $params['sub_vikey_str'],
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME
        ];
        $affect = D('vikey_binding')->add($vikeyData);
        return $this->res($affect);
    }

    /**
     * @api {post} 加密狗信息更新
     * @apiVersion 1.0.0
     * @apiName Base.UserModule.User.Vikey.add
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function update($params) {
        $rules = array(
            array('vikey_hid', 'require', PARAMS_ERROR, ISSET_CHECK), // vikey硬件设备号
            array('condition', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), // 查询条件
            array('security_str', 'require', PARAMS_ERROR, ISSET_CHECK), // 加密字符串
            array('sub_vikey_str', 'require', PARAMS_ERROR, ISSET_CHECK), // 截取的部分
        );
        if (!$this->checkInput($rules, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $vikeyData = [
            'vikey_hid' => $params['vikey_hid'],
            'security_str' => $params['security_str'],
            'sub_vikey_str' => $params['sub_vikey_str'],
            'update_time' => isset($params['update_time']) ? $params['security_str'] : NOW_TIME,
        ];
        $affect = D('vikey_binding')->where($params['condition'])->save($vikeyData);
        return $this->res($affect);
    }
    /**
     * @api {post} 加密狗单条信息检测
     * @apiVersion 1.0.0
     * @apiName Base.UserModule.User.Vikey.add
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function info($params) {

        $rules = array(
            array('condition', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), // 查询条件
        );
        if (!$this->checkInput($rules, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $vikey_info = D('vikey_binding')->where($params['condition'])->find();
        return $this->res($vikey_info);
    }

}