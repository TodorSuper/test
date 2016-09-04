<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yaozihao
 * +---------------------------------------------------------------------
 * | cms后台商品相关模块
 */

namespace Bll\Cms\Finance;

use System\Base;

class Account extends Base {
    private $_rule = null; # 验证规则列表
    public function __construct() {
        parent::__construct();
    }

    /**
     * Bll.Cms.Finance.Account.allAccount
     * @param type $params 财务对账所有订单查询
     * @return type
     *
     */
    public function  allAccount($params){
        $apiPath = "Base.FcModule.Payment.Account.allAccount";
        $list = $this->invoke($apiPath, $params);
        return $this->endInvoke($list['response'],$list['status'],$list['message']);

    }

    /**
     * Bll.Cms.Finance.Account.wechatLists
     * @param type
     * $params 微信对账列表
     * @return type
     *
     */
    public function wechatLists($params) {
        $apiPath = "Base.FcModule.Account.OrderAccount.wechatLists";
        $list = $this->invoke($apiPath, $params);
        $list['response']['lists']['account_lists'] = M('Base.FcModule.Account.Status.getAccountStatus')->getAccountStatus();
        $list['response']['account_types'] = M('Base.FcModule.Account.Status.getAccountTypes')->getAccountTypes();
        return $this->endInvoke($list['response'],$list['status'],$list['message']);
    }

    /**
     * Bll.Cms.Finance.Account.wechatUpdate
     * @param type
     * $params 对账
     * @return type
     *
     */
    public function wechatUpdate($params) {
        $apiPath = "Base.FcModule.Account.OrderAccount.wechatUpdate";
        $update = $this->invoke($apiPath, $params);
        return $this->endInvoke($update['response'],$update['status'],$update['message']);
    }

    /**
     * Bll.Cms.Finance.Account.getAccountInfo
     * @param type
     * $params 查找要对账数据
     * @return type
     *
     */
    public function getAccountInfo($params) {
        $apiPath = "Base.FcModule.Account.OrderAccount.getAccountInfo";
        $list = $this->invoke($apiPath, $params);

        return $this->endInvoke($list['response'],$list['status'],$list['message']);
    }
    /**
     * Bll.Cms.Finance.Account.advanceAccount
     * 财务对账全部订单预支付订单列表
     * @param 查询条件
     *
     */
    public function  advanceAccount($params){
        $apiPath = "Base.FcModule.Account.OrderAccount.advanceAccount";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $balance_group = M('Base.FcModule.Account.Status.getBalanceStatus')->getBalanceStatus();
        $account_bank_group =  M('Base.FcModule.Account.Status.getBankStatus')->getBankStatus();
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['pay_method_ext1']){
                $data[$k]['pay_method_ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['pay_method_ext1']);
            }
            if($v['status']){
                $data[$k]['status'] = M('Base.FcModule.Account.Status.getBalanceStatus')->getPayStatus($v['status']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['balance_status']){
                $data[$k]['balance_status'] = $balance_group[$v['balance_status']];
            }
            $list['response']['lists'] = $data;
        }
        $list['response']['list']['pay_method_group'] = $pay_method_group;
        $list['response']['list']['balance_group'] = $balance_group;
        $list['response']['list']['account_bank_group'] = $account_bank_group;
        return $this->endInvoke($list['response']);

    }

    /**
     * Bll.Cms.Finance.Account.goodsAccount
     * 财务对账全部订单商品订单列表
     * @params start_time end_time 起始时间,时间戳,非必须
     * @params balance_status 资金状态,数组,非必须
     * @params bank_code 到账流水号(入金),非必须
     * @params sc_name 卖家名称,非必须
     * @params pay_method 支付类型;数组,非必须
     * @params remit_code 汇款码,非必须
     * @params b2b_code/adv_code 订单编号,非必须
     * @return type 数据列表
     *
     */
    public function  goodsAccount($params){
        $apiPath = "Base.FcModule.Account.OrderAccount.goodsAccount";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $balance_group = M('Base.FcModule.Account.Status.getBalanceStatus')->getBalanceStatus();
        $account_bank_group =  M('Base.FcModule.Account.Status.getBankStatus')->getBankStatus();
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }
            if($v['pay_status']){
                $data[$k]['pay_status'] = M('Base.FcModule.Account.Status.getBalanceStatus')->getPayStatus($v['pay_status']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['balance_status']){
                $data[$k]['balance_status'] = $balance_group[$v['balance_status']];
            }
            $list['response']['lists'] = $data;
        }
        $list['response']['list']['pay_method_group'] = $pay_method_group;
        $list['response']['list']['balance_group'] = $balance_group;
        $list['response']['list']['account_bank_group'] = $account_bank_group;
        return $this->endInvoke($list['response']);

    }


    /**
     * Bll.Cms.Finance.Account.accountExport
     * 导出财务对账相关订单列表明细excel表
     */
    public function accountExport($params){
        $apiPath = 'Base.FcModule.Account.OrderAccount.accountExport';
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res);
    }

    /**
     * Bll.Cms.Finance.Account.unknownPayments
     * 未知回款列表
     */
    public function  unknownPayments($params) {
        $apiPath = "Base.FcModule.Account.OrderAccount.bankList";
        $list = $this->invoke($apiPath, $params);
        return $this->endInvoke($list['response'],$list['status'],$list['message']);
    }

    /**
     * Bll.Cms.Finance.Account.getMyOrders
     * 获取订单列表
     */
    public function  getMyOrders($params) {
		// 查询到账总金额
		$apiPath = "Base.FcModule.Account.OrderAccount.getBankMoney";
		$bank_money = $this->invoke($apiPath, $params['bank_ids']);
		$apiPath = "Base.FcModule.Account.OrderAccount.getMyOrders";
        $list = $this->invoke($apiPath, $params);
        $params['totalnum'] = $list['response']['totalnum'];
        $apiPath = "Base.FcModule.Account.OrderAccount.getMyOrders";
        $list = $this->invoke($apiPath, $params);
		$list['response']['bank_money'] = $bank_money['response'];
        return $this->endInvoke($list['response']);
    }

    /**
     * Bll.Cms.Finance.Account.relateunKnow
     * 未知回款关联
     */
    public function relateunKnow($params) {
        $apiPath = "Base.FcModule.Account.OrderAccount.relateunKnow";
        $list = $this->invoke($apiPath, $params);
        return $this->endInvoke($list['response'],$list['status'],$list['message']);
    }
	
    /**
     * Bll.Cms.Finance.Account.returnunKnow
     * 未知回款 "资金原路返回"
     */
    public function returnunKnow($params) {
        $apiPath = "Base.FcModule.Account.OrderAccount.returnunKnow";
        $list = $this->invoke($apiPath, $params);
        return $this->endInvoke($list['response'],$list['status'],$list['message']);
    }

}
