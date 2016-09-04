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

class ActiveInfo extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Bll.Cms.Spc.ActiveInfo.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        $apiPath='Base.SpcModule.Coupon.ActiveInfo.lists';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        //获取每个活动的优惠券数量
        $active_codes = array_column($call['response']['lists'],'active_code');
        if($active_codes){
            $apiPath = 'Base.SpcModule.Coupon.ActiveInfo.getCoupon';
            $coupon = $this->invoke($apiPath,$active_codes);
        }
        if($call['response']['lists']){
            foreach($call['response']['lists'] as $key=>$val){
                $call['response']['lists'][$key]['condition'] = M('Base.SpcModule.Center.Status.getConditionToStr')->getConditionToStr($call['response']['lists'][$key]['condition_flag']);
                $call['response']['lists'][$key]['rule'] =  M('Base.SpcModule.Center.Status.getRuleToStr')->getRuleToStr($call['response']['lists'][$key]['rule_flag']);
                $call['response']['lists'][$key]['status_message'] = M('Base.SpcModule.Center.Status.getActiveStatus')->getActiveStatus($call['response']['lists'][$key]['status'],$call['response']['lists'][$key]['start_time'],$call['response']['lists'][$key]['end_time']);
                if($coupon['response'][$val['active_code']]['num']){
                    $call['response']['lists'][$key]['coupon_num'] = $coupon['response'][$val['active_code']]['num'];
                }else{
                    $call['response']['lists'][$key]['coupon_num'] = 0;
                }
            }
        }
        //获取所有活动列表
        $apiPath = 'Base.SpcModule.Coupon.ActiveInfo.activeLists';
        $active = $this->invoke($apiPath);
        $call['response']['active_list'] = $active['response'];
        return $this->endInvoke($call['response']);
    }
    /**
     * Bll.Cms.Spc.ActiveInfo.get
     * @param type $params
     * @return type
     */
    public function get($data){
        $apiPath='Base.SpcModule.Coupon.ActiveInfo.get';
        $call=$this->invoke($apiPath,$data);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        if($call['response']){
            $call['response']['condition'] = M('Base.SpcModule.Center.Status.getConditionToStr')->getConditionToStr($call['response']['condition_flag']);
            $call['response']['rule'] =  M('Base.SpcModule.Center.Status.getRuleToStr')->getRuleToStr($call['response']['rule_flag']);
        }
        return $this->endInvoke($call['response']);
    }
    /**导出活动列表
     * yindongyang
     * Bll.Cms.Spc.ActiveInfo.export
     * @param [type] $params [description]
     */
    public function export($params){
        $apiPath = 'Base.SpcModule.Coupon.ActiveInfo.export';
        $res = $this->invoke($apiPath,$params);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        return $this->endInvoke($res['response']);
    }
}