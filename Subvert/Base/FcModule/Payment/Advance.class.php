<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: hp-wangguangjian <wangguangjian@yunputong.com >
 * +---------------------------------------------------------------------
 * | 商品相关模块
 */
namespace Base\FcModule\Payment;

use System\Base;

class Advance extends Base{
    public function __construct() {
        parent::__construct();
    }


    /**
     * Base.FcModule.Payment.Advance.getLists
     * 查询财务预付款未完成订单中的数据
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */
     public function getLists($params){
         $this->_rule = array(
             array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  订单号，唯一       非必须参数, 默认值 所有
             array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),            //  店铺编号            非必须参数, 默认值 所有
             array('pay_no', 'require', PARAMS_ERROR, ISSET_CHECK),             //  支付流水号          非必须参数, 默认值 所有
             array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),         //  支付方式		   非必须参数, 默认值 所有
             array('pay_method_ext1', 'require', PARAMS_ERROR, ISSET_CHECK),         //  支付银行		   非必须参数, 默认值 所有
             array('oc_code', 'require', PARAMS_ERROR, ISSET_CHECK),            //  订单支付编码 唯一  非必须参数, 默认值 所有
             array('remit_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //汇款码               非必须参数, 默认值 所有
             array('amount', 'require', PARAMS_ERROR, ISSET_CHECK),
             array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			   非必须参数, 默认值 所有
             array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			   非必须参数, 默认值 所有
             array('status', 'require', PARAMS_ERROR, MUST_CHECK),         //       确认状态            必须参数
         );

         // 自动校验
         if (!$this->checkInput($this->_rule, $params)) {
             return $this->res($this->getErrorField(), $this->getCheckError());
         }

         $orderWhere = array();
         $start_time = $params['start_time'];
         $end_time = $params['end_time'];
         $status = $params['status'];
         !empty($start_time) && empty($end_time) && $orderWhere['adv.pay_time'] = array('egt', $start_time);
         !empty($end_time) && empty($start_time) && $orderWhere['adv.pay_time'] = array('elt', $end_time);
         !empty($start_time) && !empty($end_time) && $orderWhere['adv.pay_time'] = array('between', array($start_time, $end_time+ 86400));
         $total_amount = empty($params['total_amount']) ? 'NO' : $params['total_amount'];
         if(!empty($params['b2b_code']) && is_array($params['b2b_code']) ){
             $orderWhere['confirm.b2b_code'] = array('in',$params['b2b_code']);
         }else if(!empty($params['b2b_code'])){
             $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
         }
         if(!empty($params['sc_code'])){
             $orderWhere['confirm.sc_code'] = $params['sc_code'];
         }
         if(!empty($params['pay_no'])){
             $orderWhere['voucher.pay_no'] = $params['pay_no'];
         }
         if(!empty($params['pay_method'])){
             $orderWhere['adv.pay_method'] = $params['pay_method'];
         }
         if(!empty($params['fc_code'])){
             $orderWhere['confirm.fc_code'] = $params['fc_code'];
         }
         if(!empty($params['remit_code'])){
             $orderWhere['adv.remit_code'] = $params['remit_code'];
         }
         if(!empty($params['oc_code'])){
             $orderWhere['confirm.oc_code'] = $params['oc_code'];
         }
         if(!empty($params['amount'])){
             $orderWhere['adv.amount'] = $params['amount'];
         }
         if(!empty($params['pay_method_ext1'])){
             $orderWhere['adv.pay_method_ext1']=$params['pay_method_ext1'];
         }
         if($total_amount == 'YES'){
             $aggre = array(
                 array('sum','adv.amount','total_amount'),
             );
             $orderParams['aggre'] = $aggre;
         }

         # 固定条件
         $orderWhere['confirm.oc_type'] = FC_ORDER_CONFIRM_OC_TYPE_ADVANCE;

         $page = isset($params['page']) ? $params['page'] : 1;
         $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
         $orderWhere['confirm.status'] = $status;

         $fileConfirm = 'confirm.cost,confirm.b2b_code,adv.amount,adv.pay_time,adv.client_name,adv.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,store.name as sc_name,confirm.oc_code,confirm.sc_code,adv.remit_code,adv.pay_method_ext1,confirm.bank_code,confirm.third_status,confirm.account_status';
         $apiPath = "Com.Common.CommonView.Lists.Lists";
         $orderParams['center_flag'] = SQL_FC;
         $orderParams['sql_flag'] = 'getAdvanceInfo';
         $orderParams['fields'] = $fileConfirm;
         $orderParams['page'] = $page;
         if($status == '2'){
            $orderParams['order'] = "confirm.update_time desc";

           }else{
             $orderWhere[] = "adv.status = (CASE WHEN (adv.status = 'UNPAY' AND adv.pay_method = 'REMIT' ) THEN 'UNPAY'  ELSE 'PAY' END) ";
             $orderParams['order'] = "confirm.update_time desc,adv.create_time desc";
         }
         $orderParams['page_number'] = $pageNumber;
         $orderParams['where'] = $orderWhere;
         $orderRes = $this->invoke($apiPath, $orderParams);
         if ( 0 != $orderRes['status']) {
             return $this->res(null, 4513);
         }
         return $this->res($orderRes['response']);
     }


    /**
     * Base.FcModule.Payment.Advance.updateConfirm
     *更新confirm表的数据（在汇总确定付款时必须更新fc_code字段）
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */

    public function updateConfirmStatus($params){
        $this->startOutsideTrans();  //必须开始事务
        $this->_rule = array(
            array('status', 'require', PARAMS_ERROR, MUST_CHECK),           //          状态码,          必须参数
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //        商铺编码           非必须参数
            array('balance_status', 'require', PARAMS_ERROR, ISSET_CHECK),           //        资金状态码          非必须参数
            array('confirm_name', 'require', PARAMS_ERROR, MUST_CHECK),           //        登录名称          非必须参数
        );
         //自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where['b2b_code'] = array('in',$params['b2b_code']);
        if(!empty($sc_code)){
            $where['sc_code'] = $params['sc_code'];
        }
        $data['status'] = $params['status'];
        $data['balance_status'] = $params['balance_status'];
        $data['confirm_name'] = $params['confirm_name'];
        $data['update_time'] = NOW_TIME;
        $res = D('FcOrderConfirm')->where($where)->data($data)->setField($data);
        if($res){
            return $this->res($res);
        }else{
            return $this->res('更新失败');
        }

    }


}
