<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@yunputong.com >
 * +---------------------------------------------------------------------
 * | cms后台优惠券相关
 */

namespace Bll\Cms\Spc;

use System\Base;

class Coupon extends Base {

    public function __construct() {
        parent::__construct();
    }
    /**新增优惠券
     * yindongyang
     * Bll.Cms.Spc.Coupon.add
     * @param [type] $params [description]
     */
    public function add($params){
        try{
            D()->startTrans();
            $apiPath = 'Base.SpcModule.Coupon.Coupon.add';
            $res = $this->invoke($apiPath,$params);
            if($res['status']!==0){
                return $this->endInvoke('',$res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 7074);
            }
            return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7074);
        }
    }

    /**编辑优惠券
     * yindongyang
     * Bll.Cms.Spc.Coupon.update
     * @param [type] $params [description]
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath = 'Base.SpcModule.Coupon.Coupon.update';
            $res = $this->invoke($apiPath,$params);
            if($res['status']!==0){
                return $this->endInvoke('',$res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 7085);
            }
            return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7085);
        }
    }
}