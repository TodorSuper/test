<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单
 */

namespace Base\OrderModule\Center;

use System\Base;

class Order extends Base {

    private $_rule = null;
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 修改订单支付码
     * Base.OrderModule.Center.Order.changeOpCode
     * @param type $params
     */
    public function changeOpCode($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $op_code = $params['op_code'];
        $first_num = substr($op_code, 0, 1);

        switch ($first_num) {
            case OC_ORDER:                 # 订单
                //生成新的op_code 
                $new_op_code = $this->generateOrderCode(OC_ORDER, OC_B2B_ORDER_OP);

                //更新ordr表的op_code
                $res = D('OcB2bOrder')->where(array('op_code' => $op_code))->save(array('update_time' => NOW_TIME, 'op_code' => $new_op_code));
                if ($res <= 0) {
                    return $this->res(NULL, 6027);
                }
                //更新order extend 表
                $res = D('OcB2bOrderExtend')->where(array('op_code' => $op_code))->save(array('op_code' => $new_op_code));

                if ($res <= 0) {
                    return $this->res(NULL, 6028);
                }

                break;

            case OC_ADVANCE_ORDER:                 # 预付款订单
                //生成新的op_code 
                $new_op_code = $this->generateOrderCode(OC_ADVANCE_ORDER, OC_ADVANCE_ORDER_OP);

                // 更新oc_advance表op_code
                $res = D('OcAdvance')->where(array('op_code' => $op_code))->save(array('update_time' => NOW_TIME, 'op_code' => $new_op_code));

                if ($res <= 0) {
                    return $this->res(NULL, 6038);
                }

                break;
        }
        return $this->res($new_op_code);
    }

