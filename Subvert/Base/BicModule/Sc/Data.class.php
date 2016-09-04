<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单生命周期卖家数据统计
 */

namespace Base\BicModule\Sc;

use System\Base;

class Data extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        $this->tablePrefix = C('DB_PREFIX');
        $this->connection = C('DB_BIC');
    }

    /**
     * 订单分析数据
     * Base.BicModule.Sc.Data.userNum
     * @param type $params
     */
    public function userNum($params){
        $model=D('UcCustomer',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];


        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));
        $where['sc_code']=array('in',$sc_code);
//        var_dump($where['sc_code']);exit;
        //得到每个卖家在查询时间内新注册的用户
        $data=$model->field('count(uc_code) as user_num,sc_code')->where($where)->group('sc_code')->select();
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
        $data=changeArrayIndex($data,'sc_code');
        //得到每个卖家在查询时间内新注册的有已付款订单的客户数
        $customer=$model->field('uc_code,sc_code')->where($where)->select();
        $customer=changeArrayIndex($customer,'uc_code');
        $uc_codes=array_column($customer,'uc_code');
        if(empty($uc_codes)){
            $last=array();
        }else{
            $wheres=array();
            !empty($start_time) && empty($end_time) && $wheres['pay_time'] = array('egt', $start_time);
            !empty($end_time) && empty($start_time) && $wheres['pay_time'] = array('elt', $end_time);
            !empty($start_time) && !empty($end_time) && $wheres['pay_time'] = array('between', array($start_time, $end_time));
            $wheres['uc_code'] = array('in',$uc_codes);
            $last=D('OcB2bOrderPay',$this->tablePrefix,$this->connection)->field('uc_code,sc_code,order_type')->where($wheres)->select();
            $last=changeArrayIndex($last,'uc_code');
        }
        $pay_user_num=array();
        foreach($sc_code as $key=>$val){
            foreach($last as $kkk=>$vvv){
                if($vvv['sc_code']==$val){
                    $pay_user_num[$val]['pay_user_num']=$pay_user_num[$val]['pay_user_num']+1;
                    if($vvv['order_type'] == 'PLATFORM'){
                        $pay_user_num[$val]['plat_pay_user_num']=$pay_user_num[$val]['plat_pay_user_num']+1;
                    }
                }
            }
        }
//        var_dump($pay_user_num);exit;
        //得出每个卖家所有的客户
        $wheres['sc_code']=array('in',$sc_code);
        $total_data=$model->field('count(uc_code) as total_user_num,sc_code')->where($wheres)->group('sc_code')->select();
        $total_data=changeArrayIndex($total_data,'sc_code');
        foreach($total_data as $key=>$val){
            if($data[$key]['user_num']){
                $total_data[$key]['new_user_num']=$data[$key]['user_num'];
            }else{
                $total_data[$key]['new_user_num']=0;
            }
            if($pay_user_num[$key]['pay_user_num']){
                $total_data[$key]['pay_user_num']=$pay_user_num[$key]['pay_user_num'];
            }else{
                $total_data[$key]['pay_user_num']=0;
            }
            if($pay_user_num[$key]['plat_pay_user_num']){
                $total_data[$key]['plat_pay_user_num']=$pay_user_num[$key]['plat_pay_user_num'];
            }else{
                $total_data[$key]['plat_pay_user_num']=0;
            }
        }
