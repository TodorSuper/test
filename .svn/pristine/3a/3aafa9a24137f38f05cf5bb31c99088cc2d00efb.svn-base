<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 店铺商品相关模块
 */

namespace Base\StoreModule\Item;

use System\Base;

class Item extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * 店铺标准库列表
     * Base.StoreModule.Item.Item.standardItem
     * @param type $params
     */
    public function standardItem($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('category_end_id', 'require', PARAMS_ERROR, ISSET_CHECK), //分类id
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK), //品牌
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code         = $params['sc_code'];
        $category_end_id = $params['category_end_id'];
        $brand           = $params['brand'];
        $goods_name      = $params['goods_name'];

        //查询已经选货的商品
        $selected_codes = array();
        $selected = D('IcStoreItem')->where(array('sc_code' => $sc_code, 'is_standard' => 'YES'))->field('ic_code')->select();
        if ($selected) {
            $selected_codes = array_column($selected, 'ic_code');
        }
        //where条件
        $where['ii.status'] = IC_ITEM_PUBLISH; //必须是发布状态的商品
        !empty($selected_codes) && $where['ii.ic_code'] = array('not in ', $selected_codes);   //如果有已经选择的商品  则需要先排除
        !empty($category_end_id) && $where['ii.category_end_id'] = $category_end_id;
        !empty($brand) && $where['ii.brand'] = array('like', "%{$brand}%");
        !empty($goods_name) && $where['ii.goods_name'] = array('like', "%{$goods_name}%");

        $order  = " ii.ic_code desc ";  //排序
        $fields = "ii.ic_code,ii.bar_code,ii.goods_img,ii.goods_name,ii.sub_name,ii.spec,ii.packing,ii.brand,ce.name as category_name"; //查询字段

        $params['order']       = $order; //排序
        $params['center_flag'] = SQL_SC; //店铺中心   
        $params['sql_flag']    = 'stardand_list';  //sql标示
        $params['where']       = $where; //where条件
        $params['fields']      = $fields; //查询字段

        $apiPath  = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        
        if ($list_res['status'] != 0) {
            return $this->res(null, $list_res['status']);
        }
        return $this->res($list_res['response']);  //返回列表
    }
   
    /**
     * 
     * 搜索商品
     * Base.StoreModule.Item.Item.searchConItems
     * @param type $params
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
     * 
     * 选择标准库商品
     * Base.StoreModule.Item.Item.selectItem
     * @param type $params
     */
    public function selectItem($params) {

        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('ic_codes', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'), //商品编码  需要选择的商品编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code = $params['sc_code'];
        $ic_codes = array_unique($params['ic_codes']);

        //首先验证 选择的商品中是否有已经 选择了的
        $select_res = D('IcStoreItem')->where(array('sc_code' => $sc_code, 'ic_code' => array('in', $ic_codes)))->find();
        if ($select_res) {
            return $this->res(null, 4506);
        }

        //查找标准库商品副标题   
        $ori_item = D('IcItem')->where(array('ic_code' => array('in', $ic_codes), 'status' => IC_ITEM_PUBLISH))
                        ->field('sub_name,ic_code')->select();
        if (count($ori_item) != count($ic_codes)) {
            return $this->res(null, 4507);
        }

        //批量插入商家选货商品
        $data = array();
        $apiPath = "Com.Tool.Code.CodeGenerate.mkCode";
        $code_params = array(
            'busType' => IC_ITEM,
            'preBusType' => IC_STORE_ITEM,
            'codeType' => SEQUENCE_STORE_ITEM,
        );
        foreach ($ori_item as $k => $v) {
            $code_res = $this->invoke($apiPath, $code_params);
            if ($code_res['status'] != 0) {
                return $this->res(null, 4513);
            }
            $sic_code = $code_res['response'];
            $data[] = array(
                'ic_code' => $v['ic_code'],
                'sub_name' => $v['sub_name'],
                'sic_code' => $sic_code,
                'sc_code' => $sc_code,
                'create_time' => NOW_TIME,
                'update_time' => NOW_TIME,
                'status' => IC_STORE_ITEM_OFF,
            );
        }
        $add_res = D('IcStoreItem')->addAll($data);
        if (FALSE === $add_res || $add_res <= 0) {
            return $this->res(null, 4508);
        }
        return $this->res($add_res); //选货成功
    }

    /**
     * 
     * 查找商家单条商品信息
     * Base.StoreModule.Item.Item.getStoreItem
     * @param type $params
     */
    public function getStoreItem($params) {
        $this->_rule = array(
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家商品编码
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, HAVEING_CHECK, 'in'), //商家商品状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_code = $params['sic_code'];
        $status   = $params['status'];
        $sc_code  = $params['sc_code'];
        $where = array(
            'ii.status' => IC_ITEM_PUBLISH,
            'isi.sic_code' => $sic_code
        );
        !empty($status) && $where['isi.status'] = $status;
        !empty($sc_code) && $where['isi.sc_code'] = $sc_code;

        $storeItem = D('IcItem')->alias('ii')
                        ->join("{$this->tablePrefix}ic_store_item isi on ii.ic_code = isi.ic_code", 'LEFT')
                        ->join("{$this->tablePrefix}category_end ce on ii.category_end_id = ce.id", 'LEFT')
                        ->field('ii.*,isi.sc_code,isi.sic_code,isi.sub_name as store_sub_name,isi.price,isi.min_num,isi.sic_no,isi.stock,isi.warn_stock,isi.is_standard,isi.status as store_status,ce.name as category_name,ce.pid as c_pid')
                        ->where($where)->find();
        // var_dump($storeItem);
        if (empty($storeItem)) {
            return $this->res(null, 4509);
        }

        //图片  解析
        $storeItem['goods_img_new'] = str_replace('&quot;', '"', $storeItem['goods_img_new']);
        $storeItem['goods_img_new'] = json_decode($storeItem['goods_img_new'], true);
        //解析描述  保存的时候嵌入了延迟加载的代码
        $storeItem['goods_desc'] = show_lazy_load($storeItem['goods_desc']);
        //商品一级分类的名称和id
        if ($storeItem['c_pid']) {
            $category_f_end_name = D('CategoryEnd')->where(array('id' => $storeItem['c_pid']))->field('name')->find();
            if ($category_f_end_name) {
                $storeItem['category_f_name'] = $category_f_end_name['name'];
            }
        }

        return $this->res($storeItem);
    }

    /**
     * 
     * 更新商家商品信息
     * Base.StoreModule.Item.Item.updateStoreItem
     * @param type $params
     */
    public function updateStoreItem($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家商品编码
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
            array('goods_img_new', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('goods_img', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_code = $params['sic_code'];
        $sc_code  = $params['sc_code'];
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

        if (isset($params['price']) && !empty($params['price'])) {
            $update_data['price'] = $params['price'];
        }

        if (isset($params['min_num']) && !empty($params['min_num'])) {
            $update_data['min_num'] = $params['min_num'] < 100000000 ? abs(intval($params['min_num'])) : 99999999;
        }

        if (isset($params['stock']) && !empty($params['stock'])) {
            $params['stock']      = abs(intval($params['stock'])) < 100000000 ? abs(intval($params['stock'])) : 99999999;
            $params['warn_stock'] = abs(intval($params['warn_stock'])) < 100000000 ? abs(intval($params['warn_stock'])) : 99999999;

            if (!empty($params['stock']) && !empty($params['warn_stock'])) {
                if ($params['stock'] < $params['warn_stock']) {
                    return $this->res(NULL, 4525);
                }
            }

            if (!empty($params['stock']) && empty($params['warn_stock'])) {
                if ($params['stock'] < $ic_item['warn_stock']) {
                    return $this->res(NULL, 4525);
                }
            }

            if ($ic_store_item['stock'] <= 0) {
                $update_data['stock'] = $params['stock'] + $ic_store_item['stock'];
            }else{
                $update_data['stock'] = $params['stock']; 
            }
            
        }
        if (isset($params['warn_stock']) && !empty($params['warn_stock'])) {
            $params['warn_stock']      = abs(intval($params['warn_stock']));
            $update_data['warn_stock'] = $params['warn_stock'];
        }

        if(!empty($params['status'])){
            $update_data['status'] = $params['status'];
        }

        $where = array(
            'sic_code' => $sic_code,
            'sc_code' => $sc_code,
//            'status' => IC_STORE_ITEM_OFF, //必须是下架商品才可以进行编辑
        );
        $update_res = D('IcStoreItem')->where($where)->save($update_data);
        if ($update_res <= 0 || $update_res === FALSE) {
            return $this->res(null, 4510);
        }

        if (isset($params['goods_img_new'])) {
            $where = array(
             'ic_code' => $ic_code,
            );
            $icItemData['goods_img_new'] = $params['goods_img_new'];
            $icItemData['goods_img']     = $params['goods_img'];
            $icItemData['update_time']   = NOW_TIME; 
            $update_res = D('IcItem')->where($where)->save($icItemData);
            
            if ($update_res <= 0 || $update_res === FALSE) {
                return $this->res(null, 4510);
            }
        }
       
        

        return $this->res($update_res);
    }

    /**
     * 
     * 商家商品列表
     * Base.StoreModule.Item.Item.storeItems
     * @param type $params
     */
    public function storeItems($params) {
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('category_end_id', 'require', PARAMS_ERROR, ISSET_CHECK), //分类id
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK), //品牌
            array('category_front_id', 'require', PARAMS_ERROR, ISSET_CHECK), //前台分类
            array('warn_stock', 'require', PARAMS_ERROR, ISSET_CHECK), //预警库存
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, ISSET_CHECK, 'in'), //商品状态
            array('is_publish', 'require', PARAMS_ERROR, ISSET_CHECK), //商品状态
            array('sic_codes', 'require', PARAMS_ERROR, ISSET_CHECK,'function'), //商品id
            array('sic_nos', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //商品id
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('stock', 'require', PARAMS_ERROR,ISSET_CHECK), //店铺编码
            array('stock_gt', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), //库存是否要大于0
            array('is_page', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), //是否分页
            array('stock_min_num', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), # 库存是否要大于起购数
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      # 分页数
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code           = $params['sc_code'];
        $category_end_id   = $params['category_end_id'];
        $brand             = $params['brand'];
        $goods_name        = $params['goods_name'];
        $status            = $params['status'];
        $category_front_id = $params['category_front_id'];
        $sic_codes         = $params['sic_codes'];
        $sic_no            = $params['sic_no']; //兼容店铺管理的搜索
        $stock_gt          = $params['stock_gt'];
        $warn_stock        = $params['warn_stock'];
        $ceid              = '';
        $stock             = $params['stock'];
        $sic_nos           = $params['sic_nos'];
        $is_page           = empty($params['is_page']) ? 'YES' : $params['is_page'];
        $is_publish        = empty($params['is_publish']) ? 'YES' : $params['is_publish'];
        $stock_min_num     = $params['stock_min_num'];
        $page_number       = $params['page_number'];
        if (isset($params['category_front_id']) && !empty($category_front_id)) {
            //如果传入前台分类  则查询对应的后台分类id
            $ceid = $this->getCategoryEndIds($category_front_id);
            $ceid = implode(',', $ceid);
        }
        if (!is_array($params['category_end_id'])) {
            $params['category_end_id'] = array($params['category_end_id']);
        }
        if ($params['sic_codes']) {
            $params['sic_codes'] = array_filter($params['sic_codes']);
        }
        $order = 'isi.id desc';
        $fields = "isi.sic_code,isi.warn_stock,isi.sic_no,ii.category_end_id,isi.sub_name,ii.ic_code,ii.bar_code,ii.goods_img,ii.goods_name,ii.spec,ii.packing,ii.brand, isi.sc_code,isi.sub_name AS store_sub_name,isi.price,ii.goods_img_new,"
                . "isi.min_num,isi.stock,isi.is_standard,isi.status AS store_status,ii.status as status";
        //必须的where条件
        $where = array(
           // 'ii.status' => IC_ITEM_PUBLISH,
            'isi.sc_code' => $sc_code,
        );

        if($is_publish == 'YES'){
            $where['ii.status'] = IC_ITEM_PUBLISH;
        }
        $sysName = $params['sysName'] ? $params['sysName'] : $this->_request_sys_name;

        !empty($stock) ? $where['isi.stock']=array('elt',0):null;
        !empty($category_end_id) && $where['ii.category_end_id'] = array('in', $params['category_end_id']);
        !empty($brand) && $where['ii.brand'] = $brand;
        !empty($goods_name) && $map['ii.goods_name'] = array('like', "%{$goods_name}%");
        !empty($goods_name) && $map['isi.sub_name'] = array('like', "%{$goods_name}%");
        $sysName != BOSS && !empty($goods_name) && $map['ii.brand'] = array('like', "%{$goods_name}%");
        $sysName != BOSS && !empty($goods_name) && $map['ii.spec'] = array('like', "%{$goods_name}%");
        !empty($goods_name) && $map['_logic']   = 'or';
        !empty($goods_name) && $where['_complex'] = $map;

        //店铺管理搜索兼容
        $sysName != BOSS && !empty($sic_no) && $where['isi.sic_no'] = array('like', "%{$sic_no}%");

        !empty($status) && $where['isi.status'] = $status;
        !empty($ceid)   && $where['ii.category_end_id'] = array('in',$ceid);
        !empty($sic_codes)   && $where['isi.sic_code'] = array('in',$sic_codes);
        !empty($sic_nos)   && $where['isi.sic_no'] = array('in',$sic_nos);
        !empty($warn_stock) && $where['_string'] = "isi.stock <= isi.warn_stock";
        $stock_gt == 'YES' ? $where['isi.stock'] = array('gt',0) : '';
        if($stock_min_num == 'YES'){                                 # 库存是否大于最小起够数
            $where['isi.stock'] = array('exp','> isi.min_num');
        }

        if($is_page == 'NO'){
            //不分页则直接查
            $item_info = D('IcItem')->alias('ii')
                                    ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
                                    ->join("{$this->tablePrefix}category_end ce ON ii.category_end_id = ce.id",'LEFT')
                                    ->where($where)
                                    ->order($order)
                                    ->field($fields)
                                    ->select();                     
             return $this->res($item_info);
        }
        
        if ($params['warn_stock_sign']) {
            $apiPath = "Base.ItemModule.Item.Item.warnStockCount";
            $warData = array(
                'sc_code' => $params['sc_code'],
                );
            $warnItemCount = $this->invoke($apiPath, $warData);
        } 
         
        $params['order'] = $order; //排序
        $params['where'] = $where; //where条件
        $params['fields'] = $fields; //查询字段
        $params['center_flag'] = SQL_SC; //店铺中心   
        $params['sql_flag'] = 'store_items';  //sql标示
        $params['page_number'] = empty($page_number) ? 20 : $page_number;

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if ($list_res['status'] != 0) {
            return $this->res(null, $list_res['status']);
        }
        $list_res['response']['warn_num'] = $warnItemCount;
        return $this->res($list_res['response']);
    }

    

    /**
     * 
     * 修改商品状态  支持批量修改 
     * Base.StoreModule.Item.Item.setStatus
     * @param type $params
     */
    public function setStatus($params) {
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

        // if ($status == IC_STORE_ITEM_OFF) {
            // $fields = "sg.sale_sic_code, sg.gift_sic_code";
            // $where['sg.sale_sic_code'] = array('in', $sic_codes);
            // $where['sg.gift_sic_code'] = array('in', $sic_codes);

            // $where['_logic']           = 'or';
            // $map['sl.sc_code']         = array('EQ', $params['sc_code']);
            // $map['sl.status']          = 'PUBLISH';
            // $map['sg.status']          = array('EQ', 'ENABLE');
            // $map['sl.end_time']        = array('gt', NOW_TIME);
            // $map['_complex']           = $where;

            // $select_res = D('SpcList')->alias('sl')
            //                          ->join("{$this->tablePrefix}spc_gift sg on sl.spc_code = sg.spc_code", 'LEFT')
            //                          ->field("{$fields}")
            //                          ->where($map)
            //                          ->select();

            // $field_ss = "ss.sic_code";
            // $where['sg.sale_sic_code'] = array('in', $sic_codes);
            // $where['sg.gift_sic_code'] = array('in', $sic_codes);

            // $where['_logic']           = 'or';
            // $map['sl.sc_code']         = array('EQ', $params['sc_code']);
            // $map['sl.status']          = 'PU
            // BLISH';
            // $map['sg.status']          = array('EQ', 'ENABLE');
            // $map['sl.end_time']        = array('gt', NOW_TIME);
            // $map['_complex']           = $where;
            // $special  = D('SpcList')->alias('sl')
            //                         ->join("{$this->tablePrefix}spc_special ss sl.spc_code = sg.spc_code", 'LEFT')
            //                         ->field($field_ss)
            //                         ->where($map)
            //                         ->select();
                                     
            // 过滤上架商品（在促销中，为赠品的和促销品）
            // if (!empty($select_res)) {
            //     $new_sic_codes = array();
            //     foreach ($select_res as $value) {
            //         if (!empty($value['sale_sic_code'])) {
            //            $new_sic_codes[] = $value['sale_sic_code'];
            //         }
            //         if (!empty($value['gift_sic_code'])) {
            //             $new_sic_codes[] = $value['gift_sic_code'];
            //         }
            //     }

            //     $new_sic_codes = array_unique($new_sic_codes);
            //     foreach ($sic_codes as $k => $sic_code) {
            //         if (in_array($sic_code, $new_sic_codes)) {
            //             unset($sic_codes[$k]);
            //         }
            //     }
            // }

        // }

        // if (empty($sic_codes)) {
        //     return $this->res(null, 7038);
        // }
        var_dump($params);die();
        $where = array(
            'sic_code' => array('in', $sic_codes),
            'sc_code'  => $params['sc_code'],
        );
        
        $update_res = D('IcStoreItem')->where($where)->save($data);
        
        if ($update_res === FALSE || $update_res <= 0) {
            return $this->res(null, 4512);
        }

        return $this->res($update_res);
    }

    private function getCategoryEndIds($category_front_id) {
        $apiPath = "Base.ItemModule.Category.Category.getCeidByCfid";
        $data = array('category_front_id' => $category_front_id);
        $category_info = $this->invoke($apiPath, $data);
        if ($category_info['status'] != 0) {
            return $this->endInvoke(NULL,$category_info['status'],'',$category_info['message']);
        }
        return $category_info['response'];
    }
    /**
     * 
     * Base.StoreModule.Item.Item.check
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function check($params){
        $this->rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //店铺编码
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //前台分类
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
            array('spec', 'require', PARAMS_ERROR, ISSET_CHECK), //规格
            );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //判断编码是否存在
        $where['sc_code'] = array('EQ', $params['sc_code']);;
        $where['_string'] = "(isi.sic_no = '{$params['sic_no']}')";
        $checkParams = D('IcItem')  ->alias('ii')
                                    ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
                                    ->where($where)
                                    ->select();
        if ($checkParams) {
            return $this->res($params, 4524);
        }

        //判断商品是否存在
        $where['sc_code'] = array('EQ', $params['sc_code']);;
        $where['_string'] = "( ii.goods_name = '{$params['goods_name']}' and ii.spec = '{$params['spec']}')";
        $checkParams = D('IcItem')  ->alias('ii')
                                    ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
                                    ->where($where)
                                    ->select();
        if ($checkParams) {
            return $this->res($params,4522);
        }
        //可以添加商品验证
        return $this->res(true);
    }




    /**
     * @api  Boss版商品更改状态接口
     * @apiVersion 1.0.0
     * @apiName Base.StoreModule.Item.Item.update
     * @apiTransaction N
     * @apiAuthor Todor <nielei@liangrenwang.com>
     * @apiDate 2015-10-20
     * @apiSampleRequest On
     */

    public function update($params){
        
        $this->rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),                                        # 商家编码
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK),                                       # 商品编码
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, ISSET_CHECK, 'in'), # 商品状态
            array('stock', 'require', PARAMS_ERROR,ISSET_CHECK),                                          # 商品库存
            array('price', 'require', PARAMS_ERROR,ISSET_CHECK),                                          # 商品价钱
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }


        $sic_code = $params['sic_code'];
        $sc_code  = $params['sc_code'];

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

        // 库存判断
        if (isset($params['stock']) && !empty($params['stock'])) {

            $params['stock']      = abs(intval($params['stock']));

            if ($params['stock'] < $ic_store_item['min_num']) {
                return $this->res(NULL, 4527);
            }       

            if ($ic_store_item['stock'] <= 0) {
                $params['stock'] = $params['stock'] + $ic_store_item['stock'];
            }
        }

        !empty($params['status']) && $data['status'] = $params['status'];
        isset($params['stock'])  && $data['stock']  = $params['stock'];
        !empty($params['price'])  && $data['price']  = $params['price'];
        $data['update_time'] = NOW_TIME;
        $res = D('IcStoreItem')->where(array('sic_code' => $sic_code, 'sc_code' => $sc_code))->save($data);

        if($res <= 0 || $res === FALSE){
            return $this->res(NULL,4503);
        }
        return $this->res($res);

    }



}

?>
