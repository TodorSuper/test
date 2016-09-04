<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: haowenhui <haowenhui>
 * +----------------------------------------------------------------------
 * | 调用控制器 系统级别公共方法
 */

namespace System;
use Library\BeansTalk;
use System\Logs;
use Library\Sms;

define("QUEUE_SYNC", 1);
define("QUEUE_ASYNC", 0);
define("WEB_SHOAP", 1);
define("WAP_SHOAP", 2);
define("GOODS", 1);
define("SECKILL_GOODS", 2);


class Controller {
	
	/**
	 * 委托调用层级
	 * @var integer
	 */
	private static  $_invokeLevel = 0;

	/**
	*  队列调用对象
	*/
	protected static $_queueObj = null;

	/**
	*  队列服务器cluster
	*/
	protected static $_queueServer = array();

	# 队列扩展属性
	private $extends_attr = array();
	private $clear_flag;

	# 自动校验结果
	private $checkError = null;

	# 自动校验结果
	private $errorField = null;
    
	# 当前调用api的系统名称
    public $_request_sys_name = null;

	# 上一个api
	private static $_befor_api = null;

	
	# 顶层api
	protected static $_top_api;

	public function __construct() {
		$this->_request_sys_name = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : null;
		$this->_top_api = $_POST['func'];
	}


