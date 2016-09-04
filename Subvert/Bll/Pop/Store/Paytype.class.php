<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | 支付类型设置
 */

namespace Bll\Pop\Store;

use System\Base;

class Paytype extends Base {

    public function __construct() {
        parent::__construct();
    }
    public function update($params) {

	    try{
	        D()->startTrans();
	        $apiPath = "Base.StoreModule.Basic.Paytype.update";
	        $add_res = $this->invoke($apiPath, $params);
	        if($add_res['status'] != 0){
	        	return $this->res($add_res['response']['message'],$add_res['response']['status']);
	        }
	        $res = D()->commit();
	        if(FALSE === $res){
	            throw new \Exception('事务提交失败');
	        }
	        return $this->res($add_res['response']);
	    } catch (\Exception $ex) {
	        D()->rollback();
	        return $this->res(NULL,9001);
	    }
    }
    /*
    * Bll.Pop.Store.PayType.lists
    */
    public function lists($params) {
    	$api = 'Base.StoreModule.Basic.Paytype.lists';
   		$res = $this->invoke($api,$params);
   		return $this->res($res['response']);
    }
}
