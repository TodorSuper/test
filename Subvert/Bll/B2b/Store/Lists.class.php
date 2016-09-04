<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 前台 商品列表
 */

namespace Bll\B2b\Store;

use System\Base;

class Lists extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }


    /**
     * 店铺列表
     * Bll.B2b.Store.Lists.lists
     * @author Todor
     */

    public function lists($params){

        $params['is_show'] = 'YES';
        $params['is_page'] = 'YES';
        // 获取店铺列表
        $apiPath = "Base.StoreModule.Basic.Store.lists";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0 ){
            return $this->endInvoke(NULL,$res['status']);
        }

        $sc_codes = array_column($res['response']['lists'],'sc_code');

        if(empty($sc_codes)){
            return $this->endInvoke($res['response']);
        }

        // 获取所有展示商家的商品的分类类型

        $class_params=array(
            'invite_code'=>$params['invite_code'],
            );
        $apiPath = "Base.ItemModule.Classify.ClassifyInfo.getClasses";
        $class_res = $this->invoke($apiPath,$class_params);
        if($class_res['status'] != 0){
            return $this->endInvoke(NULL,$class_res['status']);
        }

        //获取主营标签
        $apiPath = "Base.StoreModule.Basic.Label.getLabels";
        $data['sc_codes'] = $sc_codes;
        $label_res = $this->invoke($apiPath,$data);
        if($label_res['status'] != 0){
            return $this->endInvoke(NULL,$label_res['status']);
        }

        foreach ($label_res['response'] as $k => $v) {
            $labels[$v['sc_code']][] = $v['label_name'];
        }

        // 数据重组
        foreach ($res['response']['lists'] as $k => &$v) {
            $v['labels'] = $labels[$v['sc_code']];
        }

        $cart_params['uc_code'] = $params['uc_code'];
        $cart_params['is_show'] = "YES";
        $apiPath = "Base.UserModule.Cart.Cart.getCartNum";
        $cart_res = $this->invoke($apiPath,$cart_params);
        if($cart_res['status'] != 0){
            return $this->endInvoke(NULL,$cart_res['status']);
        }

        //获取可展示活动
        $apiPath = "Base.SpcModule.Coupon.Center.getRecentActives";
        $active_res = $this->invoke($apiPath,array());

        $res['response']['classes'] = $class_res['response'];                # 分类
        $res['response']['total_sum'] = $cart_res['response']['total_sum'];  # 购物车数量
        $res['response']['active']  = $active_res['response'];               # 活动列表

        return $this->endInvoke($res['response']);
    }


    /**
     * 获取全部分类
     * Bll.B2b.Store.Lists.getClasses
     */
    public function getClasses($params){

        $apiPath = "Base.ItemModule.Classify.ClassifyInfo.getClasses";
        $class_res = $this->invoke($apiPath,$params);
        if($class_res['status'] != 0){
            return $this->endInvoke(NULL,$class_res['status']);
        }

        return $this->endInvoke($class_res['response']);
    }


    /**
     * 获取优惠券金额
     * Bll.B2b.Store.Lists.getCoupons
     */
    public function getCoupons($params){
        //获取优惠券钱数
        $coupon_params = array(
            'flag'=>SPC_ACTIVE_CONDITION_FLAG_REGISTER,
            'uc_code'=>$params['uc_code'],
            );
        
        $apiPath = "Base.UserModule.Coupon.Coupon.amount";
        $coupon_res = $this->invoke($apiPath,$coupon_params);
        if($coupon_res['status'] != 0){
            return $this->endInvoke(NULL,$coupon_res['status']);
        }

        return $this->endInvoke($coupon_res['response']);
    }


}

?>
