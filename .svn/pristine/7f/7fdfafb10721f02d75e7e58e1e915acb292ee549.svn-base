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

namespace Base\ItemModule\Category;

use System\Base;

class Category extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加分类
     * Base.ItemModule.Category.Category.add
     * @return integer   成功时返回  自增id
     */
    public function add($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('category_name', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('category_order', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code        = $params['sc_code'];
        $category_name  = $params['category_name'];
        $category_order = $params['category_order'];

        $categoryData = array(
            'sc_code'        => $sc_code,
            'category_name'  => $category_name,
            'category_order' => $category_order,
            'create_time'    => NOW_TIME,
            'update_time'    => NOW_TIME,
            'status'         => 'ENABLE',
            );
        $categoryRes = D('IcCategory')->add($categoryData);
        if ($categoryRes <= 0) {
            return $this->res(NULL, 4536);
        }
        $categoryData = array(
            'category_id'    => $categoryRes,
            'sc_code'        => $sc_code,
            'category_name'  => $category_name,
            'category_order' => $category_order,
            'create_time'    => NOW_TIME,
            'update_time'    => NOW_TIME,
            'status'         => 'ENABLE',
            );
        return $this->res($categoryData);
    }
    

    /**
     * 删除分类
     * Base.ItemModule.Category.Category.delete
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function delete($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('id', 'require', PARAMS_ERROR, MUST_CHECK), //卖家分类唯一编码
            );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $id      = $params['id'];
        $sc_code = $params['sc_code'];

        $where            = array();
        $where['id']      = $id;
        $where['sc_code'] = $sc_code;

        $categoryData = array(
            'status' => 'DISABLE',
            );
        $categoryRes = D('IcCategory')->where($where)->save($categoryData);
        if ($categoryRes <= 0) {
            return $this->res(NULL, 4537);
        }
        return $this->res(true);
    }

    /**
     * 更新分类
     * Base.ItemModule.Category.Category.update
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function update($params){
        
        // $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('id', 'require', PARAMS_ERROR, ISSET_CHECK), //卖家种类唯一编码
            array('data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //需要传入二维数组[['id','category_order'],['id','category_order']]
            array('category_name', 'require', PARAMS_ERROR, ISSET_CHECK), //种类名称
            array('item_num', 'require', PARAMS_ERROR, ISSET_CHECK), //种类名称
            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $id            = $params['id'];
        $sc_code       = $params['sc_code'];
        $data          = $params['data'];
        $category_name = $params['category_name'];
        $item_num      = intval($params['item_num']);

        if ($id) {
            $data = $var = array();
            $var['id'] = $id;
            if($category_name) $var['category_name'] = $category_name;
            if($item_num) $var['item_num'] = $item_num;
            $data[] = $var;
        }
        $num = count($data);
        if ($num <= 0) {
            return $this->res(NULL, 4538);
        }

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                $where = $categoryData = array();
                $where['id']      = $value['id'];
                $where['sc_code'] = $sc_code;

                if ($value['category_order']) {
                    $categoryData['category_order'] = intval($value['category_order']);
                }

                $categoryData['update_time']    = NOW_TIME;
                if ($value['category_name']) {
                    $categoryData['category_name'] = $value['category_name'];
                }

                if (isset($value['item_num'])) {
                    $categoryData['item_num'] = intval($value['item_num']);
                }

                $categoryRes = D('IcCategory')->where($where)->save($categoryData);
                if($categoryRes <= 0){
                    return $this->res(NULL, 4539);
                }
            } 
        }
        return $this->res(true);
    }

    /**
     * 分类列表
     * Base.ItemModule.Category.Category.lists
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function lists($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('item_num_gt', array('YES','NO'),'require', PARAMS_ERROR, ISSET_CHECK,'in'), // 商品数量是否大于0
            );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code = $params['sc_code'];

        $where            = array();
        $where['sc_code'] = $sc_code;
        $where['status']  = 'ENABLE';
        !empty($params['item_num_gt']) && $params['item_num_gt'] == "YES" && $where['item_num'] = array('gt','0');

        $order = "category_order desc";
        $categoryRes = D('IcCategory')->order($order)->where($where)->select();
        if ($categoryRes === false) {
            return $this->res(NULL, 4540);
        }
        return $this->res($categoryRes);
    }

    /**
     * 获取单条分类信息
     * Base.ItemModule.Category.Category.getCategoryInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCategoryInfo($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //卖家唯一编码
            array('data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //分类唯一编码
            array('id', 'require', PARAMS_ERROR, ISSET_CHECK), //卖家唯一编码
            array('max', 'require', PARAMS_ERROR, ISSET_CHECK), //卖家唯一编码
            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code = $params['sc_code'];
        $id      = $params['id'];
        $data    = $params['data'];
        $max     = $params['max'];
        if ($id) {
            $data = array($id);
        }
        if (empty($data) && empty($max)) {
             return $this->res(NULL, 4560);
         } 
        $where = array();
        if(!empty($sc_code))$where['sc_code'] = $sc_code;
        if(!empty($data))$where['id'] = array('in', $data);
        if(!empty($max)){
            $limit = '1';
        }else{
            $limit = '';
        }
        $where['status'] = 'ENABLE';
        
        $order = "category_order desc";
        $categoryRes = D('IcCategory')->order($order)->where($where)->limit($limit)->select();
        if ($categoryRes === false) {
            return $this->res(NULL, 4541);
        }

        if (!empty($categoryRes)) {
            $categoryRes = changeArrayIndex($categoryRes, 'id');
        }
        if ($id) {
            $categoryRes = $categoryRes[$id];
        }
        if ($max) {
            $categoryRes = array_shift($categoryRes);
        }
        return $this->res($categoryRes);
    }

    /**
     * Base.ItemModule.Category.Category.checkName
     * @return [type] [description]
     */
    public function checkName($params){
        $this->_rule = array(
            array('category_name', 'require', PARAMS_ERROR, ISSET_CHECK), # 商品编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 商品编码
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $sc_code = $params['sc_code'];
        $category_name = $params['category_name'];

        $where = array();
        $where['category_name'] = $category_name;
        $where['sc_code'] = $sc_code;
        $where['status'] = 'ENABLE';

        $categoryRes = D('IcCategory')->where($where)->find();

        if ($categoryRes === false) {
            return $this->res(NULL, 4541);
        }
        return $this->res($categoryRes);
    }

    /**
    * 根据sic_codes 获取全部标签
    * Base.ItemModule.Category.Category.getCategorys
    * @param $sic_codes array
    * @access public
    */

    public function getCategorys($params){
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'), # 商品编码
            array('category_id', 'require', PARAMS_ERROR, ISSET_CHECK), # 商品编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), # 商品编码
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_codes   = $params['sic_codes'];
        $category_id = $params['category_id'];
        $sc_code     = $params['sc_code'];
        if (!empty($sic_codes)) {
            $map['iicr.sic_code'] = array('in',$params['sic_codes']);
        }
        if (!empty($sc_code)) {
            $map['iicr.sc_code']   = $params['sc_code'];
        }
        if (!empty($category_id)) {
            $map['iicr.category_id']   = $params['category_id'];
        }

        $map['ic.status']     = "ENABLE";
        $map['iicr.status']   = "ENABLE";

        $categorys = D('IcCategory')->alias('ic')
                          ->join("{$this->tablePrefix}ic_item_category_relation AS iicr ON ic.id = iicr.category_id")
                          ->field('ic.id,ic.category_name,ic.category_order,iicr.sic_code')
                          ->where($map)
                          ->select();
        if($categorys === false){
            return $this->res(NULL,4535);
        }
        return $this->res($categorys);
    }

    /**
     * 根据前台分类id获取后台分类id
     * Base.ItemModule.Category.Category.getCeidByCfid
     * @param type $params
     */
    public function getCeidByCfid($params){
        $this->_rule = array(
            array('category_front_id', 'require', PARAMS_ERROR, MUST_CHECK), //前台分类
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $category_front_id = $params['category_front_id'];
        $category_end_ids_info = D('CategoryRelationship')->where(array('cfid'=>$category_front_id))->field('ceid')->select();
        if(empty($category_end_ids_info)){
            return $this->res(NULL,4515);
        }
        $category_end_ids  = array_column($category_end_ids_info, 'ceid');
        return $this->res($category_end_ids);
    }
    
    
    /**
     * 获取前台分类
     * Base.ItemModule.Category.Category.getFrontCategory
     * @param type $params
     */
    public function getFrontCategory($params){
        $category_front = D('CategoryFront')->where(array('status'=>1))->order('sort desc')->field('id,name,pid')->select();
        if(empty($category_front)){
            return $this->res(array());
        }
        $category_info = array();
        foreach($category_front as $k=>$v){
            if($v['pid'] == 0){
                //一级分类
                $category_info[$v['id']]['name'] = $v['name'];
            }else{
                $category_info[$v['pid']]['sub'][$v['id']] = $v['name'];
            }
        }
        
        return $this->res($category_info);
    }
    

    /**
     * 获取后台分类
     * Base.ItemModule.Category.Category.getEndCategory
     * @param type $params
     */

    public function getEndCategory($params){

        $this->_rule = array(
            array('category_end_ids', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),  # 后台栏目ID集
        );      
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['id'] = array('in',$params['category_end_ids']);
        $field = 'id,name';

        $category = D('CategoryEnd')->field($field)->where($where)->select();
        return $this->res($category);
    }
    
    /**
     * 获取后台分类
     * Base.ItemModule.Category.Category.getCategoryByLevel
     * @param type $params
     */
    public function getCategoryByLevel($params){
        $this->_rule = array(
            array('level', 'require', PARAMS_ERROR, MUST_CHECK),  # 分类栏目
        ); 
        $level = $params['level'];
        $category_end_third = D('CategoryEnd')->where(array('level'=>$level,'status'=>1))->field('id,name')->select();
        return $this->res($category_end_third);
    }
    

    /**
     * 获取商家前台栏目
     * Base.ItemModule.Category.Category.storeCategory
     * @param type $params
     */

    public function storeCategory($params){

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),           #店铺编码
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code             = $params['sc_code'];
        $where['sc_code']    = $sc_code;
        $field               = 'ii.category_end_id';

        $categorys = D('IcStoreItem')->field($field)->alias('isi')
                   ->join("{$this->tablePrefix}ic_item ii ON ii.ic_code = isi.ic_code",'LEFT')
                   ->where($where)
                   ->select();

        $temp = array();
        foreach ($categorys as $k => $v) {
            $temp[] = $v['category_end_id'];
        }
        $categorys = array_filter(array_unique($temp));                  # 后台栏目ID集


        $top = D('CategoryFront')->where(array('level'=>1))->select();   # 获取顶级栏目并调整
        foreach ($top as $k => $v) {
            $top[$v['id']]['name'] = $v['name'];
            unset($top[$k]);
        }

        if(!empty($categorys)){
            
            foreach ($top as $k => $v) {                                      # 把子栏目 加入父栏目中

                $map['cr.ceid'] = array('in',$categorys);                     
                $map['cf.pid']  = $k;
                $field          = "cf.id,cf.name,cf.pid,cf.level";
                $category_front = D('CategoryRelationship')->field($field)->alias('cr')
                                                       ->join("{$this->tablePrefix}category_front cf ON cf.id = cr.cfid",'LEFT')
                                                       ->where($map)
                                                       ->select();

                foreach ($category_front as $key => $value) {
                    $category_front[$value['id']] = $value['name'];
                    unset($category_front[$key]);
                }


                $top[$k]['sub'] = $category_front;
            }          
        }
       

        return $this->res($top);
    }


}

?>
