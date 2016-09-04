<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 渠道管理
 */

namespace  Bll\Pop\Customer;

use System\Base;

class Channel extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Sc';
    }

    /**
     * 渠道列表
     * Bll.Pop.Customer.Channel.lists
     * @param type $params
     */
    public function lists($params) {
        $apiPath = "Base.UserModule.Customer.Channel.lists";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }

    /**
     * 添加渠道
     * Bll.Pop.Customer.Channel.add
     * @param type $params
     */
    public function add($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Channel.add";
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
            return $this->res(NULL,6703);
        }
    }

    /**
     * 更新渠道信息
     * Bll.Pop.Customer.Channel.update
     * @param type $params
     */
    public function update($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Channel.update";
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
            return $this->res(NULL,6704);
        }
    }

    /**
     * 删除渠道
     * Bll.Pop.Customer.Channel.delete
     * @param type $params
     */
    public function delete($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Channel.delete";
            $res  = $this->invoke($apiPath, $params);
            if($res['status'] != 0){
                return $this->endInvoke(NULL, $res['status']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,6704);
        }
    }

    /**
     * 生成二维码
     * Bll.Pop.Customer.Channel.qrcode
     * @param type $params
     */
    public function qrcode($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Channel.qrcode";
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
            return $this->res(NULL,6709);
        }
    }


    /**
     * 渠道模糊查询
     * Bll.Pop.Customer.Channel.search
     * @param type $params
     */

    public function search($params){
        $apiPath = "Base.UserModule.Customer.Channel.search";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }

}

?>
