<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 系统级公共函数
 */



function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
    if (function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        return iconv_substr($str, $start, $length, $charset);
    }
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) {
        return $slice . "...";
    }
    return $slice;
}
/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string 
 */
function encrypt_password($str) {
    $key = C('UC_AUTH_KEY');
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 请求一个服务层Api
 * @param string $func    	api 路径, 	ex. Wap.Address.getAllAddressByUser
 * @param array $params	api调用参数
 * @return NULL
 */
function rpc_invoke($func, $params = array(),  $host, $client = 'SUBVERT', $timeout = 13, $connect_time_out = 3) {
	$sign = C('SINGN_KEY');
	$params = array(
		'func' =>  $func,
		'params' =>json_encode( $params, JSON_UNESCAPED_UNICODE),
	);
    DG(['rpc_invoke host:'.$host, 'params:'.$params], SUB_DG_OBJECT);
    $params['info'] = 'subvert';
	$params['time'] = NOW_TIME;
	$params['signKey'] = md5($params['params'].$sign.$func);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $client);
	curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_time_out);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$response = curl_exec($ch);

	if( $response  ) {
		$response = json_decode($response, true);
	}

	if($response['status'] === false || $response['status'] === null){
		$err = curl_error($ch);
		DG(['rpc_invoke error:'.$err, 'res:'.$response], SUB_DG_OBJECT);
		$response['status'] = 22;
		//throw new \Exception('后端服务异常:paycenter');
	}
    L($response);
	return $response;
}

/**
 * 获取字符串拼音首字母
 * @param 需要获取首字母的字符串  $str
 * @return string 首字母
 */
function getinitial($str) {
    $str = iconv("UTF-8", "gb2312", $str);
    $asc = ord(substr($str, 0, 1));  //ord()获取ASCII
    if ($asc < 160) { //非中文
        return substr($str, 0, 1);
    } else {   //中文
        $asc = $asc * 1000 + ord(substr($str, 1, 1));
        //获取拼音首字母A--Z
        if ($asc >= 176161 && $asc < 176197) {
            return 'A';
        } elseif ($asc >= 176197 && $asc < 178193) {
            return 'B';
        } elseif ($asc >= 178193 && $asc < 180238) {
            return 'C';
        } elseif ($asc >= 180238 && $asc < 182234) {
            return 'D';
        } elseif ($asc >= 182234 && $asc < 183162) {
            return 'E';
        } elseif ($asc >= 183162 && $asc < 184193) {
            return 'F';
        } elseif ($asc >= 184193 && $asc < 185254) {
            return 'G';
        } elseif ($asc >= 185254 && $asc < 187247) {
            return 'H';
        } elseif ($asc >= 187247 && $asc < 191166) {
            return 'J';
        } elseif ($asc >= 191166 && $asc < 192172) {
            return 'K';
        } elseif ($asc >= 192172 && $asc < 194232) {
            return 'L';
        } elseif ($asc >= 194232 && $asc < 196195) {
            return 'M';
        } elseif ($asc >= 196195 && $asc < 197182) {
            return 'N';
        } elseif ($asc >= 197182 && $asc < 197190) {
            return 'O';
        } elseif ($asc >= 197190 && $asc < 198218) {
            return 'P';
        } elseif ($asc >= 198218 && $asc < 200187) {
            return 'Q';
        } elseif ($asc >= 200187 && $asc < 200246) {
            return 'R';
        } elseif ($asc >= 200246 && $asc < 203250) {
            return 'S';
        } elseif ($asc >= 203250 && $asc < 205218) {
            return 'T';
        } elseif ($asc >= 205218 && $asc < 206244) {
            return 'W';
        } elseif ($asc >= 206244 && $asc < 209185) {
            return 'X';
        } elseif ($asc >= 209185 && $asc < 212209) {
            return 'Y';
        } elseif ($asc >= 212209) {
            return 'Z';
        } else {
            return '~';
        }
    }
}



