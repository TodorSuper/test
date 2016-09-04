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


class WxTpl extends Base {
	private $_rule	=	null; # 验证规则列表
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 创建订单通知
	 * getGroup 
	 * Com.Common.Message.WxTpl.mkOrder
	 * @access public
	 * @return void
	 */

        
	public function mkOrder($data) {
		
		$this->_rule = array(
			array('pay_type', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单明细
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$pay_type = $data['pay_type'];

		if($pay_type == PAY_TYPE_COD || $pay_type == PAY_TYPE_TERM) {			# 货到付款
			$call = $this->invoke('Com.Common.Message.WxTpl.mkUnpayOrder', $data);

		}elseif($pay_type == PAY_TYPE_ONLINE) {	# 在线支付
			$call = $this->invoke('Com.Common.Message.WxTpl.mkpayOrder', $data);

		}else {
			return $this->res('', 5017);
		}
		
		if($call['status'] === 0) {
			return $this->res(true);

		}else {
			return $this->res('', $call['status']);

		}
	}

	/**
	 * 创建货到付款通知
	 * Com.Common.Message.WxTpl.mkUnpayOrder
	 * mkUnpayOrder 
	 * @access private
	 * @return void
	 */
	public function mkUnpayOrder($data) {
		$template_id = 'uOfp-l6QLiQ7wPFTt3FXNRaBboC3atGq7FhC2eJKBhY';
		$this->_rule = array(
			array('order_sn', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单编号
			array('openid', 'require' , PARAMS_ERROR, MUST_CHECK),					# 要发送到的用户
			array('goods_number', 'require' , PARAMS_ERROR, MUST_CHECK),			# 商品数量
			array('pay_price', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付金额
			array('url_info', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单明细
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$struct = array( # 模板数据结构
			'topcolor' => '#FF0000',
			'touser' => $data['openid'],
			'template_id' => $template_id,
			'url' => $data['url_info'],
			'data' => array(
				'first' => ['value'=>'您的订单已经成功创建,商家正在全力备货, 请打开"我的订单"进行查看.'."\n",						'color'=>'#000000'],
				'orderno' => ['value'=>$data['order_sn'],						'color'=>'#000000'],
				'refundno' => ['value'=>$data['goods_number'],					'color'=>'#000000'],
				'refundproduct' => ['value'=>$data['pay_price']."\n",						'color'=>'#000000'],
				'remark'   => ['value'=>'感谢您的惠顾,如您还有疑问，请致电客服：400-815-5577
在线服务时间：9：00-18：00',				'color'=>'#000000'],
			),
		);

		$call = $this->invoke('Com.Common.Message.Wx.pushTplMsg', $struct);
		if($call['status'] !== 0) {
			return $this->res('', 5017); # 消息推送失败
		}

		return $this->res(true);

	}

	/**
	 * 创建线上支付订单通知
	 * Com.Common.Message.WxTpl.mkUnpayOrder
	 * @access private
	 * @return void
	 */
	public function mkPayOrder($data) {
		$template_id = '_d9rn-evICrcE4YLrdlpEwpQO1UAVUXHPL4hvMmG_X0';
		$this->_rule = array(
			array('order_sn', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单编号
			array('openid', 'require' , PARAMS_ERROR, MUST_CHECK),					# 要发送到的用户
			array('goods_name', 'require' , PARAMS_ERROR, MUST_CHECK),				# 商品名称
			array('goods_number', 'require' , PARAMS_ERROR, MUST_CHECK),			# 商品数量
			array('pay_price', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付金额
			array('url_info', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单明细
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$struct = array( # 模板数据结构
			'topcolor' => '#FF0000',
			'touser' => $data['openid'],
			'template_id' => $template_id,
			'url' => $data['url_info'],
			'data' => array(
				'first' => ['value'=>'您的订单已创建成功, 打开 "我的订单" 点击进入完成付款.'."\n",						'color'=>'#000000'],
				'keyword1' => ['value'=>$data['order_sn'],						'color'=>'#000000'],
				'keyword2' => ['value'=>$data['goods_name'],					'color'=>'#000000'],
				'keyword3' => ['value'=>$data['goods_number'],					'color'=>'#000000'],
				'keyword4' => ['value'=>$data['pay_price']."\n",						'color'=>'#000000'],
				'remark'   => ['value'=>'如您还有疑问，请致电客服：400-815-5577
在线服务时间：9：00-18：00',				'color'=>'#000000'],
			),
		);

		$call = $this->invoke('Com.Common.Message.Wx.pushTplMsg', $struct);
		if($call['status'] !== 0) {
			return $this->res('', 5017); # 消息推送失败
		}

		return $this->res(true);

	}



	/**
	 * 创建线上支付预付款订单通知
	 * Com.Common.Message.WxTpl.mkPayAdvanceOrder
	 * @access private
	 * @return void
	 */
	public function mkPayAdvanceOrder($data) {
		$template_id = '_d9rn-evICrcE4YLrdlpEwpQO1UAVUXHPL4hvMmG_X0';
		$this->_rule = array(
			array('order_sn', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单编号
			array('openid', 'require' , PARAMS_ERROR, MUST_CHECK),					# 要发送到的用户
			array('goods_name', 'require' , PARAMS_ERROR, MUST_CHECK),				# 商品名称
			array('goods_number', 'require' , PARAMS_ERROR, MUST_CHECK),			# 商品数量
			array('pay_price', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付金额
			array('url_info', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单明细
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$struct = array( # 模板数据结构
			'topcolor' => '#FF0000',
			'touser' => $data['openid'],
			'template_id' => $template_id,
			'url' => $data['url_info'],
			'data' => array(
				'first' => ['value'=>'您的预付款订单已创建成功'."\n",			'color'=>'#000000'],
				'keyword1' => ['value'=>$data['order_sn'],						'color'=>'#000000'],
				'keyword2' => ['value'=>$data['goods_name'],					'color'=>'#000000'],
				'keyword3' => ['value'=>$data['goods_number'],					'color'=>'#000000'],
				'keyword4' => ['value'=>$data['pay_price']."\n",						'color'=>'#000000'],
				'remark'   => ['value'=>'如您还有疑问，请致电客服：400-815-5577
在线服务时间：9：00-18：00',				'color'=>'#000000'],
			),
		);

		$call = $this->invoke('Com.Common.Message.Wx.pushTplMsg', $struct);
		if($call['status'] !== 0) {
			return $this->res('', 5017); # 消息推送失败
		}

		return $this->res(true);

	}



	/**
	 * 支付订单成功提示
	 * Com.Common.Message.WxTpl.payOrder
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function payOrder($data) {
		$template_id = 'dNgquyJYGSSPbvPubrcdP4ahgJb6JGwpuS0eVVEgdJE';
	/*	$data = array(
			'order_sn' => '88888888',
			'openid' => 'o8JbLjuzycmXKPH5EWwp6cDxah6A',
			'pay_time' => time(),
			'pay_type' => '微信支付',
			'pay_price' => '88',
			'url_info' => 'http://www.baidu.com/',
		);
 */

		$this->_rule = array(
			array('order_sn', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单编号
			array('openid', 'require' , PARAMS_ERROR, MUST_CHECK),					# 要发送到的用户
			array('pay_time', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付时间
			array('pay_type', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付类型
			array('pay_price', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付金额
			array('url_info', 'require' , PARAMS_ERROR, MUST_CHECK),				# 详情连接
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$struct = array( # 模板数据结构
			'topcolor' => '#FF0000',
			'touser' => $data['openid'],
			'template_id' => $template_id,
			'url' => $data['url_info'],
			'data' => array(
				'first' => ['value'=>'您好，您的订单已付款成功!'."\n",						'color'=>'#000000'],
				'keyword1' => ['value'=>$data['order_sn'],						'color'=>'#000000'],
				'keyword2' => ['value'=>date('Y年m月d日 H:i', $data['pay_time']),					'color'=>'#000000'],
				'keyword3' => ['value'=>$data['pay_price'].'元',					'color'=>'#000000'],
				'keyword4' => ['value'=>$data['pay_type']."\n",						'color'=>'#000000'],
				'remark'   => ['value'=>'感谢您的惠顾,如您还有疑问，请致电客服：400-815-5577
在线服务时间：9：00-18：00',				'color'=>'#000000'],
			),
		);
		$call = $this->invoke('Com.Common.Message.Wx.pushTplMsg', $struct);
		if($call['status'] !== 0) {
			return $this->res('', 5017); # 消息推送失败
		}

		return $this->res(true);

	}


	/**
	 * 支付订单成功提示
	 * Com.Common.Message.WxTpl.payAdvanceOrder
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function payAdvanceOrder($data) {
		$template_id = 'dNgquyJYGSSPbvPubrcdP4ahgJb6JGwpuS0eVVEgdJE';

		$this->_rule = array(
			array('order_sn', 'require' , PARAMS_ERROR, MUST_CHECK),				# 订单编号
			array('openid', 'require' , PARAMS_ERROR, MUST_CHECK),					# 要发送到的用户
			array('pay_time', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付时间
			array('pay_type', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付类型
			array('pay_price', 'require' , PARAMS_ERROR, MUST_CHECK),				# 支付金额
			array('url_info', 'require' , PARAMS_ERROR, MUST_CHECK),				# 详情连接
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$struct = array( # 模板数据结构
			'topcolor' => '#FF0000',
			'touser' => $data['openid'],
			'template_id' => $template_id,
			'url' => $data['url_info'],
			'data' => array(
				'first' => ['value'=>'您好，您的预付款订单已付款成功!'."\n",						'color'=>'#000000'],
				'keyword1' => ['value'=>$data['order_sn'],						'color'=>'#000000'],
				'keyword2' => ['value'=>date('Y年m月d日 H:i', $data['pay_time']),					'color'=>'#000000'],
				'keyword3' => ['value'=>$data['pay_price'].'元',					'color'=>'#000000'],
				'keyword4' => ['value'=>$data['pay_type']."\n",						'color'=>'#000000'],
				'remark'   => ['value'=>'感谢您的惠顾,如您还有疑问，请致电客服：400-815-5577
在线服务时间：9：00-18：00',				'color'=>'#000000'],
			),
		);
		$call = $this->invoke('Com.Common.Message.Wx.pushTplMsg', $struct);
		if($call['status'] !== 0) {
			return $this->res('', 5017); # 消息推送失败
		}

		return $this->res(true);

	}

}



?>
