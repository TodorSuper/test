<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户相关模块测试
 */

namespace Test\Base\UserUser;

use System\Base;

class User extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加用户
     * Test.Base.UserUser.User.add
     * @param array $data   用户数据
     * @param integer $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @return integer   成功时返回  自增id
     */
    public function add($params) {
        try {
            $apiPath = "Base.UserModule.User.User.add";
            $data = array(
                'uc_code' => '1120000000081',
                'username' => 'test002',
            );
            $params = array(
                'data' => $data,
                'userType' => UC_USER_MERCHANT,
            );
            $res = $this->invoke($apiPath, $params);
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Test.Base.UserUser.User.update
     * @param type $params
     * @return type
     */
    public function update($params) {
        try {
            $apiPath = "Base.UserModule.User.User.update";
            $data = array(
                'id' => '7',
                'uc_code' => '1120000000080',
                'username' => 'test001',
                'company' => '测试公司',
            );
            $params = array(
                'data' => $data,
                'userType' => UC_USER_MERCHANT,
                'ucCode' => '1120000000080',
            );
            $res = $this->invoke($apiPath, $params);
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Test.Base.UserUser.User.delete
     * @param type $params
     * @return type
     */
    public function delete($params) {
        $apiPath = "Base.UserModule.User.User.delete";
        $data = array(
            'status' => 'DISABLE',
        );
        $params = array(
            'data' => $data,
            'userType' => UC_USER_MERCHANT,
            'ucCode' => '1120000000080',
        );
        
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);
    }

}

?>
