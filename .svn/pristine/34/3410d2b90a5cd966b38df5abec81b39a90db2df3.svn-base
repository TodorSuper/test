<?php
/**
 * Created by PhpStorm.
 * User: wangguangjian
 * Date: 15/11/10
 * Time: 下午19:09
 */

namespace Com\Crontab\Fc;

use System\Base;


class PayOrderSync extends Base
{
    /**
     * @api {post} 定时修改昨天的已经满足使用先锋支付用户的使用权限
     * @apiPath Com.Crontab.Fc.PayOrderSync.syncRuleUpdate
     * @apiAuthor wangguangjian <wangguangjian@liangrenwang.com>
     * @apiVersion 1.0.0
     * @apiDate 2015/12/14
     * @thinking  取出今天已经满足使用先锋条件的数据，并修改用户的操作权限
     *
     */
    public function syncRuleUpdate($params) {
        empty($params['time']) ? $Yesterday = date("Y-m-d",strtotime("-1 day")) : $Yesterday = $params['time'];

        # 获取各种时间
        $timeYesterday = strtotime($Yesterday);
        $timeToday = strtotime(date("Y-m-d H:i:s",$timeYesterday + 86399));

        #获取已经获取权限的uc_code
        $fcActionLog = D('FcActionLog');
        $whereData = array(
            'pay_privs' => FC_PAY_PRIVS_YES,
        );
        empty($params['all']) && $whereData['update_time'] =array('BETWEEN', [$timeYesterday, $timeToday]);

        $logData = $fcActionLog->field('uc_code,pay_privs,num,update_time')->where($whereData)->select();
        $uc_code = '';
        foreach($logData as $val){
            $uc_code .= $val['uc_code'].",";
        }
        if(empty($uc_code)) $this->res(null,8105);
        $uc_code = rtrim($uc_code,",");
        empty($params['uc_code']) ? '' : $uc_code = $params['uc_code'];
        $where[] = "uc_code in ({$uc_code})";
        $where['pay_privs'] = FC_PAY_PRIVS_NO;
        # 修改操作权限
        $user = D("UcMember");
        $user->pay_privs = FC_PAY_PRIVS_YES;
        $update = $user->where($where)->save();
        if(!$update){
            return $this->res(null,8100);//需要添加错误码
        }
        return $this->res(true);
    }
    /**
     * @api {post} 定时修改昨天的已经满足使用先锋支付用户的使用权限
     * @apiPath Com.Crontab.Fc.PayOrderSync.getUcCodeInsertAction
     * @apiAuthor wangguangjian <wangguangjian@liangrenwang.com>
     * @apiVersion 1.0.0
     * @apiDate 2015/12/19
     * @thinking  取出今天已经满足使用先锋条件的数据，并修改用户的操作权限
     *
     */
    public function getUcCodeInsertAction($params) {
        set_time_limit(0);
        //取出所有符合条件用户编码
        $sql = "SELECT
                 b2b.uc_code,b2b.pay_time
                FROM
                `16860_fc_order_confirm` AS com
                LEFT JOIN `16860_oc_b2b_order` AS b2b ON com.b2b_code = b2b.b2b_code
                LEFT JOIN `16860_uc_member` AS uc ON uc.uc_code = b2b.uc_code
                WHERE
                b2b.real_amount > 1000 AND com.status>1 AND uc.pay_privs='NO' and b2b.pay_status='PAY' GROUP BY uc.uc_code  order by b2b.pay_time ASC ;";
        $account_codes = D()->query($sql);
        $data = [];

        //根据用户编码存入时间
        foreach($account_codes as $val) {
            $data[$val['uc_code']] = $account_codes = D()->query("SELECT
                  b2b.pay_time
                FROM
                `16860_fc_order_confirm` AS com
                LEFT JOIN `16860_oc_b2b_order` AS b2b ON com.b2b_code = b2b.b2b_code
                WHERE
                b2b.uc_code={$val['uc_code']} and b2b.real_amount > 1000 AND com.status>1  and b2b.pay_status='PAY'  order by b2b.pay_time ASC ;");
        }


        //批量修改数据
        foreach($data as $key=>$item) {


            foreach($item as $v) {
                $logData = D("FcActionLog")->field('uc_code,pay_privs,num,update_time')->where("uc_code = {$key}")->find();
                $ucCodeWhere = array(
                    'uc_code'=> $key,
                );
                //判断用户是否已经在权限表添加数据
                if(empty($logData)){
                    $addData = array(
                        'uc_code' => $ucCodeWhere['uc_code'],
                        'create_time' => $v['pay_time'],
                        'update_time' => $v['pay_time'],
                        'pay_method_type' => FC_TYPE_UCPAY,
                        'num' => '1',
                    );
                    $add = D("FcActionLog")->add($addData);
                    if(!$add) return $this->res(null,8097);
                }else{
                    //判断当天是否已经添加过 防止一天内重复添加
                    if(strtotime(date("Y-m-d", $v['pay_time'])) > strtotime(date("Y-m-d",$logData['update_time']))){
                        $upNum = array(
                            'num' => bcadd($logData['num'], C("UC_PAY_DATA")['uc_pay_number']),
                            'update_time' => $v['pay_time'],
                        );

                        if($upNum['num'] == 5 ){

                            $upNum['pay_privs'] = FC_PAY_PRIVS_YES;
                        }
                        $update = D("FcActionLog")->where($ucCodeWhere)->data($upNum)->save();
                    }
                }

            }
        }

        return $this->res(true);

    }
}