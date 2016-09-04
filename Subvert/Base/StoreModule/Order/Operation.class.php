<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 */

namespace Base\StoreModule\Order;
use System\Base;
class Operation extends Base {

    public function __construct() {
        parent::__construct();
       
    }
    /**
     * Base.StoreModule.Order.Operation.getLastQueryTime
     */
    public function getLastQueryTime($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),                      # 订单编码
        );
        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = $this->is_set($params);

        $api = 'Base.OrderModule.B2b.OrderInfo.isShiped';
        $param = array(
            'where' => array('create_time'=>array('egt',$data['time']),'sc_code'=>$params['sc_code'],'pay_type'=>array('in',array('COD','TERM'))),
            'field' => 'count(id) as num'

        );
        $cod_count = $this->invoke($api,$param);
        
        $api = 'Base.OrderModule.B2b.OrderInfo.isShiped';
        $order_status = M('Base.OrderModule.B2b.Status.groupToDetail')->groupToDetail(OC_ORDER_GROUP_STATUS_UNSHIP,PAY_TYPE_ONLINE);

        $param = array(
            'where' => array('pay_time'=>array('egt',$data['time']),'sc_code'=>$params['sc_code'],'pay_type'=>'ONLINE'),
            'field' => 'count(id) as num'

        );
        $param['where'] = !empty($order_status)?array_merge($param['where'],$order_status):$param['where'];
        
        $not_cod_count = $this->invoke($api,$param);

        $count['num'] = $cod_count['response']['num']+$not_cod_count['response']['num'];
        return $this->res($count);
    }
    /**
     * Base.StoreModule.Order.Operation.loginSaveData
    */
    public function loginSaveData($params) {
    	D('scLastQueryTime')->where(array('sc_code'=>$params['sc_code'],'sys_name'=>$this->_request_sys_name))->save(array('time'=>time()));
    }
    public function is_set($params) {
        $data = D('scLastQueryTime')->field('time')->where(array('sc_code'=>$params['sc_code'],'sys_name'=>$this->_request_sys_name))->find();
        return $data;
    }
    /**
     * Base.StoreModule.Order.Operation.addLastTime
    */
    public function addLastTime($params) {
        $data = $this->is_set($params);
        if (empty($data)) {
            D('scLastQueryTime')->add($params);
        } else {
            $this->loginSaveData($params);
        }
    	
    }


    /**
     * B2B个人中心添加
     * Base.StoreModule.Order.Operation.add
     * @author Todor
     * @access public
     */

    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('type','require',PARAMS_ERROR,MUST_CHECK),          # 类型
            array('sys_name','require',PARAMS_ERROR,MUST_CHECK),      # 平台标识
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $data = array(
            'uc_code'  =>$params['uc_code'],
            'type'     =>$params['type'],
            'sys_name' => $params['sys_name'],
            'time'     => NOW_TIME,
            );
        $res = D('ScLastQueryTime')->add($data);
        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6057);
        }
        return $this->res($res);

    }


    /**
     * B2B个人中心修改
     * Base.StoreModule.Order.Operation.update
     * @author Todor
     * @access public
     */

    public function update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('type','require',PARAMS_ERROR,MUST_CHECK),          # 类型
            array('sys_name','require',PARAMS_ERROR,MUST_CHECK),      # 平台标识
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['uc_code']  = $params['uc_code'];
        $where['sys_name'] = $params['sys_name'];
        $where['type']     = strtoupper($params['type']);
        
        $data['time']      = NOW_TIME;
        $res = D('ScLastQueryTime')->where($where)->save($data);
        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,6058);
        }
        return $this->res($res);
    }


    /**
     * B2B个人中心获取或添加
     * Base.StoreModule.Order.Operation.check
     * @author Todor
     * @access public
     */

    public function check($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),    # 客户编码
            array('type','require',PARAMS_ERROR,MUST_CHECK),          # 类型
            array('sys_name','require',PARAMS_ERROR,MUST_CHECK),      # 平台标识
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
          
        $where['uc_code']  = $params['uc_code'];
        $where['sys_name'] = $params['sys_name'];
        $where['type']     = $params['type'];
        $res = D('ScLastQueryTime')->where($where)->find();

        if(empty($res)){               # 不存在 则添加
            try{
                D()->startTrans();
                $apiPath = "Base.StoreModule.Order.Operation.add";
                $res = $this->invoke($apiPath, $params);
                if($res['status'] != 0){
                    return $this->res(NULL,$res['status'],'',$res['message']);
                }
                $commit_res = D()->commit();
                if($commit_res === FALSE){
                    return $this->endInvoke(NULL,17);
                }
                $res['time'] = NOW_TIME;
            } catch (\Exception $ex) {
                D()->rollback();
                return $this->endInvoke(NULL,6057);
            }
        }
        return $this->res($res['time']);
    }



}    