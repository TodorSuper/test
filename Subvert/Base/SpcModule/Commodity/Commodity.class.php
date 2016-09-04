<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |促销规则
 */


namespace Base\SpcModule\Commodity;

use System\Base;


class Commodity extends Base
{

    public function __construct(){
        parent::__construct();
    }

    /**
     * Base.SpcModule.Commodity.Commodity.add
     * @param [type] $params [description]
     */
    public function add($params)
    {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('commodity_title', 'require', PARAMS_ERROR, MUST_CHECK),#订货会标题
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),#商家编码
            array('min_advance', 'require', PARAMS_ERROR, ISSET_CHECK),#最低预收款
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),#订货会开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),#订货会结束时间
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $total_info=$params['total_info'];
        // 生成促销编码
        $codeData = array(
            "busType"    => SPC_COMMODITY_CODE,
            "preBusType" => SPC_COMMODITY_MEETING,
            "codeType"   => SEQUENCE_SPC
        );

        $spcCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $codeData);
        if( $spcCode['status'] !== 0) {
            return $this->res('', 7056);
        }
        //拼装要添加到订货会表中的数据
        $data=array(
            'spc_code'=>$spcCode['response'],
            'commodity_title'=>$params['commodity_title'],
            'sc_code'=>$params['sc_code'],
            'min_advance'=>$params['min_advance'],
            'start_time'=>$params['start_time'],
            'end_time'=>$params['end_time'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>COMMODITY_STATUS_PUBLISH,
            'advance_money'=>$total_info['total_user_info']['advanced_payment'],
            'spent_money'=>$total_info['total_user_info']['spent_money'],
            'balance'=>$total_info['total_user_info']['balance'],
            'last_amount'=>$total_info['total_user_info']['last_amount'],
        );
         //添加到订货会的表中
        $result=D('SpcCommodity')->add($data);
        if(!$result){
            return $this->res('',7057);
        }
        //组装添加到订货会用户表中的数据
        $user_data=array();
        if($total_info['userInfo']){
            foreach($total_info['userInfo'] as $key=>$val){
                $user_data[]=array(
                    'sc_code'=>$params['sc_code'],
                    'spc_code'=>$spcCode['response'],
                    'uc_code'=>$val['code'],
                    'advance_money'=>$val['advanced_payment'],
                    'spent_money'=>$val['spent_money'],
                    'balance'=>$val['balance'],
                    'last_amount'=>$val['last_amount'],
                    'create_time'=>NOW_TIME,
                    'update_time'=>NOW_TIME,
                    'status'=>'ENABLE',
                );
            }
            //将数据添加到订货会用户表中
            $insert=D('SpcCustomer')->addAll($user_data);
            if(!$insert){
                return $this->res('',7058);
            }
        }
        return $this->res($spcCode['response']);
    }

    /**
     * Base.SpcModule.Commodity.Commodity.update
     * @param [type] $params [description]
     */
    public function update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK),#订货会状态
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),#商家编码
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK),#促销编码
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),#订货会开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),#订货会结束时间
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //组装更新的数据
        $data=array();
        $params['status'] ? $data['status']=$params['status'] : null;
        $params['start_time'] ? $data['start_time']=$params['start_time'] : null;
        $params['end_time']  ? $data['end_time']=$params['end_time'] : null;
        $data['create_time']=NOW_TIME;
        $data['update_time']=NOW_TIME;

        //组装更新条件
        $where=array();
        $where['sc_code']=$params['sc_code'];
        $where['spc_code']=$params['spc_code'];
        //执行更新
        $result=D('SpcCommodity')->where($where)->save($data);
        if($result===false){
            return $this->res('',7061);
        }
        return $this->res(true);
    }

    /**判断该商家目前有没有正在发布的订货会
     * Base.SpcModule.Commodity.Commodity.getInfo
     * @param [type] $params [description]
     */
    public function getInfo($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('get_commodity', array('YES','NO'), PARAMS_ERROR,ISSET_CHECK,'in'), # 是否获取正在进行的订购会
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取本商家用户的剩余金额信息
        $where=array(
            'sc_code'=>$params['sc_code'],
            'status'=>COMMODITY_STATUS_PUBLISH
        );
        //判断该商家目前有没有正在发布的订货会
        $meeting=D('SpcCommodity')->where($where)->find();

        
        $get_commodity = empty($params['get_commodity']) ? 'NO' : $params['get_commodity'];
        if($get_commodity == 'YES'){
            if(!empty($meeting)){
                return $this->res($meeting);
            }     
        }else{
            if($meeting){
                return $this->res('',7055);
            }
        }
        
        //判断该商家之前有没有订货会
        unset($where['status']);
        $info=D('SpcCommodity')->where($where)->order('create_time desc')->limit(1)->find();
        return $this->res($info);
    }



}