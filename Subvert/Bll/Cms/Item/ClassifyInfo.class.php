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

class ClassifyInfo extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Bll.Cms.Item.ClassifyInfo.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        $apiPath='Base.ItemModule.Classify.ClassifyInfo.lists';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $page       = $call['response']['page'];
        $pageNumber = $call['response']['page_number'];
        $totalnum   = $call['response']['totalnum'];
        
        //获取各个分类下的商品数量
        $classInfo = changeArrayIndex($call['response']['lists'],'id');
        $apiPath='Base.ItemModule.Classify.ClassifyInfo.classNum';
        $total = $this->invoke($apiPath);
        $total = $total['response'];
        foreach($classInfo as $key=>$val){
            if($total['on'][$key]){
                $classInfo[$key]['on']=$total['on'][$key]['classNum'];
            }else{
                $classInfo[$key]['on']=0;
            }
            if($total['off'][$key]){
                $classInfo[$key]['off']=$total['off'][$key]['classNum'];
            }else{
                $classInfo[$key]['off']=0;
            }
        }
//        if($page==1){
//            array_unshift($classInfo,$arr);
//        }
        $total = array(
            'classInfo'=>$classInfo,
            'page'=>$page,
            'pageNumber'=>$pageNumber,
            'totalnum'=>$totalnum
        );
        return $this->endInvoke($total);
    }
}