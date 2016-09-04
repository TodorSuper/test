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

class CenterInfo extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Spc';
    }

    /**
     * 促销列表
     * Test.Bll.PopSpc.CenterInfo.lists
     * @param type $params
     */
    public function lists($params)
    {
//        $mobile = '15631129607';
        $apiPath = "Bll.Pop.Spc.CenterInfo.lists";
        $data = array(
            'uc_code' => 1120000000103,
            'sc_code' => 1020000000026,
            'status'=>array('DRAFT'),
//            'search_spc'=>'东古蒸鱼豉油',
//            'sic_codes' => array(12200000306),
//            'sic_code'=>11200000006,
//            'goods_name'=>'东古生抽王',
//            'start_time' => 1441036800,
//            'end_time' => 1441036809,
        );
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }

    /**
     * 注册用户
     * Test.Bll.PopSpc.CenterInfo.export
     * @param type $params
     */
    public function export($params){
//        $mobile = '15631129607';
        $apiPath = "Bll.Pop.Spc.CenterInfo.export";
        $data = array(
            'uc_code'=>1120000000103,
            'sc_code'=>1020000000026,
            'search_spc'=>'东古蒸鱼豉油',
//            'status'=>array('DRAFT'),
//            'sic_codes'=>array(12200000306),
//            'sic_code'=>11200000006,
//            'goods_name'=>'东古生抽王',
//                'start_time'=>'2015-2-1',
//                'end_time'=>'2015-11-1',
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }
    /**
     * 修改促销商品
     * Test.Bll.PopSpc.CenterInfo.update
     * @param type $params
     */
    public function update ($params) {
        $apiPath = "Bll.Pop.Spc.Center.update";
        $data = array(
            'spc_title'=>'eeeeee',
            'spc_code' =>'11210000109',
            'sic_code'=> '12200000040',
            'sc_code'=> '1020000000026',
            'data'=>array('gift_sic_code'=>12200000040,'rule'=>'[[10,1],[20,3],[50,10]]'),
            'start_time'=>'1440500647',
            'end_time'=>'1440500647',
           // 'type'=>"REWARD_GIFT",
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }

    /**
     * 延长促销
     * Test.Bll.PopSpc.CenterInfo.updateTime
     * @param type $params
     */
    public function updateTime ($params) {
        $apiPath = "Bll.Pop.Spc.Center.update";
        $data = array(
            'spc_title'=>'111111',
            'spc_code' =>'11210000142',
             'start_time'=>'1438704000',
             'end_time'=>'1444320000',
           // 'sc_code' =>'1020000000026',
            'isUpdateGift'=>'NO',
            // 'type'=>"REWARD_GIFT",
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }
    /**
     * Test.Bll.PopSpc.CenterInfo.search
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function search($params){
         $params = array(
             'sc_code'    => 1020000000026,
             'goods_name' => '',
             'sic_no'     => 1220,
             'sic_code' => '12200000035',
             );

        $sc_code = $params['sc_code'];
        if (empty($sc_code)) {
          return $this->endInvoke('', 5);
        }
        $params['limit'] = 5;
        $apiPath = 'Base.StoreModule.Item.Item.searchConItems';
        $res = $this->invoke($apiPath, $params);
        $this->endInvoke($res);
    }

    public function orderTest(){
        $apiPath = "Bll.Pop.Order.OrderInfo.get";
        $data = array(
            'sc_code' =>'1020000000026',
            'b2b_code' =>'12200001294',
            // 'type'=>"REWARD_GIFT",
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }

    public function orderGet(){
        $apiPath = "Bll.Pop.Spc.CenterInfo.get";
        $data = array(
            'spc_code' =>'11210000148',
            'sc_code' =>'1020000000026',
            //'b2b_code' =>'12200052001',
            // 'type'=>"REWARD_GIFT",
        );
        $res  = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }

    public function orderData(){
        $apiPath = "Bll.Pop.Order.Export.export";
        $data = Array ( 'export_flag' => 'order',
            'b2b_code' =>'',
            'start_time' => '',
            'end_time' =>'',
            'uc_code'=> '1120000000103',
            'sc_code' => 1020000000026,
            'tc_code' => 1020000000073 );
        $res  = $this->invoke($apiPath, $data);
        return $this->res($res['response']);
    }
}

?>
