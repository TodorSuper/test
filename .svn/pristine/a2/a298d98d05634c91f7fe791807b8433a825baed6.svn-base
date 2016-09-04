<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | cms后台商品相关模块
 */

namespace Bll\Cms\Item;

use System\Base;

class Classify extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Bll.Cms.Item.Classify.addClass
     * @param type $params
     * @return type
     */
    public function addClass($params){
        try{
            D()->startTrans();
            $apiPath='Base.ItemModule.Classify.Classify.add';
            $call=$this->invoke($apiPath,$params);
            if($call['status']!==0){
                return $this->endInvoke('',$call['status']);
            }
            D()->commit();
           return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
        }
    }
    /**
     * Bll.Cms.Item.Classify.updateClass
     * @param type $params
     * @return type
     */
    public function updateClass($params){
        try{
            D()->startTrans();
            $apiPath='Base.ItemModule.Classify.Classify.update';
            $call=$this->invoke($apiPath,$params);
            if($call['status']!==0){
                return $this->endInvoke('',$call['status']);
            }
            D()->commit();
            return $this->endInvoke(true);
        }catch (\Exception $ex) {
            D()->rollback();
        }
    }

    /**
     * Bll.Cms.Item.Classify.setOrders
     * @param [type] $params [description]
     */
    public function setOrders($params){
        try{
            D()->startTrans();
            $apiPath = "Base.ItemModule.Classify.Classify.setOrders";
            $iRes    = $this->invoke($apiPath, $params);
            if($iRes['status'] != 0){
                return $this->endInvoke(NULL,4516,'','排序错误');
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4516,'','排序错误');
        }
    }
}