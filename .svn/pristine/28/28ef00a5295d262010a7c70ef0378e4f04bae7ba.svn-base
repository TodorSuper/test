<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2016 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 
 */

namespace Bll\Cms\Group;
use System\Base;

class Group extends Base
{
	
	public function __construct(){
        parent::__construct();
	}

	/**
	 * 设置微信分组
	 * Bll.Cms.Group.Group.setWXGroup
	 */
	public function setWXGroup($params){
		$params    = $params['message'];
		$api       = "Base.WeiXinModule.User.User.getWxFrom";
		$wxUserRes = $this->invoke($api, $params);
		if ($wxUserRes['status'] != 0) {
			return $this->endInvoke(null, $wxUserRes['status']);
		}
		
		if (!empty($wxUserRes)) {
			$data = array();
			$data['openidList'] = array_unique(array_column($wxUserRes['response'], 'openId'));
			// $data['openidList'] = array('o7lz9v3RQzkQz5fct8cAEu43vFu8');
			$data['groupName']  = $params['groupName'];
			$groupApi = 'Com.Common.Wx.Mutual.setUsersGroup';
			$res = $this->invoke($groupApi, $data);
			if ($res['status'] != 0) {
				//return $this->endInvoke(null, $res['status']);
			}

			$wxApi = 'Base.WeiXinModule.User.User.updataGroup';
			$wxData = array(
					'openidList' => $data['openidList'],
					'groupName'  => $params['groupName'],
				);
			$wxRes = $this->invoke($wxApi, $wxData);
			if ($wxRes['status'] != 0) {
				return $this->endInvoke(null, $wxRes['status']);
			}
		}
		return $this->endInvoke(true);
	}
}































