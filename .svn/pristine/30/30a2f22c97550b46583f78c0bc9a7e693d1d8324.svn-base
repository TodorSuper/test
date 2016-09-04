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

class Finance extends Base {
    private $_rule = null; # 验证规则列表
    public function __construct() {
        parent::__construct();
    }
    /**
     * Bll.Cms.Finance.Finance.tradeList
     * @param type $params
     * @return type
     */
    public function tradeList($params){
        $apiPath = "Base.FcModule.Detail.Order.orderList";
        $list = $this->invoke($apiPath, $params);
        return $this->endInvoke($list['response']);
    }
    /**
     * Bll.Cms.Finance.Finance.confirmList
     * @param type $params 查询财务确定单的列表
     * @return type
     */
    public function confirmList($params){
        $apiPath = "Base.FcModule.Payment.Order.findConfirm";
		$list = $this->invoke($apiPath, $params);
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['ext1']);
            }
            if($v['pay_method_ext1']){
                $data[$k]['pay_method_ext1'] = M('Base.OrderModule.B2b.Status')->getRemitBank($v['pay_method_ext1']);
            }
            $list['response']['lists'] = $data;
        }

        return $this->endInvoke($list['response']);
    }
    /**
     * Bll.Cms.Finance.Finance.confirmList
     * @param type $params 查询财务点单已确定的订单列表
     * @return type
     */
    public function confirmGoodsList($params){
        $apiPath = "Base.FcModule.Payment.Order.findGoodsConfirm";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status')->getPayMethod();
        $bankGroup = M('Base.OrderModule.B2b.Status')->getRemitBank();
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['pay_method_info'] = $bankGroup[$v['ext1']];
            }
            $list['response']['lists'] = $data;
        }
        $list['response']['bankGroup'] = $bankGroup;
        $list['response']['pay_method_group'] = $pay_method_group;
        return $this->endInvoke($list['response']);
    }
    /**
     * Bll.Cms.Finance.Finance.paymentList
     * @param type $params 财务付款汇总单 未生成
     *
     */
    public function paymentList($params){
        $apiPath = "Base.FcModule.Payment.Order.findPayment";
        $list = $this->invoke($apiPath, $params);
        //汇总订单总额
        $pList = $list['response']['lists'];
        foreach ($pList as $key => $v) {

            $cData = array(
                'confirm.sc_code'=>$v['sc_code'],
                'confirm.fc_code' =>$v['fc_code'],
            );
            $field = 'adv.amount as adv_amount,adv.adv_code,adv.pay_method as adv_pay_method,adv.pay_method_ext1,confirm.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,ext.commercial_name,b2b.pay_method,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,ext.remit_code';
            $list_confirm = D('FcOrderConfirm')->field($field)->alias('confirm')
                ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                ->join("{$this->tablePrefix}oc_b2b_order_extend ext ON b2b.op_code=ext.op_code",'LEFT')
                ->join("{$this->tablePrefix}sc_store store ON store.sc_code=confirm.sc_code",'LEFT')
                ->join("{$this->tablePrefix}oc_advance adv ON confirm.b2b_code=adv.adv_code",'LEFT')
                ->where($cData)
                ->select();

            $status = M('Base.OrderModule.B2b.Status.getPayMethod');

            foreach($list_confirm as $k=>$v_1){
                $list_c[$k] =$v_1;
                $list_c[$k]['pay_method'] = $status->getPayMethod($v_1['pay_method']);
            }
            if(!empty($list_c)){
                $pList[$key]['pay_confirm'] = $list_c;
            }

        }
        $total['lists'] = $pList;
        $total['totalnum'] = $list['response']['totalnum'];
        $total['total_amount'] = $list['response']['total_amount'];

        return $this->endInvoke($total);
    }

    /**
     * Bll.Cms.Finance.Finance.paymentOrderList
     * @param type $params 查询财务确定单的列表
     *
     */
    public function paymentOrderList($params){

        $fc_code = D('FcOrderConfirm')->where(['b2b_code'=>$params['where']['b2b_code']])->find();

        $params['where']['fc_code'] = $fc_code['fc_code'];
        $apiPath = "Base.FcModule.Payment.Order.findPayment";
        $list = $this->invoke($apiPath, $params);
        $pList = $list['response']['lists'];
        foreach ($pList as $key => $v) {

            $cData = array(
                'confirm.sc_code'=>$v['sc_code'],
                'confirm.fc_code' =>$v['fc_code'],
            );
            $field = 'adv.amount as adv_amount,adv.adv_code,adv.pay_method as adv_pay_method,adv.pay_method_ext1,confirm.b2b_code,b2b.real_amount as amount,b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,ext.commercial_name,b2b.pay_method,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,ext.remit_code';
            $list_confirm = D('FcOrderConfirm')->field($field)->alias('confirm')
                ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                ->join("{$this->tablePrefix}oc_b2b_order_extend ext ON b2b.op_code=ext.op_code",'LEFT')
                ->join("{$this->tablePrefix}sc_store store ON store.sc_code=confirm.sc_code",'LEFT')
                ->join("{$this->tablePrefix}oc_advance adv ON confirm.b2b_code=adv.adv_code",'LEFT')
                ->where($cData)
                ->select();

            $status = M('Base.OrderModule.B2b.Status.getPayMethod');

            foreach($list_confirm as $k=>$v_1){
                $list_c[$k] =$v_1;
                $list_c[$k]['pay_method'] = $status->getPayMethod($v_1['pay_method']);
            }
            if(!empty($list_c)){
                $pList[$key]['pay_confirm'] = $list_c;
            }

        }
        $total['lists'] = $pList;
        $total['totalnum'] = $list['response']['totalnum'];
        $total['total_amount'] = $list['response']['total_amount'];
        return $this->endInvoke($total);
    }

    /**
     * Bll.Cms.Finance.Finance.paymentDetail
     * @param type $params 查询财务汇总单的详情，同时根据条件查出关联的订单项列表
     *{"fc_code":112000548552}
     */
    public function paymentDetail($params){

        $pData = array(
            'where' =>array(
                'fc_code' =>$params['fc_code'],
                'status' => 2
            ),
            'field'=>array(
                'fc_code',
                'bank_code',
                'account_name',
                'account_number',
                'account_bank',
                'amount',
                'affirm_time',
                'sc_name',
            )
        );
        $apiPath = "Base.FcModule.Payment.Order.findPayment";
        $pDetail = $this->invoke($apiPath, $pData);
        if( 0 !== $pDetail['status'] || empty($pDetail['response']['lists']) ){
            return $this->endInvoke('查询订单失败');
        }

        //查询出关联的订单
        $cData = array(
                'fc_code' =>$params['fc_code'],
                'status' => 2,
                'f_status' => 3,
        );
        $cApiPath = "Base.FcModule.Payment.Order.findConfirm";
		$cList = $this->invoke($cApiPath, $cData);
        if( 0 !== $pDetail['status'] ){
            return $this->endInvoke('查询订单失败');
        }
        $res = array(
            'paymentDetail' => $pDetail['response']['lists'][0],
            'confirmLists' => $cList['response']['lists']
        );
        return $this->endInvoke($res);
    }



    /**
     * Bll.Cms.Finance.Finance.addOnLinePayments
     * 先成功生成汇总单，失败返回。然后更新confirm的状态和fc_code。 where中要查询状态
     * 生成汇总单成功事务结束返回，生成失败，返回。
     * 注意在bll里边先确认每个订单是否已经付款过。在进行汇总，然后插入汇总表。
     * $data = array(
                'sc_code'=>'1020000000026',
                'b2b_code'=> array(
                '12200002382','12200002381'
            ),
            'status' => 2
    );
     */

	public function addOnLinePayments($params){
        $apiPath = "Base.FcModule.Payment.Order.updateConfirm";
        $apiAddPath = "Base.FcModule.Payment.Order.addOnLinePayment";
        $num = count($params['b2b_code']);

        try{
            D()->startTrans();
            $cApiPath = "Com.Tool.Code.CodeGenerate.mkCode";
            $code_params = array(
                'busType' => FC_CODE,
                'preBusType' => FP_CODE,
                'codeType' => SEQUENCE_FC,
            );
            $code_res = $this->invoke($cApiPath, $code_params);
            if ($code_res['status'] !== 0) {
                return $this->endInvoke($code_res['response'], $code_res['status'],$code_res['message']);
            }
            $params['fc_code'] = $code_res['response'] ;

            //插入汇总表 payment  addPayments 返回数据包括商家编码sc_code、生成的fc_code.
            $addRes = $this->invoke($apiAddPath, $params);
            if(0 !== $addRes['status']){
                throw new \Exception('事务提交失败',17);
            }
            //更新confirm
            $upConfirm['data'] = array(
                'f_status' => 2,
                'fc_code' =>$addRes['response']['fc_code'],
            );
            $upConfirm['where'] = array(
                'sc_code' => $params['sc_code'],
                'b2b_code' =>$params['b2b_code'],
                'status' => 2
			);
            //更新已确认订单的状态改为已支付
			$upConfirmRes = $this->invoke($apiPath, $upConfirm);

            if($upConfirmRes['status'] !== 0  ||  $upConfirmRes['response'] !== $num ){
                throw new \Exception('事务提交失败',17);
            }

            $commit_res = D()->commit();

            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(),$ex->getCode());
        }

        return $this->endInvoke($addRes['response']);

    }
    /**
     * Bll.Cms.Finance.Finance.addYeePayments
     * 先成功生成汇总单，失败返回。然后更新confirm的状态和fc_code。 where中要查询状态
     * 生成汇总单成功事务结束返回，生成失败，返回。
     * 汇总之后调用易宝转账接口
     * 注意1、在bll里边先确认每个订单是否已经付款过。在进行汇总，然后插入汇总表。
     * $data = array(
    'sc_code'=>'1020000000026',
    'b2b_code'=> array(
    '12200002382','12200002381'
    ),
    'status' => 2
    );
     */
    public function addYeePayments($params){
        $apiPath = "Base.FcModule.Payment.Order.updateConfirm";
        $apiAddPath = "Base.FcModule.Payment.Order.addOnLinePayment";
        $apiYeePath = "Base.FcModule.Payment.Order.addYeePayment";
        $num = count($params['b2b_code']);
        try{
            D()->startTrans();
            //生成汇总单编码
            $cApiPath = "Com.Tool.Code.CodeGenerate.mkCode";
            $code_params = array(
                'busType' => FC_CODE,
                'preBusType' => FP_CODE,
                'codeType' => SEQUENCE_FC,
            );
            $code_res = $this->invoke($cApiPath, $code_params);
            if ($code_res['status'] !== 0) {
                return $this->endInvoke($code_res['response'], $code_res['status'],$code_res['message']);
            }
            //生成汇总单支付编码
            $pay_code_params = array(
                'busType' => FC_CODE,
                'preBusType' => FC_PAY_CODE,
                'codeType' => SEQUENCE_FC,
            );
            $pay_code_res = $this->invoke($cApiPath, $pay_code_params);
            if ($pay_code_res['status'] !== 0) {
                return $this->endInvoke($pay_code_res['response'], $pay_code_res['status'],$pay_code_res['message']);
            }
            $params['fc_code'] = $code_res['response'] ;
            $params['fc_pay_code'] = $pay_code_res['response'] ;

            //插入汇总表 payment  addPayments 返回数据包括商家编码sc_code，总金额amount，生成的fc_code.
            $addRes = $this->invoke($apiAddPath, $params);

            if(0 !== $addRes['status']){
                throw new \Exception('事务提交失败',17);
            }
            //更新confirm
            $upConfirm['data'] = array(
                'f_status' => 2,
                'fc_code' =>$addRes['response']['fc_code'],
            );
            $upConfirm['where'] = array(
                'sc_code' => $params['sc_code'],
                'b2b_code' =>$params['b2b_code'],
                'status' => 2
            );
            $upConfirmRes = $this->invoke($apiPath, $upConfirm);

            if($upConfirmRes['status'] !== 0  ||  $upConfirmRes['response'] !== $num ){
                throw new \Exception('事务提交失败',17);
            }

            $commit_res = D()->commit();

            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(),$ex->getCode());
        }
        $params['amount'] = $addRes['response']['amount'];
        $this->invoke($apiYeePath,$params);
        return $this->endInvoke($addRes['response']);
    }
    /**
     * Bll.Cms.Finance.Finance.upPayment
     * 财务确认汇总付款，更新银行流水号，备注，确认人，最终确认时间
     * 同时更新confirm表的关联状态
     * 同时要更新order表单的状态。
     * 接收参数
     * array $where 更新条件 'fc_code'
     * array $field 限制条件
     * array $data  更新的数据
     *
     *
     * $params = array(
     *           "data"=>array("bank_code"=> "123", "remark"=> "123", "status"=> 2),
     *           "where"=>array(	"fc_code"=>"112000548546", "sc_code"=>"1020000000026", "status"=>1 )
     *      );
     *
     */
    public function upPayment($params){

        if(empty($params['where']['fc_code']) || empty($params['where']['sc_code'])){
            return $this->res(NULL,10002,'','参数错误');
        }
        try{
			D()->startTrans();

            $paymentApiPath = "Base.FcModule.Payment.Order.updatePayment";
            $paymentParams = array(
                'where'=>$params['where'],
                'data'=>$params['data']
            );
            $paymentParams['data']['affirm_time'] = time();
			$payRes = $this->invoke($paymentApiPath, $paymentParams);
            if($payRes['status']!= 0){
                throw new \Exception('事务提交失败',17);
            }
            //财务确定后更新confirm确定表的状态
            $confirmApiPath = "Base.FcModule.Payment.Order.updateConfirm";
            $confirmParams = array(
                'where' =>array(
                    'fc_code'=>$params['where']['fc_code'],
                    'sc_code'=>$params['where']['sc_code'],
                    'status'=> 2
                ),
                'data' =>array(
                    'f_status'=> '3',
                    'balance_status'=>FC_BALANCE_STATUS_BALANCE,
                )
			);

            $cRes = $this->invoke($confirmApiPath, $confirmParams);
            if($cRes['status']!== 0 ){
                throw new \Exception('事务提交失败',17);
            }

            $data = array(
                'fc_code'=>$params['where']['fc_code'],
                'sc_code'=>$params['where']['sc_code'],
            );

            $apiSendSalesMsg = 'Base.FcModule.Payment.Order.sendSalesMsg';// 发送通知业务员短信
            $this->invoke($apiSendSalesMsg, $data);

            $apiSendStoreMsg = 'Base.FcModule.Payment.Order.sendStoreMsg';	//发送短信通知商家
            $this->invoke($apiSendStoreMsg, $data);

			D()->commit();
			return $this->endInvoke(true);
		} catch (\Exception $ex) {
            L($ex->getMessage());
			D()->rollback();
            return $this->res(NULL,10002,'','生成用户编码失败'); # fixme
        }
    }
    /**
     * Bll.Cms.Finance.Finance.addConfirm
     * @param type 添加财务确定单的列表
     * array(
     *      array(
     *           'sc_code' =>  ,
     *           'b2b_code' => ,
     *      )
     * )
     * 多条需要确定的商品单的 sc_code 1020000000026商家编号 和 b2b_code  12200002382  订单编号
     *
     */
    public function addConfirm($params){
        /*
        if(empty($params['sc_code'])){
             return $this->endInvoke($params);
         }
        if(empty($params['b2b_code'])){
             return $this->endInvoke($params);
         }
        */
        $apiPath = "Base.FcModule.Detail.Order.orderList";
        $apiAddPath = "Base.FcModule.Payment.Order.addConfirm";

        // 根据订单编号b2b_code,商铺号sc_code查询
        $num = count($params['b2b_code']);
        $success_num = 0;
        //查出数据
        $orderList = $this->invoke($apiPath, $params);
        try{
            D()->startTrans();
            $order = $orderList['response']['lists'];
            $list = $this->invoke($apiAddPath, $order);
            //$success_num = mysql_affected_rows();
//            if($list && $list['status'] == 0){
//                $success_num += 1;
//            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,6703);
        }
        $error_num = $num - $success_num;
        $res = array(
            'error_num'=>$error_num,
            'success_num'=>$success_num,
        );

        return $this->endInvoke($res);

    }

    /**
     * Bll.Cms.Finance.Finance.addConfirm
     * @param type  更新财务确定单的列表
     * array(
     *      'where' =>$where,
     *      'data' =>$data
     * )
     * 多条需要确定的商品单的 sc_code 1020000000026商家编号 和 b2b_code  12200002382  订单编号
     *
     */
	public function upConfirm($params){
		try{
			D()->startTrans();
			$apiPath = "Base.FcModule.Payment.Order.updateConfirm";
			$params['data'] = array(
				'status' => '2',
                'balance_status'=>FC_BALANCE_STATUS_YES_BALANCE,
                'confirm_name'=>$params['confirm_name'],
                'no_update_time'=>'',//传空值,base层判断是否修改update_time
			);
			$this->invoke($apiPath, $params);
			$success_num = mysql_affected_rows();
			$num = count($params['where']['b2b_code']);
			$error_num = $num - $success_num;
			$res = array(
				'success_num'=>$success_num,
				'error_num'=>$error_num,
			);
			# 改变订单为付款状态
			$b2bOrders = $this->invoke("Com.Common.CommonView.FcSql.getRemitOrderByFcCode", [ 'b2b_code'=>$params['where']['b2b_code']] );
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
					$b2b_code = $v['b2b_code'];
					$mobile = $v['mobile'];
					$data['numbers'] = [$mobile];
					$data['message'] = "平台已收到您的一笔付款，订单编号：{$b2b_code}，金额：{$price}元。";
					$this->push_queue('Com.Common.Message.Sms.send', $data, 0); # 发送短信通知
				}

			}
            #如果支付方式和到账银行不符合,例如:选银行转账民生银行支付,却汇款到招商银行
            if(!empty($params['change'])){
                $this->invoke("Base.OrderModule.Center.Order.changeBank",$params['change']);
            }
			$commit_res = D()->commit();

            # 修改订单状态操作次数
            $logApiPath = "Base.FcModule.Payment.Order.updateLog";
            $logRes = $this->invoke($logApiPath, array( 'b2b_code'=>$params['where']['b2b_code']));
            if($logRes['status']!== 0 ){
                return $this->endInvoke($logRes['response'], $logRes['status'], $logRes['message']);
            }
			if($commit_res === FALSE){
				throw new \Exception('事务提交失败',17);
			}

		} catch (\Exception $ex) {
			D()->rollback();
			return $this->res(NULL,6703);
		}

		return $this->endInvoke($res);
	}

    /**
     * Bll.Cms.Finance.Finance.getFConfirmLists
     * @param type  财务点单待点单列表。包含全部的方式。
     *
     */
    public function  getFConfirmLists($params){

        $apiPath = "Base.FcModule.Payment.Order.getFConfirmLists";
        $list = $this->invoke($apiPath, $params);
        $pay_method_group = M('Base.FcModule.Account.Status')->getPayMethod();
        $bankGroup = M('Base.OrderModule.B2b.Status')->getRemitBank();
        foreach($list['response']['lists']as $k=>$v){
            $data[$k] = $v;
            if($v['ext1']){
                $data[$k]['pay_method_info'] = $bankGroup[$v['ext1']];
            }
            $list['response']['lists'] = $data;
        }
        $list['response']['bankGroup'] = $bankGroup;
        $list['response']['pay_method_group'] = $pay_method_group;
        return $this->endInvoke($list['response']);
    }
     /**
      * Bll.Cms.Finance.Finance.yeePayCallback
      * 转账成功后会回调到该api，更新两个表的状态confirm表和payment表状态 status
      *
      * 发送短信给相关人员。
      * $message = array(
      *        'fc_code' => '112000548285',          // 支付金额
      *         'amount' => '0.01',                  // 转账单据编号
      *         'ledgerNo' => '10012544568',         //  子账户编号
      *         'fc_pay_status' =>'OK'
      *     )；
      *
      *
      * */
    public function  yeePayCallback($params){

        //转账失败，将表更新为FAIL状态
        if('FAIL'  == $params['fc_pay_status']){
            $paymentApiPath = "Base.FcModule.Payment.Order.updatePayment";
            $paymentWhere = array(
                'fc_code' => $params['fc_code'],
            );
            $paymentDate = array(
                'fc_pay_status' => 'FAIL',
            );
            $paymentParams = array(
                'where'=>$paymentWhere,
                'data'=>$paymentDate
            );
            $payRes = $this->invoke($paymentApiPath, $paymentParams);
            if($payRes['status']!= 0){
                return $this->endInvoke($payRes['response'],$payRes['status']);
            }

        }
        try{
            D()->startTrans();
            $paymentApiPath = "Base.FcModule.Payment.Order.updatePayment";
            $paymentWhere = array(
                'fc_code' => $params['fc_code'],
            );
            //以后如果需要将流水号更新，请添加  'bank_code' => $params[ ]
            $paymentDate = array(
                'affirm_time' => NOW_TIME,
                'fc_pay_status'     => 'OK',
                // 'bank_code'=>$params[' ']
                'status'      => 2,
            );
            $paymentParams = array(
                'where'=>$paymentWhere,
                'data'=>$paymentDate
            );
            $payRes = $this->invoke($paymentApiPath, $paymentParams);
            if($payRes['status']!= 0){
                throw new \Exception('事务提交失败',17);
            }
            //财务确定后更新confirm确定表的状态
            $confirmApiPath = "Base.FcModule.Payment.Order.updateConfirm";
            $confirmParams = array(
                'where' =>array(
                    'fc_code'=>$params['fc_code'],
                    'status'=> 2
                ),
                'data' =>array(
                    'f_status'=> '3',
                    'balance_status'=>FC_BALANCE_STATUS_BALANCE,
                )
            );
            $cRes = $this->invoke($confirmApiPath, $confirmParams);
            if($cRes['status']!== 0 ){
                throw new \Exception('事务提交失败',17);
            }
            # 发送通知业务员短信
            $call = $this->invoke("Com.Common.CommonView.FcSql.getPaymentOrderUserInfo", ['fc_code'=>$params['fc_code']]); # 获取汇总单人员信息
            if($call['status'] === 0 ) {
                $data = $call['response'];
                $smsData = [];
                $ucData = array_unique(array_column($data, 'uc_code'));
                foreach($ucData as $v) {
                    $smsData[$v]['amount'] = 0.00;
                    foreach($data as $d) {
                        if($d['uc_code'] == $v) {
                            $smsData[$v]['sales_mobile'] = $d['sales_mobile'];
                            $smsData[$v]['amount'] = bcadd($d['real_amount'], $smsData[$v]['amount'], 2);
                            $smsData[$v]['sc_name'] = $d['sc_name'];
                            $smsData[$v]['uc_name'] = $d['uc_name'];
                        }
                    }
                }

                $paymentDate = date('Y-m-d', NOW_TIME);
                foreach($smsData as $send) {
                    $sc_name = $send['sc_name'];
                    $uc_name = $send['uc_name'];
                    $amount = $send['amount'];
                    $data = array(
                        'sys_name'=>CMS,
                        'numbers' =>[$send['sales_mobile']],
                        'message' =>"您的客户“{$sc_name} {$uc_name}”的已付款项，平台已与贵公司结算。结算金额：￥{$amount}，结算日期：{$paymentDate}。",
                    );
                    $this->push_queue("Com.Common.Message.Sms.send", $data , 0);
                }
            }
            # 获取商家基本信息
            $fc_code = $params['fc_code'];
            $callData = array(
                'where'=>['fc_code'=>$fc_code,'status'=>1],
                'field'=>['account_number','amount','sc_code'],
            );
            $payMentInfo = $this->invoke("Base.FcModule.Payment.Order.findPayment", $callData);
            $payMentInfo = $payMentInfo['response']['lists'][0];
            # 发送短信通知商家
            $call = $this->invoke("Base.StoreModule.Basic.User.getMerchanInfo", ['sc_code'=>$payMentInfo['sc_code']]);
            if( isset($call['response']['phone']) ) {
                if($payMentInfo['amount']) {
                    $num = $payMentInfo['account_number'];
                    $num = substr($num,-4);
                    $price = $payMentInfo['amount'];
                    $data = array(
                        'sys_name'=>CMS,
                        'numbers' =>[$call['response']['phone']],
                        'message' =>"平台已向您尾号为{$num}的账户转入{$price}元，汇款备注：{$fc_code}，预计将在下个工作日内到账。",
                    );
                    $this->push_queue("Com.Common.Message.Sms.send", $data , 0);
                }
            }
            D()->commit();
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke($ex->getMessage(),$ex->getCode());
        }
    }


	/**
	 * 商家支付, for queue
	 * Bll.Cms.Finance.Finance.merchantPay
	 * @access public
	 * @return void
	 */
		
	public function merchantPay($data) {
		$data = $data['message'];
		# 查询支付信息
		$findData = array(
			'fc_code' => $data['fc_code']
		);
		$find = $this->invoke("Base.TradeModule.Pay.Bank.queryTransfer", $findData);
		if($find['status'] !== 0){
			return $this->res($find['response'], $find['status']);
		}
		$status = $find['status'] == "COMPLETE" ? "OK" : "FAIL";
	
		# 回调支付接口
		$callData = array(
			'fc_code' => $data['fc_code'],
			'amount' =>  $data['amount'],
			'ledgerNo' => $data['ledgerNo'],
			'fc_pay_status' => $status,
		);
		$pay = $this->invoke($data['callback'], $callData);

		# 返回支付结果
		if($pay['status'] !== 0) {
			return $this->res($pay['response'], $pay['status']);
		}
	
		return $this->res(true);

	}

}
