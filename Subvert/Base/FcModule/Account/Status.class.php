<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangguangjian <wangguangjian@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关状态转换
 */

namespace Base\FcModule\Account;

use System\Base;

//买家自提，卖家配送
class Status extends Base
{

    public function getAccountStatus ($account_status) {
        $account_lists = array(
          FC_ACCOUNT_STATUS_NO_ACCOUNT => '未对账', //货到付款
            FC_ACCOUNT_STATUS_ACCOUNT => '已对账',
        );
        if (!empty($account_status)) {
            return $account_lists[$account_status];
        }
        return $account_lists;
    }
    /**Base.FcModule.Account.Status.getAccountTypes 结算方
    */
    public function getAccountTypes ($data) {
        $account_types = array(
            FC_TYPE_WEIXIN => '微信支付',
            FC_TYPE_UCPAY => '先锋支付',
        );
        if (!empty($data)) {
            return $account_types[$data];
        }
        return $account_types;
    }
    /** Base.FcModule.Account.Status.getPayMethod 获取支付类型
    */
    public function getPayMethod ($pay_status) {
        $pay_group = array(
            PAY_METHOD_ONLINE_UCPAY => '先锋支付',
            PAY_METHOD_ONLINE_REMIT => '银行转账',
            PAY_METHOD_ONLINE_ALIPAY => '支付宝支付',
            PAY_METHOD_ONLINE_WEIXIN => '微信支付',
        );
        if (!empty($pay_status)) {
            return $pay_group[$pay_status];
        }
        return $pay_group;
    }

    public function getBalanceStatus ($balance_status) {
        $balance_group = array(
            FC_BALANCE_STATUS_NO_BALANCE => '未到账',
            FC_BALANCE_STATUS_YES_BALANCE => '已到账',
            FC_BALANCE_STATUS_BALANCE => '已结算',
        );
        if (!empty($balance_status)) {
            return $balance_group[$balance_status];
        }
        return $balance_group;
    }

    public function getPayStatus ($pay_status) {
        $pay_status_lsit = array(
            OC_ORDER_PAY_STATUS_UNPAY => '待付款',
            OC_ORDER_PAY_STATUS_PAY => '已付款',
        );
        if (!empty($pay_status)) {
            return $pay_status_lsit[$pay_status];
        }
        return $pay_status_lsit;
    }
    public function getOrderType($type){
        $order_type = array(
            OC_ORDER_TYPE_STORE => '店铺订单',
            OC_ORDER_TYPE_PLATFORM => '平台商城订单',
            FC_ORDER_CONFIRM_OC_TYPE_ADVANCE => '预付款充值订单',
            
        );
        if (!empty($balance_status)) {
            return $order_type[$type];
        }
        return $order_type;
    }


    //银行列表
    // apiPath = M('Base.FcModule.Account.Status.getBankStatus')->getBankStatus();
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
    // apiPath = M('Base.FcModule.Account.Status.getStoreType')->getStoreType();
    public function getStoreType($orderType) {
        $orderTypeLists = array(
            OC_ORDER_TYPE_STORE => '店铺订单',
            OC_ORDER_TYPE_PLATFORM => '平台商城订单',
        );
        if (!empty($orderType)) {
            return $orderTypeLists[$orderType];
        }
        return $orderTypeLists;
    }


    //付款银行列表
    // apiPath = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
    public function getPayBankStatus ($bank_status) {
        $bank_lists = array(
            PAY_METHOD_REMIT_CMB => '招商银行',
            PAY_METHOD_REMIT_CMBC => '民生银行',
        );
        if (!empty($bank_status)) {
            return $bank_lists[$bank_status];
        }
        return $bank_lists;
    }


}