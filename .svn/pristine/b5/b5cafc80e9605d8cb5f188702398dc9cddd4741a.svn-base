<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | pop订单列表相关的操作
 */

namespace Bll\Bi\Order;

use System\Base;

class Analyse extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    /**
     * 订单分析数据
     * Bll.Bi.Order.Analyse.Info
     * @param type $params
     */
    public function Info($params){
        $info=array();
        //得到新增订单的总数
        $apiPath='Base.BicModule.Oc.Analyse.orderCount';
        $order=$this->invoke($apiPath,$params);
        if($order['status']!==0){
            return $this->endInvoke('',$order['status']);
        }
        $info['order_num']=$order['response']['count']['num'];
        $info['day_order']=$order['response']['day_order'];
        //得到新增的成单的数量
        $apiPath='Base.BicModule.Oc.Analyse.offOrderNum';
        $call = $this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['off_order_num']=$call['response']['num']['num'];
        $info['off_day_order']=$call['response']['off_day_order'];
//        var_dump($info['off_day_order']);exit;
        //得到新增总额的同期环比
        $apiPath='Base.BicModule.Oc.Analyse.orderAmountLink';
        $call = $this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['order_amount_link']=$call['response']['link_rate'];
        $info['complete_order_amount']=$call['response']['complete_order_amount'];
        //得出平均每天得到成单的数量
        $day=($params['end_time']-$params['start_time'])/86400;
        $average=$info['off_order_num']/$day;

        $average=round($average,1);
        $info['average']=$average;

        //求出该段时间内的二次购买率
        $apiPath='Base.BicModule.Oc.Analyse.reBuyRate';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['repeat_buy_rate']=$call['response'];

        //订单支付方式分布
        $apiPath='Base.BicModule.Oc.Analyse.payMethod';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['payMethod']=$call['response'];
        //得到已完成的和已取消的订单数量
        $apiPath='Base.BicModule.Oc.Analyse.completeOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['complete_num']=$call['response'];
        $apiPath='Base.BicModule.Oc.Analyse.cancelOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['cancel_num']=$call['response'];
//        $complete=array();
//        $cancel=array();
//        foreach($order['response']['order'] as $key=>$val){
//            if($val['order_status']=='COMPLETE'){
//                $complete[]=$val;
//            }
//            if($val['order_status']=='CANCEL' || $val['order_status']=='MERCHCANCEL' || $val['order_status']=='OVERTIMECANCEL'){
//                $cancel[]=$val;
//            }
//        }
        //得到各个时间段内创建的订单数量
        $apiPath='Base.BicModule.Oc.Analyse.timeTrend';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['time_order']=$call['response']['time_order'];
        $info['time_ship']=$call['response']['time_ship'];
        //获取商家列表
        $apiPath  = "Base.BicModule.Sc.Store.lists";
        $storeLists = $this->invoke($apiPath);
        $info['store_lists'] = $storeLists['response'];
        //获取店铺列表
        $apiPath  = "Base.BicModule.Uc.User.lists";
        $userLists = $this->invoke($apiPath);
        $info['user_lists'] = $userLists['response'];

        return $this->endInvoke($info);
    }
}
    