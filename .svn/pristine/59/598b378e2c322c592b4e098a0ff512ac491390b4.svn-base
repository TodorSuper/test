<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: haowenhui <haowenhui>
 * +----------------------------------------------------------------------
 * | 初始化引导类
 */

namespace System;

class Url {

	/**
	 * dispose_url
	 * url参数解析函数
	 * @static
	 * @access public
	 */
	static public function dispose_url() {
		$err = C('ERROR');
		if(IS_GET) {
			# 获取其他参数
			$data = $_GET;

			# 处理请求数据
			self::dispose_request($data);

			# 获取API类名
			$api = $data['class'];
                        # 获取调用分组
                        $call_name = $data['name'];
			# 获取API方法 send
			$func = $data['function'];
                        
                        # model
                        $model = $data['model'];
                        
                        #layer
                        $layer = $data['layer'];

		}elseif(IS_POST) {
			# 获取其他参数
			$data = $_POST;
			
			# 处理请求数据
			self::dispose_request($data);
			
			# 获取调用分组
			$call_name = $data['name'];

			# 获取API类名
			$api = $data['class'];

			# 获取API方法 send
			$func = $data['func'];
                        
                        # model
                        $model = $data['model'];
                        
                        #layer
                        $layer = $data['layer'];
		
		}else{
		    exit;
			//E($err[4], 4);
		}
		
		# 引入相关API文件
//		if(file_exists($filename =  API_PATH. $layer.'/'.$model.'/'.$call_name. '/'. $api. EXT)) {
//			require $filename;
//		}
			
		# 实例化对象
//                $model = 
//		$new = "\\".$layer."\\$model\\". $call_name. '\\' .$api;
 //               $obj = new $new();
//		$obj = M($func);
		# 判断方法是否存在
//		if(!method_exists($obj, $func)) {
//			E($err[2], 2);
//		}

        # 验证传进来的参数是否存在，矫正参数的顺序
	//	$data = param_verify($obj, $func, $data);
		# 创建日志记录对象
		$cut = explode('.', $func);
		$function	=	$cut[4];
		DG(['func'=>$func, 'info'=>$data['info'], 'time'=>$data['time'],'sysname'=>$_SERVER['HTTP_USER_AGENT']], CREATE_DG_OBJECT);
		$object	=	M($func);  	//实例化需要invoke的类
		DG(['msg'=>$data['params'], 'main'=>$func], DG_CALL_OBJECT);
		$object->$function($data['params']);
		# 调用API
//		call_user_func(array($obj, $func), $data['params']);

	}

	/**
	 * dispose_request 
	 * 处理请求数据
	 * @param mixed $data 要处理的数据 引用传值
	 * @access private
	 * @return void
	 *
	 * Key Map:
     *   //请求的Api路径, 目前为三段, 分别为 Api模块名称.ApiClass名称.function名称
     *   func                    Wap.Collect.getList
     *   //Api的执行参数, 使用json_encode进行参数编码
     *   params                  {"uid":11}
     *   //请求的签名码, 参加下方生成说明
	 *   signKey                 029a8978e6cacdd1f272f4f77f3508ee
	 *
	 *	$signKey = md5($params.API_REQUEST_SECKEY.$func);
	 *
	 */

	static public function dispose_request(&$data) {
		# 定义基本数据
		$key = C('SINGN_KEY');
		define('API_REQUEST_SECKEY', $key);
		$func = $data['func'];
		$params = $data['params'];
		
		# 计算验证数据
		$sign = md5($params. API_REQUEST_SECKEY. $func);
		if($sign == $data['signKey']) {
			# 验证通过处理数据
			$data['params'] = json_decode($params,true);
//			$method = isset($data['method']) ? $data['method'] : '';
//			$func = explode('.', $func);
//                       $data['layer'] = $func[0];
//                        $data['model'] = $func[1];
//			$call_name = $func[2];
//			$data['type'] = $call_name;
//			$data['name'] = $call_name;
//			$data['class'] = $func[3];
//			$data['function'] = $func[4];
/*			if(EVN != "production") {
				# 非生产环境验证api是否添加至doc
				if(strtolower(trim($func[0])) === "base" || strtolower(trim($func[0])) === "com") {
					$check = docCheck($data['func'], $data['params']);
					if($check === 2) {
						E($err[14], 14);
					}elseif($check == 1) {
						E($err[15], 15);
					}
				 }
				
}*/
		}else {
			E($err[10], 10);
		}
		
	}

}

?>
