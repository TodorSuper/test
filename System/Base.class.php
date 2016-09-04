<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | api基类
 */

namespace System;

class Base extends Controller {

    protected $error;
    public $tablePrefix = null;

    public function __construct() {
        parent::__construct();
//        $this->error = C('ERROR');
        $this->tablePrefix = C('DB_PREFIX');
    }

    public function setTrance() {
        echo __FILE__ . "\n";
        echo __LINE__ . "\n";
        echo __FUNCTION__ . "\n";
        echo __CLASS__ . "\n";
        echo __METHOD__ . "\n";
        exit;
        if (empty($data))
            return;
        static $class = array();
    }

    /**
     * 组装更新的数据
     * @param type $fields
     * @param type $params    
     * @param type $data   额外的更新数据
     */
    final public function create_save_data($fields, $params, $data = array('update_time' => NOW_TIME)) {
        $save = array();
        if (empty($fields)) {
            return $data;
        }
        foreach ($fields as $k => $field) {
            if (isset($params[$field])) {
                $save[$field] = $params[$field];
            }
        }
        if (!empty($data)) {
            $save = array_merge($save, $data);
        }
        return $save;
    }

    /**
     * 验证参数
     * @param type $sc_code
     * @param type $uc_code
     */
    final public function checkParams($sc_code, $uc_code) {
        if ($this->_request_sys_name == B2B) {
            if (empty($uc_code)) {  //如果是b2b  平台 ，用户编码不能为空
                return $this->endInvoke(NULL, 6014);
            }
        } else if ($this->_request_sys_name == POP) {
            if (empty($sc_code)) {
                return $this->endInvoke(NULL, 6013);
            }
        }
    }

    /**
     * 请求支付中心获取数据 post 方式
     * rpc 
     * @access public
     * @return void
     */
    public function rpc($url, $data = '', $method = 'post', $timeout = 15, $gizp = true) {
        $post_data = $data;

        $SSL = substr($url, 0, 8) == "https://" ? true : false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($SSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($gizp) {
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        }
        $output = curl_exec($ch);
//		$data2 = curl_error($ch);
        //	L($data2);
        curl_close($ch);
        DG(['rpc_call', $output], SUB_DG_OBJECT);
        return $output;
    }

    /**
     * 创建cashier调用签名
     * mkCashierSign 
     * @param mixed $data 
     * @access public
     * @return void
     */
    public function mkCashierSign($data) {
        if ($data['sign']) {
            unset($data['sign']);
        }
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if ($v === '' || is_array($v)) {
                continue;
            }
            $str .= $v . '&';
        }
        $str.=C('SINGN_KEY');
        return md5($str);
    }

    /**
     * 获取定义好的redis操作key
     * getRedisKey 
     * @param mixed $key 
     * @access public
     * @return void
     */
    public function getRedisKey($name, $value) {
        $key = \Library\Key\RedisKeyMap::$name($value);
        return $key;
    }

