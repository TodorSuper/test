<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 编码统一生成
 */

namespace Test\Com\ToolCode;

use System\Base;

class CodeGenerate extends Base {
    public function __construct() {
        parent::__construct();
        
	}

	public function kanjiado($data) {
		for($i = 0 ; $i<= 30;$i++) {
			$this->kanjia($data);
			sleep(rand(5,10));
		}
	}
	/**
	 * Test.Com.ToolCode.CodeGenerate.kanjia
	 * iphone kanjia 
	 * @access public
	 * @return void
	 */
	public function kanjia($data) {
		$url = $data['url'];
		$uid = $data['uid'];
		$name = $this->getName();
		//$url = "http://zzdgsx.com.cn/plugin.php?id=tom_kanjia&mod=index&kid=1&uid=33777";
//		$url = "http://thrunite-store.cn/plugin.php?id=tom_kanjia&mod=index&kid=1&uid=40735";
		$version1 = rand(4,5);
		if($version1 == 5) {
			$version1 = rand(4,5);
		}
		$version2 = rand(1,4);
		if($version1 == 5) {
			$version = "5.0";
		}else {
			$version = $version1.'.'.$version2;
		}
		$str = $this->getCurl($url, $version);
		$str = mb_convert_encoding($str, 'utf-8', 'gbk');
		file_put_contents('./str.txt', $str);
//		$str = file_get_contents('./str.txt');
		$cookie = preg_match('/Set-Cookie\: 9ceI_2132_saltkey\=([\d\w%\.]{8,40})\;/',$str,$regs);
		$aa = $regs[1];
		$cookie = preg_match('/Set-Cookie\: 9ceI_2132_lastvisit\=([\d\w%\.]{8,40})\;/',$str,$regs);
		$bb = $regs[1];
		$cookie = preg_match('/Set-Cookie\: 9ceI_2132_sid\=([\d\w]{6})/',$str,$regs);
		$cc = $regs[1];
		$cookie = preg_match('/Set-Cookie\: 9ceI_2132_lastact\=([\d\w%\.]{8,40})\;/',$str,$regs);
		$dd = $regs[1];
		$cookie = preg_match('/Set-Cookie\: PHPSESSID\=([\d\w%\.]{8,40})\;/',$str,$regs);
		$ee = $regs[1];
		$cookie = '9ceI_2132_saltkey='.$aa.';9ceI_2132_lastvisit='.$bb.';9ceI_2132_sid='.$cc.';9ceI_2132_lastact='.$dd.';PHPSESSID='.$ee;
		preg_match('/formhash\" value\=\"([\d\w]{8})/',$str,$regs);
		$code = $regs[1];
		preg_match('/计算\：(\d)(\+)(\d)\=/',$str,$regs);
		switch($regs[2]) {
			case '+':
				$num = $regs[1] + $regs[3];break;
			case '-':
				$num = $regs[1] - $regs[3];break;
			case '*':
				$num = $regs[1] * $regs[3];break;
			case '/':
				$num = $regs[1] / $regs[3];break;
		}
		$post = array(
			"id" => "tom_kanjia",
			'mod' => 'ajax',
			'act' => 'kanjia',
			'kid' => 1,
			'uid' => $uid,
			'name' => $name,
			'num_sun' => (int)$num,
			'formhash' => $code,
			'openid' => '',
			'num_a' => (int)($regs[1]),
			'num_b' => (int)($regs[3]),
			'num_count' => $num
		);
		$url = 'http://zzdgsx.com.cn/plugin.php?';
//		$url = 'http://pop.ypt.com/index.php?';
		foreach($post as $k=>$v) {
			$url.= "$k=$v&";
		}
//		L($url);
//		L($cookie);
		$url = rtrim($url, "&");
		$end = $this->sendCurl($url, $version, $cookie);
		L($end);
	}


	private function getName() {
		$level = rand(2,9);
		$str = array(
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'
		);
		for($i=0 ;$i<=$level; $i++) {
			$r = rand(0,34);
			$name .= $str[$r];
		}
		return $name;
	}

