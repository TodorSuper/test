<?php
/**
 * Created by PhpStorm.
 * User: renyimin
 * Date: 16/01/12
 * Time: 09:50
 */

namespace Com\Crontab\Fc;

use System\Base;


class AccountCmbSync extends Base
{

    private $_rule = array();

    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 定时任务执行 跑入银行交易明细
     * Com.Crontab.Fc.AccountCmbSync.syncCmbBankList
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     * @access public
     * @return void
     */
    public function syncCmbBankList($params=array())
    {
        $timestamp = time();
        // 准备银行接口的参数
        if(!empty($params['dateFrom'])) {
            // 银行日期参数起始为当天
            $dateFrom = $params['dateFrom'];
            $dateTo = $params['dateTo'];
        } else {
            $dateFrom = date('Y-m-d',$timestamp);
            $dateTo = date('Y-m-d', $timestamp);
        }
        // 获取当前数据表中记录的最后一条银行明细的编号,作为这次查询的起始编号
        $where['create_time'] = array('BETWEEN',array(strtotime($dateFrom),strtotime($dateFrom)+86399));
		$where['account_bank'] = PAY_METHOD_REMIT_CMB;
        if(!isset($params['startNo'])) {
			$startNumber = D('fc_bank_info')->where($where)->order('id desc')->limit(1)->getField('startNo');
		} else {
			$startNumber = intval($params['startNo']);
		}
        // 如果当天数据表中并没有跑入记录,则从编号1开始
        $startNo = $startNumber ? $startNumber+1 : 1;
        // 每天每次读取100条记录
        $endNo = $startNo + 100;
        //参数准备完毕, 开始调取银行接口
        $bankRes = $this->getBankInfo($dateFrom, $dateTo, $startNo, $endNo);
        // 接口调用失败
        if(($bankRes['response']['status'] != '0') || (empty($bankRes['response']['response']['list']))) {
            return $this->endInvoke($bankRes,8085);
        }
        // 接口调取成功, 则将数据跑入数据表中 (并进行状态回填)
        $this->runInsertUpdate($bankRes,$startNo);	// 此处还需要传递当前起始编号(循环递增作为每条银行明细数据的编号)
    }

    /**
     * 获取银行接口数据
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @access private
     * @return mixed
     */
    private function getBankInfo($dateFrom, $dateTo, $startNo, $endNo) {
        $params = array(
            'gateway' => C('BANK_PARAMS_CMB.gateway'),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'startNo' => $startNo,
            'endNo' => $endNo,
            'typeCode' => C('BANK_PARAMS_CMB.typeCode'),
        );
        $bankRes = $this->invoke('Base.PayCenter.Info.AccountInfo.getDtl', $params);
        return $bankRes;
    }

