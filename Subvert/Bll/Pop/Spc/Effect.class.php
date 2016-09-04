<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 促销效果 (BLL)
 */

namespace Bll\Pop\Spc;
use       System\Base;

class Effect extends Base{

	private $_rule = null; 
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Spc';
    }


    /**
     * 促销效果列表
     * Bll.Pop.Spc.Effect.lists
     * @access public
     */


    public function lists($params){

        $orders = array('all_number','all_price');
        if(!in_array($params['order'], $orders) && !empty($params['order'])){
            return $this->endInvoke(NULL,5);     #参数异常
        }

    	$apiPath   = "Base.SpcModule.Center.Effect.lists";
    	$list_res  = $this->invoke($apiPath,$params);

    	if($list_res['status'] != 0){
            return $this->endInvoke(NULL,7020);   #获取促销列表失败
        }
      
        if(!empty($list_res['response']['lists'])){

            // 获取赠品信息
            $spc_codes['spc_codes'] = array_column($list_res['response']['lists'],'spc_code');
            $spc_codes['sc_code']   = $params['sc_code'];
            $spc_codes['uc_codes']  = array_column($list_res['response']['lists'],'uc_code');

            $apiPath                = 'Base.SpcModule.Gift.GiftInfo.lists';
            $res                    = $this->invoke($apiPath,$spc_codes);
            if($res['status'] != 0){
                $this->endInvoke(null,$res['status'],'',$res['message']);
            }
            $res = $res['response'];
            foreach ($res as $k => $v) {
                $res[$v['spc_code']] = $v;
                unset($res[$k]);
            }

            // 获取特价信息
            $apiPath = 'Base.SpcModule.Special.SpecialInfo.lists';
            $special_res = $this->invoke($apiPath,$spc_codes);
            if($res['status'] != 0){
                $this->endInvoke(null,$special_res['status'],'',$special_res['message']);
            }
            $special_res = $special_res['response'];
            foreach ($special_res as $k => $v) {
                $special_res[$v['spc_code']] = $v;
                unset($special_res[$k]);
            }


            // 获取商品赠品数量
            !empty($params['customer']) && $spc_codes['customer'] = $params['customer'];
            $apiPath = "Base.OrderModule.B2b.OrderInfo.gift";
            $num = $this->invoke($apiPath,$spc_codes);
            if($res['status'] != 0){
                $this->endInvoke(null,$num['status'],'',$num['message']);
            }


            //获取商品信息
            $gift['sic_codes'] = array_column($res,'gift_sic_code');
            $gift['sic_codes'] = array_unique($gift['sic_codes']);
            $gift['sc_code']   = $params['sc_code'];
            $apiPath='Base.StoreModule.Item.Item.storeItems';
            $item = $this->invoke($apiPath,$gift);
            if($item['status']!=0){
                $this->endInvoke(null,$item['status'],'',$item['message']);
            }
            $item = $item['response']['lists'];
            foreach ($item as $k => $v) {
                $item[$v['sic_code']] = $v;
                unset($item[$k]);
            }



            //  促销规则与信息
            foreach ($list_res['response']['lists'] as $k => $v) {

                if(in_array($v['spc_code'], $spc_codes['spc_codes'])){
               
                    switch ($v['type']) {
                        case SPC_TYPE_GIFT:
                            $list_res['response']['lists'][$k]['spc_detail'] = $res[$v['spc_code']];         # 满赠
                            break;
                        case SPC_TYPE_SPECIAL:
                            $list_res['response']['lists'][$k]['spc_detail'] = $special_res[$v['spc_code']]; # 特价
                            break;
                        case SPC_TYPE_LADDER :
                            $list_res['response']['lists'][$k]['spc_detail']['rule'] = $v['ladder_rule'];           # 阶梯价
                    }             
                }  

            }
            
            //  满赠数量
            foreach ($list_res['response']['lists'] as $k => $v) {
               
                    switch ($v['type']) {
                        case SPC_TYPE_GIFT:
                        if(empty($params['customer'])){

                            $num = $num['response'];
                            foreach ($num as $k => $v) {
                                $num[$v['spc_code']] = $v;
                                unset($num[$k]);
                            }

                            $nums = array_keys($num);
                                foreach ($list_res['response']['lists'] as $k => $v) {
                                    if(in_array($v['spc_code'], $nums)){
                                        $list_res['response']['lists'][$k]['order_gift'] = $num[$v['spc_code']];
                                    }
                                }
                            }else{
                                $num = $num['response'];
                                foreach ($num as $k => $v) {
                                    $num[$v['spc_code'].$v['uc_code']] = $v;
                                    unset($num[$k]);
                                }
                                $nums = array_keys($num);
                                foreach ($list_res['response']['lists'] as $k => $v) {
                                    if(in_array($v['spc_code'].$v['uc_code'], $nums)){
                                        $list_res['response']['lists'][$k]['order_gift'] = $num[$v['spc_code'].$v['uc_code']];
                                    }
                                }                                
                            }
                            break;
                    }             
            }
            

            //组装商品 规则转换
            foreach ($list_res['response']['lists'] as $k => $v) {

                switch ($v['type']) {

                    case SPC_TYPE_GIFT:                              # 满赠
                        if(in_array($v['spc_detail']['gift_sic_code'], $gift['sic_codes'])){
                             $list_res['response']['lists'][$k]['spc_detail']['gift_item'] = $item[$v['spc_detail']['gift_sic_code']];
                        }

                        $temps['rule'] = $v['spc_detail']['rule'];
                        $list_res['response']['lists'][$k]['spc_detail']['rule'] = spcRuleParse($v['type'],$temps);
                        break;
                    case SPC_TYPE_LADDER:  
                                                                     # 阶梯价
                        $temps['rule'] = $v['spc_detail']['rule'];
                        $list_res['response']['lists'][$k]['spc_detail']['rule'] = spcRuleParse($v['type'],$temps);
                        break;
                    case SPC_TYPE_SPECIAL:                           # 特价

                        $temps['special_price'] = $v['spc_detail']['special_price'];
                        $list_res['response']['lists'][$k]['spc_detail']['rule'] = spcRuleParse($v['type'],$temps);
                        break;
                }
                
            }

            // 获取后台栏目名称
            $category['category_end_ids'] = array_column($list_res['response']['lists'],'category_end_id');
            
            if(!empty($category['category_end_ids'])){

                $apiPath = "Base.ItemModule.Category.Category.getEndCategory";
                $categorys = $this->invoke($apiPath,$category);


                foreach ($categorys['response'] as $k => $v) {
                    $categorys['response'][$v['id']] = $v;
                    unset($categorys['response'][$k]);
                }

                foreach ($list_res['response']['lists'] as $k => $v) {
                    if(in_array($v['category_end_id'], $category['category_end_ids'])){
                        $list_res['response']['lists'][$k]['category'] = $categorys['response'][$v['category_end_id']]['name'];
                    }
                }
            }

        }

        $temp['sc_code'] = $params['sc_code'];
        // 调取业务员 
        $apiPath  = 'Base.UserModule.Customer.Salesman.search';
        $salesman = $this->invoke($apiPath,$temp);
        
        // 调取客户
        $temp['salesman_id'] = $params['salesman_id'];
        $apiPath  = "Base.UserModule.Customer.Customer.search";
        $customer = $this->invoke($apiPath,$temp);
 
        $list_res['response']['salesman'] = $salesman['response'];
        $list_res['response']['customer'] = $customer['response'];

        return $this->endInvoke($list_res['response']);
    }


    /**
     * 促销效果列表导出
     * Bll.Pop.Spc.Effect.export
     * @access public
     */

    public function export($params){

        $apiPath  = "Base.SpcModule.Center.Effect.export";
        $list_res = $this->invoke($apiPath,$params);

        if($list_res['status'] != 0){
            return $this->endInvoke(NULL,7021);   # 导出促销列表失败
        }
        return $this->endInvoke($list_res['response']);
    }







}





































 ?>