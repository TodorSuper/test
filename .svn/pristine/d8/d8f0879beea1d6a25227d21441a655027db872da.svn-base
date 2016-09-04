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

namespace Bll\B2b\Order;

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
     * Bll.B2b.Order.OrderInfo.lists
     * @param type $params
     */
    public function lists($params) {
        $params['page'] =  $params['pageNumber'];
        $params['page_number'] = $params['pageSize'];
        $apiPath = "Base.OrderModule.B2b.OrderInfo.lists";
        $list_res = $this->invoke($apiPath, $params);
        $list_res['response']['pageTotalItem'] = $list_res['response']['totalnum'];
        $list_res['response']['pageTotalNumber'] = $list_res['response']['total_page'];

        // 获取sic_code 结果集 并组装标签
        if(!empty($list_res['response']['lists'])){

            $sic_codes = $temp = array();
            foreach ($list_res['response']['lists'] as $k => $v) {
                $temp[] = array_column($v['order_goods'],'sic_code');
            }

            // 合并sic_code
            foreach ($temp as $k => $v) {
                foreach ($v as $key => $value) {
                    $sic_codes[] = $value;
                }
            }
            $sic_codes = array_unique($sic_codes);
            $tag_parmas['sic_codes'] = $sic_codes;

            // 获取 商品对应标签
            $apiPath = "Base.ItemModule.Tag.Tag.getTags";
            $tags_res = $this->invoke($apiPath, $tag_parmas);
            foreach ($tags_res['response'] as $k => $v) {
                $temp_tag[$v['sic_code']][] = $v;
            }
            $tag_keys = array_keys($temp_tag);

            // 组装 标签
            foreach ($list_res['response']['lists'] as $k => $v) {
                foreach ($v['order_goods'] as $key => $value) {
                    if(in_array($value['sic_code'], $tag_keys)){
                        $list_res['response']['lists'][$k]['order_goods'][$key]['tags'] = $temp_tag[$value['sic_code']];
                    }
                }
            }
        }
        return $this->endInvoke($list_res['response'], $list_res['status']);
    }

    /**
     * 订单详情
     * Bll.B2b.Order.OrderInfo.get
     * @param type $params
     */
    public function get($params) {
        $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        $order_info_res = $this->invoke($apiPath, $params);
        if ($order_info_res['status'] != 0) {
            return $this->endInvoke(NULL, $order_info_res['status']);
        }
        $sc_code = $order_info_res['response']['order_info']['sc_code'];
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $store_res = $this->invoke($apiPath, array('sc_code' => $sc_code));
        if ($store_res['status'] != 0) {
            return $this->endInvoke(NULL, $store_res['status']);
        }
        $order_info_res['response']['store_info'] = $store_res['response'];
        return $this->endInvoke($order_info_res['response']);
    }

    /**
     * 订单操作
     * Bll.B2b.Order.OrderInfo.operate
     * @param type $params
     */
    public function operate($params) {
        try {
            D()->startTrans();
            $apiPath = "Base.OrderModule.B2b.OrderInfo.operate";
            $add_res = $this->invoke($apiPath, $params);
            if ($add_res['status'] != 0) {
                return $this->endInvoke(NULL, $add_res['status'], '', $add_res['message']);
            }
            $params['pay_method'] = $add_res['response']['order_info']['pay_method'];
            $params['pay_type'] = $add_res['response']['order_info']['pay_type'];
            $api = 'Base.OrderModule.B2b.OrderAction.orderActionUp';
            $res = $this->invoke($api,$params);
            if ($res['status'] != 0) {
                return $this->endInvoke(null,$res['status']);
            }
            //订单商品信息
            $order_goods = $add_res['response']['order_goods'];
            $uc_code = $add_res['response']['order_info']['uc_code'];
            $order_info = $add_res['response']['order_info'];
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
            //如果是完成交易  则需要增加客户的订单数和金额
            if($params['status'] == OC_ORDER_GROUP_STATUS_COMPLETE){
                $sc_code = $order_info['sc_code'];
                $amount = $order_info['real_amount'];
                $this->updateCustomerOrderInfo($sc_code, $uc_code, $amount);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 6022);
        }
    }

    /**
     * 订单操作
     * Bll.B2b.Order.OrderInfo.payBack
     * @param type $params
     */
    public function payBack($params) {
        try {
            D()->startTrans();
            $apiPath = "Base.OrderModule.B2b.OrderInfo.payBack";
            $res = $this->invoke($apiPath, $params);
            
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            
            //发送短信
            $commercial_mobile = $res['response'][0]['mobile'];
            $real_name = $res['response'][0]['real_name'];
            $commercial_name = $res['response'][0]['commercial_name'];
            $salesman_id = $res['response'][0]['salesman_id'];
            $b2b_code = $res['response'][0]['oc_code'];
            $uc_code = $res['response'][0]['uc_code'];
            $sc_code = $res['response'][0]['sc_code'];
                //获取业务员信息信息
                $apiPath = "Base.UserModule.Customer.Salesman.get";
                $sales_res = $this->invoke($apiPath,array('salesman_id'=>$salesman_id));
                $mobile = $res['response'][0]['mobile'];
            $this->goShipMessage($b2b_code, $mobile, $real_name, $commercial_name, $commercial_mobile);

            //给自定义接收人发短信
            $smsApi = "Base.StoreModule.Basic.Sms.getLinkManInfo";
            $data = array(
                // 'uc_code' => $uc_code,
                'sc_code' => $sc_code,
                'sms_type' => 'NEW_ORDER',
                );
            $dataRes = $this->invoke($smsApi, $data);
            $saleMans = $dataRes['response'];
            if ($saleMans) {
               foreach ($saleMans as $key => $value) {
                   $this->goShipMessage($b2b_code, $value['phone'], $real_name,$commercial_name,$commercial_mobile);
               } 
            }

            return $this->endInvoke($res['response'],$res['status']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 6022);
        }
    }
    
    /**
     * 订单发货发送短信提醒
     */
    private function goShipMessage($b2b_code,$mobile, $client_name,$client_commercial_name,$client_mobile){
        $message = "您的客户“{$client_commercial_name} {$client_name} {$client_mobile}”有一个订单需要发货，订单号：{$b2b_code}，请尽快发货。";
        
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }
    
    /**
     * 重新下单  放入购物车
     * Bll.B2b.Order.OrderInfo.reAddOrder
     * @param type $params
     */
    public function reAddOrder($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),  //用户编码
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK), //订单支付编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //查询购买的商品
        $apiPath = "Base.OrderModule.B2b.OrderInfo.simpleGet";
        $data = array(
            'uc_code' => $params['uc_code'],
            'op_code' => $params['op_code'],
            'need_order_goods'=>'YES',
        );
        $order_res = $this->invoke($apiPath,$data);
        if($order_res['status'] != 0){
            return $this->endInvoke(NULL,$order_res['status']);
        }
        $order_goods = $order_res['response']['order_goods'];
        $sic_codes = array_unique(array_column($order_goods, 'sic_code'));
        $sc_code = $params['sc_code'];
        //获取商品信息
        $apiPath = "Base.StoreModule.Item.Item.storeItems";
        $item_param = array(
            'sc_code'=>$sc_code,
            'sic_codes'=>$sic_codes,
            'page_number' => 100,
            'stock_gt' => 'YES',
        );
        $item_res = $this->invoke($apiPath,$item_param);
        if($item_res['status'] != 0 || empty($item_res['response'])){
            return $this->endInvoke(NULL,$item_res['status'],'',$item_res['message']);
        }
        
        //重新购买  如果商品已经下架 或库存为0不显示   ，如果剩余库存小于原来购物车购买数量  则购买数量以库存为准 
        $items = $item_res['response']['lists'];