    /**
     * 跑入并完成回填状态
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     * @access private
     * @return mixed
     */
    private function runInsertUpdate($bankRes,$startNo) {
        // 对confirm表进行回填时, 需要记录需要回填的所有id
        // 对银行明细中有汇款码 , 为银行汇款(非微信,非先锋支付)的明细数据 的汇款码进行记录( 从数据表中查询出所有有汇款码并且为银行汇款的汇款订单,之后对订单金额进行比对 )
        $remitCodes = array();
        foreach($bankRes['response']['response']['list'] as $key=>$val) {
            // 针对银行汇款且有汇款码的,对于汇款码进行记录
            if (($val['opAcntNo'] != C('BANK_PARAMS.weixin')) && $val['explain'] && ($val['opAcntNo'] != C('BANK_PARAMS.ucpay'))) {
                $str = "'" . $val['explain'] ."'";	
                $remitCodes[] = $str;
            }
        }
        // 开始构建sql条件
        if($remitCodes) {	// 如果收集到汇款码,则查找对应订单
            $exe = 'yes';
            $remitWhere = '(' . implode(',', $remitCodes) . ')';
        } else {			// 如果没有收集到汇款码
            $exe = 'no';
            $remitOrders = array();
            $remitOrders_error = array();
        }
        if($exe == 'yes') {
            // 批量查询出银行汇款且有汇款码的订单
            $fileds = "confirm.oc_type as octype,b2b.real_amount as amount,adv.amount as advamount,confirm.id as confirmid,extend.remit_code as remitcode,adv.remit_code as advremitcode";
            // 查询条件为: (未确认, 未合并, 支付方式为REMIT, 未付款) 并且 (未结算, 未对账)
            $remit_sql = "SELECT {$fileds} FROM
                       {$this->tablePrefix}fc_order_confirm  confirm
                       LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                       LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                       LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on confirm.oc_code=extend.op_code
                       WHERE
                       ( confirm.status = 1 ) AND ( confirm.f_status = 1 )
                       AND ( b2b.pay_method='REMIT' OR adv.pay_method='REMIT')
                       AND ( b2b.ext1 = 'CMB' OR adv.pay_method_ext1 ='CMB' )
                       AND ( b2b.pay_status='UNPAY' OR adv.status='UNPAY')
                       AND ( confirm.account_status='NO_ACCOUNT' )
                       AND ( confirm.balance_status = 'NO_BALANCE')
                       AND
                        ( extend.remit_code in " . $remitWhere . " OR adv.remit_code in " . $remitWhere . ")";
            $remitOrders = D()->query($remit_sql);
            // 可能存在已经点单的, 也要把错误备注体现出来
            // 批量查询出银行汇款且有汇款码的并且已经点单的订单
            $fileds_error = "confirm.oc_type as octype,b2b.real_amount as amount,adv.amount as advamount,confirm.id as confirmid,extend.remit_code as remitcode,adv.remit_code as advremitcode";
            // 查询条件为: (未确认, 未合并, 支付方式为REMIT, 未付款) 并且 (未结算, 未对账)
            $remit_sql_error = "SELECT {$fileds_error} FROM
                       {$this->tablePrefix}fc_order_confirm  confirm
                       LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                       LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                       LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on confirm.oc_code=extend.op_code
                       WHERE
                       ( confirm.status = 2 )
                       AND ( b2b.pay_method='REMIT' OR adv.pay_method='REMIT')
                       AND ( b2b.ext1 = 'CMB' OR adv.pay_method_ext1 ='CMB' )
                       AND ( b2b.pay_status='PAY' OR adv.status='PAY')
                       AND ( extend.remit_code in " . $remitWhere  . " OR adv.remit_code in " . $remitWhere . ")";
            $remitOrders_error = D()->query($remit_sql_error);
        }
        // 开始为银行明细数据的跑入做数据构建
        foreach($bankRes['response']['response']['list'] as $b_key=>$b_val) {
            $b_val_hkm = $b_val['explain'];
            if($b_val['type'] == 1) {   // 如果是出账,
                $bank_type = FC_BANK_TYPE_OUT_ACCOUNT;
            } else {    // 如果是入账
                $bank_type = FC_BANK_TYPE_ACCOUNTED;
            }
            // 对汇款码进行正则提取
            $res = preg_match('/\d{9}/', $b_val_hkm, $match);
            if($res) {
                $b_val_hkm = $match[0];
            }
            // 在构建需要跑入的数据之前, 先判断该数据是否已经跑入过了,如果已经跑入过了,则不用构建该数据
            $judge = D('fc_bank_info')->where('bank_code="' . $b_val['svrId'] . '"')->select();
            if ($judge) {  // 如果已经跑入过, 则不用再次跑入 (这条写if语句只是为了阅读清楚)
                continue;
            }
            $resTag = 'NO';   // 用来判断该条明细的汇款码是否匹配到订单了
            $resTag_error = 'NO';   // 用来判断该条明细的订单是否已经被点单了
            if( !empty($b_val_hkm) ) {   // 如果有汇款码
                // 先查看汇款码所属订单是否已经被点单了
                foreach ($remitOrders_error as $r_key_error => $r_val_error) {
                    if (($r_val_error['remitcode'] == $b_val_hkm) || ($r_val_error['advremitcode'] == $b_val_hkm)) { // 如果汇款码可以匹配到已经点单过的订单
                        $errorReason = '汇款码所属订单已被点单';
                        $order_num = null;
                        $account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                        $status = FC_STATUS_NO;
                        $order_amount = null;
                        $type = PAY_METHOD_ONLINE_REMIT;
                        $resTag_error = "YES";
                        break;
                    }
                }
                // 如果该条明细所对应的订单确定没有被点单过
                if ($resTag_error == "NO") {
                    if($b_val['opAcntNo'] != C('BANK_PARAMS.weixin') && ($b_val['opAcntNo'] != C('BANK_PARAMS.ucpay'))) {  // 如果有汇款码并且不是微信,不是先锋支付（暂时考虑为银行汇款）
                        foreach ($remitOrders as $r_key => $r_val) {
                            // 判断是何种订单
                            if ($r_val['remitcode'] == $b_val_hkm || $r_val['advremitcode'] == $b_val_hkm) { // 如果汇款码可以匹配到订单
                                if ($r_val['octype'] == FC_ORDER_CONFIRM_OC_TYPE_GOODS) {  // 如果是商品订单
                                    if ($b_val['amount'] == $r_val['amount']) { // 如果汇款码可以对上, 并且金额可以对的上, 则直接自动对账
                                        $errorReason = '';
                                        $order_num = 1;
                                        $account_status = FC_ACCOUNT_STATUS_ACCOUNT;
                                        $status = FC_STATUS_END;
                                        $type = PAY_METHOD_ONLINE_REMIT;
                                        $order_amount = $r_val['amount'];
                                        $Updata[] = array(   // 回填记录id
                                            'id' => $r_val['confirmid'],
                                            'account_status' => FC_ACCOUNT_STATUS_ACCOUNT,
                                            'bank_code' => $b_val['svrId'],
                                            'balance_status' => FC_BALANCE_STATUS_YES_BALANCE,
                                            'pay_name' => $b_val['opAcntName'],
                                            'update_time' => NOW_TIME
                                        );
                                    } else {   // 如果汇款码可以对上, 但是金额少于订单金额
                                        $errorReason = '有汇款码,但银行汇款金额少于订单金额';
                                        $order_num = null;
                                        $account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                                        $status = FC_STATUS_NO;
                                        $type = PAY_METHOD_ONLINE_REMIT;
                                        $order_amount = null;
                                    }
                                } else if ($r_val['octype'] == PAY_METHOD_ONLINE_ADVANCE) {  // 如果是预付款订单
                                    if ($b_val['amount'] == $r_val['advamount']) { // 如果汇款码可以对上, 并且金额可以对的上, 则直接自动对账
                                        $errorReason = '';
                                        $order_num = 1;
                                        $account_status = FC_ACCOUNT_STATUS_ACCOUNT;
                                        $status = FC_STATUS_END;
                                        $type = PAY_METHOD_ONLINE_REMIT;
                                        $order_amount = $r_val['advamount'];
                                        $Updata[] = array(   // 回填记录id
                                            'id' => $r_val['confirmid'],
                                            'account_status' => FC_ACCOUNT_STATUS_ACCOUNT,
                                            'bank_code' => $b_val['svrId'],
                                            'balance_status' => FC_BALANCE_STATUS_YES_BALANCE,
                                            'pay_name' => $b_val['opAcntName'],
                                            'update_time' => NOW_TIME
                                        );
                                    } else {   // 如果汇款码可以对上, 但是金额少于订单金额
                                        $errorReason = '有汇款码,但银行汇款金额少于订单金额';
                                        $order_num = null;
                                        $account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                                        $status = FC_STATUS_NO;
                                        $type = PAY_METHOD_ONLINE_REMIT;
                                        $order_amount = null;
                                    }
                                }
                                $resTag = "YES";    // 如果汇款码可以匹配到订单, 则进行标记, 并且退出此次汇款码对比
                                break;
                            }
                        }
                        if ($resTag == 'NO') {   // 如果汇款码最终都没有配到合适的订单
                            $errorReason = '汇款码有误';
                            $order_num = null;
                            $account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                            $status = FC_STATUS_NO;
                            $type = PAY_METHOD_ONLINE_REMIT;
                            $order_amount = null;
                        }
                    } elseif($b_val['opAcntNo'] == C('BANK_PARAMS.weixin')) {
                        // 如果是微信的
                        $type = PAY_METHOD_ONLINE_WEIXIN;
                        $errorReason = '';
                        $account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                        $status = FC_STATUS_NO;
                        $order_num = null;
                        $order_amount = null;
                    } elseif ($b_val['opAcntNo'] == C('BANK_PARAMS.ucpay')){
						// 如果是先锋支付的
						$type = PAY_METHOD_ONLINE_UCPAY;
						$errorReason = '';
						$account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                        $status = FC_STATUS_NO;
                        $order_num = null;
                        $order_amount = null;
					}
                }
            } else {    // 如果无汇款码
                // 如果是微信的
                if ($b_val['opAcntNo'] == C('BANK_PARAMS.weixin')) {
                    $type = PAY_METHOD_ONLINE_WEIXIN;
                    $errorReason = '';
                } elseif($b_val['opAcntNo'] == C('BANK_PARAMS.ucpay')) {
                    $type = PAY_METHOD_ONLINE_UCPAY;
                    $errorReason = '';
                } else {
                    $errorReason = '无汇款码';
                    $type = PAY_METHOD_ONLINE_REMIT;
                }
                $account_status = FC_ACCOUNT_STATUS_NO_ACCOUNT;
                $status = FC_STATUS_NO;
                $order_num = null;
                $order_amount = null;
            }
            $data[] = array(
                'order_amount' => $order_amount,
                'order_num' => $order_num,
                'account_status' => $account_status,
                'status' => $status,
                'pay_name' => empty($b_val['opAcntName']) ? null : $b_val['opAcntName'],
                'bank_time' => strtotime($b_val['actDate']),
                'bank_amount' => $b_val['amount'],
                'bank_code' => $b_val['svrId'],
                'remit_code' => empty($b_val['explain']) ? null : $b_val['explain'],
                //'bank_data' => '', //暂未明确
                'type' => $type,
                'create_time' => time(),
                'startNo' => $startNo,    // 记录每条数据的编号
                'errorReason' => $errorReason,
                'bank_type'=>$bank_type,
				'account_bank' => PAY_METHOD_REMIT_CMB,
            );
            $startNo++;
        }
        if($data) {
            // 批量跑入
            $tryTimes = 0;
            $this->insertTry($data,$tryTimes);
            $tryTime = 0;
            if($Updata) {
                // 批量回填
                $this->backUpdateTry($Updata, $tryTime);
            }
        } else {
            return $this->endInvoke(null, 8083); // 如果已经跑入过, 则不用再次跑入
        }
    }

