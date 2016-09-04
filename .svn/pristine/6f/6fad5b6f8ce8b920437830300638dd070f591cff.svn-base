<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 区域操作模块
 */

namespace Com\Tool\Region;

use System\Base;

class Region extends Base {

    public function __construct() {
        parent::__construct();
    }
    
     /**
     * 获取下一级的省市区列表  1  则为省
     * Com.Tool.Region.Region.getAreaBuyPid
     * @param type $params
     */
    public function getAreaBuyPid($params){
        $params['pid'] = $params['pid'] + 0;
        $this->_rule = array(
            array('pid', 1, PARAMS_ERROR, MUST_CHECK,'egt'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $areaList = D('Region')->where(array('pid'=>$params['pid']))->field('id,name,pid')->select();
        return $this->res($areaList);
    }

    

}
