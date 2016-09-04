<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订购会导出
 * 
 */

namespace Base\SpcModule\Commodity;

use System\Base;

class Export extends Base
{
	
	
	public function __construct(){
		parent::__construct();
	}

	
	/**
     * Base.SpcModule.Commodity.Export.all
     * @param type $params
     * @return type
     */
	public function all($params){
		$this->rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),   //商家编码
            array('order', 'require', PARAMS_ERROR, ISSET_CHECK),   //商家编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),
			);
		// um.commercial_name,sch.name as channel_name,ss.name as salesman,
		$default_title    = array('客户姓名', '客户手机号', '客户店铺名', '渠道', '邀请人', '预收货款（元）', '已冲预收总额（元）', '剩余预收总额（元）');
		// $default_fields   = 'uc.name,uc.mobile,scu.advance_money,scu.spent_money,scu.balance,';
		$default_fields   = "sc_code, uc_code, advance_money, spent_money, balance";
		$default_sql_flag = 'spc_customer';
		$default_filename = '订购会全部表';
		$default_order    =  'advance_money desc';
		$default_api      =  'Com.Callback.Export.SpcExport.spcCustomerAll';

		$title        = empty($title)    ? $default_title  : $title;
		$filename     = empty($filename) ? $default_filename : $filename;
		$callback_api = empty($callback_api) ? $default_api : $callback_api;
		$sc_code      = $params['sc_code'];
		$spc_code     = $params['spc_code'];
		$order        = $params['order'];

		//组装where参数
		$where = array();
		$where['sc_code']    = array('eq', $sc_code);
		$where['status']     = array('eq', 'ENABLE');
		$where['spc_code']   = array('eq', $spc_code);

		//组装调用导出api参数
		$params['where']        =  $where;
		$params['fields']       =  $default_fields;
		$params['title']        =  $title;
		$params['center_flag']  =  SQL_SPC;//订单中心
		$params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
		$params['filename']     =  $filename;
		$params['order']        =  empty($order) ? $default_order : $order." desc";
		$params['callback_api'] =  $callback_api;
		// var_dump($params);exit();
		$apiPath  =  "Com.Common.CommonView.Export.export";
		$res = $this->invoke($apiPath, $params);
		return $this->res($res['response'],$res['status']);

	}

	/**
	 * Base.SpcModule.Commodity.Export.detail
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function detail($params){
		$this->rule = array(
			array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),   //商家编码
			array('order', 'require', PARAMS_ERROR, ISSET_CHECK),   //商家编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),
			);

		$default_title    = array('订单编号','客户名称','客户手机号','客户店铺名','订单实付总额（元）','邀请人','渠道','下单时间','支付方式','订单状态');
		$default_fields   = 'obo.b2b_code,scu.uc_code,obo.real_amount,obo.create_time,obo.pay_method,obo.order_status,obo.ship_status,obo.pay_status,obo.pay_type,obo.ship_method';
		$default_sql_flag = 'spc_detail';
		$default_filename = '订购会明细表';
		$default_order    =  'scu.advance_money desc';
		$default_api      =  'Com.Callback.Export.SpcExport.spcCustomerDetail';

		$title        = empty($title)    ? $default_title  : $title;
		$filename     = empty($filename) ? $default_filename : $filename;
		$callback_api = empty($callback_api) ? $default_api : $callback_api;
		$sc_code      = $params['sc_code'];
		$spc_code     = $params['spc_code'];
		$order        = $params['order'];

		//组装where参数
		$where = array();
		$where['scu.sc_code']    = array('eq', $sc_code);
		$where['scu.status']     = array('eq', 'ENABLE');
		$where['scu.spc_code']   = array('eq', $spc_code);
		$where['obo.pay_method'] = array('eq', 'ADVANCE');

		//组装调用导出api参数
		$params['where']        =  $where;
		$params['fields']       =  $default_fields;
		$params['title']        =  $title;
		$params['center_flag']  =  SQL_SPC;//订单中心
		$params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
		$params['filename']     =  $filename;
		$params['order']        =  empty($order) ? $default_order : "scu.".$order." desc";
		$params['callback_api'] =  $callback_api;
		$apiPath =  "Com.Common.CommonView.Export.export";
		$res     = $this->invoke($apiPath, $params);
		return $this->res($res['response'],$res['status']);
	}
}
