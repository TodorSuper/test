<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订购会客户信息 (BASE)
 */

namespace Base\SpcModule\Commodity;

use       System\Base;

class Customer extends Base {

	private $_rule	=	null;

    public function __construct() {
        parent::__construct();
	}


	/**
	 * 订购会充值增加
	 * Base.SpcModule.Commodity.Customer.add
	 * @access public 
	 */

	public function add($params){

		$this->startOutsideTrans();
		$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),        # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),        # 客户编码
            array('advance_money', 'require', PARAMS_ERROR, MUST_CHECK),  # 充值金额
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),          # 充值类型
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 获取商家订货会的spc_code编码
        $map['sc_code'] = $params['sc_code'];
        $map['status']  = SPC_STATUS_PUBLISH;
        $spc_code = D('SpcCommodity')->where($map)->getField('spc_code');
        if(empty($spc_code)){                             
        	$map['status'] = SPC_STATUS_END;
        	$spc_code = D('SpcCommodity')->where($map)->order('end_time desc')->limit(1)->getField('spc_code');

        	if(is_null($spc_code)){
        		return $this->res(true);
        	}
        }

        // 订购会客户添加或更新
        $map['uc_code'] = $params['uc_code'];
        $map['status']  = 'ENABLE';
        $map['spc_code']= $spc_code;   
       	$customer = D('SpcCustomer')->where($map)->find();
        if(empty($customer)){		  				  # 添加
        	$data = array(
				'sc_code'       =>$params['sc_code'],
				'spc_code'      =>$spc_code,
				'uc_code'       =>$params['uc_code'],
				'advance_money' =>$params['advance_money'],
				'balance'       =>$params['advance_money'],
				'create_time'   =>NOW_TIME,
				'update_time'   =>NOW_TIME,
        		);
        	$add = D('SpcCustomer')->add($data);

        	if($add <= 0 || $add === FALSE){
        		return $this->res(NULL,7059);
        	}

        	// 订货会总款更新
			$where['sc_code']      = $params['sc_code'];
			$where['spc_code']     = $spc_code;
			$temp['advance_money'] = array('+',$params['advance_money']);
			$temp['balance']       = array('+', $params['advance_money']);
			$temp['update_time']   = NOW_TIME;
			$commodity = D('SpcCommodity')->where($where)->save($temp);

			if($commodity <= 0 || $commodity === FALSE){
				return $this->res(NULL,7061);
			}

        	return $this->res($add);
        }else{										  # 更新
			$params['spc_code'] = $spc_code;
			$apiPath            = "Base.SpcModule.Commodity.Customer.update";
			$res                = $this->invoke($apiPath,$params);
        	if($res['status'] != 0){
        		return $this->endInvoke(NULL,$res['status']);
        	}
        	return $this->res($res['response']);
        }	

	}

	/**
	 * 订购会充值更新
	 * Base.SpcModule.Commodity.Customer.update
	 * @access public 
	 */

	public function update($params){

		$this->startOutsideTrans();
		$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),        			 # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),        			 # 客户编码
            array('advance_money', 'require', PARAMS_ERROR, MUST_CHECK),  			 # 充值金额
            array('type', array('add','minus'), PARAMS_ERROR, MUST_CHECK,'in'),      # 充值类型
            array('spc_code', 'require', PARAMS_ERROR, ISSET_CHECK),      			 # 促销编码
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

		$where['sc_code']      = $params['sc_code'];

        if($params['type'] == 'add'){									# 预收货款增添金额
        	$where['spc_code']     = $params['spc_code'];
			$data['advance_money'] = array('+',$params['advance_money']);
			$data['balance']       = array('+', $params['advance_money']);
			$data['update_time']   = NOW_TIME;

        }else if($params['type'] == 'minus'){                           # 预收货款减少金额

        	// 获取商家订货会的spc_code编码
	        $map['sc_code'] = $params['sc_code'];
	        $map['status']  = SPC_STATUS_PUBLISH;
	        $spc_code = D('SpcCommodity')->where($map)->getField('spc_code');
	        if(empty($spc_code)){                             # 如果为空则获取最近的spc_code
	        	$map['status'] = SPC_STATUS_END;
	        	$spc_code = D('SpcCommodity')->where($map)->order('end_time desc')->limit(1)->getField('spc_code');

	        	if(is_null($spc_code)){
        			return $this->res(true);
        		}
	        }

	        $where['spc_code'] = $spc_code;
	        $data['spent_money'] = array('+',$params['advance_money']);
	        $data['balance']     = array('-',$params['advance_money']);
	        $data['update_time'] = NOW_TIME;   

        }

        // 订货会总款更新
        $commodity = D('SpcCommodity')->where($where)->save($data);
		if($commodity <= 0 || $commodity === FALSE){
			return $this->res(NULL,7061);
		}

		// 订购会客户信息更新
		$where['uc_code']      = $params['uc_code'];
        $res = D('SpcCustomer')->where($where)->save($data);

		if($res <= 0 || $res === FALSE){
			return $this->res(NULL,7060);
		}
		return $this->res($res);

	}


	/**
	 * 获取对应订货会客户统计数目
	 * Base.SpcModule.Commodity.Customer.getNumber
	 * @access public
	 */

	public function getNumber($params){
		$this->_rule = array(
            array('spc_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), 
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $spc_codes = $params['spc_codes'];

        $data = array();
        foreach ($spc_codes as $k => $v) {
        	$where['spc_code'] = $v;
        	$num = D('SpcCustomer')->field("count('spc_code') as num")->where($where)->group('spc_code')->select();
        	$data[$v] = empty($num) ? 0 : current(current($num));
        }

		return $this->res($data);
        
	}


}