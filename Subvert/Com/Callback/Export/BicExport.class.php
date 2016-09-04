<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 交易中心 导出  回调接口
 */

namespace Com\CallBack\Export;

use System\Base;

class BicExport extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
  //买家数据统计列表
    public function userList(&$userInfo,$params){
        $params=$params['params'];
//        var_dump($params);exit;
        $userInfo=changeArrayIndex($userInfo,'uc_code');
        $uc_code=array_column($userInfo,'uc_code');
        if(!$uc_code){
            $userInfo=array();
        }

        //得到每个商家的在查询时间内的创建订单数
        $params['uc_code']=$uc_code;
        $apiPath='Base.BicModule.Uc.Data.createOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['create_order_info']=$call['response'];
        //得到每个商家在查询时间内取消的订单数
        $apiPath='Base.BicModule.Uc.Data.cancelOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['cancel_order_info']=$call['response'];
//        var_dump($info['cancel_order_info']);exit;
        //得到每个商家在查询时间内付款的订单数
        $apiPath='Base.BicModule.Uc.Data.payOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['pay_order_info']=$call['response'];
//var_dump($info['pay_order_info']);exit;
        //得到查询时间内的新增成单
        $apiPath='Base.BicModule.Uc.Data.completeOrder';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['complete_order_info']=$call['response'];
        //得到每个用户最后的成单时间
        $apiPath='Base.BicModule.Uc.Data.lastTime';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $info['last_time_info']=$call['response'];
//var_dump($info['complete_order_info']);exit;
        foreach($userInfo as $key=>$val){
            if($info['create_order_info'][$key]['order_num']){
                $userInfo[$key]['order_num']=$info['create_order_info'][$key]['order_num'];
            }else{
                $userInfo[$key]['order_num']=0;
            }
            if($info['cancel_order_info'][$key]['cancel_num']){
                $userInfo[$key]['cancel_num']=$info['cancel_order_info'][$key]['cancel_num'];
            }else{
                $userInfo[$key]['cancel_num']=0;
            }
            if($info['pay_order_info'][$key]['pay_num']){
                $userInfo[$key]['pay_num']=$info['pay_order_info'][$key]['pay_num'];
                $userInfo[$key]['pay_total_amount']=$info['pay_order_info'][$key]['pay_total_amount'];
                $userInfo[$key]['no_advance_pay']=$info['pay_order_info'][$key]['no_advance_pay'];
                $userInfo[$key]['unit_price']=round($info['pay_order_info'][$key]['pay_total_amount']/$info['pay_order_info'][$key]['pay_num'],2);
                $userInfo[$key]['average_pay_time']=round($info['pay_order_info'][$key]['average_pay_time']/60,1);
            }else{
                $userInfo[$key]['pay_num']=0;
                $userInfo[$key]['unit_price']='--';
                $userInfo[$key]['no_advance_pay']=0;
                $userInfo[$key]['pay_total_amount']=0;
                $userInfo[$key]['average_pay_time']='--';
            }
            $userInfo[$key]['link_data']=$info['complete_order_info'][$key]['link_data'];
            $userInfo[$key]['complete_order_num']=$info['complete_order_info'][$key]['complete_order_num'];
            $userInfo[$key]['complete_order_amount']=$info['complete_order_info'][$key]['order_amount'];
            if($info['last_time_info'][$key]['last_time']){
                $userInfo[$key]['last_time']=$info['last_time_info'][$key]['last_time'];
            }else{
                $userInfo[$key]['last_time']=$info['last_time_info'][$key]['last_time'];
            }
        }
//var_dump($userInfo);exit;
        $change=$userInfo;
        $userInfo=array();
        foreach($change as $key=>$val){
            if($val['invite_from'] == 'SC'){
                $invite_from = '买家';
            }
            if($val['invite_from'] == 'UC'){
                $invite_from = '平台商城买家';
            }
           $userInfo[]=array(
               'username'=>$val['commercial_name'],
               'invite_from'=>$invite_from,
               'name'=>$val['name'],
               'mobile'=>$val['mobile'],
               'uc_code'=>$val['uc_code'],
               'store_name'=>$val['store_name'],
               'create_time'=>$val['create_time'] ? date('Y-m-d H:i:s',$val['create_time']) : '',
               'login_time'=>$val['login_time'] ? date('Y-m-d H:i:s',$val['login_time']) : '',
               'order_num'=>$val['order_num'],
               'cancel_num'=>$val['cancel_num'],
               'pay_num'=>$val['pay_num'],
               'pay_total_amount'=>$val['pay_total_amount'],
               'no_advance_pay'=>$val['no_advance_pay'],
               'unit_price'=>$val['unit_price'],
               'pay_success_rate'=>'66%',
               'average_pay_time'=>$val['average_pay_time'],
               'complete_order_num'=>$val['complete_order_num'],
               'complete_order_amount'=>$val['complete_order_amount'],
               'link_rate'=>$val['link_data'],
               'last_time'=>$val['last_time'] ? date('Y-m-d H:i:s',$val['last_time']) : '',
           );
        }
//        var_dump($userInfo);exit;
    }

    public function advanceList(&$advanceList, $params){
        $ucMemberApi     = "Base.BicModule.Tc.AdvanceStatistic.ucMemberInfo";
        $ucMemberInfoRes = $this->invoke($ucMemberApi);
        $ucMemberInfo    = $ucMemberInfoRes['response'];

        $ucMerchantApi     = "Base.BicModule.Tc.AdvanceStatistic.ucMerchantInfo";
        $ucMerchantInfoRes = $this->invoke($ucMerchantApi);
        $ucMerchantInfo    = $ucMerchantInfoRes['response'];

        $scSalesmanApi     = "Base.BicModule.Tc.AdvanceStatistic.scSalesmanInfo";
        $scSalesmanInfoRes = $this->invoke($scSalesmanApi);
        $scSalesmanInfo    = $scSalesmanInfoRes['response'];

        $storeApi      = "Base.BicModule.Oc.Statistic.storesInfo";
        $storesInfoRes = $this->invoke($storeApi);
        $storesInfo    = $storesInfoRes['response'];

        if (!empty($advanceList)) {
            foreach ($advanceList as $key => $advace) {
                    if ($ucMemberInfo[$advace['code']]) {
                        $advanceList[$key]['commercial_name'] = $ucMemberInfo[$advace['code']]['commercial_name'];
                        $advanceList[$key]['invite_code']     = $ucMemberInfo[$advace['code']]['invite_code'];        
                    }else{
                        $advanceList[$key]['commercial_name'] = '';     
                    }

                    if ($scSalesmanInfo[$advanceList[$key]['invite_code']]) {
                        $advanceList[$key]['invite_person'] = $scSalesmanInfo[$advanceList[$key]['invite_code']]['name'];
                    }

                    $recharge = $advanceList[$key]['total_balance'] - $advanceList[$key]['free_balance'];
                    $advanceList[$key]['recharge'] = round($recharge, 2);
                    $advanceList[$key]['create_time'] = date('Y.m.d H:i:s', $advanceList[$key]['create_time']);
            }
        }
        // var_dump($advanceList);die();
        $arr = array();
        foreach ($advanceList as $key => $advace) {
            // var_dump($advace);
            $turn = array();
            $turn['id']              = $advace['id'];
            $turn['commercial_name'] = $advace['commercial_name']."<br/>".$advace['uc_name']."<br/>".$advace['uc_code'];
            $turn['create_time']     = $advace['create_time'];
            $turn['recharge_times']  = $advace['recharge_times'];
            $turn['total_balance']   = $advace['total_balance']."元";
            $turn['recharge']        = $advace['recharge']."元";
            $turn['free_balance']    = $advace['free_balance']."元";
            $turn['invite_person']   = $advace['invite_person'];
            $turn['name']            = $advace['name']."<br/>".$advace['sc_code'];
            $turn['salesman']        = $advace['salesman'];
            $arr[] = $turn;
        }
        // var_dump($arr);die();
        $advanceList = $arr;
    }

    public function itemList(&$itemList, $params){

        $turn = array();
        if (!empty($itemList)) {
            foreach ($itemList as $key => $item) {
                $arr = array();
                $arr['id']         = $item['id'];
                $arr['store_name'] = $item['store_name'];
                $arr['item_num']   = $item['item_num'];
                $arr['on_num']     = $item['on_num'];
                $arr['off_num']    = $item['off_num'];
                $turn[] = $arr;
            }
        }

        $itemList = $turn;
    }

    public function orderList(&$orderList, $params){
        $params = $params['params'];
        $storeApi      = "Base.BicModule.Oc.Statistic.storesInfo";
        $storesInfoRes = $this->invoke($storeApi);
        $storesInfo    = $storesInfoRes['response'];

        $customersApi     = "Base.BicModule.Oc.Statistic.customersInfo";
        $customersInfoRes = $this->invoke($customersApi);
        $customersInfo    = $customersInfoRes['response'];

        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $averData = $this->getLastMonth($params['start_time']);
        }

        if (!empty($params['pay_start_time']) && !empty($params['pay_end_time'])) {
            $averData = $this->getLastMonth($params['pay_start_time']);
        }

        if ($averData) {
            $aveMonthApi         = "Base.BicModule.Oc.Statistic.averageMonthTime";
            $averageMonthTimeRes = $this->invoke($aveMonthApi, $averData);
            $averageMonthTime    = $averageMonthTimeRes['response'];
        }
        if (!empty($orderList)) {
            foreach ($orderList as $key => $order) {
                if ($storesInfo[$order['sc_code']]) {
                    $orderList[$key]['storeName'] = $storesInfo[$order['sc_code']]['name'];
                }else{
                    $orderList[$key]['storeName'] = '';
                }

                if ($customersInfo[$order['uc_code']]) {
                    $orderList[$key]['customerName'] = $customersInfo[$order['uc_code']]['name'];
                }else{
                    $orderList[$key]['customerName'] = '';
                }

                if ($orderList[$key]['pay_time']) {
                    $time = $orderList[$key]['pay_time'] - $orderList[$key]['create_time'];

                    $orderList[$key]['payTimeLong'] = $this->timeLong($time);
                }else{
                    $orderList[$key]['payTimeLong'] = '';
                }

                if ($order['pay_type'] === 'ONLINE') {
                    if ($orderList[$key]['ship_time'] && $orderList[$key]['pay_time']) {
                        $time = $orderList[$key]['ship_time'] - $orderList[$key]['pay_time'];
                        $orderList[$key]['shipTimeLong'] = $this->timeLong($time);
                    }else{
                        $orderList[$key]['shipTimeLong'] = '';
                    }
                }else{
                    if ($orderList[$key]['ship_time']) {
                        $time = $orderList[$key]['ship_time'] - $orderList[$key]['create_time'];
                        $orderList[$key]['shipTimeLong'] = $this->timeLong($time);
                    }else{
                        $orderList[$key]['shipTimeLong'] = '';
                    }
                }

                if ($averageMonthTime) {
                    $orderList[$key]['average_pay_time']  = $this->timeLong($averageMonthTime['average_pay_time']);
                    $orderList[$key]['average_ship_time'] = $this->timeLong($averageMonthTime['average_ship_time']);
                }else{
                    $orderList[$key]['average_pay_time']  = '--';
                    $orderList[$key]['average_ship_time'] = '--';
                }
                $orderList[$key]['pay_time']    = empty($order['pay_time'])? '' : date("Y.m.d H:i:s", $order['pay_time']);
                
                $orderList[$key]['create_time'] = empty($order['create_time'])? '' : date("Y.m.d H:i:s", $order['create_time']);
                
                $orderList[$key]['ship_time']  = empty($order['ship_time'])? '' : date("Y.m.d H:i:s", $order['ship_time']);

            }
        }
        // die();
        $arr = array();
        foreach ($orderList as $key => $order) {
            $turn = array();
            $turn['b2b_code']           = $order['b2b_code'];
            $turn['storeName']          = $order['storeName'];
            $turn['customerName']       = $order['customerName'];
            $turn['create_time']        = $order['create_time'];
            $turn['status_message']     = $order['status_message'];
            $turn['real_amount']        = $order['real_amount'];
            $turn['pay_type_message']   = $order['pay_type_message'];
            $turn['pay_method_message'] = $order['pay_method_message'];
            $turn['pay_time']           = $order['pay_time'];
            $turn['payTimeLong']        = $order['payTimeLong'];
            $turn['average_pay_time']   = $order['average_pay_time'];
            $turn['ship_time']          = $order['ship_time'];
            $turn['shipTimeLong']       = $order['shipTimeLong'];
            $turn['average_ship_time']  = $order['average_ship_time'];
            $arr[] = $turn;
        }
        $orderList = $arr;
    }

    /**
     * 付款和发货，时长计算
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function timeLong($time){
        $time = round($time/60, 2);
        return $time.'分钟';
    }

    /**
     * 获取上个月
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function getLastMonth($params){
        if (empty($params)) {
            $params = NOW_TIME;
        }
        $firstday  = date("Y-m-01", $params);
        $lastMonth = date("m",strtotime("$firstday -1 month"));
        $year      = date('Y', $params);
        $res = array(
            'year'  => $year,
            'month' => $lastMonth,
            );
        return $res;
    }

    //卖家数据统计
    public function storeList(&$storeInfo,$params){
//        echo '3232';exit;
//        var_dump($storeInfo);exit;
        $params=$params['params'];
        $storeInfo=changeArrayIndex($storeInfo,'sc_code');
        $sc_code=array_column($storeInfo,'sc_code');
        $params['sc_code']=$sc_code;
        if(!$sc_code){
            $storeInfo=array();
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
         $last_store=$storeInfo;
        $storeInfo=array();
        foreach($last_store as $key=>$val){
            $storeInfo[]=array(
                'name'=>$val['name'],
                'linkman'=>$val['linkman'],
                 'phone'=>$val['phone'],
                'create_time'=>$val['create_time'] ? date('Y-m-d H:i:s',$val['create_time']) : '',
                'login_time'=>$val['login_time'] ? date('Y-m-d H:i:s',$val['login_time']) : '',
                'total_user_num'=>$val['total_user_num'],
                'new_user_num'=>$val['new_user_num'],
                'pay_user_num'=>$val['pay_user_num'],
                'total_pay_amount'=>$val['total_pay_amount'],
                'plat_pay_user_num'=>$val['plat_pay_user_num'],
                'average'=>$val['pay_user_num'] ? round($val['total_pay_amount']/$val['pay_user_num'],2) : '--',
                'no_advance_amount'=>$val['no_advance_amount'],
                'unship_num'=>$val['unship_num'],
                'merchant_cancel_num'=>$val['merchant_cancel_num'],
                'shipped_num'=>$val['shipped_num'],
                'ship_rate'=>$val['ship_rate'],
                'store_average_ship_time'=>$val['store_average_ship_time'],
                'average_ship_time'=>$val['average_ship_time'],
                'complete_num'=>$val['complete_num'],
                'total_complete_amount'=>$val['total_complete_amount'],
                'link_rate'=>$val['link_rate'],
                'complete_last_time'=>$val['complete_last_time'] ? date('Y-m-d H:i:s',$val['complete_last_time']) : ''
            );
        }
    }
}
?>
