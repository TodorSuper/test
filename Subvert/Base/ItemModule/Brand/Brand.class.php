<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 店铺品牌相关模块
 */

namespace Base\ItemModule\Brand;

use System\Base;

class Brand extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }


    /**
     * 获取商家全部品牌
     * Base.ItemModule.Brand.Brand.brands
     * @param type $params
     * @return array
     */
    public function brands($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           # 店铺编码
            array('status',array(IC_STORE_ITEM_ON,IC_STORE_ITEM_OFF), PARAMS_ERROR, ISSET_CHECK, 'in'),  # 上架与下架状态
            array('stock_gt', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), # 库存是否大于最小起够数
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK),                  # 品牌筛选
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        !empty($params['sc_code']) && $where['isi.sc_code'] = $params['sc_code'];
        !empty($params['status']) && $where['isi.status'] = $params['status'];
        !empty($params['stock_gt']) && $params['stock_gt'] == "YES" && $where['isi.stock'] = array('exp','> isi.min_num');
        !empty($params['brand']) && $where['ii.brand'] = array('like',"%".$params['brand']."%");
        $field = "ii.brand";

        $brands = D('IcStoreItem')->field($field)->alias('isi')
                        ->join("{$this->tablePrefix}ic_item ii ON ii.ic_code = isi.ic_code",'LEFT')
                        ->where($where)
                        ->group('ii.brand')
                        ->select();
        $brands = array_filter(array_column($brands,'brand'));
        return $this->res($brands);
    }





}











 ?>