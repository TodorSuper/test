<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | Boss版登陆
 */

namespace Bll\Boss\Stasitc;

use System\Base;
class Order extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



    /**
     * @api  Boss版首页统计接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Stasitc.Order.all
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function all($params){

        // 如果存在设备安全码 检查更新
        if(!empty($params['device_token'])){
            try{
                D()->startTrans();
                $apiPath = "Base.UserModule.User.User.checkDevice";
                $device_res = $this->invoke($apiPath, $params);
                if($device_res['status'] != 0){
                    return $this->endInvoke(NULL,$device_res['status'],'',$device_res['message']);
                }
                $commit_res = D()->commit();
                if($commit_res === FALSE){
                    return $this->endInvoke(NULL,17);
                }
            } catch (\Exception $ex) {
                D()->rollback();
                return $this->endInvoke(NULL,$device_res['status']);
            } 
        }

        // 今天数据
        $today_params = array(
            'sc_code' => $params['sc_code'],
            'start_time'=>strtotime(date('Y-m-d',NOW_TIME)),
            'end_time'=>NOW_TIME,
            );
        $apiPath = "Base.OrderModule.B2b.Statistics.all";
        $today_res = $this->invoke($apiPath,$today_params);
        if($today_res['status'] != 0){
            return $this->endInvoke(NULL,$today_res['status'],'',$today_res['message']);
        }
        $today = app_change_price($today_res['response']['total_cope_amount']);

        // 店铺信息
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $store_res = $this->invoke($apiPath,$params);
        if($store_res['status'] != 0){
            return $this->endInvoke(NULL,$store_res['status'],'',$store_res['message']);
        }

        $res = array(
            'today_amount'=>empty($today['amount']) ? 0 : $today['amount'],
            'today_amount_unit'=>empty($today['unit']) ? '' : $today['unit'],
            'today_orders'=>empty($today_res['response']['total_order_num']) ? 0 : $today_res['response']['total_order_num'],
            'store_create_time'=>$store_res['response']['create_time'],
            'now_time'=>NOW_TIME,
            );
        return $this->endInvoke($res);

    }


    /**
     * @api  Boss版月度统计
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Stasitc.Order.month
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function month($params){

        if($params['_version'] == "1.1.0"){
            $apiPath = "Base.OrderModule.B2b.Statistics.oldmonth";
        }else{
            $apiPath = "Base.OrderModule.B2b.Statistics.month";
        }
        
        $order_data = $this->invoke($apiPath,$params);
        if($order_data['status'] != 0){
            return $this->endInvoke(NULL,$order_data['status'],'',$order_data['message']);
        }
        return $this->endInvoke($order_data['response']);
    }

    /**
     * @Desc    : Boss版 获取本天,本周 ,本月的订单数与交易金额
     * @ApiPath : Bll.Boss.Stasitc.Order.day
     * @Date    : 2015-12-1
     * @Author  : heweijun@liangrenwang.com
     */
    public function day($params){
        $apiPath = "Base.OrderModule.B2b.Statistics.day";
        $order_data = $this->invoke($apiPath, $params);
        if($order_data['status'] != 0){
            return $this->endInvoke(NULL,$order_data['status'],'',$order_data['message']);
        }
        return $this->endInvoke($order_data['response']);
    }





















}

?>