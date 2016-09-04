<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单列表相关的操作
 */

namespace Base\OrderModule\B2b;

use System\Base;

class OrderInfo extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    private $pay_method_map;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Oc';
        $this->pay_method_map = array(
            PAY_METHOD_ONLINE_ALIPAY=>ALIPAY_WAP,
            PAY_METHOD_ONLINE_WEIXIN=>WEIXIN_JSAPI_PAY,
            PAY_METHOD_ONLINE_UCPAY=>'UCPAY_DIRECT',
        );
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
     * Base.OrderModule.B2b.OrderInfo.lists
     * @param type $params
     */
    public function lists($params){
        $order_group_status = M('Base.OrderModule.B2b.Status.groupStatusList')->groupStatusList();
        $status_list = array_keys($order_group_status);
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK), //订单编码
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK), //订单下单开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK), //订单下单结束时间
            array('real_name', 'require', PARAMS_ERROR, ISSET_CHECK), //用户名
            array('status', $status_list, PARAMS_ERROR, ISSET_CHECK, 'in'), //订单状态  组合状态
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式
            array('client_name', 'require', PARAMS_ERROR, ISSET_CHECK), //客户姓名
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ship_method', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('channel_id', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('commercial_name', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ori_where', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ori_fields', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('termstatus', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('need_total_amount', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),  //是否需要统计插叙出来的总金额
            array('sort','require',PARAMS_ERROR,ISSET_CHECK),
            array('order_type', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $status_detail = array();
        //获取平台标识
        $platform_flag = $this->_request_sys_name;
        $client_name = $params['client_name'];
        $salesman_id = $params['salesman_id'];
        $channel_id = $params['channel_id'];
        $uc_code = $params['uc_code'];
        $sc_code = $params['sc_code'];
        $b2b_code = $params['b2b_code'];
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        $real_name = $params['real_name'];
        $ship_method = $params['ship_method'];
        $status = empty($params['status']) ? OC_ORDER_GROUP_STATUS_ALL : $params['status'];   //默认取全部订单状态
        $pay_method = $params['pay_method'];
        $pay_type = $params['pay_type'];
        $commercial_name = $params['commercial_name'];
        $sql_flag = $params['sql_flag'];
        $ori_where = $params['ori_where'];
        $ori_fields = $params['ori_fields'];
        $order_type = $params['order_type'];
        $need_total_amount = empty($params['need_total_amount']) ? 'NO' : $params['need_total_amount'];
        //验证不同平台下的编码
//        $this->checkParams($sc_code, $uc_code);

        $default_fields = "ss.name as store_name,ss.logo as store_logo,obo.b2b_code,obo.uc_code,obo.pay_type,obo.op_code,obo.sc_code,obo.real_amount,obo.cope_amount,obo.username,obo.channel,obo.ship_method,"
            . "obo.pay_method,obo.order_status,obo.ship_status,obo.pay_status,obo.total_num,obo.buy_from,obo.salesman,obo.client_name,"
            . "obo.create_time,oboe.total_real_amount,oboe.total_nums,oboe.mobile,oboe.city,oboe.district,oboe.address,oboe.commercial_name,oboe.real_name,obo.before_goods_amount,obo.ext4,obo.ext5,
            obo.sender_mobile,oboe.pick_up_code,oboe.pick_up_qrcode,obo.pay_time,obo.ship_time,obo.order_type,oboe.remark,oboe.coupon_code,oboe.coupon_amount,oboe.active_code";
       
        $order = isset($params['sort']) ? ' obo.id  '.$params['sort']:'obo.id desc ';
        //where条件  组装
        $where = array('obo.is_show' => 'YES');
        //订单状态
        $apiPath = "Base.OrderModule.B2b.Status.groupToDetail";
        $status_where = $this->buildStatusWhere($status, $pay_type);
        $order_status = $status_detail['order_status'];
        $ship_status = $status_detail['ship_status'];
        $pay_status = $status_detail['pay_status'];
        !empty($uc_code) && $where['obo.uc_code'] = $uc_code;
        !empty($sc_code) && $where['obo.sc_code'] = $sc_code;
        !empty($b2b_code) && $where['obo.b2b_code'] = $b2b_code;
        !empty($start_time) && empty($end_time) && $where['obo.create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['obo.create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['obo.create_time'] = array('between', array($start_time, $end_time));
        !empty($real_name) && $where['oboe.real_name'] = array('like', "%$real_name%");
        !empty($pay_method) && $where['obo.pay_method'] = $pay_method;
        !empty($ship_method) && $where['obo.ship_method'] = $ship_method;
        !empty($client_name) && $where['client_name'] = $client_name;
        !empty($salesman_id) && $where['salesman_id'] = $salesman_id;
        !empty($channel_id) && $where['channel_id'] = $channel_id;
        !empty($commercial_name) && $where['oboe.commercial_name'] = $commercial_name;
        !empty($order_type) && $where['obo.order_type'] = $order_type;
        !empty($pay_type) && $where['obo.pay_type'] = $pay_type;
        if (!empty($status_where)) {
            $where['_complex'] = $status_where;
        }

        $params['fields'] = empty($ori_fields) ? $default_fields : $ori_fields;
        $params['order'] = $order;
        $params['where'] = empty($ori_where) ? $where : $ori_where;
        $params['center_flag'] = SQL_OC; //订单中心   
        $params['sql_flag'] = empty($sql_flag) ? 'order_list' : $sql_flag;  //sql标识

        if ($need_total_amount == 'YES') {
            $aggre = array(
                array('sum', 'obo.real_amount', 'total_amount'),
            );
            $params['aggre'] = $aggre;
        }

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status'], '', $list_res['message']);
        }
        $order_data = $list_res['response']['lists'];
        $apiPath = "Base.OrderModule.B2b.Status.detailToGroup";
        $statusModel = M($apiPath);
        $b2bArray = [];
        foreach ($order_data as $k => $v) {
            if ($platform_flag == B2B && $v['pay_status'] == OC_ORDER_PAY_STATUS_UNPAY) {
                //如果是b2b 平台  并且是未支付状态，则要显示总订单的信息
                $v['real_amount'] = $v['total_real_amount'];
                $v['total_num'] = $v['total_nums'];
//                $v['order_goods'] = $v['main_order_goods'];
            }
            $status_message = $statusModel->detailToGroup($v['order_status'], $v['ship_status'], $v['pay_status'], $v['pay_type'], $v['ship_method']);
            //$order_data[$k]['order_goods'] = json_decode($v['order_goods'], true);
//            empty($b2b_code) ? $b2bArray[] = $v['b2b_code'] : $b2bArray = $b2b_code;
            $b2bArray[] = $v['b2b_code'];

            # 添加订单留言标识处理  2015-12-28 added
            $remark = '';
            $order_data[$k]['is_remark'] = empty($v['remark']) ? 'NO': 'YES';  #是否备注
            $order_data[$k]['remark'] = empty($v['remark']) ? '': $v['remark'];#留言内容

            $order_data[$k]['status'] = $status_message['status'];   //订单组合状态
            $order_data[$k]['status_message'] = $status_message['message'];  //订单组合状态信息
            $order_data[$k]['operate'] = $statusModel->getOpeByStatus($v['order_status'], $v['ship_status'], $v['pay_status'], $v['pay_method'], $v['pay_type'], $v['ship_method']);
            if ($v['ext4'] != '') {
                $order_data[$k]['end_time'] = $v['ext4'] == PAY_TYPE_TERM_MONTH ? date('Y-m', $v['create_time']) . '-' . date('t', $v['create_time']) : date('Y-m-d', ($v['create_time'] + $v['ext5'] * 86400));
                $order_data[$k]['is_end'] = time() >= strtotime($order_data[$k]['end_time'] . ' 23:59:59') ? 'YES' : 'NO';
            }

        }
        if ($b2bArray) {
            //获取订单商品
            $goods_list = $this->_getGoods($b2bArray);
            $gift_list = $this->_getGiftByB2b($b2bArray);

            //获取促销
            $temp_gift_list = array();
            foreach ($gift_list as $k => $v) {
                $temp_gift_list[$v['b2b_code'] . '_' . $v['p_sic_code']] = $v;
            }
            $gift_list = $temp_gift_list;
            unset($temp_gift_list);
            $temp_goods_list = array();

            foreach ($goods_list as $ke => $va) {
                //$goods_list[$ke]['spc'] = $this->_getSpcByCode($va['op_code'],$spc_list);
                if (!empty($va['spc_code'])) {

                    $va['spc_goods'] = $gift_list[$va['b2b_code'] . '_' . $va['sic_code']];
                    //是赠品
                    $spc_message = spcRuleParse(SPC_TYPE_GIFT, array('rule' => $va['spc_goods']['rule']));
                    $va['spc_message'] = $spc_message;
                }
                if ($va['spc_type'] == SPC_TYPE_SPECIAL) {
                    $special_type = $this->invoke('Base.SpcModule.Special.SpecialInfo.get', ['spc_code' => $va['spc_code']]);
                    $va['special_type'] = $special_type['response']['special_type'];
                    $spc_message = spcRuleParse(SPC_TYPE_SPECIAL, array('ori_price' => $va['ori_goods_price'], 'special_price' => $va['goods_price'], 'packing' => $va['packing'], 'platform_flag' => $this->_request_sys_name, 'special_type' => $special_type['response']['special_type'], 'discount' => $va['discount']));
                    $va['spc_message'] = $spc_message;
                }
                if ($va['spc_type'] == SPC_TYPE_LADDER) {
                    $spc_message = spcRuleParse(SPC_TYPE_LADDER, array('platform_flag' => $this->_request_sys_name, 'rule' => $va['ladder_rule'], 'price' => $va['ori_goods_price'], 'packing' => $va['packing']));
                    $va['spc_message'] = $spc_message;
                }
                $temp_goods_list[$va['b2b_code']][] = $va;

            }
            $goods_list = $temp_goods_list;
            unset($temp_goods_list);
            $model = M('Base.OrderModule.B2b.Status.getShipMethodList');
            foreach ($order_data as $key => $value) {
                if (!empty($value['pay_method'])) {
                    $order_data[$key]['payMethod'] = $model->getPayMethod($value['pay_method']);
                }
                $order_data[$key]['payType'] = $model->getPayType($value['pay_type']);
                $order_data[$key]['shipMethod'] = $model->getShipMethodList($value['ship_method']);
                $order_data[$key]['order_goods'] = $goods_list[$value['b2b_code']];
                $order_data[$key]['is_remark'] = $value['is_remark'];
            }
        }
        $list_res['response']['lists'] = $order_data;
        //获取状态列表
        $list_res['response']['status_lists'] = $order_group_status;

        return $this->res($list_res['response']);
    }

    /**
     * 简单的获取订单的基本信息
     * Base.OrderModule.B2b.OrderInfo.simpleGet
     * @param type $params
     */
    public function simpleGet($params)
    {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //用户编码
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK), //订单编码
            array('need_order_goods', 'require', PARAMS_ERROR, ISSET_CHECK),//是否需要订单商品
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $need_order_goods = $params['need_order_goods'];
        $need_order_goods = empty($need_order_goods) ? 'NO' : $need_order_goods;
        $where = array(
            'op_code' => $params['op_code'],
        );

        !empty($params['uc_code']) && $where['uc_code'] = $params['uc_code'];
        $order_info = D('OcB2bOrder')->field('id,uc_code,b2b_code,sc_code,pay_type,pay_method,goods_amount,before_goods_amount,order_status,pay_time')->where($where)->master()->find();


        if (empty($order_info)) {
            return $this->res(NULL, 6015);
        }

        $order_extend_info = D('OcB2bOrderExtend')->where(array('op_code' => $params['op_code']))->master()->find();

        if (empty($order_extend_info)) {
            return $this->res(NULL, 6016);
        }
        $order_extend_info = array_merge($order_extend_info, $order_info);
