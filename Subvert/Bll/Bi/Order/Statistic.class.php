<?php

/**
 * +---------------------------------------------------------------------
 * | www.laingrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单分析类
 */

namespace Bll\Bi\Order;
use System\Base;

class Statistic extends Base
{
	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct(){
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }
  	
  	/**
  	 * Bll.Bi.Order.Statistic.orderStatistic
  	 * @param  [type] $params [description]
  	 * @return [type]         [description]
  	 */
 	public function orderStatistic($params){
 		$api = "Base.BicModule.Oc.Statistic.getOrderList";
 		$list_res = $this->invoke($api, $params);

 		if ($list_res['status'] != 0) {
 			return $this->endInvoke('', $list_res['status']);
 		}

 		$orderList   = $list_res['response']['lists'];
		$totalNum    = $list_res['response']['totalnum'];
		$page        = $list_res['response']['page'];
		$page_number = $list_res['response']['page_number'];
		$total_page  = $list_res['response']['total_page'];

		$storeApi      = "Base.BicModule.Oc.Statistic.storesInfo";
		$storesInfoRes = $this->invoke($storeApi);
		$storesInfo    = $storesInfoRes['response'];

		$customersApi     = "Base.BicModule.Oc.Statistic.customersInfo";
		$customersInfoRes = $this->invoke($customersApi);
		$customersInfo    = $customersInfoRes['response'];

		if (!empty($params['start_time']) && !empty($params['end_time'])) {
			$averData = $this->getLastMonth($params['start_time']);
		}

		if (!empty($params['pay_start_time']) && !empty($params['pay_end_time'])) {
			$averData = $this->getLastMonth($params['pay_start_time']);
		}
		if ($averData) {
			$aveMonthApi         = "Base.BicModule.Oc.Statistic.averageMonthTime";
			$averageMonthTimeRes = $this->invoke($aveMonthApi, $averData);
			$averageMonthTime    = $averageMonthTimeRes['response'];
		}
		// var_dump($orderList);
		if (!empty($orderList)) {
			foreach ($orderList as $key => $order) {
				if ($storesInfo[$order['sc_code']]) {
					$orderList[$key]['storeName'] = $storesInfo[$order['sc_code']]['name'];
				}else{
					$orderList[$key]['storeName'] = '';
				}

				if ($customersInfo[$order['uc_code']]) {
					$orderList[$key]['customerName'] = $customersInfo[$order['uc_code']]['name'];
				}else{
					$orderList[$key]['customerName'] = '';
				}

				if ($orderList[$key]['pay_time']) {
					$time = $orderList[$key]['pay_time'] - $orderList[$key]['create_time'];

					$orderList[$key]['payTimeLong'] = $this->timeLong($time);
				}else{
					$orderList[$key]['payTimeLong'] = '';
				}

				if ($order['pay_type'] === 'ONLINE') {
					if ($orderList[$key]['ship_time'] && $orderList[$key]['pay_time']) {
						$time = $orderList[$key]['ship_time'] - $orderList[$key]['pay_time'];
						$orderList[$key]['shipTimeLong'] = $this->timeLong($time);
					}else{
						$orderList[$key]['shipTimeLong'] = '';
					}
				}else{
					if ($orderList[$key]['ship_time']) {
						$time = $orderList[$key]['ship_time'] - $orderList[$key]['create_time'];
						$orderList[$key]['shipTimeLong'] = $this->timeLong($time);
					}else{
						$orderList[$key]['shipTimeLong'] = '';
					}
				}
				

				if ($averageMonthTime) {
					$orderList[$key]['average_pay_time']  = $this->timeLong($averageMonthTime['average_pay_time']);
					$orderList[$key]['average_ship_time'] = $this->timeLong($averageMonthTime['average_ship_time']);
				}else{
					$orderList[$key]['average_pay_time']  = '--';
					$orderList[$key]['average_ship_time'] = '--';
				}
				$orderList[$key]['pay_time']    = empty($order['pay_time'])? '' : date("Y.m.d H:i:s", $order['pay_time']);
				
				$orderList[$key]['create_time'] = empty($order['create_time'])? '' : date("Y.m.d H:i:s", $order['create_time']);
				
				$orderList[$key]['ship_time']  = empty($order['ship_time'])? '' : date("Y.m.d H:i:s", $order['ship_time']);
			}
		}
		$orderStatisticRes = array(
				'orderList'     => $orderList,
				'totalNum'      => $totalNum,
				'page'          => $page,
				'page_number'   => $page_number,
				'total_page'    => $total_page,
				'storesInfo'    => $storesInfo,
				'customersInfo' => $customersInfo,
			);  
		return $this->endInvoke($orderStatisticRes);

 	}

 	/**
 	 * 
 	 * Bll.Bi.Order.Statistic.export
 	 * @param  [type] $params [description]
 	 * @return [type]         [description]
 	 */
 	public function export($params){
 		$apiPath = "Base.BicModule.Oc.Statistic.export";
 		$call = $this->invoke($apiPath,$params);
        return $this->endInvoke($call['response']);
 	}

 	/**
 	 * 付款和发货，时长计算
 	 * @param  [type] $params [description]
 	 * @return [type]         [description]
 	 */
 	public function timeLong($time){
 		$time = round($time/60, 2);
 		return $time.'分钟';
 	}

 	/**
 	 * 获取上个月
 	 * @param  [type] $params [description]
 	 * @return [type]         [description]
 	 */
 	public function getLastMonth($params){
 		if (empty($params)) {
 			$params = NOW_TIME;
 		}
 		$firstday  = date("Y-m-01", $params);
 		$lastMonth = date("m",strtotime("$firstday -1 month"));
 		$year      = date('Y', $params);
 		$res = array(
 			'year'  => $year,
 			'month' => $lastMonth,
 			);
 		return $res;
 	}



}