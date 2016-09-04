<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangren.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangren.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng < zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |
 */


namespace Base\StoreModule\Item;

use System\Base;
class Statistic extends Base
{
    public $defaultPage = 1;
    public $defaultPageCount = 10;
    public function __construct()
    {
        parent::__construct();
    }
    /**
     *
     * Base.StoreModule.Item.Statistic.itemStatistic
     * @param type $params
     */
    public function itemStatistic($params)
    {
        $this->rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('pageCount', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
        );
        empty($params['page'])? $page = $this->defaultPage : $page = $params['page'];
        empty($params['pageCount'])? $pageCount = $this->defaultPageCount : $pageCount = $params['pageCount'];
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $field = 'sc_code,name';
        $allStores = D('ScStore')->field($field)->select();
        if(empty($allStores) || $allStores === false){
            return $this->res(13);
        }else{
            $allStores      = changeArrayIndex($allStores, 'sc_code');
            $sc_code        = array_column($allStores, 'sc_code');
            $map            = array();
            $map['sc_code'] = array('in', $sc_code);
        }

        if ($params['sc_code']) {
            $sc_code = $params['sc_code'];
            if (!$allStores[$sc_code]) {
                return $this->res(13);
            }
            $map            = array();
            $map['sc_code'] = array('in', $sc_code);
            $stores = array();
            $stores[$sc_code] = $allStores[$sc_code];
        }else{
            $stores = $allStores;
        }

        $field = 'sc_code,status';
        $storeItems = D('IcStoreItem')->where($map)->field($field)->select();
        $allStatusON = $allStatusOFF = 0;
        $storeInfoList = array();
        foreach($stores as $k => $store){
            $statusON = $statusOFF = 0;
            $arr = array();
            foreach($storeItems as $key => $item){
                if ($k == $item['sc_code']) {
                    if($item['status'] === 'ON')$statusON += 1;
                    if($item['status'] === 'OFF')$statusOFF += 1;
                }
                
            }
            $arr['name']      = $store['name'];
            $arr['sc_code']   = $k;
            $arr['allNum']    = $statusON + $statusOFF;
            $arr['statusON']  = $statusON;
            $arr['statusOFF'] = $statusOFF;
            $storeInfoList[]  = $arr;
        }

        foreach ($storeItems as $key => $item) {
            if ($item['status'] === 'ON') {
               $allStatusON += 1; 
            }
            if ($item['status'] === 'OFF') {
               $allStatusOFF += 1; 
            }
        }
        $storeStatistic = array();
        // var_dump($storeInfoList);
        if (count($storeInfoList) < 10) {
            $storeStatistic['list'] = $storeInfoList;
        }else{
            $storeStatistic['list'] = array_slice($storeInfoList,($page-1)*$pageCount, $pageCount);
        }
        $storeStatistic['storesInfo']   = $allStores;
        $storeStatistic['storesCount']  = count($allStores);
        $storeStatistic['totalNum']     = $allStatusON + $allStatusOFF;
        $storeStatistic['allStatusON']  = $allStatusON;
        $storeStatistic['allStatusOFF'] = $allStatusOFF;
        $storeStatistic['page']         = $page;
        $storeStatistic['page_number']  = $pageCount;
        return $this->res($storeStatistic);
    }
}