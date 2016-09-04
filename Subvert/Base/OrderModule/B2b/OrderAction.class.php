<?php
/**
* +---------------------------------------------------------------------
* | www.yunputong.com 粮人网
* +---------------------------------------------------------------------
* | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
* +---------------------------------------------------------------------
* | Author: zhoulianlei <zhoulianlei@yunputong.com >
* +---------------------------------------------------------------------
* | b2b订单列状态更新
*/

namespace Base\OrderModule\B2b;

use System\Base;

class OrderAction extends Base {

    /**
     * Base.OrderModule.B2b.OrderAction.orderActionUp
     * 订单状态更新
     */
    public function orderActionUp($param) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('pay_method','require',PARAMS_ERROR,ISSET_CHECK),
            array('pay_type','require',PARAMS_ERROR,MUST_CHECK),
            array('action_name','require',PARAMS_ERROR,ISSET_CHECK),
            array('ship_method','require',PARAMS_ERROR,ISSET_CHECK),
            array('cancel_type', 'require', PARAMS_ERROR, ISSET_CHECK),   # 取消的类型
        );
        if (!$this->checkInput($this->_rule, $param)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $model = M('Base.OrderModule.B2b.Status.groupStatusList');
        $info['uc_code'] = $param['uc_code'];
        $pay_type = $param['pay_type'];
        $ship_method = $param['ship_method'] ? $param['ship_method'] : SHIP_METHOD_DELIVERY;
        $action_name = $param['action_name']; //操作名称
        //$arr = $model->groupStatusList();
        $data = array();
        $data['b2b_code'] = $param['b2b_code'];
       // $data['action'] = $arr[$param['status']];
        $staus = $model->groupToDetail($param['status'],$pay_type);
        $data['order_status'] = isset($param['order_status'])===false ?  is_array($staus['order_status'])?$staus['order_status'][$this->_request_sys_name] :$staus['order_status']:$param['order_status'];
        $data['ship_status']  = isset($param['ship_status'])===false ? $staus['ship_status']:$param['ship_status'];
        $data['pay_status']   = isset($param['pay_status'])===false ? $staus['pay_status']:$param['pay_status'];
        $action = $model->detailToGroup($data['order_status'],$data['ship_status'],$data['pay_status'],$pay_type,$ship_method);
        $action = $action['message'];
        $data['action'] = $action;
        $real_name = $this->getOperateName($param['uc_code']);
        $data['real_name'] = empty($real_name) ? '' : $real_name;
        $data['pay_method']  = empty($param['pay_method']) ? '' : $param['pay_method'];
        $data['pay_type'] = $pay_type;
        $data['create_time'] = NOW_TIME;
        $data['update_time'] = NOW_TIME;
        $data['uc_code'] = isset($param['uc_code']) ? $param['uc_code'] :'';
        $operate_flag = $param['operate_flag'];
        //如果有传入操作名称  则不需要再传入
        $action_name = empty($action_name) ? $this->getActionName($param['status'],$param['pay_type'],'',$ship_method) : $action_name;
        $data['action_name'] = $action_name;
        if($operate_flag=='auto'){
            $data['action_name'] = '系统确认收货';
        }
        if($param['cancel_type'] == 'AUTO' && $param['status'] == OC_ORDER_GROUP_STATUS_CANCEL){
            $data['action_name'] = "系统自动取消";
            $data['real_name']   = "粮人网";
        }

        if (D('OcB2bOrderAction')->add($data)) {
            return $this->res(true,0);
        } else {
            return $this->res(null,6024);
        }

    }
    /**
     * Base.OrderModule.B2b.OrderAction.getOrderStatus
     * 订单日志
     */
    public function getOrderStatus($param) {

        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('sc_code','require',PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $param)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $rows = D('OcB2bOrder')->where(array('sc_code'=>$param['sc_code'],'b2b_code'=>$param['b2b_code']))->find();
      
        if (empty($rows)) {
            $this->res(null);
        }
        if($param['trans']){
            $where=array();
            $where['b2b_code']=$param['b2b_code'];
            $where['action_name'] = array('neq','订单改价');
            $res = D('OcB2bOrderAction')->field('real_name,action,create_time,action_name')->order('id asc')->where($where)->select();
        }else{
            $res = D('OcB2bOrderAction')->field('real_name,action,create_time,action_name')->order('id asc')->where(array('b2b_code'=>$param['b2b_code']))->select();
        }
        return $this->res($res);
    }
    
    private function getActionName($status,$pay_type = '',$pay_method='',$ship_method=''){
        $action_name = '';
        if($this->_request_sys_name == B2B){
            if($status == OC_ORDER_GROUP_STATUS_UNPAY){
                $action_name = '买家下单';
            }else if($status == OC_ORDER_GROUP_STATUS_CANCEL){
                $action_name = '买家取消订单';
            }else if($status == OC_ORDER_GROUP_STATUS_UNSHIP ){
                //买家付款  或者  货到付款 或者 账期付款的 付款
                $action_name = '买家付款';
            }else if($status == OC_ORDER_GROUP_STATUS_TAKEOVER){
                $action_name = '买家确认收货';
            }else if($status == OC_ORDER_GROUP_STATUS_COMPLETE && $pay_type == PAY_TYPE_ONLINE){
                $action_name = '买家确认收货';
            }else if($status == OC_ORDER_GROUP_STATUS_COMPLETE && $pay_type != PAY_TYPE_ONLINE ){
                $action_name = '买家付款';
            }
        }else if($this->_request_sys_name == POP){
            if($status == OC_ORDER_GROUP_STATUS_CANCEL){
                $action_name = '商家取消订单';
            }else if($status == OC_ORDER_GROUP_STATUS_SHIPPED){
                $action_name = "商家订单发货";
            }else if($status == OC_ORDER_GROUP_STATUS_COMPLETE && $ship_method == SHIP_METHOD_DELIVERY){
                $action_name = '商家确认收款';
            }else if ( ($status == OC_ORDER_SHIP_STATUS_TAKEOVER || $status == OC_ORDER_GROUP_STATUS_COMPLETE ) &&  $ship_method == SHIP_METHOD_PICKUP) {
                $action_name = '商家自提验证';
            } 
        }else if($this->_request_sys_name == CMS ){
            if($status == OC_ORDER_GROUP_STATUS_UNSHIP || ( $status == OC_ORDER_GROUP_STATUS_COMPLETE  )){
                $action_name = "平台确认到账";
            }
        }
        
        
        return $action_name;
    }
    
    
    private function getOperateName($uc_code){
        $real_name = "";
        if($this->_request_sys_name == POP){
            $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
            $data = array('uc_code'=>$uc_code);
            $res = $this->invoke($apiPath,$data);
            $real_name = $res['response']['real_name'];
        }else if($this->_request_sys_name == CMS){
            $real_name = "粮人网";
        }else if($this->_request_sys_name == B2B){
            $info['uc_code'] = $uc_code;
            $api = 'Base.UserModule.User.Basic.getBasicUserInfo';
            $user_info = $this->invoke($api,$info);
            $real_name = empty($user_info['response']['real_name']) ? '' : $user_info['response']['real_name'] ;
        }
        
        return $real_name;
    }
}