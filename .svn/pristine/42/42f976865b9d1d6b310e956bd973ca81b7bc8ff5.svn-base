<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | cms商家统计相关的操作
 */

namespace Bll\Cms\Store;

use System\Base;

class StoreInfo extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Sc';
    }
    /**
     * Bll.Cms.Store.StoreInfo.export
     * @param type $params
     * @return type
     */
    public function export($params){
        $apiPath='Base.StoreModule.Store.Statistic.export';
        $call=$this->invoke($apiPath,$params);
        return $this->endInvoke($call['response']);
    }
    /**
     * Bll.Cms.Store.StoreInfo.count_list
     * @param type $params
     * @return type
     */

    public function count_list($params){
        //得到全部的成交金额
        $params['flag']='deal';
        $params['is_page']='NO';
        $apiPath='Base.StoreModule.Store.Statistic.lists';
        $deal=$this->invoke($apiPath,$params);
        if($deal['status']!=0){
            return  $this->endInvoke('',$deal['status']);
        }
        $deal_amount=array_column($deal['response'],'deal_amount');
        $total_deal_amount=array_sum($deal_amount);       #全部的成交金额
        //得到全部的已付款金额
        $params['flag']='pay';
        $pay=$this->invoke($apiPath,$params);
        if($deal['status']!=0){
            return  $this->endInvoke('',$pay['status']);
        }
        $pay_amount=array_column($pay['response'],'pay_amount');
        $total_pay_amount=array_sum($pay_amount);    #全部的已付款金额

        //全部的店家数量
        $total_store_count=count($deal['response']);

        //将三个数量合并为一个数组
        $total=array(
            'total_deal_amount'=>$total_deal_amount,
            'total_pay_amount'=>$total_pay_amount,
            'total_store_count'=>$total_store_count,
        );
        return $total;
    }

    /**
     * Bll.Cms.Store.StoreInfo.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        $data=$params;
//        $sc_code=$params['sc_code'];
//        $shaungci_name=$params['merchant_name'];
        //得到商家名称和成交金额
        $params['flag']='deal';
        $apiPath='Base.StoreModule.Store.Statistic.lists';
        $deal=$this->invoke($apiPath,$params);
        if($deal['status']!=0){
           return  $this->endInvoke('',$deal['status']);
        }
        //得到已付款金额
        $params['flag']='pay';
        $pay=$this->invoke($apiPath,$params);
//        var_dump($pay);exit;
        if($deal['status']!=0){
            return  $this->endInvoke('',$pay['status']);
        }
        //将两次得到的结果合并为一个数组

        foreach($deal['response']['lists'] as $key=>$val){
            foreach($pay['response']['lists'] as $k=>$v){
                if($v['sc_code']==$val['sc_code']){
                    $deal['response']['lists'][$key]['pay_amount']=$v['pay_amount'];
                }
            }
        }

        //将组装的数组复制给新数组
        $store_statistic=$deal;
        $sc_codes=array_column($store_statistic['response']['lists'],'sc_code');
        //得到不分页的sc_code
        $total_sc_code=$store_statistic['response']['sc_code'];
        $total_param['sc_codes']=$total_sc_code;
        $total_param['start_time']=$params['start_time'];
        $total_param['end_time']=$params['end_time'];
        //得到商家和用户表联查的结果
        $apiPath='Base.StoreModule.Store.Statistic.Customer';

//        var_dump($total_sc_code);exit;
        //得到查询条件下注册用户的总数
        if(empty($total_sc_code)){
            $total_customer_amount=0;
        }else{
            $total_customer=$this->invoke($apiPath,$total_param);
            $customer_amount=array_column($total_customer['response'],'customer_amount');
            $total_customer_amount=array_sum($customer_amount);
        }
        if(empty($sc_codes)){
            $customer['response']=array();
        }else{
            $data['sc_codes']=$sc_codes;
            $data['start_time']=$params['start_time'];
            $data['end_time']=$params['end_time'];
            $customer=$this->invoke($apiPath,$data);
            if($customer['status']!=0){
                return $this->endInvoke('',$customer['status']);
            }
        }
//        var_dump($customer);exit;
        //得到所有店家的有效用户
        $valid_param['sc_codes']=$sc_codes;
        $valid_param['start_time']=$params['start_time'];
        $valid_param['end_time']=$params['end_time'];
        $apiPath='Base.StoreModule.Store.Statistic.valid_customer';
        $valid=$this->invoke($apiPath,$valid_param);
        //将客户信息与店家定单信息合并
        foreach($store_statistic['response']['lists'] as $k=>$v){
            foreach($customer['response'] as $key=>$val){
                    if($val['sc_code']==$v['sc_code']){
                        $store_statistic['response']['lists'][$k]['customer_amount']=$val['customer_amount'];
                    }
            }
            foreach($valid['response'] as $sc_code=>$num){
                if($sc_code==$v['sc_code']){
                    $store_statistic['response']['lists'][$k]['valid_amount']=$num;
                }
            }
        }
        $total=$this->count_list($data);
        $store_statistic['response']['total']=$total;
        $store_statistic['response']['total_customer_amount']=$total_customer_amount;
        //获取商家列表
        $apiPath  = "Base.StoreModule.Basic.Store.lists";
        $storeLists = $this->invoke($apiPath);
        $store_statistic['response']['store_lists']=$storeLists['response'];
        //获取双磁对接人
        $apiPath = "Base.UserModule.User.User.getSalesmanList";
        $store_statistic['response']['salesman_list'] = $this->invoke($apiPath)['response'];

        return $this->endInvoke($store_statistic['response']);
    }

}

?>
