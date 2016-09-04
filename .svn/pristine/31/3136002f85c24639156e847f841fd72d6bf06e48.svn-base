<?php

/**
 * +---------------------------------------------------------------------
 * | www.laingrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单分析类
 */

namespace Base\BicModule\Oc;
use System\Base;

class Analyse extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
        $this->tablePrefix=C('DB_PREFIX');
        $this->connection=C('DB_BIC');
    }
    /**
     * 新增订单数量
     * Base.BicModule.Oc.Analyse.orderCount
     * @param type $params
     */
    public function orderCount($params){
        $model=D('OcB2bOrder',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];

        //组装where条件
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));

        //查出新增加的订单数量
        $count=$model->field('count(*) as num')->where($where)->find();
        $order=$model->field('order_status')->where($where)->select();

        //如果时间范围小于1个月，则算出每天创建的订单数量
        if((($end_time-$start_time)/86400)<32){
            $day_order=$model->field('count(*) as day_order,day,create_time')->where($where)->group('year,month,day')->order('create_time asc')->select();
//            var_dump($day_order);exit;
            foreach($day_order as $key=>$val){
                $day_order[$key]['date']=date('Y-m-d',$val['create_time']);
                $day_order[$key]['create_time']=intval($val['create_time']);
            }
            $date=array_column($day_order,'date');
            $start_date=date('Y-m-d',$start_time);
            $start_date_time=strtotime($start_date);
            while($start_date_time<=$end_time){
                $start_date=date('Y-m-d',$start_date_time);
                if(!in_array($start_date,$date)){
                    array_push($day_order,array('day_order'=>0,'create_time'=>$start_date_time,'date'=>$start_date));
                }
                $start_date_time+=24*3600;
            }
            $create_time=array_column($day_order,'create_time');
//            var_dump($create_time);exit;
           array_multisort($create_time,SORT_ASC,$day_order);
           //计算
//            $count=ceil(($end_time-$start_time)/86400)-count($day_order);
//            for($i=1;$i<$count;$i++){
//              array_push($day_order,array('day_order'=>0));
//            }
//            var_dump($day_order);exit;
        }else{
            $day_order=$model->field('count(*) as day_order,month,create_time')->where($where)->group('year,month')->order('create_time asc')->select();
            foreach($day_order as $key=>$val){
                $day_order[$key]['date']=date('Y-m',$val['create_time']);
                $day_order[$key]['create_time']=intval($val['create_time']);
            }
            $date=array_column($day_order,'date');
            $start_date=date('Y-m',$start_time);
            $start_date_time=strtotime($start_date);
            while($start_date_time<=$end_time){
                $start_date=date('Y-m',$start_date_time);
                if(!in_array($start_date,$date)){
                    array_push($day_order,array('day_order'=>0,'create_time'=>$start_date_time,'date'=>$start_date));
                }
                $start_date_time+=24*3600*31;
            }
            $create_time=array_column($day_order,'create_time');
            array_multisort($create_time,SORT_ASC,$day_order);
        }
        $data=array(
            'count'=>$count,
            'order'=>$order,
            'day_order'=>$day_order,
        );
        if($count===false){
            return $this->res('',10000);
        }
        return $this->res($data);
    }

    /**
     * 新增成单数量
     * Base.BicModule.Oc.Analyse.offOrderNum
     * @param type $params
     */
    public function offOrderNum($params){
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];

        //组装where条件
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));

        //查询出订单的数量
        $num=$model->field('count(*) as num')->where($where)->find();
        //如果时间范围小于1个月，则算出每天创建的订单数量
        if((($end_time-$start_time)/86400)<32){
            $off_day_order=$model->field('count(*) as day_order,day,create_time,year,month,day,b2b_code')->where($where)->group('year,month,day')->order('create_time asc')->select();
//            echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
            foreach($off_day_order as $key=>$val){
                $off_day_order[$key]['date']=date('Y-m-d',$val['create_time']);
                $off_day_order[$key]['create_time']=intval($val['create_time']);
            }
            $date=array_column($off_day_order,'date');
//            var_dump($date);exit;
            $start_date=date('Y-m-d',$start_time);
            $end_date=date('Y-m-d',$end_time);
            $end_date_time=strtotime($end_date);
            $start_date_time=strtotime($start_date);
//            var_dump($start_date_time);
//            echo '</br>';
//            var_dump($end_date_time);
//            exit;
//            var_dump($off_day_order);exit;
            while($start_date_time<=$end_date_time){
                $start_date=date('Y-m-d',$start_date_time);
                if(!in_array($start_date,$date)){
                    array_push($off_day_order,array('day_order'=>0,'create_time'=>$start_date_time,'date'=>$start_date));
                }
                $start_date_time+=24*3600;
            }
            $create_time=array_column($off_day_order,'create_time');
//            var_dump($create_time);exit;
            array_multisort($create_time,SORT_ASC,$off_day_order);
//            var_dump($off_day_order);exit;
        }else{
            $off_day_order=$model->field('count(*) as day_order,month,create_time')->where($where)->group('year,month')->order('create_time asc')->select();
            foreach($off_day_order as $key=>$val){
                $off_day_order[$key]['date']=date('Y-m',$val['create_time']);
                $off_day_order[$key]['create_time']=intval($val['create_time']);
            }
            $date=array_column($off_day_order,'date');
            $start_date=date('Y-m',$start_time);
            $start_date_time=strtotime($start_date);
            while($start_date_time<=$end_time){
                $start_date=date('Y-m',$start_date_time);
                if(!in_array($start_date,$date)){
                    array_push($off_day_order,array('day_order'=>0,'create_time'=>$start_date_time,'date'=>$start_date));
                }
                $start_date_time+=24*3600*31;
            }
            $create_time=array_column($off_day_order,'create_time');
            array_multisort($create_time,SORT_ASC,$off_day_order);
        }
        if($num===false){
            return $this->res('',10001);
        }
        $data=array(
            'num'=>$num,
            'off_day_order'=>$off_day_order,
        );
         return $this->res($data);
    }

    /**
     * 新增成单总额同期环比
     * Base.BicModule.Oc.Analyse.orderAmountLink
     * @param type $params
     */
    public function orderAmountLink($params){
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];

        //组装where条件
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));

        //查询出此次成单的总额
        $now_order_amount=$model->field('sum(real_amount) as order_amount')->where($where)->find();
        if(!$now_order_amount['order_amount']){
            $now_order_amount['order_amount']=0;
        }
        //查询出往前同等时间范围内的
        $start_time=$start_time-($end_time-$start_time);
        $end_time=$params['start_time'];
        $where['create_time'] = array('between',array($start_time,$end_time));
        $before_order_amount=$model->field('sum(real_amount) as order_amount')->where($where)->find();
        //得出同期环比
        if($before_order_amount['order_amount']>0){
            $data=($now_order_amount['order_amount']/$before_order_amount['order_amount']-1)*100;
            $data=round($data,1);
            $data=$data.'%';
        }else{
            $data='--';
        }
        $last=array(
            'link_rate'=>$data,
            'complete_order_amount'=>$now_order_amount,
        );
       return $this->res($last);
    }
    /**
     * 二次成单占比
     * Base.BicModule.Oc.Analyse.reBuyRate
     * @param type $params
     */
    public function reBuyRate($params){
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];

        //组装where条件
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));
        //得到查询条件下完成>1成单的客户数
        $client=$model->field('count(*) as num')->where($where)->group('uc_code')->having('count(*)>1')->select();
        $client_num=count($client);
        //得到查询条件下完成成单的总客户数
        $total=$model->field('count(*) as num')->where($where)->group('uc_code')->select();
        $total_num=count($total);
        //计算二次购买率
        $repeat_buy_rate=($client_num/$total_num)*100;
        $repeat_buy_rate=round($repeat_buy_rate,1);
        $repeat_buy_rate=$repeat_buy_rate.'%';
        return $this->res($repeat_buy_rate);
    }
    /**
     * 订单支付方式分布
     * Base.BicModule.Oc.Analyse.payMethod
     * @param type $params
     */
    public function payMethod($params){
        $pay=array();
        $model=D('OcB2bOrder',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];

        //组装where条件
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));

        //得到各种支付的订单数量
        $where['pay_status']=array('eq','PAY');
        $where['pay_method']=array('neq','');
