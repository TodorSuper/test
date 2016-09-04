<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关状态转换
 */

namespace Base\OrderModule\B2b;

use System\Base;

//买家自提，卖家配送
class Status extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }

    /**
     * 组合订单状态 与 详细订单状态的 映射
     * 调用方式   M('Base.OrderModule.B2b.Status.groupToDetail')->groupToDetail($status);
     * Base.OrderModule.B2b.Status.groupToDetail
     * @param type $params
     * @return type
     */
    public function groupToDetail($status, $pay_type = '') {
        $order_status = '';
        $pay_status = '';
        $ship_status = '';
        $data = array();
        switch ($status) {
            case OC_ORDER_GROUP_STATUS_ALL:      //全部状态列表
                return $data;
                break;
            case OC_ORDER_GROUP_STATUS_CANCEL:   //取消列表
                $order_status = array(POP => OC_ORDER_ORDER_STATUS_MERCHCANCEL, B2B => OC_ORDER_ORDER_STATUS_CANCEL);
                $ship_status = OC_ORDER_SHIP_STATUS_UNSHIP;
                $pay_status = OC_ORDER_PAY_STATUS_UNPAY;
                break;
            case OC_ORDER_GROUP_STATUS_COMPLETE : //已完成
                $order_status = OC_ORDER_ORDER_STATUS_COMPLETE;
                $ship_status = OC_ORDER_SHIP_STATUS_TAKEOVER;
                $pay_status = OC_ORDER_PAY_STATUS_PAY;
                break;
            case OC_ORDER_GROUP_STATUS_UNPAY:    //未支付列表
                    $order_status = OC_ORDER_ORDER_STATUS_UNCONFIRM;
                    $ship_status = OC_ORDER_SHIP_STATUS_UNSHIP;
                    $pay_status = OC_ORDER_PAY_STATUS_UNPAY;
                    break;
            default :
                ;
        }
        if ($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {   

            //货到付款   账期支付

            switch ($status) {
                case OC_ORDER_GROUP_STATUS_SHIPPED:  //已发货列表
                        $order_status = OC_ORDER_ORDER_STATUS_CHECKED;
                        $ship_status = OC_ORDER_SHIP_STATUS_SHIPPED;
                        $pay_status = OC_ORDER_PAY_STATUS_UNPAY;
                    break;
                case OC_ORDER_GROUP_STATUS_TAKEOVER: //已收货列表 
                    $order_status = OC_ORDER_ORDER_STATUS_CHECKED;
                    $ship_status = OC_ORDER_SHIP_STATUS_TAKEOVER;
                    $pay_status = OC_ORDER_PAY_STATUS_UNPAY;
                    break;
                case OC_ORDER_GROUP_STATUS_UNSHIP:   //未发货列表
                    $order_status = OC_ORDER_ORDER_STATUS_CHECKED;
                    $ship_status = OC_ORDER_SHIP_STATUS_UNSHIP;
                    $pay_status = OC_ORDER_PAY_STATUS_UNPAY;
                    break;
                default :
                    ;
            }
        } else if($pay_type == PAY_TYPE_ONLINE) { //线上支付
            switch ($status) {
                case OC_ORDER_GROUP_STATUS_SHIPPED:  //已发货未收货列表
                    $order_status = OC_ORDER_ORDER_STATUS_UNCONFIRM;
                    $ship_status = OC_ORDER_SHIP_STATUS_SHIPPED;
                    $pay_status = OC_ORDER_PAY_STATUS_PAY;
                    break;
                case OC_ORDER_GROUP_STATUS_TAKEOVER: //已收货未完成列表 
                    $order_status = OC_ORDER_ORDER_STATUS_UNCONFIRM;
                    $ship_status = OC_ORDER_SHIP_STATUS_TAKEOVER;
                    $pay_status = OC_ORDER_PAY_STATUS_PAY;
                    break;
                case OC_ORDER_GROUP_STATUS_UNSHIP:   //已支付未发货列表
                    $order_status = OC_ORDER_ORDER_STATUS_UNCONFIRM;
                    $ship_status = OC_ORDER_SHIP_STATUS_UNSHIP;
                    $pay_status = OC_ORDER_PAY_STATUS_PAY;
                    break;
                case OC_ORDER_GROUP_STATUS_PAY:   //已支付未完成
                    $order_status = OC_ORDER_ORDER_STATUS_UNCONFIRM;
                    $ship_status = OC_ORDER_SHIP_STATUS_UNSHIP;
                    $pay_status = OC_ORDER_PAY_STATUS_PAY;
                    break;
                default :
                    ;
            }
        }

            $data = array(
                'order_status' => $order_status,
                'pay_status' => $pay_status,
                'ship_status' => $ship_status,
            );
//            'pay_type' => $pay_type,
        return $data;
    }

    /**
     * 
     * 组合订单状态 与 详细订单状态的 映射
     * 调用方式   M('Base.OrderModule.B2b.Status.detailToGroup')->detailToGroup($order_status,$ship_status,$pay_status);
     * Base.OrderModule.B2b.Status.detailToGroup
     * @param type $params
     * @return type
     */
    public function detailToGroup($order_status, $ship_status, $pay_status, $pay_type = '',$ship_method = '') {
        $data = array();
        if (in_array($order_status, array(POP => OC_ORDER_ORDER_STATUS_MERCHCANCEL, B2B => OC_ORDER_ORDER_STATUS_CANCEL)) && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_GROUP_STATUS_UNPAY) {
            $data = array('message' => '交易取消', 'status' => OC_ORDER_GROUP_STATUS_CANCEL);
        }  else if ($order_status == OC_ORDER_ORDER_STATUS_COMPLETE && $ship_status == OC_ORDER_SHIP_STATUS_TAKEOVER && $pay_status == OC_ORDER_PAY_STATUS_PAY) {
            $data = array('message' => '交易完成', 'status' => OC_ORDER_GROUP_STATUS_COMPLETE);
        }
        if (!empty($data)) {
            return $data;
        }
        if ($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {
            //线下支付
            if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY) {
                $data = array('message' => '配货中', 'status' => OC_ORDER_GROUP_STATUS_UNSHIP);
            } else if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_SHIPPED && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $ship_method=SHIP_METHOD_DELIVERY) {
                $data = array('message' => '已发货', 'status' => OC_ORDER_GROUP_STATUS_SHIPPED);
            } else if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_TAKEOVER && $pay_status == OC_ORDER_PAY_STATUS_UNPAY) {
                $data = array('message' => '待付款', 'status' => OC_ORDER_GROUP_STATUS_TAKEOVER);
            } else if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_TAKEOVER && $pay_status == OC_ORDER_PAY_STATUS_PAY) {
                $data = array('message' => '已付款', 'status' => OC_ORDER_GROUP_STATUS_COD_PAY);
            }else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY) {
                $data = array('message' => '配货中', 'status' => OC_ORDER_GROUP_STATUS_UNPAY);
            }
        } else if($pay_type == PAY_TYPE_ONLINE){
            //线下支付
            if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_PAY) {
                $data = array('message' => '配货中', 'status' => OC_ORDER_GROUP_STATUS_UNSHIP);
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_SHIPPED && $pay_status == OC_ORDER_PAY_STATUS_PAY && $ship_method=SHIP_METHOD_DELIVERY) {
                $data = array('message' => '已发货', 'status' => OC_ORDER_GROUP_STATUS_SHIPPED);
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_TAKEOVER && $pay_status == OC_ORDER_PAY_STATUS_PAY) {
                $data = array('message' => '已收货', 'status' => OC_ORDER_GROUP_STATUS_TAKEOVER);
            } else if ($order_status == OC_ORDER_ORDER_STATUS_COMPLETE && $ship_status == OC_ORDER_SHIP_STATUS_TAKEOVER && $pay_status == OC_ORDER_PAY_STATUS_PAY) {
                $data = array('message' => '交易完成', 'status' => OC_ORDER_GROUP_STATUS_COMPLETE);
            }else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY) {
                $data = array('message' => '待付款', 'status' => OC_ORDER_GROUP_STATUS_UNPAY);
            }
        }

        return $data;
    }

    /**
     * 
     * 组合订单状态列表
     * 调用方式   M('Base.OrderModule.B2b.Status.groupStatusList')->groupStatusList();
     * Base.OrderModule.B2b.Status.groupStatusList
     * @param type $params
     * @return type
     */
    public function groupStatusList() {
        $status_list = array(
            OC_ORDER_GROUP_STATUS_ALL => '全部订单',
            OC_ORDER_GROUP_STATUS_CANCEL => '交易取消',
            OC_ORDER_GROUP_STATUS_SHIPPED => '已发货',
            OC_ORDER_GROUP_STATUS_TAKEOVER => '已收货',
            OC_ORDER_GROUP_STATUS_UNPAY => '待付款',
            OC_ORDER_GROUP_STATUS_UNSHIP => '配货中',
            OC_ORDER_GROUP_STATUS_COMPLETE => '交易完成',
            OC_ORDER_GROUP_STATUS_COD_PAY => '已付款', //货到付款 或者  账期支付  付款
            OC_ORDER_GROUP_STATUS_TERM_UNPAY => '账期待付',
            OC_ORDER_GROUP_STATUS_TRADE =>'已成交',
            OC_ORDER_GROUP_STATUS_VERIFICATION=>'提货码',
        );
        return $status_list;
    }

    /**
     * 支付方式列表
     */
    public function getPayMethod($pay_method) {
        $pay_list = array(
//            PAY_METHOD_OFFLINE_COD => '货到付款', //货到付款
            PAY_METHOD_ONLINE_WEIXIN => '微信支付', //微信支付
            PAY_METHOD_ONLINE_ALIPAY => '支付宝支付', //支付宝支付
//            PAY_METHOD_ONLINE_CHINAPAY => '银联支付', //银联支付
            PAY_METHOD_ONLINE_REMIT => '银行转账',//银行转账
            PAY_METHOD_ONLINE_YEEPAY=>'银行卡支付',
            PAY_METHOD_ONLINE_ADVANCE => '预付款支付',
            PAY_METHOD_ONLINE_UCPAY => '先锋支付',
        );
        if (!empty($pay_method)) {
            return $pay_list[$pay_method];
        }
        return $pay_list;
    }

    /**
     * 根据状态  判断能进行的操作
     * 调用方式   M('Base.OrderModule.B2b.Status.getOpeBuyStatus')->getOpeBuyStatus();
     * Base.OrderModule.B2b.Status.getOpeBuyStatus
     * @param type $order_status
     * @param type $ship_status
     * @param type $pay_status
     * @param type $pay_method
     */
    public function getOpeByStatus($order_status, $ship_status, $pay_status,$pay_method='', $pay_type='',$ship_method='') {
        $data = array();
        //$this->_request_sys_name = B2B;
        switch ($this->_request_sys_name) {
            case B2B:
                $data = $this->getB2bOpe($order_status, $ship_status, $pay_status,$pay_method, $pay_type,$ship_method);
                break;
            case POP:
                $data = $this->getPopOpe($order_status, $ship_status, $pay_status,$pay_method, $pay_type,$ship_method);
                break;
            default :;
        }

        return $data;
    }

    /**
     * pop的操作
     * @param type $order_status
     * @param type $ship_status
     * @param type $pay_status
     * @param type $pay_method
     */
    private function getPopOpe($order_status, $ship_status, $pay_status,$pay_method='', $pay_type='',$ship_method='') {
        //审核  发货  支付  取消
        $data = array();
        if ($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {
            //货到付款
            if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY) {
                //发货
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_SHIPPED, 'message' => '订单发货'),
                );
            }  else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $ship_method==SHIP_METHOD_DELIVERY) {
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_SHIPPED, 'message' => '订单发货'),
                    array('status' => OC_ORDER_GROUP_STATUS_CANCEL, 'message' => '订单取消'),
                );
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $ship_method==SHIP_METHOD_PICKUP){
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_CANCEL, 'message' => '订单取消'),
                );
            }
        } else  if($pay_type == PAY_TYPE_ONLINE){
            //线上支付

            if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_PAY && $ship_method==SHIP_METHOD_DELIVERY) {
                //发货 pop
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_SHIPPED, 'message' => '订单发货'),
                );
            }
        }

        return $data;
    }

    /**
     * b2b的操作
     * @param type $order_status
     * @param type $ship_status
     * @param type $pay_status
     * @param type $pay_method
     */
    private function getB2bOpe($order_status, $ship_status, $pay_status,$pay_method='', $pay_type='',$ship_method='') {
        //取消   支付  收货
   
        $data = array();
        if ($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {
            //货到付款
            if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_SHIPPED && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $ship_method==SHIP_METHOD_DELIVERY) {
                //发货
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_TAKEOVER, 'message' => '确认收货'),
                );
            }else if ($order_status == OC_ORDER_ORDER_STATUS_CHECKED && $ship_status == OC_ORDER_SHIP_STATUS_TAKEOVER && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $pay_method != PAY_METHOD_ONLINE_REMIT ) {
                //支付
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_COMPLETE, 'message' => '前往支付'),
                );
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $ship_method==SHIP_METHOD_DELIVERY) {
                //  取消
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_CANCEL, 'message' => '取消订单'),
                );
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $ship_method==SHIP_METHOD_PICKUP){
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_CANCEL, 'message' => '取消订单'),
                    array('status' => OC_ORDER_GROUP_STATUS_TAKEOVER, 'message' => '确认收货'),
                );
            }
        } else if($pay_type == PAY_TYPE_ONLINE){
            //线上支付
            if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_UNPAY && $pay_method != PAY_METHOD_ONLINE_REMIT) {
                //支付  取消 b2b
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_CANCEL, 'message' => '取消订单'),
                    array('status' => OC_ORDER_GROUP_STATUS_UNSHIP, 'message' => '前往支付'),
                );
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_SHIPPED && $pay_status == OC_ORDER_PAY_STATUS_PAY && $ship_method==SHIP_METHOD_DELIVERY) {
                //发货
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_COMPLETE, 'message' => '确认收货'),
                );
            } else if ($order_status == OC_ORDER_ORDER_STATUS_UNCONFIRM && $ship_status == OC_ORDER_SHIP_STATUS_UNSHIP && $pay_status == OC_ORDER_PAY_STATUS_PAY && $ship_method==SHIP_METHOD_PICKUP) {
                $data = array(
                    array('status' => OC_ORDER_GROUP_STATUS_COMPLETE, 'message' => '确认收货'),
                );
            }
        }


        return $data;
    }

    /**
     * 获取货运方式
     * 调用方式   M('Base.OrderModule.B2b.Status.getShipMethodList')->getShipMethodList();
     * Base.OrderModule.B2b.Status.getShipMethodList
     */
    public function getShipMethodList($ship_method = '') {
        $ship_method_list = array(
            SHIP_METHOD_DELIVERY => '商家配送',
            SHIP_METHOD_PICKUP => '买家自提',
        );
        if (!empty($ship_method)) {
            return $ship_method_list[$ship_method];
        }
        return $ship_method_list;
    }

    /**
     * 获取订单操作前置操作状态
     * @param type $status
     * @param type $pat_method 支付方式
     * @param type $need_parse  是否需要分解成具体的 对应状态
     */
    public function getOpePre($status, $pay_method = '',$pay_type = '', $need_parse = TRUE,$ship_method='') {
        $data = '';

        switch ($this->_request_sys_name) {
            case B2B:
                $data = $this->getB2bOpePre($status, $pay_method, $pay_type,$ship_method);
                break;
            case POP:
                $data = $this->getPopOpePre($status, $pay_method, $pay_type,$ship_method);
                break;
            default:
                $data = $this->getB2bOpePre($status, $pay_method, $pay_type,$ship_method);
                break;
        }
        if (empty($data)) {
            return FALSE;
        }
        if ($need_parse === TRUE) {
            //分解成对应的状态
            $data = $this->groupToDetail($data, $pay_type);
        }
        return $data;
    }

    /**
     * b2b平台操作前置操作
     * @param type $status
     * @param type $pay_method
     */
    private function getB2bOpePre($status, $pay_method = '',$pay_type = '',$ship_method='') {
        //所有操作   取消  付款  收货
        $data = '';
        if ($status == OC_ORDER_GROUP_STATUS_CANCEL) {
            return OC_ORDER_GROUP_STATUS_UNPAY;
        } else if ($status == OC_ORDER_GROUP_STATUS_UNSHIP) {
            return OC_ORDER_GROUP_STATUS_UNPAY;
        }

        if ($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {
            if ($status == OC_ORDER_GROUP_STATUS_UNSHIP) {
                $data = '';
            } else if ($status == OC_ORDER_GROUP_STATUS_TAKEOVER && $ship_method==SHIP_METHOD_DELIVERY) {
                $data = OC_ORDER_GROUP_STATUS_SHIPPED;
            }else if ($status == OC_ORDER_GROUP_STATUS_COMPLETE){
                $data = OC_ORDER_GROUP_STATUS_TAKEOVER;
            }else if($status == OC_ORDER_GROUP_STATUS_TAKEOVER && $ship_method==SHIP_METHOD_PICKUP){
                $data = OC_ORDER_GROUP_STATUS_UNPAY;
            }
        } else if($pay_type == PAY_TYPE_ONLINE){
            if ($status == OC_ORDER_GROUP_STATUS_UNSHIP) {
                $data = OC_ORDER_GROUP_STATUS_UNPAY;
            } else if ($status == OC_ORDER_ORDER_STATUS_COMPLETE && $ship_method==SHIP_METHOD_DELIVERY) {
                $data = OC_ORDER_GROUP_STATUS_SHIPPED;
            } else if ($status == OC_ORDER_ORDER_STATUS_COMPLETE && $ship_method==SHIP_METHOD_PICKUP) {
                $data = OC_ORDER_GROUP_STATUS_PAY;
            }
        }

        return $data;
    }

    /**
     * pop平台操作前置操作
     * @param type $status
     * @param type $pay_method
     */
    private function getPopOpePre($status, $pay_method = '',$pay_type = '',$ship_method='') {
        //所有的操作  审核 取消 发货支付
        $data = '';
        if ($status == OC_ORDER_GROUP_STATUS_CANCEL) {
            return OC_ORDER_GROUP_STATUS_UNPAY;
        }

        if ($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {
            //线下支付
            if($status == OC_ORDER_GROUP_STATUS_SHIPPED && $ship_method==SHIP_METHOD_DELIVERY){
                $data = OC_ORDER_GROUP_STATUS_UNPAY;
            }
            if($status == OC_ORDER_GROUP_STATUS_TAKEOVER  && $ship_method==SHIP_METHOD_PICKUP){
                $data = OC_ORDER_GROUP_STATUS_UNPAY;
            }
        } else if($pay_type == PAY_TYPE_ONLINE) {
            //线上支付
            if ($status == OC_ORDER_GROUP_STATUS_UNSHIP) {
                $data = '';
            }
            if ($status == OC_ORDER_GROUP_STATUS_SHIPPED && $ship_method==SHIP_METHOD_DELIVERY) {
                return OC_ORDER_GROUP_STATUS_UNSHIP;
            }
            if ($status == OC_ORDER_GROUP_STATUS_COMPLETE  && $ship_method==SHIP_METHOD_PICKUP) {
                return OC_ORDER_GROUP_STATUS_PAY;
            }
        }

        return $data;
    }

    /**
     * 获取操作时候的时间字段
     */
    public function getOpeTimeField($status) {
        $field = '';
        if ($status == OC_ORDER_GROUP_STATUS_CANCEL) {
            $field = "cancel_time";
        } else if ($status == OC_ORDER_GROUP_STATUS_COD_PAY ) {
            $field = 'pay_time';
        } else if ($status == OC_ORDER_GROUP_STATUS_COMPLETE) {
            $field = 'complete_time';
        } else if ($status == OC_ORDER_GROUP_STATUS_TAKEOVER) {
            $field = "takeover_time";
        } else if ($status == OC_ORDER_GROUP_STATUS_SHIPPED) {
            $field = "ship_time";
        } else if ($status == OC_ORDER_GROUP_STATUS_UNPAY) {
            $field = "create_time";
        }
        return $field;
    }

    
    /**
     * 获取支付类型
     * @param type $pay_type
     */
    public function getPayType($pay_type){
        $pay_type_list = array(
           PAY_TYPE_ONLINE  => '立即付款',
           PAY_TYPE_COD => '货到付款',
           PAY_TYPE_TERM   => '账期付款',
        );
        if (!empty($pay_type)) {
            return $pay_type_list[$pay_type];
        }
        return $pay_type_list;
    }

    
    
     /**
      * M('Base.OrderModule.B2b.Status')->getRemitBank();
     * 支付方式列表
     */
    public function getRemitBank($remit_bank = '') {
        $remit_bank_list = array(
            PAY_METHOD_REMIT_CMB => '招商银行', //招商银行
            PAY_METHOD_REMIT_CMBC => '民生银行', //民生银行
        );
        if (!empty($remit_bank)) {
            return $remit_bank_list[$remit_bank];
        }
        return $remit_bank_list;
    }


}

?>
