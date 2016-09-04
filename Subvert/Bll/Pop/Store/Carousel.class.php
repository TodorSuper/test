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
defined( 'CAROUSEL_ENABLE' )      or  define('CAROUSEL_ENABLE', 'ENABLE');
defined( 'CAROUSEL_DISABLE' )      or  define('CAROUSEL_DISABLE', 'DISABLE');
class Carousel extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = 'Sc';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加轮播图
     * Bll.Pop.Store.Carousel.add
     * @param type $params
     */
    public function add($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.StoreModule.Basic.Carousel.add";
            $add_res = $this->invoke($apiPath, $params);
            if($add_res['status'] != 0){
                throw new \Exception($add_res['message'],$add_res['status']);
            }
            $res = D()->commit();
            if(FALSE === $res){
                throw new \Exception('事务提交失败');
            }
            return $this->res($add_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,5514);
        }
    }
     /**
     * 更新轮播图
     *  Bll.Pop.Store.Carousel.update
     * @param type $params
     */
    public function update($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.StoreModule.Basic.Carousel.update";
            $add_res = $this->invoke($apiPath, $params);
            if($add_res['status'] != 0){
                throw new \Exception($add_res['message'],$add_res['status']);
            }
            $res = D()->commit();
            if(FALSE === $res){
                throw new \Exception('事务提交失败');
            }
            return $this->res($add_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,5512);
        }
    }
    
    /**
     * 获取轮播图信息
     * Bll.Pop.Store.Carousel.get
     * @param type $params
     */
    public function get($params) {
        $apiPath = "Base.StoreModule.Basic.Carousel.get";
        $get_res = $this->invoke($apiPath, $params);
        return $this->res($get_res['response'],$get_res['status'],'',$get_res['message']);
    }

    /**
     * 获取轮播图列表信息
     * Bll.Pop.Store.Carousel.lists
     * @param type $params
     */
    public function lists($params) {
        $apiPath = "Base.StoreModule.Basic.Carousel.lists";
        $get_res = $this->invoke($apiPath, $params);
        return $this->res($get_res['response'],$get_res['status'],'',$get_res['message']);
    }
    
    /**
     * 删除轮播图
     * Bll.Pop.Store.Carousel.delete
     * @param type $params
     */
    public function delete($params){
        try{
            D()->startTrans();
            $data  = array(
                'sc_code' => $params['sc_code'],
                'car_id'  => $params['car_id'],
                'status'  => CAROUSEL_DISABLE,
            );
            $apiPath = "Base.StoreModule.Basic.Carousel.update";
            $add_res = $this->invoke($apiPath, $data);
            if($add_res['status'] != 0){
                throw new \Exception($add_res['message'],$add_res['status']);
            }
            $res = D()->commit();
            if(FALSE === $res){
                throw new \Exception('事务提交失败');
            }
            return $this->res($add_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,5512);
        }
    }

}

?>