    /**
     * 获取订单编码
     * @access private
     */
    private function generateOrderCode($busType, $preBusType) {

        $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
        $data = array(
            'busType' => $busType,
            'preBusType' => $preBusType,
            'codeType' => SEQUENCE_ORDER,
        );
        $res = $this->invoke($apiPath, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return $res['response'];
    }

    /**
     * 财务点单支付回调接口
     * Base.OrderModule.Center.Order.remit
     */
    public function remit($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('b2b_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), # 订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $b2b_codes = $params['b2b_codes'];

        
        $advance_code = array();
        $b2b_code = array();
        //区分订单号
        foreach ($b2b_codes as $k => $v) {
            $first_num = substr($v, 0, 1);
            if ($first_num == OC_ORDER) {
                //电商订单
                $b2b_code[] = $v;
            } else if ($first_num == OC_ADVANCE_ORDER) {
                //预付款订单 
                $advance_code[] = $v;
            }
        }
        if(!empty($b2b_code)){
           $b2b_res = $this->updateB2bOrder($b2b_code);
           if($b2b_res === FALSE){
               return $this->res(NULL,6044);
           }
        }
        
        if(!empty($advance_code)){
           $adv_res =  $this->updateAdvanceOrder($advance_code);
        }
        
        return $this->res($b2b_res + $adv_res);
    }

    private function addAction($b2b_code, $order_info) {
        $apiPath = "Base.OrderModule.B2b.OrderAction.orderActionUp";
        foreach ($b2b_code as $k => $v) {
            $status = '';
            //订单操作记录
            $data = array(
                'b2b_code' => $v,
                'uc_code' => '',
                'pay_method' => PAY_METHOD_ONLINE_REMIT,
                'pay_type' => $order_info[$v]['pay_type'],
            );
            if ($order_info[$v]['pay_type'] == PAY_TYPE_COD || $order_info[$v]['pay_type'] == PAY_TYPE_TERM) {
                $status = OC_ORDER_GROUP_STATUS_COMPLETE;
            } else if ($order_info[$v]['pay_type'] == PAY_TYPE_ONLINE) {
                $status = OC_ORDER_GROUP_STATUS_UNSHIP;
            }
            $data['status'] = $status;
            $action_res = $this->invoke($apiPath, $data);
            if ($action_res['status'] != 0) {
                return $action_res['status'];
            }
        }
        
        return TRUE;
    }
    
    
   //更新电商订单状态
    private function updateB2bOrder($b2b_code){
        if(empty($b2b_code)){
            return TRUE;
        }
        
        //如果包含电商订单  则  获取订单相关信息
        if (!empty($b2b_code)) {
            $b2b_order_list = D('OcB2bOrder')->alias('obo')->where(array('b2b_code' => array('in', $b2b_code, 'pay_method' => PAY_METHOD_ONLINE_REMIT,'pay_status'=>OC_ORDER_PAY_STATUS_UNPAY)))
                    ->join("{$this->tablePrefix}oc_b2b_order_extend as oboe ON obo.op_code=oboe.op_code",'LEFT')
                    ->field('obo.b2b_code,obo.op_code,obo.pay_type,obo.sc_code,obo.ship_method,oboe.pick_up_code,oboe.mobile')
                    ->select();

            if(!empty($b2b_order_list)){  
                $b2b_code = array_column($b2b_order_list, 'b2b_code');
                $order_info = changeArrayIndex($b2b_order_list, 'b2b_code');
                $action_res =   $this->addAction($b2b_code, $order_info);
                if($action_res !== TRUE){
                    return FALSE;
                }
            }else {
                return true;
            }
        }
        $order_b2b = array();
        foreach($order_info as $k=>$v){
            //推送到BOSS  提示发货
            if($v['pay_type'] == PAY_TYPE_ONLINE){
                //立即支付 则推送boss
                $this->push_queue('Com.Callback.Push.Queue.unshipWarn', array('sc_code'=>$v['sc_code']),0);
                if($v['ship_method'] == SHIP_METHOD_PICKUP){
                    //如果是买家自提  则需要发送短信
                    $this->pickUpMessage($v['pick_up_code'], $v['mobile']);
                }
            }
            
            $order_b2b[$v['pay_type']][] = $v['b2b_code'];
        }
        $model = M('Base.OrderModule.B2b.Status');
//        $where['b2b_code'] = array('in', $b2b_codes);
        $where['pay_method'] = PAY_METHOD_ONLINE_REMIT;
        if(isset($order_b2b[PAY_TYPE_COD]) && !empty($order_b2b[PAY_TYPE_COD])){  //货到付款 
            $prev_status = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER, PAY_TYPE_COD);
            $prev_status['b2b_code'] = array('in', $order_b2b[PAY_TYPE_COD]);
            $prev_status['pay_type'] = PAY_TYPE_COD;
            $prev_where = array_merge($where,$prev_status);
            $update_data = $model->groupToDetail(OC_ORDER_GROUP_STATUS_COMPLETE, PAY_TYPE_COD);
            $update_data['update_time'] = NOW_TIME;
            $update_data['pay_time'] = NOW_TIME;
            $res1 = D('OcB2bOrder')->where($prev_where)->save($update_data);
        }
        if(isset($order_b2b[PAY_TYPE_TERM]) && !empty($order_b2b[PAY_TYPE_TERM])){  //账期支付
            $prev_status = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER, PAY_TYPE_TERM);
            $prev_status['b2b_code'] = array('in', $order_b2b[PAY_TYPE_TERM]);
            $prev_status['pay_type'] = PAY_TYPE_TERM;
            $prev_where = array_merge($where,$prev_status);
            $update_data = $model->groupToDetail(OC_ORDER_GROUP_STATUS_COMPLETE, PAY_TYPE_TERM);
            $update_data['update_time'] = NOW_TIME;
            $update_data['pay_time'] = NOW_TIME;
            $res2 = D('OcB2bOrder')->where($prev_where)->save($update_data);
        }  
        if(isset($order_b2b[PAY_TYPE_ONLINE]) && !empty($order_b2b[PAY_TYPE_ONLINE])){  //立即付款
            $prev_status = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_ONLINE);
            $prev_status['b2b_code'] = array('in', $order_b2b[PAY_TYPE_ONLINE]);
            $prev_status['pay_type'] = PAY_TYPE_ONLINE;
            $prev_where = array_merge($where,$prev_status);
            $update_data = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNSHIP, PAY_TYPE_ONLINE);
            $update_data['update_time'] = NOW_TIME;
            $update_data['pay_time'] = NOW_TIME;
            $res3 = D('OcB2bOrder')->where($prev_where)->save($update_data);
        }
        
        return $res1 + $res2 + $res3;
    }
    
    //更新预付款订单状态
    private function updateAdvanceOrder($adv_code){
        if(empty($adv_code)){
            return ;
        }

        // 查询预付款订单基本数据
        $map['adv_code'] = array('in',$adv_code);
        $map['status']   = TC_PAY_VOUCHER_UNPAY;
        $map['pay_method'] = PAY_METHOD_ONLINE_REMIT;
        $advance = D('OcAdvance')->where($map)->select();
        $num = 0;

        foreach ($advance as $k => $v) {

            $uc_code         = $v['uc_code'];        # 用户编码
            $adv_code        = $v['adv_code'];       # 预付款订单编码
            $op_code         = $v['op_code'];        # 预付款支付编码
            $amount          = $v['amount'];         # 预付款支付金额
            $client_name     = $v['client_name'];    # 客户名
            $operator_ip     = $v['operator_ip'];    # 客户IP
            $update_time     = $v['update_time'];    # 更新时间
            $pay_method_ext1 = $v['pay_method_ext1'];# 支付银行
            $status          = $v['status'];         # 订单状态
            $sc_code         = $v['sc_code'];        # 商家编码

            // 判断时间版本  并且更新订单
            $where['status']   = "UNPAY";
            $where['adv_code'] = $adv_code;
            $where['pay_method'] = PAY_METHOD_ONLINE_REMIT;
            $where['update_time'] = $update_time;

            $change_num =  D('OcAdvance')->where($where)->save(array('status'=>'PAY','update_time'=>NOW_TIME+1,'pay_time'=>NOW_TIME));

            if($change_num == 0){
                continue;
            }   

            // 获取账户信息 取得tc_code
            $data = array(
                [
                    'code'     =>$uc_code,
                    'isPcode' => 'NO',
                 ],
                );
            $apiPath = "Base.TradeModule.Account.Details.getAccontListByCode";
            $account_res = $this->invoke($apiPath,$data);
            $tc_code = $account_res['response'][$uc_code]['tc_code'];


             // 增加资金帐户余额
            $balance_data = array(
                'tc_code'      =>$tc_code,             # 资金帐户编码
                'oc_code'      =>$adv_code,            # 订单中心唯一编码
                'operatorType' =>'ADVANCE_RECHARGE',   # 增加金额的增加类型
                'operatorName' =>$client_name,         # 操作员名称
                'operatorIp'   =>$operator_ip,         # 操作员 ip
                'amount'       =>$amount,              # 操作金额
                );
            $apiPath = "Base.TradeModule.Account.Balance.incr";
            $balance_res = $this->invoke($apiPath,$balance_data);


            // POP订货会客户资金增加或更新
            $customer_data = array(
                'sc_code'=>$sc_code,
                'uc_code'=>$uc_code,
                'advance_money'=>$amount,
                'type'=>'add',
                );
            $apiPath = "Base.SpcModule.Commodity.Customer.add";
            $customer_res = $this->invoke($apiPath,$customer_data);

            $num++;
        }

        
        return $num;
    }
    
    
     private function pickUpMessage($pick_up_code,$commercial_mobile){
        
        $message = "您的取货码是{$pick_up_code}，请勿泄露。";
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($commercial_mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,1);
    }
    
     /**
     * 财务点单支付回调接口
     * Base.OrderModule.Center.Order.changeBank
     */
    public function changeBank($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK), # 订单编码
            array('bank', 'require', PARAMS_ERROR, MUST_CHECK), # 订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $b2b_code = $params['b2b_code'];
        $bank = $params['bank'];
        if(!in_array($bank, array(PAY_METHOD_REMIT_CMB,PAY_METHOD_REMIT_CMBC))){
            return $this->res(NULL,4061);
        }
        
            $first_num = substr($b2b_code, 0, 1);
            if ($first_num == OC_ORDER) {
                //电商订单   更新银行
                $res = D('OcB2bOrder')->where(array('b2b_code'=>$b2b_code,'pay_method'=>PAY_METHOD_ONLINE_REMIT))->save(array('ext1'=>$bank,'update_time'=>NOW_TIME));
            
            } else if ($first_num == OC_ADVANCE_ORDER) {
                //预付款订单 更新银行
               $res = D('OcAdvance')->where(array('adv_code'=>$b2b_code,'pay_method'=>PAY_METHOD_ONLINE_REMIT))->save(array('pay_method_ext1'=>$bank,'update_time'=>NOW_TIME));
            }
            
            if($res === false ){
                return $this->res(FALSE,4062);
            }
            
            $apiPath = "Base.OrderModule.Center.Order.remit";
            $data = array('b2b_codes'=>array($b2b_code));
            $res = $this->invoke($apiPath,$data);
            return $this->res($res['response'],$res['status'],'',$res['message']);
    }

}
