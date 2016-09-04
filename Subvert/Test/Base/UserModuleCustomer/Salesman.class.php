<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 促销效果
 */

namespace Test\Base\UserModuleCustomer;

use System\Base;

class Salesman extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }


    /**
     * 批量更新二维码
     * Test.Base.UserModuleCustomer.Salesman.change_qcode
     * @access public
     * @author Todor
     */

    public function change_qcode($params){
//        $data = array(
//            'sc_code'=>'1020000000026',
//            );
//        $where['sc_code'] = $data['sc_code'];

        $salesmans = D('ScSalesman')->where()->select();

        try{
            D()->startTrans();
            foreach ($salesmans as $k => $v) {
                // 生成二维码
                $invite_code = $v['invite_code'];
                $url = C('CHANNEL_QRCODE_URL')."Register/index/type/salesman/invite_code/{$invite_code}";
                $Qrcode = new \Library\qrcodes();
                $qrcode_url = $Qrcode->generateQrcodeByUrl($url);
                if(empty($qrcode_url)){
                    return $this->res(NULL,6707);
                }

                // 上传阿里云
                $img_url  = upload_cloud($qrcode_url);
                if(empty($img_url)){
                    return $this->res(NULL,6708);
                }

                $data = array(
                    'qcode'  => $img_url,
                    'update_time' => NOW_TIME,
                );

                $map['id'] = $v['id'];
                $res = D('ScSalesman')->where($map)->save($data);

                if($res <= 0 || $res === FALSE){
                    return $this->res(NULL,6709);
                }
            }
            $commit_res = D()->commit();
            if($commit_res === FALSE){
                throw new \Exception('事务提交失败',4014);
            }
            return $this->endInvoke('批量更新二维码成功');
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke('批量更新二维码失败');
        }

    }


}








 ?>