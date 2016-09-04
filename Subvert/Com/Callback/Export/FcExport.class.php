<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 交易中心 导出  回调接口
 */

namespace Com\CallBack\Export;

use System\Base;

class FcExport extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    //财务订单列表
    public function orderList(&$data){
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($data as $key=>$v){
            if($v['pay_time']){
                $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
            }
            if($v['coupon_amount'] > 0){
                $data[$key]['real_amount'] = bcadd($v['real_amount'],$v['coupon_amount'],2);
                $data[$key]['price'] = bcadd($v['real_amount'],$v['coupon_amount'],2);
            }

            $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
            $data[$key]['type'] = '未转账';
            unset($data[$key]['pay_status']);
            unset($data[$key]['coupon_amount']);
            unset($data[$key]['order_amout']);
            unset($data[$key]['order_status']);
            unset($data[$key]['pay_no']);
            unset($data[$key]['oc_code']);
        }
    }
    //财务未确认订单列表
    public function paymentOrderList(&$data){
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($data as $key=>$v){
            if($v['pay_time']){
                $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
            }
            if($v['update_time']){
                $data[$key]['update_time'] =date('Y-m-d H:i:s',$v['update_time']);
            }
            $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
            if( $v['account_status'] == 'ACCOUNT'){
                if($v['pay_method'] == '微信支付'){
                    $data[$key]['account_amount'] =  number_format($v['amount']-$v['cost'],2);
                }else{
                    $data[$key]['account_amount'] =  number_format($v['amount'],2);
                }
            }
            if($v['pay_method'] == '先锋支付'){
                if($v['amount']>1000){
                    $data[$key]['cost'] = number_format($v['amount']*0.002,2);
                }else{
                    $data[$key]['cost'] = 2.00;
                }
            }else{
                $data[$key]['cost'] = number_format($v['cost'],2);
            }
            $data[$key]['amount'] =  number_format($v['amount'],2);
            $data[$key]['type'] = '未确认';
        }
    }
    //财务已确认订单列表
    public function paymentOrderOnList(&$data){

        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($data as $key=>&$v){
            if($v['pay_time']){
                $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
            }
            if($v['update_time']){
                $data[$key]['update_time'] =date('Y-m-d H:i:s',$v['update_time']);
            }
            if($data[$key]['pay_method'] == 'REMIT'){
                $data[$key]['pay_method'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($data[$key]['ext1']);
            }else{
                $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
            }
            if( $v['account_status'] == 'ACCOUNT'){
                $data[$key]['bank_type'] = M('Base.FcModule.Payment.Status.getBankStatus')->getBankStatus($data[$key]['bank_type']);
                if($v['pay_method'] == '微信支付'){

                    $data[$key]['account_amount'] =  number_format($v['amount']-$v['cost'],2);
                }else{
                    $data[$key]['account_amount'] =  number_format($v['amount'],2);
                }
            }else{
                $data[$key]['bank_type'] = '';
            }
            if($v['pay_method'] == '先锋支付'){
                if($v['amount']>1000){
                    $data[$key]['cost'] = number_format($v['amount']*0.002,2);
                }else{
                    $data[$key]['cost'] = 2.00;
                }
            }else{
                $data[$key]['cost'] = number_format($v['cost'],2);
            }
            if($v['coupon_amount']>0){
                $data[$key]['order_amount'] = number_format(bcadd($v['amount'],$v['coupon_amount'],2),2);
                $data[$key]['coupon_amount'] = number_format($v['coupon_amount'],2);
            }else{
                $data[$key]['order_amount'] = number_format($v['amount'],2);
                $data[$key]['coupon_amount'] = '';
            }
            $data[$key]['amount'] =  number_format($v['amount'],2);
            $data[$key]['type'] = '已确认';
        }
    }

    //预付款未确认订单列表
    public function paymentOrderSure(&$data){
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($data as $key=>&$v){
            if($v['pay_time']){
                $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
            }
            if($v['update_time']){
                $data[$key]['update_time'] =date('Y-m-d H:i:s',$v['update_time']);
            }
            if($data[$key]['pay_method'] == 'REMIT'){
                $data[$key]['pay_method'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($data[$key]['pay_method_ext1']);
            }else{
                $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
            }
            if( $v['account_status'] == 'ACCOUNT'){
                if($v['pay_method'] == '微信支付'){
                    $data[$key]['account_amount'] =  number_format($v['amount']-$v['cost'],2);
                }else{
                    $data[$key]['account_amount'] =  number_format($v['amount'],2);
                }
            }
            if($v['pay_method'] == '先锋支付'){
                if($v['amount']>1000){
                    $data[$key]['cost'] = number_format($v['amount']*0.002,2);
                }else{
                    $data[$key]['cost'] = 2.00;
                }
            }else{
                $data[$key]['cost'] = number_format($v['cost'],2);
            }
            $data[$key]['amount'] = number_format($v['amount'],2);
            $data[$key]['type'] = '未确认';
        }
    }
    //预付款已确认订单列表
    public function paymentOrderNoSure(&$data){
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($data as $key=>$v){
            if($v['pay_time']){
                $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
            }
            if($v['update_time']){
                $data[$key]['update_time'] =date('Y-m-d H:i:s',$v['update_time']);
            }
            if($data[$key]['pay_method'] == 'REMIT'){
                $data[$key]['pay_method'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($data[$key]['pay_method_ext1']);
            }else{
                $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
            }
            if( $v['account_status'] == 'ACCOUNT'){
                if($v['pay_method'] == '微信支付'){
                    $data[$key]['account_amount'] =  number_format($v['amount']-$v['cost'],2);
                }else{
                    $data[$key]['account_amount'] =  number_format($v['amount'],2);
                }
            }
            if($v['pay_method'] == '先锋支付'){
                if($v['amount']>1000){
                    $data[$key]['cost'] = number_format($v['amount']*0.002,2);
                }else{
                    $data[$key]['cost'] = 2.00;
                }
            }else{
                $data[$key]['cost'] = number_format($v['cost'],2);
            }
            $data[$key]['amount'] = number_format($v['amount'],2);
            $data[$key]['type'] = '以确认';
        }
    }
    //财务带付款列
    public function noConfirm($data){
        foreach($data as $key=>$v){
            if($v['pay_time']){
                $data[$key]['pay_time']=date('Y-m-d H:i:s',$v['pay_time']);
            }
            $data[$key]['pay'] = '待付款';
        }
    }

    //财务已转出财务e
    public function payMentList(&$data)
    {
        $list = array();
        foreach ($data as $key => $v) {
            $cData = array(
                'confirm.sc_code'=>$v['sc_code'],
                'confirm.fc_code' =>$v['fc_code'],
            );
            $field = 'adv.amount as adv_amount,adv.adv_code,adv.pay_method as adv_pay_method,
                 adv.pay_method_ext1,adv.pay_time as adv_pay_time,confirm.b2b_code,b2b.real_amount as amount,
                 b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,ext.commercial_name,
                 b2b.pay_method,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,ext.remit_code,
                 um.commercial_name as um_commercial_name,um.name as um_name';

                $list_confirm = D('FcOrderConfirm')->field($field)->alias('confirm')
                    ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                    ->join("{$this->tablePrefix}oc_b2b_order_extend ext ON b2b.op_code=ext.op_code",'LEFT')
                    ->join("{$this->tablePrefix}sc_store store ON store.sc_code=confirm.sc_code",'LEFT')
                    ->join("{$this->tablePrefix}oc_advance adv ON confirm.b2b_code=adv.adv_code",'LEFT')
                    ->join("{$this->tablePrefix}uc_member um ON um.uc_code=adv.uc_code",'LEFT')
                    ->where($cData)
                    ->select();
            $status = M('Base.OrderModule.B2b.Status.getPayMethod');

            foreach($list_confirm as $k=>&$v_1){

                if($v_1['adv_amount']){
                    $v_1['amount'] = $v_1['adv_amount'];
                    $v_1['b2b_code'] = $v_1['adv_code'];
                    $v_1['pay_method'] = $v_1['adv_pay_method'];
                    $v_1['pay_time'] = $v_1['adv_pay_time'];
                    $v_1['commercial_name'] = $v_1['um_commercial_name'];
                    $v_1['client_name'] = $v_1['um_name'];
                }
                $v_1['pay_method'] = $status->getPayMethod($v_1['pay_method']);
                $v_1['pay_time'] = date('Y-m-d H:i:s',$v_1['pay_time']);
            }

            if(!empty($list_confirm)){
                $data[$key]['pay_confirm'] = $list_confirm;
            }
            $data[$key]['affirm_time'] = date('Y-m-d H:i:s', $v['affirm_time']);


        }
    }

    //待生成
    public function waitList(&$data)
    {

        //查询出商家名称复制到数组中。
		$cApiPath = "Base.StoreModule.Basic.User.getMerchantLists";
		$sc_code  = array_unique(array_column($data, 'sc_code'));
        $store_res = $this->invoke($cApiPath, array('sc_code'=>$sc_code));
        $sc_name = [];
		if(0 == $store_res['status']){
			foreach($store_res['response'] as $v) {
				$sc_name[$v['sc_code']] = $v['name'];
			}
		}
        //支付方式的对应汉字解析。
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach ($data as $key => &$v) {
            if($v['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
                $v['oc_type'] = "商品订单";
                if($data[$key]['pay_method'] == 'REMIT'){
                    $data[$key]['pay_method'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($data[$key]['ext1']);
                }else{
                    $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
                }
                $v['amount'] = number_format($v['amount'],2);

            }
            if($v['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                $v['oc_type'] = "预付款充值订单";
                $v['amount'] = number_format($v['adv_amount'],2);
                if($data[$key]['adv_pay_method'] == 'REMIT'){
                    $data[$key]['pay_method'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($data[$key]['pay_method_ext1']);
                }else{
                    $data[$key]['pay_method'] = $status->getPayMethod($data[$key]['adv_pay_method']);
                }
                $v['b2b_code'] = $v['adv_code'];
            }

            //商家名称解析。
            $v['sc_name'] = $sc_name[$v['sc_code']];
            $v['account_no'] = rewrite($v['account_no']);
            $v['pay_time'] = date('Y-m-d',$v['pay_time']);
        }
    }

        //已汇总未付款
        public function noPayList(&$data)
        {
//         //支付方式的对应汉字解析。
            $status = M('Base.OrderModule.B2b.Status.getPayMethod');
            foreach ($data as $key => $v) {
                $cData = array(
                    'fc_code' =>$data[$key]['fc_code'],
                    'confirm_status' => 2,
                    'f_status' => 2,
                );

                $cApiPath = "Base.FcModule.Payment.Order.findConfirm";
                $cList = $this->invoke($cApiPath, $cData);
                $data[$key]['amount'] = number_format($v['amount'],2);
                $data[$key]['account_number'] = rewrite($v['account_number']);
                //商家名称解析。
                //$data[$key]['sc_name'] = $sc_name;
                foreach($cList['response']['lists'] as $k=>&$value){
                    if($value['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
                        $value['amount'] = number_format($v['amount'],2);
                        $value['oc_type_cn'] = "商品订单";
                    }
                    if($value['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                        $value['adv_amount'] = number_format($v['adv_amount'],2);
                        $value['oc_type_cn'] = "预付款充值订单";
                    }
                    $value['pay_method'] = $status->getPayMethod($value['pay_method']);
                    $value['pay_time'] = date('Y-m-d',$value['pay_time']);
                }
                $data[$key]['pay_confirm'] = $cList['response']['lists'];
            }
        }

        //已汇总已付款
        public function yesPayList(&$data,$params)
        {

//          //支付方式的对应汉字解析。
            $status = M('Base.OrderModule.B2b.Status.getPayMethod');
            foreach ($data as $key =>&$v) {

                $list = array();
                    $cData = array(
                        'confirm.sc_code'=>$v['sc_code'],
                        'confirm.fc_code' =>$v['fc_code'],
                    );
                    $field = 'adv.amount as adv_amount,adv.adv_code,adv.pay_method as adv_pay_method,adv.pay_method_ext1,confirm.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,ext.commercial_name,b2b.pay_method,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,ext.remit_code';
                    $list_confirm = D('FcOrderConfirm')->field($field)->alias('confirm')
                        ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                        ->join("{$this->tablePrefix}oc_b2b_order_extend ext ON b2b.op_code=ext.op_code",'LEFT')
                        ->join("{$this->tablePrefix}sc_store store ON store.sc_code=confirm.sc_code",'LEFT')
                        ->join("{$this->tablePrefix}oc_advance adv ON confirm.b2b_code=adv.adv_code",'LEFT')
                        ->where($cData)
                        ->select();

                    $status = M('Base.OrderModule.B2b.Status.getPayMethod');

                    foreach($list_confirm as $k=>&$v_1){

                        $list[$k] =$v_1;
                        if($list[$k]['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
                            $list[$k]['oc_type_cn'] = "商品订单";
                            $list[$k]['amount'] = number_format($v_1['amount'],2);
                        }
                        if($list[$k]['oc_type']==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                            $list[$k]['oc_type_cn'] = "预付款充值订单";
                            $list[$k]['adv_amount'] = number_format($v_1['adv_amount'],2);

                        }
                        $list[$k]['pay_method'] = $status->getPayMethod($v_1['pay_method']);
                        $list[$k]['pay_time'] = date('Y-m-d H:i:s',$v_1['pay_time']);
                    }

                    if(!empty($list)){
                        $data[$key]['pay_confirm'] = $list;
                    }


                $data[$key]['affirm_time'] = date('Y-m-d H:i:s', $v['affirm_time']);
            }
            $data['totalamount'] = $params['data']['totalamount'];
            $data['start_time'] = $params['data']['start_time'];
            $data['end_time'] = $params['data']['end_time'];

        }
    //下载平台代收款支付明细表
    public function cNAllLists(&$data,$params){
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        foreach($data as $key=>$v){
            if($v['pay_time']){
                $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
            }
            if($v['ext1']){
                $data[$key]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }

            if($v['update_time']){
                $data[$key]['update_time'] =date('Y-m-d H:i:s',$v['update_time']);
            }

            if($v['pay_method'] == PAY_METHOD_ONLINE_WEIXIN){
                $data[$key]['adv_cost'] = $v['amount']*0.006;//预计手续费金额

            }elseif($v['pay_method'] == PAY_METHOD_ONLINE_UCPAY){
                if($v['amount']>1000){
                    $data[$key]['adv_cost'] = $v['amount']*0.002;//先锋支付手续费,大于1000按照比率计算
                }else{
                    $data[$key]['adv_cost'] = 2.00;//先锋支付手续费,最低2.00￥
                }
            }else{
                $data[$key]['adv_cost'] = '0.00';//预计手续费金额
            }
            if($v['pay_method'] == PAY_METHOD_ONLINE_UCPAY){
                $data[$key]['adv_get_amount'] = $v['amount'];
                $data[$key]['get_amount'] = $v['amount'];
            }else{
                $data[$key]['adv_get_amount'] = bcsub($v['amount'],$data[$key]['adv_cost'],2);//预计到账金额
                $data[$key]['get_amount'] = bcsub($v['amount'],$data[$key]['adv_cost'],2);//平台账户到账金额
            }
            if($v['coupon_amount']>0){
                $data[$key]['order_amount'] = bcadd($v['real_amount'],$v['coupon_amount']);
            }else{
                $data[$key]['order_amount'] = $v['amount'];
            }

            $data[$key]['coupon_amount'] = $v['coupon_amount'];
            $data[$key]['cost_dif'] = bcsub($data[$key]['get_amount'],$data[$key]['adv_get_amount'],2);//手续费
            $data[$key]['cost_amount'] = "0.00";
            $data[$key]['type'] = '已确认';
            $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
            $data[$key]['account_no'] = rewrite($data[$key]['account_no']);
        }
    }
    public function advanceAllLists(&$data,$params){
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
            foreach($data as $key=>$v){
                if($v['pay_time']){
                    $data[$key]['pay_time']= date('Y-m-d H:i:s',$v['pay_time']);
                }
                if($v['ext1']){
                    $data[$key]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
                }

                if($v['update_time']){
                    $data[$key]['update_time'] =date('Y-m-d H:i:s',$v['update_time']);
                }

                if($v['pay_method'] == PAY_METHOD_ONLINE_WEIXIN){
                    $data[$key]['adv_cost'] = $v['amount']*0.006;//预计手续费金额
                }elseif($v['pay_method'] == PAY_METHOD_ONLINE_UCPAY){
                    if($v['amount']>1000){
                        $data[$key]['adv_cost'] = $v['amount']*0.002;//先锋支付手续费,大于1000按照比率计算
                    }else{
                        $data[$key]['adv_cost'] = 2.00;//先锋支付手续费,最低2.00￥
                    }
                }else{
                    $data[$key]['adv_cost'] = '0.00';//预计手续费金额
                }
                if($v['pay_method'] == PAY_METHOD_ONLINE_UCPAY){
                    $data[$key]['adv_get_amount'] = $v['amount'];
                    $data[$key]['get_amount'] = $v['amount'];
                }else{
                    $data[$key]['adv_get_amount'] = bcsub($v['amount'],$data[$key]['adv_cost'],2);//预计到账金额
                    $data[$key]['get_amount'] = bcsub($v['amount'],$data[$key]['adv_cost'],2);//平台账户到账金额
                }
                $data[$key]['cost_dif'] = bcsub($data[$key]['get_amount'],$data[$key]['adv_get_amount'],2);//手续费
                $data[$key]['cost_amount'] = "0.00";
                $data[$key]['pay_method'] = $status->getPayMethod($v['pay_method']);
                $data[$key]['account_no'] = rewrite($data[$key]['account_no']);
            }
    }

    /** Com.Callback.Export.FcExport.accountGoodsList
     *  导出财务对账商品订单表
     * */
    public function accountGoodsList(&$data){
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $balance_group = M('Base.FcModule.Account.Status.getBalanceStatus')->getBalanceStatus();
        foreach($data as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }
            if($v['pay_status']){
                $data[$k]['pay_status'] = M('Base.FcModule.Account.Status')->getPayStatus($v['pay_status']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['balance_status']){
                $data[$k]['balance_status'] = $balance_group[$v['balance_status']];
            }

            if($v['coupon_amount'] > 0){
                $data[$k]['order_amout'] = bcadd($v['amount'],$v['coupon_amount'],2);
            }
            if(strstr($v['bank_code'],",")){
                $bankCodeArray = empty($v['bank_code']) ?  : explode(",",$v['bank_code']);
                $nameArray = empty($v['pay_name']) ?  : explode(",",$v['pay_name']);
                $codeAndName = '';

                if(!empty($bankCodeArray) && !empty($nameArray)){
                    foreach($bankCodeArray as $key=>$item){
                        $codeAndName .= $bankCodeArray[$key]."<br>";

                    }
                }
            }else{
                $codeAndName = $v['bank_code']."<br>";
            }
            $data[$k]['codeAndName'] = $codeAndName;

        }
    }


    /** Com.Callback.Export.FcExport.accountAdvanceList
     *  导出财务对账商品订单表
     * */
    public function accountAdvanceList(&$data){
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $balance_group = M('Base.FcModule.Account.Status.getBalanceStatus')->getBalanceStatus();
        foreach($data as $k=>$v){
            $data[$k] = $v;
            if($v['pay_method_ext1']){
                $data[$k]['pay_method_ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['pay_method_ext1']);
            }
            if($v['status']){
                $data[$k]['status'] = M('Base.FcModule.Account.Status.getBalanceStatus')->getPayStatus($v['status']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['balance_status']){
                $data[$k]['balance_status'] = $balance_group[$v['balance_status']];
            }
        }
    }

    public function PaymentPaidList (&$data) {
        $fc_code = [];
        foreach($data as $val){
            $fc_code[] = $val['fc_code'];
        }
        $page_number = count($fc_code);
        $confirmWhere = array(
            'fc_code' => $fc_code,
        );
        #获取订单数量
        $countApiPath = "Base.FcModule.Payment.Payment.countNumber";
        $count = $this->invoke($countApiPath, $confirmWhere)['response']['lists'][0]['count'];

        $confirmWhere['page_number'] = $count;
        $confirmApiPath = "Base.FcModule.Payment.Payment.findConfirmLists";
        $confirmList = $this->invoke($confirmApiPath, $confirmWhere);
        $payMethodStatus = M('Base.OrderModule.B2b.Status.getPayMethod')->getPayMethod();
        $orderTypeLists = M('Base.FcModule.Account.Status.getStoreType')->getStoreType();
        $bankLists = M('Base.FcModule.Account.Status.getBankStatus')->getBankStatus();
        $ocTypeData = M('Base.FcModule.Payment.Status.getConfirmStatus')->getConfirmStatus();

        //合并数据
        foreach($data as &$val){
            foreach($confirmList['response']['lists'] as &$v){
                if($val['fc_code'] == $v['fc_code']){
                    $times = empty($v['oapay_time']) ?  "": date("Y-m-d",$v['oapay_time']);
                    $pay_time = empty($v['pay_time']) ? "" : date("Y-m-d",$v['pay_time']);
                    $val['account_number'] = rewrite($val['account_number']);
                    $val['bank_type'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($val['bank_type']);
                    $v['pay_time'] = empty($v['pay_time']) ? $times : $pay_time;
                    $v['amount'] = empty($v['real_amount']) ? number_format($v['amount'],2) : number_format($v['real_amount'],2);
                    $v['s_amount'] = empty($v['real_amount']) ? $v['amount'] : $v['real_amount'];
                    $v['name'] =  empty($v['obname']) ? $v['oaname'] : $v['obname'];
                    $v['pay_method'] =empty($v['oboPayMethod']) ? $payMethodStatus[$v['oaPayMethod']] : $payMethodStatus[$v['oboPayMethod']];
                    if($v['pay_method'] == 'UCPAY'){
                        $v['cost'] = '2.00';
                        if($v['adv_amount']>1000){
                            $v['cost'] = $v['adv_amount']*0.002;
                        }
                        $v['bank_amount'] = $v['s_amount'];
                    }else{
                        $v['bank_amount'] = number_format($v['s_amount'] - $v['cost'],2);
                        $v['cost'] = empty($v['cost']) ? 0 :  $v['cost'];
                    }
                    if($v['coupon_amount'] > 0){
                        $v['amount'] = bcadd($v['amount'],$v['coupon_amount'],2);
                    }
                    $v['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);

                    $v['commercial_name'] =  empty($v['obcommercial_name']) ? $v['oacommercial_name'] : $v['obcommercial_name'];
                    $v['update_time'] = empty($v['update_time']) ? '': date("Y-m-d H:i:s", $v['update_time']);
                    $v['check_time'] = empty($v['check_time']) ? '': date("Y-m-d H:i:s",$v['check_time']);
                    if($v['pay_method'] == 'REMIT') $v['pay_time'] =  date("Y-m-d H:i:s",$v['update_time']);
                        else $v['pay_time']  = date("Y-m-d H:i:s",$v['update_time']);
                    $v['oc_type'] = empty($v['order_type']) ? $ocTypeData[$v['oc_type']] : $orderTypeLists[$v['order_type']];

                    if(strstr($v['bank_code'],",") && strstr($v['pay_name'],",")){
                        $bankCodeArray = empty($v['bank_code']) ?  : explode(",",$v['bank_code']);
                        $nameArray = empty($v['pay_name']) ?  : explode(",",$v['pay_name']);
                        $codeAndName = '';
                        if(!empty($bankCodeArray) && !empty($nameArray)){
                            foreach($bankCodeArray as $key=>$item){
                                $codeAndName .= $bankCodeArray[$key]."<br>";
                                if($v['pay_name'] == $v['account_name']){
                                    $codeAndName .= "<span style='color:red;'>".$nameArray[$key] ."</span><br>";
                                }else{
                                    $codeAndName .= $nameArray[$key] ."<br>";
                                }
                            }
                        }
                    }else{
                        $codeAndName = $v['bank_code']."<br>".$v['pay_name']."<br>";
                    }
                    $v['codeAndName'] = $codeAndName;

                    $val['orderLists'][] = $v;

                }
            }
        }
    }

    /**Com.Callback.Export.FcExport.allOrderList
     * 财务审单&制单&付款 全部订单页导出
     *
    */
    public function  allOrderList(&$data){
        $pay_method_group = M('Base.FcModule.Account.Status.getPayMethod')->getPayMethod();
        $order_type = M('Base.FcModule.Account.Status.getOrderType')->getOrderType();
        $pay_status_group = M('Base.FcModule.Account.Status.getPayStatus')-> getPayStatus();
        foreach($data as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }
            if($v['pay_method_ext1']){
                $data[$k]['pay_method_ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['pay_method_ext1']);
            }
            if($v['pay_method']){
                $data[$k]['pay_method'] = $pay_method_group[$v['pay_method']];
            }
            if($v['bank_type']){
                $data[$k]['bank_type'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($v['bank_type']);
            }
            if($v['adv_pay_method']){
                $data[$k]['adv_pay_method'] = $pay_method_group[$v['adv_pay_method']];
            }
            if($v['pay_status']){
                $data[$k]['pay_status'] =$pay_status_group[$v['pay_status']];
            }
            if($v['adv_status']){
                $data[$k]['adv_status'] = $pay_status_group[$v['adv_status']];
            }
            if($v['coupon_amount'] > 0){
                $data[$k]['account_amount'] = $v['amount'];
                $data[$k]['amount'] = bcadd($v['amount'], $v['coupon_amount'],2);

            }

            if($v['oc_type'] == FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
                $data[$k]['oc_type'] = $order_type[$v['oc_type']];
                $data[$k]['order_type_info'] = $order_type[$v['oc_type']];
                $data[$k]['order_status_info'] = $pay_status_group[$v['adv_status']];
            }else{
                $data[$k]['order_type'] = $order_type[$v['order_type']];
                $data[$k]['order_type_info'] = $order_type[$v['order_type']];
                if($v['order_status']=="COMPLETE" && $v['pay_status']=="PAY"){
                    $data[$k]['order_status_info'] = '已完成';
                }else{
                    $data[$k]['order_status_info'] = $pay_status_group[$v['pay_status']];;
                }
            }
            if(strstr($v['bank_code'],",") && strstr($v['pay_name'],",")){
                $bankCodeArray = empty($v['bank_code']) ?  : explode(",",$v['bank_code']);
                $nameArray = empty($v['pay_name']) ?  : explode(",",$v['pay_name']);
                $codeAndName = '';
                if(!empty($bankCodeArray) && !empty($nameArray)){
                    foreach($bankCodeArray as $key=>$val){
                        $codeAndName .= $bankCodeArray[$key]."<br>";
                        if($v['pay_name'] == $v['account_name']){
                            $codeAndName .= "<span style='color:red;'>".$nameArray[$key] ."</span><br>";
                        }else{
                            $codeAndName .= $nameArray[$key] ."<br>";
                        }
                    }
                }
            }else{
                $codeAndName = $v['bank_code']."<br>".$v['pay_name']."<br>";
            }

            $data[$k]['codeAndName'] = $codeAndName;



        }
    }
}
?>
