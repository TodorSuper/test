<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 提现查询数据导出
 */

namespace Base\FcModule\Payment;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Fc';
    }

    /**
     * Base.FcModule.Payment.Export.export
     * @param array $params
     * @return type
     */
    public function  export($params){
        $this->_rule = array(
            //array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('sc_code','require',PARAMS_ERROR,ISSET_CHECK),       //商户编码
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出转账查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $order          =  $params['order'];
        //默认参数.
        $default_fields     =  $params['fields'];
        $default_sql_flag   =  'getFcPaymentList';
        $default_order      =  'affirm_time desc';
        //组装where 条件
        $where = $params['where'];
        //组装调用导出api参数
        $data['where']        =  $where;
        $data['fields']       =  $default_fields;
        $data['title']        =  $title;
        $data['order']        =  empty($order) ? $default_order : $order;
        $data['center_flag']  =  SQL_FC;//财务中心
        $data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $data['filename']     =  $filename;
        $data['callback_api'] = $callback_api;
        $data['template_call_api'] = $params['template_call_api'];
        $data['data'] = $params;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }
}

?>
