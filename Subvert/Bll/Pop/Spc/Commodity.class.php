<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: liangrenwang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订货会
 */
namespace Bll\Pop\Spc;

use System\Base;
class Commodity extends Base {


    public function __construct(){
        parent::__construct();
    }

    /**新增订货会
     * yindongyang
     * Bll.Pop.Spc.Commodity.add
     * @param [type] $params [description]
     */
    public function add($params){
        try{
            D()->startTrans();
            //获取本商家用户个人账户信息和总信息
            $total_info=$this->getInfo($params);
            $params['total_info']=$total_info;
            $apiPath="Base.SpcModule.Commodity.Commodity.add";
            $call=$this->invoke($apiPath,$params);
            if($call['status']!=0){
                return $this->endInvoke('',7057);
            }
            if(!$total_info['info']){
                $apiPath2='Base.OrderModule.B2b.OrderOperate.updateSpcCode';
                $data=array(
                    'spc_code'=>$call['response'],
                    'sc_code'=>$params['sc_code'],
                );
                $call2=$this->invoke($apiPath2,$data);
//                echo D()->getLastSql();exit;
                if($call2['status']!=0){
                    return $this->endInvoke('',7067);
                }
            }
            $commit_res = D()->commit();

            if ($commit_res === FALSE) {

                return $this->endInvoke(NULL, 7057);
            }
           return $this->endInvoke($call['response']);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 7057);
        }
    }

    /**更新订货会
     * yindongyang
     * Bll.Pop.Spc.Commodity.update
     * @param [type] $params [description]
     */
    public function update($params){
        try{
            D()->startTrans();
            $apiPath="Base.SpcModule.Commodity.Commodity.update";
            $call=$this->invoke($apiPath,$params);
            if($call['status']!=0){
                return $this->endInvoke('',7061);
            }
            $commit_res = D()->commit();

            if ($commit_res === FALSE) {

                return $this->endInvoke(NULL,7061);
            }
            return $this->endInvoke($call['response']);
        }catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,7061);
        }
    }

    /**获取本商家用户的剩余金额信息
     * yindongyang
     * Bll.Pop.Spc.Commodity.getInfo
     * @param [type] $params [description]
     */
    private function getInfo($params){
        $apiPath='Base.SpcModule.Commodity.Commodity.getInfo';
        $info=$this->invoke($apiPath,$params);
        if($info['status']!=0){
            $this->endInvoke('',$info['status'],'',$info['message']);
        }
        $info=$info['response'];
        //获取某个商家下客户资金账户余额
        $data=array(
            array(
                'code'=>$params['sc_code'],
                'isPcode'=>'YES',
            )
        );
        $apiPath2='Base.TradeModule.Account.Details.getAccontListByCode';
        $userInfo=$this->invoke($apiPath2,$data);
        if($userInfo['status']!=0){
            $this->endInvoke('',$userInfo['status'],'',$userInfo['message']);
        }
        $userInfo=$userInfo['response'];
//        $userInfo=array(
//            '1210000000444'=>array(
//                'free'=>100,
//                'code'=>1210000000444,
//                'spent_money'=>10,
//                'total'=>110,
//            ),
//            '1210000000443'=>array(
//                'free'=>100,
//                'code'=>1210000000443,
//                'spent_money'=>100,
//                'total'=>200,
//            ),
//            '1210000000450'=>array(
//                'free'=>50,
//                'code'=>1210000000450,
//                'spent_money'=>100,
//                'total'=>150,
//            ),
//       );
        if(!$userInfo){
            $total_user_info=array(
                'total_spent_money'=>0,
                'total_advanced_payment'=>0,
                'total_balance'=>0
            );
        }
        if($userInfo){
            foreach($userInfo as $key=>$val){
                $userInfo[$key]['spent_money']=$val['total']-$val['free'];
            }
            $free=0;
            $total=0;
            foreach($userInfo as $key=>$val){
                $free+=$val['free'];
                $total+=$val['total'];
            }
            $total_user_info=array(
                'total_balance'=>$free,
                'total_advanced_payment'=>$total,
                'total_spent_money'=>$total-$free,
            );
        }
        if($info){
            $total_user_info['spent_money']=0;
            $total_user_info['last_amount']=$info['balance'];
            $total_user_info['advanced_payment']=$info['balance'];
            $total_user_info['balance']=$info['balance'];
            foreach($userInfo as $key=>$val){
                $userInfo[$key]['spent_money']=0;
                $userInfo[$key]['last_amount']=$val['free'];
                $userInfo[$key]['advanced_payment']=$val['free'];
                $userInfo[$key]['balance']=$val['free'];
                if($val['free']<=0){
                    unset($userInfo[$key]);
                }
            }
        }else{
            $total_user_info['spent_money']=$total_user_info['total_spent_money'];
            $total_user_info['last_amount']=0;
            $total_user_info['advanced_payment']=$total_user_info['total_advanced_payment'];
            $total_user_info['balance']=$total_user_info['total_balance'];
            foreach($userInfo as $key=>$val){
                $userInfo[$key]['spent_money']=$val['spent_money'];
                $userInfo[$key]['last_amount']=0;
                $userInfo[$key]['advanced_payment']=$val['total'];
                $userInfo[$key]['balance']=$val['free'];
            }
        }
        //将每个用户的信息和用户的总信息组装为新数组
        $total_info=array(
            'userInfo'=>$userInfo,
            'total_user_info'=>$total_user_info,
            'info'=>$info,
        );
        return $total_info;
    }

    /**
     * 订购会详情
     * Bll.Pop.Spc.Commodity.get
     * @access public
     * @author Todor
     */
    public function get($params){
        
        $apiPath = "Base.SpcModule.Commodity.CommodityInfo.get";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,7064);
        }

        // 获取小B商铺名称
        $uc_codes = array_column($res['response']['customers']['lists'],'uc_code');

        if(!empty($uc_codes)){

            $temp['uc_codes'] = $uc_codes;
            $apiPath = "Base.UserModule.Customer.Customer.getCommercial";
            $commercial = $this->invoke($apiPath, $temp);
            foreach ($commercial['response'] as $k => $v) {
                $commercial['response'][$v['uc_code']] = $v['commercial_name'];
                unset($commercial['response'][$k]);
            }
            $commercial = $commercial['response'];
            
            // 组合商铺名称
            foreach ($res['response']['customers']['lists'] as $k => $v) {
                $res['response']['customers']['lists'][$k]['commercial_name'] = $commercial[$v['uc_code']];
            }
            
        }
        

        return $this->endInvoke($res['response']);

    }

    /**
     * 订购会列表
     * Bll.Pop.Spc.Commodity.lists
     * @access public
     * @author Todor
     */

    public function lists($params){

        $apiPath = "Base.SpcModule.Commodity.CommodityInfo.lists";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,7065);
        }

        // 获取订购会对应客户数目
        $spc_codes = array_column($res['response']['lists'],'spc_code');
        $data['spc_codes'] = $spc_codes;
        $apiPath = "Base.SpcModule.Commodity.Customer.getNumber";
        $number = $this->invoke($apiPath,$data);
        $number = $number['response'];
        foreach ($res['response']['lists'] as $k => $v) {
            $res['response']['lists'][$k]['number'] = $number[$v['spc_code']];
        }

        return $this->endInvoke($res['response']);
    }

    /**
     * 订购会详情
     * Bll.Pop.Spc.Commodity.export
     * @access public
     * @author zhangyupeng
     */
    public function export($params){
        
        switch ($params['export_type']) {
            case 'detail':
                $apiPath = "Base.SpcModule.Commodity.Export.detail";
                break;
            case 'all':
                $apiPath = "Base.SpcModule.Commodity.Export.all";
                break;
        }
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,7021);
        }
        return $this->endInvoke($res['response']);
    }


}