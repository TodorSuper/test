<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangliangliang <wangliangliang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 获取促销信息
 */

namespace Base\SpcModule\Center;

use System\Base;

class SpcInfo extends Base {

    /**
     * Base.SpcModule.Center.SpcInfo.get
     * @param type $params
     * @return type
     * @Author: wangliangliang <wangliangliang@liangrenwang.com >
     */
    public function get($params) {

        $this->_rule = array(
            array('spc_code', 'require', PARAMS_ERROR, MUST_CHECK), //促销标号
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家编号
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取促销信息主表

        $spc_code = $params['spc_code']; //促销编号
        $sc_code = $params['sc_code'];   //商家编号

        //验证不同平台下的编码
        //组装where条件

        $where = array(
            'spc_code' => $spc_code,
            'sc_code' => $sc_code,
        );

        $com_info = D('SpcList')->where($where)->find();

        if (empty($com_info)) {

            return $this->res(NULL, 7022);
        }

        switch ($com_info['type']) {
            case SPC_TYPE_GIFT:
                $apiPath = "Base.SpcModule.Gift.GiftInfo.get";
                break;
            case SPC_TYPE_SPECIAL:
                $apiPath = "Base.SpcModule.Special.SpecialInfo.get";
                break;
            case SPC_TYPE_LADDER:
                $apiPath = "Base.SpcModule.Ladder.LadderInfo.get";
                break;
        }

        $info = $this->invoke($apiPath, $params);
        if ($info['status'] != 0) {
            return $this->rws(null, $info['status']);
        }
        $com_info['spc_info'] = $info['response'];
        return $this->res($com_info);
    }



启动一个服务：systemctl start firewalld.service
关闭一个服务：systemctl stop firewalld.service
重启一个服务：systemctl restart firewalld.service
显示一个服务的状态：systemctl status firewalld.service
在开机时启用一个服务：systemctl enable firewalld.service
在开机时禁用一个服务：systemctl disable firewalld.service
查看服务是否开机启动：systemctl is-enabled firewalld.service
查看已启动的服务列表：systemctl list-unit-files|grep enabled
查看启动失败的服务列表：systemctl --failed
TYPE=Ethernet

BOOTPROTO=none

DEFROUTE=yes

PEERDNS=yes

PEERROUTES=yes

IPV4_FAILURE_FATAL=no

IPV6INIT=yes

IPV6_AUTOCONF=yes

IPV6_DEFROUTE=yes

IPV6_PEERDNS=yes

IPV6_PEERROUTES=yes

IPV6_FAILURE_FATAL=no

NAME=eno16777736

UUID=8a3e674d-e8a6-4b51-98c8-d68010ed96e9

DEVICE=eno16777736

ONBOOT=yes

IPADDR=192.168.0.103
NETMASK=255.255.254.0
GATEWAY=192.168.1.1
DNS1=114.114.114.114


    /**
     * B2B商品列表对应促销信息
     * Base.SpcModule.Center.SpcInfo.spcInfo
     * @param type array sic_code
     * @access public
     * @author Todor
     */

    public function spcInfo($params){
        
        $this->_rule = array(
            array('sic_code', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), 
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('need_preheat', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家编码
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_code = $params['sic_code'];  # 店铺商品编号
/*        $sc_code  = $params['sc_code'];  #商家编码
        $need_preheat = $params['need_preheat']; #是否需要预热商品
                    $time     = NOW_TIME;             # 时间
 
        $where               = array();
        !empty($sic_code) && $where['sic_code']   = array('in', $sic_code);
        !empty($sc_code) && $where['sc_code'] = $sc_code;
        if($need_preheat != 'YES'){
            $where['start_time'] = array('elt', $time);   #   不需要预热商品 大于促销开始时间
        }
        
        $where['end_time']   = array('egt', $time);   # 小于促销结束时间
        $where['status']     = SPC_STATUS_PUBLISH;    # 状态为促销中  
        // 获取其促销类型
        $temp = D('SpcList')->where($where)->select();*/
         if(empty($temp)){
             return $this->res(NULL);
         }
        //不同促销类型 促销编码合并 
        foreach ($temp as $k => $v) {
              $spcInfo[$v['type']][] = $v['spc_code'];                   
        }
        $spc_detail = array();
        foreach ($spcInfo as $k => $v) {
            switch ($k) {
                case SPC_TYPE_GIFT:                         # 满赠
                    $apiPath = "Base.SpcModule.Gift.GiftInfo.lists";
                    break;
                case SPC_TYPE_SPECIAL:                      # 特价
                    $apiPath = "Base.SpcModule.Special.SpecialInfo.lists";
                    break;
                case SPC_TYPE_LADDER:                       # 阶梯价
                    $apiPath = "Base.SpcModule.Ladder.LadderInfo.lists";
                    break;
            }
            $data = array('spc_codes'=>$v);
            $detail_res = $this->invoke($apiPath,$data);
            if($detail_res['status'] != 0){
                return $this->res(NULL,$detail_res['status']);
            }
            $spc_detail[$k] = $detail_res['response'];
        }
        
        foreach($temp as $k=>$v){
            foreach($spc_detail[$v['type']] as $key=>$val){
                if($v['spc_code'] == $val['spc_code']){
                    $temp[$k]['spc_detail'] = $val;
                    continue 2;
                }
            }
        }

        return $this->res($temp);
    
    }

}

?>
