<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 自定义公共函数
 */

/**
 * 
 * 验证传入参数数组不能为空
 * @param type $input  传入的数组
 * @return boolean
 */
function checkArrayInput($input) {
    if (!is_array($input) && empty($input)) {
        return false;
    }
    return true;
}
function StrlenStr($str) {
  
    preg_match_all('/./us', $str, $match);
    return count($match[0]); 
}

/**
 * 后台编辑商品的时候显示图片
 */
function show_lazy_load($content) {
    $content = htmlspecialchars_decode($content);
    $content = str_replace('data-original', 'src', $content);
    $content = str_replace('src="/static/images/transparent.gif"', '', $content);
    $content = str_replace('class="placeholder"', '', $content);
    $content = htmlspecialchars($content);
    return $content;
}

/**
 * 两个数组对应 相乘  ,可以选择是否需要返回总和
 * @param type $array1  不能为空
 * @param type $array2
 * @param type $is_sum  如果 true 如果返回总和，否则返回交叉相乘的数组  ，数组的大小跟 array1一样 
 */
function array_product_sum($array1, $array2, $is_sum = TRUE) {
    $arr = array();
    if (empty($array1)) {
        return FALSE;
    }
    foreach ($array1 as $k => $v) {
        $arr[] = $v * $array2[$k];
    }

    if ($is_sum) {

        return array_sum($arr);
    }
    return $arr;
}

function upload_cloud($filePath) {
    $aliyun = new \Library\AliyunUpload();
    $url = $aliyun->putFile($filePath);
//		unlink($filePath);
    return $url;
}

function update_price($list) {
    if ( ( $list['pay_type'] == PAY_TYPE_COD || $list['pay_type'] == PAY_TYPE_TERM) && $list['ship_status'] == OC_ORDER_SHIP_STATUS_UNSHIP && $list['order_status']!=OC_ORDER_ORDER_STATUS_CANCEL &&$list['order_status']!=OC_ORDER_ORDER_STATUS_MERCHCANCEL && $list['order_status']!=OC_ORDER_ORDER_STATUS_OVERTIMECANCEL ) {
        return true;
    }
    if ( $list['pay_type'] == PAY_TYPE_ONLINE && $list['pay_status'] == TC_PAY_VOUCHER_UNPAY && $list['order_status']!=OC_ORDER_ORDER_STATUS_CANCEL &&$list['order_status']!=OC_ORDER_ORDER_STATUS_MERCHCANCEL && $list['order_status']!=OC_ORDER_ORDER_STATUS_OVERTIMECANCEL ) {
        return true;
    }
  return false;
}

/**
 * 装换数组索引
 * @param type $array
 * @param type $index
 */
function changeArrayIndex($array, $index) {
    if (empty($array)) {
        return $array;
    }
    $temp = array();
    foreach ($array as $k => $v) {
        $temp[$v[$index]] = $v;
    }
    unset($array);
    return $temp;
}

/**
 * 促销规则解析
 */