	/**
	 * back 
	 * 2.0 返回方法
	 * @param mixed $response 
	 * @param int $status 
	 * @param mixed $messageArgs 
	 * @param mixed $message 
	 * @final
	 * @access public
	 * @return void
	 */
	final public function back($response = null, $arr = [0,'ok',SUCCESS], $messageArgs=null, $message = null) {

		if($arr === SAME_BACK ) 'message'];
            $flag = $response['flag'];
            $status = $response['status'];
            $response = $response['response'];

		}else {
			if( !is_array($arr) ) {
				throw new \Exception("status must be a array");
			}

			$status = $arr[0];
			$message = $arr[1];
			$flag = $arr[2];
			$end = $arr[3];

			if($flag == SUCCESS and $status == 0 ) {
				$this->invokeFlag = SUCCESS;
			}elseif($flag == OK and $status != 0 ) {
				$this->invokeFlag = NOTIFY;
			}elseif($flag != OK and $status != 0) {
				$this->invokeFlag = FAIL;
			}
		}['response'] = $response===null ? array() : $response;
		$data['status'] = intval($status);
		$data['message'] = $message!=null ? $message : ($status==0 ? "ok" :  C('ERROR')[$status] ) ;
		$data['flag'] = $flag;
		if($data['message']==null)
			$data['message']=C('ERROR')[9];
		//如果当前指定了消息或者状态码率, 且指定了有效的消息替换参数, 则进行消息替换处理
		if($messageArgs!==null && $data['message']!='ok' && is_array($messageArgs) && sizeof($messageArgs)>0){
			$search = array();
			foreach (array_keys($messageArgs) as $v)
				$search[] ="{#{$v}}";
			$data['message'] = str_replace($search, array_values($messageArgs), $data['message']);
		}

		// 调用计数器
		if(self::$_invokeLevel > 0 && $end != END_INVOKE) {
			DG($data, DG_RES_OBJECT);
			return $data;
		}

		// api-getway预检测
		if(APP_DEBUG && !$status) {
			$checkOutputData = array(
				'api' => $this->_top_api,
				'data' => $response['_output'],
			);
			M("Com.Callback.Conf.Center.debugCheck")->debugCheck( $checkOutputData );
		}

		// wdog and return
		DG($data, GET_DG_OBJECT);
		die(json_encode($data, JSON_UNESCAPED_UNICODE));
	}


	/**
	 * 请求处理完毕, 结束当前程序并发送响应结果给客户端
	 * @param mixed $response  	响应结果
	 * @param integer $status 		响应状态
	 * @param array $messageArgs	消息替换参数列表,用于替换配置或者指定的消息内的占位符
	 * @param string $message 		响应消息文本, 用于指定展示给调用的可视化文本
	 * @return void or array 如果当前处于api调用期间, 则不会终止程序, 会把响应数据返回给调用者
	 */
	final public function res($response = null, $status = 0, $messageArgs=null, $message = null){
		
		if( is_array($status) ) {
			throw new \Exception("status must be a number");
		}

		$data['response'] = $response===null ? array() : $response;
		$data['status'] = intval($status);
		$data['message'] = $message!=null ? $message : ($status==0 ? "ok" :  C('ERROR')[$status] ) ;
		if($data['message']==null)
			$data['message']=C('ERROR')[9];
		//如果当前指定了消息或者状态码率, 且指定了有效的消息替换参数, 则进行消息替换处理
		if($messageArgs!==null && $data['message']!='ok' && is_array($messageArgs) && sizeof($messageArgs)>0){
			$search = array();
			foreach (array_keys($messageArgs) as $v)
				$search[] ="{#{$v}}";
			$data['message'] = str_replace($search, array_values($messageArgs), $data['message']);
		}

		// 调用计数器
		if(self::$_invokeLevel > 0) {
			DG($data, DG_RES_OBJECT);
			return $data;
		}

		// api-getway预检测
		if(APP_DEBUG && !$status) {
			$checkOutputData = array(
				'api' => $this->_top_api,
				'data' => $response['_output'],
			);
			M("Com.Callback.Conf.Center.debugCheck")->debugCheck( $checkOutputData );
		}

		// wdog and return
		DG($data, GET_DG_OBJECT);
		die(json_encode($data, JSON_UNESCAPED_UNICODE));
	}
        
	final public function endInvoke($response = null, $status = 0, $messageArgs=null, $message = null){
		self::$_invokeLevel = 0;
		$this->res($response, $status , $messageArgs, $message);
	}

	/**
	 * 无调用返回
	 */
	final public function res2($response = null, $status = 0, $messageArgs=null, $message = null){
		if( is_array($status) ) {
			throw new \Exception("status must be a number");
		}
	
		$data['response'] = $response===null ? array() : $response;
		$data['status'] = intval($status);
		$data['message'] = $message!=null ? $message : ($status==0 ? 'ok' :  C('ERROR')[$status] ) ;
		if($data['message']==null)
			$data['message']=C('ERROR')[9];
		//如果当前指定了消息或者状态码率, 且指定了有效的消息替换参数, 则进行消息替换处理
		if($messageArgs!==null && $data['message']!='ok' && is_array($messageArgs) && sizeof($messageArgs)>0){
			$search = array();
			foreach (array_keys($messageArgs) as $v)
				$search[] ="{#{$v}}";
			$data['message'] = str_replace($search, array_values($messageArgs), $data['message']);
		}
		
		return $data;
	}

	
	/**
	 * invoke
	 * 本地调用一个API,  返回调用结果
	 * @param string $apiPath  要调用的Api字符串路径 demo: Wap.Goods.lists 
	 * @param array $apiArgs  调用指定Api时候的参数
	 * @return mixed
	 */
	final protected function invoke($apiPath, $apiArgs = '') {   //todo rpc call init
		$this->invokeState = null;
		$cut = explode('.', $apiPath);
		$function	=	$cut[4];
		$rpcConf = C('RPC_INVOKE')['CONTAINER'][strtolower( $cut[1] )];
		DG($apiPath, CREATE_DG_OBJECT);
		if($rpcConf) {
			self::$_invokeLevel++;
			DG(['msg'=>$apiArgs, 'main'=>$apiPath], DG_CALL_OBJECT);
			$response = rpc_invoke($apiPath, $apiArgs, $rpcConf['host']);
			self::$_invokeLevel--;
			DG($response, DG_RES_OBJECT);
		}else {
			$object	=	M($apiPath);  	//实例化需要invoke的类
			self::$_invokeLevel++;
			DG(['msg'=>$apiArgs, 'main'=>$apiPath], DG_CALL_OBJECT);
			$response = $object->$function($apiArgs);
			self::$_invokeLevel --;
		}
		if($response['status'] === false || $response['status'] === NULL){
			$reponse['status'] = 16;
		}
		return  $response;
	}

	

	/**
	 * push_queue( $queue_name, $function, [$post , $sync,  $sync_time, $unque_id, $lev] ) 
	 * 推送到队列
	 * @param string $queue_name 队列名称   order
	 * @param string $function 回调接口   Queue.Demo.test
	 * @param mixed $post 回调传递的数据 数据格式自己定义
	 * @param bool $sync  异步调用时是否线程等待 处理成功回应(处理安全性比较高的数据)
	 * @param int $delay 调用延时, 该参数设置消息推送到队列后延迟多久执行
	 * @param bool $sync_time  异步调用时线程等待时间
	 * @param mixed $unque_id 数据唯一ID标识符
	 * @param int $lev 队列回调处理的优先级 1高 2 低 (队列服务器异步调用时的调用级别)
	 *
	 * @return boolean 返回true 或 balse
	 */
	protected function push_queue($function, $post = null, $sync = 1, $delay = 0 , $sync_time = 60 ,$unque_id = null, $lev = 1) {
		# getinfo
		if( !isset( self::$_queueObj ) ) {
			$queue_server = C('QUEUE_CONNECT_CONFIG');
			$queue_server = $queue_server[0];
			self::$_queueObj = new BeansTalk(
				array(
					'persistent' => true,
					'host' => $queue_server['host'] ? $queue_server['host'] : "127.0.0.1",
					'port' => $queue_server['port'] ? $queue_server['port'] : 11300,
					'timeout' => $queue_server['timeout'] ? $queue_server['timeout'] : 60,
					'logger' => null
				)
			);
			# connect
			$res = self::$_queueObj->connect(5); # 设置5秒超时时间
			if(!$res) {
				return false;
			}
		}
		

		# assembly data
		$data = array(
			'sync' => $sync,                   # 回调是否等待结束
			'sync_time' => $sync_time,         # 回调线程等待时间
			'function' => $function,		   # 回调API地址
			//'queueId'=> $queue_name,           # 队列名称
			'pushTime' => time(),              # 推入队列时间
			'message' => $post,                # 消息体
			'unque_id' => $unque_id ? $unque_id : guid(),           # 唯一ID
			'worker_host' => C('QUEUE_WORKER_HOST')['host'], # 回调地址
			'error_push_number' => 0,          # 入栈出错计数器
			'error_pop_number' => 0,           # 出栈出错计数器
			'lev' => $lev,					   # 执行优先级
		);

		# check data and extends attr
		if(count($this->extends_attr) > 0 && is_array($this->extends_attr)) {
			$data = array_merge($data, $this->extends_attr);
			if($this->clear_flag) {  # 是否清除扩展属性
				$this->extends_attr = array();
			}

			if(count($data) <= 0 ) {
				return false;
			}
		};

		# do
//		$function_queue_name = $queue_name;
		$queue_name = $sync ? 'sync': 'async';
		self::$_queueObj->useTube($queue_name);
		$push_conf = C('QUEUE_CONNECT_CONFIG');
		$res = self::$_queueObj->put(0, $delay, $push_conf['0']['timeout'], json_encode($data));
		DG(['push_queue_status', $res, $data], SUB_DG_OBJECT);
		if($res) {
			return true;
		} else {
			return false;
		}
	}
	
	
	

	/**
	 * 设置队列扩展属性
	 * setQueueExtendsAttr 
	 * @param array $array   array("fail_function"=>"wms.order.demo"); 数组型扩展属性
	 * @param blooean $clear 扩展属性被使用后是否清除 
	 * @access public
	 * @return boolean 返回 成功, 失败
	 */
	public function setQueueExtendsAttr($arr, $clear = true) {
		if(is_array($arr)) {
			$this->extends_attr = $arr;
			$this->clear_flag = $clear;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * response_queue 
	 *
	 * 响应队列 同步调用时返回消费结果
	 * @param mixed $flag  true 消费失败  false 消费成功
	 * 
	 * 如果消费失败($flag = false) 会被重新扔回队列下一次继续请求消费
	 * 如果消费失败大于5次会被扔进消费失败的队列($queue_name:push:fail) 不做任何处理(后续处理逻辑等待开发完善)
	 *
	 * @access protected
	 * @return void
	 */

	protected function response_queue( $flag = true ) {
		if( $flag ) {
			echo 1;
		} else {
			echo 0;
		}
	}

	/**
	 * 获取全局自增id
	 * getSequence 
	 * 
	 * @final
	 * @access protected
	 * @return void
	 *
	 */
	final public function getGlobalSequence() {
		$res = $this->getOne("select get_sequence() as id", null, self::DEFAULT_DBCONFIG_FLAG_WRITE);
		return $res['id'];
	}

	
	
	/**
	 * sendSMS 
	 * 
	 * @param mixed $params 
	 * @type string text 短信, voice 语音
	 * @access public
	 * @return void
	 */
	final protected function sendSMS($params, $type = 'text'){
		if(!isset($this->sms)) {
			$this->sms = new Sms();
		}
		
		if ( empty($params['numbers']) ) {
			DG(['sendsms_error.numbers', $params], SUB_DG_OBJECT);
			return  false; 
		}
		if ( empty($params['message']) ) {
			DG(['sendsms_error.message', $params], SUB_DG_OBJECT);
			return  false;
		}
		$numbers = is_array($params['numbers']) ? $params['numbers'] : array($params['numbers']);
		if($type == 'voice') {
			$content = $params['message'];
			$res = $this->sms->sendVoice($numbers, $content);
		}else {
			$time = $params['time'];
			$content = C('SMS_MODEL').$params['message'];
			$res = $this->sms->sendSMS($numbers, $content);
		}
		if($res['status'] == 0){ 
			DG(['sendsms_ok', $params], SUB_DG_OBJECT);
			return  true;
		}else{
			DG(['sendsms_error.send', $params], SUB_DG_OBJECT);
			return false;
		}
	}

	

	/**
	 *
	 * 验证输入参数
	 *
	 * checkInput 
	 * 
	 * @param mixed $data 
	 * @access protected
	 * @return void
	 */
//            final protected function checkInput($rule, $data)
//        {
/*    $check = D()->validate($rule)->autoValidation($data, '');
    if (false === $check) {
        $this->checkError = D()->getError();
        $this->errorField = D()->now_check_field;
        return false;
    } else {
        return true;
    }*/
}

	/**
	 *
	 * 返回验证失败的结果
	 *
	 * checkError 
	 * 
	 * @access public
	 * @return void
	 */
	public function getCheckError() {
		return $this->
    }
  	/**
	 *
	 * 返回验证失败的结果
	 *
	 * checkError 
	 * 
	 * @access public
	 * @return void
	 */
	public function getErrorField() {
		return $this->errorField;
	}
        
        /**
         * 业务层（外部）开启事务标识
         */
        final public function startOutsideTrans(){
            $transFlag = D()->getTransStatus();
            if($transFlag == 'start'){
                return true;
            }else{
                throw new \Exception('事务未开启',1001);
            }
            
		}

	/**
	 * 设定字段操作权限
	 * setPraviteField 
	 * @param mixed $data 
	 * @final
	 * @access public
	 * @return void
	 */
	final public function setPrivateField($data) {
		D()->privateField = $data;
	}
        
        /**
         * 
         * @param type $device
         * @param type $device_token
         * @param type $sys_name
         * @param type $play_sound
         * @param type $message_type
         * @param type $message_params
         * @param type $type
         */
    final protected function pushAppMessage($device, $device_tokens, $message_type, $sys_name = BOSS, $play_sound = TRUE, $message_params = array(), $type = 'unicast')
    {
        $apiPath = "Com.Common.Message.UmengMsg.pushMessage";
        $data = array(
            'sys_name' => $sys_name,
            'type' => $type,
            'device' => $device,
            'device_tokens' => $device_tokens,
            'message_type' => $message_type,
            'message_params' => $message_params,
            'play_sound' => $play_sound,
        );
        $push_res = $this->invoke($apiPath, $data);
//            $push_res = $this->push_queue($apiPath, $data, 0);

        return TRUE;
    }
 

}
?>
