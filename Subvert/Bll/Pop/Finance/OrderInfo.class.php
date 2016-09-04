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

namespace Bll\Pop\Finance;

use System\Base;

class OrderInfo extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Fc';
    }

    /**
     *
     * 财务交易列表
     * Bll.Pop.Finance.OrderInfo.tradeLists
     * @param array $params
     */
    public function tradeLists($param)
    {
       // $param['sc_code'] = '1020000000026';
        $apiPath = "Base.FcModule.Detail.Order.orderList";
        $list = $this->invoke($apiPath, $param);
        //支付方式
        $apiPath = "Base.OrderModule.B2b.Status.getPayMethod";
        $model = M($apiPath);
        $pay_method = $model->getPayMethod();
        $list['response']['pay_method'] = $pay_method;
        return $this->endInvoke($list['response']);

    }

    /**
     * 财务已转账交易列表
     * Bll.Pop.Finance.OrderInfo.payMentList
     * @param array $params
     */
    public function  payMentList($param)
    {
        $apiPath = "Base.FcModule.Detail.Order.findPayment";
        $list = $this->invoke($apiPath, $param);
        return $this->endInvoke($list['response']);
    }

    /**
     *
     * pop财务已转账汇总单详情
     * Bll.Pop.Finance.OrderInfo.payMentDetial
     * @param array $params
     */
    public function payMentDetial($param)
    {
		$apiPath = "Base.FcModule.Detail.Order.payDetail";
        $list = $this->invoke($apiPath, $param);

        return $this->endInvoke($list);

    }





}

?>
