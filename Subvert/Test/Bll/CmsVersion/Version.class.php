<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | b2b注册模块
 */

namespace Test\Bll\CmsVersion;
use System\Base;

class Version extends Base {


    /**
     * 补丁添加
     * Test.Bll.CmsVersion.Version.addPatch
     */
    public function addPatch($params){
        // $apiPath = "Bll.Cms.Version.Version.addPatch";
        // $apiPath = "Bll.Cms.Version.Version.editPatch";
        // $apiPath = "Bll.Cms.Version.Version.patchLists";
        $apiPath = "Bll.Cms.Version.Version.getPatch";
        $params = array(
            'id'=>'1',
            // 'patch_version'=>'111',
            // 'patch_url'=>'111',
            // 'content'=>'111',
            // 'device'=>'IOS',
            // 'type'=>'APP',
            // 'version_ids'=>array('12','1'),
            );
        $res = $this->invoke($apiPath,$params);
        return $this->endInvoke($res['response']);
    }
}

?>
