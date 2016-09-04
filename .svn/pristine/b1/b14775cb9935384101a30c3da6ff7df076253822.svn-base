<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 满赠信息
 */

namespace Test\Base\SpcGift;

use System\Base;

class GiftInfo extends Base {

   

    /**
     * 计算赠品的件数
     * Test.Base.SpcGift.GiftInfo.calculate
     * @param type $params
     * @return type
     * Author: zhoulianlei <zhoulianlei@liangrenwang.com >
     */
    public function calculate($params) {
        $apiPath = "Base.SpcModule.Gift.GiftInfo.calculate";
        $rule = array(
            array(10,1),
            array(20,3),
            array(50,10),
        );
        $data = array(
            'spc_code' => '11210000050',
            'goods_number'=>151,
            'rule' =>$rule,
        );
        $res = $this->invoke($apiPath,$data);
        print_r($res);
    }

}
