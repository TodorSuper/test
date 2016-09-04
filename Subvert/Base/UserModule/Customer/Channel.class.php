<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 渠道管理
 */

namespace Base\UserModule\Customer;

use System\Base;

defined('UC_CHANNEL_STATUS_ENABLE') or define('UC_CHANNEL_STATUS_ENABLE', 'ENABLE'); # 正常
defined('UC_CHANNEL_STATUS_DISABLE') or define('UC_CHANNEL_STATUS_DISABLE', 'DISABLE'); # 删除

class Channel extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Sc';
    }

    /**
     * 业务员列表
     * Base.UserModule.Customer.Channel.lists
     * @param type $params
     */
    public function lists($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码  
            array('status', array(UC_CHANNEL_STATUS_DISABLE, UC_CHANNEL_STATUS_ENABLE), PARAMS_ERROR, HAVEING_CHECK, 'in'), //状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code = $params['sc_code'];
        $status = empty($params['status']) ? UC_CHANNEL_STATUS_ENABLE : $params['status'];

        $order = 'id desc';
        $where = array('status' => $status);
        !empty($sc_code) && $where['sc_code'] = $sc_code;

        $params['order'] = $order;
        $params['where'] = $where;
        $params['center_flag'] = SQL_UC;
        $params['sql_flag'] = 'channel_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        return $this->res($list_res['response']);
    }

    /**
     * 添加渠道
     * Base.UserModule.Customer.Channel.add
     * @param type $params
     */
    public function add($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //姓名
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $name = $params['name'];
        $sc_code = $params['sc_code'];

        //生成邀请码
        // $invite_code = M('Com.Tool.Code.CodeGenerate.getSequence')->getSequence(SEQUENCE_INVITE);

        $data = array(
            'name' => $name,
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME,
            'status' => UC_CHANNEL_STATUS_ENABLE,
            'sc_code' => $sc_code,
            // 'invite_code' => $invite_code,
        );
        $res = D('ScChannel')->add($data);
        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 6701);
        }
        return $this->res($res);
    }

    /**
     * 更新渠道信息
     * Base.UserModule.Customer.Channel.update
     * @param type $params
     */
    public function update($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, HAVEING_CHECK), //姓名
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('channel_id', 'require', PARAMS_ERROR, MUST_CHECK), //业务员id
            array('status', array(UC_CHANNEL_STATUS_DISABLE, UC_CHANNEL_STATUS_ENABLE), PARAMS_ERROR, HAVEING_CHECK, 'in'), //状态
            array('method', array('+','-'), PARAMS_ERROR, HAVEING_CHECK,'in'), //业务员id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $data = array('update_time' => NOW_TIME);
        $name = $params['name'];
        $sc_code = $params['sc_code'];
        $status = $params['status'];
        $channel_id = $params['channel_id'];
        $method = $params['method'];
        $where = array('sc_code' => $sc_code, 'id' => $channel_id);
        !empty($name) && $data['name'] = $name;
        !empty($mobile) && $data['mobile'] = $mobile;
        !empty($status) && $data['status'] = $status;
        !empty($method) && $data['customer_num'] = array($method,1);
        $res = D('ScChannel')->where($where)->save($data);
        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 6702);
        }
        return $this->res($res);
    }

    /**
     * 更新渠道信息
     * Base.UserModule.Customer.Channel.delete
     * @param type $params
     */
    public function delete($params) {
        //该渠道是否被引用  如果被引用 则不能删除
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('channel_id', 'require', PARAMS_ERROR, MUST_CHECK), //业务员id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $channel_id = $params['channel_id'];
        $channel_info = D('ScChannel')->where(array('sc_code'=>$sc_code,'id'=>$channel_id))->find();
        if(empty($channel_info) || $channel_info['customer_num'] > 0){
            return $this->res(NULL,6717);
        }
        $apiPath = "Base.UserModule.Customer.Channel.update";
        $params['status'] = UC_CHANNEL_STATUS_DISABLE;
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'], $res['status'], '', $res['message']);
    }

    /**
     * 生成二维码
     * Base.UserModule.Customer.Channel.qrcode
     * @param type $params
     */
    public function qrcode($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('channel_id', 'require', PARAMS_ERROR, MUST_CHECK), //业务员id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code    =  $params['sc_code'];
        $channel_id = $params['channel_id'];
        //查询邀请码
        $invite_code = D('ScChannel')->where(array('sc_code'=>$sc_code,'id'=>$channel_id))->field('invite_code')->find();
        if(empty($invite_code)){
            return $this->res(NULL,6705);
        }
        $invite_code = $invite_code['invite_code'];
        if(empty($invite_code)){
            return $this->res(NULL,6706);
        }
        
        $url = C('CHANNEL_QRCODE_URL')."Register/index/type/channel/invite_code/{$invite_code}";
        $Qrcode = new \Library\qrcodes();
        $qrcode_url = $Qrcode->generateQrcodeByUrl($url);
        if(empty($qrcode_url)){
            return $this->res(NULL,6707);
        }
        //上传到阿里云
        $img_url  = upload_cloud($qrcode_url);
        if(empty($img_url)){
            return $this->res(NULL,6708);
        }
        $data = array(
            'qcode'  => $img_url,
            'update_time' => NOW_TIME,
        );
        $where = array(
            'id' =>$channel_id,
            'sc_code' => $sc_code,
        );
        $res = D('ScChannel')->where($where)->save($data);
        if($res <= 0 && $res === FALSE){
            return $this->res(NULL,6709);
        }
        return $this->res($img_url);
        
    }
    
    /**
     * Base.UserModule.Customer.Channel.get
     * @param type $params
     * @return type
     */
    public function get($params){
        $this->_rule = array(
            array('invite_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //邀请码
            array('channel_id', 'require', PARAMS_ERROR, HAVEING_CHECK), //自增id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $invite_code = $params['invite_code'];
        $channel_id  = $params['channel_id'];
        $where = array();
        !empty($invite_code)  && $where['invite_code'] = $invite_code;
        !empty($channel_id)  && $where['id'] = $channel_id;
        if(empty($where)){
          return $this->res(NULL);    
        }
        $channel_info = D('ScChannel')->where($where)->find();
        return $this->res($channel_info);
        
    }

    /**
     * Base.UserModule.Customer.Channel.getAll
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getAll($params){
        $this->_rule = array(
            array('invite_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //邀请码
            array('channel_id',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), 
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $invite_code = $params['invite_code'];
        $channel_id  = $params['channel_id'];
        $where = array();
        !empty($invite_code)  && $where['invite_code'] = $invite_code;
        !empty($channel_id)  && $where['id'] = array('in', $channel_id);
        if(empty($where)){
          return $this->res(NULL);    
        }
        $channel_info = D('ScChannel')->where($where)->select();
        return $this->res($channel_info);
    }
    /**
     * 渠道模糊查询
     * Base.UserModule.Customer.Channel.search
     * @param type $params
     * @return type
     */

    public function search($params){
        $this->_rule = array(
            array('name','require',PARAMS_ERROR,ISSET_CHECK),      //模糊搜索的名称
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码  
            array('status', array(UC_CHANNEL_STATUS_DISABLE, UC_CHANNEL_STATUS_ENABLE), PARAMS_ERROR, HAVEING_CHECK, 'in'), //状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $status  = empty($params['status']) ? UC_CHANNEL_STATUS_ENABLE : $params['status'];
        $name    = $params['name'];

        $where            = array();
        !empty($name) && $where['name']    = array('like','%'.$name.'%');
        $where['sc_code'] = $sc_code;
        $where['status']  = $status;
        $channel_search = D('ScChannel')->where($where)->select();
        return $this->res($channel_search);
    }

}

?>
