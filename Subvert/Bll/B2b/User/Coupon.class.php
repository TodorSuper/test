<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: nilei <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b地址模块
 */

namespace Bll\B2b\User;
use System\Base;

class Coupon extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * 优惠券列表 
     * Bll.B2b.User.Coupon.lists
     * @access public
     * @author Todor
     */

    public function lists($params){

        $temp = array();
        foreach ($params['coupon_status'] as $k => $v) {
            $data = array(
                'uc_code'=>$params['uc_code'],
                'coupon_status'=>$v,
                'time_limit'=>'YES',
                'type'=>$params['type'],
                );
            $apiPath = "Base.UserModule.Coupon.Coupon.lists";
            $res = $this->invoke($apiPath,$data);
            if($res['status'] !== 0){
                return $this->endInvoke(NULL,$res['status']);
            }
            $temp = array_merge($temp,$res['response']);
        }

        //获取数量
        $apiPath = "Base.UserModule.Coupon.Coupon.getNums";
        $nums = $this->invoke($apiPath,$params);
        if($nums['status'] !== 0){
            return $this->endInvoke(NULL,$nums['status']);
        }
        $res = array(
            'lists'=>$temp,
            'nums'=>$nums['response'],
            );
        return $this->endInvoke($res);
    }


    /**
     * 可用优惠券列表 
     * Bll.B2b.User.Coupon.useLists
     * @access public
     * @author Todor
     */

    public function useLists($params){
        //获取全部可用优惠券
        $temp = array();
        foreach ($params['coupon_status'] as $k => $v) {
            $data = array(
                'uc_code'=>$params['uc_code'],
                'coupon_status'=>$v,
                'time_limit'=>'YES',
                'type'=>$params['type'],
                );
            $apiPath = "Base.UserModule.Coupon.Coupon.lists";
            $res = $this->invoke($apiPath,$data);
            if($res['status'] !== 0){
                return $this->endInvoke(NULL,$res['status']);
            }
            $temp = array_merge($temp,$res['response']);
        }

        //获取可用优惠券
        $data = array(
            'uc_code'=>$params['uc_code'],
            'total_amount'=>$params['amount'],
            );
        $apiPath = "Base.UserModule.Coupon.Coupon.get";
        $use_res = $this->invoke($apiPath,$data);
        if($use_res['status'] !== 0){
            return $this->endInvoke(NULL,$use_res['status']);
        }
        $use_res = $use_res['response'];

        if(empty($temp)){                        # 什么也没有
            return $this->endInvoke(TRUE);
        }elseif(empty($use_res)){                # 没有可用的
            foreach ($temp as $k => &$v) {
                $v['status'] = "INVALID";
            }
            return $this->endInvoke($temp);
        }else{                                  # 有可用 有不可用

            // 颠倒数据
            $temp_code = array_column($temp,'coupon_code');
            $use_code  = array_column($use_res,'coupon_code');
            $invalid = array_diff($temp_code, $use_code);

            $arr = array();
            foreach ($temp as $k => $v) {
                if(in_array($v['coupon_code'], $invalid)){
                    $arr[] = $v;
                }
            }

            $arr = array_map(function($v){
                if($v['status'] == "ENABLE"){
                    $v['status'] = "CLOSE";   # 不满足条件的
                }
                return $v;
            },$arr);
            
            $use_res = array_merge($use_res,$arr);
            $res = array(
                'lists'=>$use_res,
            );
            return $this->endInvoke($res);
        }
        
    }


    /**
     * 优惠券历史记录展示 
     * Bll.B2b.User.Coupon.haveShow
     * @access public
     * @author Todor
     */

    public function haveShow($params){
        try {
            D()->startTrans();
            $apiPath = "Base.UserModule.Coupon.CouponLog.update";
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4065);
        }
    }
    


}

?>
