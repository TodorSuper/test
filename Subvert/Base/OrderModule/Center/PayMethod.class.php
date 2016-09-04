<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单
 */

namespace Base\OrderModule\Center;

use System\Base;

class PayMethod extends Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Base.OrderModule.Center.PayMethod.add
     * @param [type] $params [description]
     */
    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('pay_method', 'require', PARAMS_ERROR, MUST_CHECK), //支付方式常量
            array('pay_name', 'require', PARAMS_ERROR, MUST_CHECK), //支付方式名称
            array('pay_status', 'require', PARAMS_ERROR, MUST_CHECK), //开关状态
//            array('pay_order', 'require', PARAMS_ERROR, MUST_CHECK), //排序id
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        
        $pay_name   = $params['pay_name'];
        $pay_method = $params['pay_method'];
        $pay_status = $params['pay_status'];
//        $pay_order  = $params['pay_order'];

        //获取所有的支付方式
        $statusModel =  M('Base.OrderModule.B2b.Status.groupStatusList');
        $pay_types =  $statusModel->getPayMethod();
        $pay_types  =  array_keys($pay_types);
        
        
        if(!in_array($pay_method, $pay_types)){
            return $this->res(null,3,'','支付方式输入有误，请联系开发人员');
        }
        $sData = array(
          'pay_name'   => $pay_name,
          'pay_method' => $pay_method,
        );

        $sRes = D('OcPayMethod')->where($sData)->find();
        if ($res === false) {
          return $this->res(null, 1,'','添加失败!');
        }
        
        //查找当前最大排序值
        $max_order = D('OcPayMethod')->max('pay_order');
        $max_order = $max_order + 1;

        $idata = array(
            'pay_name'    => $pay_name,
            'pay_method'  => $pay_method,
            'pay_status'  => $pay_status,
            'pay_order'   => $max_order  ,
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME,
            'status'      => 'ENABLE',
        	);

        $iRes = D('OcPayMethod')->add($idata);
        if (!$iRes) {
        	return $this->res(null, 2,'','添加失败');
        }
        return $this->res(true);
    }

    /**
     * Base.OrderModule.Center.PayMethod.update
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function update($params){
        $this->_rule = array(
            array('id', 'require', PARAMS_ERROR, MUST_CHECK), //支付方式常量
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式常量
            array('pay_name', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式名称
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK), //开关状态
//            array('pay_order', 'require', PARAMS_ERROR, ISSET_CHECK), //排序id
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $id         = $params['id'];
        $pay_name   = $params['pay_name'];
        $pay_method = $params['pay_method'];
        $pay_status = $params['pay_status'];
//        $pay_order  = $params['pay_order'];
        
        //获取所有的支付方式
        $statusModel =  M('Base.OrderModule.B2b.Status.groupStatusList');
        $pay_types =  $statusModel->getPayMethod();
        $pay_types  =  array_keys($pay_types);
        
        
        if(isset($params['pay_method']) && !in_array($pay_method, $pay_types)){
            return $this->res(null,3,'','支付方式输入有误，请联系开发人员');
        }

        $uData = $where = array();
        if(!empty($pay_name)) $uData['pay_name']     = $pay_name;
        if(!empty($pay_method)) $uData['pay_method'] = $pay_method;
        if(!empty($pay_status)) $uData['pay_status'] = $pay_status;
//        if(!empty($pay_order)) $uData['pay_order']   = $pay_order;
        $where['id'] = $id;
        $uRes = D('OcPayMethod')->where($where)->save($uData);
        if ($uRes === false) {
          return $this->res(null, 1);
        }

        return $this->res(true);
    }

    /**
     * Base.OrderModule.Center.PayMethod.lists
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function lists($params){
      $this->_rule = array(
          array('id', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式常量
          array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式常量
          array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK), //开关状态
      );

      if (!$this->checkInput($this->_rule, $params)) { # 自动校验
          return $this->res($this->getErrorField(), $this->getCheckError());
      }

      $id         = $params['id'];
      $pay_method = $params['pay_method'];
      $pay_status = $params['pay_status'];

      $where = array();
      if(!empty($id)) $where['id']     = $id;
      if(!empty($pay_method)) $where['pay_method'] = $pay_method;
      if(!empty($pay_status)) $where['pay_status'] = $pay_status;
      $sRes = D('OcPayMethod')->where($where)->order('pay_order asc')->select();
      if ($sRes === false) {
        return $this->res(null, 1);
      }
      return $this->res($sRes);
    }

    /**
     * Base.OrderModule.Center.PayMethod.payMethods
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function payMethods($params){

      $sRes = D('OcPayMethod')->field('id,pay_method,pay_name')->select();

      if ($sRes === false) {
        return $this->res(null, 1);
      }
      return $this->res($sRes);
    }
    /**
     * Base.OrderModule.Center.PayMethod.getPayMethodInfo
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getPayMethodInfo($params){
       $this->_rule = array(
           array('id', 'require', PARAMS_ERROR, MUST_CHECK), //支付方式常量
           array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK), //支付方式常量
       );
       if (!$this->checkInput($this->_rule, $params)) { # 自动校验
           return $this->res($this->getErrorField(), $this->getCheckError());
       }

       $id         = $params['id'];
       $pay_method = $params['pay_method'];

       $where                                       = array();
       if(!empty($id)) $where['id']                 = $params['id'];
       if(!empty($pay_method)) $where['pay_method'] = $params['pay_method'];

       $sRes = D('OcPayMethod')->where($where)->find();
       if ($sRes === false) {
         return $this->res(null, 1);
       }
       return $this->res($sRes);
    }
    
    
    
    /**
     * Base.OrderModule.Center.PayMethod.savePayMethodOrders
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    
    public function savePayMethodOrders($params){
        $this->startOutsideTrans();
         $this->_rule = array(
           array('orders', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK), //支付方式常量
       );
       if (!$this->checkInput($this->_rule, $params)) { # 自动校验
           return $this->res($this->getErrorField(), $this->getCheckError());
       }
       $orders = $params['orders'];
       foreach($orders as $k=>$v){
           $res = D('OcPayMethod')->where(array('id'=>$k))->save(array('pay_order'=>$v));
           if($res === FALSE){
               return $this->res(NULL,4516);
           }
       }
       return $this->res(TRUE);
    }

}