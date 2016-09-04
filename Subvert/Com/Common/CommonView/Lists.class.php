<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 公共列表
 */

namespace Com\Common\CommonView;

use System\Base;

class Lists extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     *
     * 公共列表接口
     * Com.Common.CommonView.Lists.Lists
     * @param type $params
     * @return type
     */
    public function Lists($params){
        //验证
        $this->_rule = array( 
            array('page', '0', PARAMS_ERROR, ISSET_CHECK,'gt'), # 1
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK), # 20
            array('sql_flag', 'require', PARAMS_ERROR, MUST_CHECK),
            array('center_flag',array('Ic','Sc','Tc','Uc','Oc','Spc','Fc','Bic','App'),PARAMS_ERROR, MUST_CHECK,'in'), #中心标示  SQL_IC   SQL_SC  SQL_TC  SQL_UC  SQL_OC
            array('other', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('aggre','checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),  //array(array('sum','real_amount','total_amount'));   //聚合相关参数  依次为   聚合函数名  聚合字段  聚合后的别名  别名不能为 total_item
            array('db_flag', 'require', PARAMS_ERROR, ISSET_CHECK), # 20
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());

        }

        $page        = $params['page'];   //访问页码
        $page        = max(1,$page);
        $page_number = $params['page_number'] + 0;  //单页数量
        $page_number = empty($page_number) ? C('DEFAULT_PAGE_NUMBER') : $page_number;  //默认20条数据
        $sql_flag    = $params['sql_flag'];  //sql的标示  在数组里面提取sql出来
        $center_flag = $params['center_flag']; //中心标示
        $aggre       = $params['aggre'];
        $db_flag     = $params['db_flag'];
        if($db_flag){
            $model=D('',C('DB_PREFIX'),C('DB_BIC'));
        }else{
            $model=D();
        }
        
        $start       = ($page - 1) * $page_number;
        $fields      =  empty($params['fields']) ? '*' : $params['fields'];
        $order       = $params['order']; 
        $where       = empty($params['where']) ? ' 1=1  ' : D()->parseWhereCondition($params['where']) ;
        
        //去掉最前面的where
        $where       = ltrim($where);
        $where       =  ' '.ltrim($where,'WHERE');
        $group        = $params['group'];
        $having       = $params['having'];
        $other        = $params['other'];
        $apiPath      =  "Com.Common.CommonView.{$center_flag}Sql.sqls";
        $centerSqlObj = M($apiPath);
        //获取 执行的sql
        $total_fields = ' count(*) as total_item ';
        if(!empty($aggre)){
            foreach($aggre as $k=>$v){
                if(!$v[0]){
                    $total_fields .=" ,$v[1] as $v[2] ";
                }else{
                    $total_fields .= "  ,$v[0]($v[1]) as $v[2]  ";
                }
            }
        }
        $total_sql = $centerSqlObj->sqls("$total_fields",$where,'null',$group,$having,$sql_flag,$other); //总条目sql语句
        $sql       = $centerSqlObj->sqls($fields,$where,$order,$group,$having,$sql_flag,$other);  //数据sql语句
        $sql       = $sql." limit {$start},{$page_number} ";
        if(empty($total_sql)){
            return $this->res(null,2002);
        }
        //进行转义
//        $sql = $sql;
//        $total_sql = $total_sql;

        //查询 展示的数据
        $select_res = $model->query($sql);
        // echo $sql;die();
//        if(empty($select_res)){
//            return $this->res(null,2004);
//        }
        //查询总数
        $total_res =$model->query($total_sql);
//        var_dump($total_res);exit;
        if(count($total_res) > 1 || !empty($group)){
            $total_res[0]['total_item'] = count($total_res);
        }
        $total_item = $total_res[0]['total_item'];
//        if($total_item <= 0){
//            //查询 展示的数据
//            return $this->res(null,2003);
//        }
//        $total_page = ceil($total_item/$page_number);
        $data = array(
            'totalnum'=>$total_item,
            'lists'=>$select_res,
            'page'=>$page,  //当前页码
            'page_number' => $page_number,
            'total_page' =>  ceil($total_item/$page_number),//总页数
        );
        
        //返回额外的聚合函数
        if(!empty($aggre)){
            foreach($aggre as $k=>$v){
                if(!$v[0]){
                    $data[$v[2]]=array_column($total_res,$v[2]);
                }else{
                  $data[$v[2]] = $total_res[0][$v[2]];
                }
            }
        }
        //返回值
        return $this->res($data);
    }

}

?>
