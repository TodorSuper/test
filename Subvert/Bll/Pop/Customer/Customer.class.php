<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 客户管理
 */

namespace Bll\Pop\Customer;
use System\Base;

class Customer extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    /**
     * 会员列表 zhangyupeng(改)
     * Bll.Pop.Customer.Customer.lists
     * @param type $params
     */
    public function lists($params){
        $apiPath  =  "Base.UserModule.Customer.Customer.lists";
        $list_res = $this->invoke($apiPath, $params);
        if($list_res['status'] != 0){
            return $this->endInvoke(NULL,$list_res['status']);
        }
        $customers = $list_res['response']['lists'];
        
        $data = $this->getChannelSalesman($params['sc_code']);
        $list_res['response']['salesman_list'] = $data['salesman'];
        $list_res['response']['channel_list']  = $data['channel'];
        return $this->res($list_res['response']);
    }
    
    /**
     * 获取客户信息
     * Bll.Pop.Customer.Customer.get
     * @param type $params
     */
     public function get($params){
        $apiPath  =  "Base.UserModule.Customer.Customer.get";
        $list_res = $this->invoke($apiPath, $params);
        if($list_res['status'] != 0){
            return $this->endInvoke(NULL,$list_res['status']);
        }
        // var_dump($list_res['response']);
        // if ($list_res['response']['invite_from'] === 'UC') {
            $regionApi = 'Base.UserModule.Customer.Customer.getOrderRegion';
            $regionData = array(
            'uc_code' => $params['uc_code'],
            'sc_code' => $params['sc_code'],
            );
            $res = $this->invoke($regionApi, $regionData);
            if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
            }
            if (!empty($res['response'])) {
                $list_res['response']['province'] = $res['response']['province'];
                $list_res['response']['city']     = $res['response']['city'];
                $list_res['response']['district'] = $res['response']['district'];
                $list_res['response']['address']  = $res['response']['address'];
            }else{
                $list_res['response']['province'] = '';
                $list_res['response']['city']     = '';
                $list_res['response']['district'] = '';
                $list_res['response']['address']  = '';
            }
        // }
        // var_dump($res);
        $data = $this->getChannelSalesman($params['sc_code']);
        $temp['customer_res']  = $list_res['response'];
        $temp['salesman_list'] = $data['salesman'];
        $temp['channel_list']  = $data['channel'];
        return $this->endInvoke($temp);
    }
    
    /**
     * 添加会员
     * Bll.Pop.Customer.Customer.add
     * @param type $params
     */
    public function add($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Customer.add";
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
            return $this->res(NULL,6710);
        }
    }
    
     
    /**
     * 更新客户信息
     * Bll.Pop.Customer.Customer.update
     * @param type $params
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Customer.Customer.update";
            $res  = $this->invoke($apiPath, $params);
            if($res['status'] != 0){
                $this->endInvoke(NULL,$res['status']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,6713);
        }
    }

    /**
     * 客户模糊查询
     * Bll.Pop.Customer.Customer.search
     * @param type $params
     */

    public function search($params){
        $apiPath = "Base.UserModule.Customer.Customer.search";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }


    /**
     * 客户表格导出
     * Bll.Pop.Customer.Customer.export
     * @param type $params
     */
   public function export($params){
        $apiPath  = "Base.UserModule.Customer.Customer.export";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'],$res['status'],'',$res['message']);
    }
    
    /**
     * 获取全部业务员与渠道
     * @param type $sc_code
     * @return array
     */
    private function getChannelSalesman($sc_code){
         //查询所有的渠道
        $apiPath = "Base.UserModule.Customer.Channel.lists";
        $data = array(
            'sc_code' => $sc_code,
            'page_number' => 1000,
            'status'  => 'ENABLE',
        );
        $channel_res = $this->invoke($apiPath, $data);
        if ($channel_res['status'] != 0) {
            return $this->endInvoke(NULL, $channel_res['status']);
        }
        $channel_list = $channel_res['response']['lists'];

        //查询所有的业务员
        $apiPath = "Base.UserModule.Customer.Salesman.lists";
        $data = array(
            'sc_code' => $sc_code,
            'page_number' => 1000,
            'status'  => 'ENABLE',
        );
        $salesman_res = $this->invoke($apiPath, $data);
        if ($salesman_res['status'] != 0) {
            return $this->endInvoke(NULL, $salesman_res['status']);
        }
        $salesman_list = $salesman_res['response']['lists'];
        $data = array(
            'salesman'=>$salesman_list,
            'channel' =>$channel_list, 
            );
        return $data;
    }


    
}

?>
