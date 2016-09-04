<?php 
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: haowenhui <haowenhui>
 * +----------------------------------------------------------------------
 * | 日志
 */

namespace System;

class Logs {

    const LOG_LEVEL_ERR           = 1;
    const LOG_LEVEL_WARN          = 2;
    const LOG_LEVEL_INFO          = 3;
    
    const LOG_FLAG_NORMAL         = 1;      // 正常日志
    const LOG_FLAG_ERROR          = 2;      // 错误日志
    const LOG_FLAG_WRITE          = 3;      // 存储日志（只做存储/查询，不做显示，不进入统计）
    
    
    /**
    * 错误级别定义
    * @var array
    */
    protected static $level       = array(
        'err',      // 严重错误
        'warn',     // 警告
        'info',     // 信息
    );

    protected static $levelToConst = array(
        'err' => self::LOG_LEVEL_ERR,      // 严重错误
        'warn' => self::LOG_LEVEL_WARN,     // 警告
        'info' => self::LOG_LEVEL_INFO,     // 信息  
    );
    
    /**
    * 写日志方式
    * @var string
    */
    protected static $record_type = 'syslog';
    protected static $record_obj;

    /**
     * 静态方法调用
     * @param  [type] $method    日志级别
     * @param  [type] $arguments {type: 日志类型, flag:日志标记，extends:array(1-20)扩展值}
     * @return [type]            [description]
     */
    public static function __callStatic($method, $arguments) {
        if( in_array($method, self::$level) ) {
            try {
                if( !self::validateArgs($arguments) ) {
                    throw new Exception("not enough arguments");    
                }
                
                if( 0 == self::record(self::$levelToConst[$method], $arguments[0], $arguments[1], $arguments[2]) ) {
                    return false;
                }

            }catch( Exception $ex ) {
                return false;
            }
            return true;
        }

        throw new Exception("Method [$method] does not exist.");
        
    }

    /**
     * 调用record_obj写日志
     * @param  [type] $level   [description]
     * @param  [type] $type    [description]
     * @param  [type] $flag    [description]
     * @param  [type] $extends [description]
     * @return [type]          [description]
     */
    protected static function record( $level, $type, $flag, $extends ) {
        if( self::$record_obj === null ) {
            if( self::$record_type == "file" ) {
                self::$record_obj = new LogByFile();
            } else if( self::$record_type == "syslog" ) {
                self::$record_obj = new LogBySyslog();
            } else {
                throw new Exception("not found record type [{self::$record_type}]");        
            }
        }
        
        return self::$record_obj -> record($level, $type, $flag, $extends );
    }

    /**
     * 验证参数
     * @param  [type] $arguments [description]
     * @return [type] boolean
     */
    protected static function validateArgs($arguments) {
        if( empty( $arguments[0] ) ) {
            return false;
        }

        if( empty( $arguments[1] ) ) {
            return false;
        }

        if( empty( $arguments[2] ) || ( is_array( $arguments[2] ) && 0 == count( $arguments[2] ) ) ) {
            return false;
        }
        return true;
    }
}

/**
 * 接口定义规范
 */
interface iLogBy {
    public function record($level, $type, $flag, $extends );
}


/**
 * @desc 文件写日志
 */
class LogByFile implements iLogBy {

    protected $logpath;
    protected $logfile;

    public function __construct( $logpath = '' ) {
        if( empty( $logpath ) ) {
            $logpath = LOG_PATH.'invokeLogs/';
        } 

        $this -> logpath = $logpath;
        $this -> createFolder( $this -> logpath );
        $this -> logfile = $logpath . date('Y-m-d') . '.log';
    }

    /**
     * 写日志
     * @param  [type] $level   [description]
     * @param  [type] $type    [description]
     * @param  [type] $flag    [description]
     * @param  [type] $extends [description]
     * @return [type]          [description]
     */
    public function record($level, $type, $flag, $extends ) {
        foreach($extends as $key => $val) {
            if( is_array( $val ) ) {
                $extends[$key] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
        }

        $data = array(
            "level" => $level,
            "type" => $type,
            "flag" => $flag,
            "extends" => $extends
        );

        return file_put_contents($this -> logfile, json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND|LOCK_EX);
    }

    /**
     * 创建文件夹
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function createFolder( $path ){
        if(!file_exists($path)){
            $this->createFolder(dirname($path));
            mkdir($path,0755);
        }
    }
}

/**
 * @dec 将日志写入syslog
 */
class LogBySyslog implements iLogBy {

    protected $level = array(
        // '' => LOG_EMERG,
        // '' => LOG_ALERT,
        // '' => LOG_CRIT,
        Logs::LOG_LEVEL_ERR => LOG_ERR,
        Logs::LOG_LEVEL_WARN => LOG_WARNING,
        // '' => LOG_NOTICE,
        Logs::LOG_LEVEL_INFO => LOG_INFO,
        // '' => LOG_DEBUG,
    );

    /**
     * 写日志
     * @param  [type] $level   [description]
     * @param  [type] $type    [description]
     * @param  [type] $flag    [description]
     * @param  [type] $extends [description]
     * @return [type]          [description]
     */
    public function record($level, $type, $flag, $extends ) {
		$rs = $this -> checkRequireFunc();

        if( !$rs[0] ) {
            throw new \Exception("function can not use, function name is:[{$rs[1]}]");
        }

        foreach($extends as $key => $val) {
            if( is_array( $val ) ) {
                $extends[$key] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
        }

        $data = array(
            "level" => $level,
            "type" => $type,
            "flag" => $flag,
            "extends" => $extends
        );

		openlog( 'yunputong-' . $data['type'], NULL, LOG_USER);
        return syslog($this -> level[$level], json_encode( $data, JSON_UNESCAPED_UNICODE ));
    }

    /**
     * 检测必须的方法是否被禁用
     * @param  array  $funcs [description]
     * @return [type]        [description]
     */
    public function checkRequireFunc( $funcs = array() ) {
        $defaults = array(
            'openlog', 'syslog',
        );

        if( !empty( $funcs ) ) {
            $defaults = array_merge( $defaults, $funcs );
        }

        foreach( $defaults as $func ) {
            if( !function_exists($func) ) {
                return array( false, $func );
            }
        }

        return array( true, '' );
    }

}

?>
