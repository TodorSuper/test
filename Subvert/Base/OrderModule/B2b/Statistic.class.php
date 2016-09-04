<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单列表相关的操作
 */

namespace Base\OrderModule\B2b;

use System\Base;

class Statistic extends Base
{
    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    /**
     * Base.OrderModule.B2b.Statistic.orderStatistic
     * 订单统计
     * @param type $params
     */
    public function orderStatistic($params){
        $order_group_status = M('Base.OrderModule.B2b.Status.groupStatusList')->groupStatusList();

        $status_list = array_keys($order_group_status);
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK), //订单下单开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK), //订单下单结束时间
            array('status', $status_list, PARAMS_ERROR, ISSET_CHECK, 'in'), //订单状态  组合状态
            array('pay_type', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        $sc_code = $params['sc_code'];
        $status = empty($params['status']) ? OC_ORDER_GROUP_STATUS_ALL : $params['status'];   //默认取全部订单状态
        $pay_type = $params['pay_type'];
        $status_where = $this->buildStatusWhere($status, $pay_type);
        !empty($sc_code) && $where['obo.sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['obo.create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['obo.create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['obo.create_time'] = array('between', array($start_time, $end_time));
        !empty($pay_type) && $where['obo.pay_type'] = $pay_type;
        !empty($pay_status) && $where['obo.pay_status'] = $pay_status;
        !empty($ship_status) && $where['obo.ship_status'] = $ship_status;
        if (!empty($status_where)) {
            $where['_complex'] = $status_where;
        }
        $fields='obo.real_amount';
        $params['where']=$where;
        if($params['trade']=='trade') {
            $map[] = array('obo.pay_type' => array('eq', 'ONLINE'), 'obo.pay_status' => array('eq', 'PAY'),'obo.pay_method'=>array('neq','REMIT'));
            $map[] = array('obo.pay_type' => array('eq', 'TERM'), 'obo.ship_status' => array('neq', 'UNSHIP'));
            $map[] = array('obo.pay_type' => array('eq', 'COD'),'obo.ship_status' => array('neq', 'UNSHIP'));
            $map[] = array('obo.pay_type' => array('eq', 'ONLINE'),'obo.pay_method'=>array('eq','REMIT'),'obo.order_status'=>array('not in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL)));
            $map['_logic'] = 'or';
            $params['where']['_complex'] = $map;
        }
        $where= D()->parseWhereCondition($params['where']);
        $order = 'obo.id desc';
        $sql="SELECT
                                    {$fields}
                            FROM
                                    {$this->tablePrefix}oc_b2b_order obo
                            LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend oboe ON obo.op_code = oboe.op_code
                            LEFT JOIN {$this->tablePrefix}sc_store ss ON obo.sc_code = ss.sc_code

                                    {$where}
                            ORDER BY
                                    {$order}";
        $order_data=D()->query($sql);
        return $this->res($order_data);
    }

    public function buildStatusWhere($status, $pay_type = '') {
//        $this->_request_sys_name = POP;
        $where = array();
        if($status == OC_ORDER_GROUP_STATUS_ALL){
            return $where;
        }
        switch ($this->_request_sys_name) {
            case POP:
                $where = $this->buildPopWhere($status, $pay_type = '');
                break;
            case B2B:
                $where = $this->buildB2bWhere($status, $pay_type = '');
                break;
            default :
                ;
        }
        $where['_logic'] = 'or';
        return $where;
    }


    private function buildPopWhere($status, $pay_type = '') {
        $where = array();
        $model = M('Base.OrderModule.B2b.Status');
        switch ($status){
            case OC_ORDER_GROUP_STATUS_UNPAY:
                //待付款   线上支付  待付款，线下支付 待收款
                $temp['pay_status'] =  OC_ORDER_PAY_STATUS_UNPAY;
                $temp['order_status'] = array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));  //没取消
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_UNSHIP:
                //待发货  线上支付  待发货，线下支付 待发货
                $temp = $model->groupToDetail($status, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_COD); //货到付款
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_TERM); //账期支付
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_SHIPPED:
                //  已发货： 线上支付  已发货，线下支付 已发货
                $temp = $model->groupToDetail($status, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;

                $temp = $model->groupToDetail($status, PAY_TYPE_COD);
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;

                $temp = $model->groupToDetail($status, PAY_TYPE_TERM);
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_COMPLETE:
                //交易成功：线上支付： 线上支付确认收货，线下支付  确认收款
                $where[] = $model->groupToDetail($status);
//                $where[] = $model->groupToDetail($status, PAY_METHOD_OFFLINE_COD);
                break;
            case OC_ORDER_GROUP_STATUS_CANCEL:
                //交易取消：商家和用户取消
                $where[]['order_status'] = array('in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));  //已取消
                break;
            case OC_ORDER_GROUP_STATUS_TAKEOVER:
                $temp = $model->groupToDetail($status, PAY_TYPE_COD);
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;

                $temp = $model->groupToDetail($status, PAY_TYPE_TERM);
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            default :
                ;
        }
        return $where;
    }

    private function buildB2bWhere($status, $pay_type = ''){
        $where = array();
        $model = M('Base.OrderModule.B2b.Status');
        switch ($status){
            case OC_ORDER_GROUP_STATUS_UNPAY:
                //待付款：  线上支付   待付款
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;

                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER, PAY_TYPE_COD);
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;

                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER, PAY_TYPE_TERM);
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;

                break;
            case OC_ORDER_GROUP_STATUS_SHIPPED:
                //待收货：  线上支付待发货，线上支付已发货，线下支付已发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNSHIP, PAY_TYPE_ONLINE);  //线上支付待发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_SHIPPED,PAY_TYPE_ONLINE);  //线上支付已发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_SHIPPED,PAY_TYPE_COD);  //货到付款 已发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_SHIPPED,PAY_TYPE_TERM);  //账期支付 已发货
                break;
            case OC_ORDER_GROUP_STATUS_COMPLETE:
                //已完成：  线上支付确认收货，线下支付 商家确认收款，线下支付确认收货
                $where[] = $model->groupToDetail($status);  //线上支付确认收货
//                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER,PAY_METHOD_OFFLINE_COD);  //线下支付确认收货
                break;
            case OC_ORDER_GROUP_STATUS_CANCEL:
                //已取消：  商家和用户取消
                $where['order_status'] = array('in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));  //已取消
                break;
            default :
                ;
        }
        return $where;
    }


    /**
     * b2b 冒泡统计
     * Base.OrderModule.B2b.Statistic.bubble
     * @author Todor
     * @access public
     */

    public function bubble($params){
        $this->_rule = array(
            // array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      //商家编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      //商家编码
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),   //开始时间
            array('type','require', PARAMS_ERROR, MUST_CHECK),          //订单显示状态
            array('sys_name', 'require', PARAMS_ERROR, ISSET_CHECK),    //平台类型
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // where 条件
        // $where['sc_code'] = $params['sc_code'];
        $where['uc_code'] = $params['uc_code'];

        // 平台
        $sysName = $params['sys_name'] ? $params['sys_name'] : $this->_request_sys_name;
        if($sysName = B2B){
            switch ($params['type']) {
                case OC_ORDER_GROUP_STATUS_UNPAY:   # 待付款
                    $status_where[] = array('pay_type'=>PAY_TYPE_ONLINE,'pay_status'=>TC_PAY_VOUCHER_UNPAY); 
                    $status_where[] = array('pay_type'=>PAY_TYPE_COD,'pay_status'=>TC_PAY_VOUCHER_UNPAY,'ship_status'=>OC_ORDER_SHIP_STATUS_TAKEOVER);
                    $status_where['_logic'] = 'or';
                    $where['_complex'] = $status_where;
                    $where['order_status'] = array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));
                    $where['create_time'] = array('between', array($params['start_time'], NOW_TIME));
                    break;
                case "TERM_UNPAY":                  # 账期待付
                    $where['pay_type'] = PAY_TYPE_TERM;
                    $status_where[] = array('ship_status'=>OC_ORDER_SHIP_STATUS_SHIPPED,'ship_time'=>array('between', array($params['start_time'], NOW_TIME)));
                    $status_where[] = array('ship_status'=>OC_ORDER_SHIP_STATUS_TAKEOVER,'takeover_time'=>array('between', array($params['start_time'], NOW_TIME)));
                    $status_where['_logic'] = 'or';
                    $where['_complex'] = $status_where;
                    $where['pay_status'] = TC_PAY_VOUCHER_UNPAY;
                    break;
                case OC_ORDER_GROUP_STATUS_UNSHIP:   # 待发货
                    $where['ship_status'] = OC_ORDER_SHIP_STATUS_UNSHIP;
                    $where['order_status'] = array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));
                    $where['create_time'] = array('between', array($params['start_time'], NOW_TIME));
                    $status_where[] = array('pay_type'=>PAY_TYPE_ONLINE,'pay_status'=>TC_PAY_VOUCHER_PAY);
                    $status_where[] = array('pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),'pay_status'=>TC_PAY_VOUCHER_UNPAY);
                    $status_where['_logic'] = 'or';
                    $where['_complex'] = $status_where;
                    break;
                case OC_ORDER_GROUP_STATUS_SHIPPED:  # 已发货
                    $where['ship_status'] = OC_ORDER_SHIP_STATUS_SHIPPED;
                    $where['ship_time'] = array('between', array($params['start_time'], NOW_TIME));
                    break;
                case OC_ORDER_GROUP_STATUS_COMPLETE: # 已完成
                    $where['order_status'] = OC_ORDER_ORDER_STATUS_COMPLETE;
                    $status_where[] = array('pay_type'=>PAY_TYPE_ONLINE,'complete_time'=>array('between', array($params['start_time'], NOW_TIME))); 
                    $status_where[] = array('pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),'pay_time'=>array('between', array($params['start_time'], NOW_TIME)));
                    $status_where['_logic'] = 'or';
                    $where['_complex'] = $status_where;
                    break;
                default:
                    return false;
                    break;
            }
        }

        $res = D('OcB2bOrder')->field('count(*) as num')->where($where)->select();
        if($res === false){
            return $this->error(NULL,6053);
        }
        return $this->res($res);
    }

}
