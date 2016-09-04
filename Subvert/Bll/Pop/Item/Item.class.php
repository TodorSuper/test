<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺商品相关模块业务逻辑层
 */

namespace Bll\Pop\Item;

use System\Base;

class Item extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 店铺标准库列表
     * Bll.Pop.Item.Item.standardItem
     * @param type $params
     */
    public function standardItem($params) {
        $apiPath = "Base.StoreModule.Item.Item.standardItem";
        $item_res = $this->invoke($apiPath, $params);
        return $this->res($item_res['response'],$item_res['status']);  //返回列表
    }

    /**
     * 
     * 选择标准库商品
     * Bll.Pop.Item.Item.selectItem
     * @param type $params
     */
    public function selectItem($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.StoreModule.Item.Item.selectItem";
            $select_res = $this->invoke($apiPath, $params);
            if($select_res['status'] != 0){
                throw new \Exception($select_res['message'],$select_res['status']);
            }
            D()->commit();
            return $this->res(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,4516,'','选货失败');
        }
        
    }

    /**
     * 
     * 查找商家单条商品信息
     * Bll.Pop.Item.Item.getStoreItem
     * @param type $params
     */
    public function getStoreItem($params) {

        $apiPath = "Base.ItemModule.Item.ItemInfo.getStoreItem";

        $storeItemInfoRes = $this->invoke($apiPath, $params);
        if ($storeItemInfoRes['status'] != 0) {
            return $this->endInvoke(NULL, $storeItemInfoRes['status']);
        }
        $itemData = array();
        $categoryApi = "Base.ItemModule.Category.Category.getCategorys";
        $categoryData = array(
            'sic_codes' => array($params['sic_code']),
            'sc_code' => $params['sc_code'],
            );
        $categoryRes = $this->invoke($categoryApi, $categoryData);
        if ($categoryRes['status'] != 0) {
            return $this->endInvoke(NULL, $categoryRes['status']);
        }
        $categoryList = $categoryRes['response'];
        if ($categoryList) {
            $itemData['myCategoryList'] = changeArrayIndex($categoryList, 'id');
        }else{
            $itemData['myCategoryList'] = array();
        }

        $tagApi = "Base.ItemModule.Tag.Tag.getTags";
        $tagData = array(
            'sic_codes' => array($params['sic_code']),
            'sc_code' => $params['sc_code'],
            );
        $tagRes = $this->invoke($tagApi, $tagData);
        if ($tagRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagRes['status']);
        }
        $tagList = $tagRes['response'];
        if ($tagList) {

            $itemData['myTagList'] = changeArrayIndex($tagList, 'id');
        }else{
            $itemData['myTagList'] = array();
        }
        $categoryApi = "Base.ItemModule.Category.Category.lists";
        $categoryRes = $this->invoke($categoryApi, $params);
        $itemData['categoryList'] = $categoryRes['response'];
        if ($itemData['categoryList']) {
           foreach ($itemData['categoryList'] as $key=>$value) {
                if ($itemData['myCategoryList'][$value['id']]) {
                    $itemData['categoryList'][$key]['categoryStatus'] = 'checked';
                }else{
                    $itemData['categoryList'][$key]['categoryStatus'] = '';
                }
            } 
        }
        
        $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
        $tagRes = $this->invoke($tagApi, $params);
        $itemData['tagList'] = $tagRes['response'];
        if ($itemData['tagList']) {
           foreach ($itemData['tagList'] as $key=>$value) {
                if ($itemData['myTagList'][$value['id']]) {
                    $itemData['tagList'][$key]['tagStatus'] = 'checked';
                }else{
                    $itemData['tagList'][$key]['tagStatus'] = '';
                }
            } 
        }
        $item = $storeItemInfoRes['response'];
        $itemData['item'] = $item;
        return $this->endInvoke($itemData);
    }

    /**
     * 
     * 更新商家商品信息
     * Bll.Pop.Item.Item.updateStoreItem
     * @param type $params
     */
    public function updateStoreItem($params) {
        try{
            D()->startTrans();
            $category_id_data = $params['category_id_data'];
            $tag_id_data      = $params['tag_id_data'];
            $sc_code          = $params['sc_code'];

            //验证标签
            if (!empty($tag_id_data)) {

                $checkTagRes = $this->checkTag($params);
            }
            
            //验证分类
            if (!empty($category_id_data)) {
                $checkCategoryRes = $this->checkCategory($params);
            }

            //获取全部种类
            $categoryApi = "Base.ItemModule.Category.Category.lists";
            $categoryData = array(
                'sc_code' => $params['sc_code'],
                );
            $categoryListRes = $this->invoke($categoryApi, $categoryData);
            if ($categoryListRes['status'] != 0) {
                return $this->endInvoke(NULL, $categoryListRes['status']);
            }
            $categoryList = $categoryListRes['response'];
            if (!empty($categoryList)) {
                $categoryGetApi = 'Base.ItemModule.Category.Category.getCategorys';
                $categoryGetData = array(
                    'sc_code'   => $params['sc_code'],
                    'sic_codes' => array($params['sic_code']),
                    );
                $categoryGetRes = $this->invoke($categoryGetApi, $categoryGetData);
                if ($categoryGetRes['status'] != 0) {
                    return $this->endInvoke(NULL, $categoryGetRes['status']);
                }

                $categoryGet = $categoryGetRes['response'];
                if (!empty($categoryGet)) {
                   $categoryGet_id = array_column($categoryGet, 'id'); 
                }else{
                    $categoryGet_id = array();
                }
                if (empty($category_id_data)) {
                    $category_reduce_id = $categoryGet_id;
                }else{
                    $category_interest = array_intersect($categoryGet_id, $category_id_data);
                    if (empty($category_interest)) {
                        $category_add_id = $category_id_data; 
                        $category_reduce_id = $categoryGet_id;
                    }else{
                        $category_add_id = array();
                        foreach ($category_id_data as $key => $value) {
                            if (!in_array($value, $category_interest)) {
                                $category_add_id[] = $value;
                            }
                        }
                        $category_reduce_id = array();
                        foreach ($categoryGet_id as $key => $value) {
                            if (!in_array($value, $category_interest)) {
                                $category_reduce_id[] = $value;
                            }
                        }
                    }
                }

                // var_dump($category_reduce_id);die();
                $categoryList = changeArrayIndex($categoryList, 'id');
                //更新种类
                if (!empty($category_add_id)) {
                    foreach ($category_add_id as $key => $value) {
                        if ($categoryList[$value]) {
                            $categoryList[$value]['item_num'] += 1;
                        }
                    }
                }
                // var_dump($category_add_id);
                if (!empty($category_reduce_id)) {
                    foreach ($category_reduce_id as $key => $value) {
                        if ($categoryList[$value]) {
                            $categoryList[$value]['item_num'] -= 1;
                        }
                    }  
                }

                $categoryUpdateApi = 'Base.ItemModule.Category.Category.update';
                $categoryUpdateData = array(
                    'sc_code' => $sc_code,
                    'data' => $categoryList,
                    );

                $categoryUpdateRes = $this->invoke($categoryUpdateApi, $categoryUpdateData);

                if ($categoryUpdateRes['status'] != 0) {
                    return $this->endInvoke(NULL, $categoryUpdateRes['status']);
                }
                
            }

            //获取标签
            $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
            $tagData = array(
                'sc_code' => $params['sc_code'],
                );
            $tagListRes = $this->invoke($tagApi, $tagData);
            if ($tagListRes['status'] != 0) {
                return $this->endInvoke(NULL, $tagListRes['status']);
            }

            $tagList = $tagListRes['response'];
            if (!empty($tagList)) {
                $tagGetInfo = 'Base.ItemModule.Tag.Tag.getTags';
                $tagGetData = array(
                    'sc_code'   => $params['sc_code'],
                    'sic_codes' => array($params['sic_code']),
                    );
                $tagGetRes = $this->invoke($tagGetInfo, $tagGetData);
                if ($tagGetRes['status'] != 0) {
                    return $this->endInvoke(NULL, $tagGetRes['status']);
                }

                $tagGet = $tagGetRes['response'];
                if (!empty($tagGet)) {
                   $tagGet_id = array_column($tagGet, 'id'); 
                }else{
                    $tagGet_id = array();
                }

                //是否数据标签的id进行判断
                if (empty($tag_id_data)) {
                    $tag_id_data = array();
                    $tag_reduce_id = $tagGet_id;
                }else{
                    $tag_interest = array_intersect($tagGet_id, $tag_id_data);
                    if (empty($tag_interest)) {
                        $tag_add_id = $tag_id_data;
                        $tag_reduce_id = $tagGet_id;
                    }else{
                        $tag_add_id = array();
                        foreach ($tag_id_data as $value) {
                            if (!in_array($value, $tag_interest)) {
                                $tag_add_id[] = $value;
                            }
                        }
                        $tag_reduce_id = array();
                        foreach ($tagGet_id as $key => $value) {
                            if (!in_array($value, $tag_interest)) {
                                $tag_reduce_id[] = $value;
                            }
                        }
                        
                    }
                }
                
                // var_dump($tag_reduce_id);die();
                $tagList = changeArrayIndex($tagList, 'id');
                //更新种类
                if (!empty($tag_add_id)) {
                    foreach ($tag_add_id as $key => $value) {
                        if ($tagList[$value]) {
                            $tagList[$value]['item_num'] += 1;
                        }
                    }
                }
                if (!empty($tag_reduce_id)) {
                    foreach ($tag_reduce_id as $key => $value) {
                        if ($tagList[$value]) {
                            $tagList[$value]['item_num'] -= 1;
                        }
                    }  
                }

                $tagUpdateApi = 'Base.ItemModule.Tag.Tag.updateTag';
                $tagUpdateData = array(
                    'sc_code' => $sc_code,
                    'data' => $tagList,
                    );

                $tagUpdateRes = $this->invoke($tagUpdateApi, $tagUpdateData);
                // var_dump($tag_reduce_id,$tagList);die();

                if ($tagUpdateRes['status'] != 0) {
                    return $this->endInvoke(NULL, $tagUpdateRes['status']);
                }
            }

            if (!empty($tag_id_data)) {
                $tagApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
                $tagData = array(
                    'sc_code' => $params['sc_code'],
                    'data' => $tag_id_data,
                    );
                $tagRes = $this->invoke($tagApi, $tagData);
                if ($tagRes['status'] != 0) {
                    return $this->endInvoke(NULL, $tagRes['status']);
                }
                $tag = $tagRes['response'];
                if (!empty($tag)) {
                    $tag_weight = array_column($tag, 'tag_weight');
                    $params['sort'] = array_sum($tag_weight);
                    $params['tag_ids'] = implode(',', $tag_id_data);
                }
            }else{
                $params['sort'] = '';
                $params['tag_ids'] = '';
            }
            
            $apiPath = "Base.ItemModule.Item.Item.updateStoreItem";
            $select_res = $this->invoke($apiPath, $params);
            if($select_res['status'] != 0){
                return $this->endInvoke(NULL, $select_res['status']);
            }

            D()->commit();
            return $this->res(true);
        } catch (\Exception $ex) {
            D()->rollback();
        }
    }

    /**
     * 验证标签信息
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function checkTag($params){

        $sc_code     = $params['sc_code'];
        $tag_id_data = $params['tag_id_data'];

        $tagApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
        $tagData = array(
            'sc_code' => $sc_code,
            'data'    => $tag_id_data,
            );
        $tagRes = $this->invoke($tagApi, $tagData);
        if ($tagRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagRes['status']);
        }

        $tag = $tagRes['response'];
        if (!empty($tag)) {
            if (count($tag) != count($tag_id_data)) {
            return $this->endInvoke(NULL, 4561);
            }
        }else{
            return $this->endInvoke(NULL, 4561);
        }
        return TRUE;
    }

    /**
     * 验证分类信息
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function checkCategory($params){
        $sc_code = $params['sc_code'];
        $category_id_data    = $params['category_id_data'];

        $categoryApi = 'Base.ItemModule.Category.Category.getCategoryInfo';
        $categoryData = array(
            'sc_code' => $sc_code,
            'data'    => $category_id_data,
            );

        $categoryRes = $this->invoke($categoryApi, $categoryData);
        if ($categoryRes['status'] != 0) {
            return $this->endInvoke(NULL, $categoryRes['status']);
        }

        $category = $categoryRes['response'];
        if (!empty($category)) {
            if (count($category) != count($category_id_data)) {
            return $this->endInvoke(NULL, 4560);
            }
        }else{
            return $this->endInvoke(NULL, 4560);
        }

        return TRUE;
    }
    /*
    * Bll.Pop.Item.Item.downlist
    */
    public function downlist($params) {
        $api = 'Base.ItemModule.Item.ItemInfo.storeItems';
        $res = $this->invoke($api,$params);
        //获取品牌
        $api = 'Base.ItemModule.Brand.Brand.brands';
        $barnds = $this->invoke($api,array('sc_code'=>$params['sc_code']));
        $res['response']['brands'] = $barnds['response'];
        return $this->endInvoke($res['response']);
    }
    /*
    * Bll.Pop.Item.Item.downQrcodes
    */
    public function downQrcodes($params) {
        $api = 'Base.ItemModule.Item.ItemInfo.storeItems';
        $data = $this->invoke($api,$params);
        return $this->endInvoke($data['response']);
    }
    /**
     * 
     * 商家商品列表
     * Bll.Pop.Item.Item.storeItems
     * @param type $params
     */
    public function storeItems($params) {

        // if ($params['category_id']) {
        //     $categoryById = "Base.ItemModule.Category.Category.getCategorys";
        //     $categoryByIdData = array(
        //         'sc_code'     => $params['sc_code'],
        //         'category_id' => $params['category_id'],
        //         );
        //     $categoryByIdRes = $this->invoke($categoryById, $categoryByIdData);
        //     if ($categoryByIdRes['status'] != 0) {
        //         return $this->endInvoke(NULL, $categoryByIdRes['status']);
        //     }
        //     $categorySic_codes = $categoryByIdRes['response'];
        //     // var_dump($categorySic_codes);
        //     if (!empty($categorySic_codes)) {
        //         $categorySic_codes = array_unique(array_column($categorySic_codes, 'sic_code'));
        //     }else{
        //         $categorySic_codes = array();
        //     }
        //     $params['sic_codes'] = $categorySic_codes;
        //     // var_dump($categorySic_codes);die();
        // }
        
        // if ($params['tag_id']) {
        //     $tagById = "Base.ItemModule.Tag.Tag.getTags";
        //     $tagByIdData = array(
        //         'sc_code'     => $params['sc_code'],
        //         'tag_id' => $params['tag_id'],
        //         );
        //     $tagByIdRes = $this->invoke($tagById, $tagByIdData);
        //     if ($tagByIdRes['status'] != 0) {
        //         return $this->endInvoke(NULL, $tagByIdRes['status']);
        //     }
        //     $tagSic_codes = $tagByIdRes['response'];
        //     if (!empty($tagSic_codes)) {
        //         $tagSic_codes = array_unique(array_column($tagSic_codes, 'sic_code'));
        //     }else{
        //         $tagSic_codes = array();
        //     }
        //     if (!empty($params['sic_codes']) && !empty($tagSic_codes)) {
        //         $params['sic_codes'] = array_unique(array_intersect($params['sic_codes'], $tagSic_codes));
        //     }

        //     if (empty($params['sic_codes'])) {
        //         $params['sic_codes'] = $tagSic_codes;
        //     }
        //     // var_dump($tagSic_codes);
        // }
        // var_dump($params['sic_codes']);
        $itemListPath = "Base.ItemModule.Item.ItemInfo.storeItems";
        $params['need_tag']      ='YES';
        $params['need_category'] = 'YES';
        $itemRes = $this->invoke($itemListPath, $params);
        if ($itemRes['status'] != 0) {
            return $this->endInvoke($itemRes['message'], $itemRes['status']);
        }
        $item = $itemRes['response']['lists'];
        // var_dump($item);die();
        if (!empty($item)) {
            $sic_codes = array_column($item, 'sic_code');
            $lists = changeArrayIndex($item, 'sic_code');

            //获取标签信息
            $tagApi = 'Base.ItemModule.Tag.Tag.getTags';
            $tagParams = array(
                'sic_codes' => $sic_codes,
                );
            $tagRes = $this->invoke($tagApi, $tagParams);
            if ($tagRes['status'] != 0) {
                return $this->endInvoke(NULL, $tagRes['status']);
            }

            $tag = $tagRes['response'];
            foreach ($lists as $sic_code => $value) {
                $arr = array();
                if (!empty($tag)) {
                    foreach ($tag as $key => $val) {
                        if ($sic_code == $val['sic_code']) {
                            $arr[] = $val['tag_name'];
                        }
                    }
                }
                $lists[$sic_code]['tag_name'] = $arr;
            }

            //获取种类信息
            $categoryApi = 'Base.ItemModule.Category.Category.getCategorys';
            $categoryParams = array(
                'sic_codes' => $sic_codes,
                );
            $categoryRes = $this->invoke($categoryApi, $categoryParams);
            if ($categoryRes['status'] != 0) {
                return $this->endInvoke(NULL, $categoryRes['status']);
            }

            $category = $categoryRes['response'];
            foreach ($lists as $sic_code => $value) {
                $arr = array();
                if (!empty($category)) {
                    foreach ($category as $key => $val) {
                        if ($sic_code == $val['sic_code']) {
                            $arr[] = $val['category_name'];
                        }
                    }
                }
                $lists[$sic_code]['category_name'] = $arr;
            }
            $itemRes['response']['lists'] = $lists;
        }

        //初始化标签
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
        
        $brandApi  = "Base.ItemModule.Brand.Brand.brands";
        $brandData = array(
            'sc_code' => $params['sc_code'],
            );
        $brandData = $this->invoke($brandApi,$brandData);
        $itemRes['response']['brandList'] = $brandData['response'];
        
        $categoryApi = "Base.ItemModule.Category.Category.lists";
        $categoryRes = $this->invoke($categoryApi, $params);
        $itemRes['response']['categoryList'] = $categoryRes['response'];
        // var_dump($categoryRes['response']);die();
        $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
        $tagRes = $this->invoke($tagApi, $params);
        $itemRes['response']['tagList'] = $tagRes['response'];
        // var_dump($itemRes['response']);die();
        return $this->endInvoke($itemRes['response']);
    }

    /**
    * 商品搜索
    * Bll.Pop.Item.Item.searchItems
    * @param type $params
    */
    public function searchItems($params){
        $params['search_sign'] = TRUE;
        $apiPath = "Base.ItemModule.Item.ItemInfo.searchConItems";
        $res     = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response'], $res['status'], $res['message']);
    }

    /**
     * 
     * 修改商品状态  支持批量修改 
     * Bll.Pop.Item.Item.setStatus
     * @param type $params
     */
    public function setStatus($params) {
        try {
            D()->startTrans();
            if ($params['status'] == IC_STORE_ITEM_OFF) {
                $checkSpcApi = "Base.ItemModule.Item.Item.checkSpc";
                $spcRes = $this->invoke($checkSpcApi, $params);
                if ($spcRes['status'] != 0) {
                    return $this->endInvoke($spcRes['message'], $spcRes['status']);
                }
            }
            $apiPath = "Base.ItemModule.Item.Item.setStatus";
            $set_status = $this->invoke($apiPath, $params);
            if($set_status['status'] != 0){
                return $this->endInvoke($set_status['message'],$set_status['status']);
            }
            D()->commit();
            return $this->res(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4517);
        }
    }
    /**
     * Bll.Pop.Item.Item.setTag
     * @param [type] $params [description]
     */
    public function setTag($params){
        try {
            D()->startTrans();
            $this->changeTagItemNum($params);
            $apiPath = "Base.ItemModule.Item.Item.setTag";
            $set_status = $this->invoke($apiPath, $params);
            if($set_status['status'] != 0){
                return $this->endInvoke($set_status['message'],$set_status['status']);
            }
            $this->changeSort($params);
            D()->commit();

            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            D()->rollback();
            // return $this->endInvoke(NULL, 4517);
        }
    }

    /**
     * 
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function changeSort($params){
        $sic_codes = $params['sic_codes'];
        $sc_code   = $params['sc_code'];
        $tag_id    = $params['tag_id'];

        //获取该标签信息
        $tagInfo = "Base.ItemModule.Tag.Tag.getTagInfo";
        $tagInfoData = array(
            'sc_code' => $sc_code,
            'data'    => $tag_id,
            );
        $tagInfoRes = $this->invoke($tagInfo, $tagInfoData);
        if ($tagInfoRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagInfoRes['status']);
        }
        $tag = $tagInfoRes['response'];
        // var_dump($tag);
        //获取标签列表
        $tagListApi = "Base.ItemModule.Tag.Tag.listsTag";
        $tagListData = array(
            'sc_code' => $sc_code,
            );
        $tagListRes = $this->invoke($tagListApi, $tagListData);
        if ($tagListRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagListRes['status']);
        }
        $tagList = $tagListRes['response'];
        $tagList = changeArrayIndex($tagList, 'id');
        //获取商品列表
        $itemApi = "Base.ItemModule.Item.ItemInfo.storeItems";
        $itemData = array(
            'sc_code'   => $sc_code,
            'sic_codes' => $sic_codes,
            );
        $itemRes = $this->invoke($itemApi, $itemData);
        if ($itemRes['status'] != 0) {
            return $this->endInvoke(null, $itemRes['status']);
        }

        $tag_id_arr = array_column($tag, 'id');
        $tag_id_str = implode(',', $tag_id_arr);
        // var_dump($tag);
        $itemArr = $itemRes['response']['lists'];
        // var_dump($itemArr);
        if (!empty($itemArr)) {
            foreach ($itemArr as  $item) {
                $sort = 0;
                $tag_ids = array_filter(explode(',', $item['tag_ids']));
                // var_dump($tag_ids);
                if (empty($tag_ids)) {
                    foreach ($tag as $value) {
                        $sort += $value['tag_weight'];
                    }
                    if ($sort > 0) {
                        $arr = array();
                        $arr['sc_code']  = $params['sc_code'];
                        $arr['sic_code'] = $item['sic_code'];
                        $arr['sort']     = $sort;
                        $arr['tag_ids']  = $tag_id_str;
        // var_dump($tag_id_arr,$arr);die();

                        $itemApi = "Base.ItemModule.Item.Item.updateStoreItem";
                        $itemRes = $this->invoke($itemApi, $arr);
                        if ($itemRes['status'] != 0 ) {
                            return $this->endInvoke(NULL, $itemRes['status']);
                        }
                    }
                }else{
                    $tag_ids = array_filter(array_unique(array_merge($tag_id_arr, $tag_ids)));
                    $tag_id_str = implode(',', $tag_ids);
                    foreach ($tag_ids as $value) {
                        if ($tagList[$value]['tag_weight']) {
                            $sort += $tagList[$value]['tag_weight'];
                        }
                    }
                    if ($sort > 0) {
                        $arr = array();
                        $arr['sc_code']  = $params['sc_code'];
                        $arr['sic_code'] = $item['sic_code'];
                        $arr['sort']     = $sort;
                        $arr['tag_ids']  = $tag_id_str;

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
    
    /**
     *
     * 用于在标签下，商品的数量
     * Bll.Pop.Item.Item.changeTagItemNum
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function changeTagItemNum($params){
        //获取标签
       $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
       $tagData = array(
           'sc_code' => $params['sc_code'],
           );
       $tagListRes = $this->invoke($tagApi, $tagData);
       if ($tagListRes['status'] != 0) {
           return $this->endInvoke(NULL, $tagListRes['status']);
       }
       // var_dump($params);
       $tagList = $tagListRes['response'];
       $tag_id_data = $params['tag_id'];
       $sic_codes = $params['sic_codes'];
       $sc_code = $params['sc_code'];
       // var_dump($tagList);
       if (!empty($tagList)) {
           $tagGetApi = 'Base.ItemModule.Tag.Tag.getTags';
           $tagGetData = array(
               'sc_code'   => $params['sc_code'],
               'sic_codes' => $params['sic_codes'],
               );
           $tagGetRes = $this->invoke($tagGetApi, $tagGetData);

           if ($tagGetRes['status'] != 0) {
               return $this->endInvoke(NULL, $tagGetRes['status']);
           }

           $tagGet = $tagGetRes['response'];
           
           //原来的商品的标签
           $tag_item = array();
           foreach ($tagGet as $key => $value) {
                if (!in_array($value['id'], $tag_item[$value['sic_code']])) {
                    $tag_item[$value['sic_code']][] = $value['id'];
                }
           }

           //输入的商品的标签
           $input_tag_item = array();
           foreach ($sic_codes as $sic_code) {
               $input_tag_item[$sic_code] = $tag_id_data;
           }

           // var_dump($input_tag_item,$tag_item);die();
           $tag_num = array();
           foreach ($input_tag_item as $key => $input) {
               if ($tag_item[$key]) {
                    $tags = $tag_item[$key];
                    foreach ($input as $tag) {
                        if (!in_array($tag, $tags)) {
                            $tag_num[$tag] += 1;
                        }
                    }
                      
               }else{
                    foreach ($input as $key => $tag) {
                        if ($tag_num[$tag]) {
                            $tag_num[$tag] += 1;
                        }else{
                            $tag_num[$tag] = 1;
                        }
                    }
               }
           }
            // var_dump($tag_item,$tag_num);die();
            
           $tagList = changeArrayIndex($tagList, 'id');
           //更新标签
           if (!empty($tag_num)) {
               foreach ($tagList as $key => $tag) {
                   if ($tag_num[$tag['id']]) {
                       $tagList[$key]['item_num'] += $tag_num[$tag['id']];
                   }
               }
               $tagUpdateApi = 'Base.ItemModule.Tag.Tag.updateTag';
               $tagUpdateData = array(
                   'sc_code' => $sc_code,
                   'data' => $tagList,
                   );
       // var_dump($tagGetRes,$tag_item,$input_tag_item,$tag_num, $tagUpdateData);die();

               $tagUpdateRes = $this->invoke($tagUpdateApi, $tagUpdateData);
               if ($tagUpdateRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagUpdateRes['status']);
               }
            }
        }
    }

    /**
     * Bll.Pop.Item.Item.brand
     * @return [type] [description]
     */
    public function brand($params){
        $brandApi = "Base.ItemModule.Brand.Brand.brands";
        $res = $this->invoke($brandApi, $params);
         if ($res['status'] != 0) {
           return $this->endInvoke(NULL, $res['status']);
       }
       $brand = $res['response'];
       return $this->endInvoke($brand);
    }

    /**
     * Bll.Pop.Item.Item.setItemStock
     * @param [type] $params [description]
     */
    public function setItemStock($params){
        // $apiPath  = "Base.ItemModule.Item.Item.setItemStock";
        // $uItemRes = $this->invoke($apiPath, $params);
        // if ($uItemRes['status'] != 0) {
        //     return $this->endInvoke($uItemRes['message'], $uItemRes['status']);
        // }
        // return $this->endInvoke(TRUE);

        try {
            D()->startTrans();
            $apiPath = "Base.ItemModule.Item.Item.setItemStock";
            $set_status = $this->invoke($apiPath, $params);
            if($set_status['status'] != 0){
                return $this->endInvoke($set_status['message'],$set_status['status']);
            }

            D()->commit();
            // $this->changeTagItemNum($params);

            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            // return $this->endInvoke(NULL, 4517);
        }
    }



    /**
     * Bll.Pop.Item.Item.checkItem
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function checkItem($params){
        $itemApi = 'Base.ItemModule.Item.ItemInfo.check';
        $res = $this->invoke($itemApi, $params);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL,$res['status']);
        }
        return $this->endInvoke(true);
    }
    /**
     * Bll.Pop.Item.Item.setCategory
     * @param [type] $params [description]
     */
    public function setCategory($params){
        try {
            D()->startTrans();
            $this->changeCategoryItemNum($params);
            $apiPath = "Base.ItemModule.Item.Item.setCategory";
            $set_status = $this->invoke($apiPath, $params);
            if($set_status['status'] != 0){
                return $this->endInvoke($set_status['message'],$set_status['status']);
            }
            D()->commit();
            $exceptionItem = $set_status['response']['exceptionItem'];
            if (!empty($exceptionItem)) {
                $itemApi = "Base.ItemModule.Item.ItemInfo.storeItems";
                $itemData = array(
                    'sc_code' => $params['sc_code'],
                    'sic_codes' => $exceptionItem,
                    );
                $itemRes = $this->invoke($itemApi, $itemData);
                if ($itemRes['status'] != 0) {
                    return $this->endInvoke(NULL, $itemRes['status']);
                }
                $itemList = $itemRes['response']['lists'];
                $item = array();
                foreach ($itemList as $value) {
                    $item[] = array('sic_no' => $value['sic_no'], 'goods_name' => $value['goods_name']);
                }
                return $this->endInvoke($item);
            }
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4517);
        }
    }

    /**
     * Bll.Pop.Item.Item.changeCategoryItemNum
     * @return [type] [description]
     */
    public function changeCategoryItemNum($params){
         //获取分类
        $categoryApi = "Base.ItemModule.Category.Category.lists";
        $categoryData = array(
            'sc_code' => $params['sc_code'],
            );
        $categoryListRes = $this->invoke($categoryApi, $categoryData);
        if ($categoryListRes['status'] != 0) {
            return $this->endInvoke(NULL, $categoryListRes['status']);
        }
        // var_dump($params);
        $categoryList = $categoryListRes['response'];
        $category_id_data = $params['category_id'];
        $sic_codes = $params['sic_codes'];
        $sc_code = $params['sc_code'];

        // var_dump($categoryList);die();
        if (!empty($categoryList)) {
            $categoryGetApi = 'Base.ItemModule.Category.Category.getCategorys';
            $categoryGetData = array(
                'sc_code'   => $params['sc_code'],
                'sic_codes' => $params['sic_codes'],
                );
            $categoryGetRes = $this->invoke($categoryGetApi, $categoryGetData);
            if ($categoryGetRes['status'] != 0) {
                return $this->endInvoke(NULL, $categoryGetRes['status']);
            }

            $categoryGet = $categoryGetRes['response'];
            
            //原来的商品的分类
            $category_item = array();
            foreach ($categoryGet as $key => $value) {
                 if (!in_array($value['id'], $category_item[$value['sic_code']])) {
                     $category_item[$value['sic_code']][] = $value['id'];
                 }
            }

            //输入的商品的分类
            $input_category_item = array();
            foreach ($sic_codes as $sic_code) {
                $input_category_item[$sic_code] = $category_id_data;
            }

            // var_dump($input_category_item,$category_item);die();
            $category_num = array();
            foreach ($input_category_item as $key => $input) {
                if ($category_item[$key]) {
                     $categorys = $category_item[$key];
                     foreach ($input as $category) {
                         if (!in_array($category, $categorys)) {
                             $category_num[$category] += 1;
                         }
                     }
                       
                }else{
                     foreach ($input as $key => $category) {
                         if ($category_num[$category]) {
                             $category_num[$category] += 1;
                         }else{
                             $category_num[$category] = 1;
                         }
                     }
                }
            }
             // var_dump($category_item,$category_num);die();
             
            $categoryList = changeArrayIndex($categoryList, 'id');
            //更新种类
            if (!empty($category_num)) {
                foreach ($categoryList as $key => $category) {
                    if ($category_num[$category['id']]) {
                        $categoryList[$key]['item_num'] += $category_num[$category['id']];
                    }
                }
                $categoryUpdateApi = 'Base.ItemModule.Category.Category.update';
                $categoryUpdateData = array(
                    'sc_code' => $sc_code,
                    'data' => $categoryList,
                    );
        // var_dump($categoryGetRes,$category_item,$input_category_item,$category_num, $categoryUpdateData);die();

                $categoryUpdateRes = $this->invoke($categoryUpdateApi, $categoryUpdateData);
                if ($categoryUpdateRes['status'] != 0) {
                    return $this->endInvoke(NULL, $categoryUpdateRes['status']);
                }
             }
         }
    }

    /**
     * Bll.Pop.Item.Item.getCategoryEndList
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCategoryEndList(){
        $categoryApi = "Base.ItemModule.Category.CategoryEnd.getCategoryEndList";
        $categoryRes = $this->invoke($categoryApi);
        return $this->endInvoke($categoryRes['response'], $categoryRes['status'], $categoryRes['message']);
    }
    

    /**
     * Bll.Pop.Item.Item.addStoreItem
     * @param [type] $params [description]
     */
    public function addStoreItem($params){
        // var_dump($params);
        try{
            D()->startTrans();

            //价格判断
            if (empty($params['price']) || $params['price'] <=0) {
                return $this->endInvoke(NULL, 4520);
            }

            $categoryNum = count($params['category_id_data']);
            if ($categoryNum > 3 || $categoryNum < 0) {
                return $this->endInvoke(NULL, 4559);
            }

            //验证商品编码或者商品名称和规格
            $checkApi = "Base.ItemModule.Item.ItemInfo.check";
            $checkRes = $this->invoke($checkApi, $params);
            if ($checkRes['status'] != 0) {
                return $this->endInvoke(null, $checkRes['status']);
            }
            // die();
            //照片数量判断
            $imgNum = count(json_decode($params['goods_img_new'], TRUE));
            if ($imgNum > 5 || $imgNum < 0) {
                return $this->endInvoke(NULL, 4521);
            }

            //添加标准库
            $stardandData = array(
                'goods_name'      => $params['goods_name'],
                'sub_name'        => empty($params['sub_name']) ? '' :$params['sub_name'],
                'brand'           => $params['brand'] ,
                'spec'            => empty($params['spec']) ? '' :$params['spec'],
                'packing'         => empty($params['packing']) ? '' :$params['packing'] ,
                'bar_code'        => empty($params['bar_code']) ? '' :$params['bar_code'] ,
                'goods_img'       => empty($params['goods_img']) ? '' :$params['goods_img'] ,
                'goods_img_new'   => empty($params['goods_img_new']) ? '' :$params['goods_img_new'],
                'status'          => 'PUBLISH',
                'is_standard'     => 'NO',
                'create_time'     => NOW_TIME,
                'update_time'     => NOW_TIME,
                'publish_time'    => NOW_TIME,    
                );
            $stardandApi = "Base.ItemModule.Item.Item.addStandard";
            $stardandRes = $this->invoke($stardandApi, $stardandData);
            if ($stardandRes['status'] != 0) {
                return $this->endInvoke(null, $stardandRes['status']);
            }

            //获取表标签信息
            if ($params['tag_id_data']) {
                $tagApi = "Base.ItemModule.Tag.Tag.getTagInfo";
                $tagData = array(
                    'sc_code' => $params['sc_code'],
                    'data' => $params['tag_id_data'],
                    );
                $tagRes = $this->invoke($tagApi, $tagData);

                if ($tagRes['status'] != 0) {
                    return $this->endInvoke($tagRes['message'], $tagRes['status']);
                }

                $tag = $tagRes['response'];
                if (!empty($tag)) {
                    $tag_weight = array_column($tag, 'tag_weight');
                    $params['sort'] = array_sum($tag_weight);
                }
            }


            //添加商家商品库
            $itemData = array(
                'ic_code'          => $stardandRes['response'],
                'sc_code'          => $params['sc_code'],
                'sub_name'         => empty($params['sub_name']) ? '' :$params['sub_name'] ,
                'sic_no'           => $params['sic_no'],
                'price'            => $params['price'] + 0,
                'min_num'          => empty($params['min_num']) ? 1 : $params['min_num'],
                'stock'            => $params['stock'] + 0,
                'warn_stock'       => $params['warn_stock'],
                'category_id_data' => $params['category_id_data'],
                'tag_id_data'      => $params['tag_id_data'],
                'sort'             => $params['sort'],
                'tag_ids'          => implode(',', $params['tag_id_data']),
                'status'           => $params['status'],
                'source'           => IC_ITEM_SOURCE_POP,
                );

            $icItemApi      = "Base.ItemModule.Item.Item.addStoreItem";

            $icStoreItemRes = $this->invoke($icItemApi, $itemData);

            if ($icStoreItemRes['status'] != 0) {
                return $this->endInvoke(NULL, $icStoreItemRes['status']);
            }

            //添加该分类信息的商品数量
            if (!empty($params['category_id_data']) && is_array($params['category_id_data'])) {
                $categoryGetApi = 'Base.ItemModule.Category.Category.getCategoryInfo';
                $categoryData = array(
                    'sc_code' => $params['sc_code'],
                    'data'    => $params['category_id_data'],
                    );
                $categoryGetRes = $this->invoke($categoryGetApi, $categoryData);
                if ($categoryGetRes['status'] != 0) {
                    return $this->endInvoke(NULL, $categoryGetRes['status']);
                }

                $category = $categoryGetRes['response'];
                if (empty($category)) {
                    return $this->endInvoke(NULL, 4541);
                }

                foreach ($category as $key => $value) {
                    $category[$key]['item_num'] = $value['item_num'] + 1;
                }

                $categoryApi = 'Base.ItemModule.Category.Category.update';
                $categoryParams = array(
                    'sc_code' => $params['sc_code'],
                    'data'    => $category,
                    );
                $categoryRes = $this->invoke($categoryApi, $categoryParams);

                if ($categoryRes['status'] != 0) {
                    return $this->endInvoke(NULL, $categoryRes['status']);
                }
            }

            //添加该标签信息的商品数量
            if (!empty($params['tag_id_data']) && is_array($params['tag_id_data'])) {
                //获取tag_id标签
                $tagGetApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
                $tagData = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $params['tag_id_data'],
                   );
                $tagGetRes = $this->invoke($tagGetApi, $tagData);
                if ($tagGetRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagGetRes['status']);
                }

                //更新商品数据
                $tag = $tagGetRes['response'];
                if (empty($tag)) {
                   return $this->endInvoke(NULL, 4535);
                }
                foreach ($tag as $key => $value) {
                   $tag[$key]['item_num'] = $value['item_num'] + 1;
                }

                //标签数据
                $tagApi = 'Base.ItemModule.Tag.Tag.updateTag';
                $tagParams = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $tag,
                   );
                $tagRes = $this->invoke($tagApi, $tagParams);
                if ($tagRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagRes['status']);
                }
            }


            //生成二维码
            $qrcodeApi = 'Base.ItemModule.Item.Item.qrcode';
            $sic_code = $icStoreItemRes['response']['sic_code'];
            $itemData = array(
                'sc_code' => $params['sc_code'],
                'sic_code' => $sic_code,
                'goods_img' => $params['goods_img'],
                );
            $qrcodeRes = $this->invoke($qrcodeApi,$itemData);
            if ($qrcodeRes['status'] != 0) {
                return $this->endInvoke(NULL, $qrcodeRes['status']);
            }
            D()->commit();
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            L($ex->getMessage());
            D()->rollback();
            return $this->endInvoke(null,8);

        }
        
    }
    
    public function updateTagChangeNum(){
        //检查该商品是否有标签
        $tagCheckApi = "Base.ItemModule.Tag.Tag.getTags";
        $tagCheckData = array(
            'sc_code' => $params['sc_code'],
            'sic_codes' => array($params['sic_code']),
            );
        $tagCheckRes = $this->invoke($tagCheckApi, $tagCheckData);
        if ($tagCheckRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagCheckRes['status']);
        }

        //获取已有的标签
        $tagCheck = $tagCheckRes['response'];
        if (!empty($tagCheck)) {
            //已有的商品标签
            $item_tag = array_column($tagCheck, 'id');
            //添加的商品标签
            $input_tag = $params['tag_id_data'];
            if (!empty($input_tag)) {
                $input_tag = array();
            }
            $tag_merge_tag = array_unique(array_merge($item_tag, $input_tag));
            $interest_arr = array_intersect($item_tag, $input_tag);

            $add_tag_arr = array_diff($interest_arr, $input_tag);
            $reduce_tag_arr = array_diff($interest_arr, $item_tag);
            if (!empty($add_tag_arr)) {
                $tagGetApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
                $tagData = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $tag_merge_tag,
                   );
                $tagGetRes = $this->invoke($tagGetApi, $tagData);
                if ($tagGetRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagGetRes['status']);
                }
                $tag = $tagGetRes['response'];
                $tag = changeArrayIndex($tag, 'id');
                foreach ($add_tag_arr as $key => $add_tag) {
                   $tag[$add_tag]['item_num'] += 1;
                }
                $tagApi = 'Base.ItemModule.Tag.Tag.updateTag';
                $tagParams = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $tag,
                   );
                $tagRes = $this->invoke($tagApi, $tagParams);
                if ($tagRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagRes['status']);
                }
            }

            if (!empty($reduce_tag_arr)) {
               $tagGetApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
               $tagData = array(
                  'sc_code' => $params['sc_code'],
                  'data'    => $tag_merge_tag,
                  );
               $tagGetRes = $this->invoke($tagGetApi, $tagData);
               if ($tagGetRes['status'] != 0) {
                  return $this->endInvoke(NULL, $tagGetRes['status']);
               }
               $tag = $tagGetRes['response'];
               foreach ($tag as $key => $value) {
                  $tag[$key]['item_num'] = $value['item_num'] - 1;
               }
               $tagApi = 'Base.ItemModule.Tag.Tag.updateTag';
               $tagParams = array(
                  'sc_code' => $params['sc_code'],
                  'data'    => $tag,
                  );
               $tagRes = $this->invoke($tagApi, $tagParams);
               if ($tagRes['status'] != 0) {
                  return $this->endInvoke(NULL, $tagRes['status']);
               }
            }
            if (!empty($params['tag_id_data']) && is_array($params['tag_id_data'])) {

                $tagGetApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
                $tagData = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $params['tag_id_data'],
                   );
                $tagGetRes = $this->invoke($tagGetApi, $tagData);
                if ($tagGetRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagGetRes['status']);
                }
                $tag = $tagGetRes['response'];
                foreach ($tag as $key => $value) {
                   $tag[$key]['item_num'] = $value['item_num'] + 1;
                }
                $tagApi = 'Base.ItemModule.Tag.Tag.updateTag';
                $tagParams = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $tag,
                   );
                $tagRes = $this->invoke($tagApi, $tagParams);
                if ($tagRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagRes['status']);
                }
            }else{
                $tagGetApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
                $tagData = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $params['tag_id_data'],
                   );
                $tagGetRes = $this->invoke($tagGetApi, $tagData);
                if ($tagGetRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagGetRes['status']);
                }
                $tag = $tagGetRes['response'];
                foreach ($tag as $key => $value) {
                   $tag[$key]['item_num'] = $value['item_num'] - 1;
                }
                $tagApi = 'Base.ItemModule.Tag.Tag.updateTag';
                $tagParams = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $tag,
                   );
                $tagRes = $this->invoke($tagApi, $tagParams);
                if ($tagRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagRes['status']);
                }
            }

        }else{
            if (!empty($params['tag_id_data']) && is_array($params['tag_id_data'])) {
                $tagGetApi = 'Base.ItemModule.Tag.Tag.getTagInfo';
                $tagData = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $params['tag_id_data'],
                   );
                $tagGetRes = $this->invoke($tagGetApi, $tagData);
                if ($tagGetRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagGetRes['status']);
                }
                $tag = $tagGetRes['response'];
                foreach ($tag as $key => $value) {
                   $tag[$key]['item_num'] = $value['item_num'] + 1;
                }
                $tagApi = 'Base.ItemModule.Tag.Tag.updateTag';
                $tagParams = array(
                   'sc_code' => $params['sc_code'],
                   'data'    => $tag,
                   );
                $tagRes = $this->invoke($tagApi, $tagParams);
                if ($tagRes['status'] != 0) {
                   return $this->endInvoke(NULL, $tagRes['status']);
                }
            }
        }
        
    }
    /**
     * Bll.Pop.Item.Item.getAboutAddItemInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getAboutAddItemInfo($params){
        //获取商品列表
        $categoryApi = "Base.ItemModule.Category.Category.lists";
        $categoryRes = $this->invoke($categoryApi, $params);
        if ($categoryRes['status'] != 0) {
            return $this->endInvoke(NULL, $categoryRes['status']);
        }
        $category = $categoryRes['response'];

        //获取标签列表
        $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
        $tagRes = $this->invoke($tagApi, $params);
        if ($tagRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagRes['status']);
        }
        $tag = $tagRes['response'];

        //检验标签列表是否存在和生成标签列表
        if (empty($tag)) {
            try{
                D()->startTrans();
                $initTagApi = 'Base.ItemModule.Tag.Tag.addTag';
                $initTagData = array(
                    'sc_code' => $params['sc_code'],
                    );
                $initTagRes = $this->invoke($initTagApi, $initTagData);
                if ($initTagRes['status'] != 0) {
                    return $this->endInvoke(NULL, $initTagRes['status']);
                }
                D()->commit();
            } catch (\Exception $ex) {
                D()->rollback();
                return $this->endInvoke(NULL, 8);
            }
           
            $tagApi = "Base.ItemModule.Tag.Tag.listsTag";
            $tagRes = $this->invoke($tagApi, $params);
            if ($tagRes['status'] != 0) {
                return $this->endInvoke(NULL, $tagRes['status']);
            }
            $tag = $tagRes['response'];
        }
        
        $aboutAddItemInfo = array(
            'tag'      => $tag,
            'category' => $category,
            );
        return $this->endInvoke($aboutAddItemInfo);
    }  
    /**
     * 获取前台分类
     */
    private function getCategoryFront() {
        $apiPath = "Base.ItemModule.Category.Category.getFrontCategory";
        $data = array();
        $category_res = $this->invoke($apiPath, $data);
        if ($category_res['status'] != 0) {
            return $this->endInvoke(NULL, $category_res['status']);
        }
        return $category_res['response'];
    }
    

}

?>
