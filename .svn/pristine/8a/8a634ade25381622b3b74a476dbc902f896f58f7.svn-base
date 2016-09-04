<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 店铺商品相关模块业务逻辑层
 */

namespace Bll\Pop\Item;

use System\Base;

class Category extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加分类信息
     * Bll.Pop.Item.Category.addCategory
     * @param [type] $params [description]
     */
    public function addCategory($params){
       
        $len = mb_strlen($params['category_name'], 4);
        if ($len == false || $len < 0 || $len > 4) {
           return $this->endInvoke(NULL, 4573);
        }

        //验证卖家sc_code
        $sc_code = $params['sc_code'];
        $storeData = array(
                'sc_code' => $sc_code,
                );
        $storeApi     = "Base.StoreModule.Basic.Store.get";
        $storeInfoRes = $this->invoke($storeApi, $storeData);
        $storeInfo    = $storeInfoRes['response'];
        if (empty($storeInfo)) {
            return $this->endInvoke(NULL, $storeInfoRes['status']);
        }

        //判断分类名称时候存在
        $categoryNameApi = 'Base.ItemModule.Category.Category.checkName';
        $categoryNameData = array(
            'sc_code' => $params['sc_code'],
            'category_name' => $params['category_name'],
            );

        $categoryNameRes = $this->invoke($categoryNameApi, $categoryNameData);
        if ($categoryNameRes['status'] != 0) {
            return $this->endInvoke(NULL, $categoryNameRes['status']);
        }

        $categoryName = $categoryNameRes['response'];
        if (!empty($categoryName)) {
            return $this->endInvoke(NULL, 4564);
        }
        //判断卖家是否已经添加分类
        $categoryData = array(
            'sc_code' => $sc_code,
            'max'     => 'max',
            );
        $categoryApi     = "Base.ItemModule.Category.Category.getCategoryInfo";
        $categoryInfoRes = $this->invoke($categoryApi, $categoryData);
        $categoryInfo    = $categoryInfoRes['response'];

        if (!empty($categoryInfo)) {
            $params['category_order'] = $categoryInfo['category_order'] + 1;
        }else{
            $params['category_order'] = 1;
        }
        //添加分类
        try{
            D()->startTrans();
            $apiPath = "Base.ItemModule.Category.Category.add";
            $categoryRes = $this->invoke($apiPath, $params);
            if($categoryRes['status'] != 0){
                return $this->endInvoke($categoryRes['message'], $categoryRes['status']);
            }
            D()->commit();

            return $this->endInvoke($categoryRes['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($categoryRes['message'], $categoryRes['status']);
        }
    }

    /**
     * 分类列表
     * Bll.Pop.Item.Category.listsCategory
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function listsCategory($params){
        $apiPath = "Base.ItemModule.Category.Category.lists";
        $categoryRes = $this->invoke($apiPath, $params);
        if($categoryRes['status'] != 0){
            return $this->endInvoke(NULL, $categoryRes['status']);
        }
        return $this->endInvoke($categoryRes['response']);
    }

    /**
     * Bll.Pop.Item.Category.updateCategory
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function updateCategory($params){
        $len = mb_strlen($params['category_name'], 4);
        if ($len == false || $len < 0 || $len > 4) {
           return $this->endInvoke(NULL, 4573);
        }
		//更新分类
        try{
            D()->startTrans();
            $apiPath = "Base.ItemModule.Category.Category.update";
            $categoryRes = $this->invoke($apiPath, $params);
            if($categoryRes['status'] != 0){
                return $this->endInvoke(NULL, $categoryRes['status']);
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7057);
        }   	
    }
    /**
     * Bll.Pop.Item.Category.deleteCategory
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function deleteCategory($params){
        $sc_code = $params['sc_code'];
        $id      = $params['id'];

        //判断卖家该分类下商品数
        $categoryData = array(
            'sc_code' => $sc_code,
            'id'      => $id,
            );
        $categoryApi     = "Base.ItemModule.Category.Category.getCategoryInfo";
        $categoryInfoRes = $this->invoke($categoryApi, $categoryData);
        $categoryInfo    = $categoryInfoRes['response'];

        if (empty($categoryInfo)) {
            return $this->endInvoke(NULL, 4563);
        }else{
            if ($categoryInfo['item_num'] > 0) {
                return $this->endInvoke(NULL, 4563);
            }
        }


        //删除分类
        try{
            D()->startTrans();
            $apiPath = "Base.ItemModule.Category.Category.delete";
            $categoryRes = $this->invoke($apiPath, $params);
            if($categoryRes['status'] != 0){
                return $this->endInvoke($categoryRes['message'], $categoryRes['status']);
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7057);
        }
    }


    /**
     * Bll.Pop.Item.Category.getCategoryInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCategoryInfo($params){
		$apiPath     = "Base.ItemModule.Category.Category.getCategoryInfo";
		$categoryRes = $this->invoke($apiPath, $params);

    	if($categoryRes['status'] != 0){
    	    return $this->endInvoke($categoryRes['message'], $categoryRes['status']);
    	}
    	return $this->endInvoke($categoryRes['response']);
    }
}