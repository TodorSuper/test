<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 支付回调模块
 */

namespace Bll\B2b\Pay;
use System\Base;

class PayBack extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        $this->pay_method_map = array(
            ALIPAY_WAP=>PAY_METHOD_ONLINE_ALIPAY,
            WEIXIN_JSAPI_PAY=>PAY_METHOD_ONLINE_WEIXIN,
            'UCPAY_DIRECT'=>PAY_METHOD_ONLINE_UCPAY,
        );
        parent::__construct();
    }
    
	/**
	 * 支付网关结果回调
     * Bll.B2b.Pay.PayBack.exeByPayGetway
     * @param mixed $params 
     * @access public
	 * @return void
	 * https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7 data参数介绍
     */
	public function exeByPayGetway($data){
		$_SERVER['HTTP_USER_AGENT'] = B2B;

		# 订单api完成订单相关的调用
		$payData = array(
			'op_code'=>$data['op_code'],
            'b2b_code'=>$data['oc_code'],
			'amount'=>$data['total_fee'],
            'pay_by' => $data['pay_by'],
			'pay_method'=>$this->pay_method_map[$data['pay_by']],
		);

		$first_num = substr($data['oc_code'],0,1);

		switch ($first_num) {
		case OC_ORDER:                                          # 订单api完成订单相关的调用
			$orderInfo = $this->orderPayBack($payData);
			break;

		case OC_ADVANCE_ORDER:									# 预付款订单api完成订单相关调用
		//	$this->advancePayBack($payData);
			break;
		default:
			return $this->res(null, 6052);
		}

		return $this->res(true);

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
     * 预付款订单发货信息
     * @access private
     */
    private function advanceMessage($amount,$mobile,$client_name,$client_commercial_name,$client_mobile){
        $message = "您的客户“{$client_commercial_name} {$client_name} {$client_mobile}” 有一笔预付款进行充值，充值金额为：{$amount}元，请注意查收，如有问题请拨打客服电话:400-815-5577";
         $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
    }



    /**
     * 订单api完成订单相关的调用
     * @access private
     * @author Todor
     */

    private function orderPayBack($params){

		$orderInfo = $this->invoke("Base.OrderModule.B2b.OrderInfo.payBack", $params);
		if($orderInfo['status'] !== 0) {
			return $this->endInvoke('', 6902); # 订单写入失败
		}

        //更新促销券的状态
        $coupon_code = $orderInfo['response'][0]['coupon_code'];
        if($coupon_code){
            $use_time = $orderInfo['response'][0]['pay_time'];
            $coupon_data = [
                'coupon_code'=>$coupon_code,
                'use_time'=>$use_time
            ];
            $res = $this->invoke('Base.UserModule.Coupon.Coupon.setStatus',$coupon_data);
            if($res['status']!==0){
                return $this->endInvoke('',$res['status']);
            }
        }
        // 发送短信
        $commercial_mobile = $orderInfo['response'][0]['mobile'];
        $real_name = $orderInfo['response'][0]['real_name'];

        $commercial_name = $orderInfo['response'][0]['commercial_name'];
        $salesman_id = $orderInfo['response'][0]['salesman_id'];
        $b2b_code = $orderInfo['response'][0]['oc_code'];
        $order_amount = $params['amount'];
        $pay_type = $orderInfo['response'][0]['pay_type'];
        $uc_code = $orderInfo['response'][0]['uc_code'];
        $sc_code = $orderInfo['response'][0]['sc_code'];
        $ship_method = $orderInfo['response'][0]['ship_method'];
        $pick_up_code = $orderInfo['response'][0]['pick_up_code'];

        //获取业务员信息信息
        $apiPath = "Base.UserModule.Customer.Salesman.get";
        $sales_res = $this->invoke($apiPath,array('salesman_id'=>$salesman_id));
        $mobile = $sales_res['response']['mobile'];
        // $this->goShipMessage($b2b_code, $mobile, $real_name, $commercial_name, $commercial_mobile,$order_amount);

        //如果是立即支付  则友盟推送 提示待发货
        if($pay_type == PAY_TYPE_ONLINE){
            // $this->push_queue('Com.Callback.Push.Queue.unshipWarn', array('sc_code'=>$sc_code),0);
            //如果是买家自提  则发送短信
            if($ship_method == SHIP_METHOD_PICKUP){
                $this->pickUpMessage($pick_up_code,$commercial_mobile);
            }
        }
        //给业务员发短信
        $smsApi = "Base.StoreModule.Basic.Sms.getLinkManInfo";
        $data = array(
            'uc_code' => $uc_code,
            'sc_code' => $sc_code,
            'sms_type' => 'NEW_ORDER',
            );
        $dataRes = $this->invoke($smsApi, $data);
        $saleMans = $dataRes['response'];
        // if ($saleMans) {
        //     foreach ($saleMans as $key => $value) {
        //         $this->goShipMessage($b2b_code, $value['phone'], $real_name, $commercial_name,$commercial_mobile,$order_amount);
        //     }
        // }

        $pay_time = $orderInfo['response'][0]['pay_time'];
        $pay_time = date('Y.m.d H:i:s', $pay_time);
        $amount   = $orderInfo['response'][0]['pay_price'];
        // $this->orderFinishedMessage($b2b_code, $amount, $commercial_mobile, $pay_time);
        // print_r(1);die;
        // 支付回调 记录状态 领取券  如果业务员为空则为平台用户
        if(empty($sales_res['response']) && empty($coupon_code)){
            $key = \Library\Key\RedisKeyMap::getCouponKey($b2b_code);
            $pay = \Library\Key\RedisKeyMap::getCouponHashKeyPay($b2b_code);
            $redis = R();
            $redis->Hset($key,$pay,1);
            $redis->setTimeout($key,259200);

            $coupon_params = array(
                'flag'=>SPC_ACTIVE_CONDITION_FLAG_FULL_BACK,
                'uc_code'=>$uc_code,
                'b2b_code'=>$b2b_code,
            );
            // $this->push_queue("Bll.B2b.User.Region.getCoupon",$coupon_params,1);

            // $res = $this->invoke("Bll.B2b.User.Region.getCoupon",$coupon_params);
        }


		return $orderInfo;
    }




    /**
     * 预付款订单api完成订单相关调用
     * @access private
     * @author Todor
     */

    private function advancePayBack($params){

    	// 获取预付款充值订单信息
        $apiPath = "Base.OrderModule.Advance.Order.get";
        $order_res = $this->invoke($apiPath,$params);
        if($order_res['status'] != 0){
            return $this->endInvoke(NULL,$order_res['status']);
        }

        $op_code     = $params['op_code'];                       # 预付款支付编码
        $operator_ip = $order_res['response']['operator_ip'];    # 用户IP
        $client_name = $order_res['response']['client_name'];    # 用户姓名
        $sc_code     = $order_res['response']['sc_code'];        # 商家编码
        $uc_code     = $order_res['response']['uc_code'];        # 用户编码
        $adv_code    = $order_res['response']['adv_code'];       # 预付款订单编码

        // 获取账户信息 取得tc_code
        $data = array(
            [
                'code'     =>$uc_code,
                'isPcode' => 'NO',
             ],
            );
        $apiPath = "Base.TradeModule.Account.Details.getAccontListByCode";
        $account_res = $this->invoke($apiPath,$data);
        if($account_res['status'] != 0){
            return $this->endInvoke(NULL,$account_res['status']);
        }

        $tc_code = $account_res['response'][$uc_code]['tc_code'];

        // 增加资金帐户余额
        $balance_data = array(
            'tc_code'      =>$tc_code,             # 资金帐户编码
            'oc_code'      =>$adv_code,            # 订单中心唯一编码
            'operatorType' =>'ADVANCE_RECHARGE',   # 增加金额的增加类型
            'operatorName' =>$client_name,         # 操作员名称
            'operatorIp'   =>$operator_ip,         # 操作员 ip
            'amount'       =>$params['amount'],    # 操作金额
            );
        $apiPath = "Base.TradeModule.Account.Balance.incr";
        $balance_res = $this->invoke($apiPath,$balance_data);
        if($balance_res['status'] != 0){
            return $this->endInvoke(NULL,$balance_res['status']);
        }

        // POP订货会客户资金增加或更新
        $customer_data = array(
            'sc_code'=>$sc_code,
            'uc_code'=>$uc_code,
            'advance_money'=>$params['amount'],
            'type'=>'add',
            );
        $apiPath = "Base.SpcModule.Commodity.Customer.add";
        $customer_res = $this->invoke($apiPath,$customer_data);
        if($customer_res['status'] != 0){
            return $this->endInvoke(NULL,$customer_res['status']);
        }

        // // 更新预付款订单
        $apiPath = "Base.OrderModule.Advance.Order.payBack";
        $payback_res = $this->invoke($apiPath, $params);
        if($payback_res['status'] != 0){
            return $this->endInvoke(NULL,$payback_res['status'],'',$payback_res['message']);
        }

        // 获取客户 与 业务员号码
        $mobile_data = array(
            'sc_code'=>$sc_code,
            'uc_code'=>$uc_code,
        );
        $apiPath = "Base.UserModule.Customer.Customer.get";
        $mobile_res = $this->invoke($apiPath,$mobile_data);
        if($mobile_res['status'] != 0){
            return $this->endInvoke(NULL,$mobile_res['status']);
        }

        // 发送短信
    //    $this->advanceMessage($params['amount'],$mobile_res['response']['salesman_mobile'],$mobile_res['response']['name'],$mobile_res['response']['commercial_name'],$mobile_res['response']['mobile']);

    }
    
     /**
     * 订单支付成功
     */
    private function orderFinishedMessage($b2b_code, $amount, $commercial_mobile, $pay_time){
        $message = "您的订单已支付成功；订单号：{$b2b_code}，支付金额：￥{$amount} ，支付时间：{$pay_time}。";
        
        $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($commercial_mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
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
    
}
?>
