<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 轮播接口
 */

namespace Com\Common\Message;
use System\Base;


class Sms extends Base {

	private $_rule	=	null; # 验证规则列表
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 发送一条短信
	 * send
	 * Com.Common.Message.Sms.send
	 * @access public
	 * @return void
	 */

	public function send($data) {

		if( isset($data['message']) && is_array($data['message'])) {

			$data = $data['message'];
		}
		$this->_rule = array(
			array('sys_name', 'require' , PARAMS_ERROR, MUST_CHECK),					# 系统名称标识
			array('numbers', 'checkArrayInput' , PARAMS_ERROR, MUST_CHECK, 'function'),	# 手机号码 必须为数组, 可以同时为多个号码
			array('message', 'require' , PARAMS_ERROR, MUST_CHECK),						# 要发送的消息
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		if(isset($data['type']) && $data['type'] == 'voice') {
			$res = $this->sendSMS($data, $data['type']);  # 语音验证码短信
		}else {
			$res = $this->sendSMS($data);				# 文本短信
		}

		if($res !== true) {
			return $this->res('', 5018);  # 发送短信通知失败
		}
		
		return $this->res(true);
	}

}

?>
