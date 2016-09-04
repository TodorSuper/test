<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangguangjian <wangguangjian@yunputong.com >
 * +---------------------------------------------------------------------
 * | 促销相关
 */
namespace Test\Bll\PopSpc;

use System\Base;
class Center extends Base {

    /**Test.Bll.PopSpc.Center.changePrice
     * yindongyang
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
     public function changePrice(){
         $apiPath='Bll.Pop.Order.OrderInfo.changePrice';

         $goods_prices=array(null,null,110);
         $spc_code=array('','11310000191','');
         $sic_codes=array('12200002559','12200002182','12200002182');
         $info=array();
         foreach($goods_prices as $k=>$v){
                 foreach($sic_codes as $kkk=>$vvv){
                     foreach($spc_code as $key=>$val){
                         if($k==$kkk && $kkk==$key){
                             $info[]=array(
                                 'goods_price'=>$v,
                                 'sic_code'=>$vvv,
                                 'spc_code'=>$val,
                             );
                         }
                 }
             }
         }
         $data=array(
             'info'=>$info,
             'b2b_code'=>12200002708,
             'pay_method'=>PAY_METHOD_ONLINE_WEIXIN,
             'sc_code'=>1010000000077,
         );
         foreach($data['info'] as $key=>$val){
             if(!$val['goods_price']){
                 unset($data['info'][$key]);
             }
         }
         $res=$this->invoke($apiPath,$data);
         return $this->res($res);
     }
    /**
     * Test.Bll.PopSpc.Center.delay
     * 延长促销时间
     * @param type $params
     */

    public function delay(){
        $apiPath='Bll.Pop.Spc.Center.delay';
        $data=array(
            'spc_codes'=>array('11210000119','11210000130','12210000115'),
             'end_time'=>1443628799,
            'sc_code'=>1020000000026,
        );
        $res=$this->invoke($apiPath,$data);
        return $this->res($res);
    }

    /**
     * Test.Bll.PopSpc.Center.addCommodity
     * 延长促销时间
     * @param type $params
     */

    public function addCommodity(){
        $apiPath='Bll.Pop.Spc.Commodity.add';
        $data=array(
           'commodity_title'=>'订货会',
            'sc_code'=>1020000000026,
            'min_advance'=>100,
            'start_time'=>1441635495,
            'end_time'=>1441637495
        );
        $res=$this->invoke($apiPath,$data);
        return $this->res($res);
    }
    /**
     * Test.Bll.PopSpc.Center.updateCommodity
     * 延长促销时间
     * @param type $params
     */

    public function updateCommodity(){
        $apiPath='Bll.Pop.Spc.Commodity.update';
        $data=array(
            'sc_code'=>1020000000026,
            'spc_code'=>21210000207,
//            'start_time'=>5,
//            'end_time'=>8,
            'status'=>'END',
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME
        );
        $res=$this->invoke($apiPath,$data);
        return $this->res($res);
    }
    /**
     * ZHANGYUPENG
     * Test.Bll.PopSpc.Center.add
     * @param [type] $params [description]
     */
    public function add($params){
        $params = array(
                    'sc_code'       => 1010000000077,
                    'spc_title'     => '商品阶梯价测试',
                    'sic_code'      => 12200002961,
                    'data'          => json_encode(array(
                            'sic_code' => 12200002961,
                            'rule'          => array(array(1,10,15),array(11,20,12),array(21,30,10)),
                                )),
                    'type'       => SPC_TYPE_LADDER,
                    'status' => 'PUBISH',
                    'start_time'=>NOW_TIME,
                    'end_time'=>NOW_TIME + 3600*24,
                        );
       
       $apiPath = "Bll.Pop.Spc.Center.add";
       $res = $this->invoke($apiPath,$params);
    }

    /**
     * zhagnyupeng
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function stock($params){
        
        //促销商品信息
        $itemData['sic_code'] = $params['sic_code'];
        $itemData['sc_code']  = $params['sc_code'];

        $apiPath  = 'Base.StoreModule.Item.Item.getStoreItem';
        $ItemData = $this->invoke($apiPath, $itemData);
        
        //赠品信息
        $gData['sic_code'] = $params['data']['gift_sic_code'];
        $gData['sc_code']  = $params['sc_code'];
        $giftData = $this->invoke($apiPath, $gData);
        // var_dump($ItemData);
        // echo $ItemData['response']['store_status'];
        // echo IC_STORE_ITEM_ON;
        //商品发布判断
        if($ItemData['response']['store_status'] != IC_STORE_ITEM_ON){
            return 7018;
        }

        if($giftData['response']['store_status'] != IC_STORE_ITEM_ON){
            return 7019;
        }

        //促销库存判断
        if ($ItemData['response']['stock'] < 0) {
            return 7015;
        }

        if ($giftData['response']['stock'] < 0) {
            return 7017;  
        }

        return TRUE;
    }
    
}