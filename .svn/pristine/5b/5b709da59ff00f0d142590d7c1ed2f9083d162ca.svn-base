<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 购物车相关模块
 */

namespace Base\UserModule\Coupon;

use System\Base;

class Coupon extends Base {

    public function __construct() {
        parent::__construct();
    }


    /**
     * 注册添加优惠券
     * Base.UserModule.Coupon.Coupon.addCoupon
     * @access public
     */

    public function addCoupon($params){

        $this->startOutsideTrans();
        $this->_rule = array(
            array('coupons', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), # 优惠券信息
            array('active', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),  # 优惠活动信息
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),                     # 用户编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),                   # 订单编码
            array('full_money', 'require', PARAMS_ERROR, ISSET_CHECK),                 # 策略要求金额
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code  = $params['uc_code'];
        $active   = $params['active'];
        $coupons  = $params['coupons'];
        $b2b_code = $params['b2b_code'];
        $temp = array();                        # 优惠券信息


        // 判断用户信息是否存在（队列与事物先后性区别）
        $user_msg = D('UcMember')->where(array('uc_code'=>$uc_code))->find();
        if(empty($user_msg)){
            return $this->res(NULL,1007);       # 用户不存在  重新跑队列
        }


        // 根据uc_code 获取业务员姓名
        $salesman = D('UcMember')->alias('um')->field('uu.*,us.invite_code')
                                 ->join("{$this->tablePrefix}uc_salesman us ON um.invite_code = us.invite_code ",'LEFT')
                                 ->join("{$this->tablePrefix}uc_user uu ON uu.uc_code = us.uc_code ",'LEFT')
                                 ->where(array('um.uc_code'=>$uc_code))
                                 ->master()
                                 ->find();

        
        $invite_code = $salesman['invite_code'];
        $salesman = $salesman['real_name'];
        // 判断是否领过此类型的优惠券
        $history_coupons_where['uc_code'] = $uc_code;
        $history_coupons_where['active_code'] = $active['active_code'];
        $history_coupons_where['condition_flag'] = $active['condition_flag'];
        !empty($b2b_code) && $history_coupons_where['ext1'] = $b2b_code;
        $history_coupons_res = D('UcMemberCouponLog')->where($history_coupons_where)->find();
        if(!empty($history_coupons_res)){
            return $this->res(true);       # 该用户已经领过优惠券
        }

        $amount = 0;
        foreach ($coupons as $k => $v) {

            // 增加优惠券
            $data = array(
                'uc_code'=>$uc_code,
                'active_code'=>$active['active_code'],          # 活动编码
                'bat_code'=>$v['bat_code'],                     # 优惠批次编码
                'condition_flag'=>$active['condition_flag'],    # 活动标识
                'rule_flag'=>$active['rule_flag'],              # 活动规则
                'type_flag'=>$v['type_flag'],                   # 优惠券类型
                'name'     =>$v['name'],                        # 优惠券名称
                'price'    =>$v['price'],                       # 优惠券面额
                'limit_money'=>$v['limit_money'],               # 使用条件
                'days'      =>$v['days'],                       # 多少天有效
                'time_type' => $v['time_type'],                 # 时间类型
                'start_time'=>$v['start_time'],                 # 开始时间
                'end_time'=>$v['end_time'],                     # 结束时间
                'create_time'=>NOW_TIME,                        # 增加时间
                'update_time'=>NOW_TIME,                        # 更新时间
                'status'=>SPC_MEMBER_COUPON_STATUS_ENABLE,      # 可用
                'salesman'=>$salesman,                          # 业务员
                'effect_day'=>$v['effect_day'],                 # 生效时间
                'invite_code'=>$invite_code,                    # 邀请码    
                );

            if($data['time_type'] == SPC_COUPON_TIME_TYPE_DAYS){
                $data['start_time'] = strtotime(date('Y-m-d',NOW_TIME)) + $data['effect_day']*86400;
                $data['end_time']   = strtotime(date('Y-m-d',NOW_TIME)) + ($data['days']+$data['effect_day'])*86400 - 1;
            }

            //修改优惠券数量
            $number_params = array(
                'num'=>$v['single_num'],
                'method'=>'-',
                'bat_code'=>$v['bat_code'],
                );

            $apiPath = "Base.SpcModule.Coupon.Coupon.updateLastNums";
            $number_res = $this->invoke($apiPath,$number_params);

            if($number_res['status'] == 7105){
                return $this->res(true);
            }elseif($number_res['status'] !== 0){
                return $this->res(NULL,$number_res['status']);              # 修改优惠券数量失败
            }

            // 添加优惠券张数
            for ($i=0; $i < $v['single_num']; $i++) { 
                // 生成优惠券编码
                $code_data = array(
                    "busType"    => SPC_COUPON_CODE,
                    "preBusType" => SPC_COUPON_COUPON_CODE,
                    "codeType"   => SEQUENCE_SPC,
                );
                $coupon_code = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $code_data);
                if( $coupon_code['status'] !== 0) {
                    return $this->res(NULL, 7100);
                }
                $data['coupon_code'] = $coupon_code['response'];     # 优惠券编码

                $temp[$v['bat_code']][] = $data;                     # 优惠券信息 添加 
                $amount += $data['price'];                           # 优惠券总价

                if($b2b_code){
                    $data['full_back_strategy'] =  empty($params['full_money']) ? "不限" : "订单支付满".$params['full_money']."元";        # 满返活动策略
                }
                $coupon_res = D('UcMemberCoupon')->add($data);
                if($coupon_res === FALSE){
                    return $this->res(NULL,7101);
                }
            } 

        }


