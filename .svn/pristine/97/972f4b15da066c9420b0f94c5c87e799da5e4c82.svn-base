<?php


/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Lifeifei <Lifeifei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺用户相关
 */


namespace Base\StoreModule\Basic;

use System\Base;

class Label extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

	public function __construct() {
        parent::__construct();
    }


	public function getPopLabelData($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码
            array('label_id','require',PARAMS_ERROR,ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where['label.status'] = array('eq',SC_LABEL_STATUS_ENABLE);
        $where['lis.status'] = array('eq',SC_LABEL_STATUS_ENABLE);
        !empty($params['label_id']) && $where['label.id'] = array('eq',$params['label_id']);

        $dataOne = D('ScStoreLabel')->alias('label')
                        ->field('label_name,sc_code,label.id,lis.sc_code,label.create_time')
                        ->join("{$this->tablePrefix}sc_store_label_list lis on label.id = lis.label_code", 'LEFT')
                        ->where($where)
                        ->order('label.create_time desc')
                        ->select();
        $data = array();                
        foreach ($dataOne as $k=> $v) {
            if(empty($data[$v['id']])) {
                $data[$v['id']]['id'] = $v['id'];
                $data[$v['id']]['label_name'] = $v['label_name'];
                $data[$v['id']]['sc_code'][] = $v['sc_code'];
                $data[$v['id']]['create_time'] = $v['create_time'];
            } else {
                $data[$v['id']]['id'] = $v['id'];
                $data[$v['id']]['label_name'] = $v['label_name'];
                $data[$v['id']]['sc_code'][] = $v['sc_code'];
            }
        }
        
        foreach ($data as $n => $m) {
            $data[$n]['num'] = count(array_unique($m['sc_code']));
            $data[$n]['sc_code'] = rtrim(implode(',',array_unique($m['sc_code'])),',');

        }       
        
        if (!empty($params['label_id']) ) {
            return $this->res($data);
        }
        unset($where['lis.status']);
        $dataTwo = D('ScStoreLabel')->alias('label')
                        ->field('label_name,count(sc_code) as num,label.id,label.create_time')
                        ->join("{$this->tablePrefix}sc_store_label_list lis on label.id = lis.label_code", 'LEFT')
                        ->where($where)
                        ->group('label.id')
                        ->order('label.create_time desc')
                        ->select();
     
        if (count($dataTwo) > count($data)) {
            $diff_id = array_diff(array_column($dataTwo,'id'), array_column($data,'id'));

            $map['label.id'] = array('in',$diff_id);
            $diff_data = D('ScStoreLabel')->alias('label')
                        ->field('label_name,count(sc_code) as num,label.id,label.create_time')
                        ->join("{$this->tablePrefix}sc_store_label_list lis on label.id = lis.label_code", 'LEFT')
                        ->where($map)
                        ->group('label.id')
                        ->order('label.create_time desc')
                        ->select();
            foreach($diff_data as $k=>$v) {
                $diff_data[$k]['num'] = 0;
            }
            $data = array_merge($data,$diff_data);

        }   
            
        return $this->res($data);
    }

    public function popLabelOperate($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 客户编码
            array('action','require', PARAMS_ERROR, MUST_CHECK),        # 操作
            array('label_name','require',PARAMS_ERROR,ISSET_CHECK),
            array('label_id','require',PARAMS_ERROR,ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        switch ($params['action']) {
            case 'add':
                $res = $this->labelAdd(array('label_name'=>$params['label_name']));
                break;
            case 'update':
                $res = $this->labelUpdate(array('label_name'=>$params['label_name'],'id'=>$params['label_id']));
                break;
            case 'delete':
                $res = $this->labelDelete(array('label_id'=>$params['label_id'],'uc_code'=>$params['uc_code']));
                break;
        }
        return $this->res(null,$res);
    }
    public function labelAdd($params) {
        if (empty($params['label_name']))  return 11001;
        if (mb_strlen($params['label_name'],'utf-8') >6 )  return 11004;
        $data = D('ScStoreLabel')->where(array('label_name'=>$params['label_name']))->find();
        if (!empty($data)) {
            if ($data['status'] == SC_LABEL_STATUS_ENABLE) {
                return 11002;
            }
            $res = D('ScStoreLabel')->where(array('id'=>$data['id']))->save(array('status'=>SC_LABEL_STATUS_ENABLE,'update_time'=>NOW_TIME));
            if($res <= 0 || $res === FALSE){
                return 11003;
            }
            return 0;
        }
        $labelData = array(
            'label_name' => $params['label_name'],
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME,
            'status' => SC_LABEL_STATUS_ENABLE,
        );
        $res = D('ScStoreLabel')->add($labelData);
        if ($res) {
            return 0;
        } else {
            return 11003;
        }
    }
    public function labelUpdate($params) {
        if (empty($params['label_name']))  return 11001;
        if (empty($params['id']))  return 5;
        if (mb_strlen($params['label_name'],'utf-8') >6 )  return 11004;
        $data = D('ScStoreLabel')->where(array('label_name'=>$params['label_name']))->find();
        if (!empty($data)) return 11002;
        if (!empty($data)) {
            if ($data['status'] == SC_LABEL_STATUS_DISABLE) return 11005;
        }
        $res = D('ScStoreLabel')->where(array('id'=>$params['id']))->save(array('label_name'=>$params['label_name'],'update_time'=>NOW_TIME));
        if($res <= 0 || $res === FALSE){
            return 11007;
        }  
        return 0;
    }
    public function labelDelete($params) {

        $findOne = $this->invoke('Base.StoreModule.Basic.Label.getPopLabelData',$params);

        if ($findOne['status'] != 0) {
            return $findOne['status'];
        }
    
        $data = current($findOne['response']);

        if (!empty($data)) {
            return 11005;
        }
        $res = D('ScStoreLabel')->where(array('id'=>$params['label_id']))->save(array('status'=>SC_LABEL_STATUS_DISABLE,'update_time'=>NOW_TIME));
        if($res <= 0 || $res === FALSE){
            return 11008;
        }  
        return 0;
    }

    /**
     * Base.StoreModule.Basic.Label.getLabels
     * 获取标签
     * @author Todor
     * @access public
     */

    public function getLabels($params){
        $this->_rule = array(
            array('sc_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), # 商品编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where['ssll.sc_code'] = array('in',$params['sc_codes']);
        $where['ssll.status'] = "ENABLE";
        $where['ss.status'] = "ENABLE";
        $fields = "ssll.sc_code,ss.label_name,ss.id";

        $res = D('ScStoreLabelList')->alias('ssll')
                                    ->join("{$this->tablePrefix}sc_store_label AS ss ON ss.id = ssll.label_code",'LEFT')
                                    ->field($fields)
                                    ->where($where)
                                    ->order('ss.id desc')
                                    ->select();
        return $this->res($res);
    }
    /*
    * Base.StoreModule.Basic.Label.saveLabels
    * 卖家标签更新添加操作
    */
    public function saveLabels($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 卖家编码
            array('label_id','checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),        # 标签编码
            array('action','require',PARAMS_ERROR,MUST_CHECK)           # 操作
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if ($params['action'] == 'add') {
            $status = $this->addLabels(array('sc_code'=>$params['sc_code'],'label_id'=>$params['label_id']));

        } else if ($params['action'] == 'update') {
            $lables = D('ScStoreLabelList')->field('label_code')->where(array('sc_code'=>$params['sc_code']))->select();

            if (empty($lables)) {
                $status = $this->addLabels(array('sc_code'=>$params['sc_code'],'label_id'=>$params['label_id']));
                return $this->res(null,$status);
            }
            $lables = array_column($lables,'label_code');
            D('ScStoreLabelList')->where(array('sc_code'=>$params['sc_code']))->save(array('status'=>SC_LABEL_STATUS_DISABLE));
            $flag = '';
         
            foreach ($params['label_id'] as $label) {
                if (in_array($label, $lables)) {
                    $flag = D('ScStoreLabelList')->where(array('sc_code'=>$params['sc_code'],'label_code'=>$label))->save(array('status'=>SC_LABEL_STATUS_ENABLE,'update_time'=>NOW_TIME));
                } else {
                    $flag = D('ScStoreLabelList')->add(array('sc_code'=>$params['sc_code'],'label_code'=>$label,'create_time'=>NOW_TIME,'update_time'=>NOW_TIME));
                }
                if ($flag === false || $flag <= 0) {
                    $status = 11010;
                }
            }
        }
        return $this->res(null,$status);
    }
    private function addLabels($params) {
        $data = array();
        foreach ($params['label_id'] as $k=> $label_id) {
                $data[$k]['sc_code'] = $params['sc_code'];
                $data[$k]['label_code'] = $label_id;
                $data[$k]['create_time'] = NOW_TIME;
                $data[$k]['update_time'] = NOW_TIME;
        }
        $res = D('ScStoreLabelList')->addAll($data);
        if ($res) {
            return 0;
        }
    }
}