    /**
     * 跑入失败时, 尝试3次回写
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     * @access private
     * @return mixed
     */
    private function insertTry($addData, &$tryTimes) {
        $addRes = D('fc_bank_info')->addAll($addData);
        if(!$addRes) {
            $tryTimes++;
            if($tryTimes<=3) {
                $this->insertTry($addData, $tryTimes);
            } else {     // 尝试多次, 回写状态仍然失败
                return  $this->endInvoke(null, 8082);
            }
        } else {
            return true;
        }
    }

    /**
     * 回写失败时, 尝试3次回写
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     * @access private(
     * @return mixed
     */
    private function backUpdateTry($Updata, &$tryTime) {
        foreach($Updata as $uk=>$uv) {
            $upRes = D("fcOrderConfirm")->save($uv);
        }
        if(!$upRes) {
            $tryTime++;
            if($tryTime<=3) {
                $this->backUpdateTry($Updata, $tryTime);
            } else {     // 尝试多次, 回写状态仍然失败
                return $this->endInvoke(null, 8084);
            }
        } else {
            return $this->endInvoke('ok');
        }
    }

    /*
     * 推送数据给银行
     * Com.Crontab.Fc.AccountCmbSync.bankPush
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     */
    public function bankPush() {
        // 查询payment_account表中未进行推送的数据 及 所对应银行信息
        $fields = "payment.account_bank,store.account_province,store.account_city,store.account_code as s_accountcode,paymentaccount.fc_code,payment.account_name,payment.account_number,paymentaccount.amount,store.account_type,paymentaccount.account_code";
        $sql = "SELECT {$fields} FROM
                       {$this->tablePrefix}fc_payment_account  paymentaccount
                       LEFT JOIN {$this->tablePrefix}fc_order_payment payment on paymentaccount.fc_code=payment.fc_code
                       LEFT JOIN {$this->tablePrefix}sc_store store on store.sc_code=payment.sc_code
                       WHERE
                       (
                        (
                          paymentaccount.fc_check_status=''
                          OR  (paymentaccount.fc_check_status = 'FAIL' AND paymentaccount.check_times<3)
                        )
                         AND payment.bank_type='CMB'
                       ) limit 5";  // account表中尚未推送 及 推送失败次数小于3的 制单记录 计划任务都会定时进行重复推送
        $account_codes = D()->query($sql);
        if(empty($account_codes)) {
            return $this->res(null, 8051);
        }
        $arr = $this->tryBankPush($account_codes);
        // 回填状态(是否已经推送过)
        if(!empty($arr['account_code_rig'])) {
            foreach ($arr['account_code_rig'] as $key => $val) {   // 一旦推送成功, 则把标识推送状态为"已经推送成功"
                $udata['fc_check_status'] = 'OK';
                $udata['data'] = $val['data'];
                $where['account_code'] = $val['account_code'];
                D("fcPaymentAccount")->where($where)->save($udata);
            }
        }
        if(!empty($arr['account_code_err'])) {
            foreach ($arr['account_code_err'] as $ke => $ve) { // 推送失败的制单记录(制单错误次数+1)
                $sql = "update {$this->tablePrefix}fc_payment_account set `fc_check_status`='FAIL',`check_times`=`check_times`+1 where `account_code`='" . $ve['account_code'] . "' AND check_times<3";
                D()->master()->query($sql);
            }
        }
        return $this->res('ok',true);
    }

