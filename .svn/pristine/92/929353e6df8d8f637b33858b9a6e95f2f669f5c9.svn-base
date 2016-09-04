<?php
/**
 * Created by PhpStorm.
 * User: Jnnnh
 * Date: 15/9/11
 * Time: 下午2:44
 */

namespace Com\Crontab\Fc;

use       System\Base;


class OrderSchedule extends Base
{

    private $_rule = array();

    public function __construct()
    {

        parent::__construct();
    }
    /**
   	 * @api {post} 同步订单信息到confirm表
     * @apiName Com.Crontab.Fc.OrderSchedule.syncOrderList
     * @apiDescription 调试的时候建议将max_limit设置的尽可能小，防止库内数据不足，无法测试
     * @apiAuthor jiangshao <jiangshao@liangrenwang.com>
     * @apiVersion 1.0.1
     * @apiDate 2015-09-11
     * @apiParam {Integer} [sync_chunk=50] 每次插入数据库的Row的行数
     * @apiParam {Integer} [max_limit=50] 单次API执行同步的最大数量
     * @apiParam {Integer} [timeout=30] API超时时间，单位：秒。超过时间自动终止等待下次继续执行
     * @apiParam {Integer} [retry_count=3] 当API数据入库执行失败时，重试的次数
   	 * @apiSampleRequest On
     *
     *
   	 */
    public function syncOrderList($data)
    {

        $sql = "UPDATE 16860_uc_member um LEFT JOIN 16860_oc_b2b_order obo ON um.uc_code = obo.uc_code SET obo.order_type = 'PLATFORM' WHERE um.invite_from = 'UC'";
        D()->master()->query($sql);
        $data['empty'] = false;
        $startTime = NOW_TIME;
        $params = array(
            "sync_chunk" => 50,
            "max_limit"  => 50,
            "timeout"    => 30,
            "retry_count" => 3
        );
        $data = array_merge($params, $data);

        $filedArr = array(
            'tci.b2b_code',
            'tci.op_code as oc_code',
            'tci.client_name',
            'tci.pay_method',
            'tci.real_amount',
            'tci.sc_code',
            'tci.order_status',
            'tci.pay_status',
            '1 as status',
            'tci.pay_type'
            //            'ss.pay_time',
            //            'ss.price',
            //            'ss.pay_no',
            //            'ss.oc_code',
            //            'sc.name',
        );
        $fileds = join(",", $filedArr);

        //查询上次同步的最后一条数据ID；
        $lastIdSql = "SELECT ocd.id FROM {$this->tablePrefix}fc_order_confirm fci
             LEFT JOIN {$this->tablePrefix}oc_b2b_order ocd ON fci.b2b_code = ocd.b2b_code
             WHERE fci.oc_type='GOODS'
             ORDER BY id DESC LIMIT 1";
        $last = D()->query($lastIdSql);
        $lastId = $last[ 0 ][ "id" ];
        $lastId = !$lastId ? 0 : $lastId;
        //每次同步50条数据

        $sql = "SELECT {$fileds} FROM
                                 {$this->tablePrefix}oc_b2b_order tci
                                   LEFT JOIN {$this->tablePrefix}tc_pay_voucher ss on tci.op_code=ss.oc_code
                                   LEFT JOIN {$this->tablePrefix}sc_store sc on tci.sc_code=sc.sc_code
                                   WHERE   ( tci.id > $lastId ) AND tci.pay_method<>'ADVANCE' LIMIT ".$data[ 'max_limit' ];

        //将超过单次同步最大数量的数据摘除留作下次同步的时候继续同步，可以将次限制放在sql的limit当中
        $tciData = D()->query($sql);

        //如果总共需要同步的数据超过单次同步的数量
        $chunk = array_chunk($tciData, $data[ 'sync_chunk' ]);

        $queryFunction = function ($syncData, $tries = 0) use (&$queryFunction,$data) {

            //尝试将数据插入fc_order_confirm表
            $queryResult = D("fcOrderConfirm")->addAll($syncData);
            //检测重试是否超过三次，超过三次退出
            if ($tries >= $data['retry_count']) {
                return false;
            } else {
                //如果此次插入失败，进入重试程序
                if (!$queryResult) {
                    $tries++;
                    usleep(1);
                    $queryFunction($syncData, $tries);
                } else {
                    //成功返回
                    return true;
                }
            }
        };

        foreach ($chunk as $syncData) {
            $syncData = array_map(function ($element)  {
                $element["create_time"] = NOW_TIME;
                $element["oc_type"] = 'GOODS';

                return $element;
            }, $syncData);
            //将数据分块后插入数据库
            $queryStatus = $queryFunction($syncData);
            $currentTime = time();
            //重试三次失败后，直接退出同步程序；
            if (!$queryStatus) {
                //TODO 添加执行失败的报警，和Log日志
                $this->endInvoke(null,8001,"同步数据失败 retry:".$data['retry_count']);

                break;
            } else {
                if ($currentTime - $startTime > $data[ 'timeout' ]) {
                    //TODO 添加执行失败的报警，和Log日志
                    $this->endInvoke(null,8002,"单次同步时间超时。timeout：".$data["timeout"]);

                    break;
                }
            }
            usleep(1);
        }

        return $this->res();
    }
    public function syncAdvanceList()
    {
        $data['empty'] = false;
        $startTime = NOW_TIME;
        $params = array(
            "sync_chunk" => 50,
            "max_limit" => 50,
            "timeout" => 30,
            "retry_count" => 3
        );
        $data = array_merge($params, $data);

        $filedArr = array(
            'oad.adv_code as b2b_code',
            'oad.op_code as oc_code',
            'oad.client_name',
            'oad.pay_method',
            'oad.sc_code',
            '1 as oc_type',
            '1 as pay_type'
        );
        $fileds = join(",", $filedArr);
        //查询上次同步的最后一条数据ID；

        $lastIdSql = "SELECT oad.id FROM {$this->tablePrefix}fc_order_confirm fci
         LEFT JOIN {$this->tablePrefix}oc_advance oad ON fci.b2b_code = oad.adv_code
         WHERE fci.oc_type='ADVANCE'
         ORDER BY oad.id DESC LIMIT 1";
        $last = D()->query($lastIdSql);
        $lastId = $last[0]["id"];
        $lastId = !$lastId ? 0 : $lastId;
        //每次同步50条数据
        $sql = "SELECT {$fileds} FROM
                             {$this->tablePrefix}oc_advance oad
                               WHERE   ( oad.id > $lastId )  LIMIT " . $data['max_limit'];


        //将超过单次同步最大数量的数据摘除留作下次同步的时候继续同步，可以将次限制放在sql的limit当中
        $oadData = D()->query($sql);

        //如果总共需要同步的数据超过单次同步的数量
        $chunk = array_chunk($oadData, $data['sync_chunk']);

        $queryFunction = function ($syncData, $tries = 0) use (&$queryFunction, $data) {

            //尝试将数据插入fc_order_confirm表
            $queryResult = D("fcOrderConfirm")->addAll($syncData);
            //检测重试是否超过三次，超过三次退出
            if ($tries >= $data['retry_count']) {
                return false;
            } else {
                //如果此次插入失败，进入重试程序
                if (!$queryResult) {
                    $tries++;
                    usleep(1);
                    $queryFunction($syncData, $tries);
                } else {
                    //成功返回
                    return true;
                }
            }
        };

        foreach ($chunk as $syncData) {
            $syncData = array_map(function ($element) {

                $element["create_time"] = NOW_TIME;
                $element["oc_type"] = 'ADVANCE';
                $element["pay_type"] = 'ONLINE';

                return $element;
            }, $syncData);
            //将数据分块后插入数据库
            $queryStatus = $queryFunction($syncData);
            $currentTime = time();
            //重试三次失败后，直接退出同步程序；
            if (!$queryStatus) {
                //TODO 添加执行失败的报警，和Log日志
                $this->endInvoke(null, 8001, "同步数据失败 retry:" . $data['retry_count']);

                break;
            } else {
                if ($currentTime - $startTime > $data['timeout']) {
                    //TODO 添加执行失败的报警，和Log日志
                    $this->endInvoke(null, 8002, "单次同步时间超时。timeout：" . $data["timeout"]);

                    break;
                }
            }
            usleep(1);
        }

        return $this->res();
    }


}
