<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangliangliang <wangliangliang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 获取促销信息
 */

namespace Test\Base\SpcCenter;

use System\Base;

class SpcInfo extends Base {

    


    /**
     * B2B商品列表对应促销信息
     * Test.Base.SpcCenter.SpcInfo.spcInfo
     * @param type array sic_code
     * @access public
     * @author Todor
     */

    public function spcInfo($params){
        $apiPath = "Base.SpcModule.Center.SpcInfo.spcInfo";
        $data = array(
            'sic_code' => array('12200000035'),
        );
        $res = $this->invoke($apiPath,$data);
        print_r($res);
    }
    
    /**
     * B2B商品列表对应促销信息
     * Test.Base.SpcCenter.SpcInfo.getSpcTest
     * @param type array sic_code
     * @access public
     * @author Todor
     */
    public function getSpcTest($params){
        $data = array(
            array('sic_code'=>'12200000260','goods_name'=>'古怪','price'=>70),
            array('sic_code'=>'12200000240','goods_name'=>'古怪'),
            
        );
        $res = $this->getSpc($data, '1020000000026','YES','1220000000268');
        print_r($res);
    }

}

?>