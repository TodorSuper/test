<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | EXECL导入类
 */
namespace Library;

class Execl{

    public function __construct() {
        define('SCRIPT_ROOT',  dirname(__FILE__).'/phpexecl/');
        $dir_path = SCRIPT_ROOT.'Classes/PHPExcel/IOFactory.php';
        require $dir_path;
    }
    /**
    * 获取EXECL数据
    * @param string $file EXECL文件地址
    * @return array $sheetData 处理后的数据
    */
    public function import($file) {
       
        $objPhpExcel = \PHPExcel_IOFactory::load($file);
        $sheetData = $objPhpExcel->getActiveSheet()->toArray();
        unset($sheetData[0]);
        $sheetData = $this->_dataFilter($sheetData);
        $sheetData = $this->_trun_data($sheetData);
        return $sheetData;
    }
    /**
    * 合并多表格EXECL数据
    * @param array $data execl数据
    * @return array $data 处理后的数据
    */
    private function _trun_data($data) {
        $keys = array();
        $temp = array();

        foreach ( $data as $key => $value ) {

            if ($value[0] == '') {
                $temp['rule'][] = array($value[9],$value[10]);
            }
            if ($value[0] != '') {
                $temp = $value;
                $temp['rule'][] = array($value[9],$value[10]);
            }
            if (!isset($data[$key+1])) {
                $keys[] = $temp;
            } 
            if ( $data[$key+1][0]===NULL){
            
            }else {
                $keys[] = $temp;
            }

        }

        return $keys;    
    }
    /**
    * 过滤EXECL非法数据
    * @param array $data execl数据
    * @return array $data 处理后的数据
    */
    private function _dataFilter($data) {
        array_walk_recursive($data, function (&$key,&$value) {
            $key = addslashes(strip_tags($key));
        });
        return $data;

    }
}