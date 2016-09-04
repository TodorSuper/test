<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangxuemei
 * +---------------------------------------------------------------------
 * | 财务审单、制单、付款
 */

namespace Bll\Cms\Finance;

use System\Base;

class Payment extends Base {
    private $_rule = null; # 验证规则列表
    public function __construct() {
        parent::__construct();
    }

    /**
     * Bll.Cms.Finance.Payment.accountPaidLists
     * @param type $params
     * @return type
     */

    public function accountPaidLists($params) {
        if(!empty($params['b2b_code'])){
            $getFcCodeApiPath = "Base.FcModule.Payment.Payment.getConfirm";
            $confirm = $this->invoke($getFcCodeApiPath, $params);
            $params['fc_code'] = $confirm['response']['fc_code'];
        }

        $paymentApiPath = "Base.FcModule.Payment.Payment.accountPaidLists";
        $paymentList = $this->invoke($paymentApiPath, $params);
        $fc_code = [];
        foreach($paymentList['response']['lists'] as $val){
            $fc_code[] = $val['fc_code'];
        }
        if(empty($fc_code))
        {
            $response['bankLists'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
            return $this->endInvoke($response,8051);
        }
        $confirmWhere = array(
            'fc_code' => $fc_code,
        );

        #获取订单数量
        $countApiPath = "Base.FcModule.Payment.Payment.countNumber";
        $count = $this->invoke($countApiPath, $confirmWhere)['response']['lists'][0]['count'];
        $confirmWhere['page_number'] = $count;

        $confirmApiPath = "Base.FcModule.Payment.Payment.findConfirmLists";
        $confirmList = $this->invoke($confirmApiPath, $confirmWhere);


        $response  = array(
            'paymentLists' => $paymentList['response'],
            'confirmLists' => $confirmList['response']['lists'],
            'confirmStatus' => M('Base.FcModule.Payment.Status.getConfirmStatus')->getConfirmStatus(),
            'payMethodStatus' => M('Base.OrderModule.B2b.Status.getPayMethod')->getPayMethod(),
            'orderTypeLists' => M('Base.FcModule.Account.Status.getStoreType')->getStoreType(),
            'bankLists' => M('Base.FcModule.Account.Status.getBankStatus')->getBankStatus(),
        );
        return $this->endInvoke($response);
    }

    /**
     * Bll.Cms.Finance.Payment.fcPaymentExport
     * 导出已付款相关订单列表明细excel表
     */
    public function fcPaymentExport($params){

        if(!empty($params['b2b_code'])){
            $getFcCodeApiPath = "Base.FcModule.Payment.Payment.getConfirm";
            $confirm = $this->invoke($getFcCodeApiPath, $params);
            $params['fc_code'] = $confirm['response']['fc_code'];
        }

        $apiPath = 'Base.FcModule.Payment.Payment.fcPaymentExport';
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res);
    }

