<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 公共入口文件
 */
# 开启调试模式
define("APP_DEBUG", 1);

# 设置启动模式 1.curl应答模式 2.socket tcp 应答模式
defined('START_MODE')    or  define('START_MODE',  1);

#api目录
define('API_ROOT','Subvert');

# 设置系统路径
define('APP_PATH',dirname(dirname(__FILE__)));

#环境变量      
define('ENV', 'develop');

# 引入核心文件
require APP_PATH."/System/Core.class.php";
?>
