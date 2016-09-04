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

namespace Base\BicModule\Sc;

use System\Base;

class Store extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        $this->tablePrefix=C('DB_PREFIX');
        $this->connection=C('DB_BIC');
    }

    /**
     * Base.BicModule.Sc.Store.lists
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function lists($params){
        $model=D('ScStore',$this->tablePrefix,$this->connection);
        $res = $model->field('sc_code,name')->where(array('status'=>'ENABLE'))->select();
        return $this->res($res);
    }

    public function export($params){
        $this->_rule = array(
            array('create_start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询注册日期开始时间
            array('create_end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询注册日期结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //小B用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $create_start_time   =  empty($params['create_start_time']) ? 0 : strtotime($params['create_start_time']);
        $create_end_time     =  empty($params['create_end_time'])   ? time() : strtotime($params['create_end_time']);
//        var_dump($start_time);exit;
        $sc_code    = $params['sc_code'];
        $where=array();
        !empty($create_start_time) && empty($create_end_time) && $where['create_time'] = array('egt', $create_start_time);
        !empty($create_end_time) && empty($create_start_time) && $where['create_time'] = array('elt', $create_end_time);
        !empty($create_start_time) && !empty($create_end_time) && $where['create_time'] = array('between', array($create_start_time, $create_end_time));
        !empty($sc_code) && $where['sc_code'] = $sc_code;

        //默认参数
        $default_title=array('卖家名称','卖家联系人','卖家联系电话','开通日期','最近登录时间','客户总数','新增注册客户数','新增已付款客户数','新增已付款总额(元)','新增已付款平台客户数','客单价(元)','新增已付款总额(不包括预付款金额)','待发货订单数','卖家取消','已发货','发货率','卖家上月发货平均时长(分钟)','平台上月发货平均时长(分钟)','新增成单总量','新增成单总额(元)','成单总额同期环比','最后一次成单时间');
        $default_fields='sc_code,name,create_time,linkman,phone';
        $default_filename   =  '卖家数据统计';
        $default_sql_flag   =  'store_list';
        $default_order      =  'create_time desc';
        $default_api        =  'Com.Callback.Export.BicExport.storeList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        $group='sc_code';
        $data['group']      =   $group;
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
//        echo '23';exit;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
//        var_dump($res);exit;
        return $this->res($res['response'],$res['status']);
    }
    //得到商家的信息
    public function storeInfo($params){

        $this->_rule = array(
            array('create_start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),//查询注册日期开始时间
            array('create_end_time','require',PARAMS_ERROR,ISSET_CHECK),       //查询注册日期结束时间
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店家编码
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('page_number', 'require' , PARAMS_ERROR, ISSET_CHECK),	#  每页行数			非必须参数, 默认值 20
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $create_start_time = $params['create_start_time'];
        $create_end_time = $params['create_end_time'];
        $sc_code    = $params['sc_code'];
        $where=array();
        !empty($create_start_time) && empty($create_end_time) && $where['create_time'] = array('egt', $create_start_time);
        !empty($create_end_time) && empty($create_start_time) && $where['create_time'] = array('elt', $create_end_time);
        !empty($create_start_time) && !empty($create_end_time) && $where['create_time'] = array('between', array($create_start_time, $create_end_time));
        !empty($sc_code) && $where['sc_code'] = $sc_code;

        $fields='sc_code,name,create_time,linkman,phone';
//        $fields="*";
        $order='create_time desc';
        $group='uc_code';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $data['aggre']=array(array('','sc_code','sc_code'));
        $data['page']=$page;
        $data['page_number']=$pageNumber;
        $data['where']=$where;
        $data['group']=$group;
        $data['order']=$order;
        $data['fields']=$fields;
        $data['sql_flag']='store_list';
        $data['center_flag']=SQL_BIC;
        $data['db_flag']='bic';
        $api_Path='Com.Common.CommonView.Lists.Lists';
//        $userInfo=$model->field('uc_code,username,login_time,create_time')->where($where)->select();
////        $userInfo=changeArrayIndex($userInfo,'uc_code');
//        $uc_code=array_column($userInfo,'uc_code');
        $call=$this->invoke($api_Path,$data);
//        var_dump($call);exit;
//        $store_info=$call['response']['lists'];
//        $sc_code=array_column($call['response']['lists'],'sc_code');
//
//        $store=array(
//            'store_info'=>$store_info,
//            'sc_code'=>$sc_code,
//        );
        //查出每个商户对应多少客户
//        echo D('',C('DB_PREFIX'),C('DB_BIC'))->getLastSql();exit;
        if($call['status']!==0){
            $this->res(null,$call['status'],'',$call['message']);
        }
        return $this->res($call['response']);
    }
}
?>
