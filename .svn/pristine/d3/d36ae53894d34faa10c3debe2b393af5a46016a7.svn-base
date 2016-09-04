<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 短信处理类
 */

namespace Library;

class Sms {
	/**
	 * 网关地址
	 */	
	protected $gwUrl = 'http://sdk999ws.eucp.b2m.cn:8080/sdk/SDKService?wsdl';


	/**
	 * 序列号
	 */
//	protected $serialNumber = '9SDK-EMY-0999-JERMO';
	protected $serialNumber = '9SDK-EMY-0999-JFQPR';
	
	/**
	 * 密码
	 */
	protected $password = '833535';

	/**
	 * 登录后所持有的SESSION KEY，即可通过login方法时创建
	 */
	protected $sessionKey = '467190';

	/**
	 * 连接超时时间，单位为秒
	 */
	protected $connectTimeOut = 2;

	/**
	 * 远程信息读取超时时间，单位为秒
	 */ 
	protected $readTimeOut = 10;

/**
	$proxyhost		可选，代理服务器地址，默认为 false ,则不使用代理服务器
	$proxyport		可选，代理服务器端口，默认为 false
	$proxyusername	可选，代理服务器用户名，默认为 false
	$proxypassword	可选，代理服务器密码，默认为 false
 */	
	protected $proxyhost = false;
	protected $proxyport = false;
	protected $proxyusername = false;
	protected $proxypassword = false; 

	public function __construct () {
		define('SCRIPT_ROOT',  dirname(__FILE__).'/Sms/');
		require_once SCRIPT_ROOT.'include/Client.php';
		$client = new \Client($this->gwUrl,$this->serialNumber,$this->password,$this->sessionKey,$this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword,$this->connectTimeOut,$this->readTimeOut);
		# 设置客户端编码为 utf-8
		$client->setOutgoingEncoding("UTF-8");
		$this->client = $client;
	}

	/**
	 * 接口调用错误查看
	 */

	public function chkError() {
		$err = $this->client->getError();
		if ($err) {
			return $err;
		} else {
			return false;
		}

	}

	/**
	 * 发送语音验证码 用例
	 */
	function sendVoice($number, $code) {
		$statusCode = $this->client->sendVoice($number, $code);
		$this->checkStatus($statusCode);
		return array('status'=>$statusCode, 'message'=>$this->sendMessage);
	}

	/**
	 * 登录 
	 */
	public function login() {
		$client = $this->client;

		/**
		 * 下面的操作是产生随机6位数 session key
		 * 注意: 如果要更换新的session key，则必须要求先成功执行 logout(注销操作)后才能更换
		 * 我们建议 sesson key不用常变
		 */
		//$sessionKey = $client->generateKey();
		//$statusCode = $client->login($sessionKey);

		$statusCode = $client->login();

		echo "处理状态码:".$statusCode."<br/>";
		if ($statusCode!=null && $statusCode=="0") {
			//登录成功，并且做保存 $sessionKey 的操作，用于以后相关操作的使用
			echo "登录成功, session key:".$client->getSessionKey()."<br/>";
		}else{
			//登录失败处理
			echo "登录失败";
		}

	}

	/**
	 * 注销登录 
	 */
	public function logout() {
		$statusCode = $this->client->logout();
		return "处理状态码:".$statusCode;
	}

	/**
	 * 获取版本号 
	 */
	public function getVersion() {
		return "版本:". $this->client->getVersion();
	}


	/**
	 * 取消短信转发 
	 */	
	public function cancelMOForward() {
		$statusCode = $this->client->cancelMOForward();
		return $statusCode;
	}


	/**
	 * 短信发送
	 */
	public function sendSMS($numbers, $content, $time = '') {
		if($time != '') {
			$time = date('YmdHis', $time);
		}
		$statusCode = $this->client->sendSMS($numbers, $content, $time);
		$this->checkStatus($statusCode);
		return array('status'=>$statusCode, 'message'=>$this->sendMessage);
	}

	/**
	 * 执行状态检测
	 */
	protected function checkStatus($statusCode) {
		switch ($statusCode) {
		case 0:
			$this->sendMessage = "发送成功";
			break;
		case 17:
			$this->sendMessage = "发送信息失败";
			break;
		case 18:
			$this->sendMessage = "发送定时信息失败";
			break;
		case 101:
			$this->sendMessage = "客户端网络故障";
			break;
		case 305:
			$this->sendMessage = "服务器端返回错误，错误的返回值（返回值不是数字字符串）";
			break;
		case 307:
			$this->sendMessage = "目标电话号码不符合规则，电话号码必须是以0、1开头";
			break;
		case 997:
			$this->sendMessage = "平台返回找不到超时的短信，该信息是否成功无法确定";
			break;
		case 998:
			$this->sendMessage = "由于客户端网络问题导致信息发送超时，该信息是否成功下发无法确定";
			break;
		default : 
			$this->sendMessage = "未知错误";
		}		
	} 
}
?>