function spcRuleParse($type, $data) {
    if (empty($type) || empty($data)) {
        return false;
    }
    $message='';
    switch ($type) {
        case SPC_TYPE_GIFT:
            $rule = $data['rule'];
            $rule = is_array($rule) ? $rule : json_decode($rule,true);
            foreach ($rule as $key => $value) {
                if(!empty($value[0])){
                    $message .= "满{$value[0]}赠{$value[1]} ";
                }   
            }
            break;
        case SPC_TYPE_SPECIAL:
            $platform_flag = $data['platform_flag'];
            $platform_flag = empty($platform_flag) ? POP : $platform_flag;
            $ori_price = $data['ori_price'];
            $discount = $data['discount'];
            $special_type = empty($data['special_type']) ? SPC_SPECIAL_FIXED : $data['special_type'];
            
            if($platform_flag == POP){
                $message .="优惠价:".$data['special_price'].'元';
            }else if($platform_flag == B2B){
                if($special_type == SPC_SPECIAL_FIXED){

                    $save = sprintf("%.2f", $ori_price - $data['special_price']);
                    $message .= "平台专享，每".$data['packing']."立减{$save}元";
                }else if($special_type == SPC_SPECIAL_REBATE){
                    $discount = floatval($discount);
                    $message = "平台专享，{$discount}折特惠";
                }
                
            }
            break;
        case SPC_TYPE_LADDER:
            $platform_flag = $data['platform_flag'];
            $platform_flag = empty($platform_flag) ? POP : $platform_flag;
            $rule=$data['rule'];
            $rule=is_array($rule) ? $rule : json_decode($rule,true);
            foreach ($rule as $k => $v) {           # 去除为为空的选项
                if(empty($v[0])){
                    unset($rule[$k]);
                }
            }
            if($platform_flag == POP){
                $arr=end($rule);
                $num=$arr[0]-1;
                $message="购买量区间:"."<br/>";
                $message2='';
                foreach($rule as $key=>$val){
                    if($val[0] && $val[1] && $val[0]!=$arr[0]){
                        if($data['need_type'] == "table"){
                            $message2 .= "购买量区间:".$val[0].'-'.$val[1].',￥'.$val[2].'<br/>';
                        }else{
                            $message .=$val[0].'-'.$val[1].',￥'.$val[2].'<br/>';
                        }
                    }
                }

                if($data['need_type'] == "table"){
                    $message2 .="购买量区间:"."大于".$num.',￥'.$arr[2];
                }else{
                    $message .="大于".$num.',￥'.$arr[2];
                }
                if($data['need_type'] == "table"){
                    $message=$message2;
                }
            }else if ($platform_flag == B2B){
                $num = count($rule);
                foreach($rule as $key=>$val){
                    if($key < $num-1){
                        if($val[0] && $val[1]){
                            $money = $data['price'] - $val[2];
                            $message .= $val[0].'-'.$val[1].$data['packing'].'，每'.$data['packing'].'优惠'.$money.'元<br/>';
                        }
                    }else{
                        $number=$val[0]-1;
                        $money = $data['price'] - $val[2];
                        $message .= $number.$data['packing'].'以上，每'.$data['packing'].'优惠'.$money.'元';
                    }
                }
                $message = rtrim($message,',');
            }
    }
    return $message;
}


function getGiftNums($goods_number,$rule){
    $rule = !is_array($rule) ? json_decode($rule,TRUE): $rule;
        if($goods_number <= 0){
            return 0;
        }
        //按购买力度从大到小排序
         $rule = reOrder($rule);
         //计算赠品数量  
         $total_gift_num = 0;
         foreach($rule as $k=>$v){
             if($goods_number < $v[0]){
                 continue;
             }
             $times = floor($goods_number/$v[0]);
             
             $total_gift_num += $times * $v[1];
             $goods_number = $goods_number%$v[0];
         }
         return $total_gift_num;
}

/**
 * 排序
 * @param type $rule
 * @return type
 */
 function reOrder($rule){
        $newRule = array();
        $sales_gift_rule = array_column($rule, 0);
        arsort($sales_gift_rule,SORT_NUMERIC);
        foreach ($sales_gift_rule as $k => $v) {
            $newRule[] = $rule[$k];
        }
        return $newRule;
    }


/**
 * 获取  促销商品类型
 * @param  type  类型参数
 * @return char  具体类型
 * @author Todor 
 */

function get_spc($type){

    switch ($type) {
        case SPC_TYPE_GIFT:
            return '满赠';
            break;
        case SPC_TYPE_SPECIAL:
            return "特价";
            break;
        case SPC_TYPE_LADDER:
            return "阶梯价";
            break;
    }
    
}


/**
 * 计算促销商品的真是金额
 * @param type $goods_info
 */

