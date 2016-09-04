<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | cms后台商品相关模块
 */

namespace Bll\Cms\Item;

use System\Base;

class Item extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * Bll.Cms.Item.Item.mkItemCode
     * @param type $params
     * @return type
     */
    public function mkItemCode($params) {

        try {
            D()->startTrans();
            $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
            $data = array(
                'busType' => IC_ITEM,
                'preBusType' => IC_STANDARD_ITEM,
                'codeType' => SEQUENCE_ITEM,
            );
            $code_res = $this->invoke($apiPath, $data);
            if ($code_res['status'] != 0) {
                throw new \Exception('编码生成失败', $code_res['status']);
            }
            D()->commit();
            return $this->res($code_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(null, 4505);
        }
    }
     /**
     * Bll.Cms.Item.Item.batAdd
     * @param type $params
     * @return type
     */
    public function batAdd($params) {
        set_time_limit(0);
        $data = $params['data'];
        $sc_code = $params['sc_code'];
        $file_dir = $params['file_dir'];
        $fail_picture=array();
        //验证商品编码或者商品名称和规格
        $fail_data=array();
        foreach($data as $key=>$val){
            $data[$key]['sc_code']=$sc_code;
            if(!file_get_contents($file_dir."/{$val['sic_no']}.jpg")){
                $data[$key]['fail_message'][0]='对应图片不存在';
                $fail_picture[]=$data[$key];
                unset($data[$key]);
            }
        }
        foreach($data as $key=>$val){
            $checkApi = "Base.StoreModule.Item.Item.check";
            $result = $this->invoke($checkApi, $val);
            if($result['status']!==0){
                if($result['status']===4543){
                    $result['response']['fail_message'][0]='商品编码已存在';
                }else{
                    $result['response']['fail_message'][0]='商品已经存在';
                }
                $fail_data[]=$result['response'];
                unset($data[$key]);
            }
        }
        $total_fail=array_merge($fail_picture,$fail_data);
//        var_dump($total_fail);exit;
        if(!$total_fail){
            $total_fail=array();
        }
        if(!$data){
            return $this->endInvoke($total_fail);
        }
        foreach($total_fail as $key=>$val){
            foreach($data as $kk=>$vv){
                if($val['sic_no']==$vv['sic_no']){
                    unset($data[$kk]);
                }
            }
        }
        foreach ($data as $k => $v) {
            try {
                D()->startTrans();
                //添加标准库
                $apiPath = "Base.ItemModule.Item.Item.addStandard";
                $data = array(
                    'goods_name' => $v['goods_name'],
                    'sub_name' => $v['sub_name'] ? $v['sub_name'] : '',
                    'brand' => $v['brand'],
                    'spec' => $v['spec'],
                    'packing'=>$v['packing'],
                    'bar_code'=>$v['bar_code'] ? $v['bar_code'] : '',
                    'goods_img'=>$file_dir."/{$v['sic_no']}.jpg",
                    'goods_img_new'=>  json_encode(array($file_dir."/{$v['sic_no']}.jpg")),
                    'status'=>IC_ITEM_PUBLISH,
                    'is_standard'=>'NO',
                    'create_time'     => NOW_TIME,
                    'update_time'     => NOW_TIME,
                    'publish_time'    => NOW_TIME,
                );
                
                $standard_add_res  = $this->invoke($apiPath, $data);
                if ($standard_add_res['status'] != 0) {
                    $data[$k]['fail_message'][0]='数据插入失败';
                    $total_fail=array_merge($total_fail,$data[$k]);
                    return $this->endInvoke($total_fail, $standard_add_res['status']);
                }
                $ic_code = $standard_add_res['response'];
                //添加商家商品库
                $itemData = array(
                    'ic_code'          => $ic_code,
                    'sc_code'          => $sc_code,
                    'sub_name'         => empty($v['sub_name']) ? '' :$v['sub_name'] ,
                    'sic_no'           => $v['sic_no'],
                    'price'            => $v['price'] + 0,
                    'min_num'          => empty($v['min_num']) ? 1 : $v['min_num'],
                    'stock'            => $v['stock'] + 0,
                    'warn_stock'       => $v['warn_stock'],
                    'sort'             => 10000,
                    'source'           =>$v['source'],
                    'tag_ids'          => '',
                    'status'           => 'OFF',
                );
                $icItemApi      = "Base.ItemModule.Item.Item.addStoreItem";
                $icStoreItemRes = $this->invoke($icItemApi, $itemData);
                if ($icStoreItemRes['status'] != 0) {
                    $data[$k]['fail_message'][0]='数据插入失败';
                    $total_fail=array_merge($total_fail,$data[$k]);
                    return $this->endInvoke($total_fail, $icStoreItemRes['status']);
                }


                $qrcodeApi = 'Base.ItemModule.Item.Item.qrcode';
                $sic_code = $icStoreItemRes['response']['sic_code'];
                $itemData = array(
                    'sc_code' => $params['sc_code'],
                    'sic_code' => $sic_code,
                    'goods_img' =>$file_dir."/{$v['sic_no']}.jpg",
                );
//                var_dump($itemData);exit;
                $qrcodeRes = $this->invoke($qrcodeApi,$itemData);
                if($qrcodeRes['status']!==0){
                    $data[$k]['fail_message'][0]='数据插入失败';
                    $total_fail=array_merge($total_fail,$data[$k]);
                    return $this->endInvoke($total_fail,$qrcodeRes['status']);
                }
                $res = D()->commit();
                if($res === FALSE){
                    throw new \Exception('事务提交失败',17);
                }
                
            } catch (\Exception $ex) {
                D()->rollback();
                continue;
            }
        }
        return $this->endInvoke($total_fail);
    }

    /**
     * Bll.Cms.Item.Item.add
     * @param type $params
     * @return type
     */
    public function add($params){
//        var_dump($params);exit;
        try{
            D()->startTrans();
            //验证商品编码或者商品名称和规格
            $checkApi = "Base.StoreModule.Item.Item.check";
            $checkRes = $this->invoke($checkApi, $params);
            if ($checkRes['status'] !=0) {
                return $this->endInvoke(null, $checkRes['status']);
            }

            //照片数量判断
            $imgNum = count(json_decode($params['goods_img_new'], TRUE));
            if ($imgNum > 5 || $imgNum < 0) {
                return $this->endInvoke(NULL, 4521);
            }
            //添加标准库
            $stardandData = array(
                'goods_name'      => $params['goods_name'],
                'sub_name'        => empty($params['sub_name']) ? '' :$params['sub_name'],
                'brand'           => $params['brand'] ,
                'spec'            => empty($params['spec']) ? '' :$params['spec'],
                'packing'         => empty($params['packing']) ? '' :$params['packing'] ,
                'bar_code'        => empty($params['bar_code']) ? '' :$params['bar_code'] ,
                'goods_img'       => empty($params['goods_img']) ? '' :$params['goods_img'] ,
                'goods_img_new'   => empty($params['goods_img_new']) ? '' :$params['goods_img_new'],
                'status'          => 'PUBLISH',
                'is_standard'     => 'NO',
                'create_time'     => NOW_TIME,
                'update_time'     => NOW_TIME,
                'publish_time'    => NOW_TIME,
            );
            $stardandApi = "Base.ItemModule.Item.Item.addStandard";
            $stardandRes = $this->invoke($stardandApi, $stardandData);
            if ($stardandRes['status'] != 0) {
                return $this->endInvoke(null, $stardandRes['status']);
            }
            //添加商家商品库
            $itemData = array(
                'ic_code'          => $stardandRes['response'],
                'sc_code'          => $params['sc_code'],
                'sub_name'         => empty($params['sub_name']) ? '' :$params['sub_name'] ,
                'sic_no'           => $params['sic_no'],
                'price'            => $params['price'] + 0,
                'min_num'          => empty($params['min_num']) ? 1 : $params['min_num'],
                'stock'            => $params['stock'] + 0,
                'warn_stock'       => $params['warn_stock'],
                'sort'             => $params['sort'] ? $params['sort'] : 10000,
                'source'           =>$params['source'],
                'status'           => $params['status'],
            );
            $icItemApi      = "Base.ItemModule.Item.Item.addStoreItem";
            $icStoreItemRes = $this->invoke($icItemApi, $itemData);

            if ($icStoreItemRes['status'] != 0) {
                return $this->endInvoke(NULL, $icStoreItemRes['status']);
            }
            $qrcodeApi = 'Base.ItemModule.Item.Item.qrcode';
            $sic_code = $icStoreItemRes['response']['sic_code'];
            $itemData = array(
                'sc_code' => $params['sc_code'],
                'sic_code' => $sic_code,
                'goods_img' => $params['goods_img'],
            );

            $qrcodeRes = $this->invoke($qrcodeApi,$itemData);
            D()->commit();
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4517);
        }
    }
    /**
     * Bll.Cms.Item.Item.check
     * @param type $params
     * @return type
     */
    public function check($params){
        $apiPath='Base.ItemModule.Item.ItemInfo.check';
        $res=$this->invoke($apiPath,$params);
        if($res['status']==4543){
            return $this->endInvoke('',$res['status']);
        }
        return $this->endInvoke(true);
    }
    /**
     * Bll.Cms.Item.Item.update
     * @param type $params
     * @return type
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath = "Base.ItemModule.Item.Item.updateStoreItem";
            $select_res = $this->invoke($apiPath, $params);
            if($select_res['status'] != 0){
                return $this->endInvoke(NULL, $select_res['status']);
            }
            D()->commit();
            return $this->res(true);
        } catch (\Exception $ex) {
            D()->rollback();
        }
    }
    /**
     * @api Bll.Cms.Item.Item.itemStatictis
     * @param $params
     */
    public function itemStatictis($params)
    {   
        $apiPath = "Base.StoreModule.Item.Statistic.itemStatistic";
        $storeItemStatictisRes = $this->invoke($apiPath, $params);
        if ($storeItemStatictisRes['status'] != 0) {
            return $this->endInvoke('', $storeItemStatictisRes['status']);
        }
        return $this->endInvoke($storeItemStatictisRes['response']);
    }

    /**
     * Bll.Cms.Item.Item.export
     * @return [type] [description]
     */
    public function export($params){
        
        $apiPath = "Base.StoreModule.Item.ItemExport.cmsExport";
        $storeItemStatictisRes = $this->invoke($apiPath, $params);
        if ($storeItemStatictisRes['status'] != 0) {
            return $this->endInvoke('', $storeItemStatictisRes['status']);
        }
        return $this->endInvoke($storeItemStatictisRes['response']);
    }

    /**
     * Bll.Cms.Item.Item.getStoreList
     * @return [type] [description]
     */
    public function getStoreList(){
        //获取商家列表
        $apiPath  = "Base.StoreModule.Basic.Store.lists";
        $storeLists = $this->invoke($apiPath);
        return $this->endInvoke($storeLists['response']);
    }
    /*
    public function searchTest($params) {
        $apiPath = 'Com.DataCenter.Statistic.Order.searchKey';
        $rows = $this->invoke($apiPath,$params);
        return $this->endInvoke($rows['response']);
    }
    */
     /**
     * Bll.Cms.Item.Item.setClassByCodes
     * @return [type] [description]
     */
    public function setClassByCodes($params) {
        try {
            D()->startTrans();
            $apiPath = "Base.ItemModule.Item.Item.setClassByCodes";
            $set_status = $this->invoke($apiPath, $params);
            if($set_status['status'] != 0){
                return $this->endInvoke($set_status['message'],$set_status['status']);
            }
            D()->commit();
            return $this->res($set_status['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4572);
        }
    }
}

?>
