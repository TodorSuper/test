<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@yunputong.com >
 * +---------------------------------------------------------------------
 * | 商品分类相关
 */

namespace Base\ItemModule\Category;

use System\Base;

class CategoryEnd extends Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取后台的分类信息
     * Base.ItemModule.Category.CategoryEnd.getCategoryEndList
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCategoryEndList($params){
    	
		$order           = 'level ASC';
		$categoryEndList = D('CategoryEnd')->where($where)->order($order)->select();
        $clist = array();
		foreach ($categoryEndList as $key => $value) {
            $arr['id']   = $value['id'];
            $arr['name'] = $value['name'];
            $clist[$value['level']][$value['pid']][$value['id']] = $arr;
        }
    	return $this->res($clist);
    }
    /**
     * Base.ItemModule.Category.CategoryEnd.getCategoryEndByLevel
     * @return [type] [description]
     */
    public function getCategoryEndByLevel($params){
        $this->_rule = array(
            array('level', 'require', PARAMS_ERROR, ISSET_CHECK),  # 分类栏目
            array('pid', 'require', PARAMS_ERROR, ISSET_CHECK),
        ); 
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if ($params) {
            $where = array();
            foreach ($params as $key => $value) {
                $where["$key"] = array('in', $value);
            }
        }
        $categoryEndList = D('CategoryEnd')->where($where)->select();
        return $categoryEndList;
    }



}