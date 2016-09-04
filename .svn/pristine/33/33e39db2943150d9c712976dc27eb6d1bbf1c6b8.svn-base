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

namespace Bll\Cms\Search;

use System\Base;

class Search extends Base {

    public function __construct() {
        parent::__construct();
    }
    public function searchData($params) {
        $apiPath = 'Com.DataCenter.Search.Search.searchKey';
        $rows = $this->invoke($apiPath,$params);
        return $this->endInvoke($rows['response']);
    }

}