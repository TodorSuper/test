<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 客户管理
 */

namespace Base\UserModule\Customer;

use System\Base;
defined( 'UC_CUSTOMER_STATUS_ENABLE' )     or define( 'UC_CUSTOMER_STATUS_ENABLE', 'ENABLE' ); # 正常
defined( 'UC_CUSTOMER_STATUS_DISABLE' )     or define( 'UC_CUSTOMER_STATUS_DISABLE', 'DISABLE' ); # 删除

class Customer extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    private $model = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
        $this->model = D('UcCustomer');
    }

    /**
     * 会员列表
     * Base.UserModule.Customer.Customer.lists
     * @param type $params
     */
    public function lists($params) {

        $this->_rule = array(
            array('sc_codes',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码 
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码   如果是pop平台查看   则一定需要传入  ,如果是  cms 平台  则无需传入
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('channel_id', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('invite_from', 'require', PARAMS_ERROR, ISSET_CHECK),
           
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code     = $params['sc_code'];
        $name        = $params['name'];
        $mobile      = $params['mobile'];
        $channel_id  = $params['channel_id'];
        $salesman_id = $params['salesman_id'];
        $start_time  = $params['start_time'];
        $end_time    = $params['end_time'];
        $sc_codes    = $params['sc_codes'];
        $invite_from = $params['invite_from'];
     
        $order = 'uc.id DESC';
        // $where = array('uc.sc_code' => $sc_code, 'uc.status' => 'ENABLE');
        !empty($sc_code)        &&   $where['uc.sc_code']  =  $sc_code;
        $where['uc.status'] = 'ENABLE';
        !empty($sc_codes) && $where['uc.sc_code'] = array('in', $sc_codes);
        !empty($invite_from) && $where['uc.invite_from'] = $invite_from;
      
        !empty($name) && $where['uc.name'] = array('like', "%$name%");
        !empty($mobile) && $where['uc.mobile'] = $mobile;
        !empty($channel_id) && $where['uc.channel_id'] = $channel_id;
        !empty($salesman_id) && $where['uc.salesman_id'] = $salesman_id;
        !empty($start_time) && empty($end_time) && $where['uc.create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['uc.create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['uc.create_time'] = array('between', array($start_time, $end_time));
        $fields = " uc.*,sh.name as channel_name,sh.status as channel_status,ss.name as salesman_name,ss.status as salesman_status";

        $params['fields']      = $fields;
        $params['order']       = $order;
        $params['where']       = $where;
        $params['center_flag'] = SQL_UC;
        $params['sql_flag']    = 'customer_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        return $this->res($list_res['response']);
    }

    /**
     * Base.UserModule.Customer.Customer.cmsPopCustomer
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function cmsPopCustomer($params){
        $this->_rule = array(
            array('sc_codes',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码 
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码   如果是pop平台查看   则一定需要传入  ,如果是  cms 平台  则无需传入
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK), //买家电话
            array('commercial_name', 'require', PARAMS_ERROR, ISSET_CHECK),//店铺名称
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),//买家编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code     = $params['sc_code'];
        $mobile      = $params['mobile'];
        $commercial_name = $params['commercial_name'];
        $uc_code       = $params['uc_code'];
        $page        = $params['page'] ? $params['page'] : 1;
        $page_number = $params['page_number'] ? $params['page_number'] : 20;
        $order       = 'um.id DESC';
        !empty($sc_code)  &&   $where['ss.sc_code']  =  $sc_code;
        // !empty($sc_codes) && $where['um.sc_code'] = array('in', $sc_codes);
        !empty($params['sc_code'])&& $where['ss.sc_code'] = $params['sc_code'];
        !empty($params['mobile'])&& $where['um.mobile'] = $params['mobile'];
        !empty($params['uc_code'])&& $where['um.uc_code'] = $params['uc_code'];
        !empty($params['commercial_name'])&& $where['um.commercial_name'] =array("like","%$commercial_name%");
        $where['uc.status']      = 'ENABLE';
        $where['um.invite_from'] = 'SC';
        $fields = 'um.*,ss.name as store_name,uc.create_time as create_time,um.terminal';

        $data['page_number'] = $page_number;
        $data['page']        = $page;
        $data['fields']      = $fields;
        $data['order']       = $order;
        $data['where']       = $where;
        $data['center_flag'] = SQL_UC;
        $data['sql_flag']    = 'cms_POP_customer';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $data);
        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        //获取所有终端买家数量
        $where['um.terminal']='YES';
        $where['um.invite_from']='SC';
        $where = D()->parseWhereCondition($where);
        $sql = "SELECT
                                            count(*) as num
                                    FROM
                                            {$this->tablePrefix}uc_member um
                                    LEFT JOIN {$this->tablePrefix}uc_customer uc ON um.uc_code = uc.uc_code

                                    LEFT JOIN {$this->tablePrefix}sc_store ss ON uc.sc_code = ss.sc_code
                                            {$where}
                                    ORDER BY
                                            {$order}";
        $res = D()->query($sql);
//        $terminal_num = D('UcMember')->field('count(*) as num')->where(array('terminal'=>'YES','invite_from'=>'SC'))->find();
        if(!$res[0]['num']){
            $terminal_num = 0;
        }else{
            $terminal_num = $res[0]['num'];
        }
        $list_res['response']['terminal_num'] = $terminal_num;
        return $this->res($list_res['response']);
    }

    /**
     * Base.UserModule.Customer.Customer.export
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function export_pop($params){
        $this->_rule = array(
            array('sc_code','require',PARAMS_ERROR,ISSET_CHECK),       //商户编码
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK), //买家电话
            array('commercial_name', 'require', PARAMS_ERROR, ISSET_CHECK), //买家店铺名称
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //买家编码
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出转账查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取平台标识  业务参数
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $order          =  $params['order'];
        $commercial_name = $params['commercial_name'];
        //默认参数
        $default_title      =  array('买家编码','买家账号','买家店铺','买家姓名','买家手机号','卖家店铺','注册时间','买家标签');
        $default_fields     =  'um.*,ss.name as store_name,uc.create_time as create_time,um.terminal';
        $default_callback_api = 'Com.Callback.Export.UcExport.export';

        $default_filename   =  'POP买家列表';
        $default_sql_flag   =  'cms_POP_customer';
        $default_order      =  'um.id DESC';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ?  $default_callback_api: $callback_api;

        //组装where 条件
        $where         =  array();
        !empty($params['sc_code'])&& $where['ss.sc_code'] = $params['sc_code'];
        !empty($params['mobile'])&& $where['um.mobile'] = $params['mobile'];
        !empty($params['uc_code'])&& $where['um.uc_code'] = $params['uc_code'];
        !empty($params['commercial_name'])&& $where['um.commercial_name'] =array("like","%$commercial_name%");

        //组装调用导出api参数
        $data['where']        =  $where;
        $data['fields']       =  $default_fields;
        $data['title']        =  $title;
        $data['order']        =  empty($order) ? $default_order : $order;
        $data['center_flag']  =  SQL_UC;//用户中心
        $data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $data['filename']     =  $filename;
        $data['callback_api'] = $callback_api;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }
    /**
     * Base.UserModule.Customer.Customer.plat_export
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function plat_export($params){
        $this->_rule = array(
            array('sales_uc_code','require',PARAMS_ERROR,ISSET_CHECK),       //业务员用户编码
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK), //买家电话
            array('commercial_name', 'require', PARAMS_ERROR, ISSET_CHECK), //买家店铺名称
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //买家编码
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出转账查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK), //sql的标识
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取平台标识  业务参数
        //系统参数
        $title          =  $params['title'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        $order          =  $params['order'];
        $commercial_name =  $params['commercial_name'];
        //默认参数
        $default_title      =  array('买家编码','买家账号','买家店铺','买家姓名','买家手机号','平台业务员','注册时间','买家标签');
        $default_fields     =  'um.*,uc.create_time as create_time,um.terminal';
        $default_callback_api = 'Com.Callback.Export.UcExport.plat_export';

        $default_filename   =  '平台买家列表';
        $default_sql_flag   =  'cms_platform_customer';
        $default_order      =  'uc.create_time DESC';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ?  $default_callback_api: $callback_api;

        //组装where 条件
        $where         =  array();
        $where['uc.status']      = 'ENABLE';
        $where['um.invite_from'] = 'UC';
        !empty($params['sales_uc_code'])&& $where['us.uc_code'] = $params['sales_uc_code'];
        !empty($params['mobile'])&& $where['um.mobile'] = $params['mobile'];
        !empty($params['uc_code'])&& $where['um.uc_code'] = $params['uc_code'];
        !empty($params['commercial_name'])&& $where['um.commercial_name'] = array("like","%$commercial_name%");
        //组装调用导出api参数
        $data['where']        =  $where;
        $data['fields']       =  $default_fields;
        $data['title']        =  $title;
        $data['order']        =  empty($order) ? $default_order : $order;
        $data['center_flag']  =  SQL_UC;//用户中心
        $data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $data['filename']     =  $filename;
        $data['callback_api'] = $callback_api;
        $data['param'] = $params;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }
    /**
     * Base.UserModule.Customer.Customer.platformList
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function platformList($params){
        $this->_rule = array(
            // array('invite_code',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码 
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('sales_uc_code','require',PARAMS_ERROR,ISSET_CHECK),       //业务员用户编码
            array('mobile', 'require', PARAMS_ERROR, ISSET_CHECK), //买家电话
            array('commercial_name', 'require', PARAMS_ERROR, ISSET_CHECK), //买家店铺名称
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //买家编码
            array('invite_from','require',PARAMS_ERROR,ISSET_CHECK), //客户类型，不传 取出全部 
            array('username','require',PARAMS_ERROR, ISSET_CHECK),         
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sales_uc_code    = $params['sales_uc_code'];
        $page_number = $params['page_number'];
        $page        = $params['page'];
        $commercial_name = $params['commercial_name'];
        $invite_from = $params['invite_from'];
        $username    = $params['username'];
        !empty($sales_uc_code) && $where['us.uc_code'] =  $sales_uc_code;
        !empty($params['mobile'])&& $where['um.mobile'] = $params['mobile'];
        !empty($params['uc_code'])&& $where['um.uc_code'] = $params['uc_code'];
        !empty($params['commercial_name'])&& $where['um.commercial_name'] = array("like","%$commercial_name%");
        !empty($params['invite_from']) &&  $where['um.invite_from'] =  $invite_from;
        !empty($username) && $where['_string'] = "uc.username='$username' or um.username='$username'";
        $where['uc.status']      = 'ENABLE';
        $order  = 'uc.create_time desc';
        $fields = "um.*, uc.create_time";

        $data['fields']      = $fields;
        $data['order']       = $order;
        $data['where']       = $where;
        $data['page']        = $page;
        $data['page_number'] = $page_number;
        $data['center_flag'] = SQL_UC;
        $data['sql_flag']    = 'cms_platform_customer';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        
        $list_res = $this->invoke($apiPath, $data);

        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        //获取所有终端买家数量
        $where['um.terminal']='YES';
        $where['um.invite_from']='UC';
        $where = D()->parseWhereCondition($where);
        $sql = "SELECT
                                            count(*) as num
                                    FROM
                                            {$this->tablePrefix}uc_member um
                                    LEFT JOIN {$this->tablePrefix}uc_user uc ON um.uc_code = uc.uc_code
                                    LEFT JOIN {$this->tablePrefix}uc_salesman us ON um.invite_code = us.invite_code

                                            {$where}
                                    ORDER BY
                                            {$order}";
        $res = D()->query($sql);
//        $terminal_num = D('UcMember')->field('count(*) as num')->where(array('terminal'=>'YES','invite_from'=>'UC'))->find();
        if(!$res[0]['num']){
            $terminal_num = 0;
        }else{
            $terminal_num = $res[0]['num'];
        }
        $list_res['response']['terminal_num'] = $terminal_num;
        return $this->res($list_res['response']);
    }

    /**
     * 获取客户
     * Base.UserModule.Customer.Customer.get
     * @param type $params
     */
    public function get($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码 
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];
        $where = array(
            'um.uc_code' => $uc_code,
        );
        
        if(!empty($sc_code)){
            $where['uc.sc_code'] = $sc_code;
            $where['uc.status'] = 'ENABLE';
        }
        $customer_res = $this->model->alias('uc')
                ->join("{$this->tablePrefix}uc_member um on uc.uc_code = um.uc_code", 'RIGHT')
                ->field('uc.*,um.username,um.pay_privs,um.commercial_name,um.name,um.mobile,um.province,um.city,um.district,um.invite_code,um.address,um.invite_from')
                ->where($where)
                ->find();
        if ($customer_res === FALSE) {
            return $this->res(NULL, 6711);
        }
        //注册方式
        $invite_from = $customer_res['invite_from'];
        //如果  有渠道
        if(!empty($customer_res['channel_id']) && $invite_from == 'SC'){
            //获取渠道
            $apiPath = "Base.UserModule.Customer.Channel.get";
            $data = array('channel_id'=>$customer_res['channel_id']);
            $channel_res = $this->invoke($apiPath,$data);
            if($channel_res['status'] != 0){
                return $this->res(NULL,$channel_res['status']);
            }
            $customer_res['channel'] = $channel_res['response']['name'];
        }
        if(!empty($customer_res['salesman_id'])  && $invite_from == 'SC'){
            //获取业务员
            $apiPath = "Base.UserModule.Customer.Salesman.get";
            $data = array('salesman_id'=>$customer_res['salesman_id']);
            $salesman_res = $this->invoke($apiPath,$data);
            if($salesman_res['status'] != 0){
                return $this->res(NULL,$salesman_res['status']);
            }
            $customer_res['salesman_mobile'] = $salesman_res['response']['mobile'];
            $customer_res['salesman'] = $salesman_res['response']['name'];
        }
        
        if($invite_from == 'UC'){
            //如果是平台业务员  则取出平台业务员信息
            $apiPath = "Base.UserModule.User.User.getPlatformSalesman";
            $data = array('invite_code'=>$customer_res['invite_code']);
            $uc_salesman_res = $this->invoke($apiPath, $data);
            $customer_res['salesman_mobile'] = $uc_salesman_res['response']['mobile'];
            $customer_res['salesman'] = $uc_salesman_res['response']['real_name'];
        }
        return $this->res($customer_res);
    }

    /**
     * Base.UserModule.Customer.Customer.getOrderRegion
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getOrderRegion($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码 
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('uc_codes', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];
        $uc_codes = $params['uc_codes'];
        if (!empty($uc_code)) {
            $where = array(
                'obo.uc_code' => array('in',array($uc_code)),
                'obo.sc_code' => $sc_code,
            );
        }
        if (!empty($uc_codes)) {
            $where = array();
            $where['obo.uc_code'] = array('in', $uc_codes);
            $where['obo.sc_code'] = $sc_code;
        }
        // var_dump($where);
        $order = 'obo.create_time desc';
        $res = D('OcB2bOrder')->alias('obo')->join("{$this->tablePrefix}oc_b2b_order_extend oboe on obo.op_code = oboe.op_code", 'LEFT')
                ->field('oboe.province,obo.uc_code,oboe.city,oboe.district,oboe.address')
                ->where($where)
                ->order($order)
                ->select();
        // echo D()->getLastSql();
                // var_dump($res);
        if ($res === FALSE) {
            return $this->res(NULL, 8);
        }
        if ($uc_code) {
            $res = $res[0];
        }
        return $this->res($res);
    }

    /**
     * 获取客户
     * Base.UserModule.Customer.Customer.getAll
     * @param type $params
     */
    public function getAll($params){
       $this->_rule = array(
           array('sc_codes',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码 
           array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码 
           array('uc_code',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), 
       );
       if (!$this->checkInput($this->_rule, $params)) { # 自动校验
           return $this->res($this->getErrorField(), $this->getCheckError());
       }
       $sc_code = $params['sc_code'];
       $uc_code = $params['uc_code'];
       $sc_codes = $params['sc_codes'];
       // $where = array(
       //     'uc.sc_code' => $sc_code,
       //     'uc.uc_code' => array('in', $uc_code),
       //     'uc.status' => 'ENABLE'
       // );

       !empty($sc_code)        &&   $where['uc.sc_code']  =  $sc_code;
       $where['uc.status'] = 'ENABLE';
       !empty($sc_codes) && $where['uc.sc_code'] = array('in', $sc_codes);
       !empty($uc_code) &&   $where['uc.uc_code']  =  array('in', $uc_code);

       $customer_res = $this->model->alias('uc')
               ->join("{$this->tablePrefix}uc_member um on uc.uc_code = um.uc_code", 'LEFT')
               ->field('uc.*,um.username,um.commercial_name,um.province,um.city,um.district,um.invite_code,um.address,um.invite_from')
               ->where($where)
               ->select();
        // echo D()->getLastSql();
       if ($customer_res === FALSE) {
           return $this->res(NULL, 6711);
       }
       $channel_id  = $salesman_id = array();
       foreach ($customer_res as $customer) {
           if ($customer['channel_id']) {
               $channel_id[] = $customer['channel_id'];
           }

           if ($customer['salesman_id']) {
               $salesman_id[] = $customer['salesman_id'];
           }
       }
       // var_dump($customer_res);die();
       //如果  有渠道
       if(!empty($channel_id)){
           //获取渠道
           $apiPath     = "Base.UserModule.Customer.Channel.getAll";
           $data        = array('channel_id' => $channel_id);
           $channel_res = $this->invoke($apiPath,$data);
           if($channel_res['status'] != 0){
               return $this->res(NULL,$channel_res['status']);
           }
           
           $channel = changeArrayIndex($channel_res['response'], 'id');
       }
       if(!empty($salesman_id)){
           //获取业务员
           $apiPath = "Base.UserModule.Customer.Salesman.getAll";
           $data    = array('salesman_id'=>$salesman_id);

           $salesman_res = $this->invoke($apiPath,$data);
           if($salesman_res['status'] != 0){
               return $this->res(NULL,$salesman_res['status']);
           }

           $salesman = changeArrayIndex($salesman_res['response'], 'id');
       }

       foreach ($customer_res as $key => $customer) {
           if (!empty($channel)) {
               if ($channel[$customer['channel_id']]) {
                   $customer_res[$key]['channel'] = $channel[$customer['channel_id']]['name'];
               }
           }

           if (!empty($salesman)) {
               if ($salesman[$customer['salesman_id']]) {
                  $customer_res[$key]['salesman_mobile'] = $salesman[$customer['salesman_id']]['mobile'];
                  $customer_res[$key]['salesman']        = $salesman[$customer['salesman_id']]['name'];
               }
           }
       }
       return $this->res($customer_res); 
    }

    /**
     * 添加客户主表信息
     * Base.UserModule.Customer.Customer.add
     * @param type $params
     */
    public function add($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //客户名称
            array('mobile', 'require', PARAMS_ERROR, MUST_CHECK), //电话号码
            array('channel_id', 'require', PARAMS_ERROR, HAVEING_CHECK), //渠道id
            array('salesman_id', 'require', PARAMS_ERROR, HAVEING_CHECK), //业务员id
            array('invite_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //邀请码id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];
        $name = $params['name'];
        $mobile = $params['mobile'];
        $channle_id  = empty($params['channel_id']) ? 0 :$params['channel_id'];
        $salesman_id  = empty($params['salesman_id']) ? 0 :$params['salesman_id'];
        $invite_code  = empty($params['invite_code']) ? 0 :$params['invite_code'];

        $data = array(
            'sc_code' => $sc_code,
            'uc_code' => $uc_code,
            'name' => $name,
            'mobile' => $mobile,
            'channel_id' => $channle_id,
            'salesman_id' => $salesman_id,
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME,
            'status' => 'ENABLE',
            'invite_code'=> $invite_code,
        );
        $res = $this->model->add($data);
        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 6710);
        }
        
        //如果有渠道  则 渠道数加1 
        if(!empty($channle_id)){
            $this->updateChannelNums($sc_code, $channle_id, '+', 'channel');
        }
        //如果有业务员  则业务员数量加1 
        if(!empty($salesman_id)){
            $this->updateChannelNums($sc_code, $salesman_id, '+', 'salesman');
        }
        return $this->res($res);
    }

    /**
     * Base.UserModule.Customer.Customer.addPlatCustomer
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function addPlatCustomer($data){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //客户名称
            array('mobile', 'require', PARAMS_ERROR, MUST_CHECK), //电话号码
        );
        if (!$this->checkInput($this->_rule, $data)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $res = D('UcCustomer')->add($data);
        if($res === false || $res<0){
            return $this->res('',6710);
        }
        return $this->res($res);
    }
    /**
     * Base.UserModule.Customer.Customer.getPlatCustomer
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getPlatCustomer($where){
        $res = D('UcCustomer')->where($where)->select();
       return $this->res($res);
    }
    /**
     * Base.UserModule.Customer.Customer.getInvite
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getInvite($params){
        $uc_code = $params['uc_code'];
        $where['uc_code'] =$uc_code;
        $res =  D('UcMember')->field('invite_from')->where($where)->find();
        return $this->res($res);
    }
    /**
     * 更新客户信息
     * Base.UserModule.Customer.Customer.update
     * @param type $params
     */
    public function update($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码 
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码 
            array('channel_id', 'require', PARAMS_ERROR, ISSET_CHECK), //渠道id
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK), //业务员id
            array('orders', 'require', PARAMS_ERROR, HAVEING_CHECK), //添加的订单数
            array('order_amount', 'require', PARAMS_ERROR, HAVEING_CHECK), //成交的订单金额
            array('remark','require',PARAMS_ERROR, ISSET_CHECK),  //备注
            array('order_time','require',PARAMS_ERROR, ISSET_CHECK)  //最后成交时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];
        $channel_id = $params['channel_id']+0;
        $salesman_id = $params['salesman_id']+0;
        $orders = $params['orders'];
        $order_amount = $params['order_amount'];
        $remark       = $params['remark'];
        $order_time   = $params['order_time'];
        $where = array(
            'uc_code' => $uc_code,
            'sc_code' => $sc_code
        );
        //查询该条信息是否属于该商户的
        $customer_info = $this->model->where($where)->find();
        if (empty($customer_info)) {
            return $this->res(NULL, 6712);
        }
        //创建附表更新数据
        $save_data = array();
        //更新标签
        if (isset($params['channel_id'])) {

            $this->changeChannelCustomers($customer_info['channel_id'], $channel_id, $sc_code, 'channel'); //修改渠道客户数
            if (!empty($channel_id) && $channel_id != $customer_info['channel_id']) {
                //查询该渠道的所有者
                $channel_info = D('ScChannel')->where(array('id' => $channel_id, 'sc_code' => $sc_code,'status'=>'ENABLE'))->find();

                if (empty($channel_info)) {
                    return $this->res(NULL, 6714);
                }
                
            }
            $save_data['channel_id'] = $channel_id;
        }
        if (isset($params['salesman_id'])) {
             $this->changeChannelCustomers($customer_info['salesman_id'], $salesman_id, $sc_code, 'salesman'); //修改业务员客户数
            if (!empty($salesman_id) && $salesman_id != $customer_info['salesman_id']) {
                //查询该业务员的所有者
                $salesman_info = D('ScSalesman')->where(array('id' => $salesman_id, 'sc_code' => $sc_code,'status'=>'ENABLE'))->find();
                if (empty($salesman_info)) {
                    return $this->res(NULL, 6715);
                }
                
            }
            $save_data['salesman_id'] = $salesman_id;
        }

        !empty($orders)       && $save_data['orders'] = array('+', $orders);
        !empty($order_amount) && $save_data['order_amount'] = array('+', $order_amount);
        !empty($order_time)   && $save_data['order_time']   = $order_time;
        $save_data['remark'] = $remark;
        $save_data['update_time'] = NOW_TIME;
        $res = $this->model->where($where)->save($save_data);
        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 6716);
        }
        return $this->res(TRUE);
    }

    /**
     * 修改渠道,业务员的客户数
     * @param type $old_channel_id
     * @param type $new_channel_id
     * @param type $sc_code
     */
    private function changeChannelCustomers($old_id, $new_id, $sc_code, $type) {

        $new_id = $new_id + 0;
        if ($type == 'channel') {
            $apiPath = "Base.UserModule.Customer.Channel.update";
            $field = "channel_id";
        } else if ($type == 'salesman') {
            $apiPath = "Base.UserModule.Customer.Salesman.update";
            $field = "salesman_id";
        }
        if ($old_id != $new_id) {
            //如果两次的 id 不一样  则 需要修改
            if (!empty($old_id)) {
                //如果原来不空  则 减原来的
                $data = array('sc_code' => $sc_code, $field => $old_id, 'method' => '-');
                $res = $this->invoke($apiPath, $data);
                if ($res['status'] != 0) {
                    return $this->endInvoke(NULL, $res['status']);
                }
            }
            if (!empty($new_id)) {
                $data = array('sc_code' => $sc_code, $field => $new_id, 'method' => '+');
                $res = $this->invoke($apiPath, $data);
                if ($res['status'] != 0) {
                    return $this->endInvoke(NULL, $res['status']);
                }
            }
        }
        return TRUE;
    }
    
    
    private function updateChannelNums($sc_code,$id,$method,$type){
        if ($type == 'channel') {
            $apiPath = "Base.UserModule.Customer.Channel.update";
            $field = "channel_id";
        } else if ($type == 'salesman') {
            $apiPath = "Base.UserModule.Customer.Salesman.update";
            $field = "salesman_id";
        }
//        $apiPath = "Base.UserModule.Customer.Channel.update";
        $data = array(
            'sc_code' => $sc_code,
             $field=> $id,
            'method' => $method,
        );
        $update_res = $this->invoke($apiPath, $data);
        if($update_res['status'] != 0){
            return $this->endInvoke(NULL,$update_res['status']);
        }
        return TRUE;
    }


    /**
     * 客户模糊查询
     * Base.UserModule.Customer.Customer.search
     * @param type $params
     * @return type
     */

    public function search($params){
        $this->_rule = array(
            array('name','require',PARAMS_ERROR,ISSET_CHECK),        #模糊搜索的名称
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),   #店铺编码  
            array('status', array(UC_CUSTOMER_STATUS_DISABLE, UC_CUSTOMER_STATUS_ENABLE), PARAMS_ERROR, HAVEING_CHECK, 'in'), #状态
            array('salesman_id','require',PARAMS_ERROR,ISSET_CHECK), #业务员名称
        );
        if (!$this->checkInput($this->_rule, $params)) {             # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code     = $params['sc_code'];
        $status      = empty($params['status']) ? UC_CUSTOMER_STATUS_ENABLE : $params['status'];
        $name        = $params['name'];
        $salesman_id = $params['salesman_id'];

        $where            = array();
        !empty($name) && $where['name']    = array('like','%'.$name.'%');
        !empty($salesman_id) && $where['salesman_id'] = $salesman_id;
        $where['sc_code'] = $sc_code;
        $where['status']  = $status;

        $customer_search = $this->model->where($where)->group('name')->select();
        return $this->res($customer_search);
    }


    /**
     * 客户导出
     * Base.UserModule.Customer.Customer.export
     * @param type $params
     * @return type
     */
      public function  export($params){
        $this->_rule = array(
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),           #  页码               非必须参数, 默认值 1
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK), //导出提现查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK), //文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK), //导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK),  //sql的标识
            array('name','require',PARAMS_ERROR,ISSET_CHECK),         //客户名称
            array('mobile','require',PARAMS_ERROR,ISSET_CHECK),       //手机号码
            array('channel_id','require',PARAMS_ERROR,ISSET_CHECK),   //渠道ID
            array('salesman_id','require',PARAMS_ERROR,ISSET_CHECK),  //业务员ID
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),  # 起始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),    # 结束时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取平台标识  业务参数
        $sc_code     =  $params['sc_code'];
        $uc_code     =  $params['uc_code'];
        $salesman_id = $params['salesman_id'];
        $channel_id  = $params['channel_id'];
        $invite_from = $params['invite_from'];

        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $sql_flag       =  $params['sql_flag'];
        $callback_api   =  $params['callback_api'];
        //默认参数
        $default_title      =  array('店名','客户名称','注册时间','电话','省','市','区','详细地址','成交单数','成交金额（元）','积分','客户类型','业务员', '渠道');  //默认导出列标题
        $default_fields     =  'uc.name,uc.uc_code,from_unixtime(uc.create_time) as create_time,uc.mobile,uc.orders,uc.order_amount,sh.name as shname,ss.name as ssname';            //默认导出列
        $default_filename   =  '客户管理列表';
        $default_sql_flag   =  'customer_list'; 
        $default_order      =  'uc.id DESC';
        $default_callback_api = 'Com.Callback.Export.UcExport.CustomerListPOP';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        //组装where 条件
        $where         =  array();
        $where['uc.sc_code']= $sc_code;
        empty($params['name'])        || $where['uc.name']        = array('like','%'.$params['name'].'%');
        empty($params['mobile'])      || $where['uc.mobile']      = $params['mobile'];
        empty($params['channel_id'])  || $where['uc.channel_id']  = $params['channel_id'];
        empty($params['salesman_id']) || $where['uc.salesman_id'] = $params['salesman_id'];
        
        !empty($invite_from) && $where['uc.invite_from'] = $invite_from;

        !empty($params['start_time']) && $start_time = strtotime($params['start_time']);
        !empty($params['end_time']) &&   $end_time   = strtotime($params['end_time'])+86399;
        !empty($start_time) && empty($end_time) && $where['uc.create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $where['uc.create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $where['uc.create_time'] = array('between', array($start_time, $end_time));

        //组装调用导出api参数
        $params['where']        =  $where;
        $params['fields']       =  $default_fields;
        $params['title']        =  $title;
        $params['order']        =  empty($order) ? $default_order : $order;
        $params['center_flag']  =  SQL_UC;
        $params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $params['filename']     =  $filename;
        $params['callback_api'] =  empty($callback_api) ? $default_callback_api : $callback_api;
        $params['uc_code']      =  $uc_code;
        $apiPath  =  "Com.Common.CommonView.Export.export";

        $res = $this->invoke($apiPath, $params);

        return $this->res($res['response'],$res['status']);
    }

    /**
     * 店铺列表
     * Base.UserModule.Customer.Customer.commercialList
     * @param type $params
     */
    public function commercialList($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码   如果是pop平台查看   则一定需要传入  ,如果是  cms 平台  则无需传入
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code = $params['sc_code'];

        $order = 'uc.id DESC';
        $where = array('uc.sc_code' => $sc_code, 'uc.status' => 'ENABLE');
        $commercial_lists = D('UcMember')->alias('um')
                ->join("{$this->tablePrefix}uc_customer uc on um.uc_code = uc.uc_code",'LEFT')
                ->field('distinct(um.commercial_name)')
                        ->where($where)
                        ->select();

        return $this->res($commercial_lists);
    }


    /**
     * 获取小B店铺名称
     * Base.UserModule.Customer.Customer.getCommercial
     * @access public
     * @author Todor
     */
    public function getCommercial($params){
        $this->_rule = array(
            array('uc_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), # 客户名称
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['uc_code'] = array('in',$params['uc_codes']);
        $commercial = D('UcMember')->field('commercial_name,uc_code')->where($where)->select();
        return $this->res($commercial);
    }


}

?>
