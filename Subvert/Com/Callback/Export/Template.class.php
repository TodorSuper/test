<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 更换模板回调接口
 */
namespace Com\CallBack\Export;

use System\Base;

class Template extends Base {
    /**
     * Com.Callback.Export.Template.order
     * @param type $export_data
     */
    public function order(&$export_data){

        $str = "";
         foreach($export_data as $k=>$v){
             $order_goods = $this->orderGoods($v['order_goods']);
             $type = M('Base.OrderModule.B2b.Status.getPayType')->getPayType($v['pay_type']);
             $order_goods_count = $order_goods['count'];
             $order_goods = $order_goods['str'];
             $str .= "<tr>";
             $str .= "<td>{$v['b2b_code']}</td>";
             $str .= "<td>{$v['client_name']}</td>";
             $str .= "<td>{$v['commercial_name']}</td>";
             $str .= "<td colspan='10'>{$order_goods}</td>";
             $str .= "<td>{$v['total_nums']}</td>";
             $str .= "<td>{$v['coupon_amount']}</td>";
             $str .= "<td>{$v['real_amount']}</td>";
             $str .= "<td>{$v['cope_amount']}</td>";
             $str .= "<td>{$v['ship_message']}</td>";
             $str .= "<td>{$v['real_name']}</td>";
             $str .= "<td>{$v['mobile']}</td>";
             $str .= "<td>{$v['address']}</td>";
             $invite_from = $v['order_type'] == OC_ORDER_TYPE_STORE? '自有客户':'平台客户';
             $str .= "<td>{$invite_from}</td>";
             $str .= "<td>{$v['salesman']}</td>";
             $str .= "<td>{$v['channel']}</td>";
             $str .= "<td>{$v['create_time']}</td>";
             $str .= "<td>{$type}</td>";
             $str .= "<td>{$v['pay_message']}</td>";
             $str .= "<td>{$v['status']}</td>";
             
             $str .= "</tr>";
         }
         // var_dump($str);
        // var_dump($export_data);die();
        //    L($str); 
         return $str;
    }
    
    /**
     * Com.Callback.Export.Template.orderCms
     * @param type $export_data
     */
    public function orderCms(&$export_data){
        $str = "";
         foreach($export_data as $k=>$v){
             $order_goods = $this->orderGoods($v['order_goods']);
             $order_goods_count = $order_goods['count'];
             $order_goods = $order_goods['str'];
             $str .= "<tr>";
             $str .= "<td>{$v['store_name']}</td>";
             $str .= "<td>{$v['b2b_code']}</td>";
             $str .= "<td>{$v['commercial_name']}</td>";
             $str .= "<td>{$v['uc_code']}</td>";
             $str .= "<td>{$v['client_name']}</td>";
             $str .= "<td>{$v['phone']}</td>";
             $str .= "<td colspan='10'>{$order_goods}</td>";
             $str .= "<td>{$v['total_nums']}</td>";
             $str .= "<td>{$v['active_name']}</td>";
             $str .= "<td>{$v['active_code']}</td>";
             $str .= "<td>{$v['condition']}</td>";
             $str .= "<td>{$v['coupon_code']}</td>";
             $str .= "<td>{$v['total_real_amount']}</td>";
             $str .= "<td>{$v['coupon_amount']}</td>";
             $str .= "<td>{$v['cope_amount']}</td>";
             $str .= "<td>{$v['ship_message']}</td>";
             $str .= "<td>{$v['real_name']}</td>";
             $str .= "<td>{$v['mobile']}</td>";
             $str .= "<td>{$v['address']}</td>";
             $str .= "<td>{$v['salesman']}</td>";
             $str .= "<td>{$v['channel']}</td>";
             $str .= "<td>{$v['create_time']}</td>";
             $str .= "<td>{$v['pay_type']}</td>";
             $str .= "<td>{$v['pay_message']}</td>";
             $str .= "<td>{$v['pay_time']}</td>";
             $str .= "<td>{$v['pay_no']}</td>";
             $str .= "<td>{$v['order_type']}</td>";
             $str .= "<td>{$v['status']}</td>";
             
             $str .= "</tr>";
         }
        
         return $str;
    }
    
