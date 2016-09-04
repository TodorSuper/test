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

namespace Bll\Pop\User;
use System\Base;

class User extends Base {

	private $_rule = null; # 验证规则列表

    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
	/**
	 * api登录接口,验证登录账号和密码的正确性
	 * login 
	 * Bll.Pop.User.User.login
	 * @param mixed $data
	 * @access public
	 * @return void
	 */

	public function login($data) {
        $key = \Library\Key\RedisKeyMap::getSpcKey('login_count');
        $login_count=R()->get($key);
        if($login_count>2){
            if(!$data['verifycode']){
                $arr['login_count']=$login_count;
                return $this->endInvoke($arr,4059);
            }
        }
		$res = $this->invoke('Base.UserModule.User.User.login', $data);
        if($res['status']!==0){
            R()->incr($key);
            $num=R()->get($key);
            $res['response']['login_count']=$num;
        }
		if($res['status'] == 0){
            R()->set($key,0);
            $num=R()->get($key);
            $res['response']['login_count']=$num;
			$params = array(
				'sc_code' => $res['response']['sc_code'],
				'time'=> NOW_TIME,
			);
			$this->invoke('Base.StoreModule.Order.Operation.addLastTime',$params);
            try {
                 D()->startTrans();
                 $ucData = array(
                    'uc_code' =>$res['response']['uc_code'],
                    'login_time' => NOW_TIME, 
					'sys_name' => POP,
                );
                $this->invoke('Base.UserModule.User.Basic.update',$ucData);
                D()->commit();
            } catch (\Exception $ex) {
                D()->rollback();
                return $this->endInvoke(NULL,5526);
            }
		}
		return $this->res($res['response'], $res['status']);
	}

	/**
	 * 修改商家信息
	 * edit
	 * Bll.Pop.User.User.edit
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */
	public function edit($data) {
	
		if( !$data['uc_code'] ) {
			return $this->res('', 5507);
		}
		try{
			D()->startTrans();
			# 修改店铺信息
			$updateStore = $this->invoke('Base.StoreModule.Basic.Store.update', ['data'=>$data, 'uc_code'=>$data['uc_code'] ] );
			if($updateStore['status'] != 0 ) {
				return $this->res($updateStore['response'], $updateStore['status']); # 修改店铺信息失败
			}

			$merchant_id = $updateStore['response']['merchant_id'];

			# 修改商家信息
			$merchantData = array(
				'data'=>array(
					'linkman' => $data['linkman_merchant'],
					'phone' => $data['phone_merchant'],
					'salesman' => $data['salesman'],
					'merchant_name' => $data['merchant_name'],
					'short_name' => $data['short_name']
				),
				'userType' => UC_USER_MERCHANT,
				'uc_code' => $merchant_id
			);
			$updateMerchant = $this->invoke('Base.UserModule.User.User.update', $merchantData);

			if($updateMerchant['status'] != 0 ) {
				return $this->res($updateMerchant['response'], $updateMerchant['status']); # 修改商家资料失败
			}
			
			$label = $this->invoke('Base.StoreModule.Basic.Label.saveLabels',array('sc_code'=>$updateStore['response']['sc_code'],'label_id'=>$data['label_id'],'action'=>'update'));

			if ($label['status'] != 0) {
				return $this->res(null,$label['status']);
			}
			# 提交数据
			$commit = D()->commit();
			if($commit == false) {
				return $this->res('', 5503); # 系统繁忙
			}

		}catch(\Exception $e) {
			$e->getMessage();
			D()->rollback();
			return $this->res('', 5504); # 系统繁忙
		}

		return $this->res(true); # ok

	}
	/**
	 * 添加商户
	 * Bll.Pop.User.User.add
	 * @access public
	 * @return void
	 */
	public function add($data) {
		try{
			D()->startTrans();
			# 创建用户
			$userData['username'] = $data['username'];
			$userData['mobile'] = $data['phone'];
			$userData['real_name'] = $data['linkman'];
			$userData['pre_bus_type'] = UC_USER_MERCHANT;
			$createUser = $this->invoke('Base.UserModule.User.Basic.add', $userData);
			if($createUser['status'] != 0 ) {
				return $this->res('', 5500); # 创建用户失败
			}
			$data['uc_code'] = $createUser['response']['uc_code'];
			# 写入商户信息
			$merchantData = array(
				'data'=>array(
					'linkman' => $data['linkman_merchant'],
					'phone' => $data['phone_merchant'],
					'salesman' => $data['salesman'],
					'merchant_name' => $data['merchant_name'],
					'short_name' => $data['short_name']
				),
				'userType' => UC_USER_MERCHANT,
			);
			$createMerchant = $this->invoke('Base.UserModule.User.User.add', $merchantData);
			if($createMerchant['status'] != 0 || $createMerchant['response'] <= 0) {
				return $this->res('', 5501); # 创建商户失败
			}

			// 不再创建资金帐户			
			# 初始化资金账户
/*			$createAccount = $this->invoke('Base.TradeModule.Account.Balance.add', ['uc_code'=>$data['uc_code'], 'accountType'=>"MERCHANT" ] );
			if($createAccount['status'] != 0 ) {
				return $this->res('', 5502); # 创建资金帐户失败
			}*/
			
			# 初始化店铺信息
//			$data['tc_code'] = $createAccount['response'];
			$data['merchant_id'] = $createMerchant['response'];
			$storeData = array(
				'data' => $data,
				'pre_bus_type' => POP_CODE_SC,
				'uc_code' => $data['uc_code'],
				'is_show' => $data['is_show'],
			);
			$createStore = $this->invoke('Base.StoreModule.Basic.Store.add', $storeData);
			if($createStore['status'] != 0 ) {
				return $this->res('', 5502); # 创建资金帐户失败
			}
			//添加卖家标签
			if (!empty($data['label_id'])) {
				$label = $this->invoke('Base.StoreModule.Basic.Label.saveLabels',array('sc_code'=>$createStore['response'],'label_id'=>$data['label_id'],'action'=>'add'));
				if ($label['status'] != 0) {
				
					return $this->res(null,$label['status']);
				}
			}
			# 提交数据
			$commit = D()->commit();
			if($commit == false) {
				D()->rollback();
				return $this->res('', 5503); # 系统繁忙
			}

		}catch(\Exception $e) {
			$e->getMessage();
			D()->rollback();
			return $this->res('', 5504); # 系统繁忙
		}

		return $this->res(true); # 创建成功
	}

