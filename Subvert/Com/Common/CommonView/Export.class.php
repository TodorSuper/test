<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 公共导出类
 */

namespace Com\Common\CommonView;

use System\Base;

class Export extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    
    /**
     *
     * 队列同步数据导出
     * Com.Common.CommonView.Export.export
     * @param type $params
     * @return type
     */
    public function export($params){

        set_time_limit(0);
        $this->_rule = array( 
            array('sql_flag', 'require', PARAMS_ERROR, MUST_CHECK),
            array('center_flag',array('Ic','Sc','Tc','Uc','Oc','Spc','Fc','Bic'),PARAMS_ERROR, MUST_CHECK,'in'), #中心标示  SQL_IC   SQL_SC  SQL_TC  SQL_UC  SQL_OC SQL_SPC SQL_BIC
            array('other', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('title', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),  //标题
            array('fields', 'require', PARAMS_ERROR, MUST_CHECK),  //字段
            array('filename', 'require', PARAMS_ERROR, MUST_CHECK),  //文件名
            array('callback_api', 'require', PARAMS_ERROR, HAVEING_CHECK),  //回调处理数据的api
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),  //用户编码
            array('template_call_api','require',PARAMS_ERROR,ISSET_CHECK),
            array('db_flag', 'require', PARAMS_ERROR, ISSET_CHECK), # 20
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        };
        $export_limit_nums = C('EXPORT_LIMIT_NUMS');
        $sql_flag    = $params['sql_flag'];  //sql的标示  在数组里面提取sql出来
        $center_flag = $params['center_flag']; //中心标示
        $fields      = $params['fields'];  //导出字段
        $order       = $params['order']; 
        $title       = $params['title']; //导出数据标题
        $filename    = $params['filename'];//文件名称
        $where       = empty($params['where']) ? ' 1=1  ' : D()->parseWhereCondition($params['where']);
        //去掉最前面的where
        $where       = ltrim($where);
        $where       =  ' '.ltrim($where,'WHERE');
        $group       = $params['group'];
        $having      = $params['having'];
        $other       = $params['other'];
        $callback_api= $params['callback_api'];
        $template_call_api=$params['template_call_api'];
        $uc_code     = $params['uc_code'];
        $db_flag     = $params['db_flag'];
        if($db_flag){
            $model=D('',C('DB_PREFIX'),C('DB_BIC'));
        }else{
            $model=D();
        }
        //查询导出数据总条数  
        $apiPath     = "Com.Common.CommonView.{$center_flag}Sql.sqls";
        $centerSqlObj= M($apiPath);
        $total_sql   = $centerSqlObj->sqls(' count(*) as total_item ',$where,'null',$group,$having,$sql_flag,$other); //总条目sql语句
// echo $total_sql;exit;
        $total_res   = $model->query($total_sql);
        $total_item  = $total_res[0]['total_item'];

        //如果  大于同步导出最大数量   则采用异步导出方式
        if($total_item > $export_limit_nums){
            //扔队列  插数据库
            $params['total_num'] = $total_item;
            $res = $this->async($params);
            return $this->res(TRUE);
        }
        
        if(!($filename = $this->setDir($filename))){  //生成文件夹  获取文件名
            return $this->res(NULL,19);
        }
        $filename = $filename['absolute_name'];
        $fp = $this->setFileHead($filename, $title);  //生成文件

        //小于同步导出最大数量  则采用同步方式
        $sql         = $centerSqlObj->sqls($fields,$where,$order,$group,$having,$sql_flag,$other);  //数据sql语句
        //一次查询所有数据
        $_export_data = array();
        $_export_data = $model->query($sql);
        if(!empty($_export_data)){
            if(!empty($callback_api)){
                //如果设置回调处理数据的api  则  调用api
                $callback_api_exp   = explode('.', $callback_api);
                $callback_api_model = M($callback_api);
                $function = $callback_api_exp[4];  //调用函数
                $callback_api_model->$function($_export_data,$params);
               // var_dump($_export_data);exit;
            }
            if(!empty($template_call_api)){
                $template_call_api_exp=explode('.',$template_call_api);
                $template_call_api_model=M($template_call_api);
                $function=$template_call_api_exp[4];
                $data_str=$template_call_api_model->$function($_export_data,$params);
            }else{
//                var_dump($_export_data);exit;
                $data_str='';
                foreach($_export_data as $key=>$val){
                    $data_str  .=  '<tr>';
                    foreach ($val as $k=>$v){
                        if(is_numeric($v) && strlen($v) <= 10){
                            $data_str .= "<td x:num>{$v}</td>";
                        }else {
                            $data_str .= "<td>{$v}</td>";
                        }
                        
                    }
                    $data_str   .= "</tr>\n";
                }
            }
            $new_str = mb_convert_encoding($data_str, "GBK", "UTF-8");
            fwrite($fp, $new_str);
        }
        $this->setFileFooter($fp);
        $aliyun_path = upload_cloud($filename);
       // var_dump($aliyun_path);exit;
        return $this->res($aliyun_path);
    }
    
    
    /**
     *
     * 队列异步数据导出
     * Com.Common.CommonView.Export.asyncExport
     * @param type $params
     * @return type
     */
    public function asyncExport($params){
        $params = $params['message'];
        set_time_limit(0);
        $this->_rule = array( 
            array('sql_flag', 'require', PARAMS_ERROR, MUST_CHECK),
            array('center_flag',array('Ic','Sc','Tc','Uc','Oc','Spc','Fc','Bic'),PARAMS_ERROR, MUST_CHECK,'in'), #中心标示  SQL_IC   SQL_SC  SQL_TC  SQL_UC  SQL_OC SQL_SPC
            array('other', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('total_num', C('EXPORT_LIMIT_NUMS'), PARAMS_ERROR, MUST_CHECK,'gt'),  //大于 1000 条 
            array('title', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),  //标题
            array('fields', 'require', PARAMS_ERROR, MUST_CHECK),  //字段
            array('filename', 'require', PARAMS_ERROR, MUST_CHECK),  //文件名
            array('callback_api', 'require', PARAMS_ERROR, HAVEING_CHECK),  //回调处理数据的api
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),  //用户编码
            array('downlist_id', 'require', PARAMS_ERROR, MUST_CHECK),  //下载列表
            array('template_call_api','require',PARAMS_ERROR,ISSET_CHECK),
            array('db_flag', 'require', PARAMS_ERROR, ISSET_CHECK), # 20
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //每次查询数量
        $page_number = C('SINGLE_EXPORT_NUMS');
        $page        = 1;//起始页数
        $total_num   = $params['total_num'];//总数量
        $sql_flag    = $params['sql_flag'];  //sql的标示  在数组里面提取sql出来
        $center_flag = $params['center_flag']; //中心标示
        $fields      = $params['fields'];  //导出字段
        $order       = $params['order']; 
        $title       = $params['title']; //导出数据标题
        $filename    = $params['filename'];//文件名称
        $where       = empty($params['where']) ? ' 1=1  ' : D()->parseWhereCondition($params['where']);
        //去掉最前面的where
        $where       = ltrim($where);
        $where       =  ' '.ltrim($where,'WHERE');
        $group       = $params['group'];
        $having      = $params['having'];
        $other       = $params['other'];
        $callback_api= $params['callback_api'];
        $template_call_api=$params['template_call_api'];
        $uc_code     = $params['uc_code'];
        $db_flag     = $params['db_flag'];
        if($db_flag){
            $model=D('',C('DB_PREFIX'),C('DB_BIC'));
        }else{
            $model=D();
        }
        
        //查询数据库中是否有该条数据
        $downlist = D('Downlist')->where(array('id'=>$params['downlist_id']))->field('id')->master()->find();
        if(empty($downlist)){
            return $this->res(NULL,6501);
        }
        if(!($filename = $this->setDir($filename))){  //生成文件夹  获取文件名
            return $this->res(NULL,19);
        }
        $basic_name = $filename['basic_name'];
        $filename   = $filename['absolute_name'];
        $fp = $this->setFileHead($filename, $title);  //生成文件
        
        $apiPath     = "Com.Common.CommonView.{$center_flag}Sql.sqls";
        $centerSqlObj= M($apiPath);
        $sql         = $centerSqlObj->sqls($fields,$where,$order,$group,$having,$sql_flag,$other);  //数据sql语句
        $_export_data = array(); //导出数据
        if(!empty($callback_api)){
                //如果设置回调处理数据的api  则  调用api
                $callback_api_exp   = explode('.', $callback_api);
                $callback_api_model = M($callback_api);
                $function = $callback_api_exp[4];  //调用函数
         }
        //查询数据
        for($i = 0;$i < $total_num; $i = $i + $page_number){
            $data_str = '';
            $start      = ($page - 1) * $page_number;
            $sql_select = $sql ." LIMIT {$start},{$page_number}";
            $_export_data       = $model->query($sql_select);
            if(!empty($callback_api)){
                $callback_api_model->$function($_export_data,$params);
            }
            if(!empty($template_call_api)){
                $template_call_api_exp=explode('.',$template_call_api);
                $template_call_api_model=M($template_call_api);
                $function=$template_call_api_exp[4];
                $data_str=$template_call_api_model->$function($_export_data,$params);
            }else{
                foreach($_export_data as $key=>$val){
                    $data_str  .=  '<tr>';
                    foreach ($val as $k=>$v){
                        $data_str .= "<td>{$v}</td>";
                    }
                    $data_str   .= "</tr>\n";
                }
            }
            $new_str = mb_convert_encoding($data_str, "GB2312", "UTF-8");
            fwrite($fp, $new_str);
            unset($new_str);
            unset($_export_data);
            unset($data_str);
            $page ++;
        }
        $this->setFileFooter($fp);
        
        //上传到阿里云
        $aliyun_path = upload_cloud($filename);
        
        //写到数据库
        $data = array(
            'url' =>'http://'.$aliyun_path,
            'update_time' => NOW_TIME,
            'status' => 'ENABLE',
            'filename'=>$basic_name,
        );
        $update_res = D('Downlist')->where(array('id'=>$params['downlist_id']))->save($data);
        if(FALSE === $update_res || $update_res <= 0){
            return $this->res(FALSE,6504);
        }
        return $this->res($aliyun_path);
    }
    
    /**
     * 生成文件
     */
    private function setDir($filename){
        $rand_num = mt_rand(1000, 9999);
        //生成上传文件文件夹
        $filename = $filename .date("YmdHis",NOW_TIME).$rand_num.'.xls';
        $basic_name = $filename;
        $dir_name = UPLOAD_PATH.date('Ymd',NOW_TIME);
        $filename = $dir_name.'/'.$filename;
        
        if(file_exists($filename)){  //判断该文件是否存在
            return FALSE;
        }
        mkdir($dir_name,0777,TRUE);
        return array('basic_name'=>$basic_name,'absolute_name'=>$filename);
    }

    /**
     * 生成文件名  并  将头写入
     * @param type $filename
     * @param type $title
     */
    private  function setFileHead($filename,$title){
        $fp = fopen($filename, 'a+');
        $header = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\nxmlns=\"http://www.w3.org/TR/REC-html40\">\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html>\n<head>\n<meta http-equiv=\"Content-type\" content=\"text/html;charset=GBK\" />\n<style>\ntd{padding:4px;mso-ignore:padding;color:windowtext;font-size:10.0pt;font-weight:400;font-style:normal;text-decoration:none;font-family:Arial;mso-generic-font-family:auto;mso-font-charset:134;mso-number-format:General;text-align:general;vertical-align:middle;border:.5pt solid windowtext;mso-background-source:auto;mso-pattern:auto;mso-protection:locked visible;white-space:nowrap;mso-rotate:0;}\n</style>\n</head><body>\n<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style=\"border-collapse: collapse\">";
        $str  = $header;
        $str .= "<tr>";
        foreach($title as $k=>$val){
            $str .= "<td>{$val}</td>";
        }
        $str .= "</tr>\n";
        $str = iconv('utf-8', 'gb2312', $str);  //转码
        fwrite($fp, $str);
        return $fp;
    }
    
    
    /**
     * 设置文件的尾部
     * @param type $fp
     */
    private function setFileFooter($fp){
        $footer = "</table>\n</body></html>";
        fwrite($fp,$footer);
        fclose($fp);
    }
    
    /**
     * 异步推送队列
     */
    private function async($params){
        //插入下载记录到数据库
        $data = array(
            'uc_code'  => $params['uc_code'],
            'url'      => '',
            'params'   => json_encode($params),
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'     =>'DISABLE',
        );
        // $db_flag = $params['db_flag'];
        // if($db_flag){
        //     $model=D('',C('DB_PREFIX'),C('DB_BIC'));
        // }else{
        //     $model=D();
        // }
        $down_id = D('Downlist')->add($data);
        if($down_id <=0 || $down_id === FALSE){
            return $this->res(NULL,6502);
        }
        $params['downlist_id'] = $down_id;
        $res = $this->push_queue("Com.Common.CommonView.Export.asyncExport",$params);
        if(FALSE === $res){
            return $this->endInvoke(NULL,6503);
        }
        return TRUE;
    }
    
    
    
    

}

?>
