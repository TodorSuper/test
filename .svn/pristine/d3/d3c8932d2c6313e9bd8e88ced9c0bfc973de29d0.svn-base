<?php

/**
 * +---------------------------------------------------------------------
 * | www.laingrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单分析类
 */

namespace Base\BicModule\Oc;
use System\Base;

class Statistic extends Base
{
	private $_rule = null; # 验证规则列表

    public function __construct(){
        parent::__construct();
        $this->tablePrefix=C('DB_PREFIX');
        $this->connection=C('DB_BIC');
    }
  	
  	/**
  	 * 
  	 * Base.BicModule.Oc.Statistic.getOrderList
  	 * @param  [type] $params [description]
  	 * @return [type]         [description]
  	 */
  	public function getOrderList($params){
  		$this->_rule = array(
  		    array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
  		    array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
  		    array('pay_start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
  		    array('pay_end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
  		    array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
  		    array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
  		);
  		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
  		    return $this->res($this->getErrorField(), $this->getCheckError());
  		}

		$start_time     = $params['start_time'];
		$end_time       = $params['end_time'];
		$sc_code        = $params['sc_code'];
		$uc_code        = $params['uc_code'];
		$pay_start_time = $params['pay_start_time'];
		$pay_end_time   = $params['pay_end_time'];
		$page           = $params['page'];
		$page_number    = $params['page_number'];

		$where = array();
		!empty($uc_code) && $where['uc_code'] = $uc_code;
		!empty($sc_code) && $where['sc_code'] = $sc_code;
		!empty($start_time) && empty($end_time) && $where['create_time']  = array('egt', $start_time);
		!empty($end_time) && empty($start_time) && $where['create_time']  = array('elt', $end_time);
		!empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));

		!empty($pay_start_time) && empty($pay_end_time) && $where['pay_time']  = array('egt', $pay_start_time);
		!empty($pay_end_time) && empty($pay_start_time) && $where['pay_time']  = array('elt', $pay_end_time);
		!empty($pay_start_time) && !empty($pay_end_time) && $where['pay_time'] = array('between', array($pay_start_time, $pay_end_time));
		
		if ($where['pay_time'] && $where['create_time']) {
			unset($where['create_time']);
		}

		$data = array();
		$fields = "b2b_code,uc_code,sc_code,status_message,real_amount,create_time,pay_type,pay_method_message,pay_type_message,pay_time,ship_time";
		$data['fields'] = empty($fields) ? '*' : $fields;
		if(!empty($where)) $data['where'] = $where;
		$data['center_flag'] = SQL_BIC;       
		$data['sql_flag']    = 'order_list';  
		$data['page']        = $page;
		$data['order']       = 'id desc';
		$data['page_number'] = $page_number;
		$data['db_flag']     = 'bic_db';

		$apiPath     = "Com.Common.CommonView.Lists.Lists";
		$list_res    = $this->invoke($apiPath, $data);

