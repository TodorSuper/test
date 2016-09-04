<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b订单列状态更新
 */

namespace Base\OrderModule\B2b;

use System\Base;

class Statistics extends Base {

    private $_rule  =   null;

    public function __construct() {
        parent::__construct();
    }


    /**
     * @api  Boss版首页统计接口
     * @apiVersion 1.0.1
     * @apiName Base.OrderModule.B2b.Statistics.all
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiParam {String} [sc_code] 我是商铺编码,我规定参数必填且类型为String类型
     */

    public function all($params){

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),       //店铺编码
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),    //开始时间
            array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),      //结束时间
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code     =  $params['sc_code'];
        $start_time  =  $params['start_time'];
        $end_time    =  $params['end_time'];

        $where['sc_code'] = $params['sc_code'];

        $where['_complex'] = $this->orderWhere('',$start_time,$end_time);
        $total_cope_amount = D('OcB2bOrder')->field('sum(cope_amount) as total_cope_amount,count(*) as total_order_num')->where($where)->find();

        if($total_cope_amount === FALSE){
            return $this->res(NULL,6046);
        }
        $total_cope_amount['total_cope_amount'] = empty($total_cope_amount['total_cope_amount']) ? 0 : $total_cope_amount['total_cope_amount'];
        return $this->res($total_cope_amount);

    }



    /**
     * @api  Boss版月度统计
     * @apiVersion 1.0.0
     * @apiName Base.OrderModule.B2b.Statistics.oldmonth
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function oldmonth($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),     # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),     # 用户编码
            array('year', 'require', PARAMS_ERROR, ISSET_CHECK),        # 年
            array('month', 'require', PARAMS_ERROR, ISSET_CHECK),       # 月份
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 时间设置
        $start_time = mktime(0,0,0,$params['month'],1,$params['year']);
        $end_time   = mktime(23,59,59,$params['month']+1,0,$params['year']);


        $where['sc_code'] = $params['sc_code'];
        $where['uc_code'] = $params['uc_code'];

        $where['_complex'] = $this->orderWhere('',$start_time,$end_time);
        $field = "sum(cope_amount) as amount,count(*) as orders";

        $amount = D('OcB2bOrder')->field($field)->where($where)->select();

        if($amount === FALSE){
            return $this->res(NULL,6047);
        }

        $amount = $amount[0];
        $res = array(
            'amount'=>empty($amount['amount']) ? 0 : $amount['amount'],
            'orders'=>(int)$amount['orders'],
        );

        return $this->res($res);

    }


    /*
   * path  : Base.OrderModule.B2b.Statistics.month
   * fixed : 2015-12-1 added  oldmonth change Form
   * author: heweijun@liangrenwang.com
   * desc  : 获取相应年份的每个月的销售额与订单数
   */
    public function month($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 店铺编码
            array('year', 'require', PARAMS_ERROR, MUST_CHECK),        # 年
            array('sort', 'require', PARAMS_ERROR, ISSET_CHECK),       # 排序
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //制取排序的方式  MONTH_DESC/MONTH_ASC
        $sort = !empty($params['sort']) ? explode('_', $params['sort'])[1] : 'DESC';
        $year = !empty($params['year']) ? $params['year'] : data("Y",time());
        $sc_code = !empty($params['sc_code'])? $params['sc_code'] : '';

        $sql = "SELECT sum(real_amount) as amount,count(*) as orders,CASE  WHEN  pay_type = 'ONLINE'  THEN from_unixtime(create_time, '%m')  WHEN ship_method = 'DELIVERY' AND ( `pay_type` IN ('COD','TERM'))  THEN from_unixtime(ship_time,'%m') WHEN ship_method='PICKUP' AND ( `pay_type` IN ('COD','TERM')) THEN from_unixtime(takeover_time,'%m') END AS month FROM `16860_oc_b2b_order`
                WHERE ( `sc_code` = '{$sc_code}' ) AND (  (  ( `pay_type` = 'ONLINE' )  AND ( `pay_status` = 'PAY' ) AND (  (from_unixtime(create_time,'%Y') = {$year}) ) )
                OR (  ( `pay_type` IN ('COD','TERM') ) AND ( `ship_method` = 'DELIVERY' ) AND ( `ship_status` IN ('SHIPPED','TAKEOVER') ) AND (  (from_unixtime(ship_time,'%Y') = {$year} ) ) ) 
                OR (  ( `pay_type` IN ('COD','TERM') ) AND ( `ship_method` = 'PICKUP' )  AND (  (from_unixtime(takeover_time,'%Y') = {$year} ) ) ) )
                GROUP BY month ORDER BY month {$sort}";

        $ocB2bOrderD =  D('OcB2bOrder');
        $orders =  $ocB2bOrderD->query($sql);
        foreach($orders as $key=>$val){
            $temp = array();
            $temp = app_change_price($val['amount']);
            //去掉unit 为空的
            $temp = array_filter(array(
                'year'  => $year,
                'month' => (int)$val['month'],
                'amount'=> empty($temp['amount']) ? 0 : $temp['amount'],
                'unit'  => empty($temp['unit']) ? '' : $temp['unit'],
                'orders'=>(int)$val['orders'],
            ));
            $res['order_data'][] = $temp;

        }

        $where['sc_code']  = $params['sc_code'];
        $where['_complex'] = $this->orderWhere();
        $field = 'create_time as start_data';
        $start_data = $ocB2bOrderD->field($field)->where($where)->find();
        $start_data = $start_data['start_data'];
        $res['start_data']= $start_data;

        return $this->res($res);
    }


    /**
     * @api  Boss版热销商品统计
     * @apiVersion 1.0.0
     * @apiName Base.OrderModule.B2b.Statistics.hot
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function hot($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 店铺编码
            array('year', 'require', PARAMS_ERROR, MUST_CHECK),        # 年
            array('month', 'require', PARAMS_ERROR, MUST_CHECK),       # 月
            array('sort', 'require', PARAMS_ERROR, HAVEING_CHECK),     # 销量
            array('number', 'require', PARAMS_ERROR, HAVEING_CHECK),   # 取多少

        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $year = $params['year'];
        $month = $params['month'];
        $number = empty($params['number']) ? 10 : $params['number'];

        // 时间设置
        $start_time = mktime(0,0,0,$params['month'],1,$params['year']);
        $end_time   = mktime(23,59,59,$params['month']+1,0,$params['year']);

        // 统计销售量 商品名 规格
        $field = "SUM(obog.goods_number) as num,obog.goods_name,obog.spec,obog.sic_code,SUM(obog.goods_number*obog.goods_price) as amount";
        $order =  (empty($params['sort']) || $params['sort'] == "ORDERS_DESC") ? 'num desc,amount desc' : 'num asc,amount asc';
        $map['obog.sc_code'] = $params['sc_code'];
        $map['_complex'] = $this->orderWhere('obo.',$start_time,$end_time);

        $res = D('OcB2bOrderGoods')->alias('obog')->field($field)
            ->join("{$this->tablePrefix}oc_b2b_order obo ON obog.b2b_code = obo.b2b_code", 'LEFT')
            ->where($map)
            ->group('obog.sic_code')
            ->limit($number)
            ->order($order)
            ->select();

        $items['item_data'] = $res;
        return $this->res($items);
    }



    /**
     * @api  Boss版客户统计
     * @apiVersion 1.0.0
     * @apiName Base.OrderModule.B2b.Statistics.customer
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function customer($params){

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 店铺编码  
            array('year', 'require', PARAMS_ERROR, MUST_CHECK),        # 年
            array('month', 'require', PARAMS_ERROR, MUST_CHECK),       # 月
            array('sort', 'require', PARAMS_ERROR, ISSET_CHECK),       # 销量
            array('pageNumber', 'require', PARAMS_ERROR, ISSET_CHECK),   # 当前页
            array('pageSize', 'require', PARAMS_ERROR, ISSET_CHECK),     # 分页数
            array('search_name', 'require', PARAMS_ERROR, ISSET_CHECK),  # 搜索
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $fields = "um.commercial_name,um.name as relate_name,sum(obo.cope_amount) as amount,uc.uc_code,count(*) as orders";
        $order  = (empty($params['sort']) || $params['sort'] == "AMOUNT_DESC") ? 'amount desc' : 'amount asc' ;
        $group  = "uc.uc_code";

        // 时间限制
        $start_time = mktime(0,0,0,$params['month'],1,$params['year']);
        $end_time   = mktime(0,0,0,$params['month']+1,1,$params['year'])-1;
        $where['uc.sc_code'] = $params['sc_code'];
        

        //搜索条件
        if(!empty($params['search_name'])){
            $params['search_name'] = trim($params['search_name']);
            $where['um.commercial_name|um.name|um.mobile'] = array('like',"%".$params['search_name']."%");
        }

        $where['_complex'] = $this->orderWhere('obo.',$start_time,$end_time);
        $params['page']        = $params['pageNumber'];
        $params['page_number'] = $params['pageSize'];
        $params['fields']      = $fields;
        $params['where']       = $where;
        $params['order']       = $order;
        $params['group']       = $group;
        $params['center_flag'] = SQL_OC;
        $params['sql_flag']    = 'order_customer_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $customers = $this->invoke($apiPath,$params);

        if ($customers['status'] != 0) {
            return $this->res(NULL,6049);
        }

        // 数量限制
        foreach ($customers['response']['lists'] as $k => $v) {
            $customers['response']['lists'][$k]['amount'] = $v['amount'] < 100000000 ? number_format($v['amount'],2) : "9999,9999.99";
            $customers['response']['lists'][$k]['orders'] = $v['orders'] < 100000000 ? (empty($v['orders']) ? 0 : $v['orders']) : "99999999";
        }

        //返回需要匹配高亮字段
        $customers['response']['need_check'] = array('commercial_name','relate_name');

        return $this->res($customers['response']);

    }

    /**
     * @api  Where 条件
     * @apiVersion 1.0.0
     * @apiName Base.OrderModule.B2b.Statistics.orderWhere
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest Off
     */

    private function orderWhere($head,$start_time,$end_time){
        $status_where  = array();
        if(empty($start_time)){
            $status_where[] = array('pay_type'=>PAY_TYPE_ONLINE,'pay_status'=>OC_ORDER_PAY_STATUS_PAY);
            // 货到付款与账期
            $status_where[] = array('ship_method'=>SHIP_METHOD_DELIVERY,'pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),'ship_status'=>array('in',array(OC_ORDER_SHIP_STATUS_SHIPPED,OC_ORDER_SHIP_STATUS_TAKEOVER)));
            // 买家自提 货到付款与账期
            $status_where[] = array('ship_method'=>SHIP_METHOD_PICKUP,'pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)));
            $status_where['_logic'] = 'or';
        }else{
            //在线支付(微信，支付宝，预付款)已支付
            $status_where[] = array($head.'pay_type'=>PAY_TYPE_ONLINE,$head.'pay_status'=>OC_ORDER_PAY_STATUS_PAY,$head.'create_time'=>array('between',array($start_time,$end_time)));
            // 货到付款与账期
            $status_where[] = array($head.'ship_method'=>SHIP_METHOD_DELIVERY,$head.'pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),$head.'ship_status'=>array('in',array(OC_ORDER_SHIP_STATUS_SHIPPED,OC_ORDER_SHIP_STATUS_TAKEOVER)),$head.'ship_time'=>array('between',array($start_time,$end_time)));
            // 买家自提 货到付款与账期
            $status_where[] = array($head.'ship_method'=>SHIP_METHOD_PICKUP,$head.'pay_type'=>array('in',array(PAY_TYPE_COD,PAY_TYPE_TERM)),$head.'takeover_time'=>array('between',array($start_time,$end_time)));
            $status_where['_logic'] = 'or';
        }
        return $status_where;
    }

    /**
     * @Desc    : Boss版 获取本天,本周 ,本月的订单数与交易金额
     * @ApiPath : Base.OrderModule.B2b.Statistics.day
     * @Date    : 2015-12-1
     * @Author  : heweijun@liangrenwang.com
     */
    public function day($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 店铺编码
            array('year',  'require', PARAMS_ERROR, MUST_CHECK),       # 年
            array('month', 'require', PARAMS_ERROR, MUST_CHECK),    # 当前页
            array('day',  'require', PARAMS_ERROR, MUST_CHECK),      # 当天
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code       =  $params['sc_code'];
        $year          =  $params['year'];
        $month         =  $params['month'];
        $day           =  $params['day'];

        $res = array();
        $timeGroupsArr = getTimeGroups(array('year'=>$year, 'month'=> $month, 'day'=>$day));
        $ocB2bOrderD = D('OcB2bOrder');
        foreach($timeGroupsArr as $key=>$val){
            $start_time    = $val['start_time' ];
            $end_time      = $val['end_time'];

            $where['sc_code']  = $params['sc_code'];
            $where['_complex'] = $this->orderWhere('',$start_time,$end_time);
            $field = "sum(cope_amount) as amount, count(*) as orders";
            $dayData = $ocB2bOrderD->field($field)->where($where)->select();

            if($dayData === FALSE){
                return $this->res(NULL,6047);
            }

            $temp = array();
            $temp = app_change_price($dayData[0]['amount']);


            $res["$key"] = array(
                'orders'  => empty($dayData[0]['orders']) ? 0 : $dayData[0]['orders'],
                'amount'  => empty($temp['amount']) ? 0 : $temp['amount'],
                'unit'    => empty($temp['unit']) ? '' : $temp['unit'],
            );

        }
        $where = array();
        $where['sc_code']  = $params['sc_code'];
        $where['_complex'] = $this->orderWhere();
        $field = 'create_time as start_data';
        $start_data = $ocB2bOrderD->field($field)->where($where)->find();
        $start_data = $start_data['start_data'];
        $res['start_data']= $start_data;

        return $this->res($res);
    }


    /**
     * @api  Boss版客户详情商品列表
     * @apiVersion 1.4.0
     * @apiName Base.OrderModule.B2b.Statistics.items
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2016-1-20
     * @apiSampleRequest On
     */

    public function items($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),     # 用户编码 
            array('year', 'require', PARAMS_ERROR, MUST_CHECK),        # 年
            array('month', 'require', PARAMS_ERROR, MUST_CHECK),       # 月
            array('sort', 'require', PARAMS_ERROR, HAVEING_CHECK),     # 销量
            array('pageNumber', 'require', PARAMS_ERROR, ISSET_CHECK),   # 当前页
            array('pageSize', 'require', PARAMS_ERROR, ISSET_CHECK),     # 分页数
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 时间设置
        $start_time = mktime(0,0,0,$params['month'],1,$params['year']);
        $end_time   = mktime(23,59,59,$params['month']+1,0,$params['year']);

        // 统计销售量 商品名 规格
        $fields = "SUM(obog.goods_number*obog.goods_price) as amount,obog.goods_name,obog.spec,obog.sic_code";
        $order =  (empty($params['sort']) || $params['sort'] == "ORDERS_DESC") ? 'amount desc ,goods_name desc' : 'amount asc,goods_name asc';
        $map['obo.sc_code'] = $params['sc_code'];
        $map['obo.uc_code'] = $params['uc_code'];
        $map['_complex'] = $this->orderWhere('obo.',$start_time,$end_time);
        
        $params['page']        = $params['pageNumber'];
        $params['page_number'] = 11;
        $params['fields']      = $fields;
        $params['where']       = $map;
        $params['order']       = $order;
        $params['group']       = 'obog.sic_code';
        $params['center_flag'] = SQL_OC;
        $params['sql_flag']    = 'order_item_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $items = $this->invoke($apiPath,$params);

        if ($items['status'] != 0) {
            return $this->res(NULL,6068);
        }

        // // 获取订单总钱数
        $amount = D('OcB2bOrder')->alias('obo')->field("SUM(cope_amount) AS amount")->where($map)->find();
        $items['response']['amount'] = $amount['amount'];


        return $this->res($items['response']);
    }

}