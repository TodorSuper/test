<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订购会详情与列表 (BASE)
 */

namespace Base\SpcModule\Commodity;

use       System\Base;

class CommodityInfo extends Base {

	private $_rule	=	null;

    public function __construct() {
        parent::__construct();
	}

	/**
	 * 订购会详情
	 * Base.SpcModule.Commodity.CommodityInfo.get
	 * @access public 
	 * @author Todor
	 */

	public function get($params){

		$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),                                       # 商铺编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),                                      # 促销编码
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),                                         # 当前页数
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),                                  # 分页数
            array('order', array('advance_money', 'spent_money', 'balance'), PARAMS_ERROR, ISSET_CHECK,'in'), # 排序规则
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 该订购会信息
        $where['spc_code'] = $params['spc_code'];
        $where['sc_code'] = $params['sc_code'];
        $commodityInfo = D('SpcCommodity')->where($where)->find();

        if(empty($commodityInfo)){
        	return $this->res(NULL,7062);
        }

        // 该订货会客户信息
        $map['sc.spc_code'] = $params['spc_code'];
        $map['sc.sc_code']  = $params['sc_code'];
        $order  = empty($params['order']) ? 'sc.advance_money desc' : 'sc.'.$params['order'].' desc';
        $fields = 'sc.uc_code,sc.spc_code,sc.sc_code,uc.name,uc.mobile,ss.name as salesman,sc.advance_money,sc.spent_money,sc.balance,sc.last_amount';
        $params['page']        = $params['page'];
        $params['page_number'] = $params['page_number'];
        $params['fields']      = $fields;
        $params['where']       = $map;
        $params['order']       = $order;
        $params['center_flag'] = SQL_SPC;
        $params['sql_flag']    = 'customer_lists';
        
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $customers = $this->invoke($apiPath,$params);
        
        if ($customers['status'] != 0) {
            return $this->res(NULL,7063);
        }
        
        // 数组重组
        $res['commodityInfo'] = $commodityInfo;
        $res['customers']     = $customers['response'];

        return $this->res($res);
	}


    /**
     * 订货会列表
     * Base.SpcModule.Commodity.CommodityInfo.lists
     * @access public 
     * @author Todor
     */

    public function lists($params){

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),                                      # 商铺编码
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),                                         # 当前页数
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),                                  # 分页数
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['sc_code'] = $params['sc_code'];
        $order            = "id desc";
        $page             = $params['page'];
        $fields = 'spc_code,commodity_title,min_advance,sc_code,start_time,end_time,status,advance_money,spent_money,balance,last_amount';
        $params['page']        = $page;
        $params['page_number'] = $page_number;
        $params['fields']      = $fields;
        $params['where']       = $where;
        $params['order']       = $order;
        $params['center_flag'] = SQL_SPC;
        $params['sql_flag']    = 'commodity_lists';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $res = $this->invoke($apiPath,$params);
        if ($res['status'] != 0) {
            return $this->res(NULL,7065);
        }
        return $this->res($res['response']);

    }


}