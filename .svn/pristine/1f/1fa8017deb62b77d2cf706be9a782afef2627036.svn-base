<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 商品相关模块
 */

namespace Base\ItemModule\Item;

use System\Base;

class ItemInfo extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }

    /**
     * Base.ItemModule.Item.ItemInfo.getStoreItem
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getStoreItem($params){
        $this->_rule = array(
            array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家商品编码
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK), //商家商品编码
            array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, HAVEING_CHECK, 'in'), //商家商品状态
            array('need_tag',array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'),    # 是否需要标签
            array('need_category', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK , 'in'), # 是否需要栏目
            array('stock_gt', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), # 库存是否大于最小起够数
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            L($this->getErrorField(), $this->getCheckError());
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_code = $params['sic_code'];
        $status   = $params['status'];
        $sc_code  = $params['sc_code'];
        $stock_gt = $params['stock_gt'];
        $where = array(
            'ii.status' => IC_ITEM_PUBLISH,
            'isi.sic_code' => $sic_code
        );
        !empty($status) && $where['isi.status'] = $status;
        !empty($sc_code) && $where['isi.sc_code'] = $sc_code;
        !empty($stock_gt) && $stock_gt == "YES" && $where['isi.stock'] = array('exp','>= isi.min_num');
 
        $storeItem = D('IcItem')->alias('ii')
                        ->join("{$this->tablePrefix}ic_store_item isi on ii.ic_code = isi.ic_code", 'LEFT')
                        ->field('ii.*,isi.sc_code,isi.sic_code,isi.sub_name as store_sub_name,isi.price,isi.min_num,isi.sic_no,isi.stock,isi.warn_stock,isi.is_standard,isi.status as store_status,isi.qrcode')
                        ->where($where)->find();

        if (empty($storeItem)) {
            return $this->res(null, 4509);
        }

        //图片  解析
        $storeItem['goods_img_new'] = str_replace('&quot;', '"', $storeItem['goods_img_new']);
        $storeItem['goods_img_new'] = json_decode($storeItem['goods_img_new'], true);

        //解析描述  保存的时候嵌入了延迟加载的代码
        $storeItem['goods_desc'] = show_lazy_load($storeItem['goods_desc']);

        // 需要标签
        if(isset($params['need_tag']) && $params['need_tag'] == "YES" ){
            $map['iitr.sic_code'] = $sic_code;
            $map['iitr.status'] = 'ENABLE';
            $tags = D('IcTag')->alias('it')
                              ->join("{$this->tablePrefix}ic_item_tag_relation AS iitr ON it.id = iitr.tag_id")
                              ->field('it.id,it.tag_name,it.tag_img,tag_weight,item_num')
                              ->where($map)
                              ->order('it.tag_weight')
                              ->select();
            $storeItem['tags'] = $tags;
        }
        

        // 需要栏目
        if(isset($params['need_category']) && $params['need_category'] == "YES" ){
            $map = array();
            $map['iicr.sic_code'] = $sic_code;
            $map['iicr.status'] = 'ENABLE';
            $categorys = D('IcCategory')->alias('ic')
                              ->join("{$this->tablePrefix}ic_item_category_relation AS iicr ON ic.id = iicr.category_id")
                              ->field('ic.id,ic.category_name,item_num,category_order')
                              ->where($map)
                              ->order('ic.category_order')
                              ->select();
            $storeItem['categorys'] = $categorys;
        }
           

        return $this->res($storeItem);
    }



    /*
     * Base.ItemModule.Item.ItemInfo.storeItems
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function storeItems($params){

    	$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //店铺编码
    	    array('sc_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码多个
    	    array('category_end_id', 'require', PARAMS_ERROR, ISSET_CHECK), //分类id
    	    array('brand', 'require', PARAMS_ERROR, ISSET_CHECK), //品牌
    	    array('tagId', 'require', PARAMS_ERROR, ISSET_CHECK), //标签id
    	    array('categoryId', 'require', PARAMS_ERROR, ISSET_CHECK), //分类id
    	    array('warn_stock', 'require', PARAMS_ERROR, ISSET_CHECK), //预警库存
    	    array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
    	    array('status', array(IC_STORE_ITEM_OFF, IC_STORE_ITEM_ON), PARAMS_ERROR, ISSET_CHECK, 'in'), //商品状态
    	    array('is_publish', 'require', PARAMS_ERROR, ISSET_CHECK), //商品状态
    	    array('sic_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //商品id
    	    array('sic_nos', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //商品id
    	    array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
    	    array('stock', 'require', PARAMS_ERROR,ISSET_CHECK), //店铺编码
    	    array('stock_gt', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), //库存是否要大于0
    	    array('is_page', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), //是否分页
    	    array('stock_min_num', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), # 库存是否要大于起购数
    	    array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),   # 分页数
            array('class_id', 'require', PARAMS_ERROR, ISSET_CHECK),      # 平台分类
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK),      # sql标识
            array('invite_code','require',PARAMS_ERROR, ISSET_CHECK),     # 邀请码
    	);

    	if (!$this->checkInput($this->_rule, $params)) { # 自动校验
    	    return $this->res($this->getErrorField(), $this->getCheckError());
        }
		$sc_code       = $params['sc_code'];
		$brand         = $params['brand'];
		$goods_name    = $params['goods_name'];
		$status        = $params['status'];
		$sic_codes     = $params['sic_codes'];
		$sic_no        = $params['sic_no']; //兼容店铺管理的搜索
		$stock_gt      = $params['stock_gt'];
		$warn_stock    = $params['warn_stock'];
		$stock         = $params['stock'];
		$sic_nos       = $params['sic_nos'];
		$is_page       = empty($params['is_page']) ? 'YES' : $params['is_page'];
		$is_publish    = empty($params['is_publish']) ? 'YES' : $params['is_publish'];
		$stock_min_num = $params['stock_min_num'];
		$page_number   = $params['page_number'];
		$tagId         = $params['tagId'];
		$categoryId    = $params['categoryId'];
        $sc_codes      = $params['sc_codes'];
        $sql_flag      = $params['sql_flag'];
		$class_id      = $params['class_id'];

    	if (!empty($tagId)) {
    		$tagWhere = array();
    		$tagWhere['sc_code'] = $sc_code;
    		$tagWhere['tag_id'] = array('in', array($tagId));
    		$tagWhere['status'] = 'ENABLE';
    		$tagRes = D('IcItemTagRelation')->where($tagWhere)->select();
    		if ($tagRes === false) {
    			return $this->res(null, 4535);
    		}
    		if (!empty($tagRes)) {
    			$relation_sic_codes = array_unique(array_column($tagRes, 'sic_code'));
    		}else{
                $relation_sic_codes = array('8888888888888888'); //用于兼容设置
            }
    	}
    	if (!empty($categoryId)) {
    		$categoryWhere = array();
    		$categoryWhere['sc_code'] = $sc_code;
    		$categoryWhere['category_id'] = array('in', array($categoryId));
    		$categoryWhere['status'] = 'ENABLE';
    		$categoryRes = D('IcItemCategoryRelation')->where($categoryWhere)->select();
    		if ($categoryRes === false) {
    			return $this->res(null, 4541);
    		}

    		if (!empty($categoryRes)) {
    			$category_sic_codes = array_unique(array_column($categoryRes, 'sic_code'));
                if(!empty($relation_sic_codes)){
                    $relation_sic_codes = array_intersect($category_sic_codes, $relation_sic_codes);
                }else{
                    $relation_sic_codes = $category_sic_codes;
                }
    		}else{
                $relation_sic_codes = array('999999999999999'); //用于兼容设置值
            }
    	}

        // 邀请码判断
        if(($params['invite_code'] != C('TEXT_INVITE_COE')) && (strlen($params['invite_code']) == 4)){
            $where['isi.sc_code'] = array('neq',C('LIANGREN_SC_CODE'));
        }

        if ( $this->_request_sys_name == CMS || $this->_request_sys_name == POP) {
            $order = 'isi.create_time desc';
        }else {
            $order = 'isi.sort desc, isi.create_time desc';
        }
    	
    	$fields = "isi.sic_code,isi.warn_stock,isi.sic_no,ii.category_end_id,isi.sub_name,ii.ic_code,ii.bar_code,ii.goods_img,ii.goods_name,ii.spec,ii.packing,ii.brand, isi.sc_code,isi.sub_name AS store_sub_name,isi.price,ii.goods_img_new,"
    	        . "isi.min_num,isi.stock,isi.is_standard,isi.sort,isi.tag_ids,isi.qrcode,isi.status AS store_status,ii.status as status,isi.create_time,ii.class_id,isi.sc_code";
    	if (!empty($sic_codes)) {
    		if (!empty($relation_sic_codes)) {
    			$sic_codes = array_merge($sic_codes, $relation_sic_codes);
    		}
    	}else{
    		if (!empty($relation_sic_codes)) {
    			$sic_codes = $relation_sic_codes;
    		}
    	}

    	//必须的where条件

        !empty($sc_codes) && $where['isi.sc_code'] = array('in',$sc_codes);
        !empty($sc_code) && $where['isi.sc_code'] = $sc_code;

        
    	if($is_publish == 'YES'){
    	    $where['ii.status'] = IC_ITEM_PUBLISH;
    	}
    	$sysName = $params['sysName'] ? $params['sysName'] : $this->_request_sys_name;

    	!empty($stock) ? $where['isi.stock']=array('elt',0):null;
    	!empty($brand) && $where['ii.brand'] = array('like',"%$brand%");
    	!empty($goods_name) && $map['ii.goods_name'] = array('like', "%{$goods_name}%");
    	!empty($goods_name) && $map['isi.sub_name'] = array('like', "%{$goods_name}%");
		!empty($class_id) &&  $where['ii.class_id']  = array('eq',$class_id);
    	$sysName == B2B && !empty($goods_name) && $map['ii.brand'] = array('like', "%{$goods_name}%");
        $sysName == BOSS && !empty($goods_name) && $map['isi.sic_no'] = array('like', "%{$goods_name}%");
        if ($this->_request_sys_name == CMS) {
            unset($map['isi.sub_name']);
        }
    	!empty($goods_name) && $map['_logic']   = 'or';
    	!empty($goods_name) && $where['_complex'] = $map;



    	//店铺管理搜索兼容
    	 !empty($sic_no) && $where['isi.sic_no'] = array('like', "%{$sic_no}%");

    	!empty($status) && $where['isi.status'] = $status;
    	!empty($sic_codes)   && $where['isi.sic_code'] = array('in',$sic_codes);
    	!empty($sic_nos)   && $where['isi.sic_no'] = array('in',$sic_nos);
    	!empty($warn_stock) && $where['_string'] = "isi.stock <= isi.warn_stock";
    	$stock_gt == 'YES' ? $where['isi.stock'] = array('gt',0) : '';
    	if($stock_min_num == 'YES'){                                 # 库存是否大于最小起够数
    	    $where['isi.stock'] = array('exp','>= isi.min_num');
    	}

    	if($is_page == 'NO'){
    	    //不分页则直接查
    	    $item_info = D('IcItem')->alias('ii')
    	                            ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
    	                            ->join("{$this->tablePrefix}ic_item_class cl ON ii.class_id = cl.id",'LEFT')
    	                            ->where($where)
    	                            ->order($order)
    	                            ->field($fields)
    	                            ->select();                     
    	     return $this->res($item_info);
    	}
        
        if(empty($sql_flag)){
            $sql_flag = "store_items";
            $fields .= ",cl.class_name,cl.status";
        }else{
            $sql_flag = $sql_flag;                                       # 用于平台商品列表
            $sql_flag == 'platform_items' && $where['ss.is_show'] = "YES";
        }
		$params['order']       = $order; //排序
		$params['where']       = $where; //where条件
		$params['fields']      = $fields; //查询字段
		$params['center_flag'] = SQL_SC; //店铺中心   
		$params['sql_flag']    = $sql_flag;  //sql标示
		$params['page_number'] = empty($page_number) ? 20 : $page_number;

		$apiPath  = "Com.Common.CommonView.Lists.Lists";

		$list_res = $this->invoke($apiPath, $params);

    	if ($list_res['status'] != 0) {
    	    return $this->res(null, $list_res['status']);
    	}

    	return $this->res($list_res['response']);
    }

    /**
     * Base.ItemModule.Item.ItemInfo.searchConItems
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
            array('brand', 'require', PARAMS_ERROR, ISSET_CHECK),

        );
        if (!$this->checkInput($this->_rule, $params)) { 
          return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $fields = "ii.goods_name, isi.sic_code, isi.sic_no, ii.brand, ii.spec, ii.packing, isi.sub_name, isi.price, isi.stock";
        $limit  = empty($params['limit']) ? '': $params['limit']; //搜索条数

        !empty($params['sic_no']) && $where['isi.sic_no'] = array('like', "%{$params['sic_no']}%");  //匹配商家商品编码
        !empty($params['goods_name']) && $where['ii.goods_name'] = array('like', "%{$params['goods_name']}%"); //匹配商品名称
        $where['isi.sc_code'] = array('EQ', $params['sc_code']);  //店铺编码
        $where['ii.status']   = array('EQ', 'PUBLISH');  //过滤标准库中的ic_item status不等于PUBLISH
        
        if (!$params['search_sign']) {
            $where['isi.status'] = array('EQ', IC_STORE_ITEM_ON);  // 商家商品必须是上家的
        }

        $select_res = D('IcItem')->alias('ii')
                        ->join("{$this->tablePrefix}ic_store_item isi on ii.ic_code = isi.ic_code",'LEFT')
                        ->field("{$fields}")
                        ->where($where)
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
     * 检查sic_no存不存在
     * Base.ItemModule.Item.ItemInfo.check
     * @author Todor
     * @access public
     */

    public function check($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家编码
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //商家商品编码
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
            array('spec', 'require', PARAMS_ERROR, ISSET_CHECK), //规格
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sic_no_where = $item_where = array();

        $sic_no_where['sc_code'] = $params['sc_code'];
        !empty($params['sic_no']) && $sic_no_where['sic_no'] = $params['sic_no'];
        !empty($params['goods_name']) && $item_where['ii.goods_name'] = $params['goods_name'];
        !empty($params['spec']) && $item_where['ii.spec'] = $params['spec'];

        if (!empty($sic_no_where)) {
            $res = D('IcStoreItem')->where($sic_no_where)->find();
            if($res === false){
                return $this->res(null,4509);
            }
            if (!empty($res)) {
                return $this->res($params, 4543);
            }
        }
        
        if (!empty($item_where)) {
            $item_where['isi.sc_code'] = $params['sc_code'];
            $res = D('IcItem')  ->alias('ii')
                                ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
                                ->where($item_where)
                                ->find();
            if($res === false){
                return $this->res(null,4509);
            }
            if (!empty($res)) {
                return $this->res($params, 4522);
            }
        } 

        return $this->res(true);
    }

    /**
     * Base.ItemModule.Item.ItemInfo.checkItemName
     * @return [type] [description]
     */
    public function checkItemName($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商家编码
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK), //商家商品编码
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品名称
            array('spec', 'require', PARAMS_ERROR, ISSET_CHECK), //规格
        );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

         $sic_no_where = $item_where = array();

        $sic_no_where['sc_code'] = $params['sc_code'];
        !empty($params['sic_no']) && $sic_no_where['sic_no'] = $params['sic_no'];
        !empty($params['goods_name']) && $item_where['ii.goods_name'] = $params['goods_name'];
        !empty($params['spec']) && $item_where['ii.spec'] = $params['spec'];
    }



}