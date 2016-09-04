<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 
 */

namespace Bll\Cms\Order;

use System\Base;

class PayMethod extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }

    /**
     * Bll.Cms.Order.PayMethod.addPayMethod
     * @param [type] $params [description]
     */
    public function addPayMethod($params){
    	try{
			D()->startTrans();
			$apiPath = "Base.OrderModule.Center.PayMethod.add";
			$iRes    = $this->invoke($apiPath, $params);
			if($iRes['status'] != 0){
			    return $this->endInvoke(NULL,4516,'',$iRes['message']);
			}
			D()->commit();
			return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4516,'','添加错误');
        }
    }
    	
    /**
     * Bll.Cms.Order.PayMethod.updatePayMethod
     * @param [type] $params [description]
     */
    public function updatePayMethod($params){
    	try{
    	    D()->startTrans();
			$apiPath    = "Base.OrderModule.Center.PayMethod.update";
			$uRes = $this->invoke($apiPath, $params);
    	    if($uRes['status'] != 0){
			    return $this->endInvoke(NULL,4518,'','更新失败');
    	    }
    	    D()->commit();
    	    return $this->endInvoke(true);
    	} catch (\Exception $ex) {
    	    D()->rollback();
    	    return $this->endInvoke(NULL,4518,'','更新失败');
    	}
    }

    /**
     * Bll.Cms.Order.PayMethod.getPayMethodInfo
     * @param [type] $params [description]
     */
    public function getPayMethodInfo($params){
    	$apiPath    = "Base.OrderModule.Center.PayMethod.getPayMethodInfo";
		$select_res = $this->invoke($apiPath, $params);
		if ($select_res['status'] != 0) {
			return $this->endInvoke(false);
		}
		return $this->endInvoke($select_res['response']);
    }

    /**
     * Bll.Cms.Order.PayMethod.listPayMethod
     * @param [type] $params [description]
     */
    public function listPayMethod($params){
    	$apiPath    = "Base.OrderModule.Center.PayMethod.lists";
    	$select_res = $this->invoke($apiPath, $params);
    	if ($select_res['status'] != 0) {
    		return $this->endInvoke(false);
    	}

    	$apiPath = "Base.OrderModule.Center.PayMethod.payMethods";
    	$payMethod_res = $this->invoke($apiPath);
    	if ($payMethod_res['status'] != 0) {
    		return $this->endInvoke(false);
    	}
    	$sData = array(
			'lists'      => $select_res['response'],
			'payMethods' => $payMethod_res['response'],
    		);
    	return $this->endInvoke($sData);
    }
    
    
    /**
     * Bll.Cms.Order.PayMethod.savePayMethodOrders
     * @param [type] $params [description]
     */
    public function savePayMethodOrders($params){
    	try{
			D()->startTrans();
			$apiPath = "Base.OrderModule.Center.PayMethod.savePayMethodOrders";
			$iRes    = $this->invoke($apiPath, $params);
			if($iRes['status'] != 0){
			    return $this->endInvoke(NULL,4516,'','排序错误');
			}
			D()->commit();
			return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4516,'','排序错误');
        }
    }
}