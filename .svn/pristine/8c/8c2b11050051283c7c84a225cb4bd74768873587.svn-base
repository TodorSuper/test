<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户扩展信息模块
 */

namespace Base\UserModule\User; 

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
     * Base.UserModule.User.User.userNum
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function userNum($uc_codes){
        $call = D('UcMember')->field('count(*) as num,um.invite_code,us.uc_code')->alias('um')->join("left join {$this->tablePrefix}uc_user uu on um.uc_code=uu.uc_code")->join("left join {$this->tablePrefix}uc_salesman us on um.invite_code=us.invite_code")->group('um.invite_code')->where(array('us.uc_code'=>array('in',$uc_codes),'uu.status'=>'ENABLE'))->select();
        $call = changeArrayIndex($call,'uc_code');
        $arr = array();
        foreach($uc_codes as $key=>$val){
            if($call[$val]['num']){
                $arr[$val]['num']=$call[$val]['num'];
            }else{
                $arr[$val]['num']=0;
            }
        }
        return $this->res($arr);
    }
    /**
     * Base.UserModule.User.User.platformSaleman
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function platformSaleman($params){
        $this->_rule = array(
            array('sales_uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('real_name', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('is_page', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), //是否分页

            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $page_number = $params['page_number'];
        $real_name   = $params['real_name'];
        $page        = $params['page'];
        $is_page     = $params['is_page'];
        $uc_code = $params['sales_uc_code'];

        $where = array();
        if (isset($real_name) && !empty($real_name)) {
            $where['uc.real_name'] = $params['real_name'];
        }
        if (isset($uc_code) && !empty($uc_code)) {
            $where['uc.uc_code'] = $params['sales_uc_code'];
        }
        // $where['ac.roles'] = ','.CMS_SC_PLATFROM_USER.',';
        $where['ac.roles'] = array('like',"%,".CMS_SC_PLATFROM_USER."%");
        $order  = 'uc.create_time desc';
        $fields = 'uc.id,uc.uc_code,uc.mobile,uc.real_name,us.num,us.invite_code,us.qcode,uc.status';
        if($is_page == 'NO'){
            //不分页则直接查
            $item_info = D('ac_principal')->alias('ac')
                                    ->join("{$this->tablePrefix}uc_user uc ON uc.id = ac.uid",'LEFT')
                                    ->join("{$this->tablePrefix}uc_salesman us ON uc.uc_code = us.uc_code",'LEFT')
                                    ->where($where)
                                    ->order($order)
                                    ->field($fields)
                                    ->select();                     
             return $this->res($item_info);
        }

        $data = array();
        $data['order']       = $order; //排序
        $data['where']       = $where; //where条件
        $data['fields']      = $fields; //查询字段
        $data['center_flag'] = SQL_UC; //店铺中心   
        $data['sql_flag']    = 'cms_platformSaleman';  //sql标示
        $data['page']        = $page;
        $data['page_number'] = empty($page_number) ? 20 : $page_number;

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $data);
        if ($list_res['status'] != 0) {
            return $this->res(null, $list_res['status']);
        }
        return $this->res($list_res['response']);
    }

    /**
     * Base.UserModule.User.User.getQrcode
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getQrcode($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),

            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $where['uc_code'] = $uc_code;
        $qrcodes = D('UcSalesman')->where($where)->find();
        if ($qrcodes === false || empty($qrcodes)) {
            return $this->res(NULL, 8);
        }
        return $this->res($qrcodes);
    }
    /**
     * 设置平台业务员状态
     * Base.UserModule.User.User.setPlatformSaleman
     * @param [type] $params [description]
     */
    public function setPlatformSaleman($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('uid', 'require', PARAMS_ERROR, MUST_CHECK),
            array('status', 'require', PARAMS_ERROR, MUST_CHECK),
            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $uid     = $params['uid'];
        $status  = $params['status'];
        if (!isset($status) || empty($status)) {
            return $this->res(NULL, 18);
        }

        $data                = array();
        $data['status']      = $status;
        $data['update_time'] = NOW_TIME;

        if (isset($uc_code) && !empty($uc_code)) {
            $ucWhere = array();
            $ucWhere['uc_code'] = $uc_code;
        }else{
            return $this->res(NULL, 18);
        }

        $ucRes = D('UcUser')->where($ucWhere)->save($data);
        if ($ucRes === false || $ucRes <= 0) {
            return $this->res(NULL, 8);
        }
        $umRes = D('UcSalesman')->where($ucWhere)->save($data);
        if ($umRes === false || $umRes <= 0) {
            return $this->res(NULL, 8);
        }
        return $this->res(true);
    }

    /**
     * 设置邀请码
     * Base.UserModule.User.User.setPlatInviteCode
     * @param [type] $params [description]
     */
    public function setPlatInviteCode($params){

        // $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        //生成邀请码
        $invite_code = mt_rand(1000,9999);
        $checkData = array(
            'invite_code' => $invite_code,
            );
        $checkRes = D('UcSalesman')->where($checkData)->find();
        if (!empty($checkRes) || $checkRes === false) {
            return $this->res(NULL, 6719);
        }

       //生成二维码  generateQrcodeByUrl($url, '', 100, $goods_img);
        $url = C('CHANNEL_QRCODE_URL')."Register/index/type/salesman/invite_code/{$invite_code}";
        $Qrcode = new \Library\qrcodes();
        $qrcode_url = $Qrcode->generateQrcodeByUrl($url, '', 100);
        if(empty($qrcode_url)){
           return $this->res(NULL,6707);
        }

        //上传到阿里云
        $img_url  = upload_cloud($qrcode_url);
        if(empty($img_url)){
           return $this->res(NULL,6708);
        }
        
        $data   = array(
            'update_time' => NOW_TIME,
            'uc_code'     => $uc_code,
            'invite_code' => $invite_code,
            'qcode'       => $img_url,
        );
        $where = array();
        $where['uc_code'] = $uc_code;
        
        $userRes = D('UcSalesman')->where($where)->find();
        if ($userRes === false) {
            return $this->res(NULL, 6720);
        }

        if (empty($userRes)) {
            $data['create_time'] = NOW_TIME;
            $data['status'] = 'ENABLE';
            $userRes = D('UcSalesman')->add($data);
            if ($userRes === false || $userRes <= 0) {
                return $this->res(NULL, 6721);
            }
        }else{
            $res = D('UcSalesman')->where($where)->save($data);
            if($res <= 0  || $res === FALSE){
                return $this->res(NULL,6701);
            }
        }
        
        return $this->res(true);
    }
    
    /**
     * 获取平台业务员信息
     * Base.UserModule.User.User.getPlatformSalesman
     * @param [type] $params [description]
     */
    public function getPlatformSalesman($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('invite_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $invite_code = $params['invite_code'];
        
        $where = array();
        if(!empty($uc_code)){
            $where['uu.uc_code'] = $uc_code;
        }
        if(!empty($invite_code)){
            $where['us.invite_code'] = $invite_code;
        }
        
        $salesman_res = D('UcSalesman')->alias('us')
                ->join("{$this->tablePrefix}uc_user uu on us.uc_code = uu.uc_code", 'LEFT')
                ->field('us.*,uu.username,uu.real_name,uu.mobile')
                ->where($where)
                ->find();
        return $this->res($salesman_res);
    }

    /**
     * 设置用户为终端买家或取消终端买家
     * Base.UserModule.User.User.setTerminal
     * @param [type] $params [description]
     */
    public function setTerminal($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('terminal', 'require', PARAMS_ERROR, MUST_CHECK),
        );

        $uc_code = $params['uc_code'];
        $terminal = $params['terminal'];

        $data = array(
            'terminal'=>$terminal
        );

        $res = D('UcMember')->where(array('uc_code'=>$uc_code))->save($data);

        if($res === false){
            return $this->res('',2011);
        }

        return $this->res(true);
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
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
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
        $ucCode = $params['uc_code'];
        $data = $params['data'];
        $userType = $params['userType'];

		$userTable = self::getUserTable($userType);  //获取uc用户表名
		if($params['userType'] == UC_USER_MERMBER) {
			$where = array(
				'uc_code' =>$ucCode
			);
		}else {
			$where = array(
				'id' =>$ucCode
			);
			
		}
   
        $res = D($userTable)->where($where)->save($data);
      
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
        $data = array('uc_code'=>$uc_code);
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
			array('username', 'require' , PARAMS_ERROR, MUST_CHECK ),  # 用户名				* 必须字段
			array('password', 'require' , PARAMS_ERROR, ISSET_CHECK ),  # 密码			
			array('sysName', 'require' , PARAMS_ERROR, ISSET_CHECK ),  # 要登陆到的系统		非必需 (不传入的话默认取调用系统名称的值)
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		# 获取调用系统名称
		$sysName = $data['sysName'] ?$data['sysName'] : $this->_request_sys_name;


		# 获取基本用户信息
		$find = D('UcUser')->field('id,username,password,group_id,status,uc_code,real_name,mobile,email')->where(array('username'=>$data['username']))->find();
		if($find == null)  {
            if($sysName == BOSS){
                $login_key  = \Library\Key\RedisKeyMap::getLogin($data['username']);
                $redis = R();
                $num = $redis->get($login_key);
                $num = empty($num) ? 1 : (int)$num+1;
                $redis->set($login_key,$num);
                $redis->expire($login_key, 300);
            }
			return $this->res('', 4024);
		}

		# 是否禁用
		if($find['status'] != 'ENABLE') {
			return $this->res('', 4026);
		}

		# 判断用户密码是否正确 B2B不判断密码的正确性
		if( encrypt_password($data['password']) != $find['password'] && $sysName != 'B2B') {
            if($sysName == BOSS){
                $login_key  = \Library\Key\RedisKeyMap::getLogin($data['username']);
                $redis = R();
                $num = $redis->get($login_key);
                $num = empty($num) ? 1 : (int)$num+1;
                $redis->set($login_key,$num);
                $redis->expire($login_key, 300);
            }
			return $this->res('', 4025); # 密码不正确
		}

		switch($sysName) {
		case POP:

			if($find['group_id'] != MERCHANT_GROUP && $find['group_id'] != SUB_ACCOUNT_GROUP) {

				return $this->res('', 4028); # 用户组不正确
			}
            $api = 'Base.StoreModule.User.SubAccount.findOne';
            $res = $this->invoke($api,['uc_code'=>$find['uc_code']]);
            $res = $res['response'];
            if(!$res && $find['group_id'] == MERCHANT_GROUP)
            {
                $res = D('ScStore')->field('uc_code,tc_code,status,sc_code,name,province,city,area,address,account_bank,account_no,account_name,logo,tpl_status')->where(array('uc_code'=>$find['uc_code']))->find();

                if($res === null) {
                    return $this->res('', 4027); # 商户不存在
                }
                if($res['status'] != 'ENABLE') {
                    return $this->res('', 4029); # 商户被禁用
                }

                //默认创建登录角色
                $api = 'Bll.Pop.User.SubAccount.addDefaultRoles';
                $this->invoke($api,['uc_code'=>$res['uc_code'],'sc_code'=>$res['sc_code']]);

            }

            if(!$res){
                return $this->res('', 5525);
            }
            if($find['status'] != 'ENABLE') {
                return $this->res('', 4029); # 用户被禁用
            }
			$find = array_merge($find, $res);

			break;

		case B2B:
			# 跟微信相关的自己写吧
			if($find['group_id'] != MERMBER_GROUP) {
				return $this->res('', 4028); # 用户组不正确
			}
			$field = 'um.commercial_name,um.username as linkman,um.province,um.city,um.district';
			$res = D('UcMember')->alias('um')->field($field)->where(['uc_code'=>$find['uc_code']])->find();
			if(!$res) {
				return $this->res('', 4019); # 用户基本信息不存在
			}
			$find = array_merge($find, $res);
			break;

        case BOSS:
            $this->startOutsideTrans();
            $login_key  = \Library\Key\RedisKeyMap::getLogin($data['username']);  # 错误次数清0
            $redis = R();
            $redis->set($login_key,0);

            if($find['group_id'] != MERCHANT_GROUP) {
                return $this->res('', 4028); # 用户组不正确
            }
            // 获取sc_code
            $apiPath = "Base.StoreModule.Basic.Store.get";
            $res = $this->invoke($apiPath,$find);
            if($res['status'] != 0){
                return $this->res('',$res['status']);
            }
            $sc_code = $res['response']['sc_code'];

            // 获取用户操作表 无则添加
            $map['sc_code'] = $sc_code;
            $map['uc_code'] = $find['uc_code'];
            $options = D('UcOptions')->where($map)->find();
            if(empty($options)){
                $options = array(
                    'sc_code'=>$sc_code,
                    'uc_code'=>$find['uc_code'],
                    'push_msg'=>'ON',
                    'prompt_sound'=>'ON',
                    'show_img'=>'ON',
                    'create_time'=>NOW_TIME,
                    'update_time'=>NOW_TIME,
                );
                $apiPath = "Base.UserModule.User.User.options";
                $res = $this->invoke($apiPath,$options);
                if($res['status'] != 0){
                    return $this->res('',$res['status']);
                }
            }

            // 查看设备类型 如果不同则添加或更新
            $device_data = array(
                'sc_code'=>$sc_code,
                'uc_code'=>$find['uc_code'],
                'device'=>$data['device'],
                'device_token'=>$data['device_token'],
                );

            $apiPath = "Base.UserModule.User.User.checkDevice";
            $device_res = $this->invoke($apiPath,$device_data);
            if($device_res['status'] != 0){
                return $this->res('',$device_res['status']);
            }


            $msg = array(
                'sc_code'=>$sc_code,
                'uc_code'=>$find['uc_code'],
                'device'=>$data['device'],          # 传入的设备类型
                'role'=>'超级管理员',              
                'push_msg'=>$options['push_msg'],
                'prompt_sound'=>$options['prompt_sound'],
                'show_img'=>$options['show_img'],
                'bind_phone'=>$find['mobile'],
                'counsel_phone'=>C('CALL_NUMBER'),
                );
            $find = $msg;
            break;
		case CMS:
            if($find){
                //更新登录时间
                $data = array(
                    'login_time'=>NOW_TIME,
                );
               $res = D('UcUser')->where(array('id'=>$find['id']))->save($data);
                if($res===false){
                    return $this->res('',2010);
                }
            }
            // do nothing
            // modified by liaoxianwen
            break;
		default:
			return $this->res('', 4023);
		}

		unset($find['password']);
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
	 * 通过openid获取用户基本信息
	 * Base.UserModule.User.User.getUserInfoByOpenid 
	 * @access public
	 * @return void
	 */
	public function getUserInfoByOpenid($data) {
		if( empty($data['openid']) ) {
			return $this->res('openid', PARAMS_ERROR); # 缺少openid
		}
		$find = D('UcUser')->alias('uc')->field('uc.username,uc.uc_code')->join('left join __UC_WEIXIN__ as uw on uc.uc_code=uw.uc_code')->where(['uw.open_id'=>$data['openid']])->find();
		return $this->res($find);
	}

    /**
     * 通过uc_code获取小B用户的邀请码
     * Base.UserModule.User.User.getInviteCode
     * @access public
     * @return void
     */
    public function getInviteCode($data){
        if(empty($data['uc_code'])){
            return $this->res('uc_code',PARAMS_ERROR);
        }
        $result=D('UcMember')->field('invite_code,pay_privs')->where(array('uc_code'=>$data['uc_code']))->select();
        return $this->res($result);
    }
    
    /**
     * 获取双磁对接人
     * Base.UserModule.User.User.getSalesmanList
     * @param type $params
     */
    public function getSalesmanList($params){
        $salesman = D('UcMerchant')->group('salesman')->where(array('salesman'=>array('neq','')))->field('salesman')->select();
        return $this->res($salesman);
    }
    
    public function getMerchantInfo($params){
        $this->_rule = array(
			array('merchant_id', 'require' , PARAMS_ERROR, MUST_CHECK ),  # 
		);
		
		if(!$this->checkInput($this->_rule, $data)){ # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
                }
    }



    /**
     * @api  Boss添加用户操作信息
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.options
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function options($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码
            array('push_msg', 'require', PARAMS_ERROR, MUST_CHECK),     # 推送消息
            array('prompt_sound', 'require', PARAMS_ERROR, MUST_CHECK), # 提示声音    
            array('show_img', 'require', PARAMS_ERROR, MUST_CHECK),     # 展示商品图片
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $res = D('UcOptions')->add($params);
        if($res <= 0 || $res == FALSE){
            return $this->res(NULL,4040);
        }

        return $this->res(true);

    }


    /**
     * @api  Boss添加用户设备类型
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.deviceAdd
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function deviceAdd($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码
            array('device_token', 'require', PARAMS_ERROR, ISSET_CHECK), # 设备唯一标识
            array('device', 'require', PARAMS_ERROR, MUST_CHECK),       # 设备类型    
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $data = array(
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'sc_code'=>$params['sc_code'],
            'uc_code'=>$params['uc_code'],
            'device_token'=>$params['device_token'],
            'device'=>$params['device'],
            );
        $res = D('UcDevice')->add($data);
        if($res <= 0 || $res == FALSE){
            return $this->res(NULL,4041);
        }
        return $this->res(true);
    }


    /**
     * @api  Boss更新用户设备类型
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.deviceUpdate
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function deviceUpdate($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码
            array('device_token', 'require', PARAMS_ERROR, ISSET_CHECK), # 设备唯一标识
            array('device', 'require', PARAMS_ERROR, MUST_CHECK),       # 设备类型    
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $map['sc_code'] = $params['sc_code'];
        $map['uc_code'] = $params['uc_code'];
        $data = array(
            'device_token'=>$params['device_token'],
            'device'=>$params['device'],
            'update_time'=>NOW_TIME,
            );
        $res = D('UcDevice')->where($map)->save($data);
        if($res <= 0 || $res == FALSE){
            return $this->res(NULL,4042);
        }
        return $this->res(true);

    }


    /**
     * @api  Boss查看用户设备类型
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.getDevice
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-26
     * @apiSampleRequest On
     */

    public function getDevice($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码
            array('device_token', 'require', PARAMS_ERROR, ISSET_CHECK), # 设备唯一标识
            array('device', 'require', PARAMS_ERROR, ISSET_CHECK),      # 设备类型  
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $map['sc_code'] = $params['sc_code'];
        $map['uc_code'] = $params['uc_code'];
        !empty($params['device_token']) && $map['device_token'] = $params['device_token'];
        !empty($params['device']) && $map['device'] = $params['device'];
        $res = D('UcDevice')->where($map)->find();

        if($res == FALSE){
            return $this->res(NULL,4055);
        }
        
        return $this->res($res);

    }


    /**
     * @api  Boss查看用户设置
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.getOptions
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function getOptions($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码 
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['sc_code'] = $params['sc_code'];
        $where['uc_code'] = $params['uc_code'];
        $options = D('UcOptions')->where($where)->find();
        if($options == FALSE){
            return $this->res(NULL,4043);
        }
        return $this->res($options);
    }


    /**
     * @api  Boss查看用户设置
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.updateOptions
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function updateOptions($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),       # 店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),       # 客户编码
            array('push_msg', 'require', PARAMS_ERROR, ISSET_CHECK),     # 推送消息
            array('prompt_sound', 'require', PARAMS_ERROR, ISSET_CHECK), # 提示声音    
            array('show_img', 'require', PARAMS_ERROR, ISSET_CHECK),     # 展示商品图片
        );

        $where['sc_code'] = $params['sc_code'];
        $where['uc_code'] = $params['uc_code'];

        $data = array();
        isset($params['push_msg']) && $data['push_msg'] = $params['push_msg'];
        isset($params['prompt_sound']) && $data['prompt_sound'] = $params['prompt_sound'];
        isset($params['show_img']) && $data['show_img'] = $params['show_img'];
        $data['update_time'] = NOW_TIME;

        $res = D('UcOptions')->where($where)->save($data);

        if($res <= 0 || $res == FALSE){
            return $this->res(NULL,4053);
        }
        return $this->res(true);

    }



    /**
     * @api  APP 首页与登陆页弱网登陆 获取不到设备唯一标识
     * @apiVersion 1.0.1
     * @apiName Base.UserModule.User.User.checkDevice
     * @apiTransaction N
     * @apiAuthor Todor <tudou@liangrenwang.com>
     * @apiDate 2015-10-21
     * @apiSampleRequest On
     */

    public function checkDevice($params){

        $map['sc_code'] = $params['sc_code'];
        $map['uc_code'] = $params['uc_code'];
        !empty($params['device_token']) && $map['device_token'] = $params['device_token'];   # 设备唯一标识
        $map['device'] = $params['device'];               # 设备类型
        $device = D('UcDevice')->where($map)->find();

        if(empty($device)){
            // 检查是否需要更新
            unset($map['device_token']);
            unset($map['device']);

            $device_change = D('UcDevice')->where($map)->find();

            $device = array(
                'sc_code'=>$params['sc_code'],
                'uc_code'=>$params['uc_code'],
                'device_token'=>$params['device_token'],
                'device'=>$params['device'],
                );

            if(empty($device_change)){    # 增加
                $apiPath = "Base.UserModule.User.User.deviceAdd";
            }else{                        # 更新
                $apiPath = "Base.UserModule.User.User.deviceUpdate";
            }

            $res = $this->invoke($apiPath,$device);
            if($res['status'] != 0){
                return $this->res('',$res['status']);
            }
        }
    }
    public function deleteSeller($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 编码
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $ucUser = D('UcUser')->where(array('uc_code'=>$uc_code))->find();
        $ucMember = D('UcMember')->where(array('uc_code'=>$uc_code))->find();
        
        if ( !empty($ucUser) ) {
            $ucUser['delete_time'] = NOW_TIME;
            D('UcUserDelete')->add($ucUser);
        }
        if ( !empty($ucMember) ) {
            $ucMember['delete_time'] = NOW_TIME;
            D('UcMemberDelete')->add($ucMember);
        }
        $redis = R();
        $userkey = \Library\Key\RedisKeyMap::userInfo($uc_code);
        $sessionKey = \Library\Key\RedisKeyMap::userSessionId($uc_code);
        $sessionValue = $redis->hGet($userkey,$sessionKey);
        $redis->select(15);
        $ress = $redis->delete('PHPREDIS_SESSION:'.$sessionValue);
        
        $sql = "UPDATE 
                    16860_uc_user uu
                    LEFT JOIN 16860_uc_member um ON  uu.uc_code = um.uc_code
                    LEFT JOIN 16860_uc_weixin uw ON  um.uc_code = uw.uc_code
                SET  
                    uu.username = concat(uu.username,'_',uu.id,'_','delete'),
                    um.username = concat(um.username,'_',uu.id,'_','delete'),
                    uw.open_id  = concat(uw.open_id,'_',uw.id),
                    uu.status   = 'DISABLE'
                WHERE 
                    uu.uc_code = '$uc_code'";
        $res = D()->master()->query($sql);    
        if ($res > 0 ) {
            return $this->res(true);
        } else {


            return $this->res(null,4015);
        }
        
    }

    /**
     * 获取用户业务员编码
     * Base.UserModule.User.User.getSalesman
     */
    public function getSalesman($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码 
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        // 根据uc_code 获取业务员姓名
        $salesman = D('UcMember')->alias('um')->field('uu.*,us.invite_code')
                                 ->join("{$this->tablePrefix}uc_salesman us ON um.invite_code = us.invite_code ",'LEFT')
                                 ->join("{$this->tablePrefix}uc_user uu ON uu.uc_code = us.uc_code ",'LEFT')
                                 ->where(array('um.uc_code'=>$uc_code))
                                 ->find();
        return $this->endInvoke($salesman);
    }


}
?>
