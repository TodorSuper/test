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

namespace Base\SpcModule\Ladder;

use System\Base;

class Ladder extends Base{

    public $ruleMax = 3;
    public $buyMax = 999;
    
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Base.SpcModule.Ladder.Ladder.add
	 * @param [type] $params [description]
	 */
	public function add($params){
		$this->startOutsideTrans();  
		$this->_rule = array(
				array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('itemPrice', 'require', PARAMS_ERROR, MUST_CHECK),
				array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK),
				array('rule', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
			);
		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
		    return $this->res($this->getErrorField(), $this->getCheckError());
		}
		//验证规则
		$rmark = $this->checkRule($params['rule'], $params['itemPrice']);
		if (!is_array($rmark)) {
		    return $this->res('', $rmark);
		}else{
		    $params['rule'] = json_encode($rmark);
		}

		//添加阶梯价数据
        $ladderData = array(
				'spc_code'    => $params['spc_code'],
				'sic_code'    => $params['sic_code'],
				'rule'        => $params['rule'],
				'create_time' => NOW_TIME,
				'update_time' => NOW_TIME,
        		);
        $insert = D('SpcLadder')->add($ladderData);
        if(!$insert) {
            return $this->res('', 7002);
        }

		return $this->res(true);
	}

	public function checkRule($rule, $itemPrice){
		if (!is_array($rule)) {
			return 7005;
		}
		//判断促销条数
		$ruleNum = count($rule);
		if ($ruleNum > $this->ruleMax || $ruleNum < 1) {
		    return 7005;
		}

		$code1 = null;
		$code2 = null;
		$price = null;
		foreach ($rule as $key => $value) {
			if (empty($value[2]) && $value[0] && empty($value[1])) {
				unset($rule[$key]);
				continue;
			}
			if (empty($value[0]) && empty($value[1]) && empty($value[2])) {
				unset($rule[$key]);
				continue;
			}
			if (!empty($value[0]) || !empty($value[1])) {
				if (intval($value[0]) > $this->buyMax) {
					return 7054;
				}
				if (intval($value[1]) > $this->buyMax) {
					return 7054;
				}
			}
			if($code1){
				if ($code2) {
					if (empty($value[0])) {
						return 7046;
					}
					if ($value[0] > $code2) {
						//规则号1判断
						if ($value[0] != $code2+1) {
							// echo "促销阶梯价有问题(20-21)";
							return 7047;
						}
						
					}else{
						// echo '促销一号位起始不能小于促销二号位的起始';
						return 7048;
					}
				}
				
			}else{
				if (empty($value[0])) {
					   // echo '规则起始处，不能为空';
					return 7052;
				}else{
					$code1 = $value[0];
				}
			}
			
			if (!empty($value[1])) {
				if (empty($value[2])) {
					return 7053;
				}
				//规则号2判断
				if($value[2] < $itemPrice){
					if ($price) {
						if ($value[2] < $price) {
							$price = $value[2];
						}else{
							// echo '促销价格应该小于上条促销价格';
							return 7049;
						}
					}else{
						$price = $value[2];
					}
					
				}else{
					// echo '促销价格不应该大于商品原价';
					return 7050;
				}

			}else{
				$code2 = $value[1];
				if ($value[0] && empty($value[2])) {
					return 7053;
				}

				if ($value[2]) {
					if (empty($value[0]) && empty($value[1])) {
						return 7046;
					}
				}

				if ($value[2] < $itemPrice) {
					if (!empty($price)) {
						if ($value[2] < $price) {
							$price = $value[2];
						}else{
							// echo '促销价格出现问题';
							return 7049;
						}
					}
					
				}else{
					// echo '促销价格不应该大于商品原价';
					return 7051;
				}
				
				
				
			}
		}

		return $rule;
	}

	/**
	 * Base.SpcModule.Ladder.Ladder.update
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function update($params){
		$this->startOutsideTrans();
		$this->_rule = array(
			array('itemPrice', 'require', PARAMS_ERROR, MUST_CHECK),
            array('sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('rule', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
        );

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
		    return $this->res($this->getErrorField(), $this->getCheckError());
		}

		//验证规则
		$rmark = $this->checkRule($params['rule'], $params['itemPrice']);

		if (!is_array($rmark)) {
		    return $this->res('', $rmark);
		}else{
		    $params['rule'] = json_encode($rmark);
		}

		//添加阶梯价数据
        $ladderData = array(
				'sic_code'    => $params['sic_code'],
				'rule'        => $params['rule'],
				'update_time' => NOW_TIME,
        		);
        
        $where['spc_code'] = $params['spc_code'];
		$update = D('SpcLadder')->where($where)->save($ladderData);
		if(!$update) {
		    return $this->res('', 7007);
		}

		return $this->res(true);

	}


}