    /**
     * 向银行推送数据的方法
     */
    private function tryBankPush($account_codes)
    {
        // 开始往银行推送数据
        foreach ($account_codes as $k => $v) {
            if (SC_ACCOUNT_TYPE_ENTERPRISE_ACCOUNT == $v['account_type']) { // 对公账户
                $account_type = 1;
            } else {  // 对私账户(SC_ACCOUNT_TYPE_PERSONAL_ACCOUNT)
                $account_type = 2;
            }
            // 向银行发送数据
            $bankApiPath = "Base.PayCenter.Info.AccountInfo.Xfer";
            $bankparams = array(
                'gateway' => C('BANK_PARAMS_CMB.gateway'),
                'bankName' => trim($v['account_bank']),
                'insId' => trim($v['account_code']),
                'bankBM' => trim($v['s_accountcode']),
                'amount' => trim($v['amount']),
                'acntToName' => trim($v['account_name']),
                'acntToNo' => trim(preg_replace("/\s+/", '', $v['account_number'])),
                'rcvCustType' => trim($account_type),
				'province' => trim($v['account_province']),
				'city' => trim($v['account_city']),
            );
            $code_res = $this->invoke($bankApiPath, $bankparams);
            if (0 != $code_res['status']) { // 如果推送失败
                $account_code_err[] = array(
                    'account_code'=> $v['account_code'],
                );
            } elseif (0 == $code_res['status'] && 0 != $code_res['response']['status']) {   // 如果推送成功但信息有误 (失败)
                // 尝试多次, 回写状态仍然失败
                $account_code_err[] = array(
                    'account_code'=> $v['account_code'],
                );
            } else {    // 发送成功
                // 银行返回冗余信息回填
                $account_code_rig[] = array(
                    'account_code' => $v['account_code'],
                    'data' => json_encode($code_res['response']['response']['list']),
                );
            }
        }
        return $arr = array(
            'account_code_rig'=>$account_code_rig,
            'account_code_err'=>$account_code_err,
        );
    }

