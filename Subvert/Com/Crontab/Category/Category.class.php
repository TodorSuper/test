<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 创建表扫描类的api调度器
 */

namespace Com\Crontab\Category;
use       System\Base;

class Category extends Base{

	private $_rule = array();

    public function __construct() {
		parent::__construct();
    }


    /**
	 * 定时任务执行改变栏目数量
	 * Com.Crontab.Category.Category.index
	 * @access public
	 * @return void
	 */

    public function index(){

        $data_end = D('IcItem')->field('ce.id,count(*) as item_num')                          #后台栏目增加
        				   ->alias('ii')
        				   ->join("{$this->tablePrefix}category_end ce ON ce.id = ii.category_end_id",'LEFT')
        				   ->where(array('ce.level'=>3))
        				   ->group('ii.category_end_id')
        				   ->select();
        foreach ($data_end as $k => $v) {
        	$where['id']      = $v['id'];
        	$temp['item_num'] = $v['item_num']; 
        	D('CategoryEnd')->where($where)->save($temp);
        }

        $data_front = D('CategoryEnd')->field('cr.cfid as id,sum(ce.item_num) as item_num')   #前台栏目增加
        				       ->alias('ce')
					           ->join("{$this->tablePrefix}category_relationship cr ON ce.id = cr.ceid",'LEFT')
					           ->where(array('ce.level'=>3))
					           ->group('cr.cfid')
					           ->select();

		foreach ($data_front as $k => $v) {
			if(!empty($v['id'])){
				$where['id']             = $v['id'];
				$temp_front['item_num']  = $v['item_num'];
				D('CategoryFront')->where($where)->save($temp_front);
			}
		}        
    	
    }




}












 ?>