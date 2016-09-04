<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |优惠券相关
 */


namespace Base\SpcModule\Coupon;

use System\Base;


class Center extends Base{

    public function __construct(){
        parent::__construct();
    }


    /**
     * B2B优惠券获得入口
     * Base.SpcModule.Coupon.Center.getCoupon
     * @param type $params
     * @return type
     * @author Todor
     */

    public function getCoupon($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('flag', 'require', PARAMS_ERROR, MUST_CHECK),     # 或活动标识
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),  # 用户编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),# 订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }


        // 获取操作句柄
        $control = 'coupon:'.$params['flag'].':'.$params['uc_code'];
        $handle = $this->getCouponHandle($control, 5);  # 获取操作句柄, 设定句柄的过期时间为5秒(通用)(二次封装)
        if( !is_object($handle) ) {
            return $this->res('', $handle); # 获取操作句柄失败
        }


        // 查看对应活动标识有没有活动
        $apiPath = "Base.SpcModule.Coupon.Active.getRecent";
        $active_res = $this->invoke($apiPath,$params);
        if($active_res['status'] !== 0){
            return $this->endInvoke(NULL,$active_res['status']); # 相同条件的优惠活动存在多个
        }else{
            if(!is_array($active_res['response'])){
                return $this->res(true);                         # 不存在活动
            } 
        }

        $active_res = $active_res['response'][0];
        $rule_flag = $active_res['rule_flag'];
        $condition_flag = $active_res['condition_flag'];
        $rule = $rule_flag."_".$condition_flag;
        
        //根据活动规则
        $condition_params = array();
        switch ($rule) {
            case SPC_ACTIVE_RULE_FLAG_ONE_TIME."_".SPC_ACTIVE_CONDITION_FLAG_REGISTER:      # 一次发放所有优惠券  并且为注册
                $apiPath = "Base.SpcModule.Coupon.Center.registerOneTime";
                break;
            
            case SPC_ACTIVE_RULE_FLAG_ONE_TIME."_".SPC_ACTIVE_CONDITION_FLAG_FULL_BACK:     # 一次发放所有优惠券  并且为满返
                $apiPath = "Base.SpcModule.Coupon.Center.fullBackOneTime";
                $condition_params['b2b_code'] = $params['b2b_code'];

                break;

            default:
                return $this->res(NULL,7084);
                break;
        }

        $condition_params['active_res'] = $active_res;
        $condition_params['uc_code']    = $params['uc_code'];
        $condition_res = $this->invoke($apiPath,$condition_params);

        if($condition_res['status'] !== 0){
            return $this->res(NULL,$condition_res['status']);
        }

