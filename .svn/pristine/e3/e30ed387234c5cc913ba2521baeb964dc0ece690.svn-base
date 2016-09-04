<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangguangjian <wangguangjian@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 财务订单相关状态转换
 */

namespace Base\FcModule\Payment;

use System\Base;


class Status extends Base
{
    //订单类型
    // apiPath = M('Base.FcModule.Payment.Status.getConfirmStatus')->getConfirmStatus();
    public function getConfirmStatus ($confirm_status) {
        $confirm_lists = array(
            FC_ORDER_CONFIRM_OC_TYPE_GOODS => '商品订单',
            FC_ORDER_CONFIRM_OC_TYPE_ADVANCE => '预付款充值订单',
        );
        if (!empty($confirm_status)) {
            return $confirm_lists[$confirm_status];
        }
        return $confirm_lists;
    }

    //订单状态
    // apiPath = M('Base.FcModule.Payment.Status.getOrderStatus')->getOrderStatus();
    public function getOrderStatus ($orderStatus) {
        $status_lists = array(
            'status_'.FC_STATUS_ON_CONFIRM => "已点单",
            'status_'.FC_STATUS_CONFIRM => "已审单",
            'pay_status_'.FC_STATUS_ON_PAYMENT => "已制单",
            'pay_status_'.FC_STATUS_PAYMENT => "已付款",
        );

        return $status_lists;
    }

    //银行列表
    // apiPath = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
    public function getBankStatus ($bank_status) {
        $bank_lists = array(
            PAY_METHOD_REMIT_CMB => '招商银行',
            PAY_METHOD_REMIT_CMBC => '民生银行',
        );
        if (!empty($bank_status)) {
            return $bank_lists[$bank_status];
        }
        return $bank_lists;
    }

    //获取平台订单状态
    // apiPath = M('Base.FcModule.Payment.Status.getStoreType')->getStoreType();
    public function getStoreType($OrderType) {
        $orderType = array(
            OC_ORDER_TYPE_STORE => '平台商品订单',
            OC_ORDER_TYPE_PLATFORM => '平台订单',
        );
        if (!empty($OrderType)) {
            return $orderType[$OrderType];
        }
        return $orderType;
    }

}