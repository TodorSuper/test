<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |促销规则
 */

namespace Base\SpcModule\Gift;

use System\Base;

class Gift extends Base{

    public $ruleMax = 3;
    
	public function __construct(){
		parent::__construct();
	}

	/**
	 * ZHANGYUPENG
	 * Base.SpcModule.Gift.Gift.add
	 * @param [type] $params [description]
	 */
	public function add($params){
        
		$this->startOutsideTrans();  
		$this->_rule = array(
				array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('sale_sic_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('gift_sic_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('rule', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
			);
        
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
		    return $this->res($this->getErrorField(), $this->getCheckError());
		}

		//判断满赠规则
        $rmark = $this->rule($params['rule']);

        if (!is_array($rmark)) {
            return $this->res('', $rmark);
        }else{
            $params['rule'] = json_encode($rmark);
        }

		//添加满赠数据
        $giftData = array(
				'spc_code'      => $params['spc_code'],
				'sale_sic_code' => $params['sale_sic_code'],
				'gift_sic_code' => $params['gift_sic_code'],
				'rule'          => $params['rule'],
				'create_time'   => NOW_TIME,
				'update_time'   => NOW_TIME,
        		);
        
        $insert = D('SpcGift')->add($giftData);
        if(!$insert) {
            return $this->res('', 7004);
        }

		return $this->res(true);
	}

    /**
     * 判断促销规则
     * @param  [type] $rule [description]
     * @return [type]       [description]
     */
    public function rule($ruleArr){
        //转化类型
        if (is_string($ruleArr)) {
            $ruleArr = json_decode($ruleArr);
        }

        if (!is_array($ruleArr)) {
            return 7005;
        }

        //判断促销条数
        $ruleNum = count($ruleArr);
        if ($ruleNum > $this->ruleMax || $ruleNum < 1) {
            return 7005;
        }

        //循环判断规则
        foreach($ruleArr as $k=>&$value){
             //过滤全部为空的           
            if(empty($value[0]) && empty($value[1])){
                unset($ruleArr[$k]);
            }else{
                //有一个为空的或者为零，
                if (empty($value[0]) || empty($value[1])) {
                    return 7005;
                }
                //防止输入字符串，转为整形
                $value[0] = intval($value[0]);
                $value[1] = intval($value[1]);

                if ($value[0] <= 0 || $value[1] <= 0) {
                    return 7005;
                }
            }

        }

        //过滤后防止数组再次为空
        $ruleNum = count($ruleArr);
        if ($ruleNum < 1) {
            return 7005;
        }

        //规则数组排序
        $tempsort = array();
        foreach ( $ruleArr as $k => $v ) {
            if (empty($v)) {
                return 7005;
            }

            $tempsort[] = $v[0];
        }
        array_multisort($ruleArr,SORT_ASC,$tempsort);

        //规则赠送规则检测(越增越多)
        if ($ruleNum >= 1) {
            $check = null;
            foreach ($ruleArr as $rule) {
                if ($rule[1] > $rule[0]) {
                    return 7024;
                }

                if (!empty($check)) {
                    if (intval($rule[1])/intval($rule[0]) <= intval($check[1])/intval($check[0])) {
                        return 7024;
                    }
                    $check = $rule;
                }else{
                    $check = $rule;
                }
            }
        }
        return $ruleArr;

    }

    /**
     * 
     * Base.SpcModule.Gift.Gift.update
     * @param [type] $params [description]
     */
    public function update ($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('gift_sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('rule', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //判断满赠规则
        $rmark = $this->rule($params['rule']);

        if (!is_array($rmark)) {
            return $this->res('', $rmark);
        }else{
            $params['rule'] = json_encode($rmark);
        }
        //添加满赠数据
        $field = array('gift_sic_code', 'rule');
        $data  = $this->create_save_data($field, $params);

        if(empty($data)){
            return $this->res('', 2001);
        }

        $data['rule']        = $data['rule'];
        $data['update_time'] = NOW_TIME;
        $where['spc_code']   = $params['spc_code'];

        $update = D('SpcGift')->where($where)->save($data);
        
        if(!$update) {
            return $this->res('', 7016);
        }

        return $this->res(true);
    }

}





