function C( $name = null, $value = null, $default = null ){
    static $_config = array();
    // 无参数时获取所有
    if( empty( $name ) ) {return $_config;}
    // 优先执行设置获取或赋值
    if( is_string( $name ) ) {
        if( !strpos( $name, '.' ) ) {
            $name = strtolower( $name );
            if( is_null( $value ) ) return isset( $_config[$name] ) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode( '.', $name );
        $name[0] = strtolower( $name[0] );
        if( is_null( $value ) ) return isset( $_config[$name[0]][$name[1]] ) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if( is_array( $name ) ) {
        $_config = array_merge( $_config, array_change_key_case( $name ) );
        return;
    }
    return null; // 避免非法参数
}

/**
 * 抛出异常处理
 * @param string $msg
 *        异常消息
 * @param integer $code
 *        异常代码 默认为0
 * @param blooed $log
 *        强制日志记录
 * @param blooed $dg
 *        是否进行过DG记录
 * @return void
 */
function E( $msg, $code = 0, $dg = false ){
	!$dg ? DG(['exception', $msg], SUB_DG_OBJECT) : null;
	if(empty($msg)) {
		$arr =  C("ERROR");
		$msg = $arr[$code];
	}
    $error = new System\Error( $msg , $code );
	if( APP_DEBUG ) {
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		$error ->msg = $trace.$msg;
        $error->log( );
	}
    $error->back( );
}

function L( $msg, $code = 0 ,$fileName = 'log.txt', $nornal = true){

	if(is_array($msg) || is_object($msg) || is_resource($msg))
		$msg = var_export($msg, true)."\n";
	
	if(is_bool($msg) == true) {
		if($msg == true) {
			$msg = 'true';
		}else {
			$msg = 'false';
		}
	}
	
	if(APP_DEBUG) {
		if($nornal == true) {
			$e = new \Exception();
			$trace = $e->getTrace();
			$file = $trace[0]['file'];
			$line = $trace[0]['line'];
			$msg = "\n---------------------------\n". $file ."  line: $line \n". $msg;
		}
	}

	if($nornal == false )
		$msg = "\n".$msg;
    $error = new System\Error( $msg, $code);
	$error->log($fileName);
	return $msg;

}

/**
 * 调试记录
 *
 * trace 
 * 
 * @param string $value 
 * @param string $label 
 * @param string $level 
 * @param mixed $record 
 * @access public
 * @return void
 */
function trace($value='[api]',$label='',$level='DEBUG',$record=false, $fileName = 'trace.txt') {
    $error = new System\Error( "[ $level ]"." - ".$value, "");
	$error->log2($fileName);
	return $msg;
}


/**
 * 获取一个Redis操作对象
 * @param boolean $useSlave
 *        是否使用从服务器; 默认为false. 即使用主服务器
 * @return \Redis
 */
function R( $useSlave = false ){
    static $redis = array();
    $serverType = $useSlave ? 'slave' : 'master';
    // 如果已存在指定类型Redis服务器的句柄, 则
    if( isset( $redis[$serverType] ) ) return $redis[$serverType]; // 直接返回
                                
    // 创建Redis链接句柄
    $redis_cluster = C( 'REDIS_CONNECT_CONFIG' );
    $config = $redis_cluster[$serverType];
    
    // 如果是从服务器, 则根据配置进行负载均衡
    if( $useSlave ) {
        $index = get_slave_redis( count( $config ) );
        $config = $config[$index];
    }
    $handle = new Redis( );
	$con = $handle->connect( $config['host'], $config['port'] );
	DG(['connect redis:', $con], SUB_DG_OBJECT);
	if(isset($config['pwd'])) {
		$handle->auth($config['pwd']);
	}
	$redis[$serverType] = $handle;
    return $redis[$serverType];
}

function get_slave_redis( $slave_number ){
    // 单个从服务器直接返回
    if( $slave_number <= 1 ) {return 0;}
    
    // hase 散列算法取链接池句柄
    $hash = get_hash_id( mt_rand( ), $slave_number );
    return $hash;
}

/**
 * get_hash_id
 * 随机数 获取hase散列id
 * @param mixed $id
 * @param mixed $m
 * @access private
 * @return void
 */
function get_hash_id( $id, $m ){
    $k = md5( $id );
    $l = strlen( $k );
    $b = bin2hex( $k );
    $h = 0;
    
    for( $i = 0; $i < $l; $i++ ) {
        $h += substr( $b, $i * 2, 2 );
    }
    
    $hash = ( $h * 1 ) % $m;
    return $hash;
}


/**
 * 验证邮箱格式是否正确
 * @email 邮箱地址
 * @return boolean
 */
function check_email_format( $email ){
    return preg_match( '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email ) === 1;
}

/**
 * 验证手机号码格式是否正确
 * $mobile 手机号码
 * @return boolean
 */
function check_mobile_format( $mobile ){
    return preg_match( '/^1[3578][0-9]{9}$/', $mobile ) === 1;
}

/**
 * 数组去重
 * @return array
 */
function unique_arr( $array2D, $stkeep = false, $ndformat = true ){
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if( $stkeep ) $stArr = array_keys( $array2D );
    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if( $ndformat ) $ndArr = array_keys( end( $array2D ) );
    // 降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach( $array2D as $v ) {
        $v = join( ",", $v );
        $temp[] = $v;
    }
    // 去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique( $temp );
    // 再将拆开的数组重新组装
    foreach( $temp as $k=>$v ) {
        if( $stkeep ) $k = $stArr[$k];
        if( $ndformat ) {
            $tempArr = explode( ",", $v );
            foreach( $tempArr as $ndkey=>$ndval )
                $output[$k][$ndArr[$ndkey]] = $ndval;
        }else
            $output[$k] = explode( ",", $v );
    }
    return $output;
}
/**
 * 处理异常回溯信息
 * @param $exception
 * @return string exception backtrace info.
 */
function getExceptionTraceAsString($trace) {
    $rtn = "";
    $count = 0;
    foreach($trace as $frame) {
        $args = "";
        if (isset($frame['args'])) {
            $args = array();
            foreach($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                }
                elseif (is_array($arg)) {
                    $args[] = "Array";
                }
                elseif (is_null($arg)) {
                    $args[] = 'NULL';
                }
                elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                }
                elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                }
                elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                }
                else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        $rtn .= sprintf("#%s %s(%s): %s(%s)\n", $count, $frame['file'], $frame['line'], $frame['function'], $args);
        $count++;
    }
    return $rtn;
}


