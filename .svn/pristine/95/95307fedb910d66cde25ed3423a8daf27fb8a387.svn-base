<?php
/**
* +---------------------------------------------------------------------
* | www.yunputong.com 粮人网
* +---------------------------------------------------------------------
* | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
* +---------------------------------------------------------------------
* | Author: zhoulianlei <zhoulianlei@yunputong.com >
* +---------------------------------------------------------------------
* | b2b订单列状态更新
*/

namespace Base\OrderModule\Center;

use System\Base;

class OrderPayExtend extends Base {

    /**
     * Base.OrderModule.Center.OrderPayExtend.add
     * 订单状态更新
     */
    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('oc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('ext1', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ext2', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ext3', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ext4', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ext5', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('ext6', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $oc_code = $params['oc_code'];
        $ext1 = $params['ext1'];
        $ext2 = $params['ext2'];
        $ext3 = $params['ext3'];
        $ext4 = $params['ext4'];
        $ext5 = $params['ext5'];
        $ext6 = $params['ext6'];
        
        $data = array(
            'oc_code' => $oc_code,
            'ext1'    => $ext1,
            'ext2'    => $ext2,
            'ext3'    => $ext3,
            'ext4'    => $ext4,
            'ext5'    => $ext5,
            'ext6'    => $ext6,
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status' => 'ENABLE',
        );
        
        $add_res = D('OcOrderPayExtend')->add($data);
        if($add_res <= 0 || $add_res === FALSE){
            return $this->res(NULL,6031);
        }
        return $this->res(TRUE);
    }
    
    /**
     * Base.OrderModule.Center.OrderPayExtend.getPayExtendInfo
     * 订单状态更新
     */
    public function getPayExtendInfo($params){
        $this->_rule = array(
            array('oc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $oc_code = $params['oc_code'];
        $payExtendInfo  = D('OcOrderPayExtend')->where(array('oc_code'=>$oc_code))->find();
        return $this->res($payExtendInfo);        
    }
}