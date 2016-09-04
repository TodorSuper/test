<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangren.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangren.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangren.com >
 * +---------------------------------------------------------------------
 * | 店铺商品相关模块
 */

namespace Base\StoreModule\Item;

use System\Base;

class ItemExport extends Base {

	public function __construct()
    {
        parent::__construct();
    }

    /**
     * 
     * Base.StoreModule.Item.ItemExport.export
     * @return [type] [description]
     */
    public function export($params){
		
		$data['fields'] =  "ss.sc_code,ss.name,count(*) AS total, sum(case isi.status when 'ON' then 1 else 0 end) as totalON,sum(case isi.status when 'OFF' then 1 else 0 end) as totalOFF";
		$data['filename'] = '商品统计';
		$data['sql_flag'] = "store_item_export";
		$data['title'] = array('序号', '商家名称', '商品总数', '上架数', '下架数');
		if ($params['sc_code']) {
			$data['where'] = array('isi.sc_code' => $params['sc_code']);
		}
		
		//自装 where
		$data['group']       = "isi.sc_code";
		$data['center_flag'] = "Sc";

		$apiPath                = "Com.Common.CommonView.Export.export";
		$list_res               = $this->invoke($apiPath, $data);
		return $this->res($list_res['response'],$list_res['status'],'',$list_res['message']);
    }
    public function cmsExport($params) {

    	$data['fields'] =  "sc.name,from_unixtime(isi.create_time) as create_time,isi.sic_no,ii.goods_name,ii.spec,ii.packing,ii.brand,ii.sub_name,isi.price,isi.stock,isi.warn_stock,isi.min_num,isi.status";
		$data['filename'] = 'POP商品列表';
		$data['sql_flag'] = "cms_item_export";
		$data['title'] = array('卖家店铺', '创建时间', '商品编码', '商品名称', '规格','包装单位','品牌','商品简介','单价(元)','库存','库存预警','起购数量','商品状态');
		$data['uc_code'] = $params['uc_code'];

		if ( !empty($params['sc_code']) ) $data['where'][] = array('isi.sc_code' => $params['sc_code']);
		if ( !empty($params['brand']) ) {
			$brand_like = $params['brand'];
			$data['where'][] = array('ii.brand' =>array('like',"%$brand_like%"));
		}
		if ( !empty($params['sic_no']) ) $data['where'][] = array('isi.sic_no' => $params['sic_no']);
		if ( !empty($params['goods_name']) ) $data['where'][] = array('ii.goods_name' => $params['goods_name']);
		if ( !empty($params['status']) ) $data['where'][] = array('isi.status' => $params['status']);
		if ( !empty($params['class_id']) ) $data['where'][] = array('ii.class_id' => $params['class_id']);
		$data['where'][] = array('sc.sc_code'=>array('neq','1010000000077'));
		//自装 where
		$data['center_flag'] = "Sc";
		$data['order'] = 'isi.create_time desc';
		$apiPath                = "Com.Common.CommonView.Export.export";
		$callback_api = 'Com.Callback.Export.ScExport.cmsEport';
		$data['callback_api'] = $callback_api;
		$list_res               = $this->invoke($apiPath, $data);
		return $this->res($list_res['response'],$list_res['status'],'',$list_res['message']);
    }
}