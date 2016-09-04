<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 商品相关模块
 */

namespace Base\ItemModule\Item;

use System\Base;

class Item extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * Base.ItemModule.Item.Item.addStandard
     * 添加标准库商品
     * @param type array
     */
    public function addStandard($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('goods_name', 'require', PARAMS_ERROR, MUST_CHECK),  # 
            array('sub_name', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('spec', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('packing', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('bar_code', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('goods_img', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('goods_img_new', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('goods_desc', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK),  # 
            array('is_standard', array('YES',"NO"), PARAMS_ERROR, ISSET_CHECK,'in'),  # 
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $goods_name      = $params['goods_name'];
        $sub_name        = $params['sub_name'];
        $brand           = $params['brand'];
        $spec            = $params['spec'];
        $packing         = $params['packing'];
        $bar_code        = $params['bar_code'];
        $goods_img       = $params['goods_img'];
        $goods_img_new   = $params['goods_img_new'];
        $goods_desc      = $params['goods_desc'];
        $status          = empty($params['status']) ? 'EDIT' : $params['status'];
        $is_standard     = empty($params['is_standard']) ? 'NO' :$params['is_standard'];
        
        //生成标准库编码
        $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
        $params = array(
            'busType'    =>IC_ITEM,
            'preBusType' =>IC_STANDARD_ITEM,
            'codeType'   =>SEQUENCE_ITEM,
        );
        $code_res = $this->invoke($apiPath, $params);
        if($code_res['status'] != 0){
            return $this->res(NULL,$code_res['status']);
        }

        $ic_code = $code_res['response'];
        $data = array(
            'ic_code'         => $ic_code,
            'goods_name'      => $goods_name,
            'sub_name'        => empty($sub_name) ? '' :$sub_name ,
            'brand'           => empty($brand) ? '' :$brand ,
            'spec'            => empty($spec) ? '' :$spec  ,
            'packing'         => empty($packing) ? '' :$packing ,
            'bar_code'        => empty($bar_code) ? '' :$bar_code ,
            'goods_img'       => empty($goods_img) ? '' :$goods_img ,
            'goods_img_new'   => empty($goods_img_new) ? '' :$goods_img_new,
            'goods_desc'      => empty($goods_desc) ? '' :$goods_desc ,
            'status'          => $status,
            'is_standard'     => $is_standard,
            'create_time'     => NOW_TIME,
            'update_time'     => NOW_TIME,
            'publish_time'    => NOW_TIME,
            
        );
        $add_res = D('IcItem')->add($data);

        if(FALSE === $add_res){
            return $this->res(NULL,4519);
        }
        
        return $this->res($ic_code);
        
    }

    /**
     * Base.ItemModule.Item.Item.addStoreItem
     * 添加标准库商品
     * @param type array
     */
    public function addStoreItem($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('ic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商品编码  需要选择的商品编码
            array('sic_no', 'require', PARAMS_ERROR, MUST_CHECK), //商品货号
            array('category_id_data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('tag_id_data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('source', 'require', PARAMS_ERROR, MUST_CHECK), //商品来源(POP, CMS)
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //得到所有的可操作商家
        $storeWhere = array(
            'status' => 'ENABLE',
            'sc_code' => $params['sc_code'],
            );
        $store = D('sc_store')->field('sc_code')->where($storeWhere)->select();
        if ($store <= 0 || $store === FALSE) {
            return $this->res('',4565);
        }

        $ic_code          = $params['ic_code'];
        $status           = empty($params['status']) ? IC_STORE_ITEM_OFF : $params['status'];
        $categoryRelation = $params['category_id_data'];
        $tagRelation      = $params['tag_id_data'];
        $source           = $params['source'];
        $sc_code          = $params['sc_code'];

        //批量插入商家选货商品
        $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
        $code_params = array(
            'busType'    => IC_ITEM,
            'preBusType' => IC_STORE_ITEM,
            'codeType'   => SEQUENCE_STORE_ITEM,
        );
        $code_res = $this->invoke($apiPath, $code_params);
        if ($code_res['status'] != 0) {
            return $this->res(null, 4513);
        }
        $sic_code = $code_res['response'];

        $data = array(
            'ic_code'     => $ic_code,
            'sub_name'    => empty($params['sub_name']) ? '' :$params['sub_name'] ,
            'sic_code'    => $sic_code,
            'sc_code'     => $sc_code,
            'sic_no'      => $params['sic_no'],
            'price'       => $params['price'] + 0,
            'min_num'     => empty($params['min_num']) ? 1 : $params['min_num'],
            'warn_stock'  => $params['warn_stock'],
            'stock'       => $params['stock'] + 0,
            'sort'        => empty($params['sort']) ? '' :$params['sort'],
            'tag_ids'     => empty($params['tag_ids'])? '':$params['tag_ids'],
            'qrcode'      => empty($params['qrcode'])? '':$params['qrcode'],
            'source'      => $source,
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME,
            'status'      => $status,
        );
        $add_res = D('IcStoreItem')->add($data);
        
        if (FALSE === $add_res || $add_res <= 0) {
            return $this->res(null, 4508);
        }

        if (!empty($categoryRelation) && is_array($categoryRelation)) {
            $categoryRelationData = array();
            foreach ($categoryRelation as $key => $category_id) {
                $arr = array();
                $arr['sc_code'] = $sc_code;
                $arr['sic_code'] = $sic_code;
                $arr['category_id'] = $category_id;
                $arr['create_time'] = NOW_TIME;
                $arr['update_time'] = NOW_TIME;
                $arr['status'] = 'ENABLE';
                $categoryRelationData[] = $arr;
            }
            $iCategoryRelationRes = D('IcItemCategoryRelation')->addAll($categoryRelationData);
            if ($iCategoryRelationRes <= 0 || $iCategoryRelationRes === FALSE) {
                return $this->res(NULL, 4536);
            }
        }
        if (!empty($tagRelation) && is_array($tagRelation)) {
            $tagRelationData = array();
            foreach ($tagRelation as $key => $tag_id) {
                $arr = array();
                $arr['sc_code']     = $sc_code;
                $arr['tag_id']      = $tag_id;
                $arr['sic_code']    = $sic_code;
                $arr['create_time'] = NOW_TIME;
                $arr['update_time'] = NOW_TIME;
                $arr['status']      = 'ENABLE';
                $tagRelationData[] = $arr;
            }
            $iTagRelationRes = D('IcItemTagRelation')->addAll($tagRelationData);
            if ($iTagRelationRes <= 0 || $iTagRelationRes === FALSE) {
                return $this->res(NULL, 4531);
            }
        }
        $item = array('sic_code'=> $sic_code);
        return $this->res($item); //添加成功
    }

    /**
     * 
     * 更新商家商品信息
     * Base.ItemModule.Item.Item.updateStoreItem
     * @param type array
     */
    public function updateStoreItem($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //系统商品编码
            // array('sub_name', '0,60', PARAMS_ERROR, HAVEING_CHECK, 'length'), //商品副标题
            array('sub_name', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('price', 'currency', PARAMS_ERROR, HAVEING_CHECK, 'regex'), //商品单价
            array('price', 0, PARAMS_ERROR, HAVEING_CHECK, 'gt'), //商品单价
            array('min_num', 'require', PARAMS_ERROR, ISSET_CHECK),
            // array('min_num', 1, PARAMS_ERROR, HAVEING_CHECK, 'egt'), //起订量
            // array('min_num', 'number', PARAMS_ERROR, HAVEING_CHECK, 'regex'), //起订量
            array('stock', 'require', PARAMS_ERROR, HAVEING_CHECK), //库存
            array('status', 'require', PARAMS_ERROR, ISSET_CHECK), //状态
            array('warn_stock', 'require', PARAMS_ERROR, ISSET_CHECK), //预警库存
            array('category_id_data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('tag_id_data', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK),         # 商家商品名称
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK),              # 商品品牌
            array('spec', 'require', PARAMS_ERROR, ISSET_CHECK),               # 商品规格
            array('packing', 'require', PARAMS_ERROR, ISSET_CHECK),            # 商品包装
            array('goods_img', 'require', PARAMS_ERROR, ISSET_CHECK),          # 商品默认图片
            array('goods_img_new', 'require', PARAMS_ERROR, ISSET_CHECK),      # 商品图集
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK),             # 商家商品编码
            array('sort', 'require', PARAMS_ERROR, ISSET_CHECK),             //商品权重
            array('tag_ids', 'require', PARAMS_ERROR, ISSET_CHECK),             //商品权重

        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sic_code = $params['sic_code'];
        $sc_code  = $params['sc_code'];
        $sort = $params['sort'];
        $store = D('sc_store')->field('sc_code')->where(array('status'=>'ENABLE'))->select();
        $store =array_column($store,'sc_code');
        if(!in_array($sc_code,$store)){
            return $this->res('',4565);
        }
        //查找商家商品信息
        $ic_store_item = D('IcStoreItem')->where(array('sic_code' => $sic_code, 'sc_code' => $sc_code))->find();
        if (empty($ic_store_item)) {
            return $this->res(null, 4504);
        }

        $ic_code = $ic_store_item['ic_code'];
        //标准库中该商品是否处于发布状态
        $ic_item = D('IcItem')->where(array('ic_code' => $ic_code, 'status' => IC_ITEM_PUBLISH))->find();

        if (empty($ic_item)) {
            return $this->res(null, 4509);
        }

        //需要更新的数据 和 条件
        $update_data['update_time'] = NOW_TIME;
        if (isset($params['sub_name'])) {
            $update_data['sub_name'] = $params['sub_name'];
        }

        if(isset($params['sic_no'])){
            $sic_no_where = array(
                'sc_code'=>$params['sc_code'],
                'sic_no'=>$params['sic_no'],
                'sic_code'=>array('neq',$sic_code),
                );

            $sic_no_res = D('IcStoreItem')->where($sic_no_where)->find();


            if(empty($sic_no_res)){
                $update_data['sic_no'] = $params['sic_no'];
            }else{
                return $this->res(null,4555);
            }
        }

        if (isset($params['price']) && !empty($params['price'])) {
            $update_data['price'] = $params['price'];
        }

        if (isset($params['sort'])) {
            $update_data['sort'] = $params['sort'];
        }

        if (isset($params['min_num']) && !empty($params['min_num'])) {
            $update_data['min_num'] = $params['min_num'] < 100000000 ? abs(intval($params['min_num'])) : 99999999;
        }

        if (isset($params['stock']) && !empty($params['stock'])) {
            $params['stock']      = abs(intval($params['stock'])) < 100000000 ? abs(intval($params['stock'])) : 99999999;
            $params['warn_stock'] = abs(intval($params['warn_stock'])) < 100000000 ? abs(intval($params['warn_stock'])) : 99999999;

            if ($ic_store_item['stock'] <= 0) {
                $update_data['stock'] = $params['stock'] + $ic_store_item['stock'];
            }else{
                $update_data['stock'] = $params['stock']; 
            }
            
        }
        
        $params['warn_stock']      = abs(intval($params['warn_stock']));
        $update_data['warn_stock'] = $params['warn_stock'];

        if(!empty($params['status'])){
            $update_data['status'] = $params['status'];
        }

        // 图片二维码修改
        if(!empty($params['goods_img'])  && ($ic_item['goods_img'] !== $params['goods_img'])){
            //生成二维码
            $url = C('CHANNEL_QRCODE_URL')."Index/get/sic_code/{$sic_code}";
            $Qrcode = new \Library\qrcodes();
            $qrcode_url = $Qrcode->generateQrcodeByUrl($url, '', 100, $params['goods_img']);
            if(empty($qrcode_url)){
                return $this->res(NULL,4556);
            }

            //上传到阿里云
            $img_url  = upload_cloud($qrcode_url);
            if(empty($img_url)){
                return $this->res(NULL,4557);
            }
            $update_data['qrcode'] = $img_url;
            $update_data['goods_img'] = $params['goods_img'];
        }

        // TODO
        $where = array(
            'sic_code' => $sic_code,
            'sc_code' => $sc_code,
        );

        if (isset($params['tag_ids'])) {
            $update_data['tag_ids'] = $params['tag_ids'];
        }

        $update_res = D('IcStoreItem')->where($where)->save($update_data);

        if ($update_res <= 0 || $update_res === FALSE) {
            return $this->res(null, 4510);
        }

        // 更新标准库商品信息
        !empty($params['goods_name']) && $item_data['goods_name'] = $params['goods_name'];
        isset($params['brand']) && $item_data['brand'] = $params['brand'];
        !empty($params['spec'])  && $item_data['spec'] = $params['spec'];
        !empty($params['packing']) && $item_data['packing'] = $params['packing'];
        !empty($params['goods_img']) && $item_data['goods_img'] = $params['goods_img'];
        !empty($params['goods_img_new']) && $item_data['goods_img_new'] = $params['goods_img_new'];
        isset($params['sub_name']) && $item_data['sub_name'] = $params['sub_name'];

        // 检查商品规格是否存在
        if(!empty($params['goods_name']) && !empty($params['spec'])){
            $goods_map['ii.goods_name'] = $params['goods_name'];
            $goods_map['ii.spec'] = $params['spec'];
            $goods_map['ii.ic_code'] = array('neq',$ic_code);
            $goods_map['isi.sc_code'] = array('eq',$params['sc_code']);
            $goods_res = D('IcItem')->alias('ii')->join("{$this->tablePrefix}ic_store_item as isi on ii.ic_code = isi.ic_code","left")->where($goods_map)->find();
            if($goods_res){
                return $this->res(null,4522);
            }
        }
        
        if(count($item_data) > 0){
            $item_data['update_time'] = NOW_TIME;
            $item_map['ic_code']  = $ic_code;
            $item_res = D('IcItem')->where($item_map)->save($item_data);
            if($item_res === FALSE){
                return $this->res(null,4510);
            }
        }
        
        //更新分类信息
        $categoryId = $params['category_id_data'];    
        $tagId      = $params['tag_id_data'];
        $categoryIdNum = count($categoryId);
        if ($categoryIdNum > 3 || $categoryIdNum < 0) {
            return $this->res(NULL, 4559);
        }
        if (!empty($categoryId)) {
            $categoryWhere = array();
            $categoryWhere['sc_code'] = $sc_code;
            $categoryWhere['sic_code'] = $sic_code;
            $categoryWhere['status'] = 'ENABLE';
            $categoryRes = D('IcItemCategoryRelation')->where($categoryWhere)->select();
            if ($categoryRes === FALSE ) {
                return $this->res(NULL, 4541);
            }

            if (!empty($categoryRes)) {
                $categoryData = array();
                $categoryData['status'] = 'DISABLE';
                $categoryRes = D('IcItemCategoryRelation')->where($categoryWhere)->save($categoryData);
                if ($categoryRes === FALSE || $categoryRes <= 0) {
                    return $this->res(NULL, 4544);
                }
            }

            $categoryRelationData = array();
            foreach ($categoryId as $key => $value) {
                $arr = array();
                $arr['sc_code']     = $sc_code;
                $arr['sic_code']    = $sic_code;
                $arr['category_id'] = $value;
                $arr['create_time'] = NOW_TIME;
                $arr['update_time'] = NOW_TIME;
                $arr['status']      = 'ENABLE';
                $categoryRelationData[] = $arr;
            }
            $iCategoryRelationRes = D('IcItemCategoryRelation')->addAll($categoryRelationData);
            if ($iCategoryRelationRes <= 0 || $iCategoryRelationRes === FALSE) {
                return $this->res(NULL, 4536);
            }

        }else{
            if (isset($params['category_id_data'])) {
                $categoryWhere = array();
                $categoryWhere['sc_code'] = $sc_code;
                $categoryWhere['sic_code'] = $sic_code;
                $categoryWhere['status'] = 'ENABLE';
                $categoryRes = D('IcItemCategoryRelation')->where($categoryWhere)->select();
                if ($categoryRes === FALSE ) {
                    return $this->res(NULL, 4541);
                }

                if (!empty($categoryRes)) {
                    $categoryData = array();
                    $categoryData['status'] = 'DISABLE';
                    $categoryRes = D('IcItemCategoryRelation')->where($categoryWhere)->save($categoryData);
                    if ($categoryRes === FALSE || $categoryRes <= 0) {
                        return $this->res(NULL, 4544);
                    }
                }
            }
            
        }


        //更新标签信息
        if (!empty($tagId)) {
            $tagWhere = array();
            $tagWhere['sc_code'] = $sc_code;
            $tagWhere['sic_code'] = $sic_code;
            $tagWhere['status'] = 'ENABLE';
            $tagRes = D('IcItemTagRelation')->where($tagWhere)->select();
            if ($tagRes === FALSE ) {
                return $this->res(NULL, 4541);
            }

            if (!empty($tagRes)) {
                $tagData = array();
                $tagData['status'] = 'DISABLE';
                $tagRes = D('IcItemTagRelation')->where($tagWhere)->save($tagData);
                if ($tagRes === FALSE ) {
                    return $this->res(NULL, 4541);
                }
            }

            $tagRelationData = array();
            foreach ($tagId as $key => $value) {
                $arr = array();
                $arr['sc_code']     = $sc_code;
                $arr['sic_code']    = $sic_code;
                $arr['tag_id']      = $value;
                $arr['create_time'] = NOW_TIME;
                $arr['update_time'] = NOW_TIME;
                $arr['status']      = 'ENABLE';
                $tagRelationData[]  = $arr;
            }
            $iTagRelationRes = D('IcItemTagRelation')->addAll($tagRelationData);
            if ($iTagRelationRes <= 0 || $iTagRelationRes === FALSE) {
                return $this->res(NULL, 4531);
            }

            $where = array();
            $where['id']      = array('in', $tagId);
            $where['sc_code'] = $sc_code;
            $fields           = 'id,tag_weight';
            $order  = 'tag_weight desc';
            $tagRes = D('IcTag')->where($where)->field($fields)->order($order)->select();
            if ($tagRes === FALSE) {
                return $this->res(NULL, 4534);
            }

            $tag_weight = array_column($tagRes, 'tag_weight');
            $tagIds     = array_column($tagRes, 'id');
            $itemWhere = $itemData = array();
            $itemWhere['sc_code']    = $sc_code;
            $itemWhere['sic_code']   = $sic_code;
            $itemData['tag_ids']     = implode(',', $tagIds);
            $itemData['sort']        = array_sum($tag_weight);
            $itemData['update_time'] = NOW_TIME;

            $itemRes = D('IcStoreItem')->where($itemWhere)->save($itemData);
            
            if ($itemRes === FALSE ) {
                return $this->res(NULL, 4518);
            }
        }else{
            if (isset($params['tag_id_data'])) {
               $tagWhere = array();
               $tagWhere['sc_code'] = $sc_code;
               $tagWhere['sic_code'] = $sic_code;
               $tagWhere['status'] = 'ENABLE';

               $tagData = array();
               $tagData['status'] = 'DISABLE';
               $tagRes = D('IcItemTagRelation')->where($tagWhere)->save($tagData);
               if ($tagRes === FALSE ) {
                   return $this->res(NULL, 4541);
               }

               $itemWhere = $itemData = array();
               $itemWhere['sc_code']    = $sc_code;
               $itemWhere['sic_code']   = $sic_code;
               $itemData['tag_ids']     = '';
               $itemData['sort']        = 0;
               $itemData['update_time'] = NOW_TIME;

               $itemRes = D('IcStoreItem')->where($itemWhere)->save($itemData);
               
               if ($itemRes === FALSE ) {
                   return $this->res(NULL, 4518);
               } 
            }
            
        }

        return $this->res(TRUE);
    }

    /**
     * Base.ItemModule.Item.Item.setItemStock
     * @param [type] $params [description]
     */
    public function setItemStock($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('stock', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //变量初始化
        $sc_code  = $params['sc_code'];
        $sic_code = $params['sic_code'];
        $stock    = $params['stock'];

        //查询条件（验证商品是否存在）
        $where = array();
        $where['sc_code']  = $sc_code;
        $where['sic_code'] = $sic_code;
        $itemRes = D('IcStoreItem')->where($where)->find();
        if ($itemRes == FALSE || $itemRes <= 0) {
            return $this->res(NULL, 4541);
        }

        //商品库存条件验证
        $stock      = abs(intval($stock)) < 100000000 ? abs(intval($stock)) : 99999999;

        //商品库存数据处理（并更新）
        $itemData = array();
        if ($itemRes['stock'] <= 0) {
            $itemData['stock'] = $stock + $itemRes['stock'];
        }else{
            $itemData['stock'] = $stock; 
        }

        $itemData['update_time'] = NOW_TIME;
        $uItemRes = D('IcStoreItem')->where($where)->save($itemData);
        // echo D({$item['stock']})->getLastSql();die();
        if ($uItemRes === FALSE || $uItemRes <= 0) {
            return $this->res(NULL, 4542);
        }

        return $this->res(true);
    }

    /**
     * Base.ItemModule.Item.Item.qrcode
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function qrcode($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商品唯一标识
            array('goods_img', 'require', PARAMS_ERROR, MUST_CHECK), //商品主图
        );
        
        if (!$this->checkInput($this->_rule, $params)) { 
          return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code   = $params['sc_code'];
        $sic_code  = $params['sic_code'];
        $goods_img = $params['goods_img'];

        $where = array();
        $where['sc_code']  = $sc_code;
        $where['sic_code'] = $sic_code;
        $item = D('IcStoreItem')->where($where)->master()->find();
        if ($item == FALSE) {
            return $this->res(NULL, 4509);
        }

        //生成二维码
        $url = C('CHANNEL_QRCODE_URL')."Index/get/sic_code/{$sic_code}";
        $Qrcode = new \Library\qrcodes();
        $qrcode_url = $Qrcode->generateQrcodeByUrl($url, '', 100, $goods_img);
        if(empty($qrcode_url)){
            return $this->res(NULL,4556);
        }

        //上传到阿里云
        $img_url  = upload_cloud($qrcode_url);
        if(empty($img_url)){
            return $this->res(NULL,4557);
        }
        $data = array(
            'qrcode'       => $img_url,
            'update_time' => NOW_TIME,
        );

        $where = array(
            'sc_code'  =>$sc_code,
            'sic_code' => $sic_code,
        );

        $res = D('IcStoreItem')->where($where)->save($data);
        if($res <= 0 && $res === FALSE){
            return $this->res(NULL,4503);
        }
        return $this->res($img_url);    
    }

    /**
     * Base.ItemModule.Item.Item.searchConItems
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function searchConItems($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('goods_name', '0,20', PARAMS_ERROR, HAVEING_CHECK, 'length'), //商品名称
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //商品货号
            array('sic_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('limit', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('unique', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        
        if (!$this->checkInput($this->_rule, $params)) { 
          return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $fields                 = "ii.goods_name, isi.sic_code, isi.sic_no, ii.brand, ii.spec, ii.packing, isi.sub_name, isi.price, isi.stock";
        $limit                  = empty($params['limit']) ? '': $params['limit']; //搜索条数
        $where['isi.sic_no']    = array('like', "%{$params['goods_name']}%");  //匹配商家商品编码
        $where['ii.goods_name'] = array('like', "%{$params['goods_name']}%"); //匹配商品名称
        $where['_logic']        = 'or';
        $map['isi.sc_code']     = array('EQ', $params['sc_code']);  //店铺编码
        $map['ii.status']       = array('EQ', 'PUBLISH');  //过滤标准库中的ic_item status不等于PUBLISH
        
        switch ($params['type']) {
            case 'spc':
                // $map['isi.stock'] = array('gt', 0);
                break;
        }
        if (!$params['search_sign']) {
            $map['isi.status']      = array('EQ', IC_STORE_ITEM_ON);  // 商家商品必须是上家的
        }
        $map['_complex']        = $where;

        $select_res = D('IcItem')->alias('ii')
                        ->join("{$this->tablePrefix}ic_store_item isi on ii.ic_code = isi.ic_code",'LEFT')
                        ->field("{$fields}")
                        ->where($map)
                        ->limit($limit)
                        ->select();
        
        $params['unique'] = empty($params['unique']) || !isset($params['unique']) ? FALSE : TRUE;
        if ($params['unique'] === FALSE) {
            $arr = array();
            foreach ($select_res as $val) {
                if (!in_array($val['goods_name'], $arr)) {
                    $arr[] = $val['goods_name'];
                }else{
                    unset($val);
                }
            }
        }
        
        return $this->res($select_res);
    }
    /**
     * Base.ItemModule.Item.Item.setStatus
     * @param [type] $params [description]
     */
    public function setStatus($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, MUST_CHECK, 'in'), //必须是上下架状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $sc_code   = $params['sc_code'];
        $sic_codes = $params['sic_codes'];
        $status    = $params['status'];

        $data = array(
            'status' => $status,
            'update_time' => NOW_TIME,
        );

        //如果是去上架  则验证 该商品价格不能小于 0 
        if ($status == IC_STORE_ITEM_ON) {
            $where['price'] = array('gt', 0);
        }
        $where = array(
            'sic_code' => array('in', $sic_codes),
            'sc_code'  => $sc_code,
        );
        
        $update_res = D('IcStoreItem')->where($where)->save($data);
        
        if ($update_res === FALSE) {
            return $this->res(null, 4512);
        }

        return $this->res($update_res);
    }
    

    /**
     * Base.ItemModule.Item.Item.checkSpc
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function checkSpc($params){
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, MUST_CHECK, 'in'), //必须是上下架状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_codes                 = $params['sic_codes'];
        $fields                    = "sg.gift_sic_code";
        $where['sg.gift_sic_code'] = array('in', $params['sic_codes']);
        $where['sl.sc_code']       = array('EQ', $params['sc_code']);
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
       
        $fields          = "sic_code";
        $map['sic_code'] = array('in', $params['sic_codes']);
        $map['sc_code']  = array('EQ', $params['sc_code']);
        $map['status']   = IC_ITEM_PUBLISH;
        $map['end_time'] = array('gt', NOW_TIME);
        $spc_res         = D('SpcList')->field("{$fields}")->where($map)->select();

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
        
        if (empty($sic_codes)) {
            return $this->res(null,4525);
        }else{
            $params['sic_codes'] = $sic_codes;
            return $this->res($params);
        }

    } 

    /**
     * Base.ItemModule.Item.Item.setCategory
     * @param [type] $params [description]
     */
    public function setCategory($params){
//       $params = array(
//               'sc_code' => 1020000000026,
//               'sic_codes' => array("1211",'333','123'),
//               'category_id' => array(5,6,8),
//           );
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('category_id', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家商品种类编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_codes   = $params['sic_codes'];
        $sc_code     = $params['sc_code'];
        $category_id = $params['category_id'];

        if (empty($sic_codes) || !is_array($sic_codes)) {
            return $this->res(NULL, 4545);
        }
        if (empty($category_id) || !is_array($category_id)) {
            return $this->res(NULL, 4545);
        }
        $categoryIdNum = count($category_id);
        if ($categoryIdNum > 3 || $categoryIdNum < 0) {
            return $this->res(NULL, 4559);
        }

        $where = array();
        $where['sc_code']  = $sc_code;
        $where['status']   = 'ENABLE';
        $where['sic_code'] = array('in', $sic_codes);
        $fields = "sic_code,sc_code,category_id";
        $itemCategoryRes = D('IcItemCategoryRelation')->where($where)->field($fields)->select();
        if ($itemCategoryRes === FALSE) {
            return $this->res(NULL, 4540);
        }
        $otherItem = array();
        if (!empty($itemCategoryRes)) {
            $itemCategory = array();
            foreach ($itemCategoryRes as $value) {
                if ($itemCategory[$value['sic_code']]) {
                    $itemCategory[$value['sic_code']][] = $value['category_id'];
                }else{
                    $arr = array();
                    $arr[] = $value['category_id'];
                    $itemCategory[$value['sic_code']] = $arr;
                }
            }

            $itemCategory_sic_code = array_unique(array_column($itemCategoryRes, 'sic_code'));
            $diff_sic_codes = array();
            foreach ($sic_codes as $sic_code) {
                if (!in_array($sic_code, $itemCategory_sic_code)) {
                    $diff_sic_codes[] = $sic_code;
                }
            }
            // var_dump($itemCategoryRes);
            $item = array();
            foreach ($itemCategory as $sic_code => $category) {
                $arr = array_unique(array_merge($category, $category_id));
                $num = count($arr);
                if ($num <= 3) {
                   $item[$sic_code] = array('category_id'=>$arr,'sic_code'=> $sic_code);
                }else{
                   $otherItem[] = $sic_code;
                }
            }
            // var_dump($otherItem);die();
            $aCategoryData = array();
            if (!empty($item)) {
                $uSic_codes = array_keys($item);
                $uWhere = $uCategoryData = array();
                $uWhere['sc_code']       = $sc_code;
                $uWhere['status']        = 'ENABLE';
                $uWhere['sic_code']      = array('in', $uSic_codes);
                $uCategoryData['status'] = 'DISABLE';
                $uCategoryRes = D('IcItemCategoryRelation')->where($uWhere)->save($uCategoryData);

                if ($uCategoryRes == FALSE || $uCategoryRes <= 0) {
                    return $this->res(NULL, 4539);
                }

                foreach ($item as $sic_code => $category) {
                   foreach ($category['category_id'] as $value) {
                       $arr = array();
                       $arr['sc_code']     = $sc_code;
                       $arr['sic_code']    = $sic_code;
                       $arr['category_id'] = $value;
                       $arr['create_time'] = NOW_TIME;
                       $arr['update_time'] = NOW_TIME;
                       $arr['status']      = 'ENABLE';
                       $aCategoryData[]    = $arr;
                   }
                }

            }

            if (!empty($diff_sic_codes)) {
                foreach ($diff_sic_codes as $sic_code) {
                    foreach ($category_id as $value) {
                       $arr = array();
                       $arr['sc_code']     = $sc_code;
                       $arr['sic_code']    = $sic_code;
                       $arr['category_id'] = $value;
                       $arr['create_time'] = NOW_TIME;
                       $arr['update_time'] = NOW_TIME;
                       $arr['status']      = 'ENABLE';
                       $aCategoryData[]    = $arr;
                    }
                }
            }
           
        }else{
            $aCategoryData = array();
            foreach ($sic_codes as $sic_code) {
                foreach ($category_id as $value) {
                   $arr = array();
                   $arr['sc_code']     = $sc_code;
                   $arr['sic_code']    = $sic_code;
                   $arr['category_id'] = $value;
                   $arr['create_time'] = NOW_TIME;
                   $arr['update_time'] = NOW_TIME;
                   $arr['status']      = 'ENABLE';
                   $aCategoryData[]    = $arr;
                }
            }
        }
        if (!empty($aCategoryData)) {
           $aCategoryRes = D('IcItemCategoryRelation')->addAll($aCategoryData);
           if ($aCategoryRes == FALSE || $aCategoryRes <= 0) {
               return $this->res(NULL, 4536);
           }
        }
        
        $exceptionItem = array(
                'exceptionItem' => $otherItem,
            );
        return $this->res($exceptionItem);
    }

    /**
     * Base.ItemModule.Item.Item.setTag
     * @param [type] $params [description]
     */
    public function setTag($params){
        $this->_rule = array(
            array('sic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('tag_id', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商家标签编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }


        $sic_codes = $params['sic_codes'];
        $sc_code   = $params['sc_code'];
        $tag_id    = $params['tag_id'];
        if (empty($sic_codes) || !is_array($sic_codes)) {
            return $this->res(NULL, 4545);
        }
        if (empty($tag_id) || !is_array($tag_id)) {
            return $this->res(NULL, 4545);
        }

        $tagIdNum = count($tag_id);
        if ($tagIdNum > 2 || $tagIdNum < 0) {
            return $this->res(NULL, 4558);
        }
        $where = array();
        $where['sc_code']  = $sc_code;
        $where['status']   = 'ENABLE';
        $where['sic_code'] = array('in', $sic_codes);
        $fields  = "sic_code,sc_code,tag_id";
        $itemTagRes = D('IcItemTagRelation')->where($where)->field($fields)->select();
        if ($itemTagRes === FALSE) {
            return $this->res(NULL, 4534);
        }
        //查询已经有标签商品
        if (!empty($itemTagRes)) {
            $itemTag = array();
            foreach ($itemTagRes as $value) {
                if ($itemTag[$value['sic_code']]) {
                    $itemTag[$value['sic_code']][] = $value['tag_id'];
                }else{
                    $arr = array();
                    $arr[] = $value['tag_id'];
                    $itemTag[$value['sic_code']] = $arr;
                }
            }
            //和已经有标签的sic_code(取差集)
            $itemTag_sic_code = array_unique(array_column($itemTagRes, 'sic_code'));

            $diff_sic_codes = array();
            foreach ($sic_codes as $sic_code) {
                if (!in_array($sic_code, $itemTag_sic_code)) {
                    $diff_sic_codes[] = $sic_code;
                }
            }

            //有标签商品，标签数量验证
            $item = array();
            foreach ($itemTag as $sic_code => $tag) {
                $arr = array_unique(array_merge($tag, $tag_id));
                $num = count($arr);
                if ($num <= 2) {
                   $item[$sic_code] = array('tag_id'=>$arr,'sic_code'=> $sic_code);
                }else{
                   $otherItem[] = $sic_code;
                }
            }
            
            // var_dump($tag_id);
            // var_dump($itemTag ,$itemTag_sic_code,$diff_sic_codes);die();

            $aTagData = array();
            if (!empty($item)) {
                $uSic_codes = array_keys($item);
                $uWhere = $uTagData = array();
                $uWhere['sc_code']  = $sc_code;
                $uWhere['status']   = 'ENABLE';
                $uWhere['sic_code'] = array('in', $uSic_codes);
                $uTagData['status'] = 'DISABLE';
                $uTagRes = D('IcItemTagRelation')->where($uWhere)->save($uTagData);

                if ($uTagRes === FALSE ) {
                   return $this->res(NULL, 4539);
                }

                foreach ($item as $sic_code => $tag) {
                   foreach ($tag['tag_id'] as $value) {
                       $arr = array();
                       $arr['sc_code']     = $sc_code;
                       $arr['sic_code']    = $sic_code;
                       $arr['tag_id']      = $value;
                       $arr['create_time'] = NOW_TIME;
                       $arr['update_time'] = NOW_TIME;
                       $arr['status']      = 'ENABLE';
                       $aTagData[]         = $arr;
                   }
                }
            }

            //差集标签进行插入
            if (!empty($diff_sic_codes)) {
                foreach ($diff_sic_codes as $sic_code) {
                    foreach ($tag_id as $value) {
                       $arr                = array();
                       $arr['sc_code']     = $sc_code;
                       $arr['sic_code']    = $sic_code;
                       $arr['tag_id']      = $value;
                       $arr['create_time'] = NOW_TIME;
                       $arr['update_time'] = NOW_TIME;
                       $arr['status']      = 'ENABLE';
                       $aTagData[]         = $arr;
                    }
                }
            }
        }else{
            //查询sic_code都是没有标签
            $aTagData = array();
            foreach ($sic_codes as $sic_code) {
                foreach ($tag_id as $value) {
                    $arr                = array(); 
                    $arr['sc_code']     = $sc_code;
                    $arr['sic_code']    = $sic_code;
                    $arr['tag_id']      = $value;
                    $arr['create_time'] = NOW_TIME;
                    $arr['update_time'] = NOW_TIME;
                    $arr['status']      = 'ENABLE';
                    $aTagData[]         = $arr;
                }
            }
        }
        // var_dump($aTagData);
        // die();
        if (!empty($aTagData)) {
            $aTagRes = D('IcItemTagRelation')->addAll($aTagData);
            // echo D()->getLastsql();
            if ($aTagRes === FALSE ) {
                return $this->res(NULL, 4531);
            }
        }
        
        return $this->res(TRUE);    
    }

    /**
     * 
     * Base.ItemModule.Item.Item.warnStockCount
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public  function warnStockCount($params)
    {   
        $map['isi.sc_code'] = array('eq', $params['sc_code']);
        $map['_string'] = "isi.stock <= isi.warn_stock";
        $warnItemCount = D('IcItem')->alias('ii')
                                    ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
                                    ->where($map)
                                    ->count();
        return $warnItemCount;
    }
     /**
     * 
     * Base.ItemModule.Item.Item.setClassByCodes
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function setClassByCodes($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('ic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商品编码
            array('class_id', 'require', PARAMS_ERROR, MUST_CHECK), //分类ID
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $res = D('IcStoreItem')->where(array('status'=>$params['status'],'ic_code'=>array('in',$params['ic_codes'])))->select();

        if ( count($params['ic_codes']) != count($res) ) {
            return $this->res(null, 4572);
        }
        $where['ic_code'] = array('in',$params['ic_codes']);
        $update_res = D('IcItem')->where($where)->save(array('class_id'=>$params['class_id']));
        if ($update_res === FALSE || $update_res < 0) {
            return $this->res(null, 4572);
        }
        return $this->res($update_res);
    }

}

?>
