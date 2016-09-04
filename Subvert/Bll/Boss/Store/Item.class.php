<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | Boss版登陆
 */

namespace Bll\Boss\Store;

use System\Base;
class Item extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



    /**
     * @api  Boss版商品列表接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Item.lists
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function lists($params){

        // 输入的字符必须小于15个
        if(mb_strlen(trim($params['search_name'])) > 15){
            return $this->endInvoke(NULL,4528);
        }
        
        $data = array(
            'sc_code'=>$params['sc_code'],
            'page'=>$params['pageNumber'],
            'page_number'=>$params['pageSize'],
            'sysName' => BOSS,
            );
        !empty($params['search_name']) && $data['goods_name'] = trim($params['search_name']);
        $apiPath = "Base.ItemModule.Item.ItemInfo.storeItems";
        $res = $this->invoke($apiPath,$data);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }

        //  用户设置信息
        $apiPath = "Base.UserModule.User.User.getOptions";
        $options_res = $this->invoke($apiPath,$params);
        if($options_res['status'] != 0){
            return $this->endInvoke(NULL,$options_res['status'],'',$options_res['message']);
        }


        // 整理数据
        $temp = array();
        $temp['pageTotalItem'] = $res['response']['totalnum'];
        $temp['pageTotalNumber'] = $res['response']['total_page'];
        $temp['show_img'] = $options_res['response']['show_img'];
        $temp['need_check'] = array('goods_name','store_sub_name');
        foreach ($res['response']['lists'] as $k => $v) {
            $temp['lists'][$k] = array(
                'sic_code'       =>$v['sic_code'],
                'goods_img'      =>$v['goods_img'],
                'goods_name'     =>$v['goods_name'],
                'spec'           =>$v['spec'],
                'packing'        =>$v['packing'],
                'store_sub_name' =>$v['store_sub_name'],
                'price'          =>$v['price'],
                'min_num'        =>$v['min_num'],
                'stock'          =>$v['stock'],
                'store_status'   =>$v['store_status'],
                );
        }

        return $this->endInvoke($temp);

    }


    /**
     * @api  Boss版商品更改状态接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Item.update
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function update($params){
        // 规则校验
        if(!empty($params['goods_name'])){
            $this->_checkRule($params);
        }
        
        try {
            D()->startTrans();
            $params['sub_name'] = $params['store_sub_name'];

            if(is_array($params['goods_imgs']) && !empty($params['goods_imgs'])){
                $params['goods_img_new'] = json_encode($params['goods_imgs']);
                $params['goods_img']     = $params['goods_imgs'][0];
            }
            unset($params['store_sub_name']);
            unset($params['goods_imgs']);
            $apiPath = "Base.ItemModule.Item.Item.updateStoreItem";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                return $this->endInvoke(NULL,$res['status'],'',$res['message']);
            }
            D()->commit();
            return $this->endInvoke(TRUE);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4503);
        }

    }


    /**
     * @api  编码焦点时候判断编码存不存在
     * @apiVersion 1.1.0
     * @apiName Bll.Boss.Store.Item.checkSicNo
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-17
     * @apiSampleRequest On
     */


    public function checkSicNo($params){
        // 如果为空不判断
        if(empty($params['sic_no'])){
            return $this->endInvoke(TRUE);
        }
        $apiPath = "Base.ItemModule.Item.ItemInfo.check";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }        
        return $this->endInvoke(true);
    }



    /**
     * @api  品牌联动效果
     * @apiVersion 1.1.0
     * @apiName Bll.Boss.Store.Item.getBrands
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-17
     * @apiSampleRequest On
     */

    public function getBrands($params){
        $apiPath = "Base.ItemModule.Brand.Brand.brands";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }
        return $this->endInvoke($res['response']);
    }


    /**
     * @api  商品添加
     * @apiVersion 1.1.0
     * @apiName Bll.Boss.Store.Item.add
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-17
     * @apiSampleRequest On
     */

    public function add($params){

        // 校验规则
        $this->_checkRule($params);
        try {
            D()->startTrans();

            // 检查商品和规格是否同时存在 或商家商品编码存在
            $check_params = array(
                'sc_code'=>$params['sc_code'],
                'sic_no'=>$params['sic_no'],
                'goods_name'=>$params['goods_name'],
                'spec'=>$params['spec'],
                );
            $apiPath = "Base.ItemModule.Item.ItemInfo.check";
            $check_res = $this->invoke($apiPath,$params);
            if($check_res['status'] != 0 ){
                return $this->endInvoke(NULL,$check_res['status']);
            }

            // 添加标准库
            $ic_item_params = array(
                'goods_name'    =>$params['goods_name'],
                'sub_name'      =>$params['store_sub_name'],
                'packing'       =>$params['packing'],
                'brand'         =>$params['brand'],
                'spec'          =>$params['spec'],
                'status'        => 'PUBLISH',
                'is_standard'   => 'NO',
                );
            if(is_array($params['goods_imgs'])){
                $ic_item_params['goods_img_new'] = json_encode($params['goods_imgs']);
                $ic_item_params['goods_img'] = $params['goods_imgs'][0];
            }
            $apiPath = "Base.ItemModule.Item.Item.addStandard";
            $ic_item_res = $this->invoke($apiPath,$ic_item_params);
            if($ic_item_res['status'] != 0 ){
                return $this->endInvoke(NULL,$ic_item_res['status']);
            }

            // 添加商家商品库
            $ic_store_params = array(
                'ic_code'  => $ic_item_res['response'],
                'sc_code'  => $params['sc_code'],
                'sub_name' => $params['store_sub_name'],
                'sic_no'   => $params['sic_no'],
                'price'    => $params['price'],
                'min_num'  => empty($params['min_num']) ? 1 : $params['min_num'],
                'stock'    => $params['stock'],
                'status'   => $params['status'],
                'source'   => IC_ITEM_SOURCE_BOSS,
                );

            $apiPath = "Base.ItemModule.Item.Item.addStoreItem";
            $ic_store_res = $this->invoke($apiPath,$ic_store_params);
            if($ic_store_res['status'] != 0 ){
                return $this->endInvoke($apiPath, $ic_store_res['status']);
            }

            // 生成二维码  更新商品
            $sic_code = $ic_store_res['response']['sic_code'];
            $qrcode_params = array(
                'sc_code'=>$params['sc_code'],
                'sic_code'=>$sic_code,
                'goods_img'=>$params['goods_imgs'][0],
                );

            $apiPath = "Base.ItemModule.Item.Item.qrcode";
            $qrcode_res = $this->invoke($apiPath, $qrcode_params);
            if($qrcode_res['status'] != 0 ){
                return $this->endInvoke(NULL, $qrcode_res['status']);
            }
            D()->commit();
            $data = array(
                'sic_code'       =>$sic_code,
            );
            return $this->endInvoke($data);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4501);
        }

    }
    
     /**
     * @api  Boss版商品更改状态接口
     * @apiVersion 1.0.0
     * @apiName Bll.Boss.Store.Item.getSts
     * @apiTransaction N
     * @apiAuthor Todor <zhoulianlei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */
    public function getSts($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];
        
        $oss = new \Library\Oss('MOBILE');  //移动端的  配置
        $key_res = $oss->getSts($uc_code);
        if($key_res === FALSE){
            return $this->endInvoke(NULL,9102);
        }
        $key_res['Expiration'] = strtotime($key_res['Expiration']);
        $key_res['StorePath'] = 'boss/'.$sc_code.'/'.date('Ym');
        
        return $this->endInvoke($key_res);
        
    }


    /**
     * @api  商品信息获取
     * @apiVersion 1.1.0
     * @apiName Bll.Boss.Store.Item.get
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-17
     * @apiSampleRequest On
     */

    public function get($params){
        $apiPath = "Base.ItemModule.Item.ItemInfo.getStoreItem";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0 ){
            return $this->endInvoke(NULL, $res['status']);
        }
        $data = array(
            'goods_name'     =>$res['response']['goods_name'],
            'sic_no'         =>$res['response']['sic_no'],
            'spec'           =>$res['response']['spec'],
            'packing'        =>$res['response']['packing'],
            'brand'          =>$res['response']['brand'],
            'price'          =>$res['response']['price'],
            'stock'          =>$res['response']['stock'],
            'store_sub_name' =>$res['response']['store_sub_name'],
            'min_num'        =>$res['response']['min_num'],
            'goods_imgs'     =>$res['response']['goods_img_new'],
            'store_status'   =>$res['response']['store_status'],
            'sic_code'       =>$res['response']['sic_code'],
            );
        return $this->endInvoke($data);
    }


    /**
     * @api  商品校验
     * @apiVersion 1.1.0
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-17
     * @apiSampleRequest On
     */
    private function _checkRule($params){
        // 请输入商品名称
        if(empty($params['goods_name'])){
            return $this->endInvoke(NULL,4544,array('name'=>'商品名称'));
        }

        // 请输入商品编码
        if(empty($params['sic_no'])){
            return $this->endInvoke(NULL,4544,array('name'=>'商品编码'));
        }

        // 不允许包含特殊字符
        $rule = "/^[\x{4e00}-\x{9fa5}\w]{1,50}$/u";
        if(!preg_match($rule, $params['sic_no'])){
            return $this->endInvoke(NULL,4546);
        }

        // 请输入商品规格
        if(empty($params['spec'])){
            return $this->endInvoke(NULL,4544,array('name'=>'商品规格'));
        }

        // 请输入包装单位
        if(empty($params['packing'])){
            return $this->endInvoke(NULL,4544,array('name'=>'包装单位'));
        }

        // 单价不能为0  0.00 empty过不了
        if(empty($params['price']) || $params['price'] == 0){
            return $this->endInvoke(NULL,4547);
        }

        // 单价输入有误
        if(isset($params['price']) && (!is_numeric(trim($params['price'])) || trim($params['price']) >= 100000000 || trim($params['price']) < 0 || $params['price'] < 0.01) ){
            return $this->endInvoke(NULL,4548);
        }

        //库存不能为0
        if(empty($params['stock']) || $params['stock'] == 0 ){
            return $this->endInvoke(NULL,4549);
        }

        // 库存输入有误
        if(isset($params['stock']) && (!is_numeric(trim($params['stock'])) || strpos($params['stock'],'.') || trim($params['price']) < 0) ){
            return $this->endInvoke(NULL,4550);
        }

        //库存输入超过最大值
        if($params['stock'] >= 100000000){
            return $this->endInvoke(NULL,4551);
        }

        //起订数量输入有误
        if(isset($params['min_num']) && (!is_numeric(trim($params['min_num'])) || trim($params['min_num']) >= 100000000 || strpos($params['min_num'],'.') || trim($params['min_num']) < 0)){
            return $this->endInvoke(NULL,4552);
        }

        //起订数量不能大于现有库存
        if(isset($params['min_num']) && $params['min_num'] > $params['stock'] ){
            return $this->endInvoke(NULL, 4562);
        }

        // 商品图片数量不能大于5个
        if(is_array($params['goods_imgs'])){
            if(count($params['goods_imgs']) > 5){
                return $this->endInvoke(NULL,4553);
            }
        }else{
            if(count(json_decode($params['goods_imgs'],true)) > 5){
                return $this->endInvoke(NULL,4553);
            }
        }

        // 商品图片不能为空
        // if(empty($params['goods_imgs'])){
        //     return $this->endInvoke(NULL,4554);
        // }
    }

}

?>