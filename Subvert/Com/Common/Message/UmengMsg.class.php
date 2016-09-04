<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 轮播接口
 */

namespace Com\Common\Message;

use System\Base;
use Library\Umeng;
class UmengMsg extends Base {

    private $umeng = null;
    private $message_type = null;

    public function __construct() {
        
        parent::__construct();
        $this->message_type = array(
            BOSS => array(//boss app的提示
                UMENG_BOSS_UNSHIP => array(//代发货
                    ANDROID => array(//Android 的相关参数配置
                        'ticker' => '您有新的订单需要处理',
                        'title' => '您有新的订单需要处理',
                        'text' => '您有新的订单需要处理',
                        'after_open'=>'go_activity',
                        'activity'=>'com.liangrenwang.android.boss.modules.order.OrderListActivity_',
                    ),
                    IOS => array(//ios相关配置
                        'ticker' => '您有新的订单需要处理',
                        
//                        'after_open'=>'go_activity',
                    ),
                ),
            ),
        );
    }

    /**
     * Com.Common.Message.UmengMsg.pushMessage
     * @param type $data
     * @return type
     */
    public function pushMessage($data) {
        if (isset($data['message']) && is_array($data['message'])) {
            $data = $data['message'];
        }
//             $data = array (
//  'sys_name' => 'BOSS',
//  'type' => 'unicast',
//  'device' => 'IOS',
//  'device_tokens' => 'cf3a76d9aa9fc1cb30376ff310ba73c69f1b6210a52c027f084280de49ba5abe',
//  'message_type' => 'UNSHIP',
//  'message_params' => 
//  array (
//  ),
//  'play_sound' => true,
//);
        $this->_rule = array(
            array('sys_name', 'require', PARAMS_ERROR, MUST_CHECK), # 系统名称标识   BOSS
            array('type', array('unicast', 'listcast', 'filecast', 'broadcast', 'groupcast', 'customizedcast'), PARAMS_ERROR, MUST_CHECK, 'in'), # 推送类型 unicast  listcast filecast broadcast groupcast customizedcast  默认为  unicast 单播
            array('device', array(IOS, ANDROID), PARAMS_ERROR, ISSET_CHECK, 'in'), # 手机设备类型
            array('device_tokens', 'require', PARAMS_ERROR, MUST_CHECK), # 设备唯一标识
            array('play_sound', array(TRUE, FALSE), PARAMS_ERROR, ISSET_CHECK, 'in'), # 是否开启声音 ios不管用
            array('message_type', 'require', PARAMS_ERROR, MUST_CHECK), # 通知类型  用于查找出消息的 提示文字等配置
            array('message_params', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK), # 通知消息需要的参数  可能要替换的参数  比如消息标题  {#b2b_code} 则message_type=array('b2b_code'=>'1000000') 会替换掉
        );
        if (!$this->checkInput($this->_rule, $data)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
   
        $sys_name = $data['sys_name'];
        $type = $data['type'];
        $device = $data['device'];
        $device_tokens = $data['device_tokens'];
        $play_sound = $data['play_sound']==='' ? TRUE : $data['play_sound'];
        
        $message_type = $data['message_type'];

        $message_params = $data['message_params'];
        $message = $this->message_type[$sys_name][$message_type][$device];  //获取到具体的参数消息
    //    $message = $this->parseMessage($message,$message_params);
        $ticker = $message['ticker'];
        $title  =  $message['title'];
        $text   =   $message['text'];
        $after_open = $message['after_open'];
        $activity = $message['activity'];
        $this->umeng = new Umeng($sys_name,$device);
        switch ($type) {
            case 'unicast':  //单播
                if ($device == IOS) {
                    $res = $this->umeng->sendIOSUnicast($device_tokens, $ticker,$play_sound);  //IOS
                } else if ($device == ANDROID) {
                    $res = $this->umeng->sendAndroidUnicast($device_tokens, $ticker, $title, $text, $play_sound,$after_open,$activity);   //Android
                }
                break;
            default :
                break;
        }
        if ($res == FALSE) {
            return $this->res(NULL, 21);
        }
        return $this->res();
    }

    private function parseMessage($message, $message_params) {
        $keys = array_keys($message_params);
        foreach ($keys as $k => $v) {
            $search[] = "{#{$v}}";
        }
        foreach ($message as $flag => $m) {
            if(!empty($m)){
                $message[$flag] = str_replace($search, array_values($message_params), $m);
            }
        }
        
        return $message;

            
    }

}

?>