    /**Bll.Cms.Finance.Payment.getAllOrder
     *财务审单&制单&付款 订单列表页
     *
     */
    public function getAllOrder($params){
        $apiPath = "Base.FcModule.Payment.Payment.getAllOrder";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $order_type = M('Base.FcModule.Account.Status.getOrderType')->getOrderType();
        $order_status = M('Base.FcModule.Payment.Status.getOrderStatus')->getOrderStatus();
        $list['response']['pay_method_group'] = $pay_method_group;
        $list['response']['order_status'] = $order_status;
        $list['response']['order_type'] = $order_type;
        $list['response']['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }
            if($v['pay_method_ext1']){
                $data[$k]['pay_method_ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['pay_method_ext1']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['adv_pay_method']){
                $data[$k]['adv_pay_method'] = $pay_method_group[$v['adv_pay_method']];
            }
            if($v['oc_type'] == FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                $data[$k]['oc_type'] = $order_type[$v['oc_type']];
            }else{
                $data[$k]['order_type'] = $order_type[$v['order_type']];
            }
            if($v['pay_status']){
                $data[$k]['pay_status'] =M('Base.FcModule.Account.Status.getPayStatus')-> getPayStatus($v['pay_status']);
            }
            if($v['adv_status']){
                $data[$k]['adv_status'] =M('Base.FcModule.Account.Status.getPayStatus')-> getPayStatus($v['adv_status']);
            }
            $list['response']['lists'] = $data;
        }
        return $this->endInvoke($list['response']);
    }

    /**
     * Bll.Cms.Finance.Payment.getCount
     * 统计所有未审单的订单
     */
    public function getCount($params){
        $apiPath = 'Base.FcModule.Payment.Payment.getCount';
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'],$res['status'],$res['message']);
    }

    /**Bll.Cms.Finance.Payment.checkOrder
     *审单列表
     */
    public function checkOrder($params){
        $apiPath = "Base.FcModule.Payment.Payment.checkOrder";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $order_type = M('Base.FcModule.Account.Status.getOrderType')->getOrderType();
        $order_status = M('Base.FcModule.Payment.Status.getOrderStatus')->getOrderStatus();
        $list['response']['pay_method_group'] = $pay_method_group;
        $list['response']['order_status'] = $order_status;
        $list['response']['order_type'] = $order_type;
        $list['response']['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }
            if($v['pay_method_ext1']){
                $data[$k]['pay_method_ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['pay_method_ext1']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['adv_pay_method']){
                $data[$k]['adv_pay_method'] = $pay_method_group[$v['adv_pay_method']];
            }
            if($v['oc_type'] == FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                $data[$k]['oc_type'] = $order_type[$v['oc_type']];
            }else{
                $data[$k]['order_type'] = $order_type[$v['order_type']];
            }
            if($v['pay_status']){
                $data[$k]['pay_status'] =M('Base.FcModule.Account.Status.getPayStatus')-> getPayStatus($v['pay_status']);
            }
            if($v['adv_status']){
                $data[$k]['adv_status'] =M('Base.FcModule.Account.Status.getPayStatus')-> getPayStatus($v['adv_status']);
            }
            $list['response']['lists'] = $data;
        }
        return $this->endInvoke($list['response']);
    }

    /**
     * Bll.Cms.Finance.Payment.getShopName
     * 获取商铺名称
     */
    public function getShopName($params){

        $apiPath = 'Base.FcModule.Payment.Payment.getShopName';
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'],$res['status'],$res['message']);
    }



    /**Bll.Cms.Finance.Payment.upCheckOrder
     *审单操作
     */
    public function upCheckOrder($params){
        $apiPath = "Base.FcModule.Payment.Payment.upCheckOrder";
        $resInfo = $this->invoke($apiPath, $params);
        return $this->endInvoke($resInfo['response'],$resInfo['status'],$resInfo['message']);
    }

    /**
     * Bll.Cms.Finance.Payment.allOrderExport
     * 导出已付款相关订单列表明细excel表
     */
    public function allOrderExport($params){
        $apiPath = 'Base.FcModule.Payment.Payment.allOrderExport';
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res);
    }

    /**
     * 待制单列表
     * Bll.Cms.Finance.Payment.unCreateOrder
     * @param array $params
     * @return array
     */
    public function unCreateOrder($params) {
        $apiPath = "Base.FcModule.Payment.Payment.unCreateOrder";
        $list = $this->invoke($apiPath, $params);
        $method = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $StoreType = M('Base.FcModule.Account.Status.getStoreType')->getStoreType();

        // 整理数组
        foreach($list['response']['lists'] as $k => &$v) {
            $v['account_no_f'] = rewrite($v['account_no']);
            if($v['oc_type'] == 'GOODS') {   // 如果是商品订单
                if($v['order_status'] == OC_ORDER_ORDER_STATUS_COMPLETE) {
                    $v['new_status'] = '已完成';
                    $v['c_time'] = $v['complete_time'] ? $v['complete_time'] : '--';
                } elseif($v['pay_status'] == OC_ORDER_PAY_STATUS_PAY) {
                    $v['new_status'] = '已付款';
                    $v['c_time'] = '--';
                } else {
                    $v['new_status'] = '--';
                    $v['c_time'] = '--';
                }
                $v['p_method'] = $v['bmethod'];
                $v['amount'] = $v['realamount'];
                $v['ext'] = $v['b2bext1'];
                $v['commercial_name'] = $v['gcname'];
                $v['name'] = $v['gname'];
                foreach($method as $mk=>$mv) {
                    if($v['bmethod'] == $mk) {
                        if($mk == PAY_METHOD_ONLINE_UCPAY) {    // 如果是先锋支付的话, 手续费需要做处理
                            if($v['amount'] <= 1000) {
                                $v['cost'] = 2;
                            } else {
                                $v['cost'] = $v['amount'] * 0.002;
                            }
                        }
                        $v['paymeth'] = $mv;
                        if ($mk == FC_TYPE_REMIT) {
                            $v['newpaytime'] = $v['confirmtime'];
                        } else {
                            $v['newpaytime'] = $v['b2bpaytime'];
                        }
                    }
                }
            } else {
                $v['ext'] = $v['advext1'];
                if($v['a_status'] == OC_ORDER_ORDER_STATUS_COMPLETE) {
                    $v['new_status'] = '已完成';
                    $v['c_time'] = $v['complete_time'] ? $v['complete_time'] : '--';
                } elseif($v['a_status'] == OC_ORDER_PAY_STATUS_PAY) {
                    $v['new_status'] = '已付款';
                    $v['c_time'] = '--';
                } else {
                    $v['new_status'] = '--';
                    $v['c_time'] = '--';
                }
                $v['p_method'] = $v['amethod'];
                $v['amount'] = $v['advamount'];
                $v['commercial_name'] = $v['acname'];
                $v['name'] = $v['aname'];
                foreach($method as $mk=>$mv) {
                    if($v['amethod'] == $mk) {
                        if($mk == PAY_METHOD_ONLINE_UCPAY) {    // 如果是先锋支付的话, 手续费需要做处理
                            if($v['amount'] <= 1000) {
                                $v['cost'] = 2;
                            } else {
                                $v['cost'] = $v['amount'] * 0.002;
                            }
                        }
                        $v['paymeth'] = $mv;
                        if ($mk == FC_TYPE_REMIT) {
                            $v['newpaytime'] = $v['confirmtime'];
                        } else {
                            $v['newpaytime'] = $v['advpaytime'];
                        }
                    }
                }
            }
            if($v['p_method'] == PAY_METHOD_ONLINE_UCPAY) {
                $v['bank_money'] = $v['amount'];
            } else {
                $v['bank_money'] = bcsub($v['amount'], $v['cost'], 2);
            }
        }
        $list['response']['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
        $list['response']['paymethod'] = $method;
        $list['response']['StoreType'] = $StoreType;
        return $this->endInvoke($list['response'],$list['status'],$list['message']);
    }

    /**
     * 已制单列表
     * Bll.Cms.Finance.Payment.hasCreateOrder
     * @param array $params
     * @return array
     */
    public function hasCreateOrder($params) {

        if(!empty($params['b2b_code'])){
            $getFcCodeApiPath = "Base.FcModule.Payment.Payment.getConfirm";
            $confirm = $this->invoke($getFcCodeApiPath, $params);
            $params['fc_code'] = $confirm['response']['fc_code'];
        }

        $apiPath = "Base.FcModule.Payment.Payment.hasCreateOrderPayment";
        $paymentLists = $this->invoke($apiPath, $params);
        $fc_code = array();
        foreach($paymentLists['response']['lists'] as $val){
            $fc_code[] = $val['fc_code'];
        }
        if(empty($fc_code)) {
            $response['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
            return $this->endInvoke($response,0);
        }
        // 收集汇总表中所有的fc_code
        $confirmWhere = array(
            'fc_code' => $fc_code,
            'createStart' => $params['createStart'],
            'createEnd' => $params['createEnd']
        );
        // 查询汇总表关联的所有订单
        $orderapi = "Base.FcModule.Payment.Payment.hasCreateOrderInfo";
        $orderlist = $this->invoke($orderapi, $confirmWhere);
        $response  = array(
            'paymentLists' => $paymentLists['response'],
            'confirmLists' => $orderlist['response']['lists'],
        );
        $method = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        // 整理数组
        foreach($response['paymentLists']['lists'] as &$val){
            $val['account_number_f'] = rewrite($val['account_number']);
            foreach($response['confirmLists'] as &$v){
                if($v['oc_type'] == 'GOODS') {
                    $v['amount'] = $v['real_amount'];
                    $v['commercial_name'] = $v['ocommercial_name'];
                    $v['name'] = $v['real_name'];
                    $v['p_method'] = $v['bmethod'];
                    if($v['order_status'] == OC_ORDER_ORDER_STATUS_COMPLETE) {
                        $v['new_status'] = '已完成';
                        $v['c_time'] = $v['complete_time'] ? $v['complete_time'] : '--';
                    } elseif($v['pay_status'] == OC_ORDER_PAY_STATUS_PAY) {
                        $v['new_status'] = '已付款';
                        $v['c_time'] = '--';
                    } else {
                        $v['new_status'] = '--';
                        $v['c_time'] = '--';
                    }
                    foreach($method as $mk=>$mv) {
                        if($v['bmethod'] == $mk) {
                            if($mk == PAY_METHOD_ONLINE_UCPAY) {    // 如果是先锋支付的话, 手续费需要做处理
                                if($v['amount'] <= 1000) {
                                    $v['cost'] = 2;
                                } else {
                                    $v['cost'] = $v['amount'] * 0.002;
                                }
                            }
                            $v['paymeth'] = $mv;
                            if ($mk == FC_TYPE_REMIT) {
                                $v['newpaytime'] = $v['update_time'];
                            } else {
                                $v['newpaytime'] = $v['b2bpaytime'];
                            }
                        }
                    }
                } else {
                    if($v['a_status'] == OC_ORDER_ORDER_STATUS_COMPLETE) {
                        $v['new_status'] = '已完成';
                        $v['c_time'] = $v['complete_time'] ? $v['complete_time'] : '--';
                    } elseif($v['a_status'] == OC_ORDER_PAY_STATUS_PAY) {
                        $v['new_status'] = '已付款';
                        $v['c_time'] = '--';
                    } else {
                        $v['new_status'] = '--';
                        $v['c_time'] = '--';
                    }
                    $v['amount'] = $v['advamount'];
                    $v['commercial_name'] = $v['ucommercial_name'];
                    $v['p_method'] = $v['amethod'];
                    foreach($method as $mk=>$mv) {
                        if($v['amethod'] == $mk) {
                            if($mk == PAY_METHOD_ONLINE_UCPAY) {    // 如果是先锋支付的话, 手续费需要做处理
                                if($v['amount'] <= 1000) {
                                    $v['cost'] = 2;
                                } else {
                                    $v['cost'] = $v['amount'] * 0.002;
                                }
                            }
                            $v['paymeth'] = $mv;
                            if ($mk == FC_TYPE_REMIT) {
                                $v['newpaytime'] = $v['update_time'];
                            } else {
                                $v['newpaytime'] = $v['oapay_time'];
                            }
                        }
                    }
                }

                if($val['fc_code'] == $v['fc_code']){
                    if($v['p_method'] == PAY_METHOD_ONLINE_UCPAY) {
                        $v['bank_amount'] = $v['amount'];
                    } else {
                        $v['bank_amount'] = bcsub($v['amount'], $v['cost'], 2);
                    }
                    $val['orderLists'][] = $v;
                }
            }
        }
        //给数据赋值
        foreach($response['paymentLists']['lists'] as &$val){
            $val['count'] = count($val['orderLists']);
        }
        $StoreType = M('Base.FcModule.Account.Status.getStoreType')->getStoreType();
        $response['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
        $response['paymethod'] = $method;
        $response['StoreType'] = $StoreType;

        return $this->endInvoke($response);
    }

    /**
     * 单个制单
     * Bll.Cms.Finance.Payment.createOrder
     * @param array $params
     * @return array
     */
    public function createOrder($params) {
        $apiPath = "Base.FcModule.Payment.Payment.createOrder";
        $confirmapiPath = "Base.FcModule.Payment.Order.updateConfirm";
        $splitApiPath = "Base.FcModule.Payment.Payment.createOrderSplit";
        $params['bank_type'] = ($params['bank_type'] == PAY_METHOD_REMIT_CMB) ? $params['bank_type'] : PAY_METHOD_REMIT_CMBC;
        if(empty($params['account_bank'])) {
            return $this->endInvoke(null,8091);
        }
        if(empty($params['account_code'])) {
            return $this->endInvoke(null,8092);
        }
        if(empty($params['account_name'])) {
            return $this->endInvoke(null,8093);
        }
        if(empty($params['account_number'])) {
            return $this->endInvoke(null,8094);
        }
        if(empty($params['account_type'])) {
            return $this->endInvoke(null,8095);
        }
        $params['status'] = FC_STATUS_ON_PAYMENT;   // 已汇总,未付款
        // 以下提前生成汇总fc_code
        try {
            D()->startTrans();
            // 生成fc_code
            $cApiPath = "Com.Tool.Code.CodeGenerate.mkCode";
            $code_params = array(
                'busType' => FC_CODE,
                'preBusType' => FP_CODE,
                'codeType' => SEQUENCE_FC,
            );
            $code_res = $this->invoke($cApiPath, $code_params);
            if ($code_res['status'] != 0) {
                return $this->endInvoke($code_res['response'], $code_res['status'], $code_res['message']);
            }
            $params['fc_code'] = $code_res['response'];

            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(), $ex->getCode());
        }
        // 以下提前生成拆单fc_code
        $split_tag = "YES";
        // 判断订单金额,跨行,公转私
        $limitAmount = C('BANK_PARAMS.limitAmount');
        if (($params['amount'] > $limitAmount) && ($params['account_code'] != $params['bank_type']) && $params['account_type']==PERSONAL_ACCOUNT) { // 如果需要拆分订单(金额大于50000并且是跨行公转私)
            $cou = ceil($params['amount'] / $limitAmount);
            $split_tag = "NO";
            $params['payment_type'] = 'YES';
            $params['payment_num'] = $cou;
        }
        if($split_tag == "NO") {    // 如果需要拆分订单(金额大于50000并且是跨行公转私)
            for($j=1; $j<=$cou; $j++) {
                try {
                    D()->startTrans();
                    // 生成fc_code
                    $cApiPath = "Com.Tool.Code.CodeGenerate.mkCode";
                    $code_params = array(
                        'busType' => FC_CODE,
                        'preBusType' => FP_CODE,
                        'codeType' => SEQUENCE_FC,
                    );
                    $code_res = $this->invoke($cApiPath, $code_params);
                    if ($code_res['status'] != 0) {
                        return $this->endInvoke($code_res['response'], $code_res['status'], $code_res['message']);
                    }
                    $fc_codes_acc[] = $code_res['response'];
                    $commit_res = D()->commit();
                    if ($commit_res === FALSE) {
                        throw new \Exception('事务提交失败', 17);
                    }
                } catch (\Exception $ex) {
                    D()->rollback();
                    return $this->endInvoke($ex->getMessage(), $ex->getCode());
                }
            }
        }
        try {
            D()->startTrans();
            // 首先需要插入汇总表 payment 返回数据包括商家编码sc_code、生成的fc_code.
            $res = $this->invoke($apiPath, $params);
            if (0 != $res['status']) {
                throw new \Exception($res['message '], $res['status']);
            }
            // 更新confirm
            $upConfirm['data'] = array(
                'f_status' => FC_F_STATUS_ON_PAYMENT,
                'fc_code' =>$res['response']['fc_code'],
            );
            $upConfirm['no_update_time'] = 'no';
            $upConfirm['where'] = array(
                'sc_code' => $params['sc_code'],
                'b2b_code'=> array($params['b2b_code']),
                'status' =>  FC_STATUS_CONFIRM,
                'f_status' => FC_F_STATUS_UN_PAYMENT,
            );
            $upConfirmRes = $this->invoke($confirmapiPath, $upConfirm);
            if($upConfirmRes['status'] != 0) {
                throw new \Exception($upConfirmRes['message'],$upConfirmRes['status']);
            }
            if ($split_tag == 'NO') { // 如果需要拆分订单(金额大于50000并且是跨行公转私)
                for ($i = 0; $i < $cou; $i++) {
                    if($i == ($cou-1)) {
                        $amount = bcsub($params['amount'],$limitAmount*$i,2);
                    } else {
                        $amount = $limitAmount;
                    }
                    $cur_data[] = array(
                        'fc_code' => $params['fc_code'],
                        'account_code' => $fc_codes_acc[$i],
                        'amount' => $amount,
                        'create_time' => NOW_TIME,
                        'bank_type' => $params['bank_type'],
                    );
                }
                // 插入payment_account表
                $res = $this->invoke($splitApiPath, $cur_data);
                if (0 !== $res['status']) {
                    throw new \Exception($res['message '], $res['status']);
                }
            } else {
                // 插入payment_account表
                $c_data[] = array(
                    'fc_code' => $params['fc_code'],
                    'account_code' => $params['fc_code'],
                    'amount' => $params['amount'],
                    'create_time' => NOW_TIME,
                    'bank_type' => $params['bank_type'],
                );
                $result = $this->invoke($splitApiPath, $c_data);
                if (0 !== $result['status']) {
                    throw new \Exception($result['message '], $result['status']);
                }
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(), $ex->getCode());
        }


        return $this->endInvoke('ok', 0);
    }

    /**
     * 批量制单
     * Bll.Cms.Finance.Payment.createOrderS
     * @param array $params
     * @return array
     */
    public function createOrderS($params) {
        // 所有的b2b_code
        $b2b_codes = array();
        // 所有的商铺编号sc_code
        $sc_codes = array();
        // 商铺对应订单到账银行信息
        //$store_bank = array();
        foreach($params['data'] as $k=>$v) {
            $b2b_codes[] = $v[0];
            $sc_codes[] = $v[1];
            // 对到账银行进行整理
            if($v[4] == PAY_METHOD_REMIT_CMB) {
                $params['data'][$k][4] = PAY_METHOD_REMIT_CMB;
            } else {
                $params['data'][$k][4] = PAY_METHOD_REMIT_CMBC;
            }
        }

        // 标记选择的订单是否属于同一个银行
        $tag0 = 0;
        $tag1 = 0;
        // 如果用户选择的订单中有招行也有民生,则提示用户
        foreach($params['data'] as $k1=>$v1) {
            if ($v1[4] == PAY_METHOD_REMIT_CMB) {
                $tag0 = 1;
            }
            if ($v1[4] == PAY_METHOD_REMIT_CMBC) {
                $tag1 = 1;
            }
            if($tag0 == 1 && $tag1 == 1) {
                return $this->endInvoke(null,8110);
            }
        }
        // 记录此次制单银行
        $bank_type = $params['data'][0][4];
        //将所有商铺的银行信息查出来
        $storeWhere['sc_code'] = array(
            'in',
            $sc_codes
        );
        $storeFields = 'name as sc_name,sc_code,account_bank,account_name,account_no,account_type,account_code';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $storeParams['center_flag'] = SQL_FC;
        $storeParams['sql_flag'] = 'getStoreInfo';
        $storeParams['where'] = $storeWhere;
        $storeParams['fields'] = $storeFields;
        $storeBankInfo = $this->invoke($apiPath, $storeParams);
        if(empty($storeBankInfo['response']['lists'])) {
            return $this->res(null,7091);
        }
        $storeBankInfo = $storeBankInfo['response']['lists'];
        // 对数组进行处理
        foreach($storeBankInfo as $bank_k=>$bank_v) {
            if(empty($bank_v['account_bank'])) {
                return $this->endInvoke(null,8091);
            }
            if(empty($bank_v['account_code'])) {
                return $this->endInvoke(null,8092);
            }
            if(empty($bank_v['account_name'])) {
                return $this->endInvoke(null,8093);
            }
            if(empty($bank_v['account_no'])) {
                return $this->endInvoke(null,8094);
            }
            if(empty($bank_v['account_type'])) {
                return $this->endInvoke(null,8095);
            }
            // 只要有一个商家是跨行共对私并且大于50000
            if($storeBankInfo[$bank_k]['account_type']==PERSONAL_ACCOUNT && $bank_v['account_code'] != $params['bank_type'] && $params['amount_money']>C('BANK_PARAMS.limitAmount')) {
                return $this->res(null,7095);
            }
            foreach ($params['data'] as $key => $val) {
                if($bank_v['sc_code'] == $val[1]) {
                    $storeBankInfo[$bank_k]['amount'] = bcadd($storeBankInfo[$bank_k]['amount'], $val[2],2);
                    $storeBankInfo[$bank_k]['make_time'] = NOW_TIME;
                    $storeBankInfo[$bank_k]['status'] = FC_STATUS_ON_PAYMENT;
                    $storeBankInfo[$bank_k]['create_name'] = $params['real_name'];
                    $storeBankInfo[$bank_k]['uc_code'] = $params['uc_code'];
                    $storeBankInfo[$bank_k]['bank_type'] = $bank_type;
                }
            }
        }
        $apiPath = "Base.FcModule.Payment.Payment.createOrderS";
        $confirmapiPath = "Base.FcModule.Payment.Order.updateConfirm";
        $splitApiPath = "Base.FcModule.Payment.Payment.createOrderSplit";
        $c_data = array();
        // 生成fc_code
        try {
            D()->startTrans();
            foreach($storeBankInfo as $k=>$v) {
                $cApiPath = "Com.Tool.Code.CodeGenerate.mkCode";
                $code_params = array(
                    'busType' => FC_CODE,
                    'preBusType' => FP_CODE,
                    'codeType' => SEQUENCE_FC,
                );
                $code_res = $this->invoke($cApiPath, $code_params);
                if ($code_res['status'] !== 0) {
                    return $this->endInvoke($code_res['response'], $code_res['status'],$code_res['message']);
                }
                $storeBankInfo[$k]['fc_code'] = $code_res['response'];
                $storeBankInfo[$k]['account_number'] = $v['account_no'];
                $c_data[$k]['fc_code'] = $code_res['response'];
                $c_data[$k]['account_code'] = $code_res['response'];
                $c_data[$k]['amount'] = $v['amount'];
                $c_data[$k]['create_time'] = NOW_TIME;  // 制单时间
                $c_data[$k]['bank_type'] = $bank_type;
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(), $ex->getCode());
        }
        try{
            D()->startTrans();
            // 插入汇总表 payment  addPayments 返回数据包括商家编码sc_code、生成的fc_code.
            $res = $this->invoke($apiPath, $storeBankInfo);
            if(0 !== $res['status']){
                throw new \Exception($res['message '],$res['status']);
            }
            // 插入payment_account表
            $result = $this->invoke($splitApiPath, $c_data);
            if (0 != $result['status']) {
                throw new \Exception($result['message '], $result['status']);
            }
            // 批量更新confirm
            foreach($res['response'] as $key=>$val) {
                // 查找当前sc_code所对应的所有b2b_code
                $confirm_b2bcodes = array();
                foreach($params['data'] as $kk=>$vv) {
                    if($vv[1] == $val['sc_code']) {
                        $confirm_b2bcodes[] = $vv[0];
                    }
                }
                $upConfirm['no_update_time'] = 'no';
                $upConfirm['data'] = array(
                    'f_status' => FC_F_STATUS_ON_PAYMENT,
                    'fc_code' => $val['fc_code'],
                );
                $upConfirm['where'] = array(
                    'sc_code' =>  $val['sc_code'],
                    'f_status' => FC_F_STATUS_UN_PAYMENT,
                    'b2b_code' => $confirm_b2bcodes,
                    'status' => FC_STATUS_CONFIRM,
                );
                $upConfirmRes = $this->invoke($confirmapiPath, $upConfirm);
                if($upConfirmRes['status'] !== 0) {  // ||  $upConfirmRes['response'] !== $num ){
                    throw new \Exception($upConfirmRes['message '],$upConfirmRes['status']);
                }
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(),$ex->getCode());
        }
        return $this->endInvoke('ok',0);
    }

    /**
     * 重新制单
     * Bll.Cms.Finance.Payment.failCreateOrder
     * @param array $params
     * @return array
     */
    public function failCreateOrder($params) {
        $apiPath = "Base.FcModule.Payment.Payment.failCreateOrder";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res,$res['status']);
    }

    /**
     * 重新制单
     * Bll.Cms.Finance.Payment.failCreateOrder
     * @param array $params
     * @return array
     */
    public function failCreateOrderInfo($params) {
        if(!empty($params['b2b_code'])){
            $getFcCodeApiPath = "Base.FcModule.Payment.Payment.getConfirm";
            $confirm = $this->invoke($getFcCodeApiPath, $params);
            $params['fc_code'] = $confirm['response']['fc_code'];
        }

        $apiPath = "Base.FcModule.Payment.Payment.failCreateOrderPayment";
        $paymentLists = $this->invoke($apiPath, $params);
        $fc_code = array();
        foreach($paymentLists['response']['lists'] as $val){
            $fc_code[] = $val['fc_code'];
        }
        if(empty($fc_code)) {
            $response['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
            return $this->endInvoke($response,0);
        }
        // 收集汇总表中所有的fc_code
        $confirmWhere = array(
            'fc_code' => $fc_code,
            'createStart' => $params['createStart'],
            'createEnd' => $params['createEnd']
        );
        // 查询汇总表关联的所有订单
        $orderapi = "Base.FcModule.Payment.Payment.hasCreateOrderInfo";
        $orderlist = $this->invoke($orderapi, $confirmWhere);
        $response  = array(
            'paymentLists' => $paymentLists['response'],
            'confirmLists' => $orderlist['response']['lists'],
        );
        $method = M('Base.OrderModule.B2b.Status.getPayMethod')->getPayMethod();
        // 整理数组
        foreach($response['paymentLists']['lists'] as &$val){
            $val['account_number_f'] = rewrite($val['account_number']);
            foreach($response['confirmLists'] as &$v){
                if($v['oc_type'] == 'GOODS') {
                    $v['amount'] = $v['real_amount'];
                    $v['commercial_name'] = $v['ocommercial_name'];
                    $v['name'] = $v['real_name'];
                    $v['p_method'] = $v['bmethod'];
                    foreach($method as $mk=>$mv) {
                        if($v['bmethod'] == $mk) {
                            if($mk == PAY_METHOD_ONLINE_UCPAY) {    // 如果是先锋支付的话, 手续费需要做处理
                                if($v['amount'] <= 1000) {
                                    $v['cost'] = 2;
                                } else {
                                    $v['cost'] = $v['amount'] * 0.002;
                                }
                            }
                            $v['paymeth'] = $mv;
                            if ($mk == FC_TYPE_REMIT) {
                                $v['newpaytime'] = $v['update_time'];
                            } else {
                                $v['newpaytime'] = $v['b2bpaytime'];
                            }
                        }
                    }
                } else {
                    $v['amount'] = $v['advamount'];
                    $v['commercial_name'] = $v['ucommercial_name'];
                    $v['p_method'] = $v['amethod'];
                    foreach($method as $mk=>$mv) {
                        if($v['amethod'] == $mk) {
                            if($mk == PAY_METHOD_ONLINE_UCPAY) {    // 如果是先锋支付的话, 手续费需要做处理
                                if($v['amount'] <= 1000) {
                                    $v['cost'] = 2;
                                } else {
                                    $v['cost'] = $v['amount'] * 0.002;
                                }
                            }
                            $v['paymeth'] = $mv;
                            if ($mk == FC_TYPE_REMIT) {
                                $v['newpaytime'] = $v['update_time'];
                            } else {
                                $v['newpaytime'] = $v['oapay_time'];
                            }
                        }
                    }
                }

                if($val['fc_code'] == $v['fc_code']){
                    if($v['p_method'] == PAY_METHOD_ONLINE_UCPAY) {
                        $v['bank_amount'] = $v['amount'];
                    } else {
                        $v['bank_amount'] = bcsub($v['amount'], $v['cost'], 2);
                    }
                    $val['orderLists'][] = $v;
                }
            }
        }
        //给数据赋值
        foreach($response['paymentLists']['lists'] as &$val){
            $val['count'] = count($val['orderLists']);
        }
        $StoreType = M('Base.FcModule.Account.Status.getStoreType')->getStoreType();
        $response['account_bank_group'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus();
        $response['StoreType'] = $StoreType;
        return $this->endInvoke($response);
    }
    /**
     * Bll.Cms.Finance.Payment.abnormal
     * @param type $params
     * @return type
     */

    public function abnormal($params) {

        if(!empty($params['b2b_code'])){
            $getFcCodeApiPath = "Base.FcModule.Payment.Payment.getConfirm";
            $confirm = $this->invoke($getFcCodeApiPath, $params);
            $params['fc_code'] = $confirm['response']['fc_code'];
        }

        $paymentApiPath = "Base.FcModule.Payment.Payment.abnormal";
        $paymentList = $this->invoke($paymentApiPath, $params);
        $fc_code = [];
        foreach($paymentList['response']['lists'] as $val){
            $fc_code[] = $val['fc_code'];
        }
        if(empty($fc_code))
        {
            return $this->endInvoke(null,8051);
        }
        $confirmWhere = array(
            'fc_code' => $fc_code,
        );

        #获取订单数量
        $countApiPath = "Base.FcModule.Payment.Payment.countNumber";
        $count = $this->invoke($countApiPath, $confirmWhere)['response']['lists'][0]['count'];
        $confirmWhere['page_number'] = $count;

        $confirmApiPath = "Base.FcModule.Payment.Payment.findConfirmLists";
        $confirmList = $this->invoke($confirmApiPath, $confirmWhere);


        $response  = array(
            'paymentLists' => $paymentList['response'],
            'confirmLists' => $confirmList['response']['lists'],
            'confirmStatus' => M('Base.FcModule.Payment.Status.getConfirmStatus')->getConfirmStatus(),
            'payMethodStatus' => M('Base.OrderModule.B2b.Status.getPayMethod')->getPayMethod(),
            'orderTypeLists' => M('Base.FcModule.Account.Status.getStoreType')->getStoreType(),
            'bankLists' => M('Base.FcModule.Account.Status.getPayBankStatus')->getPayBankStatus(),
        );
        return $this->endInvoke($response);
    }

    /**
     * 手动线下付款
     * Bll.Cms.Finance.Payment.offLinePay
     * @param array $params
     * @return array
     */
    public function offLinePay($params) {
        $apiPath = "Base.FcModule.Payment.Payment.offLinePay";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'],$res['status']);
    }

    /**
     * Bll.Cms.Finance.Payment.getFailCount
     * 统计所有未审单的订单
     */
    public function getFailCount($params){
        $apiPath = 'Base.FcModule.Payment.Payment.getFailCount';
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'],$res['status'],$res['message']);
    }


}
