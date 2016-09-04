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

namespace Bll\Boss\User;

use System\Base;
class Login extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



   /**
    * @api  Boss版登陆获取验证码接口  （废除）
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.Login.getVerify
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-21
    * @apiSampleRequest On
    */

    public function getVerify(){
        $mt_code = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        $verify = '';
        for ($i=0; $i <4 ; $i++) { 
            $verify .= $mt_code[mt_rand(0,strlen($mt_code))];
        }
        $data = array(
            'verify' => $verify,
            '_output' => array(
                'code' => $verify,
                ),
            );

        return $this->endInvoke($data);
    }



   /**
    * @api  Boss登录接口
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.Login.Login
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-21
    * @apiSampleRequest On
    */

   public function Login($params){

        // 登陆错5次禁止登陆
        $params['username'] = strtolower($params['username']);
        $login_key  = \Library\Key\RedisKeyMap::getLogin($params['username']);
        $redis = R();
        $num = $redis->get($login_key);
        if($num >= 5 && !empty($num)){                   # 5 次以上
            $error_data = array(
                'error_times'=>$num,
                'counsel_phone'=>C('CALL_NUMBER'),
                );
            return $this->endInvoke($error_data,4054);
        }

        // 开始事物 主要用户设置 与 用户设备相关
       try{
            D()->startTrans();
            $params['sysName'] = BOSS;
            $apiPath = "Base.UserModule.User.User.login";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                $num = empty($num) ? 1 : $num+1;
                $error_data = array(
                    'error_times'=>$num,
                    'counsel_phone'=>C('CALL_NUMBER'),
                    );
                if($num < 3 && !empty($num)){              # 1~2次
                    return $this->endInvoke($error_data,4058);
                }elseif($num < 5 ){                        # 3~4次
                    return $this->endInvoke($error_data,4057,array('num'=>(5-$num)));
                }else{
                    return $this->endInvoke($error_data,4054);
                }
            }

            $res['response']['_output'] = array(
                'uc_code' => $res['response']['uc_code'],
                'sc_code' => $res['response']['sc_code'],
                'push_msg'=> $res['response']['push_msg'],
                'prompt_sound'=>$res['response']['prompt_sound'],
                'show_img'=>$res['response']['show_img'],
            );
            unset($res['response']['sc_code']);
            unset($res['response']['uc_code']);
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',$res['status']);
            }
            return $this->res($res['response']);
       } catch (\Exception $ex) {
           D()->rollback();
           return $this->endInvoke(NULL,$res['status']);
       }
        
   }







   


















}

?>