function caculateItemAmount($item) {
    if (isset($item['spc_info']) && $item['spc_info']['type'] == 'SPECIAL') {
        //特价促销活动
        $spc_info = $item['spc_info'];
        if ($spc_info['max_buy'] == 0) {
            //不限购
            $itemPrice = $spc_info['spc_detail']['special_price'] * $item['number'];
        } else {
            //限购
            $have_buy = $item['have_buy'];
            if ($have_buy >= $spc_info['max_buy']) {
                //已经买超了  则按原价算
                $itemPrice = $item['price'] * $item['number'];
            } else {
                //没有买超  则有可能要按两部分算
                $last_spc_number = $spc_info['max_buy'] - $have_buy;  //剩余可以享受促销的购买数量
                if ($last_spc_number <= $item['number']) {    //剩余可以购买的促销数量比  购买的数量小
                    //买超
                    $itemPrice = $last_spc_number * $spc_info['spc_detail']['special_price'] + ($item['number'] - $last_spc_number) * $item['price'];
                } else {
                    //没有买超
                    $itemPrice = $spc_info['spc_detail']['special_price'] * $item['number'];
                }
            }
        }
    } else if(isset($item['spc_info']) && $item['spc_info']['type'] == 'LADDER'){
        $goods_price = getLadderPrice($item['spc_info']['spc_detail']['rule'],$item['number'],$item['price']);  //获取阶梯价价格
        $itemPrice = $goods_price * $item['number'];              # 购买数小于阶梯价最小值的情况
    } else {
        $itemPrice = $item['price'] * $item['number'];
    }
    unset($item);
    return $itemPrice;
}




/**
 * 获取阶梯价的价格
 */
function getLadderPrice($rule,$goods_number,$ori_goods_price){
    
    $rule = is_array($rule) ? $rule : json_decode($rule,TRUE);
    foreach ($rule as $k => $v) {                               # 满足规则并且在范围内
        if( (empty($v[1]) && $v[0] <= $goods_number ) || ($v[0] <= $goods_number && $goods_number <= $v[1])){
            return $v[2];
        }
    }
    $last_rule = $rule[count($rule)-1];                         # 购买数大于阶梯价最大值的情况 
    if(!empty($last_rule[1]) && $goods_number >= $last_rule[1]){
        return $last_rule[2];
    }
    return $ori_goods_price;
}


/**
 * 更改金钱类型
 * @return char  返回钱数
 * @author Todor 
 */

function change_price($param){

    $end = substr($param, -3);
    $head = intval($param);
    $head = strrev($head);
    $head = str_split($head,4);
    $head = implode('.', $head);
    $head = strrev($head);
    $str = $head.$end;
    return $str;
}
/**
 *  改变银行卡格式
 * @return    string
 * @author wangguangjian
 */

function rewrite($str){
    $arr=str_split($str,4);//4的意思就是每4个为一组
    $str=implode(' ',$arr);
    return $str;
}


/**
 * BOSS 根据金额改变形式
 * @return array  返回 金钱与单位
 * @author Todor
 */

function app_change_price($params){

    if($params < 100000){
        $res = array(
            'amount'=>$params,
            'unit'=>'元',
            );
        
    }elseif($params < 100000000){
        $params = (int)$params;
        $params = strrev($params);
        $params  = str_split($params,4);
        $params = implode('.', $params);
        $params = strrev($params);
        $params = substr($params, 0,-3);
        $res = array(
            'unit'=>'万元',
            'amount'=>$params,
            );
    }else{
        $params = (int)$params;  
        $params = strrev($params);
        $params  = str_split($params,4);
        $params = implode('.', $params);
        $params = strrev($params);
        $params = substr($params, 0,-8);
        $res = array(
            'unit'=>'亿元',
            'amount'=>$params,
            );
    }
    return $res;
}

/**
 * BOSS 根据单位改变金额
 * @return array  返回 金额
 * @author Todor
 */

function app_get_price($amount,$unit){

    if($unit == '元'){
        $res = $amount;
    }elseif($unit == '万元'){
        $amount = (int)$amount;
        $res = bcdiv($amount,10000,2);
        $res = ($res == 0.00) ? 0 : $res;
    }
    return $res;
}



/**
 * DESC   : 格式化数字的输出
 * AUTHOR : heweijun@liangrenwang.com
 */
