<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 商品分类相关
 */
        
namespace Base\ItemModule\Tag;

use System\Base;

class Tag extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
        
    /**
     * Base.ItemModule.Tag.Tag.addTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function addTag($params){
    	$this->startOutsideTrans();
    	$this->_rule = array(
    	    array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
    	    // array('tag_name', 'require', PARAMS_ERROR, MUST_CHECK), //卖家标签
    	    // array('tag_weight', 'require', PARAMS_ERROR, MUST_CHECK), //卖家标签权重
    	    );

    	if (!$this->checkInput($this->_rule, $params)) { # 自动校验
    	    return $this->res($this->getErrorField(), $this->getCheckError());
    	}

		$sc_code    = $params['sc_code'];
		// $tag_name   = $params['tag_name'];
		// $tag_weight = $params['tag_weight'];

  //   	$tagData = array(
		// 	'sc_code'     => $sc_code,
		// 	'tag_name'    => $tag_name,
		// 	'tag_weight'  => $tag_weight,
		// 	'create_time' => NOW_TIME,
		// 	'update_time' => NOW_TIME,
		// 	'status'      => 'ENABLE',
  //   	    );

    	// $tagRes = D('IcTag')->add($tagData);

		$tagData = array(
				array(
				'sc_code'     => $sc_code,
				'tag_name'    => "爆款推荐",
				'tag_weight'  => 2,
				'create_time' => NOW_TIME,
				'update_time' => NOW_TIME,
				'status'      => 'ENABLE',
					),
				array(
				'sc_code'     => $sc_code,
				'tag_name'    => "新品上市",
				'tag_weight'  => 1,
				'create_time' => NOW_TIME,
				'update_time' => NOW_TIME,
				'status'      => 'ENABLE',
					),
			);
		$tagRes = D('IcTag')->addAll($tagData);
    	if ($tagRes <= 0) {
    	    return $this->res(NULL, 4531);
    	}
    	return $this->res(true);
    }


    /**
     * Base.ItemModule.Tag.Tag.updateTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function updateTag($params){
    	// $this->startOutsideTrans();
    	$this->_rule = array(
    	    array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('id', 'require', PARAMS_ERROR, ISSET_CHECK), //标签id
    	    array('data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //需要传入二维数组[['id','tag_weight'],['id','tag_weight']]
            array('tag_name', 'require', PARAMS_ERROR, ISSET_CHECK), //标签名称
            array('item_num', 'require', PARAMS_ERROR, ISSET_CHECK), //商品数据
    	    );
    	if (!$this->checkInput($this->_rule, $params)) { # 自动校验
    	    return $this->res($this->getErrorField(), $this->getCheckError());
    	}

        $id       = $params['id'];
        $sc_code  = $params['sc_code'];
        $data     = $params['data'];
        $item_num = $params['item_num'];
        $tag_name = $params['tag_name'];

		if ($id) {
			$data = $var = array();
			$var['id'] = $id;
			if($tag_name) $var['tag_name'] = $tag_name;
			if($item_num) $var['item_num'] = $item_num;
			$data[] = $var;
		}

    	$num = count($data);
    	if ($num <= 0) {
    	    return $this->res(NULL, 4533);
    	}
    	
    	if (!empty($data) && is_array($data)) {
    	    foreach ($data as $key => $value) {
    	        $where = $tagData = array();
    	        $where['id']      = $value['id'];
    	        $where['sc_code'] = $sc_code;

    	        if ($value['tag_weight']) {
    	        	$tagData['tag_weight'] = $value['tag_weight'];
    	        }

    	        $tagData['update_time']    = NOW_TIME;
    	        if ($value['tag_name']) {
    	            $tagData['tag_name'] = $value['tag_name'];
    	        }

    	        if (isset($value['item_num'])) {
    	        	$tagData['item_num'] = $value['item_num'];
    	        }

    	        $tagRes = D('IcTag')->where($where)->save($tagData);
                // echo D()->getLastSql();
    	        if($tagRes <= 0){
    	            return $this->res(NULL, 4532);
    	        }
    	    } 
    	}
    	return $this->res(true);
    }

    /**
     * Base.ItemModule.Tag.Tag.listsTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function listsTag($params){
    	$this->_rule = array(
    	    array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('item_num_gt', array('YES','NO'),'require', PARAMS_ERROR, ISSET_CHECK,'in'), // 商品数量是否大于0
    	    );
    	if (!$this->checkInput($this->_rule, $params)) { # 自动校验
    	    return $this->res($this->getErrorField(), $this->getCheckError());
    	}

    	$sc_code = $params['sc_code'];

    	$where = array();
        $where['sc_code'] = $sc_code;
        $where['status']  = 'ENABLE';
        !empty($params['item_num_gt']) && $params['item_num_gt'] == "YES" && $where['item_num'] = array('gt','0');
        
    	$order = "tag_weight desc";

    	$tagRes = D('IcTag')->where($where)->order($order)->select();
    	if ($tagRes === false) {
    		return $this->res(NULL, 4534);
    	}
    	return $this->res($tagRes);
    }
    
    /**
     * Base.ItemModule.Tag.Tag.getTagInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getTagInfo($params){
    	$this->_rule = array(
    	    array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('id', 'require', PARAMS_ERROR, ISSET_CHECK), //标签唯一编码
    	    array('data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //标签唯一编码 数组类型的id   array(12,13,14)
    	    );
    	if (!$this->checkInput($this->_rule, $params)) { # 自动校验
    	    return $this->res($this->getErrorField(), $this->getCheckError());
    	}

        $sc_code = $params['sc_code'];
        $id      = $params['id'];
        $data    = $params['data'];
        if ($id) {
            $data = array($id);
        }

        if (empty($data)) {
            return $this->res(NULL, 4561);
        }
    	$where = array();
    	if(!empty($sc_code))$where['sc_code'] = $sc_code;
        $where['status'] = 'ENABLE';
    	if(!empty($data))$where['id'] = array('in', $data);

        $order  = "tag_weight desc";
        $tagRes = D('IcTag')->where($where)->order($order)->select();
    	if ($tagRes === false) {
    	    return $this->res(NULL, 4535);
    	}
        if (!empty($tagRes)) {
            $tagRes = changeArrayIndex($tagRes, 'id');
        }

        if ($id) {
            $tagRes = $tagRes[$id];
        }
    	return $this->res($tagRes);
    }


    /**
    * 根据sic_codes 获取全部标签
    * Base.ItemModule.Tag.Tag.getTags
    * @param $sic_codes array
    * @author Todor
    * @access public
    */

    public function getTags($params){
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'), # 商品编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), # 商品编码
            array('tag_id', 'require', PARAMS_ERROR, ISSET_CHECK), # 商品编码
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_codes = $params['sic_codes'];
        $tag_id    = $params['tag_id'];
        $sc_code   = $params['sc_code'];

        if (!empty($sic_codes)) {
            $map['iitr.sic_code'] = array('in',$params['sic_codes']);
        }
        if (!empty($sc_code)) {
            $map['iitr.sc_code'] = $sc_code;
        }
        if (!empty($tag_id)) {
            $map['iitr.tag_id'] = $tag_id;
        }

        $map['it.status']     = "ENABLE";
        $map['iitr.status']   = "ENABLE";
        $tags = D('IcTag')->alias('it')
                    ->join("{$this->tablePrefix}ic_item_tag_relation AS iitr ON it.id = iitr.tag_id")
                        ->field('it.id,it.tag_name,it.tag_img,iitr.sic_code,it.tag_weight,it.sc_code')
                        ->where($map)
                        ->order('it.tag_weight desc')
                        ->select();
        if($tags === false){
            return $this->res(NULL,4535);
        }

        return $this->res($tags);
    }
}