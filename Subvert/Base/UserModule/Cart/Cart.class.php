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

namespace Base\UserModule\Cart;

use System\Base;

class Cart extends Base {

    private $_rule = null; # 验证规则列表
    private $_max_num = 999; #购物车最大商品数量
    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加到购物车
     * Base.UserModule.Cart.Cart.add
     * @return integer   成功时返回  自增id
     */
    public function add($params) {
        $this->startOutsideTrans();  //必须开始事务
        //验证参数
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('number', 'require', PARAMS_ERROR, MUST_CHECK), //购买数量
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $sic_code = $params['sic_code'];
        $number = $params['number'];
        $sc_code = $params['sc_code'];


        //查询该商品是否上架
        $where = array(
            'isi.sic_code' => $sic_code,
            'ii.status' => IC_ITEM_PUBLISH,
            'isi.status' => IC_STORE_ITEM_ON,
        );
        $item_info = D('IcItem')->alias('ii')
                ->field('ii.id')
                ->join("{$this->tablePrefix}ic_store_item isi on ii.ic_code = isi.ic_code", 'LEFT')
                ->where($where)
                ->find();
        if (empty($item_info)) {
            return $this->res(NULL, 4030);
        }
        //查询该记录是否存在
        $where = array(
            'uc_code' => $uc_code,
            'sic_code' => $sic_code,
            'status' => 'ENABLE',
        );
        $cart_exists = D('UcCart')->where($where)->find();
        if($number > $this->_max_num){
            return $this->res(NULL,4038);
        }
        if (!empty($cart_exists)) {
            //如果新添的购物车  和  原来的购物车数量 加起来大于  999  则  不允许加入
            if($cart_exists['number'] + $number > $this->_max_num){
                return $this->res(NULL,4038);
            }
            //该商品已存在购物车  则添加相应的数量
            $res = D('UcCart')->where($where)->save(array('update_time' => NOW_TIME, 'number' => array('+', $number)));
        } else {
            //不存在  则添加购物车商品
            $data = array(
                'sc_code' => $sc_code,
                'uc_code' => $uc_code,
                'number' => $number,
                'sic_code' => $sic_code,
                'create_time' => NOW_TIME,
                'update_time' => NOW_TIME,
                'status' => 'ENABLE',
            );
            $res = D('UcCart')->add($data);
        }

        if ($res <= 0 || $res === FALSE) {
            return $this->res(NULL, 4020);
        }

        return $this->res($res);
    }

    /**
     * 删除购物车列表
     * Base.UserModule.Cart.Cart.delete
     * @return integer   成功时返回  自增id
     */
    public function delete($params) {
        $this->startOutsideTrans();  //必须开始事务
        //验证参数
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'), //商家商品编码  数组
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $sic_codes = $params['sic_codes'];
        $where = array(
            'uc_code' => $uc_code,
            'status' => 'ENABLE',
        );
        !empty($sic_codes[0])  && $where['sic_code'] = array('in', $sic_codes);
        $delete_res = D('UcCart')->where($where)
                ->save(array('update_time' => NOW_TIME, 'status' => 'DISABLE'));
        if ($delete_res === FALSE) {
            return $this->res(null, 4021);
        }

        return $this->res($delete_res);
    }

