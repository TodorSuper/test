<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@yunputong.com >
 * +---------------------------------------------------------------------
 * | 商家中心 导出  回调接口
 */

namespace Com\CallBack\Export;

use System\Base;

class ScExport extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    public function CustomerList(&$data,$params){
        $uc_codes=array_column($data,'uc_code');
        foreach($uc_codes as $key=>$uc_code){
            if(!$uc_code){
                unset($uc_codes[$key]);
            }
        }
        if(empty($uc_codes)){
            $res=array();
            $res_deal=array();
            $res_pay=array();
        }else{
            $where['uc_code']=array('in',$uc_codes);
            //得到小B商家的信息
            $res=D('UcMember')->where($where)->select();

            //得到每个小B用户的交易金额和下单数量
            $arr[]=array('pay_type'=>'ONLINE','pay_status'=>'PAY');
            $arr[]=array('pay_type'=>array('neq','ONLINE'),'ship_status'=>array('neq','UNSHIP'));
            $arr['_logic']='or';
            $where['_complex']=$arr;
            $res_deal=D('OcB2bOrder')->field('sum(real_amount) as deal_amount,sc_code,uc_code,count(uc_code) as order_count')->where($where)->group('uc_code')->select();

            //得到每个小B用户的已支付金额
            $wheres['pay_status']=array('eq',OC_ORDER_PAY_STATUS_PAY);
            $wheres['uc_code']=array('in',$uc_codes);
            $res_pay=D('OcB2bOrder')->field('sum(real_amount) as pay_amount,sc_code,uc_code')->where($wheres)->group('uc_code')->select();
        }


       //将大B商家信息和小B商家信息合并
        foreach($data as $key=>$val){
            foreach($res as $k=>$v){
                if($v['uc_code']==$val['uc_code']){
                    $data[$key]['store_info']=$v;
                }
            }
        }
        foreach($data as $key=>$val){
            foreach($res_deal as $k=>$v){
                if($v['uc_code']==$val['uc_code']){
                    $data[$key]['store_info']['deal_info']=$v;
                }
            }
        }
        foreach($data as $key=>$val){
            foreach($res_pay as $k=>$v){
                if($v['uc_code']==$val['uc_code']){
                    $data[$key]['store_info']['pay_info']=$v;
                }
            }
        }
       $statistic_info=$data;
       // var_dump($statistic_info);die();
        $data=array();
        //重新拼装数组
        foreach($statistic_info as $key=>$val) {
                   $data[]=array(
                       'big_store_name'     =>$val['name'],
                       'linkman'            =>$val['linkman'],
                       'store_phone'        =>$val['phone'],
                       'little_store_name'  =>$val['store_info']['commercial_name'],
                       'little_linkman'     =>$val['store_info']['name'],
                       'little_mobile'      =>$val['store_info']['mobile'],
                       'order_amount'       =>$val['store_info']['deal_info']['order_count'],
                       'deal_amount'        =>$val['store_info']['deal_info']['deal_amount'],
                       'pay_amount'         =>$val['store_info']['pay_info']['pay_amount'],
                       'little_create_time' =>$val['create_time']?date('Y-m-d',$val['create_time']): null,
                       'city'               =>$val['store_info']['city'],
                       'district'           =>$val['store_info']['district'],
                       // 'little_address'     =>$val['store_info']['city'].$val['store_info']['district'].$val['store_info']['address'],
                       'little_address'     =>$val['store_info']['address'],
                       'salesman'           =>$val['salesman'],
                   );
        }
    }
  public function cmsEport(&$data) {
    foreach ($data as $k=>$v) {
      if ($v['status'] == 'ON') {
        $data[$k]['status'] = '上架';
      }else {
        $data[$k]['status'] = '下架';
      }
    }
  }
}

?>
