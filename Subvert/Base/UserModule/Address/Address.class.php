<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户地址相关模块
 */

namespace Base\UserModule\Address;

use System\Base;

class Address extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加用户
     * Base.UserModule.Address.Address.add
     * @return integer   成功时返回  自增id
     */
    public function add($params) {
        $this->startOutsideTrans();
        //必须传递的参数
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('real_name', 'require', PARAMS_ERROR, MUST_CHECK),
            array('mobile', 'require', PARAMS_ERROR, MUST_CHECK),
            array('province', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('city', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('district', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('address', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('is_default', array('YES', "NO"), PARAMS_ERROR, ISSET_CHECK, 'in'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //地址数据
        $address_data = array(
            'uc_code' => $params['uc_code'],
            'real_name' => $params['real_name'],
            'mobile' => $params['mobile'],
            'province' => $params['province'],
            'city' => $params['city'],
            'district' => $params['district'],
            'address' => $params['address'],
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME,
            'status' => 'ENABLE',
        );

        //电话号码  有则添加
        if (isset($params['area_code'])) {
            $address_data['area_code'] = $params['area_code'];
        }
        if (isset($params['phone'])) {
            $address_data['phone'] = $params['phone'];
        }
        if (isset($params['extension'])) {
            $address_data['extension'] = $params['extension'];
        }
        //如果该用户没有地址  则第一条地址为默认地址
        $user_address = D('UcAddress')->where(array('uc_code'=>$params['uc_code'],'status'=>'ENABLE'))->find();
        if(empty($user_address)){
            $params['is_default'] = 'YES';
        }
        
        //是否是默认地址
        if (isset($params['is_default'])) {
            $address_data['is_default'] = $params['is_default'];
            //如果是默认地址  则要将之前的默认地址删除
            if ($params['is_default'] === 'YES') {
                $deleteDefault = $this->deleteDefaultAddress($params['uc_code']);
                if ($deleteDefault === FALSE) {
                    return $this->res(null, 4005);
                }
            }
        } else {
            $address_data['is_default'] = 'NO';
        }

        $addres_res = D('UcAddress')->add($address_data);
        if ($addres_res <= 0 || false === $addres_res) {
            //添加失败
            return $this->res(null, 4004);
        }

        return $this->res($addres_res);
    }

    /**
     * 修改用户信息
     * Base.UserModule.Address.Address.update
     * @param array $data   修改的字段
     * @param intger $address_id  地址自增id
     * @param intger $uc_code   用户编码
     * @return intger   影响行数
     */
    public function update($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('address_id', 'require', PARAMS_ERROR, MUST_CHECK),
            array('is_default', array('YES', "NO"), PARAMS_ERROR, ISSET_CHECK, 'in'),
            array('status', array('ENABLE', "DISABLE"), PARAMS_ERROR, ISSET_CHECK, 'in'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $fields = array('real_name','mobile','province','city','district','address','area_code','phone','extension','is_default','status');
        $data  = $this->create_save_data($fields, $params);
        //更新的数据
        $address_id = $params['address_id'];
        $uc_code = $params['uc_code'];

        $data['update_time'] = NOW_TIME;

        $update_res = D('UcAddress')->where(array('id'=>$address_id,'uc_code'=>$uc_code))->save($data);
        if($update_res === FALSE || $update_res <= 0){
            return $this->res(null,4006);
        }
        
        if (isset($data['is_default']) && $data['is_default'] == 'YES') {
            //如果是默认地址  则要将之前的默认地址删除
            $deleteDefault = $this->deleteDefaultAddress($params['uc_code'],$address_id);
            if ($deleteDefault === FALSE) {
                return $this->res(null, 4005);
            }
        }
        
        return $this->res($update_res);
    }

    /**
     * 删除信息
     * Base.UserModule.Address.Address.delete
     * @param intger $address_id  用户地址id 
     * @param intger $uc_code   用户编码
     * @return intger  影响行数
     */
    public function delete($params) {
        $params['status'] = 'DISABLE';
        $apiPath = "Base.UserModule.Address.Address.update";
        $delete_res = $this->invoke($apiPath, $params);
        return $this->res($delete_res['response'],$delete_res['status']);
    }
    
    /**
     * 获取用户地址详细信息
     * Base.UserModule.Address.Address.get
     * @param intger $address_id  用户地址id 
     * @param intger $uc_code   用户编码
     * @return intger  影响行数
     */
    public function get($params) {
        $this->_rule = array(
            array('address_id', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('is_default', array('YES','NO'), PARAMS_ERROR, HAVEING_CHECK,'in'), //是否默认
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('status', array('ENABLE','DISABLE'), PARAMS_ERROR, HAVEING_CHECK,'in'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $address_id = $params['address_id'];
        $uc_code = $params['uc_code'];
        $status = $params['status'];
        $status = empty($status) ? 'ENABLE' : $status;
        $is_default = $params['is_default'];
        $where = array(
            'uc_code'  =>$uc_code,
            'status'   =>$status,
        );
        !empty($address_id)   && $where['id'] = $address_id;
        !empty($is_default)   && $where['is_default'] = $is_default;
        $address_info = D('UcAddress')->where($where)->find();
//        if(empty($address_info)){
//            return $this->res(null,4007);
//        }
        return $this->res($address_info);
    }
    
    /**
     * 用户地址列表
     * Base.UserModule.Address.Address.lists
     * @param array $params
     */
    public function lists($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $where = array(
            'uc_code'=>$uc_code,
            'status'=>'ENABLE',
        );
        $params['center_flag'] = SQL_UC;
        $params['sql_flag'] = 'address_list';
        $params['where'] = empty($params['where']) ? $where : array_merge($params['where'],$where);
        $params['order'] = empty($params['order']) ? ' id desc ' : $params['order'];
        $params['page'] = empty($params['page']) ? 1 : $params['page'];
        $params['page_number'] = empty($params['page_number']) ? 100 : $params['page_number'];
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        
        $res = $this->invoke($apiPath, $params);
        if($res['status'] != 0){
            return $this->res(NULL,$res['status']);
        }
        return $this->res($res['response']);
    }
    
    /**
     * 将之前的默认收货地址改成非默认收货地址
     * @param type $uc_code  用户编码
     * @param type $exclude_address_id  排除的用户id
     * @return type
     */
    private function deleteDefaultAddress($uc_code, $exclude_address_id = 0) {
        $where = array(
            'uc_code' => $uc_code,
            'status' => 'ENABLE',
            'is_default' => 'YES',
        );
        if ($exclude_address_id) {
            $where['id'] = array('neq', $exclude_address_id);
        }
        $res = D('UcAddress')->where($where)->save(array('is_default' => 'NO'));
        return $res;
    }

}

?>
