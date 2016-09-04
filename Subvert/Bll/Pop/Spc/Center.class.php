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
namespace Bll\Pop\Spc;

use System\Base;
class Center extends Base {

    public $spcType = array('REWARD_GIFT', 'SPECIAL','LADDER');

    public function __construct(){
        parent::__construct();
    }
    /**
     * Bll.Pop.Spc.Center.delay
     * 延长促销时间
     * @param type $params
     */

    public function delay($params){
        $apiPath='Base.SpcModule.Center.Spc.batDelay';
        $response=$this->invoke($apiPath,$params);
        if($response['status']!=0){
            $this->endInvoke(null,$response['status'],'',$response['message']);
        }
        $this->endInvoke($response['response']);
    }
    /**
     * zhangyupeng
     * Bll.Pop.Spc.Center.add
     * @param [type] $params [description]
     */
    public function add($params){

        //库存信息判断
        $mark = $this->stock($params, $params['type']);
        if(!is_array($mark)){
                return $this->endInvoke(NULL, $mark);
        }
        $goods_price = $mark['price'];
        
        $params['data'] = json_decode($params['data'], TRUE);
        $params['goods_price'] = $goods_price;
        try{

            D()->startTrans();
            $apiPath = "Base.SpcModule.Center.Spc.add";
            $res     = $this->invoke($apiPath, $params);

            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }

            $commit_res = D()->commit();

            if ($commit_res === FALSE) {

                return $this->endInvoke(NULL, 7013);
            }

            return $this->endInvoke(TRUE);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7013);
        }

    }

    /**
     * zhangyupeng
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function stock($params, $type){
        //促销商品信息
        $apiPath  = 'Base.StoreModule.Item.Item.getStoreItem';  

        $data = json_decode($params['data'],TRUE);
        if (!in_array($type, $this->spcType)) {
            return 7032;
        }
        

        //满赠上家和库存判断
        if ($type === 'REWARD_GIFT') {
            $params['sic_code'] = $data['sale_sic_code'];
            $gData['sic_code'] = $data['gift_sic_code'];
            $gData['sc_code']  = $params['sc_code'];
            $giftData = $this->invoke($apiPath, $gData);
            if($giftData['response']['store_status'] !== IC_STORE_ITEM_ON){
               return 7019;
            }
            if ($giftData['response']['stock'] <= 0) {
               return 7017;  
            }
        }
        
        $sData['sic_code'] = $params['sic_code'];
        $sData['sc_code']  = $params['sc_code'];
        $saleData = $this->invoke($apiPath, $sData);
        //上架判断
        if($saleData['response']['store_status'] !== IC_STORE_ITEM_ON){
           return 7018;
        }
         //促销库存判断
        if ($saleData['response']['stock'] <= 0) {
           return 7015;
        }
       

        return $saleData['response'];
    }

    /**
     * Bll.Pop.Spc.Center.update
     * @param [type] $params [description]
     */

    public function update($params){
        //库存判断
        // L($params);
        // if(!empty($params['isUpdateGift'])){
        //     $api          = 'Base.SpcModule.Center.Spc.getStatusBySpcCode';
        //     $data         = $this->invoke($api, $params['spc_code']);
        //     $api2         = 'Base.SpcModule.Center.Spc.getGiftBySpcCode';
        //     $data['data'] =  $this->invoke($api2, $params['spc_code']);
        // }
        // else{
        
        if ($params['time_type']) {
            unset($params['time_type']);
        }else{
            $mark = $this->stock($params, $params['type']);
            if(!is_array($mark)){
                return $this->endInvoke(NULL, $mark);
            }
            $goods_price = $mark['price'];
            
        }
        // }
        // else{
        //     $data = $params;
        //     $rules = $params['data']['rule'];
        //     $apiRule = 'Base.SpcModule.Gift.Gift.rule';
        //     $isRule =  $this->invoke($apiRule, $rules);
        //     if(!is_array($isRule)){
        //         return $this->endInvoke(NULL, $isRule);
        //     }
        // }
        
        
        
        $params['data'] = json_decode($params['data'], TRUE);
        $params['goods_price'] = $goods_price;
        try{

            D()->startTrans();
            $apiPath = "Base.SpcModule.Center.Spc.update";

            $res = $this->invoke($apiPath, $params);
            ;
            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {;

                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke($res['response']);
        }catch (\Exception $ex) {

            D()->rollback();
            return $this->endInvoke(NULL, 4020);
        }

    }
}