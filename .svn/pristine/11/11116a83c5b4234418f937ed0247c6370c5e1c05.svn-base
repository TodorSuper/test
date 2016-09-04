<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 交易明细数据导出
 */

namespace Base\TradeModule\Account;

use System\Base;

class Trans extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Tc';
    }

    /**
     * Base.TradeModule.Account.Export.export
     * @param type $params
     * @return type
     */
    public function  export($params){
        $this->_rule = array(
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('tc_code','require',PARAMS_ERROR,MUST_CHECK),       //账户编码
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK), //提现查询开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK), //提现查询结束时间
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK), //提现状态
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出提现查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取平台标识  业务参数
        $tc_code        =  $params['tc_code'];
        if($params['start_time'] && $params['end_time']){
            $start_time     =  strtotime($params['start_time']);
            $end_time       =  strtotime($params['end_time'])+24*3600;
        }
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $order          =  $params['order'];
        //默认参数
        $default_title      =  array('流水号','对方账号','订单号','款项到账时间','实收金额（元）');  //默认导出列标题
        $default_fields     =  'tci.trade_no,oo.username,oo.b2b_code,from_unixtime(oo.pay_time),oo.real_amount';  //默认导出列
        $default_filename   =  '交易明细列表';
        $default_sql_flag   =  'getTransList';
        $default_order      =  'tci.create_time desc';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        //组装where 条件
        $where         =  array();
        isset($start_time) ? $where['tci.create_time'] = ['egt'=>$start_time] : null;
        isset($end_time) ? ( $where['tci.create_time'] = ['between', [$start_time, $end_time ] ] ) : null;
        $where['tci.type']                                  =   ['eq','B2B_CASH'];
        $where['tci.tc_code']                               =   ['eq',$tc_code];
        //组装调用导出api参数
        $params['where']        =  $where;
        $params['fields']       =  $default_fields;
        $params['title']        =  $title;
        $params['order']        =  empty($order) ? $default_order : $order;
        $params['center_flag']  =  SQL_TC;//订单中心
        $params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $params['filename']     =  $filename;
        $params['callback_api'] = $callback_api;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);
    }
}

?>
