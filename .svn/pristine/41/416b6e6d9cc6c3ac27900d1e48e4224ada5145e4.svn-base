<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 执行TASK任务
 */

namespace Com\Crontab\Scan;
use System\Base;

class Run extends Base {
	private $_rule = array();

    public function __construct() {
		parent::__construct();
    }

	/**
	 * 运行一个定时任务
	 * runTask
	 * Com.Crontab.Scan.Run.runTask
	 * @access public
	 * @return void
	 */

	public function runTask($data) {
		# 定义返回结果数据结构
		$struct = array(
			'run_task_code' => '',		# 当前执行的任务编码
			'run_task_response' => '',  # 当前任务的返回结果
			'run_task_status' => 0,     # 当前任务的返回信息
			'break' => 0,			    # 是否终止该次任务调度 0停止 非零 继续
		);

		# 查询任务列表
		$where = array(
			'status' => array('in', array(CRONTAB_SACN_WAIT,CRONTAB_SACN_ERR)),
			'update_time' => array('elt', NOW_TIME),
		);
		$task = D('CrontabExe')->field('task_code,unique_str,params,api,allow_err_num,status,err_num')->master()->where($where)->limit(2)->select();
		if( $task === false ) {
			return $this->res($struct, 5009);	# 查询出错 over

		}elseif($task === null) {
			return $this->res($struct, 5010);	# 没有可执行任务 over

		}
		$break = count($task) - 1;
		$task = $task[0]; # 取第一条
		
		# 设置任务执行状态
		$save = array(
			'status' => CRONTAB_SACN_RUN,
			'update_time' => NOW_TIME,
		);
		$update  = D('CrontabExe')->where(array('unique_str' => $task['unique_str']))->save($save);
		if($update !== 1) {
			return $this->res($struct, 5012);	# 任务更改状态失败 over
		}
		
		# 执行任务
		try{
			D()->startTrans();
			$res = $this->invoke($task['api'], json_decode($task['params'], true));
			$status = $res['status'];
			$message = $res['response'];
			if($status === 0 ) {			# 执行任务成功
				$save = array(
					'last_response' => $message,
					'last_status' => $status,
					'status' => CRONTAB_SACN_END,
					'update_time' => NOW_TIME,
				);
				$update  = D('CrontabExe')->where(array('unique_str' => $task['unique_str']))->save($save);
				if($update === 1) {				# 更新任务状态成功 next
					D()->commit();

				}else {							# 更新任务状态失败 over
					D()->rollback();
					return $this->res($struct, 5013);
				}

			}else {								# 执行任务失败 next
				throw new \Exception($res['response']);
			}

		}catch(\Exception $e) {
			if( empty($res) ) {
				$message =  $e->getMessage();
				$status = 5011;
			}
			
			D()->rollback();
		}
		# 写入执行异常结果
		$save = array();
		if($status !== 0) {
			$task['err_num'] += 1;
			if($task['allow_err_num'] <= $task['err_num']) {
				$save['status'] = CRONTAB_SACN_ERR_END;
			}else {
				$save['status'] = CRONTAB_SACN_ERR;
			}
			$save['update_time'] = NOW_TIME;
			$save['err_num'] = $task['err_num'];
			$save['last_response'] = $message;
			$save['last_status'] = $status;
			$update  = D('CrontabExe')->where(array('unique_str' => $task['unique_str']))->save($save); # 不判断写入结果, 如果写入失败则由健康检查系统捕获
		}
		# 返回结果
		$struct['run_task_code'] = $task['task_code'];
		$struct['run_task_status'] = $status;
		$struct['run_task_response'] = $message;
		$struct['break'] = $break;

		return $this->res($struct);
	}	

}

?>
