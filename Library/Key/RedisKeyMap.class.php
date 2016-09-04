<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | redis通用key注册管理类
 */

namespace Library\Key;

class RedisKeyMap {

    public static function getHandleKey($key) {
        return "handle:$key";
    }

    public static function getWxTokenKey($key) {
        return "weixin:$key";
    }
    
    /**
     * 经常购买键值
     * @param type $key
     * @return type
     */
    public static function getOftenBuy($sc_code,$uc_code) {
        return "oftenBuy:{$uc_code}:{$sc_code}";
    }
    /*
    *   促销KEY
    */
    public static function getSpcKey($sc_code,$uc_code) {
        return "spckey:{$uc_code}:{$sc_code}";
    }

    /**
     * 栏目键值
     */
    public static function getCategory($sc_code){
        return "category:{$sc_code}";
    }

    /**
     * 品牌键值
     */
    public static function getBrand($sc_code){
        return "brand:{$sc_code}";
    }
    
      public static function getCount ($uc_code,$mobile) {
        return "count:{$uc_code}:{$mobile}";
    }

    /*
     * 登陆次数键值
     */
    public static function getLogin($username){
        return "BossLogin:{$username}";
    }
    

    public static function getCode($phone){
        return "BossCode:{$phone}";
    }  

    public static function loginCount ($username) {
        return "login_count:{$username}";
    }

    public static function msgCount ($sc_code,$uc_code,$mobile) {
        return "account:{$sc_code}:{$uc_code}:{$mobile}";
    }
    public static function getCouponKey ($b2b_code) {
        return "orders:{$b2b_code}";
    }
    public static function getCouponHashKey ($b2b_code) {
        return "order_status:{$b2b_code}";
    }

    public static function getCouponHashKeyLog ($b2b_code) {
        return "coupon_status:{$b2b_code}";
    }

    public static function getCouponHashKeyPay ($b2b_code) {
        return "pay_status:{$b2b_code}";
    }


    public static function loginTime($username){
        return "Codetime:{$username}";
    }


    public static function userInfo($uc_code){
        return "userInfo:{$uc_code}";
    }

    public static function userSessionId($uc_code){
        return "userSessionId:{$uc_code}";
    }
}

?>
