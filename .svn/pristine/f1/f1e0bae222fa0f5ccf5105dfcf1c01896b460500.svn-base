<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */

namespace Base\StoreModule\Basic;

use System\Base;

class Sms extends Base {

	public $smsType = array(SC_SMS_NEW_ORDER, SC_SMS_NEW_DELIVERY, SC_SMS_NEW_BALANCE, SC_SMS_ALL_BALANCE);
	public function __construct(){
        parent::__construct();
	}

	/**
	 * 添加卖家联系人
	 * Base.StoreModule.Basic.Sms.addStoreLinkMan
	 * @param [type] $params [description]
	 */
	public function addStoreLinkMan($params){
		$this->startOutsideTrans();
		$this->rule = array(
			array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 店铺编码  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 用户编码
			array('sms_type', 'require', PARAMS_ERROR, MUST_CHECK),
			array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //验证卖家是否存在
        $where = array();
        $where['uc_code'] = $params['uc_code'];
        $where['sc_code'] = $params['sc_code'];
        $storeRes = D('ScStore')->where($where)->find();

        if (!$storeRes) {
        	return $this->res('', 2001);
        }

        //删除以前插入的联系人
        $map = array();
		// $map['uc_code']  = $params['uc_code'];
		$map['sc_code']  = $params['sc_code'];
		$map['sms_type'] = $params['sms_type'];
		$map['status']   = 'ENABLE';
        $linkManRes = D('ScLinkman')->where($map)->select();

        if (!empty($linkManRes)) {
			$data['status']   = 'DISABLE';
			$data['sms_type'] = $params['sms_type'];
        	$delRes = D('ScLinkman')->where($map)->save($data);
        	if (!$delRes) {
        		return $this->res(null, 9003);
        	}
        }

        if (empty($params['data'])) {
        	return $this->res(true);
        }

        //添加数据组成
		$linkMandata = array();
		$data        = $params['data'];

		if (count($data) > 5) {
			return $this->res(null, 9009);
		}
		//获取业务员
		// $saleManWhere            = array();
		// $saleManWhere['sc_code'] = $params['sc_code'];
		// $saleManRes = D('ScSalesman')->where($saleManWhere)->select();
		// if ($saleManRes === false) {
		// 	return $this->res('', 9005);
		// }
		// if (!empty($saleManRes)) {
		// 	$arr                 = array();
		// 	$arr['storelinkman'] = $saleManRes['name'];
		// 	$arr['phone']        = $saleManRes['mobile'];
		// }
		
		//判断是否是付款类型(不是总付款类型)
		// if ($params['sms_type'] === SC_SMS_NEW_ORDER) {
		// 	$storeWhere            = array();
		// 	$storeWhere['sc_code'] = $params['sc_code'];
		// 	$storeRes = D('ScStore')->where($storeWhere)->find();
		// 	if (!$storeRes) {
		// 		return $this->res('', 9006);
		// 	}
		// 	$arr = array();
		// 	$arr['storelinkman'] = $value['name'];
		// 	$arr['phone'] = $value['phone'];
		// 	$data[] = $arr;
		// }
		foreach ($data as  $linkMan) {
			//电话号码验证
	        $phoneLen = strlen($linkMan[1]);
	        if ($phoneLen < 0 || $phoneLen > 11) {
	        	return $this->res('', 9007);
	        }
	        $arr = array();
			$arr['uc_code']      = $params['uc_code'];
			$arr['sc_code']      = $params['sc_code'];
			$arr['storelinkman'] = $linkMan[0];
			$arr['phone']        = $linkMan[1];
			$arr['sms_type']     = $params['sms_type'];
			$arr['create_time']  = NOW_TIME;
			$arr['update_time']  = NOW_TIME;
			$arr['status']       = 'ENABLE';
			$linkMandata[]       = $arr;
		}
		$insertRes = D('ScLinkman')->addAll($linkMandata);
		if (!$insertRes) {
			return $this->res('', 4001);
		}
		return $this->res(true);
	}

	/**
	 * 删除卖家联系人
	 * Base.StoreModule.Basic.Sms.delStoreLinkMan
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function delStoreLinkMan($params){
		$this->startOutsideTrans();
		$this->rule = array(
			array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 店铺编码  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 用户编码
			array('id', 'require', PARAMS_ERROR, MUST_CHECK),
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //定义更新条件
        $where = array();
		$where['id']      = intval($params['id']);
		$where['uc_code'] = intval($params['uc_code']);
		$where['sc_code'] = intval($params['sc_code']);

		$delRes = D('ScLinkman')->where($where)->save(array('update_time' => NOW_TIME, 'status' => 'DISABLE'));
		if (!$delRes) {
			return $this->res('', 4015);
		}
		return $this->res($delRes);
	}

	/**
	 * 修改卖家联系人
	 * Base.StoreModule.Basic.Sms.updateStoreLinkMan
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function updateStoreLinkMan($params){
		$this->startOutsideTrans();
		$this->rule = array(
			array('id', 'require', PARAMS_ERROR, MUST_CHECK),
			array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 店铺编码  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 用户编码
			array('storelinkman', 'require', PARAMS_ERROR, ISSET_CHECK),
			array('phone', 'require', PARAMS_ERROR, ISSET_CHECK),
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //卖家联系人是否存在
        $where = array();
		$where['id']      = $params['id'];
		$where['uc_code'] = $params['uc_code'];
		$where['sc_code'] = $params['sc_code'];
        $storeRes = D('ScStore')->where($where)->find();
        if (!$storeRes) {
        	return $this->res('', 2001);
        }

        //防止修改数据，全部为空
        if ($params['phone'] && $params['storelinkman']) {
        	return $this->res('', 2001);
        }

        //电话号码验证
        if ($params['phone']) {
        	$phoneLen = strlen(trim($params['phone']));
	        if ($phoneLen <= 0 || $phoneLen >11) {
	        	return $this->res('', 2001);
	        }
        }

        //更新数据
		$linkData                 = array();
		empty($params['storelinkman'])? :$linkData['storelinkman'] = $params['storelinkman'];
		empty($params['phone'])? :$linkData['phone']               = $params['phone'];
		$linkData['update_time']  = NOW_TIME;

		$updateRes = D('ScStore')->where($where)->save($linkData);
		if (!$updateRes) {
			return $this->res('', 4003);
		}
		return $updateRes;
	}

	/**
	 * 获取联系人信息
	 * Base.StoreModule.Basic.Sms.getLinkManInfo
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function getLinkManInfo($params){
		$this->rule = array(
			array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 店铺编码  * 必须字段
			// array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 用户编码
			array('sms_type', 'require', PARAMS_ERROR, MUST_CHECK),
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //定义卖家联系人条件
        $where = array();
		// $where['uc_code']  = $params['uc_code'];
		$where['sc_code']  = $params['sc_code'];
		$where['sms_type'] = $params['sms_type'];
		$where['status']   = 'ENABLE';
		$field = 'storelinkman,phone';
		//查询结果
		$linkManInfo = D('ScLinkman')->where($where)->field($field)->select();
		if ($linkManInfo === false) {
			return $this->res('', 9004);
		}
		return $this->res($linkManInfo);
	}

	/**
	 * Base.StoreModule.Basic.Sms.getLinkListInfo
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function getLinkListInfo($params){
		$this->rule = array(
			array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 店铺编码  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 用户编码
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取业务员
		$saleManWhere            = $data = array();
		$saleManWhere['sc_code'] = $params['sc_code'];
		$saleManRes = D('ScSalesman')->where($saleManWhere)->select();

		if (!$saleManRes) {
			return $this->res('', 9005);
		}

		//获取卖家信息
		$storeWhere            = array();
		//获取卖家信息
		$storeWhere            = array();
		$storeWhere['uc_code'] = $params['uc_code'];
		$storeWhere['sc_code'] = $params['sc_code'];
		$storeRes = D('ScStore')->where($storeWhere)->find();
		if (!$storeRes) {
			return $this->res('', 9006);
		}
		$data['storeMan'] = $storeRes['linkman'];

		//获取卖家联系人列表
		$where = array();
		$where['uc_code'] = $params['uc_code'];
		$where['sc_code'] = $params['sc_code'];
		$where['status']  = 'ENABLE';
		$field = 'sms_type,storelinkman,phone';
		$linkManRes = D('ScLinkman')->where($where)->field($field)->select();
		if ($linkManRes === false) {
			return $this->res('', 9004);
		}

		$linkManData = array();
		if (empty($linkManRes)) {
			$linkManData['link'] = array('NEW_ORDER' => '', 'NEW_DELIVERY' => '', 'NEW_BALANCE' => '', 'ALL_BALANCE' => '');
			$linkManData['list'] = array('NEW_ORDER' => '', 'NEW_DELIVERY' => '', 'NEW_BALANCE' => '', 'ALL_BALANCE' => '');
			// $data = array(
			// 	'uc_code'      => $params['uc_code'],
			// 	'sc_code'      => $params['sc_code'],
			// 	'storelinkman' => $storeRes['username'],
			// 	'phone'        => $storeRes['phone'],
			// 	'sms_type'     => 'NEW_DELIVERY',
			// 	'create_time'  => NOW_TIME,
			// 	'update_time'  => NOW_TIME,
			// 	'status'       => 'ENABLE',
			// 	);
			// $insertRes = D('ScLinkman')->add($data);
			// if (!$insertRes) {
			// 	return $this->res('', 4001);
			// }
		}else{
			$newArr = array();
			$linkManArr = array();
			foreach ($linkManRes as $linkMan) {
				$newArr[$linkMan['sms_type']][] = $linkMan['storelinkman'];
				$linkManArr[$linkMan['sms_type']][] = $linkMan;
			}

			foreach ($newArr as  $key => $value) {
				$newArr[$key] = implode('、', $value);
			}
			
			$linkManData['link'] = $newArr;
			$linkManData['list'] = $linkManArr;
		}
		$linkManData['storeMan'] = $data['storeMan'];
		return $this->res($linkManData);
		
		
	}
}

