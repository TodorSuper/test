<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 编码统一生成
 */

namespace Test\Com\ToolCode;

use System\Base;

class Sms extends Base {
    public function __construct() {
        parent::__construct();
        
	}
   /**
    * Test.Com.ToolCode.Sms.smsTest
    * @param type $params
    */     
    public function smsTest($params){
        
        $numbers = array('18625696323');
        $apiPath = "Com.Common.Message.Sms.send";
        for($i = 0;$i<count($numbers);$i++){
            $data = array(
            'sys_name'=>B2B,
            'numbers'=>array($numbers[$i]),
            'message'=>'您的验证码为125',
        );
        $res = $this->invoke($apiPath,$data);
        }
        
        print_r($res);
    }
    

}
