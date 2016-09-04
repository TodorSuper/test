<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | debug api
 */

namespace Com\Tool\FrameLogs;
use System\Base;

class Logs extends Base {
	/**
	 * 获取所有的系统日志并且以json格式返回
	 *
	 * Com.Tool.FrameLogs.Logs.getAll 
	 * 
	 * @param mixed $params 
	 * @access public
	 * @return void
	 */
	public function getAll($params){
		# 获取日志文件的内容
		$data = array();
		$data['log'] = $this->getLogs("log.txt");
		$data['trace'] = $this->getLogs("trace.txt");

		# 返回结果
		$this->res($data);
	}

	private function getLogs($fileName) {
		$file_path = LOG_PATH.$fileName;
		$fp = fopen($file_path , 'r');
		if(!$fp) {
			$this->res("Logs/$fileName 不存在!\n");
		}
		# 从缓存获取行号, 如果行号不存在则写入
		$line = S(md5($file_path));
		if(!$line) {
			$line = $this->getFileLine($fp);
			S(md5($file_path), $line);
			$notHave = true;
		}

		# 读取文件内容
		if($notHave) { 
			$data = $this->getLineToLast($fp, $line - 5);
		}else {
			$data = $this->getLineToLast($fp, $line);
			S(md5($file_path), $data['line']);
		}
		fclose($fp);
		return $data['content'];
	}

	# 获取文件的行数
	private function getFileLine(&$fp) {
		$line = 0;
		while(stream_get_line($fp,8192,"\n")){
			$line++;
		}
		return $line;
	}

	# 从指定行获取到行的结尾
	private function getLineToLast(&$fp, $num) {
		$line = 0;
		$content = '';
		while($lineInfo = stream_get_line($fp,8192,"\n")){
			$line++;
			if($line > $num) {
				$content .= $lineInfo."\n";
			}
		}
		return array("content"=>$content, "line"=>$line);
	}

}

?>
