<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单统计相关
 */

namespace Bll\Pop\Order;

use System\Base;
class Statistic extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Bll.Pop.Order.Statistic.getNumsByStatus
     * 根据时间获取订单下单数
     * @param type $params
     */
    public function getNumsByStatus($params){
        $res_data  = array();
        $sc_code = $params['sc_code'];
        $apiPath = "Com.DataCenter.Statistic.Order.getNumsByStatus";
        //获取待付款订单
        $data = array(
            'sc_code' => $sc_code,
            'status'  => OC_ORDER_GROUP_STATUS_UNPAY,
        );
        $res = $this->invoke($apiPath,$data);
        $res_data['unpay'] = $res['response']['total_order_num'];
        //获取待发货订单
        $data = array(
            'sc_code'  => $sc_code,
            'status'   => OC_ORDER_GROUP_STATUS_UNSHIP,
        );
        $res = $this->invoke($apiPath,$data);
        $res_data['unship'] = $res['response']['total_order_num'];
        //获取待确认收款订单数量
        $data = array(
            'sc_code' => $sc_code,
            'status'  => OC_ORDER_GROUP_STATUS_TAKEOVER,
        );
        $res = $this->invoke($apiPath,$data);
        $res_data['cod_pay'] = $res['response']['total_order_num'];
        
        //获取七天内的订单  和  金额
        $apiPath = "Com.DataCenter.Statistic.Order.deal";
        $data = array(
            'sc_code'  => $sc_code,
            'start_time'=>  strtotime(date('Y-m-d',NOW_TIME)) - 6*24*3600,
            'end_time' => NOW_TIME,
        );
        $res = $this->invoke($apiPath,$data);
        $res_data['seven_order_nums'] = $res['response']['total_order_num'];
        $res_data['seven_order_amount'] = $res['response']['total_real_amount'];
        return $this->endInvoke($res_data);
    }

    /**
     * Bll.Pop.Order.Statistic.getList
     * 根据时间获取订单下单数
     * @param type $params
     */
    public function getList($params){
        $list=array();
        $sc_code=$params['sc_code'];
        $data=array(
            'sc_code'=>$sc_code,
            'warn_stock_sign'=>true
        );
        $apiPath='Base.StoreModule.Item.Item.storeItems';
        $res=$this->invoke($apiPath,$data);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        //得到商品库存报警
        $list['warn_num']=$res['response']['warn_num'];
        $apiPath='Base.OrderModule.B2b.Statistic.orderStatistic';
        $data = array(
            'sc_code'=>$sc_code,
            'status'=>'UNSHIP',
        );
        $res=$this->invoke($apiPath,$data);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        //得到订单待发货的数量
        $list['unship_count']=count($res['response']);
        //得到订单账期代付款的数量
        $data = array(
            'sc_code'=>$sc_code,
            'status'=>'UNPAY',
            'pay_type'=>'TERM',
            'order_status'=>array('not in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL,OC_ORDER_ORDER_STATUS_OVERTIMECANCEL))
        );
        $apiPath='Base.OrderModule.B2b.Statistic.orderStatistic';
        $res=$this->invoke($apiPath,$data);
//        echo D()->getLastSql();exit;
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        $list['unpay_count']=count($res['response']);

        //得到促销品已经缺货的记录
        $data=array(
            'is_page'=>'NO',
            'sc_code'=>$sc_code,
            'stock'=>'stock',
        );
        $res=M('Bll.Pop.Spc.CenterInfo.stock_list')->stock($data);
        $list['spc_count']=count($res);
//        得到当日的交易状况
         $data=array(
             'trade'=>'trade',
             'sc_code'=>$sc_code,
             'start_time'=>strtotime(date('Y-m-d',NOW_TIME)),
             'end_time'=>strtotime(date('Y-m-d',NOW_TIME))+24*3600-1,
         );
        $apiPath='Base.OrderModule.B2b.Statistic.orderStatistic';
        $res=$this->invoke($apiPath,$data);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        $list['trade_today_count']=count($res['response']);
        $amount=array_column($res['response'],'real_amount');
        $list['trade_today_amount']=array_sum($amount);
        $month_time=date('Y-m-d',mktime(0, 0 , 0,date('m'),1,date('Y')));
//        得到本月的交易状况
        $data=array(
            'trade'=>'trade',
            'sc_code'=>$sc_code,
            'start_time'=>strtotime($month_time),
            'end_time'=>NOW_TIME,
        );
        $apiPath='Base.OrderModule.B2b.Statistic.orderStatistic';
        $res=$this->invoke($apiPath,$data);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        $list['trade_week_count']=count($res['response']);
        $amount=array_column($res['response'],'real_amount');
        $list['trade_week_amount']=array_sum($amount);
        //得到未转账的金额
        $data=array(
            'sc_code'=>$sc_code,
            'f_status'=>array(1,2),
        );
        $apiPath='Base.FcModule.Detail.Order.orderAmount';
        $res=$this->invoke($apiPath,$data);

        if($res['status']!=0){
            return $this->endInvoke('',$res['status']);
        }
        $list['total_no_trans']=$res['response']['price'];
        //得到已转账的金额
        $data=array(
            'sc_code'=>$sc_code,
        );
        $apiPath='Base.FcModule.Detail.Order.payMentAmount';
        $res=$this->invoke($apiPath,$data);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        $list['total_trans']=$res['response']['total_amount'];
        return $this->endInvoke($list);
    }
}

?>
