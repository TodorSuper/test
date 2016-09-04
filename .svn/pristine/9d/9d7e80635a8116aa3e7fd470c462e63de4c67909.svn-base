<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | pop平台消息相关
 */

namespace Bll\Pop\Message;
use System\Base;

class ScUpdate extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    
    /**
     * 修改用户密码
     * Bll.Pop.Message.ScUpdate.getList
     */
	public function getList($data){
		$res = $this->invoke('Com.Common.Message.Sys.getList', $data);
		return $this->res($res['response']);
    }
 
}

?>
