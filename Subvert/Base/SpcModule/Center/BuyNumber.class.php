<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 促销购买量
 */

namespace Base\SpcModule\Center;

use System\Base;

class BuyNumber extends Base {

    public function __construct() {
        parent::__construct();
    }
   
    /**
     * Base.SpcModule.Center.BuyNumber.add
     * 增加购买数量
     */
    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK), //促销中心编码
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商品编码
            array('number', 'require', PARAMS_ERROR, MUST_CHECK),   //购买数量
            array('number', '0', PARAMS_ERROR, MUST_CHECK,'gt'),   //购买数量
            
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $uc_code = $params['uc_code'];
        $spc_code = $params['spc_code'];
        $sic_code = $params['sic_code'];
        $number = $params['number'];
        
        
        //查询该记录是否存在，存在则更新  不存在则添加
        $buyNumberInfo = D('SpcBuyNumber')->where(array('uc_code'=>$uc_code,'spc_code'=>$spc_code))->find();
        if(empty($buyNumberInfo)){
            //不存在   添加
            $data = array(
                'uc_code' => $uc_code,
                'spc_code'=>$spc_code,
                'sic_code'=>$sic_code,
                'number' => $number,
                'create_time'=>NOW_TIME,
                'update_time'=>NOW_TIME,
                
            );
            $res = D('SpcBuyNumber')->add($data);
        }else {
            //存在  更新
            $data = array(
                'number' => array('+',$number),
                'update_time'=>NOW_TIME,
            );
            $res = D('SpcBuyNumber')->where(array('uc_code'=>$uc_code,'spc_code'=>$spc_code))->save($data);
        }
        if($res === FALSE || $res <= 0){
            return $this->res(NULL,7033);
        }
        return $this->res($res);
    }
    /**
     *
     */
    /**
     * Base.SpcModule.Center.BuyNumber.sub
     * 减少购买数量
     * @param type $params
     */
    public function sub($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK), //促销中心编码
            array('number', 'require', PARAMS_ERROR, MUST_CHECK),   //购买数量
            array('number', '0', PARAMS_ERROR, MUST_CHECK,'gt'),   //购买数量
            
        );
        
        $data = array(
            'number' => array('-',$params['number']),
            'update_time' => NOW_TIME,
        );
        
        $where = array(
            'uc_code' => $params['uc_code'],
            'spc_code' => $params['spc_code'],
        );
        
        $res = D('SpcBuyNumber')->where($where)->save($data);
        if($res === FALSE || $res <= 0){
            return $this->res(NULL,7034);
        }
        
        return $this->res($res);
        
    }
    
    /**
     * Base.SpcModule.Center.BuyNumber.get
     * 获取某个客户促销信息购买数量
     * @param type $params
     */
    public function get($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK), //促销中心编码
        );
        $where = array(
            'uc_code' => $params['uc_code'],
            'spc_code' => $params['spc_code'],
        );
        
        $res = D('SpcBuyNumber')->where($where)->find();
        return $this->res($res);
    }
    
    /**
     * Base.SpcModule.Center.BuyNumber.lists
     * 批量获取客户  促销信息购买数量
     * @param type $params
     */
    public function lists($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('spc_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), //促销中心编码
        );

        $uc_code = $params['uc_code'];
        $spc_codes = $params['spc_codes'];

        $where = array(
            'uc_code' => $uc_code,
            'spc_codes' => array('in',$spc_codes),
        );

        $res = D('SpcBuyNumber')->where($where)->select();
        return $this->res($res);
}
}