//        $total_num=$model->field('count(*) as total_num')->where($where)->select();
//        $total_num=$total_num['total_num'];
        $payMethod=$model->field('pay_method,count(*) as num,pay_method_message')->where($where)->group('pay_method')->select();
        $total=array_column($payMethod,'num');
        $total_num=array_sum($total);
        foreach($payMethod as $key=>$val){
//            if($val['pay_method']=='ADVANCE'){
//                $pay['advance']=$val['num'];
//            }
//            if($val['pay_method']=='ALIPAY'){
//                $pay['alipay']=$val['num'];
//            }
//            if($val['pay_method']=='WEIXIN'){
//                $pay['weixin']=$val['num'];
//            }
//            if($val['pay_method']=='REMIT'){
//                $pay['remit']=$val['num'];
//            }
            $pro=($val['num']/$total_num)*100;
            $pro=round($pro,1);
            $pro=$pro.'%';
            $pay[$val['pay_method']]['num']=$val['num'];
            $pay[$val['pay_method']]['pro']=$pro;
            $pay[$val['pay_method']]['pay_method_message']=$val['pay_method_message'];
        }
        $data=array(
            'total_num'=>$total_num,
            'payMethod'=>$pay,
        );
        return $this->res($data);
    }
    /**
     * 订单支付方式分布
     * Base.BicModule.Oc.Analyse.timeTrend
     * @param type $params
     */
    public function timeTrend($params){
        $model=D('OcB2bOrder',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];

        //组装where条件
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));
        $time_order=array();
        $time=array();
        for($i=1;$i<25;$i++){
            $time[]=$i;
        }
