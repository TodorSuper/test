<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | 支付类型设置
 */
namespace Base\StoreModule\Basic;
use System\Base;
class Paytype extends Base {
    public static $model;
    public function __construct() {
        self::$model = D('ScPayType');
        parent::__construct();
    }
    public function update($params) {
    	$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 店铺编码
     		array('status', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //货到付款
        $bool = $this->_checkData(array('sc_code'=>$params['sc_code'],'pay_type'=>PAY_TYPE_COD));
        //到期
        $expire = $this->_checkData(array('sc_code'=>$params['sc_code'],'pay_type'=>PAY_TYPE_TERM));
        //更新货到付款数据
        $type = in_array(PAY_TYPE_COD,$params['status'])?'ENABLE':'DISABLE';
        $result = $this->_updataData($bool['status'],array('key'=>$bool['key'],'sc_code'=>$params['sc_code']),$type);
        if ( $result === false ){
            return $this->res(null,9002);
        }
        //更新到期付款
       	if ( in_array(PAY_TYPE_TERM_MONTH,$params['status']) ) {
       		$data['ext1'] = 'YES';
       		$data['ext2'] = $params['yname'];
       		$data['ext3'] = PAY_TYPE_TERM_MONTH;
       		$data['status'] = 'ENABLE';
       	} else {
       		$data['ext1'] = 'NO';
       	}
       	if ( in_array(PAY_TYPE_TERM_PERIOD,$params['status']) ) {
       		$data['ext4'] = 'YES';
       		$data['ext5'] = $params['qname'];
       		$data['ext6'] = $params['qday'];
       		$data['ext7'] = PAY_TYPE_TERM_PERIOD;
       		$data['status'] = 'ENABLE';
       	} else {
       		$data['ext4'] = 'NO';
       	}
       	if ( !in_array(PAY_TYPE_TERM_MONTH,$params['status']) && !in_array(PAY_TYPE_TERM_PERIOD,$params['status']) ) {
       		$data['status'] = 'DISABLE';
       	}
       	$data['pay_type'] = PAY_TYPE_TERM;	
       	
       	$data['sc_code'] = $params['sc_code'];
       	$data['create_time'] = NOW_TIME;
       	$data['update_time'] = NOW_TIME;
       	$row = $this->_expireData($expire['status'],array('key'=>$expire['key']),$data);
       	if ( $result === false ){
            return $this->res(null,9003);
        }
        return $this->res(true);
    }
    /*
    * 检测数据是否存在
    */
    private function _checkData($params) {
    	$res = self::$model->where($params)->find();
    	if ($res) {
    		return array('status'=>true,'key'=>$res['id']);
    	} else {
    		return array('status'=>false);
    	}
    }
    /*
    * 更新货到付款数据
    */
    private function _updataData($status,$param,$type) {
    	
        if ($status) {
            $data = array(
                'status' => $type,
                'update_time' => NOW_TIME
            );
            return self::$model->where(array('id'=>$param['key']))->save($data);
        } else {
            $data = array(
                'sc_code' => $param['sc_code'],
                'pay_type' => PAY_TYPE_COD,
                'update_time' => NOW_TIME,
                'create_time' => NOW_TIME,
                'status' => $type,
            );
            return self::$model->add($data);
        }
    }
    private function _expireData($status,$param,$data) {
    	foreach ($data as $v) {
    		if (empty($v)) {
    			return false;
    		}
    	}
        if ($status) {
        	unset($data['create_time']);
        	return self::$model->where(array('id'=>$param['key']))->save($data);
        } else {
        	return self::$model->add($data);
        }
    }
    /*
    * Base.StoreModule.Basic.Paytype.lists
    */
    public function lists($param) {
    	$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 店铺编码
        );
        if (!$this->checkInput($this->_rule, $param)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
    	$data = self::$model->where(array('sc_code'=>$param['sc_code']))->select();
    	$list = array();
    	//拼接前台显示数据
    	foreach ($data as $k => $v) {
    		if($v['pay_type'] == 'COD' && $v['status'] == 'ENABLE') {
    			$list['cod'] = 1;
    		}
    		if ($v['ext1'] == 'YES') {
    			$list['yue'] = array(
    				'name' => $v['ext2']
    			);
    		}
    		if ($v['ext4'] == 'YES') {
    			$list['day'] = array(
    				'name' => $v['ext5'],
    				'day' => $v['ext6']
    			);
    		}
    	}
    	
    	return $this->res($list);
    }
}    
