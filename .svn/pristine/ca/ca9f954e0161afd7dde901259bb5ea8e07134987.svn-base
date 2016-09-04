<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 公共入口测试文件
 */
error_reporting(0);
define('PWD', '94368AABFC65209B15999ECD1A5B0BFE');
$password = $_GET['pwd'];
if($password != PWD){
    return;
}

unset($_POST);
$key = '_#@DU^^&JGK_((*&gjGH';
$data = $_GET;
$datas['params'] = $data['data'];
$datas['func'] = $data['func'];
# 计算验证数据
$sign = md5($data['data']. $key. $_GET['func']);
$datas['signKey'] = $sign;
$_GET = $datas;
# 开启调试模式
define("APP_DEBUG", 1);

# 设置启动模式 1.curl应答模式 2.socket tcp 应答模式
defined('START_MODE')    or  define('START_MODE',  1);

# MYSQL监控模式
define('MONITOR', FALSE);                 // 是否开启全局监控，暂未启用
define('MONITOR_LOG_PIPELINE', 'REDIS');  // 设定监控日志数据存储介质  REDIS 或 SQLITE
define('MONITOR_MYSQL', FALSE);           // 是否开启MYSQL监控
define('MONITOR_MYSQL_RATE', 0);          // 设定MYSQL监控比率 0-100
define('MONITOR_MYSQL_THE',  MONITOR_MYSQL || mt_rand(0, 99) < MONITOR_MYSQL_RATE); // 是否开启MYSQL子监控

# 设置系统路径
define('APP_PATH',  dirname(dirname(__FILE__)));
#环境变量
define('ENV', 'develop');
# 引入核心文件
require APP_PATH."/System/Core.class.php";

?>