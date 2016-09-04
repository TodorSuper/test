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

class Basic extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }

    /**
     * 添加用户基本信息
     * Base.UserModule.User.Basic.add
     * @param type $params
     * @return array
     */
    public function add($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('username', 'require', PARAMS_ERROR, MUST_CHECK),
            array('password', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('pre_bus_type', 'require', PARAMS_ERROR, MUST_CHECK), //用户类型  UC_USER_MERCHANT  UC_USER_MERCHANT 商户  ， UC_USER_MERMBER  会员
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //如果没有uc_code  则生成 uc_code
        if (empty($params['uc_code'])) {
            $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
            $data = array(
                'busType' => UC_USER,
                'preBusType' => $params['pre_bus_type'],
                'codeType' => SEQUENCE_USER,
            );
            $code_res = $this->invoke($apiPath, $data);
            $params['uc_code'] = $code_res['response'];
        }

        //如果没有传入密码  则  生成密码
        if (!$params['password']) {
            $params['password'] = 'f14b0f55897c2bfec813ec847af409cd';
        }
        if($params['pre_bus_type'] == UC_USER_MERCHANT ){
            $group_id =  MERCHANT_GROUP;
        }
        if($params['pre_bus_type'] == UC_USER_MERMBER){
            $group_id =  MERMBER_GROUP;
        }
        if($params['pre_bus_type'] == UC_USER_SUB_ACCOUNT){
            $group_id=  SUB_ACCOUNT_GROUP;
        }

        $data = array(
            'username' => $params['username'],
            'password' => $params['password'],
            'uc_code' => $params['uc_code'],
            'real_name' => $params['real_name'],
            'mobile' => $params['mobile'],
            'email' => $params['email'],
            'create_time' => empty($params['create_time']) ? NOW_TIME : $params['create_time'],
            'update_time' => NOW_TIME,
            'create_ip' => $params['create_ip'],
            'status' => 'ENABLE',
            'group_id' =>$group_id,
        );

        $user_res = D('UcUser')->add($data);
        if (!$user_res) {
            return $this->res(null, 4008);
        }

        $return_data = array(
            'username' => $params['username'],
            'uc_code' => $params['uc_code'],
            'mobile' => $params['mobile'],
            'real_name' => $params['real_name'],
        );
        return $this->res($return_data);
    }

    /**
     * 更新用户基本信息
     * Base.UserModule.User.Basic.update
     * @param type $params
     * @return integer
     */
    public function update($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('merchant_id', 'require', PARAMS_ERROR, ISSET_CHECK),
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        unset($params['uc_code']);
        $save_data = $params;
        $save_data['update_time'] = NOW_TIME;
        $update_res = D('UcUser')->where(array('uc_code' => $uc_code))->save($save_data);
        if ($update_res === FALSE || $update_res <= 0) {
            return $this->res(null, 4011);
        }


        // APP 修改手机 修改全部表
        if(isset($params['merchant_id']) && !empty($params['merchant_id'])){
            $data['phone'] = $params['mobile'];
            $data['update_time'] = NOW_TIME;
            $res = D('UcMerchant')->where(array('id'=>$params['merchant_id']))->save($data);

            if ($res === FALSE || $res <= 0) {
                return $this->res(null, 4011);
            }
        }

        return $this->res($update_res); //返回影响函数  
    }

    /**
     * 删除用户基本信息
     * Base.UserModule.User.Basic.delete
     * @param type $uc_code  用户编码
     * @return integer
     */
    public function delete($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $data = array(
            'status' => 'DISABLE',
            'update_time' => NOW_TIME,
        );
        $delete_res = D('UcUser')->where(array('uc_code' => $uc_code))->save($data);
        if ($delete_res <= 0 || FALSE === $delete_res) {
            return $this->res(null, 4015);
        }
        return $this->res($delete_res);
    }

    /**
     * 修改用户密码
     * Base.UserModule.User.Basic.changePwd
     * @param type $check_ori_pwd
     * @param type $ori_pwd
     * @param type $new_pwd
     * @param type $confirm_pwd
     * @param type $uc_code
     * @return array
     */
    public function changePwd($params) {

        $this->startOutsideTrans();
        $this->_rule = array(
            array('check_ori_pwd', array('YES', 'NO'), PARAMS_ERROR, HAVEING_CHECK, 'in'), //是否需要验证原密码
            array('ori_pwd', 'require', PARAMS_ERROR, HAVEING_CHECK), // 原密码
            array('confirm_pwd', 'require', PARAMS_ERROR, MUST_CHECK), //确认新密码  跟新密码是一样的
            array('new_pwd', 'require', PARAMS_ERROR, MUST_CHECK), // 原密码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        if (empty($params['check_ori_pwd'])) {
            //默认需要验证原始密码
            $params['check_ori_pwd'] = 'YES';
        }
        $check_ori_pwd = $params['check_ori_pwd'];
        $ori_pwd       = $params['ori_pwd'];
        $new_pwd       = $params['new_pwd'];
        $confirm_pwd   = $params['confirm_pwd'];
        $uc_code       = $params['uc_code'];

        $UcUserModel = D('UcUser');

        //验证原始密码
        if ($check_ori_pwd == 'YES') {
            $password = $UcUserModel->where(array('uc_code' => $uc_code))->field('password')->find();
            if (encrypt_password($ori_pwd) != $password['password']) {
                return $this->res(null, 4012);
            }
        }
        
        //新密码和密码确认是否一致
        if ($new_pwd != $confirm_pwd) {
            return $this->res(null, 4013); //两次密码输入不一致
        }

        $new_pwd = encrypt_password($new_pwd);
        $data = array(
            'password' => $new_pwd,
            'update_time' => NOW_TIME,
        );
        $changePwd_res = $UcUserModel->where(array('uc_code' => $uc_code))->save($data);
        if ($changePwd_res <= 0 || $changePwd_res === FALSE) {
            return $this->res(null, 4014);
        }

        return $this->res($changePwd_res); //影响行数
    }

    /**
     * 校验原密码
     * Base.UserModule.User.Basic.checkPwd
     * @access public
     * @author Todor
     */

    public function checkPwd($params){
        $this->_rule = array(
            array('ori_pwd', 'require', PARAMS_ERROR, MUST_CHECK), // 原密码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $password = D('UcUser')->where(array('uc_code' => $params['uc_code']))->field('password')->find();
        if (encrypt_password($params['ori_pwd']) != $password['password']) {
            return $this->res(null, 4012);
        }
        return $this->res(true);
    }


    /**
     * 获取用户基本信息
     * Base.UserModule.User.Basic.getBasicUserInfo
     * @param type $params
     * @return type
     */
    public function getBasicUserInfo($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), // 用户编码
            array('username', 'require', PARAMS_ERROR, HAVEING_CHECK), // 用户名
            array('status', array('ENABLE', 'DISABLE'), PARAMS_ERROR, HAVEING_CHECK, 'in'),
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $status = $params['status'];
        $uc_code = $params['uc_code'];
        $username = $params['username'];
        $mobile = $params['mobile'];
        if(empty($uc_code) && empty($username) && empty($mobile)){
            return $this->res(NULL,4033);
        } 
        !empty($status)   && $where['status'] = $status;
        !empty($uc_code)   && $where['uc_code'] = $uc_code;
        !empty($username)   && $where['username'] = $username;
        !empty($mobile)  && $where['mobile'] = $mobile;
        $user_info = D('UcUser')->where($where)->find();
        if (empty($user_info)) {
            return $this->res(null);
        }
        return $this->res($user_info);
    }


    /**
     * 获取用户扩展信息
     * Base.UserModule.User.Basic.getExtendUserInfo
     * @param type $params
     * @return type
     */
    public function getExtendUserInfo($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),   # 用户编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];

        $where['uc_code'] = $uc_code;
        $user_info = D('UcMember')->where($where)->find();
        return $this->res($user_info);
    }


}

?>