    /**
     * 传入商品列表信息 获取促销信息 并 重组
     * @param  params  商品列表
     * @param  sc_code 店铺编码
     * @author Todor
     * @return params 合并后的商品列表
     */
    public function getSpc($goods_info, $sc_code, $need_preheat = 'YES',$uc_code = '') {
        //获取全部促销信息
        $arr = array();
        $arr['sic_code'] = array_column($goods_info, 'sic_code');
        $arr['sc_code'] = $sc_code;
        $arr['need_preheat'] = $need_preheat;
        $apiPath = "Base.SpcModule.Center.SpcInfo.spcInfo";
        $spc = $this->invoke($apiPath, $arr);
        if ($spc['status'] != 0) {
            return 7023;
        }
        $spc_info = $spc['response'];
        if (empty($spc_info)) {  //促销信息为空
            return $goods_info;
        }
        $spc_codes = array_column($spc_info, 'spc_code');
        if(!empty($spc_codes) && !empty($uc_code)){
            //获取已经购买的促销数量
            $apiPath = "Base.SpcModule.Center.BuyNumber.lists";
            $buy_params = array(
                'spc_codes'=>$spc_codes,
                'uc_code'=>$uc_code,
            );
            
            $buy_res = $this->invoke($apiPath,$buy_params);
            if($buy_res['status'] != 0){
                return $buy_res['status'];
            }
            $buy_nums = $buy_res['response'];
            $buy_nums = changeArrayIndex($buy_nums, 'spc_code');
        }
        $spc_detail = array_column($spc_info, 'spc_detail');  //促销信息
        //满赠的情况下  获取赠品的商品信息
        $gift_sic_codes = array_column($spc_detail, 'gift_sic_code');
        $gift_sic_codes = array_unique($gift_sic_codes);
        if (!empty($gift_sic_codes)) {
            //如果有赠品
            $apiPath = "Base.ItemModule.Item.ItemInfo.storeItems";
            $data = array(
                'sic_codes' => $gift_sic_codes,
                'is_page' => 'NO',
                'stock_gt' => 'YES',
            );
            $item_res = $this->invoke($apiPath, $data);
            if ($item_res['status'] != 0) {
                return $item_res['status'];
            }

            $gift_items = $item_res['response'];
            //换索引
            $gift_items = changeArrayIndex($gift_items, 'sic_code');
        }

        //促销信息换索引
        $spc_info = changeArrayIndex($spc_info, 'sic_code');
        //组装促销信息
        foreach ($goods_info as $k => $v) {
            
            //如果有促销信息
            if (isset($spc_info[$v['sic_code']])) {
                $spc_data = array();
                //促销信息是满赠  则需要获取赠品的商品信息
                if ($spc_info[$v['sic_code']]['type'] == SPC_TYPE_GIFT) {
                    $gift_sic_code = $spc_info[$v['sic_code']]['spc_detail']['gift_sic_code'];
                    if (empty($gift_items[$gift_sic_code])) {
                        continue;
                    }
                    if($spc_info[$v['sic_code']]['max_buy'] != 0 && !empty($uc_code)){
                        $buy_goods_number = min($v['number'] ,$spc_info[$v['sic_code']]['max_buy'] - $buy_nums[$spc_info[$v['sic_code']]['spc_code']]['number']);
                        $buy_goods_number = max(0,$buy_goods_number);
                    }else{
                        $buy_goods_number = $v['number'];
                    }
                    $gift_number = getGiftNums($buy_goods_number, $spc_info[$v['sic_code']]['spc_detail']['rule']);
                    
                    $gift_items[$gift_sic_code]['goods_number'] = $gift_number;
                    $spc_info[$v['sic_code']]['spc_detail']['gift_item'] = $gift_items[$gift_sic_code];
                    $spc_data = array(
                        'start_time' => $spc_info[$v['sic_code']]['start_time'],
                        'end_time' => $spc_info[$v['sic_code']]['end_time'],
                        'rule' => $spc_info[$v['sic_code']]['spc_detail']['rule']
                    );
                } else if ($spc_info[$v['sic_code']]['type'] == SPC_TYPE_SPECIAL) {
                    //商品原始价格
//                    $ori_price = 0;
                    $price_field = 'price';
                    if (isset($v['price'])) {
//                        $ori_price = $v['price'];
                        $price_field = 'price';
                    } else if (isset($v['goods_price'])) {
//                        $ori_price = $v['goods_price'];
                        $price_field = 'goods_price';
                    }
                    $ori_price = $spc_info[$v['sic_code']]['goods_price'];
                    $spc_data = array(
                        'platform_flag' => $this->_request_sys_name,
                        'ori_price' => $ori_price,
                        'special_price' => $spc_info[$v['sic_code']]['spc_detail']['special_price'],
                        'discount' => $spc_info[$v['sic_code']]['spc_detail']['discount'],
                        'special_type' => $spc_info[$v['sic_code']]['spc_detail']['special_type'],
                        'packing'=>$v['packing'],
                    );
                }else if($spc_info[$v['sic_code']]['type'] == SPC_TYPE_LADDER){
                    $spc_data = array(
                        'price' => $v['price'],
                        'packing' => $v['packing'],
                        'platform_flag' => $this->_request_sys_name,
                        'rule'=>$spc_info[$v['sic_code']]['spc_detail']['rule'],
                    );
                }
                $spc_message = spcRuleParse($spc_info[$v['sic_code']]['type'], $spc_data);
                $spc_info[$v['sic_code']]['spc_message'] = $spc_message;
                $goods_info[$k]['spc_info'] = $spc_info[$v['sic_code']];
                if($spc_info[$v['sic_code']]['type'] == SPC_TYPE_SPECIAL){
                    //如果是特价 并且是促销中 则  使用原价
                    if($spc_info[$v['sic_code']]['status'] == SPC_STATUS_PUBLISH && NOW_TIME > $spc_info[$v['sic_code']]['start_time'] && NOW_TIME < $spc_info[$v['sic_code']]['end_time']){
                        $goods_info[$k][$price_field] = $ori_price;
                    }
                    
                }
                
                //如果已经购买
                if(isset($buy_nums[$spc_info[$v['sic_code']]['spc_code']])){
                    $goods_info[$k]['have_buy'] = $buy_nums[$spc_info[$v['sic_code']]['spc_code']]['number'];
                }else{
                    $goods_info[$k]['have_buy'] = 0;
                }
            }
        }
        return $goods_info;
    }

}

?>
