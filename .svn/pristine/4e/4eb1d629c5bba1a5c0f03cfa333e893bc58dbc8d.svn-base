<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: nielei <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | cms用户相关模块
 */

namespace Bll\Cms\User;
use System\Base;

class Feedback extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();

    }


    /**
     * 意见反馈列表
     * Bll.Cms.User.Feedback.lists
     * @access public
     */

    public function lists($params){
        $apiPath = 'Base.UserModule.User.Feedback.lists';
        $res = $this->invoke($apiPath, $params);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return $this->endInvoke($res['response']);
    }


    /**
     * 意见反馈
     * Bll.Cms.User.Feedback.update
     * @access public
     */

    public function update($params){
        try {
            D()->startTrans();
                $apiPath = 'Base.UserModule.User.Feedback.update';
                $res = $this->invoke($apiPath, $params);
                if ($res['status'] != 0) {
                    return $this->endInvoke(NULL, $res['status']);
                }
                $commit_res = D()->commit();
                if ($commit_res === FALSE) {
                    return $this->endInvoke(NULL, 17);
                }
            return $this->endInvoke($res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4066);
        }
    }

   
}

?>
