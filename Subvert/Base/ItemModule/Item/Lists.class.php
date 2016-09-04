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

namespace Base\ItemModule\Item;

use System\Base;

class Lists extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询上架商品列表
     * Base.ItemModule.Item.Lists.Goods
     * @author Todor
     * @access public
     */
    public function Goods($params){
        $this->_rule = array(
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //商品编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK), //商品品牌
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK), //商品状态
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      # 分页数
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),      # 页码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sic_no = $params['sic_no'];
        $sc_code = $params['sc_code'];
//        $sc_code = 1020000000026;
        $brand = $params['brand'];
        $goods_name = $params['goods_name'];
        $status = $params['status'];

        $order = 'isi.id desc';
        $fields = "isi.sic_code,isi.warn_stock,isi.sic_no,ii.category_end_id,isi.sub_name,ii.ic_code,ii.bar_code,ii.goods_img,ii.goods_name,ii.spec,ii.packing,ii.brand, isi.sc_code,isi.sub_name AS store_sub_name,isi.price,ii.goods_img_new,"
            . "isi.min_num,isi.stock,isi.is_standard,isi.status AS store_status,ii.status as status,ss.name,isi.create_time";

        //组装where条件
        $where=array();
        empty($sic_no) ? null : $where['isi.sic_no']=$sic_no;
        empty($sc_code) ? null : $where['isi.sc_code']=$sc_code;
        empty($brand) ? null : $where['ii.brand']=$brand;
        empty($goods_name) ? null : $where['ii.goods_name']= array('like', "%{$goods_name}%");
        empty($status) ? null : $where['isi.status']=$status;

        $data=array(
            'where'=>$where,
            'order'=>$order,
            'field'=>$fields,
            'page'=>$params['page'] ? $params['page'] : 1,
            'page_number'=>$params['page_number'] ? $params['page_number'] : 20,
            'center_flag'=>SQL_SC,
            'sql_flag'=>'goods_items'
        );

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $data);
        if ($list_res['status'] != 0) {
            return $this->res(null, $list_res['status']);
        }
        return $this->res($list_res['response']);

    }

    /**
     *
     * 修改商品状态  支持批量修改
     * Base.ItemModule.Item.Lists.setStatus
     * @param type $params
     */
    public function setStatus($params) {
     
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家商品编码
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, MUST_CHECK, 'in'), //必须是上下架状态
            array('sc_code', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sic_codes = $params['sic_codes'];
        $status    = $params['status'];
        $sc_code   = $params['sc_code'];
        
        $res = D('IcStoreItem')->alias('isi')
                ->join("{$this->tablePrefix}sc_store ii on ii.sc_code = isi.sc_code",'LEFT')
                ->field('isi.id')
                ->where(array('isi.status'=>$status,'sic_code'=>array('in',$sic_codes),'ii.status'=>'ENABLE','isi.sc_code'=>array('in',$sc_code)))
                ->select();
        // if (!empty($res)) {
        //     return $this->res(null,4512);
        // }
        
        $data = array(
            'status' => $status,
            'update_time' => NOW_TIME,
        );
        //如果是去上架  则验证 该商品价格不能小于 0
       
        $where = array(
            'sic_code' => array('in', $sic_codes),
        );
        if ($status == IC_STORE_ITEM_ON) {
            $where['price'] = array('gt', 0);
            $where['status'] = 'OFF';
        } else {
            $where['status'] = 'ON';
        }
      
        $update_res = D('IcStoreItem')->where($where)->save($data);
       
        if ($update_res === FALSE || $update_res <= 0) {
            return $this->res(null, 4512);
        }
     
        return $this->res($update_res);
    }

}

