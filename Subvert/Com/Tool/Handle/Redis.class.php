<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | Handle类的封装 redis驱动
 */

namespace Com\Tool\Handle;
use System\Base;

class Redis extends Base {
	private $hanleDriver = "redis"; # 驱动类型
	private $handle; # 句柄集   array('name'=>xx)
	private $redis; # redis 操作句柄
	
    public function __construct() {
		parent::__construct();
		$this->redis = R();
    }
	
	/**
	 *
	 * 创建操作句柄 
	 * createHandle 
	 * Com.Tool.Handle.Redis.createHandle
	 * @param mixed $uniqueString 唯一标识
	 * @param mixed $expires 过期时间
	 * @access public
	 * @return object 成功返回句柄对象 失败返回错误信息
	 * 
	 */
	public function createHandle($data) {
		$expires = $data['expires'];
		$uniqueString = $data['uniqueString'];
		
		if($uniqueString == false) {
			return $this->res('', 5);
		}

		if( $uniqueString == '' ) {
			return $this->res('', 5);
		}

		if( $expires <= 0 ) {
			return $this->res('', 5);
		}
		
		$handleName = $this->getKey($uniqueString);

		if( in_array( $handleName, $this->handle) ) {
			return $this->res('', 5001);
		}
		$set = $this->redis->set($handleName, $expires, array("NX", "EX"=>$expires));
		if($set) {
			$this->handle[] = $handleName;
			return $this->res($this);
		}else {
			return $this->res('', 5002);
		}
		
	}

	/**
	 * 根据操作redis的key
	 * getKey 
	 * 
	 * @param mixed $key 
	 * @access private
	 * @return void
	 */
	private function getKey($key) {
		$key = md5($key);
		$handleName = \Library\Key\RedisKeyMap::getHandleKey($key);
		return $handleName;
	}

	/**
	 * 判断操作句柄是否存在
	 * getHandle 
	 * 
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function getHandle($uniqueString) {
		$key = $this->getKey($uniqueString);
		if( in_array($key, $this->handle) ) {
			$res = $this->redis->get($key);
			if( !$res )  {
				unset($this->handle[$key]);
				return false;
			}else {
				return $res;
			}

		} else {
			return false;
		}
	}
	
	/**
	 * 关闭操作句柄
	 *
	 * closeHandle
	 * 
	 * @access public
	 * @return void
	 */
	public function closeHandle($uniqueString) {
		$key = $this->getKey($uniqueString);
		$res = $this->redis->delete($key);
		$num = array_search($key, $this->handle);
		unset($this->handle[0]);
		if($num !== false) {
			unset($this->handle[$num]);
		}
	}

}

?>
