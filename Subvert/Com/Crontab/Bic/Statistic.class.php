<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 创建表扫描类的api调度器
 */

namespace Com\Crontab\Bic;
use       System\Base;

class Statistic extends Base{

    private $_rule = array();
    private $mobile = array(18518544428);

    public function __construct() {
        parent::__construct();
    }


    /**
     * 定时任务执行改变栏目数量
     * Com.Crontab.Bic.Statistic.Order
     * @access public
     * @return void
     */

    public function Order(){
        //得到第一单的下单时间
        $time=D('OcB2bOrder')->field('create_time')->order('create_time asc')->limit(1)->select();
        //得到最后一单的下单时间
        $last=D('OcB2bOrder')->field('create_time')->order('create_time desc')->limit(1)->select();
        //最后一单的上一月
        $last_month=date('Y-m-01',$last[0]['create_time']);
        //最后一单的上一月的1号
        $last_month=date('Y-m-01',strtotime("$last_month -1 month"));
        $month_num=date('m',strtotime($last_month));
        //第一单的1号
        $month=date('Y-m-01',$time[0]['create_time']);
        $time=strtotime($month);
        $end_time=strtotime(date('Y-m-d 23:59:59', strtotime("$month +1 month -1 day")));
        //查出这个月的立即支付方式的付款时长和订单总数
        $where=array(
            'pay_type'=>'ONLINE',
            'pay_status'=>'PAY',
            'create_time'=>array('between',array($time,$end_time)),
        );
        $fields='sum(pay_time-create_time) as payment_time,count(*) as order_num';
        $res=D('OcB2bOrder')->field($fields)->where($where)->select();
        $average_pay_time=$res[0]['payment_time']/$res[0]['order_num'];
        //查出这个月立即支付订单的发货时长和订单总数
        $where=array(
            'pay_type'=>'ONLINE',
            'ship_status'=>array('not in',array('UNSHIP')),
            'create_time'=>array('between',array($time,$end_time))
        );
        $fields='sum(ship_time-pay_time) as shipment_time,count(*) as order_num';
        $res=D('OcB2bOrder')->field($fields)->where($where)->select();
        //查出这个月货到付款和账期订单的发货时长和订单总数
        $fields='sum(ship_time-create_time) as shipment_time,count(*) as order_num';
        $where=array(
            'pay_type'=>array('not in',array('ONLINE')),
            'ship_status'=>array('not in',array('UNSHIP')),
            'create_time'=>array('between',array($time,$end_time))
        );
        $res2=D('OcB2bOrder')->field($fields)->where($where)->select();
        $total=array(
            'shipment_time'=>$res[0]['shipment_time']+$res2[0]['shipment_time'],
            'order_num'=>$res[0]['order_num']+$res2[0]['order_num'],
        );
        $average_ship_time=$total['shipment_time']/$total['order_num'];
        $data[]=array(
            'time'=>$time,
            'average_pay_time'=>$average_pay_time,
            'average_ship_time'=>$average_ship_time,
        );
        $res=D('BicOrderAverage')->addAll($data);
    }

    /**
     * 定时任务执行更新表16860_bic_order_average
     * Com.Crontab.Bic.Statistic.update
     * @access public
     * @return void
     */

    public function update(){
        //得到当前的日期
        $now_date=date('Y-m-d',NOW_TIME);
        //得到上个月1号的时间戳
        $last_start_time=strtotime(date('Y-m-01',strtotime("$now_date -1 month")));
        //得到上个月最后一天的时间戳
        $last_end_time=strtotime(date('Y-m-d 23:59:59',strtotime("$now_date -1 day")));
        //查出上个月的立即支付方式的付款时长和订单总数
        $where=array(
            'pay_type'=>'ONLINE',
            'pay_status'=>'PAY',
            'create_time'=>array('between',array($last_start_time,$last_end_time)),
        );
        $fields='sum(pay_time-create_time) as payment_time,count(*) as order_num';
        $res=D('OcB2bOrder')->field($fields)->where($where)->select();
        $average_pay_time=$res[0]['payment_time']/$res[0]['order_num'];
        //查出上个月立即支付订单的发货时长和订单总数
        $where=array(
            'pay_type'=>'ONLINE',
            'ship_status'=>array('not in',array('UNSHIP')),
            'create_time'=>array('between',array($last_start_time,$last_end_time))
        );
        $fields='sum(ship_time-pay_time) as shipment_time,count(*) as order_num';
        $res=D('OcB2bOrder')->field($fields)->where($where)->select();
        //查出上个月货到付款和账期订单的发货时长和订单总数
        $fields='sum(ship_time-create_time) as shipment_time,count(*) as order_num';
        $where=array(
            'pay_type'=>array('not in',array('ONLINE')),
            'ship_status'=>array('not in',array('UNSHIP')),
            'create_time'=>array('between',array($last_start_time,$last_end_time))
        );
        $res2=D('OcB2bOrder')->field($fields)->where($where)->select();
        $total=array(
            'shipment_time'=>$res[0]['shipment_time']+$res2[0]['shipment_time'],
            'order_num'=>$res[0]['order_num']+$res2[0]['order_num'],
        );
        $average_ship_time=$total['shipment_time']/$total['order_num'];
        $data=array(
            'time'=>$last_start_time,
            'average_pay_time'=>$average_pay_time,
            'average_ship_time'=>$average_ship_time,
        );
        for($i=1;$i<4;$i++){
            $insert=D('BicOrderAverage')->add($data);
            if($insert===false){
                    continue;
            }else{
                break;
            }
        }

        if($insert===false){
            //TODO 添加执行失败的报警，和Log日志
            $data = array(
                'sys_name'=>CMS,
                'numbers'=>$this->mobile,
                'message'=>'定时任务添加数据失败',
            );
            $apiPath = "Com.Common.Message.Sms.send";
            $this->push_queue($apiPath, $data,0);
            $this->endInvoke(null, 8001, "添加数据失败 retry:3" );
        }else{
            return $this->res(true);
        }
    }
}












?>