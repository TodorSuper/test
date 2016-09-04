<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 创建表扫描类的api调度器
 */

namespace Com\Crontab\Scan;
use System\Base;

class Create extends Base {
	private $_rule = array();

    public function __construct() {
		parent::__construct();
    }

	/**
	 * 添加一个定时调度任务
	 * addTask
	 * Com.Crontab.Scan.Create.addTask
	 * @access public
	 * @return void
	 */
	public function addTask($data) {
		$this->_rule = array(
			array('unique_str', 'require' , PARAMS_ERROR, MUST_CHECK),				#  唯一字符串		* 必须字段
			array('params', 'require' , PARAMS_ERROR, MUST_CHECK),					#  调用参数			* 必须字段
			array('api', 'require' , PARAMS_ERROR, MUST_CHECK),						#  调用api			* 必须字段
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK),					#  任务类型			* 必须字段
			array('run_time', 'require' , PARAMS_ERROR, ISSET_CHECK),				#  允许运行时间		非必须
			array('allow_err_num', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  允许调用错误次数	非必须
		);
		
		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());
		
		# 设置默认参数
		$data['run_time'] = isset($data['run_time']) ? $data['run_time'] : NOW_TIME;  # 默认为写入即被执行
		$data['allow_err_num'] = isset($data['allow_err_num']) ? $data['allow_err_num'] : 1; # 默认为出错后不在执行
		$data['unique_str'] = md5($data['unique_str'].$data['type']); # 确定唯一防止同号码的订单
		
		# 获取任务编码
		$taskCode = array(
			"busType" => CRONTAB_RUN,
			"preBusType" => CRONTAB_SACN_EXE,
			"codeType" => SEQUENCE_CRONTAB_TASK
		);
		
		$taskCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $taskCode);
		if( $taskCode['status'] !== 0 ) {
			return $this->res('', 2500); # 生成编码失败
		}

		# 参数确定
		$data2 = array(
			'task_code' => $taskCode['response'],
			'create_time' => NOW_TIME,
			'update_time' => NOW_TIME,
			'status' => CRONTAB_SACN_WAIT,
			'err_num' => 0,
			'last_response' => '',
			'last_status' => '',
			'last_response' => '',
		);
		
		# 写入任务
		$insert = D('CrontabExe')->add(array_merge($data, $data2));
		if($insert <= 0)  {
			return $this->res('', 5008);
		}
		
		return $this->res($taskCode['response']);

	}	

}

?>
