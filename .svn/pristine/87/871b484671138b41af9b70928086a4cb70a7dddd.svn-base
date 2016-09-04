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

namespace Bll\Bi\Advance;
use System\Base;

class AdvanceStatistic extends Base
{
	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct(){
        parent::__construct();
        self::$uc_prefix = 'Tc';
    }

 	/**
 	 * Bll.Bi.Advance.AdvanceStatistic.statistic
 	 * @param  [type] $params [description]
 	 * @return [type]         [description]
 	 */
	public function statistic($params){

		$api = "Base.BicModule.Tc.AdvanceStatistic.getAdvanceList";
		$list_res = $this->invoke($api, $params);

		if ($list_res['status'] != 0) {
			return $this->endInvoke('', $list_res['status']);
		}
		
		$advanceList = $list_res['response']['lists'];
		$totalNum    = $list_res['response']['totalnum'];
		$page        = $list_res['response']['page'];
		$page_number = $list_res['response']['page_number'];
		$total_page  = $list_res['response']['total_page'];
		
		$ucMemberApi     = "Base.BicModule.Tc.AdvanceStatistic.ucMemberInfo";
		$ucMemberInfoRes = $this->invoke($ucMemberApi);
		$ucMemberInfo    = $ucMemberInfoRes['response'];

		$scSalesmanApi     = "Base.BicModule.Tc.AdvanceStatistic.scSalesmanInfo";
		$scSalesmanInfoRes = $this->invoke($scSalesmanApi);
		$scSalesmanInfo    = $scSalesmanInfoRes['response'];
		
		$ucMerchantApi     = "Base.BicModule.Tc.AdvanceStatistic.ucMerchantInfo";
		$ucMerchantInfoRes = $this->invoke($ucMerchantApi);
		$ucMerchantInfo    = $ucMerchantInfoRes['response'];

		$storeApi      = "Base.BicModule.Oc.Statistic.storesInfo";
		$storesInfoRes = $this->invoke($storeApi);
		$storesInfo    = $storesInfoRes['response'];

		if (!empty($advanceList)) {
			foreach ($advanceList as $key => $advace) {
					if ($ucMemberInfo[$advace['code']]) {
						$advanceList[$key]['commercial_name'] = $ucMemberInfo[$advace['code']]['commercial_name'];
						$advanceList[$key]['invite_code']     = $ucMemberInfo[$advace['code']]['invite_code'];		
					}else{
						$advanceList[$key]['commercial_name'] = '';		
					}

					if ($scSalesmanInfo[$advanceList[$key]['invite_code']]) {
						$advanceList[$key]['invite_person'] = $scSalesmanInfo[$advanceList[$key]['invite_code']]['name'];
					}

					$advanceList[$key]['recharge'] = round($advanceList[$key]['total_balance'] - $advanceList[$key]['free_balance'], 2);
					if ($advanceList[$key]['recharge'] == 0) {
						$advanceList[$key]['recharge'] = '0.00';
					}
					$advanceList[$key]['create_time'] = date('Y.m.d H:i:s', $advanceList[$key]['create_time']);
			}
		}

		$advanceStatisticRes = array(
				'advanceList'    => $advanceList,
				'totalNum'       => $totalNum,
				'page'           => $page,
				'page_number'    => $page_number,
				'total_page'     => $total_page,
				'storesInfo'     => $storesInfo,
				'ucMemberInfo'   => $ucMemberInfo,
				'ucMerchantInfo' => array_unique(array_filter(array_column($ucMerchantInfo, 'salesman'))),
			);  
		return $this->endInvoke($advanceStatisticRes);

	}

	/**
	 * 
	 * Bll.Bi.Advance.AdvanceStatistic.export
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function export($params){
		$apiPath = "Base.BicModule.Tc.AdvanceStatistic.export";
		$call = $this->invoke($apiPath,$params);
	   return $this->endInvoke($call['response']);
	}
}
  	