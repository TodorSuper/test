<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop订单列表相关的操作
 */

namespace Bll\Pop\Order;

use System\Base;

class OrderInfo extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * 
     * 订单列表
     * pop  显示子订单（无论支付还是未支付）
     * b2b  未支付显示总订单    已支付显示子订单信息 
     * cms  未支付显示总订单    已支付显示子订单信息
     *  
     * sc_code    店铺编码  pop平台的
     * uc_code    用户编码  用户平台的(weixin  app)
     * b2b_code   订单编码
     * start_time 订单下单开始时间
     * end_time   订单下单结束时间
     * username   用户名
     * status  订单状态  组合状态
     * 
     * Bll.Pop.Order.OrderInfo.lists
     * @param type $params
     */
    public function lists($params){
        $params['need_total_amount'] = 'YES';
        $apiPath = "Base.OrderModule.B2b.OrderInfo.lists";
        $list_res = $this->invoke($apiPath, $params);
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        $list_res['response']['pay_methods'] = $status->getPayMethod();
        $list_res['response']['pay_types'] = $status->getPayType();
        $list_res['response']['ship_method'] = M('Base.OrderModule.B2b.Status.getPayType')->getPayType();
        $custrom_info['sc_code'] = $params['sc_code'];
        $custrom = $this->invoke('Bll.Pop.Customer.Customer.search',$custrom_info);
        $list_res['response']['custrom'] = $custrom['response'];

        $sales = $this->invoke('Bll.Pop.Customer.Salesman.search',$custrom_info);
        $list_res['response']['sales'] = $sales['response'];

        $channel = $this->invoke('Bll.Pop.Customer.Channel.search',$custrom_info);

        $list_res['response']['channel'] = $channel['response'];
        if ($params['status'] == 'ALL' || $params['status'] == 'UNSHIP') {
            M('Base.StoreModule.Order.Operation.loginSaveData')->loginSaveData($custrom_info);
        }
        //获取商家列表
        $customer_list = $this->invoke('Base.UserModule.Customer.Customer.commercialList',array('sc_code'=>$params['sc_code']));
        $customer_list = $customer_list['response'];
        $customer_list = array_unique(array_column($customer_list, 'commercial_name'));
        $list_res['response']['commercial_list'] = $customer_list;
 
        return $this->res($list_res['response'],$list_res['status']);
    }
    
    
    /**
     * 订单详情
     * Bll.Pop.Order.OrderInfo.get
     * @param type $params
     */
    public function get($params){
        $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        $order_info_res = $this->invoke($apiPath, $params);
        $params['trans'] = true;
        $apiPath = 'Base.OrderModule.B2b.OrderAction.getOrderStatus';
        $order_status = $this->invoke($apiPath,$params);
        if($order_status['status']!==0){
            return $this->endInvoke('',$order_status['status']);
        }
        $total=array(
            'order_info_res'=>$order_info_res['response'],
            'order_status'=>$order_status['response']
        );
        return $this->res($total,$order_info_res['status']);
    }

    /**
     * 订单详情
     * Bll.Pop.Order.OrderInfo.changePrice
     * @param type $params
     */
    public function changePrice($params){
        try{
            D()->startTrans();
            $apiPath='Base.OrderModule.B2b.ChangePrice.changePrice';
            $res=$this->invoke($apiPath,$params);
            $result = D()->commit();
            if($result === FALSE){
                throw new \Exception('提交事务失败',17);
            }
        }catch (\Exception $ex){
            D()->rollback();
            return $this->endInvoke(NULL,7039);
        }
        if($res['response']['flag']){
            if($res['response']['pay_method']==PAY_METHOD_ONLINE_REMIT){
                $time=date('Y-m-d H:i:s',NOW_TIME);
                $message="您有一笔订单价格于{$time}被修改，汇款码：{$res['response']['single']['remit_code']}，请进入”我的订单“中查看";
            }else{
                $time=date('Y-m-d H:i:s',NOW_TIME);
                $message="您有一笔订单价格于{$time}被修改,请进入”我的订单“中查看";
            }
            $this->goShipMessage($res['response']['single']['mobile'],$message);
        }
        return $this->endInvoke($res['response'],$res['status']);
    }
    /**
     * 订单详情
     * Bll.Pop.Order.OrderInfo.operate
     * @param type $params
     */
    public function operate($params) {
        $pop_uc_code = $params['uc_code'];
        if (isset($params['uc_code'])) unset($params['uc_code']);
        try {
            D()->startTrans();
            $apiPath = "Base.OrderModule.B2b.OrderInfo.operate";
            $add_res = $this->invoke($apiPath, $params);
            if ($add_res['status'] != 0) {
                return $this->endInvoke(NULL, $add_res['status'], '', $add_res['message']);
            }
//            $api = 'Base.StoreModule.Basic.Store.get';
//            $uc_code['sc_code'] = $params['sc_code'];
//            $res = $this->invoke($api,$uc_code);
//            $params['uc_code'] = $res['response']['uc_code'];
            $params['pay_method'] = $add_res['response']['order_info']['pay_method'];
            $params['pay_type'] = $add_res['response']['order_info']['pay_type'];
            $params['uc_code'] = $pop_uc_code;
            $api = 'Base.OrderModule.B2b.OrderAction.orderActionUp';

            $res = $this->invoke($api,$params);
            if ($res['status'] != 0) {
                return $this->endInvoke(null,$res['status']);
            }
            
            //订单商品信息
            $order_goods = $add_res['response']['order_goods'];
            $uc_code = $add_res['response']['order_info']['uc_code'];
            $mobile = $add_res['response']['order_info']['mobile'];
            unset($params['uc_code']);
            //促销商品需要回滚购买数量
            $data = array();
            foreach($order_goods as $k=>$v){
                if(!empty($v['spc_code'])){
                    $data[] = array(
                        'spc_code'=>$v['spc_code'],
                        'number' => $v['goods_number'],
                        'uc_code'=>$uc_code,
                        'spc_type'=>$v['spc_type'],
                    );
                }
            }
            if(!empty($data) && $params['status'] == OC_ORDER_GROUP_STATUS_CANCEL){
                $this->rollbackSpcBuyLimit($data);
            }
            
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            
            if($params['status'] == OC_ORDER_GROUP_STATUS_SHIPPED){
                //如果是发或  则发送发货短息
                $this->shipMessage($params['b2b_code'],$mobile);
            }
            
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 6022);
        }
    }
    /*
     * Bll.Pop.Order.OrderInfo.getOrderStatus
     * $param b2b_code
     */
    public function getOrderStatus($param) {
        $api = 'Base.OrderModule.B2b.OrderAction.getOrderStatus';
        $res = $this->invoke($api,$param);
        if (empty($res['response'])) {
            return $this->res(null);
        } else {
            return $this->res($res['response']);
        }
    }
    
    /**
     * 取消订单的时候回滚促销购买的数量
     * @param type $data
     */
    private function rollbackSpcBuyLimit($data){
        $apiPath  = "Base.SpcModule.Center.BuyNumber.sub";
        foreach($data as $k=>$v){
            if($v['spc_type'] == SPC_TYPE_LADDER){
                continue;
            }
            $res = $this->invoke($apiPath,$v);
            if($res['status'] != 0){
                throw new \Exception($res['message'],$res['status']);
            }
        }
        return TRUE;
    }
    
    /**
     * 订单发货发送短信提醒
     */
    private function shipMessage($b2b_code,$mobile){
        $message = "您有一个订单已经发货，请注意查收。订单号：{$b2b_code}。";
        
        $data = array(
            'sys_name'=>POP,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }
    /*
    * Bll.Pop.Order.OrderInfo.getLastTime
    * 获取最后订单的数量
    */
    public function getLastTime($params) {
        $api = 'Base.StoreModule.Order.Operation.getLastQueryTime';
        $res = $this->invoke($api,$params);
        return $this->res($res['response']);
    }

    /**
     * 订单改价发送短信提醒
     */
    private function goShipMessage($mobile,$message){
        $data = array(
            'sys_name'=>POP,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
        return true;
    }
    public function checkPickUpCode($params) {
        //取货码状态查询
        $res = $this->invoke('Base.OrderModule.B2b.OrderInfo.get',$params);

        if ( $res['status'] != 0 && $res['status'] != 6062) {
            return $this->endInvoke($res['message'],$res['status']);
        }
        //更新订单状态

        $info = $res['response']['order_info'];
        if ($res['status'] == 6062) {
            $info = $res['response'];
        }

        //获取订单数据
        $rows = $this->invoke('Base.OrderModule.B2b.OrderInfo.lists',array('sc_code'=>$params['sc_code'],'b2b_code'=>$info['b2b_code']));
        if ($res['status'] == 6062) {
            return $this->endInvoke($info['b2b_code'],6062);
        }
        $status = $info['pay_type'] == PAY_TYPE_ONLINE ? OC_ORDER_GROUP_STATUS_COMPLETE : OC_ORDER_GROUP_STATUS_TAKEOVER;

        $params = array(
            'status' => $status,
            'b2b_code' => $info['b2b_code'],
            'sc_code' => $info['sc_code'],
            'uc_code' => $params['uc_code'],

        );

        $pop_uc_code = $params['uc_code'];

        if (isset($params['uc_code'])) unset($params['uc_code']);
        try {
            D()->startTrans();
            $apiPath = "Base.OrderModule.B2b.OrderInfo.operate";

            $add_res = $this->invoke($apiPath, $params);

            if ($add_res['status'] != 0) {
                return $this->endInvoke(NULL, $add_res['status'], '', $add_res['message']);
            }
//            $api = 'Base.StoreModule.Basic.Store.get';
//            $uc_code['sc_code'] = $params['sc_code'];
//            $res = $this->invoke($api,$uc_code);
//            $params['uc_code'] = $res['response']['uc_code'];
            $params['pay_method'] = $add_res['response']['order_info']['pay_method'];
            $params['pay_type'] = $add_res['response']['order_info']['pay_type'];
            $params['uc_code'] = $pop_uc_code;
            $params['ship_method'] = $info['ship_method'];
            $api = 'Base.OrderModule.B2b.OrderAction.orderActionUp';

            $res = $this->invoke($api,$params);
          
            if ($res['status'] != 0) {
                return $this->endInvoke(null,$res['status']);
            }
            
            //订单商品信息
            $order_goods = $add_res['response']['order_goods'];
            $uc_code = $add_res['response']['order_info']['uc_code'];
            $mobile = $add_res['response']['order_info']['mobile'];
            unset($params['uc_code']);
            //促销商品需要回滚购买数量
            $data = array();
            foreach($order_goods as $k=>$v){
                if(!empty($v['spc_code'])){
                    $data[] = array(
                        'spc_code'=>$v['spc_code'],
                        'number' => $v['goods_number'],
                        'uc_code'=>$uc_code,
                        'spc_type'=>$v['spc_type'],
                    );
                }
            }
            if(!empty($data) && $params['status'] == OC_ORDER_GROUP_STATUS_CANCEL){
                $this->rollbackSpcBuyLimit($data);
            }
            
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            
            if($params['status'] == OC_ORDER_GROUP_STATUS_SHIPPED){
                //如果是发或  则发送发货短息
                $this->shipMessage($params['b2b_code'],$mobile);
            }
            
            return $this->endInvoke($info['b2b_code']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 6022);
        }
    }
}

?>
