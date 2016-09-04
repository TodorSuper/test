<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@yunputong.com >
 * +---------------------------------------------------------------------
 * | cms后台优惠券相关
 */

namespace Bll\Cms\Spc;

use System\Base;

class CouponInfo extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Bll.Cms.Spc.CouponInfo.lists
     * @param type $params
     * @return type
     */
    public function lists($data){
        $hide = $data['hide'];
        //得到该促销活动的信息
        $apiPath = 'Base.SpcModule.Coupon.ActiveInfo.get';
        $info = $this->invoke($apiPath,['active_code'=>$data['active_code']]);
        if($info['response']){
            $info['response']['condition'] = M('Base.SpcModule.Center.Status.getConditionToStr')->getConditionToStr($info['response']['condition_flag']);
            $info['response']['rule'] =  M('Base.SpcModule.Center.Status.getRuleToStr')->getRuleToStr($info['response']['rule_flag']);
        }
        $apiPath='Base.SpcModule.Coupon.CouponInfo.lists';
        $call=$this->invoke($apiPath,$data);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }

        if($call['response']['lists']){
            $bat_codes = array_column($call['response']['lists'],'bat_code');
            $data = [
                'bat_codes'=>$bat_codes,
                'hide'=>$hide,
            ];
            //统计已领取得优惠券过期的数量
            $apiPath = 'Base.UserModule.Coupon.Coupon.overNum';
            $res = $this->invoke($apiPath,$data);
            if($res['status']!==0){
                return $this->endInvoke('',$res['status']);
            }
            //统计已领取的数量
            $apiPath = 'Base.UserModule.Coupon.Coupon.pickNum';
            $pick = $this->invoke($apiPath,$data);
            if($pick['status']!==0){
                return $this->endInvoke('',$pick['status']);
            }
            //统计已领取得优惠券已使用的数量
            $apiPath = 'Base.UserModule.Coupon.Coupon.useNum';
            $use = $this->invoke($apiPath,$data);
            if($use['status']!==0){
                return $this->endInvoke('',$use['status']);
            }
            //统计已经占用的优惠券数量
            $apiPath = 'Base.UserModule.Coupon.Coupon.occupyNum';
            $occupy = $this->invoke($apiPath,$data);
            if($occupy['status']!==0){
                return $this->endInvoke('',$occupy['status']);
            }
            foreach($call['response']['lists'] as $key=>$val){
                if($res['response'][$val['bat_code']]['num']){
                    $call['response']['lists'][$key]['over_num'] = $res['response'][$val['bat_code']]['num'];
                }else{
                    $call['response']['lists'][$key]['over_num'] = 0;
                }
                if($pick['response'][$val['bat_code']]['num']){
                    $call['response']['lists'][$key]['pick_num'] = $pick['response'][$val['bat_code']]['num'];
                }else{
                    $call['response']['lists'][$key]['pick_num'] = 0;
                }
                if($use['response'][$val['bat_code']]['num']){
                    $call['response']['lists'][$key]['use_num'] = $use['response'][$val['bat_code']]['num'];
                }else{
                    $call['response']['lists'][$key]['use_num'] = 0;
                }
                if($occupy['response'][$val['bat_code']]['num']){
                    $call['response']['lists'][$key]['occupy_num'] = $occupy['response'][$val['bat_code']]['num'];
                }else{
                    $call['response']['lists'][$key]['occupy_num'] = 0;
                }
            }
        }
//        var_dump($call['response']['lists']);exit;
        $call['response']['lists'] = array_map(function($v){
            if($v['type_flag'] == 'CASH'){
                $v['type'] = '现金券';
            }
            return $v;
         },$call['response']['lists']);
       $call['response']['active_info'] = $info['response'];
        return $this->endInvoke($call['response']);
    }

    /**
     * Bll.Cms.Spc.CouponInfo.get
     * @param type $params
     * @return type
     */
    public function get($params){
        $apiPath='Base.SpcModule.Coupon.CouponInfo.get';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        return $this->endInvoke($call['response']);
    }
}