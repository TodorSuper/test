<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\B2bOrder;

use System\Base;
class Order extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Oc';
    }

	public function addUser() {
		$a = 18511115575;
		for($i=0; $i<1000; $i++) {
			$api = 'Bll.B2b.User.Region.region';
			$data = array(
				'mobile' => ($a = $a+1), 
				'name' => 'tests',
				'commercial_name'=> 'Test',
				'address' => '朝阳',
				'openid' =>'o5sEMt8WVDwmdwK1pem8bt0W9R6I'.$a, 
				'city' => '北京市',
				'district' =>'石景山区',
				'check_code' => '123456',
				'invite_code'=>'100131',
			);
			$res = $this->invoke($api, $data);

		}

		return $this->res($res);
	}
    /**
     * 创建b2b订单
     * Test.Bll.B2bOrder.Order.add
     * @param type $params
     */
	public function add($params) {
		$seq = R()->get('testOrder');
		if(!$seq) {
			$seq = 74;
		}
		if($seq > 671) {
			$seq = 74;
		}
		R()->set('testOrder', $seq+1);
		$address_id = D('UcAddress')->field('id,uc_code')->where(['id'=>$seq])->find();
		$uc_code = $address_id['uc_code'];
		$address_id = $address_id['id'];
		$cartData = array(
			'uc_code'=> $uc_code,
			'sc_code'=> '1020000000026',
			'sic_code'=> '12200002560',
			'number'=> 1
		);
		try {
			D()->startTrans();
			$apiPath = "Base.UserModule.Cart.Cart.add";
			$res = $this->invoke('Base.UserModule.Cart.Cart.add', $cartData);
			if ($res['status'] != 0) {
				return $this->endInvoke($res['response'], $res['status']);
			}
			$commit_res = D()->commit();
			if ($commit_res === FALSE) {
				return $this->endInvoke(NULL, 17);
			}
		} catch (\Exception $ex) {
			D()->rollback();
			return $this->endInvoke(NULL, 4020);
		}
//		return $this->res($res);
		//购物车参数
        $sic_codes = array('12200002560');
        $data = array(
//            'uc_code' => '1120000000104',
            'uc_code' => $uc_code,
			'sic_codes'=>$sic_codes,
            'address_id' => $address_id,
            'buy_from' => OC_B2B_WEIXIN,
            'is_cart' => 'YES',
            'pay_method'=>PAY_METHOD_OFFLINE_COD,
            'ship_method'=>SHIP_METHOD_DELIVERY,
		);
        $apiPath = "Bll.B2b.Order.Order.add";
        $add_res = $this->invoke($apiPath,$data);
        return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
    }

    /**
     * 展示即将下单的信息
     * Test.Bll.B2bOrder.Order.showOrderInfo
     * @param type $params
     */
    public function showOrderInfo($params) {
        $apiPath = "Bll.B2b.Order.Order.showOrderInfo";
                   //    非购物车参数
        $data = array(
            'uc_code' => '1120000000104',
            'sic_code' => '12200000040',
            'goods_number' => '2',
//           'cart_ids'=>'',
            'address_id' => '18',
            'buy_from' => OC_B2B_WEIXIN,
            'is_cart' => 'NO',
        );

        //购物车参数
//        $sic_codes = array('12200000040');
//        $data = array(
//            'uc_code' => '1120000000104',
////            'sic_code' => '12200000005',
////            'goods_number' => '2',
//           'sic_codes'=>$sic_codes,
//            'address_id' => '18',
//            'buy_from' => OC_B2B_WEIXIN,
//            'is_cart' => 'YES',
//        );
        $add_res = $this->invoke($apiPath,$data);
        return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
    }

    /**
     * 验证商品是否允许购买
     * Test.Bll.B2bOrder.Order.isAllowBuy
     * @param type $params
     */
    public function isAllowBuy($params) {
        $apiPath = "Bll.B2b.Order.Order.isAllowBuy";
        //非购物车参数
//        $data = array(
//            'uc_code' => '1120000000104',
//            'sic_code' => '12200000040',
//            'goods_number' => '10',
////           'cart_ids'=>'',
//            'address_id' => '18',
//            'buy_from' => OC_B2B_WEIXIN,
//            'is_cart' => 'NO',
//        );

        //购物车参数
        $sic_codes = array('12200000040');
        $data = array(
            'uc_code' => '1120000000104',
//            'sic_code' => '12200000005',
//            'goods_number' => '2',
           'sic_codes'=>$sic_codes,
            'address_id' => '18',
            'buy_from' => OC_B2B_WEIXIN,
            'is_cart' => 'YES',
        );
        $add_res = $this->invoke($apiPath,$data);
        return $this->endInvoke(NULL,$add_res['status'],'',$add_res['message']);
    }
    
     /**
     * 验证商品是否允许购买
     * Test.Bll.B2bOrder.Order.itemgenerate
     * @param type $params
     */
    public function itemgenerate($params){
        try{
            D()->startTrans();
              $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
              $data = array(
                  'busType'=>IC_ITEM,
                  'preBusType'=>IC_STANDARD_ITEM,
                  'codeType'=>SEQUENCE_ITEM,
              );
              $code_res = $this->invoke($apiPath, $data);
              if($code_res['status'] != 0){
                  throw new \Exception('编码生成失败',$code_res['status']);
              }
            D()->commit();
//            return $this->res($code_res['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->res(null,4505);
        }
        $goods_name = array('德玛西亚','诡术妖姬','寒冰射手','无极剑圣','皮特女警');
        $brand      = array('坦克','法师','adc','刺客','adc');
        $spec     = array('100血/肉','100法强/法术','100物攻/箭','200血/刀','200血/咚咚咚');
        $packing  = array('肉','法术','箭','刀','咚咚咚');
        $goods_img = array('http://cdn.njw88.com/20150717/1437118815_534966595.jpg','http://cdn.njw88.com/20150708/1436328993_382461226.jpg','http://cdn.njw88.com/20150720/1437363961_1804170087.jpg','http://cdn.njw88.com/20150720/1437363961_1804170087.jpg','http://cdn.njw88.com/20150704/1436000789_568341526.jpg');
        $num = mt_rand(0, 4);
        $ic_code = $code_res['response'];
        $data = array(
            'ic_code' => $ic_code,
            'category_end_id'=>726,
            'goods_name'=>$goods_name[$num],
            'sub_name' =>'我是'.$goods_name[$num],
            'brand' => $brand[$num],
            'spec'=>$spec[$num],
            'packing'=>$packing[$num],
            'goods_img'=>$goods_img[$num],
            'status'=>'PUBLISH',
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'publish_time'=>NOW_TIME,
        );
        $res = D('IcItem')->add($data);
        echo $res;
    } 
    
     /**
     * 验证商品是否允许购买
     * Test.Bll.B2bOrder.Order.sendOrderMsgTest
     * @param type $params
     */
    public function sendOrderMsgTest($params){
        $apiPath = "Base.OrderModule.B2b.Order.sendOrderMsg";
        $data = array(
            'order_sn' => '12200001485',
            'goods_name' => '云铺通商品',
            'goods_number' => 1,
            'pay_price' => 0.01,
            'uc_code' => '1220000000156',
        );
        $res = $this->invoke($apiPath,$data);
        print_r($res);
    }

     /**
     * 验证商品是否允许购买
     * Test.Bll.B2bOrder.Order.test
     * @param type $params
     */
    public function test($params){
        $apiPath = "Bll.Cms.Finance.Advance.updateConfirm";
        $params = array(
            'b2b_code' =>array("21200003041"),
            'status' =>'2',
        );
        $res = $this->invoke($apiPath,$params);
        print_r($res);
    }

}

?>
