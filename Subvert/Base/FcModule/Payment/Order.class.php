<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: hp-yaozihao <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 商品相关模块
 */
namespace Base\FcModule\Payment;

use System\Base;

class Order extends Base{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Base.FcModule.Payment.Order.findPayment
     * 财务付款汇总单 未付款、已付款
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */
	public function findPayment($params){
        $where = $params['where'];
        $field = $params['field'];
        $page = $params['page'] ? $params['page'] : 1;
        $page_number = $params['page_number'] ? $params['page_number'] : 20;
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  账户编码			* 必须字段
            array('id', 'require', PARAMS_ERROR, ISSET_CHECK),                //  根据id查询			* 必须字段
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK),                //  状态
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),              //  交易类型			非必须参数, 默认值 所有
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
        );
        # 时间区间确定
        ( $where['start_time'] && $where['end_time'] ) ? $where['affirm_time'] = array('BETWEEN', [$where['start_time'], $where['end_time'] ]) : null;
        // 自动校验
        if (!$this->checkInput($this->_rule, $where)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where[] ='make_time is null';
        $field[] = 'create_time';
		$lists = D('FcOrderPayment')->where($where)->field($field)->order('affirm_time desc,create_time desc')->page($page,$page_number)->select();
        $amount = D('FcOrderPayment')->where($where)->field(' sum(amount) as total_amount')->find();
        $totalnum =  D('FcOrderPayment')->where($where)->count();
        $res = array(
            'totalnum'=> $totalnum,
            'lists' => $lists,
            'total_amount'=>($amount['total_amount'])?$amount['total_amount']:0,
            'page' => $page,
            'page_number' => $page_number,
        );

        return $this->res($res);
    }


    /**
     * Base.FcModule.Payment.Order.addOnLinePayment
     * 财务确认付款，payment添加字段
     * 注意在bll里边先确认每个订单是否已经付款过。在进行汇总，然后插入汇总表。
     * $params array('sc_code'=> '','b2b_code'=> array())   订单编号的集合数组
     * @param type $params
     * 返回数据包括商家编码sc_code，总金额amount，生成的fc_code
     */
    public function addOnLinePayment($params){
        //计算金额写入汇总表
        $amount =  $this->amountOnLinePayment($params);
        //将商铺的银行信息查出来
        $storeWhere = array(
            'sc_code'=>$params['sc_code']
        );
        $storeFields = 'name,account_bank,account_name,account_no';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $storeParams['center_flag'] = SQL_FC;
        $storeParams['sql_flag'] = 'getStoreInfo';
        $storeParams['where'] = $storeWhere;
        $storeParams['fields'] = $storeFields;
        $storeBankInfo = $this->invoke($apiPath, $storeParams);

        $storeBanklist = $storeBankInfo['response']['lists'][0];
        $params['amount'] = $amount['response']['totalamount'];                           // 转账支付金额
        $params['account_bank'] = $storeBanklist['account_bank'];           // 商户的开户行
        $params['account_number'] = $storeBanklist['account_no'];           // 银行账号
        $params['account_name'] = $storeBanklist['account_name'];           // 开户名
        $params['sc_name'] = $storeBanklist['name'];           // 商铺名
        $params['create_time'] = NOW_TIME;
        $params['status'] = 1;



        $this->_rule = array(
            array('sc_code','require',PARAMS_ERROR,MUST_CHECK),             // 店铺编号必须的必
            array('fc_code','require',PARAMS_ERROR,MUST_CHECK),             // 汇总付款编号
            array('amount','require',PARAMS_ERROR,MUST_CHECK),              // 转账支付金额
            array('status','require',PARAMS_ERROR,MUST_CHECK),              // 当前状态
            array('account_bank','require',PARAMS_ERROR,MUST_CHECK),        // 商户的开户行
            array('account_number','require',PARAMS_ERROR,MUST_CHECK),      // 银行账号
            array('account_name','require',PARAMS_ERROR,MUST_CHECK),        // 开户名
            //array('uc_code','require',PARAMS_ERROR,ISSET_CHECK),            // 财务编号
            array('create_time','require',PARAMS_ERROR,MUST_CHECK),         // 创建时间
            array('pay_method','require',PARAMS_ERROR,ISSET_CHECK),         // 支付方式
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $account_arr['fc_code'] = $params['fc_code'];
        $account_arr['account_code'] = $params['fc_code'];
        $account_arr['amount'] = $params['amount'];
        $account_arr['create_time'] = NOW_TIME;
        $res = D('FcOrderPayment')->data($params)->add();
        $account = D('FcPaymentAccount')->data($account_arr)->add();
        $resLists = array(
            'fc_code'=>$params['fc_code'],
            'sc_code'=>$params['sc_code'],
            'amount'=>$params['amount'],
        );
        if($res && $account){
            return $this->res($resLists);
        }else{
            return $this->res(null,8053);

        }
    }

    /**
     * Base.FcModule.Payment.Order.addYeePayment
     *更新confirm表的数据（在汇总确定付款时必须更新fc_code字段）
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */
    public function addYeePayment($params){
        $this->_rule = array(
            array('amount', 'require', PARAMS_ERROR, MUST_CHECK),          //  账户编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),          //  账户编码
            //  交易类型			非必须参数, 默认值 所有
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取商家账户列表信息 判断此商家汇总是否是以保障好 如果不是易宝账号提示管理员注册易宝
        $storeApiPath = 'Base.StoreModule.Basic.Store.getBankInfoLists';
        $sc_code = array(
            'sc_code' => array($params['sc_code'])
        );
        $storeRes = $this->invoke($storeApiPath, $sc_code);
        if(empty($storeRes['response'][$params['sc_code']]['yee_account'])) {
            return $this->endInvoke('', 10000);
        }

        $data = array(
            'ledgerNo'=>$storeRes['response'][$params['sc_code']]['yee_account'],
            'fc_code'=>$params['fc_code'],
            'fc_pay_code'=>$params['fc_pay_code'],
            'amount'=>$params['amount'],
            'callback'=>'Bll.Cms.Finance.Finance.yeePayCallback',//回调接口
        );
        //调取转账接口，进行转账申请。
        $writeTransferApiPath = "Base.TradeModule.Pay.Bank.writeTransfer";
        $list = $this->invoke($writeTransferApiPath, $data);
        //改变申请状态
        $paymentApiPath = "Base.FcModule.Payment.Order.updatePayment";
        $paymentParams = array(
            'where'=>array(
                'fc_code' =>$params['fc_code'],
                'sc_code' => $params['sc_code'],
            ),
            'data' =>array()
        );
        if($list['status'] !== 0){
            $paymentParams['data']['fc_apply_pay_status'] = 'FAIL';

        }else{
            $paymentParams['data']['fc_apply_pay_status'] = 'OK';

        }
        $this->invoke($paymentApiPath, $paymentParams);

        return $this->endInvoke($list['response'],$list['status']);

    }
    /**
     * Base.FcModule.Payment.Order.updatePayment
     * 财务确认汇总付款，更新银行流水号，备注，确认人，最终确认时间
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */
    public function updatePayment($params){

        $where = $params['where'];
        $data = $params['data'];

        if(empty($data) || !is_array($data)){
            return $this->res(null,5);
        }
        $this->_rule = array(
            array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  汇总编号			* 必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $where)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $field = array(
            'bank_code',
            'remark',
            'affirm_time',
            'status',
            'fc_pay_code',
            'fc_pay_status',
            'fc_apply_pay_status'
        );
        $res = D('FcOrderPayment')->where($where)->field($field)->data($data)->setField($data);
        return $this->res($res);
    }
    /**
     * Base.FcModule.Detail.Order.addConfirm
     * 财务确认到账 添加一条信息
     *  需查询的订单的单号集合
     * @param type $params
     */
    public function addConfirm($params){
        $data = array();
        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  订单号，唯一		* 必须字段
            array('client_name', 'require', PARAMS_ERROR, MUST_CHECK),        //  客户姓名			* 必须字段
            //array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),            //  用户编号			* 必须字段
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),            //  店铺编号 			* 必须字段
            //array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),            //  汇总编号唯一    	非必须字段 预留更新字段
            array('pay_no', 'require', PARAMS_ERROR, MUST_CHECK),             //  支付流水号		* 必须字段
            array('oc_code', 'require', PARAMS_ERROR, MUST_CHECK),            //  订单支付编码 唯一* 必须字段
            //array('pay_time', 'require', PARAMS_ERROR, MUST_CHECK),           //  付款时间			* 必须字段
            array('real_amount', 'require', PARAMS_ERROR, MUST_CHECK),        //  买家实际支付订单金额			非必须参数, 默认值 所有
            array('price', 'require', PARAMS_ERROR, MUST_CHECK),              //  订单金额			* 必须字段
            //array('amount', 'require', PARAMS_ERROR, ISSET_CHECK),             //  订单总金额		非必须字段 预留字段
            array('pay_method', 'require', PARAMS_ERROR, MUST_CHECK),         //  支付方式			* 表单状态
            //array('get_amount', 'require', PARAMS_ERROR, ISSET_CHECK),        //  到账金额			非必须参数, 预留字段
            //array('pay_poundage', 'require', PARAMS_ERROR, ISSET_CHECK),      //  预计手续费		非必须参数, 预留字段
            array('create_time', 'require', PARAMS_ERROR, MUST_CHECK),        //  确定时间			* 必须字段
            array('status', 'require', PARAMS_ERROR, MUST_CHECK),            //  状态：1.已确认。2.已汇总。3.已汇总并付款			* 必须字段
        );
        foreach($params as $key => $v){
            $data[$key] = array(
                'b2b_code' => $v['b2b_code'],
                'client_name' => $v['client_name'],
                //'uc_code' => $v['uc_code'],
                'sc_code' => $v['sc_code'],
                // 'fc_code' => $v['fc_code'],
                'pay_no' => $v['pay_no'],
                'oc_code' => $v['oc_code'],
                //'pay_time' => $v['pay_time'],
                'real_amount' => $v['real_amount'],
                'price' => $v['price'],
                //'amount' => $v['amount'],
                'pay_method' => $v['pay_method'],
                //'get_amount' => $v['get_amount'],
                //'pay_poundage' => $v['pay_poundage'],
                'create_time' => NOW_TIME,
                'status' => '1',
            );
            // 自动校验
            if (!$this->checkInput($this->_rule, $data[$key])) {
                return $this->res($this->getErrorField(), $this->getCheckError());
            }
        }
        $add_res = D('FcOrderConfirm')->data($data)->addAll($data);
        if (FALSE === $add_res || $add_res <= 0) {
            return $this->res(null, 8052);
        }
        return $this->res($add_res); //添加成功
    }
    /**
     * Base.FcModule.Payment.Order.findGoodsConfirm
     * 查询财务点单已确定订单的列表Confirm
     * 接收数据
     */
    public function findGoodsConfirm($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  账户编码
            array('oc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  付款单号
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式
            array('pay_method_ext1', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付银行
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),             //  交易类型			非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示数量			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $orderWhere = array();
        if(!empty($params['b2b_code']) && is_array($params['b2b_code']) ){
            $orderWhere['confirm.b2b_code'] = array('in',$params['b2b_code']);
        }else if(!empty($params['b2b_code'])){
            $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
        }
        if(!empty($params['bank_code']) && !empty($params['bank_code'])){
            $orderWhere['confirm.bank_code'] = $params['bank_code'];
        }
        if(!empty($params['sc_code'])){
            $orderWhere['confirm.sc_code'] = $params['sc_code'];
        }
        if(!empty($params['oc_code'])){
            $orderWhere['confirm.oc_code'] = $params['oc_code'];
        }
        if(!empty($params['oc_type'])){
            $orderWhere['confirm.oc_type'] = $params['oc_type'];
        }
        if(!empty($params['pay_no'])){
            $orderWhere['voucher.pay_no'] = $params['pay_no'];
        }
        if(!empty($params['pay_method'])){
            $orderWhere['b2b.pay_method'] = $params['pay_method'];
        }
        if(!empty($params['pay_method_ext1'])){
            $orderWhere['b2b.ext1'] = $params['pay_method_ext1'];
        }
        if(!empty($params['fc_code'])){
            $orderWhere['confirm.fc_code'] = $params['fc_code'];
        }
        if(!empty($params['remit_code'])){
            $orderWhere['extend.remit_code'] = $params['remit_code'];
        }
        if(!empty($params['amount'])){
            $orderWhere['b2b.real_amount'] = $params['amount'];
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $total_amount = empty($params['total_amount']) ? 'NO' : $params['total_amount'];
        //将确定列相关联的订单信息查询出来
        if(!empty($params['confirm_status'])){
            $orderWhere['confirm.status']= $params['confirm_status'];
        }
        if(!empty($params['f_status'])) {
            $orderWhere['confirm.f_status'] = $params['f_status'];
        }
        # 时间区间确定
        ( $params['start_time'] && $params['end_time'] ) ? $orderWhere['b2b.pay_time'] = array('BETWEEN', [$params['start_time'], $params['end_time']+ 86400 ]) : null;
        # 固定条件  不能有默认条件
//		!$params['f_status'] ? $orderWhere['confirm.f_status'] = 1 : $orderWhere['confirm.f_status'] = $params['f_status'];  # 只取出未汇总的
        $orderWhere['confirm.oc_type'] = FC_ORDER_CONFIRM_OC_TYPE_GOODS;
        if($total_amount == 'YES'){
            $aggre = array(
                array('sum','b2b.real_amount','total_amount'),
                array('sum','extend.coupon_amount','total_coupon_amount'),
            );
            $orderParams['aggre'] = $aggre;
        }
        $fileConfirm = 'confirm.cost,confirm.b2b_code,b2b.real_amount as amount,b2b.ext1,b2b.pay_time,b2b.order_amout,
        b2b.client_name,extend.commercial_name,extend.coupon_amount,b2b.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,
        store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,extend.remit_code,confirm.bank_code,confirm.third_status,confirm.account_status,confirm.balance_status';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getOrderGoodsInfo';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "confirm.update_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderRes = $this->invoke($apiPath, $orderParams);
        $orderRes['response']['total_amount'] = bcadd($orderRes['response']['total_amount'],$orderRes['response']['total_coupon_amount'],2);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);
    }

    /**
     * Base.FcModule.Payment.Order.findConfirm
     * 财务付款汇总单 待生成
     * 接收数据
     */
    public function findConfirm($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  账户编码
            array('oc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  账户编码
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //  账户编码
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),             //  交易类型			非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  交易类型			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  交易类型			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $orderWhere = array();
        if(!empty($params['b2b_code']) && is_array($params['b2b_code']) ){
            $orderWhere['confirm.b2b_code'] = array('in',$params['b2b_code']);
        }else if(!empty($params['b2b_code'])){
            $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
        }
        if(!empty($params['bank_code']) && !empty($params['bank_code'])){
            $orderWhere['confirm.bank_code'] = $params['bank_code'];
        }
        if(!empty($params['sc_code'])){
            $orderWhere['confirm.sc_code'] = $params['sc_code'];
        }
        if(!empty($params['oc_code'])){
            $orderWhere['confirm.oc_code'] = $params['oc_code'];
        }
        if(!empty($params['oc_type'])){
            $orderWhere['confirm.oc_type'] = $params['oc_type'];
        }
        if(!empty($params['pay_no'])){
            $orderWhere['voucher.pay_no'] = $params['pay_no'];
        }
        if(!empty($params['pay_method'])){
            $pay_method['b2b.pay_method'] = $params['pay_method'];
        }
        if(!empty($params['fc_code'])){
            $orderWhere['confirm.fc_code'] = $params['fc_code'];
        }
        if(!empty($params['remit_code'])){
            $orderWhere['extend.remit_code'] = $params['remit_code'];
        }
        if(!empty($params['oc_type'])&&!empty($params['amount'])){
            if($params['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
                $orderWhere['b2b.real_amount'] = $params['amount'];
            }
            if($params['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                $orderWhere['adv.amount'] = $params['amount'];
            }
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        //将确定列相关联的订单信息查询出来
        if(!empty($params['confirm_status'])){
            $orderWhere['confirm.status']= $params['confirm_status'];
        }
        if(!empty($params['f_status'])) {
            $orderWhere['confirm.f_status'] = $params['f_status'];
        }
        if(!empty($params['pay_status'])) {
            $pay_status['b2b.pay_status'] = $params['pay_status'];
        }


        if($pay_method){
            $orderWhere[] = "b2b.pay_method='{$pay_method['b2b.pay_method']}' OR adv.pay_method='{$pay_method['b2b.pay_method']}'";
        }
        # 流水号确定
        $params['pay_no'] ? $orderWhere['voucher.pay_no'] = $params['pay_no'] : null;

        if($pay_status){
            $orderWhere[] = "b2b.pay_status='{$pay_status['b2b.pay_status']}' OR adv.status='{$pay_status['b2b.pay_status']}'";
        }
        $total_amount = empty($params['total_amount']) ? 'NO' : $params['total_amount'];

        if($total_amount == 'YES'){
            $aggre = array(
                array('sum','b2b.real_amount','total_b2b_amount'),
                array('sum','adv.amount','total_adv_amount'),
            );
            $orderParams['aggre'] = $aggre;
        }
        # 时间区间确定
        if(!$orderWhere['confirm.fc_code']){
            $orderWhere['confirm.status'] = FC_STATUS_ON_CONFIRM;
            $orderWhere['confirm.f_status'] = FC_F_STATUS_UN_PAYMENT;
            $orderWhere[] = "b2b.pay_status='".OC_ORDER_PAY_STATUS_PAY."' OR adv.status='".OC_ORDER_PAY_STATUS_PAY."'";
            $orderWhere[] = "b2b.pay_method='".PAY_METHOD_ONLINE_REMIT."' OR adv.pay_method='".PAY_METHOD_ONLINE_REMIT."'";
            $orderWhere[] = "b2b.ext1='".PAY_METHOD_REMIT_CMB."' OR adv.pay_method_ext1='".PAY_METHOD_REMIT_CMB."'";
        }



        # 固定条件  不能有默认条件
//		!$params['f_status'] ? $orderWhere['confirm.f_status'] = 1 : $orderWhere['confirm.f_status'] = $params['f_status'];  # 只取出未汇总的
        $fileConfirm = 'adv.amount as adv_amount,adv.adv_code,adv.pay_method as adv_pay_method,adv.pay_method_ext1,confirm.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,extend.commercial_name,b2b.pay_method,voucher.pay_no,store.account_bank,store.account_name,store.account_no,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,extend.remit_code';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getOrderInfo';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "confirm.update_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderRes = $this->invoke($apiPath, $orderParams);
        $orderRes['response']['total_amount'] = bcadd($orderRes['response']['total_b2b_amount'],$orderRes['response']['total_adv_amount'],2);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);

    }

    /**
     * Base.FcModule.Payment.Order.updateConfirm
     *更新confirm表的数据（在汇总确定付款时必须更新fc_code字段）
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */
	public function updateConfirm($params){
        $where = array();
        if($params['where']['sc_code']){
            $where['sc_code'] = $params['where']['sc_code'];
        }
        if($params['where']['b2b_code'] && is_array($params['where']['b2b_code'])){
            $where['b2b_code'] =  array('in',$params['where']['b2b_code']);
        }
        if($params['where']['f_status']){
            $where['f_status'] =  $params['where']['f_status'];
        }
        !empty($params['where']['fc_code']) && $where['fc_code'] = $params['where']['fc_code'];
        $where['status'] = $params['where']['status'];
        $data = $params['data'];
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR,ISSET_CHECK),           //  店铺编号			* 必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $where)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
		}
        //判断是否修改confirm表update_time.
        if(empty($params['no_update_time'])) {
            $data['update_time'] = NOW_TIME;
        }
        $res = D('FcOrderConfirm')->where($where)->data($data)->setField($data);

        if($res){
            //$success_num = mysql_affected_rows();
            return $this->res($res);
        }else{
            return $this->res(null,8053);
        }
	}


    /**
     * Base.FcModule.Payment.Order.amountOnLinePayment
     *合并付款之前现支付金额计算
     * 在线支付包括（WEIXIN、ALIPAY、REMIT）
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */
    public function amountOnLinePayment($params){
        $code = $this->confirmList($params);
        if($code['status']!=0){
            return $this->res('没有返回数据、参数异常','5518');
        }
        if($code['response']['GOODS']){
            $amount_goods = $this->amountGoodsPayment($params);
            $amount_goods = $amount_goods['response']['price'];
        }
        if($code['response']['ADVANCE']){
            $amount_advance = $this->amountAdvancePayment($params);
            $amount_advance = $amount_advance['response']['price'];
        }
        $amount = floatval($amount_goods)+floatval($amount_advance);
        return $this->res(['totalamount'=>$amount]);
    }

    /**
     * Base.FcModule.Payment.Order.amountGoodsPayment
     *合并付款之前商品订单金额计算
     * 在线支付包括（WEIXIN、ALIPAY、REMIT）
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */
    public function amountGoodsPayment($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  商户编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  订单编码
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where = array(
            'confirm.sc_code'=>$params['sc_code'],
            'b2b.b2b_code'=> array('in',$params['b2b_code']),
            'confirm.status' => 2,
            'confirm.f_status' => 1,
        );
        $field = 'sum(b2b.real_amount) as price';

        $amount = D('FcOrderConfirm')->field($field)->alias('confirm')
                ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                ->where($where)
                ->find();
        return $this->res($amount);
    }
    /**
     * Base.FcModule.Payment.Order.amountAdavancePayment
     *合并付款之前现支付金额计算
     * 在线支付包括（WEIXIN、ALIPAY、REMIT）
     * 接收参数
     * array $where 更新条件
     * array $field 限制条件
     * array $data  更新的数据
     */
    public function amountAdvancePayment($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  商户编码
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  订单编码

        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where = array(
            'confirm.sc_code'=>$params['sc_code'],
            'adv.adv_code'=> array('in',$params['b2b_code']),
            'confirm.status' => 2,
            'confirm.f_status' => 1,
        );
        $field = 'sum(adv.amount) as price';

        $amount = D('FcOrderConfirm')->field($field)->alias('confirm')
                ->join("{$this->tablePrefix}oc_advance adv ON confirm.b2b_code=adv.adv_code",'LEFT')
                ->where($where)
                ->find();
        return $this->res($amount);
    }

    /**
     * Base.FcModule.Payment.Order.amountAdvancePayment
     * 批量订单查询
     * 接收参数
     * array $params 数组b2b_code
     * array("sc_code"=>"13123",
     *       "b2b_code=>[
     *                   "12100002529",
     *                    "12100002531"
     *      ]);
     */
    public function confirmList($params){

        $this->_rule = array(
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  订单编码
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  订单编码
        );

        if($params['b2b_code']){
            $where['b2b_code'] =array('in',$params['b2b_code']);
        }else{
            $where['fc_code'] =array('in',$params['fc_code']);
        }

        $field = ['oc_type,b2b_code'];
        //将金额总数查出来
        $b2b_code = D('FcOrderConfirm')->where($where)->field($field)->select();
        if(!$b2b_code)  return $this->res('没有返回数据、参数异常','5518');
        foreach($b2b_code as $k=>$v){
            if($v['oc_type']=="GOODS"){
                $code['GOODS'][] = $v['b2b_code'];
            }
            if($v['oc_type']=="ADVANCE"){
                $code['ADVANCE'][] = $v['b2b_code'];
            }
        }
        return $this->res($code);
    }

    /**
     * Base.FcModule.Payment.Order.getFConfirmLists
     * 查询财务待点单列表
     * 接收数据
     */
    public function getFConfirmLists($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  账户编码
            array('oc_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  付款单号
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式
            array('pay_method_ext1', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付银行
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),             //  交易类型			非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示数量			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $orderWhere = array();
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        !empty($start_time) && empty($end_time) && $orderWhere['b2b.pay_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $orderWhere['b2b.pay_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $orderWhere['b2b.pay_time'] = array('between', array($start_time, $end_time+ 86400));
        $total_amount = empty($params['total_amount']) ? 'NO' : $params['total_amount'];

        if(!empty($params['b2b_code']) && is_array($params['b2b_code']) ){
            $orderWhere['confirm.b2b_code'] = array('in',$params['b2b_code']);
        }else if(!empty($params['b2b_code'])){
            $orderWhere['confirm.b2b_code'] = $params['b2b_code'];
        }
        if(!empty($params['sc_code'])){
            $orderWhere['confirm.sc_code'] = $params['sc_code'];
        }
        if(!empty($params['amount'])){
            $orderWhere['b2b.real_amount'] = $params['amount'];
        }
        if(!empty($params['pay_no'])){
            $orderWhere['voucher.pay_no'] = $params['pay_no'];
        }
        if(!empty($params['pay_method'])){
            $orderWhere['b2b.pay_method'] = $params['pay_method'];
        }
        if(!empty($params['pay_method_ext1'])){
            $orderWhere['b2b.ext1'] = $params['pay_method_ext1'];
        }
        if(!empty($params['fc_code'])){
            $orderWhere['confirm.fc_code'] = $params['fc_code'];
        }
        if(!empty($params['remit_code'])){
            $orderWhere['extend.remit_code'] = $params['remit_code'];
        }
        if(!empty($params['oc_code'])){
            $orderWhere['confirm.oc_code'] = $params['oc_code'];
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        //将确定列相关联的订单信息查询出来
        if(!empty($params['confirm_status'])){
            $orderWhere['confirm.status']= $params['confirm_status'];
        }
        //过滤支付方式为空或者支付方式为ADVANCE的订单
        if(!$orderWhere['b2b.pay_method'] ){
            $orderWhere[] = "b2b.pay_method<>''";
            $orderWhere[] = "b2b.pay_method <> 'ADVANCE'";
        }
        $orderWhere[] = "b2b.pay_status = (CASE WHEN (b2b.pay_status = 'UNPAY' AND b2b.pay_method = 'REMIT') THEN 'UNPAY'  ELSE 'PAY' END) ";
        # 流水号确定
        $params['pay_no'] ? $orderWhere['voucher.pay_no'] = $params['pay_no'] : null;
        if($total_amount == 'YES'){
            $aggre = array(
                array('sum','b2b.real_amount','total_amount'),
                array('sum','extend.coupon_amount','total_coupon_amount'),
            );
            $orderParams['aggre'] = $aggre;
        }
        # 流
        # 时间区间确定
        ( $params['start_time'] && $params['end_time'] ) ? $orderWhere['b2b.pay_time'] = array('BETWEEN', [$params['start_time'], $params['end_time']+ 86400 ]) : null;
        # 固定条件
        !$params['f_status'] ? $orderWhere['confirm.f_status'] = 1 : $orderWhere['confirm.f_status'] = $params['f_status'];  # 只取出未汇总的
        $orderWhere['confirm.oc_type'] = FC_ORDER_CONFIRM_OC_TYPE_GOODS;
        $fileConfirm = 'b2b.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,b2b.pay_method,
                        extend.commercial_name,extend.coupon_amount,
                        voucher.pay_no,store.account_bank,store.account_name,store.account_no,
                        store.name as sc_name,confirm.cost,confirm.oc_code,extend.remit_code,confirm.bank_code,confirm.third_status,confirm.account_status,confirm.balance_status';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getOrderGoodsInfo';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "ASCII(confirm.account_status) asc,b2b.create_time desc";
//        $orderParams['order'] = "confirm.update_time desc,b2b.create_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderRes = $this->invoke($apiPath, $orderParams);

        $orderRes['response']['total_amount'] = bcadd($orderRes['response']['total_amount'],$orderRes['response']['total_coupon_amount'],2);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        return $this->res($orderRes['response']);
    }

    /**
     * Base.FcModule.Payment.Order.sendSalesMsg
     * # 发送通知业务员短信
     * 接收数据
     */
    public function sendSalesMsg($params){
        $this->_rule = array(
            array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  汇总编码			* 必须字段
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  汇总编码			* 必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //判断是否有预付款订单列表
        $getMerchan = $this->invoke("Base.StoreModule.Basic.User.getMerchanInfo", ['sc_code'=>$params['sc_code']]);
        $codes = $this->confirmList(['fc_code'=>$params['fc_code']]);
        if($codes['response']['GOODS']){
            $call_goods = $this->invoke("Com.Common.CommonView.FcSql.getPaymentOrderUserInfo", ['fc_code'=>$params['fc_code']]); # 获取汇总单人
            $info = $call_goods['response'];

        }
        if($codes['response']['ADVANCE']){
            $call_advance = $this->invoke("Com.Common.CommonView.FcSql.getPaymentAdvanceUserInfo", ['fc_code'=>$params['fc_code']]); # 获取汇总单人
            $info = $call_advance['response'];
        }

        if($call_goods['response']&&$call_advance['response']){
            $call = array_merge($call_goods['response'],$call_advance['response']);
        }else{
            $call = $info;
        }

        $data = [
            'uc_code'=>$getMerchan['response']['uc_code'],
            'sc_code'=>$params['sc_code'],
            'sms_type'=>SC_SMS_NEW_BALANCE
        ];
        $getLinkManInfo = $this->invoke("Base.StoreModule.Basic.Sms.getLinkManInfo", $data);//自定义短信
        if($call) {
            $data = $call;
            $smsData = [];
            $ucData = array_unique(array_column($data, 'uc_code'));
            foreach($ucData as $v) {
                $smsData[$v]['amount'] = 0.00;
                foreach($data as $d) {
                    if($d['uc_code'] == $v) {
                        $smsData[$v]['phone'] = $d['sales_mobile'];
                        $smsData[$v]['amount'] = bcadd($d['amount'], $smsData[$v]['amount'], 2);
                        $smsData[$v]['sc_name'] = $d['sc_name'];
                        $smsData[$v]['uc_name'] = $d['uc_name'];
                    }
                }
            }

            $paymentDate = date('Y-m-d', NOW_TIME);
            foreach($smsData as $send) {
                $phone = [];
                //自定义短信
                if($getLinkManInfo['response']){
                    foreach($getLinkManInfo['response'] as $k=>$v){
                        $phone[] = $v['phone'];
                    }
                }
                $phone[]  = $send['phone'];
                $sc_name = $send['sc_name'];
                $uc_name = $send['uc_name'];
                $amount = $send['amount'];
                $data = array(
                    'sys_name'=>CMS,
                    'numbers' =>$phone,
                    'message' =>"您的客户“{$sc_name} {$uc_name}”的已付款项，平台已与贵公司结算。结算金额：￥{$amount}，结算日期：{$paymentDate}。",
                );
                $this->push_queue("Com.Common.Message.Sms.send", $data , 0);
            }
        }
    }
    /**
     * Base.FcModule.Payment.Order.sendSalesMsg
     * # 发送短信通知商家
     * 接收数据
     */
    public function sendStoreMsg($params){
        $this->_rule = array(
            array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),           // 财务汇总编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),           // 商户编码			* 必须字段
        );
        if (!$this->checkInput($this->_rule, $params)) {
            // 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $call = $this->invoke("Base.StoreModule.Basic.User.getMerchanInfo", ['sc_code'=>$params['sc_code']]);
        $data = [
            'uc_code'=>$call['response']['uc_code'],
            'sc_code'=>$params['sc_code'],
            'sms_type'=>SC_SMS_NEW_DELIVERY
        ];
        $getLinkManInfo = $this->invoke("Base.StoreModule.Basic.Sms.getLinkManInfo", $data);//自定义短信
        if( isset($call['response']['phone']) ) {
            # 获取商家基本信息
            $fc_code = $params['fc_code'];
            $callData = array(
                'where'=>['fc_code'=>$fc_code],
                'field'=>['account_number','amount'],
            );

            $field[] = 'create_time,account_number,amount';
            $payMentInfo = D('FcOrderPayment')->where($callData['where'])->field($field)->find();
            if($payMentInfo['amount']) {
                $num = $payMentInfo['account_number'];
                $num = substr($num,-4);
                $price = $payMentInfo['amount'];
                $phone[] = $call['response']['phone'];

                if($getLinkManInfo['response']){
                    foreach($getLinkManInfo['response'] as $k=>$v){
                        $phone[] = $v['phone'];
                    }

                }
                $data = array(
                    'sys_name'=>CMS,
                    'numbers' =>$phone,
                    'message' =>"平台已向您尾号为{$num}的账户转入{$price}元，汇款备注：{$fc_code}，预计将在下个工作日内到账。",
                );
                $this->push_queue("Com.Common.Message.Sms.send", $data , 0);

            }
        }
    }

    /**
     * Base.FcModule.Payment.Order.amountPayment
     * 查询财务已付款订单的总金额
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */
    public function amountPayment($params){
        $where = $params['where'];
        $field = $params['field'];

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  账户编码			* 必须字段
            array('bank_code', 'require', PARAMS_ERROR, ISSET_CHECK),           // 交易流水编码			非必须参数, 默认值 所有
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  汇款编码			非必须参数, 默认值 所有
            array('status', 'require', PARAMS_ERROR, MUST_CHECK),             //  * 当前汇总状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
        );
        # 时间区间确定
        ( $where['start_time'] && $where['end_time'] ) ? $where['affirm_time'] = array('BETWEEN', [$where['start_time'], $where['end_time'] ]) : null;
        // 自动校验
        if (!$this->checkInput($this->_rule, $where)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $field[] = 'create_time';
        $amount =  D('FcOrderPayment')->where($where)->sum("amount");
        $res = array(
            'amount'=> $amount,
        );
        return $this->res($res);
    }

    /**
     * Base.FcModule.Payment.Order.updateLog
     * 判断订单是否够先锋支付的条件
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function updateLog ($params) {

        $this->_rule = array(
            array('b2b_code', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function')         //  订单号			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $today = strtotime(date("Y-m-d H:i:s",$yesterday + 86400));
        $b2b_code = '';
        foreach($params['b2b_code'] as $val){
            $b2b_code .= $val.",";
        }

        $b2b_code = rtrim($b2b_code,",");
        $where[] = "b2b_code in ({$b2b_code})";
        # 根据订单号获取当前收货人的编号
        $orderData =  D('OcB2bOrder')->field("uc_code,real_amount")->where($where)->select();
        if(empty($orderData)) return $this->res(null,8096);

        # 判断金额是否比限制金额大
        foreach($orderData as $val){

                if($val['real_amount'] > C("UC_PAY_DATA")['uc_pay_amount']){
                    #根据uc_code判断是否已经添加过 if(add) update num  else  add data
                    $ucCodeWhere = array(
                        'uc_code'=> $val['uc_code'],
                    );
                    $fcActionLog = D('FcActionLog');
                    $logData = $fcActionLog->field('uc_code,pay_privs,num,update_time')->where($ucCodeWhere)->find();

                    # 判断是否已经满足权限
                    if($logData['pay_privs'] != FC_PAY_PRIVS_YES){
                        # 是否查到对应订单
                        if(empty($logData)){
                            $addData = array(
                                'uc_code' => $val['uc_code'],
                                'create_time' => NOW_TIME,
                                'update_time' => NOW_TIME,
                                'pay_method_type' => FC_TYPE_UCPAY,
                                'num' => '1',
                            );
                            # 判断当前计数的数字是否已经到达获取权限的条件
                            if($addData['num'] == C("UC_PAY_DATA")['uc_pay_num'] ){
                                $addData['pay_privs'] = FC_PAY_PRIVS_YES;
                            }

                            $add = $fcActionLog->add($addData);
                            if(!$add) return $this->res(null,8097);
                        }else{
                            // 判断当前用户是否已经获取权限 && 判断当前计数的数字小于到达获取权限的条件
                            if($logData['pay_privs'] == FC_PAY_PRIVS_NO && $logData['num'] < C("UC_PAY_DATA")['uc_pay_num']){

                                $upNum = array(
                                    'num' => bcadd($logData['num'], C("UC_PAY_DATA")['uc_pay_number']),
                                    'update_time' => NOW_TIME,
                                );
                                # 判断当前计数的数字是否已经到达获取权限的条件
                                if($upNum['num'] == C("UC_PAY_DATA")['uc_pay_num'] ){
                                    $upNum['pay_privs'] = FC_PAY_PRIVS_YES;
                                }

                                //计数最后修改时间 大于今天0:0:0 与订单最后修改时间小于当前时间 则不能修改数据
                                if($logData['update_time'] < $today){
                                    $update = $fcActionLog->where($ucCodeWhere)->data($upNum)->save();
                                    if(!$update) return $this->res(null,8051);
                                }
                            }
                        }
                    }
                }
        }
        return $this->res(true);

    }

    public function payDetail($params){


        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  商家编码			* 必须字段
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  付款编码			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = array(
            'fc_code'=>$params['fc_code'],
            'sc_code'=>$params['sc_code']
        );

        $where = ['confirm.f_status'=>FC_F_STATUS_PAYMENT,
            'confirm.fc_code'=>$params['fc_code'],
            'confirm.sc_code'=>$params['sc_code']
        ];
        $pay_ment = D('FcOrderPayment')->where($data)->select();
        $field = 'confirm.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,ext.commercial_name,b2b.pay_method,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,ext.remit_code';
        $list_confirm = D('FcOrderConfirm')->field($field)->alias('confirm')
            ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
            ->join("{$this->tablePrefix}oc_b2b_order_extend ext ON b2b.op_code=ext.op_code",'LEFT')
            ->join("{$this->tablePrefix}sc_store store ON store.sc_code=confirm.sc_code",'LEFT')
            ->where($where)
            ->select();


        $list['pay_ment'] = $pay_ment[0];
        $list['pay_confirm'] = $list_confirm;

        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($list['pay_confirm'] as $k=>$v){
            $list['pay_confirm'][$k] =$v;
            $list['pay_confirm'][$k]['pay_method'] =$status->getPayMethod($v['pay_method']);
            $list['pay_confirm'][$k]['pay_time'] = date('Y-m-d H:i:s',$v['pay_time']);

        }
        return $this->endInvoke($list);

    }

}

?>