/**
 * 对AES128加密数据进行验证
 * @param string  $deviceSign      加密数据
 * @param string  $clientTime      用户时间戳
 * @return bool   验证结果
 * @author 刘靖
 */
function devicesign_check($deviceSign, $clientTime){
    $aesKey = date('ymdHHdmy', $clientTime);
    $deviceSign = aes128_decrypt($deviceSign, $aesKey);
    $clientCrcCode = substr($deviceSign, -1);
    $deviceSign = substr($deviceSign, 0, -1);
    $crcCode = (intval('20'.substr($aesKey, 0, 2)) + intval(substr($aesKey, 2, 2)) + intval(substr($aesKey, 4, 2)) + intval(substr($aesKey, 6, 2)) ) %10;
    if($crcCode != $clientCrcCode)
        return false;
    return $deviceSign;
}

/**
 * 使用AES128对数据进行解密
 * @param string  $data      要解密的数据
 * @param string  $key    加密数据时候使用的密钥(128bit-->32byte密钥; 256bit-->32byte密钥)
 * @param string  $iv    加密向量数据, 默认为: 0123456789123456
 * @return string   已解密的数据
 * @author 刘靖
 */
function aes128_decrypt($data, $key, $iv='0123456789123456', $padding="\0") {
    //先进行解密, 最后对解密的数据进行填充去除
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, hex2bin($data), MCRYPT_MODE_CBC, $iv), $padding);
}

