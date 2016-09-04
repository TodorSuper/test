<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */

namespace Base\StoreModule\Basic;

use System\Base;

class User extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }

    /**
     * 添加用户扩展信息
     * Base.UserModule.User.User.add
     * @param array $data   用户数据
     * @param integer $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @return integer   成功时返回  自增id
     */
    public function add($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),
            array('userType', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = $params['data'];
        $userType = $params['userType'];

        //业务表示号  和  预留业务表示  的判定
        $userTable = self::getUserTable($userType);  //获取uc用户表名
        if (false === $userTable) {
            return $this->res(null, 4002);
        }
        //添加用户
        $res = D($userTable)->add($data);
        if ($res <= 0 || false === $res) {
            return $this->res(null, 4001);
        }
        return $this->res($res);
    }

    /**
     * 修改用户扩展信息
     * Base.UserModule.User.User.update
     * @param array $data   用户信息
     * @param intger $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @param intger $ucCode   用户编码
     * @return intger
     */
    public function update($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),
            array('userType', 'require', PARAMS_ERROR, MUST_CHECK),
            array('ucCode', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        //用户名，用户编码，自增id  不能更新  
        if (isset($params['data']['uc_code'])) {
            unset($params['data']['uc_code']);
        }
        if (isset($params['data']['username'])) {
            unset($params['data']['username']);
        }
        if (isset($params['data']['id'])) {
            unset($params['data']['id']);
        }

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $ucCode = $params['ucCode'];
        $data = $params['data'];
        $userType = $params['userType'];

        $userTable = self::getUserTable($userType);  //获取uc用户表名
        $res = D($userTable)->where(array('uc_code' => $ucCode))->save($data);
        if (false === $res) {
            return $this->res(null, 4003);
        }
        return $this->res($res);  //返回影响函数    0  行 或  1行
    }

    /**
     * 删除或禁用 用户扩展信息
     * Base.UserModule.User.User.delete
     * @param array $status   修改状态信息
     * @param intger $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @param intger $ucCode   用户编码
     * @return intger
     */
    public function delete($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $userTable = self::getUserTable($userType);  //获取uc用户表名
        $res = D($userTable)->where(array('uc_code' => $uc_code))->save(array('status' => 'DISABLE', 'update_time' => NOW_TIME));
        if (false === $res || $res <= 0) {
            return $this->res(null, 4015);
        }
        return $this->res($res);
    }

    /**
     * 获取用户的所有信息
     * Base.UserModule.User.User.getFullUserInfo
     * @param intger $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @return intger
     */
    public function getFullUserInfo($params) {
        $this->_rule = array(
            array('open_id', 'require', PARAMS_ERROR, ISSET_CHECK), //微信的open_id  获取微信信息的时候有可能需要该参数
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('user_type', 'require', PARAMS_ERROR, MUST_CHECK), //用户类型  UC_USER_MERCHANT     UC_USER_MERCHANT 
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $uc_code = $params['uc_code'];
        $open_id = $params['open_id'];
        $user_type = $params['user_type'];

        if (empty($open_id) && empty($uc_code)) {
            return $this->res(null, 4016);
        }
        
        $full_user_info = array();
        
        //如果是微信用户 

        if ($user_type == UC_USER_MERMBER) {
            $weixin_info = $this->getWeixinUserInfo($open_id, $uc_code);
            $uc_code = $weixin_info['uc_code'];
        }

        //获取用户基础信息
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $data = array('uc_ciode'=>$uc_code);
        $basic_res = $this->invoke($apiPath, $data);
        if($basic_res['status'] != 0){
            return $this->res($basic_res['response'],$basic_res['status']);
        }
        $basic_user_info = $basic_res['response'];
        
        //基本信息
        $full_user_info['username'] = $basic_user_info['username'];
        $full_user_info['uc_code'] = $basic_user_info['uc_code'];
        $full_user_info['real_name'] = $basic_user_info['real_name'];
        $full_user_info['mobile'] = $basic_user_info['mobile'];
        $full_user_info['email'] = $basic_user_info['email'];
        $full_user_info['create_time'] = $basic_user_info['create_time'];
        $full_user_info['cms_group_id'] = $basic_user_info['group_id'];   //cms平台的group_id   区分微信用户的group_id
        $full_user_info['basic_status'] = $basic_user_info['status'];     //基础用户状态  是否删除
        
        //获取扩展信息
        $extend_user_table = self::getUserTable($user_type);
        //获取用户扩展信息
        $extend_user_info = D($extend_user_table)->where(array('uc_code'=>$uc_code))->find();
        
        //处理扩展信息
        $function = "get{$extend_user_table}ExtendInfo";
        $extend_info = $this->$function($extend_user_info);
        if(!empty($extend_info)){
           $full_user_info = array_merge($full_user_info,$extend_info);
        }
        
        //其他信息  如店铺  或者  微信信息
        
        return $this->res($full_user_info);
        
        
    }

    /**
     * 获取相应用户类型的表名
     * @param type $userType  用户类型
     * @return boolean
     */
    private static function getUserTable($userType) {
        $userTable = '';
        switch ($userType) {
            case UC_USER_MERCHANT:    //商户
                $userTable = 'Merchant';
                break;
            case UC_USER_MERMBER:     //用户
                $userTable = 'Member';
                break;
            default :
                return false;  //返回 4002 错误码
        }

        return self::$uc_prefix . $userTable;
    }

    /**
     * 获取微信用户信息
     * @param type $open_id
     * @param type $uc_code
     * @return type
     */
    private function getWeixinUserInfo($open_id, $uc_code = '') {
        $apiPath = "Base.WeiXinModule.User.User.getWeixinInfo";
        if (empty($uc_code)) {
            $data = array('open_id' => $open_id);
        } else {
            $data = array('uc_code' => $uc_code);
        }
        $weixin_user_res = $this->invoke($apiPath, $data);
        if ($weixin_user_res['status'] != 0 || empty($weixin_user_res['response'])) {
            return $this->endInvoke($weixin_user_res['response'], $weixin_user_res['status']);
        } 
        
        $weixin_user_info = $weixin_user_res['response'];
        return $weixin_user_info;
        
    }

	/**
	 * api登录接口,验证登录账号和密码的正确性
	 * login 
	 * Base.UserModule.User.User.login
	 * @param mixed $data
	 * @access public
	 * @return void
	 */

	public function login($data) {
		$this->_rule = array(
			array('username', 'require' , PARAMS_ERROR, MUST_CHECK ),  # 用户名		* 必须字段
			array('password', 'require' , PARAMS_ERROR, MUST_CHECK ),  # 密码		* 必须字段
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 获取基本用户信息
		$find = D('UcUser')->field('username,password,group_id,status,uc_code,real_name,mobile,email')->where(array('username'=>$data['username']))->find();
		if($find == null)  {
			return $this->res('', 4024);
		}
		
		# 是否禁用
		if($find['status'] != 'ENABLE') {
			return $this->res('', 4026);
		}

		# 判断用户密码是否正确
		if( encrypt_password($data['password']) != $find['password'] ) {
			return $this->res('', 4025); # 密码不正确
		}

		switch($find['group_id']) {
		case MERCHANT_GROUP:
			$res = D('UcMerchant')->field('tc_code,status')->where(array('uc_code'=> $find['uc_code']))->find();
			if($res === null) {
				return $this->res('', 4027); # 商户不存在
			}

			if($res['status'] != 'ENABLE') {
				return $this->res('', 4028); # 商户被禁用
			}
			$find['tc_code'] = $res['tc_code'];
			break;

		case MERMBER_GROUP:
			# 跟微信相关的自己写吧
			break;

		default:
			return $this->res('', 4023);
		}
		
		return $this->res($find);

	}
    
    /**
     * 处理普通用户 的扩展信息
     * @param type $ori_info
     */
    private function getUcMemberExtendInfo($ori_info){
        $user_info = array();
        $user_info['extend_status'] = $ori_info['status'];
        return $user_info;
    }
    
    /**
     * 处理 商户 的扩展信息
     * @param type $ori_info
     */
    private function getUcMerchantExtendInfo($ori_info){
        $user_info = array();
        $user_info['sc_code'] = $ori_info['sc_code'];
        $user_info['extend_status'] = $ori_info['status'];
        return $user_info;
    }

    /**
     * 处理 商户 的扩展信息
     *
     * 专业提供列表
     */
    public function getMerchantLists($params) {
        //如果传入sc_code那么直接查出名字返回；
		if(!empty($params['sc_code'])){
			if( is_array($params['sc_code']) ) {
				$where = array('sc_code'=>['in', $params['sc_code']]);
			}else {
				$where = array('sc_code'=>$params['sc_code']);
			}
            $res = D('ScStore')->field(array('sc_code','name'))->where($where)->select();
        }else{
            $res = D('ScStore')->field(array('sc_code','name'))->select();

        }
        return $this->res($res);
	}

	/**
	 * Base.StoreModule.Basic.User.getMerchanInfo
	 * 获取商户明细
	 * getMerchanInfo 
	 * @access public
	 * @return void
	 */
	public function getMerchanInfo($data) {
		$sc_code = $data['sc_code'];
		$find = D('ScStore')->where(['sc_code'=>$sc_code])->find();
		return $this->res($find);
	}



}
?>
