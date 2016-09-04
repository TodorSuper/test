<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */

namespace Base\BicModule\Uc;

use System\Base;

class Data extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        $this->tablePrefix=C('DB_PREFIX');
        $this->connection=C('DB_BIC');
    }


    //得到店铺创建的订单
    public function createOrder($params){

        $order_model=D('OcB2bOrder',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('uc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];


        //组装where条件
        $where_order=array();
        !empty($start_time) && empty($end_time) && $where_order['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where_order['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where_order['create_time'] = array('between', array($start_time, $end_time));


             //先查出在此筛选条件下的uc_code
            $uc_code=$params['uc_code'];
            $where_order['uc_code']=array('in',$uc_code);
            $res=$order_model->field('count(*) as order_num,uc_code')->where($where_order)->group('uc_code')->select();
            $res=changeArrayIndex($res,'uc_code');
        return $this->res($res);
    }

    //得到店铺取消的订单
    public function cancelOrder($params){

        $order_model=D('OcB2bOrderCancel',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('uc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];


        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['cancel_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['cancel_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['cancel_time'] = array('between', array($start_time, $end_time));

        //先查出在此筛选条件下的uc_code
         $uc_code=$params['uc_code'];
         $where['uc_code']=array('in',$uc_code);
         $where['order_status']=array('eq','CANCEL');
         $res=$order_model->field('count(*) as cancel_num,uc_code,order_status')->where($where)->group('uc_code')->select();
         $res=changeArrayIndex($res,'uc_code');
        return $this->res($res);
    }

    /**
     * 得到已付款的订单
     * Base.BicModule.Uc.Data.payOrder
     * @param type $params
     */
    public function payOrder($params){

        $order_model=D('OcB2bOrderPay',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('uc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];


        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['pay_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['pay_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['pay_time'] = array('between', array($start_time, $end_time));

           //先查出在此筛选条件下的uc_code
            $uc_code=$params['uc_code'];
            $where['uc_code']=array('in',$uc_code);
            $pay_info=$order_model->field('count(*) as pay_num,uc_code,pay_status,sum(real_amount) as pay_total_amount')->where($where)->group('uc_code')->select();
            $pay_info=changeArrayIndex($pay_info,'uc_code');
            $where['pay_type']='ONLINE';
            $res=$order_model->field('count(*) as num,sum(pay_time-create_time) as total_pay_time,uc_code')->where($where)->group('uc_code')->select();
            $pay_time=changeArrayIndex($res,'uc_code');
            //查出不包含预付款的已付款总金额
            unset($where['pay_type']);
            $where['pay_method']=array('neq',PAY_METHOD_ONLINE_ADVANCE);
            $no_advance_pay=$order_model->field('sum(real_amount) as pay_total_amount,uc_code')->where($where)->group('uc_code')->select();
//        echo D('',$this->tablePrefix,$this->connection)->getLastSql();exit;
            $no_advance_pay=changeArrayIndex($no_advance_pay,'uc_code');
            foreach($pay_info as $key=>$val){
                if($pay_time[$key]){
                    $pay_info[$key]['average_pay_time']=round($pay_time[$key]['total_pay_time']/$pay_time[$key]['num'],1);
                }else{
                    $pay_info[$key]['average_pay_time']='--';
                }
                if($no_advance_pay[$key]['pay_total_amount']){
                    $pay_info[$key]['no_advance_pay']=$no_advance_pay[$key]['pay_total_amount'];
                }else{
                    $pay_info[$key]['no_advance_pay']=0;
                }
            }
        return $this->res($pay_info);
    }

    /**
     * 得到已付款的订单
     * Base.BicModule.Uc.Data.completeOrder
     * @param type $params
     */
    public function completeOrder($params){
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $this->_rule = array(
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询开始时间
            array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询结束时间
            array('uc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $start_time = $params['start_time'];
        $end_time   = $params['end_time'];


        //组装where条件
        $where=array();
        !empty($start_time) && empty($end_time) && $where['success_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['success_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['success_time'] = array('between', array($start_time, $end_time));

        $uc_code=$params['uc_code'];
//        var_dump($uc_code);exit;
        $where['uc_code']=array('in',$uc_code);
        $data=$model->field('sum(real_amount) as order_amount,count(*) as complete_order_num,uc_code')->where($where)->group('uc_code')->select();
        $data=changeArrayIndex($data,'uc_code');
//        var_dump($data);exit;
        //查出往前推同等范围
        $where['success_time']=array('between',array($start_time-($end_time-$start_time),$start_time));
//        var_dump($where);exit;
        $link_data=$model->field('sum(real_amount) as order_amount,count(*) as complete_order_num,uc_code')->where($where)->group('uc_code')->select();
//        var_dump($link_data);exit;
        $link_data=changeArrayIndex($link_data,'uc_code');

//        var_dump($res);exit;

//        var_dump($link_data);exit;
//        var_dump($data);exit;
        $total=array();
            foreach($uc_code as $key=>$val){
                if($data[$val]['order_amount']>0 && $link_data[$val]['order_amount']>0){
                    $total[$val]['order_amount']=$data[$val]['order_amount'];
                    $total[$val]['complete_order_num']=$data[$val]['complete_order_num'];
                    $total[$val]['uc_code']=$data[$val]['uc_code'];
                    $total[$val]['link_data']=round(($data[$val]['order_amount']-$link_data[$val]['order_amount'])/$link_data[$val]['order_amount'],3)*100;
                    $total[$val]['link_data']=$total[$val]['link_data'].'%';
                }
                if(!$data[$val]['order_amount'] && $link_data[$val]['order_amount']>0){
                    $total[$val]['order_amount']=0;
                    $total[$val]['complete_order_num']=0;
                    $total[$val]['uc_code']=$val;
                    $total[$val]['link_data']=round(($data[$val]['order_amount']-$link_data[$val]['order_amount'])/$link_data[$val]['order_amount'],3)*100;
                    $total[$val]['link_data']=$total[$val]['link_data'].'%';
                }
                if($data[$val]['order_amount'] && !$link_data[$val]['order_amount']){
                    $total[$val]['order_amount']=$data[$val]['order_amount'];
                    $total[$val]['complete_order_num']=$data[$val]['complete_order_num'];
                    $total[$val]['uc_code']=$data[$val]['uc_code'];
                    $total[$val]['link_data']='--';
                }
                if(!$data[$val]['order_amount'] && !$link_data[$val]['order_amount']){
                    $total[$val]['order_amount']=0;
                    $total[$val]['complete_order_num']=0;
                    $total[$val]['uc_code']=$val;
                    $total[$val]['link_data']='--';
                }
        }
//        $arr['data']=$data;
//        $arr['link_data']=$link_data;
//        var_dump($data);exit;
        return $this->res($total);
    }
    //得到每个买家最后下单的时间
    public function lastTime($params){
        $model=D('OcB2bOrderSuccess',$this->tablePrefix,$this->connection);
        $uc_code=$params['uc_code'];
        //得到每个买家最后一次成单的时间
        $where['uc_code']=array('in',$uc_code);
        $res=$model->field('max(success_time) as last_time,uc_code')->where($where)->group('uc_code')->select();
        $res=changeArrayIndex($res,'uc_code');
        return $this->res($res);
    }
}
?>
