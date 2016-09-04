<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 业务员管理
 */

namespace Bll\Pop\Customer;
use System\Base;

class Salesman extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Sc';
    }
    
    /**
     *业务员列表
     * Bll.Pop.Customer.Salesman.lists
     * @param type $params
     */
    public function lists($params){
        $apiPath = "Base.UserModule.Customer.Salesman.lists";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }
    
    /**
     * Bll.Pop.Customer.Salesman.getQrcode
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getQrcode($params){
        $apiPath = 'Base.UserModule.Customer.Salesman.getQrcode';
        $res = $this->invoke($apiPath, $params);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return $this->endInvoke($res['response']);
    }


    /**
     * 添加业务员
     * Bll.Pop.Customer.Salesman.add
     * @param type $params
     */
    public function add($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Salesman.add";
            $res  = $this->invoke($apiPath, $params);
            if($res['status'] != 0){
                throw new \Exception($res['message'],$res['status']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,6701);
        }
    }
    
    /**
     * 更新业务员信息
     * Bll.Pop.Customer.Salesman.update
     * @param type $params
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Salesman.update";
            $res  = $this->invoke($apiPath, $params);
            if($res['status'] != 0){
                throw new \Exception($res['message'],$res['status']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,6702);
        }
    }
    
    /**
     * 禁用业务员
     * Bll.Pop.Customer.Salesman.delete
     * @param type $params
     */
    public function delete($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Salesman.delete";
            $res  = $this->invoke($apiPath, $params);
            if($res['status'] != 0){
                return $this->endInvoke(NULL,$res['status']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,6702);
        }
    }
    
    /**
     * 启用业务员
     * Bll.Pop.Customer.Salesman.start
     * @param type $params
     */
    public function start($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Salesman.start";
            $res  = $this->invoke($apiPath, $params);
            if($res['status'] != 0){
                return $this->endInvoke(NULL,$res['status']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,6702);
        }
    }

    /**
     * 业务员模糊查询
     * Bll.Pop.Customer.Salesman.search
     * @param type $params
     */

    public function search($params){
        $apiPath = "Base.UserModule.Customer.Salesman.search";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }
    
}

?>
