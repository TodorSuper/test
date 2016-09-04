<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | cms后台商品相关模块
 */

namespace Bll\Cms\Item;

use System\Base;

class lists extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询单个商品
     * Bll.Cms.Item.Lists.get
     * @author Todor
     * @access public
     */
    public function get($params){
        $apiPath='Base.ItemModule.Item.ItemInfo.getStoreItem';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $goods=$call['response'];
        $sc_code = $call['response']['sc_code'];
        $apiPath='Base.StoreModule.Basic.Store.get';
        $data=array(
            'sc_code'=>$sc_code
        );
        $call=$this->invoke($apiPath,$data);
        $name=$call['response']['name'];
        $goods['name']=$name;
        $apiPath='Base.ItemModule.Tag.Tag.listsTag';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $goods['tag']=$call['response'];
        $apiPath='Base.ItemModule.Category.Category.lists';
        $call=$this->invoke($apiPath,$params);
        if($call['status']!==0){
            return $this->endInvoke('',$call['status']);
        }
        $goods['category']=$call['response'];
        $itemData = array();
        $categoryApi = "Base.ItemModule.Category.Category.getCategorys";
        $categoryData = array(
            'sic_codes' => array($params['sic_code']),
            'sc_code' => $params['sc_code'],
        );
        $categoryRes = $this->invoke($categoryApi, $categoryData);
        if ($categoryRes['status'] != 0) {
            return $this->endInvoke(NULL, $categoryRes['status']);
        }
        $categoryList = $categoryRes['response'];
        if ($categoryList) {
            $goods['myCategoryList'] = changeArrayIndex($categoryList, 'id');
        }else{
            $goods['myCategoryList'] = array();
        }

        $tagApi = "Base.ItemModule.Tag.Tag.getTags";
        $tagData = array(
            'sic_codes' => array($params['sic_code']),
            'sc_code' => $params['sc_code'],
        );
        $tagRes = $this->invoke($tagApi, $tagData);
        if ($tagRes['status'] != 0) {
            return $this->endInvoke(NULL, $tagRes['status']);
        }
        $tagList = $tagRes['response'];
        if ($tagList) {

            $goods['myTagList'] = changeArrayIndex($tagList, 'id');
        }else{
            $goods['myTagList'] = array();
        }

        return $this->endInvoke($goods);
    }
    /**
     * 查询上架商品列表
     * Bll.Cms.Item.Lists.Goods
     * @author Todor
     * @access public
     */
    public function Goods($param) {
         //店家列表
        // $_SERVER['HTTP_USER_AGENT'] = CMS;
        $store_lists = $this->invoke('Base.StoreModule.Basic.Store.lists');
        $list['store_lists'] = $store_lists['response'];
        if ( empty($param['sc_code']) ) {
            $param['sc_codes'] = array_column($list['store_lists'],'sc_code');
        } else {
            $param['sc_codes'] = array($param['sc_code']);
        }
        if (ENV == 'production') {
            $key = array_search('1010000000077', $param['sc_codes']);
        if ($key !== false) {
            unset($param['sc_codes'][$key]);
          }
            foreach ($list['store_lists'] as $k => $v) {
                if ($v['sc_code'] == '1010000000077') {
                    unset($list['store_lists'][$k]);
                }
            }
        }
        $data = $this->invoke('Base.ItemModule.Item.ItemInfo.storeItems',$param);
        $apiPath = 'Base.ItemModule.Classify.ClassifyInfo.listsAll';
        $call = $this->invoke($apiPath);
        $list['class'] = $call['response'];
        //上架商品列表
        $list['lists'] = $data['response']['lists'];
        $list['totalnum'] = $data['response']['totalnum'];
        $list['page_number'] = $data['response']['page_number'];
        //品牌列表
        $brands = $this->invoke('Base.ItemModule.Brand.Brand.brands');
        $list['brandList'] = $brands['response'];
       
        return $this->endinvoke($list);
    }
    /**
     * 操作商品下架
     * Bll.Cms.Item.Lists.setStatus
     * @author Todor
     * @access public
     */
    public function setStatus($params) {
     
        try {
            D()->startTrans();
            if ($params['status'] == IC_STORE_ITEM_OFF) {
            
              
                $sign = $this->checkSpc($params);

                if (is_array($sign)) {
                    $params = $sign;

                }else{
                    return $this->endInvoke(NULL, $sign);
                }
            }
            $apiPath = "Base.ItemModule.Item.Lists.setStatus";
            $set_status = $this->invoke($apiPath, $params);

            if($set_status['status'] != 0){
                return $this->endInvoke($set_status['message'],$set_status['status']);
            }
            D()->commit();
            return $this->res($set_status['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 4517);
        }
    }

    /**
     * 用于过滤促销中的商品(和赠品)
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function checkSpc($params){
        if ($params['status'] == IC_STORE_ITEM_OFF) {
            $sic_codes                 = $params['sic_codes'];

            $fields                    = "sg.gift_sic_code";
            $where['sg.gift_sic_code'] = array('in', $params['sic_codes']);
            $where['sl.sc_code']       = array('in', $params['sc_code']);
            $where['sl.status']        = IC_ITEM_PUBLISH;
            $where['sg.status']        = array('EQ', 'ENABLE');
            $where['sl.end_time']      = array('gt', NOW_TIME);

            $gift_res = D('SpcList') ->alias('sl')
                ->join("{$this->tablePrefix}spc_gift sg on sl.spc_code = sg.spc_code", 'LEFT')
                ->field("{$fields}")
                ->where($where)
                ->select();
            if (!empty($gift_res)) {
                $spcArr = array_column($gift_res, 'gift_sic_code');
            }else{
                $spcArr = array();
            }

            $fields               = "sic_code";
            $map['sic_code']    = array('in', $params['sic_codes']);
            $map['sc_code']  = array('in', $params['sc_code']);
            $map['status']   = IC_ITEM_PUBLISH;
            $map['end_time'] = array('gt', NOW_TIME);
            $spc_res              = D('SpcList')->field("{$fields}")->where($map)->select();

            if (!empty($spc_res)) {
                $spc_res = array_column($spc_res, 'sic_code');
                $spcArr = array_merge($spcArr, $spc_res);
            }

            //过滤上架商品（在促销中，为赠品的和促销品）
            if (!empty($spcArr)) {
                $new_sic_codes = array_unique($spcArr);
                foreach ($sic_codes as $k => $sic_code) {
                    if (in_array($sic_code, $new_sic_codes)) {
                        unset($sic_codes[$k]);
                    }
                }
            }
        }
        if (empty($sic_codes)) {
            return 4525;
        }else{
            $params['sic_codes'] = $sic_codes;
            return $params;
        }

    }

}