    /**
     * 修改购物车商品数量
     * Base.UserModule.Cart.Cart.changeNum
     * @return integer   成功时返回  自增id
     */
    public function changeNum($params) {
        $this->startOutsideTrans();  //必须开始事务
        //验证参数
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //购物车id
            array('number', 'require', PARAMS_ERROR, MUST_CHECK), //购买数量
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $sic_code = $params['sic_code'];
        $number = $params['number'];

        $change_res = D('UcCart')->where(array('uc_code' => $uc_code, 'sic_code' => $sic_code, 'status' => 'ENABLE'))
                ->save(array('update_time' => NOW_TIME, 'number' => $number));
        if (FALSE === $change_res) {
            return $this->res(NULL, 4022);
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
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function'), //购物车id
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK),   # sql标识
            array('where', 'require', PARAMS_ERROR, ISSET_CHECK),      # where条件
            array('fields', 'require', PARAMS_ERROR, ISSET_CHECK),     # fields条件
            array('order', 'require', PARAMS_ERROR, ISSET_CHECK),      # order条件
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $sic_codes = $params['sic_codes'];
        $sc_code = $params['sc_code'];
        $sql_flag = $params['sql_flag'];

        $where = array(
            'uc.uc_code' => $uc_code,
            'uc.status' => 'ENABLE',
            'ii.status' => IC_ITEM_PUBLISH,
            'isi.status' => IC_STORE_ITEM_ON,
        );
        !empty($sic_codes) && $where['uc.sic_code'] = array('in', $sic_codes);
        !empty($sc_code) && $where['uc.sc_code'] = $sc_code;

        $fields = " ii.ic_code,ii.goods_name,ii.brand,ii.spec,ii.packing,ii.bar_code,ii.category_end_id,ii.class_id,ii.goods_img,isi.sc_code,isi.sic_no,isi.sic_code,isi.price,isi.sub_name as store_sub_name,isi.min_num,isi.stock,uc.number ";
        $params['where'] = empty($params['where']) ? $where : array_merge($params['where'], $where); //where 条件
        $params['order'] = empty($params['order']) ? ' isi.sort desc ' : $params['order'];
        $params['fields'] = empty($params['fields']) ? $fields : $params['fields'];
        $params['page'] = empty($params['page']) ? 1 : $params['page'];
        $params['page_number'] = empty($params['page_number']) ? 100 : $params['page_number']; //默认显示  100 条  基本上是不分页了   以后调整购物车最大能放多少条商品

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $params['center_flag'] = SQL_UC;
        $params['sql_flag'] = empty($sql_flag) ? 'cart_list' : $sql_flag;
        $res = $this->invoke($apiPath, $params);
        if ($res['status'] != 0) {
            return $this->res(null, $res['status']);
        }
        return $this->res($res['response']);
    }

    /**
     * 查询购物车总商品种类数，总商品数
     * Base.UserModule.Cart.Cart.getCartNum
     * @return integer   成功时返回  自增id
     */
    public function getCartNum($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('is_show', 'require', PARAMS_ERROR, ISSET_CHECK), //商家是否展示
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = array();
        $sc_code = $params['sc_code'];
        $uc_code = $params['uc_code'];

        if($params['is_show'] == "YES"){
            !empty($sc_code) && $where['uc.sc_code'] = $sc_code;
            !empty($uc_code) && $where['uc.uc_code'] = $uc_code;
            $where['uc.status'] = "ENABLE";
            $where['ss.is_show'] = "YES";
            $where['isi.status'] = "ON";
            $data = $cart_info = D('UcCart')->alias('uc')->field("count(*) as total_count ,sum(number) as total_sum")
                                            ->join("{$this->tablePrefix}sc_store ss ON uc.sc_code = ss.sc_code",'LEFT')
                                            ->join("{$this->tablePrefix}ic_store_item isi ON isi.sic_code = uc.sic_code",'LEFT')
                                            ->where($where)
                                            ->find();
        }else{
            !empty($sc_code) && $where['sc_code'] = $sc_code;
            !empty($uc_code) && $where['uc_code'] = $uc_code;
            $where['status'] = "ENABLE";
            $data = $cart_info = D('UcCart')->field("count(*) as total_count ,sum(number) as total_sum")->where($where)->find();
        }
        if (empty($cart_info['total_count'])) {
            $data = array(
                'total_count' => 0,
                'total_sum' => 0
            );
        }
        return $this->res($data);
    }

    /**
     * 
     * 批量加入购物车
     * Base.UserModule.Cart.Cart.batAdd
     * @param type $params
     */
    public function batAdd($params) {
        $this->startOutsideTrans();  //必须开始事务
        //验证参数
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), //商品ids
            array('numbers', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'), //购买数量
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = array();
        $uc_code  = $params['uc_code'];
        $sic_codes = $params['sic_codes'];
        $numbers = $params['numbers'];
        $sc_code = $params['sc_code'];
        
        $num = count($sic_codes);
        for($i = 0; $i< $num;$i++){
            $temp = array(
                'uc_code' => $uc_code,
                'sic_code'=> $sic_codes[$i],
                'number'  => $numbers[$i],
                'sc_code'=> $sc_code,
                'create_time' => NOW_TIME,
                'update_time' => NOW_TIME,
            );
            $data[] = $temp;
        }
        $res = D('UcCart')->addAll($data);
        if($res <=0 || $res === FALSE){
            return $this->res(NULL,4036);
        }
        return $this->res($res);
    }

}

?>
