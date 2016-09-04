<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | Boss版登陆
 */

namespace Bll\Boss\User;

use System\Base;
class Customer extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



   /**
    * @api  Boss版客户统计客户详情
    * @apiVersion 1.0.1
    * @apiName Bll.Boss.User.Customer.get
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2015-10-21
    * @apiSampleRequest On
    */

    public function get($params){

        $data = array();
        // 获取用户基本信息
        $user_params = array(
            'uc_code'=>$params['uc_code'],
            );
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $user_res = $this->invoke($apiPath,$user_params);
        if($user_res['status'] !== 0){
            return $this->endInvoke(null,$user_res['status']);
        }

        // 获取用户扩展信息
        $apiPath = "Base.UserModule.User.Basic.getExtendUserInfo";
        $user_extend_res = $this->invoke($apiPath,$user_params);
        if($user_extend_res['status'] !== 0){
            return $this->endInvoke(null,$user_extend_res['status']);
        }

        $data['user_info'] = array(
            'commercial_name'=>$user_extend_res['response']['commercial_name'],
            'name'=>$user_res['response']['real_name'],
            'mobile'=>$user_res['response']['mobile'],
            'create_time'=>date('Y-m-d',$user_res['response']['create_time']),
            );

        // 获取地址列表
        $apiPath = "Base.UserModule.Address.Address.lists";
        $address_res = $this->invoke($apiPath,$user_params);
        if($address_res['status'] !== 0){
            return $this->endInvoke(null,$address_res['response']['status']);
        }
        if($address_res['response']['lists']){
            $address_res['response']['lists'] = array_map(function($v){
                if($v['province'] == $v['city']){
                    $v['address'] = $v['city'].$v['district'].$v['address'];
                }else{
                    $v['address'] = $v['province'].$v['city'].$v['district'].$v['address'];
                }
                return $v;
            },$address_res['response']['lists']);
        }
        $data['address_lists'] = $address_res['response']['lists'];

        $params['register_time'] = $user_res['response']['create_time'];
        $months = $this->getMonth($params);     # 获取月份

        // 获取月份对应的金额 与有效订单
        $orders = array();
        foreach ($months as $k => $v) {
            $temp = array(
                'year'=>$v['year'],
                'month'=>$v['month'],
                'uc_code'=>$params['uc_code'],
                'sc_code'=>$params['sc_code'],
                );
            $apiPath = "Base.OrderModule.B2b.Statistics.oldmonth";
            $order_res = $this->invoke($apiPath,$temp);
            if($order_res['status'] !== 0){
                return $this->endInvoke(null,$order_res['status']);
            }
            $order_res['response']['month'] = $v['month'];
            $orders[] = $order_res['response'];
        }


        // 判断金钱的单位
        if(!empty($orders)){
            foreach ($orders as $k => $v) {
                $max = empty($max) ? $v['amount'] : ( $v['amount'] > $max ? $v['amount'] : $max );  # 金额最大值
                $min = empty($min) ? $v['amount'] : ($v['amount'] < $min ? $v['amount'] : $min);    # 金额最小值
            }

            if($max >= 1000){              # 获取单位
                $unit = "万元";
            }else{
                $unit = "元";
            }                     
            $indent = array();                          # 金额列表
            $num = array();                             # 订单列表

            foreach ($orders as $k => $v) {
                $price = app_get_price($v['amount'],$unit);                                     # 获取相应单位的金额
                $indent[] = array(
                    'amount'=>$price,
                    'unit'=>$unit,
                    'month'=>$v['month'],
                    );
                $num[] = array(
                    'num'=>$v['orders'],
                    'month'=>$v['month'],
                    );
            }
        }
        
        $data['order_max_price'] = app_get_price($max,$unit);
        $data['order_price_lists'] = $indent;
        $data['order_num_lists']  = $num;
        

        // 饼状图 商品列表 其他 商品列表
        $item_parmas = array(
            'sc_code'=>$params['sc_code'],
            'uc_code'=>$params['uc_code'],
            'year'=>$params['year'],
            'month'=>$params['month'],
            'pageSize'=>11,
            );
        $apiPath = "Base.OrderModule.B2b.Statistics.items";
        $items_res = $this->invoke($apiPath,$item_parmas);
        if($items_res['status'] !== 0){
            return $this->endInvoke(NULL,$items_res['status']);
        }
        $number = $items_res['response']['totalnum'];
        $lists = $items_res['response']['lists'];
        $num = 0;   # 饼状图数量
        foreach ($items_res['response']['lists'] as $k => $v) {
            $base_num = $items_res['response']['amount']*0.02;
            if(($v['amount'] > $base_num) && $k < 7){                   # 饼状图最多7个
                $num += 1;
            }else{
                break;
            }
        }


        if($number <= 7 ){         
            if($number==$num){
                $data['have_more'] = "NO";
                $data['item_lists'] = $lists;
            }else{
                $data['item_lists'] = array_slice($lists,0,$num);
                $data['have_more'] = "YES";
                $now_amount = array_sum(array_column($data['item_lists'],'amount'));
                $data['item_lists'][] = array(
                    'goods_name'=>'其他',
                    'amount'=>sprintf("%.2f",$items_res['response']['amount']-$now_amount),
                );
                $data['other_item_lists'] = array_slice($lists, $num);
            }
            $data['more'] = "NO";
            $data['item_percent_lists'] = array_column($data['item_lists'],'amount');
        }else{                              # 有更多无其余
            $data['more'] = $number <= 11 ?  "NO" : "YES";   # 是否可以拉伸
            $data['have_more'] = "YES";
            $data['item_lists'] = ($num == 7) ? array_slice($lists, 0,$num-1) : array_slice($lists, 0,$num);
            $now_amount = array_sum(array_column($data['item_lists'],'amount'));
            $data['item_lists'][] = array(
                'goods_name'=>'其他',
                'amount'=>sprintf("%.2f",$items_res['response']['amount']-$now_amount),
                );
            $data['item_percent_lists'] = array_column($data['item_lists'],'amount');
            $data['other_item_lists'] = ($num == 7) ? array_slice($lists,$num-1) : array_slice($lists, $num);
        }   

        return $this->endInvoke($data);
    }



    /**
     * 月份筛选
     * @access private
     * @author Todor
     */

    private function getMonth($params){
        $month = $params['month'];
        $year  = $params['year'];
        $register_time = $params['register_time'];
        $data = array();
        $data[] = array(
            'year'=>$year,
            'month'=>$month,
            );
        // 获取查询日期后的天数
        for ($i=0; $i < 2 ; $i++) {
            $month += 1;
            if($month > 12){
                $year = $year+1;
                $month = 1;
            }

            // 如果大于当前时间则结束
            $now = strtotime($year."-".$month);
            if($now > NOW_TIME){
                break;
            }
            $data[] = array(
                'year'=>$year,
                'month'=>$month,
                );
        }

        $month = $params['month'];
        $year  = $params['year'];
        $num = count($data);
        $last_num = bcsub(5,$num);      # 剩余几个月

        for ($i=0; $i < $last_num; $i++) { 
            $month -= 1;
            if($month < 1){
                $year = $year-1;
                $month = 12;
            }

            $now = strtotime($year."-".$month);
            $register_time = strtotime(date('Y-m',$register_time));
            if($now < $register_time){
                continue;
            }
            $data[] = array(
                'year'=>$year,
                'month'=>$month,
                );
        }

        // 重组 排序
        $years = array_column($data,'year');
        $months = array_column($data,'month');
        array_multisort($years,SORT_NUMERIC,SORT_ASC,$months,SORT_NUMERIC,SORT_ASC,$data);

        // 判断总体数量 再后补
        $year = end($data)['year'];
        $month = end($data)['month'];
        if(count($data) < 5){
             // 获取查询日期后的天数
            for ($i=0; $i < 2 ; $i++) {
                $month += 1;
                if($month > 12){
                    $year = $year+1;
                    $month = 1;
                }
                // 如果大于当前时间则结束

                $now = strtotime($year."-".$month);
                if($now > NOW_TIME){
                    break;
                }

                $data[] = array(
                    'year'=>$year,
                    'month'=>$month,
                    );
            }
        }


        return $data;
    }


   /**
    * @api  Boss版客户统计客户商品列表
    * @apiVersion 1.4.0
    * @apiName Bll.Boss.User.Customer.itemLists
    * @apiTransaction N
    * @apiAuthor Todor <tudou@liangrenwang.com>
    * @apiDate 2016-1-20
    * @apiSampleRequest On
    */
   public function itemLists($params){
        $apiPath = "Base.OrderModule.B2b.Statistics.items";
        $items_res = $this->invoke($apiPath,$params);
        if($items_res['status'] !== 0){
            return $this->endInvoke(NULL,$items_res['status']);
        }
        $data['pageTotalItem'] = $items_res['response']['totalnum'];
        $data['pageTotalNumber'] = $items_res['response']['total_page'];
        $data['lists'] = $items_res['response']['lists'];
        return $this->endInvoke($data);

   }











   










}

?>