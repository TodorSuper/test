<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 云铺通
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b订单相关测试模块
 */

namespace Test\Bll\B2bItem;

use System\Base;
class Lists extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 展示首页
     * Test.Bll.B2bItem.Lists.getmsg
     * @param type $params
     */

    public function getmsg($params){
        $data = array(
            'version'=>'1.2.0',
            'down_url'=>'itms-services://?action=download-manifest&url=https://appdownload.liangren.com/manifest.plist',
            'update_type'=>'FORCE',
            'device'=>'IOS',
            'show_update'=>'OFF',
            'clear_cache'=>'NO',
            'content'=>'2015年，我们一起见证粮人网从无到有，由有到优。粮人网V1.2.0，增加了订单改价、营业统计、查看每日销售额和订单等功能，优化了页面风格，我们一直在努力，好生意更容易！',
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>'ENABLE',
            );
        $data = D('AppVersion')->add($data);
        L(D()->getLastSql());
    	return $this->endInvoke($res['response']);
    }


    /**
     * 展示首页
     * Test.Bll.B2bItem.Lists.lists
     * @param type $params
     */

    public function lists($params){
        $apiPath = "Bll.B2b.Item.Lists.lists";

        $params = array(
            // 'sc_code'=>'1010000000077',
            'uc_code'=>'1210000000309',
            // 'brand'=>'绿湖',
            // 'category_front_id'=>'784'12200002164
            // 'tagId'=>'17',
            // 'categoryId'=>'6',
            // 'goods_name'=>'酱',
            // 'class_id'=>0,
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }



    /**
     * 判断起够数
     * Test.Bll.B2bItem.Lists.minbuy
     * @param type $params
     */

    public function minbuy($params){
        $apiPath = "Bll.B2b.Item.Lists.minbuy";

        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' =>'1220000000271',
            );
        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }

    /**
     * 促销列表
     * Test.Bll.B2bItem.Lists.spcList
     * @param type $params
     */

    public function spcList($params){
        $apiPath = "Bll.B2b.Item.Lists.spcList";

        $params = array(
            'sc_code'=>'1020000000026',
            'uc_code'=>'1210000000390',
            // 'brand'=>'绿湖',
            // 'category_front_id'=>'784'
            );
        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }
    
    /**
     * Test.Bll.B2bItem.Lists.test
     */
    public function  test($params){
        echo 'ggg4444';
    }


    /**
     * Test.Bll.B2bItem.Lists.get
     */

    public function get($params){
        $params = array(
            'sic_code'=>'12200002090',
            'sc_code'=>'1020000000026',
            'uc_code'=>'1210000000390',
            );
        $apiPath = "Bll.B2b.Item.Lists.get";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response']);
    }


    /**
     *Test.Bll.B2bItem.Lists.platformLists
     *
     */
    public function platformLists($params){
        $params = array(
            'goods_name'=>'酱',
            'class_id'=>0,
            );
        $apiPath = "Bll.B2b.Item.Lists.lists";
        $res = $this->invoke($apiPath, $params);
        return $this->endInvoke($res['response']);
    }



}























 ?>