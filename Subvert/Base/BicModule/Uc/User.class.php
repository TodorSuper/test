<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */

namespace Base\BicModule\Uc;

use System\Base;

class User extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        $this->tablePrefix=C('DB_PREFIX');
        $this->connection=C('DB_BIC');
    }

    //得到店铺名称
    public function lists(){
        $model=D('UcMember',$this->tablePrefix,$this->connection);
        $res = $model->field('uc_code,commercial_name as username')->where(array('status'=>'ENABLE'))->select();
        return $this->res($res);
    }

    public function export($params){
        $this->_rule = array(
            array('create_start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询注册日期开始时间
            array('create_end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询注册日期结束时间
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $create_start_time   =  empty($params['create_start_time']) ? 0 : strtotime($params['create_start_time']);
        $create_end_time     =  empty($params['create_end_time'])   ? time() : strtotime($params['create_end_time']);
        $uc_code    = $params['uc_code'];
        $where=array();
        !empty($create_start_time) && empty($create_end_time) && $where['create_time'] = array('egt', $create_start_time);
        !empty($create_end_time) && empty($create_start_time) && $where['create_time'] = array('elt', $create_end_time);
        !empty($create_start_time) && !empty($create_end_time) && $where['create_time'] = array('between', array($create_start_time, $create_end_time));
        !empty($uc_code) && $where['uc_code'] = $uc_code;

        //默认参数
        $default_title=array('买家姓名','买家类型','联系人姓名','联系人电话','买家编码','卖家姓名','注册日期','最近登录时间','创建订单数','买家取消','已付款','已付款总额(元)','已付款总额(不包括预付款金额)','订单平均价(元)','付款成功率','付款平均时长(分钟)','新增成单量','新增成单总额(元)','成单总额同期环比','最后一次成单时间');
        $default_fields='uc_code,commercial_name,invite_from,name,store_name,mobile,login_time,create_time';
        $default_filename   =  '买家数据统计';
        $default_sql_flag   =  'user_list';
        $default_order      =  'create_time desc';
        $default_api        =  'Com.Callback.Export.BicExport.userList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        $data['params']      =  $params;
        $data['where']        =  $where;
        $data['fields']       =  $default_fields;
        $data['title']        =  $title;
        $data['center_flag']  =  SQL_BIC;//订单中心
        $data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $data['filename']     =  $filename;
        $data['order']        =  empty($order) ? $default_order : $order;
        $data['callback_api'] = $callback_api;
        $data['db_flag']='bic';
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
//        var_dump($res);exit;
        return $this->res($res['response'],$res['status']);
    }

    //得到买家的信息
    public function userInfo($params){
        $this->_rule = array(
            array('create_start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询注册日期开始时间
            array('create_end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询注册日期结束时间
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('page_number', 'require' , PARAMS_ERROR, ISSET_CHECK),	#  每页行数			非必须参数, 默认值 20
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $create_start_time = $params['create_start_time'];
        $create_end_time = $params['create_end_time'];
        $uc_code    = $params['uc_code'];
        $where=array();
        !empty($create_start_time) && empty($create_end_time) && $where['create_time'] = array('egt', $create_start_time);
        !empty($create_end_time) && empty($create_start_time) && $where['create_time'] = array('elt', $create_end_time);
        !empty($create_start_time) && !empty($create_end_time) && $where['create_time'] = array('between', array($create_start_time, $create_end_time));
        !empty($uc_code) && $where['uc_code'] = $uc_code;

        $fields='uc_code,commercial_name,name,mobile,login_time,create_time,store_name,invite_from';
        $order='create_time desc';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $data['page']=$page;
        $data['page_number']=$pageNumber;
        $data['where']=$where;
        $data['order']=$order;
        $data['fields']=$fields;
        $data['sql_flag']='user_list';
        $data['center_flag']=SQL_BIC;
        $data['db_flag']='bic';
        $api_Path='Com.Common.CommonView.Lists.Lists';
        $call=$this->invoke($api_Path,$data);
        if($call['status']!==0){
            $this->res(null,$call['status'],'',$call['message']);
        }
        return $this->res($call['response']);
    }
}
?>
