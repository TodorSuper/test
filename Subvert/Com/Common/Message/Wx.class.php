<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 微信模板消息通知接口
 */

namespace Com\Common\Message;
use System\Base;


class Wx extends Base {
	private $_rule	=	null; # 验证规则列表
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 推送一条微信模板消息
	 * getGroup 
	 * Com.Common.Message.Wx.pushTplMsg
	 * @access public
	 * @return void
	 */

	public function pushTplMsg($data) {
		$this->_rule = array(
			array('touser', 'require' , PARAMS_ERROR, MUST_CHECK),					# 要发送到的用户
			array('template_id', 'require' , PARAMS_ERROR, MUST_CHECK),				# 消息模板id
			array('url', 'require' , PARAMS_ERROR, MUST_CHECK),						# 消息链接地址
			array('topcolor', 'require' , PARAMS_ERROR, ISSET_CHECK),				# 标题颜色
			array('data', 'checkArrayInput' , PARAMS_ERROR,MUST_CHECK, 'function'),# 消息数据
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$data['topcolor'] = isset($data['topcolor']) ? $data['topcolor'] : '#FF0000';
		unset($data['openid']);


		$token = $this->invoke('Com.Common.Wx.Mutual.getAccessToken');
		if($token['status'] !== 0) {
			return $this->res('', 5017);
		}
		$token = $token['response'];
		$conf = C('WEIXIN_CONF');
		$url = $conf['TPL_MSG_URL'].'access_token='.$token;
		$data = json_decode( $this->rpc($url, json_encode($data)) , true);  # 发送微信通知
		if($data['errcode']  !== 0)  {
			return $this->res('', 5017);
		}
		
		return $this->res(true);

	}

	/**
	 * 发送批量客服通知消息
	 * Com.Common.Message.Wx.pushServiceMsg 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	
	public function pushServiceMsg($data) {
		$this->_rule = array(
			array('openid', 'checkArrayInput' , PARAMS_ERROR, MUST_CHECK, 'function'),			# 要发送到的用户, 支持批量发送
			array('msg', 'require' , PARAMS_ERROR, MUST_CHECK),									# 消息模板id
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$token = $this->invoke('Com.Common.Wx.Mutual.getAccessToken');
		if($token['status'] !== 0) {
			return $this->res('', 5017);
		}
		$token = $token['response'];
		$conf = C('WEIXIN_CONF');
		$url = $conf['SEND_SVC_URL'].'access_token='.$token;
		foreach($data['openid'] as $v) {
			$msg = array(
				'touser'=>$v,
				'msgtype' => 'text',
				'text' => ['content'=>$data['msg']]
			);
			$res = json_decode( $this->rpc($url, json_encode($msg, JSON_UNESCAPED_UNICODE)) , true);  # 发送微信通知
			if($res['errcode']  !== 0)  {
				return $this->res('', 5017);
			}
		}


		return $this->res(true);

	}
}



?>
