<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关模块
 */

namespace Base\OrderModule\B2b;
use System\Base;
class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }

    /**
     * 创建b2b订单
     * Base.OrderModule.B2b.Order.add
     * @param type $params
     */
    public function add($params) {
        //验证参数

//        $this->startOutsideTrans();  //必须开始事务
        $statusModel =  M('Base.OrderModule.B2b.Status.groupStatusList');
        $pay_type  =  $statusModel->getPayType();
        $pay_type  =  array_keys($pay_type);
        $ship_method =  $statusModel->getShipMethodList();
        $ship_method = array_keys($ship_method);

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('coupon_code', 'require', PARAMS_ERROR, ISSET_CHECK), //促销券编码
            array('sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家商品编码
            array('goods_number', 'require', PARAMS_ERROR, HAVEING_CHECK), //购买数量
            array('goods_number', 0, PARAMS_ERROR, HAVEING_CHECK, 'gt'), //购买数量
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function'), //购物车中的商品id
            array('address_id', 'require', PARAMS_ERROR, MUST_CHECK), //收货地址id
            array('buy_from', array(OC_B2B_APP, OC_B2B_WEIXIN), PARAMS_ERROR, MUST_CHECK, 'in'), //购买来源  必须是  app 或者  微信
            array('is_cart', array('YES', 'NO'), PARAMS_ERROR, MUST_CHECK, 'in'), //是否是购物车购买
            array('pay_type', $pay_type, PARAMS_ERROR, MUST_CHECK, 'in'), //是否是购物车购买
            array('ship_method', $ship_method, PARAMS_ERROR, MUST_CHECK, 'in'), //配送方式
            array('term_method', array(PAY_TYPE_TERM_MONTH,PAY_TYPE_TERM_PERIOD), PARAMS_ERROR, ISSET_CHECK, 'in'), //账期支付方式
            array('period_day','require' , PARAMS_ERROR, ISSET_CHECK), //账期支付为  期结时候的天数
            array('remark', 'require', PARAMS_ERROR, ISSET_CHECK, ), //买家留言
            array('openid', 'require', PARAMS_ERROR, ISSET_CHECK), //openid
            array('order_type', 'require', PARAMS_ERROR, ISSET_CHECK), //openid
            array('is_show', 'require', PARAMS_ERROR, ISSET_CHECK), //是不是满足满返的条件
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code        = $params['uc_code'];
        $sc_code        = $params['sc_code'];
        $sic_code       = $params['sic_code'];
        $goods_number   = $params['goods_number'];
        $sic_codes      = array_unique($params['sic_codes']);
        $address_id     = $params['address_id'];
        $buy_from       = $params['buy_from'];
        $is_cart        = $params['is_cart'];
        $remark         = $params['remark'];   //买家备注  array('sic_code'=>'remark');  array('1100123'=>'发顺丰物流啊')
        $pay_type       = $params['pay_type'];
        $ship_method    = $params['ship_method'];
        $term_method    = $params['term_method'];
        $period_day     = $params['period_day'];
        $openid         = $params['openid'];
        $order_type     = $params['order_type'];
//        $remit_bank     = $params['remit_bank'];  //银行汇款的时候选择的银行
        //验证是否允许购买
        //获取购买商品信息
        if ($is_cart == 'YES') {
            //购物车购买
            $goods_info = $this->getCartGoodsInfo($uc_code, $sic_codes);
        } else if ($is_cart == 'NO') {
            //单件商品购买
            $goods_info = $this->getStoreItemInfo($sic_code, $goods_number);
        }
        try {
            D()->startTrans();
            //生成支付码
            $op_code       = $this->generateOrderCode(OC_B2B_ORDER_OP);
            $b2b_code = $this->generateOrderCode(OC_B2B_ORDER_B2B);
            //获取商品的促销信息  当前只考虑一个订单只有一个商家的情况
            $goods_info = $this->getSpc($goods_info, $goods_info[0]['sc_code'],'NO');
            //处理购买商品信息(验证 最小起购量  和 库存  按商家拆分订单 )
            $ori_goods_info    = $this->setGoodsData($goods_info,$op_code,$b2b_code);
            $goods_info    = $ori_goods_info['order_goods'];
            $sc_codes      = $ori_goods_info['sc_codes'];
            $gift_item     = $ori_goods_info['gift_item'];  //赠品
            $spc_codes     = $ori_goods_info['spc_codes'];  //促销编码
            //获取地址信息
            $address_info  = $this->getAddress($uc_code, $address_id);
            //获取用户信息
            $user_info     = $this->getUserInfo($uc_code);
            //如果促销编码不为空  则获取该促销活动已经购买过的商品
            $buy_number = array();
            if(!empty($spc_codes)){
                $buy_number = $this->getBuyNumber($spc_codes, $uc_code);
            }
             //处理客户信息
            if($is_cart == 'NO'){
                $sic_codes[] = $sic_code;
            }

            $customer_info = $this->disposeCustomer($sc_codes, $uc_code, $user_info['real_name'], $user_info['mobile']);
            if($customer_info[$uc_code.'_'.$sc_code]['invite_from'] == 'UC'){
                //如果是平台的客户，查询是不是在此商家下下过单
                $arr = array('sc_code'=>$sc_code,'uc_code'=>$uc_code);
                $info =$this->getPlatCustomer($arr);
                //如果没下过单则添加
                if(!$info){
                    $data = array(
                        'sc_code'=>$sc_code,
                        'uc_code'=>$uc_code,
                        'name'=>$customer_info[$uc_code.'_'.$sc_code]['name'],
                        'mobile'=>$customer_info[$uc_code.'_'.$sc_code]['mobile'],
                        'create_time'=>NOW_TIME,
                        'update_time'=>NOW_TIME,
                        'status'=>'ENABLE',
                        'invite_from'=>'UC',
                    );
                    $result = $this->addPlatCustomer($data);
                    if($result===false){
                        return $this->res('',6710);
                    }
                }
            }
            $coupon_code = $params['coupon_code'];

            //得到促销编码的信息
            $coupon = $this->_getCoupon($coupon_code,$uc_code);
            //如果有优惠券则把优惠券状态改为已占用
            if($coupon['coupon_code']){
                //首先判断前置状态
                $this->_operate($coupon);
                $this->_setStatus($coupon);
            }
            $is_show = $params['is_show'];
            //根据商品信息拆分子订单（包括扣减库存，拆分子订单,生成订单号，插入子订单，插入订单商品）

            $order_info_return    = $this->splitOrderByGoods($goods_info,$a, $user_info, $op_code, $is_cart, $buy_from,$remark,$pay_type,$ship_method,$customer_info,$gift_item,$buy_number,$term_method,$period_day,$order_type,$b2b_code,$coupon,$is_show);
            $order_info           = $order_info_return['order_info'];
//            $b2b_code = $order_info[0]['b2b_code'];

           //如果是买家自提  则生成取货码
           $pick_up_code = '';
           if($ship_method == SHIP_METHOD_PICKUP){
               $pick_up_code = $this->generatePickUpCode($b2b_code);
           }

            //生成订单扩展信息
            $order_extend_info    = $this->orderExtend($order_info, $address_info, $op_code,$customer_info[$uc_code.'_'.$sc_codes[0]],$pick_up_code,$remark,$coupon);
            //如果是购物车购买,清除购物车
            if($is_cart == 'YES'){
                $this->clearCart($uc_code, $sic_codes);
            }
            
            //增加下单动作
             $this->addAction($pay_method, $uc_code, $b2b_code,$pay_type);
            $often_buy_data = array(
                'sic_codes'=>$sic_codes,
                'uc_code'  =>$uc_code,
                'sc_code'  =>$sc_codes[0],
            );
            $res = $this->push_queue('Base.OrderModule.B2b.Order.oftenBuy', $often_buy_data,0);

            $weixinMsg = array(
                        'order_sn'     => $b2b_code, 
                        'goods_name'   => '粮人网商品', 
                        'goods_number' => $order_extend_info['total_nums'],
                        'pay_price' => $order_extend_info['total_real_amount'],
                        'uc_code' => $uc_code,
                        'pay_type' => $pay_type,
                        'openid'   => $openid,
                        );
             //发送下单通知
            $this->push_queue('Base.OrderModule.B2b.Order.sendOrderMsg', $weixinMsg,0);
            //异步生成提货码二维码
            if($ship_method == SHIP_METHOD_PICKUP){
                $pick_data = array(
                    'b2b_code'=>$b2b_code,
                    'pick_up_code'=>$pick_up_code,
                );
              $pick_up_res =  $this->push_queue('Base.OrderModule.B2b.Order.genePickUpQrcode',$pick_data ,1,3);
              if($pick_up_res === FALSE){
                  return $this->res(FALSE,6059);
              }
            }
            
            //事务提交
            $res = D()->commit();
            if($res === FALSE){
                throw new \Exception('提交事务失败',17);
            }
            
            
            
            
            $temp_customer = $customer_info[$uc_code.'_'.$sc_codes[0]];
            //返回信息
            $return_data = array(
                'order_amount' => $order_extend_info['total_real_amount'],
                'total_nums'   => $order_extend_info['total_nums'],
                'op_code'      => $op_code,
                'sc_code'=>$sc_codes[0],
                'client_mobile'=>$temp_customer['mobile'],
                'client_name'=>$temp_customer['name'],
                'client_commercial_name'=>$temp_customer['commercial_name'],
                'salesman_id'=>$temp_customer['salesman_id'],
                'pick_up_code'=>$pick_up_code,
                'ship_method'=>$ship_method,
                'b2b_code'   => $b2b_code
            );
            return $this->res($return_data);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(NULL, 6009);
        }
    }

    private function _operate($coupon){
        $apiPath = 'Base.UserModule.Coupon.Coupon.operate';
        $data = [
            'coupon_code'=>$coupon['coupon_code'],
             'operate_status'=>'occupy'
        ];
        $status = $this->invoke($apiPath,$data);
        if($status['status']!==0){
            return $this->endInvoke('',$status['status']);
        }
        return true;
    }
    private function _setStatus($coupon){
        $apiPath = 'Base.UserModule.Coupon.Coupon.setStatus';
        $data = [
            'coupon_code'=>$coupon['coupon_code'],
            'flag'=>'occupy'
        ];
        $coupon_info = $this->invoke($apiPath,$data);
        if($coupon_info['status']!==0){
            return $this->endInvoke('',$coupon_info['status']);
        }
    }
    private function _getCoupon($coupon_code,$uc_code){
      if($coupon_code){
          $apiPath = 'Base.UserModule.Coupon.Coupon.getCoupon';
          $data = [
              'coupon_code'=>$coupon_code,
              'uc_code'=>$uc_code
          ];
          $coupon_info = $this->invoke($apiPath,$data);
          if($coupon_info['status']!==0){
             return $this->endInvoke('',$coupon_info['status']);
          }else{
              $coupon = [
                  'coupon_code'=>$coupon_info['response']['coupon_code'],
                  'coupon_amount'=>$coupon_info['response']['price'],
                  'active_code'=>$coupon_info['response']['active_code'],
                  'limit_money'=>$coupon_info['response']['limit_money'],
              ];
          }

      }else{
          $coupon = [
              'coupon_code'=>'',
              'coupon_amount'=>'',
              'active_code'=>'',
              'limit_money'=>0
          ];
      }
      return $coupon;
    }
    private  function getPlatCustomer($arr){
        $apiPath ='Base.UserModule.Customer.Customer.getPlatCustomer';
        $res = $this->invoke($apiPath,$arr);
       return $res['response'];
    }
    //添加平台用户购买信息
    private function addPlatCustomer($data){
        $apiPath ='Base.UserModule.Customer.Customer.addPlatCustomer';
        $res = $this->invoke($apiPath,$data);
        if($res['status']!==0){
            return false;
        }
        return true;
    }
    /**
     * 展示即将下单的信息
     * Base.OrderModule.B2b.Order.sendOrderMsg
     * @param type $params
     */
    public function sendOrderMsg($params)
    {
        $params = $params['message'];
//        $apiPath          = 'Base.WeiXinModule.User.User.getWeixinInfo';
//        $data             = $this->invoke($apiPath, array('uc_code'=>$params['uc_code']));
//        
//        if($data['status'])
//        {
//            return $this->res($data['response'],$data['status']);
//        }

        
//        $params['openid']   = $data['response']['open_id'];
//        $params['url_info'] = C('DEFAULT_WEIXIN_URL').'OrderInfo/Index2/orderid/'.$params['order_sn'].'.html';
        $params['url_info'] = C('STATIC_DOMAIN')."static/views/order/orderDetail.html?id={$params['order_sn']}";
        $apiPath            = 'Com.Common.Message.WxTpl.mkOrder';
        $data = $this->invoke($apiPath, $params);

        return $this->res($data['response'],$data['status']);

    }

    /**
     * 展示即将下单的信息
     * Base.OrderModule.B2b.Order.showOrderInfo
     * @param type $params
     */
    public function showOrderInfo($params) {
         $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家商品编码
            array('goods_number', 'require', PARAMS_ERROR, HAVEING_CHECK), //购买数量
            array('goods_number', 0, PARAMS_ERROR, HAVEING_CHECK, 'gt'), //购买数量
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function'), //购物车id
//            array('buy_from', array(OC_B2B_APP, OC_B2B_WEIXIN), PARAMS_ERROR, MUST_CHECK, 'in'), //购买来源  必须是  app 或者  微信
            array('is_cart', array('YES', 'NO'), PARAMS_ERROR, MUST_CHECK, 'in'), //是否是购物车购买
            array('address_id', 'require', PARAMS_ERROR,ISSET_CHECK), //是否是购物车购买
            
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $uc_code = $params['uc_code'];
        $sic_code = $params['sic_code'];
        $goods_number = $params['goods_number'];
        $sic_codes = array_unique($params['sic_codes']);
        $address_id = $params['address_id'];
//        $buy_from = $params['buy_from'];
        $is_cart = $params['is_cart'];

        $total_nums = 0;  //总数量
        $total_amount = 0;  //总金额
        //获取购买商品信息
        if ($is_cart == 'YES') {
            //购物车购买
            $goods_info = $this->getCartGoodsInfos($uc_code, $sic_codes);
        } else if ($is_cart == 'NO') {
            //单件商品购买
            $goods_info = $this->getStoreItemInfo($sic_code, $goods_number);
        }
        //获取促销信息  当前只考虑 一个订单只有一个商家的情况
        $goods_info = $this->getSpc($goods_info, $goods_info[0]['sc_code'],'NO');
        //获取购买商品信息
        $ori_goods_info = $this->setGoodsData($goods_info);
        $order_goods    = $ori_goods_info['order_goods'];
        $sc_codes      = $ori_goods_info['sc_codes'];
        $gift_item     = $ori_goods_info['gift_item'];  //赠品
        $spc_codes     = $ori_goods_info['spc_codes'];  //促销编码
        //如果促销编码不为空  则获取该促销活动已经购买过的商品
        $buy_number = array();
        if(!empty($spc_codes)){
            $buy_number = $this->getBuyNumber($spc_codes, $uc_code);
        }
        //处理拆分商品
        $splite_info = $this->disposeOrderGoods($goods_info, $buy_number, $gift_item);
        $goods_info = $splite_info['goods_info'];
        $total_nums  = $splite_info['total_num'];
        $total_amount = $splite_info['order_amount'];
        $sc_codes   = array_keys($ori_goods_info['order_goods']);  //所有店铺编码
        // 获取支付类型
        $api = 'Base.StoreModule.Basic.Paytype.lists';
        $pay_type_list = $this->invoke($api,array('sc_code'=>$sc_codes[0]));

        //获取地址列表信息
        $address_list = $this->getDefaultAddress($uc_code,$address_id);
        //获取用户信息
        $user_info  = $this->getUserInfo($uc_code);
        //获取商品店铺信息
        $store_info = $this->getStoreInfo($sc_codes); 
        //订单信息
        $order_info = array(
            'total_amount' => $total_amount,
            'total_nums' => $total_nums,
        );
        //返回数据
        $data = array(
            'goods_info' => $goods_info,
            'user_info' => $user_info,
            'address_list' => $address_list,
            'order_info' => $order_info,
            'store_info' => $store_info,
            'pay_type_list' => $pay_type_list['response']
        );
        return $this->res($data);
    }
    
    private function disposeOrderGoods($goods_info,$buy_number='',$gift_item=''){
       $order_goods = array();
       $temp_gift_item = changeArrayIndex($gift_item, 'p_sic_code');
       $total_num = 0;
       $gift = array();
       foreach ($goods_info as $k2 => $v) {
            $gift_number = 0;
            //如果有参与促销活动，则 如果超过促销活动最大购买商品  需要拆分商品
            if(!empty($v['spc_info'])){   //参与促销商品
                
                $v['goods_number'] = $v['number'];
                $v['ori_goods_price'] = $v['price'];
                $v['spc_code'] = $v['spc_info']['spc_code'];
                $v['spc_max_buy'] = $v['spc_info']['max_buy'];
                $v['spc_type'] = $v['spc_info']['type'];
                
                if($v['spc_info']['type'] == SPC_TYPE_LADDER){
                    //阶梯价不用拆分
                    $v['price'] = getLadderPrice($v['spc_info']['spc_detail']['rule'], $v['number'], $v['price']);
                    $total_num += $v['number'];
                    $order_amount += $v['number'] * $v['price'];
                    $order_goods[] = $v;
                    continue;
                }
//                $v['goods_price'] = $v['price'];
                if($v['spc_type'] == SPC_TYPE_SPECIAL){
                    //如果是特价  则 goods_price 为特价
                    $v['goods_price'] = $v['spc_info']['spc_detail']['special_price'];
                }else {
                    $v['goods_price'] = $v['price'];
                }
                $split_goods_info = $this->spliteGoods($v, $buy_number[$v['spc_code']]['number'],$temp_gift_item[$v['sic_code']]['rule']);   //拆分商品
                $spc_goods_info = $split_goods_info['spc_goods_info'];   //拆分后的促销商品消息
                $common_goods_info = $split_goods_info['goods_info'];  //拆分后的不参与促销的商品信息

                if(!empty($spc_goods_info)){  //促销商品信息不为空

                    if($v['spc_type'] == SPC_TYPE_GIFT){    //如果是赠品  
                        $gift_nums = getGiftNums($spc_goods_info['goods_number'], $temp_gift_item[$v['sic_code']]['rule']);
                        $v['spc_info']['spc_detail']['gift_item']['goods_number'] = $gift_nums;
                        $total_num += $gift_nums;
                    }
                    $v['number'] = $spc_goods_info['goods_number'];
                    $v['price'] = $spc_goods_info['goods_price'];
                    $total_num += $spc_goods_info['goods_number'];
                    $order_amount += $spc_goods_info['goods_number'] * $spc_goods_info['goods_price'];
                    $order_goods[] = $v;
                }

                //如果超出最大限购  则拆分商品
                if(!empty($common_goods_info)){
                    $common_goods_info['number'] = $common_goods_info['goods_number'];
                    $common_goods_info['price'] = $common_goods_info['goods_price'];
                    unset($common_goods_info['spc_info']);
                    $order_goods[] = $common_goods_info;
                    $total_num += $common_goods_info['goods_number'];
                    $order_amount += $common_goods_info['goods_number'] * $common_goods_info['goods_price'];

                }else {
                    //没有超出最大限购

                }
            }else {  //未参与促销商品
                $total_num += $v['number'];
                $order_amount += $v['number'] * $v['price'];
                $order_goods[] = $v;
            }
        }
        
        
        $return_data = array(
            'goods_info'=>$order_goods,
            'order_amount'=>$order_amount,
            'total_num'=>$total_num,
        );
        
        return $return_data;
        
    }

    /**
     * 验证商品是否允许购买
     * Base.OrderModule.B2b.Order.isAllowBuy
     * @param type $params
     */
    public function isAllowBuy($params) {
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
            array('sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家商品编码
            array('goods_number', 'require', PARAMS_ERROR, HAVEING_CHECK), //购买数量
            array('goods_number', 0, PARAMS_ERROR, HAVEING_CHECK, 'gt'), //购买数量
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK, 'function'), //购物车id
//            array('buy_from', array(OC_B2B_APP, OC_B2B_WEIXIN), PARAMS_ERROR, MUST_CHECK, 'in'), //购买来源  必须是  app 或者  微信
            array('is_cart', array('YES', 'NO'), PARAMS_ERROR, MUST_CHECK, 'in'), //是否是购物车购买
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $sic_code = $params['sic_code'];
        $goods_number = $params['goods_number'];
        $sic_codes = array_unique($params['sic_codes']);
        $address_id = $params['address_id'];
        $buy_from = $params['buy_from'];
        $is_cart = $params['is_cart'];

        if ($is_cart == 'YES') {
            //购物车购买
            $goods_info = $this->getCartGoodsInfo($uc_code, $sic_codes); //会同时 验证商品是否已经下架
        } else if ($is_cart == 'NO') {
            //单件商品购买
            $goods_info = $this->getStoreItemInfo($sic_code, $goods_number);
        }

        //验证商品的 库存  和  起购量  是否够 
        $this->setGoodsData($goods_info);
        return $this->res(TRUE);
    }

    /**
     * 获取用户购物车商品
     * @param type $uc_code
     * @param type $sic_codes
     */
    private function getCartGoodsInfo($uc_code, $sic_codes) {
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $data = array(
            'uc_code' => $uc_code,
        );
        if (!empty($sic_codes)) {
            $data['sic_codes'] = $sic_codes;
        }
        $res = $this->invoke($apiPath, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        if (count($sic_codes) != $res['response']['totalnum']) {
            return $this->endInvoke(NULL, 6010);
        }
        return $res['response']['lists'];
    }

    /**
     * 商品信息
     * @param type $sic_code
     * @param type $goods_number
     * @return type
     */
    private function getStoreItemInfo($sic_code, $goods_number) {
        $apiPath = "Base.StoreModule.Item.Item.getStoreItem";

        $data = array(
            'sic_code' => $sic_code,
            'status' => IC_STORE_ITEM_ON,
        );

        $res = $this->invoke($apiPath, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        //购买数量
        $res['response']['number'] = $goods_number;
        $goods_info[0] = $res['response'];
        return $goods_info;
    }

    /**
     * 标准化订单商品信息
     * @param type $goods_info
     * @param type $op_code
     * @param type $need_split  是否需要以拆分的形式返回去
     * @return string
     */
    private function setGoodsData($goods_info, $op_code = '',$b2b_code='') {
        if (empty($goods_info)) {
            return $this->endInvoke(null, '6001');
        }
        $order_goods = array();
        $total_nums = 0;
        $total_amount = 0;
        $sc_codes = array();
        $gift_item = array();
        $spc_codes  =  array();
        foreach ($goods_info as $k => $v) {
            $ladder_rule = '';
            $spc_code = '';
            $discount = '';
            $price = $v['price'];
            //验证购买数量是否大于起购量
            if ($v['number'] < $v['min_num']) {
                return $this->endInvoke(null, 6002, array('goodsName' => $v['goods_name']));
            }
            //库存是否足够
            if ($v['number'] > $v['stock']) {
                return $this->endInvoke(max($v['stock'],0), 6003, array('goodsName' => $v['goods_name'],'stock'=>max($v['stock'],0),'packing'=>$v['packing']));
            }
            $spc_info = $v['spc_info'];   //促销信息
            //如果有促销信息  则要处理促销信息
            if(isset($v['spc_info']) && !empty($v['spc_info'])){
                
                $spc_code = $spc_info['spc_code'];
                $spc_codes[] = $spc_code;    //全部促销的促销编码
                switch ($spc_info['type']){
                    case SPC_TYPE_GIFT:   //赠品类型的   需要记录赠品信息
                        $temp_gift = $spc_info['spc_detail']['gift_item'];
                        //计算赠品 数量
                        $gift_number = getGiftNums($v['number'],  $spc_info['spc_detail']['rule']);
                        //如果赠品数量大于 0 
                        if($gift_number <= 0 || $gift_number === FALSE){
                            break;
                        }
                        //现在只针对一个订单一个商家的
                        $gift_item[] = array(
                            'op_code' => $op_code,
                            'b2b_code'=>$b2b_code,
                            'sc_code' => $temp_gift['sc_code'],
                            'sic_code' => $temp_gift['sic_code'],
                            'p_sic_code' => $v['sic_code'],//所属促销品赠品
                            'sic_no' =>  empty($temp_gift['sic_no']) ? '' : $temp_gift['sic_no'],
                            'goods_name' => $temp_gift['goods_name'],
                            'sub_name' => $temp_gift['store_sub_name'],
                            'goods_number' => $gift_number,
                            'goods_price' => $temp_gift['price'],
                            'goods_img' => $temp_gift['goods_img'],
                            'category_end_id' => empty( $temp_gift['category_end_id']) ? 0 : $temp_gift['category_end_id'],
                            'spec' => $temp_gift['spec'],
                            'packing' => $temp_gift['packing'],
                            'bar_code' => $temp_gift['bar_code'],
                            'spc_code' => $spc_code,
                            'rule' => $spc_info['spc_detail']['rule'],
                        );
                        break;
                    case SPC_TYPE_SPECIAL:      #特价
                            //如果是一口价  则没有折扣   
                            if($spc_info['spc_detail']['special_type'] == SPC_SPECIAL_FIXED){  #一口价
                                $discount = '';
                            }else if($spc_info['spc_detail']['special_type'] == SPC_SPECIAL_REBATE){  #折扣
                                $discount =$spc_info['spc_detail']['discount'];
                            }
                            $price = $spc_info['spc_detail']['special_price'];
                        break;
                    case SPC_TYPE_LADDER:
                        $ladder_rule = $spc_info['spc_detail']['rule'];
                        $price = getLadderPrice($ladder_rule, $v['number'], $v['price']);
                        break;
                }
            }
            
            $total_nums += $v['number'];
            $total_amount += $v['number'] * $v['price'];
            
            
            $temp = array(
                'op_code' => $op_code,
                'sc_code' => $v['sc_code'],
                'sic_code' => $v['sic_code'],
                'sic_no' => empty($v['sic_no']) ? '' : $v['sic_no'],
                'goods_name' => $v['goods_name'],
                'sub_name' => $v['store_sub_name'],
                'goods_number' => $v['number'],
                'goods_price' => $price,
                'ori_goods_price'=>$v['price'],
                'goods_img' => $v['goods_img'],
                'category_end_id' => empty( $v['category_end_id']) ? 0 : $v['category_end_id'],
                'spec' => $v['spec'],
                'packing' => $v['packing'],
                'bar_code' => $v['bar_code'],
                'spc_code' => empty($spc_code) ? '' : $spc_code,
                'spc_max_buy' => empty($spc_info['max_buy']) ? 0 : $spc_info['max_buy'],
                'spc_type' => empty($spc_info['type']) ? '' : $spc_info['type'],
                'discount' => $discount,
                'class_id'=>$v['class_id'],
                'ladder_rule'=>  $ladder_rule,
            );
            $sc_codes[] = $v['sc_code'];
            $order_goods[$v['sc_code']][$v['sic_code']] = $temp;
        }
        $sc_codes = array_unique($sc_codes);
        $data = array(
            'order_goods' => $order_goods,
            'total_nums'   => $total_nums,
            'total_amount'=> $total_amount,
            'sc_codes'    => $sc_codes,
            'gift_item'   => $gift_item,
            'spc_codes'   => $spc_codes,
        );
        return $data;
    }

    /**
     * 单号生成  订单号  和 支付单号
     * @param type $pre_bus_type
     */
    private function generateOrderCode($pre_bus_type) {
        $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
        $data = array(
            'busType' => OC_ORDER,
            'preBusType' => $pre_bus_type,
            'codeType' => SEQUENCE_ORDER,
        );
        $res = $this->invoke($apiPath, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return $res['response'];
    }

    /**
     * 获取用户地址信息
     * @param type $uc_code
     * @param type $address_id
     */
    private function getAddress($uc_code, $address_id) {
        $apiPath = "Base.UserModule.Address.Address.get";
        $data = array(
            'uc_code' => $uc_code,
            'address_id' => $address_id,
        );
        $address_res = $this->invoke($apiPath, $data);
        if ($address_res['status'] != 0) {
            return $this->endInvoke(NULL, $address_res['status']);
        }
        // 收获地址不能为空
        if(empty($address_res['response']['district']) || empty($address_res['response']['address'])){
            return $this->endInvoke(NULL,6056);
        }
        return $address_res['response'];
    }

    /**
     * 获取用户信息
     * @param type $uc_code
     */
    private function getUserInfo($uc_code) {
        $apiPath = "Base.UserModule.User.Basic.getBasicUserInfo";
        $data = array('uc_code' => $uc_code, 'status' => 'ENABLE');

        $user_res = $this->invoke($apiPath, $data);
        if ($user_res['status'] != 0) {
            return $this->endInvoke(NULL, $user_res['status']);
        }
        return $user_res['response'];
    }

    /**
     * 拆分子订单
     * @param type $goods_info
     * @param type $user_info
     */
    private function splitOrderByGoods($goods_info, $user_info, $op_code, $is_cart, $buy_from,$remark,$pay_type,$ship_method,$customer_info,$gift_item='',$buy_number='',$term_method='',$period_day='',$order_type,$b2b_code,$coupon,$is_show) {
        if (empty($goods_info) || empty($user_info)) {
            return $this->endInvoke(NULL, 6004);
        }
        $order_info = array();
        $order_goods = array();
        $i = 0;
        $sc_count = count($goods_info); //商家数量
        $sc_codes = array();//店铺编码
        $gift = array();  //赠品列表
        $buy_number_record = array();
        //处理赠品信息
        $temp_gift_item = changeArrayIndex($gift_item, 'p_sic_code');
        foreach ($goods_info as $sc_code => $value) {
            $spc_code = '';
//            $b2b_code = '';
            $total_num = 0;
            $real_amount = 0;
            $order_amount = 0;
            $sc_codes[] = $sc_code;
            //如果只有一个商家  则订单号和支付单号是一样的
//            if ($sc_count == 1) {
//                $b2b_code = $op_code;
//            }
            //如果没有订单号  则生成订单号
//            if (empty($b2b_code)) {
//                $b2b_code = $this->generateOrderCode(OC_B2B_ORDER_B2B);
//            }
            foreach ($value as $k2 => $v) {
                $v['b2b_code'] = $b2b_code;
                $v['op_code']  = $op_code;
                $v['before_goods_price'] = $v['goods_price'];
                if(isset($remark[$v['sic_code']])  && !empty($remark[$v['sic_code']])){
                    $v['remark'] = $remark[$v['sic_code']]; //如果买家给该商品留言了
                }else {
                    $v['remark'] = '';
                }
                $gift_number = 0;
                //如果有参与促销活动，则 如果超过促销活动最大购买商品  需要拆分商品
                if(!empty($v['spc_code']) ){   //参与促销商品
                    if($v['spc_type'] == SPC_TYPE_LADDER){
                        $this->subStock($v['sic_code'], $v['sc_code'], $v['goods_number'],0);
                        $total_num += $v['goods_number'];
                        $real_amount += $v['goods_number'] * $v['goods_price'];
                        $order_amount += $v['goods_number'] * $v['ori_goods_price'];
                        $order_goods[] = $v;
                        continue;
                    }
                    $split_goods_info = $this->spliteGoods($v, $buy_number[$v['spc_code']]['number'],$temp_gift_item[$v['sic_code']]['rule']);   //拆分商品
                    $spc_goods_info = $split_goods_info['spc_goods_info'];   //拆分后的促销商品消息
                    $common_goods_info = $split_goods_info['goods_info'];  //拆分后的不参与促销的商品信息
                    if(!empty($spc_goods_info)){  //促销商品信息不为空
                        
                        if($v['spc_type'] == SPC_TYPE_GIFT){    //如果是赠品  
                            $gift_nums = getGiftNums($spc_goods_info['goods_number'], $temp_gift_item[$v['sic_code']]['rule']);
                            $temp_gift_item[$v['sic_code']]['goods_number'] = $gift_nums;
                            $gift[] = $temp_gift_item[$v['sic_code']];
                            $total_num += $gift_nums;
                            $this->subStock($temp_gift_item[$v['sic_code']]['sic_code'], $temp_gift_item[$v['sic_code']]['sc_code'], $gift_nums,0);  //减赠品库存
                        }
                        //购买记录
                        $buy_number_record[] = array(
                            'uc_code'=>$user_info['uc_code'],
                            'spc_code'=>$v['spc_code'],
                            'sic_code'=>$spc_goods_info['sic_code'],
                            'number'=>$spc_goods_info['goods_number'],
                        );
                        $order_goods[] = $spc_goods_info;
                        $total_num += $spc_goods_info['goods_number'];
                        $real_amount += $spc_goods_info['goods_number'] * $spc_goods_info['goods_price'];
                        $order_amount += $spc_goods_info['goods_number'] * $spc_goods_info['ori_goods_price'];
                        $this->subStock($spc_goods_info['sic_code'], $spc_goods_info['sc_code'], $spc_goods_info['goods_number'],0);  //减库存
                    }
                    
                    //如果超出最大限购  则拆分商品
                    if(!empty($common_goods_info)){
                        $order_goods[] = $common_goods_info;
                        $total_num += $common_goods_info['goods_number'];
                        $real_amount += $common_goods_info['goods_number'] * $common_goods_info['goods_price'];
                        $order_amount += $common_goods_info['goods_number'] * $common_goods_info['ori_goods_price'];
                        $this->subStock($common_goods_info['sic_code'], $common_goods_info['sc_code'], $common_goods_info['goods_number'],0);  //减库存
                        
                    }else {
                        //没有超出最大限购
                        
                    }
                }else {  //未参与促销商品
                    $this->subStock($v['sic_code'], $v['sc_code'], $v['goods_number'],0);
                    $total_num += $v['goods_number'];
                    $real_amount += $v['goods_number'] * $v['goods_price'];
                    $order_amount += $v['goods_number'] * $v['ori_goods_price'];
                    $order_goods[] = $v;
                }
            }
            $cope_amount = $real_amount;
            $customer_key = $user_info['uc_code'].'_'.$sc_code;
            //获取双磁对接人
            $merchant = $this->getMerchant($sc_code);
//            $merchant = D('ScStore')->alias('ss')->field('um.salesman')->join("{$this->tablePrefix}uc_merchant as um on ss.merchant_id=um.id","left")->find();
            //获取订单属于平台商城订单还是店铺订单
            $arr = array('uc_code'=>$user_info['uc_code']);
            $invite_from = $this->getInvite($arr);
            if($invite_from['invite_from'] == 'UC'){
                $order_type = 'PLATFORM';
            }elseif($invite_from['invite_from'] == 'SC'){
                $order_type = 'STORE';
            }
            //获取订货会
            $spc_code = $this->getCommodity($sc_code);
            if($coupon['coupon_code']){
                if($real_amount<=$coupon['coupon_amount']){
                    return $this->endInvoke('',7103);
                }
                if($real_amount<$coupon['limit_money']){
                    return $this->endInvoke('',6065);
                }else{
                    $real_amount = bcsub($real_amount, $coupon['coupon_amount']) ;
                }
                //更新用户促销券表中的b2b_code
                $operate_status = $this->invoke('Base.UserModule.Coupon.Coupon.updateB2bCode',['b2b_code'=>$b2b_code,'coupon_code'=>$coupon['coupon_code']]);
                if($operate_status['status']!==0){
                    return $this->endInvoke('',7104);
                }
            }
            if(!$coupon['coupon_code']){
                //如果用户满足满返活动的条件，往redis里面存一条数据
                if($is_show == 1){
                    $key = \Library\Key\RedisKeyMap::getCouponKey($b2b_code);
                    $hashKey = \Library\Key\RedisKeyMap::getCouponHashKey($b2b_code);
                    R()->Hset($key,$hashKey,1);
                    R()->setTimeout($key,259200);
                }
            }
            //订单数据
            $order_info[$i] = array(
                'b2b_code'      => $b2b_code,
                'uc_code'       => $user_info['uc_code'],
                'op_code'       => $op_code,
                'sc_code'       => $sc_code,
                'total_num'     => $total_num,
                'real_amount'   => $real_amount,
                'cope_amount'   =>$cope_amount,
                'amount'        => $real_amount,
                'username'      => $user_info['username'],
                'order_status'  => OC_ORDER_ORDER_STATUS_UNCONFIRM,
                'ship_status'   => OC_ORDER_SHIP_STATUS_UNSHIP,
                'pay_status'    => OC_ORDER_PAY_STATUS_UNPAY,
                'buy_from'      => $buy_from,
                'is_cart'       => $is_cart,
                'create_time'   => NOW_TIME,
                'update_time'   => NOW_TIME,
                'pay_type'    => $pay_type,
                'ship_method'   => $ship_method,
                'goods_amount'  => $real_amount,
                'before_goods_amount'=>$real_amount,
                'order_amout'   => $order_amount,
                'salesman_id'   => empty($customer_info[$customer_key]['salesman_id']) ? 0 : $customer_info[$customer_key]['salesman_id'],
                'salesman'      => empty($customer_info[$customer_key]['salesman']) ? '' : $customer_info[$customer_key]['salesman'],
                'channel_id'    => empty($customer_info[$customer_key]['channel_id']) ? 0 : $customer_info[$customer_key]['channel_id'],
                'channel'       => empty($customer_info[$customer_key]['channel']) ? '' : $customer_info[$customer_key]['channel'],
                'client_name'   => $user_info['real_name'],
                'ext4'=>  empty($term_method) ? '' : $term_method,
                'ext5'=>  empty($period_day) ? '' : $period_day,
                'spc_code'=>  empty($spc_code) ? '' :$spc_code,
                'joint_man'=>$merchant['salesman'],
                'order_type'=>$order_type
             );
            
            if ($i === 0) {
                $order_info[$i]['is_show'] = 'YES';  //在列表中是否显示该订单信息   第一条默认为是 
            } else {
                $order_info[$i]['is_show'] = 'NO';
            }
            //如果是线下支付  则要更新客户的下单信息
//            if($pay_method == PAY_METHOD_OFFLINE_COD){
//                $this->updateCustomerOrderInfo($sc_code, $user_info['uc_code'], $amount);
//            }
            //添加订单
            $i++;
        }
        //添加订单商品
        $order_goods_res = D('OcB2bOrderGoods')->addAll($order_goods);
        if ($order_goods_res <= 0 || $order_goods_res === FALSE) {
            return $this->endInvoke(NULL, 6008);
        }
        $order_res = D('OcB2bOrder')->addAll($order_info);
        if (FALSE === $order_res || $order_res <= 0) {
            return $this->endInvoke(NULL, 6005);
        }
        
        //插入赠品
            if(!empty($gift)){
                $gift_res = D('OcB2bOrderGift')->addAll($gift);
                if($gift_res === FALSE){
                    return $this->endInvoke(NULL,6025);
                }
            }
         //更新购买量
            if(!empty($buy_number_record)){
                $this->updateBuyNumber($buy_number_record);
            }
        $return_data = array(
            'order_info' => $order_info,
            'sc_codes' => $sc_codes,
            'real_amount'=>$real_amount,
        );
        return $return_data;
    }

    private function getMerchant($sc_code){
        $data['sc_code']=$sc_code;
        $apiPath = 'Base.StoreModule.Basic.Store.getMerchant';
        $res = $this->invoke($apiPath,$data);
        return $res['response'];
    }
    private function getInvite($arr){
        $apiPath = 'Base.UserModule.Customer.Customer.getInvite';
        $data['uc_code'] = $arr['uc_code'];
        $res = $this->invoke($apiPath,$data);
        if($res['status']!==0){
            return $this->endInvoke('',$res['status']);
        }
        return $res['response'];
    }
    /**
     * 
     * 减库存
     * @param type $sic_code
     * @param type $sc_code
     * @param type $number
     */
    private function subStock($sic_code, $sc_code, $number,$gift_number=0) {
        $apiPath = "Base.StoreModule.Item.Stock.changeStock";
        $data = array(
            'sic_code' => $sic_code,
            'number' => -$number,
            'sc_code' => $sc_code,
            'other_number'=> -$gift_number,
        );
        $stock_res = $this->invoke($apiPath, $data);
        if ($stock_res['status'] != 0) {
            return $this->endInvoke(NULL, $stock_res['status']);
        }
        return TRUE;
    }

    /**
     * 生成订单扩展信息
     * @param type $order_info
     * @param type $address_info
     * @param type $op_code
     * @return type
     */
    private function orderExtend($order_info, $address_info, $op_code,$customer_info='',$pick_up_code = '',$remark,$coupon) {
        $total_amount = array_sum(array_column($order_info, 'order_amout'));  //总金额
        $total_real_amount = array_sum(array_column($order_info, 'real_amount'));  //总金额
        $total_nums = array_sum(array_column($order_info, 'total_num'));
        if ($total_amount <= 0 || $total_nums <= 0) {
            return $this->endInvoke(NULL, 6006);
        }

        $data = array(
            'op_code' => $op_code,
            'total_amount' => $total_amount,
            'total_nums' => $total_nums,
            'total_real_amount' => $total_real_amount, //订单真实金额
            'province' => $address_info['province'],
            'city' => $address_info['city'],
            'district' => $address_info['district'],
            'address' => $address_info['address'],
            'mobile' => $address_info['mobile'],
            'phone' => $address_info['phone'],
            'real_name' => $address_info['real_name'],
            'commercial_name'=>  empty($customer_info) ? '' : $customer_info['commercial_name'],
            'pick_up_code'=>$pick_up_code,
            'remark' =>$remark,
            'coupon_code'=>$coupon['coupon_code'],
            'coupon_amount'=>$coupon['coupon_amount'],
            'active_code'=>$coupon['active_code'],
//            'remit_code'=>$remit_code,
        );

        $extend_res = D('OcB2bOrderExtend')->add($data);
        if ($extend_res <= 0 || FALSE === $extend_res) {
            return $this->endInvoke(null, 6007);
        }
        return $data;
    }

    /**
     * 获取用户地址
     * @param type $uc_code
     * @return type
     */
    private function getDefaultAddress($uc_code,$address_id='') {
        $apiPath = "Base.UserModule.Address.Address.get";
        $data = array(
            'uc_code' => $uc_code,
        );
        if(empty($address_id)){
            $data['is_default'] = 'YES';
        }else{
            $data['address_id'] = $address_id;
        }
        $address_res = $this->invoke($apiPath, $data);
        if ($address_res['status'] != 0) {
            return $this->endInvoke(NULL, $address_res['status']);
        }
        return $address_res['response'];
    }
    
    private function getCustomer($sc_code,$uc_code){
        $apiPath = "Base.UserModule.Customer.Customer.get";
        $data = array(
//            'sc_code' => $sc_code,
            'uc_code' => $uc_code,
        );
        $res = $this->invoke($apiPath, $data);
        if($res['status'] != 0 ){
            //数据库执行异常
            return $this->endInvoke(NULL,$res['status']);
        }
        
        return $res['response'];
    }
    
    private function addCustomer($sc_code,$uc_code,$name,$mobile){
        $apiPath = "Base.UserModule.Customer.Customer.add";
        $data = array(
            'sc_code' => $sc_code,
            'uc_code' => $uc_code,
            'name'    => $name,
            'mobile'  => $mobile,
        );
        $res = $this->invoke($apiPath, $data);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }
        return TRUE;
    }
    
    private function disposeCustomer($sc_codes,$uc_code,$name,$mobile){
        $customer_info = array();
        foreach($sc_codes  as $k=>$sc_code){
//            $temp = $this->getCustomer($sc_code, $uc_code);
            $temp = $this->getCustomer($sc_code, $uc_code);
            if(FALSE === $temp){
                //不存在则添加
                $this->addCustomer($sc_code, $uc_code, $name, $mobile);
            }else {
                $customer_info[$uc_code.'_'.$sc_code] = $temp;
            }
        }
        return $customer_info;
    }
    
   
    
    
    /**
     * 购买商品后 删除购物车相关商品
     * @param type $uc_code
     * @param type $sic_codes
     */
    private function clearCart($uc_code,$sic_codes){
        $apiPath = "Base.UserModule.Cart.Cart.delete";
        $data = array(
            'uc_code'  => $uc_code,
            'sic_codes'=> $sic_codes,
        );

        $clear_res = $this->invoke($apiPath, $data);
        if($clear_res['status'] != 0){
            return $this->endInvoke(NULL,$clear_res['status']);
        }
        return TRUE;
    }
    
    /**
     * 获取店铺信息
     * @param type $sc_codes
     */
    private function getStoreInfo($sc_codes){
        if(empty($sc_codes) || !is_array($sc_codes)){
            return ;
        }
        $apiPath = "Base.StoreModule.Basic.Store.get";
        $data = array();
        $store_info = array();
        foreach($sc_codes as $k=>$v){
            $data = array('sc_code'=>$v);
            $temp = $this->invoke($apiPath,$data);
            $temp = $temp['response'];
            $store_data = array(
                'sc_code' => $temp['sc_code'],
                'name'    => $temp['name'],
                'logo'    => $temp['logo'],
            );
            $store_info[$v] = $store_data;
        }
        return $store_info;
    }
    
    
    /**
     * Base.OrderModule.B2b.Order.oftenBuy
     * 最近经常购买记录  redis
     * @param type $sic_codes
     * @param type $sc_code
     * @param type $uc_code
     */
    public function oftenBuy($params){
        $params = $params['message'];
        $sic_codes = $params['sic_codes'];
        $sc_code = $params['sc_code'];
        $uc_code  = $params['uc_code'];
        $limit = 40;
        $redis = R();
        $count = count($sic_codes);
        $key = \Library\Key\RedisKeyMap::getOftenBuy($sc_code, $uc_code);
        //获取已经有的商品id
        $ori_sic_codes = $redis->get($key);
        if(!empty($ori_sic_codes)){
            $ori_sic_codes = explode(',', $ori_sic_codes);
        }
        for($i = 0;$i<$count;$i++){
           if(in_array($sic_codes[$i], $ori_sic_codes)){
               //原来有的则删除   寻找索引
               $index = array_search($sic_codes[$i], $ori_sic_codes);
               unset($ori_sic_codes[$index]);
           }   
        }
        //如果原来的数量  加上 加上去的数量 大于 40  则删除
        !empty($ori_sic_codes)  && $sic_codes = array_merge($sic_codes,$ori_sic_codes);
        $sic_codes = array_chunk($sic_codes, $limit);
        $sic_codes = $sic_codes[0];
        $sic_codes = implode(',',$sic_codes);
        $redis->set($key,$sic_codes);
        return $this->res(TRUE);
    }

     /**
     * 计算赠品的件数
     * Base.SpcModule.Gift.GiftInfo.calculate
     * @param type $params
     * @return type
     * Author: zhoulianlei <zhoulianlei@liangrenwang.com >
     */
    private function calculate($goods_number,$rule) {
  
        $rule = !is_array($rule) ? json_decode($rule,TRUE): $rule;
        
        //按购买力度从大到小排序
         $rule = $this->reOrder($rule);
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
    //重新排序
    private function reOrder($rule){
        $newRule = array();
        $sales_gift_rule = array_column($rule, 0);
        arsort($sales_gift_rule,SORT_NUMERIC);
        foreach ($sales_gift_rule as $k => $v) {
            $newRule[] = $rule[$k];
        }
        return $newRule;
    }
    
    private function getBuyNumber($spc_codes,$uc_code){
        $apiPath = "Base.SpcModule.Center.BuyNumber.lists";
        $data = array(
            'uc_code'=>$uc_code,
            'spc_codes'=>$spc_codes,
        );
        if(!empty($spc_codes)){
            $res = $this->invoke($apiPath,$data);
            if($res['status'] != 0){
                return $this->endInvoke(NULL,$res['status']);
            }
            
            if(!empty($res['response'])){
                $res['response'] = changeArrayIndex($res['response'], 'spc_code');
            }
            
            return $res['response'];
        }
        return '';
    }
    
    /**
     * 促销商品需要拆分商品
     * @param type $goods_info
     */
    private function spliteGoods($goods_info,$buy_number,$rule=''){
        //如果用户购买数量和 已经购买的数量 没有达到限制的数量   则不拆商品
        if($goods_info['goods_number'] + $buy_number <= $goods_info['spc_max_buy'] || $goods_info['spc_max_buy'] == 0){    //没有超卖  或者  不限购
            if($goods_info['spc_type'] == SPC_TYPE_GIFT){   //如果是赠品类型的
                if($goods_info['spc_max_buy'] == 0){
                    $can_gift_numbers = $goods_info['goods_number'];
                    
    //                return array('spc_goods_info'=>$goods_info);
                }else {
                    $can_gift_numbers = $goods_info['spc_max_buy'] - $buy_number;
                }
            //计算可以参与  满赠的促销品件数
//            $can_gift_numbers = $goods_info['spc_max_buy'] - $buy_number;
            $gift_num = getGiftNums($can_gift_numbers, $rule);    #赠品数量
            //如果数量为  0  则说明参与赠品数量不足  需要按照 普通商品购买
                if($gift_num <= 0){
                    $goods_info['goods_price'] = $goods_info['ori_goods_price'];
                    $goods_info['spc_code'] = '';
                    $goods_info['spc_max_buy'] = 0;
                    $goods_info['spc_type'] = '';
                    $goods_info['discount'] = '';
                    return array('goods_info'=>$goods_info);
                }else {
                    return array('spc_goods_info'=>$goods_info);
                }
            }
            return array('spc_goods_info'=>$goods_info);
        }
        //如果已经购买的数量   大于最大限购数
        if($buy_number >= $goods_info['spc_max_buy']){
                $goods_info['goods_price'] = $goods_info['ori_goods_price'];
                $goods_info['spc_code'] = '';
                $goods_info['spc_max_buy'] = 0;
                $goods_info['spc_type'] = '';
                $goods_info['discount'] = '';
                return array('goods_info'=>$goods_info);
        }
        
//        $spc_goods_numbers = $goods_info['goods_number'] + $buy_number - $goods_info['spc_max_buy'];
        $spc_goods_numbers = $goods_info['spc_max_buy'] - $buy_number;
        if($goods_info['spc_type'] == SPC_TYPE_GIFT){   //如果是赠品类型的
            //计算可以参与  满赠的促销品件数
            $can_gift_numbers = $goods_info['spc_max_buy'] - $buy_number;
            $gift_num = getGiftNums($can_gift_numbers, $rule);    #赠品数量
            //如果数量为  0  则说明参与赠品数量不足  需要按照 普通商品购买
            if($gift_num <= 0){
                $goods_info['goods_price'] = $goods_info['ori_goods_price'];
                $goods_info['spc_code'] = '';
                $goods_info['spc_max_buy'] = 0;
                $goods_info['spc_type'] = '';
                $goods_info['discount'] = '';
                return array('goods_info'=>$goods_info);
            }
        }
        //超出最大购买量  则需要拆分商品
        $spc_goods_info = $goods_info;
        $spc_goods_info['goods_number'] = $spc_goods_numbers;
        
        
        $goods_info['goods_price'] = $goods_info['ori_goods_price'];
        $goods_info['spc_code'] = '';
        $goods_info['spc_max_buy'] = 0;
        $goods_info['spc_type'] = '';
        $goods_info['discount'] = '';
        $goods_info['goods_number'] = $goods_info['goods_number'] - $spc_goods_numbers;
        
        return array('goods_info'=>$goods_info,'spc_goods_info'=>$spc_goods_info);
        
    }
    
    
    private function updateBuyNumber($buy_number_record){
        $apiPath = "Base.SpcModule.Center.BuyNumber.add";
        if(!empty($buy_number_record)){
            foreach($buy_number_record as $k=>$v){
                $res = $this->invoke($apiPath, $v);
                if($res['status'] != 0){
                    return $this->endInvoke(NULL,7035);
                }
            }
        }
        return TRUE;
    }
    /**
     * 下单添加动作
     * @param type $pay_method
     * @param type $uc_code
     * @param type $b2b_code
     */
    private function addAction($pay_method,$uc_code,$b2b_code,$pay_type){
        $apiPath = "Base.OrderModule.B2b.OrderAction.orderActionUp";
        $data = array(
            'pay_method'=>$pay_method,
            'uc_code'=>$uc_code,
            'b2b_code'=>$b2b_code,
            'status'=>OC_ORDER_GROUP_STATUS_UNPAY,
            'pay_type'=>$pay_type,
        );
        $res = $this->invoke($apiPath,$data);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }
        return true;
    }
    
    /**
     * 修改订单支付码
     * Base.OrderModule.B2b.Order.changeOpCode
     * @param type $params
     */
//    public function changeOpCode($params){
//        $this->startOutsideTrans();
//        $this->_rule = array(
//            array('op_code', 'require', PARAMS_ERROR, MUST_CHECK), //用户编码
//        );
//        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
//            return $this->res($this->getErrorField(), $this->getCheckError());
//        }
//        $op_code = $params['op_code'];
//        
//        //生成新的op_code 
//        $new_op_code = $this->generateOrderCode(OC_B2B_ORDER_OP);
//        
//        //更新ordr表的op_code
//        $res = D('OcB2bOrder')->where(array('op_code'=>$op_code))->save(array('update_time'=>NOW_TIME,'op_code'=>$new_op_code));
//        if($res <= 0){
//            return $this->res(NULL,6027);
//        }
//        //更新order extend 表
//        $res = D('OcB2bOrderExtend')->where(array('op_code'=>$op_code))->save(array('op_code'=>$new_op_code));
//        
//        if($res <= 0){
//            return $this->res(NULL,6028);
//        }
//        
//        return $this->res($new_op_code);
//        
//    }
    
    /**
     * 生成汇款码
     * Base.OrderModule.B2b.Order.getRemitCode
     * @param type $params
     */
    public function getRemitCode($params){
        $this->startOutsideTrans();
        $remit_code = $this->generateRemitCode();
        return $this->res($remit_code);
    }
    
    
    /**
     * 添加银行转账扩展信息
     * @param type $remit_bank
     * @param type $oc_code
     */
    private function checkRemitBank($remit_bank,$oc_code){
        if(empty($remit_bank)){
            return $this->endInvoke(NULL,6032);
        }
        //获取支持的银行
        $remit_bank_list = M('Base.OrderModule.B2b.Status')->getRemitBank();
        $remit_bank_list = array_keys($remit_bank_list);
        if(!in_array($remit_bank, $remit_bank_list)){
            return $this->endInvoke(NULL,6033);
        }
        //添加扩展信息
        $apiPath = "Base.OrderModule.Center.OrderPayExtend.add";
        $data = array(
            'oc_code'=>$oc_code,
            'ext1'   => $remit_bank,
        );
        $res =  $this->invoke($apiPath,$data);
        if($res['status'] != 0){
            return $this->endInvoke(NULL,$res['status']);
        }
        return true;
    }
    
    private function getTerm(){
        
    }
    
    private function getCommodity($sc_code){
        $apiPath = "Base.SpcModule.Commodity.Commodity.getInfo";
        $data = array(
            'sc_code'=>$sc_code,
            'get_commodity'=>'YES',
        );
        
        $get_res = $this->invoke($apiPath,$data);
        if($get_res['status'] != 0){
            return $this->endInvoke(null,$get_res['status']);
        }
        
        return $get_res['response']['spc_code'];
    }
    
    
    private function generatePickUpCode($b2b_code){
        $pick_up_code = mt_rand(10000000, 99999999);
        $data = array(
            'create_time'=>NOW_TIME,
            'b2b_code'=>$b2b_code,
            'pick_up_code'=>$pick_up_code,
        );
      
        $add_res = D('OcB2bPickUpCode')->add($data);
        if($add_res <= 0 || false === $add_res){
            return $this->endInvoke(false,6063);
        }
        return $pick_up_code;
    }
    
    
    public function genePickUpQrcode($params){
        
        $params = $params['message'];
        $b2b_code = $params['b2b_code'];
        $pick_up_code = $params['pick_up_code'];
        
        //查询订单是否已经生成
        $order_info = D('OcB2bOrder')->where(array('b2b_code'=>$b2b_code))->field('op_code')->find();
        if(empty($order_info)){
            return $this->res(FALSE,6015);
        }
        $Qrcode = new \Library\qrcodes();
        //如果订单已经生成  则  生成取货码二维码 并上传到阿里云
        $qrcode_url = $Qrcode->generateQrcodeByUrl($pick_up_code);
        if(empty($qrcode_url)){
            return $this->res(NULL,6707);
        }
        
        $img_url  = upload_cloud($qrcode_url);
        if(empty($img_url)){
            return $this->res(NULL,6708);
        }
        
        //更新qrcode
        $qrcode_res = D('OcB2bOrderExtend')->where(array('op_code'=>$order_info['op_code']))->save(array('pick_up_qrcode'=>$img_url));
        if($qrcode_res === FALSE || $qrcode_res <= 0){
            return $this->res(FALSE,6060);
        }
        
        return $this->res(TRUE);
    }

    /**
     * 创建支付任务
     * Base.OrderModule.B2b.Order.createPay
     */
    public function createPay($params) {
        $this->_rule = array(
            array('total_fee','require',PARAMS_ERROR,MUST_CHECK),             // 金额
            array('op_code','require',PARAMS_ERROR,MUST_CHECK),             // 订单号
            array('uc_code','require',PARAMS_ERROR,MUST_CHECK),              // 用户编码
            array('oc_code','require',PARAMS_ERROR,MUST_CHECK),              // 支付编码
            array('bindId','require',PARAMS_ERROR,ISSET_CHECK),             // 银行编码
            array('realName','require',PARAMS_ERROR,ISSET_CHECK),      // 真实姓名
            array('cardNo','require',PARAMS_ERROR,ISSET_CHECK),        // 身份证
            array('mobileNo','require',PARAMS_ERROR,ISSET_CHECK),            // 手机号
            array('bankCardNo','require',PARAMS_ERROR,ISSET_CHECK),         //  银行卡号
            array('bankBM','require',PARAMS_ERROR,ISSET_CHECK),         //  银行编码
            array('returnUrl','require',PARAMS_ERROR,ISSET_CHECK),         // 回调页面
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $apiPath = 'Base.PayCenter.Task.PayTask.Create';
        $res = $this->invoke($apiPath, $params);

        return $this->res($res);
    }

    /**
     * 银行卡列表
     * Base.OrderModule.B2b.Order.BankList
     */
    public function bankList($params) {
        $apiPath = 'Base.PayCenter.Info.AccountInfo.BankList';
        $res = $this->invoke($apiPath, $params);

        return $this->res($res);
    }


    /**
     * 已绑定银行卡列表
     * Base.OrderModule.B2b.Order.bindList
     */
    public function bindList($params) {
        $this->_rule = array(
            array('uc_code','require',PARAMS_ERROR,MUST_CHECK),              // 用户编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $apiPath = 'Base.PayCenter.Info.AccountInfo.BindList';
        $res = $this->invoke($apiPath, $params);

        return $this->res($res);
    }

    /**
     * 解除绑定银行卡
     * Base.OrderModule.B2b.Order.unbindCard
     */
    public function unbindCard($params) {
        $this->_rule = array(
            array('uc_code','require',PARAMS_ERROR,MUST_CHECK),              // 用户编码
            array('bindId','require',PARAMS_ERROR,MUST_CHECK),             // 银行编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $apiPath = 'Base.PayCenter.Info.AccountInfo.UnbindCard';
        $res = $this->invoke($apiPath, $params);

        return $this->res($res);
    }


    /**
     * 获取用户购物车商品（不需要检查上下架）
     * @param type $uc_code
     * @param type $sic_codes
     */
    private function getCartGoodsInfos($uc_code, $sic_codes) {
        $apiPath = "Base.UserModule.Cart.Cart.lists";
        $data = array(
            'uc_code' => $uc_code,
        );
        if (!empty($sic_codes)) {
            $data['sic_codes'] = $sic_codes;
        }
        $res = $this->invoke($apiPath, $data);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        return $res['response']['lists'];
    }

}

?>
