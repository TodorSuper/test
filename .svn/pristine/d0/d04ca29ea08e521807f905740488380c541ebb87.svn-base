<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 交易中心 导出  回调接口
 */

namespace Com\CallBack\Export;

use System\Base;

class TcExport extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    public function cashList(&$data){
        foreach($data as $key=>$v){
            if($v['create_time']){
                $data[$key]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
            }
            unset($data[$key]['oc_code']);
            unset($data[$key]['name']);
            unset($data[$key]['phone']);
            unset($data[$key]['linkman']);
            unset($data[$key]['status']);
        }
    }

    public function tradeList(&$data){
          foreach($data as $key=>$v){
              if($v['pay_time']){
                  $data[$key]['pay_time']=date('Y-m-d H:i:s',$v['pay_time']);
              }
          }
    }

    public function CmsCashList(&$data){
         foreach($data as $key=>$v){
             if($v['create_time']){
                 $data[$key]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
             }
           if($v['status']=='PASS'){
               $data[$key]['status']='提现成功';
           }elseif($v['status']=='APPLY'){
               $data[$key]['status']='处理中';
           }else{
               $data[$key]['status']='提现驳回';
           }
             unset($data[$key]['account_name']);
             unset($data[$key]['account_bank']);
             unset($data[$key]['account_no']);
         }
    }
    
       

}

?>
