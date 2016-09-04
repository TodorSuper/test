<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | 账期模块
 */

namespace Base\OrderModule\B2b;
use System\Base;
class Account extends Base {
    public function __construct() {
        parent::__construct();
    }
    /*
    * 账期列表
    * Base.OrderModule.B2b.Account.accountLists
    */
    public function accountLists($params) {

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('uc_code','require',PARAMS_ERROR,ISSET_CHECK),     //用户编码
            array('search_name', 'require', PARAMS_ERROR, ISSET_CHECK), //搜索词
            array('pageSize','require',PARAMS_ERROR,ISSET_CHECK), //页数
            array('pageNumber','require',PARAMS_ERROR,ISSET_CHECK), //当前页码
            array('aggre','require',PARAMS_ERROR,ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if ( !isset($params['uc_code']) && !isset($params['sc_code'])) {
            return $this->res(null,5);
        }
        !empty($params['uc_code']) && $where['uc_code'] = $params['uc_code'];
        !empty($params['sc_code']) && $where['sc_code'] = $params['sc_code'];
        if (empty($params['sc_code']) && empty($params['uc_code'])) {
            return $this->res(null,5);
        }
        if (isset($params['search_name']) && $params['search_name'] != '') {
            //根据用户名称或者店铺名称获取订单编号

            $where['client_name'] = array('like',"%{$params['search_name']}%");
            $twoData = D('OcB2bOrder')->field('b2b_code')->where($where)->select();
        
            unset($where['client_name']);
            $client_name_b2b_code = array_column($twoData,'b2b_code');
            unset($where['client_name']);
            $where['commercial_name'] = array('like',"%{$params['search_name']}%");
            $oneData = D('OcB2bOrderExtend')->field('op_code')->where($where)->select();
            unset($where['commercial_name']);
            if (empty($oneData)) {
                $commercial_name_b2b_code = '';
            } else {
                $op_code['op_code'] = array('in',array_column($oneData,'op_code'));
                $commercial_name_b2b_code = D('OcB2bOrder')->field('b2b_code')->where($op_code)->select();
                $commercial_name_b2b_code = array_column($commercial_name_b2b_code,'b2b_code');   
            }
            
            if ( !is_array($commercial_name_b2b_code) ) $commercial_name_b2b_code = array();
            if ( !is_array($client_name_b2b_code)) $client_name_b2b_code = array();
            $code = array_unique(array_merge($client_name_b2b_code,$commercial_name_b2b_code));
            if (empty($code)) {
                return $this->res(null,0);
            }
            $where['b2b_code'] = array('in',$code);
        }
        $where['pay_status'] = OC_ORDER_PAY_STATUS_UNPAY;
        $where['pay_type'] = PAY_TYPE_TERM;
        if ( isset($params['sc_code']) ) {
           
            $fields = 'od.*,ex.*,sum(od.real_amount) as money ';
        } else {
            $fields = 'od.*,ex.*';
        }
        if ( isset($params['pageSize']) && isset($params['pageNumber']) ) {
            $params['page'] = $params['pageNumber'];
            $params['page_number'] = $params['pageSize'];
        }

        $order = 'od.id '.CREATE_TIME_DESC;
        $params['fields'] = $fields;
        $params['order'] = $order;
        $params['where'] = $where;
        $params['center_flag'] = SQL_OC;
        $params['sql_flag'] = 'account_list';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        return $this->res($list_res['response']);
    }
}