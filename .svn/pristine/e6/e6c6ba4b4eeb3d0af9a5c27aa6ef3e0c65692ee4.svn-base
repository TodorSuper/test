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

class ActiveInfo extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 优惠促销活动列表
     * Base.SpcModule.Coupon.ActiveInfo.lists
     * @return integer   成功时返回  自增id
     */
    public function lists($params){
        $this->_rule = array(
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      # 分页数
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),      # 页码
            array('active_name', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动名称
            array('draft', 'require', PARAMS_ERROR, ISSET_CHECK),      # 未上线
            array('preheat', 'require', PARAMS_ERROR, ISSET_CHECK),      # 预热中
            array('publish', 'require', PARAMS_ERROR, ISSET_CHECK),      # 已上线
            array('end', 'require', PARAMS_ERROR, ISSET_CHECK),      # 已下线
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动上线时间查询开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动上线时间查询结束时间
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $order            = "create_time desc";
        $page             = $params['page'];
        $page_number      = $params['page_number'];
        $draft            = $params['draft'];
        $publish          = $params['publish'];
        $preheat          = $params['preheat'];
        $end              = $params['end'];
        $active_name      = trim($params['active_name']);
        $start_time       = $params['start_time'];
        $end_time         = $params['end_time'];
        $condition        = $params['condition'];
        $map = array();
        $draft == SPC_STATUS_DRAFT ?  $map[] = array('status'=>SPC_STATUS_DRAFT): '';
        $preheat == SPC_STATUS_PREHEAT ?  $map[] = array('status'=>SPC_STATUS_PUBLISH,'start_time'=>array('gt',NOW_TIME)): '';
        $publish == SPC_STATUS_PUBLISH ?  $map[] = array('status'=>SPC_STATUS_PUBLISH,'start_time'=>array('lt',NOW_TIME),'end_time'=>array('gt',NOW_TIME)): '';
        if($end == SPC_STATUS_END){
            $arr[] = array('status'=>SPC_STATUS_PUBLISH,'end_time'=>array('lt',NOW_TIME));
            $arr[] = array('status'=>SPC_STATUS_END);
            $arr['_logic'] = 'or';
            $map[] = array('_complex'=>$arr);
        }
        if($map){
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $active_name ? $where['active_name'] = array('eq',"{$active_name}") : '';
        $condition ? $where['condition_flag'] = ['eq',$condition] : '';
        $where['online_time'] = array('between',array($start_time,$end_time));
        $fields = 'id,active_code,active_name,condition_flag,rule_flag,start_time,end_time,create_time,online_time,offline_time,status';
        $data['where']      = $where;
        $data['page']        = $page;
        $data['page_number'] = $page_number ? $page_number : 20;
        $data['fields']      = $fields;
        $data['order']       = $order;
        $data['center_flag'] = SQL_SPC;
        $data['sql_flag']    = 'active_lists';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $res = $this->invoke($apiPath,$data);
        if ($res['status'] != 0) {
            return $this->res(NULL,7075);
        }
        return $this->res($res['response']);
    }

    /**
     *Base.SpcModule.Coupon.ActiveInfo.export
     * @param [type] $params [description]
     */
    public function export($params){
        $this->_rule = array(
            array('active_name', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动名称
            array('draft', 'require', PARAMS_ERROR, ISSET_CHECK),      # 未上线
            array('preheat', 'require', PARAMS_ERROR, ISSET_CHECK),      # 预热中
            array('publish', 'require', PARAMS_ERROR, ISSET_CHECK),      # 已上线
            array('end', 'require', PARAMS_ERROR, ISSET_CHECK),      # 已下线
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动上线时间查询开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动上线时间查询结束时间
            array('hide', 'require', PARAMS_ERROR, ISSET_CHECK),      # 是否隐藏测试数据
            array('condition', 'require', PARAMS_ERROR, ISSET_CHECK),      # 活动条件
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取平台标识  业务参数
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $draft            = $params['draft'];
        $publish          = $params['publish'];
        $preheat          = $params['preheat'];
        $end              = $params['end'];
        $hide            =  $params['hide'];
        $condition        = $params['condition'];
        $active_name      = trim($params['active_name']);
        $params['start_time'] ? $start_time = strtotime($params['start_time']) : $start_time=0;
        $params['end_time'] ? $end_time = strtotime($params['end_time']) : $end_time = NOW_TIME;
        //默认参数
        $default_title      =  array('活动创建时间','活动编码','活动名称','活动上线时间','活动起时间','活动止时间','活动条件','活动策略','活动规则','活动状态','平台买家编码','平台客户姓名','平台客户电话','参与活动时间(领券时间)','优惠券编码','优惠券名称','面额(元)','优惠券占用时间(生成订单的时间)','优惠券使用时间(订单支付的时间)','优惠券使用状态','订单编号','业务员姓名');
        $default_fields     =  'sa.create_time as active_create_time,sa.active_code,sa.active_name,sa.online_time,sa.start_time,sa.end_time,sa.condition_flag,umc.full_back_strategy,sa.rule_flag,sa.status as active_status,umc.uc_code,um.name,um.mobile,umc.create_time,umc.coupon_code,sac.name as coupon_name,umc.price,umc.occupy_time,umc.use_time,umc.status,umc.b2b_code,umc.salesman,umc.start_time as coupon_start_time,umc.end_time as coupon_end_time';
        $default_callback_api = 'Com.Callback.Export.SpcExport.active_export';

        $default_filename   =  'CMS活动导出明细';
        $default_sql_flag   =  'cms_active_info';
        $default_order      =  'umc.create_time desc';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ?  $default_callback_api: $callback_api;

        $where         =  array();
        if($hide == 1){
            $where['umc.invite_code'] = ['neq',C('TEXT_INVITE_COE')];
        }
        $map = array();
        $draft == SPC_STATUS_DRAFT ?  $map[] = array('sa.status'=>SPC_STATUS_DRAFT): '';
        $preheat == SPC_STATUS_PREHEAT ?  $map[] = array('sa.status'=>SPC_STATUS_PUBLISH,'sa.start_time'=>array('gt',NOW_TIME)): '';
        $publish == SPC_STATUS_PUBLISH ?  $map[] = array('sa.status'=>SPC_STATUS_PUBLISH,'sa.start_time'=>array('lt',NOW_TIME),'sa.end_time'=>array('gt',NOW_TIME)): '';
        if($end == SPC_STATUS_END){
            $arr[] = array('sa.status'=>SPC_STATUS_PUBLISH,'sa.end_time'=>array('lt',NOW_TIME));
            $arr[] = array('sa.status'=>SPC_STATUS_END);
            $arr['_logic'] = 'or';
            $map[] = array('_complex'=>$arr);
        }
        if($map){
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $condition ? $where['sa.condition_flag'] = ['eq',$condition] : '';
        !empty($active_name) ? $where['sa.active_name'] = array('eq',"{$active_name}") : '';
        $where['sa.online_time'] = array('between',array($start_time,$end_time));
        //组装调用导出api参数
        $data['where']        =  $where;
        $data['fields']       =  $default_fields;
        $data['title']        =  $title;
        $data['order']        =  empty($order) ? $default_order : $order;
        $data['center_flag']  =  SQL_SPC;//促销中心
        $data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $data['filename']     =  $filename;
        $data['callback_api'] = $callback_api;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }
    /**
     * 获取每个活动的优惠券数量
     * Base.SpcModule.Coupon.ActiveInfo.getCoupon
     * @return integer   成功时返回  自增id
     */
    public function getCoupon($active_codes){
        $where = array();
        $where['sacr.active_code'] = array('in',$active_codes);
        $where['sac.status'] = 'ENABLE';
        $result = D('SpcActiveCouponRelation')->alias('sacr')->field('count(*) as num,sacr.active_code')->join("{$this->tablePrefix}spc_active_coupon sac on sacr.bat_code = sac.bat_code","inner")->where($where)->group('sacr.active_code')->select();
        if($result){
            $result = changeArrayIndex($result,'active_code');
        }
        return $this->res($result);
    }
    /**
     * 获取单个促销活动的信息
     * Base.SpcModule.Coupon.ActiveInfo.get
     * @return integer   成功时返回  自增id
     */
    public function get($data){
        $where = [];
        $where['active_code'] = $data['active_code'];
        $field = 'id,active_code,active_name,active_banner,desc,is_store_show,condition_flag,rule_flag,start_time,end_time,create_time,online_time,offline_time,status';
        $info = D('SpcActive')->field($field)->where($where)->find();
        if($info['condition_flag'] == 'FULL_BACK'){
            //查出活动策略
            $policy = D('SpcActiveFullBack')->field('id,full_money')->where(['status'=>'ENABLE','active_code'=>$data['active_code']])->select();
            $info['policy'] = $policy;
        }
        if($info === false){
            return $this->res('',7076);
        }
        return $this->res($info);
    }
    /**
     * 获取单个促销活动的信息
     * Base.SpcModule.Coupon.ActiveInfo.activeLists
     * @return integer   成功时返回  自增id
     */
    public function activeLists(){
        $active = D('SpcActive')->field('active_name')->select();
        $active = array_column($active,'active_name');
        return $this->res($active);
    }
}