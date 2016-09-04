<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: nilei <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b地址模块
 */

namespace Bll\B2b\User;
use System\Base;

class Feedback extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * 问题反馈添加
     * Bll.B2b.User.Feedback.add
     * @access public
     * @author Todor
     */

    public function add($params){
        try {
            D()->startTrans();
            $apiPath = "Base.UserModule.User.Feedback.add";
            $res = $this->invoke($apiPath, $params);
            if ($res['status'] != 0) {
                return $this->endInvoke($res['response'], $res['status']);
            }
            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                return $this->endInvoke(NULL, 17);
            }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4064);
        }
    }


    


}

?>
