<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b购物车
 */

namespace Bll\B2b\User;

use System\Base;

class Cart extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }

    /**
     * 购物车列表
     * Bll.B2b.User.Cart.lists
     * @param type $params
     */
    public function lists($params) {
        //购物车列表

        $sc_code = $params['sc_code'];
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $cart_res = $this->invoke($apiPath, $params);
        if($cart_res['status'] != 0){
            return $this->endInvoke($cart_res['response'],$cart_res['status'],'',$cart_res['message']);
        }
        $cart_list = $cart_res['response']['lists'];
        $cart_list = $this->getSpc($cart_list, $sc_code,'NO',$params['uc_code']);
       
        if (!empty($cart_list)) {

            $cart_amount = array();
            foreach ($cart_list as $k => $v) {
                $cart_lists[$v['sic_code']] = $v;
                $cart_amount[] = caculateItemAmount($v);
                $cart_lists[$v['sic_code']]['itemPrice'] = sprintf('%.2f',caculateItemAmount($v));  # 每个商品的总价钱

                if(isset($v['spc_info']) && $v['spc_info']['type'] == 'LADDER'){                    # 阶梯价价钱
                    $cart_lists[$v['sic_code']]['ladder_price'] = sprintf('%.2f',getLadderPrice($v['spc_info']['spc_detail']['rule'],$v['number'],$v['price']));                   
                }
            }
            $cart_amount = array_sum($cart_amount);

            //获取对应标签
            $sic_codes = array_column($cart_lists,'sic_code');
            $tag_parmas['sic_codes'] = $sic_codes;
            $apiPath = "Base.ItemModule.Tag.Tag.getTags";
            $tags_res = $this->invoke($apiPath, $tag_parmas);
            foreach ($tags_res['response'] as $k => $v) {
                $temp[$v['sic_code']][] = $v;
            }
            $tag_keys = array_keys($temp);

            // 拼装 标签
            foreach ($cart_lists as $k => &$v) {
                if(in_array($v['sic_code'],$tag_keys)){
                    $v['tags'] = $temp[$v['sic_code']];
                }
            }
            unset($v); 
        }

        //获取店铺信息
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $store_res = $this->invoke($apiPath, array('sc_code'=>$sc_code));
        if($store_res['status'] != 0){
            return $this->endInvoke($store_res['response'],$store_res['status'],'',$store_res['message']);
        }
        
        $data = array(
            'cart_list'   => $cart_lists,
            'store_info'  => $store_res['response'],
            'cart_amount' =>sprintf('%.2f',$cart_amount),
        );

        return $this->endInvoke($data);
    }

    /**
     * 添加购物车
     * Bll.B2b.User.Cart.add
     * @param type $params
     */
    public function add($params) {
        try {
            D()->startTrans();
            $apiPath = "Base.UserModule.Cart.Cart.add";
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4020);
        }
    }
    
    /**
     * 删除购物车
     * Bll.B2b.User.Cart.delete
     * @param type $params
     */
    public function delete($params){
        try {
            D()->startTrans();
            $apiPath = "Base.UserModule.Cart.Cart.delete";
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4021);
        }
    }
    
    /**
     * 修改购物车商品数量
     * Bll.B2b.User.Cart.changeNum
     * @param type $params
     */
    public function changeNum($params){
        try {
            D()->startTrans();
            $apiPath = "Base.UserModule.Cart.Cart.changeNum";
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }
            //如果 影响行数为 0 则  购物车内没有该商品  添加
            if(empty($res['response'])){
                $apiPath = "Base.UserModule.Cart.Cart.add";
                $add_res = $this->invoke($apiPath, $params);
                if($add_res['status'] != 0){
                    return $this->endInvoke(NULL,$add_res['status']);
                }
            }
            
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4022);
        }
    }


    /**
     * 平台购物车列表
     * Bll.B2b.User.Cart.platformLists
     * @access public 
     */

    public function platformLists($params){

        $params['sql_flag'] = 'platformCartLists';
        $params['fields']   = "ii.ic_code,ii.goods_name,ii.brand,ii.spec,ii.packing,ii.bar_code,ii.category_end_id,ii.goods_img,isi.sc_code,isi.sic_no,isi.sic_code,isi.price,isi.sub_name as store_sub_name,isi.min_num,isi.stock,uc.number,ss.min_money,ss.name";
        $params['where']    = array(
            'ss.status' =>'ENABLE',
            );
        $params['order'] = 'isi.sort desc ,isi.create_time';
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $cart_res = $this->invoke($apiPath, $params);
        if($cart_res['status'] != 0){
            return $this->endInvoke(NULL, $cart_res['status']);
        }

        $cart_list = $cart_res['response']['lists'];
        $cart_list = $this->getSpc($cart_list, $sc_code,'NO',$params['uc_code']);

        // 计算价钱 
        if(!empty($cart_list)){

            // 获取标签
            $sic_codes = array_column($cart_list,'sic_code');
            $tag_parmas['sic_codes'] = $sic_codes;
            $apiPath = "Base.ItemModule.Tag.Tag.getTags";
            $tags_res = $this->invoke($apiPath, $tag_parmas);
            foreach ($tags_res['response'] as $k => $v) {
                $temp[$v['sic_code']][] = $v;
            }
            $tag_keys = array_keys($temp);

            // 拼装 标签
            foreach ($cart_list as $k => &$v) {
                if(in_array($v['sic_code'],$tag_keys)){
                    $v['tags'] = $temp[$v['sic_code']];
                }
            }
            unset($v); 


            // 根据店铺重组
            foreach ($cart_list as $k => $v) {
                $cart_list[$v['sc_code']][] = $v;
                unset($cart_list[$k]);
            }

            foreach ($cart_list as $key => $value) {
                $cart_lists = array();                                                                  # 临时变量
                foreach ($value as $k => $v) {
                    $cart_lists[$v['sic_code']] = $v;
                    $cart_lists[$v['sic_code']]['itemPrice'] = sprintf('%.2f',caculateItemAmount($v));  # 每个商品的总价钱

                    if(isset($v['spc_info']) && $v['spc_info']['type'] == 'LADDER'){                    # 阶梯价价钱
                        $cart_lists[$v['sic_code']]['ladder_price'] = sprintf('%.2f',getLadderPrice($v['spc_info']['spc_detail']['rule'],$v['number'],$v['price']));                   
                    }

                    $min_money = $v['min_money'];
                }
                $amount = array_sum(array_column($cart_lists,'itemPrice'));
                $cart_list[$key] = array(
                    'cart_list'=> $cart_lists,
                    'amount' =>sprintf('%.2f',$amount),
                    'min_money'=>$min_money,
                );
            }
        }
        return $this->endInvoke($cart_list);
    }

}

?>
