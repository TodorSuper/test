<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: nielei <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | b2b提示信息
 */

namespace Base\OrderModule\B2b;
use System\Base;
class Prompt extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }


    /**
     * @api  提示信息增加
     * @apiVersion 1.0.1
     * @apiName Base.OrderModule.B2b.Prompt.add
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-4
     * @apiParam {String} [sc_code] 我是商铺编码,我规定参数必填且类型为String类型
     */

    public function add($params){
        // $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('sc_code','require',PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('unpay','require',PARAMS_ERROR,ISSET_CHECK),        # 待付款
            array('term_unpay','require',PARAMS_ERROR,ISSET_CHECK),   # 待付账期
            array('unship','require',PARAMS_ERROR,ISSET_CHECK),       # 待发货
            array('ship','require',PARAMS_ERROR,ISSET_CHECK),         # 已发货
            array('complete','require',PARAMS_ERROR,ISSET_CHECK),     # 已完成
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $data = array(
            'uc_code'=>$params['uc_code'],
            'sc_code'=>$params['sc_code'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            );
        !empty($params['unpay']) && $data['unpay']           = $params['unpay'];
        !empty($params['term_unpay']) && $data['term_unpay'] = $params['term_unpay'];
        !empty($params['unship']) && $data['unship']         = $params['unship'];
        !empty($params['ship']) && $data['ship']             = $params['ship'];
        !empty($params['complete']) && $data['complete']     = $params['complete'];

        $res = D('OcB2bPrompt')->add($data);
        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6053);
        }
        return $this->res($res);
    }


    /**
     * @api  提示信息更新
     * @apiVersion 1.0.1
     * @apiName Base.OrderModule.B2b.Prompt.update
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-4
     * @apiParam {String} [sc_code] 我是商铺编码,我规定参数必填且类型为String类型
     * @apiParam {Array}  [unpay]  为待付款数量，0表示清0，array('+',1)表示增加，array('-',1)表示减少
     */

    public function update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('sc_code','require',PARAMS_ERROR, MUST_CHECK),      # 店铺编码
            array('unpay','require',PARAMS_ERROR,ISSET_CHECK),        # 待付款
            array('term_unpay','require',PARAMS_ERROR,ISSET_CHECK),   # 待付账期
            array('unship','require',PARAMS_ERROR,ISSET_CHECK),       # 待发货
            array('ship','require',PARAMS_ERROR,ISSET_CHECK),         # 已发货
            array('complete','require',PARAMS_ERROR,ISSET_CHECK),     # 已完成
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $where['uc_code'] = $params['uc_code'];
        $where['sc_code'] = $params['sc_code'];

        $data['update_time'] = NOW_TIME;

        isset($params['unpay']) && $data['unpay']           = $params['unpay'];
        isset($params['term_unpay']) && $data['term_unpay'] = $params['term_unpay'];
        isset($params['unship']) && $data['unship']         = $params['unship'];
        isset($params['ship']) && $data['ship']             = $params['ship'];
        isset($params['complete']) && $data['complete']     = $params['complete'];

        $res = D('OcB2bPrompt')->where($where)->save($data);
        if($res === FALSE){
            return $this->res(NULL,6054);
        }
        return $this->res($res);
    }


    /**
     * @api  提示信息获取
     * @apiVersion 1.0.1
     * @apiName Base.OrderModule.B2b.Prompt.get
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-4
     * @apiParam {String} [sc_code] 我是商铺编码,我规定参数必填且类型为String类型
     */

    public function get($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('sc_code','require',PARAMS_ERROR, MUST_CHECK),      # 店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['sc_code'] = $params['sc_code'];
        $where['uc_code'] = $params['uc_code'];

        $res = D('OcB2bPrompt')->where($where)->find();

        if($res == FALSE){
            return $this->res(NULL,6055);
        }
        return $this->res($res);
    }




    /**
     * @api  根据情况添加或更新
     * @apiVersion 1.0.1
     * @apiName Base.OrderModule.B2b.Prompt.check
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-11-6
     * @apiParam {String} [sc_code] 我是商铺编码,我规定参数必填且类型为String类型
     */


    public function check($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('sc_code','require',PARAMS_ERROR, MUST_CHECK),      # 店铺编码
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['sc_code'] = $params['sc_code'];
        $where['uc_code'] = $params['uc_code'];

        $res = D('OcB2bPrompt')->where($where)->find();
        if(empty($res)){
            $data = array(
            'uc_code'=>$params['uc_code'],
            'sc_code'=>$params['sc_code'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            );
            !empty($params['unpay']) && $data['unpay']           = $params['unpay'][1];
            !empty($params['term_unpay']) && $data['term_unpay'] = $params['term_unpay'][1];
            !empty($params['unship']) && $data['unship']         = $params['unship'][1];
            !empty($params['ship']) && $data['ship']             = $params['ship'][1];
            !empty($params['complete']) && $data['complete']     = $params['complete'][1];
            $res = D('OcB2bPrompt')->add($data);
            if($res <= 0 || $res === FALSE){
                return $this->res(NULL,6053);
            }
        }else{
            $where['uc_code'] = $params['uc_code'];
            $where['sc_code'] = $params['sc_code'];

            $data['update_time'] = NOW_TIME;

            isset($params['unpay']) && $data['unpay']           = $params['unpay'];
            isset($params['term_unpay']) && $data['term_unpay'] = $params['term_unpay'];
            isset($params['unship']) && $data['unship']         = $params['unship'];
            isset($params['ship']) && $data['ship']             = $params['ship'];
            isset($params['complete']) && $data['complete']     = $params['complete'];

            $res = D('OcB2bPrompt')->where($where)->save($data);
            if($res === FALSE){
                return $this->res(NULL,6054);
            }
        }

        return $this->res($res);

    }











}

?>