/**
 *
 * 生成全局唯一ID
 * guid 
 * 
 * @access public
 * @return void
 */
function guid(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);
		$uuid = chr(123)
			.substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid,12, 4).$hyphen
			.substr($charid,16, 4).$hyphen
			.substr($charid,20,12)
			.chr(125);
		return $uuid;
	}
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}


function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 创建一个数据库操作对象
 *
 * D 
 * 
 * @access public
 * @return void
 */
function D($name = '', $tablePrefix='', $connection='') {
    static $_model  = array();
    $class      =   'System\\Model';
    $guid           =   $tablePrefix . $name . '_' . $class;
	if (!isset($_model[$guid])) {
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
	}
    return $_model[$guid];
}  

/**
 * 创建一个搜索引擎操作对象
 * index
 * @access public
 * @return void
 */
function EL($index=null) {
	static $_model  = array();
	if (!isset($_model[$index])) {
		$est = new  \Search\Elasticsearch();
		$config = array(
			'host' => C('SEARCH')['host'],
			'port' => C('SEARCH')['port']
		);
		$est->setConf($config);
		$est->setIndex($index);
		$_model[$index] = $est;
	}
    return $_model[$index];
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value='', $path=DATA_PATH) {
	static $_cache  =   array();
	static $_connect  = false;
	if($_connect == false) {
		\System\Storage::connect("file");
		$_connect = true;
	}
    $filename       =   $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            if(false !== strpos($name,'*')){
                return false; // TODO 
            }else{
                unset($_cache[$name]);
                return \System\Storage::unlink($filename,'F');
            }
		} else {
//			echo 1233;exit;
            \System\Storage::put($filename,serialize($value),'F');
            // 缓存数据
            $_cache[$name]  =   $value;
            return ;
        }
    }
    // 获取缓存数据
    if (isset($_cache[$name]))
        return $_cache[$name];
    if (\System\Storage::has($filename,'F')){
        $value      =   unserialize(\System\Storage::read($filename,'F'));
        $_cache[$name]  =   $value;
	} else {
        $value          =   false;
	}
    return $value;
}

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name,$value='',$options=null) {
	static $cache   =   '';
//	static $_connect  = false;
//	if($_connect == false) {
//		\System\Cache::connect("FILECACHE");
//		$_connect = true;
//	}

    if(is_array($options) && empty($cache)){
        // 缓存操作的同时初始化
//        $type       =   isset($options['type'])?$options['type']:'';
        $cache      =   System\Cache::getInstance("filecache",$options);
    }elseif(is_array($name)) { // 缓存初始化
 //       $type       =   isset($name['type'])?$name['type']:'';
        $cache      =   System\Cache::getInstance("filecache",$name);
        return $cache;
	}elseif(empty($cache)) { // 自动初始化
        $cache      =   System\Cache::getInstance("filecache");
    }
    if(''=== $value){ // 获取缓存
        return $cache->get($name);
    }elseif(is_null($value)) { // 删除缓存
        return $cache->rm($name);
    }else { // 缓存数据
        if(is_array($options)) {
            $expire     =   isset($options['expire'])?$options['expire']:NULL;
        }else{
            $expire     =   is_numeric($options)?$options:NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @return mixed
 */
function I($name,$default='',$filter=null) {
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'request' :   $input =& $_REQUEST;   break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        default:
            return NULL;
    }
    if(empty($name)) { // 获取全部变量
        $data       =   $input;
        array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        is_array($data) && array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return   isset($default)?$default:NULL;
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:NULL;
    }
    return $data;
}

function array_map_recursive($filter, $data) {
     $result = array();
     foreach ($data as $key => $val) {
         $result[$key] = is_array($val)
             ? array_map_recursive($filter, $val)
             : call_user_func($filter, $val);
     }
     return $result;
 }

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @return mixed
 */
function N($key, $step=0,$save=false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
    if(false !== $save){ // 保存结果
        S('N_'.$key,$_num[$key],$save);
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}

// 过滤表单中的表达式
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}

// 不区分大小写的in_array实现
function in_array_case($value,$array){
    return in_array(strtolower($value),array_map('strtolower',$array));
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function G($start,$end='',$dec=4) {
    static $_info       =   array();
    static $_mem        =   array();
    if(is_float($end)) { // 记录时间
        $_info[$start]  =   $end;
    }elseif(!empty($end)){ // 统计时间和内存使用
        if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
        if(MEMORY_LIMIT_ON && $dec=='m'){
            if(!isset($_mem[$end])) $_mem[$end]     =  memory_get_usage();
            return number_format(($_mem[$end]-$_mem[$start])/1024);
        }else{
            return number_format(($_info[$end]-$_info[$start]),$dec);
        }

    }else{ // 记录时间和内存使用
        $_info[$start]  =  microtime(TRUE);
        if(MEMORY_LIMIT_ON) $_mem[$start]           =  memory_get_usage();
    }
}

 /**
 * N函数用于实例化模型类
 * @param string $name
 *        资源地址 格式 '[Wap.Goods]' 或 [Goods] 则会自动定位到 Api/Common/ 分组下
 * @return 实例化后的单例对象
 */
function M( $name ){
    if( empty( $name ) ) return;
    static $class = array();
    $cut = explode( '.', $name );
//    if( count( $cut ) == 1 ) {
//        $group = 'Common';
//        $new_class = $cut[0];
//    }else {
        $group = $cut[2];
        $new_class = $cut[3];
   // }
    $layer = $cut[0];
    $model = $cut[1];
    // 单例判断
    $class_path = $layer.$model.$group.$new_class;
	if( isset( $class[$class_path] ) ) {
		return $class[$class_path];
	}
    // 加载实例化判断
    $new_class_name = "\\$layer"."\\$model\\" . $group . '\\' . $new_class;
    if( class_exists( $new_class_name, false ) ) {
        $class[$class_path] = new $new_class_name( );
        // 加载实例化判断
    }else {
        require API_PATH."$layer/$model/" . $group . '/' . $new_class . EXT;
        $class[$class_path] = new $new_class_name( );
    }
    
	# 创建日志记录对象
    return $class[$class_path];
}


/**
 * 文档验证
 *
 * docCheck 
 * 
 * @param mixed $name 
 * @access public
 * @return void
 */
function docCheck($name, $params) {
	$name = strtolower(trim($name));
	$where = array('api_name'=> $name);
	$_GET['docCheck']['noTrace'] = true;
	$res = D()->db(3, "DB_DOC")->table("16860_api_doc")->field("api_name,params")->where($where)->find();
	$doc_params = array_column(json_decode($res['params'], true), "param_name");
//        $is_params_need = array_column(json_decode($res['params'], true), "is_need");
	D()->db(0);
	$_GET['docCheck']['noTrace'] = false;
	$apiName = strtolower(trim($res['api_name']));
	if($apiName ===  $name) {
		foreach($doc_params as $k=>$v) {
			unset($params[$v]);
		}
		if(!empty($params)) {
			return 1;
		}
		return 0;
	}else {
		return 2;
	}

}

/**
 * 记录代码执行堆栈信息
 * DG 
 * @param mixed $msg   
 * @param mixed $flag  CREATE_DG_OBJECT SUB_DG_OBJECT DATA_DG_OBJECT GET_DG_OBJECT 参考常量表
 * @access public
 * @return void or array
 */

function DG($msg = null, $flag = null) {
	static $total = [];					# 统计对象载体
	static $v = ['root'];				# 调用栈
	static $i = 0 ;						# 调用计数器
	static $hlr = [];					# 寄存器
	static $info = [];					# 额外调用信息记录
	
	if(!C('DG_OPEN')) return ['DG_OPEN'];

	if($flag !== null) {
		switch($flag) {
		case CREATE_DG_OBJECT:		# 创建操作对象
			if( is_array($msg) ) {
				$info['call_time'] = $msg['time'];
				$info['api_start_time'] = microtime();
				$info['info'] = $msg['info']['user'];
				$info['sysname'] = $msg['sysname'];
				$info['operate_ip'] = $msg['info']['operate_ip'];
				$info['env'] = ENV;
				$msg = $msg['func'];
			}
			$i++;
			$object = $i."#".$msg;
			$total[$object]['main'] = $msg;
			break;

		case SUB_DG_OBJECT:			# 写入对象执行过程
			$total [$v[0]] ['process'] ['sub'] [] = $msg;
			break;

		case SQL_DG_OBJECT:			# 写入对象的操作信息
			$total [$v[0]] ['process'] ['sql'] [] = $msg;
			break;

		case DG_CALL_OBJECT:			# 写入对象的请求数据
			$object = $i."#".$msg['main'];
			$total [$v[0]] ['call'] [$object] ['data'] = $msg['msg'];
			array_unshift($v, $object); ## 压入
			break;

		case DG_RES_OBJECT:			# 写入对象的返回数据
			if(is_array($msg['response']) && count($msg['response'],1)>20 ) {
				$msg['response'] = 'res too many';
			}

			$total[$v[0]]['res'] = $msg;
			if(C('DG_TREE')) {
				$total [$v[1]] ['call'] [$v[0]] ['stack']  = $total[$v[0]];
				unset( $total[$v[0]] );
			}else {
				$total[$v[0]]['father'] = $v[1];
			}
			array_shift($v);			## 弹出
			break;

		case GET_DG_OBJECT:			# 获取操作对象
			if(is_array($msg['response']) && count($msg['response'],1)>20 ) {
				$msg['response'] = 'res too many';
			}
			$total[$v[0]]['father'] = $v[1];
			$total[$v[0]]['res'] = $msg;
			$info['api_end_time'] = microtime();
			$info['status'] = $msg['status'];
			$api = $total[ key($total) ] ['main'];
			$url = C('DG_LOCAL_URL');
			if(ENV == C('DG_LOCAL_ENV') && $url){
				$data['level'] = 3;
				$data['type'] = $api;
				$data['flag'] = 1;
				$data['extends'] = [date('Y-m-d H:i:s', NOW_TIME), json_encode($total,JSON_UNESCAPED_UNICODE), json_encode($info,JSON_UNESCAPED_UNICODE)];
				$url .= '/Index/add';
				curl($url, ['log'=>json_encode($data)], 'POST', 1);
			}

			\System\Logs::info($api, \System\Logs::LOG_FLAG_NORMAL, [date('Y-m-d H:i:s', NOW_TIME), $total, $info]); # 写入到syslog
		}
	}
}

function curl($url, $postData = array(), $method = 'GET', $timeout = 5){
    $data = '';
    if (!empty($url)) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            if (strtoupper($method) == 'POST') {
                $curlPost = is_array($postData) ? http_build_query($postData) : $postData;
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            }
			$data = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            $data = null;
        }
	}

    return $data;
}

function getRFiles($path,&$files) {
	if(is_dir($path)){
		if( preg_match("/\/\..*$/i", $path) ) {
			return;
		}
		$dp = dir($path);
		while ($file = $dp ->read()){
			if($file !="." && $file !=".."  ){
				getRFiles($path."/".$file, $files);  
			}  
		}  
		$dp ->close();  
	}  
	if(is_file($path) && preg_match("/\.(php)$/i", $path) ){ 
		$files[] =  $path;
	}
}

function getAFiles($dir){
	$files =  array();  
	getRFiles($dir,$files);
	return $files;  
}

/**
 * 递归载入
 * rLoad 
 * 
 * @access public
 * @return void
 */
function rLoad($dir) {
	$dir = rtrim($dir, '/');
	$files = getAFiles($dir);
	foreach($files as $k=>$v) {
		require_once $v;
	}
}
