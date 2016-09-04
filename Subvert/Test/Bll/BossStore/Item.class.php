<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\BossStore;

use System\Base;
class Item extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 商品列表接口
     * Test.Bll.BossStore.Item.lists
     * @param type $params
     */

    public function lists($params){
    	$apiPath = "Bll.Boss.Store.Item.lists";
        // $apiPath = "Base.OrderModule.Boss.Order.all";
        $params = array(
            'sc_code' =>'1020000000026',
            // 'search_name'=>'',
            'pageNumber'=>'1',
            'pageSize'=>'20',
            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }


    /**
     * 商品更改状态接口
     * Test.Bll.BossStore.Item.update
     * @param type $params
     */
    public function update($params){

        $apiPath = "Bll.Boss.Store.Item.update";
        // $apiPath = "Base.OrderModule.Boss.Order.month";
        $params = array(
            'sic_code'=>'12200001909',
            'sc_code'=>'1020000000026',
            'status'=>'OFF',
            'goods_name'=>'a火腿',
            'sic_no'=>'aasa1',
            'spec'=>'a12*12',
            'packing'=>'a箱',
            'brand'=>'a嘻哈',
            'price'=>'10.01',
            'stock'=>'112',
            'store_sub_name'=>'a谁知道',
            'min_num'=>'111',
            'goods_imgs'=>array('http://cdn.liangren.com/S1020000000026/20150906/YS203002.jpg'),
        );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }


    /**
     * 检查sic_no存不存在
     * Test.Bll.BossStore.Item.checkSicNo
     * @author Todor
     * @access public
     */

    public function checkSicNo($params){

        $apiPath = "Bll.Boss.Store.Item.checkSicNo";
        $params = array(
            'sc_code'=>'1020000000026',
            'sic_no'=>'YS2030201',
            );

        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);

    }


    /**
     * 品牌联动效果
     * Test.Bll.BossStore.Item.getBrands
     * @author Todor
     * @access public
     */

    public function getBrands(){
        $apiPath = "Bll.Boss.Store.Item.getBrands";
        $params = array(
            'sc_code'=>'1020000000026',
            'brand'=>'TC',
            );

        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }


    /**
     * 商品增加
     * Test.Bll.BossStore.Item.add
     * @author Todor
     * @access public
     */

    public function add($params){
        
        $params = array(
            'sc_code'=>'1020000000026',
            'status'=>'OFF',
            'goods_name'=>'火腿',
            'sic_no'=>'sysy111111111111',
            'spec'=>'12*12',
            'packing'=>'箱',
            'brand'=>'嘻哈',
            'price'=>'0.01',
            'stock'=>'12',
            'store_sub_name'=>'谁知道',
            'min_num'=>'11',
            'goods_imgs'=>array('http://cdn.liangren.com/S1020000000026/20150906/YS221002.jpg'),
        );

        $apiPath = "Bll.Boss.Store.Item.add";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response']);
    }


    /**
     * 商品获取
     * Test.Bll.BossStore.Item.get
     * @author Todor
     * @access public
     */

    public function get($params){
        $params = array(
            'sic_code'=>'12200001913',
            'sc_code'=>'1020000000026',
            );
        $apiPath = "Bll.Boss.Store.Item.get";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response']);
    }

}























 ?>