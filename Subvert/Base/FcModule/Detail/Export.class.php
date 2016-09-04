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

namespace Base\FcModule\Detail;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Fc';
    }

    /**
     * Base.FcModule.Detail.Export.export
     * @param array $params
     * @return type
     */
    public function  export($params){
        $this->_rule = array(
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('sc_code','require',PARAMS_ERROR,ISSET_CHECK),       //商户编码
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出转账查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取平台标识  业务参数
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $order          =  $params['order'];
        //默认参数
         $default_title      =  $params['title'];
         $default_fields     =  $params['fields'];

        $default_filename   =  $params['filename'];
        $default_sql_flag   =  'getPaymentList';
        $default_order      =  'tci.pay_time desc';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? null : $callback_api;

        //组装where 条件
        $where         =  array();
        !empty($params['oc_code']) && $where['tci.oc_code'] = $params['oc_code'];
        !empty($params['sc_code']) && $where['tci.sc_code'] = $params['sc_code'];
        !empty($params['pay_no']) && $where['ss.pay_no'] = $params['pay_no'];
        !empty($params['status']) && $where['confirm.status'] = $params['status'];
        !empty($params['f_status'])&& $where['confirm.f_status'] = $params['f_status'];
        !empty($params['b2b_code'])&& $where['tci.b2b_code'] = $params['b2b_code'];
        !empty($params['b2b_code'])&& $where['tci.b2b_code'] = $params['b2b_code'];
        !empty($params['pay_time'])&& $where['tci.pay_time'] = $params['pay_time'];
        !empty($params['pay_status'])&& $where['tci.pay_status'] = $params['pay_status'];
        if(!empty($params['pay_type'])&&$params['pay_type']=='remit'){
            $where['tci.pay_method']  = PAY_METHOD_ONLINE_REMIT;
        }else{
            !empty($params['pay_method'])?$where['tci.pay_method'] = $params['pay_method']:$where['tci.pay_method'] = array('in',array(PAY_METHOD_ONLINE_WEIXIN,PAY_METHOD_ONLINE_ALIPAY,PAY_METHOD_ONLINE_REMIT,PAY_METHOD_ONLINE_UCPAY));
            $where['tci.pay_status'] = OC_ORDER_PAY_STATUS_PAY;
        }
        $where['tci.sc_code'] =  $params['sc_code'];
        if(!empty($params['f_status'])&&is_array($params['f_status'])){
           $where['confirm.f_status'] = array('in', $params['f_status']);
        }elseif(!empty($params['f_status'])){
           $where['confirm.f_status'] =  $params['f_status'];
        }

        //组装调用导出api参数
        $params['where']        =  $where;
        $params['fields']       =  $default_fields;
        $params['title']        =  $title;
        $params['order']        =  empty($order) ? $default_order : $order;
        $params['center_flag']  =  SQL_FC;//财务中心
        $params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $params['filename']     =  $filename;
        $params['callback_api'] = $callback_api;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);
    }
}

?>