    /*
     * 查询制单记录在银行的付款状态
     * Com.Crontab.Fc.AccountCmbSync.QryXfer
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     */
    public function QryXfer() {
        // 查询制单记录在银行的状态
        // 对payment_account中所有 推送成功(未审核) 并且 未查询付款的或者付款失败并且次数小于3次 (每次检查10条数据)
        $account_codes = D('FcPaymentAccount')->where('fc_check_status="OK" AND (fc_pay_status="" OR (fc_pay_status="FAIL" AND pay_times<3)) AND bank_type="CMB"')->Field('fc_code,account_code,data')->limit(11)->select();
        if(empty($account_codes)) {
            return $this->res(null,8051);
        }
        $arr = $this->bankQryXfer($account_codes);
        if(!empty($arr['codes_ok'])) {
            foreach ($arr['codes_ok'] as $key => $val) {  // 对所有付款成功的制单记录进行标识
                $udata['fc_pay_status'] = 'OK';
                $where['account_code'] = $val;
                $where['fc_check_status'] = 'OK';  // 固定条件为推送成功未审核
                D("fcPaymentAccount")->where($where)->save($udata);
            }
        }
        if(!empty($arr['codes_fail'])) {
            foreach ($arr['codes_fail'] as $ke => $va) { // 对所有查询付款出现失败的制单记录进行标识
                $sql = "update {$this->tablePrefix}fc_payment_account set `fc_pay_status`='FAIL',`pay_times`=`pay_times`+1 where `account_code`='" . $va . "' AND `fc_check_status`='OK' AND pay_times<3";
                D()->master()->query($sql);
            }
        }
        return $this->res('ok',true);
    }