function app_change_price_new($params){
    return array('amount' => number_format($params, 2));
}

/**
 * DESC   :  Boss 获取某一年， 1月份到 当前月份的所有的开始日期跟结束日期
 * AUTHOR :  heweijun
 * DATE   : 2015-12-1
 * PARAMS : ARRAY
 *          string year, string sort 排序方式 DESC／ASC
 */
function getPassedMonthByYear($params){
    $year     = $params['year'];
    $sort     = $params['sort'];
    $i        = $year < date('Y') ? 12 : date('m');  //如果输入的年份小于当前的年份，将是获取某年的数据值
    $n        = !empty($params['num']) ? $params['num'] : 12;  //默认取两个月的值
    $res=array();
    #part1
//  $nn = $n;
    for($m=$i;$m>=1; $m--){
        if($n == 0) continue;
        $timestamp = getStartToEndTimestampByMonthAndYear($m, $year);
        extract($timestamp);
        $sortkey = $start_time;
        $res["$sortkey"] = array(
            'year'       => $year,
            'month'      => $m,
            'start_time' => $start_time,
            'end_time'   => $end_time,
        );
        $n--;
    }

    //计算取得的月数，不足月数则继续向上取，补足所需的月数
    #part2  补足数据
//    $currentResNum = count($res);
//    $needNum = $nn - $currentResNum;
//    if($needNum > 0){
//        for($m = 12;$m>=1;$m--){
//            if($needNum == 0) continue;
//            $timestamp = getStartToEndTimestampByMonthAndYear($m, $year-1);
//            extract($timestamp);
//            $sortkey = $start_time;
//            $res["$sortkey"] = array(
//                'year'       => $year-1,
//                'month'      => $m,
//                'start_time' => $start_time,
//                'end_time'   => $end_time,
//            );
//            $needNum--;
//        }
//    }
    $sort == 'DESC' ? $res : ksort($res);
    return $res;
}

/*
 * DESC   : 根据某年某月获取当前月的开始于结束的时间戳
 * AUTHOR : heweijun@liangrenwang.com
 */
function getStartToEndTimestampByMonthAndYear($m, $year){
    return array(
        'start_time'=> mktime(0,0,0,  $m,1,$year),
        'end_time'  => mktime(23,59,59, $m+1,0,$year),
    );
}

/**
 * 获取周时间间隔的时间戳
 */
function getWeekPeriodTimestamp($currentTimestamp){
    $w_day=date("w", $currentTimestamp);
    if($w_day=='1')  $cflag = '+0';
    else $cflag = '-1';
    //本周一零点的时间戳
    $start_time = strtotime(date('Y-m-d',strtotime("$cflag week Monday", $currentTimestamp)));
    //本周末零点的时间戳
    $end_time = strtotime(date('Y-m-d',strtotime("$cflag week Monday", $currentTimestamp)))+7*24*3600;
    return array(
        'start_time' => $start_time,
        'end_time'   => $end_time,
    );
}

/**
 * 获取当天启示跟结束的时间戳
 */
function getTodayPeriodTimestamp($currentTimestamp){
    return array(
        'start_time' => $currentTimestamp,
        'end_time'   => $currentTimestamp + 24*3600,
    );
}

/**
 * DESC   : 获取本天，本周 ，本月 三组时间戳制取,如果有一组数据没有传则不获取对应时间的数据值
 * AUTHOR : heweijun@liangrenwang.com
 * PARAMS : ARRAY
 *          year, month, day
 */
function getTimeGroups($params){
    $options = array(
        'year'  => '',
        'month' => '',
        'day'   => '',
    );
    $params = array_merge($options, $params);

    $month = $params['month'];
    $year  = $params['year'];
    $day   = $params['day'];
    $currentTimestamp = strtotime($year."-".$month."-".$day);

    return  array(
            'month' => getStartToEndTimestampByMonthAndYear($month, $year),
            'week'  => getWeekPeriodTimestamp($currentTimestamp),
            'day'   => getTodayPeriodTimestamp($currentTimestamp),

    );
}