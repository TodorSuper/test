<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 促销中心 导出  回调接口
 */

namespace Com\CallBack\Export;

use System\Base;

class SpcExport extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    //缺货促销列表回调函数
    public function stock_spcList(&$data,$params){
        $gift=$this->gift_list($params);
        $spc_codes=array();
        foreach($data as $val){
            if($val['type']=='REWARD_GIFT'){
                $spc_codes[]=$val['spc_code'];
            }
        }
        if(empty($spc_codes)){
            $res2=array();
        }else{
            $params['spc_codes']=$spc_codes;
            //得到促销列表和满赠列表的查询
            $apiPath='Base.SpcModule.Gift.GiftInfo.lists';
            $res2=$this->invoke($apiPath,$params);
            if($res2['status']!=0){
                $this->endInvoke(null,$res2['status'],'',$res2['message']);
            }
        }
        //得到促销商品数量为0的列表
        $sic_codes=array();
        foreach($res2['response'] as $val){
            $sic_codes[]=$val['gift_sic_code'];
        }
        $params['sic_codes']=$sic_codes;
        unset($params['stock']);
        $params['status']=IC_STORE_ITEM_ON;
        $apiPath='Base.StoreModule.Item.Item.storeItems';
        $res3=$this->invoke($apiPath,$params);
        if($res3['status']!=0){
            $this->endInvoke(null,$res3['status'],'',$res3['message']);
        }
        //得到促销列表和特价列表的查询
        $codes=array();
        foreach($data as $val){
            if($val['type']=='SPECIAL'){
                $codes[]=$val['spc_code'];
            }
            if($val['type']==SPC_TYPE_LADDER){
                $ladder_codes[]=$val['spc_code'];
            }
        }
        $arr=array(
            'spc_codes'=>$codes,
        );
        if(empty($arr)){
            $special['response']=array();
        }else{
            $apiPath='Base.SpcModule.Special.SpecialInfo.lists';
            $special=$this->invoke($apiPath,$arr);
        }
        //          得到促销列表和阶梯价的查询
        $ladder_codes['spc_codes']=$ladder_codes;
        if(empty( $ladder_codes['spc_codes'])){
            $ladder['response']=array();
        }else{
            $apiPath='Base.SpcModule.Ladder.LadderInfo.lists';
            $ladder=$this->invoke($apiPath,$ladder_codes);
            if($ladder['status']!=0){
                $this->endInvoke(null,$ladder['status'],'',$ladder['message']);
            }
        }
        //将几次的查询的信息合并得到促销列表
        $lists=array();
        foreach($data as $key=>$val){
            $data[$key]['spc_info']=array(
                'start_time'=>$val['start_time'],
                'end_time' =>$val['end_time'],
                'spc_code'=>$val['spc_code'],
                'sic_code'=>$val['sic_code'],
                'status'  =>$val['status'],
                'type'    =>$val['type'],
                'sc_code'=>$val['sc_code'],
                'spc_title'=>$val['spc_title'],
            );
            unset($data[$key]['start_time']);
            unset($data[$key]['end_time']);
            unset($data[$key]['status']);
            unset($data[$key]['type']);
            unset($data[$key]['spc_title']);
            foreach($special['response'] as $spc){
                if($val['spc_code']==$spc['spc_code']){
                    $data[$key]['spc_info']['spc_detail']=$spc;
                }
            }
            foreach($ladder['response'] as $lad){
                if($val['spc_code']==$lad['spc_code']){
                    $data[$key]['spc_info']['spc_detail']=$lad;
                }
            }
            foreach($res2['response'] as $k=>$v){
                if($val['spc_code']==$v['spc_code']){
                    $data[$key]['spc_info']['spc_detail']=$v;
                }
                foreach($res3['response'] as $kkk=>$vvv){
                    if($vvv['sic_code']==$data[$key]['spc_info']['spc_detail']['gift_sic_code']){
                        $data[$key]['spc_info']['spc_detail']['gift_item']=$vvv;
                        $data[$key]['gift_stock']=$vvv['stock'];
                    }
                }
            }
        }
        //将促销类型和促销状态组装入数组
        foreach($data as $key=>$list){
            if($list['spc_info']['spc_detail']){
                switch($list['spc_info']['type']){
                    case SPC_TYPE_GIFT:
                        $arr = array(
                            'start_time'=>$list['spc_info']['start_time'],
                            'end_time'=>$list['spc_info']['end_time'],
                            'status'=>$list['spc_info']['status'],
                            'rule'=>$list['spc_info']['spc_detail']['rule'],
                        );
                        break;
                    case SPC_TYPE_SPECIAL:
                        $arr = array(
                            'special_price'=>$list['spc_info']['spc_detail']['special_price'],
                        );
                        break;
                    case SPC_TYPE_LADDER:
                        $arr=array(
                            'rule'=>$list['spc_info']['spc_detail']['rule'],
                        );
                        break;
                }
                $rule=spcRuleParse($list['spc_info']['type'],$arr);
                $data[$key]['spc_info']['spc_message']=$rule;
            }
            $type=M('Base.SpcModule.Center.Status.getType')->getType($list['spc_info']['type']);
            $status=M('Base.SpcModule.Center.Status.getType')->getStatus($list['spc_info']['status'],$list['spc_info']['start_time'],$list['spc_info']['end_time']);
            $data[$key]['spc_info']['type_message']=$type;
            $data[$key]['spc_info']['status']=$status;
            $data[$key]['spc_info']['status_message']=$list['spc_info']['status'];
        }
        $arr=array_merge($gift,$data);
        $stock=array();
        foreach($arr as $v){
            if($v['spc_info']['status']=='促销中' || $v['spc_info']['status']=='预热中'){
                $stock[]=$v;
            }
        }
        //执行去重
        if(!empty($stock)){
            $yin=changeArrayIndex($stock,'spc_code');
            foreach($yin as $v){
                $final_stock[]=$v;
            }
        }else{
            $final_stock=$stock;
        }
            //重新组装导出列表
        $data=array();
        foreach($final_stock as $val){
            if(isset($val['spc_info']['spc_detail']['gift_item']['stock'])){
                if($val['spc_info']['spc_detail']['gift_item']['stock']<=0){
                    $gift_stock=0;
                }else{
                    $gift_stock=$val['spc_info']['spc_detail']['gift_item']['stock'];
                }
            }else{
                $gift_stock='';
            }
            if($val['max_buy']>0){
                $max_buy=$val['max_buy'];
            }else{
                $max_buy='不限';
            }
            $val['stock']<=0 ? $count_stock=0 : $count_stock=$val['stock'];
            $data[]=array(
                'spc_code'=>$val['spc_code'],
                'sic_no'=>$val['sic_no'],
                'goods_name'=>$val['goods_name'],
                'stock'=>$count_stock,
                'spc_title'=>$val['spc_info']['spc_title'],
                'start_time'=>date('Y-m-d',$val['spc_info']['start_time']),
                'end_time'=>date('Y-m-d',$val['spc_info']['end_time']),
                'type'=>$val['spc_info']['type_message'],
                'rule'=>$val['spc_info']['spc_message'],
                'gift_sic_no'=>$val['spc_info']['spc_detail']['gift_item']['sic_no'],
                'gift_name'=>$val['spc_info']['spc_detail']['gift_item']['goods_name'],
                'gift_stock'=>$gift_stock,
                'status'=>$val['spc_info']['status'],
                'max_buy'=>$max_buy
            );
        }
    }

    //得到赠品为0的列表
    private function gift_list($params){
        unset($params['stock']);
        $apiPath='Base.SpcModule.Center.Spc.lists';
        $res=$this->invoke($apiPath,$params);
        if($res['status']!=0){
            $this->endInvoke(null,$res['status'],'',$res['message']);
        }
        $spc_codes=array();
        foreach($res['response'] as $val){
            if($val['type']=='REWARD_GIFT'){
                $spc_codes[]=$val['spc_code'];
            }
        }
        if(empty($spc_codes)){
            $res2['response']=array();
        }else{
            $params['spc_codes']=$spc_codes;
            //得到促销列表和满赠列表的查询
            $apiPath='Base.SpcModule.Gift.GiftInfo.lists';
            $res2=$this->invoke($apiPath,$params);
            if($res2['status']!=0){
                $this->endInvoke(null,$res2['status'],'',$res2['message']);
            }
        }
        //得到促销列表和特价列表的查询
        $codes=array();
        foreach($res['response'] as $val){
            if($val['type']=='SPECIAL'){
                $codes[]=$val['spc_code'];
            }
        }
        if(empty($codes)){
            $special['response']=array();
        }else{
            $arr=array(
                'spc_codes'=>$codes,
            );
            $apiPath='Base.SpcModule.Special.SpecialInfo.lists';
            $special=$this->invoke($apiPath,$arr);
        }
        //得到赠品数量为0的列表
        $sic_codes=array();
        foreach($res2['response'] as $val){
            $sic_codes[]=$val['gift_sic_code'];
        }

        $stock=array(
            'sc_code'=>$params['sc_code'],
            'sic_codes'=>$sic_codes,
            'stock'=>'stock',
            'is_page'=>'NO',
            'status'=>IC_STORE_ITEM_ON,
        );
        $apiPath='Base.StoreModule.Item.Item.storeItems';
        $res3=$this->invoke($apiPath,$stock);
        if($res3['status']!=0){
            $this->endInvoke(null,$res3['status'],'',$res3['message']);
        }
        //将几次的查询的信息合并得到促销列表
        $lists=array();
        foreach($res['response'] as $key=>$val){
            $res['response'][$key]['spc_info']=array(
                'start_time'=>$val['start_time'],
                'end_time' =>$val['end_time'],
                'spc_code'=>$val['spc_code'],
                'sic_code'=>$val['sic_code'],
                'status'  =>$val['status'],
                'type'    =>$val['type'],
                'sc_code'=>$val['sc_code'],
                'spc_title'=>$val['spc_title'],
            );
            unset($res['response'][$key]['start_time']);
            unset($res['response'][$key]['end_time']);
            unset($res['response'][$key]['status']);
            unset($res['response'][$key]['type']);
            unset($res['response'][$key]['spc_title']);
            foreach($special['response'] as $spc){
                if($val['spc_code']==$spc['spc_code']){
                    $res['response'][$key]['spc_info']['spc_detail']=$spc;
                }
            }
            foreach($res2['response'] as $k=>$v){
                if($val['spc_code']==$v['spc_code']){
                    $res['response'][$key]['spc_info']['spc_detail']=$v;
                }
                foreach($res3['response'] as $kkk=>$vvv){
                    if($vvv['sic_code']==$res['response'][$key]['spc_info']['spc_detail']['gift_sic_code']){
                        $res['response'][$key]['spc_info']['spc_detail']['gift_item']=$vvv;
                        $res['response'][$key]['gift_stock']=$vvv['stock'];
                    }
                }
            }
        }
        //将促销类型和促销状态组装入数组
        foreach($res['response'] as $key=>$list){
            if($list['spc_info']['spc_detail']){
                switch($list['spc_info']['type']){
                    case SPC_TYPE_GIFT:
                        $data = array(
                            'start_time'=>$list['spc_info']['start_time'],
                            'end_time'=>$list['spc_info']['end_time'],
                            'status'=>$list['spc_info']['status'],
                            'rule'=>$list['spc_info']['spc_detail']['rule'],
                        );
                        break;
                    case SPC_TYPE_SPECIAL:
                        $data = array(
                            'special_price'=>$list['spc_info']['spc_detail']['special_price'],
                        );
                        break;
                }
                $rule=spcRuleParse($list['spc_info']['type'],$data);
                $res['response'][$key]['spc_info']['spc_message']=$rule;
            }
            $type=M('Base.SpcModule.Center.Status.getType')->getType($list['spc_info']['type']);
            $status=M('Base.SpcModule.Center.Status.getType')->getStatus($list['spc_info']['status'],$list['spc_info']['start_time'],$list['spc_info']['end_time']);
            $res['response'][$key]['spc_info']['type_message']=$type;
            $res['response'][$key]['spc_info']['status']=$status;
            $res['response'][$key]['spc_info']['status_message']=$list['spc_info']['status'];
        }
        $arr=array();
        foreach($res['response'] as $key=>$val){
            if(!isset($val['spc_info']['spc_detail']['gift_item'])){
                continue;
            }
            $arr[]=$val;
        }
        return $arr;
    }
    //促销列表导出回调函数
    public function spcList(&$data,$params){
        $spc_codes=array();
        foreach($data as $val){
            if($val['type']==SPC_TYPE_GIFT){
                $spc_codes[]=$val['spc_code'];
            }
            if($val['type']==SPC_TYPE_LADDER){
                $ladder_codes[]=$val['spc_code'];
            }
        }
        if(empty($spc_codes)){
            $res2['response']=array();
        }else{
            $params['spc_codes']=$spc_codes;
            //得到促销列表和满赠列表的查询
            $apiPath='Base.SpcModule.Gift.GiftInfo.lists';
            $res2=$this->invoke($apiPath,$params);
            if($res2['status']!=0){
                $this->endInvoke(null,$res2['status'],'',$res2['message']);
            }
        }
        //得到促销列表和特价列表的查询
        $codes=array();
        foreach($data as $val){
            if($val['type']=='SPECIAL'){
                $codes[]=$val['spc_code'];
            }
        }
        if(empty($codes)){
            $special['response']=array();
        }else{
            $arr=array(
                'spc_codes'=>$codes,
            );
            $apiPath='Base.SpcModule.Special.SpecialInfo.lists';
            $special=$this->invoke($apiPath,$arr);
            if($special['status']!=0){
                $this->endInvoke(null,$special['status'],'',$special['message']);
            }
        }
        //          得到促销列表和阶梯价的查询
        $ladder_codes['spc_codes']=$ladder_codes;
        if(empty($ladder_codes['spc_codes'])){
            $ladder['response']=array();
        }else{
            $apiPath='Base.SpcModule.Ladder.LadderInfo.lists';
            $ladder=$this->invoke($apiPath,$ladder_codes);
            if($ladder['status']!=0){
                $this->endInvoke(null,$ladder['status'],'',$ladder['message']);
            }
        }
        //得到满赠商品的信息
        $sic_codes=array();
        foreach($res2['response'] as $val){
            $sic_codes[]=$val['gift_sic_code'];
        }
        $arr['sic_codes']=$sic_codes;
        $arr['is_page']='NO';
        $arr['sc_code']=$params['sc_code'];
        $arr['status']=IC_STORE_ITEM_ON;
        $apiPath='Base.StoreModule.Item.Item.storeItems';
        $res3=$this->invoke($apiPath,$arr);

        if($res3['status']!=0){
            $this->endInvoke(null,$res3['status'],'',$res3['message']);
        }
        //将几次的查询的信息合并得到促销列表
        foreach($data as $key=>$val){
            foreach($res2['response'] as $k=>$v){
                if($val['spc_code']==$v['spc_code']){
                    $data[$key]['spc_detail']=$v;
                }
                foreach($res3['response'] as $kkk=>$vvv){
                    if($vvv['sic_code']==$data[$key]['spc_detail']['gift_sic_code']){
                        $data[$key]['spc_detail']['gift_info']=$vvv;
                    }
                }
            }
            foreach($special['response'] as $spc){
                if($val['spc_code']==$spc['spc_code']){
                    $data[$key]['spc_detail']=$spc;
                }
            }
            foreach($ladder['response'] as $lad){
                if($val['spc_code']==$lad['spc_code']){
                    $data[$key]['spc_detail']=$lad;
                }
            }
        }
//        var_dump($data);exit;
        foreach($data as $key=>$list){
            if($list['spc_detail']){
                switch($list['type']){
                    case SPC_TYPE_GIFT:
                        $arr['start_time']=$list['start_time'];
                        $arr['end_time']=$list['end_time'];
                        $arr['rule']=$list['spc_detail']['rule'];
                        break;
                    case SPC_TYPE_SPECIAL:
                        $arr = array(
                            'special_price'=>$list['spc_detail']['special_price'],
                        );
                        break;
                    case SPC_TYPE_LADDER:
                        $arr=array(
                            'rule'=>$list['spc_detail']['rule'],
                            'need_type'=>'table'
                        );
                        break;
                }
                $rule=spcRuleParse($list['type'],$arr);
                $rule = str_replace('<br/>', '.', $rule);
                $data[$key]['spc_detail']['rule']=$rule;
            }
            $type=M('Base.SpcModule.Center.Status.getType')->getType($list['type']);
            $status=M('Base.SpcModule.Center.Status.getStatus')->getStatus($list['status'],$list['start_time'],$list['end_time']);
            $data[$key]['type']=$type;
            $data[$key]['status']=$status;
        }
        $lists=$data;
        //重新组装导出列表
        $data=array();
        foreach($lists as $val){
            if(isset($val['spc_detail']['gift_info']['stock'])){
                if($val['spc_detail']['gift_info']['stock']<=0){
                    $gift_stock=0;
                }else{
                    $gift_stock=$val['spc_detail']['gift_info']['stock'];
                }
            }else{
                $gift_stock='';
            }
            $val['stock']<=0 ? $stock=0 : $stock=$val['stock'];
            if($val['max_buy']>0){
                $max_buy=$val['max_buy'];
            }else{
                $max_buy='不限';
            }
            $data[]=array(
                'spc_code'=>$val['spc_code'],
                'sic_no'=>$val['sic_no'],
                'goods_name'=>$val['goods_name'],
                'stock'=>$stock,
                'spc_title'=>$val['spc_title'],
                'start_time'=>date('Y-m-d',$val['start_time']),
                'end_time'=>date('Y-m-d',$val['end_time']),
                'type'=>$val['type'],
                'rule'=>$val['spc_detail']['rule'],
                'gift_sic_no'=>$val['spc_detail']['gift_info']['sic_no'],
                'gift_name'=>$val['spc_detail']['gift_info']['goods_name'],
                'gift_stock'=>$gift_stock,
                'status'=>$val['status'],
                'max_buy'=>$max_buy
            );
        }
    }

    /**
     * 导出订购会全部 zhangyupeng
     * Com.Callback.Export.SpcExport.spcCustomerAll
     * @param  [type] &$data  [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function spcCustomerAll(&$data, $params){
        //整理用户uc_code
        $uc_code = array();
        foreach ($data as $value) {
            if ($value['uc_code']) {
                if (!in_array($value['uc_code'], $uc_code)) {
                    $uc_code[] = $value['uc_code']; 
                }
            }
        }

        //根据uc_code获取店铺信息
        $customerData = array(
                'sc_code' => $params['sc_code'],
                'uc_code' => $uc_code,
            );
        $customerApi  = "Base.UserModule.Customer.Customer.getAll";
        $customer_res = $this->invoke($customerApi, $customerData);
        $customer     = changeArrayIndex($customer_res['response'], 'uc_code');
        // var_dump($customer,$data,$uc_code);die();
        //整理输出excel数据
        $newData = array();
        foreach ($data as $key => $value) {
            
            $newData[$key]['name']            = $customer[$value['uc_code']]['name'] ? $customer[$value['uc_code']]['name'] :'';
            $newData[$key]['mobile']          = $customer[$value['uc_code']]['mobile'] ? $customer[$value['uc_code']]['mobile'] :'';
            $newData[$key]['commercial_name'] = $customer[$value['uc_code']]['commercial_name'] ? $customer[$value['uc_code']]['commercial_name'] :'';
            $newData[$key]['channel']         = $customer[$value['uc_code']]['channel'] ? $customer[$value['uc_code']]['channel'] :'';
            $newData[$key]['salesman']        = $customer[$value['uc_code']]['salesman'] ? $customer[$value['uc_code']]['salesman'] :'';
            $newData[$key]['advance_money']   = number_format($value['advance_money'], 2, '.', '.');
            $newData[$key]['spent_money']     = number_format($value['spent_money'], 2, '.', '.');
            $newData[$key]['balance']         = number_format($value['balance'], 2, '.', '.');
        }
        $data = $newData;
    }

    /**
     * 导出全部订购会明细  zhangyupeng
     * Com.Callback.Export.SpcExport.spcCustomerDetail
     * @param  [type] &$data  [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function spcCustomerDetail(&$data, $params){
        //整理用户uc_code
        $uc_code = array();
        foreach ($data as $value) {
            if ($value['uc_code']) {
                if (!in_array($value['uc_code'], $uc_code)) {
                    $uc_code[] = $value['uc_code']; 
                }
            }
        }

        //根据uc_code获取店铺信息
        $customerData = array(
                'sc_code' => $params['sc_code'],
                'uc_code' => $uc_code,
            );
        $customerApi  = "Base.UserModule.Customer.Customer.getAll";
        $customer_res = $this->invoke($customerApi, $customerData);
        $customer     = changeArrayIndex($customer_res['response'], 'uc_code');
        
        //整理输出excel数据
        $newData = array();
        foreach ($data as $key => $value) {
            $newData[$key]['b2b_code']        = $value['b2b_code'];
            $newData[$key]['name']            = $customer[$value['uc_code']]['name'] ? $customer[$value['uc_code']]['name'] :'';
            $newData[$key]['mobile']          = $customer[$value['uc_code']]['mobile'] ? $customer[$value['uc_code']]['mobile'] :'';
            $newData[$key]['commercial_name'] = $customer[$value['uc_code']]['commercial_name'] ? $customer[$value['uc_code']]['commercial_name'] :'';
            $newData[$key]['real_amount']     = $value['real_amount'];
            $newData[$key]['salesman']        = $customer[$value['uc_code']]['salesman'] ? $customer[$value['uc_code']]['salesman'] :'';
            $newData[$key]['channel']         = $customer[$value['uc_code']]['channel'] ? $customer[$value['uc_code']]['channel'] :'';
            $newData[$key]['create_time']     = empty($value['create_time']) ? '' : date('Y-m-d H:i:s',$value['create_time']);
            $newData[$key]['pay_method']      = "预付款支付";
            $newData[$key]['order_status']    = M('Base.OrderModule.B2b.Status.detailToGroup')->detailToGroup($value['order_status'],$value['ship_status'],$value['pay_status'], $value['pay_type'],$value['ship_method'])['message'];
        }
        $data = $newData; 
    }

    /**
     * 促销效果查询回掉函数
     * Com.Callback.Export.SpcExport.effect
     * @access public
     * @author Todor
     */
    public function effect(&$data,$params){

        if(!empty($data)){

            // 获取赠品信息
            $spc_codes['spc_codes'] = array_column($data,'spc_code');
            $spc_codes['sc_code']   = $params['where']['obo.sc_code'];
            $apiPath                = 'Base.SpcModule.Gift.GiftInfo.lists';
            $res                    = $this->invoke($apiPath,$spc_codes);
            if($res['status'] != 0){
                $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $res = $res['response'];
            foreach ($res as $k => $v) {
                $res[$v['spc_code']] = $v;
                unset($res[$k]);
            }

            // 获取特价信息
            $apiPath = 'Base.SpcModule.Special.SpecialInfo.lists';
            $special_res = $this->invoke($apiPath,$spc_codes);
            if($res['status'] != 0){
                $this->endInvoke(null,$special_res['status'],'',$special_res['message']);
            }
            $special_res = $special_res['response'];
            foreach ($special_res as $k => $v) {
                $special_res[$v['spc_code']] = $v;
                unset($special_res[$k]);
            }

            // 获取商品赠品数量
            $spc_codes['type'] = $params['type'];
            $spc_codes['customer'] = $params['where']['obo.client_name'];
            $spc_codes['salesman'] = $params['where']['obo.salesman_id'];

            if(!empty($spc_codes['customer']) || $spc_codes['type'] == 'detail'){
                $spc_codes['uc_codes']  = array_column($data,'uc_code');
            }

            $apiPath = "Base.OrderModule.B2b.OrderInfo.gift";
            $num = $this->invoke($apiPath,$spc_codes);
            
            if($res['status'] != 0){
                $this->endInvoke(null,$num['status'],'',$num['message']);
            }
            $num = $num['response'];

            if(!empty($spc_codes['customer']) || $spc_codes['type'] == 'detail'){   # 有客户 或详情 

                foreach ($num as $k => $v) {
                    $num[$v['spc_code'].$v['uc_code']] = $v;
                    unset($num[$k]);
                }

                $customer = array_keys($num);
                foreach ($data as $k => $v) {
                    switch ($v['type']) {
                        case SPC_TYPE_GIFT:
                            if(in_array($v['spc_code'].$v['uc_code'], $customer)){
                                $data[$k]['order_gift'] = $num[$v['spc_code'].$v['uc_code']];  # 赠品数量        
                            }
                            break;
                    }   
                }

            }else{

                foreach ($num as $k => $v) {
                    $num[$v['spc_code']] = $v;
                    unset($num[$k]);
                }

                $customer = array_keys($num);
                foreach ($data as $k => $v) {
                    switch ($v['type']) {
                        case SPC_TYPE_GIFT:
                            if(in_array($v['spc_code'], $customer)){
                                $data[$k]['order_gift'] = $num[$v['spc_code']];  # 赠品数量        
                            }
                            break;
                    }   
                }

            }



            //获取商品信息
            $gift['sic_codes'] = array_column($res,'gift_sic_code');
            $gift['sic_codes'] = array_unique($gift['sic_codes']);
            $gift['sc_code']   = $params['where']['obo.sc_code'];
            $gift['is_page']   = 'NO';
            $gift['status']    =IC_STORE_ITEM_ON;
            $apiPath='Base.StoreModule.Item.Item.storeItems';
            $item = $this->invoke($apiPath,$gift);

            if($item['status']!=0){
                $this->endInvoke(null,$item['status'],'',$item['message']);
            }
            $item = $item['response'];
            foreach ($item as $k => $v) {
                $item[$v['sic_code']] = $v;
                unset($item[$k]);
            }

            // 组装赠品信息
            foreach ($data as $k => $v) {
                    if(in_array($v['spc_code'], $spc_codes['spc_codes'])){

                        switch ($v['type']) {

                            case SPC_TYPE_GIFT:
                                $data[$k]['spc_detail'] = $res[$v['spc_code']];          # 赠品信息
                                break;

                            case SPC_TYPE_SPECIAL:
                                $data[$k]['spc_detail'] = $special_res[$v['spc_code']];  # 特价信息
                                break;

                            case SPC_TYPE_LADDER:
                                $data[$k]['spc_detail']['rule'] = $v['ladder_rule'];     # 阶梯价信息
                                break;            
                        }
          
                    }
            }          
            

            //组装商品
            foreach ($data as $k => $v) {

                if(in_array($v['spc_detail']['gift_sic_code'], $gift['sic_codes'])){
                    $data[$k]['spc_detail']['gift_item'] = $item[$v['spc_detail']['gift_sic_code']];
                }

            }

            // 组装导出商品信息
            $datas = array();

            foreach ($data as $k => $v) {

                // 规则
                switch ($v['type']) {
                    case SPC_TYPE_GIFT:
                        $temp['rule'] = $v['spc_detail']['rule'];
                        break;
                    case SPC_TYPE_SPECIAL:
                        $temp['special_price'] = $v['spc_detail']['special_price'];
                        break;

                    case SPC_TYPE_LADDER:
                        $temp['need_type'] = 'table';
                        $temp['rule']      = $v['spc_detail']['rule'];
                        break;
                }

                //导出所有需要的字段
                $datas[$k] = array(

                    'spc_code'        => $v['spc_code'],
                    'sic_no'          => $v['sic_no'],
                    'goods_name'      => $v['goods_name'],
                    'spec'            => $v['spec'],
                    'spc_title'       => $v['spc_title'],
                    'start_time'      => date('Y-m-d',$data[$k]['start_time']),
                    'end_time'        => date('Y-m-d',$data[$k]['end_time']),
                    'type'            => get_spc($v['type']),
                    'all_number'      => $v['all_number'],
                    'all_price'       => $v['all_price'],
                    'send_rule'       => spcRuleParse($v['type'],$temp),
                    'send_sic_no'     => $v['spc_detail']['gift_item']['sic_no'],
                    'send_goods_name' => $v['spc_detail']['gift_item']['goods_name'],
                    'send_paking'     => $v['spc_detail']['gift_item']['packing'],
                    'goods_number'    => $v['type'] == 'REWARD_GIFT' ? (empty($v['order_gift']['goods_number']) ? 0 : $v['order_gift']['goods_number']) : '',
                    'goods_price'     => $v['type'] == SPC_TYPE_SPECIAL ? $v['goods_price'] : '',
                    'special_price'   => $v['spc_detail']['special_price'],
                    'salesman'        => $v['salesman'],
                    'customer'        => $v['client_name'],
                    );

                // 详细导出 改变显示位置
                if($params['type'] == 'detail'){

                    $arr = array(
                        'salesman'=>$datas[$k]['salesman'],
                        'customer'=>$datas[$k]['customer'],
                        );
                    array_splice($datas[$k], 1, 0, $arr);    # 插入客户与业务员
                    array_splice($datas[$k], -2);            # 删除最后两个元素
                }

                if($params['type'] == 'all'){               # 按表格头 选择数据数量
                    $num = count($params['title']);
                    $datas[$k] = array_slice($datas[$k], 0, $num,ture);
                }

            }

            $data = $datas;
        }        
    }
    //促销券活动列表导出回调
    public function active_export(&$data){
       $data = array_map(function($v){
           switch($v['status']){
               case 'ENABLE':
                   if($v['coupon_end_time']<NOW_TIME){
                       $v['status'] = '已过期';
                   }elseif($v['coupon_start_time']>NOW_TIME){
                       $v['status']='未生效';
                   }else{
                       $v['status'] = '可用';
                   }
                   break;
               case 'INVALID':
                   $v['status']='未生效';
                   break;
               case 'USED':
                   $v['status']='已使用';
                   break;
               case 'OCCUPY':
                   $v['status']='占用';
                   break;
               default :
           }
           $v['condition_flag'] = M('Base.SpcModule.Center.Status.getConditionToStr')->getConditionToStr($v['condition_flag']);
           $v['rule_flag'] = M('Base.SpcModule.Center.Status.getRuleToStr')->getRuleToStr($v['rule_flag']);
           $v['active_status'] =  M('Base.SpcModule.Center.Status.getActiveStatus')->getActiveStatus($v['active_status'],$v['start_time'],$v['end_time']);
           $v['start_time'] = date('Y/m/d H:i:s',$v['start_time']);
           $v['end_time'] = date('Y/m/d H:i:s',$v['end_time']);
           $v['active_create_time'] = date('Y/m/d H:i:s',$v['active_create_time']);
           $v['create_time'] = date('Y/m/d H:i:s',$v['create_time']);
           if($v['occupy_time']>0){
               $v['occupy_time'] =  date('Y/m/d H:i:s',$v['occupy_time']);
           }else{
               $v['occupy_time'] = '未占用';
           }
           if($v['use_time']>0){
               $v['use_time'] = date('Y/m/d H:i:s',$v['use_time']);
           }else{
               $v['use_time'] = '未使用';
           }
           if($v['online_time']>0){
               $v['online_time'] = date('Y/m/d H:i:s',$v['online_time']);
           }else{
               $v['online_time'] = '未上线';
           }
           unset($v['coupon_start_time']);
           unset($v['coupon_end_time']);
           return $v;
       },$data);
    }

}

?>
