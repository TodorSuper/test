<?php
/**
 * Created by PhpStorm.
 * User: wangguangjian@liangrenwang.com
 * Date: 2015/9/8
 * Time: 15:09
 */

namespace Base\FcModule\Account;

use System\Base;

class OrderAccount extends Base
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 微信对账(判断订单金额与银行传过来的金额是否相等 if yes->修改信息)
     * Base.FcModule.Account.OrderAccount.wechatUpdate
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     */
    public function wechatUpdate ($params) {
    
        $this->_rule = array(
            array('bank_time', 'require', PARAMS_ERROR, MUST_CHECK),           //  到账日期			* 必须字段
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),           //  交易开始日期		* 必须字段
            array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),           //  交易结束日期		* 必须字段
            array('bank_code','require', PARAMS_ERROR, MUST_CHECK),             //  银行编码	* 必须字段
            array('id','require', PARAMS_ERROR, MUST_CHECK),   //id编号
            array('pay_method','require', PARAMS_ERROR, MUST_CHECK),   //支付类型 微信or先锋支付
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //取出所需银行信息
        $bankWhere['bank_time'] = $params['bank_time'];
        $bankWhere['type'] = $params['pay_method'];
        $bankInfo = D("FcBankInfo")->field("bank_amount,bank_code")->where($bankWhere)->find();
        if(empty($bankInfo['bank_amount'])) return $this->endInvoke(null,8066);
        //获取所有订单信息
        $orderInfo = $this->getOrderInfo($params);
        //判断金额是否相等并且是否允许对账
        if($params['pay_method'] == FC_TYPE_UCPAY){
            if((string)$orderInfo['all_amount'] != (string)$bankInfo['bank_amount']) return  $this->res(null,8099);
        }else{

            if((string)$orderInfo['all_amount'] != bcadd((string)$bankInfo['bank_amount'],(string)$orderInfo['poundage'], 2)) return  $this->res(null,8065);
        }
        //修改数据
        try{
            D()->startTrans();
            //拼接数据
            $confirmUpdateWhere['account_status'] = FC_ACCOUNT_STATUS_NO_ACCOUNT;
            $confirmUpdateWhere['b2b_code'] = array('in', $orderInfo['b2bCodes']);
            //待修改的数据
            $confirmUpdateData['balance_status'] = FC_BALANCE_STATUS_YES_BALANCE;
            $confirmUpdateData['account_status'] = FC_ACCOUNT_STATUS_ACCOUNT;
            $confirmUpdateData['bank_code'] = $params['bank_code'];
            $confirmUpdateData['update_time'] = NOW_TIME;
            //修改财务订单状态
            $updateConfirm = D('FcOrderConfirm')->where($confirmUpdateWhere)->save($confirmUpdateData);
            if(!$updateConfirm) return $this->endInvoke(null,8067);

            // 拼接数据
            $bankUpdateWhere['id'] = $params['id'];
            $bankUpdateWhere['bank_time'] = $params['bank_time'];
            $bankUpdateWhere['type'] = $params['pay_method'];
            $bankUpdateWhere['account_status'] = FC_ACCOUNT_STATUS_NO_ACCOUNT;
            //待修改的数据
            $bankUpdateData['account_status'] = FC_ACCOUNT_STATUS_ACCOUNT;
            $bankUpdateData['bank_code'] = $params['bank_code'];
            $bankUpdateData['update_time'] = NOW_TIME;

            $bankUpdateData['order_amount'] = round($orderInfo['all_amount'],2);
            $bankUpdateData['start_time'] = $params['start_time'];
            $bankUpdateData['end_time'] = $params['end_time'];
            $bankUpdateData['order_num'] = $orderInfo['order_num'];
            $bankUpdateData['cost'] = $orderInfo['poundage'];
            //修改银行绑定数据
            $updateConfirm = D('FcBankInfo')->where($bankUpdateWhere)->save($bankUpdateData);
            if(!$updateConfirm) return $this->endInvoke(null,8068);

            D()->commit();
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL,8069);
        }
        return $this->res(true);
    }

    /**
     * 获取要对账信息
     * Base.FcModule.Account.OrderAccount.getAccountInfo
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     */


    public function getAccountInfo($params) {

        $this->_rule = array(
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),           //  交易开始日期		* 必须字段
            array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),           //  交易结束日期		* 必须字段
            array('pay_method', 'require', PARAMS_ERROR, MUST_CHECK),           //  支付类型 微信or先锋		* 必须字段
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //判断时间是否重复
        if(is_array($this->getBankInfo($params))) return $this->res(null,8070);
        //获取所有订单信息
        $orderInfo = $this->getOrderInfo($params);
        unset($orderInfo['b2bCode']);
        $orderInfo['pay_method'] = $params['pay_method'];
        if($params['pay_method']==FC_TYPE_WEIXIN){
            $orderInfo['acc_add'] = bcsub($orderInfo['all_amount'],$orderInfo['poundage'],2);
        }else{
            $orderInfo['acc_add'] = $orderInfo['all_amount'];
        }
        return $this->res($orderInfo);
    }

    private function getBankInfo($params){
        $end_time = $params['end_time'] - 86399;
        $where = "((start_time >= {$params['start_time']} and end_time <= {$params['end_time']})
         or (start_time = {$params['start_time']})
         or (end_time = {$params['end_time']})
         or (start_time = {$params['start_time']} and end_time <= {$params['end_time']})
         or (start_time >= {$params['start_time']} and end_time = {$params['end_time']})
         or (start_time = {$end_time}))
         and type = '{$params['pay_method']}'
         ";
        $find = D("FcBankInfo")->where($where)->find();
        return $find;
    }

    /**
     * 第三方对账列表
     * Base.FcModule.Account.OrderAccount.wechatLists
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     */

    public function wechatLists ($params) {
        $this->_rule = array(
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),           //  开始时间       非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),            //  结束时间            非必须参数, 默认值 所有
            array('account_status', 'require', PARAMS_ERROR, ISSET_CHECK),             //  对账状态          非必须参数, 默认值 所有
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),             //  结算方          非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK)            //分页
    );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $accountWhere = [];
        ( $params['start_time'] && $params['end_time'] ) ? $accountWhere['bank_time'] = array('BETWEEN', [$params['start_time'], $params['end_time'] ]) : null;
        if(!empty($params['account_status'])){

            if($params['account_status'][FC_ACCOUNT_STATUS_NO_ACCOUNT] && $params['account_status'][FC_ACCOUNT_STATUS_ACCOUNT]){

            }else if($params['account_status'][FC_ACCOUNT_STATUS_ACCOUNT]){
                $accountWhere['account_status'] = FC_ACCOUNT_STATUS_ACCOUNT;
            }else if($params['account_status'][FC_ACCOUNT_STATUS_NO_ACCOUNT]){
                $accountWhere['account_status'] = FC_ACCOUNT_STATUS_NO_ACCOUNT;
            }
        }
        $type = array_filter($params['type']);
        if(!empty($type)){
            if($type[FC_TYPE_WEIXIN] && $type[FC_TYPE_UCPAY]){
                $accountWhere['type'] = array('in',[FC_TYPE_WEIXIN,FC_TYPE_UCPAY]);
            }else if($type[FC_TYPE_WEIXIN]){
                $accountWhere['type'] = FC_TYPE_WEIXIN;
            }else if($type[FC_TYPE_UCPAY]){
                $accountWhere['type'] = FC_TYPE_UCPAY;
            }
        }else{
            $accountWhere['type'] = array('in',[FC_TYPE_WEIXIN,FC_TYPE_UCPAY]);
        }
        //固定条件