    private function orderGoods($order_goods){
        $str = "<table>";
        $count  = 0;
        foreach($order_goods as $k=>$v){
            $count ++ ;
            if(isset($v['gift_item']) && ($v['before_goods_price'] == $v['goods_price'])){
                $count ++;
                $gift_item = $v['gift_item'];
                $str .="<tr>";
                
                $str .= "<td>{$v['sic_no']}</td>";
                $str .= "<td>{$v['goods_name']}</td>";
                $str .= "<td>{$v['spec']}</td>";
                $str .= "<td>{$v['packing']}</td>";
                $str .= "<td>{$v['goods_price']}</td>";
                $str .= "<td>{$v['goods_price']}</td>";
                $str .= "<td>{$v['goods_number']}</td>";
                $str .= "<td></td>";
                $str .= "<td rowspan='2' >{$v['spc_code']}</td>";
                $str .= "<td rowspan='2' >{$v['rule']}</td>";
                
                $str .= "</tr>";
                
                $str .="<tr>";
                $str .= "<td>{$gift_item['sic_no']}</td>";
                $str .= "<td>{$gift_item['goods_name']}</td>";
                $str .= "<td>{$gift_item['spec']}</td>";
                $str .= "<td>{$gift_item['packing']}</td>";
                $str .= "<td>0.00</td>";
                $str .= "<td>0.00</td>";
                $str .= "<td>{$gift_item['goods_number']}</td>";
                $str .= "<td>赠</td>";
                $str .= "</tr>";
            }else if ($v['spc_type'] == 'SPECIAL'){
                $str .="<tr>";
                $str .= "<td>{$v['sic_no']}</td>";
                $str .= "<td>{$v['goods_name']}</td>";
                $str .= "<td>{$v['spec']}</td>";
                $str .= "<td> {$v['packing']}</td>";
                $str .= "<td>{$v['ori_goods_price']}</td>";
                $str .= "<td>{$v['goods_price']}</td>";
                $str .= "<td>{$v['goods_number']}</td>";
                $str .= "<td>特</td>";
                $str .= "<td>{$v['spc_code']}</td>";
                $str .= "<td>{$v['rule']}</td>";
                $str .= "</tr>";
             
            } else if (isset($v['gift_item']) && ($v['before_goods_price'] != $v['goods_price']) ){
                $count ++;
                $gift_item = $v['gift_item'];
                $str .="<tr>";
                
                $str .= "<td>{$v['sic_no']}</td>";
                $str .= "<td>{$v['goods_name']}</td>";
                $str .= "<td>{$v['spec']}</td>";
                $str .= "<td>{$v['packing']}</td>";
                $str .= "<td>{$v['ori_goods_price']}</td>";
                $str .= "<td>{$v['goods_price']}</td>";
                $str .= "<td>{$v['goods_number']}</td>";
                $str .= "<td></td>";
                $str .= "<td rowspan='2' >{$v['spc_code']}</td>";
                $str .= "<td rowspan='2' >{$v['rule']}</td>";
                
                $str .= "</tr>";
                
                $str .="<tr>";
                $str .= "<td>{$gift_item['sic_no']}</td>";
                $str .= "<td>{$gift_item['goods_name']}</td>";
                $str .= "<td>{$gift_item['spec']}</td>";
                $str .= "<td>{$gift_item['packing']}</td>";
                $str .= "<td>0.00</td>";
                $str .= "<td>0.00</td>";
                $str .= "<td>{$gift_item['goods_number']}</td>";
                $str .= "<td>赠</td>";
                $str .= "</tr>";
            } else {
                $str .="<tr>";
                $str .= "<td>{$v['sic_no']}</td>";
                $str .= "<td>{$v['goods_name']}</td>";
                $str .= "<td>{$v['spec']}</td>";
                $str .= "<td>{$v['packing']}</td>";
                $str .= "<td>{$v['ori_goods_price']}</td>";
                $str .= "<td>{$v['goods_price']}</td>";
                $str .= "<td>{$v['goods_number']}</td>";
                $str .= "<td></td>";
                $str .= "<td></td>";
                $str .= "<td></td>";
                $str .= "</tr>";
            }
            
            
        }
        $str .="</table>";
        unset($order_goods);
        return array('str'=>$str,'count'=>$count);
    }

    public function paymentList($data){
        $str = "";
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['fc_code']}</td>";
            $str .="<td>{$v['bank_code']}</td>";
            $str .="<td>{$v['amount']} </td>";
            if($v['pay_confirm']){
                $str .="<td><table border='1'>";
                foreach($v['pay_confirm'] as $k=>$v_c){
                    $str.="<tr>";
                    $str.="<td>{$v_c['b2b_code']}</td>";
                    $str.="<td>{$v_c['client_name']}</td>";
                    $str.="<td>{$v_c['commercial_name']}</td>";
                    $str.="<td>{$v_c['pay_method']}</td>";
                    $str.="<td>{$v_c['pay_time']}</td>";
                    $str.="<td>{$v_c['amount']}</td>";
                    $str.="<td>{$v_c['amount']}</td>";
                    $str.= "</tr>";
                }
                $str.="</table></td>";
            }
            $str .="<td>{$v['affirm_time']} </td>";
            $str .="<td>{$v['remark']} </td>";

