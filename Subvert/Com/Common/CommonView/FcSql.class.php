<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 订单中心列表sql
 */

namespace Com\Common\CommonView;

use System\Base;

class FcSql extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }


    /**
     * 财务中心列表sql
     * @param type $fields
     * @param type $where
     * @param type $order
     * @param type $group
     * @param type $having
     * @param type $sql_flag
     * @param type $other
     * @return type
     */
    public function sqls($fields,$where,$order,$group,$having,$sql_flag,$other){

        //额外参数
        if(!empty($other)){
            extract($other);
        }
        //列表sql  开发者往里面写sql就可以
        $sqls = array(
            'down_list' => "SELECT {$fields}FROM
                                {$this->tablePrefix}oc_b2b_order
                            WHERE
                                {$where}
                            ",

                'getFcOrderList'=>
                    "SELECT {$fields} FROM
                      {$this->tablePrefix}oc_b2b_order tci
                       LEFT JOIN {$this->tablePrefix}tc_pay_voucher ss on tci.b2b_code=ss.oc_code
					   LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend sc on tci.op_code=sc.op_code
					   LEFT JOIN {$this->tablePrefix}fc_order_confirm foc on foc.b2b_code=tci.b2b_code

                       WHERE {$where} order by {$order}",

                'getStoreInfo'=>"
                      SELECT {$fields} FROM
                            {$this->tablePrefix}sc_store
                       WHERE
                            {$where}",

				'getOrderGoodsInfo' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on b2b.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                       WHERE
                            {$where} order by {$order}
                      ",
            'getOrderGoodsAllInfo' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on b2b.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                       LEFT JOIN {$this->tablePrefix}uc_member member  on b2b.uc_code=member.uc_code
                       WHERE
                            {$where} order by {$order}
                      ",
                'getOrderInfo' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                      LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on b2b.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                       WHERE
                            {$where} order by {$order}
                      ",

                'getFcPaymentList'=>
                    "SELECT {$fields} FROM {$this->tablePrefix}fc_order_payment WHERE {$where} order by {$order}",

                'getFcPaymentstoreList' =>
                "SELECT {$fields} FROM {$this->tablePrefix}fc_order_payment payment
                 LEFT JOIN {$this->tablePrefix}sc_store store  on payment.sc_code=store.sc_code
                 WHERE {$where} order by {$order}",


                'getPaymentList'=>"SELECT {$fields} FROM
                          {$this->tablePrefix}oc_b2b_order tci
                           LEFT JOIN {$this->tablePrefix}tc_pay_voucher ss on tci.b2b_code=ss.oc_code
                           LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend sc on tci.op_code=sc.op_code
                           LEFT JOIN {$this->tablePrefix}fc_order_confirm confirm on tci.b2b_code=confirm.b2b_code
                            LEFT JOIN {$this->tablePrefix}sc_store store on tci.sc_code= store.sc_code
							WHERE {$where}	 order by {$order}  ",
                'financeConfirmLists' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.oc_code=b2b.op_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on confirm.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                       WHERE
                            {$where}
						 order by {$order}  limit {$other['page']},{$other['page_number']}
                      ",
				'getAdvanceInfo' => "SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on confirm.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                       WHERE
                            {$where} order by {$order}
                ",
                'getAdvanceAllInfo' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on confirm.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on confirm.oc_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                       LEFT JOIN {$this->tablePrefix}uc_member member  on adv.uc_code=member.uc_code
                       WHERE
                            {$where} order by {$order}
                      ",
            'getAdvanceAccountInfo' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}oc_advance adv
                      LEFT JOIN {$this->tablePrefix}fc_order_confirm  confirm on confirm.b2b_code=adv.adv_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on adv.sc_code=store.sc_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on confirm.b2b_code=voucher.oc_code
                       WHERE
                            {$where} order by {$order}
                      ",
            'getGoodsAccountInfo' => "
                      SELECT {$fields} FROM
                            {$this->tablePrefix}oc_b2b_order b2b
                      LEFT JOIN {$this->tablePrefix}fc_order_confirm  confirm on confirm.b2b_code=b2b.b2b_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on b2b.b2b_code=voucher.oc_code
                       WHERE
                            {$where} order by {$order}
                      ",
            'getBankInfoInfo' => "
                    SELECT {$fields} FROM
                             {$this->tablePrefix}fc_bank_info
                                    WHERE
                                {$where} order by {$order}",
            'findConfirmData' => "
                  SELECT {$fields} FROM
                             {$this->tablePrefix}fc_order_confirm foc
                  LEFT JOIN {$this->tablePrefix}oc_b2b_order obo ON foc.b2b_code=obo.b2b_code
                  LEFT JOIN {$this->tablePrefix}oc_advance oa ON  foc.b2b_code=oa.adv_code
                  LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend oboe on obo.op_code=oboe.op_code
                  LEFT JOIN {$this->tablePrefix}uc_member um ON oa.uc_code=um.uc_code
                  LEFT JOIN {$this->tablePrefix}sc_store store ON foc.sc_code=store.sc_code
               WHERE
                                {$where} order by {$order}",
            'getCreatOrderInfo' => "SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                      LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                      LEFT JOIN {$this->tablePrefix}tc_pay_voucher voucher on confirm.b2b_code=voucher.oc_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                      LEFT JOIN {$this->tablePrefix}uc_member member  on adv.uc_code=member.uc_code
                      LEFT JOIN {$this->tablePrefix}fc_order_payment payment  on confirm.fc_code=payment.fc_code
                       WHERE
                            {$where} order by {$order}",
            'getAllOrder' => "SELECT {$fields} FROM
                            {$this->tablePrefix}fc_order_confirm  confirm
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order b2b on confirm.b2b_code=b2b.b2b_code
                      LEFT JOIN {$this->tablePrefix}oc_advance adv on confirm.b2b_code=adv.adv_code
                      LEFT JOIN {$this->tablePrefix}oc_b2b_order_extend extend  on b2b.op_code=extend.op_code
                      LEFT JOIN {$this->tablePrefix}sc_store store  on confirm.sc_code=store.sc_code
                      LEFT JOIN {$this->tablePrefix}fc_order_payment payment  on confirm.fc_code=payment.fc_code
                      LEFT JOIN {$this->tablePrefix}uc_member member on adv.uc_code=member.uc_code
                       WHERE
                            {$where} order by {$order}",
        );
        return $sqls[$sql_flag];
    }

	/**
	 * Com.Common.CommonView.FcSql.getRemitOrderByFcCode
	 * 根据 fc_code 获取银行汇款的单据
	 * getRemitOrderByFcCode
	 * @access public
	 * @return void
	 */
	public function getRemitOrderByFcCode($data) {
		if(empty($data['b2b_code'])) {
			return $this->res(null, 6);
		}
		$data = array('b2b_code'=>['IN', $data['b2b_code']]);
		$where = D()->parseWhereCondition($data);
        $sql = "select bo.b2b_code,um.mobile,bo.real_amount as amount from  {$this->tablePrefix}oc_b2b_order bo left join {$this->tablePrefix}uc_member um on bo.uc_code=um.uc_code $where and bo.pay_method='".PAY_METHOD_ONLINE_REMIT."' and bo.pay_status='".OC_ORDER_PAY_STATUS_UNPAY."' and bo.order_status not in('MERCHCANCEL', 'CANCEL')";
        $find = D()->query($sql);
		$b2bCodes = array_column($find, 'b2b_code');
		$res['b2b_codes'] = $b2bCodes;
		$res['find'] = $find;
		return $this->res($res);
	}

    /**
     * Com.Common.CommonView.FcSql.getRemitOrderByB2bCode
     * 根据 fc_code 获取银行汇款的单据
     * getRemitOrderByB2bCode
     * @access public
     * @return void
     */
    public function getRemitOrderByB2bCode($data) {
        if(empty($data['b2b_code'])) {
            return $this->res(null, 6);
        }
        $data = array('adv_code'=>['IN', $data['b2b_code']]);
        $where = D()->parseWhereCondition($data);
        $sql = "select adv.adv_code as b2b_code,cus.mobile,adv.amount from  {$this->tablePrefix}oc_advance adv left join {$this->tablePrefix}uc_customer cus on adv.uc_code=cus.uc_code $where and adv.pay_method='".PAY_METHOD_ONLINE_REMIT."' and adv.status='".OC_ORDER_PAY_STATUS_UNPAY."'";
        $find = D()->query($sql);
        $b2bCodes = array_column($find, 'b2b_code');
        $res['b2b_codes'] = $b2bCodes;
        $res['find'] = $find;
        return $this->res($res);
    }

	/**
	 * Com.Common.CommonView.FcSql.getPaymentOrderUserInfo
	 * 获取财务付款单所属的商品订单的用户信息
	 * @access public
	 * @return void
	 */
	public function getPaymentOrderUserInfo($data) {
		$this->_rule = array(
			array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  财务汇总单编码			* 必须字段
		);

		# 自动校验
		if (!$this->checkInput($this->_rule, $data)) {
			return $this->res($this->getErrorField(), $this->getCheckError());
		}

		$fc_code = $data['fc_code'];

		$pre = $this->tablePrefix;
		$sql = "select
			foc.b2b_code,
			obo.real_amount as amount,
			ss.mobile as sales_mobile,
			obo.salesman_id as sales_id,
			um.commercial_name as sc_name,
			um.`name` as uc_name,
			obo.uc_code
			from {$pre}fc_order_confirm foc
			left join {$pre}oc_b2b_order obo on foc.b2b_code = obo.b2b_code
			left join {$pre}sc_salesman ss on obo.salesman_id = ss.id
			left join {$pre}uc_member um on obo.uc_code = um.uc_code
			where
			foc.fc_code = '{$fc_code}'AND foc.oc_type = 'GOODS'";
		$data = D()->query($sql);
		if(!$data) {
			return $this->res(null, 8051);
		}
		return $this->res($data);
	/*	$data = array(
			array( # a用户 a业务员
				'b2b_code'=>'12400002453',
				'real_amount'=>'156',
				'sales_mobile' =>'111111',
				'sales_id' =>1,
				'sc_name' =>'你猜',
				'uc_name'=>'a',
				'uc_code' => '123213213123'
			),
			array(# a用户 a业务员
				'b2b_code'=>'12400002454',
				'real_amount'=>'88',
				'sales_mobile' =>'111111',
				'sales_id' =>1,
				'sc_name' =>'你猜',
				'uc_name'=>'a',
				'uc_code' => '123213213123'
			),
			array( # b用户 b业务员
				'b2b_code'=>'12400002453',
				'real_amount'=>'156',
				'sales_mobile' =>'66666',
				'sales_id' =>1,
				'sc_name' =>'你猜',
				'uc_name'=>'b',
				'uc_code' => '8888888888888'
			),
			array( # c用户 c业务员
				'b2b_code'=>'12400002453',
				'real_amount'=>'888',
				'sales_mobile' =>'222222222',
				'sales_id' =>5,
				'sc_name' =>'你猜',
				'uc_name'=>'c',
				'uc_code' => '6666666666666'
			),

		); */

	}

	/*
	 * Com.Common.CommonView.FcSql.getPaymentAdvanceUserInfo
	 * 获取财务付款单所属的预付款订单的用户信息
	 * @access public
	 * @return void
	 */
	public function getPaymentAdvanceUserInfo($data) {
		$this->_rule = array(
			array('fc_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  财务汇总单编码			* 必须字段
		);

		# 自动校验
		if (!$this->checkInput($this->_rule, $data)) {
			return $this->res($this->getErrorField(), $this->getCheckError());
		}
		$fc_code = $data['fc_code'];
		$pre = $this->tablePrefix;
		$sql = "select
			foc.b2b_code,
			adv.amount,
			ss.mobile as sales_mobile,
			umer.salesman_id as sales_id,
			um.commercial_name as sc_name,
			um.`name` as uc_name,
			adv.uc_code
			from {$pre}fc_order_confirm foc
			left join {$pre}oc_advance adv on foc.b2b_code = adv.adv_code
			left join {$pre}uc_customer umer on adv.uc_code = umer.uc_code
			left join {$pre}sc_salesman ss on umer.salesman_id = ss.id
			left join {$pre}uc_member um on adv.uc_code = um.uc_code
			where
			foc.fc_code = '{$fc_code}'AND foc.oc_type = 'ADVANCE'";

		$data = D()->query($sql);
		if(!$data) {
			return $this->res(null, 8051);
		}
		return $this->res($data);
		/*	$data = array(
                array( # a用户 a业务员
                    'b2b_code'=>'12400002453',
                    'real_amount'=>'156',
                    'sales_mobile' =>'111111',
                    'sales_id' =>1,
                    'sc_name' =>'你猜',
                    'uc_name'=>'a',
                    'uc_code' => '123213213123'
                ),
                array(# a用户 a业务员
                    'b2b_code'=>'12400002454',
                    'real_amount'=>'88',
                    'sales_mobile' =>'111111',
                    'sales_id' =>1,
                    'sc_name' =>'你猜',
                    'uc_name'=>'a',
                    'uc_code' => '123213213123'
                ),
                array( # b用户 b业务员
                    'b2b_code'=>'12400002453',
                    'real_amount'=>'156',
                    'sales_mobile' =>'66666',
                    'sales_id' =>1,
                    'sc_name' =>'你猜',
                    'uc_name'=>'b',
                    'uc_code' => '8888888888888'
                ),
                array( # c用户 c业务员
                    'b2b_code'=>'12400002453',
                    'real_amount'=>'888',
                    'sales_mobile' =>'222222222',
                    'sales_id' =>5,
                    'sc_name' =>'你猜',
                    'uc_name'=>'c',
                    'uc_code' => '6666666666666'
                ),

            ); */

	}
}

?>