    /**
     * 对制单记录到银行进行查询
     */
    private function bankQryXfer($account_codes) {
        // 调用转账交易查询
        $apiPath = "Base.PayCenter.Info.AccountInfo.QryXfer";
        $codes_ok = array(); // 所有已付款的制单记录;
        $codes_fail = array(); // 所有付款失败的制单记录;
        // 对制单记录进行查询
        foreach($account_codes as $k=>$v) {
			$data = json_decode($v['data'],TRUE);
            $params = array(
                'gateway' => C('BANK_PARAMS_CMB.gateway'), //需要调用的网关
                'insId' => $data[0]['insId'], 
				'svrId' => $data[0]['svrId'], 
            );
            $code_res = $this->invoke($apiPath, $params);
            if (0 != $code_res['status']) { // 如果查询失败
                $codes_fail[] = $v['account_code'];
            } else {    // 查询成功
                if(192118 == $code_res['response']['status']) {  // 如果是待审核
                    // 不做任何处理
                } else if( 0 == $code_res['response']['status']) {   // 如果是已付款
                    $codes_ok[] = $v['account_code'];
                } else {    // 出错
                    $codes_fail[] =$v['account_code'];
                }
            }
        }
        return array(
            'codes_ok' => $codes_ok,
            'codes_fail' => $codes_fail,
        );
    }

