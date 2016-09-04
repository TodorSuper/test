<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订单生命周期卖家数据统计
 */

namespace Bll\Bi\Store;

use System\Base;

class Store extends Base
{

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct()
    {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }

    /**
     * 卖家订单分析数据导出
     * Bll.Bi.Store.Store.export
     * @param type $params
     */
    public function export($params){
        $params['start_time']   =  empty($params['start_time']) ? 0 : strtotime($params['start_time']);
//        var_dump($start_time);exit;
        $params['end_time']     =  empty($params['end_time'])   ? time() : strtotime($params['end_time']);
        $apiPath='Base.BicModule.Sc.Store.export';
        $userInfo=$this->invoke($apiPath,$params);
//        var_dump($userInfo);exit;
        if($userInfo['status']!==0){
            return $this->endInvoke('',$userInfo['status']);
        }
        return $this->endInvoke($userInfo['response']);
    }

    /**
     * 订单分析数据
     * Bll.Bi.Store.Store.Info
     * @param type $params
     */
    public function Info($params){
        //计算总的一些数据
        $total_data=$this->total_pay_amount($params);
        $Info=array();
        //得到卖家列表
        $apiPath='Base.BicModule.Sc.Store.lists';
        $store=$this->invoke($apiPath);
//        var_dump($store);exit;
        $apiPath='Base.BicModule.Sc.Store.storeInfo';
        $storeInfo=$this->invoke($apiPath,$params);
        $totalnum=$storeInfo['response']['totalnum'];
        $pageNum=$storeInfo['response']['page_number'];
//        var_dump($storeInfo);exit;
        if($storeInfo['status']!==0){
            return $this->endInvoke('',$storeInfo['status']);
        }
        $storeInfo=$storeInfo['response']['lists'];
        $sc_code=array_column($storeInfo,'sc_code');
        $storeInfo=changeArrayIndex($storeInfo,'sc_code');
//        var_dump($sc_code);exit;
        $params['sc_code']=$sc_code;
        if(!$sc_code){
//            $list['list']=array();
            $list['totalnum']=0;
            $list['page_number']=20;
            $list['store_lists']=$store['response'];
            return $this->endInvoke($list);
        }
        //得到每个卖家的客户数量
        $apiPath='Base.BicModule.Sc.Data.userNum';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['user_num']=$call['response'];
        //得到每个商家未发货的数量
        $apiPath='Base.BicModule.Sc.Data.unshipNum';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['unship_info']=$call['response'];

        //得到每个商家取消的订单
        $apiPath='Base.BicModule.Sc.Data.merchantCancelNum';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['merchant_cancel_info']=$call['response'];

        //得到每个商家发货的订单
        $apiPath='Base.BicModule.Sc.Data.shippedNum';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['shipped_info']=$call['response'];

        //得到平台发货平均时长
        $apiPath='Base.BicModule.Sc.Data.shipTime';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['average_info']=$call['response']['ship_time'];
//       var_dump($info['average_info']);exit;
        $info['store_average_info']=$call['response']['store_ship_time'];

        //得到新增成单总量
        $apiPath='Base.BicModule.Sc.Data.completeOrder';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['complete_order_info']=$call['response'];
//        var_dump($info['average_info']['average_ship_time']);exit;
//var_dump($info['complete_order_info']);exit;
        //得到新增成单总量
        $apiPath='Base.BicModule.Sc.Data.lastTime';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$storeInfo['status']);
        }
        $info['last_time_info']=$call['response'];

        //得到已付款总额和不包含预付款的已付款总额
        $apiPath='Base.BicModule.Sc.Data.pay_amount';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['total_pay_amount']=$call['response']['total_pay_amount'];
        $info['no_advance_amount']=$call['response']['no_advance_amount'];
        foreach($storeInfo as $key=>$val){
            if($info['user_num'][$key]['total_user_num']){
                $storeInfo[$key]['total_user_num']=$info['user_num'][$key]['total_user_num'];
            }else{
                $storeInfo[$key]['total_user_num']=0;
            }
            if($info['user_num'][$key]['new_user_num']){
                $storeInfo[$key]['new_user_num']=$info['user_num'][$key]['new_user_num'];
            }else{
                $storeInfo[$key]['new_user_num']=0;
            }
            if($info['user_num'][$key]['pay_user_num']){
                $storeInfo[$key]['pay_user_num']=$info['user_num'][$key]['pay_user_num'];
            }else{
                $storeInfo[$key]['pay_user_num']=0;
            }
            if($info['user_num'][$key]['plat_pay_user_num']){
                $storeInfo[$key]['plat_pay_user_num']=$info['user_num'][$key]['plat_pay_user_num'];
            }else{
                $storeInfo[$key]['plat_pay_user_num']=0;
            }
            if($info['unship_info'][$key]['unship_num']){
                $storeInfo[$key]['unship_num']=$info['unship_info'][$key]['unship_num'];
            }else{
                $storeInfo[$key]['unship_num']=0;
            }
            if($info['merchant_cancel_info'][$key]['cancel_num']){
                $storeInfo[$key]['merchant_cancel_num']=$info['merchant_cancel_info'][$key]['cancel_num'];
            }else{
                $storeInfo[$key]['merchant_cancel_num']=0;
            }
            if($info['shipped_info'][$key]['shipped_num']){
                $storeInfo[$key]['shipped_num']=$info['shipped_info'][$key]['shipped_num'];
            }else{
                $storeInfo[$key]['shipped_num']=0;
            }
            if(($info['shipped_info'][$key]['shipped_num']+$info['unship_info'][$key]['unship_num'])>0){
                $storeInfo[$key]['ship_rate']=round($info['shipped_info'][$key]['shipped_num']/($info['shipped_info'][$key]['shipped_num']+$info['unship_info'][$key]['unship_num']),3)*100;
                $storeInfo[$key]['ship_rate']=$storeInfo[$key]['ship_rate'].'%';
            }else{
                $storeInfo[$key]['ship_rate']='--';
            }
            if($info['average_info']['average_ship_time']){
                $storeInfo[$key]['average_ship_time']=round($info['average_info']['average_ship_time']/60,1);
            }else{
                $storeInfo[$key]['average_ship_time']='--';
            }
            if($info['store_average_info'][$key]['average_ship_time']){
                $storeInfo[$key]['store_average_ship_time']=round($info['store_average_info'][$key]['average_ship_time']/60,1);
            }else{
                $storeInfo[$key]['store_average_ship_time']='--';
            }
            if( $info['total_pay_amount'][$key]['pay_amount']){
                $storeInfo[$key]['total_pay_amount']=$info['total_pay_amount'][$key]['pay_amount'];
            }else{
                $storeInfo[$key]['total_pay_amount']=0;
            }
            if( $info['no_advance_amount'][$key]['pay_amount']){
                $storeInfo[$key]['no_advance_amount']=$info['no_advance_amount'][$key]['pay_amount'];
            }else{
                $storeInfo[$key]['no_advance_amount']=0;
            }
            $storeInfo[$key]['complete_num']=$info['complete_order_info'][$key]['complete_num'];
            $storeInfo[$key]['total_complete_amount']=$info['complete_order_info'][$key]['total_complete_amount'];
            $storeInfo[$key]['link_rate']=$info['complete_order_info'][$key]['link_rate'];
            if($info['last_time_info'][$key]['last_time']){
                $storeInfo[$key]['complete_last_time']=$info['last_time_info'][$key]['last_time'];
            }else{
                $storeInfo[$key]['complete_last_time']='--';
            }
        }