//        return $total_data;
       return $this->res($total_data);
    }

    /**
     * 订单分析数据
     * Base.BicModule.Sc.Data.unshipNum
     * @param type $params
     */
    public function unshipNum($params){
        $model=D('OcB2bOrder',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];

//var_dump($start_time);exit;
        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['unship_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['unship_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['unship_time'] = array('between', array($start_time, $end_time));
//        !empty($start_time) && empty($end_time) && $where['pay_time'] = array('egt', $start_time);
//        !empty($end_time) && empty($start_time) && $where['pay_time'] = array('elt', $end_time);
//        !empty($start_time) && !empty($end_time) && $where['pay_time'] = array('between', array($start_time, $end_time));
        $where['sc_code']=array('in',$sc_code);
        $map[]=array('pay_type'=>'ONLINE','pay_status'=>'PAY','ship_status'=>'UNSHIP');
        $map[]=array('pay_type'=>array('neq','ONLINE'),'order_status'=>'UNCONFIRM','ship_status'=>'UNSHIP');
        $map['_logic']='or';
        $where['_complex'] = $map;
        $data=$model->field('count(*) as unship_num,sc_code')->where($where)->group('sc_code')->select();
//        var_dump($data);exit;
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
        $data=changeArrayIndex($data,'sc_code');
//        $where2=array();
//        $where2['sc_code']=array('in',$sc_code);
//        $where2['ship_status']='UNSHIP';
//        !empty($start_time) && empty($end_time) && $where2['create_time'] = array('egt', $start_time);
//        !empty($end_time) && empty($start_time) && $where2['create_time'] = array('elt', $end_time);
//        !empty($start_time) && !empty($end_time) && $where2['create_time'] = array('between', array($start_time, $end_time));
//        $where2['pay_type']=array('neq','ONLINE');
//        $data2=$model->field('count(*) as unship_num,sc_code')->where($where2)->group('sc_code')->select();
//        var_dump($data2);exit;
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;

//        $data2=changeArrayIndex($data2,'sc_code');
//        $total=array();
//        foreach($sc_code as $key=>$val){
//            if($data[$val] && $data2[$val]){
//                $total[$val]['unship_num']=$data[$val]['unship_num']+$data2[$val]['unship_num'];
//                $total[$val]['sc_code']=$val;
//            }
//            if($data[$val] && !$data2[$val]){
//                $total[$val]['unship_num']=$data[$val]['unship_num'];
//                $total[$val]['sc_code']=$val;
//            }
//            if(!$data[$val] && $data2[$val]){
//                $total[$val]['unship_num']=$data2[$val]['unship_num'];
//                $total[$val]['sc_code']=$val;
//            }
//        }
        return $this->res($data);
    }
    /**
     * 订单分析数据
     * Base.BicModule.Sc.Data.merchantCancelNum
     * @param type $params
     */
    public function merchantCancelNum($params){
        $model=D('OcB2bOrderCancel',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];


        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['cancel_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['cancel_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['cancel_time'] = array('between', array($start_time, $end_time));
//        !empty($start_time) && empty($end_time) && $where['pay_time'] = array('egt', $start_time);
//        !empty($end_time) && empty($start_time) && $where['pay_time'] = array('elt', $end_time);
//        !empty($start_time) && !empty($end_time) && $where['pay_time'] = array('between', array($start_time, $end_time));
        $where['sc_code']=array('in',$sc_code);
        $where['order_status']=array('eq','MERCHCANCEL');
        $data=$model->field('count(*) as cancel_num,sc_code')->where($where)->group('sc_code')->select();
        $data=changeArrayIndex($data,'sc_code');
//        var_dump($data);exit;
        return $this->res($data);
    }
    /**
     * 订单分析数据
     * Base.BicModule.Sc.Data.merchantCancelNum
     * @param type $params
     */
    public function shippedNum($params){
        $model=D('OcB2bOrderShipped',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];


        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['ship_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['ship_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['ship_time'] = array('between', array($start_time, $end_time));
//        !empty($start_time) && empty($end_time) && $where['pay_time'] = array('egt', $start_time);
//        !empty($end_time) && empty($start_time) && $where['pay_time'] = array('elt', $end_time);
//        !empty($start_time) && !empty($end_time) && $where['pay_time'] = array('between', array($start_time, $end_time));
        $where['sc_code']=array('in',$sc_code);
        $data=$model->field('count(*) as shipped_num,sc_code')->where($where)->group('sc_code')->select();
        $data=changeArrayIndex($data,'sc_code');
        return $this->res($data);
    }
    /**
     * 得到平台发货时间
     * Base.BicModule.Sc.Data.shipTime
     * @param type $params
     */
    public function shipTime($params){
        $model=D('OcOrderMonthAverage',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];

        //得到查询时间年月的上个月的时间
        $date=date('Y-m-d',$start_time);
//        var_dump($date);exit;
        $year=date('Y',strtotime("$date -1 month"));
        $month=date('m',strtotime("$date -1 month"));

        $where['year']=$year;
        $where['month']=$month;

        //得到每个店铺的发货平均时长
//        $where['sc_code']=array('in',$sc_code);
        $store_model=D('ScAverageShipTime',$this->tablePrefix,$this->connection);
        $store_ship=$store_model->field('average_ship_time,sc_code')->where($where)->group('sc_code')->select();
        $store_ship=changeArrayIndex($store_ship,'sc_code');
//var_dump($store_ship);exit;
        $data=$model->field('average_ship_time')->where($where)->find();
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
//        var_dump($data);exit;
        $total=array(
            'store_ship_time'=>$store_ship,
            'ship_time'=>$data
        );
        return $this->res($total);
    }
    /**
     * 得到查询时间内的成单信息
     * Base.BicModule.Sc.Data.completeOrder
     * @param type $params
     */
    public function completeOrder($params){
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];

        $where=array();
        !empty($start_time) && empty($end_time) && $where['success_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['success_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['success_time'] = array('between', array($start_time, $end_time));
//        !empty($start_time) && empty($end_time) && $where['pay_time'] = array('egt', $start_time);
//        !empty($end_time) && empty($start_time) && $where['pay_time'] = array('elt', $end_time);
//        !empty($start_time) && !empty($end_time) && $where['pay_time'] = array('between', array($start_time, $end_time));
        $where['sc_code']=array('in',$sc_code);
        $data=$model->field('count(*) as complete_num,sc_code,sum(real_amount) as total_complete_amount')->where($where)->group('sc_code')->select();
        $data=changeArrayIndex($data,'sc_code');
//var_dump($data);exit;
        //查出往前推同等范围时间内的成单总额
        $where['success_time']=array('between',array($start_time-($end_time-$start_time),$start_time));
        $link_data=$model->field('count(*) as complete_num,sc_code,sum(real_amount) as total_complete_amount')->where($where)->group('sc_code')->select();
        $link_data=changeArrayIndex($link_data,'sc_code');
//var_dump($link_data);exit;
//        var_dump($sc_code);exit;
        $total=array();
        foreach($sc_code as $key=>$val){
//            if($link_data[$key]['total_complete_amount']>0){
//                $data[$key]['link_rate']=round($data[$key]['total_complete_amount']/$link_data[$key]['total_complete_amount'],1);
//            }else{
//                $data[$key]['link_rate']='--';
//            }
            if($data[$val]['total_complete_amount']>0 && $link_data[$val]['total_complete_amount']>0){
                $total[$val]['total_complete_amount']=$data[$val]['total_complete_amount'];
                $total[$val]['complete_num']=$data[$val]['complete_num'];
                $total[$val]['sc_code']=$data[$val]['sc_code'];
                $total[$val]['link_rate']=round(($data[$val]['total_complete_amount']-$link_data[$val]['total_complete_amount'])/$link_data[$val]['total_complete_amount'],3)*100;
                $total[$val]['link_rate']=$total[$val]['link_rate'].'%';
            }
            if(!$data[$val]['total_complete_amount'] && $link_data[$val]['total_complete_amount']>0){
                $total[$val]['order_amount']=0;
                $total[$val]['complete_num']=0;
                $total[$val]['sc_code']=$val;
                $total[$val]['link_rate']=round(($data[$val]['total_complete_amount']-$link_data[$val]['total_complete_amount'])/$link_data[$val]['total_complete_amount'],3)*100;
                $total[$val]['link_rate']=$total[$val]['link_rate'].'%';
            }
            if($data[$val]['total_complete_amount'] && !$link_data[$val]['total_complete_amount']){
                $total[$val]['total_complete_amount']=$data[$val]['total_complete_amount'];
                $total[$val]['complete_num']=$data[$val]['complete_num'];
                $total[$val]['sc_code']=$data[$val]['sc_code'];
                $total[$val]['link_rate']='--';
            }
            if(!$data[$val]['total_complete_amount'] && !$link_data[$val]['total_complete_amount']){
                $total[$val]['total_complete_amount']=0;
                $total[$val]['complete_num']=0;
                $total[$val]['sc_code']=$val;
                $total[$val]['link_rate']='--';
            }
        }
//var_dump($total);exit;
        return $this->res($total);
    }
    public function lastTime($params){

        $sc_code=$params['sc_code'];
        $where['sc_code']=array('in',$sc_code);
        //得到每个卖家最后一次成单的时间
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $last_time=$model->field('max(success_time) as last_time,sc_code')->where($where)->group('sc_code')->select();
        $last_time=changeArrayIndex($last_time,'sc_code');
//        var_dump($last_time);exit;
        return $this->res($last_time);
    }

    //得到已付款金额和不包含预付款的已付款金额
    public function pay_amount($params){
        $model=D('OcB2bOrderPay',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];
        $sc_code    = $params['sc_code'];

        $where=array();
        !empty($start_time) && empty($end_time) && $where['pay_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['pay_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['pay_time'] = array('between', array($start_time, $end_time));
        $where['sc_code']=array('in',$sc_code);

        //得到新增已付款总额
        $total_pay_amount=$model->field('sum(real_amount) as pay_amount,sc_code')->where($where)->group('sc_code')->select();
        $total_pay_amount=changeArrayIndex($total_pay_amount,'sc_code');
        //得到不包含预付款的已付款总金额
        $where['pay_method']=array('neq',PAY_METHOD_ONLINE_ADVANCE);
        $no_advance_amount=$model->field('sum(real_amount) as pay_amount,sc_code')->where($where)->group('sc_code')->select();
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
        $no_advance_amount=changeArrayIndex($no_advance_amount,'sc_code');

        $total=array(
            'total_pay_amount'=>$total_pay_amount,
            'no_advance_amount'=>$no_advance_amount,
        );
        return $this->res($total);
    }
}