<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */

namespace Bll\Pop\Store;

use System\Base;

class Store extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

        /**
	 * 获取店铺信息
	 * Bll.Pop.Store.Store.get
	 * @param string sc_code
	 * @access public
	 * @return void
	 */
        public function get($params){
            $apiPath   = "Base.StoreModule.Basic.Store.get";
            $store_res = $this->invoke($apiPath, $params);
            if ($store_res['status'] != 0) {
                return $this->endInvoke(NULL, $store_res['status']);
            }
            $regionApi = 'Base.StoreModule.Basic.Store.getRegion';
            $region    = $this->invoke($regionApi);
            $store_res['response']['region'] = $region['response'];
            return $this->res($store_res['response'],$store_res['status'],'',$store_res['message']);
        }

        /**
         * Bll.Pop.Store.Store.getRegion
         * @param  [type] $params [description]
         * @return [type]         [description]
         */
        public function getRegion($params){
            $apiPath = 'Base.StoreModule.Basic.Store.getRegion';
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke(NULL, $res['status']);
            }
            $province = $params['province'];
            $city     = $params['city'];
            $area     = $params['area'];
            $region = $res['response']; 
            // var_dump($region);
            // var_dump($region['province']);
            $data = array();
            if ($region['province'][$province]) {
                $data['province'] = $region['province'][$province];
            }else{
                $data['province'] = '';
            }

            if ($region['city'][$province]) {
                $data['city'] = $region['city'][$province];
            }else{
                $data['city'] = '';
            }

            if ($region['area'][$city]) {
                $data['area'] = $region['area'][$city];
            }else{
                $data['area'] = '';
            }
            return $this->endInvoke($data);
        }

         /**
	 * 更新店铺信息
	 * Bll.Pop.Store.Store.update
	 * @param string sc_code
	 * @access public
	 * @return void
	 */
    public function update($params){
       try{
            D()->startTrans();
            // $params['city']     = C('DEFAULT_CITY');
            // $params['province'] = C('DEFAULT_PROVINCE');
            unset($params['data']['phone']);
            // $params['sign'] = 'POP';
            $apiPath            = "Base.StoreModule.Basic.Store.update"; 
            $update_res         = $this->invoke($apiPath,$params);
            if($update_res['status'] != 0){
                return $this->endInvoke(NULL, $update_res['status']);
            }
            $res = D()->commit();
            if($res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($update_res['response']);
       } catch (\Exception $ex) {
            D()->rollck();
            return $this->res(NULL,4031,'',$ex->getMessage());
       }
    }

    /**
     * 获取地区信息
     * Bll.Pop.Store.Store.getArea
     * @param string sc_code
     * @access public
     * @return void
     */
    public  function getArea($params) {
        //取出网关参数
        $params['gateway'] = C("BANK_PARAMS")['gateway'];
        $apiPath = "Base.PayCenter.Info.AccountInfo.GetAccountArea";
        $info  = $this->invoke($apiPath,$params);
        if($info['status'] != 0){
            return $this->endInvoke(NULL, $info['status']);
        }
        return $this->res($info['response'], $info['status'], $info['message']);




    }

}
?>