//        $numbers = array_column($order_goods, 'goods_number');
        $sic_numbers = array();
        foreach($order_goods as $k=>$v){
            if(isset($sic_numbers[$v['sic_code']])){
                $sic_numbers[$v['sic_code']] += $v['goods_number'];
            }else {
                $sic_numbers[$v['sic_code']] = $v['goods_number'];
            }
        }
        
        
//        $sic_numbers = array_combine($sic_codes, $numbers);
        $numbers = array();
        $sic_codes = array();
        foreach($items as $k=>$v){
           $sic_codes[] = $v['sic_code'];
           $numbers[] = min($v['stock'],$sic_numbers[$v['sic_code']]);
        }
        
        try {
            D()->startTrans();
            
            //清空之前的购物车
            $apiPath = "Base.UserModule.Cart.Cart.delete";
            $delete_params = array(
                'uc_code' => $params['uc_code'],
            );
            $delete_res = $this->invoke($apiPath,$delete_params);
            if($delete_res['status'] != 0){
                return $this->endInvoke(NULL,$delete_res['status']);
            }
            $add_params = array(
                'uc_code' => $params['uc_code'],
                'sic_codes' => $sic_codes,
                'numbers' => $numbers,
                'sc_code' => $sc_code,
            );
            $apiPath = "Base.UserModule.Cart.Cart.batAdd";
            $add_res = $this->invoke($apiPath,$add_params);
            if($add_res['status'] != 0){
                return $this->endInvoke(NULL,$add_res['status']);
            }
            
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 6022);
        }
    }
    
    /**
     * 
     * Bll.B2b.Order.OrderInfo.sendRemitSms
     * @param type $params
     */
    public function sendRemitSms($params){
        $remit_code = $params['remit_code'];
        $remit_bank = $params['remit_bank'];
        $is_order_list = $params['is_order_list'];
        try{
             D()->startTrans();
           
             //获取订单信息
             $apiPath = "Base.OrderModule.B2b.OrderInfo.simpleGet";
             $order_data = array(
                 'op_code'=>$params['op_code'],
             );
             $order_res = $this->invoke($apiPath,$order_data);
             $b2b_code = $order_res['response']['b2b_code'];
             //添加银行转账支付方式  如果之前没有生成
             if($is_order_list == 1){
                 $this->checkRemitBank($remit_bank, $b2b_code);
             }
             
            //更新支付方式
            $apiPath  = "Base.OrderModule.B2b.OrderInfo.changePayMethod";
            $pay_params = array(
                'op_code' => $params['op_code'],
                'uc_code' => $params['uc_code'],
                'pay_method' => PAY_METHOD_ONLINE_REMIT,
            );
            $pay_res = $this->invoke($apiPath,$pay_params);
            if($pay_res['status'] != 0){
                return $this->endInvoke(NULL,$pay_res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
        } catch (\Exception $ex) {
               D()->rollback();
               return $this->endInvoke(NULL, 6026);
        }
        //发短信
        $uc_code = $params['uc_code'];
        
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $userInfo = $this->invoke($apiPath, $params);
        $userInfo = $userInfo['response'];
        $mobile = $userInfo['mobile'];
        $amount = $params['amount'];
        //根据  op_code 获取  b2b_code 
        
        
        $message = $this->getRemitMessage($remit_code, $amount, $remit_bank);
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
        return $this->res($remit_code);
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
    
    
     //更新客户的下单金额  和  下单数量
    private function updateCustomerOrderInfo($sc_code,$uc_code,$amount){
        //判断是什么用户
        $apiPath = "Base.UserModule.Customer.Customer.get";
        $params = array(
            'uc_code'=>$uc_code,
        );
        $customer_res = $this->invoke($apiPath, $params);
        if($customer_res['status'] != 0){
            return ;
        }
        
         $invite_from  =  $customer_res['response']['invite_from'];
        if($invite_from == 'UC'){
            //如果是  平台邀请  则直接返回
            return ;
        }
        
        $apiPath = "Base.UserModule.Customer.Customer.update";
        $data = array(
            'sc_code' => $sc_code,
            'uc_code' => $uc_code,
            'orders'  => 1,
            'order_amount' => $amount,
            'order_time' => NOW_TIME,
        );
        $res = $this->invoke($apiPath,$data);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }
        return TRUE;
    }
    
   
    
    /**
     * 获取招行的 消息信息
     */
    private function  getRemitMessage($remit_code,$amount,$remit_bank){
        $remit_banks = array(
            'CMB' => '招商银行北京分行营业部',
            'CMBC'=> '民生银行北京常营支行',
        );
        
        $remit_bank_nos = array(
            'CMB' => '110918380910206',
            'CMBC'=> '695446572',
        );
        $remit_bank_name = $remit_banks[$remit_bank];
        $bank_no = $remit_bank_nos[$remit_bank];
        $message = "
您的汇款码是:{$remit_code},汇款时请务必填写至备注，请您将订单应付金额转入下方银行账户。如有问题请拨打客服电话：400-815-5577,
开户名：北京双磁信息科技有限公司
开户行：{$remit_bank_name}
账号：{$bank_no}
应付款：￥{$amount}";
        return $message;
    }
    
    /**
     * 添加银行转账扩展信息
     * @param type $remit_bank
     * @param type $oc_code
     */
    private function checkRemitBank($remit_bank,$oc_code){
        if(empty($remit_bank)){
            return $this->endInvoke(NULL,6032);
        }
        //获取支持的银行
        $remit_bank_list = M('Base.OrderModule.B2b.Status')->getRemitBank();
        $remit_bank_list = array_keys($remit_bank_list);
        if(!in_array($remit_bank, $remit_bank_list)){
            return $this->endInvoke(NULL,6033);
        }
        //添加扩展信息
        $apiPath = "Base.OrderModule.Center.OrderPayExtend.add";
        $data = array(
            'oc_code'=>$oc_code,
            'ext1'   => $remit_bank,
        );
        $res =  $this->invoke($apiPath,$data);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }
        return true;
    }
    
    /**
     * 
     * Bll.B2b.Order.OrderInfo.pickUpCode
     * @param type $params
     */
    public function pickUpCode($params) {
        $apiPath = 'Base.OrderModule.B2b.OrderInfo.pickUpCode';
        $res = $this->invoke($apiPath,$params);
        if ($res['status'] != 0) {
            return $this->endInvoke($res['message'],$res['status']);
        }
        return $this->endInvoke($res['response']);
    }
    /**
     * 
     * Bll.B2b.Order.OrderInfo.isPaySuccess
     * @param type $params
     */
    public function isPaySuccess($params) {
        $apiPath = 'Base.OrderModule.B2b.OrderInfo.isPaySuccess';
        $res = $this->invoke($apiPath,$params);
        if ($res['status'] != 0) {
            return $this->endInvoke($res['message'],$res['status']);
        }
        return $this->endInvoke($res['response']);
    }
        /*
    * Bll.B2b.Order.OrderInfo.getOrderOpCode
    * 获取订单号
    */

    public function getOrderOpCode ($params) {
        $apiPath = 'Base.OrderModule.B2b.OrderInfo.getOrderOpCode';
        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response'], $res['status'], $res['message']);
    }


    /**
     * 订单判断是否领取到券
     * Bll.B2b.Order.OrderInfo.checkCoupon
     * @access public
     */

    public function checkCoupon($params){
        $b2b_code = $params['b2b_code'];
        // 是否展示过
        $key = \Library\Key\RedisKeyMap::getCouponKey($b2b_code);
        $hashKey = \Library\Key\RedisKeyMap::getCouponHashKey($b2b_code);
        $coupon = \Library\Key\RedisKeyMap::getCouponHashKeyLog($b2b_code);
        $pay =  \Library\Key\RedisKeyMap::getCouponHashKeyPay($b2b_code);
        $redis = R();
        $show = $redis->hGet($key,$hashKey);

        // 是否领取券
        $show = $redis->hGet($key,$hashKey);
        $coupon = $redis->hGet($key,$coupon);
        $pay = $redis->hGet($key,$pay);

        $data = array(
            'show'=>empty($show) ? 0 : $show,
            'coupon'=>empty($coupon) ? 0 : $coupon,
            'pay'=>empty($pay) ? 0 : $pay,
            );
        return $this->endInvoke($data);
    }

}

?>
