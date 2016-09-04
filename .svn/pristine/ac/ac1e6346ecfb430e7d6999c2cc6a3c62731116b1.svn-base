<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <neilei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 订货会添加订单
 */

namespace Bll\B2b\Order;

use System\Base;
class Advance extends Base {

    private $_rule = null; 
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }


    /**
     * 获取用户余额
     * Bll.B2b.Order.Advance.getAdvance
     * @access public
     * @author Todor 
     */
    public function getAdvance($params){

        // 获取账户信息
        $data = array(
            [
                'code'     =>$params['uc_code'],
                'isPcode' => 'NO',
             ],
            );
        $apiPath = "Base.TradeModule.Account.Details.getAccontListByCode";
        $res = $this->invoke($apiPath,$data);

        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }elseif(empty($res['response'])){                       #  如果为空的情况下增加资金账户
            try{
                D()->startTrans();
                $add_date = array(
                    'code'=>(string)$params['uc_code'],
                    'pCode'=>(string)$params['sc_code'],
                    'accountType'=>TC_ACCOUNT_PREPAYMENT,
                    );
                $apiPath = "Base.TradeModule.Account.Balance.add";
                $add_res = $this->invoke($apiPath, $add_date);

                if($add_res['status'] != 0){
                    return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
                }
                $commit_res = D()->commit();
                if($commit_res === FALSE){
                    return $this->endInvoke(NULL,17);
                }
            } catch (\Exception $ex) {
                D()->rollback();
                return $this->endInvoke(NULL,2502);
            }
        }

        // 去充值的时候获取当前订货会的情况
        $params['get_commodity'] = 'YES';
        $apiPath = "Base.SpcModule.Commodity.Commodity.getInfo";
        $spc_res = $this->invoke($apiPath,$params);
        if($spc_res['status'] != 0){
            return $this->endInvoke(NULL,$spc_res['status'],'',$spc_res['message']);
        }
        $res['response']['commodity'] = $spc_res['response'];
        return $this->endInvoke($res['response']);
    }

    
    /**
     * 去充值的时候获取当前订货会的情况
     * Bll.B2b.Order.Advance.goRecharge
     * @access public
     * @author Todor
     */

    public function goRecharge($params){
        $apiPath = "Base.SpcModule.Commodity.Commodity.getInfo";
        $res = $this->invoke($apiPath,$params);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status'],'',$res['message']);
        }
        return $this->endInvoke($res['response']);
    }


    /**
     * 生成预付款订单
     * Bll.B2b.Order.Advance.add
     * @access public
     * @author Todor 
     */

    public function add($params){
        $params['amount'] = rtrim($params['amount'],'.html');
        // 如果b2b端 session 获取不到 
        if(empty($params['client_name'])){
            $apiPath = "Base.UserModule.Customer.Customer.get";
            $customer = $this->invoke($apiPath,$params);
            if($customer['status'] != 0){
                 return $this->endInvoke(NULL,$customer['status'],'',$customer['message']);
            }
            $params['client_name'] = $customer['response']['name'];
        }

        // 如果金额为0 返回错误信息
        if(empty($params['amount'])){
            return $this->endInvoke(NULL,7068);
        }

        // 获取账户信息 如果用户金额大于1亿则返回错误
        $data = array(
            [
                'code'     =>$params['uc_code'],
                'isPcode' => 'NO',
             ],
            );
        $apiPath = "Base.TradeModule.Account.Details.getAccontListByCode";
        $advance_res = $this->invoke($apiPath,$data);
        if($advance_res['status'] != 0){
            return $this->endInvoke(NULL,$advance_res['status'],'',$advance_res['message']);
        }

        foreach ($advance_res['response'] as $k => $v) {
            $amount = $v['free'];
        }
        $all    = $amount + $params['amount'];
        if((int)$all >= 100000000){
            return $this->endInvoke(NULL,7067);
        }

        try{
            D()->startTrans();
            $apiPath = "Base.OrderModule.Advance.Order.add";
            $add_res = $this->invoke($apiPath, $params);
            if($add_res['status'] != 0){
                return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                return $this->endInvoke(NULL,17);
            }
            // 如果是银行卡支付 获取全部信息
            if($params['pay_method'] == PAY_METHOD_ONLINE_REMIT){
                $data['op_code'] = $add_res['response']['op_code'];
                $apiPath = "Base.OrderModule.Advance.Order.get";
                $res = $this->invoke($apiPath, $data);
                if($res['status'] != 0){
                    return $this->endInvoke(NULL,$res['status'],'',$res['message']);
                }
                
                // 发送短信
                $remit_code = $res['response']['remit_code'];
                $remit_bank = $res['response']['pay_method_ext1'];
                $amount = $res['response']['amount'];
                $uc_code = $res['response']['uc_code'];

                // 获取用户电话
                $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
                $userInfo = $this->invoke($apiPath, $params);
                $userInfo = $userInfo['response'];
                $mobile = $userInfo['mobile'];

                $message = $this->getRemitMessage($remit_code, $amount, $remit_bank);
                $data = array(
                    'sys_name'=>B2B,
                    'numbers'=>array($mobile),
                    'message'=>$message,
                );

                $apiPath = "Com.Common.Message.Sms.send";
                $this->push_queue($apiPath, $data,0);

                return $this->endInvoke($res['response']);
            }

            return $this->endInvoke($add_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,6035);
        }
    }




    /**
     * 获取银行的 消息信息
     * @access private
     */
    private function  getRemitMessage($remit_code,$amount,$remit_bank){
        $remit_banks = array(
            'CMB' => '招商银行北京分行营业部',
            'CMBC'=> '民生银行北京常营支行',
        );
        
        $remit_bank_nos = array(
            'CMB' => '110918380910206',
            'CMBC'=> '695446572',
        );
        $remit_bank_name = $remit_banks[$remit_bank];
        $bank_no = $remit_bank_nos[$remit_bank];
        $message = "
您的汇款码是:{$remit_code},汇款时请务必填写至备注，请您将预付款应付金额转入下方银行账户。如有问题请拨打客服电话：400-815-5577,
开户名：北京双磁信息科技有限公司
开户行：{$remit_bank_name}
账号：{$bank_no}
应付款：￥{$amount}";
        return $message;
    }

    
    /**
     * 生成预付款订单
     * Bll.B2b.Order.Advance.getPayMethodList
     * @access public
     * @author Todor 
     */
    public function getPayMethodList($params){
        //获取支付列表
        $apiPath = "Base.OrderModule.Center.PayMethod.lists";
        $data = array('pay_status'=>'ON');
        $pay_lists = $this->invoke($apiPath,$data);
        
        return $this->endInvoke($pay_lists['response']);
    }

}

?>
