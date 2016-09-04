<?php
/**
 * Created by PhpStorm.
 * User: wangguangjian
 * Date: 15/11/10
 * Time: ����19:09
 */

namespace Com\Crontab\Fc;

use System\Base;


class PayOrderSync extends Base
{
    /**
     * @api {post} ��ʱ�޸�������Ѿ�����ʹ���ȷ�֧���û���ʹ��Ȩ��
     * @apiPath Com.Crontab.Fc.PayOrderSync.syncRuleUpdate
     * @apiAuthor wangguangjian <wangguangjian@liangrenwang.com>
     * @apiVersion 1.0.0
     * @apiDate 2015/12/14
     * @thinking  ȡ�������Ѿ�����ʹ���ȷ����������ݣ����޸��û��Ĳ���Ȩ��
     *
     */
    public function syncRuleUpdate($params) {
        empty($params['time']) ? $Yesterday = date("Y-m-d",strtotime("-1 day")) : $Yesterday = $params['time'];

        # ��ȡ����ʱ��
        $timeYesterday = strtotime($Yesterday);
        $timeToday = strtotime(date("Y-m-d H:i:s",$timeYesterday + 86399));

        #��ȡ�Ѿ���ȡȨ�޵�uc_code
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
        # �޸Ĳ���Ȩ��
        $user = D("UcMember");
        $user->pay_privs = FC_PAY_PRIVS_YES;
        $update = $user->where($where)->save();
        if(!$update){
            return $this->res(null,8100);//��Ҫ��Ӵ�����
        }
        return $this->res(true);
    }
    /**
     * @api {post} ��ʱ�޸�������Ѿ�����ʹ���ȷ�֧���û���ʹ��Ȩ��
     * @apiPath Com.Crontab.Fc.PayOrderSync.getUcCodeInsertAction
     * @apiAuthor wangguangjian <wangguangjian@liangrenwang.com>
     * @apiVersion 1.0.0
     * @apiDate 2015/12/19
     * @thinking  ȡ�������Ѿ�����ʹ���ȷ����������ݣ����޸��û��Ĳ���Ȩ��
     *
     */
    public function getUcCodeInsertAction($params) {
        set_time_limit(0);
        //ȡ�����з��������û�����
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

        //�����û��������ʱ��
        foreach($account_codes as $val) {
            $data[$val['uc_code']] = $account_codes = D()->query("SELECT
                  b2b.pay_time
                FROM
                `16860_fc_order_confirm` AS com
                LEFT JOIN `16860_oc_b2b_order` AS b2b ON com.b2b_code = b2b.b2b_code
                WHERE
                b2b.uc_code={$val['uc_code']} and b2b.real_amount > 1000 AND com.status>1  and b2b.pay_status='PAY'  order by b2b.pay_time ASC ;");
        }


        //�����޸�����
        foreach($data as $key=>$item) {


            foreach($item as $v) {
                $logData = D("FcActionLog")->field('uc_code,pay_privs,num,update_time')->where("uc_code = {$key}")->find();
                $ucCodeWhere = array(
                    'uc_code'=> $key,
                );
                //�ж��û��Ƿ��Ѿ���Ȩ�ޱ��������
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
                    //�жϵ����Ƿ��Ѿ���ӹ� ��ֹһ�����ظ����
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