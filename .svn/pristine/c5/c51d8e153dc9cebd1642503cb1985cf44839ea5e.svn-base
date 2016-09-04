<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 系统连接文件
 */
$connect = array(
    

	 # 数据库连接配置

/*	'DB_HOST' => '192.168.11.108,192.168.11.108', // 服务器地址
	'DB_NAME' => 'yun_develop,yun_develop', // 数据库名
	'DB_USER' => 'yelang,yelang', // 用户名
	'DB_PWD' => 'vacn123,vacn123', // 密码
	'DB_PORT' => '3306,3306', // 端口 */

	"DB_MASTER" => array(
		'DB_HOST' => 'rds04j3upaxnrcr675dy.mysql.rds.aliyuncs.com', // 主库服务器地址
		'DB_NAME' => 'ypt_db', // 数据库名
		'DB_USER' => 'ypt', // 用户名
		'DB_PWD' => 'Dn14RXFKYqTfHUtZ8xpR', // 密码
		'DB_PORT' => '3306', // 端口
	),

	"DB_SLAVE" => array(
		array(  # slave 1
			'DB_HOST' => 'rds04j3upaxnrcr675dy.mysql.rds.aliyuncs.com', // 从库服务器地址
			'DB_NAME' => 'ypt_db', // 数据库名
			'DB_USER' => 'ypt', // 用户名
			'DB_PWD' => 'Dn14RXFKYqTfHUtZ8xpR', // 密码
			'DB_PORT' => '3306', // 端口
		),
                array(  # slave 2
                        'DB_HOST' => 'rds37qz01goi065y187b.mysql.rds.aliyuncs.com', // 从库服务器地址
                        'DB_NAME' => 'ypt_db', // 数据库名
                        'DB_USER' => 'ypt', // 用户名
                        'DB_PWD' => 'Dn14RXFKYqTfHUtZ8xpR', // 密码
                        'DB_PORT' => '3306', // 端口
                ),
	),

	"DB_DOC" => array(   // doc 数据库配置信息
		'DB_TYPE' => 'mysql', // 数据库类型
		'DB_HOST' => '101.200.235.66', // 主库服务器地址
		'DB_NAME' => 'apidoc', // 数据库名
		'DB_USER' => 'yelang', // 用户名
		'DB_PWD' => '33d041813dcc9e0fb7590d7c34ffdec6', // 密码
		'DB_PORT' => '3306', // 端口
	),

	'DB_TYPE' => 'mysql', // 数据库类型
	'DB_PREFIX' => '16860_', // 数据库表前缀
	'DB_FIELDTYPE_CHECK' => false, // 是否进行字段类型检查
	'DB_FIELDS_CACHE' => true, // 是否进行字段类型检查
	'DB_CHARSET' => 'utf8', // 数据库编码默认采用utf8
	'DB_DEPLOY_TYPE' => 1, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
	'DB_RW_SEPARATE' => true, // 数据库读写是否分离 主从式有效
	'DB_MASTER_NUM' => 1, // 读写分离后 主服务器数量
	'DB_SLAVE_NO' => '', // 指定从服务器序号
	'DB_SQL_BUILD_CACHE' => true, // 数据库查询的SQL创建缓存
	'DB_SQL_BUILD_QUEUE' => 'file', // SQL缓存队列的缓存方式 支持 file xcache和apc
	'DB_SQL_BUILD_LENGTH' => 20, // SQL缓存的队列长度
	'DB_SQL_LOG' => false, // SQL执行日志记录
	'DB_BIND_PARAM' => false, // 数据库写入数据自动参数绑定

	/* 数据缓存设置 */
	'DATA_CACHE_TIME' => 0, // 数据缓存有效期 0表示永久缓存
	'DATA_CACHE_COMPRESS' => false, // 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK' => false, // 数据缓存是否校验缓存
	'DATA_CACHE_PREFIX' => '', // 缓存前缀
	'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File
	'DATA_CACHE_PATH' => TEMP_PATH, // 缓存路径设置 (仅对File方式缓存有效)
	'DATA_CACHE_SUBDIR' => false, // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
	'DATA_PATH_LEVEL' => 1, // 子目录缓存级别
	'DEFAULT_FILTER' => 'htmlspecialchars', // 默认参数过滤方法 用于I函数...
		
    'REDIS_CONNECT_CONFIG' => array(//redis 链接池配置
        'master' => array('host' => 'c9f4a7defab14870.m.cnbja.kvstore.aliyuncs.com', 'port' => 6379, 'pwd'=>"c9f4a7defab14870:FEpF2cooVYH1gOnb0rg9"), //主服务器配置
        'slave' => array(//从服务器配置
        // array('host'=>'192.168.3.56', 'port'=>6379),  //主服务器配置
        )
    ),
    'QUEUE_CONNECT_CONFIG' => array(// 异步调用队列服务器配置
        array('host' => 'queue.yunputong.com', 'port' => 11300, "timeout" => 60)
    ),
    'QUEUE_WORKER_HOST' => array(//队列异步调用地址 SERVER_API 地址 开发环境可以填写本机内网地址
        'host' => 'http://api.liangrenwang.com/',
    ),
    'SPHINX_CONF' => array(
        'host' => "sphinx.njw88.com",
        'port' => 9312,
        'limits' => 1000,
        'query_time' => 3,
        'default_field' => "*,id",
        'source' => "main",
        'search_model' => SPH_MATCH_EXTENDED2,
        'order_by' => SPH_SORT_EXTENDED
    ),
);

