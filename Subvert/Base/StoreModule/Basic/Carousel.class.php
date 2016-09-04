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

namespace Base\StoreModule\Basic;

use System\Base;

defined( 'CAROUSEL_ENABLE' )      or  define('CAROUSEL_ENABLE', 'ENABLE');
defined( 'CAROUSEL_DISABLE' )      or  define('CAROUSEL_DISABLE', 'DISABLE');
class Carousel extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = 'Sc';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加轮播图
     * Base.StoreModule.Basic.Carousel.add
     * @param type $params
     */
    public function add($params) {
        $this->startOutsideTrans();
        $params['orders']  =  $params['orders'] + 0;
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 店铺编码
            array('name', '3,60', PARAMS_ERROR, MUST_CHECK,'length'), # 轮播图名称
            array('orders', 0, PARAMS_ERROR, MUST_CHECK,'egt'), # 轮播排列顺序
            array('img', 'require', PARAMS_ERROR, MUST_CHECK), # 轮播图片
            array('link', 'require', PARAMS_ERROR, MUST_CHECK), # 轮播链接
            array('is_show', array('YES','NO'), PARAMS_ERROR, MUST_CHECK,'in'), # 是否显示
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $data = array(
            'sc_code'    =>  $params['sc_code'],
            'name'       =>  $params['name'],
            'orders'     =>  $params['orders'],
            'img'        =>  $params['img'],
            'link'       =>  $params['link'],
            'is_show'    =>  $params['is_show'],
            'short_desc' => $params['short_desc'],
            'status'     => CAROUSEL_ENABLE,
            'create_time'=> NOW_TIME,
            'update_time'=> NOW_TIME,
        );
        
        $add_res = D('ScCarousel')->add($data);
        if($add_res <= 0 || $add_res === FALSE){
            return $this->res(NULL,5514);
        }
        return $this->res($add_res);
    }
     /**
     * 更新轮播图
     * Base.StoreModule.Basic.Carousel.add
     * @param type $params
     */
    public function update($params) {
        $this->startOutsideTrans();
        $params['orders']  =  $params['orders'] + 0;
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 店铺编码
            array('car_id', 'require', PARAMS_ERROR, MUST_CHECK), # 轮播图id
            array('car_id', 0, PARAMS_ERROR, MUST_CHECK,'gt'), # 轮播图id
            array('name', '3,60', PARAMS_ERROR, HAVEING_CHECK,'length'), # 轮播图名称
            array('orders', 0, PARAMS_ERROR, HAVEING_CHECK,'egt'), # 轮播排列顺序
            array('img', 'require', PARAMS_ERROR, HAVEING_CHECK), # 轮播图片
            array('link', 'require', PARAMS_ERROR, HAVEING_CHECK), # 轮播链接
            array('is_show', array('YES','NO'), PARAMS_ERROR, HAVEING_CHECK,'in'), # 是否显示
            array('status', array(CAROUSEL_ENABLE,CAROUSEL_DISABLE), PARAMS_ERROR, HAVEING_CHECK,'in'), # 是否显示
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code  = $params['sc_code'];
        $car_id   = $params['car_id'];

        //准备更新的数据
        $fields = array('name','orders','img','link','is_show','short_desc','status');
        $data  = $this->create_save_data($fields, $params);
        $where = array(
            'id'       => $car_id,
            'sc_code'  => $sc_code,
        );
        $update_res = D('ScCarousel')->where($where)->save($data);
        if($update_res <= 0 || $update_res === FALSE){
            return $this->res(NULL,5512);
        }
        return $this->res($update_res);
    }
    
    /**
     * 获取轮播图信息
     * Base.StoreModule.Basic.Carousel.get
     * @param type $params
     */
    public function get($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 店铺编码
            array('car_id', 'require', PARAMS_ERROR, MUST_CHECK), # 轮播图id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code  =  $params['sc_code'];
        $car_id   =  $params['car_id'];
        $where    =  array(
            'sc_code' => $sc_code,
            'id'      =>$car_id,
        );
        $car_info =  D('ScCarousel')->where($where)->find();
        if(empty($car_info)){
            return $this->res(NULL,5513);
        }
        return $this->res($car_info);
    }

    /**
     * 获取轮播图列表信息
     * Base.StoreModule.Basic.Carousel.lists
     * @param type $params
     */
    public function lists($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), # 店铺编码
            array('is_show', array('YES','NO'), PARAMS_ERROR, HAVEING_CHECK,'in'), # 店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_code  =  $params['sc_code'];
        $is_show  =  $params['is_show'];
        $status   =  $params['status'];
        
        $order    =  'id desc';
        $where    =  array('sc_code'=>$sc_code,'status'=>CAROUSEL_ENABLE);
        !empty($is_show)  &&  $where['is_show']  = $is_show;
        $params['order']        =  $order;
        $params['where']        =  $where;
        $params['center_flag']  = SQL_SC;//店铺中心   
        $params['sql_flag']     = 'carousel_list';  //sql标示
        $apiPath  =  "Com.Common.CommonView.Lists.Lists";
        $car_list =  $this->invoke($apiPath, $params);
        if($car_list['status'] != 0){
            return $this->res(NULL,$car_list['status']);
        }
        return $this->res($car_list['response']);
    }

}

?>
