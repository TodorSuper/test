<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\PopItem;

use System\Base;
class Category extends Base {


    public function __construct() {
        parent::__construct();
    }

    /**
     * Test.Bll.PopItem.Category.addCategory
     * @param [type] $params [description]	
     */
    public function addCategory($params){
    	//添加分类种类
    		$params = array(
    			'sc_code' => 1020000000026,
    			'category_name' => '醋',
    			);
	    	$apiPath = "Bll.Pop.Item.Category.addCategory";
    	$categoryRes = $this->invoke($apiPath, $params);
    	return $this->endInvoke($categoryRes);
    }

    /**
     * Test.Bll.PopItem.Category.listsCategory
     * @return [type] [description]
     */
    public function listsCategory(){
    	//获取分类列表(默认按照分类权重降序排列)
    		$params = array(
    			'sc_code' => 1020000000026,
    			);
	    $apiPath = "Bll.Pop.Item.Category.listsCategory";
    	$categoryRes = $this->invoke($apiPath, $params);
    	return $this->endInvoke($categoryRes);
    }

    /**
     * Test.Bll.PopItem.Category.updateCategory
     * @param  [type] $params [description]  Bll.Pop.Item.Category.updateCategory
     * @return [type]         [description]
     */
    public function updateCategory($params){
    	//	用于--------测试种类名称修改
    	// $params = array(
    	// 		'sc_code' => 1020000000026,
    	// 		'category_name' => '酸酸的醋',
    	// 		'id' => 5,
    	// 	);
    	
    	//	用于---------测试拖拽数据修改
    	// $params = array(
    	// 		'sc_code' => 1020000000026,

			  //   'data' => array(
			  //   			array(
			  //   				'category_name' => '测试123',
			  //   				'id' => '4',
			  //   				'category_order' => '66',
			  //   				),
			  //   			array(
			  //   				'category_name' => '测试177',
			  //   				'id' => '6',
			  //   				'category_order' => '66',
			  //   				),
			  //   			array(
			  //   				'category_name' => '测试12388',
			  //   				'id' => '7',
			  //   				'category_order' => '77',
			  //   				),
			  //   			array(
			  //   				'category_name' => '测试155',
			  //   				'id' => '8',
			  //   				'category_order' => '6',
			  //   				),
			  //   			array(
			  //   				'category_name' => '测123',
			  //   				'id' => '9',
			  //   				'category_order' => '56',
			  //   				),
			  //   			)
    	// 	);
    	
    	//	用于该种类下商品的数量
    	// $params = array(
    	// 	'sc_code' => 1020000000026,
    	// 	'id' => 9,
    	// 	'item_num' => 77,
    	// 	);
    	$apiPath = "Bll.Pop.Item.Category.updateCategory";
    	$categoryRes = $this->invoke($apiPath, $params);
    	return $this->endInvoke($categoryRes);
    }

    /**
     * Test.Bll.PopItem.Category.deleteCategory
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function deleteCategory($params){
    	//普通删除(和该分类下商品数量不为零的情况下测试)
    	$params = array(
    		'sc_code' => 1020000000026,
    		'id' => 8,
    		);
    	$apiPath = "Bll.Pop.Item.Category.deleteCategory";
    	$categoryRes = $this->invoke($apiPath, $params);
    	return $this->endInvoke($categoryRes);
    }

    /**
     * Test.Bll.PopItem.Category.getCategoryInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCategoryInfo($params){
    	// 用于测试获取单条数据(一维数组，默认获取权重最大的那条分类)
    	$params = array(
    		'sc_code' => 1020000000026,
    		'id' => 9,
    		);
    	$apiPath = "Bll.Pop.Item.Category.getCategoryInfo";
    	$categoryRes = $this->invoke($apiPath, $params);
    	return $this->endInvoke($categoryRes);
    }
}