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

namespace Base\BicModule\Ic;
use System\Base;

class Item extends Base
{
	private $_rule = null; # 验证规则列表

    public function __construct(){
        parent::__construct();
		$this->tablePrefix = C('DB_PREFIX');
		$this->connection  = C('DB_BIC');
    }

    /**
     * 
     * Base.BicModule.Ic.Item.itemStatistic
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function itemStatistic($params){
        $model = D('ScStore',$this->tablePrefix,$this->connection);
        $this->rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), 
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK), 
        );
        if (!$this->checkInput($this->_rule, $params)) { # ×Ô¶¯Ð£Ñé
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $page        = $params['page'];
        $page_number = $params['page_number'];
        $sc_code     = $params['sc_code'];

        $data             = array();
        $data['fields']   = '*';
        if(!empty($sc_code)) $data['where'] = array('sc_code' => $sc_code);;
        $data['center_flag'] = SQL_BIC;       
        $data['sql_flag']    = 'item_list';  
        $data['page']        = $page;
        $data['order']       = 'id asc';
        $data['page_number'] = $page_number;
        $data['db_flag']     = 'bic_db';
        $data['aggre']       = array(array('sum','item_num','allNum'), array('sum','on_num','onNum'), array('sum','off_num','offNum'));

        $apiPath     = "Com.Common.CommonView.Lists.Lists";
        $list_res    = $this->invoke($apiPath, $data);

        return $this->res($list_res['response']);
    }



    /**
     * 
     * Base.BicModule.Ic.Item.export
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function export($params){
      	$this->rule = array(
      	    array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), 
      	    array('page', 'require', PARAMS_ERROR, ISSET_CHECK), 
      	);
      	if (!$this->checkInput($this->_rule, $params)) { # ×Ô¶¯Ð£Ñé
      	    return $this->res($this->getErrorField(), $this->getCheckError());
      	}

        $page        = $params['page'];
        $page_number = $params['page_number'];
        $sc_code     = $params['sc_code'];

        //默认参数
        $default_title    = array('序号','卖家店铺','商品数量','上架数','下架数');
        $default_fields   = '*';
        $default_filename =  '商家统计列表';
        $default_sql_flag =  'item_list';
        $default_order    =  'id asc';
        $default_api      =  'Com.Callback.Export.BicExport.itemList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        if ($sc_code) {
          $where['sc_code'] = $sc_code;
        }
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

        $apiPath =  "Com.Common.CommonView.Export.export";
        $res     = $this->invoke($apiPath, $data);

        return $this->res($res['response'],$res['status']);
    }


}
  	