<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺商品相关模块
 */

namespace Test\Base\StoreItem;

use System\Base;

class Item extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 店铺标准库列表
     * Test.Base.StoreItem.Item.standardItem
     * @param type $params
     */
    public function standardItem($params) {
        $apiPath = "Base.StoreModule.Item.Item.standardItem";
        $data = array('sc_code' => '123456');
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }

    /**
     * Test.Base.StoreItem.Item.getStoreItem
     * @param type $params
     */
    public function getStoreItem($params) {
        $apiPath = "Base.StoreModule.Item.Item.getStoreItem";
        $data = array(
            'sic_code'=>'12200000003',
            'sc_code' => '123456',
        );
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'], $res['status']);
    }

    /**
     * Test.Base.StoreItem.Item.updateStoreItem
     * @param type $params
     */
    public function updateStoreItem($params) {
        try {
            D()->startTrans();
            $apiPath = "Base.StoreModule.Item.Item.updateStoreItem";
            $data = array(
                'sc_code' => '123456',
                'sic_code'=>'12200000003',
                'sub_name' => 'aaaaaa',
                'price' => '2',
                'min_num'=>1,
                'stock'=>1,
            );
            $res = $this->invoke($apiPath, $data);
            D()->commit();
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            D()->rollback();
        }
    }
     /**
     * Test.Base.StoreItem.Item.selectItem
     * @param type $params
     */
    public function selectItem($params){
        try {
            D()->startTrans();
            $apiPath = "Base.StoreModule.Item.Item.selectItem";
            $ic_codes = array('112000007','11200000006');
            $data = array(
                'sc_code' => '123456',
                'ic_codes' => $ic_codes,
              
            );
            $res = $this->invoke($apiPath, $data);
            D()->commit();
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            D()->rollback();
        }
    }
    
    
    /**
     * Test.Base.StoreItem.Item.storeItems
     * @param type $params
     */
    public function storeItems($params){
        $apiPath = "Base.StoreModule.Item.Item.storeItems";
        $data = array('sc_code'=>'123456');
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }
    
    /**
     * 
     * Test.Base.StoreItem.Item.setStatus
     */
    public function setStatus($params){
        try {
            D()->startTrans();
            $apiPath = "Base.StoreModule.Item.Item.setStatus";
            $sic_codes = array('12200000003','12200000004');
            $data = array(
                'sc_code' => '123456',
                'sic_codes' => $sic_codes,
                'status'=>IC_STORE_ITEM_ON,
            );
            $res = $this->invoke($apiPath, $data);
            D()->commit();
            return $this->res($res['response'], $res['status']);
        } catch (\Exception $ex) {
            D()->rollback();
        }
    }

}

?>
