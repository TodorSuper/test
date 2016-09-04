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

namespace Base\BicModule\Tc;
use System\Base;

class AdvanceStatistic extends Base
{
	private $_rule = null; # 验证规则列表

    public function __construct(){
        parent::__construct();
        $this->tablePrefix=C('DB_PREFIX');
        $this->connection=C('DB_BIC');
    }

    /**
     * Base.BicModule.Tc.AdvanceStatistic.getAdvanceList
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getAdvanceList($params){
        $this->_rule = array(
            array('merchant_id','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码ume.invite_code,
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $mername     = $params['mername'];
        $sc_code     = $params['sc_code'];
        $uc_code     = $params['uc_code'];
        $page        = $params['page'];
        $page_number = $params['page_number'];

        if(!empty($uc_code)) $where['uc.uc_code']  = $uc_code;
        if(!empty($mername)) $where['um.salesman'] = $mername;
        if(!empty($sc_code)) $where['ss.sc_code']  = $sc_code;

        $fields = "tma.id,tma.create_time,tma.code,tma.tc_code,tma.free_balance,tma.total_balance,tma.recharge_times,tma.recharge_times,ss.linkman,ss.name,um.salesman,uc.uc_code,uc.name as uc_name,uc.sc_code";
        $data['fields'] = empty($fields) ? '*' : $fields;
        if(!empty($where)) $data['where'] = $where;
        $data['center_flag'] = SQL_BIC;       
        $data['sql_flag']    = 'advance_list';  
        $data['page']        = $page;
        $data['order']       = 'tma.id desc';
        $data['page_number'] = $page_number;
        $data['db_flag']     = 'bic_db';
        // var_dump($data);die();
        $apiPath     = "Com.Common.CommonView.Lists.Lists";
        $list_res    = $this->invoke($apiPath, $data);

        return $this->res($list_res['response']);

    }

    /**
     * Base.BicModule.Tc.AdvanceStatistic.export
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function export($params){
        $this->_rule = array(
            array('merchant_id','require',PARAMS_ERROR,ISSET_CHECK),       //下单结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //大B商家编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $mername     = $params['mername'];
        $sc_code     = $params['sc_code'];
        $uc_code     = $params['uc_code'];
        $page        = $params['page'];
        $page_number = $params['page_number'];

        if(!empty($uc_code)) $where['uc.uc_code']  = $uc_code;
        if(!empty($mername)) $where['um.salesman'] = $mername;
        if(!empty($sc_code)) $where['ss.sc_code']  = $sc_code;

        //默认参数
        $default_title = array('序号','买家信息','支付账户生成','充值次数','充值总额（元）','已冲抵金额','剩余预收','买家邀请人','所属卖家','双磁对接人');
        $default_fields = 'tma.id,tma.create_time,tma.code,tma.tc_code,tma.free_balance,tma.total_balance,tma.recharge_times,tma.recharge_times,ss.linkman,ss.name,um.salesman,uc.uc_code,uc.name as uc_name,uc.sc_code';
        $default_filename   =  '预付款订货会数据统计';
        $default_sql_flag   =  'advance_list';
        $default_order      =  'tma.id desc';
        $default_api        =  'Com.Callback.Export.BicExport.advanceList';

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
     * Base.BicModule.Tc.AdvanceStatistic.ucMemberInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function ucMemberInfo($params){
        $model=D('UcMember',$this->tablePrefix,$this->connection);
        $this->_rule = array(
          array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
          return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        if ($uc_code) {
        $where = array();
        $where['uc_code'] = $uc_code;
        $model = $model->where($where);
        }

        $ucMemberInfo = $model->select();

        if ($ucMemberInfo === false) {
        return $this->res(null, 10002);
        }

        $ucMemberInfo = empty($ucMemberInfo)? array() : changeArrayIndex($ucMemberInfo, 'uc_code');
        return $this->res($ucMemberInfo);
    }

      /**
       * Base.BicModule.Tc.AdvanceStatistic.ucMerchantInfo
       * @param  [type] $params [description]
       * @return [type]         [description]
       */
      public function ucMerchantInfo($params){
      	$model=D('UcMerchant',$this->tablePrefix,$this->connection);
      	$this->_rule = array(
    		    array('id', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
    		);
    		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
    		    return $this->res($this->getErrorField(), $this->getCheckError());
    		}
    		$id = $params['id'];
    		if ($id) {
    			$where = array();
    			$where['id'] = $id;
    			$model = $model->where($where);
    		}

    		$ucMerchantInfo = $model->select();
    		if ($ucMerchantInfo === false) {
    			return $this->res(null, 10002);
    		}

    		$ucMerchantInfo = empty($ucMerchantInfo)? array() : changeArrayIndex($ucMerchantInfo, 'id');
    		return $this->res($ucMerchantInfo);
	  }

    /**
     * Base.BicModule.Tc.AdvanceStatistic.scSalesmanInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function scSalesmanInfo($params){
      $model=D('ScSalesman',$this->tablePrefix,$this->connection);
      $this->_rule = array(
          array('invite_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
      );
      if (!$this->checkInput($this->_rule, $params)) { # 自动校验
          return $this->res($this->getErrorField(), $this->getCheckError());
      }
      $invite_code = $params['invite_code'];
      if ($invite_code) {
        $where = array();
        $where['invite_code'] = $invite_code;
        $model = $model->where($where);
      }

      $scSalesmanInfo = $model->select();
      if ($scSalesmanInfo === false) {
        return $this->res(null, 10002);
      }

      $scSalesmanInfo = empty($scSalesmanInfo)? array() : changeArrayIndex($scSalesmanInfo, 'invite_code');
      return $this->res($scSalesmanInfo);
  }
}
  	