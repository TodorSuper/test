<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 系统消息通知接口
 */

namespace Com\Common\Message;
use System\Base;


class Sys extends Base {
	private $_rule	=	null; # 验证规则列表
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取一组系统通知消息
	 * getList
	 * Com.Common.Message.Sys.getList
	 * @access public
	 * @return void
	 */
	public function getList($data) {
		$this->_rule = array(
			array('message_id', 'require' , PARAMS_ERROR, MUST_CHECK),		# 消息组id
			array('message_number', 'require' , PARAMS_ERROR, ISSET_CHECK),	# 消息数量
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$where = array(
			'type_id' =>$data['message_id'],
			'endtime' =>['gt', NOW_TIME],
			'status' => 1
		);
		
		D('Notice')->field('title,content')->where($where)->order('endtime desc');
		if(isset($data['message_number'])) {
			D('Notice')->limit($data['message_number']);
		}

		return $this->res(D('Notice')->select());
	}
}

?>
