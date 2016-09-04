<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 业务员管理
 */

namespace Base\UserModule\Customer;
use System\Base;
defined( 'UC_SALESMAN_STATUS_ENABLE' )     or define( 'UC_SALESMAN_STATUS_ENABLE', 'ENABLE' ); # 正常
defined( 'UC_SALESMAN_STATUS_DISABLE' )     or define( 'UC_SALESMAN_STATUS_DISABLE', 'DISABLE' ); # 删除

class Salesman extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Sc';
    }
    
    /**
     *业务员列表
     * Base.UserModule.Customer.Salesman.lists
     * @param type $params
     */
    public function lists($params){
        $this->_rule = array(
            array('sc_codes',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码  
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码  
            array('status', array(UC_SALESMAN_STATUS_DISABLE,UC_SALESMAN_STATUS_ENABLE), PARAMS_ERROR, HAVEING_CHECK,'in'), //状态
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK),

        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $sc_code   = $params['sc_code'];
        $status    = $params['status'] ;
        $name      = $params['name'];
        $type      = $params['type'];
        $sc_codes  = $params['sc_codes'];

        $order  =  'id desc';
        !empty($sc_code)        &&   $where['sc_code']  =  $sc_code;
        !empty($name)           &&   $where['name']     =  $name;
        !empty($sc_codes) && $where['sc_code'] = array('in', $sc_codes);

        if($type == 'code'){
            
            empty($status) ?  $where['status'] = UC_SALESMAN_STATUS_ENABLE  : $where['status']   = $status;
        }
            
        $params['order']        =  $order;
        $params['where']        =  $where;
        $params['center_flag']  =  SQL_UC;
        $params['sql_flag']     = 'salesman_list';
        
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if($list_res['status'] != 0){
            return $this->res(NULL,$list_res['status']);
        }
        // 业务员筛选业务员
        $map['status']  = 'ENABLE';
        $map['sc_code'] = $params['sc_code'];
        $list = D('ScSalesman')->field('name')->where($map)->group('name')->select();
        $list_res['response']['list'] = $list;
        return $this->res($list_res['response']);
    }
    
    /**
     * 添加业务员
     * Base.UserModule.Customer.Salesman.add
     * @param type $params
     */
    public function add($params){
        $this->startOutsideTrans();

        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //姓名
            array('mobile', 'require', PARAMS_ERROR, MUST_CHECK), //电话号码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $name    = $params['name'];
        $mobile  = $params['mobile'];
        $sc_code = $params['sc_code'];
        
        //生成邀请码
        // $invite_code = M('Com.Tool.Code.CodeGenerate.getSequence')->getSequence(SEQUENCE_INVITE);
        $invite_code = mt_rand(100000,999999);

        $data   = array(
            'name'    => $name,
            'mobile'  => $mobile,
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'     => UC_SALESMAN_STATUS_ENABLE,
            'sc_code'    => $sc_code,
            'invite_code' => $invite_code,
        );

        $res = D('ScSalesman')->add($data);
        if($res <= 0  || $res === FALSE){
            return $this->res(NULL,6701);
        }

        //生成二维码
        $apiPath = "Base.UserModule.Customer.Salesman.qrcode";
        $temp['sc_code']     = $sc_code;
        $temp['salesman_id'] = $res;
        $result  = $this->invoke($apiPath, $temp);

        if($result['status'] != 0){
            return $this->endInvoke(NULL,$result['status']);
        }
        return $this->res($res);
    }
    
    /**
     * 更新业务员信息
     * Base.UserModule.Customer.Salesman.update
     * @param type $params
     */
    public function update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK), //姓名
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK), //电话号码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK), //业务员id
            array('status', array(UC_SALESMAN_STATUS_DISABLE,UC_SALESMAN_STATUS_ENABLE), PARAMS_ERROR, ISSET_CHECK,'in'), //状态
            array('method', array('+','-'), PARAMS_ERROR, HAVEING_CHECK,'in'), //业务员id
            array('table', 'require', PARAMS_ERROR, HAVEING_CHECK),    //数据表
            array('invite_code', 'require', PARAMS_ERROR, ISSET_CHECK),    //邀请码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data    = array('update_time'=>NOW_TIME);
        $name    = $params['name'];
        $mobile  = $params['mobile'];
        $sc_code = $params['sc_code'];
        $status  = $params['status'];
        $salesman_id = $params['salesman_id'];
        $method = $params['method'];
        $invite_code = $params['invite_code'];
        !empty($sc_code) &&   $where['sc_code'] = $sc_code;
        !empty($salesman_id) && $where['id'] = $salesman_id;
        !empty($invite_code) && $where['invite_code'] = $invite_code;
        !empty($name)    &&   $data['name']       = $name;
        !empty($mobile)  &&   $data['mobile']     = $mobile;
        !empty($status)  &&    $data['status']    = $status;
        !empty($method)  && empty($params['table']) && $data['customer_num'] = array($method,1);
        !empty($method)  && !empty($params['table']) && $data['num'] = array($method,1);
        $table = empty($params['table']) ? 'ScSalesman' : $params['table'];
        $res = D($table)->where($where)->save($data);
        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6702);
        }
        return $this->res($res);
    }
    
    /**
     * 禁用业务员
     * Base.UserModule.Customer.Salesman.delete
     * @param type $params
     */
    public function delete($params){
        //该渠道是否被引用  如果被引用 则不能删除
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('salesman_id', 'require', PARAMS_ERROR, MUST_CHECK), //业务员id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $salesman_id = $params['salesman_id'];
        $salesman_info = D('ScSalesman')->where(array('sc_code'=>$sc_code,'id'=>$salesman_id))->find();
        if(empty($salesman_info) || $salesman_info['customer_num'] > 0){
            return $this->res(NULL,6718);
        }
        $apiPath = "Base.UserModule.Customer.Salesman.update";
        $params['status'] = UC_SALESMAN_STATUS_DISABLE;
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }


    /**
     * 启用业务员
     * Base.UserModule.Customer.Salesman.start
     * @param type $params
     */
    public function start($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('salesman_id', 'require', PARAMS_ERROR, MUST_CHECK), //业务员id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $salesman_id = $params['salesman_id'];
        $apiPath = "Base.UserModule.Customer.Salesman.update";
        $params['status'] = UC_SALESMAN_STATUS_ENABLE;
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status'],'',$res['message']);
    }
    


     /**
     * 生成二维码
     * Base.UserModule.Customer.Salesman.qrcode
     * @param type $params
     */

    public function qrcode($params) {
        
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('salesman_id', 'require', PARAMS_ERROR, MUST_CHECK), //业务员id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code     =  $params['sc_code'];
        $salesman_id = $params['salesman_id'];

        //查询邀请码
        $invite_code = D('ScSalesman')->where(array('sc_code'=>$sc_code,'id'=>$salesman_id))->field('invite_code')->master()->find();

        if(empty($invite_code)){
            return $this->res(NULL,6705);
        }
        $invite_code = $invite_code['invite_code'];
        if(empty($invite_code)){
            return $this->res(NULL,6706);
        }
        
        //生成二维码 generateQrcodeByUrl($url, '', 100, $goods_img)
        $url = C('CHANNEL_QRCODE_URL')."Register/index/type/salesman/invite_code/{$invite_code}";
        $Qrcode = new \Library\qrcodes();
        $qrcode_url = $Qrcode->generateQrcodeByUrl($url, '', 100);
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
            'id' =>$salesman_id,
            'sc_code' => $sc_code,
        );
        $res = D('ScSalesman')->where($where)->save($data);

        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6709);
        }
        return $this->res($img_url);    
    }

    /**
     * Base.UserModule.Customer.Salesman.getQrcode
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getQrcode($params){
        $this->_rule = array(
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK),

            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $name = $params['name'];
        $where['name'] = $name;
        $qrcodes = D('ScSalesman')->where($where)->find();
        if ($qrcodes === false || empty($qrcodes)) {
            return $this->res(NULL, 8);
        }
        return $this->res($qrcodes);
    }

    /**
     * 业务员模糊查询
     * Base.UserModule.Customer.Salesman.search
     * @param type $params
     * @return type
     */

    public function search($params){
        $this->_rule = array(
            array('name','require',PARAMS_ERROR,ISSET_CHECK),       //模糊搜索的名称
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),  //店铺编码  
            array('status', array(UC_SALESMAN_STATUS_DISABLE, UC_SALESMAN_STATUS_ENABLE), PARAMS_ERROR, HAVEING_CHECK, 'in'), //状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $status  = empty($params['status']) ? UC_SALESMAN_STATUS_ENABLE : $params['status'];
        $name    = $params['name'];

        $where            = array();
        !empty($name) && $where['name']    = array('like','%'.$name.'%');
        $where['sc_code'] = $sc_code;
        $where['status']  = $status;
        $salesman_search = D('ScSalesman')->where($where)->select();
        return $this->res($salesman_search);
    }
    
    /**
     * 获取业务员
     * Base.UserModule.Customer.Salesman.get
     * @param type $params
     * @return type
     */
    public function get($params){
        $this->_rule = array(
            array('invite_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //邀请码
            array('salesman_id', 'require', PARAMS_ERROR, HAVEING_CHECK), //自增id
            array('table', 'require', PARAMS_ERROR, ISSET_CHECK),       //数据表
            array('status', array('ENABLE','DISABLE'), PARAMS_ERROR, ISSET_CHECK,'in'),      // 状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $invite_code = $params['invite_code'];
        $salesman_id  = $params['salesman_id'];
        $status = $params['status'];
        $where = array();
        !empty($invite_code)  && $where['invite_code'] = $invite_code;
        !empty($salesman_id)  && $where['id'] = $salesman_id;
        !empty($status) && $where['status'] = $status;
        if(empty($where)){
          return $this->res(NULL);    
        }
        $table = empty($params['table']) ? 'ScSalesman' : $params['table'];
        $salesman_info = D($table)->where($where)->find();
        return $this->res($salesman_info);
    }


    /**
     * Base.UserModule.Customer.Salesman.getAll
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getAll($params){
        $this->_rule = array(
            array('invite_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //邀请码
            array('salesman_id',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), 
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $invite_code = $params['invite_code'];
        $salesman_id = $params['salesman_id'];

        $where = array();
        !empty($invite_code)  && $where['invite_code'] = $invite_code;
        !empty($salesman_id)  && $where['id'] = array('in', $salesman_id);
        if(empty($where)){
          return $this->res(NULL);    
        }

        $salesman_info = D('ScSalesman')->where($where)->select();
        return $this->res($salesman_info);
    }



    
}

?>
