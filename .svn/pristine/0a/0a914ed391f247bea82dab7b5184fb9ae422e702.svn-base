<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | Boss版登陆
 */

namespace Bll\Boss\Stasitc;

use System\Base;
class Customer extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



    /**
     * @api  Boss版商品列表接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Stasitc.Customer.all
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function all($params){

        $apiPath = "Base.OrderModule.B2b.Statistics.customer";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }

        $res['response']['pageTotalItem'] = $res['response']['totalnum'];
        $res['response']['pageTotalNumber'] = $res['response']['total_page'];
        unset($res['response']['totalnum']);
        unset($res['response']['page_number']);
        unset($res['response']['page']);
        unset($res['response']['total_page']);
        return $this->endInvoke($res['response']);
    }






}

?>