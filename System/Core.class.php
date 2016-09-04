<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: haowenhui <haowenhui>
 * +----------------------------------------------------------------------
 * | 核心文件 负责载入框架的所有文件和处理基本业务逻辑
 */

# URL 模式定义
const URL_COMMON        =   0;  # 普通模式

# API类文件后缀
const EXT               =   '.class.php';

define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));

# 系统常量定义
defined('APP_DEBUG') 	or define('APP_DEBUG',      false);										# 是否调试模式
defined('CORE_PATH') 	or define('CORE_PATH',     __DIR__.'/');								# 系统目录 System
defined('DATA_PATH')     or define('DATA_PATH',     APP_PATH.'/Cache/Data/'); 					# 数据层目录
defined('LIB_PATH')     or define('LIB_PATH',       realpath('./Library').'/');					# 系统核心类库目录
defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.'/Common/');						# 应用公共目录
defined('CONF_PATH')    or define('CONF_PATH',      APP_PATH.'/Conf/');							# 应用配置目录
defined('LOG_PATH')     or define('LOG_PATH',       APP_PATH.'/Logs/');							# 应用日志目录
defined('API_PATH') 	or define('API_PATH',       APP_PATH."/".API_ROOT."/");                          # 项目路径 /Subvert/
defined('UPLOAD_PATH')     or define('UPLOAD_PATH',       APP_PATH.'/Cache/File/');							# 上传文件目录

defined('TEMP_PATH')     or define('TEMP_PATH',       APP_PATH.'/Cache/Tmp/');					# 应用日志目录

defined('LOG_FLAG_NORMAL')     or define('LOG_FLAG_NORMAL', 1);                                 # 正常日志
defined('LOG_FLAG_ERROR')     or define('LOG_FLAG_ERROR', 2);                                   # 错误日志
defined('LOG_FLAG_WRITE')     or define('LOG_FLAG_WRITE', 3);                                   # 存储日志（只做存储/查询，不做显示，不进入统计）

# 临时常量定义
define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);


# 引入初始化类
require CORE_PATH.'Start'.EXT;

# 引入url处理类
require CORE_PATH.'Url'.EXT;

# 引入异常处理类
require CORE_PATH.'Exception'.EXT;
require CORE_PATH.'Error'.EXT;

# 引入基本类库

require CORE_PATH.'Controller'.EXT;
require CORE_PATH.'Base'.EXT;

# 初始化系统
System\Start::initialize();

?>
