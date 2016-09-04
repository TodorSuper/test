<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 购物车相关模块
 */

namespace Test\Base\UserCart;

use System\Base;

class Cart extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加到购物车
     * Test.Base.UserCart.Cart.add
     * @return integer   成功时返回  自增id
     */
    public function add($params) {
        try{
            D()->startTrans();
            $apiPath = "Base.UserModule.Cart.Cart.add";
            $data = array(
                'uc_code'=>'1120000000104',
                'number' => 10,
                'sic_code' =>'12300000024',
            );
            $res = $this->invoke($apiPath, $data);
            D()->commit();
            return $this->res($res['response'],$res['status'],'',$res['message']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res();
        }
    }
    
    
    /**
     * 删除购物车列表
     * Base.UserModule.Cart.Cart.delete
     * @return integer   成功时返回  自增id
     */
    public function delete($params){
        $this->startOutsideTrans();  //必须开始事务
        //验证参数
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('cart_id', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //购物车id  数组
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $uc_code = $params['uc_code'];
        $cart_id = $params['cart_id'];
        
        $delete_res = D('UcCart')->where(array('uc_code'=>$uc_code,'cart_id'=>array('in',$cart_id),'status'=>'ENABLE'))
                                 ->save(array('update_time'=>NOW_TIME,'status'=>'DISABLE'));
        if($delete_res <= 0 || $delete_res === FALSE){
            return $this->res(null,4021);
        }
        
        return $this->res($delete_res);
    }
    
    
    /**
     * 修改购物车商品数量
     * Base.UserModule.Cart.Cart.changeNum
     * @return integer   成功时返回  自增id
     */
    public function changeNum($params){
        $this->startOutsideTrans();  //必须开始事务
        //验证参数
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('cart_id', 'require', PARAMS_ERROR, MUST_CHECK), //购物车id
            array('number', 'require', PARAMS_ERROR, MUST_CHECK), //购买数量
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $uc_code = $params['uc_code'];
        $cart_id = $params['cart_id'];
        $number = $params['number'];
        
        $change_res = D('UcCart')->where(array('uc_code'=>$uc_code,'id'=>$cart_id))
                                 ->save(array('update_time'=>NOW_TIME,'number'=>$number));
        if($change_res <= 0 || FALSE === $change_res){
            return $this->res(null,4022);
        }
        return $this->res($change_res);
        
    }
    
    

    /**
     * 购物车列表
     * Base.UserModule.Cart.Cart.lists
     * @return integer   成功时返回  自增id
     */
    public function lists($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('cart_ids', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK,'function'), //购物车id
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $uc_code  = $params['uc_code'];
        $cart_ids = $params['cart_ids'];
        
        $where = array(
            'uc.uc_code'=>$uc_code,
            'uc.status'=>'ENABLE',
            'ii.status'=>IC_ITEM_PUBLISH,
            'isi.status'=>IC_STORE_ITEM_ON,
        );
        if(!empty($cart_ids)){
            $where['uc.id'] = array('in',$cart_ids);
        }
        $fields = " ii.ic_code,ii.goods_name,ii.brand,ii.spec,ii.packing,ii.category_end_id,ii.goods_img,isi.sc_code,isi.sic_code,isi.price,isi.sub_name as store_sub_name,isi.min_num,isi.stock,uc.number ";
        $params['where'] = empty($params['where']) ? $where : array_merge($params['where'],$where); //where 条件
        $params['order'] = empty($params['order']) ? ' uc.id desc ' : $params['order'];
        $params['fields'] = empty($params['fields']) ? $fields : $params['fields'];
        $params['page'] = empty($params['page']) ? 1 : $params['page'];
        $params['page_number'] = empty($params['page_number']) ? 100 : $params['page_number']; //默认显示  100 条  基本上是不分页了   以后调整购物车最大能放多少条商品
        
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $params['center_flag'] = SQL_UC;
        $params['sql_flag'] = 'cart_list';
        $res = $this->invoke($apiPath, $params);
        if(FALSE === $res){
            return $this->res(null,8);
        }
        return $this->res($res['response']);
    }

}

?>
