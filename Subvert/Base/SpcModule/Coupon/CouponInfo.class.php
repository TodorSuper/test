<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 优惠券相关
 */

namespace Base\SpcModule\Coupon;

use System\Base;

class CouponInfo extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 获取单个促销活动的促销券列表
     * Base.SpcModule.Coupon.CouponInfo.lists
     * @return integer   成功时返回  自增id
     */
    public function lists($params){
        $this->_rule = array(
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      # 分页数
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),      # 页码
            array('active_code', 'require', PARAMS_ERROR, MUST_CHECK),      #促销活动编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //查看该活动的条件
        $condition = D('SpcActive')->field('condition_flag')->where(['active_code'=>$params['active_code']])->find();
        $condition = $condition['condition_flag'];
        $page             = $params['page'];
        $page_number      = $params['page_number'];
        $active_code        = $params['active_code'];
        switch($condition){
            case 'REGISTER':
                $order            = "sac.create_time desc";
                $where = [];
                $where['sacr.active_code'] = $active_code;
                $where['sac.status'] = ['neq','DELETE'];
                $fields = 'sac.bat_code,sac.type_flag,sac.name,sac.effect_day,sac.price,sac.limit_money,sac.days,sac.start_time,sac.end_time,sac.time_type,sac.nums,sac.last_nums,sac.nums-sac.last_nums as pick_nums,sac.create_time,sac.single_num,sac.status';
                $data['sql_flag']    = 'coupon_lists';
                break;
            case 'FULL_BACK':
                $order            = "sac.create_time desc";
                $where = [];
                $where['sacfr.active_code'] = $active_code;
                $where['sac.status'] = ['neq','DELETE'];
                $fields = 'sac.bat_code,sac.type_flag,sac.name,safb.full_money,sac.effect_day,sac.price,sac.limit_money,sac.days,sac.start_time,sac.end_time,sac.time_type,sac.nums,sac.last_nums,sac.nums-sac.last_nums as pick_nums,sac.create_time,sac.single_num,sac.status';
                $data['sql_flag']    = 'coupon_lists_full_back';
                break;
            default:
        }
        $data['where']      = $where;
        $data['page']        = $page;
        $data['page_number'] = $page_number ? $page_number : 20;
        $data['fields']      = $fields;
        $data['order']       = $order;
        $data['center_flag'] = SQL_SPC;
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $res = $this->invoke($apiPath,$data);
        if ($res['status'] != 0) {
            return $this->res(NULL,7075);
        }
        return $this->res($res['response']);
    }
    /**
     * 获取单个促销券信息
     * Base.SpcModule.Coupon.CouponInfo.get
     * @return integer   成功时返回  自增id
     */
    public function get($data){
        $where = [];
        $where['bat_code'] = $data['bat_code'];
        $where['status'] = array('neq','DELETE');
        $field = 'id,bat_code,type_flag,name,price,limit_money,days,start_time,effect_day,end_time,time_type,nums,create_time,status';
        $info = D('SpcActiveCoupon')->field($field)->where($where)->find();
        //查询该优惠券对应哪个活动策略
        $full_info = D('SpcActiveCouponFullRelation')->field('full_back_id,active_code')->where(['bat_code'=>$data['bat_code']])->find();
        if($full_info){
            $info['full_back_id'] = $full_info['full_back_id'];
        }
        if(!$info){
            return $this->res('',7081);
        }
        return $this->res($info);
    }
}