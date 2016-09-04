<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 微信交互接口
 */

namespace Com\Common\Wx;
use System\Base;


class Mutual extends Base {
	private $_rule	=	null; # 验证规则列表
	private $token  =   false;
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取公众号的 access_token
	 * 内部封装了缓存方法, 无需考虑存储
	 * getAccessToken
	 * Com.Common.Wx.Mutual.getAccessToken
	 * @access public
	 * @return void
	 */

	public function getAccessToken($data = null) {
		
		if($this->token) {
			return $this->res($token);
		}

		if(!isset($data['invalide'])) {
			$data['invalide'] = false;
		}
		$key = $this->getRedisKey('getWxTokenKey', 'token');
		$token = R()->get($key);
		if($token && $data['invalide'] != true)  {
			return $this->res($token);
		}
		$conf = C('WEIXIN_CONF');
		$url = $conf['TOOKEN_URL'].'&appid='.$conf['APPID'].'&secret='.$conf['APPSECRET'];
		$data = json_decode( $this->rpc($url) , true);  # 请求微信服务器拿到token
		if( isset($data['access_token']) ) {
			$token = $data['access_token'];
			$expire = $data['expires_in'] - 1000;
			$key = $this->getRedisKey('getWxTokenKey', 'token');
			R()->setex($key, $expire, $token);
		}else {
			return $this->res('', 5014); # 创建失败
		}
		$this->token = $token;

		return $this->res($token);
	}