//        var_dump($storeInfo);exit;
        $list['list']=$storeInfo;
        $list['totalnum']=$totalnum;
        $list['page_number']=$pageNum;
        $list['store_lists']=$store['response'];
        $list['total_data']=$total_data;
//        var_dump($list);exit;
         return $this->endInvoke($list);

    }

    /**
     * 订单分析数据
     * Bll.Bi.Store.Store.Info
     * @param type $params
     */
    private function total_pay_amount($params){
        $apiPath='Base.BicModule.Sc.Store.storeInfo';
        $storeInfo=$this->invoke($apiPath,$params);
//        echo json_encode($storeInfo['response']);exit;
        if($storeInfo['status']!==0){
            return $this->endInvoke('',$storeInfo['status']);
        }
        $sc_code=$storeInfo['response']['sc_code'];
        $params['sc_code']=$sc_code;
        if(!$sc_code){
            $total=array(
                'total_new_user_num'=>0,
                'total_pay_user_num'=>0,
                'total_pay_amount'=>0,
                'total_no_advance_amount'=>0
            );
            return $total;
        }
        //得到每个卖家的客户数量
        $apiPath='Base.BicModule.Sc.Data.userNum';
        $call=$this->invoke($apiPath,$params);
//        var_dump($call['response']);exit;
        $new_user=array_column($call['response'],'new_user_num');
        $total_new_user=array_sum($new_user);
        $pay_user=array_column($call['response'],'pay_user_num');
        $total_pay_user=array_sum($pay_user);
//        var_dump($call['response']);exit;
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        //得到已付款总额和不包含预付款的已付款总额
        $apiPath='Base.BicModule.Sc.Data.pay_amount';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
//        var_dump($call['response']);exit;
        $pay_amount=array_column($call['response']['total_pay_amount'],'pay_amount');
        $total_pay_amount=array_sum($pay_amount);
        $no_advance_amount=array_column($call['response']['no_advance_amount'],'pay_amount');
        $total_no_advance_amount=array_sum($no_advance_amount);
        $total=array(
            'total_new_user_num'=>$total_new_user,
            'total_pay_user_num'=>$total_pay_user,
            'total_pay_amount'=>$total_pay_amount,
            'total_no_advance_amount'=>$total_no_advance_amount
        );
        return $total;
//
    }

}
