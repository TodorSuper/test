<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop提现查询数据导出
 */

namespace Bll\Pop\Finance;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Fc';
    }

    /**
     * Bll.Pop.Finance.Export.export
     * @param array $params
     * @return array
     */
    public function  export($params){
        $type = $params['fc_type'] ;
        $group = $params['group'];
        $callback_api = '';
        switch($type)
        {
            //点单列表
            case 'paymentOrderList':
                $params['filename'] = 'paymentOrderList';
                if($group){//是否确认
                    $params['status'] = 2;
                    $params['f_status'] = 1;
                    $params['pay_status'] = OC_ORDER_PAY_STATUS_PAY;
                    $params['title']  =  array('订单编号','商家名称','付款时间','订单金额','买家实付','支付流水号','支付方式','确认打款时间','点单状态');  //默认导出列标题
                    $params['fields'] = 'tci.b2b_code,store.name as store_name,tci.pay_time,tci.order_amout,tci.real_amount as price,ss.pay_no,tci.pay_method,confirm.update_time';
                    $callback_api = 'Com.Callback.Export.FcExport.paymentOrderOnList';
                }else{
                    $params['status'] = 1;
                    $params['f_status'] = 1;
                    $params['title']  =  array('订单编号','商家名称','付款时间','订单金额','买家实付','支付流水号','支付方式','点单状态');  //默认导出列标题
                    $params['fields'] = 'tci.b2b_code,store.name as store_name,tci.pay_time,tci.order_amout,tci.real_amount as price,ss.pay_no,tci.pay_method';
                    $callback_api = 'Com.Callback.Export.FcExport.paymentOrderList';
                }
                break;
            //付款单待付款列表
            case 'noConfirm':

                $params['filename'] = 'noConfirm';
                $params['title']  =  array('订单编号','商家名称','订单金额','开户名','开户行','银行账号');  //默认导出列标题
                $params['fields'] = 'tci.b2b_code,store.name as store_name,tci.order_amout,store.account_name,store.account_bank,store.account_no';
                $callback_api = 'Com.Callback.Export.FcExport.noConfirm';

                break;
            //pop交易未转账列表
            case 'orderList':
                $start_time = $params['start_time'];
                $end_time 	= $params['end_time'];
                !empty($start_time) ? $params['pay_time'] = ['egt' =>  strtotime($start_time)] : null;
                !empty($end_time)?$params['pay_time'] = ['between',[strtotime($start_time), strtotime($end_time)+86399]] : null;
                $params['f_status'] = [1,2];  # 未支付状态
                $params['filename'] = 'orderList';
                $params['title']  =  array('订单编号','客户名称','店铺名称','支付方式','买家付款时间','订单金额','买家实付','转账状态');  //默认导出列标题
                $params['fields'] = 'tci.b2b_code,tci.client_name,sc.commercial_name,tci.pay_method,tci.pay_time,tci.real_amount,tci.real_amount as price,ss.pay_no,tci.order_amout,sc.coupon_amount';
                $callback_api = 'Com.Callback.Export.FcExport.orderList';
                break;
        }

        $params['callback_api'] = $callback_api;

        $apiPath  = "Base.FcModule.Detail.Export.export";

        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }



    /**
     * Bll.Pop.Finance.Export.export
     * @param array $params
     * @return array
     */
}

?>