    /*
     * payment表读取payment_account表拆单状态
     * Com.Crontab.Fc.AccountCmbSync.QryXferSplit
     * @apiAuthor renyimin <renyimin@liangrenwang.com>
     * @apiVersion 1.0.0
     */
    public function QryXferSplit() {
        // 对payment中未付款状态 ( 推送未成功 或者 推送成功但未付款 ) 的制单记录都需要进行状态读取
        $payment_codes = D('FcOrderPayment')->where('status=1 AND ( `fc_check_status` <> "OK" OR `fc_pay_status` <> "OK") AND bank_type="CMB"')->Field('fc_code,sc_code')->select();
        if(empty($payment_codes)) {
            return $this->res(null,8051);
        }
		
        try {
            D()->startTrans();
            foreach($payment_codes as $k=>$v) {
                $update = array();
                $updateConfirm = array();
                // 查询制单记录在payment_account表中对应的拆单项
                $account_codes = D('FcPaymentAccount')->where('fc_code="' . $v['fc_code'] . '"')->Field('fc_code,check_times,pay_times,account_code,fc_check_status,fc_pay_status')->select();
                if(empty($account_codes)) {
                    continue;
                }
                $check_status_tag = '';   // 标记该制单记录所对应的拆分单是否全部推送成功了
                $status_tag = '';  // 标记该制单记录所对应的拆分单是否全部付款了
                $cou = count($account_codes);   // 如果全部为未推送 null
                $i = 0;
                foreach ($account_codes as $key => $val) {
                    if ($val['fc_check_status'] == "FAIL" && $val['check_times'] >= 3) { // 如果拆分开的订单中有推送失败的并且失败次数为3次
                        $check_status_tag = 'FAIL'; // 在payment表中标记为fail
                        break;
                    }
                    if ($val['fc_check_status'] == "OK") {  // 拆单中有跑成功的
                        $i++;
                    }
                }
                if($i == $cou) {    // 表示全部为未推送 null 或者 NO
                    $check_status_tag = 'OK';
                }
                if($check_status_tag == 'OK') { // 表示推送是成功的
                    $j = 0;
                    foreach ($account_codes as $sk => $sv) { // 检测付款状态
                        if ($sv['fc_pay_status'] == "FAIL" && $sv['pay_times'] >= 3) {  // // 如果拆分开的订单中有付款失败的并且失败次数为3次
                            $status_tag = 'FAIL';
                            break;
                        }
                        if ($sv['fc_pay_status'] == "OK") {  // 拆单中有付款"成功"的
                            $j++;
                        }
                    }
                    if($j == $cou ) {    // 表示全部为未查询的
                        $status_tag = 'OK';
                    }
                }
                if($status_tag == 'OK' && $check_status_tag == "OK") {  // 如果推送成功并且已经付款, 则标记为已付款
                    $update['status'] = FC_STATUS_PAYMENT;
                    $update['affirm_time'] = NOW_TIME;
                    $update['affirm_name'] = "孙艳";
                    // confirm表
                    $updateConfirm['f_status'] = FC_F_STATUS_PAYMENT;
                    $updateConfirm['balance_status'] = FC_BALANCE_STATUS_BALANCE;
                }
                if($check_status_tag) {
                    $update['fc_check_status'] = $check_status_tag;
                }
                if($status_tag) {
                    $update['fc_pay_status'] = $status_tag;
                }
                if(!empty($update)) {
                    $where = 'fc_code="' . $v['fc_code'] . '"';
                    $res = D('FcOrderPayment')->where($where)->save($update);

                    if ($res === FALSE) {
                        throw new \Exception('事务提交失败', 17);
                    }
                }
                if(!empty($updateConfirm)) {
                    // 回填confirm表
                    $res = D('FcOrderConfirm')->where($where)->save($updateConfirm);
                    if ($res === FALSE) {
                        throw new \Exception('事务提交失败', 17);
                    }
                    // 记录成功制单的, 需要发送短信
                    $message_codes[] = array(
                        'fc_code' => $v['fc_code'],
                        'sc_code' => $v['sc_code'],
                    );
                }
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            L($ex->getMessage());
            L($ex->getCode());
        }
        foreach($message_codes as $mk=>$mv) {
            // 发送短信
            $apiSendStoreMsg = 'Base.FcModule.Payment.Order.sendStoreMsg';    //发送短信通知商家
            $storedata['fc_code'] = $mv['fc_code'];
            $storedata['sc_code'] = $mv['sc_code'];
            $this->invoke($apiSendStoreMsg, $storedata);
            $apiSendSalesMsg = 'Base.FcModule.Payment.Order.sendSalesMsg';   // 发送通知业务员短信
            $saledata['fc_code'] = $mv['fc_code'];
            $saledata['sc_code'] = $mv['sc_code'];
            $this->invoke($apiSendSalesMsg, $saledata);
        }
        return $this->res('ok',true);
    }
}