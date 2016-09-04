<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop订单列表相关的操作
 */

namespace Bll\Cms\Order;

use System\Base;

class OrderInfo extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
    
    /**
     * 
     * 订单列表
     * pop  显示子订单（无论支付还是未支付）
     * b2b  未支付显示总订单    已支付显示子订单信息 
     * cms  未支付显示总订单    已支付显示子订单信息
     *  
     * sc_code    店铺编码  pop平台的
     * uc_code    用户编码  用户平台的(weixin  app)
     * b2b_code   订单编码
     * start_time 订单下单开始时间
     * end_time   订单下单结束时间
     * username   用户名
     * status  订单状态  组合状态
     * 
     * Bll.Cms.Order.OrderInfo.lists
     * @param type $params
     */
    public function lists($params){
        $pay_method_map = array(
            PAY_METHOD_ONLINE_ALIPAY=>ALIPAY_WAP,
            PAY_METHOD_ONLINE_WEIXIN=>WEIXIN_JSAPI_PAY,
            PAY_METHOD_ONLINE_UCPAY=>'UCPAY_DIRECT',
        );
        
        //订单列表方面  cms和pop  一致
        $_SERVER['HTTP_USER_AGENT'] = POP;
        
        $params['ori_fields'] =  "ss.name as store_name,ss.logo as store_logo,obo.b2b_code,obo.pay_type,obo.uc_code,obo.op_code,obo.sc_code,obo.real_amount,obo.cope_amount,obo.username,obo.channel,obo.ship_method,"
                . "obo.pay_method,obo.order_status,obo.ship_status,obo.pay_status,obo.total_num,obo.buy_from,obo.client_name,obo.pay_time,obo.pay_type,obo.ext1,"
                . "obo.create_time,oboe.total_real_amount,oboe.total_nums,oboe.mobile,oboe.city,oboe.district,oboe.address,oboe.commercial_name,oboe.real_name,um.salesman,obo.order_type";
        $params['sql_flag'] = "order_list_2";
        $params['page_number'] = '50';
        
        //自装 where
      
        $b2b_code = $params['b2b_code'];
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        $pay_method  = $params['pay_method'];
        $sc_code = $params['sc_code'];
        $salesman = $params['salesman'];
        $store_name = $params['store_name'];
        $deal_status = $params['deal_status'];
        $pay_status = $params['pay_status'];
        $ship_status = $params['ship_status'];
        $pay_type = $params['pay_type'];
        $b2b_price = $params['b2b_price'];
        $pay_start_time = $params['pay_start_time'];
        empty($params['pay_end_time'])? : $pay_end_time = $params['pay_end_time'];

        $where['obo.sc_code'] = array('not in','1010000000077');
//        $where = array();
        if($deal_status == 'success'){
            $where['obo.order_status'] = OC_ORDER_ORDER_STATUS_COMPLETE;
        }else if($deal_status == 'dealing'){
            $where['obo.order_status'] = array('not in',array(OC_ORDER_ORDER_STATUS_COMPLETE,OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL));
        }else if($deal_status == 'cancle'){
            $where['obo.order_status'] = array('in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL));
        }
        
        $where['obo.create_time'] = array('between',array($start_time,$end_time));
        $where['obo.pay_time'] = array('between', array($pay_start_time, $pay_end_time));

        !empty($b2b_price) && $where['obo.real_amount'] = $b2b_price;
        if ($b2b_price === '0') {
             
            $where['obo.real_amount'] = $b2b_price;
        }

        !empty($pay_type) && $where['obo.pay_type'] = $pay_type;
        !empty($pay_method) && $where['obo.pay_method'] = $pay_method;
        !empty($pay_type) && $where['obo.pay_type'] = $pay_type;
        !empty($sc_code) && $where['obo.sc_code'] = $sc_code;
        !empty($salesman) && $where['um.salesman'] = $salesman;
        !empty($store_name) && $where['oboe.commercial_name'] = array('like',"%{$store_name}%");
        !empty($pay_status) && $where['obo.pay_status'] = $pay_status;
        !empty($b2b_code) && $where['obo.b2b_code'] = $b2b_code;
        if(!empty($ship_status)){
            if($ship_status == OC_ORDER_SHIP_STATUS_UNSHIP){
                $where['obo.ship_status'] = $ship_status;
            }else {
                $where['obo.ship_status'] = array('neq',OC_ORDER_SHIP_STATUS_UNSHIP);
            }
        }
        $params['ori_where'] = $where;
        $apiPath = "Base.OrderModule.B2b.OrderInfo.lists";
        $list_res = $this->invoke($apiPath, $params);
        
        //算订单总金额
        $where = D()->parseWhereCondition($where) ;
        $sql = "SELECT
                                    sum(obo.real_amount) as total_amount
                            FROM
                                    {$this->tablePrefix}oc_b2b_order obo
                            LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend oboe ON obo.op_code = oboe.op_code
                            LEFT JOIN {$this->tablePrefix}sc_store ss ON obo.sc_code = ss.sc_code
                            LEFT JOIN {$this->tablePrefix}uc_merchant  um ON ss.merchant_id = um.id
                            
                                    {$where}
                                    ";
            $total_amount_res = D()->query($sql);
            $total_amount = $total_amount_res[0]['total_amount'];
        
        //获取商家列表
        $apiPath  = "Base.StoreModule.Basic.Store.lists";
        $storeLists = $this->invoke($apiPath);
        $list_res['response']['store_lists'] = $storeLists['response'];
        
        //获取支付方式
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        $list_res['response']['pay_methods'] = $status->getPayMethod();
        
        //获取支付类型
        $pay_types = M('Base.OrderModule.B2b.Status')->getPayType();
        $list_res['response']['pay_types'] = $pay_types;

        //获取双磁对接人
        $apiPath = "Base.UserModule.User.User.getSalesmanList";
        $list_res['response']['salesman_list'] = $this->invoke($apiPath)['response'];
        
        //获取流水
        $apiPath = "Base.TradeModule.Pay.Voucher.getInfo";
        foreach($list_res['response']['lists'] as $k=>$v){
            if($v['pay_status'] == OC_ORDER_PAY_STATUS_PAY && in_array($v['pay_method'],array(PAY_METHOD_ONLINE_ALIPAY,PAY_METHOD_ONLINE_WEIXIN)) ){
                $voucher_params = array(
                    'oc_code'=>$v['b2b_code'],
                    'pay_by'=>$pay_method_map[$v['pay_method']],
                );
               $voucher_res = $this->invoke($apiPath,$voucher_params);
               if($voucher_res['status'] != 0){
                   continue;
               }
               $list_res['response']['lists'][$k]['pay_no'] = $voucher_res['response']['pay_no'];
            }
            if ($v['pay_type']) {
                $pay_type = M('Base.OrderModule.B2b.Status')->getPayType($v['pay_type']);
                $list_res['response']['lists'][$k]['pay_type'] = $pay_type; 
            }
            // var_dump(number_format($v['real_amount'],2,',',''));
            if ($v['real_amount']) {
                $list_res['response']['lists'][$k]['real_amount'] = number_format($v['real_amount'], 2, '.', ','); 
            }

            if ($v['pay_method'] == PAY_METHOD_ONLINE_REMIT) {
                $str = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
                $list_res['response']['lists'][$k]['payMethod'] = $str;
            }
        }
        $list_res['response']['total_amount'] = $total_amount;
        return $this->res($list_res['response'],$list_res['status']);
    }
    
    
    /**
     * 订单详情
     * Bll.Cms.Order.OrderInfo.get
     * @param type $params
     */
    public function get($params){
        $apiPath = "Base.OrderModule.B2b.OrderInfo.get";
        $order_info_res = $this->invoke($apiPath, $params);
        return $this->res($order_info_res['response'],$order_info_res['status']);
    }
    
 /**
     * 导出订单
     * Bll.Cms.Order.OrderInfo.export
     * @param type $params
     */
    public function export($params){
        
        //订单列表方面  cms和pop  一致
        $_SERVER['HTTP_USER_AGENT'] = POP;
        
        $params['ori_fields'] =  "ss.name as store_name,obo.uc_code,obo.b2b_code,obo.real_amount,obo.cope_amount,obo.create_time,obo.order_status,obo.ship_status,obo.pay_status,"
                . "obo.pay_method,obo.pay_type,obo.ext1,obo.client_name,obo.pay_time,oboe.total_nums,oboe.total_real_amount,oboe.total_amount,oboe.phone,obo.ship_method,oboe.real_name,oboe.mobile,oboe.province,oboe.city,oboe.district,oboe.address,"
                . "oboe.mobile,oboe.commercial_name,um.salesman,obo.channel,obo.order_type,sa.active_name,sa.active_code,sa.condition_flag,oboe.coupon_code,oboe.coupon_amount";
        // $params['ori_fields'] = "obo.b2b_code,obo.client_name,oboe.commercial_name,obog.sc_code,obog.sic_code,obog.goods_name,obog.spec,obog.packing,obog.goods_price,obog.ori_goods_price,oboe.total_nums,oboe.total_real_amount,obo.ship_method,oboe.real_name,oboe.mobile,oboe.province,oboe.city,oboe.district,oboe.address"
        //         . ",obo.salesman,obo.channel,obo.create_time,obo.pay_time,obo.pay_method,obo.order_status,obo.ship_status,obo.pay_status";
        $params['sql_flag'] = "order_list_2";
        $params['title'] = array('卖家店铺','订单号','买家店铺','买家编码','买家姓名','买家手机号','商品编码','商品名称','规格','包装','单价（元）','成交价(元)','商品数量','促销方式','促销编号','优惠详情','商品总数','活动名称','活动编码','活动条件','优惠券编码','应付总额(元)','优惠金额(元)','实收总额（元）','配送方式','收货人','手机号码','收货地址','业务员','渠道','下单时间','支付类型','支付方式','支付时间','支付流水','订单类型','订单状态');
        //自装 where
        $pay_start_time = $params['pay_start_time'];
        $pay_end_time = $params['pay_end_time'];
        $start_time  = $params['start_time'];
        $end_time    = $params['end_time'];
        $pay_method  = $params['pay_method'];
        $sc_code     = $params['sc_code'];
        $salesman    = $params['salesman'];
        $store_name  = $params['store_name'];
        $deal_status = $params['deal_status'];
        $pay_status  = $params['pay_status'];
        $ship_status = $params['ship_status'];
        $b2b_code    = $params['b2b_code'];
        $pay_type    = $params['pay_type'];
        $b2b_price   = $params['b2b_price'];

        $where['obo.sc_code'] = array('not in','1010000000077');
        if($deal_status == 'success'){
            $where['obo.order_status'] = OC_ORDER_ORDER_STATUS_COMPLETE;
        }else if($deal_status == 'dealing'){
            $where['obo.order_status'] = array('not in',array(OC_ORDER_ORDER_STATUS_COMPLETE,OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL));
        }else if($deal_status == 'cancle'){
            $where['obo.order_status'] = array('in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL));
        }

        $start_time               = empty($start_time) ? 0 : strtotime($start_time);
        $end_time                 = empty($end_time) ? NOW_TIME : strtotime($end_time);
        
        $pay_start_time           = empty($pay_start_time) ? 0 : strtotime($pay_start_time);
        $pay_end_time             = empty($pay_end_time) ? NOW_TIME : strtotime($pay_end_time);
        $where['obo.pay_time']    = array('between', array($pay_start_time, $pay_end_time));
        $where['obo.create_time'] = array('between',array($start_time,$end_time));
        !empty($pay_method) && $where['obo.pay_method']       = $pay_method;
        !empty($sc_code) && $where['obo.sc_code']             = $sc_code;
        !empty($salesman) && $where['um.salesman']            = $salesman;
        !empty($store_name) && $where['oboe.commercial_name'] = array('like',"%{$store_name}%");
        !empty($pay_status) && $where['obo.pay_status']       = $pay_status;
        !empty($b2b_code) && $where['obo.b2b_code']           = $b2b_code;
        !empty($b2b_price) && $where['obo.real_amount']       = $b2b_price;
        !empty($pay_type) && $where['obo.pay_type']           = $pay_type;
        
        if(!empty($ship_status)){
            if($ship_status == OC_ORDER_SHIP_STATUS_UNSHIP){
                $where['obo.ship_status'] = $ship_status;
            }else {
                $where['obo.ship_status'] = array('neq',OC_ORDER_SHIP_STATUS_UNSHIP);
            }
        }

        $params['template_call_api'] = "Com.Callback.Export.Template.orderCms";
        $params['ori_where']    = $where;
        $params['callback_api'] = "Com.Callback.Export.OcExport.orderListCms";
        $apiPath                = "Base.OrderModule.B2b.Export.export";
        $list_res               = $this->invoke($apiPath, $params);
        return $this->res($list_res['response'],$list_res['status'],'',$list_res['message']);
     
    }
    

}

?>