	/**
	 * 获取微信用户资料信息
	 * Com.Common.Wx.Mutual.getUserInfo 
	 * @access public
	 * @return void
	 */
	public function getUserInfo($data) {
		$this->_rule = array(										
			array('openid', 'require' , PARAMS_ERROR, MUST_CHECK),		# 微信用户openid
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$token = $this->invoke('Com.Common.Wx.Mutual.getAccessToken');
		if($token['status'] !== 0) {
			return $this->res('', 5015);
		}
		$token = $token['response'];
		$conf = C('WEIXIN_CONF');
		$url = $conf['USER_INFO_URL'].'&openid='.$data['openid'].'&access_token='.$token;
		$wx = json_decode( $this->rpc($url) , true);  # 请求微信服务器拿到用户资料
		if(!isset($wx['openid'])) {  # 返回结果
			$token = $this->invoke('Com.Common.Wx.Mutual.getAccessToken', ['invalide'=>true]); # token 失效了重新获取
			$token = $token['response'];
			$url = $conf['USER_INFO_URL'].'&openid='.$data['openid'].'&access_token='.$token;
			$wx = json_decode( $this->rpc($url) , true);
			if(!isset($wx['openid'])) {
				return $this->res('', 5015);
			}
		}
		
		return $this->res($wx);
	}

	/**
	 * 获取code置换openId
	 * 前台系统-->bll-->com-->url 
	 * 前台系统 --> url --> 前台系统(url?code=xxxx)
	 * 该接口需要传递一个回调地址, 返回url地址, 前台系统重定向后会重定向到回调地址, 在调用的时候会带上code
	 * code只能置换一次openid, 并且存在时效性
	 * Com.Common.Wx.Mutual.getCode
	 * @param mixed $data
	 * @access public
	 * @return void
	 */
	public function getCode($data) {
		$this->_rule = array(										
			array('notify_url', 'require' , PARAMS_ERROR, MUST_CHECK),		# 回调地址
		);

		if(!$this->checkinput($this->_rule, $data)) # 自动校验
			return $this->res($this->geterrorfield(),$this->getcheckerror());
		
		$redirect = urlencode($data['notify_url']);
		$urlObj = [];
		$conf = C('WEIXIN_CONF');
		# 拼凑请求数据集
		$urlObj["appid"] = $conf['APPID'];
		$urlObj["redirect_uri"] = "$redirect";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$buff = "";

		# 转换为url请求参数格式
		foreach ($urlObj as $k => $v) {
			$buff .= $k . "=" . $v . "&";
		}

		$bizString = trim($buff, "&");
		$request = $conf['GET_OPEN_ID_URL'];
		if(!$request) {
			return $this->res('', 5019);
		}
		return $this->res( $request.$bizString );
	}

	/**
	 * 获取openid
	 * Com.Common.Wx.Mutual.getOpenId 
	 * @param mixed $id 
	 * @access public
	 * @return void
	 */
	public function getOpenId($data) {
		$this->_rule = array(										
			array('code', 'require' , PARAMS_ERROR, MUST_CHECK),		# 微信服务器返回的code
		);

		if(!$this->checkinput($this->_rule, $data)) # 自动校验
			return $this->res($this->geterrorfield(),$this->getcheckerror());
		
		# 拼凑请求数据
		$conf = C('WEIXIN_CONF');
		$urlObj["appid"] = $conf['APPID'];
		$urlObj["secret"] = $conf["APPSECRET"];
		$urlObj["code"] = $data['code'];
		$urlObj["grant_type"] = "authorization_code";

		# 转换为url请求参数格式
		foreach ($urlObj as $k => $v) {
			$buff .= $k . "=" . $v . "&";
		}
		$bizString = trim($buff, "&");
		$request = $conf['GET_OPEN_ID_URL2'];
		$url =  $request.$bizString;

		# 请求微信服务器		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		curl_close($ch);

		# 取出openid
		$data = json_decode($res,true);
		$openid = $data['openid'];
		if(!$openid) {
			L($data);
			return $this->res('', 5020);
		}
		
		return $this->res(['openid'=>$openid]);

	}

	/**
	 * Com.Common.Wx.Mutual.setUsersGroup 
	 * 批量设置用户分组
	 * @access public
	 * @return void
	 */
	public function setUsersGroup($data) {
		$this->_rule = array(
			array('groupName', 'require' , PARAMS_ERROR, MUST_CHECK), 
		);

		if(!$this->checkinput($this->_rule, $data)) # 自动校验
			return $this->res($this->geterrorfield(),$this->getcheckerror());

		$openidList = $data['openidList'];
		$groupName = $data['groupName'];
		if( count($openidList) > 50 || !is_array($data['openidList']) ) {
			return $this->res(null,5028);
		}

	
		# 获得分组id
		$groups = $this->invoke('Com.Common.Wx.Mutual.getUserGroup'); 
		if($groups['status'] !== 0) {
			return $this->res(null, $groups['status']);
		}
		$groups = $groups['response']['groups'];
		$groupId = null;
		foreach($groups as $v) {
			if($v['name'] == $groupName) {
				$groupId = $v['id'];
			}
		}
		if(!$groupId) {
			return $this->res($groups, 5029);
		}

		$token = $this->invoke('Com.Common.Wx.Mutual.getAccessToken'); 
		if($token['status'] !== 0) {
			return $this->res('', 5015);
		}
		$token = $token['response'];

		# 拼凑请求数据
		$conf = C('WEIXIN_CONF');
		$urlObj["access_token"] = $token;

		# 转换为url请求参数格式
		foreach ($urlObj as $k => $v) {
			$buff .= $k . "=" . $v . "&";
		}
		$bizString = trim($buff, "&");
		$request = $conf['MOVE_USERS_GROUP'];
		$url =  $request.$bizString;

		$requestData = array(
			'openid_list'=>$data['openidList'],
			'to_groupid' => $groupId,
		);

		# 移动分组
		$res = json_decode( $this->rpc($url, json_encode( $requestData ) )  , true);

		if($res['errcode'] === 0 ) {
			return $this->res(true);
		}else{
			return $this->res($res, 5030);
		}
	}

	/**
	 * Com.Common.Wx.Mutual.getUserGroup 
	 * 获取微信用户分组
	 * @access public
	 * @return void
	 */
	public function getUserGroup() {
		
		# 获取token
		$token = $this->invoke('Com.Common.Wx.Mutual.getAccessToken'); 
		if($token['status'] !== 0) {
			return $this->res('', 5015);
		}

		# 拼凑请求数据
		$conf = C('WEIXIN_CONF');
		$urlObj["access_token"] = $token['response'];

		# 转换为url请求参数格式
		foreach ($urlObj as $k => $v) {
			$buff .= $k . "=" . $v . "&";
		}
		$bizString = trim($buff, "&");
		$request = $conf['GET_USER_GROUP'];
		$url =  $request.$bizString;

		# 请求微信服务器
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($res,true);
		return $this->res($data);
	}	

}

?>
