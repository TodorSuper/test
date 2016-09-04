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

namespace Com\Crontab\Uc;
use System\Base;

class User extends Base
{
	
	public function __construct(){
		parent::__construct();
		
	}

	/**
	 * 用于设置微信用户平台用户
	 * Com.Crontab.Uc.User.setWxUcGroup
	 */
	public function setWxUcGroup(){
		$api = 'Bll.Cms.Group.Group.setWXGroup';
		$data = array();
		$data['invite_from'] = 'UC';
		$data['groupName']   = '平台组';
		$this->push_queue($api, $data);
	} 

	/**
	 * 用于设置微信用户店铺用户
	 * Com.Crontab.Uc.User.setWxScGroup
	 */
	public function setWxScGroup(){
		$api = 'Bll.Cms.Group.Group.setWXGroup';
		$data = array();
		$data['invite_from'] = 'SC';
		$data['groupName']   = '店铺组';

		$this->push_queue($api, $data);
	}
}






