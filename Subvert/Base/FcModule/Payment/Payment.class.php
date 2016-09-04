<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangxuemei <wangxuemei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 财务审单、制单、付款
 */
namespace Base\FcModule\Payment;

use System\Base;

class Payment extends Base{

    public function __construct() {
        parent::__construct();
    }

    /**
     * Base.FcModule.Payment.Payment.accountPaidLists
     * 获取当前已经付过款的数据
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function accountPaidLists($params) {
        $this->_rule = array(
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			   非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			   非必须参数, 默认值 所有
            array('shopName', 'require', PARAMS_ERROR, ISSET_CHECK),         //  店铺名称			   非必须参数, 默认值 所有
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),         //  付款编号			   非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示的数量			非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $checkWhere = array();
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        !empty($start_time) && empty($end_time) && $checkWhere['affirm_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $checkWhere['affirm_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $checkWhere['affirm_time'] = array('between', array($start_time, $end_time+ 86400));

        if(!empty($params['fc_code'])){
            $checkWhere['fc_code'] = $params['fc_code'];
        }

        if(!empty($params['bank'])) {
            $checkWhere[] = "bank_type = '" . $params['bank'] ."'";
        }

        if(!empty($params['sc_name'])){
            $checkWhere['sc_name'] = $params['sc_name'];
        }

        # 固定条件
        $checkWhere[] = "make_time is not null";
        $checkWhere['status'] = FC_STATUS_PAYMENT;
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $fileConfirm = 'bank_type,fc_code,amount,sc_name,account_name,account_bank,account_number,status,make_time as create_time,affirm_time,create_name,affirm_name,status';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getFcPaymentList';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "affirm_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $checkWhere;
        $orderParams['aggre'] = array(
            array('sum','amount','total_amount'),
        );
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response']);
    }
    /**
     * Base.FcModule.Payment.Payment.findConfirmLists
     * 查询当前跟支付编码匹配的订单号
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function findConfirmLists ($params) {
        $this->_rule = array(
            array('fc_code', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function')      //  付款编号			   必须参数
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //拼接条件
        $orderWhere = array();

        if(is_array($params['fc_code'])){
            $fcCode = join(",",$params['fc_code']);
            $orderWhere[] = "foc.fc_code in({$fcCode}) ";
        }else{
            $orderWhere[] = "foc.fc_code in({$params['fc_code']}) ";
        }

        # 固定条件
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;

        $filed = array(
            'foc.fc_code',
            'foc.b2b_code',
            'foc.cost',
            'foc.bank_code',
            'foc.bank_code',
            'foc.confirm_name',
            'foc.check_name',
            'foc.update_time',
            'foc.check_time',
            'foc.f_status',
            'foc.pay_name',
            'foc.oc_type',

            'obo.real_amount',
            'obo.order_amout',
            'obo.pay_time',
            'obo.ext1',
            'obo.pay_method as oboPayMethod',
            'obo.order_type',

            'oboe.real_name obname',
            'oboe.commercial_name as obcommercial_name',
            'oboe.coupon_amount',
            'oa.pay_time as oapay_time',
            'oa.amount',
            'oa.pay_method as oaPayMethod',
            'oa.pay_method_ext1',

            'um.commercial_name as oacommercial_name',
            'um.name as oaname',

            'store.linkman',
            'store.name as sc_name',
            'store.account_type'
        );
        $fileConfirm = join(",", $filed);
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'findConfirmData';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "foc.create_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $orderWhere;
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response']);
    }

    /**
     * Base.FcModule.Payment.Payment.fcPaymentExport.
     * 导出对账全部订单相关明细
     * 接收数据
     */
    public function  fcPaymentExport($params) {
        $this->_rule = array(
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			   非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			   非必须参数, 默认值 所有
            array('shopName', 'require', PARAMS_ERROR, ISSET_CHECK),         //  店铺编号			   非必须参数, 默认值 所有
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),         //  付款编号			   非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $orderWhere = array();
        $start_time = strtotime($params['start_time']);
        $end_time = strtotime($params['end_time']);
        !empty($start_time) && empty($end_time) && $orderWhere['affirm_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $orderWhere['affirm_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $orderWhere['affirm_time'] = array('between', array($start_time, $end_time+ 86400));

        if(!empty($params['fc_code'])){
            $orderWhere['fc_code'] = $params['fc_code'];
        }

        if(!empty($params['bank'])){
            $orderWhere['bank_type'] = $params['bank'];
        }
        if(!empty($params['sc_name'])){
            $orderWhere['sc_name'] = $params['sc_name'];
        }
        $orderWhere['status'] = FC_STATUS_PAYMENT;
        $orderWhere[] = "make_time is not null";
        $data['title'] = array('付款编号', '应付金额', '付款手续费','付款银行','卖家信息', '订单编号', '订单金额', '支付时间','手续费','到账金额','优惠金额','到账流水号(入金)','买家信息','订单类型','点单','审单','制单','付款','付款状态');  //默认导出列标题
        $data['center_flag']  =  SQL_FC;//财务中心
        $apiPath  =  "Com.Common.CommonView.Export.export";
        //组装调用导出api参数
        $data['filename'] = 'fcPaymentExport';
        $data['fields'] = 'bank_type,fc_code,amount,sc_name,account_name,account_bank,account_number,status,make_time as create_time,affirm_time,create_name,affirm_name';
        $data['sql_flag']     =  'getFcPaymentList'; //sql标识
        $data['callback_api'] =  'Com.Callback.Export.FcExport.PaymentPaidList';
        $data['order']        =  "affirm_time desc";
        $data['template_call_api'] = 'Com.Callback.Export.Template.PaymentPaidList';
        $data['where'] = $orderWhere;
        $res = $this->invoke($apiPath, $data);
        return $this->endInvoke($res['response'],$res['status'],'',$res['message']);
    }

    /**
     * Base.FcModule.Payment.Payment.getCount
     * 统计所有未审单的订单
     * 接收数据
     */
    public function getCount($params) {
        $this->_rule = array(
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),       //  要取数据的类型		   非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取前一天23:59:59秒的时间戳
        $where_time =  strtotime(date("Y-m-d",NOW_TIME))-1;
        $confirmWhere[] = "( CASE WHEN b2b.order_type='".OC_ORDER_TYPE_PLATFORM."' THEN (b2b.order_status ='".OC_ORDER_ORDER_STATUS_COMPLETE."' and
         b2b.complete_time<= '".$where_time."') ELSE (b2b.order_status != '') END)";
        //添加默认条件
        $confirmWhere['confirm.status'] = FC_STATUS_ON_CONFIRM;
        $confirmWhere[] = "confirm.confirm_name != '' ";
        $confirmWhere['confirm.f_status'] = FC_F_STATUS_UN_PAYMENT;
        $confirmData = D("FcOrderConfirm")->alias('confirm')
            ->join("LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code")
            ->join("LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code")->field("count(confirm.id) as conCount")->where($confirmWhere)->find();


