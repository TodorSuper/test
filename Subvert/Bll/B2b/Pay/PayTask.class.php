<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 创建支付任务
 */

namespace Bll\B2b\Pay;

use System\Base;

class PayTask extends Base {
    private $pay_method_map = null;  //交易中心和订单中心支付方式映射
    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
        $this->pay_method_map = array(
            PAY_METHOD_ONLINE_ALIPAY=>ALIPAY_WAP,
            PAY_METHOD_ONLINE_WEIXIN=>WEIXIN_JSAPI_PAY,
            PAY_METHOD_ONLINE_UCPAY=>'UCPAY_DIRECT',
        );
    }

    /**
     * 创建支付任务
     * Bll.B2b.Pay.PayTask.createPayTask
     * @param type $params
     */
    public function createPayTask($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 用户编码
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK), # 订单编码
            array('open_id', 'require', PARAMS_ERROR, ISSET_CHECK), # open_id
            array('pay_method', 'require', PARAMS_ERROR, MUST_CHECK), # 支付方式
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $first_num = substr($params['op_code'],0,1);
        switch ($first_num) {
            case OC_ORDER:                                 # 订单创建支付任务
                $data = $this->orderPayTask($params);
                break;
            
            case OC_ADVANCE_ORDER:                         # 预付款订单创建支付任务
                $data = $this->advancePayTask($params);
                break;
        }

        $apiPath = "Base.TradeModule.Pay.Task.createPayTask";
        $task_res = $this->invoke($apiPath,$data);
        if($task_res['status'] != 0){
            return $this->endInvoke(NULL,$task_res['status']);
        }
        
        return $this->endInvoke($task_res['response'],$task_res['status']);
    }


    /**
     * 订单创建支付任务
     * @access private
     * @author Todor
     */

    private function orderPayTask($params){

        $op_code = $params['op_code'];
        $open_id = $params['open_id'];
        $uc_code = $params['uc_code'];
        $pay_method = $params['pay_method'];

        //获取支付总金额
        $apiPath = "Base.OrderModule.B2b.OrderInfo.simpleGet";
        $param = array(
            'uc_code'=>$uc_code,
			'op_code'=>$op_code,
        );
        $order_res = $this->invoke($apiPath,$param);
        if($order_res['status'] != 0){
            return $this->endInvoke(NULL,$order_res['status']);
        }
        
        $order_info = $order_res['response'];
        //如果改价了  则需要 修改支付码
        if($order_info['goods_amount'] != $order_info['before_goods_amount']){
            try{
                D()->startTrans();
                $apiPath = "Base.OrderModule.Center.Order.changeOpCode";
                $data = array(
                    'op_code'=>$op_code,
                );
                $res = $this->invoke($apiPath,$data);
                if($res['status'] != 0){
                    return $this->endInvoke(NULL,$res['status']);
                }
                $commit_res = D()->commit();
                if ($commit_res === FALSE) {
                    return $this->endInvoke(NULL, 17);
                }
                $op_code = $res['response'];
            } catch (\Exception $ex) {
                 D()->rollback();
                 return $this->endInvoke(NULL,6029);
            }
            
        }
		$data = array(
			'oc_code' => $order_info['b2b_code'],
            'op_code' => $op_code,
            'total_fee' => $order_info['total_real_amount'],
            'getway' => $this->pay_method_map[$pay_method],
            'open_id' => $open_id,
        );
        return $data;
    }


    /**
     * 预付款订单创建支付任务
     * @access private
     * @author Todor
     */

    private function advancePayTask($params){

        $op_code = $params['op_code'];
        $open_id = $params['open_id'];
        $uc_code = $params['uc_code'];
        $pay_method = $params['pay_method'];

        // 获取预付款金额
        $data = array(
            'op_code'=>$op_code,
            );

        $apiPath = "Base.OrderModule.Advance.Order.get";
        $order_res = $this->invoke($apiPath,$data);
        $total_fee = $order_res['response']['amount'];
		$data = array(
			'oc_code' => $order_res['response']['adv_code'],
            'op_code' => $op_code,
            'total_fee' => $total_fee,
            'getway' => $this->pay_method_map[$pay_method],
            'open_id' => $open_id,
            );
        return $data;

    }

    /**
     * 先锋支付相关操作
     * Bll.B2b.Pay.PayTask.pioneerPay
     * @param type $params
     */

    public function pioneerPay($params){
        $params['gateway'] = C("UC_PAY_DATA")['gateway'];
        switch ($params['type']) {
            case 'Create':                                 # 先锋认证支付
                $apiPath = "Base.OrderModule.B2b.Order.createPay";
                break;

            case 'BindList':                         # 绑定银行卡列表
                $apiPath = "Base.OrderModule.B2b.Order.bindList";
                break;

            case 'BankList':                        # 银行卡列表
                $apiPath = "Base.OrderModule.B2b.Order.bankList";
                break;

            case 'UnbindCard':                       # 取消银行卡绑定
                $apiPath = "Base.OrderModule.B2b.Order.unbindCard";
                break;
        }

        unset($params['type']);

        $order_res = $this->invoke($apiPath,$params);
        return $this->endInvoke($order_res['response']);
    }

}

?>
