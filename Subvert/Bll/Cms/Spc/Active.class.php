<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | cms后台优惠券相关模块
 */

namespace Bll\Cms\Spc;

use System\Base;

class Active extends Base {

    public function __construct() {
        parent::__construct();
    }
    /**新增促销活动
     * yindongyang
     * Bll.Cms.Spc.Active.add
     * @param [type] $params [description]
     */
    public function add($params){
        try{
            D()->startTrans();
            $apiPath = 'Base.SpcModule.Coupon.Active.add';
            $res = $this->invoke($apiPath,$params);
            if($res['status']!==0){
                return $this->endInvoke('',$res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 7071);
            }
            return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7071);
        }
    }

    /**删除活动策略
     * yindongyang
     * Bll.Cms.Spc.Active.deletePolicy
     * @param [type] $params [description]
     */
    public function deletePolicy($params){
        $apiPath = 'Base.SpcModule.Coupon.Active.deletePolicy';
        $res = $this->invoke($apiPath,$params);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        return $this->endInvoke(true);
    }
    /**编辑促销活动
     * yindongyang
     * Bll.Cms.Spc.Active.update
     * @param [type] $params [description]
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath = 'Base.SpcModule.Coupon.Active.update';
            $res = $this->invoke($apiPath,$params);
            if($res['status']!==0){
                return $this->endInvoke('',$res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 7077);
            }
            return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7077);
        }
    }
    /**设置活动上下线
     * yindongyang
     * Bll.Cms.Spc.Active.setLine
     * @param [type] $params [description]
     */
    public function setLine($params){
        try{
         D()->startTrans();
        $apiPath = 'Base.SpcModule.Coupon.Active.setLine';
        $res = $this->invoke($apiPath,$params);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        $commit_res = D()->commit();
        if ($commit_res === FALSE) {
            return $this->endInvoke(NULL, 7098);
        }
            return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7098);
        }
    }

}