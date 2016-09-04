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

namespace Bll\Bi\User;

use System\Base;

class User extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }

    /**
     * 订单分析数据
     * Bll.Bi.User.User.export
     * @param type $params
     */
    public function export($params){
        $params['start_time']   =  empty($params['start_time']) ? 0 : strtotime($params['start_time']);
//        var_dump($start_time);exit;
        $params['end_time']     =  empty($params['end_time'])   ? time() : strtotime($params['end_time']);
        $apiPath='Base.BicModule.Uc.User.export';
        $userInfo=$this->invoke($apiPath,$params);
//        var_dump($userInfo);exit;
        if($userInfo['status']!==0){
            return $this->endInvoke('',$userInfo['status']);
        }
        return $this->endInvoke($userInfo['response']);
    }
    /**
     * 订单分析数据
     * Bll.Bi.User.User.Info
     * @param type $params
     */
    public function Info($params){
        $info=array();
        //得到买家列表
        $apiPath='Base.BicModule.Uc.User.lists';
        $user=$this->invoke($apiPath);
        //得到商家的信息
        $apiPath='Base.BicModule.Uc.User.userInfo';
        $userInfo=$this->invoke($apiPath,$params);
        if($userInfo['status']!==0){
            return $this->endInvoke('',$userInfo['status']);
        }
        $total_num=$userInfo['response']['totalnum'];
        $page_number=$userInfo['response']['page_number'];
        $userInfo=$userInfo['response']['lists'];
        $userInfo=changeArrayIndex($userInfo,'uc_code');
//        var_dump($userInfo);exit;
        $uc_code=array_column($userInfo,'uc_code');
        if(!$uc_code){
            $list['list']=array();
            $list['totalnum']=0;
            $list['page_number']=20;
            $list['user_lists']=$user['response'];
            return $this->endInvoke($list);
        }
        //得到每个商家的在查询时间内的创建订单数
        $params['uc_code']=$uc_code;
        $apiPath='Base.BicModule.Uc.Data.createOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['create_order_info']=$call['response'];
//        var_dump($info['create_order_info']);exit;
        //得到每个商家在查询时间内取消的订单数
        $apiPath='Base.BicModule.Uc.Data.cancelOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['cancel_order_info']=$call['response'];
//        var_dump($info['cancel_order_info']);exit;
        //得到每个商家在查询时间内付款的订单数
        $apiPath='Base.BicModule.Uc.Data.payOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['pay_order_info']=$call['response'];
//var_dump($info['pay_order_info']);exit;
        //得到查询时间内的新增成单
        $apiPath='Base.BicModule.Uc.Data.completeOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['complete_order_info']=$call['response'];
        //得到每个用户最后的成单时间
        $apiPath='Base.BicModule.Uc.Data.lastTime';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['last_time_info']=$call['response'];
//var_dump($info['complete_order_info']);exit;
        foreach($userInfo as $key=>$val){
            if($info['create_order_info'][$key]['order_num']){
                $userInfo[$key]['order_num']=$info['create_order_info'][$key]['order_num'];
            }else{
                $userInfo[$key]['order_num']=0;
            }
            if($info['cancel_order_info'][$key]['cancel_num']){
                $userInfo[$key]['cancel_num']=$info['cancel_order_info'][$key]['cancel_num'];
            }else{
                $userInfo[$key]['cancel_num']=0;
            }
            if($info['pay_order_info'][$key]['pay_num']){
                $userInfo[$key]['pay_num']=$info['pay_order_info'][$key]['pay_num'];
                $userInfo[$key]['pay_total_amount']=$info['pay_order_info'][$key]['pay_total_amount'];
                $userInfo[$key]['no_advance_pay']=$info['pay_order_info'][$key]['no_advance_pay'];
                $userInfo[$key]['unit_price']=round($info['pay_order_info'][$key]['pay_total_amount']/$info['pay_order_info'][$key]['pay_num'],2);
                $userInfo[$key]['average_pay_time']=round($info['pay_order_info'][$key]['average_pay_time']/60,1);
            }else{
                $userInfo[$key]['pay_num']=0;
                $userInfo[$key]['unit_price']='--';
                $userInfo[$key]['no_advance_pay']=0;
                $userInfo[$key]['pay_total_amount']=0;
                $userInfo[$key]['average_pay_time']='--';
            }
            $userInfo[$key]['link_data']=$info['complete_order_info'][$key]['link_data'];
            $userInfo[$key]['complete_order_num']=$info['complete_order_info'][$key]['complete_order_num'];
            $userInfo[$key]['complete_order_amount']=$info['complete_order_info'][$key]['order_amount'];
            if($info['last_time_info'][$key]['last_time']){
                $userInfo[$key]['last_time']=$info['last_time_info'][$key]['last_time'];
            }else{
                $userInfo[$key]['last_time']=$info['last_time_info'][$key]['last_time'];
            }
        }
        $list['list']=$userInfo;
        $list['totalnum']=$total_num;
        $list['page_number']=$page_number;
        $list['user_lists']=$user['response'];

//        var_dump($userInfo);exit;
        return $this->endInvoke($list);
    }
}