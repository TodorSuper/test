<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b注册模块
 */

namespace Bll\B2b\User;
use System\Base;

class Region extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }

    /**
     * 注册用户
     * Bll.B2b.User.Region.region
     * @param type $params
     */
    public function region($params){
        $this->_rule = array(
            array('mobile', 'require', PARAMS_ERROR, MUST_CHECK),  //手机号码
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //用户姓名
            array('commercial_name', 'require', PARAMS_ERROR, MUST_CHECK), //商户名
            array('province', 'require', PARAMS_ERROR, ISSET_CHECK), //省
            array('city', 'require', PARAMS_ERROR, ISSET_CHECK), //市
            array('district', 'require', PARAMS_ERROR, ISSET_CHECK), //区
            array('address', 'require', PARAMS_ERROR, ISSET_CHECK), //地址
            array('invite_code', 'require', PARAMS_ERROR, MUST_CHECK), //邀请码
            array('openid', 'require', PARAMS_ERROR, ISSET_CHECK), //open_id
            array('register_from', 'require', PARAMS_ERROR, MUST_CHECK),   # 注册来源 WAP WEIXIN
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $mobile       = $params['mobile'];
        $check_code   = $params['check_code'];
        $commercial_name = $params['commercial_name'];
        $name = $params['name'];
        $province = empty($params['province']) ? C('DEFAULT_PROVINCE') : $params['province'];
        $city = empty($params['city']) ? C('DEFAULT_CITY') : $params['city'];
        $district = $params['district'];
        $address  = $params['address'];
        $invite_code = $params['invite_code'];
        $register_from = $params['register_from'];

        $openid  = $params['openid'];
        //查询该号码是否已经被注册过
        $this->getBasicInfo($mobile);
        //验证码校验  待做
        try{
            D()->startTrans();
            //生成基础信息 
            $basic_info = $this->basicInfo($mobile, $mobile, $name);
            $uc_code = $basic_info['uc_code'];
            //生成会员信息
            $member_data = array(
                'uc_code' =>$uc_code,
                'username' => $mobile,
                'commercial_name' => $commercial_name,
                'mobile' => $mobile,
                'name'  => $name,
                'province' => $province,
                'city'   => $city,
                'district' => $district,
                'address' => $address,
                'invite_code'=>$invite_code,
                'invite_from'=>(strlen($invite_code) == 4) ? 'UC' : 'SC',
                'register_from'=>$register_from,
            );
            $this->addMemberInfo($member_data);
            //如果有 邀请码  则生成商家客户信息
            if(!empty($invite_code)){
                $this->addCustomer($uc_code, $invite_code, $name, $mobile);
            }

            //生成微信信息
            if(!empty($openid)){
                $this->addWeixinInfo($openid, $uc_code);
            }
            //添加默认地址
            $this->setDefaultAddress($uc_code, $name, $mobile, $province, $city, $district, $address);

            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }

            //如果是平台注册 添加优惠券
            if(strlen($params['invite_code']) == 4){
                $coupon_params = array(
                    'flag'=>SPC_ACTIVE_CONDITION_FLAG_REGISTER,
                    'uc_code'=>$uc_code,
                    );
                $coupon_res = $this->push_queue("Bll.B2b.User.Region.getCoupon",$coupon_params,1);
                // $coupon_res = $this->invoke("Bll.B2b.User.Region.getCoupon",$coupon_params);
            }

        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4032);
        }


        //获取登陆信息

        # 调用登录接口
        $loginData =array(
                'sysName' => B2B,
                'username' => $params['mobile'],
            );
        $call = $this->invoke('Base.UserModule.User.User.login', $loginData); # 登录
        if($call['status'] !== 0) {
            return $this->res('', $call['status']);                           # 调用失败
        }

        //得到小B用户的邀请码 user_member
        $arr=array(
            'uc_code'=>$call['response']['uc_code'],
        );
        $result=$this->invoke('Base.UserModule.User.User.getInviteCode',$arr);
        if($result['status']!==0){
            return $this->endInvoke('',$result['status']);
        }
        $invite_code=$result['response'][0]['invite_code'];

        //根据邀请码得到sc_code
        if(strlen($invite_code) == 6){
            $param=array(
                'invite_code'=>$invite_code,
            );
            $respon=$this->invoke('Base.UserModule.Customer.Salesman.get',$param);
            if($respon['status']!==0){
                return $this->endInvoke('',$respon['status']);
            }
            
            $sc_code = $respon['response']['sc_code']; 
            $call['response']['sc_code'] = $sc_code;
        }

        $call['response']['invite_code'] = $invite_code;
        $call['response']['pay_privs'] = $result['response'][0]['pay_privs'];       # 先锋支付标识
        !empty($openid) && $call['response']['openid'] = $openid;

        $key = \Library\Key\RedisKeyMap::userInfo($arr['uc_code']);
        $session_id = \Library\Key\RedisKeyMap::userSessionId($arr['uc_code']);
        $redis = R();
        $redis->Hset($key,$session_id,$params['session_id']);

        return $this->res($call['response']);
    }


    /**
     * 获取优惠券
     * Bll.B2b.User.Region.getCoupon
     *
     */

    public function getCoupon($params){

        if (isset($params['message']) && is_array($params['message'])) {            # 如果为队列
            $params = $params['message'];
        }

        try{
            D()->startTrans();
            $apiPath = "Base.SpcModule.Coupon.Center.getCoupon";
            $res = $this->invoke($apiPath,$params);
            if($res['status'] != 0){
                $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
            return $this->res($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,7082);
        }

    }


    /**
     * 获取注册的初始化信息
     * Bll.B2b.User.Region.getRegisterInit
     * @param type $params
     */
    public function getRegisterInit($params){
        //查询用户是否关注
        $apiPath = "Com.Common.Wx.Mutual.getUserInfo";
        $data = array(
            'openid' => $params['open_id'],
        );
        $weixin_res =$this->invoke($apiPath,$data);
        if($weixin_res['status'] != 0 ){
            return $this->endInvoke($weixin_res);
        }
        //如果未关注
        if($weixin_res['status'] != 0 || empty($weixin_res['response']) || $weixin_res['response']['subscribe'] == 0){
            return $this->endInvoke(NULL,4037);
        }

        // 判断用户是否存在
        $apiPath = "Base.WeiXinModule.User.User.getWeixinInfo";
        $res = $this->invoke($apiPath, $params);
        if($res['status'] != 0 ){
            return $this->endInvoke(NULL,$res['status']);
        }
        if(!empty($res['response'])){
            return $this->endInvoke(NULL,4060);
        }

        $this->getAreaList();
    }
    /**
     * 获取市区地址
     * Bll.B2b.User.Region.getAreaList
     * @param type $params
     */
    public function getAreaList(){
        $params = array(
            'pid'=>33,
        );
        $apiPath = "Com.Tool.Region.Region.getAreaBuyPid";
        $area_res = $this->invoke($apiPath,$params);
        return $this->endInvoke($area_res['response'],$area_res['status'],'',$area_res['message']);
    }

    /**
     * 发送短信
     * Bll.B2b.User.Region.sendCodeSms
     * @param type $params
     */
    public function sendCodeSms($params){
        $this->_rule = array(
            array('check_code', 1, PARAMS_ERROR, MUST_CHECK,'egt'),
            array('numbers', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('from_msg', 'require', PARAMS_ERROR, ISSET_CHECK),  # 主要判断是来自注册还是微信
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $numbers=$params['numbers'];

        //  根据电话号码判断是否注册
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $data = array(
            'username' => $numbers[0],
        );
        $basic_info = $this->invoke($apiPath, $data);
        if($basic_info['status'] != 0){
            return $this->endInvoke(NULL,$basic_info['status']);
        }

        if($params['from_msg'] !== 'login'){                  # 如果不是登陆 验证号码注册没
            if(!empty($basic_info['response'])){
                return $this->endInvoke(NULL,4034);
            }
        }else{                                                # 是登陆判断是否存在
            if(empty($basic_info['response'])){
                return $this->endInvoke(NULL,4024);
            }
        }

        // 记录获取验证码时间
        $time_key  = \Library\Key\RedisKeyMap::loginTime($numbers[0]);
        $redis = R();
        $time = $redis->setnx($time_key,NOW_TIME);
        if($time){
            $redis->expire($time_key,15);
        }else{
            return $this->endInvoke(NULL,2014);  # 验证码发送过于频繁,15秒后再次尝试
        }

        $message = "尊敬的用户，您的验证码是：{$params['check_code']}, 我们会为您提供更优质的服务，非常感谢您的加入！";
        if($params['type'] == 'voice'){
            $message = $params['check_code'];
        }
        $data = array(
            'sys_name'=>B2B,
            'numbers' =>$numbers,
            'message' =>$message,
            'type'    =>$params['type'],
        );
        $apiPath = "Com.Common.Message.Sms.send";
        $send_res = $this->invoke($apiPath,$data);
        return $this->endInvoke($send_res['response'],$send_res['status'],'',$send_res['message']);
    }


    /**
     * 添加会员基本信息
     * @param type $username
     * @param type $mobile
     * @param type $real_name
     * @return type
     */
    private function basicInfo($username,$mobile,$real_name){
        $apiPath = "Base.UserModule.User.Basic.add";
        $data = array(
            'username'  => $username,
            'real_name' => $real_name,
            'mobile'    => $mobile,
            'pre_bus_type' => UC_USER_MERMBER,
        );
        $basic_info = $this->invoke($apiPath, $data);
        if($basic_info['status'] != 0){
            return $this->endInvoke(NULL,$basic_info['status']);
        }

        return $basic_info['response'];
    }


    private function addMemberInfo($data){
        $apiPath = "Base.UserModule.User.User.add";
        $params = array(
            'data'    =>$data,
            'userType'=>UC_USER_MERMBER,
        );
        $extend_info = $this->invoke($apiPath, $params);
        if($extend_info['status'] != 0){
            return $this->endInvoke(NULL,$extend_info['status'],'','邀请码输入错误');
        }
        return true;
    }

    /**
     * 根据
     * @param type $invite_code
     */
    private function addCustomer($uc_code,$invite_code,$name,$mobile){
        $arr = array(
            'invite_code'=>$invite_code,
            'table'      => strlen($invite_code)== 6 ? 'ScSalesman' : 'UcSalesman',
            'status'     => 'ENABLE',
        );
        //根据邀请码获取商家信息
        $apiPath = "Base.UserModule.Customer.Salesman.get";
        $channel_info = $this->invoke($apiPath, $arr);
        if(empty($channel_info['response']) || $channel_info['response']['status'] != 'ENABLE'){
            return $this->endInvoke(NULL,5515);
        }
        //操作平台销售 2015-12-14
        if(strlen($invite_code)== 4){
            $apiPath = "Base.UserModule.Customer.Salesman.update";
            $arr['method'] = '+';
            $update_res = $this->invoke($apiPath, $arr);
            if($update_res['status'] != 0){
                return $this->endInvoke(NULL,$update_res['status']);
            }
            return true;
        }
        $sc_code = $channel_info['response']['sc_code'];
        //添加客户信息
        $apiPath = "Base.UserModule.Customer.Customer.add";
        $data = array(
            'sc_code'  => $sc_code,
            'uc_code'  => $uc_code,
            'name'     => $name,
            'mobile'   => $mobile,
            'salesman_id' => $channel_info['response']['id'],
        );
        $customer_res = $this->invoke($apiPath, $data);
        if($customer_res['status'] != 0){
            return $this->endInvoke(NULL,$customer_res['status']);
        }
        return TRUE;
    }

    private function addWeixinInfo($openid,$uc_code){
        //获取微信信息
        $apiPath = "Com.Common.Wx.Mutual.getUserInfo";
        $data = array(
            'openid'=>$openid,
        );
        $weixin_res = $this->invoke($apiPath,$data);
        if($weixin_res['status'] != 0){
            return $this->endInvoke(NULL,$weixin_res['status'],'',$weixin_res['message']);
        }
        $weixin_info =$weixin_res['response'];
        if(empty($weixin_info)){
            return TRUE;
        }

        $apiPath = "Base.WeiXinModule.User.User.getWeixinInfo";
        $params  = array('uc_code'=>$uc_code,'open_id'=>$openid);
        $weixin_info_res = $this->invoke($apiPath, $params);
        if($weixin_info_res['status'] != 0){
            return $this->endInvoke(NULL,$weixin_info_res['status']);
        }
        $data = array(
            'nickname'=>$weixin_info['nickname'],
            'sex'=>$weixin_info['sex'],
            'province'=>$weixin_info['province'],
            'city'=>$weixin_info['city'],
            'country'=>$weixin_info['country'],
            'headimgurl'=>$weixin_info['headimgurl'],
            'remark'=>$weixin_info['remark'],
            'subscribe_time'=>$weixin_info['subscribe_time'],
            'language'=>$weixin_info['language'],
            'groupid'=>$weixin_info['groupid'],
            'unionid'=>$weixin_info['unionid'],
            'subscribe'=>$weixin_info['subscribe'],
            'open_id'  => $openid,
            'uc_code'  => $uc_code,
        );
        if(empty($weixin_info_res['response'])){
            //如果是空的  则添加
            $apiPath = "Base.WeiXinModule.User.User.add";
        }else{
            //更新
            $apiPath = "Base.WeiXinModule.User.User.update";
        }

        //是否已经有该信息
        $add_res = $this->invoke($apiPath,$data);
        if($add_res['status'] != 0){
            return $this->endInvoke(NULL,$add_res['status']);
        }
        return TRUE;
    }
    private function getBasicInfo($username){
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $data = array(
            'username' => $username,
        );
        $basic_info = $this->invoke($apiPath, $data);
        if($basic_info['status'] != 0){
            return $this->endInvoke(NULL,$basic_info['status']);
        }
        if(!empty($basic_info['response'])){
            return $this->endInvoke(NULL,4034);
        }
        return TRUE;
    }

    //验证手机号是否注册过
    public function checkMobile($data){
        $mobile=$data['mobile'];
        $res=$this->getBasicInfo($mobile);
        if($res){
            $this->endInvoke();
        }
    }
    /**
     * 添加默认地址
     * @param type $uc_code
     * @param type $real_name
     * @param type $mobile
     * @param type $province
     * @param type $city
     * @param type $district
     * @param type $address
     */
    private function setDefaultAddress($uc_code,$real_name,$mobile,$province,$city,$district,$address){
        $params = array(
            'uc_code'  => $uc_code,
            'real_name'=> $real_name,
            'mobile'   => $mobile,
            'province' => $province,
            'city'     => $city,
            'district' => $district,
            'address'  => $address,
            'is_default'=> 'YES',
        );
        $apiPath = "Base.UserModule.Address.Address.add";
        $add_res = $this->invoke($apiPath,$params);
        if($add_res['status'] != 0){
            return $this->endInvoke(NULL,$add_res['status']);
        }
        return true;
    }









}

?>