        $handle->closeHandle($control);
        return $this->res(true);

    }




    /**
     * 一次性发放所有优惠券 注册
     * Base.SpcModule.Coupon.Center.registerOneTime
     * @access public
     * @author Todor
     */

    public function registerOneTime($params){

        $this->startOutsideTrans();
        $this->_rule = array(
            array('active_res', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),     # 活动信息
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),                           # 用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $active_res = $params['active_res'];
        $uc_code    = $params['uc_code'];

        // 根据活动获取批次
        $coupon_res = $this->_getRegisterCoupon($active_res);


        // 如果存在其中1个批次的券发行量不足,该活动优惠券批次至少有一个过期 则领取失败
        $this->_checkNumTime($coupon_res);

        $new_params['coupons'] = $coupon_res;
        $new_params['active']  = $active_res;
        $new_params['uc_code'] = $uc_code;
        $apiPath = "Base.UserModule.Coupon.Coupon.addCoupon";
        $member_coupons_res = $this->invoke($apiPath,$new_params);
        if($member_coupons_res['status'] !== 0){
            return $this->res(NULL,$member_coupons_res['status']);
        }

        return $this->res(true);
    }


    /**
     * 注册根据活动获取批次
     * @access private
     */

    private function _getRegisterCoupon($active_res){
        $where['sacr.active_code'] = $active_res['active_code'];
        $where['sacr.status'] = "ENABLE";
        $where['sac.status'] = "ENABLE";
        $fields = "sacr.active_code,sac.*";

        $coupon_res = D('SpcActiveCouponRelation')->alias('sacr')->field($fields)
                                                  ->join("{$this->tablePrefix}spc_active_coupon sac ON sac.bat_code = sacr.bat_code",'INNER')
                                                  ->where($where)
                                                  ->select();
        return $coupon_res;
    }




    /**
     * 一次性发放所有优惠券 满返
     * Base.SpcModule.Coupon.Center.fullBackOneTime
     * @access public
     * @author Todor
     */

    public function fullBackOneTime($params){

        $this->startOutsideTrans();
        $this->_rule = array(
            array('active_res', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),     # 活动信息
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),                           # 用户编码
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),                          # 订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $active_res = $params['active_res'];
        $uc_code    = $params['uc_code'];
        $b2b_code   = $params['b2b_code'];


        // 通过b2b_code 获取订单信息
        $order_params = array('b2b_code'=>$b2b_code); 
        $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        $order_res = $this->invoke($apiPath,$order_params);
        $amount = $order_res['response']['order_info']['real_amount'];

        // 获取符合条件的所有策略
        $map['active_code'] = $active_res['active_code'];
        $map['status']  = "ENABLE";
        $map['full_money']  = array('elt',$amount);
        $order = 'full_money desc';
        $full_back_res = D('SpcActiveFullBack')->where($map)->order($order)->select();

        if(empty($full_back_res)){
            return $this->res(true);        # 没有符合策略
        }


        // 通过策略查找优惠券批次
        foreach ($full_back_res as $k => $v) {
            
            $where['sacfr.active_code'] = $v['active_code'];
            $where['sacfr.status'] = "ENABLE";
            $where['sac.status'] = "ENABLE";
            $where['sacfr.full_back_id'] = $v['id'];
            $fields = "sacfr.active_code,sac.*";    

            $coupon_res = D('SpcActiveCouponFullRelation')->alias('sacfr')->field($fields)
                                                      ->join("{$this->tablePrefix}spc_active_coupon AS sac ON sac.bat_code = sacfr.bat_code","LEFT")
                                                      ->where($where)
                                                      ->select();

            // 查看优惠券条件
            if(empty($coupon_res)){
                unset($full_back_res[$k]);
                continue;
            }else{
                foreach ($coupon_res as $key => $value) {
                    if($value['last_nums'] < $value['single_num']){         # 该活动优惠券批次至少有一个批次不足
                        unset($full_back_res[$k]);
                        continue 2;
                    }

                    if($value['time_type'] === SPC_COUPON_TIME_TYPE_TIME_SPAN && $value['end_time'] < NOW_TIME){  # 该活动优惠券批次至少有一个过期
                        unset($full_back_res[$k]);
                        continue 2;
                    }
                }

                $full_back_res[$k]['amount'] = $amount;
                $full_back_res[$k]['coupon_res'] = $coupon_res;
            }
        }

        $full_back_res = array_slice($full_back_res, 0,1,false)[0];
        
        if(empty($full_back_res)){
            return $this->res(true);        # 没有符合策略
        }
        $coupon_res = $full_back_res['coupon_res'];

        $new_params['coupons'] = $coupon_res;
        $new_params['active']  = $active_res;
        $new_params['uc_code'] = $uc_code;
        $new_params['b2b_code'] = $b2b_code;
        $new_params['full_money'] = $full_back_res['full_money'];
        $apiPath = "Base.UserModule.Coupon.Coupon.addCoupon";
        $member_coupons_res = $this->invoke($apiPath,$new_params);
        if($member_coupons_res['status'] !== 0){
            return $this->res(NULL,$member_coupons_res['status']);
        }

        return $this->res(true);   
    }


    /**
     * 优惠券时间与数量判断
     * @author Todor
     * @access private
     */

    private function _checkNumTime($coupon_res){

        foreach ($coupon_res as $k => $v) {
            if($v['last_nums'] < $v['single_num']){
                return $this->endInvoke(true);          # 该活动优惠券批次至少有一个批次不足
            }

            if($v['time_type'] === SPC_COUPON_TIME_TYPE_TIME_SPAN && $v['end_time'] < NOW_TIME){  # 该活动优惠券批次至少有一个过期
                return $this->endInvoke(true);
            }

        }

        return true;

    }



    /**
     * 获取账号的操作权限
     *
     * getAccontHandle 
     * 
     * @param mixed $ucCode 
     * @param mixed $type 
     * @access private
     * @return max 成功返回操作句柄 失败返回错误号
     */
    private function getCouponHandle($control, $expires) {
        # 获取操作句柄
        $handle = $this->invoke('Com.Tool.Handle.Redis.createHandle', array('uniqueString'=>$control, 'expires'=> $expires));
        if($handle['status'] === 0) {
            return $handle['response'];  # return obj
        }else {
            return $handle['status'];
        }

    }


    /**
     * 获取活动列表
     * Base.SpcModule.Coupon.Center.getRecentActives
     * @author Todor
     */

    public function getRecentActives($params){

         // 查看对应活动标识有没有活动
        $fields = "active_code,active_name,condition_flag,rule_flag,start_time,end_time,status,active_banner,desc";
        // $where['start_time'] = array('elt',NOW_TIME);
        $where['end_time'] = array('egt',NOW_TIME);
        $where['status'] = SPC_ACTIVE_STATUS_PUBLISH;
        $where['is_store_show'] = "YES";
        $active_res = D('SpcActive')->field($fields)->where($where)->order('id desc')->select();

        //判断条件
        foreach ($active_res as $k => $v) {

            // 获取活动的优惠券批次
            $active_code = $v['active_code'];
            switch ($v['condition_flag']) {
                case SPC_ACTIVE_CONDITION_FLAG_REGISTER:                # 注册
                    $coupon_res = $this->_getRegisterCoupon($v);
                    foreach ($coupon_res as $key => $value) {
                        if($value['last_nums'] < $value['single_num']){         # 该活动优惠券批次至少有一个批次不足
                            unset($active_res[$k]);       
                        }

                        if($value['time_type'] === SPC_COUPON_TIME_TYPE_TIME_SPAN && $value['end_time'] < NOW_TIME){  # 该活动优惠券批次至少有一个过期
                            unset($active_res[$k]);
                        }

                    }
                    break;
                case SPC_ACTIVE_CONDITION_FLAG_FULL_BACK:               # 满赠
                    $map['sacfr.active_code'] = $v['active_code'];
                    $map['sacfr.status'] = "ENABLE";
                    $map['sac.status'] = "ENABLE";
                    $fields = "sacfr.active_code,sac.*";    

                    $coupon_res = D('SpcActiveCouponFullRelation')->alias('sacfr')->field($fields)
                                                                  ->join("{$this->tablePrefix}spc_active_coupon AS sac ON sac.bat_code = sacfr.bat_code","LEFT")
                                                                  ->where($map)
                                                                  ->select();

                    foreach ($coupon_res as $key => $value) {
                        if($value['last_nums'] < $value['single_num']){         # 该活动优惠券批次至少有一个批次不足
                            unset($coupon_res[$key]);       
                        }

                        if($value['time_type'] === SPC_COUPON_TIME_TYPE_TIME_SPAN && $value['end_time'] < NOW_TIME){  # 该活动优惠券批次至少有一个过期
                            unset($coupon_res[$key]);
                        }

                    }
                    if(empty($coupon_res)){
                        unset($active_res[$k]);
                    }

                    break;
                    
                default:
                    return $this->res(NULL,7084);
                    break;
            }

        }

        return $this->res($active_res);
        
    }


    /**
     * 下订单获取最合适的优惠券
     * Base.SpcModule.Coupon.Center.getSuitable
     * @author Todor
     */

    public function getSuitable($params){

        $this->_rule = array(
            array('flag', 'require', PARAMS_ERROR, MUST_CHECK),     # 活动标识
            array('amount', 'require', PARAMS_ERROR, ISSET_CHECK),  # 订单金额
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 查看对应活动标识有没有活动
        $apiPath = "Base.SpcModule.Coupon.Active.getRecent";
        $active_res = $this->invoke($apiPath,$params);
        if($active_res['status'] !== 0){
            return $this->endInvoke(NULL,$active_res['status']); # 相同条件的优惠活动存在多个
        }else{
            if(!is_array($active_res['response'])){
                return $this->res(true);                         # 不存在活动
            } 
        }
        $active_res = $active_res['response'][0];

        // 获取符合条件的所有策略
        $map['active_code'] = $active_res['active_code'];
        $map['status']  = "ENABLE";
        !empty($params['amount']) && $map['full_money']  = array('elt',$params['amount']);
        $order = !empty($params['amount']) ? 'full_money desc' : 'full_money asc';
        $full_back_res = D('SpcActiveFullBack')->where($map)->order($order)->select();

        // 通过策略查找优惠券批次
        foreach ($full_back_res as $k => $v) {
            
            $where['sacfr.active_code'] = $v['active_code'];
            $where['sacfr.status'] = "ENABLE";
            $where['sac.status'] = "ENABLE";
            $where['sacfr.full_back_id'] = $v['id'];
            $fields = "sacfr.active_code,sac.*";    

            $coupon_res = D('SpcActiveCouponFullRelation')->alias('sacfr')->field($fields)
                                                      ->join("{$this->tablePrefix}spc_active_coupon AS sac ON sac.bat_code = sacfr.bat_code","LEFT")
                                                      ->where($where)
                                                      ->select();
            // 查看优惠券条件
            if(empty($coupon_res)){
                unset($full_back_res[$k]);
                continue;
            }else{
                $amount = 0;
                foreach ($coupon_res as $key => $value) {
                    if($value['last_nums'] < $value['single_num']){         # 该活动优惠券批次至少有一个批次不足
                        unset($full_back_res[$k]);
                        continue 2;
                    }

                    if($value['time_type'] === SPC_COUPON_TIME_TYPE_TIME_SPAN && $value['end_time'] < NOW_TIME){  # 该活动优惠券批次至少有一个过期
                        unset($full_back_res[$k]);
                        continue 2;
                    }

                    $amount += $value['price'];

                }

                $full_back_res[$k]['amount'] = $amount;
                $full_back_res[$k]['coupon_res'] = $coupon_res;
            }
        }

        if(empty($params['amount'])){
            return $this->res($full_back_res);          # 如果为空则返回全部
        }else{
            $full_back_res = array_slice($full_back_res, 0,1,false)[0];
            return $this->res($full_back_res);
        }
        

    }




}