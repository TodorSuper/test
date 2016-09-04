<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 前台 商品列表
 */

namespace Bll\B2b\Item;

use System\Base;

class Lists extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }


    /**
     * 
     * 商家列表首页
     * Bll.B2b.Item.Lists.lists
     * @param type $params
     */

    public function lists($params){

        $page = $params['page'] + 0;
        $params['page'] = max($page, 1);
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];
        $params['stock_gt'] = 'YES';
        $params['stock_min_num'] = 'YES';

        // 获取购物车列表
        if (!empty($uc_code)) {
            $cart_info = $this->getCartList($sc_code, $uc_code);
            $cart_info['lists'] = $this->getSpc($cart_info['lists'],$params['sc_code'],'NO',$uc_code);
            if (!empty($cart_info['lists'])) {

                $cart_amount = array();
                foreach ($cart_info['lists'] as $k => $v) {
                    $cart_list[$v['sic_code']] = $v;
                    $cart_amount[] = caculateItemAmount($v);

                    if(isset($v['spc_info']) && $v['spc_info']['type'] == 'LADDER'){            # 阶梯价价钱
                        $cart_list[$v['sic_code']]['spc_info']['goods_price'] = getLadderPrice($v['spc_info']['spc_detail']['rule'],$v['number'],$v['price']);
                        
                    }
                }
                $cart_num = count($cart_list);
                $goods_numbers = array_sum(array_column($cart_info['lists'], 'number'));
                $cart_amount = array_sum($cart_amount);
                
            }
        }

        $apiPath = "Base.ItemModule.Item.ItemInfo.storeItems";
        $params['status'] = IC_STORE_ITEM_ON;
        $lists_info = $this->invoke($apiPath, $params);
        if ($lists_info['status'] != 0) {
            return $this->res(NULL, $lists_info['status']);
        }


        // 组装标签
        if (!empty($lists_info['response']['lists'])) {
            //获取对应标签
            $sic_codes = array_column($lists_info['response']['lists'],'sic_code');
            $tag_parmas['sic_codes'] = $sic_codes;
            $apiPath = "Base.ItemModule.Tag.Tag.getTags";
            $tags_res = $this->invoke($apiPath, $tag_parmas);
            foreach ($tags_res['response'] as $k => $v) {
                $temp[$v['sic_code']][] = $v;
            }
            $tag_keys = array_keys($temp);

            // 拼装 标签
            foreach ($lists_info['response']['lists'] as $k => &$v) {
                if(in_array($v['sic_code'],$tag_keys)){
                    $v['tags'] = $temp[$v['sic_code']];
                }
            }
            unset($v); 
        }

        $data = array(
            'item_list' => $lists_info['response'], //商品列表信息
            'cart_list' => $cart_list, // 购物车列表
            'cart_num' => $cart_num, //购物车商品总类
            'cart_amount' => $cart_amount, //购物车总金额
            'goods_numbers'=> $goods_numbers, # 购物车总件数
        );   
        

        $data['item_list']['lists'] = $this->getSpc($data['item_list']['lists'],$params['sc_code'],'NO',$uc_code);     
        
        return $this->res($data);
    }


    /**
     * 获取轮播图列表
     * @param type $sc_code
     * @return type
     */
    private function getCarouselList($sc_code) {
        //获取轮播图
        $apiPath = "Base.StoreModule.Basic.Carousel.lists";
        $data = array('sc_code' => $sc_code, 'is_show' => 'YES');
        $carousel_res = $this->invoke($apiPath, $data);
        if ($carousel_res['status'] != 0) {
            return $this->endInvoke(NULL, $carousel_res['status']);
        }
        $carousel_lists = $carousel_res['response']['lists'];
        return $carousel_lists;
    }


    /**
     * 获取店铺信息
     * @param type $sc_code
     * @return type
     */
    private function _getStoreInfo($sc_code) {
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $data = array('sc_code' => $sc_code);
        $store_res = $this->invoke($apiPath, $data);
        if ($store_res['status'] != 0) {
            return $this->endInvoke(NULL, $store_res['status']);
        }
        return $store_res['response'];
    }


    /**
     * 获取商家栏目
     * @param type $sc_code
     * @return categorys
     */

    private function _getCategory($sc_code){
        $apiPath = "Base.ItemModule.Category.Category.lists";
        $data = array(
            'sc_code'=>$sc_code,
            'item_num_gt'=>'YES',
            );
        $categorys = $this->invoke($apiPath,$data);
        if($categorys['status'] != 0 ){
            return $this->endInvoke(NULL, $categorys['status']);
        }
        return $categorys['response'];
    }


    /**
     * 获取商家标签
     * @param type $sc_code
     * @return tags
     */

    private function _getTags($sc_code){
        $apiPath = "Base.ItemModule.Tag.Tag.listsTag";
        $data = array(
            'sc_code'=>$sc_code,
            'item_num_gt'=>'YES',
            );
        $tags = $this->invoke($apiPath, $data);
        if($tags['status'] != 0 ){
            return $this->endInvoke(NULL, $tags['status']);
        }

        return $tags['response'];
    }


    /**
    *
     *

     *
     */
    public fnction

    /**
     * 获取购物车列表
     * @param type $sc_code
     * @param type $uc_code
     */
    private function getCartList($sc_code, $uc_code) {
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $data = array(
            'sc_code' => $sc_code,
            'uc_code' => $uc_code,
        );
        $cart_res = $this->invoke($apiPath, $data);
        if ($cart_res['status'] != 0) {
            return $this->endInvoke(NULL, $cart_res['status']);
        }
        return $cart_res['response'];
    }


    /**
     * 获取店铺品牌名称
     * @param type $sc_code
     */

    private function _getBrands($sc_code){
        $apiPath = "Base.ItemModule.Brand.Brand.brands";
        $data = array(
            'sc_code' => $sc_code,
            'status'=>'ON',
            'stock_gt'=>'YES',
            );

        $brands = $this->invoke($apiPath, $data);
        if($brands['status'] != 0){
            return $this->endInvoke(NULL, $brands['status']);
        }
        return $brands['response'];

    }


    /**
     * 获取客户信息
     * @param type $sc_code
     */ 

    private function _getSalesman($uc_code,$sc_code){
        $apiPath = "Base.UserModule.Customer.Customer.get";
        $data = array(
            'uc_code'=>$uc_code,
            'sc_code'=>$sc_code,
            );
        $customer = $this->invoke($apiPath,$data);
        if($customer['status'] != 0){
            return $this->endInvoke(NULL, $customer['status']);
        }
        return $customer['response'];

    }



    /**
     * 分类 店铺相关信息
     * Bll.B2b.Item.Lists.getmsg
     * @param type $sc_code
     * @param type $uc_code
     */

    public function getmsg($params){

        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];
        $store_info = $this->_getStoreInfo($sc_code);            
        $categorys =  $this->_getCategory($sc_code);                             # 获取品牌
        $tags      = $this->_getTags($sc_code);                                 # 获取标签
        $brands    = $this->_getBrands($sc_code);                               # 获取栏目

       // 获取购物车列表
        if (!empty($uc_code)) {
            $cart_info = $this->getCartList($sc_code, $uc_code);
            $cart_info['lists'] = $this->getSpc($cart_info['lists'],$params['sc_code'],'NO',$uc_code);
            if (!empty($cart_info['lists'])) {

                $cart_amount = array();
                foreach ($cart_info['lists'] as $k => $v) {
                    $cart_list[$v['sic_code']] = $v;
                    $cart_amount[] = caculateItemAmount($v);

                    if(isset($v['spc_info']) && $v['spc_info']['type'] == 'LADDER'){                        # 阶梯价价钱
                        $cart_list[$v['sic_code']]['spc_info']['goods_price'] = getLadderPrice($v['spc_info']['spc_detail']['rule'],$v['number'],$v['price']);

                    }

                }
                $cart_num = count($cart_list);
                $goods_numbers = array_sum(array_column($cart_info['lists'], 'number'));
                $goods_prices = array_column($cart_info['lists'], 'price');
                $cart_amount = array_sum($cart_amount);
            }
        }


        $data = array(
            'store_info'  => $store_info,         # 店铺信息
            'categorys'   => $categorys,          # 栏目信息
            'tags'        => $tags,               # 标签
            'brands'      => $brands,             # 品牌
            'cart_list'   => $cart_list,          # 购物车列表
            'cart_num'    => $cart_num,           # 购物车商品总类
            'cart_amount' => $cart_amount,        # 购物车总金额
            'customer'    => $customer,           # 客户信息
            'goods_numbers'=>$goods_numbers,      # 购物车商品数量
            );
        
        return $this->endInvoke($data);
    }


    /**
     * 经常购买与搜索
     * Bll.B2b.Item.Lists.oftenSearch
     * @param type $sc_code
     * @param type $uc_code
     */

    public function oftenSearch($params){

        $is_often   = $params['is_often'];
        $sc_code    = $params['sc_code'];
        $uc_code    = $params['uc_code'];
        $goods_name = $params['goods_name'];

        if($is_often == 1 && !empty($goods_name)){
            unset($params['is_often']);
        }

        // 获取购物车列表
        if (!empty($uc_code)) {
            $cart_info = $this->getCartList($sc_code, $uc_code);
            $cart_info['lists'] = $this->getSpc($cart_info['lists'],$params['sc_code'],'NO',$uc_code);
            if (!empty($cart_info['lists'])) {

                $cart_amount = array();
                foreach ($cart_info['lists'] as $k => $v) {
                    $cart_list[$v['sic_code']] = $v;
                    $cart_amount[] = caculateItemAmount($v);

                    if(isset($v['spc_info']) && $v['spc_info']['type'] == 'LADDER'){                        # 阶梯价价钱
                        $cart_list[$v['sic_code']]['spc_info']['goods_price'] = getLadderPrice($v['spc_info']['spc_detail']['rule'],$v['number'],$v['price']);

                    }

                }
            }
        }

        // 如果是经常购买  查询购买id
        if ($is_often == 1) {
            $key = \Library\Key\RedisKeyMap::getOftenBuy($sc_code, $uc_code);
            $redis = R();
            $sic_codes = $redis->get($key);
            $params['sic_codes'] = explode(',', $sic_codes);
            $params['sic_codes'] = array_filter($params['sic_codes']);

        }

        //经常购买 搜索调取列表
        if (($is_often == 1 && !empty($params['sic_codes'])) || !empty($goods_name)) { 

            $params['page_number']   = '1000';
            $params['stock_min_num'] = 'YES';
            $apiPath = "Base.ItemModule.Item.ItemInfo.storeItems";

            $params['status'] = IC_STORE_ITEM_ON;
            $lists_info = $this->invoke($apiPath, $params);
            if ($lists_info['status'] != 0) {
                return $this->res(NULL, $lists_info['status']);
            }
            $data['lists'] = $lists_info['response']['lists'];

            if($is_often == 1){                                        # 最近购买的出现最前面

                foreach ($data['lists'] as $k => $v) {
                    $data['lists'][$v['sic_code']] = $v;
                    unset($data['lists'][$k]);
                }
                $temp = array();
                foreach ($params['sic_codes'] as $k => $v) {

                    !empty($data['lists'][$v]) && $temp[] = $data['lists'][$v];
                }

                $data['lists'] = $temp;
            }  
            
            $data['lists'] = $this->getSpc($data['lists'],$params['sc_code'],'NO',$uc_code);

            // 组装标签
            if (!empty($data['lists'])) {
                //获取对应标签
                $sic_codes = array_column($data['lists'],'sic_code');
                $tag_parmas['sic_codes'] = $sic_codes;
                $apiPath = "Base.ItemModule.Tag.Tag.getTags";
                $tags_res = $this->invoke($apiPath, $tag_parmas);
                foreach ($tags_res['response'] as $k => $v) {
                    $temp[$v['sic_code']][] = $v;
                }
                $tag_keys = array_keys($temp);

                // 拼装 标签
                foreach ($data['lists'] as $k => &$v) {
                    if(in_array($v['sic_code'],$tag_keys)){
                        $v['tags'] = $temp[$v['sic_code']];
                    }
                }
                unset($v); 
            }
        }
        $data['cart_list'] = $cart_list;
        return $this->endInvoke($data);
    }

    /**
     * 促销信息
     * Bll.B2b.Item.Lists.spcList
     * @param type $sc_code
     */

    public function spcList($params){

        $page             = $params['page'] + 0;
        $page             = max($page, 1);
        $params['page']   = $page;
        $sc_code          = $params['sc_code'];
        $uc_code          = $params['uc_code'];
        $params['status'] = array("PUBLISH");
        $params['type']   = array(SPC_TYPE_GIFT,SPC_TYPE_SPECIAL,SPC_TYPE_LADDER);
        $this->_request_sys_name = 'B2B';

        // 获取购物车列表
        if (!empty($uc_code)) {
            $cart_info = $this->getCartList($sc_code, $uc_code);
            $cart_info['lists'] = $this->getSpc($cart_info['lists'],$params['sc_code'],'NO',$uc_code);
            if (!empty($cart_info['lists'])) {

                $cart_amount = array();
                foreach ($cart_info['lists'] as $k => $v) {
                    $cart_list[$v['sic_code']] = $v;
                    $cart_amount[] = caculateItemAmount($v);

                    if(isset($v['spc_info']) && $v['spc_info']['type'] == 'LADDER'){                # 阶梯价价钱
                        $cart_list[$v['sic_code']]['spc_info']['goods_price'] = getLadderPrice($v['spc_info']['spc_detail']['rule'],$v['number'],$v['price']);
                        
                    }

                }
                $cart_num = count($cart_list);
                $goods_numbers = array_column($cart_info['lists'], 'number');
                $goods_prices = array_column($cart_info['lists'], 'price');
                $cart_amount = array_sum($cart_amount);
            }
        }


        //得到促销列表和商品列表的联合查询
        $apiPath='Base.SpcModule.Center.Spc.lists';
        $res=$this->invoke($apiPath,$params);
        if($res['status']!=0){
            $this->endInvoke(null,$res['status'],'',$res['message']);
        }

        $res['response']['lists'] = $this->getSpc($res['response']['lists'],$sc_code,'NO',$uc_code);

        // 去除 赠品数量小于0的促销品  
        foreach ($res['response']['lists'] as $k => $v) {
            if($v['spc_info']['type'] == 'REWARD_GIFT'){
                if($v['spc_info']['spc_detail']['gift_item']['stock'] <= 0){
                    unset($res['response']['lists'][$k]);
                }
            }
        }

        // 获取 商品对应标签
        if(!empty($res['response']['lists'])){
            $sic_codes = array_column($res['response']['lists'],'sic_code');
            $tag_parmas['sic_codes'] = $sic_codes;
            $apiPath = "Base.ItemModule.Tag.Tag.getTags";
            $tags_res = $this->invoke($apiPath, $tag_parmas);
            foreach ($tags_res['response'] as $k => $v) {
                $temp[$v['sic_code']][] = $v;
            }
            $tag_keys = array_keys($temp);

            // 拼装 标签
            foreach ($res['response']['lists'] as $k => &$v) {
                if(in_array($v['sic_code'],$tag_keys)){
                    $v['tags'] = $temp[$v['sic_code']];
                }
            }
            unset($v);
        }

        $data = array(
            'item_list' => $res['response'], //商品列表信息
            'cart_list' => $cart_list, // 购物车列表
            'cart_num' => $cart_num, //购物车商品总类
            'cart_amount' => $cart_amount, //购物车总金额
        );

        return $this->endInvoke($data);
    }


    /**
     * 联动查询
     * Bll.B2b.Item.Lists.search
     * @param type $sc_code
     */

    public function search($params){

        $params['page_number']   = '1000';
        $params['stock_min_num'] = 'YES';
        $params['status'] = IC_STORE_ITEM_ON;
        $apiPath = "Base.StoreModule.Item.Item.storeItems";
        $lists_info = $this->invoke($apiPath, $params);

        if ($lists_info['status'] != 0) {
            return $this->res(NULL, $lists_info['status']);
        }

        return $this->endInvoke($lists_info['response']);

    }


    /**
     * 检查是否够起够数
     * Bll.B2b.Item.Lists.minbuy
     * @param type sc_code uc_code
     */

    public function minbuy($params){
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $cart = $this->invoke($apiPath,$params);
        return $this->endInvoke($cart['response']);

    }


    /**
     * 扫码获取单个商品信息
     * Bll.B2b.Item.Lists.get
     * @param type sic_code sc_code
     */

    public function get($params){

        // 判断用户是否关注
        $apiPath = "Com.Common.Wx.Mutual.getUserInfo";
        $data = array(
            'openid' => $params['openid'],
        );
        $weixin_res =$this->invoke($apiPath,$data);

        if($weixin_res['status'] != 0 || empty($weixin_res['response']) || $weixin_res['response']['subscribe'] == 0){
            return $this->endInvoke(NULL,4037);
        }

        // 判断用户是否注册
        $apiPath = "Base.WeiXinModule.User.User.getWeixinInfo";
        $check_params = array(
            'open_id' => $params['openid'],
            );
        $res = $this->invoke($apiPath, $check_params);
        if($res['status'] != 0 ){
            return $this->endInvoke(NULL,$res['status']);
        }
        if(empty($res['response'])){
            return $this->endInvoke(NULL,4017);
        }

        // 获取商品信息
        $params['status'] = IC_STORE_ITEM_ON;
        $params['need_tag'] = 'YES';
        $params['stock_gt'] = 'YES';
        $apiPath = "Base.ItemModule.Item.ItemInfo.getStoreItem";
        $res = $this->invoke($apiPath,$params);

        if($res['status'] != 0 ){
            return $this->endInvoke(NULL,4542);
        }

        // 获取商品购物车信息
        $params = array(
            'sic_codes'=> array($params['sic_code']),
            'uc_code' => $params['uc_code'],
            'sc_code' => $params['sc_code'],
            );
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $cart_res = $this->invoke($apiPath, $params);
        $res['response']['cart_num'] = $cart_res['response']['lists'][0]['number'];

        //获取促销信息
        $temp[] = $res['response'];
        $res = $this->getSpc($temp,$params['sc_code'],'NO',$params['uc_code']);

        return $this->endInvoke(current($res));
    }


    /**
     * 获取下载APP路径
     * Bll.B2b.Item.Lists.getAppUrl
     * @param
     */

    public function getAppUrl($params){

        $params['device'] = ANDROID;
        $apiPath = "Base.UserModule.User.App.getVersion";
        $android_res = $this->invoke($apiPath, $params);
        if($android_res['status'] != 0){
            return $this->endInvoke(NULL, $android_res['status']);
        }

        $params['device'] = IOS;
        $apiPath = "Base.UserModule.User.App.getVersion";
        $ios_res = $this->invoke($apiPath, $params);
        if($ios_res['status'] != 0){
            return $this->endInvoke(NULL, $ios_res['status']);
        }
        $data = array(
            'android'=>$android_res['response'],
            'ios'=>$ios_res['response'],
            );
        return $this->endInvoke($data);
    }


}

?>
