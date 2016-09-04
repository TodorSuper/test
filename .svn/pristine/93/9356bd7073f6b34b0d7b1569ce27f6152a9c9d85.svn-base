<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: haowenhui <haowenhui>
 * +----------------------------------------------------------------------
 * | 异常处理类
 */

namespace System;

class Error {

	public $code;
	public $msg;

	/**
	 * back
	 * 异常处理返回函数
	 * @access public
	 * @return void
	 */
	public function back() {
//		if(START_MODE == 2) {
//			return die( json_encode( Controller::res(null, $this->code, null, $this->msg) ) );
//		}else {
//			DG([$this->code,$this->msg], GET_DG_OBJECT);
			die( json_encode( Controller::res(null, $this->code, null, $this->msg) , JSON_UNESCAPED_UNICODE) );
//		}
	}

	/**
	 * __construct 
	 * 系统初始化函数
	 * @param mixed $msg 异常消息
	 * @param mixed $code 异常代码
	 * @access public
	 * @return void
	 */
	public function __construct($msg ,$code) {
		$this->msg = $msg;
		$this->code = $code;
	}
	/**
	 * log 
	 * 异常记录
	 * @param string $prefix 记录文件名称
	 * @access public
	 * @return void
	 */
	public function log($prefix = "log.txt") {
		$str = '';
		$filename = LOG_PATH.$prefix;
		$res = fopen($filename, 'a+');
		if($res) {
			$time = date('Y-m-d H:i:s');
			$str .= "[$time]".' Msg: '. $this->msg . ' Code:'. $this->code . "\r\n";
			fwrite($res, $str);
		}
		fclose($res);
	}

	/**
	 * log 
	 * 异常记录
	 * @param string $prefix 记录文件名称
	 * @access public
	 * @return void
	 */
	public function log2($prefix = "log.txt") {
		if($_GET['docCheck']['noTrace'] === true)
			return;
		$str = '';
		$filename = LOG_PATH.$prefix;
		$res = fopen($filename, 'a+');
		if($res) {
			$time = date('Y-m-d H:i:s');
			$str .= "[ $time ]".' '. $this->msg."\r\n";
			fwrite($res, $str);
		}
//		echo $filename;exit;
		fclose($res);
	}

}
