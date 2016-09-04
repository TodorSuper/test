<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2015/9/8
 * Time: 15:09
 */

namespace Base\FcModule\Detail;

use System\Base;

class Order extends Base{
    public function __construct() {
        parent::__construct();
    }


    /**
     * Base.FcModule.Detail.Order.orderList
     *  交易流水
     * @param array $data
     */
    public function orderList($data){

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),            #  账户编码				* 必须字段
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),            #  页码				非必须参数, 默认值 1
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),    #  每页行数			非必须参数, 默认值 20
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),        #  开始时间			非必须参数, 默认值 所有
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),        #  结束时间			非必须参数, 默认值 所有
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),            #  交易类型			非必须参数, 默认值 所有
        );

        if (!$this->checkInput($this->_rule, $data)) # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());

        # 生成where条件
		!empty($data['oc_code']) && $where['tci.oc_code'] = $data['oc_code'];
        !empty($data['b2b_code'])&& $where['tci.b2b_code'] = $data['b2b_code'];
        isset($data['pay_time'])? $where['tci.pay_time'] = $data['pay_time']:null;
        isset($data['name'])? $where['sc.name'] = $data['name']:null;
        $where['tci.pay_status'] = OC_ORDER_PAY_STATUS_PAY;//已支付
        isset($data['pay_method'])?$where['tci.pay_method'] = $data['pay_method']:$where['tci.pay_method'] =   array('in',array(PAY_METHOD_ONLINE_WEIXIN,PAY_METHOD_ONLINE_ALIPAY,PAY_METHOD_ONLINE_CHINAPAY,PAY_METHOD_ONLINE_REMIT,PAY_METHOD_ONLINE_UCPAY));
		$where['tci.sc_code'] =  $data['sc_code'];
		$where['foc.f_status'] = array('in', $data['f_status']);
        # 默认值
        $page = isset($data['page']) ? $data['page'] : 1;
        $pageNumber = isset($data['page_number']) ? $data['page_number'] : 20;
        # commonView
        $aggre = array(
            array('sum','tci.real_amount','total_amount'),
            array('sum','sc.coupon_amount','total_coupon_amount'),
        );
        $params['aggre'] = $aggre;
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $params['center_flag'] = SQL_FC;
        $params['sql_flag'] = 'getFcOrderList';
        $params['where'] = $where;
        $params['page'] = $page;
        $params['order'] = "tci.pay_time desc";
        $params['page_number'] = $pageNumber;
        $params['fields'] = 'tci.b2b_code,tci.op_code,tci.client_name,tci.pay_method,tci.uc_code,tci.real_amount,tci.sc_code,tci.order_amout,tci.order_status,tci.pay_status,tci.pay_time,ss.pay_no,ss.oc_code,sc.commercial_name as name,sc.coupon_amount';
        $res = $this->invoke($apiPath, $params);
        $res['response']['total_amount'] = bcadd($res['response']['total_amount'],$res['response']['total_coupon_amount'],2);
        return $this->res($res['response'], $res['status']);
    }
    
    /**
     * Base.FcModule.Detail.Order.orderAmount
     *  交易流水未转出订单总额
     * @param array $data
     */
    public function orderAmount($data){

        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),            #  账户编码				* 必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $data)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        !empty($data['sc_code']) && $where['tci.sc_code'] = $data['sc_code'];
        !empty($data['oc_code']) && $where['tci.oc_code'] = $data['oc_code'];
        !empty($data['b2b_code'])&& $where['tci.b2b_code'] = $data['b2b_code'];
        isset($data['pay_time'])? $where['tci.pay_time'] = $data['pay_time']:null;

        $where['tci.pay_status'] = OC_ORDER_PAY_STATUS_PAY;//已支付
        $where['foc.f_status'] = array('in', $data['f_status']);
        isset($data['pay_method'])?$where['tci.pay_method'] = $data['pay_method']:$where['tci.pay_method'] =   array('in',array(PAY_METHOD_ONLINE_WEIXIN,PAY_METHOD_ONLINE_ALIPAY,PAY_METHOD_ONLINE_UCPAY,PAY_METHOD_ONLINE_REMIT));
        # 默认值
        $fileConfirm = 'sum(tci.real_amount) as price ,sum(sc.coupon_amount) as coupon_amount';
        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $params['center_flag'] = SQL_FC;
        $params['sql_flag'] = 'getFcOrderList';
        $params['where'] = $where;
        $params['fields'] = $fileConfirm;
        $params['order'] = "null";
        $res = $this->invoke($apiPath, $params);
        $total = $res['response']['lists'][0];
        $res['response']['lists'][0]['price'] = bcadd($total['price'],$total['coupon_amount'],2);
        return $this->res(($res['response']['lists'][0]));

    }
    /**
     * Base.FcModule.Detail.Order.findPayment
     * 财务付款汇总单 未付款、已付款
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */
    public function findPayment($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),            #  商户编码				* 必须字段
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),            #  付款编码				* 非必须字段
            array('bank_code', 'require', PARAMS_ERROR, ISSET_CHECK),            # 银行编码				* 非必须字段
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),            #  开始时间			* 非必须字段
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),            # 结束时间			    * 非必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if($params['bank_code']) $where['bank_code'] = $params['bank_code'];
        if($params['fc_code']) $where['fc_code'] = $params['fc_code'];
        ( $params['start_time'] && $params['end_time'] ) ? $where['affirm_time'] = array('BETWEEN', [strtotime($params['start_time']), strtotime($params['end_time'])+86400 ]) : null;
        $field = ['fc_code,bank_code,remark,affirm_time,amount'];
        $where['sc_code'] = $params['sc_code'];
        $where['status'] = FC_STATUS_PAYMENT;
        $page = $params['page'] ? $params['page'] : 1;
        $page_number = $params['page_number'] ? $params['page_number'] : 20;
        $field[] = 'create_time';
        $lists = D('FcOrderPayment')->where($where)->field($field)->order('affirm_time desc,create_time desc')->page($page,$page_number)->select();
        $amount = D('FcOrderPayment')->where($where)->field(' sum(amount) as total_amount')->find();
        $totalnum =  D('FcOrderPayment')->where($where)->count();
        $res = array(
            'totalnum'=> $totalnum,
            'lists' => $lists,
            'total_amount'=>($amount['total_amount'])?$amount['total_amount']:0,
            'page' => $page,
            'page_number' => $page_number,
        );

        return $this->res($res);
    }
    /**
     * Base.FcModule.Detail.Order.findConfirmList
     * 财务付款汇总单 未付款、已付款
     * 接收数据
     * array $where 查询条件
     * array $field 限制条件
     * int   $page  当前页面
     * int   $page_number 查询的条数
     */
    public function findConfirmList($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),            #  商户编码				* 必须字段
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),            #  付款编码				* 非必须字段
            array('bank_code', 'require', PARAMS_ERROR, ISSET_CHECK),            # 银行编码				* 非必须字段
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),            #  开始时间			* 非必须字段
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),            # 结束时间			    * 非必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        if($params['bank_code']) $where['bank_code'] = $params['bank_code'];
        if($params['fc_code']) $where['fc_code'] = $params['fc_code'];
        ( $params['start_time'] && $params['end_time'] ) ? $where['affirm_time'] = array('BETWEEN', [strtotime($params['start_time']), strtotime($params['end_time'])+86400 ]) : null;
        $field = ['fc_code,bank_code,remark,affirm_time,amount'];
        $where['sc_code'] = $params['sc_code'];
        $where['status'] = FC_STATUS_PAYMENT;
        $page = $params['page'] ? $params['page'] : 1;
        $page_number = $params['page_number'] ? $params['page_number'] : 20;
        $field[] = 'create_time';
        $lists = D('FcOrderPayment')->where($where)->field($field)->order('affirm_time desc,create_time desc')->page($page,$page_number)->select();
        $amount = D('FcOrderPayment')->where($where)->field(' sum(amount) as total_amount')->find();
        $totalnum =  D('FcOrderPayment')->where($where)->count();
        $res = array(
            'totalnum'=> $totalnum,
            'lists' => $lists,
            'total_amount'=>($amount['total_amount'])?$amount['total_amount']:0,
            'page' => $page,
            'page_number' => $page_number,
        );

        return $this->res($res);
    }


    /**
     * Base.FcModule.Detail.Order.totalAmount
     *  交易流水已转出订单总额
     * @param array $data
     */
    public function payMentAmount($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),            #  商户编码				* 必须字段
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $where['sc_code'] = $params['sc_code'];
        $where['status'] = FC_STATUS_PAYMENT;
        $amount = D('FcOrderPayment')->where($where)->field(' sum(amount) as total_amount')->find();
        return $this->res(array('total_amount'=>$amount['total_amount']));
    }

    /**
     * Base.FcModule.Detail.Order.payDetail
     * pop 财务付款汇总详情页
     * 接收数据
     * array $where 查询条件
     */
    public function payDetail($params){


        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),           //  商家编码			* 必须字段
            array('fc_code', 'require', PARAMS_ERROR, ISSET_CHECK),           //  付款编码			*非必须参数, 默认值 所有
        );
        // 自动校验
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $data = array(
            'fc_code'=>$params['fc_code'],
            'sc_code'=>$params['sc_code']
        );

        $where = [
            'confirm.fc_code'=>$params['fc_code'],
            'confirm.sc_code'=>$params['sc_code']
        ];
        $pay_ment = D('FcOrderPayment')->where($data)->find();
        $field = 'adv.amount as adv_amount,adv.adv_code,adv.pay_method as adv_pay_method,
                 adv.pay_method_ext1,adv.pay_time as adv_pay_time,confirm.b2b_code,b2b.real_amount as amount,
                 b2b.order_amout,b2b.pay_time,b2b.client_name,b2b.ext1,ext.commercial_name,ext.coupon_amount,
                 b2b.pay_method,store.name as sc_name,confirm.oc_code,confirm.oc_type,confirm.sc_code,ext.remit_code,
                 um.commercial_name as um_commercial_name,um.name as um_name';

        $list_confirm = D('FcOrderConfirm')->field($field)->alias('confirm')
            ->join("{$this->tablePrefix}oc_b2b_order b2b ON confirm.b2b_code=b2b.b2b_code",'LEFT')
            ->join("{$this->tablePrefix}oc_b2b_order_extend ext ON b2b.op_code=ext.op_code",'LEFT')
            ->join("{$this->tablePrefix}sc_store store ON store.sc_code=confirm.sc_code",'LEFT')
            ->join("{$this->tablePrefix}oc_advance adv ON confirm.b2b_code=adv.adv_code",'LEFT')
            ->join("{$this->tablePrefix}uc_member um ON um.uc_code=adv.uc_code",'LEFT')
            ->where($where)
            ->select();
        $status = M('Base.OrderModule.B2b.Status.getPayMethod');
        $list['pay_ment'] = $pay_ment;
        $list['pay_confirm'] = $list_confirm;
        $list['pay_method_list'] = $status->getPayMethod();

        return $this->endInvoke($list);

    }







}

?>
