<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户相关模块
 */

namespace Com\Common\DataVersion;
use System\Base;

class Mysql extends Base {

	private $_rule	=	null; # 验证规则列表

    public function __construct() {
        parent::__construct();
	}

	public function add($data) {
		$this->startOutsideTrans();  # 外部事务模式
		$this->_rule = array(
			array('mainTable', 'require' , PARAMS_ERROR, MUST_CHECK),				# 主数据表名 注: 框架的首字母驼峰格式  * 必须参数
			array('versionTable', 'require' , PARAMS_ERROR, ISSET_CHECK),			# 版本存储表 注: 框架的首字母驼峰格式  * 必须参数
			array('where', 'checkInput' , PARAMS_ERROR, MUST_CHECK, 'function'),	# 查询条件 不支持批量版本记录          * 必须参数
			array('timeField', 'require' , PARAMS_ERROR, ISSET_CHECK),				# 版本表记录版本时间的字段			   非必需参数
			array('mainTableVersionField', 'require' , PARAMS_ERROR, ISSET_CHECK),	# 主数据字段名 注: 框架的首字母驼峰格式  非必须参数
		);
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		# 设置参数和默认值
		$mainTable = $data['mainTable'];
		$where = $data['where'];
		$versionTable = $data['versionTable'] ? $data['versionTable'] : $mainTable."_version";  # 不传则默认为  main_table_version 格式
		$timeField = $data['timeField'] ? $data['timeField'] : 'version_time';					# 不传则默认为  version_time
		$mainTableVersionField = $data['mainTableVersionField'] ? $data['mainTableVersionField'] : 'version'; # 不传则默认为  version
		
		# 拼装数据
		$mainData = D($mainTable)->where($where)->find();
		$mainData[$timeField] = NOW_TIME;
		
		# 操作数据
		$insert  = D($versionTable)->add($mainData);
		if($insert > 0 ) {
			$update  = D($mainTable)->where($where)->setInc($mainTableVersionField, 1);
			if($update !== 1) {
				return $this->res($mainTable, 5004); # 更新版本时主表版本号写入失败
			}
		}else {
			return $this->res($versionTable, 5003); # 更新版本时版本表写入失败
		}

		return $this->res(true);
	}

	/**
	 *
	 * 验证用户组的类型, 目前只支持商家验证
	 *
	 * checkUserType
	 *
	 * Com.Common.User.User.checkUserType
	 *
	 * data.uc_code, data.type
	 *
	 * @access public
	 *
	 * @return array
	 *
	 */

	public function checkUserType($data) {

		$this->_rule = array(
			array('uc_code', 'require' , PARAMS_ERROR, MUST_CHECK),
			array('type', array(MERCHANT_GROUP) , PARAMS_ERROR, MUST_CHECK, 'in'),
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		switch($data['type']) { 

		case MERCHANT_GROUP:
			return $this->res( $this->getMerchantType($data['uc_code']) );

		default:
			return $this->res(null);
		}

	}

	/**
	 *
	 * 验证商户--关联商户表查询
	 *
	 * getMerchantType 
	 * 
	 * @param mixed $uc_code 
	 * @access private
	 * @return void
	 */
	private function getMerchantType($uc_code) {
		$where = array(
			"u.uc_code" => $uc_code,
			"u.group_id" => MERCHANT_GROUP, 
			"um.status" => 'ENABLED', # 激活
		);
		
		$merchant = D( "UcUser" )->alias( 'u' )->field('um.id,um.status as merchant_status,u.status as user_status,u.group_id')
						->join( 'left join __UC_MERCHANT__ um on u.uc_code = um.uc_code' )
						->where( $where )->find();
		
		if(isset($merchant['id'])) {
			$merchant['group_id'] = MERCHANT_GROUP;
			return $merchant;
		}else{
			return null;
		}

	}


}








?>
