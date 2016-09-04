<?php
/**
* +---------------------------------------------------------------------
* | www.liangrenwang.com 粮人网
* +---------------------------------------------------------------------
* | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
* +---------------------------------------------------------------------
* | Author: Todor <nielei@liangrenwang.com >
* +---------------------------------------------------------------------
* | b2b订单列状态更新
*/

namespace Base\OrderModule\Advance;

use System\Base;

class Order extends Base {

    private $_rule  =   null;

    public function __construct() {
        parent::__construct();
    }


    /**
     * 生成预付款订单
     * Base.OrderModule.Advance.Order.add
     * @access public 
     */

    public function add($params){

        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),        # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),        # 客户编码
            array('amount', 'require', PARAMS_ERROR, MUST_CHECK),         # 充值金额
            array('pay_method', 'require', PARAMS_ERROR, MUST_CHECK),     # 支付方式
            array('client_name', 'require', PARAMS_ERROR, MUST_CHECK),    # 用户姓名
            array('operator_ip', 'require', PARAMS_ERROR, MUST_CHECK),    # 用户IP
            array('bank', 'require', PARAMS_ERROR, ISSET_CHECK),          # 银行转账银行
        );
        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $adv_code = $this->generateOrderCode(OC_ADVANCE_ORDER_ADV);
        $op_code  = $this->generateOrderCode(OC_ADVANCE_ORDER_OP);

        $data = array(
            'uc_code'=>$params['uc_code'],
            'sc_code'=>$params['sc_code'],
            'adv_code'=>$adv_code,
            'op_code'=>$op_code,
            'amount'=>$params['amount'],
            'pay_method'=>$params['pay_method'],
            'client_name'=>$params['client_name'],
            'operator_ip'=>$params['operator_ip'],
            'pay_time'=>0,
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
        );

        // 如果是银行转账
        if($params['pay_method'] == PAY_METHOD_ONLINE_REMIT){
            $remit_code = $this->generateRemitCode();
            $data['remit_code'] = $remit_code;
            $data['pay_method_ext1'] = $params['bank'];
        }
        $res = D('OcAdvance')->add($data);
        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6035);
        }

        // 发送微信消息
        $weixinMsg = array(
            'order_sn'     => $adv_code, 
            'goods_name'   => '粮人网商品', 
            'goods_number' => 1,
            'pay_price' => $params['amount'],
            'uc_code' => $params['uc_code'],
            'pay_method' => $params['pay_method'],
            'url_info'=>C('DEFAULT_WEIXIN_URL').'Advance/getAdvance.html',
            'type'=>'pay',
        );

        $this->push_queue('Base.OrderModule.Advance.Order.sendOrderMsg', $weixinMsg, 0);

        return $this->res(array('op_code'=>$op_code,'adv_code'=>$adv_code,'amount'=>$params['amount']));
    }


    /**
     * Base.OrderModule.Advance.Order.sendOrderMsg
     * 根据uc_code 获取open_id 并且发送模板
     * @access private
     * @author Todor
     */
    public function sendOrderMsg($params){

        $params = $params['message'];
        //获取OPENID
        $api = 'Base.WeiXinModule.User.User.getWeixinInfo';
        $res = $this->invoke($api, array('uc_code' => $params['uc_code']));
        $params['openid'] = $res['response']['open_id'];
        if ($res['status'] != 0){
            return $this->res(null, $res['status']);
        }

        if($params['type'] == 'pay'){
            $api = 'Com.Common.Message.WxTpl.mkPayAdvanceOrder';
        }else{
            $api = "Com.Common.Message.WxTpl.payAdvanceOrder";
        }

        $respon = $this->invoke($api, $params);
        return $this->res($respon['response'], $respon['status']);
    }


    /**
     * 预货款订单回掉
     * Base.OrderModule.Advance.Order.payBack
     * @access public 
     */

    public function payBack($params){

        $this->startOutsideTrans();
        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK),        # 预付款订单编码
            array('amount', 'require', PARAMS_ERROR, MUST_CHECK),         # 充值金额
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),     # 支付方式
            array('pay_by', 'require', PARAMS_ERROR, ISSET_CHECK),     # 支付方式
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where['op_code'] = $params['op_code'];

        // 获取预付款订单信息  
        $order = D('OcAdvance')->where($where)->find();
        $uc_code = $order['uc_code'];
        $pay_by = $params['pay_by'];
        $update_time = $order['update_time'];
        $where['update_time'] = $update_time;
        $where['status']      = 'UNPAY';
        $adv_code             = $order['adv_code'];

        $data = array(
            'amount'=>$params['amount'],
            'pay_method'=>$params['pay_method'],
            'status'=>'PAY',
            'update_time'=>NOW_TIME+1,
            'pay_time'=>NOW_TIME,
            );
        if(!empty($pay_by)){
            $data['pay_method_ext1'] = $pay_by;
        }
        $res = D('OcAdvance')->where($where)->save($data);

        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6037);
        }

        $pay_method = $params['pay_method'];
        if($pay_method == PAY_METHOD_ONLINE_ALIPAY){
            $pay_method_message = '支付宝支付';
        }else if ($pay_method == PAY_METHOD_ONLINE_WEIXIN){
            $pay_method_message = '微信支付';
        }

        // 发送微信消息
        $weixinMsg = array(
            'order_sn' => $adv_code,
            'pay_time' => NOW_TIME,
            'pay_type' => $pay_method_message,
            'pay_price' => $params['amount'],
            'url_info'=>C('DEFAULT_WEIXIN_URL').'Advance/getAdvance.html',
            'uc_code' => $uc_code,
            'type'=>'pay_back',
        );

        $this->push_queue('Base.OrderModule.Advance.Order.sendOrderMsg', $weixinMsg, 0);
        return $this->res(true);
    }

    /**
     * 获取预付款订单
     * Base.OrderModule.Advance.Order.get
     * @access public 
     */

    public function get($params){
        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK),        # 订单支付编码
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['op_code'] = $params['op_code'];
        $res = D('OcAdvance')->where($where)->find();
        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6036);
        }
        return $this->res($res);
    }


    /**
     * 获取预付款订单编码
     * @access private
     */
    private function generateOrderCode($preBusType) {

        $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
        $data = array(
            'busType' => OC_ADVANCE_ORDER,
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
     * 生成汇款码
     */
    private function generateRemitCode(){

        $apiPath = "Com.Tool.Code.CodeGenerate.mkCycleCode";
        $data = array('codeType'=>SEQUENCE_REMIT);
        $res = $this->invoke($apiPath,$data);
        if($res['status'] != 0){
            return $this->endInvoke(null,$res['status']);
        }
        return $res['response'];
    }

}