//        $accountWhere['type'] = FC_TYPE_WEIXIN;
       // M('Base.OrderModule.B2b.Status')->getRemitBank($data[$key]['pay_method_ext1']);
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $fileConfirm = 'id,start_time,end_time,order_amount,order_num,bank_amount,bank_time,account_status,bank_code,cost,type';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getBankInfoInfo';
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "LENGTH( start_time ) ASC,bank_time DESC";
        $orderParams['page_number'] = $pageNumber;
        $orderParams['where'] = $accountWhere;
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response']);
    }

    //获取所有订单金额 订单号
    private function getOrderInfo ($params) {
        $b2bFiled = 'foc.b2b_code,obo.real_amount,foc.cost';
        $advFiled = 'foc.b2b_code,ad.amount,foc.cost';
        if($params['pay_method']==FC_TYPE_WEIXIN){
            $check = FC_THIRD_STATUS_CHECK;
        }else{
            $check = FC_THIRD_STATUS_NO_CHECK;
        }

//        $weixin =PAY_METHOD_ONLINE_WEIXIN;
        $pay_method = $params['pay_method'];
        $status = OC_ORDER_PAY_STATUS_PAY;
//        $end_time = $params['end_time'] + 86399;//无用代码
        $sql = "SELECT {$b2bFiled} FROM {$this->tablePrefix}fc_order_confirm AS foc
                JOIN {$this->tablePrefix}oc_b2b_order obo ON obo.b2b_code = foc.b2b_code
                WHERE
                    obo.pay_method ='{$pay_method}'
                AND obo.pay_status = '{$status}'
                AND foc.third_status = '{$check}'
                AND obo.pay_time BETWEEN '{$params['start_time']}'
                AND '{$params['end_time']}'
                UNION ALL
                SELECT {$advFiled} FROM {$this->tablePrefix}fc_order_confirm AS foc
                JOIN {$this->tablePrefix}oc_advance ad ON ad.adv_code = foc.b2b_code
                WHERE
                    ad.pay_method = '{$pay_method}'
                AND ad. STATUS = '{$status}'
                AND foc.third_status = '{$check}'
                AND ad.pay_time BETWEEN '{$params['start_time']}'
                AND '{$params['end_time']}'
                ";
        $orderInfo = D()->query($sql);

        //取出所有金额 订单号
        $totalAmount = 0;
        $b2bCodes = [];
        $cost = 0;
        if($pay_method==FC_TYPE_WEIXIN){
            foreach($orderInfo as $val){
                $totalAmount += $val['real_amount'];
                $b2bCodes [] = $val['b2b_code'];
                $cost += $val['cost'];
            }
        }else{
            foreach($orderInfo as $val){
                $totalAmount += $val['real_amount'];
                $b2bCodes [] = $val['b2b_code'];
                if($val['real_amount']<=1000 && $val['real_amount']>0){
                    $cost += 2.00;
                }else{
                    $cost += $val['real_amount']*0.002;
                }
                $cost += $val['cost'];
            }
        }

        //拼接数据
        $response = array(
            'all_amount' => $totalAmount,//总金额
            'b2bCodes' => $b2bCodes, //订单号
            'order_num' =>count($b2bCodes), //订单数量
            'poundage' =>$cost, //手续费
        );
        return $response;
    }
    /**
     * Base.FcModule.Account.OrderAccount.advanceAccount
     * 财务对账  全部订单 预付款订单列表
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function advanceAccount($params){
        $this->_rule = array(
            array('sc_name', 'require', PARAMS_ERROR, ISSET_CHECK),          //  店铺名称
            array('adv_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  预付款订单编号
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),          // 商品 订单编码
            array('bank_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  支付流水号(入金)
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式
            array('remit_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付凭证
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
            array('balance_status', 'require', PARAMS_ERROR, ISSET_CHECK),   //  资金状态			非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示的数量			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //组装查询条件
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        if(!empty($params['adv_code'])){
            $orderWhere['adv.adv_code']=$params['adv_code'];
        }
        if(!empty($params['balance_status'])){
            $orderWhere['confirm.balance_status']=array('in',$params['balance_status']);
        }
        if(!empty($params['bank_code'])){
            $orderWhere['confirm.bank_code']=$params['bank_code'];
        }
        if(!empty($params['sc_name'])){
            $orderWhere['store.name']=$params['sc_name'];
        }
        if(!empty($params['remit_code'])){
            $orderWhere['adv.remit_code']=$params['remit_code'];
        }
        if(!empty($params['pay_method'])){
            $orderWhere['adv.pay_method']=array('in',$params['pay_method']);
        }
        if($params['balance_status'][FC_BALANCE_STATUS_YES_BALANCE]==FC_BALANCE_STATUS_YES_BALANCE && count($params['balance_status'])==1){
            $orderParams['order'] = "confirm.update_time desc";
        }else{
            $orderParams['order'] = "adv.pay_time desc,adv.create_time desc";
        }
        $orderWhere[] = " adv.status = (CASE WHEN adv.pay_method != 'REMIT' THEN 'PAY'   ELSE '' END) OR adv.pay_method_ext1='CMBC'";
        # 时间区间确定
        ( $params['start_time'] && $params['end_time'] ) ? $orderWhere['adv.pay_time'] = array('BETWEEN', [$params['start_time'], $params['end_time'] ]) : null;
        //组装sql语句
        //预付款订单(oc_advance)的数据,订单扩展表(extend),财务确定单表(confirm)
        $fileConfirm = 'store.name as sc_name,
                        adv.adv_code,adv.amount,adv.pay_time,adv.pay_method,adv.pay_method_ext1,adv.status,adv.remit_code,adv.create_time,
                        confirm.oc_type,confirm.bank_code,confirm.balance_status,confirm.account_status,confirm.third_status,confirm.cost,
                        voucher.pay_no';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getAdvanceAccountInfo';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['order'] = "adv.pay_time desc,adv.create_time desc";
        $orderParams['page_number'] = $pageNumber;
        $orderRes = $this->invoke($apiPath, $orderParams);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        $AccountNoConfirm = $this->accountNoConfirm($orderParams,FC_ORDER_CONFIRM_OC_TYPE_ADVANCE);
        $NoAccount = $this->noAccount($orderParams,FC_ORDER_CONFIRM_OC_TYPE_ADVANCE);
        $orderRes['response']['totalnum_noconfirm'] = $AccountNoConfirm['response']['total_item'];
        $orderRes['response']['total_amount'] = $AccountNoConfirm['response']['total_amount'];
        $orderRes['response']['totalnum_noaccount'] = $NoAccount['response']['total_item'];
        return $this->res($orderRes['response']);
    }

    /**
     * Base.FcModule.Account.OrderAccount.goodsAccount
     * 财务对账 全部订单 商品订单
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */

    public function goodsAccount($params){
        $this->_rule = array(
            array('sc_name', 'require', PARAMS_ERROR, ISSET_CHECK),          //  店铺名称
            array('adv_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  预付款订单编号
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),          // 商品 订单编码
            array('bank_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  支付流水号(入金)
            array('pay_method', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式
            array('remit_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付凭证
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
            array('balance_status', 'require', PARAMS_ERROR, ISSET_CHECK),   //  资金状态			非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示的数量			非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //组装查询条件
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        if(!empty($params['b2b_code'])){
            $orderWhere['b2b.b2b_code']=$params['b2b_code'];
        }
        if(!empty($params['balance_status'])){
            $orderWhere['confirm.balance_status']=array('in',$params['balance_status']);
        }
        if(!empty($params['bank_code'])){
            $orderWhere['confirm.bank_code']=$params['bank_code'];
        }
        if(!empty($params['sc_name'])){
            $orderWhere['store.name']=$params['sc_name'];
        }
        if(!empty($params['remit_code'])){
            $orderWhere['extend.remit_code']=$params['remit_code'];
        }
        if(!empty($params['bank_type'])){
            if($params['bank_type'] == PAY_METHOD_REMIT_CMBC){
                $bank_CMB = PAY_METHOD_REMIT_CMB;
                $orderWhere[] = "( b2b.ext1 <>'".$bank_CMB."')";
            } else {
                if($params['pay_method'] == "REMIT"){
                    $orderWhere[] = "( b2b.ext1 = '".$params['bank_type']."' or adv.pay_method_ext1 = '".$params['bank_type']."')";
                }else{
                    $bank_CMB = PAY_METHOD_REMIT_CMB;
                    $orderWhere[] = "( b2b.ext1 ='".$bank_CMB."')";
                }
            }
        }
        if(!empty($params['pay_method'])){
            $orderWhere['b2b.pay_method']=array('in',$params['pay_method']);
        }
        if($params['balance_status'][FC_BALANCE_STATUS_YES_BALANCE]==FC_BALANCE_STATUS_YES_BALANCE && count($params['balance_status'])==1){
            $orderParams['order'] = "confirm.update_time desc";
        }else{
            $orderParams['order'] = "b2b.pay_time DESC,b2b.create_time DESC";
        }
        //默认条件
        $orderWhere[] = "b2b.pay_status = (CASE WHEN b2b.pay_method != 'REMIT' THEN 'PAY'   ELSE '' END) or  b2b.ext1='CMBC' 	OR b2b.ext1 = 'CMB' ";
        # 时间区间确定
        ( $params['start_time'] && $params['end_time'] ) ? $orderWhere['b2b.pay_time'] = array('BETWEEN', [$params['start_time'], $params['end_time'] ]) : null;
        //组装sql语句
        //先查询订单表(b2b_order)的相关数据,订单扩展表(extend),财务确定单表(confirm)
        $fileConfirm = 'store.name as sc_name,
                        b2b.b2b_code,b2b.pay_time,b2b.op_code,b2b.order_status,b2b.order_amout,b2b.pay_status,b2b.pay_method,b2b.ext1,b2b.real_amount as amount,b2b.create_time,
                        extend.remit_code,extend.coupon_amount,
                        confirm.oc_type,confirm.bank_code,confirm.balance_status,confirm.account_status,confirm.third_status,confirm.cost,
                        voucher.pay_no';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        $orderParams['sql_flag'] = 'getGoodsAccountInfo';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['page'] = $page;
        $orderParams['page_number'] = $pageNumber;
        $orderRes = $this->invoke($apiPath, $orderParams);
        if ( 0 != $orderRes['status']) {
            return $this->res(null, 8051);
        }
        $AccountNoConfirm = $this->accountNoConfirm($orderParams,FC_ORDER_CONFIRM_OC_TYPE_GOODS);
        $NoAccount = $this->noAccount($orderParams,FC_ORDER_CONFIRM_OC_TYPE_GOODS);
        $orderRes['response']['totalnum_noconfirm'] = $AccountNoConfirm['response']['total_item'];

        $orderRes['response']['total_amount'] = bcadd($AccountNoConfirm['response']['total_amount'],$AccountNoConfirm['response']['total_coupon_amount'],2);
        $orderRes['response']['totalnum_noaccount'] = $NoAccount['response']['total_item'];

        return $this->res($orderRes['response']);
    }

    /**
     * Base.FcModule.Account.OrderAccount.accountNoConfirm.
     * 财务对账查询已到账订单数,和已到账订单总金额
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     */
    private function accountNoConfirm($params,$type){
        $orderParams = $params;
        if(count($orderParams['where']['confirm.balance_status']['1'])== 1 && $orderParams['where']['confirm.balance_status']['1'][FC_BALANCE_STATUS_NO_BALANCE]){
            $orderRes['response']['totalnum']=0;
            return $this->res($orderRes['response']);
        }elseif(!$orderParams['where']['confirm.balance_status']['1'][FC_BALANCE_STATUS_YES_BALANCE] && $orderParams['where']['confirm.balance_status']['1'][FC_BALANCE_STATUS_BALANCE]){
            $orderRes['response']['totalnum']=0;
            $orderRes['response']['total_amount']=0;
            return $this->res($orderRes['response']);
        }else{
            $orderParams['where']['confirm.balance_status']=FC_BALANCE_STATUS_YES_BALANCE;
        }
        if($type==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
            $count = D('OcB2bOrder')->alias('b2b')
                ->join("{$this->tablePrefix}fc_order_confirm confirm ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                ->join("{$this->tablePrefix}oc_b2b_order_extend extend ON confirm.oc_code=extend.op_code",'LEFT')
                ->join("{$this->tablePrefix}sc_store store ON confirm.sc_code=store.sc_code",'LEFT')
                ->field("count(b2b.id) as total_item   ,sum(b2b.real_amount) as total_amount,sum(extend.coupon_amount) as total_coupon_amount")
                ->where($orderParams['where'])
                ->find();
        }elseif($type==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
            $count = D('OcAdvance')->alias('adv')
                ->join("{$this->tablePrefix}fc_order_confirm confirm ON confirm.b2b_code=adv.adv_code",'LEFT')
                ->join("{$this->tablePrefix}sc_store store ON confirm.sc_code=store.sc_code",'LEFT')
                ->field("count(adv.id) as total_item   ,sum(adv.amount) as total_amount")
                ->where($orderParams['where'])
                ->find();
        }

        return $this->res($count);
    }

    /**
     * Base.FcModule.account.OrderAccount.noAccount.
     * 财务对账查询未到账订单数量
     * 接收数据
     * array $field 限制条件
     * array $where 查询条件
     */
    private function noAccount($params,$type){
        $orderParams = $params;
        if($orderParams['where']['confirm.balance_status']['1'][FC_BALANCE_STATUS_NO_BALANCE] || !$orderParams['where']['confirm.balance_status'] ){
            $orderParams['where']['confirm.balance_status']=FC_BALANCE_STATUS_NO_BALANCE;
        }elseif(!$orderParams['where']['confirm.balance_status']['1'][FC_BALANCE_STATUS_NO_BALANCE] || $orderParams['where']['confirm.balance_status']){
            $orderRes['response']['totalnum']=0;
            return $this->res($orderRes['response']);
        }

        if($type==FC_ORDER_CONFIRM_OC_TYPE_GOODS){
            $count = D('OcB2bOrder')->alias('b2b')
                ->join("{$this->tablePrefix}fc_order_confirm confirm ON confirm.b2b_code=b2b.b2b_code",'LEFT')
                ->join("{$this->tablePrefix}oc_b2b_order_extend extend ON confirm.oc_code=extend.op_code",'LEFT')
                ->join("{$this->tablePrefix}sc_store store ON confirm.sc_code=store.sc_code",'LEFT')
                ->field("count(b2b.id) as total_item")
                ->where($orderParams['where'])
                ->find();
        }elseif($type==FC_ORDER_CONFIRM_OC_TYPE_ADVANCE){
            $count = D('OcAdvance')->alias('adv')
                ->join("{$this->tablePrefix}fc_order_confirm confirm ON confirm.b2b_code=adv.adv_code",'LEFT')
                ->join("{$this->tablePrefix}sc_store store ON confirm.sc_code=store.sc_code",'LEFT')
                ->field("count(adv.id) as total_item")
                ->where($orderParams['where'])
                ->find();
        }

        return $this->res($count);
    }

    /**
     * Base.FcModule.account.OrderAccount.accountExport.
     * 导出对账全部订单相关明细
     * 接收数据
     */
    public function  accountExport($params) {
        $this->_rule = array(
            array('fc_type', 'require', PARAMS_ERROR, MUST_CHECK),          //  导出类型
            array('sc_name', 'require', PARAMS_ERROR, ISSET_CHECK),          //  店铺名称
            array('adv_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  预付款订单编号
            array('b2b_code', 'require', PARAMS_ERROR, ISSET_CHECK),          // 商品 订单编码
            array('bank_code', 'require', PARAMS_ERROR, ISSET_CHECK),          //  支付流水号(入金)
            array(PAY_METHOD_ONLINE_REMIT, 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式 在线支付
            array(PAY_METHOD_ONLINE_WEIXIN, 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式 微信支付
            array(PAY_METHOD_ONLINE_ALIPAY, 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式 支付宝支付
            array(PAY_METHOD_ONLINE_UCPAY, 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付方式 先锋支付
            array(FC_BALANCE_STATUS_NO_BALANCE, 'require', PARAMS_ERROR, ISSET_CHECK),       //  资金状态 未到账
            array(FC_BALANCE_STATUS_YES_BALANCE, 'require', PARAMS_ERROR, ISSET_CHECK),       //  资金状态 已到账
            array(FC_BALANCE_STATUS_BALANCE, 'require', PARAMS_ERROR, ISSET_CHECK),       //  资金状态 已结算
            array('remit_code', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付凭证
            array('pay_status', 'require', PARAMS_ERROR, ISSET_CHECK),       //  支付状态
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       //  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),         //  结束时间			非必须参数, 默认值 所有
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             //  页码数			非必须参数, 默认值 所有
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      //  每页显示的数量			非必须参数, 默认值 所有
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $fc_type = $params['fc_type'];
        $data = array();
        $params['pay_method'] = array_filter([
            PAY_METHOD_ONLINE_REMIT => $params[PAY_METHOD_ONLINE_REMIT],
            PAY_METHOD_ONLINE_WEIXIN => $params[PAY_METHOD_ONLINE_WEIXIN],
            PAY_METHOD_ONLINE_ALIPAY => $params[PAY_METHOD_ONLINE_ALIPAY],
            PAY_METHOD_ONLINE_UCPAY => $params[PAY_METHOD_ONLINE_UCPAY],
        ]);
        $params['balance_status'] = array_filter([
            FC_BALANCE_STATUS_NO_BALANCE => $params[FC_BALANCE_STATUS_NO_BALANCE],
            FC_BALANCE_STATUS_YES_BALANCE => $params[FC_BALANCE_STATUS_YES_BALANCE],
            FC_BALANCE_STATUS_BALANCE => $params[FC_BALANCE_STATUS_BALANCE],
        ]);
        if(!empty($params['balance_status'])){
            $data['where']['confirm.balance_status']=array('in',$params['balance_status']);
        }
        if(!empty($params['bank_code'])){
            $data['where']['confirm.bank_code']=$params['bank_code'];
        }

        if(!empty($params['sc_name'])){
            $data['where']['store.name']=$params['sc_name'];
        }
        $data['title'] = array('订单编号', '付款时间', '付款凭证码','卖家名称','订单状态', '支付方式', '订单金额(元)', '买家实付(元)','手续费(元)','到账金额(元)','优惠金额','到账银行','到账流水号(入金)','资金状态');  //默认导出列标题

        $data['center_flag']  =  SQL_FC;//财务中心
        $apiPath  =  "Com.Common.CommonView.Export.export";
        switch ($fc_type) {
            //商品订单的导出明细
            case 'gALists':
                //组装调用导出api参数
                $data['filename'] = $fc_type;
                $data['fields'] = 'store.name as sc_name,
                        b2b.b2b_code,b2b.pay_time,b2b.op_code,b2b.order_status,b2b.pay_status,b2b.order_amout,b2b.pay_method,b2b.ext1,b2b.real_amount as amount,b2b.create_time,
                        extend.remit_code,extend.coupon_amount,
                        confirm.oc_type,confirm.bank_code,confirm.balance_status,confirm.account_status,confirm.third_status,confirm.cost,
                        voucher.pay_no';
                $data['sql_flag']     =  'getGoodsAccountInfo'; //sql标识
                $data['callback_api'] =  'Com.Callback.Export.FcExport.accountGoodsList';
                if($params['balance_status'][FC_BALANCE_STATUS_YES_BALANCE]==FC_BALANCE_STATUS_YES_BALANCE && count($params['balance_status'])==1){
                    $data['order'] = "confirm.update_time desc";
                }else{
                    $data['order']        =  "b2b.pay_time DESC,b2b.create_time DESC";
                }
                $data['template_call_api'] = 'Com.Callback.Export.Template.gALists';
                if(!empty($params['b2b_code'])){
                    $data['where']['b2b.b2b_code']=$params['b2b_code'];
                }
                if(!empty($params['remit_code'])){
                    $data['where']['extend.remit_code']=$params['remit_code'];
                }
                if(!empty($params['bank_type'])){
                    if($params['bank_type'] == PAY_METHOD_REMIT_CMBC){
                        $bank_CMB = PAY_METHOD_REMIT_CMB;
                        $data['where'][] = "( b2b.ext1 <>'".$bank_CMB."')";
                    } else {
                        if($params['pay_method'] == "REMIT"){
                            $data['where'][] = "( b2b.ext1 = '".$params['bank_type']."' or adv.pay_method_ext1 = '".$params['bank_type']."')";
                        }else{
                            $bank_CMB = PAY_METHOD_REMIT_CMB;
                            $data['where'][] = "( b2b.ext1 ='".$bank_CMB."')";
                        }
                    }
                }
                if(!empty($params['pay_method'])){
                    $data['where']['b2b.pay_method']=array('in',$params['pay_method']);
                }


                //默认条件
                $data['where'][] = "b2b.pay_status = (CASE WHEN b2b.pay_method != 'REMIT' THEN 'PAY'   ELSE '' END) or  b2b.ext1='CMBC' or b2b.ext1='CMB'";
                //限定时间
                ( $params['start_time'] && $params['end_time'] ) ? $data['where']['b2b.pay_time'] = array('BETWEEN', [strtotime($params['start_time'].' 00:00:00'), strtotime($params['end_time'].' 23:59:59')]) : null;
                break;
            //预付款订单对账全部订单导出
            case 'aALists':
                //组装调用导出api参数
                $data['filename'] = $fc_type;
                $data['fields'] = 'store.name as sc_name,
                        adv.adv_code,adv.amount,adv.pay_time,adv.pay_method,adv.pay_method_ext1,adv.status,adv.remit_code,adv.create_time,
                        confirm.oc_type,confirm.bank_code,confirm.balance_status,confirm.account_status,confirm.third_status,confirm.cost,
                        voucher.pay_no';
                $data['sql_flag']     =  'getAdvanceAccountInfo'; //sql标识
                $data['callback_api'] =  'Com.Callback.Export.FcExport.accountAdvanceList';
                if($params['balance_status'][FC_BALANCE_STATUS_YES_BALANCE]==FC_BALANCE_STATUS_YES_BALANCE && count($params['balance_status'])==1){
                    $data['order'] = "confirm.update_time desc";
                }else{
                    $data['order']        =  "adv.pay_time desc,adv.create_time desc";
                }
                $data['template_call_api'] = 'Com.Callback.Export.Template.aALists';
                if(!empty($params['b2b_code'])){
                    $data['where']['adv.adv_code']=$params['b2b_code'];
                }
                if(!empty($params['remit_code'])){
                    $data['where']['adv.remit_code']=$params['remit_code'];
                }
                if(!empty($params['pay_method'])){
                    $data['where']['adv.pay_method']=array('in',$params['pay_method']);
                }
                # 时间区间确定
                ( $params['start_time'] && $params['end_time'] ) ? $data['where']['adv.pay_time'] = array('BETWEEN',  [strtotime($params['start_time'].' 00:00:00'), strtotime($params['end_time'].' 23:59:59')]) : null;
               //固定条件
                $data['where'][] = " adv.status = (CASE WHEN adv.pay_method != 'REMIT' THEN 'PAY'   ELSE '' END) OR adv.pay_method_ext1='CMBC'";
                break;
        }
        $res = $this->invoke($apiPath, $data);
        return $this->endinvoke($res['response'],$res['status'],'',$res['message']);
    }



    /**
     *
     * Base.FcModule.account.OrderAccount.bankList
     * 未知回款列表
     */
    public function bankList($params)
    {
        $this->_rule = array(
            array('bankTime_start', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('bankTime_end', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $bankTime_start = $params['bankTime_start'];
        $bankTime_end = $params['bankTime_end'];
        $bank_type = $params['bank_type'];
        //取出查询条件
        !empty($bankTime_start) && empty($bankTime_end) && $where['bank_time'] = array('egt', $bankTime_start);
        if(!empty($bankTime_start) && !empty($bankTime_end)) {
            $bankTime_end = $bankTime_end + 86399;
            $where['bank_time'] = array('between', array($bankTime_start, $bankTime_end));
        }
        empty($bankTime_start) && !empty($bankTime_end) && $where['bank_time'] = array('elt', $bankTime_end);
        if( $params['status'][0] ) {
            $where['status'] = array('in', $params['status']);  // 处理条件
        }
        if( $bank_type ) {
            $where['account_bank'] = array('in', $bank_type);  // 处理条件
        }
        $where['type'] = PAY_METHOD_ONLINE_REMIT;
        $where['bank_type'] = FC_BANK_TYPE_ACCOUNTED;
        $where['errorReason'] = array('NEQ','');
        $pageNumber = 20;
        $startNo = ($params['page']-1) * 20;
        $count = D('FcBankInfo')->where($where)->count(); // 查询数据总量
        if($count === false) {
            return $this->res(NULL,8071);
        }
        $res['totalnum'] = $count;
        $res['pageNumber'] = $pageNumber;
        $res['lists'] = D('FcBankInfo')->where($where)->order('id desc')->limit($startNo,$pageNumber)->select();  // 查询信息
        if($res['lists'] === false) {
            return $this->res(NULL,8071);
        } else {
            return $this->res($res);
        }
    }


    /**
     *
     * Base.FcModule.account.OrderAccount.getMyOrders
     * 未知回款对账订单列表
     */
    public function getMyOrders($params)
    {
        $this->_rule = array(
            array('order_start', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('order_end', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('remit_code_name', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('pay_name', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('order_code', 'require', PARAMS_ERROR, ISSET_CHECK),
			array('order_amount', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $order_start = $params['order_start'];
        $order_end = $params['order_end'];
        if(!empty($params['pay_name'])) {
            $orderWhere[] = "b2b.client_name='" . $params['pay_name'] ."' OR adv.client_name='" . $params['pay_name'] ."'";
        }
        if(!empty($params['remit_code_name'])) {
            $orderWhere[] = "extend.remit_code='" . $params['remit_code_name'] ."'";
        }
		if(!empty($params['order_code'])) {
            $orderWhere[] = "confirm.b2b_code='" . $params['order_code'] ."'";
        }
		if(!empty($params['order_amount'])) {
            $orderWhere[] = "b2b.real_amount='" . $params['order_amount'] ."' OR adv.amount='" . $params['order_amount'] . "'";
        }
        //取出查询条件
        $order_end = $order_end + 86399;
		if(!empty($params['order_start'])){
			$orderWhere[] = "b2b.create_time  BETWEEN " . $order_start . " AND " . $order_end . " OR adv.create_time BETWEEN "  . $order_start . " AND " . $order_end;
		}	
        $orderWhere[] = "b2b.pay_method='" . FC_TYPE_REMIT ."' OR adv.pay_method='" . FC_TYPE_REMIT . "'";
        $orderWhere[] = "b2b.pay_status='" . OC_ORDER_PAY_STATUS_UNPAY ."' OR adv.status='" . OC_ORDER_PAY_STATUS_UNPAY . "'";
        $orderWhere[] = "b2b.ext1 in ('" . PAY_METHOD_REMIT_CMBC ."','" . PAY_METHOD_REMIT_CMB . "') OR adv.pay_method_ext1 in ('" . PAY_METHOD_REMIT_CMBC ."','" . PAY_METHOD_REMIT_CMB . "')";
        $fileConfirm = array(
		'confirm.oc_type as octype',
		'confirm.b2b_code',
		'b2b.create_time',
		'b2b.real_amount as real_amount',
		'b2b.client_name as b2b_client_name',
		'adv.client_name as adv_client_name',
		'adv.create_time as advtime',
		'adv.remit_code as advremitcode',
		'adv.amount as advamount',
		'b2b.pay_status as paystatus',
		'adv.status as advstatus',
		'store.name as storename',
		'extend.remit_code as remitcode',
		'extend.commercial_name as b2b_commercial_name',
		'member.commercial_name as adv_commercial_name'
		//'extend.remit_code as remitcode', // 买家联系人姓名
		//'extend.remit_code as remitcode', // 买家店铺
		);
		$fileConfirm = implode(',', $fileConfirm);
        $orderWhere[] = "confirm.account_status='" . FC_ACCOUNT_STATUS_NO_ACCOUNT ."'";
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $orderParams['center_flag'] = SQL_FC;
        //$orderParams['sql_flag'] = 'getOrderInfo';
		$orderParams['sql_flag'] = 'getCreatOrderInfo';
        $orderParams['where'] = $orderWhere;
        $orderParams['fields'] = $fileConfirm;
        $orderParams['order'] = "b2b.create_time desc";
        $orderParams['page_number'] = ($params['totalnum'] ? $params['totalnum'] : 1);
        $orderRes = $this->invoke($apiPath, $orderParams);
        return $this->res($orderRes['response'],0);
    }

    /**
     * 未知回款关联
     * Base.FcModule.account.OrderAccount.relateunKnow
     */
    public function relateunKnow($params) {
        if(empty($params['orders'])) {
            return $this->endInvoke(null,8107);
        }
		if(empty($params['bank_ids'])) {
            return $this->endInvoke(null,8107);
        }
		if(count($params['bank_ids']) > 10) {
			return $this->endInvoke(null,8108);
		}
		// 查询出订单信息
		$order_where['b2b_code'] = array('in', $params['orders']);
		$order_info = D('OcB2bOrder')->where($order_where)->select();
		$tag3 = 0;
		$tag4 = 0;
		$tag5 = 0;
		// 查询出银行明细信息
		$bank_info_where['id'] = array('in', $params['bank_ids']);
		$bank_info_where['bank_type'] = FC_BANK_TYPE_ACCOUNTED;	// 只取入账
		$bank_info = D('FcBankInfo')->where($bank_info_where)->select();
		$bank_codes = array();
		$pay_names = array();
		foreach($bank_info as $k1=>$v1) {
			// 先判断到账明细是否已经关联过
			if($v1['status'] != FC_STATUS_NO ) {
				return $this->endInvoke(null,8112);
			}
			if($v1['account_bank'] != PAY_METHOD_REMIT_CMB) {
				$bank_info[$k1]['account_bank'] = PAY_METHOD_REMIT_CMBC;
			}
		}
		foreach($bank_info as $k=>$v) {
			if($v['account_bank'] == PAY_METHOD_REMIT_CMBC) {
				$tag3 = 1;
				continue;
			}
			if($v['account_bank'] == PAY_METHOD_REMIT_CMB) {
				$tag4 = 1;
				continue;
			}
			$bank_codes[] = $v['bank_code'];
			$pay_names[] = $v['pay_name'];
		}
		if($tag3==1 && $tag4==1) {	// 如果选择的明细中存在多个银行, 则提示用户
			return $this->endInvoke(null,8109);
		}
		foreach($order_info as $key=>$val) {
			// 仅支持同一买家的订单
			if($val['uc_code'] != $order_info[0]['uc_code']) {
				$tag5 = 1;
			}
			if($val['ext1'] != PAY_METHOD_REMIT_CMB) {
				$order_info[$key]['ext1'] = PAY_METHOD_REMIT_CMBC;
			}
		}	
		if($tag5 == 1) {	// 仅支持同一买家的订单
			return $this->endInvoke(null, 8111);
		}
		
		$bank_id_where['id'] = array('in', $params['bank_ids']);
		$bank_amount = D('FcBankInfo')->where($bank_id_where)->sum('bank_amount');
		$order_id_where['b2b_code'] = array('in', $params['orders']);
		$order_amount = D('OcB2bOrder')->where($order_id_where)->sum('real_amount');
		//L($params);
		//L($bank_amount);
		//L($order_amount);
        if($order_amount != $bank_amount) {  // 订单金额不等于到账金额,则无法关联
            // 提示 不匹配需要原路返回
            return $this->endInvoke(null,8088);
        }
		/**
		 * 如果所选订单属于同一买家, 并且所选到账明细和所选订单的金额匹配,则进行如下操作:
		 * 1. 修改订单为关联状态, 订单的流水号以及订单的付款人( 选择多笔明细的话, 用逗号隔开 )
		 * 2. 修改所选明细对应的关联订单数量和金额 ( 每笔明细对应的订单数量和金额 都为所关联订单的总数量和总金额 )
		 * 3. 修改订单所对应的付款银行为所选明细的到账银行
		 */
        try{
            D()->startTrans();
            $where['b2b_code'] = array('in', $params['orders']);
            $data['bank_code'] = implode(',', $bank_codes);
			$data['pay_name'] = implode(',', $pay_names);
            $data['account_status'] = FC_ACCOUNT_STATUS_ACCOUNT;
            $data['balance_status'] = FC_BALANCE_STATUS_YES_BALANCE;
			$data['update_time'] = NOW_TIME;
            $confirmRes = D('FcOrderConfirm')->where($where)->save($data); // 关联order_confirm表( 修改 account_status, 修改用户选中的关联订单 bank_code 为银行汇款明细中的汇款码)
            if ($confirmRes == FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
			// 修改订单的付款银行为到账银行
			$orderwhere['b2b_code'] = array('in', $params['orders']);
			$ext1_data['ext1'] = $bank_info[0]['account_bank'];
			$b2bupdate = D('OcB2bOrder')->where($orderwhere)->save($ext1_data);
            $uData['status'] = FC_STATUS_END;
            $uData['account_status'] = FC_ACCOUNT_STATUS_ACCOUNT;
            $uData['order_num'] = count($params['orders']);  // 关联订单数目
            $uData['order_amount'] = $order_amount;
			$b_where['id'] = array('in',$params['bank_ids']);
            $bankRes = D('FcBankInfo')->where($b_where)->save($uData);// 关联bank_info表 修改为已处理, 已对账
            if ($bankRes === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
            D()->commit();
        } catch (\Exception $ex) {
            L($ex->getMessage());
            D()->rollback();
            return $this->res(NULL,6703);
        }
        return $this->res($bankRes,0);
    }
	
	/**
	 * Base.FcModule.account.OrderAccount.getBankMoney
	 */
	public function getBankMoney($params) {
		$where['id'] = array('in', $params);
		$bank_amount = D('FcBankInfo')->where($where)->sum('bank_amount');
		$lists = D('FcBankInfo')->where($where)->select();
		$res['b_amount'] = $bank_amount;
		$res['lists'] = $lists;
		if(!$res) {
            return $this->res(NULL,8071);
        }
        return  $this->res($res,0);
    }
	
	
    /**
     * 未知回款"资金原路返回"
     * Base.FcModule.account.OrderAccount.returnunKnow
     */
    public function returnunKnow($params) {
        $this->_rule = array(
            array('id', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = array(
            'status' => FC_STATUS_CLOSE,
        );
        $res = D('FcBankInfo')->where("id =" . $params['id'] . " AND status<>'" . FC_STATUS_CLOSE . "'")->save($data);
        if(!$res) {
            return $this->res(NULL,8087);
        }
        return  $this->res($res,0);
    }

}
