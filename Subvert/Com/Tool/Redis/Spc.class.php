<?php
namespace Com\Tool\Redis;
use System\Base;

class Spc extends Base {
	public function __construct() {               
        parent::__construct();
    }
	public function import($params) {
		$key = \Library\Key\RedisKeyMap::getSpcKey($params['sc_code'],$params['uc_code']);
		$res = R()->set($key,$params['data']);
		return $res;
	}
	public function clearData($params) {
		$key = \Library\Key\RedisKeyMap::getSpcKey($params['sc_code'],$params['uc_code']);
		$res = R()->delete($key);
	
		return $res;
	}

}