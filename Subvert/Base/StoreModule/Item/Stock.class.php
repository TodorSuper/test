<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺库存相关模块
 */

namespace Base\StoreModule\Item;

use System\Base;

class Stock extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * 
     * 减库存
     * Base.StoreModule.Item.Stock.changeStock
     * @param type $params
     */
    public function changeStock($params){
       $this->startOutsideTrans();
       $this->_rule = array( 
           array('sic_code','require',PARAMS_ERROR,MUST_CHECK),
           array('number','require',PARAMS_ERROR,ISSET_CHECK), 
           array('sc_code','require',PARAMS_ERROR,MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sic_code = $params['sic_code']; //商家商品编码
        $number   = $params['number'] + 0;  // 改变的数量  负数  则为减
        $sc_code  = $params['sc_code'];  // 商家编码
        $where = array(
            'sic_code'=>$sic_code,
            'sc_code'=>$sc_code,
        );
        
        //如果是减库存   则  要判断库存是否够减
        if($number <= 0){
            $other_number   = $params['other_number'];
            
//            $where['stock'] = array('egt', abs($number));
            $number  = $number + $other_number;
        }
        $res = D('IcStoreItem')->where($where)->save(array('stock'=>array('+',$number),'update_time'=>NOW_TIME));
        if($res <= 0 || FALSE === $res){
            return $this->res(null,4514);
        }
        return $this->res($res);
    }
}

?>
