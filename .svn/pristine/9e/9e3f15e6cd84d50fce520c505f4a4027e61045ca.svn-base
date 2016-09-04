<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单统计相关
 */

namespace Com\DataCenter\Statistic;

use System\Base;
class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Com.DataCenter.Statistic.Order.getNumsByStatus
     * 根据时间获取订单下单数
     * @param type $params
     */
    public function getNumsByStatus($params){

        //查询的内容
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('status', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('start_time', 'require', PARAMS_ERROR, HAVEING_CHECK), //开始时间
            array('end_time', 'require', PARAMS_ERROR, HAVEING_CHECK), //结束时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $orderInfoModel = M('Base.OrderModule.B2b.OrderInfo.buildStatusWhere');
        $sc_code     =  $params['sc_code'];
        $start_time  =  $params['start_time'];
        $end_time    =  $params['end_time'];
        $status      =  $params['status'];
        
       $where['sc_code'] = $params['sc_code'];
       !empty($start_time) && empty($end_time)   &&   $where['create_time']   =    array('egt',$start_time);
       !empty($end_time)   && empty($start_time) &&   $where['create_time']   =    array('elt',$end_time);
       !empty($start_time) && !empty($end_time)  &&   $where['create_time']   =    array('between',array($start_time,$end_time));
       
       if(!empty($status)){
           $status_where = $orderInfoModel->buildStatusWhere($status);
           if(!empty($status_where)){
             $where['_complex'] = $status_where;
           }
       }
       
       $res = D('OcB2bOrder')->alias('obo')->field('count(*) as total_order_num,sum(real_amount) as total_real_amount')->where($where)->find();
       if(FALSE === $res){
           return $this->res(NULL,10000);
       }
       return $this->res($res);
    }
    
    /**
     * Com.DataCenter.Statistic.Order.deal
     * 获取成交金额及下单数
     * @param type $params
     */
    public function deal($params){
        //查询的内容
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('status', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('start_time', 'require', PARAMS_ERROR, HAVEING_CHECK), //开始时间
            array('end_time', 'require', PARAMS_ERROR, HAVEING_CHECK), //结束时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code     =  $params['sc_code'];
        $start_time  =  $params['start_time'];
        $end_time    =  $params['end_time'];
        $status      =  $params['status'];
        
       $where['sc_code'] = $params['sc_code'];
       !empty($start_time) && empty($end_time)   &&   $where['create_time']   =    array('egt',$start_time);
       !empty($end_time)   && empty($start_time) &&   $where['create_time']   =    array('elt',$end_time);
       !empty($start_time) && !empty($end_time)  &&   $where['create_time']   =    array('between',array($start_time,$end_time));
       
       $status_where  = array();
       //线上支付  已支付的
       $status_where[] = array('pay_status'=>OC_ORDER_PAY_STATUS_PAY,'pay_method'=>array('neq',PAY_METHOD_OFFLINE_COD));
       //线下支付  未取消的
       $status_where[] = array('order_status'=>array('not in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL)),'pay_method'=>PAY_METHOD_OFFLINE_COD);
       $status_where['_logic'] = 'or';
       $where['_complex'] = $status_where;
       $res = D('OcB2bOrder')->field('count(*) as total_order_num,sum(real_amount) as total_real_amount')->where($where)->find();
       if(FALSE === $res){
           return $this->res(NULL,10001);
       }
       return $this->res($res);
    }
    
}




?>
