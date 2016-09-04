<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 满赠信息
 */

namespace Base\SpcModule\Ladder;

use System\Base;

class LadderInfo extends Base {

    /**
     * Base.SpcModule.Ladder.LadderInfo.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        $this->_rule = array(
            array('spc_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), //促销编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if($params['spc_codes']){
            $spc['spc_code'] = array('in',$params['spc_codes']);
        }
        $res=D('SpcLadder')->where($spc)->select();
        if($res===false){
            return $this->res('',7045);
        }else{
            return $this->res($res);
        }
    }

    /**
     * Base.SpcModule.Special.SpecialInfo.get
     * @param type $params
     * @return type
     * Author: yindongyang <yindongyang@liangrenwang.com >
     */
    public function get($params) {
        $this->_rule = array(
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK), //促销标号
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取促销信息从表（type）

        $spc_code = $params['spc_code']; //促销编号
        //组装where条件

        $where = array(
            'spc_code' => $spc_code,
        );

        $com_info = D('SpcLadder')->where($where)->find();
        if($com_info===false){
            return $this->res('',7022);
        }else{
            return $this->res($com_info);
        }
    }
}