//        for($i=1;$i<25;$i++){
//            $where['time']=array('eq',$i-1);
         $res=$model->field('count(*) as num,time')->where($where)->group('time')->select();
        $res=changeArrayIndex($res,'time');
        foreach($time as $key=>$val){
            if($res[$val]['num']){
                $time_order[$val]=$res[$val]['num'];
            }else{
                $time_order[$val]=0;
            }
        }
//        var_dump($time_order);exit;
//            echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
//            $time_order[$i]=$res['num'];
//        }
        //得到各个时间段内发货的订单
        $where['ship_time']=$where['create_time'];
        unset($where['create_time']);
        $model=D('OcB2bOrderShipped',$this->tablePrefix,$this->connection);
        $time_ship=array();
        $res=$model->field('count(*) as num,time')->where($where)->group('time')->select();
        $res=changeArrayIndex($res,'time');
        foreach($time as $key=>$val){
            if($res[$val]['num']){
                $time_ship[$val]=$res[$val]['num'];
            }else{
                $time_ship[$val]=0;
            }
        }
        $data=array(
            'time_order'=>$time_order,
            'time_ship'=>$time_ship
        );
        return $this->res($data);
    }
    /**
     *已完成订单查询
     * Base.BicModule.Oc.Analyse.completeOrder
     * @param type $params
     */
    public function completeOrder($params){
        $model=D('OcB2bOrderComplete',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //组装where条件
        $uc_code=$params['uc_code'];
        $sc_code=$params['sc_code'];
        $start_time=$params['start_time'];
        $end_time=$params['end_time'];
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));
        $res=$model->field('count(*) as num')->where($where)->find();
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
//        var_dump($res);exit;
        $num=$res['num'];
        return $this->res($num);
    }
    /**
     *已完成订单查询
     * Base.BicModule.Oc.Analyse.cancelOrder
     * @param type $params
     */
    public function cancelOrder($params){
        $model=D('OcB2bOrderCancel',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //组装where条件
        $uc_code=$params['uc_code'];
        $sc_code=$params['sc_code'];
        $start_time=$params['start_time'];
        $end_time=$params['end_time'];
        $where=array();
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));
        $res=$model->field('count(*) as num')->where($where)->find();
        $num=$res['num'];
        return $this->res($num);
    }
}
	
