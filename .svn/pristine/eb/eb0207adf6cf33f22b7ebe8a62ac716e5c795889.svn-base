<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 用户中心 导出  回调接口
 */

namespace Com\CallBack\Export;
use System\Base;

class UcExport extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * POP 卖家客户导出
     *
     * Com.CallBack.Export.UcExport.CustomerListPOP
     * @param [type] &$data  [description]
     * @param [type] $params [description]
     */
    public function CustomerListPOP(&$customers, $params)
    {
        $uc_codes = array_unique(array_column($customers, 'uc_code'));
        $sc_code = $params['sc_code'];

        $memApi = 'Base.UserModule.Customer.Customer.getAll';
        $dataParams = array(
            'uc_code' => $uc_codes,
            'sc_code' => $sc_code,
        );
        $memRes = $this->invoke($memApi, $dataParams);
        if ($memRes['status'] != 0) {
            return $this->endInvoke(NULL, $memRes['status']);
        }
        $memArr = $memRes['response'];

        $regionApi = 'Base.UserModule.Customer.Customer.getOrderRegion';
        $regionData = array(
        	'sc_code' => $sc_code,
        	'uc_codes' => $uc_codes,
        	);
        // var_dump($regionData);
        $regionRes = $this->invoke($regionApi, $regionData);
        if ($regionRes['status'] != 0) {
        	return $this->endInvoke(NULL, $regionRes['status']);
        }
        $region = $regionRes['response'];
        if (!empty($memArr)) {
            $memArr = changeArrayIndex($memArr, 'uc_code');
            $region = changeArrayIndex($region, 'uc_code');
            foreach ($customers as $key => $customer) {
                if (!empty($memArr[$customer['uc_code']]) ) {
					$customers[$key]['invite_from']     = $memArr[$customer['uc_code']]['invite_from'];
					$customers[$key]['commercial_name'] = $memArr[$customer['uc_code']]['commercial_name'];
					$customers[$key]['remark']          = $memArr[$customer['uc_code']]['remark'];

                } else {
					$customers[$key]['commercial_name'] = '';
					$customers[$key]['invite_from']     = '来自火星';
					$customers[$key]['remark']          = $memArr[$customer['uc_code']]['remark'];
                }

                if (!empty($region[$customer['uc_code']])) {
					$customers[$key]['province'] = $region[$customer['uc_code']]['province'];
					$customers[$key]['city']     = $region[$customer['uc_code']]['city'];
					$customers[$key]['district'] = $region[$customer['uc_code']]['district'];
					$customers[$key]['address']  = $region[$customer['uc_code']]['address'];
                }else{
					$customers[$key]['province'] = '';
					$customers[$key]['city']     = '';
					$customers[$key]['district'] = '';
					$customers[$key]['address']  = '';
                }
            }
        }
        if (!empty($customers)) {
            $turn = array();
            foreach ($customers as $key => $value) {
                $arr = array();
				$arr['commercial_name'] = $value['commercial_name'];
				$arr['name']            = $value['name'];
				$arr['create_time']     = $value['create_time'];
				$arr['mobile']          = $value['mobile'];
				$arr['province']        = $value['province'];
				$arr['city']            = $value['city'];
				$arr['district']        = $value['district'];
				$arr['address']         = $value['address'];
				$arr['orders']          = $value['orders'];
				$arr['order_amount']    = $value['order_amount'];
				$arr['remark']          = $value['order_amount'];
                if ($value['invite_from'] === 'SC') {
                    $arr['invite_from'] = '自有客户';
                } else {
                    $arr['invite_from'] = '平台客户';
                }
                $arr['ssname'] = $value['ssname'];
                if (empty($value['shname'])) {
                    $arr['shname'] = '';
                } else {
                    $arr['shname'] = $value['shname'];
                }
                $turn[] = $arr;
            }
        }
        $customers = $turn;
        // var_dump($customers);
        // die();
    }

    public function export(&$data)
    {
        $arr = array();
        foreach ($data as $key => $val) {
            if ($val['terminal'] == 'NO') {
                $terminal = '';
            } elseif ($val['terminal'] == 'YES') {
                $terminal = '终端买家';
            }
            $arr[$key] = array(
                'uc_code' => $val['uc_code'],
                'username' => $val['username'],
                'commercial_name' => $val['commercial_name'],
                'name' => $val['name'],
                'mobile' => $val['mobile'],
                'store_name' => $val['store_name'],
                'create_time' => $val['create_time'] ? date('Y-m-d H:i:s', $val['create_time']) : '',
                'terminal' => $terminal
            );
        }
        $data = $arr;
    }

    /**
     * Com.Callback.Export.UcExport.plat_export
     * @param type $export_data
     */
    public function plat_export(&$data, $params)
    {
        $platformSalemanApi = 'Base.UserModule.User.User.platformSaleman';
        $arr['is_page'] = 'NO';
        $arr['uc_code'] = $params['param']['sales_uc_code'];
        $res = $this->invoke($platformSalemanApi, $arr);
        if ($res['status'] != 0) {
            return $this->endInvoke(NULL, $res['status']);
        }
        $platformSalemanList = $res['response'];
        $platformSalemanList = changeArrayIndex($platformSalemanList, 'invite_code');
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($platformSalemanList[$value['invite_code']]) {

                    $data[$key]['platformSaleman'] = $platformSalemanList[$value['invite_code']]['real_name'];
                } else {
                    $data[$key]['platformSaleman'] = '火星人';
                }
            }
        }
        $arr = array();
        foreach ($data as $key => $val) {
            if ($val['terminal'] == 'NO') {
                $terminal = '';
            } elseif ($val['terminal'] == 'YES') {
                $terminal = '终端买家';
            }
            $arr[$key] = array(
                'uc_code' => $val['uc_code'],
                'username' => $val['username'],
                'commercial_name' => $val['commercial_name'],
                'name' => $val['name'],
                'mobile' => $val['mobile'],
                'saleman' => $val['platformSaleman'],
                'create_time' => $val['create_time'] ? date('Y-m-d H:i:s', $val['create_time']) : '',
                'terminal' => $terminal
            );
        }
        $data = $arr;
    }
}

?>
