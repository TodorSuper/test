<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\PopItem;

use System\Base;
class Tag extends Base {


    public function __construct() {
        parent::__construct();
    }

    /**
     * Test.Bll.PopItem.Tag.addTag
     * @param [type] $params [description]
     */
    public function addTag($params){
        //添加标签（在Base写死）
    	// $params = array(
    	// 	'sc_code' => 1020000000026,
     //        'tag_name' => '欢乐豆'
    	// 	);
    	// echo json_encode($params);
    	// var_dump($params);

	    $apiPath = "Bll.Pop.Item.Tag.addTag";
	    $tagRes = $this->invoke($apiPath, $params);
	    
	    return $this->endInvoke($tagRes['message'], $tagRes['status']);
	    
    	    
    }

    /**
     * Test.Bll.PopItem.Tag.listsTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function listsTag($params){
    	$params = array(
    		'sc_code' => 1020000000026,
    		);
    	$apiPath = "Bll.Pop.Item.Tag.listsTag";
	    $tagRes = $this->invoke($apiPath, $params);
	    
	    return $this->endInvoke($tagRes);
	    
    }

    /**
     * Test.Bll.PopItem.Tag.updateTag
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function updateTag($params){
        $params = array(
            'sc_code' => 1020000000026,
            'data' => array(
                    array(
                        'id' => 17,
                        'tag_weight' => 2,
                        ),
                    array(
                        'id' => 18,
                        'tag_weight' => 1
                        ),
                )
            );
        $apiPath = "Bll.Pop.Item.Tag.updateTag";
        $tagRes = $this->invoke($apiPath, $params);
        
        return $this->endInvoke($tagRes);
    }
}