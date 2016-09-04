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

class Store extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加店铺
     * Base.StoreModule.Basic.Store.add
     * @return integer   成功时返回  自增id
	 */

	public function add($params) {

		$this->startOutsideTrans();
		$this->_rule = array(
			array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),			# 写入数据  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),		# 用户编码  * 必须字段
            array('pre_bus_type', 'require', PARAMS_ERROR, MUST_CHECK), # 商户类型  * 必须字段  参考常量表
		);

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
			return $this->res($this->getErrorField(), $this->getCheckError());
		}
		
		# 生成店铺编码
		$apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
		$data = array(
			'busType'=> POP_CODE,
			'preBusType'=>$params['pre_bus_type'],
			'codeType'=> SEQUENCE_POP,
		);

		$code_res = $this->invoke($apiPath, $data);
		if($code_res['status'] !== 0 ) {
			return $this->res('', $code_res['status']);
		}
		$params['data']['sc_code'] = $code_res['response'];
		$params['data']['uc_code'] = $params['uc_code'];
		$params['data']['create_time'] = NOW_TIME;
		$params['data']['update_time'] = NOW_TIME;

		# 插入店铺数据
		$insert = D('ScStore')->add($params['data']);
		if($insert <= 0 ) {
			return $this->res('', 5506);
		}

		return $this->res($code_res['response']); # 返回店铺编码
	}

	/**
	 * 修改店铺信息
	 * update
	 * Base.StoreModule.Basic.Store.update
	 * @param mixed $data 
	 * @access public
	 * @return void
	 */

	public function update($data) {
		// var_dump($data);die();
        $this->startOutsideTrans();
		$this->_rule = array(
			array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),			# 写入数据  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),							# 用户编码  * 必须字段
			array('sign', 'require', PARAMS_ERROR, ISSET_CHECK),							# 用户编码  * 必须字段

		);
		if (!$this->checkInput($this->_rule, $data)) { # 自动校验
			return $this->res($this->getErrorField(), $this->getCheckError());
		}
		$where = ['uc_code' => $data['uc_code']];
		$find = D('ScStore')->field('merchant_id,sc_code')->where($where)->find();
		if( !$find ) {
			return $this->res('', 5508); # 用户不存在
		}

		if ($data['sign'] == 'POP') {
			$province = $data['data']['province'];
			$city = $data['data']['city'];
			$area = $data['data']['area'];
			
	        $data['update_time'] = NOW_TIME;
	        $where = array();
	        $where['id'] = array('in', array($province,$city, $area));
			$region = D('Region')->where($where)->select();
			if ($region == false || empty($region)) {
				return $this->res(NULL, 8);
			}
			$region = changeArrayIndex($region, 'id');
			if (!empty($region)) {
				foreach ($region as $key => $value) {
					if ($key == $province) {
						$data['data']['province'] = $value['name'];
					}
					if ($key == $city) {
						$data['data']['city'] = $value['name'];

					}
					if ($key == $area) {
						$data['data']['area'] = $value['name'];

					}
				}
			}
		}
		
		$scWhere = array();
		$scWhere['uc_code'] = $data['uc_code'];
		// $scWhere['sc_code'] = $data['sc_code'];
		$save = D('ScStore')->where($scWhere)->save($data['data']);
		
		if($save === false) {
			return $this->res('', 5509); # 修改失败
		}

		if (isset($data['name']) && empty($data['name'])) {
			$arr = array();
			$arr['merchant_name'] = $data['name'];
			$arr['update_time']   = NOW_TIME;
        	$merchant_info = D('UcMerchant')->where(array('id'=>$find['merchant_id']))->save();
        	if ($merchant_info === false) {
        		return $this->res(NULL, 5509);
        	}
		}

		return $this->res(['merchant_id'=>$find['merchant_id'],'sc_code'=>$find['sc_code']]); # ok

	}
    
    /**
     * 用于获取地区
     * Base.StoreModule.Basic.Store.getRegion
     * @param mixed $data 
     * @access public
     * @return void
     */
    public function getRegion($params){
    	$regions = D('Region')->order('pid asc')->where('id != 1')->select();
		$arr = $province = $city = $area = array();
    	$regions = changeArrayIndex($regions, 'id');
    	foreach ($regions as $key => $region) {
    		if ($region['pid'] <= 1) {
    			$province[$region['id']] = $region; 
    			
    		}else{
    			$value = $regions[$region['pid']];
    			if ($value['pid'] <= 1) {
    				$city[$region['pid']][] = $region;
    			}else{
    				$area[$region['pid']][] = $region;
    			}
    		}
    	}

		$arr['province'] = $province;
		$arr['city']    = $city;
		$arr['area']    = $area;
    	return $this->res($arr);
    }   
    /**
	 * 获取店铺信息
	 * Base.StoreModule.Basic.Store.get
	 * @param string sc_code
	 * @access public
	 * @return void
	 */
        public function get($params){
               $this->_rule = array(
			array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),							# 店铺编码  * 必须字段
			array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),							# 用户编码
                        array('detail', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),							# 是否需要更详细的信息
		);

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
			return $this->res($this->getErrorField(), $this->getCheckError());
		}
                $sc_code  =  $params['sc_code'];
                $uc_code = $params['uc_code'];
                $detail = $params['detail'];
                $where = array();
                !empty($sc_code) && $where['sc_code'] = $sc_code;
                !empty($uc_code) && $where['uc_code'] = $uc_code;
                $sc_info  =  D('ScStore')->where($where)->find();
                
                if(empty($sc_info)){
                    return $this->res(NULL,4027);
                }
                if($detail == 'YES'){
                    $merchant_info = D('UcMerchant')->field('short_name,salesman')->where(array('id'=>$sc_info['merchant_id']))->find();
                    if(!empty($merchant_info)){
                        $sc_info = array_merge($sc_info,$merchant_info);
                    }
                }
                return $this->res($sc_info);
        }

    /**
     * 用于获取双磁对接人
     * Base.StoreModule.Basic.Store.getMerchant
     * @param mixed $data
     * @access public
     * @return void
     */
      public function getMerchant($sc_code){
          $where['ss.sc_code']=$sc_code['sc_code'];
          $res = D('ScStore')->alias('ss')->field('um.salesman')->join("{$this->tablePrefix}uc_merchant as um on ss.merchant_id=um.id","left")->where($where)->find();
          return $this->res($res);
      }
        /**
         * Base.StoreModule.Basic.Store.lists
         * 商家列表
         * @access public
         * @author Todor
         */

        public function lists($params){
        	$this->_rule = array(
				array('is_show', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),		# 是否展示 
				array('is_page', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'),		# 是否需要分页
				array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             			# 当前页数
            	array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      			# 分页数
            	array('status', array('ENABLE','DISABLE','NORMAL'), PARAMS_ERROR, ISSET_CHECK,'in'),     # 状态
            	array('order', 'require', PARAMS_ERROR, ISSET_CHECK),             			# 排序
            	array('sic_code','require',PARAMS_ERROR, ISSET_CHECK),						# 卖家编码
            	array('user_name','require',PARAMS_ERROR, ISSET_CHECK),						# 卖家账号
            	array('name','require',PARAMS_ERROR, ISSET_CHECK),							# 卖家店铺
            	array('sc_code','require',PARAMS_ERROR, ISSET_CHECK),						# 卖家
            	array('invite_code','require',PARAMS_ERROR, ISSET_CHECK),				    # 邀请码
			);

			if (!$this->checkInput($this->_rule, $params)) { # 自动校验
				return $this->res($this->getErrorField(), $this->getCheckError());
			}

			$is_show = empty($params['is_show']) ? array('in',array('YES','NO')) : $params['is_show'];
			$is_page = empty($params['is_page']) ? 'NO' : $params['is_page'];
			$page = empty($params['page']) ? '1' : $params['page'];
			$page_number = empty($params['page_number']) ? '20' : $params['page_number'];
			$status = empty($params['status']) ? 'ENABLE' : $params['status'];
			$order = empty($params['order']) ? 'ss.id asc' : $params['order'];

			$is_page == "YES" ? $front = 'ss.' : '';

			$where[$front.'is_show'] = $is_show;
			$where[$fornt.'status'] = $status;
			empty($params['sic_code']) || $where[$fornt.'sic_code'] = $params['sic_code'] ;
			empty($params['user_name']) || $where[$front.'username'] = $params['user_name'];
			empty($params['name']) || $where[$front.'name'] = $params['name'];
			empty($params['sc_code']) || $where[$front.'sc_code'] = $params['sc_code'];

			// 邀请码判断
			if($params['invite_code'] != C('TEXT_INVITE_COE')){
				$where[$front.'sc_code'] = array('neq',C('LIANGREN_SC_CODE'));
			}

			// 不分页 
			if($is_page == "NO"){
				$res = D('ScStore')->where($where)->select();
            	return $this->res($res);
			}

			$fields = 'ss.sc_code,ss.name,ss.username,ss.store_desc,um.salesman,ss.logo,ss.min_money';
        	$params['page']		   = $page;
        	$params['page_number'] = $page_number;
        	$params['fields']      = $fields;
        	$params['where']       = $where;
        	$params['order']       = $order;
        	$params['center_flag'] = SQL_SC;
        	$params['sql_flag']    = 'store_lists';

        	$apiPath = "Com.Common.CommonView.Lists.Lists";
        	$lists_res = $this->invoke($apiPath,$params);
       	
        	if ($lists_res['status'] != 0) {
            	return $this->res(NULL, $lists_res['status']);
        	}
        	return $this->res($lists_res['response']);

        }


}
?>
