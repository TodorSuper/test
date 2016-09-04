<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b注册模块
 */

namespace Test\Bll\CmsStore;
use System\Base;

class Store extends Base {
    /**
     * 获取商家统计列表
     * Test.Bll.CmsStore.Store.lists
     * @param type $params
     */
      public function lists(){
          $data=array(
//              'merchant_name'=>'余氏福翔商贸有限公司',
//              'sc_code'=>1010000000081
          );
          $apiPath='Bll.Cms.Store.StoreInfo.lists';
          $res= $this->invoke($apiPath,$data);
          return $this->res($res['response']);
      }
    /**
     * 导出商品统计明细
     * Test.Bll.CmsStore.Store.export
     * @param type $params
     */
    public function export(){
        $data=array(
//              'merchant_name'=>'余氏福翔商贸有限公司',
//                         'start_time'=>0,
//            'end_time'=>NOW_TIME,
        );
        $apiPath='Bll.Cms.Store.StoreInfo.export';
        $res= $this->invoke($apiPath,$data);
        return $this->res($res['response']);
    }
    /**
     *
     * Test.Bll.CmsStore.Store.count_list
     * @param type $params
     */
    public function count_list(){
        $data=array(

        );
        $apiPath='Bll.Cms.Store.StoreInfo.count_list';
        $res= $this->invoke($apiPath,$data);
        return $this->res($res['response']);
    }
}

?>
