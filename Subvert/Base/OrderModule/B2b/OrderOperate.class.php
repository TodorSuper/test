<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b操作
 */

namespace Base\OrderModule\B2b;

use System\Base;

//买家自提，卖家配送
class OrderOperate extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Base.OrderModule.B2b.OrderOperate.selectPayMethod
     * 选择支付方式
     * @param type $params
     */
    public function selectPayMethod($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('b2b_code','require',PARAMS_ERROR, MUST_CHECK),   //订单编码
            array('pay_method','require',PARAMS_ERROR,MUST_CHECK),  //支付编码
            array('remit_bank','require',PARAMS_ERROR,ISSET_CHECK),
            array('pay_type','require',PARAMS_ERROR,MUST_CHECK)
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $model = D('OcB2bOrder');
        if ($params['pay_type'] == PAY_TYPE_COD || $params['pay_type'] == PAY_TYPE_TERM) {
            $status = OC_ORDER_GROUP_STATUS_TAKEOVER;
        } else {
            $status = OC_ORDER_GROUP_STATUS_UNPAY;
        }
        $order_status = M('Base.OrderModule.B2b.Status.groupToDetail')->groupToDetail($status,$params['pay_type']);
        $where = array(
            'uc_code' => $params['uc_code'],
            'op_code'=> $params['b2b_code'],
            'pay_method'=>array('neq',PAY_METHOD_ONLINE_REMIT)
        );

        $where = !empty($order_status['order_status'])?array_merge($where,$order_status):$where;

        $data = array(
            'pay_method'  => $params['pay_method'],
            'update_time' => NOW_TIME,
        );
        switch ($params['pay_method']) {
            case PAY_METHOD_ONLINE_REMIT:

                if ( !isset($params['remit_bank']) || empty($params['remit_bank']) ) {
                    return $this->res(null,6034);
                }

                $data['ext1'] = $params['remit_bank'];
                $code_rows = $this->invoke('Base.OrderModule.B2b.OrderInfo.setRemitCode', array('op_code'=>$params['b2b_code'],'uc_code'=>$params['uc_code'],'remit_code'=>$params['code']));
                $code_rows = $code_rows['status'] == 0 ?true:false;
                break;
            case PAY_METHOD_ONLINE_ADVANCE:
                $code_rows = true;
                break;    
            default:
                break;
        }

        $rows = $this->_saveData($model,$where,$data);

        if ( $rows === true && $code_rows === true ) {
            return $this->res(true);
        } else {

            return $this->res(null,6034);

        } 
    }
    private function _saveData($model,$params,$data) {
        $rows = $model->where($params)->save($data);
        if ($rows > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * Base.OrderModule.B2b.OrderOperate.updateSpcCode
     * 更新余额支付的订单的spc_code
     * @param type $params
     */
    public function updateSpcCode($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家编码
            array('spc_code','require',PARAMS_ERROR, MUST_CHECK),   //订单编码
            
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $spc_code = $params['spc_code'];
        $order_res = D('OcB2bOrder')->where(array('sc_code'=>$sc_code,'pay_method'=>PAY_METHOD_ONLINE_ADVANCE))->save(array('update_time'=>NOW_TIME,'spc_code'=>$spc_code));
        if($order_res === FALSE){
            return $this->res(NULL,6039);
        }
        return $this->res(TRUE);
    }
    
    
    
}

?>
