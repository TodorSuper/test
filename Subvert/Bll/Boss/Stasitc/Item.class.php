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
class Item extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



    /**
     * @api  Boss版商品列表接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Stasitc.Item.hot
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function hot($params){
        $apiPath = "Base.OrderModule.B2b.Statistics.hot";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }

        // 金钱千分位
        foreach ($res['response']['item_data'] as $k => $v) {
            $res['response']['item_data'][$k]['amount'] = $v['amount'] < 100000000 ? number_format($v['amount'],2) : "9999,9999.99";
            $res['response']['item_data'][$k]['num']    = $v['num'] < 100000000 ? $v['num'] : "99999999";
        }
        return $this->endInvoke($res['response']);
    }






}

?>