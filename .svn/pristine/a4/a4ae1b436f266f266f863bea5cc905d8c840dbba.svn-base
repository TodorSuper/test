<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 支付队列回调接口
 */

namespace Com\CallBack\Push;

use System\Base;

class Queue extends Base {

	private $_rule = null; # 验证规则列表

	public function __construct() {
		parent::__construct();
	}


    /**
     * 友盟推送  待发货
     * Com.CallBack.Push.Queue.unshipWarn
     * @param type $params
     * @return type
     */
    public function unshipWarn($params){
        if (isset($params['message']) && is_array($params['message'])) {
            $params = $params['message'];
        }
        $sc_code = $params['sc_code'];
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $data = array('sc_code'=>$sc_code);
        $store_res = $this->invoke($apiPath, $data);
        if($store_res['status'] != 0){
            return $this->endInvoke(NULL,$store_res['status']);
        }
        
        $uc_code = $store_res['response']['uc_code'];
        $apiPath = "Base.UserModule.User.User.getDevice";
        $data = array('uc_code'=>$uc_code,'sc_code'=>$sc_code);
        $device_res = $this->invoke($apiPath, $data);
        if($device_res['status'] != 0){
            return $this->endInvoke(NULL,$device_res['status']);
        }
        //获取是否开启声音
        $apiPath = "Base.UserModule.User.User.getOptions";
        $sound_res = $this->invoke($apiPath,$data);
        if($sound_res['status'] != 0){
            return $this->endInvoke(NULL,$sound_res['status']);
        }
        //是否推送消息
        $push_msg = $sound_res['response']['push_msg'] ;
        if($push_msg == 'OFF'){
            //不推送消息
            return $this->res(true);
        }
        $prompt_sound = $sound_res['response']['prompt_sound'];
        $play_sound = $prompt_sound == 'ON' ? TRUE : FALSE;
        $device_tokens = $device_res['response']['device_token'];
        $device = $device_res['response']['device'];
        //推送消息
        $this->pushAppMessage($device, $device_tokens, UMENG_BOSS_UNSHIP,BOSS,$play_sound);
    }
}

?>
