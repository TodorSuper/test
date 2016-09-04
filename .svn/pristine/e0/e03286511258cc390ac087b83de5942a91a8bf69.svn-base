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

namespace Test\Com\ToolFrameLogs;
use System\Base;

class Logs extends Base {

	public function schema() { # 修复框架bug
//		$res = D('Timer')->where(['timer_type'=>'VOUCHER'])->save(['value'=>98,'tt'=>97]);

		D()->startTrans();
		# 生成店铺编码
		$apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
		$data = array(
			'busType'=> POP_CODE,
			'preBusType'=> 0,
			'codeType'=> SEQUENCE_POP,
		);
		$code_res = $this->invoke($apiPath, $data);
		D()->commit();
		return $this->res($code_res);
	}

	/**
	 * 测试
	 *
	 * Test.Com.ToolFrameLogs.Logs.test
	 * 
	 * @param mixed $params 
	 * @access public
	 * @return void
	 */
	public function test($data){
//		Log::info("api.call.push_queue", Log::LOG_FLAG_NORMAL, ["hello"]);
//		exit;
		D()->find();
/*		D()->startTrans();
		D('Sequence')->where(['type'=>7])->save(['id'=>['+', 1]]);
		$res = D('Sequence')->where(['type'=>7])->select();
		exit;
		D()->commit();
		return $this->res($res); */

		$a = '==========';
		L('============');
		$res = $this->invoke('Test.Com.ToolFrameLogs.Logs.tt1', $a);
		$data = D('UcUser')->find(1);
	//	$res = $this->invoke('Test.Com.ToolFrameLogs.Logs.tt4', $a);
		$data = D('UcUser')->find(1);
		return $this->res(['end'=>'end']);
	}

	private function tt0($a) {
	}

	public function  tt1($a) {
		$res = $this->invoke('Test.Com.ToolFrameLogs.Logs.tt4', $a);
		return $this->res($res['response']);
	}

	public function tt3($a) {
		$res = $this->invoke('Test.Com.ToolFrameLogs.Logs.tt4', $a);
		return $this->res($a);
	}
# SELECT * FROM `16860_uc_user` WHERE ( `id` = 1 ) LIMIT 1   [ RunTime: 0.000305s 0.305ms ]  [ speed: 火箭 ]	
	public function tt4($a) {
		DG('start trans=======', SUB_DG_OBJECT);
		$data = D('UcUser')->find(1);
		DG('end trans========', SUB_DG_OBJECT);
		return $this->res($data);
	}

	private function getLogs($fileName) {
		$file_path = LOG_PATH.$fileName;
		$fp = fopen($file_path , 'r');
		if(!$fp) {
			$this->endResponse("Logs/$fileName 不存在!\n");
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