        $checkWhere[] = "b2b.pay_status = '" . OC_ORDER_PAY_STATUS_PAY . "' OR adv.status = '" . OC_ORDER_PAY_STATUS_PAY . "'"; // 已付款
        $checkWhere[] = "confirm.status=" . FC_STATUS_CONFIRM; // 已审单
        $checkWhere[] = "confirm.f_status = " . FC_F_STATUS_UN_PAYMENT;   // 未汇总
        $checkWhere[] = "confirm.balance_status = '" . FC_BALANCE_STATUS_YES_BALANCE . "'";   // 已结算

        $checkData = D("FcOrderConfirm")->alias('confirm')->join("LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code")->join("LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code")->field("count(confirm.id) as cheCount")->where($checkWhere)->find();


        $data = array_merge($confirmData, $checkData);
        return $this->endInvoke($data);

    }


    /**
     * Base.FcModule.Payment.Payment.getFailCount
     * 统计所有未审单的订单
     * 接收数据
     */

    public function getFailCount($params) {
        $this->_rule = array(
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),       //  要取数据的类型		   非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $checkData = D("FcOrderPayment")->alias('payment')
            ->field("count(payment.id) as failCount")->where("payment.status = 1 AND payment.make_time is not null AND payment.fc_check_status = 'FAIL' AND payment.fc_pay_status = ''")->find();
        return $this->endInvoke($checkData);
    }

    /**Base.FcModule.Payment.Payment.getAllOrder
     *财务审单&制单&付款 全部订单列表数据
     *
     */
    public function getAllOrder($params){
        $this->_rule = array(
            array('pay_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付开始时间    非必须参数
            array('pay_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付结束时间    非必须参数
            array('confirm_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //点单开始时间    非必须参数
            array('confirm_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //点单结束时间    非必须参数
            array('check_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //审单开始时间    非必须参数
            array('check_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //审单结束时间    非必须参数
            array('create_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //制单开始时间    非必须参数
            array('create_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //制单结束时间    非必须参数
            array('affirm_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款开始时间    非必须参数
            array('affirm_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款结束时间    非必须参数
            array('complete_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款结束时间    非必须参数
            array('complete_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款结束时间    非必须参数
            array('order_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单结算状态    非必须参数，中文(已点单,已审单,已制单,已付款)
            array('sc_name', 'require', PARAMS_ERROR, ISSET_CHECK),       //卖家店铺名称    非必须参数
            array('account_bank', 'require', PARAMS_ERROR, ISSET_CHECK),       //到账银行    非必须参数,暂时数据库无存储
            array('order_type', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单类型    非必须参数
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单号    非必须参数
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付方式    非必须参数
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        if(!empty($params['complete_start_time']) && !empty($params['complete_end_time'])){
            $orderWhere['b2b.complete_time'] = array('between', array($params['complete_start_time'], $params['complete_end_time']+ 86400));
            $orderWhere['b2b.order_status'] = OC_ORDER_ORDER_STATUS_COMPLETE;
        }

        if(!empty($params['pay_start_time']) && !empty($params['pay_end_time'])){
            $orderWhere[] = " adv.pay_time between ".$params['pay_start_time']." and ".($params['pay_end_time']+86400)." or
                            b2b.pay_time between ".$params['pay_start_time']." and ".($params['pay_end_time']+86400);
        }
        if(!empty($params['confirm_start_time']) && !empty($params['confirm_end_time'])){
            $orderWhere['confirm.update_time'] = array('between', array($params['confirm_start_time'], $params['confirm_end_time']+ 86400));
        }
        if(!empty($params['check_start_time']) && !empty($params['check_end_time'])){
            $orderWhere['confirm.check_time'] = array('between', array($params['check_start_time'], $params['check_end_time']+ 86400));
        }
        if(!empty($params['make_start_time']) && !empty($params['make_end_time'])){
            $orderWhere['payment.make_time'] = array('between', array($params['make_start_time'], $params['make_end_time']+ 86400));
        }
        if(!empty($params['affirm_start_time']) && !empty($params['affirm_end_time'])){
            $orderWhere['payment.affirm_time'] = array('between', array($params['affirm_start_time'], $params['affirm_end_time']+ 86400));
        }
        if(!empty($params['order_status'])){
            if($params['order_status'] == 'status_'.FC_STATUS_ON_CONFIRM){
                $orderWhere['confirm.status'] = FC_STATUS_ON_CONFIRM;
            }elseif($params['order_status'] == 'status_'.FC_STATUS_CONFIRM){
                $orderWhere['confirm.status'] = FC_STATUS_CONFIRM;
            }elseif($params['order_status'] == 'pay_status_'.FC_STATUS_ON_PAYMENT){
                $orderWhere['payment.status'] = FC_STATUS_ON_PAYMENT;
            }elseif($params['order_status'] == 'pay_status_'.FC_STATUS_PAYMENT ){
                $orderWhere['payment.status'] = FC_STATUS_PAYMENT;
            }
        }
        if(!empty($params['sc_name'])){
            $orderWhere['store.name'] = $params['sc_name'];
        }
        if(!empty($params['order_type'])){
            $orderWhere['b2b.order_type'] = $params['order_type'];
        }
        if(!empty($params['b2b_code'])){
            $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
        }

        if(!empty($params['bank'])){
            if($params['bank'] == PAY_METHOD_REMIT_CMBC){
                $bank_CMB = PAY_METHOD_REMIT_CMB;
                $orderWhere[] = "( b2b.ext1 <>'".$bank_CMB."')";
            } else {
                if($params['pay_method'] == "REMIT"){
                    $orderWhere[] = "( b2b.ext1 = '".$params['bank']."' or adv.pay_method_ext1 = '".$params['bank']."')";
                }else{
                    $bank_CMB = PAY_METHOD_REMIT_CMB;
                    $orderWhere[] = "( b2b.ext1 ='".$bank_CMB."')";
                }
            }
        }

        if(!empty($params['pay_method'])){
            $orderWhere[] = "( b2b.pay_method = '".$params['pay_method']."' or adv.pay_method = '".$params['pay_method']."')";
        }
        //添加默认条件
        //$orderWhere[] = " ( b2b.ext1!= 'CMB' or adv.pay_method_ext1 != 'CMB') and
        $orderWhere[]= "confirm.status >1 ";
        //组装sql语句的条件
        $fileConfirm = 'store.name as sc_name,store.linkman,store.account_name,store.account_no,store.account_bank,store.account_type,
                        adv.pay_time,adv.pay_method as adv_pay_method,adv.pay_method_ext1,adv.amount as adv_amount,adv.status as adv_status,
                        b2b.pay_time as b2b_pay_time,b2b.pay_method,b2b.real_amount as amount,b2b.ext1,b2b.order_type,b2b.complete_time,b2b.order_amout,b2b.order_status,b2b.pay_status,
                        confirm.b2b_code,confirm.oc_type,confirm.bank_code,confirm.cost,confirm.confirm_name,confirm.check_name,confirm.check_time,confirm.update_time,
                        confirm.account_status,confirm.pay_name,confirm.status,confirm.balance_status,
                        extend.real_name,extend.commercial_name,extend.coupon_amount,
                        payment.bank_type,payment.create_name,payment.make_time,payment.affirm_name,payment.affirm_time,payment.bank_code as payment_bank_code,payment.amount as payment_amount,
                        member.commercial_name as adv_c_name,member.name as adv_name';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getAllOrder';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "confirm.update_time DESC";
        $orderParams['page_number'] = $pageNumber;
        //求和总金额
        $aggre = array(
            array('sum','b2b.real_amount','total_b2b_amount'),
            array('sum','extend.coupon_amount','total_coupon_amount'),
            array('sum','adv.amount','total_adv_amount'),
        );
        $orderParams['aggre'] = $aggre;
        $orderRes = $this->invoke($apiPath, $orderParams);
        $orderRes['response']['total_amount'] = $orderRes['response']['total_b2b_amount']+$orderRes['response']['total_adv_amount']+$orderRes['response']['total_coupon_amount'];
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);


    }


    /**
     * Base.FcModule.Payment.Payment.getShopName
     * 商家模糊搜索
     * 接收数据
     */
    public function getShopName($params) {
        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK),       //  商家名称		   非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $name = $params['name'];
        $where[] = "name like '%".$name."%'";
        $data = D("ScStore")->field("name")->where($where)->limit(5)->select();

        return $this->endInvoke($data);

    }


    /**Base.FcModule.Payment.Payment.checkOrder
     *审单列表
     */
    public function checkOrder($params){
        $this->_rule = array(
            array('pay_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付开始时间    非必须参数
            array('pay_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付结束时间    非必须参数
            array('confirm_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //点单开始时间    非必须参数
            array('confirm_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //点单结束时间    非必须参数
            array('check_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //审单开始时间    非必须参数
            array('check_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //审单结束时间    非必须参数
            array('order_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单结算状态    非必须参数，中文(已点单,已审单)
            array('sc_name', 'require', PARAMS_ERROR, ISSET_CHECK),       //卖家店铺名称    非必须参数
            array('account_bank', 'require', PARAMS_ERROR, ISSET_CHECK),       //到账银行    非必须参数,暂时数据库无存储
            array('oc_type', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单类型    非必须参数
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单号    非必须参数
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付方式    非必须参数
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        if(!empty($params['complete_start_time']) && !empty($params['complete_end_time'])){
            $orderWhere['b2b.complete_time'] = array('between', array($params['complete_start_time'], $params['complete_end_time']+ 86400));
            $orderWhere['b2b.order_status'] = OC_ORDER_ORDER_STATUS_COMPLETE;
        }
        if(!empty($params['pay_start_time']) && !empty($params['pay_end_time'])){
            $orderWhere[] = " adv.pay_time between ".$params['pay_start_time']." and ".($params['pay_end_time']+86400)." or
                            b2b.pay_time between ".$params['pay_start_time']." and ".($params['pay_end_time']+86400);
        }
        if(!empty($params['confirm_start_time']) && !empty($params['confirm_end_time'])){
            $orderWhere['confirm.update_time'] = array('between', array($params['confirm_start_time'], $params['confirm_end_time']+ 86400));
        }
        if(!empty($params['check_start_time']) && !empty($params['check_end_time'])){
            $orderWhere['confirm.check_time'] = array('between', array($params['check_start_time'], $params['check_end_time']+ 86400));
        }
        if(!empty($params['sc_name'])){
            $orderWhere['store.name'] = $params['sc_name'];
        }
        if(!empty($params['order_type'])){
            $orderWhere['b2b.order_type'] = $params['order_type'];
        }
        if(!empty($params['b2b_code'])){
            $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
        }
        if(!empty($params['pay_method'])){
            $orderWhere[] = "( b2b.pay_method = '".$params['pay_method']."' or adv.pay_method = '".$params['pay_method']."')";
        }
        if(!empty($params['bank'])){
            if($params['bank'] == PAY_METHOD_REMIT_CMBC){
                $bank_CMB = PAY_METHOD_REMIT_CMB;
                $orderWhere[] = "( b2b.ext1 <>'".$bank_CMB."')";
                } else {
                    if($params['pay_method'] == "REMIT"){
                        $orderWhere[] = "( b2b.ext1 = '".$params['bank']."' or adv.pay_method_ext1 = '".$params['bank']."')";
                    }else{
                        $bank_CMB = PAY_METHOD_REMIT_CMB;
                        $orderWhere[] = "( b2b.ext1 ='".$bank_CMB."')";
                    }
                }
        }
        #排序方式选择
        if($params['type']=='1'){
            $orderParams['order'] = "confirm.check_time DESC";//已审单
            $orderWhere['confirm.status'] = FC_STATUS_CONFIRM;
            $orderWhere['confirm.f_status'] = FC_F_STATUS_UN_PAYMENT;
        }else{
            $orderParams['order'] = "confirm.update_time ASC";//待审单
            $orderWhere['confirm.status'] = FC_STATUS_ON_CONFIRM;
            $orderWhere['confirm.f_status'] = FC_F_STATUS_UN_PAYMENT;
        }
        //获取前一天23:59:59秒的时间戳
        $where_time =  strtotime(date("Y-m-d",NOW_TIME))-1;
        $orderWhere[] = "( CASE WHEN b2b.order_type='".OC_ORDER_TYPE_PLATFORM."' THEN (b2b.order_status ='".OC_ORDER_ORDER_STATUS_COMPLETE."' and  b2b.complete_time<= '".$where_time."')
                        ELSE (b2b.order_status != '') END)";
        //添加默认条件
        // (  b2b.ext1!= 'CMB' or adv.pay_method_ext1 != 'CMB' ) and
        $orderWhere[] = "confirm.status > 1 ";
        $orderWhere[] = "confirm.confirm_name != '' ";
        //组装sql语句的条件
        $fileConfirm = 'store.name as sc_name,store.linkman,store.account_name,store.account_no,store.account_bank,store.account_type,
                        adv.pay_time,adv.pay_method as adv_pay_method,adv.pay_method_ext1,adv.amount as adv_amount,adv.status as adv_status,
                        b2b.pay_time as b2b_pay_time,b2b.pay_method,b2b.real_amount as amount,b2b.order_amout,b2b.ext1,b2b.order_type,b2b.complete_time,b2b.order_status,b2b.pay_status,
                        confirm.b2b_code,confirm.oc_type,confirm.bank_code,confirm.cost,confirm.confirm_name,confirm.check_name,confirm.check_time,
                        confirm.update_time,confirm.account_status,confirm.pay_name,confirm.status,confirm.balance_status,
                        extend.real_name,extend.commercial_name,extend.coupon_amount,
                        payment.create_name,payment.make_time,payment.affirm_name,payment.affirm_time,payment.bank_code as payment_bank_code,payment.amount as payment_amount,
                        member.commercial_name as adv_c_name,member.name as adv_name';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getAllOrder';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['page_number'] = $pageNumber;
        //求和总金额
        $aggre = array(
            array('sum','b2b.real_amount','total_b2b_amount'),
            array('sum','extend.coupon_amount','total_coupon_amount'),
        );
        $orderParams['aggre'] = $aggre;
        $orderRes = $this->invoke($apiPath, $orderParams);
        $orderRes['response']['total_amount'] = bcadd($orderRes['response']['total_b2b_amount'],$orderRes['response']['total_coupon_amount'],2);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);
    }

    /**Base.FcModule.Payment.Payment.upCheck
     *审单操作
     */
    public function upCheckOrder($params){
        $this->_rule = array(
            array('b2b_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),//订单号    必须参数
            array('user_name', 'require', PARAMS_ERROR, MUST_CHECK),//点单人姓名    必须参数
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['b2b_code'] = array('in',$params['b2b_code']);
        $where['status'] = FC_STATUS_ON_CONFIRM;
        $updateData['status'] = FC_STATUS_CONFIRM;
        $updateData['check_time'] = NOW_TIME;
        $updateData['check_name'] = $params['user_name'];
        $updateInfo = D('FcOrderConfirm')->where($where)->save($updateData);
        $success_num =$updateInfo;
        $error_num = count($params['b2b_code'])-$updateInfo;
        $res = array(
            'success_num'=>$success_num,
            'error_num'=>$error_num,
        );
        if(!empty($updateInfo)){
            return $this->res($res);
        }else{
            return $this->res(null,8090);
        }

    }

    /**Base.FcModule.Payment.Payment.allOrderExport
     *导出审单&制单&付款 全部订单
     */
    public function allOrderExport($params){
        $this->_rule = array(
            array('pay_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付开始时间    非必须参数
            array('pay_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付结束时间    非必须参数
            array('confirm_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //点单开始时间    非必须参数
            array('confirm_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //点单结束时间    非必须参数
            array('check_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //审单开始时间    非必须参数
            array('check_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //审单结束时间    非必须参数
            array('create_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //制单开始时间    非必须参数
            array('create_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //制单结束时间    非必须参数
            array('affirm_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款开始时间    非必须参数
            array('affirm_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款结束时间    非必须参数
            array('complete_start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款结束时间    非必须参数
            array('complete_end_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //付款结束时间    非必须参数
            array('order_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单结算状态    非必须参数，中文(已点单,已审单,已制单,已付款)
            array('sc_name', 'require', PARAMS_ERROR, ISSET_CHECK),       //卖家店铺名称    非必须参数
            array('account_bank', 'require', PARAMS_ERROR, ISSET_CHECK),       //到账银行    非必须参数,暂时数据库无存储
            array('order_type', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单类型    非必须参数
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //订单号    非必须参数
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //支付方式    非必须参数
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if(!empty($params['complete_start_time']) && !empty($params['complete_end_time'])){
            $orderWhere['b2b.complete_time'] = array('between', array(strtotime($params['complete_start_time']), strtotime($params['complete_end_time'])+ 86400));
            $orderWhere['b2b.order_status'] = OC_ORDER_ORDER_STATUS_COMPLETE;
        }
        if(!empty($params['pay_start_time']) && !empty($params['pay_end_time'])){
            $orderWhere[] = " adv.pay_time between ".strtotime($params['pay_start_time'])." and ".(strtotime($params['pay_end_time'])+86400)." or
                            b2b.pay_time between ".strtotime($params['pay_start_time'])." and ".(strtotime($params['pay_end_time'])+86400);
        }
        if(!empty($params['confirm_start_time']) && !empty($params['confirm_end_time'])){
            $orderWhere['confirm.update_time'] = array('between', array(strtotime($params['confirm_start_time']), strtotime($params['confirm_end_time'])+ 86400));
        }
        if(!empty($params['check_start_time']) && !empty($params['check_end_time'])){
            $orderWhere['confirm.check_time'] = array('between', array(strtotime($params['check_start_time']), strtotime($params['check_end_time'])+ 86400));
        }
        if(!empty($params['make_start_time']) && !empty($params['make_end_time'])){
            $orderWhere['payment.make_time'] = array('between', array(strtotime($params['make_start_time']), strtotime($params['make_end_time'])+ 86400));
        }
        if(!empty($params['affirm_start_time']) && !empty($params['affirm_end_time'])){
            $orderWhere['payment.affirm_time'] = array('between', array(strtotime($params['affirm_start_time']), strtotime($params['affirm_end_time'])+ 86400));
        }
        if(!empty($params['order_status'])){
            if($params['order_status'] == 'status_'.FC_STATUS_ON_CONFIRM){
                $orderWhere['confirm.status'] = FC_STATUS_ON_CONFIRM;
            }elseif($params['order_status'] == 'status_'.FC_STATUS_CONFIRM){
                $orderWhere['confirm.status'] = FC_STATUS_CONFIRM;
            }elseif($params['order_status'] == 'pay_status_'.FC_STATUS_ON_PAYMENT){
                $orderWhere['payment.status'] = FC_STATUS_ON_PAYMENT;
            }elseif($params['order_status'] == 'pay_status_'.FC_STATUS_PAYMENT ){
                $orderWhere['payment.status'] = FC_STATUS_PAYMENT;
            }
        }
        if(!empty($params['sc_name'])){
            $orderWhere['store.name'] = $params['sc_name'];
        }
        if(!empty($params['order_type'])){
            $orderWhere['b2b.order_type'] = $params['order_type'];
        }
        if(!empty($params['b2b_code'])){
            $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
        }
        if(!empty($params['bank'])){
            if($params['bank'] == PAY_METHOD_REMIT_CMBC){
                $bank_CMB = PAY_METHOD_REMIT_CMB;
                $orderWhere[] = "( b2b.ext1 <>'".$bank_CMB."')";
            } else {
                if($params['pay_method'] == "REMIT"){
                    $orderWhere[] = "( b2b.ext1 = '".$params['bank']."' or adv.pay_method_ext1 = '".$params['bank']."')";
                }else{
                    $bank_CMB = PAY_METHOD_REMIT_CMB;
                    $orderWhere[] = "( b2b.ext1 ='".$bank_CMB."')";
                }
            }
        }
        if(!empty($params['pay_method'])){
            $orderWhere[] = "( b2b.pay_method = '".$params['pay_method']."' or adv.pay_method = '".$params['pay_method']."')";
        }
        //添加默认条件
        // 开启招商银行 ( b2b.ext1!= 'CMB' or adv.pay_method_ext1 != 'CMB') and
        $orderWhere[] = " confirm.status >1 ";
        $data['where'] = $orderWhere;
        $data['title'] = array('序号','订单编号','订单金额','支付时间','支付方式','订单状态','订单完成时间','到账银行','到账流水号','付款方','买家名称','买家店铺名称','手续费金额','平台账户到账金额','优惠金额',
            '应付金额','付款手续费','付款银行','付款流水号','卖家店铺名称','卖家开户行名','卖家银行卡开户行','卖家银行卡号','订单类型','点单人','点单时间',
            '审单人','审单时间','制单人','制单时间','付款人','付款时间');
        //组装sql语句的条件
        $fileConfirm = 'store.name as sc_name,store.linkman,store.account_name,store.account_no,store.account_bank,
                        adv.pay_time,adv.pay_method as adv_pay_method,adv.pay_method_ext1,adv.amount as adv_amount,adv.status as adv_status,
                        b2b.pay_time as b2b_pay_time,b2b.pay_method,b2b.real_amount as amount,b2b.ext1,b2b.order_type,b2b.order_amout,b2b.complete_time,b2b.order_status,b2b.pay_status,
                        confirm.b2b_code,confirm.oc_type,confirm.bank_code,confirm.cost,confirm.confirm_name,confirm.check_name,confirm.check_time,
                        confirm.update_time,confirm.account_status,confirm.pay_name,confirm.status,confirm.balance_status,
                        extend.real_name,extend.commercial_name,extend.coupon_amount,
                        payment.bank_type,payment.create_name,payment.make_time,payment.affirm_name,payment.affirm_time,payment.bank_code as payment_bank_code,payment.amount as payment_amount,
                        member.commercial_name as adv_c_name,member.name as adv_name';
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $data['filename'] = $params['fc_type'];
        $data['callback_api'] =  'Com.Callback.Export.FcExport.allOrderList';
        $data['template_call_api'] = 'Com.Callback.Export.Template.aOLists';
        $data['center_flag'] = SQL_FC;
        $data['sql_flag'] = 'getAllOrder';
        $data['where'] = $orderWhere;
        $data['fields'] = $fileConfirm;
        $data['order'] = "confirm.update_time DESC";
        $res = $this->invoke($apiPath, $data);
        return $this->endInvoke($res['response'],$res['status'],$res['message']);
    }

    /**
     * 待制单列表
     * Base.FcModule.Payment.Payment.unCreateOrder
     * @param array $params
     * @return array
     */
    public function unCreateOrder($params) {
        // 检索参数校验
        $this->_rule = array(
            array('payStart', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('payEnd', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('pointStart', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('pointEnd', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('checkStart', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('checkEnd', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('storeName', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('paymethod', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('bank', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('orderType', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('orderCode', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        // 构建sql条件
        // 支付时间: 在线支付,取的是订单的支付成功时间; 银行转账取点单时间;
        if(!empty($params['payStart']) && !empty($params['payEnd'])) {
            $payStart = strtotime($params['payStart'] .' 00:00:00');
            $payEnd = strtotime($params['payEnd'] .' 23:59:59');
            $orderWhere[] = "adv.pay_time BETWEEN " . $payStart . " AND " . $payEnd ." OR b2b.pay_time BETWEEN " . $payStart . " AND " . $payEnd;
        }
        // 点单时间
        if(!empty($params['pointStart']) && !empty($params['pointEnd'])) {
            $pointStart = strtotime($params['pointStart'] .' 00:00:00');
            $pointEnd = strtotime($params['pointEnd'] .' 23:59:59');
            $orderWhere[] = "confirm.update_time BETWEEN " . $pointStart . " AND " . $pointEnd;
        }
        // 审核时间
        if(!empty($params['checkStart']) && !empty($params['checkEnd'])) {
            $checkStart = strtotime($params['checkStart'] .' 00:00:00');
            $checkEnd = strtotime($params['checkEnd'] .' 23:59:59');
            $orderWhere[] = "confirm.check_time BETWEEN " . $checkStart . " AND " . $checkEnd;
        }
        // 支付方式
        if(!empty($params['paymethod'])) {
            $orderWhere[] = "b2b.pay_method = '" . $params['paymethod'] ."' OR adv.pay_method='" . $params['paymethod'] . "'";
        }

        if(!empty($params['storeName'])) {
            $orderWhere[] = "store.name = '" . $params['storeName'] ."' OR member.commercial_name='" . $params['storeName'] . "'";
        }

        if(!empty($params['bank'])){
            if($params['bank'] == PAY_METHOD_REMIT_CMBC){
                $bank_CMB = PAY_METHOD_REMIT_CMB;
                $orderWhere[] = "( b2b.ext1 <>'".$bank_CMB."')";
            } else {
                if($params['pay_method'] == "REMIT"){
                    $orderWhere[] = "( b2b.ext1 = '".$params['bank']."' or adv.pay_method_ext1 = '".$params['bank']."')";
                }else{
                    $bank_CMB = PAY_METHOD_REMIT_CMB;
                    $orderWhere[] = "( b2b.ext1 ='".$bank_CMB."')";
                }
            }
        }

        if(!empty($params['orderType'])) {
            $orderWhere[] = "b2b.order_type = '" . $params['orderType'] ."'";
        }
        if(!empty($params['orderCode'])) {
            $orderWhere[] = "confirm.b2b_code = '" . $params['orderCode'] ."'";
        }
        // 固定条件
        $orderWhere[] = "b2b.pay_status = '" . OC_ORDER_PAY_STATUS_PAY . "' OR adv.status = '" . OC_ORDER_PAY_STATUS_PAY . "'"; // 已付款
        $orderWhere[] = "confirm.status=" . FC_STATUS_CONFIRM; // 已审单
        $orderWhere[] = "confirm.balance_status = '" . FC_BALANCE_STATUS_YES_BALANCE ."'";   // 已结算
        $orderWhere[] = "confirm.f_status = " . FC_F_STATUS_UN_PAYMENT;   // 未汇总
        // 订单金额:商品订单取real_amount, 预付款订单advamount
        // 支付时间:在线支付,取的是订单的支付成功时间；银行转账取点单时间
        $fileConfirm = array(
            'payment.bank_type',
            'confirm.sc_code',      // 订单的卖家店铺编号
            'confirm.b2b_code as b2bcode' , // 订单编号
            'confirm.oc_type',      // 订单类型(GOODS / ADVANCE)
            'confirm.account_status',
            'b2b.pay_method as bmethod',    // GOODS订单的支付方式
            'adv.pay_method as amethod',    // ADVANCE订单的支付方式
            'b2b.pay_time as b2bpaytime',   // 订单的支付时间(GOODS订单)
            'adv.pay_time as advpaytime',   // 订单的支付时间(ADVANCE订单)
            'b2b.real_amount as realamount',    // GOODS订单的订单金额
            'b2b.order_status',
            'b2b.ext1 as b2bext1',
            'adv.pay_method_ext1 as advext1',
            'b2b.pay_status',
            'b2b.complete_time',
            'b2b.order_amout',
            'adv.amount as advamount',          // ADVANCE订单的订单金额
            'adv.status as a_status',
            'confirm.update_time as confirmtime',   // 订单的确认点单时间(如果是银行汇款,则作为支付时间)
            'confirm.cost',     // 订单的手续费
            'confirm.bank_code',    // 订单的交易流水号(入账)
            'member.commercial_name as acname',   // 订单的买家店铺名称(advance)
            'member.name as aname',     // 订单的买家名称
            'extend.commercial_name as gcname',   // 订单的买家店铺名称(goods)
            'extend.real_name as gname',     // 订单的买家名称
            'extend.coupon_amount',// 订单优惠券金额
            'store.name as storename',  // 订单的卖家店铺名称
            'store.username',   // 订单的卖家名称
            'store.account_name',   // 卖家持卡人姓名或企业名称, 银行接口所需
            'store.account_bank',   // 卖家开户行
            'store.account_no',     // 卖家银行账户
            'store.account_code',   // 卖家银行编码, 银行接口所需
            'store.account_type',   // 卖家银行账户类型
            'confirm.confirm_name', // 点单操作人
            'confirm.check_name',   // 审单操作人
            'confirm.check_time',    // 审单时间
            'confirm.pay_name',  // 银行实际付款人(入账)
            'store.linkman', // 卖家联系人
            'b2b.order_type', // 订单类型(平台和非平台)
        );
        $fileConfirm = implode(',', $fileConfirm);
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getCreatOrderInfo';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $params['page'];
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 30;
        $orderParams['order'] = "confirm.check_time asc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $orderWhere;
        $orderParams['aggre'] = array(array('sum','b2b.real_amount','b2b_total_amount'),array('sum','extend.coupon_amount','total_coupon_amount'));
        $orderRes = $this->invoke($apiPath, $orderParams);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        $orderRes['response']['total_amount'] = bcadd($orderRes['response']['b2b_total_amount'],$orderRes['response']['total_coupon_amount'],2);
        return $this->res($orderRes['response']);
    }

    /**
     * 已制单列表 (payment)
     * Base.FcModule.Payment.Payment.hasCreateOrderPayment
     * @param array $params
     * @return array
     */
    public function hasCreateOrderPayment($params) {
        // 检索参数校验
        $this->_rule = array(
            array('storeName', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('bank', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        // 构建sql条件
        // 制单时间
        if(!empty($params['creatStart']) && !empty($params['creatEnd'])) {
            $createStart = strtotime($params['creatStart'] .' 00:00:00');
            $createEnd = strtotime($params['creatEnd'] .' 23:59:59');
            $orderWhere[] = "payment.make_time BETWEEN " . $createStart . " AND " . $createEnd;
        }


        if(!empty($params['bank'])) {
            $orderWhere[] = "payment.bank_type = '" . $params['bank'] ."'";
        }

        if(!empty($params['storeName'])) {
            $orderWhere[] = "payment.sc_name = '" . $params['storeName'] ."'";
        }
        if(!empty($params['fc_code'])) {
            $orderWhere[] = "payment.fc_code = '" . $params['fc_code'] ."'";
        }

        // 固定条件
        $orderWhere[] = "payment.status = " . FC_STATUS_ON_PAYMENT;   // 已制单(已汇总未付款)
        $orderWhere[] = "payment.make_time is not null";
        // 订单金额:商品订单取real_amount, 预付款订单advamount
        $fileConfirm = 'payment.bank_type,payment.make_time,payment.fc_code,payment.amount,payment.sc_name,payment.sc_code,payment.account_name,payment.account_bank,payment.account_number,payment.status,payment.make_time,payment.affirm_time,payment.create_name,store.linkman,store.account_type,store.name as sto_name';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getFcPaymentstoreList';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $params['page'];
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 30;
        $orderParams['order'] = "make_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $orderWhere;
        $orderParams['aggre'] = array(
            array('sum','amount','total_amount'),
        );
        $orderRes = $this->invoke($apiPath, $orderParams);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);
    }

    /**
     * 已制单列表 (order)
     * Base.FcModule.Payment.Payment.hasCreateOrderInfo
     * @param array $params
     * @return array
     */
    public function hasCreateOrderInfo($params) {
        $this->_rule = array(
            array('creatStart', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('creatEnd', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('fc_code', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function')      //  汇总单号			   必须参数
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        // 构建sql条件
        // 制单时间
        if(!empty($params['createStart']) && !empty($params['createEnd'])) {
            $createStart = strtotime($params['createStart'] .' 00:00:00');
            $createEnd = strtotime($params['createEnd'] .' 23:59:59');
            $orderWhere[] = "confirm.create_time BETWEEN " . $createStart . " AND " . $createEnd;
        }

        // 固定条件
        $orderWhere[] = "foc.status=" . FC_STATUS_CONFIRM; // 已审单
        $orderWhere[] = "foc.balance_status = '" . FC_BALANCE_STATUS_YES_BALANCE . "'";   // 已结算
        $orderWhere[] = "foc.f_status = 2";   // 已汇总未付款
        $orderWhere['fc_code'] = array('in', $params['fc_code']);   // 已汇总未付款
        // 订单金额:商品订单取real_amount, 预付款订单amount
        $filed = array(
            'foc.fc_code',
            'foc.b2b_code',
            'obo.order_status',
            'oa.status as a_status',
            'obo.pay_status',
            'obo.complete_time',
            'obo.order_type',
            'obo.order_amout',
            'obo.ext1',
            'foc.cost',
            'foc.bank_code',
            'foc.confirm_name',
            'foc.check_name',
            'foc.update_time',
            'foc.check_time',
            'foc.f_status',
            'foc.pay_name',
            'foc.oc_type',
            'foc.account_status',
            'obo.real_amount',
            'obo.ext1',
            'obo.pay_time as b2bpaytime',
            'oa.pay_time',
            'oboe.real_name',
            'oboe.commercial_name as ocommercial_name',
            'oboe.coupon_amount',
            'oa.pay_time as oapay_time',
            'oa.amount as advamount',
            'um.commercial_name as ucommercial_name',
            'um.name',
            'oa.pay_method as amethod',
            'obo.pay_method as bmethod',
            'store.linkman',
            'store.name as sto_name'
        );
        //$orderParams['page'] = $params['page'];
        //$pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $fileConfirm = join(",", $filed);
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'findConfirmData';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['order'] = "foc.create_time desc";
        $orderParams['page_number'] = ($params['totalnum'] ? $params['totalnum'] : 900);
        $orderParams['where'] = $orderWhere;
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response']);
    }


    /**
     * 单个制单
     * Base.FcModule.Payment.Payment.createOrder
     */
    function createOrder($params) {
        $params['make_time'] = NOW_TIME;  // 制单时间
        $params['create_time'] = NOW_TIME;  // 制单时间
        $this->_rule = array(
            array('sc_code','require',PARAMS_ERROR,MUST_CHECK),             // 店铺编号必须的必
            array('fc_code','require',PARAMS_ERROR,MUST_CHECK),             // 汇总付款编号
            array('amount','require',PARAMS_ERROR,MUST_CHECK),              // 转账支付金额
            array('status','require',PARAMS_ERROR,MUST_CHECK),              // 当前状态
            array('account_bank','require',PARAMS_ERROR,ISSET_CHECK),        // 商户的开户行
            array('account_number','require',PARAMS_ERROR,ISSET_CHECK),      // 银行账号
            array('account_name','require',PARAMS_ERROR,ISSET_CHECK),        // 开户名
            array('uc_code','require',PARAMS_ERROR,ISSET_CHECK),            // 财务编号
            array('create_time','require',PARAMS_ERROR,MUST_CHECK),         // 创建时间
            array('create_name','require',PARAMS_ERROR,ISSET_CHECK),         // 制单人
            array('make_time','require',PARAMS_ERROR,ISSET_CHECK),         // 制单时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $res = D('FcOrderPayment')->data($params)->add();
        $resLists = array(
            'fc_code'=>$params['fc_code'],
            'sc_code'=>$params['sc_code'],
            'amount'=>$params['amount'],
        );
        if($res){
            return $this->res($resLists);
        }else{
            return $this->res(null,7090);
        }
    }

    /**
     * 单个制单拆分
     * Base.FcModule.Payment.Payment.createOrderSplit
     */
    function createOrderSplit($params) {
        $res = D('FcPaymentAccount')->addAll($params);
        $resLists = array(
            'fc_code'=>$params['fc_code'],
            'account_code'=>$params['account_code'],
            'amount'=>$params['amount'],
        );
        if($res){
            return $this->res($resLists);
        }else{
            return $this->res(null,7090);
        }
    }


    /**
     * 批量制单
     * Base.FcModule.Payment.Payment.createOrderS
     */
    function createOrderS($params) {
        $res = D('FcOrderPayment')->addAll($params);
        if($res){
            return $this->res($params);
        }else{
            return $this->res(null,7090);
        }
    }
     /*
      * 重新制单
      * Base.FcModule.Payment.Payment.failCreateOrder
      */
    public function failCreateOrder($params){
        $this->_rule = array(
            array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where = [
                'fc_code'=>$params,
                'fc_check_status'=>'FAIL',
                'fc_pay_status'=>"",
        ];

        $payment = D('FcOrderPayment')->where($where)->find();
        $code = D('FcPaymentAccount')->where($where)->field('account_code')->select();
        foreach($code as $k=>$v){
            $ids[] = $v['account_code'];
        }

        if(!$payment || !$ids){
            return $this->res(null,8101);
        }
        try {
            D()->startTrans();
            $pay = D('FcOrderPayment')->where($where)->save(['fc_check_status'=>null]);
            if($pay <= 0 || $pay === FALSE){
                return $this->res(NULL,8102);
            }

            $where['account_code'] = array('in', $ids);
            $accuont = D('FcPaymentAccount')->where($where)->save(['check_times'=>0]);
            if($accuont <= 0 || $accuont === FALSE){
                return $this->res(NULL,8103);
            }

            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(), $ex->getCode());
        }


        return $this->res(null);


    }

    /**
     * 失败制单列表 (order)
     * Base.FcModule.Payment.Payment.hasCreateOrderInfo
     * @param array $params
     * @return array
     */

    public function failCreateOrderPayment($params) {
        // 检索参数校验
        $this->_rule = array(
            array('storeName', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('bank', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        // 构建sql条件
        // 制单时间
        if(!empty($params['creatStart']) && !empty($params['creatEnd'])) {
            $createStart = strtotime($params['creatStart'] .' 00:00:00');
            $createEnd = strtotime($params['creatEnd'] .' 23:59:59');
            $orderWhere[] = "payment.make_time BETWEEN " . $createStart . " AND " . $createEnd;
        }

        if(!empty($params['bank'])) {
            $orderWhere[] = "payment.bank_type = '" . $params['bank'] ."'";
        }
        if(!empty($params['storeName'])) {
            $orderWhere[] = "payment.sc_name = '" . $params['storeName'] ."'";
        }
        if(!empty($params['fc_code'])) {
            $orderWhere[] = "payment.fc_code = '" . $params['fc_code'] ."'";
        }
        // 固定条件
        $orderWhere[] = "payment.status = " . FC_STATUS_ON_PAYMENT;   // 已制单(已汇总未付款)
        $orderWhere[] = "payment.make_time is not null";
        $orderWhere['payment.fc_check_status'] = "FAIL";
        $orderWhere['payment.fc_pay_status'] = "";
        // 订单金额:商品订单取real_amount, 预付款订单advamount
        $fileConfirm = 'payment.bank_type,payment.make_time,payment.fc_code,payment.amount,payment.sc_name,payment.sc_code,payment.account_name,payment.account_bank,payment.account_number,payment.status,payment.make_time,payment.affirm_time,payment.create_name,store.linkman,store.account_type,store.name as sto_name';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getFcPaymentstoreList';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $params['page'];
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 30;
        $orderParams['order'] = "make_time asc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $orderWhere;
        $orderParams['aggre'] = array(
            array('sum','amount','total_amount'),
        );
        $orderRes = $this->invoke($apiPath, $orderParams);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);
    }
    /**
     * 获取订单数量
     * Base.FcModule.Payment.Payment.countNumber
     */
    public function countNumber($params) {
        $this->_rule = array(
            array('fc_code', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function')      //  付款编号			   必须参数
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //拼接条件
        $orderWhere = array();

        if(is_array($params['fc_code'])){
            $fcCode = join(",",$params['fc_code']);
            $orderWhere[] = "foc.fc_code in({$fcCode}) ";
        }else{
            $orderWhere[] = "foc.fc_code in({$params['fc_code']}) ";
        }

        # 固定条件
        $orderWhere[] = "(obo.ext1!= 'CMB' or oa.pay_method_ext1!= 'CMB' )";
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;

        $filed = array(
            'count(foc.b2b_code) as count',

        );
        $fileConfirm = join(",", $filed);
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'findConfirmData';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "foc.create_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $orderWhere;
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response']);
    }
    /**
     * Base.FcModule.Payment.Payment.accountPaidLists
     * 异常付款列表
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function abnormal($params) {
        $this->_rule = array(
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			   非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			   非必须参数, 默认值 所有
            array('shopName', 'require', PARAMS_ERROR, ISSET_CHECK),         //  店铺名称			   非必须参数, 默认值 所有
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),         //  付款编号			   非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示的数量			非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $checkWhere = array();
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        !empty($start_time) && empty($end_time) && $checkWhere['make_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $checkWhere['make_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $checkWhere['make_time'] = array('between', array($start_time, $end_time+ 86400));

        # 固定条件
        $status1 = FC_STATUS_ON_PAYMENT;
        $status2 = FC_STATUS_PAYMENT;
//        $checkWhere['fc_check_status'] = "OK";

        if(!empty($params['fc_code'])){

            $checkWhere['fc_code'] = $params['fc_code'];
            $checkWhere[] = "(
                            (
                                (`status`=1 and `make_time` is not null)
                                AND (
                                    (fc_pay_status = '')
                                    OR (fc_pay_status = 'FAIL')
                                )
                            )
                            OR (
                                `status`=2 and
                                fc_pay_status = 'OK'
                                AND hand_remark <> ''
                            )
                        )";
        }else{
            $fail = FC_PAY_STATUS_FAIL;
            $checkWhere[] = "( (status ={$status2} and fc_pay_status='OK' AND hand_remark <> '') or ( (`status`=1 and `make_time` is not null) AND (fc_pay_status ='{$fail}')) )";
        }

        if(!empty($params['sc_name'])){
            $checkWhere['sc_name'] = $params['sc_name'];
        }

        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $fileConfirm = 'bank_type,fc_code,sc_code,affirm_time,amount,sc_name,account_name,account_bank,account_number,status,make_time as create_time,affirm_time,create_name,affirm_name,status';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getFcPaymentList';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "make_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $checkWhere;
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response']);
    }


    /**
     * Base.FcModule.Payment.Payment.getFcCode
     * 获取当前订单信息
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function getConfirm ($params) {
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),       //  订单编号			   非必须参数, 默认值 所有
        );

        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where = array(
            'b2b_code' => $params['b2b_code'],
        );

        $confirm = D("FcOrderConfirm")->where($where)->find();
        //查询订单为空 则赋值一个错误的订单号来查询，防止fc_code为空
        if(empty($confirm['fc_code'])) $confirm['fc_code'] = $params['b2b_code']."error" ;

        return $this->res($confirm);
    }

    /**
     * 手动线下付款
     * Base.FcModule.Payment.Payment.offLinePay
     * @param array $params
     * @return array
     */
    public function offLinePay($params) {
        // 检索参数校验
        $this->_rule = array(
            array('bank_type', 'require', PARAMS_ERROR, MUST_CHECK),
            array('bank_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('msg', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if(!in_array($params['bank_type'], array(PAY_METHOD_REMIT_CMB,PAY_METHOD_REMIT_CMBC))) {
            return $this->res(null, 7096);
        }
        // 先查看是否已经付款过了
        $isno = D('FcOrderPayment')->where('fc_code="' . $params['fc_code'] . '" and status=' . FC_STATUS_PAYMENT)->select();
        if($isno) {
            return $this->res(null, 8106);
        }
        // 完成付款状态回填
        try{
            D()->startTrans();
            // 首先修改payment_account表
            $fpa_where['fc_code'] = $params['fc_code']; // 查找所有和fc_code关联的account_code
            //$fpa_where['fc_check_status'] = 'OK';  // 这些都是已制单的
            $fpa_updateData['fc_pay_status'] = 'OK';
            $fpa_updateData['fc_check_status'] = 'OK';
            $fpa_updateData['update_time'] = NOW_TIME;
            $fpa_updateData['bank_type'] = $params['bank_type'];
            $fpa_upInfo = D('FcPaymentAccount')->where($fpa_where)->save($fpa_updateData);
            if ($fpa_upInfo === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
            // 修改order_payment表
            $fop_where['fc_code'] = $params['fc_code'];
            //$fop_where['fc_check_status'] = 'OK';  // 这些都是已制单的
            $fop_where['status'] = FC_STATUS_ON_PAYMENT;
            $fop_updateData['fc_pay_status'] = 'OK';
            $fop_updateData['fc_check_status'] = 'OK';
            $fop_updateData['affirm_time'] = NOW_TIME;
            $fop_updateData['affirm_name'] = '孙艳';
            $fop_updateData['bank_type'] = $params['bank_type'];
            $fop_updateData['bank_code'] = $params['bank_code'];
            $fop_updateData['hand_remark'] = '线下付款';
            $fop_updateData['status'] = FC_STATUS_PAYMENT;
            $fop_upInfo = D('FcOrderPayment')->where($fop_where)->save($fop_updateData);
            if ($fop_upInfo === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
            // 修改order_confirm表
            $oc_where = 'fc_code="' . $params['fc_code'] . '"';
            $oc_updateData['f_status'] = FC_F_STATUS_PAYMENT;
            $oc_updateData['balance_status'] = FC_BALANCE_STATUS_BALANCE;
            $oc_upInfo = D('FcOrderConfirm')->where($oc_where)->save($oc_updateData);
            if ($oc_upInfo === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(),$ex->getCode());
        }
        // 是否发送短信
        if($params['msg']) {
            // 发送短信
            $apiSendStoreMsg = 'Base.FcModule.Payment.Order.sendStoreMsg';    //发送短信通知商家
            $storedata['fc_code'] = $params['fc_code'];
            $storedata['sc_code'] = $params['sc_code'];
            $this->invoke($apiSendStoreMsg, $storedata);
            $apiSendSalesMsg = 'Base.FcModule.Payment.Order.sendSalesMsg';   // 发送通知业务员短信
            $saledata['fc_code'] = $params['fc_code'];
            $saledata['sc_code'] = $params['sc_code'];
            $this->invoke($apiSendSalesMsg, $saledata);
        }
        return $this->res('ok',0);
    }

}

?>
