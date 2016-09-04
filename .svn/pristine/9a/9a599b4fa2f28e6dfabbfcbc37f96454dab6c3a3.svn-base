<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单中心 导出  回调接口
 */

namespace Com\CallBack\Weixin;

use System\Base;

class AcceptPush extends Base {

	private $_rule = null; # 验证规则列表

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 记录微信通知事件
	 * Com.Callback.Weixin.AcceptPush.envent
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function envent($data) {
		$invoke = $this->invoke('Base.WeiXinModule.User.User.update', $data); # 更新用户微信资料
		if($invoke['status'] !== 0) {
			return $this->res('', 5016);
		}else{
			return $this->res(true);
		}
	}

	/**
	 * 发送批量客服通知消息
	 * Com.Callback.Weixin.AcceptPush.sendBatCustomMsg
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function sendBatCustomMsg($data) {
		$invoke = $this->invoke('Com.Common.Message.Wx.pushServiceMsg', $data);
		return $this->res($invoke['response'], $invoke['status']);
	}
}

?>