        //添加优惠券领取记录表
        $log_params = array();                                          
        $log_params['uc_code'] = $uc_code;
        $log_params['active_code'] = $active['active_code'];
        $log_params['condition_flag'] = $active['condition_flag'];
        $log_params['coupon_info'] = json_encode($temp);
        $log_params['amount'] = $amount;
        !empty($b2b_code) && $log_params['ext1'] = $b2b_code;             
        $apiPath = "Base.UserModule.Coupon.CouponLog.add";
        $log_res = $this->invoke($apiPath,$log_params);
        if($log_res['status'] !== 0){
            return $this->res(NULL,$log_res['status']);
        }

        // 存入缓存
        if(!empty($b2b_code)){
            $key = \Library\Key\RedisKeyMap::getCouponKey($b2b_code);
            $coupon = \Library\Key\RedisKeyMap::getCouponHashKeyLog($b2b_code);
            $redis = R();
            $redis->Hset($key,$coupon,$amount);
            $redis->setTimeout($key,259200);
        }

        return $this->res(true);

    }




    /**
     * 获取优惠券总钱数
     * Base.UserModule.Coupon.Coupon.amount
     * @access public
     * @author Todor
     */

    public function amount($params){
        $this->_rule = array(
            array('flag', 'require', PARAMS_ERROR, MUST_CHECK),     # 或活动标识
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),  # 用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['condition_flag'] = $params['flag'];
        $where['uc_code'] = $params['uc_code'];
        $where['have_show'] = "NO";
        $res = D('UcMemberCouponLog')->where($where)->find();
        return $this->res($res['amount']);
    }


    /**
     * 获取优惠券列表
     * Base.UserModule.Coupon.Coupon.lists
     * @access public
     * @author Todor
     */

    public function lists($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),  # 用户编码
            array('coupon_status', array(SPC_MEMBER_COUPON_STATUS_ENABLE,SPC_MEMBER_COUPON_STATUS_INVALID,SPC_MEMBER_COUPON_STATUS_OVERDUE,SPC_MEMBER_COUPON_STATUS_USED,"ENABLE_AND_INVALID"), PARAMS_ERROR, MUST_CHECK,'in'),  # 状态
            array('time_limit',array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),  # 是否有时间限制
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $coupon_status = $params['coupon_status'];
        $time_limit = $params['time_limit'];

        $where['uc_code'] = $params['uc_code'];
        switch ($coupon_status) {
            case SPC_MEMBER_COUPON_STATUS_ENABLE:     # 可用
                $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
                $where['start_time'] = array('elt',NOW_TIME);
                $where['end_time']   = array('egt',NOW_TIME);
                $status = SPC_MEMBER_COUPON_STATUS_ENABLE;
                break;

            case SPC_MEMBER_COUPON_STATUS_INVALID:    # 未生效
                $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
                $where['start_time'] = array('egt',NOW_TIME);
                $status = SPC_MEMBER_COUPON_STATUS_INVALID;
                break;

            case SPC_MEMBER_COUPON_STATUS_OVERDUE:    # 已过期
                $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
                $where['end_time'] = array('elt',NOW_TIME);
                $time_limit == "YES" && $where['end_time'] = array('between',array((NOW_TIME-48*3600),NOW_TIME));
                $status = SPC_MEMBER_COUPON_STATUS_OVERDUE;
                break;

            case SPC_MEMBER_COUPON_STATUS_USED:       # 已使用
                $status_where[] = array('status'=>SPC_MEMBER_COUPON_STATUS_USED,'occupy_time'=>array('between',array((NOW_TIME-48*3600),NOW_TIME)));
                $status_where[] = array('status'=>SPC_MEMBER_COUPON_STATUS_OCCUPY,'use_time'=>array('between',array((NOW_TIME-48*3600),NOW_TIME)));
                $status_where['_logic'] = 'or';
                $where['_complex'] = $status_where;
                $status = SPC_MEMBER_COUPON_STATUS_USED;
                break;
            case "ENABLE_AND_INVALID":                 # 已使用 与 未生效
                $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
                $where['end_time']   = array('egt',NOW_TIME);
                $status = SPC_MEMBER_COUPON_STATUS_ENABLE;
                break;
            default:
                return $this->res(NULL,7105);   # 该优惠券状态不存在
                break;
        }

        $fields = "coupon_code,name,price,limit_money,days,start_time,end_time,time_type";
        $res = D('UcMemberCoupon')->field($fields)->where($where)->order('create_time desc')->select();

        foreach ($res as $k => &$v) {
            $v['status'] = $status;
        }
        unset($v);

        return $this->res($res);
    }


    /**
     * 获取每个优惠券数量
     * Base.UserModule.Coupon.Coupon.getNums
     * @access public
     * @author Todor
     */

    public function getNums($params){

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),  # 用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //可用
        $where['uc_code'] = $params['uc_code'];
        $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
        $where['start_time'] = array('elt',NOW_TIME);
        $where['end_time']   = array('egt',NOW_TIME);
        $enable = D('UcMemberCoupon')->field("count(*) as num ")->where($where)->select();
        $enable = $enable['0']['num'];

        //未生效
        $where = array();
        $where['uc_code'] = $params['uc_code'];
        $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
        $where['start_time'] = array('egt',NOW_TIME);
        $invalib = D('UcMemberCoupon')->field("count(*) as num ")->where($where)->select();
        $invalib = $invalib['0']['num'];
        //全部
        $where = array();
        $status_where[] = array('status'=>SPC_MEMBER_COUPON_STATUS_ENABLE,'end_time'=>array('between',array((NOW_TIME-48*3600),NOW_TIME))); 
        $status_where[] = array('status'=>SPC_MEMBER_COUPON_STATUS_USED,'use_time'=>array('between',array((NOW_TIME-48*3600),NOW_TIME)));
        $status_where[] = array('status'=>SPC_MEMBER_COUPON_STATUS_OCCUPY,'use_time'=>array('between',array((NOW_TIME-48*3600),NOW_TIME)));
        $status_where['_logic'] = 'or';
        $where['_complex'] = $status_where;
        $where['uc_code'] = $params['uc_code'];
        $all = D('UcMemberCoupon')->field("count(*) as num ")->where($where)->select();

        $all = $all['0']['num'];
        $all = $enable+$invalib+$all;

        $res = array(
            'enable'=>$enable,
            'invalib'=>$invalib,
            'all'=>$all,
            );
        return $this->res($res);

    }



    /**
     * 获取用户订单价格匹配优惠券数量
     * Base.UserModule.Coupon.Coupon.suitable
     * @access public
     * @author Todor
     */

    public function suitable($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),  # 用户编码
            array('total_amount','require', PARAMS_ERROR, MUST_CHECK),  # 订单总钱数
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where = array();
        $where['uc_code'] = $params['uc_code'];
        $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
        $where['start_time'] = array('elt',NOW_TIME);
        $where['end_time']   = array('egt',NOW_TIME);
        $where['price']      = array('elt',$params['total_amount']);
        $where['limit_money']  = array('elt',$params['total_amount']);

        $fields = "count(*) as num";
        $res = D('UcMemberCoupon')->field($fields)->where($where)->select();

        if($res === FALSE){
            return $this->res(NULL,6067);
        }

        return $this->res($res);
    }


    /**
     * 获取用户订单价格优惠券
     * Base.UserModule.Coupon.Coupon.get
     * @access public
     * @author Todor
     */

    public function get($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 用户编码
            array('total_amount','require', PARAMS_ERROR, MUST_CHECK),  # 订单总钱数
            array('coupon_code','require', PARAMS_ERROR, ISSET_CHECK),  # 优惠券编码
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where = array();
        $where['uc_code'] = $params['uc_code'];
        $where['status'] = SPC_MEMBER_COUPON_STATUS_ENABLE;
        $where['start_time'] = array('elt',NOW_TIME);
        $where['end_time']   = array('egt',NOW_TIME);
        $where['price']      = array('elt',$params['total_amount']);
        $where['limit_money']  = array('elt',$params['total_amount']);
        !empty($params['coupon_code']) && $where['coupon_code']  = $params['coupon_code'];

        $fields = "coupon_code,name,price,limit_money,days,time_type,start_time,end_time,status";
        $res = D('UcMemberCoupon')->field($fields)->where($where)->select();

        if($res === FALSE){
            return $this->res(NULL,6066);
        }

        return $this->res($res);
    }

    /**
     * 统计过期的优惠券数量
     * Base.UserModule.Coupon.Coupon.overNum
     * @access public
     */

    public function overNum($params){
        $this->_rule = array(
            array('bat_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
            array('hide', 'require', PARAMS_ERROR, ISSET_CHECK),      # 是否隐藏测试数据
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $bat_codes = $params['bat_codes'];
        $where = [
            'bat_code'=>['in',$bat_codes],
            'end_time'=>['lt',NOW_TIME],
            'status'=>['eq','ENABLE']
        ];
        if($params['hide']==1){
            $where['invite_code'] = ['neq',C('TEXT_INVITE_COE')];
        }
        $result = D('UcMemberCoupon')->field('count(*) as num,bat_code')->group('bat_code')->where($where)->select();
        if($result){
            $result = changeArrayIndex($result,'bat_code');
        }
        return $this->res($result);
    }


    /**
     * 统计已使用的优惠券数量
     * Base.UserModule.Coupon.Coupon.overNum
     * @access public
     */

    public function useNum($params){
        $this->_rule = array(
            array('bat_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
            array('hide', 'require', PARAMS_ERROR, ISSET_CHECK),      # 是否隐藏测试数据
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $bat_codes = $params['bat_codes'];
        $where = [
            'bat_code'=>['in',$bat_codes],
            'status'=>'USED'
        ];
        if($params['hide']==1){
            $where['invite_code'] = ['neq',C('TEXT_INVITE_COE')];
        }
        $result = D('UcMemberCoupon')->field('count(*) as num,bat_code')->group('bat_code')->where($where)->select();
        if($result){
            $result = changeArrayIndex($result,'bat_code');
        }
        return $this->res($result);
    }
    /**
     * 统计已占用用的优惠券数量
     * Base.UserModule.Coupon.Coupon.occupyNum
     * @access public
     */

    public function occupyNum($params){
        $this->_rule = array(
            array('bat_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
            array('hide', 'require', PARAMS_ERROR, ISSET_CHECK),      # 是否隐藏测试数据
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $bat_codes = $params['bat_codes'];
        $where = [
            'bat_code'=>['in',$bat_codes],
            'status'=>'OCCUPY'
        ];
        if($params['hide']==1){
            $where['invite_code'] = ['neq',C('TEXT_INVITE_COE')];
        }
        $result = D('UcMemberCoupon')->field('count(*) as num,bat_code')->group('bat_code')->where($where)->select();
        if($result){
            $result = changeArrayIndex($result,'bat_code');
        }
        return $this->res($result);
    }
    /**
     * 统计已领取的优惠券数量
     * Base.UserModule.Coupon.Coupon.pickNum
     * @access public
     */

    public function pickNum($params){
        $this->_rule = array(
            array('bat_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
            array('hide', 'require', PARAMS_ERROR, ISSET_CHECK),      # 是否隐藏测试数据
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $bat_codes = $params['bat_codes'];
        $where = [
            'bat_code'=>['in',$bat_codes],
        ];
        if($params['hide']==1){
            $where['invite_code'] = ['neq',C('TEXT_INVITE_COE')];
        }
        $result = D('UcMemberCoupon')->field('count(*) as num,bat_code')->group('bat_code')->where($where)->select();
        if($result){
            $result = changeArrayIndex($result,'bat_code');
        }
        return $this->res($result);
    }

    /**
     * 获取优惠券的信息
     * Base.UserModule.Coupon.Coupon.getCoupon
     * @access public
     */
    public function getCoupon($data){
        $coupon_code = $data['coupon_code'];
        $uc_code = $data['uc_code'];
        $info = D('UcMemberCoupon')->field('coupon_code,price,active_code,limit_money')->where(['coupon_code'=>$coupon_code,'uc_code'=>$uc_code])->find();
        if(!$info){
            return $this->res('',7102);
        }
        return $this->res($info);
    }

    /**
     * 设置优惠券的状态
     * Base.UserModule.Coupon.Coupon.setStatus
     * @access public
     */
    public function setStatus($params){
        $this->_rule = array(
            array('coupon_code', 'require', PARAMS_ERROR, MUST_CHECK),   # 优惠券编码
            array('use_time', 'require', PARAMS_ERROR, ISSET_CHECK),   # 使用时间
            array('flag', 'require', PARAMS_ERROR, ISSET_CHECK),   # 操作标志
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if($params['flag'] == 'occupy'){
            $data = [
                'status'=>'OCCUPY',
                'update_time'=>NOW_TIME,
                'use_time'=>NOW_TIME,
            ];
            $operate_status = 'ENABLE';
        }else{
            $data = [
                'status'=>'USED',
                'use_time'=>$params['use_time'],
                'update_time'=>NOW_TIME,
            ];
            $operate_status = 'OCCUPY';
        }
        $res = D('UcMemberCoupon')->where(['coupon_code'=>$params['coupon_code'],'status'=>$operate_status])->save($data);
        if($res === false || $res<=0){
            return $this->res('',2012);
        }
        return $this->res(true);
    }

    /**
     * 设置优惠券的状态
     * Base.UserModule.Coupon.Coupon.rollback
     * @access public
     */

    public function rollback($params){
        $this->_rule = array(
            array('coupon_code', 'require', PARAMS_ERROR, MUST_CHECK),   # 优惠券编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = [
            'status'=>'ENABLE',
            'b2b_code'=>'',
            'occupy_time'=>'',
            'update_time'=>NOW_TIME,
        ];
        $res = D('UcMemberCoupon')->where(['coupon_code'=>$params['coupon_code']])->save($data);
        if($res === false){
            return $this->res('',2012);
        }
        return $this->res(true);
    }
    /**
     *获取操作优惠券状态的前置条件
     * Base.UserModule.Coupon.Coupon.operate
     * @access public
     */
    public function operate($params){
        $this->_rule = array(
            array('coupon_code', 'require', PARAMS_ERROR, MUST_CHECK),   # 优惠券编码
            array('operate_status', 'require', PARAMS_ERROR, MUST_CHECK),   # 要操作的状态
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $coupon_code = $params['coupon_code'];
        $operate_status = $params['operate_status'];
        $where = [];
        switch($operate_status){
            case 'occupy' :    #提交订单
                $map[] = ['status'=>'OCCUPY'];
                $map[] = ['status'=>'USED'];
                $map['_logic'] = 'or';
                $where_submit['_complex'] = $map;
                $where_submit['coupon_code'] = $coupon_code;
                $where_another['status'] = 'ENABLE';
                $where_another['coupon_code'] = $coupon_code;
                $where_another['end_time'] = ['lt',NOW_TIME];
                break;
            case 'pay' :     #支付订单
                $where['coupon_code'] = $coupon_code;
                $where['status'] = 'OCCUPY';
                break;
            case 'enable':   #回滚订单
                $where['coupon_code'] = $coupon_code;
                $where['status'] = 'OCCUPY';
                break;
            default:
        }
        if($where_submit){
            $info = D('UcMemberCoupon')->where($where_submit)->find();
            if($info){
                return $this->res('',7100);
            }
            $info = D('UcMemberCoupon')->where($where_another)->find();
            if($info){
                return $this->res('',7101);
            }
        }
       if($where){
           $info = D('UcMemberCoupon')->where($where)->find();
           if(!$info){
               return $this->res('',7088);
           }
       }
        return $this->res(true);
    }


    /**
     * 更新订单号
     *更新订单号
     * Base.UserModule.Coupon.Coupon.updateB2bCode
     * @access public
     */
    public function updateB2bCode($params){
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 订单号
            array('coupon_code', 'require', PARAMS_ERROR, MUST_CHECK),  # 促销券编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $b2b_code = $params['b2b_code'];
        $coupon_code = $params['coupon_code'];
        $occupy_time = NOW_TIME;
        $res =  D('UcMemberCoupon')->where(['coupon_code'=>$coupon_code])->save(['b2b_code'=>$b2b_code,'occupy_time'=>$occupy_time]);
        if($res === false){
            return $this->res('',7104);
        }
        return $this->res(true);
    }





}

?>
