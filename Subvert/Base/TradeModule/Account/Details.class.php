<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 账户明细查询
 */

namespace Base\TradeModule\Account;
use System\Base;

class Details extends Base {

    public function __construct() {
        parent::__construct();
    }

	/**
	 * 根据code获取账户信息明细
	 * Base.TradeModule.Account.Details.getAccontListByCode
	 * @param mixed $data 
	 * @access public
	 * @return void
	 * 处理三种情况:
	 * 1. 全部是code
	 * 2. 全部是pCode
	 * 3. 有code和pCode
	 */
	public function getAccontListByCode($data) {
		if(count($data) == 0) {
			return $this->res(null, 5);
		}

		# 根据输入的数据分组
		$code = [];
		$pCode = [];
		foreach($data as $k=>$v) {
			if($v['isPcode'] == 'YES' && $v['code'] ) {
				$pCode[] = $v['code'];
			}elseif($v['isPcode'] == 'NO' && $v['code'] ) {
				$code[] = $v['code'];
			}
		}
		# 判断数组是否过大或空,如果过大则直接报错
		$field = 'tc_code,code,ext1 as pcode,free_balance as free,freeze_balance as freeze,total_balance as total, status,account_type as accountType,create_time';
		$codeNum = count($code);
		$pCodeNum = count($pCode);
		
		# 分别查询
		if($codeNum >= 500 ) {
			return $this->res(null, 2550);  
		}

		if($codeNum != 0) {
			$where = array(
				'code'=> array('in', $code)
			);
			$codeData = D('TcMainAccount')->field($field)->where($where)->select();
		}

		if($pCodeNum >= 50 ) {
			return $this->res(null, 2551);
		}

		if($pCodeNum != 0) {
			$where = array(
				'ext1'=> array('in', $pCode)
			);
            $where['total_balance']=array('gt',0);
			$pCodeData = D('TcMainAccount')->field($field)->where($where)->select();
		}
		
		# 组装返回
		unset($data);
		$returnData = [];
		foreach($codeData as $k=>$v) {
			$returnData[$v['code']] = $v;
		}

		unset($codeData);

		foreach($pCodeData as $k=>$v) {
			$returnData[$v['code']] = $v;
		}

		unset($pCodeData);

		return $this->res($returnData);

	}
}















?>