            $str .= "</tr>";
        }
        $str .="</table>";
       return $str;

    }
    public  function  waitList($data){
//       'fc_code,sc_code,bank_code,amount,account_name,account_bank,account_number,remark';

        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['b2b_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['amount']} </td>";
            $str .="<td>{$v['oc_type']} </td>";
            $str .="<td>{$v['pay_method']} </td>";
            $str .="<td>{$v['account_name']} </td>";
            $str .="<td>{$v['account_bank']} </td>";
            $str .="<td>{$v['account_no']} </td>";
            $str .="<td>待生成</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }
    public  function  noPayList($data){
//        ('付款编号','商家名称','付款金额（元）','订单编号','订单金额（元）','开户名','开户行','银行账号','付款状态',);  //默认导出列标题
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['fc_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['amount']} </td>";
            if($v['pay_confirm']){
                $str .="<td><table border='1'>";
                foreach($v['pay_confirm'] as $k=>$v_c){

                    $str.="<tr>";
                    $str.="<td>{$v_c['b2b_code']}</td>";
                    if($v_c['oc_type']=="GOODS"){
                        $str.="<td>{$v_c['amount']}</td>";
                    }
                    if($v_c['oc_type']=="ADVANCE"){
                        $str.="<td>{$v_c['adv_amount']}</td>";
                    }
                    $str.="<td>{$v_c['oc_type_cn']}</td>";
                    $str.= "</tr>";
                }
                $str.="</table></td>";
            }
            $str .="<td>{$v['account_name']} </td>";//开户名
            $str .="<td>{$v['account_bank']} </td>";//开户行
            $str .="<td>{$v['account_number']} </td>";//银行账号
            $str .="<td>未付款</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }
    public  function  yesPayList($data){
        //array('付款编号','银行流水号','商家名称','付款金额（元）','订单编号','订单金额（元）','开户名','开户行','银行账号','备注','付款状态');  //默认导出列标题
        $total_amount = ($data['totalamount'])?$data['totalamount']:0;
        $start_time = ($data['start_time'])?$data['start_time']:'';
        $end_time = ($data['end_time'])?$data['end_time']:'';
        unset($data['totalamount']);
        unset($data['start_time']);
        unset($data['end_time']);
        $count = count($data);
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['affirm_time']}</td>";
            $str .="<td>{$v['fc_code']}</td>";
            $str .="<td>{$v['bank_code']}</td>";
            $str .="<td>{$v['sc_name']} </td>";
            $str .="<td>{$v['amount']} </td>";

            if($v['pay_confirm']){
                $str .="<td><table border='1'>";
                foreach($v['pay_confirm'] as $k=>$v_c){
                    $str.="<tr>";
                    $str.="<td>{$v_c['b2b_code']}</td>";
                    if($v_c['oc_type']=="GOODS"){
                        $str.="<td>{$v_c['amount']}</td>";
                    }
                    if($v_c['oc_type']=="ADVANCE"){
                        $str.="<td>{$v_c['adv_amount']}</td>";
                    }
                    $str.="<td>{$v_c['oc_type_cn']}</td>";
                    $str.= "</tr>";
                }
                $str.="</table></td>";
            }
            $str .="<td>{$v['account_name']} </td>";
            $str .="<td>{$v['account_bank']} </td>";
            $str .="<td>{$v['account_number']} </td>";
            $str .="<td>{$v['remark']} </td>";
            $str .="<td>已付款</td>";

            $str .= "</tr>";
        }
        $str .="</table>";
        $str .= "<table border='1'>";
        if($total_amount){
            $str.="<tr><td>已付款金额：{$total_amount}</td></tr>";
        }
        if($start_time&&$end_time){
            $str.="<tr><td>付款日期 {$start_time}--{$end_time}</td></tr>";
        }
        $str.="<tr> <td> 已付款笔数：{$count}</td></tr>";
        $str .="</table>";
        return $str;
    }

    /**财务点单 待点单列表导出
    */
    public  function  cNLists($data){
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['b2b_code']}</td>";
            $str .="<td>{$v['oc_code']}</td>";
            $str .="<td>{$v['remit_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            if(!empty($v['pay_time'])){
                $str .="<td>{$v['pay_time']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            $str .="<td>{$v['order_amount']} </td>";
            $str .="<td>{$v['amount']} </td>";
            $str .="<td>{$v['pay_method']} </td>";
            if(!empty($v['pay_no'])){
                $str .="<td>{$v['pay_no']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            if( $v['account_status'] == 'ACCOUNT') {
                $str .= "<td>{$v['account_amount']}</td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['coupon_amount']}</td>";
            if( $v['account_status'] == 'ACCOUNT') {
                if($v['pay_method']=='招商银行'){
                    $str .="<td>招商银行</td>";
                }else{
                    $str .="<td>民生银行</td>";
                }
                $str .="<td>{$v['bank_code']}</td>";
                $str .="<td>{$v['cost']}</td>";
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
                $str .="<td></td>";
            }
            $str .="<td>待点单</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }

    /**财务点单 已点单列表导出
     */
    public  function  cYLists($data){
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['b2b_code']}</td>";
            $str .="<td>{$v['oc_code']}</td>";
            $str .="<td>{$v['remit_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            if(!empty($v['pay_time'])){
                $str .="<td>{$v['pay_time']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            $str .="<td>{$v['order_amount']} </td>";
            $str .="<td>{$v['amount']} </td>";
            $str .="<td>{$v['pay_method']} </td>";
            if(!empty($v['pay_no'])){
                $str .="<td>{$v['pay_no']} </td>";
            }else{
                $str .="<td>-- </td>";
            }

            if( $v['account_status'] == 'ACCOUNT'){
                    $str .="<td>{$v['account_amount']}</td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['coupon_amount']}</td>";
            if($v['pay_method']=='招商银行'){
                $str .="<td>招商银行</td>";
            }else{
                $str .="<td>民生银行</td>";
            }
            if( $v['account_status'] == 'ACCOUNT'){
                $str .="<td>{$v['bank_code']}</td>";
                $str .="<td>{$v['cost']}</td>";
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
            }
            $str .="<td>{$v['update_time']} </td>";
            $str .="<td>已点单</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }

    /**预付款点单 待点单列表导出
     */
    public  function  aCLists($data){
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['b2b_code']}</td>";
            $str .="<td>{$v['oc_code']}</td>";
            $str .="<td>{$v['remit_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            if(!empty($v['pay_time'])){
                $str .="<td>{$v['pay_time']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            $str .="<td>{$v['amount']} </td>";
            $str .="<td>{$v['pay_method']} </td>";
            if(!empty($v['pay_no'])){
                $str .="<td>{$v['pay_no']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            if( $v['account_status'] == 'ACCOUNT'){
                if($v['pay_method'] == '先锋支付'){
                    $str .="<td>{$v['amount']}</td>";
                }else{
                    $str .="<td>{$v['account_amount']}</td>";
                }
                if($v['pay_method']=='招商银行'){
                    $str .="<td>招商银行</td>";
                }else{
                    $str .="<td>民生银行</td>";
                }
                $str .="<td>{$v['bank_code']}</td>";
                $str .="<td>{$v['cost']}</td>";
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
                $str .="<td></td>";
                $str .="<td></td>";
            }
            $str .="<td>未确认</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }

    /**预付款点单 已点单列表导出
     */
    public  function  aYLists($data){
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['b2b_code']}</td>";
            $str .="<td>{$v['oc_code']}</td>";
            $str .="<td>{$v['remit_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            if(!empty($v['pay_time'])){
                $str .="<td>{$v['pay_time']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            $str .="<td>{$v['amount']} </td>";
            $str .="<td>{$v['pay_method']} </td>";

            if(!empty($v['pay_no'])){
                $str .="<td>{$v['pay_no']} </td>";
            }else{
                $str .="<td>-- </td>";
            }

            if( $v['account_status'] == 'ACCOUNT'){
                if($v['pay_method'] == '先锋支付'){
                    $str .="<td>{$v['amount']}</td>";
                }else{
                    $str .="<td>{$v['account_amount']}</td>";
                }
            }else{
                $str .="<td></td>";
            }
            if($v['pay_method']=='招商银行'){
                $str .="<td>招商银行</td>";
            }else{
                $str .="<td>民生银行</td>";
            }

            if( $v['account_status'] == 'ACCOUNT'){
                $str .="<td>{$v['bank_code']}</td>";
                $str .="<td>{$v['cost']}</td>";
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
            }

            $str .="<td>{$v['update_time']} </td>";
            $str .="<td>已确认</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }

    /**财务点单订单所有数据导出
     */
    public function cNAllLists($data){
        $info = $data;
        $str = "<table border='1'>";
        $count  = '0';
        $amount = 0;//订单金额
        $adv_cost = 0;  //预计手续费金额
        $adv_get_amount = 0; //预计到账金额
        $get_amount = 0;//平台账户到账金额
        $cost_dif = 0; //手续费
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$count}</td>";
            $str .="<td>{$v['b2b_code']}</td>";
            $str .="<td>".number_format($v['order_amount'],2)."</td>";
                if(!empty($v['pay_time'])){
                    $str .="<td>{$v['pay_time']} </td>";
                }else{
                    $str .="<td>-- </td>";
                }
            if($v['pay_method'] == '银行转账'){
                $str .="<td>{$v['ext1']}</td>";
            }else{
                $str .="<td>{$v['pay_method']}</td>";
            }
            if( $v['pay_method'] == '微信支付'){
                $str .="<td>0.60%</td>";
            }elseif( $v['pay_method'] == '先锋支付'){
                $str .="<td>0.20%</td>";
            }else{
                $str .="<td>0.00%</td>";
            }
            $str .="<td>".$v['adv_cost']."</td>";
            $str .="<td>".number_format($v['adv_get_amount'],2)."</td>";
            $str .="<td>".number_format($v['get_amount'],2)."</td>";
            $str .= "<td>".number_format($v['coupon_amount'],2)."</td>";
            $str .="<td>".$v['cost_amount']."</td>";
            $str .="<td>".$v['cost_dif']."</td>";
            $str .="<td>".number_format($v['amount'],2)."</td>";
            if(!empty($v['pay_no'])){
                $str .="<td>{$v['pay_no']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            $str .="<td>{$v['remit_code']}</td>";
            $str .="<td>{$v['name']}</td>";
            $str .="<td>{$v['commercial_name']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['account_name']}</td>";
            $str .="<td>{$v['account_bank']}</td>";
            $str .="<td>{$v['account_no']}</td>";
            $str .= "</tr>";
        }

        foreach($info as $k=>$v){
            $amount += $v['amount'];
            $adv_cost += $v['adv_cost'];
            $adv_get_amount += $v['adv_get_amount'];
            $get_amount += $v['get_amount'];
            $cost_dif += $v['cost_dif'];
        }

        $str.="<tr>
                <td>合计</td>
                <td>--</td>
                <td style='color: red'>".number_format($amount,2)."</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td  style='color: red'>".number_format($adv_cost,2)."</td>
                <td  style='color: red'>".number_format($adv_get_amount,2)."</td>
                <td  style='color: red'>".number_format($get_amount,2)."</td>
                <td  style='color: red'>0.00</td>
                <td  style='color: red'>".number_format($cost_dif,2)."</td>
                <td  style='color: red'>".number_format($amount,2)."</td>
                </tr>";
        $str .="<tr><td colspan='20'>制表日期:".date('Y-m-d')."</td></tr>";
        $str .="</table>";
        return $str;
    }

    /**预付款订单所有数据导出
    */
    public function advanceAllLists($data){

        $info = $data;
        $str = "<table border='1'>";
        $count  = '0';
        $amount = 0;//订单金额
        $adv_cost = 0;  //预计手续费金额
        $adv_get_amount = 0; //预计到账金额
        $get_amount = 0;//平台账户到账金额
        $cost_dif = 0; //手续费

        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$count}</td>";
            $str .="<td>{$v['adv_code']}</td>";
            $str .="<td>".number_format($v['amount'],2)."</td>";
            if(!empty($v['pay_time'])){
                $str .="<td>{$v['pay_time']} </td>";
            }else{
                $str .="<td>-- </td>";
            }

            if($v['pay_method'] == '银行转账'){
                $str .="<td>{$v['ext1']}</td>";
            }else{
                $str .="<td>{$v['pay_method']}</td>";
            }
            if( $v['pay_method'] == '微信支付'){
                $str .="<td>0.60%</td>";
            }elseif($v['pay_method'] == '先锋支付'){
                $str .="<td>0.20%</td>";
            }else{
                $str .="<td>0.00%</td>";
            }
            $str .="<td>".$v['adv_cost']."</td>";
            $str .="<td>".number_format($v['adv_get_amount'],2)."</td>";
            $str .="<td>".number_format($v['get_amount'],2)."</td>";
            $str .="<td>".$v['cost_amount']."</td>";
            $str .="<td>".$v['cost_dif']."</td>";
            $str .="<td>".number_format($v['amount'],2)."</td>";

            if(!empty($v['pay_no'])){
                $str .="<td>{$v['pay_no']} </td>";
            }else{
                $str .="<td>-- </td>";
            }
            $str .="<td>{$v['remit_code']}</td>";
            $str .="<td>{$v['name']}</td>";
            $str .="<td>{$v['commercial_name']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['account_name']}</td>";
            $str .="<td>{$v['account_bank']}</td>";
            $str .="<td>{$v['account_no']}</td>";
            $str .= "</tr>";
        }

        foreach($info as $k=>$v){
            $amount += $v['amount'];
            $adv_cost += $v['adv_cost'];
            $adv_get_amount += $v['adv_get_amount'];
            $get_amount += $v['get_amount'];
            $cost_dif += $v['cost_dif'];
        }

        $str.="<tr>
                <td>合计</td>
                <td>--</td>
                <td style='color: red'>".number_format($amount,2)."</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td  style='color: red'>".number_format($adv_cost,2)."</td>
                <td  style='color: red'>".number_format($adv_get_amount,2)."</td>
                <td  style='color: red'>".number_format($get_amount,2)."</td>
                <td  style='color: red'>0.00</td>
                <td  style='color: red'>".number_format($cost_dif,2)."</td>
                <td  style='color: red'>".number_format($amount,2)."</td>
                </tr>";
        $str .="<tr><td colspan='20'>制表日期:".date('Y-m-d')."</td></tr>";
        $str .="</table>";
        return $str;
    }

    /**财务对账全部订单,商品订单导出
    */
    public  function  gALists($data){
        $str = "<table border='1'>";
        foreach($data as $k=>$v){
            $str .= "<tr>";
            $str .="<td>{$v['b2b_code']}</td>";
            if($v['pay_time']=='0'){
                $str .="<td>--</td>";
            }else{
                $str .="<td>".date('Y-m-d H:i:s',$v['pay_time'])."</td>";
            }
            $str .="<td>{$v['remit_code']}{$v['pay_no']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['pay_status']} </td>";
            $str .="<td>{$v['pay_method']} </td>";
            if($v['coupon_amount'] > 0){
                $str .="<td>".number_format($v['order_amout'],2)."</td>";
            }else{
                $str .="<td>".number_format($v['amount'],2)."</td>";
            }

            $str .="<td>".number_format($v['amount'],2)."</td>";
            //手续费及到账金额 已对账订单显示书续费和到账金额
            if( $v['account_status'] == FC_ACCOUNT_STATUS_ACCOUNT){
                if($v['pay_method']=='微信支付'){
                    $str.="<td>".number_format($v['cost'],2)."</td>";
                    $str .="<td>".number_format(($v['amount']-$v['cost']),2)."</td>";
                }elseif($v['pay_method']=='先锋支付'){
                    if($v['amount']>1000){
                        $str.="<td>".number_format($v['amount']*0.002,2)."</td>";
                    }else{
                        $str.="<td>2.00</td>";
                    }
                    $str .="<td>".number_format($v['amount'],2)."</td>";
                }else{
                    $str .="<td></td>";
                    $str .="<td>".number_format($v['amount'],2)."</td>";
                }
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
            }
            //优惠活动-优惠金额
            if($v['coupon_amount'] > 0){
                $str .="<td>".number_format($v['coupon_amount'],2)."</td>";
            }else{
                $str .="<td>--</td>";
            }
            //已点单和已对账订单显示到账银行
            if( $v['balance_status'] != '未到账'){
                if($v['pay_method'] == "微信支付" || $v['pay_method'] == "支付宝支付" || $v['pay_method'] == "先锋支付"){
                    $str .= "<td>民生银行</td>";
                }else{
                    $str .= "<td>{$v['ext1']}</td>";
                }

            }else{
                $str .="<td></td>";
            }
            //已对账订单显示到账流水号
            if( $v['account_status'] == FC_ACCOUNT_STATUS_ACCOUNT){
                $str .="<td>{$v['codeAndName']} </td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['balance_status']}</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }
    /**财务对账全部订单,预付款订单导出
     */
    public  function  aALists($data){
        $str = "<table border='1'>";
        foreach($data as $k=>$v){
            $str .= "<tr>";
            $str .="<td>{$v['adv_code']}</td>";
            if($v['pay_time']=='0'){
                $str .="<td>--</td>";
            }else{
                $str .="<td>".date('Y-m-d H:i:s',$v['pay_time'])."</td>";
            }
            $str .="<td>{$v['remit_code']}{$v['pay_no']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['status']} </td>";
            $str .="<td>{$v['pay_method']} </td>";
            $str .="<td>".number_format($v['amount'],2)."</td>";
            $str .="<td>".number_format($v['amount'],2)."</td>";
            //手续费及到账金额 已对账订单显示书续费和到账金额
            if( $v['account_status'] == FC_ACCOUNT_STATUS_ACCOUNT){
                if($v['pay_method']=='微信支付'){
                    $str.="<td>".number_format($v['cost'],2)."</td>";
                    $str .="<td>".number_format(($v['amount']-$v['cost']),2)."</td>";
                }elseif($v['pay_method']=='先锋支付'){
                    if($v['amount']>1000){
                        $str.="<td>".number_format($v['amount']*0.002,2)."</td>";
                    }else{
                        $str.="<td>2.00</td>";
                    }
                    $str .="<td>".number_format($v['amount'],2)."</td>";
                }else{
                    $str .="<td></td>";
                    $str .="<td>".number_format($v['amount'],2)."</td>";
                }
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
            }
            //已点单和已对账订单显示到账银行
            if( $v['balance_status'] != '未到账'){
                if($v['pay_method'] == "微信支付" || $v['pay_method'] == "支付宝支付" || $v['pay_method'] == "先锋支付"){
                    $str .= "<td>民生银行</td>";
                }else{
                    $str .= "<td>{$v['ext1']}</td>";
                }
            }else{
                $str .="<td></td>";
            }
            //已对账订单显示到账流水号
            if( $v['account_status'] == FC_ACCOUNT_STATUS_ACCOUNT){
                $str .="<td>{$v['bank_code']} </td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['balance_status']}</td>";
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }

    public function PaymentPaidList($data){
        $total_amount = ($data['totalamount'])?$data['totalamount']:0;
        unset($data['totalamount']);
        unset($data['start_time']);
        unset($data['end_time']);
        $count = count($data);
        $str = "<table border='1'>";
        $count  = 0;
        foreach($data as $k=>$v){
            $count ++;
            $str .= "<tr>";
            $str .="<td>{$v['fc_code']}</td>";
            $str .="<td>{$v['amount']}</td>";
            $str .="<td>-</td>";
            $str .="<td>{$v['bank_type']} </td>";
            $str .="<td >{$v['sc_name']}<br>{$v['account_name']}<br>{$v['account_bank']}<br>{$v['account_number']}</td>";
                $str .="<td><table border='1'>";
                foreach($v['orderLists'] as $ks=>$v_c){
                    $str.="<tr>";
                    $str.="<td>{$v_c['b2b_code']}</td>";
                    $str.="<td>{$v_c['amount']}</td>";
                    if ($val['pay_method'] = 'REMIT'){
                        $str.="<td>{$v_c['update_time']}<br>";
                        $str.="{$v_c['pay_method']}</td>";
                    }else{
                        $str.="<td>{$v_c['pay_time']}<br>";
                        $str.="{$v_c['pay_method']}</td>";
                    }
                    $str.="<td>{$v_c['cost']}</td>";
                    $str.="<td>{$v_c['bank_amount']}</td>";
                    if($v_c['coupon_amount'] > 0){
                        $str.="<td>{$v_c['coupon_amount']}</td>";
                    }



                    $str.="<td>{$v_c['ext1']}<br>{$v_c['codeAndName']}</td>";
                    $str.="<td>{$v_c['name']}<br>{$v_c['commercial_name']}</td>";
                    $str.="<td>{$v_c['oc_type']}</td>";
                    $str.="<td>{$v_c['confirm_name']}<br>{$v_c['update_time']}</td>";
                    $str.="<td>{$v_c['check_name']}<br>{$v_c['check_time']}</td>";
                    $str.="<td>{$v['create_name']}<br>";
                    if(!empty($v['create_time'])){
                        $str.= date("Y-m-d H:i:s",$v['create_time'])."</td>";
                    }
                    $str.="<td>{$v['affirm_name']}<br>";
                    if(!empty($v['affirm_name'])) {

                        $str.= date("Y-m-d H:i:s", $v['affirm_time']) . "</td>";
                    }
                    $str.="<td>已付款</td>";
                    $str.= "</tr>";
                }
                $str.="</table></td>";

            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }
    /**导出列表 财务审单&制单&付款 全部订单
    */
    public function aOLists($data){

        $i = 1;
        $str = "<table border='1'>";
        foreach($data as $k=>$v){
            $str .= "<tr>";
            $str .="<td>".$i++."</td>";
            $str .="<td>{$v['b2b_code']}</td>";
            if($v['oc_type']=='GOODS'){
                $str .="<td>".number_format($v['amount'],2)."</td>";
                if($v['b2b_pay_time']!='' && $v['b2b_pay_time']!='0'){
                    $str .="<td>".date('Y-m-d H:i:s',$v['b2b_pay_time'])."</td>";
                }else{
                    $str .="<td></td>";
                }
                if($v['pay_method']=='银行转账'){
                    $str .="<td>{$v['ext1']}</td>";
                }else{
                    $str .="<td>{$v['pay_method']}</td>";
                }
            }elseif($v['oc_type']=='预付款充值订单'){
                $str .="<td>".number_format($v['adv_amount'],2)."</td>";
                if($v['pay_time']!='' && $v['pay_time']!='0'){
                    $str .="<td>".date('Y-m-d H:i:s',$v['pay_time'])."</td>";
                }else{
                    $str .="<td></td>";
                }
                if($v['adv_pay_method']=='银行转账'){
                    $str .="<td>{$v['pay_method_ext1']}</td>";
                }else{
                    $str .="<td>{$v['adv_pay_method']}</td>";
                }
            }
            $str .="<td>{$v['order_status_info']}</td>";
            if($v['complete_time']!='' && $v['complete_time']!='0' && $v['order_status_info']=='已完成') {
                $str .= "<td>" . date('Y-m-d H:i:s', $v['complete_time']) . "</td>";
            }else{
                $str .="<td></td>";
            }
            if($v['balance_status']!=FC_BALANCE_STATUS_NO_BALANCE){
                if($v['pay_method'] == "微信支付" || $v['pay_method'] == "支付宝支付" || $v['pay_method'] == "先锋支付"){
                    $str .= "<td>民生银行</td>";
                }else{
                    $str .= "<td>{$v['ext1']}</td>";
                }
            }else{
                $str .="<td></td>";
            }
            if($v['account_status']==FC_ACCOUNT_STATUS_ACCOUNT){
                $str .="<td>{$v['codeAndName']}</td>";
		$str .="<td>{$v['pay_name']}</td>";
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
            }
            if($v['oc_type']=='GOODS'){
                $str .="<td>{$v['real_name']}</td>";
                $str .="<td>{$v['commercial_name']}</td>";
            }elseif($v['oc_type']=='预付款充值订单'){
                $str .="<td>{$v['adv_name']}</td>";
                $str .="<td>{$v['adv_c_name']}</td>";
            }
            if($v['account_status']=='ACCOUNT'){
                if($v['pay_method']=='先锋支付'){
                    if($v['oc_type']=='GOODS'){
                        if($v['amount']>1000){
                            $str .="<td>".number_format($v['amount']*0.002,2)."</td>";
                        }else{
                            $str .="<td>2.00</td>";
                        }
                        $str .="<td>".number_format($v['amount'],2)."</td>";
                    }
                    if($v['oc_type']=='预付款充值订单'){
                        if($v['adv_amount']>1000){
                            $str .="<td>".number_format($v['adv_amount']*0.002,2)."</td>";
                        }else{
                            $str .="<td>2.00</td>";
                        }
                        $str .="<td>".number_format($v['adv_amount'],2)."</td>";
                    }
                }else{
                    $str .="<td>{$v['cost']}</td>";
                    if($v['oc_type']=='GOODS'){
                        $str .="<td>".number_format(($v['account_amount']-$v['cost']),2)."</td>";
                    }
                    if($v['oc_type']=='预付款充值订单'){
                        $str .="<td>".number_format(($v['adv_amount']-$v['cost']),2)."</td>";
                    }
                }
            }else{
                $str .="<td></td>";
                $str .="<td></td>";
            }

            if($v['oc_type']=='GOODS'){

                $str .="<td>".number_format($v['coupon_amount'],2)."</td>";
                $str .="<td>".number_format($v['amount'],2)."</td>";
            }


            if($v['oc_type']=='预付款充值订单'){
                $str .="<td>".number_format($v['adv_amount'],2)."</td>";
            }

            $str .="<td></td>";
            if($v['pay_method'] == "微信支付" || $v['pay_method'] == "支付宝支付" || $v['pay_method'] == "先锋支付"){
                $str .= "<td>民生银行</td>";
            }else{
                $str .= "<td>{$v['ext1']}</td>";
            }
            $str .="<td>{$v['payment_bank_code']}</td>";
            $str .="<td>{$v['sc_name']}</td>";
            $str .="<td>{$v['account_name']}</td>";
            $str .="<td>{$v['account_bank']}</td>";
            $str .="<td>".rewrite($v['account_no'])."</td>";
            $str .="<td>{$v['order_type_info']}</td>";
            $str .="<td>{$v['confirm_name']}</td>";
            if($v['update_time']!='' && $v['update_time']!='0'){
                $str .="<td>".date('Y-m-d H:i:s',$v['update_time'])."</td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['check_name']}</td>";
            if($v['check_time']!='' && $v['check_time']!='0'){
                $str .="<td>".date('Y-m-d H:i:s',$v['check_time'])."</td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['create_name']}</td>";
            if($v['make_time']!='' && $v['make_time']!='0'){
                $str .="<td>".date('Y-m-d H:i:s',$v['make_time'])."</td>";
            }else{
                $str .="<td></td>";
            }
            $str .="<td>{$v['affirm_name']}</td>";
            if($v['affirm_time']!='' && $v['affirm_time']!='0'){
                $str .="<td>".date('Y-m-d H:i:s',$v['affirm_time'])."</td>";
            }else{
                $str .="<td></td>";
            }
            $str .= "</tr>";
        }
        $str .="</table>";
        return $str;
    }

}