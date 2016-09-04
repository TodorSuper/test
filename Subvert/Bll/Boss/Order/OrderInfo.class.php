<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | APP订单列表相关的操作
 */

namespace Bll\Boss\Order;

use System\Base;

class OrderInfo extends Base {
	public function __construct() {
        parent::__construct();
    }

   /*
    * APP订单列表接口
    * Bll.Boss.Order.OrderInfo.lists
    */
    public function lists($params) {
        $_SERVER['HTTP_USER_AGENT'] = POP;
    	$params['page'] = isset($params['pageNumber']) ? $params['pageNumber']:1;
    	$params['page_number'] = isset($params['pageSize']) ? $params['pageSize']:20;
        if (isset($params['sort'])) {
            if ($params['sort'] == 'CREATE_TIME_DESC') {
                $params['sort'] = 'DESC';
            } else {
                $params['sort'] = 'ASC';
            }
        }
        if (isset($params['uc_code'])) unset($params['uc_code']);
    	$res = $this->invoke('Base.OrderModule.B2b.OrderInfo.lists',$params);

    	//组装数据
    	$data = array();
    	if ($res['response']['totalnum'] == 0) {
    		$data['pageTotalItem'] = 0;
    		$data['pageTotalNumber'] = 0;
    		$data['list'] = array();
    	} else {
    		$data['pageTotalNumber'] = $res['response']['total_page'];
    		$data['pageTotalItem'] = $res['response']['totalnum'];
    		foreach($res['response']['lists'] as $k => $v) {
    			$data['lists'][$k]['b2b_code'] = $v['b2b_code'];
    			$data['lists'][$k]['status_message'] = $v['status_message'];
    			$data['lists'][$k]['client_name'] = $v['client_name'];
    			$data['lists'][$k]['commercial_name'] = $v['commercial_name'];
    			$data['lists'][$k]['payMethod'] = $v['payMethod'];
    			$data['lists'][$k]['payType'] = $v['payType'];
    			$data['lists'][$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
    			$data['lists'][$k]['real_amount'] = $v['cope_amount'];
    			$data['lists'][$k]['is_remark'] = $v['is_remark'];
    			$data['lists'][$k]['operate'] = $v['operate'];
                $temp =  array(
                    'pay_type'=>$v['pay_type'],
                    'ship_status'=>$v['ship_status'],
                    'order_status'=>$v['order_status'],
                    'pay_status'=>$v['pay_status'],
                    );
                if(update_price($temp) && empty($v['coupon_code'])){                                # 没有使用优惠券才可以改价
                    $data['lists'][$k]['operate'][] = array(
                        'message'=>'订单改价',
                        'status'=>'CHANGE',
                        );
                }

                $data['lists'][$k]['have_coupon'] = empty($v['coupon_code']) ? "NO" : "YES";        # 用没用优惠券

                foreach ($v['order_goods'] as $key => $value) {
                    if($value['before_goods_price'] !== $value['goods_price']){
                        $data['lists'][$k]['is_change'] = "YES";
                        break;
                    }
                    $data['lists'][$k]['is_change'] = "NO";
                }
    		}
    	}
    	return $this->endInvoke($data);
    }
    
    /*
    * Bll.Boss.Order.OrderInfo.operator
    * APP订单状态操作接口
    */
    public function operator($params) {
        $_SERVER['HTTP_USER_AGENT'] = POP;
        $pop_uc_code = $params['uc_code'];
        if (isset($params['uc_code'])) unset($params['uc_code']);
        try {
            D()->startTrans();
            $apiPath = "Base.OrderModule.B2b.OrderInfo.operate";
            $add_res = $this->invoke($apiPath, $params);

            if ($add_res['status'] != 0) {
                return $this->endInvoke(NULL, $add_res['status'], '', $add_res['message']);
            }
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
            $order_info = $add_res['response']['order_info'];
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
            //如果是完成交易  则需要增加客户的订单数和金额
            if($params['status'] == OC_ORDER_GROUP_STATUS_COMPLETE){
                $sc_code = $order_info['sc_code'];
                $amount = $order_info['cope_amount'];
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
    /*
    * Bll.Boss.Order.OrderInfo.get
    * APP订单详情
    */
    public function get($params) {
        $_SERVER['HTTP_USER_AGENT'] = POP;
    	$apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        if (isset($params['uc_code'])) unset($params['uc_code']);
        $order_info_res = $this->invoke($apiPath, $params);
        if ($order_info_res['status'] != 0) {
        	return $this->endInvoke(null,$order_info_res['status']);
        }
        $info = $order_info_res['response'];

        $data['order_info'] = array(
        	'client_name' => $info['order_info']['client_name'],
        	'commercial_name' => $info['order_info']['commercial_name'],
        	'status_message' => $info['order_info']['status_message'],
        	'b2b_code' => $info['order_info']['b2b_code'],
        	'create_time' => date('Y-m-d H:i:s',$info['order_info']['create_time']),
        	'real_name' => $info['order_info']['real_name'],
        	'mobile' => $info['order_info']['mobile'],
        	'address' => $info['order_info']['city'].$info['order_info']['district'].$info['order_info']['address'],
        	'real_amount' => $info['order_info']['real_amount'],
            'order_amount' => $info['order_info']['before_goods_amount'],
        	'operate' => $info['order_info']['operate'],
            'ship_method_message' => $info['order_info']['ship_method_message'],
            'remark'              => !empty($info['order_info']['remark']) ? $info['order_info']['remark'] : '',
            'coupon_amount'       => $info['order_info']['coupon_amount'],
            //添加是否验证通过的判断,通过验证则显示取货码，否则不现实取货码  //todo
            'pick_up_code'        => (!empty($info['order_info']['pick_up_code']) && $info['order_info']['ship_status'] == 'TAKEOVER') ? $info['order_info']['pick_up_code'] : '',
            'takeover_time'       => (!empty($info['order_info']['pick_up_code']) && ($info['order_info']['ship_status'] == 'TAKEOVER') && !empty($info['order_info']['takeover_time'])) ? date("Y-m-d H:i:s",$info['order_info']['takeover_time']) : '',
        );

        // 判断能否改价
        $temp =  array(
            'pay_type'=>$info['order_info']['pay_type'],
            'ship_status'=>$info['order_info']['ship_status'],
            'order_status'=>$info['order_info']['order_status'],
            'pay_status'=>$info['order_info']['pay_status'],
            );
         if(update_price($temp) && empty($info['order_info']['coupon_code'])){
            $data['order_info']['operate'][] = array(
                'message'=>'订单改价',
                'status'=>'CHANGE',
                );
         }

        $data['pay_info'] = array(
        	'pay_method_message' => isset($info['order_info']['pay_method_message']) ? $info['order_info']['pay_method_message']:'',
        	'pay_type_message' => $info['order_info']['pay_type_message'],
        	'pay_time' => $info['pay_info']['pay_time'] != 0 ? date('Y-m-d H:i:s',$info['pay_info']['pay_time']):'',
        	'pay_no' => isset($info['pay_info']['pay_no']) ? $info['pay_info']['pay_no']:'',
        );
        $data['order_goods'] = array();

        foreach ($info['order_goods'] as $k => $v) {
        	$data['order_goods'][$k]['goods_name'] = $v['goods_name'];
        	$data['order_goods'][$k]['spec'] = $v['spec'];
        	$data['order_goods'][$k]['goods_price'] = $v['goods_price'];
        	$data['order_goods'][$k]['goods_number'] = $v['goods_number'];
            $data['order_goods'][$k]['packing'] = $v['packing'];
            $data['order_goods'][$k]['spc_flag'] = $v['spc_type'];
        	$data['order_goods'][$k]['spc_type'] = !empty($v['spc_type'])?get_spc($v['spc_type']):'';
        	$data['order_goods'][$k]['spc_message'] = isset($v['spc_message']) ? $v['spc_max_buy'] != 0 ? '限购'.$v['spc_max_buy'].$v['packing'].','.$v['spc_message'] :$v['spc_message'] :'';
            if ($v['spc_type'] == 'SPECIAL') {
                $data['order_goods'][$k]['spc_type'] = $v['special_type'] == 'FIXED' ? '一口价':'折扣';
            }
            $data['order_goods'][$k]['special_type'] = isset($v['special_type']) ? $v['special_type']:'';
            $data['order_goods'][$k]['special_type'] = isset($v['special_type']) ? $v['special_type']:'';
        	if (isset($v['spc_goods'])) {
    			$data['order_goods'][$k]['spc_goods']['goods_name'] = $v['spc_goods']['goods_name'];
    			$data['order_goods'][$k]['spc_goods']['goods_price'] = 0;
    			$data['order_goods'][$k]['spc_goods']['goods_number'] = $v['spc_goods']['goods_number'];
    			$data['order_goods'][$k]['spc_goods']['spec'] = $v['spc_goods']['spec'];
                $data['order_goods'][$k]['spc_goods']['packing'] = $v['spc_goods']['packing'];
                $data['order_goods'][$k]['spc_code'] = $v['spc_goods']['b2b_code'];
        	} else {
                $data['order_goods'][$k]['spc_code'] = '';
            }

            if($v['before_goods_price'] !== $v['goods_price']){
                $data['order_info']['is_change'] = "YES";
            }

        }

        if(!isset($data['order_info']['is_change'])){
            $data['order_info']['is_change'] = "NO";
        }
       
        return $this->endInvoke($data);
    }
 
    /*
    * Bll.Boss.Order .OrderInfo.accountSearch
    * 账期列表
    */
    public function accountSearch($params) {
    	if (isset($params['uc_code'])) unset($params['uc_code']);
   		$params['pageNumber'] = isset($params['pageNumber']) ? $params['pageNumber'] : 1;
   		$params['pageSize']   = isset($params['pageSize']) ? $params['pageSize'] : 20;
        $params['aggre']      = array(array('sum','cope_amount','toal_amount'));
        $params['group']      = ' group by uc_code';
    	$data = $this->invoke('Base.OrderModule.B2b.Account.accountLists',$params);
        $params['group']      = ' group by sc_code';
        $total = $this->invoke('Base.OrderModule.B2b.Account.accountLists',$params);
    	if ($data['status'] == 0) {
    		$newData = array();
        
    		foreach($data['response']['lists'] as $k => $v) {
    			$newData[$v['uc_code']]['commercial_name'] = $v['commercial_name'];
    			$newData[$v['uc_code']]['client_name'] = $v['client_name'];
    			$newData[$v['uc_code']]['mobile'] = $v['mobile'];
    			$newData[$v['uc_code']]['money'] = $v['money'];
                $newData[$v['uc_code']]['o_uc_code'] = $v['uc_code'];
             
    		}
    		if ($data['response']['toal_amount'] == 0) {
    			$this->endInvoke(array('lists'=>array()),0);
    		}
    		$res['lists'] = array_values($newData);
    		
            $res['toal_amount'] = (string)$total['response']['toal_amount'];
    		$res['pageTotalItem'] = $data['response']['totalnum'];
    		$res['pageTotalNumber'] = $data['response']['total_page'];
            $res['need_check'] = array('commercial_name','client_name');
    		return $this->endInvoke($res);
    	} else {
    		return $this->endInvoke(array('lists'=>array()),$data['status']);
    	}
    }
    /*
    * 账期详情
    * Bll.Boss.Order .OrderInfo.accountInfo
    */
    public function accountInfo($params) {
   
   		$params['pageNumber'] = isset($params['pageNumber']) ? $params['pageNumber'] : 1;
   		$params['pageSize']   = isset($params['pageSize']) ? $params['pageSize'] : 20;
   		if (isset($params['sc_code'])) unset($params['sc_code']);
        $params['uc_code'] = $params['o_uc_code'];
    	$data = $this->invoke('Base.OrderModule.B2b.Account.accountLists',$params);
    	if ($data['status'] != 0) {
    		$this->endInvoke(null,$data['status']);
    	}
    	$sort = array();
    	$result = array();
    	$count = $data['response']['totalnum'];
    	foreach ($data['response']['lists'] as $k => $v) {
    		$result['lists'][$k]['b2b_code'] = $v['b2b_code'];
    		$result['lists'][$k]['commercial_name'] = $v['commercial_name'];
    		$result['lists'][$k]['client_name'] = $v['client_name'];
    		$result['lists'][$k]['money'] = $v['cope_amount'];
    		$result['lists'][$k]['type'] = $v['ext4'] == PAY_TYPE_TERM_PERIOD ? '期结' : '月结';
    		$result['lists'][$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $time[] = $v['create_time'];
            $result['lists'][$k]['time'] = $v['create_time'];
    		$result['lists'][$k]['end_time'] =  $v['ext4'] == PAY_TYPE_TERM_PERIOD ? date('Y-m-d',$v['create_time']+86400*$v['ext5']): date('Y-m',$v['create_time']).'-'.date('t',$v['create_time']);
    		$result['lists'][$k]['sort'] = $sort[] = strtotime($result['lists'][$k]['end_time']);
    		$result['lists'][$k]['end_time'] = strtotime($result['lists'][$k]['end_time']) >= strtotime(date('Y-m-d'))?$result['lists'][$k]['end_time']:'已到期';
    	}

    	array_multisort($sort,SORT_ASC,SORT_NUMERIC,$time,SORT_DESC,SORT_NUMERIC,$result['lists']);
    	$result['pageTotalItem'] = $count;
    	$result['pageTotalNumber'] = $data['response']['total_page'];
    	return $this->endInvoke($result);
    }



    /**
     * @api  Boss版订单改价
     * @apiVersion 1.2
     * @apiName Bll.Boss.Order.OrderInfo.changePrice
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-12-2
     * @apiSampleRequest On
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

            // 获取订单详情
            $_SERVER['HTTP_USER_AGENT'] = POP;
            $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
            if (isset($params['uc_code'])) unset($params['uc_code']);
            $order_info_res = $this->invoke($apiPath, $params);
            if ($order_info_res['status'] != 0) {
                return $this->endInvoke(null,$order_info_res['status']);
            }

            foreach ($order_info_res['response']['order_goods'] as $key => $value) {
                if($value['before_goods_price'] !== $value['goods_price']){
                    $data['is_change'] = "YES";
                    break;
                }
                $data['is_change'] = "NO";
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
            $this->_goShipMessage($res['response']['single']['mobile'],$message);
        }

        return $this->endInvoke($data);
    }



    /**
     * @api  Boss版订单改价发送短信
     * @apiVersion 1.2
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-12-2
     * @apiSampleRequest On
     */
    private function _goShipMessage($mobile,$message){
        $data = array(
            'sys_name'=>POP,
            'numbers'=>array($mobile),
            'message'=>$message,
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $this->push_queue($apiPath, $data,0);
        return true;
    }


    /**
     * @api  Boss版订单改价订单信息
     * @apiVersion 1.2
     * @apiName Bll.Boss.Order.OrderInfo.orderGoods
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-12-2
     * @apiSampleRequest On
     */   

    public function orderGoods($params){
        $_SERVER['HTTP_USER_AGENT'] = POP;
        $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        $order_info_res = $this->invoke($apiPath, $params);
        if (isset($params['uc_code'])) unset($params['uc_code']);
        if ($order_info_res['status'] != 0) {
            return $this->endInvoke(null,$order_info_res['status']);
        }

        $info = $order_info_res['response'];
        $order_goods = array();
        $order_info = array(
            'order_amount' => $info['order_info']['before_goods_amount'],
            );

        // 重组订单商品信息
        foreach ($info['order_goods'] as $k => $v) {
            $order_goods[$k] = array(
                'sic_code'     =>$v['sic_code'],
                'goods_name'   =>$v['goods_name'],
                'spec'         =>$v['spec'],
                'goods_price'  =>$v['goods_price'],
                'goods_number' =>$v['goods_number'],
                'spc_code'     =>$v['spc_code'],
                );
            if($v['goods_price'] == $v['before_goods_price']){
                $order_goods[$k]['is_change'] = 'NO';
            }else{
                $order_goods[$k]['is_change'] = 'YES';
                $order_goods[$k]['amount'] = $v['goods_price']*$v['goods_number'];
                $order_info['real_amount'] = $info['order_info']['cope_amount'];      
            }
        }

        $data = array(
            'order_info'=>$order_info,
            'order_goods'=>$order_goods,
            );
        return $this->endInvoke($data);

    }



    /**
     * @api  Boss版获取今日订单
     * @apiVersion 1.2
     * @apiName Bll.Boss.Order.OrderInfo.getOrders
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-12-2
     * @apiSampleRequest On
     */   

    public function getOrders($params){
        empty($params['date']) && $params['date'] = NOW_TIME;
        // 订单where条件
        $start_time = mktime(0,0,0,date('m',$params['date']),date('d',$params['date']),date('Y',$params['date']));
        $end_time   = mktime(23,59,59,date('m',$params['date']),date('d',$params['date']),date('Y',$params['date']));
        // 订单金额条件
        $status_where  = array();
        //在线支付(微信，支付宝，预付款)已支付
        $status_where[] = array('obo.pay_type'=>PAY_TYPE_ONLINE,'obo.pay_status'=>OC_ORDER_PAY_STATUS_PAY,'obo.create_time'=>array('between',array($start_time,$end_time)));
        // 货到付款与账期
        $status_where[] = array('obo.ship_method'=>SHIP_METHOD_DELIVERY,'obo.pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),'obo.ship_status'=>array('in',array(OC_ORDER_SHIP_STATUS_SHIPPED,OC_ORDER_SHIP_STATUS_TAKEOVER)),'obo.ship_time'=>array('between',array($start_time,$end_time)));
        // 买家自提 货到付款与账期
        $status_where[] = array('obo.ship_method'=>SHIP_METHOD_PICKUP,'obo.pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),'obo.takeover_time'=>array('between',array($start_time,$end_time)));
        $status_where['_logic'] = 'or';
        $where['_complex'] = $status_where;
        $where['obo.sc_code'] = $params['sc_code'];
        $params['ori_where']  = $where;
        $params['page'] = $params['pageNumber'];
        $params['page_number'] = $params['pageSize'];

        $apiPath = "Base.OrderModule.B2b.OrderInfo.lists";
        $res = $this->invoke($apiPath,$params);
        // 数据拼装
        $data = array();
        foreach ($res['response']['lists'] as $k => $v) {
            $data['lists'][$k]['b2b_code'] = $v['b2b_code'];
            $data['lists'][$k]['status_message'] = $v['status_message'];
            $data['lists'][$k]['client_name'] = $v['client_name'];
            $data['lists'][$k]['commercial_name'] = $v['commercial_name'];
            $data['lists'][$k]['payMethod'] = $v['payMethod'];
            $data['lists'][$k]['payType'] = $v['payType'];
            $data['lists'][$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $data['lists'][$k]['real_amount'] = $v['cope_amount'];

            foreach ($v['order_goods'] as $key => $value) {
                if($value['before_goods_price'] !== $value['goods_price']){
                    $data['lists'][$k]['is_change'] = "YES";
                    break;
                }
                $data['lists'][$k]['is_change'] = "NO";
            }

        }
        $data['pageTotalItem'] = $res['response']['totalnum'];
        $data['pageTotalNumber'] = $res['response']['total_page'];
        return $this->endInvoke($data);
    }

    /**
     * @api  Boss版校验取货码
     * @apiVersion 1.3
     * @apiName Bll.Boss.Order.OrderInfo.checkPickUpCode
     * @apiTransaction N
     * @apiAuthor 何威俊 <heweijun@liangrenwang.com>
     * @apiDate 2015-12-23
     * @apiSampleRequest On
     * @param type $params
     * @desc  pick_up_code，获取uc_code, sc_code, b2b_code[订单编号]
     * @return 　
     */
    public function checkPickUpCode($params){
        //取货码状态查询
        $res = $this->invoke('Base.OrderModule.B2b.OrderInfo.checkPickUpCode',$params);
        if ( $res['status'] != 0) {
            return $this->endInvoke($res['response'],$res['status'],$res['message']);
        }
        return $this->endInvoke($res['response']);
    }

    /**
     * @api  Boss版 订单状态修改
     * @apiVersion 1.3
     * @apiName Bll.Boss.Order.OrderInfo.updateOrderStatus
     * @apiTransaction Y
     * @apiAuthor 何威俊 <heweijun@liangrenwang.com>
     * @apiDate 2015-12-23
     * @apiSampleRequest On
     * @params type $params
     * @desc 根据 sc_code 修改订单的状态
     */
     public function updateOrderStatus($params){
         $_SERVER['HTTP_USER_AGENT'] = POP;
         $res = $this->invoke('Base.OrderModule.B2b.OrderInfo.updateOrderStatus',$params);
         if ( $res['status'] != 0) {
             return $this->endInvoke($res['message'],$res['status']);
         }
         return $this->endInvoke($res['response']);
     }

    /**
     * @api  Boss版 订单详情获取
     * @apiVersion 1.3
     * @apiName Bll.Boss.Order.OrderInfo.getOrderDetail
     * @apiTransaction N
     * @apiAuthor 何威俊 <heweijun@liangrenwang.com>
     * @apiDate 2015-12-23
     * @apiSampleRequest On
     * @params type $params
     * @desc 根据 sc_code,  b2b_code, 获取 product_info
     */
     public function getOrderDetail($params){
         $_SERVER['HTTP_USER_AGENT'] = POP;
         $res = $this->invoke('Base.OrderModule.B2b.OrderInfo.get',$params);
         if ( $res['status'] != 0) {
             return $this->endInvoke($res['message'],$res['status']);
         }
         return $this->endInvoke($res['response']);
     }
}