		return $this->res($list_res['response']);

	}

	/**
	 * Base.BicModule.Oc.Statistic.export
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function export($params){
  		$this->_rule = array(
  		    array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
  		    array('end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
  		    array('pay_start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//下单开始时间
  		    array('pay_end_time','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
  		    array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
  		    array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
  		);
  		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
  		    return $this->res($this->getErrorField(), $this->getCheckError());
  		}

		$start_time     = strtotime($params['start_time']);
		$end_time       = strtotime($params['end_time']);
		$sc_code        = $params['sc_code'];
		$uc_code        = $params['uc_code'];
		$pay_start_time = strtotime($params['pay_start_time']);
		$pay_end_time   = strtotime($params['pay_end_time']);
		$page           = $params['page'];
		$page_number    = $params['page_number'];

		$params['start_time']     = $start_time;
		$params['end_time']       = $end_time;
		$params['pay_start_time'] = $pay_start_time;
		$params['pay_end_time']   = $pay_end_time;
		
		$where = array();
		!empty($uc_code) && $where['uc_code'] = $uc_code;
		!empty($sc_code) && $where['sc_code'] = $sc_code;
		!empty($start_time) && empty($end_time) && $where['create_time']  = array('egt', $start_time);
		!empty($end_time) && empty($start_time) && $where['create_time']  = array('elt', $end_time);
		!empty($start_time) && !empty($end_time) && $where['create_time'] = array('between', array($start_time, $end_time));

		!empty($pay_start_time) && empty($pay_end_time) && $where['pay_time']  = array('egt', $pay_start_time);
		!empty($pay_end_time) && empty($pay_start_time) && $where['pay_time']  = array('elt', $pay_end_time);
		!empty($pay_start_time) && !empty($pay_end_time) && $where['pay_time'] = array('between', array($pay_start_time, $pay_end_time));

        //默认参数
        $default_title = array('订单编号','卖家信息','买家信息','订单下单时间','订单状态','订单应付（元）','支付类型','支付方式','付款时间','付款时长','平台月平均付款时长','发货时间','发货时长','店铺月平均发货时长');
        $default_fields = 'b2b_code,uc_code,sc_code,status_message,real_amount,pay_type,create_time,pay_method_message,pay_type_message,pay_time,ship_time';
        $default_filename   =  '订单数据统计';
        $default_sql_flag   =  'order_list';
        $default_order      =  'create_time desc';
        $default_api        =  'Com.Callback.Export.BicExport.orderList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

		// $group                = 'sc_code';
		$data['group']        =  $group;
		$data['params']       =  $params;
		$data['where']        =  $where;
		$data['fields']       =  $default_fields;
		$data['title']        =  $title;
		$data['center_flag']  =  SQL_BIC;//订单中心
		$data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
		$data['filename']     =  $filename;
		$data['order']        =  empty($order) ? $default_order : $order;
		$data['callback_api'] = $callback_api;
		$data['db_flag']      = 'bic';
       // echo '23';exit;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
//        var_dump($res);exit;
        return $this->res($res['response'],$res['status']);
	}

	/**
	 * Base.BicModule.Oc.Statistic.storesInfo
	 * 获取所有大B信息
	 * @return [type] [description]
	 */
	public function storesInfo(){
		$model=D('ScStore',$this->tablePrefix,$this->connection);
		$storesInfo = $model->select();
		if ($storesInfo === false) {
			return $this->res(null, 10002);
		}

		$storesInfo = empty($storesInfo)? array() : changeArrayIndex($storesInfo, 'sc_code');
		return $this->res($storesInfo);
	}

	/**
	 * Base.BicModule.Oc.Statistic.customersInfo
	 * 获取所有小b信息
	 * @return [type] [description]
	 */
	public function customersInfo(){
		$model=D('UcCustomer',$this->tablePrefix,$this->connection);
		$customersInfo = $model->select();
		if ($customersInfo === false) {
			return $this->res(null, 10002);
		}

		$customersInfo = empty($customersInfo)? array() : changeArrayIndex($customersInfo, 'uc_code');
		return $this->res($customersInfo);
	}

	/**
	 * 获取月平均时长 OcOrderMonthAverage
	 * Base.BicModule.Oc.Statistic.averageMonthTime
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function averageMonthTime($params){
		$this->rule = array(
			array('year', 'require', PARAMS_ERROR, MUST_CHECK), //小B用户编码
			array('month', 'require', PARAMS_ERROR, MUST_CHECK), //小B用户编码
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
		    return $this->res($this->getErrorField(), $this->getCheckError());
		}
		$model = D('OcOrderMonthAverage',$this->tablePrefix,$this->connection);
		$averageMonthTime = $model->where($params)->find();
		if ($averageMonthTime === false) {
			return $this->res(null, 10002);
		}
		$averageMonthTime = empty($averageMonthTime)? array() : $averageMonthTime;
		return $this->res($averageMonthTime);
	}
}