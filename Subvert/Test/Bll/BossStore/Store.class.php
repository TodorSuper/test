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

namespace Test\Bll\BossStore;

use System\Base;
class Store extends Base {

	private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 店铺信息
     * Test.Bll.BossStore.Store.getmsg
     * @param type $params
     */

    public function getmsg($params){
    	$apiPath = "Bll.Boss.Store.Store.getmsg";
        // $apiPath = "Base.OrderModule.Boss.Order.all";
        $params = array(
            'sc_code' =>'1020000000026',

            );

    	$res =  $this->invoke($apiPath,$params);
    	return $this->endInvoke($res['response']);
    }



    /**
    * 设置首页
    * Test.Bll.BossStore.Store.index
    * @param 
    */

    public function index($params){
        $apiPath = "Bll.Boss.Store.Store.index";
        // $apiPath = "Base.OrderModule.Boss.Order.all";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1120000000103',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }




   /**
    * 初始化
    * Test.Bll.BossStore.Store.init
    * @param 
    */

    public function init($params){
        $apiPath = "Bll.Boss.Store.Store.init";
        // $apiPath = "Bll.Boss.Store.Store.checkUpdate";
        $params = array(
            'version' =>'120',
            'device' => 'IOS',
            'patch_id'=>'1',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }


   /**
    * 检查更新接口
    * Test.Bll.BossStore.Store.checkUpdate
    * @param 
    */

    public function checkUpdate($params){
        $apiPath = "Bll.Boss.Store.Store.checkUpdate";
        // $apiPath = "Base.OrderModule.Boss.Order.all";
        $params = array(
            'version' =>'1.0',
            'device' => '1120000000103',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }


    /**
     * Boss版修改配置
     * Test.Bll.BossStore.Store.set
     * @param type $params
     */

    public function set($params){

        $apiPath = "Bll.Boss.Store.Store.set";
        $params = array(
            'sc_code' =>'1020000000026',
            'uc_code' => '1120000000103',
            'push_msg' => 'YES',
            'prompt_sound' => 'ON',
            'show_img'=>'ON',
            );

        $res =  $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);

    }








}























 ?>