	/** Bll.Pop.User.User.sendMessage
	 * ajax发送验证码
	*/
	public function sendMessage($data){
		$sc_code = $data['sc_code'];
		$phone = $data['phone'];
		$uc_code = $data['uc_code'];
		$msg_code =  str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);//生成短信验证码
		$redis = R();
		$key= \Library\Key\RedisKeyMap::msgCount($sc_code,$uc_code,$phone);
		$info = json_decode($redis->get($key),true);
		$out_time = (strtotime(date('Y-m-d',NOW_TIME)))+ 86400-NOW_TIME;

		if(!$info){
			$count = 1;
			$redis->set($key,json_encode(['time'=>NOW_TIME,'count'=>$count,'msg_code'=>$msg_code]),$out_time);
		}

		if((NOW_TIME-$info['time'])<=60){
			$this->res(null,2004);
		}
		if($info['count']<3){
			$redis->set($key,json_encode(['time'=>NOW_TIME,'count'=>$info['count']+1,'msg_code'=>$msg_code]),$out_time);

		}else{
			$this->res(null,2005);
		}
		$resInfo['numbers'] = [$phone];
		$resInfo['sys_name'] = CMS;
		$resInfo['message'] = "您的校验码为：{$msg_code}";
		$info = $this->push_queue('Com.Common.Message.Sms.send', $resInfo, 0 );
		if(!$info){
			$this->res(null,'2006');
		}
		$this->res($resInfo);
	}

	/** Bll.Pop.User.User.CodeCheck
	 * ajax发送验证码
	 */
	public function codeCheck($data){
		$uc_code = $data['uc_code'];
		$phoneArrKey= [$data['a_phone']=>$data['a_code']];
		$redis = R();
		foreach($phoneArrKey as $k=>$v){
			$key = \Library\Key\RedisKeyMap::msgCount($data['sc_code'],$uc_code,$k);
			$res['data'] = $k;
			if(!$key) {

				$this->res($res,2007);
			}
			$info = json_decode($redis->get($key),true);
			if($info['msg_code'] != $v){

				$this->res($res,2008);
			}
			if((NOW_TIME-$info['time'])>=1800){

				$this->res($res,2009);
			}
		}

		$info['message'] = '验证通过';
		$this->res($info);
	}
	/** Bll.Pop.User.User.bankList
	 * 获取银行编码
	 */
	public function bankList($param) {
		$params = ['gateway' => C('BANK_PARAMS.gateway')];
		$apiPath = 'Base.PayCenter.Info.AccountInfo.BankList';
		$bankInfo = $this->invoke($apiPath, $params);
		//获取卖家标签标签
		$apiPath = 'Base.StoreModule.Basic.Label.getPopLabelData';
        $list_res = $this->invoke($apiPath,$param);
        //获取单个商家的标签ID
        if (!empty($param['sc_code'])) {
        	$apiPath = 'Base.StoreModule.Basic.Label.getLabels';
       		$labelId = $this->invoke($apiPath,array('sc_codes'=>$param['sc_code']));
       		$bankInfo['response']['lables'] = $labelId['response'];
        }
        
        $bankInfo['response']['labelList'] = $list_res['response'];
		return $this->endInvoke($bankInfo['response'], $bankInfo['status'], $bankInfo['message']);
	}

	/** Bll.Pop.User.User.getBankName
	 * 获取银行名称
	 */
	public function getBankName($params) {
		$params['gateway'] = C('BANK_PARAMS.gateway');
		$apiPath = 'Base.PayCenter.Info.AccountInfo.SearchBank';
		$bankInfo = $this->invoke($apiPath, $params);
		return $this->endInvoke($bankInfo['response'], $bankInfo['status'], $bankInfo['message']);
	}

}

?>
