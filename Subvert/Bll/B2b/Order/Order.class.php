<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关模块
 */

namespace Bll\B2b\Order;

use System\Base;
class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }

    /**
     * 创建b2b订单
     * Bll.B2b.Order.Order.add
     * @param type $params
     */
    public function add($params) {

        $pay_list = array();
        $api = 'Base.StoreModule.Basic.Paytype.lists';
        $pay_type_list = $this->invoke($api,array('sc_code'=>$params['sc_code']));
        if ( isset($pay_type_list['response']['yue'])  ) {
            $pay_list[PAY_TYPE_TERM_MONTH] = 1; 
        } 
        if (isset($pay_type_list['response']['day'])) {
            $pay_list[TERM_PERIOD] = 1;
        }
        if ( !isset($pay_list[$params['term_method']]) && $params['pay_type'] != PAY_TYPE_ONLINE && $params['pay_type'] != PAY_TYPE_COD ) {

            return $this->endInvoke(null,6045);
        }
        $apiPath = "Base.OrderModule.B2b.Order.add";
        $add_res = $this->invoke($apiPath,$params);

        if($add_res['status'] == 0){
            //下单成功
            //如果是货到付款  的  则需要发送短信
            if($params['pay_type'] == PAY_TYPE_COD || $params['pay_type'] == PAY_TYPE_TERM){
                //获取商家手机号码
                $client_name = $add_res['response']['client_name'];
                $client_commercial_name = $add_res['response']['client_commercial_name'];
                $client_mobile = $add_res['response']['client_mobile'];
                $sc_code = $add_res['response']['sc_code'];
                $salesman_id = $add_res['response']['salesman_id'];
                $order_amount = $add_res['response']['order_amount'];
                $pick_up_code = $add_res['response']['pick_up_code'];
                $ship_method = $add_res['response']['ship_method'];
                //获取业务员信息信息
                $apiPath = "Base.UserModule.Customer.Salesman.get";
                $res = $this->invoke($apiPath,array('salesman_id'=>$salesman_id));
                $mobile = $res['response']['mobile'];
                
                $this->goShipMessage($add_res['response']['op_code'], $mobile, $client_name,$client_commercial_name,$client_mobile,$order_amount);
                
                //如果配送方式是  买家自提
                if($ship_method == SHIP_METHOD_PICKUP){
                    $this->pickUpMessage($pick_up_code, $client_mobile);
                }
                
                //给自定义接收人发短信
                $smsApi = "Base.StoreModule.Basic.Sms.getLinkManInfo";
                $data = array(
                    // 'uc_code' => $params['uc_code'],
                    'sc_code' => $sc_code,
                    'sms_type' => 'NEW_ORDER',
                    );
                $dataRes = $this->invoke($smsApi, $data);
                $saleMans = $dataRes['response'];
                if ($saleMans) {
                    foreach ($saleMans as $key => $value) {
                        $this->goShipMessage($add_res['response']['op_code'], $value['phone'], $client_name,$client_commercial_name,$client_mobile,$order_amount);
                    }
                }
                
                                
                //推送 友盟
                $this->push_queue('Com.Callback.Push.Queue.unshipWarn', array('sc_code'=>$sc_code), 0);
                
                
            }

            // 如果是平台用户 2天则会自动取消
            if($params['order_type'] == OC_ORDER_TYPE_PLATFORM){
                $time = strtotime(date('Y-m-d',NOW_TIME))+2*86400-1;
                $time -= NOW_TIME;
                $ancel_res = $this->push_queue("Bll.B2b.Order.Order.autoCancel",array('b2b_code'=>$add_res['response']['b2b_code']),1,$time);
            }
            
        }
        $add_res['response']['pay_list'] = $pay_list;
        return $this->endInvoke($add_res['response'],$add_res['status'],'',$add_res['message']);
        
    }

    /**
     * 展示即将下单的信息
     * Bll.B2b.Order.Order.showOrderInfo
     * @param type $params
     */
    public function showOrderInfo($params) {

        $apiPath = "Base.OrderModule.B2b.Order.showOrderInfo";
        $res = $this->invoke($apiPath,$params);
        //获取购物券信息
        $data = array(
            'uc_code'=>$params['uc_code'],
            'total_amount'=>$res['response']['order_info']['total_amount'],
        );
        if(empty($params['coupon_code'])){                  # 自动选择查看有多少数量
            $apiPath = "Base.UserModule.Coupon.Coupon.suitable";
        }else{                                              # 手动选择
            $data['coupon_code'] = $params['coupon_code'];
            $apiPath = "Base.UserModule.Coupon.Coupon.get";
        }
       
        $coupon_res = $this->invoke($apiPath,$data);
        if($coupon_res['status'] !== 0){
            return $this->endInvoke(NULL,$coupon_res['status']);
        }
        $res['response']['coupon'] = $coupon_res['response'];


        //根据钱数判断能不能得到优惠券 并返回全部策略
        $full_back_params = array(
            'flag'=>SPC_ACTIVE_CONDITION_FLAG_FULL_BACK,
            'amount'=>$res['response']['order_info']['total_amount'],
            );

        $apiPath = "Base.SpcModule.Coupon.Center.getSuitable";
        $full_back = $this->invoke($apiPath,$full_back_params);
        if($full_back['status'] !== 0){
            return $this->endInvoke(NULL,$full_back['status']);
        }

        $res['response']['full_back'] = $full_back['response'];


        if(!empty($res['response']['goods_info'])){

            //获取对应标签
            $sic_codes = array_column($res['response']['goods_info'],'sic_code');
            $tag_parmas['sic_codes'] = $sic_codes;
            $apiPath = "Base.ItemModule.Tag.Tag.getTags";
            $tags_res = $this->invoke($apiPath, $tag_parmas);
            foreach ($tags_res['response'] as $k => $v) {
                $temp[$v['sic_code']][] = $v;
            }
            $tag_keys = array_keys($temp);

            // 拼装 标签
            foreach ($res['response']['goods_info'] as $k => &$v) {
                if(in_array($v['sic_code'],$tag_keys)){
                    $v['tags'] = $temp[$v['sic_code']];
                }
            }
            unset($v);

        }
        return $this->endInvoke($res['response']);
    }

    /**
     * 验证商品是否允许购买
     * Bll.B2b.Order.Order.isAllowBuy
     * @param type $params
     */
    public function isAllowBuy($params) {
        $apiPath = "Base.OrderModule.B2b.Order.isAllowBuy";
        $add_res = $this->invoke($apiPath,$params);
        return $this->endInvoke($add_res['response'],$add_res['status'],'',$add_res['message']);
    }

    
    // /**
    //  * 订单发货发送短信提醒
    //  */
    // private function goShipMessage($b2b_code,$mobile, $client_name,$client_commercial_name,$client_mobile){
    //     $message = "您的客户“{$client_commercial_name} {$client_name} {$client_mobile}”有一个订单需要发货，订单号：{$b2b_code}，请尽快发货。";
        
    //     $data = array(
    //         'sys_name'=>B2B,
    //         'numbers'=>array($mobile),
    //         'message'=>$message,
    //     );
    //     $apiPath = "Com.Common.Message.Sms.send";
    //     $this->push_queue($apiPath, $data,0);
    //     return true;
    // }
    /*
    * 预付款短信
    */
    private function goadvanceMessage($mobile, $code){
        $message ="您的支付验证码是：{$code}，请不要向任何人透露支付验证码，平台不会以任何形式向个人索要支付验证码!。";
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
        return true;
    }
    /*
    * 获取商家支付类型
    * Bll.B2b.Order.Order.peroid
    */
    public function peroid($params) {
        $api = 'Base.StoreModule.Basic.Paytype.lists';
        $pay_type_list = $this->invoke($api,array('sc_code'=>$params['sc_code']));
        return $this->endInvoke($pay_type_list['response'],$pay_type_list['status'],$pay_type_list['message']);
    }
    /*
    * 预付款发送短信
    * Bll.B2b.Order.Order.getAdvanceSms
    */
    public function getAdvanceSms($params){
        //获取客户电话
        $apiPath = 'Base.UserModule.Customer.Customer.get';
        $mobile  = $this->invoke($apiPath,$params);
        $mobile  = $mobile['response']['mobile'];
        $code    = $params['code'];
        //发送短信
        $row = $this->goadvanceMessage($mobile,$code);
        if ($row === true) {
            return $this->endInvoke(null,0);
        } else {
            return $this->endInvoke(null,5018);
        }
    }
    /*
    * 余额支付是否显示
    * Bll.B2b.Order.Order.isAdvanceShow
    */
    public function isAdvanceShow($params) {
        //获取订单金额
        $apiPath = 'Base.OrderModule.B2b.OrderInfo.simpleGet';
        $money = $this->invoke($apiPath,array('uc_code'=>$params['uc_code'],'op_code'=>$params['op_code']));

        //获取支付列表
        $apiPath = "Base.OrderModule.Center.PayMethod.lists";
        $data = array('pay_status'=>'ON');
        $pay_lists = $this->invoke($apiPath,$data);
        return $this->res(array('payList'=>$pay_lists['response'],'b2b_code'=>$money['response']['b2b_code'],'pick_up_code'=>$money['response']['pick_up_code']),0);

    }
    /*
    * 预付款更新资金
    * Bll.B2b.Order.Order.saveAdvanceData
    */
    public function saveAdvanceData($params) {
        // $params = array(
        //     'uc_code'      => 1230000000690,
        //     'operatorType' => 'b2bAdvanceReduce',
        //     'operatorIp'   => $_SERVER["REMOTE_ADDR"],
        //     'sc_code'      => 1020000000026,
        //     'type'         => 'minus',
        //     'oc_code'      => 12200003757,
        // );
        //获取用户信息
 
        $apiPath = 'Base.OrderModule.B2b.OrderInfo.simpleGet';
        $money = $this->invoke($apiPath,array('uc_code'=>$params['uc_code'],'op_code'=>$params['oc_code']));
        $ship_method = $this->invoke('Base.OrderModule.B2b.OrderInfo.get',array('uc_code'=>$params['uc_code'],'b2b_code'=>$params['oc_code']));

        
        if (!isset($money['response']['total_real_amount'])) {
            return $this->res(null,6042);
        }

        $pay_time = $money['response']['pay_time'];
        $params['amount'] = $money['response']['total_real_amount'];
        $params['advance_money'] = $money['response']['total_real_amount'];
        $api = 'Base.TradeModule.Account.Details.getAccontListByCode';
        $res = $this->invoke($api,array(array('code'=>$params['uc_code'],'isPcode'=>'NO')));
        $params['tc_code'] = current($res['response'])['tc_code'];
        //获取操作人用户名
        $api = 'Base.UserModule.Customer.Customer.get';
        $name = $this->invoke($api,array('sc_code'=>$params['sc_code'],'uc_code'=>$params['uc_code']));
        $params['operatorName'] = $name['response']['commercial_name'];
        $commercial_mobile = $name['response']['mobile'];
        $real_name = $name['response']['real_name'];
        $salesman_id = $name['response']['salesman_id'];
        try{
            D()->startTrans();
            $api = 'Base.TradeModule.Account.Balance.desc';

            $add_res = $this->invoke($api,$params);
            $apiPath = 'Base.SpcModule.Commodity.Customer.update';
            $res     = $this->invoke($apiPath,$params);
            //更新order_status状态
            $status = $money['response']['pay_type'] === PAY_TYPE_ONLINE ? OC_ORDER_SHIP_STATUS_UNSHIP:OC_ORDER_GROUP_STATUS_COMPLETE;
            $order_status = $this->invoke('Base.OrderModule.B2b.OrderInfo.operate',array('uc_code'=>$params['uc_code'],'status'=>$status,'b2b_code'=>$params['oc_code'],'need_action'=>'YES'));
            if( $add_res['status'] != 0 || $res['status'] != 0 || $order_status['status'] != 0 ){
              
                return $this->res(null,6040);
            }
            $res = D()->commit();
            if(FALSE === $res){
                throw new \Exception('事务提交失败');
            }
            $this->advanceDesMessage($params['oc_code'], $params['advance_money'], $commercial_mobile);

            //给自定义接收人发短信
            $smsApi = "Base.StoreModule.Basic.Sms.getLinkManInfo";
            $data = array(
                'sc_code' => $params['sc_code'],
                'sms_type' => 'NEW_ORDER',
                );
            $dataRes = $this->invoke($smsApi, $data);
            $saleMans = $dataRes['response'];
            if ($saleMans) {
                foreach ($saleMans as $key => $value) {
                    $this->goShipMessage($params['oc_code'], $value['phone'], $real_name, $params['operatorName'],$commercial_mobile,$money['response']['total_real_amount']);
                }
            }

            //获取业务员信息信息
            $apiPath   = "Base.UserModule.Customer.Salesman.get";
            $sales_res = $this->invoke($apiPath,array('salesman_id'=>$salesman_id));
            $mobile    = $sales_res['response']['mobile'];
            // var_dump($b2b_code,$mobile, $real_name,$commercial_mobile,$commercial_name);
            $this->goShipMessage($b2b_code, $mobile, $real_name, $commercial_name, $commercial_mobile,$money['response']['total_real_amount']);

            $apiPath = 'Base.OrderModule.B2b.OrderInfo.simpleGet';
            $money = $this->invoke($apiPath,array('uc_code'=>$params['uc_code'],'op_code'=>$params['oc_code']));
            if (!isset($money['response']['total_real_amount'])) {
                return $this->res(null,6042);
            }
            $pay_time = $money['response']['pay_time'];
            $pay_time = date('Y.m.d H:i:s', $pay_time);
            $this->orderFinishedMessage($params['oc_code'], $params['advance_money'], $commercial_mobile, $pay_time);
            return $this->res($ship_method['response']['order_info'],0);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,6040);
        }
    }

    /**
     * 订单支付成功
     */
    private function orderFinishedMessage($b2b_code, $amount, $commercial_mobile, $pay_time){
        $message = "您的订单已支付成功；订单号：{$b2b_code}，支付金额：￥{$amount} ，支付时间：".$pay_time."。";
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($commercial_mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }

     /**
     * 订单发货发送短信提醒
     */
    private function goShipMessage($b2b_code,$mobile, $client_name,$client_commercial_name,$client_mobile,$order_amount){
        $message = "您的客户“{$client_commercial_name} {$client_name} {$client_mobile}”有一个订单需要发货，订单号：{$b2b_code}，金额：{$order_amount}元，请尽快发货。";
        
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }

    /**
     * Bll.B2b.Order.Order.advanceDesMessage
     * 预付款扣除发送短信
     */
    public function advanceDesMessage($b2b_code, $advance_money, $mobile){
        $message = "您的预付款发生订单支付扣款，扣款人民币：{$advance_money}，扣款订单号：{$b2b_code}。如有疑问，请致电客服：400-815-5577。";
         $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }

    /*
    * Bll.B2b.Order.Order.selectPayMethod
    * 设置支付方式  
    */
    public function selectPayMethod($params) {
        try{
            D()->startTrans();
            $apiPath = 'Base.OrderModule.B2b.OrderInfo.simpleGet';
            $res = $this->invoke($apiPath,array('uc_code'=>$params['uc_code'],'op_code'=>$params['b2b_code']));
            if ( $params['pay_method'] == PAY_METHOD_ONLINE_REMIT ) {
                //生成汇款码
                $remitCode = $this->generateRemitCode();
                $code  = $params['code'] = $remitCode;
                $amout = $res['response']['total_real_amount'];
            }
            $params['pay_type'] = $res['response']['pay_type'];
            $api = 'Base.OrderModule.B2b.OrderOperate.selectPayMethod';
            
            $add_res = $this->invoke($api,$params);

            if ( $params['pay_method'] == PAY_METHOD_ONLINE_REMIT ) {
                //生成汇款码
                $add_res['code'] = $code;
                $add_res['amout'] = $amout;
            }
            if($add_res['status'] != 0){
                return $this->res($add_res['message'],$add_res['status']);
            }
           
            $res = D()->commit();
            if(FALSE === $res){
                throw new \Exception('事务提交失败');
            }

            return $this->res($add_res);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,9001);
        }
     
       
       
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
    /*
    * Bll.B2b.Order.Order.changeOpCode
    * 更改汇款码
    */
    public function changeOpCode($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.OrderModule.Center.Order.changeOpCode";
            $res = $this->invoke($apiPath,$params);
            D()->commit();
            return $this->res($res['response'], $res['status'], $res['message']);

        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,9001);
        }
    }



    /**
     * 订单自动取消
     * Bll.B2b.Order.Order.autoCancel
     * @access public
     * @author Todor
     */

    public function autoCancel($params){

        if (isset($params['message']) && is_array($params['message'])) {            # 如果为队列
            $params = $params['message'];
        }

        // 获取订单信息
        $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        $order_res = $this->invoke($apiPath,$params);
        if($order_res['status'] !== 0){
            return $this->endInvoke(NULL,$order_res['status']);
        }
        $order_info = $order_res['response']['order_info'];

        // 判断订单条件
        $status = M('Base.OrderModule.B2b.Status.groupToDetail')->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY);
        if(($order_info['order_status'] !== $status['order_status']) || ($order_info['pay_status'] !== $status['pay_status']) || ($order_info['ship_status'] !== $status['ship_status'])){
            return $this->endInvoke(true);            # 2天内订单状态已改变
        }

        // 改变订单状态
        try{
            D()->startTrans();
            $params = array(
                'status'=>OC_ORDER_GROUP_STATUS_CANCEL,
                'b2b_code'=>$params['b2b_code'],
                'need_action'=>'YES',
                'cancel_type'=>'AUTO',
                );
            $_SERVER['HTTP_USER_AGENT'] = B2B;
            $apiPath = "Base.OrderModule.B2b.OrderInfo.operate";
            $res = $this->invoke($apiPath,$params);
            D()->commit();
            return $this->res($res['response'], $res['status'], $res['message']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,6066);
        }


    }


    /**
     * 订单返回满减优惠券所有策略
     * Bll.B2b.Order.Order.fullBack
     * @access public
     */

    public function fullBack($params){
        $params['flag'] = SPC_ACTIVE_CONDITION_FLAG_FULL_BACK;
        $apiPath = "Base.SpcModule.Coupon.Center.getSuitable";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] !== 0 ){
            return $this->endInvoke(NULL,$res['status']);
        }
        return $this->endInvoke($res['response']);
    }

}

?>
