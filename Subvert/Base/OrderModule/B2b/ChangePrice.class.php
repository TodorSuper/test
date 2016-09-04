<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关模块
 */

namespace Base\OrderModule\B2b;
use System\Base;
class ChangePrice extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }

    public function changePrice($params)
    {
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK), //订单编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家编码
            array('info', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),  # 改价信息
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $check['b2b_code']=array('eq',$params['b2b_code']);
        $check['sc_code']=array('eq',$params['sc_code']);
        $exist=D('OcB2bOrder')->where($check)->find();
        if(!$exist){
            return $this->endInvoke('',7042);
        }
        //得到改价之前的real_amount
        $origin_real_amount = $exist['real_amount'];
        $origin_cope_amount = $exist['cope_amount'];
        $pay=D('OcB2bOrder')->field('pay_method,pay_status,ship_status,op_code,order_status,pay_type,ship_method')->where($check)->find();
        $change=update_price($pay);
        if(!$change){
            return $this->endInvoke('',7043);
        }
        $pay_method=$pay['pay_method'];
        $pay_type = $pay['pay_type'];
        $ship_method = $pay['ship_method'];
        $info=$params['info'];
        $flag=false;
            foreach($info as $key=>$val){
                $data['goods_price']=$val['goods_price'];
                $where=array();
                $where['b2b_code']=array('eq',$params['b2b_code']);
                $where['sic_code']=array('eq',$val['sic_code']);
                $where['spc_code']=array('eq',$val['spc_code']);
                $where['sc_code']=array('eq',$params['sc_code']);
                $dbs = D('OcB2bOrderGoods');
                $res=$dbs->where($where)->save($data);
                if($res>0){
                    $flag=true;
                }
                $goods=$dbs->master()->field('goods_price,goods_number')->where($where)->find();
                $this_goods_num=$goods['goods_number'];#得到要改价的商品在这个订单中的数量
                $this_good_price[]=(intval($this_goods_num)) * ($goods['goods_price']);#得到改价后的商品在这个订单中的总金额
                if($res===false){
                    return $this->endInvoke('',7039);
                }
            }
            //获取下单人的电话号码
            $w['obo.b2b_code']=array('eq',$params['b2b_code']);
            $single=D('OcB2bOrder')->alias('obo')->field('oboe.mobile,oboe.remit_code')->where($w)->join("{$this->tablePrefix}oc_b2b_order_extend oboe on obo.op_code=oboe.op_code")->find();
            //计算改价之后的商品总价
            $this_good_prices=array_sum($this_good_price);
            $sic_codes=array_column($info,'sic_code');#改价的商品编码
            $wheres['b2b_code']=array('eq',$params['b2b_code']);
            $wheres['sic_code']=array('not in',$sic_codes);
            $wheres['sc_code']=array('eq',$params['sc_code']);
            //得到除改价之外的商品在此订单中的金额
            $goods_info=D('OcB2bOrderGoods')->field('sic_code,goods_number,goods_price')->where($wheres)->select();
            $money=array();
            if($goods_info){
                foreach($goods_info as $key=>$val){
                    $money[]=intval($val['goods_number']) * $val['goods_price'];
                }
                $other_total_price=array_sum($money);
            }else{
                $other_total_price=0;
            }

            //得到订单的总金额
            $order_total_price=$this_good_prices+$other_total_price;

            //更新订单的总金额
            $real_amount['cope_amount'] = $origin_cope_amount-($origin_real_amount-$order_total_price);
            $real_amount['real_amount']=$order_total_price;
            $real_amount['goods_amount']=$order_total_price;
            $real_amount['update_time']=NOW_TIME;
            $update=D('OcB2bOrder')->where(array('b2b_code'=>array('eq',$params['b2b_code'])))->save($real_amount);
            if($update===false){
                return $this->endInvoke('',7040);
            }
            //更改扩展表里的数据
            $total_real_amount['total_real_amount']=$order_total_price;
            $op_code=$pay['op_code'];
            $update_extend=D('OcB2bOrderExtend')->where(array('op_code'=>array('eq',$op_code)))->save($total_real_amount);
            if($update_extend===false){
                return $this->endInvoke('',7041);
            }
             //记录当前的操作名称
             $require['obo.b2b_code']=array('eq',$params['b2b_code']);
             $operat_info=D('OcB2bOrder')->alias('obo')->field('ss.linkman,ss.uc_code')->where($require)->join("{$this->tablePrefix}sc_store ss on obo.sc_code=ss.sc_code")->find();
             $add['uc_code']=$operat_info['uc_code'];
             $add['b2b_code']=$params['b2b_code'];
             $add['action_name']='订单改价';
             $add['pay_method']=$pay_method;
             $add['pay_type'] = $pay_type;
             $status=M('Base.OrderModule.B2b.Status.detailToGroup')->detailToGroup($pay['order_status'],$pay['ship_status'],$pay['pay_status'],$pay_type,$ship_method);
             $add['status']=$status['status'];
             $apiPath='Base.OrderModule.B2b.OrderAction.orderActionUp';
             $call=$this->invoke($apiPath,$add);
             if($call['status']!=0){
                 return $this->endInvoke('',7044);
             }
        $response['flag']=$flag;
        $response['pay_method']=$pay_method;
        $response['single']=$single;
        return $this->res($response);
    }


}

?>
