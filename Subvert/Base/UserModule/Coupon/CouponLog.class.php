<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 购物车相关模块
 */

namespace Base\UserModule\Coupon;

use System\Base;

class CouponLog extends Base {

    public function __construct() {
        parent::__construct();
    }


    /**
     * 优惠券领取记录
     * Base.UserModule.Coupon.CouponLog.add
     * @access public
     * @author Todor
     */


    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code','require',PARAMS_ERROR,ISSET_CHECK),        # 用户编码
            array('active_code', 'require', PARAMS_ERROR, ISSET_CHECK), # 活动编码
            array('condition_flag','require',PARAMS_ERROR,ISSET_CHECK), # 活动条件
            array('coupon_info','require',PARAMS_ERROR,ISSET_CHECK),    # 优惠券信息 json形式
            array('ext1','require',PARAMS_ERROR,ISSET_CHECK),           # b2b_code
            array('amount','require',PARAMS_ERROR,ISSET_CHECK),         # 领取金额
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $data = array(
            'uc_code'=>$params['uc_code'],
            'active_code'=>$params['active_code'],
            'condition_flag'=>$params['condition_flag'],
            'coupon_info'=>$params['coupon_info'],
            'ext1'=>empty($params['ext1']) ? '' : $params['ext1'],
            'amount'=>$params['amount'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>'ENABLE',
            );

        $log_res = D('UcMemberCouponLog')->add($data);
        
        if(empty($log_res)){
            return $this->res(NULL,7104);
        }

        return $this->res(true);
    }


    /**
     * 修改优惠券领取记录
     * Base.UserModule.Coupon.CouponLog.update
     * @access public
     * @author Todor
     */

    public function update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code','require',PARAMS_ERROR,MUST_CHECK),        # 用户编码
            array('have_show','require',PARAMS_ERROR,ISSET_CHECK),     # 是否展示过
            array('condition_flag','require',PARAMS_ERROR,ISSET_CHECK),     # 条件标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['uc_code'] = $params['uc_code'];
        !empty($params['condition_flag']) && $where['condition_flag'] = $params['condition_flag'];
        !empty($params['have_show']) && $data['have_show'] = $params['have_show'];

        $res = D('UcMemberCouponLog')->where($where)->save($data);

        if(empty($res)){
            return $this->res(NULL,4065);
        }
        return $this->res(true);
    }


    /**
     * 修改优惠券领取记录
     * Base.UserModule.Coupon.CouponLog.get
     * @access public
     * @author Todor
     */


    public function get($params){
        $this->_rule = array(
            array('uc_code','require',PARAMS_ERROR,ISSET_CHECK),        # 用户编码
            array('ext1','require',PARAMS_ERROR,ISSET_CHECK),           # 订单编码
            array('condition_flag','require',PARAMS_ERROR,ISSET_CHECK), # 条件标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        !empty($params['uc_code']) && $where['uc_code'] = $params['uc_code'];
        !empty($params['ext1']) && $where['ext1'] = $params['ext1'];
        !empty($params['condition_flag']) && $where['condition_flag'] = $params['condition_flag'];

        $res = D('UcMemberCouponLog')->where($where)->find();
        
        return $this->res($res);

    }



}

?>
