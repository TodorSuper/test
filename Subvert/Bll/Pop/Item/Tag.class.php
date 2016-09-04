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

class Tag extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * Bll.Pop.Item.Tag.listsTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function listsTag($params){

		$apiPath = "Base.ItemModule.Tag.Tag.listsTag";
		$tagRes  = $this->invoke($apiPath, $params);
    	if($tagRes['status'] != 0){
    	    return $this->endInvoke($tagRes['message'], $tagRes['status']);
    	}
        if (empty($tagRes['response'])) {
           D()->startTrans();
           $addTagApi = 'Base.ItemModule.Tag.Tag.addTag';
           $addTagRes = $this->invoke($addTagApi, $params);
           if($addTagRes['status'] != 0){
               return $this->endInvoke($addTagRes['message'], $addTagRes['status']);
           }
           D()->commit();
        }
        $apiPath = "Base.ItemModule.Tag.Tag.listsTag";
        $tagRes  = $this->invoke($apiPath, $params);
        if($tagRes['status'] != 0){
            return $this->endInvoke($tagRes['message'], $tagRes['status']);
        }
    	return $this->endInvoke($tagRes['response']);
    }

    /**
     * Bll.Pop.Item.Tag.addTag
     * @param [type] $params [description]
     */
    public function addTag($params){
    	
    	//验证卖家
    	$sc_code = $params['sc_code'];
    	$storeData = array(
    	        'sc_code' => $sc_code,
    	        );
    	$storeApi     = "Base.StoreModule.Basic.Store.get";
    	$storeInfoRes = $this->invoke($storeApi, $storeData);
    	$storeInfo    = $storeRes['response'];
    	if (empty($storeInfo)) {
    	    return $this->endInvoke(NULL, $storeRes['status']);
    	}

        //判断卖家是否已经添加标签
        // $tagData = array(
        //     'sc_code' => $sc_code,
        //     );
        // $tagApi     = "Base.ItemModule.Tag.Tag.getTagInfo";
        // $tagInfoRes = $this->invoke($tagApi, $tagData);
        // $tagInfo    = $tagInfoRes['response'];
        // if (!empty($tagInfo)) {
        //     $params['tag_weight'] = $tagInfo['tag_weight'] + 1;
        // }else{
        //     $params['tag_weight'] = 1;
        // }

    	try{
    	    D()->startTrans();
    	    $apiPath = "Base.ItemModule.Tag.Tag.addTag";
    	    $tagRes = $this->invoke($apiPath, $params);
    	    if($tagRes['status'] != 0){
    	        return $this->endInvoke($tagRes['message'], $tagRes['status']);
    	    }
    	    D()->commit();
    	    return $this->endInvoke(true);
    	} catch (\Exception $ex) {
    	    D()->rollback();
    	    return $this->endInvoke($tagRes['message'], $tagRes['status']);
    	}
    }

    /**
     * Bll.Pop.Item.Tag.updateTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function updateTag($params){

		//更新分类
        try{
            D()->startTrans();
            $apiPath = "Base.ItemModule.Tag.Tag.updateTag";
            $tagRes = $this->invoke($apiPath, $params);
            if($tagRes['status'] != 0){
                return $this->endInvoke($tagRes['message'], $tagRes['status']);
            }

            //获取含有标签的商品sic_codes
            
            // var_dump($tagRes);die();
            // $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
            // $tagRes = $this->invoke($tagApi, $params);
            // if ($tagRes != 0) {
            //     return $this->endInvoke(NULL, $tagRes['status']);
            // }

            // $tag = $tagRes['response'];
            // $tag = changeArrayIndex($tag, 'id');
            // $itemApi = 'Base.ItemModule.Item.ItemInfo.storeItems';
            // $itemParams = array(
            //         'sc_code' => $params['sc_code'],
            //         'need_tag' => 'YES',
            //     );
            // $itemRes = $this->invoke($itemApi, $itemParams);
            // if ($itemRes['status'] != 0) {
            //     return $this->endInvoke(NULL, $itemRes['status']);
            // }
            // $itemArr = $itemRes['response'];
            // $uItem = array();
            D()->commit();

            D()->startTrans();
            $this->updateSort($params);
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            D()->rollback();
            return $this->endInvoke(NULL, 8);
        }   
    }

    public function updateSort($params){
        //查找有商品标签的sic_code
        $tagItemAPI = "Base.ItemModule.Tag.Tag.getTags";
        $tagItemData = array(
            'sc_code' => $params['sc_code'],
            );
        $tagItemRes = $this->invoke($tagItemAPI, $tagItemData);
        if ($tagItemRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagItemRes['status']);
        }
        $tagItem = $tagItemRes['response'];
        // var_dump($tagItem);
        //如果存在就检查
        if (!empty($tagItem)) {
            $sic_codes = array_unique(array_column($tagItem, 'sic_code'));
            // var_dump($sic_codes);
            $itemApi = "Base.ItemModule.Item.ItemInfo.storeItems"; 
            $itemData = array(
                'sc_code'   => $params['sc_code'],
                'sic_codes' => $sic_codes,
                'is_page'   => 'NO',
                );
            $itemRes = $this->invoke($itemApi, $itemData);
            if ($itemRes['status'] != 0) {
                return $this->endInvoke(NULL, $itemRes['status']);
            }
            // var_dump($itemRes);
            $itemArr = $itemRes['response'];
            // var_dump($itemArr);die();
            // 获取标签列表
            $tagListApi = "Base.ItemModule.Tag.Tag.listsTag";
            $tagListData = array(
                'sc_code' => $params['sc_code'],
                );
            $tagListRes = $this->invoke($tagListApi, $tagListData);
            if ($tagListRes['status'] != 0) {
                return $this->endInvoke(NULL, $tagListRes['status']);
            }
            $tagList = $tagListRes['response'];
            $tagList = changeArrayIndex($tagList, 'id');
            if (!empty($itemArr) && !empty($tagList)) {
                foreach ($itemArr as $key => $item) {
                    $sort = 0;
                    $tag_ids = array_filter(array_unique(explode(',', $item['tag_ids'])));
                    if (!empty($tag_ids)) {
                       foreach ($tag_ids as $value) {
                           if ($tagList[$value]['tag_weight']) {
                               $sort += $tagList[$value]['tag_weight'];
                           }
                       } 
                       
                    }
                    
                    if ($sort > 0) {
                        $arr = array();
                        $arr['sc_code']  = $params['sc_code'];
                        $arr['sic_code'] = $item['sic_code'];
                        $arr['sort']     = $sort;
                        $itemApi = "Base.ItemModule.Item.Item.updateStoreItem";
                        $itemRes = $this->invoke($itemApi, $arr);
                        if ($itemRes['status'] != 0 ) {
                            return $this->endInvoke(NULL, $itemRes['status']);
                        }
                    }
                }
            }

        }
    }
}