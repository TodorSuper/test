<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户相关模块
 */

namespace Bll\B2b\User;
use System\Base;

class User extends Base {

	private $_rule = null; # 验证规则列表
	private static $uc_prefix = null;
	public function __construct() {
		parent::__construct();
		self::$uc_prefix = 'Uc';
	}

	/**
	 * 获取openId的置换code
	 * Bll.B2b.User.User.getCode
	 * @param mixed $data
	 * @access public
	 * @return void
	 */
	public function getCode($data){
		$call = $this->invoke('Com.Common.Wx.Mutual.getCode', $data);
		if($call['status'] !== 0) {
			return $this->res('', $call['status']); # 调用失败
		}
		return $this->res($call['response']);
	}

	/**
	 * 根据置换code获取openId
	 * getOpenId
	 * Bll.B2b.User.User.getOpenId
	 * @param mixed $data
	 * @access public
	 * @return void
	 */
	public function getOpenId($data) {
		$call = $this->invoke('Com.Common.Wx.Mutual.getOpenId', $data);
		if($call['status'] !== 0) {
			return $this->res('', $call['status']); # 调用失败
		}
		return $this->res($call['response']);
	}

	/**
	 * 实现b2b用户自动登录的接口
	 * Bll.B2b.User.User.autoLogin
	 * @access public
	 * @return void
	 */
	public function autoLogin($data) {

		$this->_rule = array(
			array('openid', 'require' , PARAMS_ERROR, ISSET_CHECK ), 		# opend_id
			array('username', 'require' , PARAMS_ERROR, ISSET_CHECK ), 	    # 小B用户名即手机号(wap 登陆)
			array('type', 'require' , PARAMS_ERROR, ISSET_CHECK ), 			# 登陆类型
			array('session_id', 'require' , PARAMS_ERROR, ISSET_CHECK ),    # session_id
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		if(empty($data['username'])){                   # 微信用户自动登录
			# 通过openid获取用户登录账户名称
			$weixinInfo = $this->invoke('Base.UserModule.User.User.getUserInfoByOpenid', ['openid'=>$data['openid']]);
			if($weixinInfo['status'] !== 0 || !$weixinInfo['response']['username']) {
				return $this->res('', 6907); # 用户未在微信端注册
			}
			$loginData =array(
				'sysName' => B2B,
				'username' => $weixinInfo['response']['username'],
			);
		}else{               							# 输入登陆
			$loginData =array(
				'sysName' => B2B,
				'username' => $data['username'],
			);
		}
		

		# 调用登录接口
		$call = $this->invoke('Base.UserModule.User.User.login', $loginData); # 登录
		if($call['status'] !== 0) {
			return $this->res('', $call['status']);							  # 调用失败
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

		// 如果是wap注册 微信首次登陆 添加微信信息
		if($data['type'] == "weixin" && !empty($data['username']) && !empty($data['openid'])){
			$this->_addWeixinInfo($data['openid'],$arr['uc_code']);
		}

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

		$key = \Library\Key\RedisKeyMap::userInfo($arr['uc_code']);
        $session_id = \Library\Key\RedisKeyMap::userSessionId($arr['uc_code']);
        $redis = R();
        $redis->Hset($key,$session_id,$data['session_id']);

		$call['response']['invite_code'] = $invite_code;
		$call['response']['pay_privs'] = $result['response'][0]['pay_privs'];       # 先锋支付标识
		return $this->res($call['response']);
	}

	/**
	 * B2B个人中心
	 * Bll.B2b.User.User.userCenter
	 * @author Todor
	 * @access public
	 */
	public function userCenter($params){

		//获取 待付款数量 
		$params['sys_name'] = B2B;
		$params['type'] = OC_ORDER_GROUP_STATUS_UNPAY;
		$apiPath = "Base.StoreModule.Order.Operation.check";
		$unpay_res = $this->invoke($apiPath,$params);
		if($unpay_res['status']!==0){
			return $this->endInvoke('',$unpay_res['status']);
		}
		$params['start_time'] = $unpay_res['response'];
		$apiPath = "Base.OrderModule.B2b.Statistic.bubble";
		$unpay_num = $this->invoke($apiPath,$params);
		if($unpay_num['status']!==0){
			return $this->endInvoke('',$unpay_num['status']);
		}

		//获取 账期待款数量
		$params['sys_name'] = B2B;
		$params['type'] = "TERM_UNPAY";
		$apiPath = "Base.StoreModule.Order.Operation.check";
		$term_unpay_res = $this->invoke($apiPath,$params);
		if($term_unpay_res['status']!==0){
			return $this->endInvoke('',$term_unpay_res['status']);
		}
		$params['start_time'] = $term_unpay_res['response'];
		$apiPath = "Base.OrderModule.B2b.Statistic.bubble";
		$term_unpay_num = $this->invoke($apiPath,$params);
		if($term_unpay_num['status']!==0){
			return $this->endInvoke('',$term_unpay_num['status']);
		}


		//获取 待发货数量
		$params['sys_name'] = B2B;
		$params['type'] = OC_ORDER_GROUP_STATUS_UNSHIP;
		$apiPath = "Base.StoreModule.Order.Operation.check";
		$unship_res = $this->invoke($apiPath,$params);
		if($unship_res['status']!==0){
			return $this->endInvoke('',$unship_res['status']);
		}
		$params['start_time'] = $unship_res['response'];
		$apiPath = "Base.OrderModule.B2b.Statistic.bubble";
		$unship_num = $this->invoke($apiPath,$params);
		if($unship_num['status']!==0){
			return $this->endInvoke('',$unship_num['status']);
		}

		//获取 已发货数量
		$params['sys_name'] = B2B;
		$params['type'] = OC_ORDER_GROUP_STATUS_SHIPPED;
		$apiPath = "Base.StoreModule.Order.Operation.check";
		$shiped_res = $this->invoke($apiPath,$params);
		if($shiped_res['status']!==0){
			return $this->endInvoke('',$shiped_res['status']);
		}
		$params['start_time'] = $shiped_res['response'];
		$apiPath = "Base.OrderModule.B2b.Statistic.bubble";
		$shiped_num = $this->invoke($apiPath,$params);
		if($shiped_num['status']!==0){
			return $this->endInvoke('',$shiped_num['status']);
		}


		// 获取购物券数量
		$coupon_params = array(
			'uc_code'=>$params['uc_code'],
			'coupon_status'=>SPC_MEMBER_COUPON_STATUS_ENABLE,
			);
		$apiPath = "Base.UserModule.Coupon.Coupon.lists";
		$coupon_res = $this->invoke($apiPath,$coupon_params);
		if($coupon_res['status']!==0){
			return $this->endInvoke('',$coupon_res['status']);
		}


		// 获取微信信息
		$apiPath = "Base.WeiXinModule.User.User.getWeixinInfo";
		$weixin_res = $this->invoke($apiPath,$params);
		if($weixin_res['status'] !== 0){
			return $this->endInvoke(null,$weixin_res['response']);
		}

		$res = array(
			'unpay'=>$unpay_num['response'][0]['num'],
			'term_unpay'=>$term_unpay_num['response'][0]['num'],
			'unship'=>$unship_num['response'][0]['num'],
			'shiped'=>$shiped_num['response'][0]['num'],
			'complete'=>$complete_num['response'][0]['num'],
			'coupon_num'=>count($coupon_res['response']),
			'tel'=>C('CALL_NUMBER'),
			'img'=>$weixin_res['response']['headimgurl'],
		);
		return $this->endInvoke($res);
	}


	/**
	 * B2B个人中心 查看
	 * Bll.B2b.User.User.look
	 * @author Todor
	 * @access public
	 */

	public function look($params){
		try{
			D()->startTrans();
			$params['sys_name'] = B2B;
			$params['type'] = strtoupper($params['type']);
			$apiPath = "Base.StoreModule.Order.Operation.update";
			$res = $this->invoke($apiPath,$params);
			if($res['status'] != 0){
				return $this->endInvoke(NULL,$res['status']);
			}
			$commit_res = D()->commit();
			if($commit_res === FALSE){
				return $this->endInvoke(NULL,17);
			}
			return $this->endInvoke($res['response']);
		} catch (\Exception $ex) {
			D()->rollback();
			return $this->endInvoke(NULL,6058);
		}
	}


	/**
	 * wap 注册 微信首次登陆 添加微信信息
	 * @access private
	 */

	private function _addWeixinInfo($openid,$uc_code){
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
        $params  = array('uc_code'=>$uc_code);
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
            // 添加微信信息
	        try{
				D()->startTrans();
				$add_res = $this->invoke($apiPath,$data);
				if($add_res['status'] != 0){
					return $this->endInvoke(NULL,$add_res['status']);
				}
				$commit_res = D()->commit();
				if($commit_res === FALSE){
					return $this->endInvoke(NULL,17);
				}
				return TRUE;
			} catch (\Exception $ex) {
				D()->rollback();
				return $this->endInvoke(NULL,4063);
			}
        }else{
        	// return TRUE;
        	return $this->endInvoke(NULL,4063);
        }

    }

}

















