<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */

namespace Bll\Pop\Store;

use System\Base;

class Sms extends Base {

	public function __construct(){
        parent::__construct();
	}

	
	/**
	 * Bll.Pop.Store.Sms.addLinkMan
	 * @param [type] $params [description]
	 */
	public function addLinkMan($params){
		// $data = array();
		// $data['sms_type'] = SC_SMS_NEW_DELIVERY;
		// $data['uc_code'] = 1120000000103;
		// $data['sc_code'] = 1020000000026;
		// $data['data'][] = array('storelinkman' =>'小虎儿', 'phone'=>'15010855393');
		// $params = $data;
		// echo json_encode($params);die();
		$sms_type = $params['sms_type'];
		if (empty($sms_type) || !in_array($sms_type, array(SC_SMS_NEW_ORDER, SC_SMS_NEW_DELIVERY, SC_SMS_NEW_BALANCE, SC_SMS_ALL_BALANCE))) {
			return $this->endInvoke('', 9008);
		}

		try{
			D()->startTrans();
			$apiPath = "Base.StoreModule.Basic.Sms.addStoreLinkMan";
			$addRes = $this->invoke($apiPath, $params);

			$res = D()->commit();
			if($res === FALSE){
                    throw new \Exception('事务提交失败',17);
            }
            if ($addRes['status'] != 0) {
            	return $this->endInvoke($addRes['status']);
            }
            return $this->endInvoke($addRes['response ']);
		}catch(\Exception $ex){
			D()->rollck();
            return $this->endInvoke(NULL, 9002);
		}
	}

	/**
	 * Bll.Pop.Store.Sms.delLinkMan
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function delLinkMan($params){
		try{
		    D()->startTrans();
		    $apiPath = "Base.StoreModule.Basic.Sms.delStoreLinkMan";
		    $delLinkRes  = $this->invoke($apiPath, $params);
		    if($delLinkRes['status'] != 0){
		        return $this->endInvoke(NULL, $delLinkRes['status']);
		    }

		    $commit_res = D()->commit();
		    if($commit_res === FALSE){
		        throw new \Exception('事务提交失败',17);
		    }

		    return $this->endInvoke($delLinkRes['response']);
		} catch (\Exception $ex) {
		    D()->rollback();
		    return $this->endInvoke(NULL,6704);
		}
	}

	/**
	 * Bll.Pop.Store.Sms.getLinkMan
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function getLinkMan($params){
		// $params['sms_type'] = SC_SMS_NEW_ORDER;
		// $params['sc_code'] = 1020000000026;
		// $params['uc_code'] = 1120000000103;
		$apiPath = 'Base.StoreModule.Basic.Sms.getLinkManInfo';
		$linkManRes = $this->invoke($apiPath, $params);
		if($linkManRes['status'] != 0){
		    return $this->endInvoke(NULL, $linkManRes['status']);
		}
		return  $this->endInvoke($linkManRes['response']);
	}

	/**
	 * Bll.Pop.Store.Sms.getLinkInfo
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function getLinkInfo($params){
		// $params['sc_code'] = 1020000000026;
		// $params['uc_code'] = 1120000000103;
		$apiPath = 'Base.StoreModule.Basic.Sms.getLinkListInfo';
		$linkInfoRes = $this->invoke($apiPath, $params);
		if($linkInfoRes['status'] != 0){
		        return $this->endInvoke(NULL, $linkInfoRes['status']);
		}
		return  $this->endInvoke($linkInfoRes['response']);
	}
}

