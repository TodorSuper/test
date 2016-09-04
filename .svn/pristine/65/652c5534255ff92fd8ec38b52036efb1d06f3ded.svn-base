<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: liaoxianwen <liaoxianwen@yunputong.com >
 * +---------------------------------------------------------------------
 * | 
 */

namespace Library;

class Vikey {
    private $rsa_obj; # rsa 实例

    private $publicKeyPath = '';
    private $privateKeyPath = '';

    public function __construct() {
        $this->publicKeyPath = __DIR__ . '/Vikey/rsa_public_key.pem';
        $this->privateKeyPath = __DIR__ . '/Vikey/rsa_private_key.pem';
        $this->rsa_obj = new Rsa($this->publicKeyPath, $this->privateKeyPath);
    }

    public function encrypt($vikeyArr) {
        return $this->rsa_obj->encrypt(json_encode($vikeyArr));
    }

    private function decrypt($vikeyStr) {
        return $this->rsa_obj->decrypt($vikeyStr);
    }

    public  function mkSecurityStr() {
        $arr = str_split('abcdefghjkmnpqrstuvwxyABCDEFGHJKMNPQRSTUVWXY?+_!&#');
        $rand_keys = array_rand($arr, 5);
        $securityStr = '';
        foreach($rand_keys as $rand_key) {
            $securityStr .= $arr["$rand_key"];
        }
        return $securityStr;
    }

    public function checkVikeyHid($sign, $viKeyHid, $subViKeyStr) {
        $sign .= $subViKeyStr;
        $signInfo = json_decode($this->decrypt($sign));
        if($signInfo->hid !== $viKeyHid) {
            return false;
        }
        return true;
    }
}