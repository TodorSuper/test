<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop提现查询数据导出
 */

namespace Bll\Cms\Finance;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Fc';
    }

    /**
     * Bll.Finance.Cms.Export.export
     * @param array $params
     * $data['where']['sc_code'] = 1020000000026;
     * $group = 6;
     * $params = array(
     *         'where' =>array(
     *
     *              'fc_code' => fc_code
     *          );
     *
     *  )
     *         $params['where']['fc_code'] = 112000548578;
     * @return array
     */
    public function  export($params){

        $group = $params['group'];
        $callback_api = '';
        switch($group)
        {
            //已汇总未付款
            case '5':
                $data['filename']   = 'noPayList';
                $data['title']      =  array('付款编号','商家名称','付款金额（元）','订单编号','订单金额（元）','订单类型','开户名','开户行','银行账号','付款状态',);  //默认导出列标题
                $data['fields']     = 'fc_code,sc_code,bank_code,amount,account_name,account_bank,account_number,remark,sc_name';
                $callback_api         = 'Com.Callback.Export.FcExport.noPayList';
                $data['template_call_api'] = "Com.Callback.Export.Template.noPayList";
                $data['where']['status'] = 1;


                if(!empty($params['fc_code']) ){
                    $data['where']['fc_code'] = trim($params['fc_code']);
                }
                if(!empty($params['b2b_code']) ){
                    $fc_code = D('FcOrderConfirm')->where(['b2b_code'=>$params['b2b_code']])->find();
                    unset($params['where']['b2b_code']);
                    $data['where']['fc_code'] = $fc_code['fc_code'];
                }
                if(!empty($params['sc_code'])){
                    $data['where']['sc_code'] = trim($params['sc_code']);
                }

                $data['order'] = 'create_time desc';
                break;
            //已汇总已付款
            case '6':
                $data['filename'] = 'yesPayList';
                $data['title']    = array('付款时间','付款编号','交易流水号','商家名称','付款金额（元）','订单编号','订单金额（元）','订单类型','开户名','开户行','银行账号','备注','付款状态');  //默认导出列标题
                $data['fields']   = 'sc_code,fc_code,bank_code,amount,account_name,account_bank,account_number,remark,sc_name,affirm_time';
                $callback_api = 'Com.Callback.Export.FcExport.yesPayList';
                $data['template_call_api'] = "Com.Callback.Export.Template.yesPayList";
                $data['where']['status'] = 2;
                $data['where'][] ='make_time is null';
                if(!empty($params['sc_code'])){
                    $data['where']['sc_code'] = trim($params['sc_code']);
                }

                if(!empty($params['fc_code'])){
                    $data['where']['fc_code'] = trim($params['fc_code']);
                }
                if(!empty($params['b2b_code']) ){
                    $fc_code = D('FcOrderConfirm')->where(['b2b_code'=>$params['b2b_code']])->find();
                    unset($params['b2b_code']);
                    $data['where']['fc_code'] = $fc_code['fc_code'];

                }
                if(!empty($params['bank_code'])){
                    $data['where']['bank_code'] = trim($params['bank_code']);
                    $data['where']['bank_code'] = trim($params['bank_code']);
                }
                if( $params['start_time'] && $params['end_time']){
                    $data['where']['affirm_time'] = array('BETWEEN', [strtotime($params['start_time']), strtotime($params['end_time'])+ 86400 ]);
                    $data['start_time']= $params['start_time'];
                    $data['end_time']= $params['end_time'];
                }
                $data['order'] = 'affirm_time desc';
                $apiAmount = "Base.FcModule.Payment.Order.amountPayment";
                $amount = $this->invoke($apiAmount, $data);
                $data['totalamount'] = $amount['response']['amount'];
                break;
            case '7':
                $start_time = $params['start_time'];
                $end_time 	= $params['end_time'];

                !empty($params['fc_code'])?$where['fc_code'] = $params['fc_code']:null;
                !empty($params['bank_code'])?$where['bank_code'] = $params['bank_code']:null;
                !empty($start_time) ? $where['affirm_time'] = ['egt' =>  strtotime($start_time)] : null;
                !empty($end_time)?$where['affirm_time'] = ['between',[strtotime($start_time), strtotime($end_time)+ 86400]] : null;
                $where['status'] = $params['status']=2;
                $where['sc_code'] = $params['sc_code'];
                $data['where'] = $where;
                $data['filename'] = 'paymentList';
                $data['title']    =  array('付款编号','交易流水号','收款金额','订单编号','客户名称','店铺名称','支付方式','买家付款时间','订单金额','买家实付','平台结算时间','备注');  //默认导出列标题
                $data['fields']   = 'fc_code,bank_code,amount,affirm_time,remark,sc_code';
                $callback_api = 'Com.Callback.Export.FcExport.payMentList';
                $data['order'] = 'create_time desc';
                $data['template_call_api'] = "Com.Callback.Export.Template.paymentList";
                break;
            default :
                $data['filename'] = 'waitList';
                $data['title']    = array('订单编号','商家名称','付款金额（元）','订单类型','支付方式','开户名','开户行','银行账号','付款状态');  //默认导出列标题
                $data['fields'] = 'adv.adv_code,b2b.b2b_code,b2b.real_amount as amount,voucher.pay_time,b2b.ext1,adv.pay_method_ext1,adv.amount as adv_amount,adv.pay_method as adv_pay_method,b2b.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,confirm.oc_code,confirm.sc_code,confirm.oc_type';
                $callback_api = 'Com.Callback.Export.FcExport.waitList';
                $data['template_call_api'] = "Com.Callback.Export.Template.waitList";
                $data['where']['confirm.status'] = 2;
                $data['where']['confirm.f_status'] = 1;
                $data['sql_flag'] =  'getOrderInfo';
                $data['order'] = 'confirm.update_time desc';
                $data['where'][] ="b2b.pay_status='PAY' OR adv.status='PAY'";
                $data['where'][]= "b2b.ext1='".PAY_METHOD_REMIT_CMB."' OR adv.pay_method_ext1='".PAY_METHOD_REMIT_CMB."'";
                if(!empty($params['sc_code'])){
                    $data['where']['confirm.sc_code'] = trim($params['sc_code']);
                }
                if(!empty($params['fc_code'])){
                    $data['where']['confirm.fc_code'] = trim($params['fc_code']);
                }
                if(!empty($params['oc_type'])){
                    $data['where']['confirm.oc_type'] = trim($params['oc_type']);
                }
                if(!empty($params['b2b_code'])){
                    $data['where']['confirm.b2b_code'] = trim($params['b2b_code']);
                }

                if(!empty($params['pay_method'])){
                    $data['where'][] = "b2b.pay_method='{$params['pay_method']}' OR adv.pay_method='{$params['pay_method']}'";
                }
                if(!empty($params['oc_type'])&&!empty($params['amount'])){
                    if($params['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
                        $data['where']['b2b.real_amount'] = $params['amount'];
                    }
                    if($params['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                        $data['where']['adv.amount'] = $params['amount'];
                    }
                }

                break;
        }
        $data['callback_api'] = $callback_api;
        $apiPath  = "Base.FcModule.Payment.Export.export";
        $res = $this->invoke($apiPath, $data);

        return $this->res($res['response'],$res['status'],'',$res['message']);
    }

    /**
     * Bll.Cms.Finance.Export.confirmExport
     * @param array $params
     * @return array
     */
    public function  confirmExport($params)
    {
        $fc_type = $params['fc_type'];
        $data = array();
        $b2b_code = !empty($params['b2b_code']) ? trim($params['b2b_code']) : '';
        $fc_code = !empty($params['fc_code']) ? trim($params['fc_code']) : '';
        $oc_code = !empty($params['oc_code']) ? trim($params['oc_code']) : '';
        $sc_code = !empty($params['sc_code']) ? trim($params['sc_code']) : '';
        $pay_no = !empty($params['pay_no']) ? trim($params['pay_no']) : '';
        $start_time = !empty($params['start_time']) ? strtotime($params['start_time']) : '';
        $end_time = !empty($params['end_time']) ? strtotime($params['end_time']): '';
        $pay_type = !empty($params['pay_type']) ? trim($params['pay_type']) : '';
        $amount = !empty($params['amount']) ? trim($params['amount']) : '';
        $pay_method = !empty($params['pay_method']) ? trim($params['pay_method']) : '';
        $pay_method_ext1 = !empty($params['pay_method_ext1']) ? trim($params['pay_method_ext1']) : '';
        $remit_code = !empty($params['remit_code']) ? trim($params['remit_code']) : '';
        $limit_start = !empty($params['limit_start']) ? intval($params['limit_start']) : 1;
        $limit_end = !empty($params['limit_end']) ? trim($params['limit_end']) : 1000;
        switch ($fc_type) {
            //点单列表
            /**待点单列表导出
            */
            case 'cNLists':
                //组装调用导出api参数
                $data['filename'] = $fc_type;
                $data['title'] = array('订单编号', '付款单号', '汇款码','商家名称','付款时间', '订单金额', '买家实付', '支付方式','支付流水号','银行实际到账','优惠金额','到账银行','银行流水号(入金)', '手续费（元）', '点单状态');  //默认导出列标题
                $data['fields'] = 'confirm.cost,b2b.b2b_code,b2b.real_amount as amount,b2b.ext1,b2b.pay_time,b2b.client_name,extend.commercial_name,b2b.pay_method,b2b.order_amout,
                                    voucher.pay_no,store.account_bank,store.account_name,store.account_no,store.name as sc_name,confirm.oc_code,confirm.bank_code,confirm.account_status,
                                    extend.remit_code,extend.coupon_amount';
                $data['order']        =  'ASCII(confirm.account_status) asc,b2b.create_time desc';
                $data['center_flag']  =  SQL_FC;//财务中心
                $data['sql_flag']     =  'getOrderGoodsInfo'; //sql标识
                $data['callback_api'] =  'Com.Callback.Export.FcExport.paymentOrderOnList';
                $data['template_call_api'] = 'Com.Callback.Export.Template.cNLists';
                $apiPath  =  "Com.Common.CommonView.Export.export";
                $data['where']        =  array();
                //支付方式条件，以后可能进行修改
                if(!empty($params['pay_method'])){
                    $data['where']['b2b.pay_method'] = $params['pay_method'];
                }
                if(!empty($params['pay_method_ext1'])){
                    $data['where']['b2b.ext1'] = $params['pay_method_ext1'];
                }

                $data['where'][] = "b2b.pay_status = (CASE WHEN (b2b.pay_status = 'UNPAY' AND b2b.pay_method = 'REMIT') THEN 'UNPAY'   ELSE 'PAY' END) ";

                ( $start_time && $end_time ) ?  $data['where']['b2b.pay_time'] = array('BETWEEN', [$start_time,$end_time+86400]) : null;
                if( !empty($sc_code)){
                    $data['where']['confirm.sc_code'] = $sc_code;
                }
                if( !empty($pay_no)){
                    $data['where']['voucher.pay_no'] = $pay_no;
                }
                if( !empty($fc_code)){
                    $data['where']['confirm.fc_code'] = $fc_code;
                }
                if( !empty($b2b_code)){
                    $data['where']['confirm.b2b_code'] = $b2b_code;
                }
                if( !empty($oc_code)){
                    $data['where']['confirm.oc_code'] = $oc_code;
                }
                if(!empty($remit_code)){
                    $data['where']['extend.remit_code'] = $remit_code;
                }
                if(!$pay_method){
                    $data['where'][] = "b2b.pay_method<>''";
                }
                if($amount){
                    $data['where']['b2b.real_amount'] = $amount;
                }
                $data['where'][] = "b2b.pay_method <>'ADVANCE'";
                $data['where']['confirm.status'] = 1;
                $data['where']['confirm.f_status'] = 1;
                $data['where']['confirm.oc_type'] ='GOODS';
                break;
            /**财务点单已点单导出
            */
            case 'cYLists':
                //组装调用导出api参数
                $data['filename'] = $fc_type;
                $data['title'] = array('订单编号', '付款单号', '汇款码','商家名称','付款时间', '订单金额', '买家实付',  '支付方式','支付流水号','到账金额(元)','优惠金额','到账银行','银行流水号(入金)','手续费（元）', '确认时间','点单状态');  //默认导出列标题
                $data['fields'] = 'confirm.cost,b2b.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.ext1,
                                b2b.pay_time,b2b.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,
                                store.name as sc_name,confirm.oc_code,confirm.update_time,extend.remit_code,extend.coupon_amount,confirm.bank_code,confirm.account_status';
                $data['order']        =  'confirm.update_time desc';
                $data['center_flag']  =  SQL_FC;//财务中心
                $data['sql_flag']     =  'getOrderGoodsInfo'; //sql标识
                $data['callback_api'] =  'Com.Callback.Export.FcExport.paymentOrderOnList';
                $data['template_call_api'] = 'Com.Callback.Export.Template.cYLists';
                $apiPath  =  "Com.Common.CommonView.Export.export";
                $data['where']        =  array();
                //支付方式条件，以后可能进行修改
                $data['where']['b2b.pay_status'] = OC_ORDER_PAY_STATUS_PAY;
                if(!empty($params['pay_method'])){
                    $data['where']['b2b.pay_method'] = $params['pay_method'];
                }
                if(!empty($params['pay_method_ext1'])){
                    $data['where']['b2b.ext1'] = $params['pay_method_ext1'];
                }

                ( $start_time && $end_time ) ?  $data['where']['b2b.pay_time'] = array('BETWEEN', [$start_time,$end_time+86400]) : null;
                if( !empty($sc_code)){
                    $data['where']['confirm.sc_code'] = $sc_code;
                }
                if( !empty($pay_no)){
                    $data['where']['voucher.pay_no'] = $pay_no;
                }
                if( !empty($fc_code)){
                    $data['where']['confirm.fc_code'] = $fc_code;
                }
                if( !empty($b2b_code)){
                    $data['where']['confirm.b2b_code'] = $b2b_code;
                }
                if( !empty($oc_code)){
                    $data['where']['confirm.oc_code'] = $oc_code;
                }
                if(!empty($remit_code)){
                    $data['where']['extend.remit_code'] = $remit_code;
                }
                if($amount){
                    $data['where']['b2b.real_amount'] = $amount;
                }
                $data['where']['confirm.oc_type'] ='GOODS';
                $data['where']['confirm.status'] = 2;
                break;
            //预付款列表
            case 'aCLists':
                //组装调用导出api参数
                $data['filename'] = $fc_type;
                $data['title'] = array('订单编号', '付款单号', '汇款码','商家名称','付款时间', '充值金额', '支付方式', '支付流水号','银行实际到账','到账银行','银行流水号(入金)','手续费（元）', '点单状态');  //默认导出列标题
                $data['fields'] = 'confirm.cost,confirm.b2b_code,adv.pay_method_ext1,adv.amount,adv.pay_time,adv.client_name,adv.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,store.name as sc_name,confirm.oc_code,confirm.sc_code,adv.remit_code,confirm.bank_code,confirm.account_status';
                $data['order']        =  'confirm.update_time desc,adv.create_time desc';
                $data['center_flag']  =  SQL_FC;//财务中心
                $data['sql_flag']     =  'getAdvanceInfo'; //sql标识
                $data['callback_api'] =  'Com.Callback.Export.FcExport.paymentOrderNoSure';
                $data['template_call_api'] = 'Com.Callback.Export.Template.aCLists';
                $apiPath  =  "Com.Common.CommonView.Export.export";
                $data['where']        =  array();
                //支付方式条件，以后可能进行修改
//                if(!empty($params['b2b_code']) && is_array($params['b2b_code']) ){
//                    $orderWhere['confirm.b2b_code'] = array('in',$params['b2b_code']);
//                }else if(!empty($params['b2b_code'])){
//                    $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
//                }
                ( $start_time && $end_time ) ?  $data['where']['adv.pay_time'] = array('BETWEEN', [$start_time,$end_time+ 86400 ]) : null;
                if( !empty($sc_code)){
                    $data['where']['confirm.sc_code'] = $sc_code;
                }
                if( !empty($pay_no)){
                    $data['where']['voucher.pay_no'] = $pay_no;
                }
                if( !empty($fc_code)){
                    $data['where']['confirm.fc_code'] = $fc_code;
                }
                if( !empty($b2b_code)){
                    $data['where']['confirm.b2b_code'] = $b2b_code;
                }
                if( !empty($oc_code)){
                    $data['where']['confirm.oc_code'] = $oc_code;
                }
                if(!empty($remit_code)){
                    $data['where']['extend.remit_code'] = $remit_code;
                }
                if(!empty($amount)){
                    $data['where']['adv.amount'] = $amount;
                }
                if(!empty($pay_method)){
                    $data['where']['adv.pay_method'] = $pay_method;
                    if($pay_method == 'REMIT' && !empty($pay_method_ext1)){
                        $data['where']['adv.pay_method_ext1'] = $pay_method_ext1;
                    }
                }
                $data['where'][] = "adv.status = (CASE WHEN (adv.status = 'UNPAY' AND adv.pay_method = 'REMIT' ) THEN 'UNPAY'  WHEN ( adv.status = 'UNPAY'   AND adv.pay_method = 'REMIT' ) THEN 'NORMAL' ELSE 'PAY' END) ";

                $data['where']['confirm.oc_type'] = 'ADVANCE';
                $data['where']['confirm.status'] = 1;
                break;

            case 'aYLists':
                //组装调用导出api参数
                $data['filename'] = $fc_type;
                $data['title'] = array('订单编号', '付款单号', '汇款码','商家名称','付款时间', '充值金额','支付方式', '支付流水号', '银行实际到账','到账银行','银行流水号(入金)','手续费（元）','确认时间', '点单状态');  //默认导出列标题
                $data['fields'] = 'confirm.cost,confirm.b2b_code,adv.pay_method_ext1,adv.amount,adv.pay_time,adv.client_name,adv.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,store.name as sc_name,confirm.oc_code,confirm.sc_code,adv.remit_code,confirm.update_time,confirm.bank_code,confirm.account_status';
                $data['order']        =  'confirm.update_time desc';
                $data['center_flag']  =  SQL_FC;//财务中心
                $data['sql_flag']     =  'getAdvanceInfo'; //sql标识
                $data['callback_api'] =  'Com.Callback.Export.FcExport.paymentOrderSure';
                $data['template_call_api'] = 'Com.Callback.Export.Template.aYLists';
                $apiPath  =  "Com.Common.CommonView.Export.export";
                $data['where']        =  array();

//                if(!empty($params['b2b_code']) && is_array($params['b2b_code']) ){
//                    $orderWhere['confirm.b2b_code'] = array('in',$params['b2b_code']);
//                }else if(!empty($params['b2b_code'])){
//                    $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
//                }
                ( $start_time && $end_time ) ?  $data['where']['adv.pay_time'] = array('BETWEEN', [$start_time,$end_time+ 86400 ]) : null;
                if( !empty($sc_code)){
                    $data['where']['confirm.sc_code'] = $sc_code;
                }
                if( !empty($pay_no)){
                    $data['where']['voucher.pay_no'] = $pay_no;
                }
                if( !empty($fc_code)){
                    $data['where']['confirm.fc_code'] = $fc_code;
                }
                if( !empty($b2b_code)){
                    $data['where']['confirm.b2b_code'] = $b2b_code;
                }
                if( !empty($oc_code)){
                    $data['where']['confirm.oc_code'] = $oc_code;
                }
                if(!empty($remit_code)){
                    $data['where']['extend.remit_code'] = $remit_code;
                }
                if(!empty($amount)){
                    $data['where']['adv.amount'] = $amount;
                }
                if(!empty($pay_method)){
                    $data['where']['adv.pay_method'] = $pay_method;
                    if($pay_method == 'REMIT' && !empty($pay_method_ext1)){
                        $data['where']['adv.pay_method_ext1'] = $pay_method_ext1;
                    }
                }
                $data['where']['confirm.oc_type'] = 'ADVANCE';
                $data['where']['confirm.status'] = 2;
                break;
        }

        $res = $this->invoke($apiPath, $data);
//        var_dump($res);die;
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }
    /**
     * Bll.Cms.Finance.Export.confirmAllExport
     * @param array $params
     * @return array
     */
    public function confirmAllExport($params){
        $fc_type = $params['fc_type'];
        $data = array();
        $b2b_code = !empty($params['b2b_code']) ? trim($params['b2b_code']) : '';
        $fc_code = !empty($params['fc_code']) ? trim($params['fc_code']) : '';
        $oc_code = !empty($params['oc_code']) ? trim($params['oc_code']) : '';
        $sc_code = !empty($params['sc_code']) ? trim($params['sc_code']) : '';
        $pay_no = !empty($params['pay_no']) ? trim($params['pay_no']) : '';
        $start_time = !empty($params['start_time']) ? strtotime($params['start_time']) : '';
        $end_time = !empty($params['end_time']) ? strtotime($params['end_time']) : '';
        $pay_method = !empty($params['pay_method']) ? trim($params['pay_method']) : '';
        $pay_method_ext1 = !empty($params['pay_method_ext1']) ? trim($params['pay_method_ext1']) : '';
        $remit_code = !empty($params['remit_code']) ? trim($params['remit_code']) : '';
        $amount =  !empty($params['amount']) ? trim($params['amount']) : '';
        $limit_start = !empty($params['limit_start']) ? intval($params['limit_start']) : 1;
        $limit_end = !empty($params['limit_end']) ? trim($params['limit_end']) : 1000;
        $data['filename'] = $fc_type;
        $data['title'] =  $data['title'] = array('序号','订单编号', '订单金额', '支付时间','支付渠道','预计手续费率', '预计手续费金额', '预计到账金额', '平台账户到账金额','优惠金额', '手续费金额','手续费差', '支付金额',
            '支付流水号','汇款码','支付方名称','支付方店铺名称','商户名称','开户名','开户行','银行账号'
        );  //默认导出列标题
        $data['fields'] = 'b2b.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,
                    voucher.pay_no,extend.commercial_name,extend.coupon_amount,b2b.pay_method,b2b.ext1,store.account_bank,store.account_name,
                    store.account_no,store.name as sc_name,confirm.oc_code,extend.remit_code,member.commercial_name,member.name';
        $data['order']        =  'b2b.create_time desc';
        $data['center_flag']  =  SQL_FC;//财务中心
        $data['sql_flag']     =  'getOrderGoodsAllInfo'; //sql标识
        $data['callback_api'] =  'Com.Callback.Export.FcExport.cNAllLists';
        $data['template_call_api'] = 'Com.Callback.Export.Template.cNAllLists';
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $data['where']        =  array();
        //支付方式条件，以后可能进行修改
        if(!empty($pay_method) && !empty($pay_method_ext1)){
            $data['where']['b2b.ext1'] = $pay_method_ext1;
        }elseif(!empty($pay_method) && empty($pay_method_ext1)){
            $data['where']['b2b.pay_method'] = $pay_method;
        }
        ( $start_time && $end_time ) ?  $data['where']['b2b.pay_time'] = array('BETWEEN', [$start_time,$end_time+86400]) : null;
        if( !empty($sc_code)){
            $data['where']['confirm.sc_code'] = $sc_code;
        }
        if( !empty($pay_no)){
            $data['where']['voucher.pay_no'] = $pay_no;
        }
        if( !empty($fc_code)){
            $data['where']['confirm.fc_code'] = $fc_code;
        }
        if( !empty($b2b_code)){
            $data['where']['confirm.b2b_code'] = $b2b_code;
        }
        if( !empty($oc_code)){
            $data['where']['confirm.oc_code'] = $oc_code;
        }
        if(!empty($remit_code)){
            $data['where']['extend.remit_code'] = $remit_code;
        }
        if(!empty($remit_code)){
            $data['where']['extend.remit_code'] = $remit_code;
        }
        if($amount){
            $data['where']['b2b.real_amount'] = $amount;
        }
        $data['where']['confirm.oc_type'] ='GOODS';
        $data['where']['confirm.status'] = 2;
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }

    /**
     * Bll.Cms.Finance.Export.advanceAllExport
     * @param array $params
     * @return array
     */
    public function advanceAllExport($params){
        $fc_type = $params['fc_type'];
        $data = array();
        $b2b_code = !empty($params['b2b_code']) ? trim($params['b2b_code']) : '';
        $fc_code = !empty($params['fc_code']) ? trim($params['fc_code']) : '';
        $oc_code = !empty($params['oc_code']) ? trim($params['oc_code']) : '';
        $sc_code = !empty($params['sc_code']) ? trim($params['sc_code']) : '';
        $pay_no = !empty($params['pay_no']) ? trim($params['pay_no']) : '';
        $start_time = !empty($params['start_time']) ? strtotime($params['start_time']) : '';
        $end_time = !empty($params['end_time']) ? strtotime($params['end_time']) : '';
        $pay_method = !empty($params['pay_method']) ? trim($params['pay_method']) : '';
        $remit_code = !empty($params['remit_code']) ? trim($params['remit_code']) : '';
        $amount =  !empty($params['amount']) ? trim($params['amount']) : '';
        $limit_start = !empty($params['limit_start']) ? intval($params['limit_start']) : 1;
        $limit_end = !empty($params['limit_end']) ? trim($params['limit_end']) : 1000;
        $data['filename'] = $fc_type;
        $data['title'] =  $data['title'] = array('序号','订单编号', '订单金额', '支付时间','支付渠道','预计手续费率', '预计手续费金额', '预计到账金额', '平台账户到账金额', '手续费金额','手续费差', '支付金额',
            '支付流水号','汇款码','支付方名称','支付方店铺名称','商户名称','开户名','开户行','银行账号'
        );  //默认导出列标题
        $data['fields'] = 'adv.adv_code,adv.amount,adv.pay_time,adv.client_name,voucher.pay_no,extend.commercial_name,adv.pay_method,adv.pay_method_ext1 as ext1,store.account_bank,store.account_name,store.account_no,store.name as sc_name,confirm.oc_code,adv.remit_code,member.commercial_name,member.name';
        $data['order']        =  'adv.create_time desc';
        $data['center_flag']  =  SQL_FC;//财务中心
        $data['sql_flag']     =  'getAdvanceAllInfo'; //sql标识
        $data['callback_api'] =  'Com.Callback.Export.FcExport.advanceAllLists';
        $data['template_call_api'] = 'Com.Callback.Export.Template.advanceAllLists';
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $data['where']        =  array();
        //支付方式条件，以后可能进行修改
        ( $start_time && $end_time ) ?  $data['where']['adv.pay_time'] = array('BETWEEN', [$start_time,$end_time+86400]) : null;
        if( !empty($sc_code)){
            $data['where']['confirm.sc_code'] = $sc_code;
        }
        if( !empty($pay_no)){
            $data['where']['voucher.pay_no'] = $pay_no;
        }
        if( !empty($fc_code)){
            $data['where']['confirm.fc_code'] = $fc_code;
        }
        if( !empty($b2b_code)){
            $data['where']['confirm.b2b_code'] = $b2b_code;
        }
        if( !empty($oc_code)){
            $data['where']['confirm.oc_code'] = $oc_code;
        }
        if(!empty($remit_code)){
            $data['where']['extend.remit_code'] = $remit_code;
        }
        if(!empty($amount)){
            $data['where']['adv.amount'] = $amount;
        }
        if(!empty($pay_method)){
            $data['where']['adv.pay_method'] = $pay_method;
        }
        $data['where']['confirm.oc_type'] = 'ADVANCE';
        $data['where']['confirm.status'] = 2;
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }




}

?>
