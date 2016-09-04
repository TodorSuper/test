<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | 
 * +---------------------------------------------------------------------
 * | 获取促销信息
 */

namespace Test\Base\SpcCenter;

use System\Base;

class Spc extends Base {

    


    /**
     * Test.Base.SpcCenter.Spc.getCoupon
     * @param type array sic_code
     * @access public
     * @author Todor
     */

    public function getCoupon($params){
        
        try{
            D()->startTrans();
            $apiPath = "Base.SpcModule.Coupon.Center.getCoupon";
            $data = array(
                'flag' => SPC_ACTIVE_CONDITION_FLAG_REGISTER,
                'uc_code'=>'11111',
            );
            $res = $this->invoke($apiPath,$data);
            D()->commit();
            return $this->res($res['response'],$res['status'],'',$res['message']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res();
        }
    }
    


}

?>