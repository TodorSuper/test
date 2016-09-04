<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b注册模块
 */

namespace Test\Bll\PopSpc;
use System\Base;

class Status extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Spc';
    }

    /**
     * 批量发布和批量结束
     * Test.Bll.PopSpc.CenterInfo.setStatus
     * @param type $params
     */
    public function setStatus($params)
    {
//        $mobile = '15631129607';
        $apiPath = "Bll.Pop.Spc.Status.setStatus";
        $data = array(
            'opera_status' => 'PUBLISH',
            'spc_codes' => array('11210000046', '11210000038', '11210000039','11210000040','11210000041'),
        );
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }
}