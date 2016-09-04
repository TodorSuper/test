<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |
 */

namespace Com\Crontab\Order;
use System\Base;

class Confirm extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Com.Crontab.Order.Confirm.Confirm
     * @return [type] [description]
     */
    public function Confirm(){
        $_SERVER['HTTP_USER_AGENT'] = B2B;
        //首先获取所有支付方式为立即支付，配送方式为商家配送已发货且未确认收货的订单
        $where = array(
            'pay_type'=>PAY_TYPE_ONLINE,
            'ship_method'=>SHIP_METHOD_DELIVERY,
            'ship_status'=>OC_ORDER_SHIP_STATUS_SHIPPED,
            'order_status'=>OC_ORDER_ORDER_STATUS_UNCONFIRM,
        );
        $field = 'ship_time,b2b_code';
        $orderInfo = D('OcB2bOrder')->field($field)->where($where)->limit(100)->select();
        foreach($orderInfo as $key=>$val){
            //如果当前时间-这个订单的发货时间>72个小时的话就执行确认收货的操作
            if(NOW_TIME-$val['ship_time']>259200){
                D()->startTrans();
              try{
                  $apiPath = 'Base.OrderModule.B2b.OrderInfo.operate';
                  $params = array(
                      'status'=>OC_ORDER_GROUP_STATUS_COMPLETE,
                      'operate_flag'=>'auto',
                      'b2b_code'=>$val['b2b_code'],
                      'need_action'=>'YES',
                  );
                  $res = $this->invoke($apiPath,$params);
                  if($res['status']!==0){
                      return $this->endInvoke('',$res['status']);
                  }
                  D()->commit();
              }catch(\Exception $ex){
                  D()->rollback();
                  return $this->endInvoke('',6064);
              }

            }
        }
        return $this->endInvoke(true);
    }
}