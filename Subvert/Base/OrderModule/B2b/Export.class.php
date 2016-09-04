<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单信息导出
 */

namespace Base\OrderModule\B2b;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {               
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * Base.OrderModule.B2b.Export.export
     * @param type $params
     * @return type
     */
    public function  export($params){

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK), //订单编码
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK), //订单下单开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK), //订单下单结束时间
            array('real_name', 'require', PARAMS_ERROR, ISSET_CHECK), //用户名
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK), //订单状态  组合状态
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式
            array('client_name', 'require', PARAMS_ERROR, ISSET_CHECK), //客户姓名
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('channel_id', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出订单文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
            array('template_call_api', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
            array('ori_where', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ori_fields', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('order_type','require',PARAMS_ERROR, ISSET_CHECK)
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        //获取平台标识  业务参数
        $uc_code        =  $params['uc_code']; 
        $sc_code        =  $params['sc_code']; 
        $b2b_code       =  $params['b2b_code']; 
        $start_time     =  strtotime($params['start_time']);
        $end_time       =  $params['end_time'] != '' ?strtotime($params['end_time'])+86399:'';
        $real_name      =  $params['real_name']; 
        $status         =  empty($params['status']) ? OC_ORDER_GROUP_STATUS_ALL : $params['status'];   //默认取全部订单状态
        $client_name    =  $params['client_name'];
        $salesman_id    =  $params['salesman_id'];
        $channel_id     =  $params['channel_id'];
        $pay_method     =  $params['pay_method'];
        $pay_type       =  $params['pay_type'];
        $order_type    =  $params['order_type'];
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $order          =  $params['order'];
        $template_call_api = $params['template_call_api'];
        $ori_where = $params['ori_where'];
        $ori_fields = $params['ori_fields'];
        $pay_start_time = $params['pay_start_time'];
        $pay_end_time   = $params['pay_end_time'];
//        $this->checkParams($sc_code, $uc_code);
        
        //默认参数
        $default_title      =  array('订单编号','客户名称','店铺名称','商品编码','商品名称','规格','包装','单价（元）','成交价(元)','商品数量','促销方式','促销编号','优惠详情','商品总数','平台优惠券抵扣(元)','买家实付(元)','实收总额（元）','配送方式','收货人','手机号码','收货地址','客户类型','业务员','渠道','下单时间','支付类型','支付方式','订单状态');  //默认导出列标题
//        $default_title      =  array('订单编号','客户名称','店铺名称','商品信息','商品数量','实付金额(元)','客户','下单时间','支付方式','订单状态');  //默认导出列标题
        //$default_fields     =  'obo.b2b_code,obo.total_num,obo.real_amount,oboe.real_name,obo.create_time';  //默认导出列
        $default_fields = "obo.b2b_code,obo.client_name,oboe.commercial_name,oboe.total_nums,obo.pay_type,oboe.coupon_amount,obo.real_amount,obo.cope_amount,obo.ship_method,oboe.real_name,oboe.mobile,oboe.province,oboe.city,oboe.district,oboe.address"
                . ",obo.salesman,obo.channel,obo.create_time,obo.pay_method,obo.order_status,obo.ship_status,obo.pay_status,obo.order_type";
//        $default_fields = "ss.name as store_name,ss.logo as store_logo,obo.b2b_code,obo.uc_code,obo.op_code,obo.sc_code,obo.real_amount,obo.username,"
//            . "obo.pay_method,obo.order_status,obo.ship_status,obo.pay_status,obo.total_num,obo.buy_from,obo.client_name,"
//            . "obo.create_time,oboe.total_real_amount,oboe.total_nums,oboe.real_name";
        $default_filename   =  '订单列表';
        $default_sql_flag   =  'order_list';
        $default_order      =  'obo.id desc';
        $default_api        =  'Com.Callback.Export.OcExport.orderList';  
        
        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        $status_where = M('Base.OrderModule.B2b.OrderInfo')->buildStatusWhere($status,$pay_type);
        //组装where 条件
        $where         =  array();
        $apiPath       =  "Base.OrderModule.B2b.Status.groupToDetail";
        $status_detail =  M($apiPath)->groupToDetail($status,$pay_type);
        $order_status  = $status_detail['order_status'];
        $ship_status   = $status_detail['ship_status'];
        $pay_status    = $status_detail['pay_status'];
        !empty($sc_code)        &&   $where['obo.sc_code']       =    $sc_code;
        !empty($b2b_code)       &&   $where['obo.b2b_code']      =    $b2b_code;
        !empty($order_type)     &&   $where['obo.order_type']    =      $order_type;
        !empty($start_time) && empty($end_time)   &&   $where['obo.create_time']   =    array('egt',$start_time);
        !empty($end_time)   && empty($start_time) &&   $where['obo.create_time']   =    array('elt',$end_time);
        !empty($start_time) && !empty($end_time)  &&   $where['obo.create_time']   =    array('between',array($start_time,$end_time));
        !empty($pay_start_time) && !empty($pay_end_time)  &&   $where['obo.pay_time']   =    array('between',array($pay_start_time,$pay_end_time));

        !empty($real_name)      &&   $where['oboe.real_name']    =    array('like',"%$real_name%");
        !empty($pay_method)     &&   $where['obo.pay_method']    =    $pay_method;
        !empty($pay_type)     &&   $where['obo.pay_type']    =    $pay_type;
        !empty($client_name)    &&   $where['client_name']       =    $client_name;
        !empty($salesman_id)    &&   $where['salesman_id']       =    $salesman_id;
        !empty($channel_id)     &&    $where['channel_id']         =    $channel_id;
        if(!empty($status_where)){
            $where['_complex'] = $status_where;
        }
        //组装调用导出api参数
        $params['where']        = empty($ori_where) ? $where : $ori_where;
        $params['fields']       = empty($ori_fields) ? $default_fields :$ori_fields;
        $params['title']        =  $title;
        $params['order']        =  empty($order) ? $default_order : $order;
        $params['center_flag']  =  SQL_OC;//订单中心   
        $params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $params['filename']     =  $filename;
        $params['callback_api'] = $callback_api;
        $params['template_call_api'] =  !empty($template_call_api) ? $template_call_api : "";
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $params);

        return $this->res($res['response'],$res['status']);
    }
}

?>
