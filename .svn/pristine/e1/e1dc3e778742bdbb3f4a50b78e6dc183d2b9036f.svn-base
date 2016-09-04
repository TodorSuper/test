<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 满赠信息
 */

namespace Base\SpcModule\Gift;

use System\Base;

class GiftInfo extends Base {

    /**
     * Base.SpcModule.Gift.GiftInfo.lists
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
        $res=D('SpcGift')->where($spc)->select();
        if($res===false){
            return $this->res('',7012);
        }else{
            return $this->res($res);
        }
    }
    /**
     * Base.SpcModule.Gift.GiftInfo.get
     * @param type $params
     * @return type
     * Author: wangliangliang <wangliangliang@liangrenwang.com >
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

        $com_info = D('SpcGift')->where($where)->find();
        if (empty($com_info)) {

            return $this->res(NULL, 7022);
        }
        return $this->res($com_info);
    }
    
    /**
     * 计算赠品的件数
     * Base.SpcModule.Gift.GiftInfo.calculate
     * @param type $params
     * @return type
     * Author: zhoulianlei <zhoulianlei@liangrenwang.com >
     */
    public function calculate($params) {
        $this->_rule = array(
            array('spc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //促销标号
            array('goods_number', 'require', PARAMS_ERROR, MUST_CHECK), //购买数量
            array('goods_number', 0, PARAMS_ERROR, MUST_CHECK, 'gt'), //购买数量
            array('rule', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK,'function'), //规则
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $spc_code = $params['spc_code'];
        $goods_number = $params['goods_number'];
        $rule = $params['rule'];
        if (!empty($spc_code)) {
            $giftInfo = D('SpcGift')->where(array('spc_code' => $spc_code))->find();
            if (empty($giftInfo)) {
                return $this->res(NULL, 7022);
            }
            $rule = json_decode($giftInfo['rule'], TRUE);
        }
        //按购买力度从大到小排序
         $rule = $this->reOrder($rule);
         //计算赠品数量  
         $total_gift_num = 0;
         foreach($rule as $k=>$v){
             if($goods_number < $v[0]){
                 continue;
             }
             $times = floor($goods_number/$v[0]);
             $total_gift_num += $times * $v[1];
             $goods_number = $goods_number%$v[0];
         }
         return $this->res($total_gift_num);
    }
    
    public function reOrder($rule){
        $newRule = array();
        $sales_gift_rule = array_column($rule, 0);
        arsort($sales_gift_rule,SORT_NUMERIC);
        foreach ($sales_gift_rule as $k => $v) {
            $newRule[] = $rule[$k];
        }
        return $newRule;
    }
    

    

}