//        $order_extend_info['uc_code'] = $order_info['uc_code'];
//        $order_extend_info['sc_code'] = $order_info['sc_code'];
//        $order_extend_info['pay_method']  = $order_info['pay_method'];
        //是否需要订单商品数据
        if ($need_order_goods == 'YES') {
            $order_goods = D('OcB2bOrderGoods')->where(array('b2b_code' => $order_info['b2b_code']))->select();
            $order_extend_info['order_goods'] = $order_goods;
        }

        return $this->res($order_extend_info);
    }

    /**
     * 订单详情
     * Base.OrderModule.B2b.OrderInfo.get
     * @param type $params
     */
    public function get($params)
    {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //用户编码
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('b2b_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //订单编码
            array('pick_up_code', 'require', PARAMS_ERROR, HAVEING_CHECK) //提货码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $sc_code = $params['sc_code'];
        $b2b_code = $params['b2b_code'];
        $pick_up_code = $params['pick_up_code'];

        $this->checkParams($sc_code, $uc_code);
        //查询订单
        if (!empty($b2b_code)) {
            $where = array('b2b_code' => $b2b_code);
        }
        $this->_request_sys_name == POP && $where['sc_code'] = $sc_code;      //如果是pop平台   则一定要有商家编码
        $this->_request_sys_name == B2B && $where['uc_code'] = $uc_code;      //如果是b2b平台   则一定要有用户编码
        $order_extend = null;
        $order_info = null;
        if (!empty($pick_up_code)) {
            #根据取货码，获取唯一的订单编号 op_code
            $code = D('OcB2bOrderExtend')->where(array('pick_up_code' => $pick_up_code))->find();
            if (empty($code)) {
                return $this->res(null, 6061);
            }
            $order_extend = $code;
            #根据订单标号，和商家编号获取订单信息
            $order_info = D('OcB2bOrder')->where(array('op_code' => $code['op_code'], 'sc_code' => $sc_code))->find();
            if (empty($order_info)) {
                return $this->res(NULL, 6061);
            }
            if ($order_info['ship_status'] == OC_ORDER_SHIP_STATUS_TAKEOVER) {
                return $this->res($order_info, 6062);
            }
        }
        if ($order_info === null) {
            $order_info = D('OcB2bOrder')->where($where)->find();
        }
        if (empty($order_info)) {
            return $this->res(NULL, 6015);
        }
        if ($order_extend === null) {
            $order_extend = D('OcB2bOrderExtend')->where(array('op_code' => $order_info['op_code']))->find();
        }
        if (empty($order_extend)) {
            return $this->res(null, 6016);
        }

        unset($order_extend['id']);
        unset($order_extend['op_code']);
        $where = array();
        $order_info = array_merge($order_info, $order_extend);
        if ($this->_request_sys_name == B2B && $order_info['pay_status'] == OC_ORDER_PAY_STATUS_UNPAY) {
            //如果是  b2b 平台  ，并且未支付  则 显示总订单信息  包括总金额  ，总数量 
//            $order_info['real_amount'] = $order_info['total_real_amount'];
            $order_info['total_num'] = $order_info['total_nums'];
            $order_info['goods_amount'] = $order_info['total_goods_amount'];
            $where['b2b_code'] = $order_info['b2b_code'];   //查询订单商品的时候  查询全部的订单商品
        } else {
            $where['b2b_code'] = $order_info['b2b_code'];  //否则只查 该订单号的订单商品 
        }

//        $wheres['op_code'] =  $order_info['op_code'];

        //查询订单商品 信息
        $order_goods = D('OcB2bOrderGoods')->where($where)->select();
        // $order_goods['spc_goods'] = D("OcB2bOrderGift")->where($wheres)->select();
        if (empty($order_goods)) {
            return $this->res(NULL, 6017);
        }
        $apiPath = "Base.OrderModule.B2b.Status.detailToGroup";
        $model = M($apiPath);
        $status_info = $model->detailToGroup($order_info['order_status'], $order_info['ship_status'], $order_info['pay_status'], $order_info['pay_type'], $order_info['ship_method']);
        $order_info['status'] = $status_info['status'];
        $order_info['status_message'] = $status_info['message'];
        $order_info['ship_method_message'] = $model->getShipMethodList($order_info['ship_method']);
        if (!empty($order_info['pay_method'])) {
            $order_info['pay_method_message'] = $model->getPayMethod($order_info['pay_method']);
        }
        $order_info['pay_type_message'] = $model->getPayType($order_info['pay_type']);
        $order_info['operate'] = $model->getOpeByStatus($order_info['order_status'], $order_info['ship_status'], $order_info['pay_status'], $order_info['pay_method'], $order_info['pay_type'], $order_info['ship_method']);
        if ($order_info['pay_method'] != PAY_METHOD_ONLINE_ADVANCE && $order_info['pay_method'] != PAY_METHOD_ONLINE_REMIT && !empty($order_info['pay_method'])) {
            //获取支付信息
            $apiPath = "Base.TradeModule.Pay.Voucher.getInfo";
            $pay_data = array(
                'oc_code' => $order_info['op_code'],
                'pay_by' => $this->pay_method_map[$order_info['pay_method']],
            );
            $pay_res = $this->invoke($apiPath, $pay_data);
            if ($pay_res['status'] != 0) {
                return $this->res(NULL, $pay_res['status']);
            }
            $pay_info = $pay_res['response'];
        }
        //查询赠品信息
        $gift_list = $this->_getGiftByB2b($b2b_code);

        $temp_gift_list = array();
        foreach ($gift_list as $k => $v) {
            $temp_gift_list[$v['p_sic_code']] = $v;
        }
        $gift_list = $temp_gift_list;
//      $goods_list = $this->_getGoods($b2b_code);
        foreach ($order_goods as $ke => $va) {
            if (isset($gift_list[$va['sic_code']]) && !isset($order_goods[$k]['flag'])) {
                $rule = spcRuleParse(SPC_TYPE_GIFT, array('rule' => $gift_list[$va['sic_code']]['rule']));
                $order_goods[$ke]['prefer'] = $rule;
                $order_goods[$ke]['spc_goods'] = $gift_list[$va['sic_code']];
                $order_goods[$ke]['flag'] = true;
            }
            if ($va['spc_type'] == SPC_TYPE_SPECIAL) {
                $special_type = $this->invoke('Base.SpcModule.Special.SpecialInfo.get', ['spc_code' => $va['spc_code']]);
                $order_goods[$ke]['special_type'] = $special_type['response']['special_type'];

                $spc_message = spcRuleParse(SPC_TYPE_SPECIAL, array('ori_price' => $va['ori_goods_price'], 'special_price' => $va['before_goods_price'], 'packing' => $va['packing'], 'platform_flag' => B2B, 'special_type' => $special_type['response']['special_type'], 'discount' => $special_type['response']['discount']));
                $order_goods[$ke]['spc_message'] = $spc_message;

            }
            if ($va['spc_type'] == SPC_TYPE_LADDER) {
                $spc_message = spcRuleParse(SPC_TYPE_LADDER, array('platform_flag' => $this->_request_sys_name, 'rule' => $va['ladder_rule'], 'price' => $va['ori_goods_price'], 'packing' => $va['packing']));
                $order_goods[$ke]['spc_message'] = $spc_message;
            }
        }
//            $order_info['order_goods'] = $this->_getGoodByCode($b2b_code,$goods_list);

        $return_data = array(
            'order_info' => array_filter($order_info),
            'order_goods' => $order_goods,
            'pay_info' => $pay_info,
        );
        return $this->res($return_data);
    }


    public function buildStatusWhere($status, $pay_type = '')
    {

        $where = array();
        if ($status == OC_ORDER_GROUP_STATUS_ALL) {
            return $where;
        }
        switch ($this->_request_sys_name) {
            case POP:
                $where = $this->buildPopWhere($status, $pay_type = '');
                break;
            case B2B:
                $where = $this->buildB2bWhere($status, $pay_type = '');
                break;
            default :
                ;
        }
        $where['_logic'] = 'or';
        return $where;
    }

    private function buildPopWhere($status, $pay_type = '')
    {
        $where = array();
        $model = M('Base.OrderModule.B2b.Status');
        switch ($status) {
            case OC_ORDER_GROUP_STATUS_UNPAY:
                //待付款   线上支付  待付款，线下支付 待收款
                $temp['pay_status'] = OC_ORDER_PAY_STATUS_UNPAY;
                $temp['order_status'] = array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));  //没取消
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_UNSHIP:
                //待发货  线上支付  待发货，线下支付 待发货
                $temp = $model->groupToDetail($status, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_COD); //货到付款
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_TERM); //账期支付
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_SHIPPED:
                //  已发货： 线上支付  已发货，线下支付 已发货
                $temp = $model->groupToDetail($status, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;

                $temp = $model->groupToDetail($status, PAY_TYPE_COD);
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;

                $temp = $model->groupToDetail($status, PAY_TYPE_TERM);
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_COMPLETE:
                //交易成功：线上支付： 线上支付确认收货，线下支付  确认收款
                $where[] = $model->groupToDetail($status);
//                $where[] = $model->groupToDetail($status, PAY_METHOD_OFFLINE_COD);
                break;
            case OC_ORDER_GROUP_STATUS_CANCEL:
                //交易取消：商家和用户取消
                $where[]['order_status'] = array('in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));  //已取消
                break;
            case OC_ORDER_GROUP_STATUS_TAKEOVER:
                $temp = $model->groupToDetail($status, PAY_TYPE_COD);
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;

                $temp = $model->groupToDetail($status, PAY_TYPE_TERM);
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            case  OC_ORDER_GROUP_STATUS_TRADE:
                $map[] = array('obo.pay_type' => array('eq', PAY_TYPE_ONLINE), 'obo.pay_status' => array('eq', TC_PAY_VOUCHER_PAY), 'obo.pay_method' => array('neq', PAY_METHOD_ONLINE_REMIT));
                $map[] = array('obo.pay_type' => array('eq', PAY_TYPE_TERM), 'obo.ship_status' => array('neq', OC_ORDER_SHIP_STATUS_UNSHIP));
                $map[] = array('obo.pay_type' => array('eq', PAY_TYPE_COD), 'obo.ship_status' => array('neq', OC_ORDER_SHIP_STATUS_UNSHIP));
                $map[] = array('obo.pay_type' => array('eq', PAY_TYPE_ONLINE), 'obo.pay_method' => array('eq', PAY_METHOD_ONLINE_REMIT), 'obo.order_status' => array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL)));
                $map['_logic'] = 'or';
                $where[]['_complex'] = $map;
                break;
            case OC_ORDER_GROUP_STATUS_VERIFICATION:
                $map['ship_method'] = SHIP_METHOD_PICKUP;
                $map['ship_status'] = OC_ORDER_SHIP_STATUS_TAKEOVER;
                $where[] = $map;
            default :
                ;
        }
        return $where;
    }

    private function buildB2bWhere($status, $pay_type = '')
    {
        $where = array();
        $model = M('Base.OrderModule.B2b.Status');
        switch ($status) {
            case OC_ORDER_GROUP_STATUS_UNPAY:
                //待付款：  线上支付   待付款
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;

                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER, PAY_TYPE_COD);
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;


                // $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_TERM);
                // $temp['pay_type'] = PAY_TYPE_TERM;
                // $where[] = $temp;

                break;
            case OC_ORDER_GROUP_STATUS_SHIPPED:
                //待收货：  线上支付待发货，线上支付已发货，线下支付已发货
//                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNSHIP, PAY_TYPE_ONLINE);  //线上支付待发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_SHIPPED, PAY_TYPE_ONLINE);  //线上支付已发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_SHIPPED, PAY_TYPE_COD);  //货到付款 已发货
                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_SHIPPED, PAY_TYPE_TERM);  //账期支付 已发货
                break;
            case OC_ORDER_GROUP_STATUS_COMPLETE:
                //已完成：  线上支付确认收货，线下支付 商家确认收款，线下支付确认收货

                $where[] = $model->groupToDetail($status);  //线上支付确认收货
