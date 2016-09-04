<?php

/**
 * +---------------------------------------------------------------------
 * | www.laingrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 
 */

namespace Bll\Bi\Item;
use System\Base;

class Item extends Base
{

    public function __construct(){
        parent::__construct();
        // self::$uc_prefix = 'Ic';
    }

    /**
     * Bll.Bi.Item.Item.statistic
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function statistic($params){
		$apiPath  = "Base.BicModule.Ic.Item.itemStatistic";
		$list_res = $this->invoke($apiPath, $params);
		if ($list_res['status'] != 0) {
			return $this->endInvoke('', $list_res['status']);
		}

		$lists       = $list_res['response']['lists'];
		$totalNum    = $list_res['response']['totalnum'];
		$page        = $list_res['response']['page'];
		$page_number = $list_res['response']['page_number'];
		$total_page  = $list_res['response']['total_page'];
		$allNum      = $list_res['response']['allNum'];
		$onNum       = $list_res['response']['onNum'];
		$offNum      = $list_res['response']['offNum'];

		$storeApiPath = "Base.BicModule.Sc.Store.lists";
		$store_res    = $this->invoke($storeApiPath, $params);
		$storesInfo   = $store_res['response'];

		$storeList = array(
			'lists'        => $lists,
			'totalNum'     => $totalNum,
			'page'         => $page,
			'page_number'  => $page_number,
			'total_page'   => $total_page,
			'allStatusOFF' => $offNum,
			'allStatusON'  => $onNum,
			'storesInfo'   => $storesInfo,
			);
		return $this->endInvoke($storeList);
    }

    /**
     * Bll.Bi.Item.Item.export
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function export($params){
    	$apiPath  = "Base.BicModule.Ic.Item.export";
    	$list_res = $this->invoke($apiPath, $params);
    	if ($list_res['status'] != 0) {
    		return $this->endInvoke('', $list_res['status']);
    	}

    	return $this->endInvoke($list_res['response']);
    }

}