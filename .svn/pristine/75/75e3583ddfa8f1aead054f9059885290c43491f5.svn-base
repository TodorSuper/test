<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |优惠券相关
 */


namespace Base\SpcModule\Coupon;

use System\Base;


class Coupon extends Base
{

    public function __construct(){
        parent::__construct();
    }

    /**
     * Base.SpcModule.Coupon.Coupon.add
     * @param [type] $params [description]
     */
    public function add($params)
    {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('active_code', 'require', PARAMS_ERROR, MUST_CHECK),#促销活动编码
            array('type', 'require', PARAMS_ERROR, MUST_CHECK),#促销券类型
            array('name', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券名称
            array('price', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券面额
            array('limit_money', 'require', PARAMS_ERROR, MUST_CHECK),#使用条件 满多少元抵扣
            array('days', 'require', PARAMS_ERROR, ISSET_CHECK),#多少天内有效
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),#优惠券开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),#优惠券结束时间
            array('effect_day', 'require', PARAMS_ERROR, ISSET_CHECK),#优惠券生效时间
            array('full_back_id', 'require', PARAMS_ERROR, ISSET_CHECK),#活动策略ID
            array('nums', 'require', PARAMS_ERROR, ISSET_CHECK),#发行优惠券数量
            array('time_type', 'require', PARAMS_ERROR, MUST_CHECK),#时间类型：DAYS:领取若干天内有效  ，TIME_SPAN 具体的时间
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //判断该活动状态下允不允许添加促销券
        $where = [];
        $map[] = array('status'=>SPC_STATUS_DRAFT,'active_code'=>$params['active_code']);
        $arr[] = array('status'=>SPC_STATUS_PUBLISH,'end_time'=>array('lt',NOW_TIME),'active_code'=>$params['active_code']);
        $arr[] = array('status'=>SPC_STATUS_END,'active_code'=>$params['active_code']);
        $arr['_logic'] = 'or';
        $map[] = array('_complex'=>$arr);
        $map['_logic'] = 'or';
        $where['_complex'] = $map;
        $active = D('SpcActive')->where($where)->find();
        if(!$active){
            return $this->res('',7099);
        }
        //生成优惠券批次号
        $codeData = array(
            "busType"    => SPC_COUPON_CODE,
            "preBusType" => SPC_COUPON_COUPON,
            "codeType"   => SEQUENCE_SPC
        );

        $batCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $codeData);
        if( $batCode['status'] !== 0) {
            return $this->res('', 7079);
        }
        //组装添加到表16860_spc_active_coupon中的数据
        $data = [
            'bat_code'=>$batCode['response'],
            'type_flag'=>$params['type'],
            'name'=>$params['name'],
            'price'=>$params['price'],
            'limit_money'=>$params['limit_money'],
            'days'=>$params['days'],
            'effect_day'=>$params['effect_day'],
            'start_time'=>$params['start_time'],
            'end_time'=>$params['end_time'],
            'time_type'=>$params['time_type'],
            'nums'=>$params['nums'],
            'last_nums'=>$params['nums'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>'ENABLE',
        ];
        $res = D('SpcActiveCoupon')->add($data);
        if($res === false || $res<=0){
            return $this->res('',7074);
        }
        //如果是新用户注册组装添加到表16860_spc_active_coupon_relation的数据
         $data = [
                'bat_code'=>$batCode['response'],
                'active_code'=>$params['active_code'],
                'create_time'=>NOW_TIME,
                'update_time'=>NOW_TIME,
                'status'=>'ENABLE',
         ];
         $res = D('SpcActiveCouponRelation')->add($data);
        if($params['full_back_id']){
            //如果是满返活动的优惠券则添加到表16860_spc_active_coupon_full_relation
            $data = [
                'bat_code'=>$batCode['response'],
                'active_code'=>$params['active_code'],
                'full_back_id'=>$params['full_back_id'],
                'create_time'=>NOW_TIME,
                'update_time'=>NOW_TIME,
                'status'=>'ENABLE',
            ];
            $res = D('SpcActiveCouponFullRelation')->add($data);
        }
        if($res === false || $res<=0){
            return $this->res('',7080);
        }
        return $this->res(true);
    }

    /**
     *Base.SpcModule.Coupon.Coupon.update
     * @param [type] $params [description]
     */
    public function update($params){
        $this->startOutsideTrans();
        $flag = $params['flag'];
        if($flag == 'delete' || $flag == 'disable' || $flag == 'enable'){
            $this->_rule = array(
                array('active_code', 'require', PARAMS_ERROR, ISSET_CHECK),#促销券对应的活动编码
                array('bat_code', 'require', PARAMS_ERROR, MUST_CHECK),#促销券批次
                array('flag', 'require', PARAMS_ERROR, ISSET_CHECK),#操作类型
            );
        }else{
            $this->_rule = array(
                array('active_code', 'require', PARAMS_ERROR, ISSET_CHECK),#促销券对应的活动编码
                array('bat_code', 'require', PARAMS_ERROR, MUST_CHECK),#促销券批次
                array('flag', 'require', PARAMS_ERROR, ISSET_CHECK),#操作类型
                array('type', 'require', PARAMS_ERROR, MUST_CHECK),#促销券类型
                array('name', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券名称
                array('price', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券面额
                array('limit_money', 'require', PARAMS_ERROR, MUST_CHECK),#使用条件 满多少元抵扣
                array('days', 'require', PARAMS_ERROR, ISSET_CHECK),#多少天内有效
                array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),#优惠券开始时间
                array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),#优惠券结束时间
                array('nums', 'require', PARAMS_ERROR, ISSET_CHECK),#发行优惠券数量
                array('time_type', 'require', PARAMS_ERROR, MUST_CHECK),#时间类型：DAYS:领取若干天内有效  ，TIME_SPAN 具体的时间
            );
        }
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //先查询该优惠券存不存在
        $status = $this->_getStatus($flag);
        $res = D('SpcActiveCoupon')->where(['bat_code'=>$params['bat_code'],'status'=>$status])->find();
        if(!$res){
            return $this->res('',7086);
        }
        if($flag == 'delete' || $flag == 'disable' || $flag == 'enable'){
            $where = [];
            switch($flag){
                case 'delete' :
                    $status ='DELETE';
                    $where['active_code'] = $params['active_code'];
                    $where['status'] = SPC_STATUS_DRAFT;
                    break;
                case 'disable' :
                    $status = 'DISABLE';
                    $where['active_code'] = $params['active_code'];
                    $map[] = array('status'=>SPC_STATUS_PUBLISH,'end_time'=>array('lt',NOW_TIME));
                    $map[] = array('status'=>SPC_STATUS_END);
                    $map['_logic'] = 'or';
                    $where['_complex'] = $map;
                    break;
                case 'enable' :
                    $status = 'ENABLE';
                    $where['active_code'] = $params['active_code'];
                    $map[] = array('status'=>SPC_STATUS_PUBLISH,'end_time'=>array('lt',NOW_TIME));
                    $map[] = array('status'=>SPC_STATUS_END);
                    $map['_logic'] = 'or';
                    $where['_complex'] = $map;
                    break;
                default;
            }
            //判断该优惠券所在活动允不允许操作
            $active = D('SpcActive')->where($where)->find();
            if(!$active){
                return $this->res('',7099);
            }
            $data = [
                'status'=>$status,
                'update_time'=>NOW_TIME
            ];
            $res = D('SpcActiveCoupon')->where(['bat_code'=>$params['bat_code']])->save($data);
            if($res === false){
                return $this->res('',7098);
            }
            return $this->res(true);
        }
        //判断该优惠券所在活动允不允许更新
        $active = D('SpcActive')->where(['status'=>SPC_STATUS_DRAFT,'active_code'=>$params['active_code']])->find();
        if(!$active){
            return $this->res('',7099);
        }
        //组装要更新的数据
        $data = [
            'type_flag'=>$params['type'],
            'name'=>$params['name'],
            'price'=>$params['price'],
            'limit_money'=>$params['limit_money'],
            'days'=>$params['days'],
            'start_time'=>$params['start_time'],
            'end_time'=>$params['end_time'],
            'time_type'=>$params['time_type'],
            'nums'=>$params['nums'],
            'last_nums'=>$params['nums'],
            'update_time'=>NOW_TIME,
        ];
        $res = D('SpcActiveCoupon')->where(['bat_code'=>$params['bat_code']])->save($data);
        if($res === false){
            return $this->res('',7085);
        }
        return $this->res(true);
    }

    //获取操作前置状态
    private function _getStatus($flag){
        switch($flag){
            case 'delete' :
                $status = 'ENABLE';
                break;
            case 'disable' :
                $status = 'ENABLE';
                break;
            case 'enable' :
                $status = 'DISABLE';
                break;
            default:
                $status = 'ENABLE';
                break;
        }
        return $status;
    }


    /**
     * 更新优惠券剩余数量
     * Base.SpcModule.Coupon.Coupon.updateLastNums
     * @param [type] $params [description]
     * @author Todor
     */

    public function updateLastNums($params){

        $this->startOutsideTrans();
        $this->_rule = array(
            array('num', 'require', PARAMS_ERROR, MUST_CHECK),     # 更改数量
            array('method', 'require', PARAMS_ERROR, MUST_CHECK),  # 方法
            array('bat_code', 'require', PARAMS_ERROR, MUST_CHECK),# 促销券批次
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $data = $where = array();
        $where['bat_code'] = $params['bat_code'];
        $data['update_time'] = NOW_TIME;
        $data['last_nums'] = array($params['method'],$params['num']);

        if($params['method'] == '-'){
            $where['last_nums'] = array('egt',$params['num']);
        }
        $update_res = D('SpcActiveCoupon')->where($where)->save($data);
        if($update_res === false){
            return $this->res(NULL,7103);
        }
        if($update_res === 0){ 
            return $this->res(null,7105);
        }

        return $this->res(true);
    }


}