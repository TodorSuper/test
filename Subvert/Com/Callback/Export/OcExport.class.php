<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单中心 导出  回调接口
 */

namespace Com\CallBack\Export;

use System\Base;

class OcExport extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 处理订单列表导出数据回调函数
     * 调用方式  M('Com.CallBack.Export.OcExport.orderList')->orderList($data)
     * Com.CallBack.Export.OcExport.orderList
     * @param type $data
     */
    public function orderList(&$data){

        $status_model = M('Base.OrderModule.B2b.Status.detailToGroup');
        

        $order_goods =  array();  //订单商品
        $order_gift  = array();   //订单赠品
        //获取订单商品
        $b2b_code = array_unique(array_column($data,'b2b_code' ));
        $temp_order_goods = D('OcB2bOrderGoods')->where(array('b2b_code'=>array('in',$b2b_code)))->select();

        //获取订单赠品
        $temp_order_gift = D('OcB2bOrderGift')->where(array('b2b_code'=>array('in',$b2b_code)))->select();
        
        //订单赠品进行重组
        $order_gift = array();

        foreach($temp_order_gift as $k=>$v){
            //$v['rule'] = spcRuleParse(SPC_TYPE_GIFT, array('rule'=>$v['rule']));

            $order_gift[$v['b2b_code'].'_'.$v['p_sic_code']] = $v;
        }
        unset($temp_order_gift);
        
        foreach($temp_order_goods as $k=>$v){
            if(isset($order_gift[$v['b2b_code'].'_'.$v['sic_code']])){
                $v['gift_item'] = $order_gift[$v['b2b_code'].'_'.$v['sic_code']];
            }
            switch ($v['spc_type']) {
                case SPC_TYPE_GIFT:
                    $v['rule'] = '满赠促销优惠:'.round(($v['gift_item']['goods_price']*$v['gift_item']['goods_number']),2).'元';
                    $v['gift_item']['rule'] = json_decode($v['gift_item']['rule'],true);
                    break;
                case SPC_TYPE_SPECIAL:
                    $v['rule'] = '特价促销优惠:'.round(($v['ori_goods_price']-$v['before_goods_price']),2).'元';
                    break;
                case SPC_TYPE_LADDER:
                    $v['rule'] = '阶梯价促销优惠:'.round(($v['ori_goods_price']-$v['before_goods_price']),2).'元';
                    break;
                default:
                    $v['rule'] = '';
                    # code...
                    break;
            }
            if ($v['before_goods_price'] != $v['goods_price']) {
                $v['rule'] .= '　改价优惠:'.round(($v['before_goods_price']-$v['goods_price']),2).'元'; 
               
            }
            $order_goods[$v['b2b_code']][] = $v; 
        }
        
        unset($order_gift);
        foreach( $data as $k=>$v) {
            $data[$k]['order_goods']  = $order_goods[$v['b2b_code']];   
            $data[$k]['create_time']  = date('Y-m-d H:i:s',$v['create_time']);
            $data[$k]['pay_type_message']  = $status_model->getPayType($v['pay_type']);
            if(!empty($v['pay_method'])){
                $data[$k]['pay_message']  = $status_model->getPayMethod($v['pay_method']);
            }else {
                $data[$k]['pay_message']  = '';
            }
            
            $data[$k]['ship_message'] = $status_model->getShipMethodList($v['ship_method']);
            $data[$k]['address']      = $v['province'].$v['city'].$v['district'].$v['address'];
            $data[$k]['status']       = $status_model->detailToGroup($v['order_status'],$v['ship_status'],$v['pay_status'],$v['pay_type'],$v['ship_method'])['message'];
        }
    }
    
    /**
     * Com.CallBack.Export.OcExport.orderListCms
     * cms导出订单
     * @param type $data
     */
    public function orderListCms(&$data){
        $status_model = M('Base.OrderModule.B2b.Status.detailToGroup');
        //获取订单商品
        $b2b_code         = array_unique(array_column($data,'b2b_code' ));
        $temp_order_goods = D('OcB2bOrderGoods')->where(array('b2b_code'=>array('in',$b2b_code)))->select();

        $pay_method_map = array(
            PAY_METHOD_ONLINE_ALIPAY=>ALIPAY_WAP,
            PAY_METHOD_ONLINE_WEIXIN=>WEIXIN_JSAPI_PAY,
            PAY_METHOD_ONLINE_UCPAY=>'UCPAY_DIRECT',
        );

        $apiPath         = "Base.TradeModule.Pay.Voucher.getInfo";
        $model           = M('Base.OrderModule.B2b.Status');
        $pay_method_list = $model->getPayMethod();
        
        $order_goods =  array();  //订单商品
        $order_gift  = array();   //订单赠品

        //获取订单商品
        $b2b_code         = array_unique(array_column($data,'b2b_code' ));
        $temp_order_goods = D('OcB2bOrderGoods')->where(array('b2b_code'=>array('in',$b2b_code)))->select();

        //获取订单赠品
        $temp_order_gift = D('OcB2bOrderGift')->where(array('b2b_code'=>array('in',$b2b_code)))->select();
        
        //订单赠品进行重组
        $order_gift = array();

        foreach($temp_order_gift as $k=>$v){
            //$v['rule'] = spcRuleParse(SPC_TYPE_GIFT, array('rule'=>$v['rule']));
            $order_gift[$v['b2b_code'].'_'.$v['p_sic_code']] = $v;
        }

        unset($temp_order_gift);
        
        foreach($temp_order_goods as $k=>$v){
            if(isset($order_gift[$v['b2b_code'].'_'.$v['sic_code']])){
                $v['gift_item'] = $order_gift[$v['b2b_code'].'_'.$v['sic_code']];
            }
            switch ($v['spc_type']) {
                case SPC_TYPE_GIFT:
                    $v['rule'] = '满赠促销优惠:'.round(($v['gift_item']['goods_price']*$v['gift_item']['goods_number']),2).'元';
                    $v['gift_item']['rule'] = json_decode($v['gift_item']['rule'],true);
                    break;
                case SPC_TYPE_SPECIAL:
                    $v['rule'] = '特价促销优惠:'.round(($v['ori_goods_price']-$v['before_goods_price']),2).'元';
                    break;
                case SPC_TYPE_LADDER:
                    $v['rule'] = '阶梯价促销优惠:'.round(($v['ori_goods_price']-$v['before_goods_price']),2).'元';
                    break;
                default:
                    $v['rule'] = '';
                    break;
            }
            if ($v['before_goods_price'] != $v['goods_price']) {
                $v['rule'] .= '　改价优惠:'.round(($v['before_goods_price']-$v['goods_price']),2).'元'; 
               
            }
            $order_goods[$v['b2b_code']][] = $v; 
        }
        // var_dump($data);die();
        $temp_data = array();
        foreach($data as $k=>$v){
            $str = '';
            $temp_data[$k]['store_name']        = $v['store_name'];
            $temp_data[$k]['b2b_code']          = $v['b2b_code'];
            $temp_data[$k]['commercial_name']   = $v['commercial_name'];
            $temp_data[$k]['uc_code']           = $v['uc_code'];
            $temp_data[$k]['client_name']       = $v['client_name'];
            $temp_data[$k]['phone']             = $v['phone'];
            $temp_data[$k]['total_nums']        = $v['total_nums'];
            $temp_data[$k]['order_type']        = $v['order_type'] == OC_ORDER_TYPE_STORE? '店铺订单':'平台商城订单';
            if ($v['pay_status'] === 'PAY') {
               $temp_data[$k]['total_real_amount'] = $v['total_real_amount']; 
            }else{
               $temp_data[$k]['total_real_amount'] = ''; 
            }
            if ($v['pay_method'] == PAY_METHOD_ONLINE_REMIT) {
               $str= M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
                if(!empty($str)){
                   $temp_data[$k]['pay_message'] = $str;
               }
            }else {
                if(!empty($v['pay_method'])){
                   $temp_data[$k]['pay_message'] = $status_model->getPayMethod($v['pay_method']);
                }
            }
            $temp_data[$k]['order_goods']       = $order_goods[$v['b2b_code']];   
            $temp_data[$k]['create_time']       = date('Y-m-d H:i:s',$v['create_time']);
//            $temp_data[$k]['pay_message']       = empty($v['pay_method']) ? '' : $status_model->getPayMethod($v['pay_method']).$str;
            $temp_data[$k]['pay_time']          = empty($v['pay_time']) ? '' : date('Y-m-d H:i:s',$v['pay_time']);
            $temp_data[$k]['ship_message']      = $status_model->getShipMethodList($v['ship_method']);
            $temp_data[$k]['real_name']         = $v['real_name'];
            $temp_data[$k]['mobile']            = $v['mobile'];
            $temp_data[$k]['address']           = $v['province'].$v['city'].$v['district'].$v['address'];
            $temp_data[$k]['salesman']          = $v['salesman'];
            $temp_data[$k]['channel']           = $v['channel'];
            $temp_data[$k]['real_name']         = $v['real_name'];
            $temp_data[$k]['status']            = $status_model->detailToGroup($v['order_status'],$v['ship_status'],$v['pay_status'],$v['pay_type'],$v['ship_method'])['message'];
            if($v['pay_status'] == OC_ORDER_PAY_STATUS_PAY && in_array($v['pay_method'],array(PAY_METHOD_ONLINE_ALIPAY,PAY_METHOD_ONLINE_WEIXIN)) ){

                $voucher_params = array(
                    'oc_code' =>$v['b2b_code'],
                    'pay_by'  =>$pay_method_map[$v['pay_method']],
                );

               $voucher_res = $this->invoke($apiPath,$voucher_params);
               if($voucher_res['status'] != 0){
                   continue;
               }
               $temp_data[$k]['pay_no'] = $voucher_res['response']['pay_no'];

            }else {
                $temp_data[$k]['pay_no'] = '';
            }
            if ($v['pay_type']) {
                $pay_type = M('Base.OrderModule.B2b.Status')->getPayType($v['pay_type']);
                $temp_data[$k]['pay_type'] = $pay_type; 
            }
            // $temp_data[$k]['status_message'] = $model->detailToGroup($v['order_status'],$v['ship_status'],$v['pay_status'],$v['pay_type'])['message'];
            $temp_data[$k]['total_amount']      = number_format($v['total_amount'], 2, '.', ',');
            $temp_data[$k]['total_amount']      = number_format($v['total_amount'], 2, '.', ',');
            $temp_data[$k]['cope_amount']      = number_format($v['cope_amount'], 2, '.', ',');
            $temp_data[$k]['active_code']      = $v['active_code'];
            $temp_data[$k]['coupon_code']      = $v['coupon_code'];
            $temp_data[$k]['active_name']      = $v['active_name'];
            $temp_data[$k]['condition'] = M('Base.SpcModule.Center.Status.getConditionToStr')->getConditionToStr($v['condition_flag']);
            $temp_data[$k]['coupon_amount']      = number_format($v['coupon_amount'], 2, '.', ',');
            $temp_data[$k]['order_amount']      = number_format($v['real_amount'], 2, '.', ',');
            $temp_data[$k]['total_real_amount'] = number_format($v['total_real_amount'], 2, '.', ',');
          
        }
         // var_dump($temp_data);die();
        
        $data = $temp_data;
        unset($temp_data);
        
    }
}

?>