	private function getCurl($url, $version) {
		$ip = $this->getIp();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip));
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android $version; Build) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36 MicroMessenger/6.2.5.51_rfe7d7c5.621 NetType/WIFI Language/zh_CN"); // 微信浏览器
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$out = curl_exec($ch);
		//var_dump(curl_error($ch));
		curl_close($ch);
		return $out;
	}
	
	private function sendCurl($url, $version, $cookie) {
		$ip = $this->getIp();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip));
		curl_setopt($ch, CURLOPT_REFERER, "http://www.pianzi.com");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android $version; Build) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36 MicroMessenger/6.2.5.51_rfe7d7c5.621 NetType/WIFI Language/zh_CN"); // 微信浏览器
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$out = curl_exec($ch);
//		echo $out;
		//var_dump(curl_error($ch));
		curl_close($ch);
		return $out;
	}

	private function getIp() {
        $ip_long = array(
            array('607649792', '608174079'), //36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
		$ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
		return $ip;
	}

	# Test.Com.ToolCode.CodeGenerate.mkFinanceCode
	public function mkFinanceCode() {
		D()->startTrans();

		$tcMainAccountCodeData = array(
			"busType" => FC_CODE,
			"preBusType" => FP_CODE,
			"codeType" => SEQUENCE_FC
		);

		$accountCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $tcMainAccountCodeData);
		if( $accountCode['status'] !== 0) {
			return $this->res('', 2500); # 生成编码失败
		}

		D()->commit();

		return $this->res($accountCode['response']);
	}


	public function testCode() {
		D()->startTrans();
		# 生成账户流水编码
		$tcMainAccountCodeData = array(
			"busType" => FLOW_NO,
			"preBusType" => TC_ACCOUNT_TRADE_NO,
			"codeType" => SEQUENCE_TRADE_NO
		);

		$accountCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $tcMainAccountCodeData);
		if( $accountCode['status'] !== 0) {
			return $this->res2('', 2500); # 生成编码失败
		}
		D()->commit();
		return $this->res($accountCode['response']);
	}	
    /**
     * 生成订单号 及 流水号
     * Com.Tool.Code.CodeGenerate.mkOrderCode
     * @param type $orderType
     * @param type $preOrderType
     * @return boolean
     */
    public  function mkOrderCode($params){
        $apiPath =  "Com.Tool.Code.CodeGenerate.mkCode";
        try{
            $res = $this->invoke($apiPath, $params);
        } catch (\Exception $ex) {

        }
        
        return $this->res($res['response']);
    }


	
    /** 
     * 生成用户编码
     * Com.Tool.Code.CodeGenerate.mkUserCode
     * @param type $userType 用户类型
     * @param type $preUserType  预留用户类型
     * @return boolean
     */
    public  function mkUserCode($params){
        $userType = $params['userType'] ? $params['userType'] : USER_SUPPLIER;
        $preUserType = $params['preUserType'] ? $params['preUserType'] : USER_SUPPLIER_PC;
        //获取增长空间id
        $id = $this->getSequence(SEQUENCE_USER, 10);
        if(false == $id || $id <= 0){
            return $this->endResponse(false,1000);
        }
        return $this->endResponse($userType.$preUserType.$id);
    }
    
    /**
     * 获取自增空间id
     * @param type $type    类别
     * @param type $length  扩充长度
     * @return boolean
     * @throws \Exception
     */
    private function getSequence($type,$length = 8){
        if(empty($type)){
            return false ;
        }
        try{
            $sql = "update {$this->tablePrefix}sequence  set id = LAST_INSERT_ID(id + step) where type = {$type}"; 
            $res = D()->execute($sql);
            if($res <= 0){
                throw new \Exception('生成编码失败');
            }
            $idInfo = D('Sequence')->master()->field('LAST_INSERT_ID() as id')->find();
            $id = $idInfo['id'];
            if($id <=0){
                throw new \Exception('获取编码失败');
            }
            $env_config = C('ENV');
            //获取环境
            $env = $env_config[ENV];
            //扩充位数
            $id = $env.str_pad($id, $length, 0,STR_PAD_LEFT);
            return $id;
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            throw new \Exception($message);
        } 
    }
    

}
