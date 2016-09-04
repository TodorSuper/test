<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | pop促销中心数据导出
 */
namespace Bll\Pop\Spc;

use System\Base;

class Status extends Base{

    public function setStatus($params){
        $apiPath='Base.SpcModule.Center.Status.setStatus';
        $res=$this->invoke($apiPath,$params);
        $this->endInvoke($res['response'],$res['status'],'',$res['message']);
    }
    
}