//                $where[] = $model->groupToDetail(OC_ORDER_GROUP_STATUS_TAKEOVER,PAY_METHOD_OFFLINE_COD);  //线下支付确认收货
                break;
            case OC_ORDER_GROUP_STATUS_CANCEL:
                //已取消：  商家和用户取消
                $where['order_status'] = array('in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));  //已取消
                break;
            case OC_ORDER_GROUP_STATUS_UNSHIP:
                //待发货  线上支付  待发货，线下支付 待发货
                $temp = $model->groupToDetail($status, PAY_TYPE_ONLINE);
                $temp['pay_type'] = PAY_TYPE_ONLINE;
                $where[] = $temp;
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_COD); //货到付款
                $temp['pay_type'] = PAY_TYPE_COD;
                $where[] = $temp;
                $temp = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, PAY_TYPE_TERM); //账期支付
                $temp['pay_type'] = PAY_TYPE_TERM;
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_TERM_UNPAY:
                $temp['pay_status'] = OC_ORDER_PAY_STATUS_UNPAY;
                $temp['pay_type'] = PAY_TYPE_TERM;
                $temp['ship_status'] = array('in', array(OC_ORDER_SHIP_STATUS_SHIPPED, OC_ORDER_SHIP_STATUS_TAKEOVER));
                $temp['order_status'] = array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL));
                $where[] = $temp;
                break;
            case OC_ORDER_GROUP_STATUS_VERIFICATION:
                $map[] = array('obo.pay_type' => array('neq', PAY_TYPE_ONLINE), 'obo.order_status' => array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL)), 'ship_method' => SHIP_METHOD_PICKUP, 'ship_status' => array('eq', OC_ORDER_SHIP_STATUS_UNSHIP));
                $map[] = array('obo.pay_type' => array('eq', PAY_TYPE_ONLINE), 'obo.pay_status' => array('eq', TC_PAY_VOUCHER_PAY), 'ship_method' => SHIP_METHOD_PICKUP, 'ship_status' => array('eq', OC_ORDER_SHIP_STATUS_UNSHIP));
                $map['_logic'] = 'or';
                $where[] = $map;
            default :
                ;
        }
        return $where;
    }

    /**
     * 订单状态操作
     * Base.OrderModule.B2b.OrderInfo.operate
     * @param type $params
     */
    public function operate($params)
    {

        $model = M('Base.OrderModule.B2b.Status.groupStatusList');
        $status_list = $model->groupStatusList();
        $status_list = array_keys($status_list);
        $this->startOutsideTrans();
        $this->_rule = array(
            array('status', $status_list, PARAMS_ERROR, MUST_CHECK, 'function'),
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('need_action', array('YES', 'NO'), PARAMS_ERROR, ISSET_CHECK, 'in'),
            array('sender_mobil', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('sender', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('cancel_type', 'require', PARAMS_ERROR, ISSET_CHECK),   # 取消的类型
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if (!empty($params['sender_mobile']) && empty($params['sender'])) {
            if (!preg_match('/(^1{1}[3|8|5|7][\d]{9}$)|(^[02-9][\d]{1,19}$)/', $params['sender_mobile'])) return $this->res(null, 9100);
            $status_data['sender_mobile'] = $params['sender_mobile'];
        }

        if (!empty($params['sender_mobile']) && !empty($params['sender'])) {
            if (!preg_match('/^1{1}[3|8|5|7][\d]{9}$/', $params['sender_mobile'])) return $this->res(null, 9100);
            if (!preg_match("/[\x{4e00}-\x{9fa5}\w]+$/u", $params['sender'])) return $this->res(null, 9101);

            $status_data['sender_mobile'] = $params['sender_mobile'];
            $status_data['sender'] = $params['sender'];
        }
        $status = $params['status'];
        $uc_code = $params['uc_code'];
        $b2b_code = $params['b2b_code'];
        $sc_code = $params['sc_code'];
        $need_action = empty($params['need_action']) ? 'NO' : $params['need_action'];
        $where = array('b2b_code' => $b2b_code);
        // $this->checkParams($sc_code, $uc_code);
        !empty($uc_code) && $where['uc_code'] = $uc_code;
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        //获取订单的信息
        $order_info = D('OcB2bOrder')->where($where)->find();
        if (empty($order_info)) {
            return $this->res(NULL, 6015);
        }

        //获取订单扩展信息
        $order_extend = D('OcB2bOrderExtend')->where(array('op_code'=>$order_info['op_code']))->field('mobile,coupon_code')->find();
        $order_info['mobile'] = $order_extend['mobile'];
        $orderGoodsInfo = D('OcB2bOrderGoods')->where(array('b2b_code' => $b2b_code))->field('sic_code,goods_number,sc_code,spc_code,spc_type,spc_max_buy')->select();
        $pay_method = $order_info['pay_method'];
        $pay_type = $order_info['pay_type'];
        $ship_method = $order_info['ship_method'];
        //获取操作权限

        $status_where = $model->getOpePre($status, $pay_method, $pay_type, true, $ship_method);
        if (FALSE === $status_where) {
            return $this->res(NULL, 6021);
        }

        $operate_flag = $params['operate_flag'];
        if($operate_flag=='auto'){
            $status_data['operate_type'] ='AUTO';
        }
        $order_status = $status_where['order_status'];
        $pay_status = $status_where['pay_status'];
        $ship_status = $status_where['ship_status'];
        $where['order_status'] = is_array($order_status) ? array('in', $order_status) : $order_status;
        $where['pay_status'] = is_array($pay_status) ? array('in', $pay_status) : $pay_status;
        $where['ship_status'] = is_array($ship_status) ? array('in', $ship_status) : $ship_status;
        $status_res = $model->groupToDetail($status, $pay_type);
        $status_data['order_status'] = is_array($status_res['order_status']) ? $status_res['order_status'][$this->_request_sys_name] : $status_res['order_status'];
        $status_data['pay_status'] = $status_res['pay_status'];
        $status_data['ship_status'] = $status_res['ship_status'];
        $status_data['update_time'] = NOW_TIME;

        if($status == OC_ORDER_GROUP_STATUS_CANCEL){
            if(empty($params['cancel_type'])){
                $this->_request_sys_name = B2B && $status_data['cancel_type'] = "MEMBER";           # 小B
                $this->_request_sys_name = POP && $status_data['cancel_type'] = "MERCHANT";         # 大B
            }else{
                $status_data['cancel_type'] = $params['cancel_type'];                               # 自动
                $status_data['order_status'] = OC_ORDER_GROUP_STATUS_CANCEL;                        # 状态
            }

        }

        //获取时间字段
        $time_field = $model->getOpeTimeField($status);
        !empty($time_field) && $status_data[$time_field] = NOW_TIME;
        if (($status == OC_ORDER_ORDER_STATUS_COMPLETE && $pay_type != PAY_TYPE_ONLINE) || ($status == OC_ORDER_GROUP_STATUS_UNSHIP && $pay_type == PAY_TYPE_ONLINE)) {
            $status_data['pay_time'] = NOW_TIME;
        }
        $ope_res = D('OcB2bOrder')->where($where)->save($status_data);
        if ($ope_res <= 0 || $ope_res == FALSE) {
            return $this->res(NULL, 6022);
        }
        $data['pay_method'] = $pay_method;
        //去过取消  则回滚
        if ($status == OC_ORDER_GROUP_STATUS_CANCEL) {
            $orderGoodsGift = D('OcB2bOrderGift')->where(array('b2b_code' => $b2b_code))->field('sic_code,goods_number,sc_code')->select();
            $this->rollbackStock($orderGoodsInfo,$orderGoodsGift);
            //如果有促销券则回滚状态
            $coupon_code = $order_extend['coupon_code'];
            if($coupon_code){
                //先判断前置状态
                $apiPath = 'Base.UserModule.Coupon.Coupon.operate';
                $data = [
                    'coupon_code'=>$coupon_code,
                    'operate_status'=>'enable'
                ];
                $status = $this->invoke($apiPath,$data);
                if($status['status']!==0){
                    return $this->res('',$status['status']);
                }
               $api = 'Base.UserModule.Coupon.Coupon.rollback';
                $call = $this->invoke($api,['coupon_code'=>$coupon_code]);
                if($call['status']!==0){
                    return $this->res('',$call['status']);
                }
            }
//            //如果支付方式是线下支付
//            if($pay_method == PAY_METHOD_OFFLINE_COD){
//                //客户订单金额回滚
//                $this->updateCustomerOrderInfo($order_info['sc_code'], $order_info['uc_code'],-1, -$order_info['real_amount']);
//            }
        }

        if ($need_action == 'YES') {
            $apiPath = "Base.OrderModule.B2b.OrderAction.orderActionUp";
            $data = array(
                'b2b_code'=>$b2b_code,
                'uc_code'=>$uc_code,
                'status'=>$status,
                'pay_method'=>$pay_method,
                'pay_type'=>$pay_type,
                'operate_flag'=>$operate_flag,
                'cancel_type'=>$params['cancel_type'],
            );
            $action_res = $this->invoke($apiPath, $data);
            if ($action_res['status'] != 0) {
                return $this->res(NULL, $action_res['status']);
            }
        }

        $data['order_goods'] = $orderGoodsInfo;
        $data['order_info'] = $order_info;
        return $this->res($data);
    }

    /**
     * 支付回调
     * Base.OrderModule.B2b.OrderInfo.payBack
     * @param type $params
     */
    public function payBack($params)
    {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('amount', 'require', PARAMS_ERROR, MUST_CHECK),
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('pay_by', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //查询金额
        $apiPath = "Base.OrderModule.B2b.OrderInfo.simpleGet";
        $pay_method = $params['pay_method'];
        $data = array(
            'op_code' => $params['op_code'],
        );
        $order_res = $this->invoke($apiPath, $data);
        if ($order_res['status'] != 0) {
            return $this->res(NULL, $order_res['status']);
        }
        $ori_amount = $order_res['response']['total_real_amount'];
        $pay_type = $order_res['response']['pay_type'];
        $pay_by = $params['pay_by'];
        if ($ori_amount != $params['amount']) {
            return $this->res(NULL, 6023);
        }
        if ($pay_type == PAY_TYPE_ONLINE) {
            //修改状态
            $data = array(
                'order_status' => OC_ORDER_ORDER_STATUS_UNCONFIRM,
                'pay_status' => OC_ORDER_PAY_STATUS_PAY,
                'ship_status' => OC_ORDER_SHIP_STATUS_UNSHIP,
                'pay_time' => NOW_TIME,
                'update_time' => NOW_TIME,
                'pay_method' => $pay_method,
            );
        } else {
            $data = array(
                'order_status' => OC_ORDER_ORDER_STATUS_COMPLETE,
                'pay_status' => OC_ORDER_PAY_STATUS_PAY,
                'ship_status' => OC_ORDER_SHIP_STATUS_TAKEOVER,
                'pay_time' => NOW_TIME,
                'update_time' => NOW_TIME,
                'pay_method' => $pay_method,
                'complete_time'=>NOW_TIME,
            );
        }

        if (!empty($pay_by)) {
            $data['ext1'] = $pay_by;
        }
        if ($pay_type == PAY_TYPE_ONLINE) {
            $where = array(
                'order_status' => OC_ORDER_ORDER_STATUS_UNCONFIRM,
                'pay_status' => OC_ORDER_PAY_STATUS_UNPAY,
                'ship_status' => OC_ORDER_SHIP_STATUS_UNSHIP,
                'op_code' => $params['op_code'],
            );
        } else {
            $where = array(
                'order_status' => OC_ORDER_GROUP_STATUS_CHECKED,
                'pay_status' => OC_ORDER_PAY_STATUS_UNPAY,
                'ship_status' => OC_ORDER_SHIP_STATUS_TAKEOVER,
                'op_code' => $params['op_code'],
            );
        }

        $update_res = D('OcB2bOrder')->where($where)->save($data);
        if ($update_res <= 0 || $update_res === FALSE) {
            return $this->res(NULL, 6022);
        }

        //订单操作记录
        $apiPath = "Base.OrderModule.B2b.OrderAction.orderActionUp";
        $data = array(
            'b2b_code' => $order_res['response']['b2b_code'],
            'uc_code' => $order_res['response']['uc_code'],
            'status' => OC_ORDER_GROUP_STATUS_UNSHIP,
            'pay_method' => $pay_method,
            'pay_type' => $pay_type,
        );
        $action_res = $this->invoke($apiPath, $data);
        if ($action_res['status'] != 0) {
            return $this->res(null, $action_res['status']);
        }

        //发送支付微信提醒
        //    $pay_method = $order_res['response']['pay_method'];
        if ($pay_method == PAY_METHOD_ONLINE_ALIPAY) {
            $pay_method_message = '支付宝支付';
        } else if ($pay_method == PAY_METHOD_ONLINE_WEIXIN) {
            $pay_method_message = '微信支付';
        }

        $data = array(
            'order_sn' => $order_res['response']['b2b_code'],
            'pay_time' => NOW_TIME,
            'pay_type' => $pay_method_message,
            'pay_price' => $params['amount'],
//            'url_info' => C('DEFAULT_WEIXIN_URL') . 'OrderInfo/Index2/orderid/' . $params['op_code'] . '.html',
            'url_info' => C('STATIC_DOMAIN') . "static/views/order/orderDetail.html?id={$order_res['response']['b2b_code']}",
            'uc_code' => $order_res['response']['uc_code'],
        );
        $this->push_queue('Base.OrderModule.B2b.OrderInfo.payBackto', $data, 0);
        //查询订单信息
        $orderInfo = D('OcB2bOrder')->where(array('op_code' => $params['op_code']))->field('b2b_code as oc_code,op_code,real_amount as total_fee,pay_type,sc_code,salesman_id,ship_method')->master()->select();
        foreach ($orderInfo as $k => $v) {
            $orderInfo[$k]['oc_type'] = OC_B2B_GOODS_ORDER;
        }
        //获取扩展信息
        $orderExtendInfo = D('OcB2bOrderExtend')->where(array('op_code' => $orderInfo[0]['op_code']))->find();
        $orderInfo[0]['real_name'] = $orderExtendInfo['real_name'];
        $orderInfo[0]['mobile'] = $orderExtendInfo['mobile'];
        $orderInfo[0]['commercial_name'] = $orderExtendInfo['commercial_name'];
        $orderInfo[0]['pay_time']        = NOW_TIME;
        $orderInfo[0]['pay_price']       = $orderExtendInfo['total_real_amount'];
        $orderInfo[0]['uc_code']         = $order_res['response']['uc_code'];
        $orderInfo[0]['pick_up_code']    = $orderExtendInfo['pick_up_code'];
        $orderInfo[0]['coupon_code']     = $orderExtendInfo['coupon_code'];

        //添加客户 信息
        $this->updateCustomerOrderInfo($order_res['response']['sc_code'], $order_res['response']['uc_code'], 1, $params['amount']);
        return $this->res($orderInfo);
    }

    /**
     * 支付回调异步队列
     * Base.OrderModule.B2b.OrderInfo.payBackto
     * @param type $params
     */
    public function payBackto($param)
    {
        $param = $param['message'];
        //获取OPENID
        $api = 'Base.WeiXinModule.User.User.getWeixinInfo';
        $res = $this->invoke($api, array('uc_code' => $param['uc_code']));
        $param['openid'] = $res['response']['open_id'];
        if ($res['status'] != 0)
            return $this->res(null, $res['status']);
        $api = 'Com.Common.Message.WxTpl.payOrder';
        $respon = $this->invoke($api, $param);
        return $this->res($respon['response'], $respon['status']);
    }

    /**
     * 如果是取消订单   库存回滚
     */
    private function rollbackStock($orderGoodsInfo, $orderGoodsGift)
    {

        $apiPath = "Base.StoreModule.Item.Stock.changeStock";
        foreach ($orderGoodsInfo as $k => $v) {
            $params = array(
                'sic_code' => $v['sic_code'],
                'number' => $v['goods_number'],
                'sc_code' => $v['sc_code'],
            );
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke(NULL, $res['status']);
            }
        }
        //回滚赠品库存
        if (!empty($orderGoodsGift)) {
            foreach ($orderGoodsGift as $k => $v) {
                $params = array(
                    'sic_code' => $v['sic_code'],
                    'number' => $v['goods_number'],
                    'sc_code' => $v['sc_code'],
                );
                $res = $this->invoke($apiPath, $params);
                if ($res['status'] != 0) {
                    return $this->endInvoke(NULL, $res['status']);
                }
            }
        }

        return true;
    }

    //更新客户的下单金额  和  下单数量
    private function updateCustomerOrderInfo($sc_code,$uc_code,$orders,$amount){
        //判断是什么用户
        $apiPath = "Base.UserModule.Customer.Customer.get";
        $params = array(
            'uc_code'=>$uc_code,
        );
        $customer_res = $this->invoke($apiPath, $params);
        if($customer_res['status'] != 0){
            return ;
        }
        
         $invite_from  =  $customer_res['response']['invite_from'];
        if($invite_from == 'UC'){
            //如果是  平台邀请  则直接返回
            return ;
        }
        
        $apiPath = "Base.UserModule.Customer.Customer.update";
        $data = array(
            'sc_code' => $sc_code,
            'uc_code' => $uc_code,
            'orders' => $orders,
            'order_amount' => $amount,
        );
        if ($orders > 0) {
            $data['order_time'] = NOW_TIME;
        }
        $res = $this->invoke($apiPath, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return TRUE;
    }

    private function _getGoods($id)
    {
        $where['b2b_code'] = is_array($id) ? array('in', $id) : array('eq', $id);
        $result = D('OcB2bOrderGoods')->where($where)->select();
        return $result;
    }

    private function _getGiftByB2b($b2b)
    {
        $where['b2b_code'] = is_array($b2b) ? array('in', $b2b) : array('eq', $b2b);
        return D('OcB2bOrderGift')->where($where)->select();
    }

    private function _getGoodByCode($b2b_code, $goodslist)
    {
        $goods = array();
        foreach ($goodslist as $k => $v) {
            if ($v['b2b_code'] == $b2b_code) {
                $goods[] = $goodslist[$k];
            }

        }
        return $goods;
    }

    private function _getSpcByB2b($op_code, $spc)
    {
        if (!empty($spc)) {
            foreach ($spc as $k => $v) {
                if ($v['b2b_code'] == $op_code) {
                    return $spc[$k];
                }
            }
        } else {
            return array();
        }
    }


    /**
     * Base.OrderModule.B2b.OrderInfo.gift
     * 根据spc_code 获取 其基本信息
     * @param  type $spc_code
     * @param  type $b2b_code
     * @access public
     * @author Todor
     */

    public function gift($params)
    {

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),                      # 店铺编码
            array('spc_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'), # 促销编码
            array('salesman', 'require', PARAMS_ERROR, ISSET_CHECK),                     # 业务员
            array('customer', 'require', PARAMS_ERROR, ISSET_CHECK),                     # 客户
            array('uc_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),  # 客户编码
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }


        //促销效果的订单状态
        $order_status_where = array(
            array('obo.pay_status' => OC_ORDER_PAY_STATUS_PAY, 'obo.pay_method' => array('neq', PAY_METHOD_OFFLINE_COD)),
            array('obo.order_status' => array('not in', array(OC_ORDER_ORDER_STATUS_CANCEL, OC_ORDER_ORDER_STATUS_MERCHCANCEL)), 'obo.pay_method' => PAY_METHOD_OFFLINE_COD),
            '_logic' => 'or',
        );
        $where['_complex'] = $order_status_where;

        !empty($params['spc_codes']) && $where['obog.spc_code'] = array('in', $params['spc_codes']);

        !empty($params['customer']) && $where['obo.client_name'] = $params['customer'];
        !empty($params['salesman']) && $where['obo.salesman_id'] = $params['salesman'];
        !empty($params['sc_code']) && $where['obo.sc_code'] = $params['sc_code'];

        $group = "obog.spc_code";

        if (!empty($params['customer']) || $params['type'] == 'detail') {   # 有客户 或者导出为详细
            $group = 'obog.spc_code,obo.uc_code';
            !empty($params['uc_codes']) && $where['obo.uc_code'] = array('in', $params['uc_codes']);
        }

        $data = D('OcB2bOrderGift')->alias('obog')
            ->field('obog.*,SUM(obog.goods_number) as goods_number,obo.client_name,obo.uc_code')
            ->where($where)
            ->join("{$this->tablePrefix}oc_b2b_order obo on obo.b2b_code = obog.b2b_code", 'LEFT')
            ->group($group)
            ->select();

        return $this->res($data);
    }

    /**
     * Base.OrderModule.B2b.OrderInfo.changePayMethod
     * 修改支付方式
     */
    public function changePayMethod($params)
    {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK),                      # 订单编码
            array('pay_method', 'require', PARAMS_ERROR, MUST_CHECK),                      # 支付类型
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),                      # 用户编码
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $op_code = $params['op_code'];
        $pay_method = $params['pay_method'];
        $uc_code = $params['uc_code'];

        $res = D('OcB2bOrder')->where(array('uc_code' => $uc_code, 'op_code' => $op_code))->save(array('update_time' => NOW_TIME + 1, 'pay_method' => $pay_method));
        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 6026);
        }
        return $this->res(TRUE);
    }

    /**
     * Base.OrderModule.B2b.OrderInfo.remit
     */
    public function remit($params)
    {
        $this->_rule = array(
            array('b2b_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), # 订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $b2b_codes = $params['b2b_codes'];

        $apiPath = "Base.OrderModule.B2b.OrderAction.orderActionUp";
        foreach($b2b_codes as $k=>$b2b_code){
            //获取该订单的coupon_code
            $extend_info = D('OcB2bOrder')->alias('obo')->join("{$this->tablePrefix}oc_b2b_order_extend oboe on obo.op_code=oboe.op_code","left")->where(['obo.b2b_code'=>$b2b_code])->find();
            $coupon_code = $extend_info['coupon_code'];
            if($coupon_code){
                $use_time = NOW_TIME;
                $coupon_data = [
                    'coupon_code'=>$coupon_code,
                    'use_time'=>$use_time
                ];
                $res = $this->invoke('Base.UserModule.Coupon.Coupon.setStatus',$coupon_data);
                if($res['status']!==0){
                    return $this->endInvoke('',$res['status']);
                }
            }
            //订单操作记录
            $data = array(
                'b2b_code' => $b2b_code,
                'uc_code' => '',
                'status' => OC_ORDER_GROUP_STATUS_UNSHIP,
                'pay_method' => PAY_METHOD_ONLINE_REMIT,
            );
            $action_res = $this->invoke($apiPath, $data);
            if ($action_res['status'] != 0) {
                return $this->res(null, $action_res['status']);
            }
        }

        $model = M('Base.OrderModule.B2b.Status');
        $prev_status = $model->groupToDetail(OC_ORDER_GROUP_STATUS_UNPAY, 'NOT_COD');
        $where = $prev_status;
        $where['b2b_code'] = array('in', $b2b_codes);
        $where['pay_method'] = PAY_METHOD_ONLINE_REMIT;
        $res = D('OcB2bOrder')->where($where)->save(array('update_time' => NOW_TIME, 'pay_time' => NOW_TIME, 'pay_status' => OC_ORDER_PAY_STATUS_PAY));

        return $this->res($res);
    }

    /*
    * Base.OrderModule.B2b.OrderInfo.getOrderOneData
    * 
    */
    public function getOrderOneData($params)
    {
        $count = D('ocB2bOrder')->field($params['field'])->where($params['where'])->find();
        return $count;
    }

    /*
   * Base.OrderModule.B2b.OrderInfo.isShiped
   *
   */
    public function isShiped($params)
    {
        $where = $this->buildStatusWhere('UNSHIP');
        $params['where']['_complex'] = $where;
        $data = $this->getOrderOneData($params);
        return $this->res($data);
    }


    /**
     * 更新订单汇款码
     * Base.OrderModule.B2b.OrderInfo.setRemitCode
     * 修改支付方式
     */
    public function setRemitCode($params)
    {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, ISSET_CHECK),                      # 订单编码
            array('remit_code', 'require', PARAMS_ERROR, MUST_CHECK),                      # 汇款码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),                      # 用户编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),                      # 用户编码
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $op_code = $params['op_code'];
        $remit_code = $params['remit_code'];
        $uc_code = $params['uc_code'];
        $b2b_code = $params['b2b_code'];
        $where = array('uc_code' => $uc_code);
        !empty($op_code) && $where['op_code'] = $op_code;
        !empty($b2b_code) && $where['b2b_code'] = $b2b_code;
        //查询是否有权限操作该订单
        $order_info = D('OcB2bOrder')->where($where)->field('b2b_code,op_code')->find();
        $op_code = $order_info['op_code'];
        if (empty($order_info)) {
            return $this->res(NULL, 6021);
        }

        $res = D('OcB2bOrderExtend')->where(array('op_code' => $op_code))->save(array('update_time' => NOW_TIME, 'remit_code' => $remit_code));
        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 6030);
        }
        return $this->res(TRUE);
    }

    public function pickUpCode($params)
    {

        $this->_rule = array(
            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK),   # 订单编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $op_code = $params['op_code'];
        $uc_code = $params['uc_code'];
        $where = array('uc_code' => $uc_code);
        !empty($op_code) && $where['b2b_code'] = $op_code;
        //查询是否有权限操作该订单
        $order_info = D('OcB2bOrder')->where($where)->field('b2b_code,op_code')->find();

        $op_code = $order_info['op_code'];
        if (empty($order_info)) {
            return $this->res(NULL, 6021);
        }

        $res = D('OcB2bOrderExtend')->field('pick_up_code')->where(array('op_code' => $op_code))->find();
        if ($res === FALSE) {
            return $this->res(NULL, 6030);
        }
        $res['b2b_code'] = $order_info['b2b_code'];
        return $this->res($res);
    }

    public function isPaySuccess($params)
    {
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),   # 订单编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $b2b_code = $params['b2b_code'];
        $uc_code = $params['uc_code'];
        $where = array('uc_code' => $uc_code, 'b2b_code' => $b2b_code, 'pay_status' => OC_ORDER_PAY_STATUS_PAY, 'pay_type' => PAY_TYPE_ONLINE, 'ship_method' => SHIP_METHOD_PICKUP);
        $order_info = D('OcB2bOrder')->where($where)->find();
        if (empty($order_info)) {
            return $this->res(NULL, 6021);
        } else {
            return $this->res(null, 0);
        }
    }

    /*＊
     * @desc   ： BOSS v1.3使用 扫码校验通过，返回商家的订单编码
     * @date   ： 2015-12-23
     * @author ： heweijun@liangrenwang.com
     * @input params ： pick_up_code
     * @output params ： 成功返回订单详情
     * @apipath       : Base.OrderModule.B2b.OrderInfo.checkPickUpCode
     * @fixed  ： 添加商家的判断  2016-1-4
     */
    public function checkPickUpcode($params){
        $this->_rule = array(
            array('pick_up_code', 'require', PARAMS_ERROR, MUST_CHECK),  //提货码
//            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),   //用户信息
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),   //商家信息
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //取货码状态判断
        $pick_up_code = trim($params['pick_up_code']);

//        $uc_code = !empty($params['uc_code']) ? trim($params['uc_code']) : '';
        $sc_code = !empty($params['sc_code']) ? trim($params['sc_code']) : '';

        $_SERVER['HTTP_USER_AGENT'] = POP;
        $order_extend = $order_info = null;
        if(!empty($pick_up_code)) {
            #根据取货码，获取唯一的订单编号 op_code
            $code = D('OcB2bOrderExtend')->where(array('pick_up_code' => $pick_up_code))->find();
            if (empty($code)) {
                $data['status'] = 2;
                return $this->res($data, 0);
            }
            $order_extend = $code;
            #根据订单标号，和商家编号获取订单信息
            $order_info = D('OcB2bOrder')->where(array('op_code' => $code['op_code']))->find();

//            if($uc_code != $order_info['uc_code'] || $sc_code != $order_info['sc_code']){
            #商家校验，如果非登陆的商家，则提示验证码错误
            if($sc_code != $order_info['sc_code']){
                 $data['status'] = 2;
                 $this->endInvoke($data,0);
            }

            if (empty($order_info)) {
                $data['status'] = 2;
                return $this->res($data, 0);
            }
            if ($order_info['ship_status'] == OC_ORDER_SHIP_STATUS_TAKEOVER) {
                $res = $this->res($data, 6062);
                $data = array(
                    'b2b_code' => $order_info['b2b_code'],  //订单编号
                    'status'   => 1,                        //验证失败
                );
                #根据订单标号，和商家编号获取订单信息
                $this->endInvoke($data, 0,$res['message']);
            }
        }
        if (empty($order_info)) {
            $data['status'] = 1;
            return $this->res($data, 0);
        }
        if ($order_extend === null) {
            $order_extend = D('OcB2bOrderExtend')->where(array('op_code' => $order_info['op_code']))->find();
        }
        if (empty($order_extend)) {
            $data['status'] = 1;
            return $this->res($data, 0);
        }

        //返回用户订单的b2b_code  校验通过，做更新订单状态的操作，并返回订单信息 fixed 2015-12-25
        $data = array(
            'b2b_code' => $order_info['b2b_code'],  //订单编号
            'sc_code' => $order_info['sc_code'],    //店铺编号
            'uc_code' => $order_info['uc_code'],
        );

        $res = $this->invoke('Base.OrderModule.B2b.OrderInfo.updateOrderStatus',$data);
        $resdata = array(
            'b2b_code' => $order_info['b2b_code'],  //订单编号
        );
        if ( $res['status'] != 0) {
            $resdata = array('status'   => 1);
            return $this->endInvoke($resdata, 0);
        }
        $resdata = array('status'   => $res['status']);
        return $this->endInvoke($resdata, $res['status']);

    }


    /*＊
     ＊@desc   ： BOSS APP1.3 修改订单的状态使用
     ＊@date   ： 2015－12-23
     ＊@author ： heweijun@liangrenwang.com
     *@apipath :  Base.OrderModule.B2b.OrderInfo.updateOrderStatus
     */
    public function updateOrderStatus($params){
        $this->_rule = array(
            array('uc_code',  'require', PARAMS_ERROR, ISSET_CHECK),   //用户编码
            array('sc_code',  'require', PARAMS_ERROR, ISSET_CHECK),   //店铺编码
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),      //订单编号
        );
        $this->_request_sys_name = POP;
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //取货码状态判断
        $uc_code  = $params['uc_code'];
        $sc_code  = $params['sc_code'];
        $b2b_code = $params['b2b_code'];

        #获取订单的信息，并判断
        $where['uc_code']  =  !empty($uc_code) ?  $uc_code : '';
        $where['sc_code']  =  !empty($sc_code) ?  $sc_code : '';
        $where['b2b_code'] =  !empty($b2b_code) ? $b2b_code : '';
        $where = array_filter($where);
        $info = D('OcB2bOrder')->where($where)->find();
        //获取订单数据
        $status = $info['pay_type'] == PAY_TYPE_ONLINE ? OC_ORDER_GROUP_STATUS_COMPLETE : OC_ORDER_GROUP_STATUS_TAKEOVER;
        $params = array(
            'status'   => $status,
            'b2b_code' => $info['b2b_code'],
            'sc_code'  => $info['sc_code'],
            'uc_code'  => $info['uc_code'],
        );
        $pop_uc_code = $params['uc_code'];
        if (isset($params['uc_code'])) unset($params['uc_code']);
        try {
            D()->startTrans();
            $apiPath = "Base.OrderModule.B2b.OrderInfo.operate";
            $add_res = $this->invoke($apiPath, $params);
            if ($add_res['status'] != 0) {
                return $this->endInvoke(NULL, $add_res['status'], '', $add_res['message']);
            }

            $params['pay_method'] = $add_res['response']['order_info']['pay_method'];
            $params['pay_type'] = $add_res['response']['order_info']['pay_type'];
            $params['uc_code'] = $pop_uc_code;
            $params['ship_method'] = $info['ship_method'];

            #更新订单的状态操作
            $api = 'Base.OrderModule.B2b.OrderAction.orderActionUp';
            $res = $this->invoke($api,$params);
            if ($res['status'] != 0) {
                return $this->endInvoke(null,$res['status']);
            }

            //订单商品信息
            $order_goods = $add_res['response']['order_goods'];
            $uc_code = $add_res['response']['order_info']['uc_code'];
            $mobile = $add_res['response']['order_info']['mobile'];
            unset($params['uc_code']);
            //促销商品需要回滚购买数量
            $data = array();
            foreach($order_goods as $k=>$v){
                if(!empty($v['spc_code'])){
                    $data[] = array(
                        'spc_code'=>$v['spc_code'],
                        'number' => $v['goods_number'],
                        'uc_code'=>$uc_code,
                        'spc_type'=>$v['spc_type'],
                    );
                }
            }
            if(!empty($data) && $params['status'] == OC_ORDER_GROUP_STATUS_CANCEL){
                $this->rollbackSpcBuyLimit($data);
            }

            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }

            if($params['status'] == OC_ORDER_GROUP_STATUS_SHIPPED){
                //如果是发或  则发送发货短息
                $this->shipMessage($params['b2b_code'],$mobile);
            }
            #成功获取订单信息，获取订单详情
            $_SERVER['HTTP_USER_AGENT'] = POP;
            $res = $this->invoke('Base.OrderModule.B2b.OrderInfo.get',array('b2b_code'=>$info['b2b_code'], "sc_code"=>$info['sc_code']));
            if ( $res['status'] != 0) {
                return $this->endInvoke($res['message'],$res['status']);
            }
//            return $this->endInvoke($res['response']);
            return $this->endInvoke(array('b2b_code'=>$info['b2b_code'], "status"=>$res['status']));
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 6022);
        }
    }
    
    /**
     * 获取订单单号
     * Base.OrderModule.B2b.OrderInfo.getOrderOpCode
     * 修改支付方式
     */
    public function getOrderOpCode($params){
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),   # 订单编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        $res = D('OcB2bOrder')->field('b2b_code,real_amount')->where(array('op_code'=>$params['op_code'],'uc_code'=>$params['uc_code']))->find();
        return $this->res($res);
    }

}


