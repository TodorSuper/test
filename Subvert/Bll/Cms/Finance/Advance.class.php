<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yaozihao
 * +---------------------------------------------------------------------
 * | cms后台商品相关模块
 */

namespace Bll\Cms\Finance;

use System\Base;

class Advance extends Base {
    private $_rule = null; # 验证规则列表
    public function __construct() {
        parent::__construct();
    }
    /**
     * Bll.Cms.Finance.advance.getAdvanceLists
     * @param type $params
     * @return type
     */

    public function getAdvanceLists($params){
        $apiPath = "Base.FcModule.Payment.Advance.getLists";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status')->getPayMethod();
        foreach($list['response']['lists'] as &$val){
            if(!empty($val['pay_method_ext1'])){
                $val['pay_method_ext1'] =  M('Base.OrderModule.B2b.Status')->getRemitBank($val['pay_method_ext1']);
            }
        }
        $list['response']['pay_method_group'] = $pay_method_group;
        return $this->endInvoke($list['response']);
    }

    /**
     * Bll.Cms.Finance.advance.updateConfirm
     * @param type $params
     * @return type
     */

    public function updateConfirm($params){
        try{
            D()->startTrans();
            $params['status'] = 2;
            $params['balance_status'] = FC_BALANCE_STATUS_YES_BALANCE;

            $apiPath = "Base.FcModule.Payment.Advance.updateConfirmStatus";
            $list = $this->invoke($apiPath, $params);
            $success_num = mysql_affected_rows();
            $num = count($params['b2b_code']);
            $error_num = $num - $success_num;
            $res = array(
                'success_num'=>$success_num,
                'error_num'=>$error_num,
            );
            # 改变订单为付款状态
            $b2bOrders = $this->invoke("Com.Common.CommonView.FcSql.getRemitOrderByB2bCode", [ 'b2b_code'=>$params['b2b_code']] );
            if($b2bOrders['response']['b2b_codes']) {
                //更新order表
                $orderApiPath = "Base.OrderModule.Center.Order.remit";
                $oRes = $this->invoke($orderApiPath, $b2bOrders['response']);
                if($oRes['status']!== 0 ){
                    throw new \Exception('事务提交失败',17);
                }

                # 发送通知用户短信
                $data = array(
                    'sys_name'=>CMS,
                );

                foreach($b2bOrders['response']['find'] as $k=>$v) {
                    $price = $v['amount'];
                    $adv_code = $v['b2b_code'];
                    $mobile = $v['mobile'];
                    $data['numbers'] = [$mobile];
                    $data['message'] = "平台已收到您的一笔预付款，订单编号：{$adv_code}，金额：{$price}元。";
                    $this->push_queue('Com.Common.Message.Sms.send', $data, 0 ); # 发送短信通知

                }
            }
            D()->commit();
        } catch (\Exception $ex) {
            L($ex->getMessage());
            D()->rollback();
            return $this->res(NULL,6703);
        }
        return $this->endInvoke($res);

    }

}