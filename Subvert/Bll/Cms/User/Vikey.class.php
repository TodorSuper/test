<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: liaoxianwen <liaoxianwen@yunputong.com >
 * +---------------------------------------------------------------------
 * | 加密狗安全模块 Vikey
 */
namespace Bll\Cms\User;
use System\Base;
use Library\Vikey as VikeyLib;


class Vikey extends Base {
    // 加密狗lib实例
    protected  $vikey_obj;
    public function __construct() {
        parent::__construct();
        $this->vikey_obj = new VikeyLib();
    }
    /**
     * @api {post} 列表
     * @apiVersion 1.0.0
     * @apiName Bll.Cms.User.Vikey.lists
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function lists($params) {
        empty($params['uc_codes']) && !is_array($params['uc_codes']) AND $this->endInvoke($params, 4201);
        $uc_codes = $params['uc_codes'];
        $api_params['condition'] = ['uc_code' => array('in',$uc_codes)];
        $api_params['fields'] = 'id,uc_code,vikey_hid,update_time,create_time';
        $apiPath =  "Base.UserModule.User.Vikey.lists";
        return $this->response($apiPath, $api_params);
    }
    /**
     * @api {post} 绑定加密狗
     * @apiVersion 1.0.0
     * @apiName Bll.Cms.User.Vikey.bind
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function bind($params) {
        $rules = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户的编码
            array('vikey_hid', 'require', PARAMS_ERROR, MUST_CHECK), // 硬件设备号
        );
        $this->checkRules($rules, $params);
        // 看vikey_hid 是否已经绑定过了
        $api_params['condition']['vikey_hid'] = $params['vikey_hid'];
        $vikey_res = $this->getInfo($api_params);
        if(!empty($vikey_res['response'])) {
            $this->endInvoke('', 2405);
        }
        // 看下用户是否之前绑定过
        $api_params = [];
        $api_params['condition']['uc_code'] = $params['uc_code'];
        $vikey_res = $this->getInfo($api_params);
        // 用公钥加密字符串回传给加密狗
        $vikey_strs = $this->getVikeyStr($params['vikey_hid']);
        // api_params
        $api_params = [
            'vikey_hid' => $params['vikey_hid'],
            'uc_code' => $params['uc_code'],
            'security_str' => $vikey_strs['security_str'],
            'sub_vikey_str' => $vikey_strs['sub_vikey_str'],
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME
        ];
        $apiPath =  "Base.UserModule.User.Vikey.add";
        if($vikey_res['status'] == 0 && !empty($vikey_res['response'])) {
            // 获取vikey 信息
            $api_params['condition'] = [
                'uc_code' => $params['uc_code']
            ];
            $apiPath =  "Base.UserModule.User.Vikey.update";
        }
        $this->response($apiPath, $api_params, ['error_code' => 2405, 'endInvoke' => true], ['vikey_str' => $vikey_strs['vikey_str']]);
    }

    private function getInfo($apiParams) {
        return  $this->response(
            'Base.UserModule.User.Vikey.info',
            $apiParams,
            ['error_code' => 0, 'endInvoke' => false]
        );
    }
    /**
     * @api {post} 重新绑定加密狗
     * @apiVersion 1.0.0
     * @apiName Bll.Cms.User.Vikey.rebind
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function rebind($params) {
        $rules = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户的编码
            array('vikey_hid', 'require', PARAMS_ERROR, MUST_CHECK), // 硬件设备号
        );
        $this->checkRules($rules, $params);
        // 用公钥加密字符串回传给加密狗
        $vikey_strs = $this->getVikeyStr($params['vikey_hid']);
        // api_params
        $api_params = [
             'condition'=> [
                 'vikey_hid' => $params['vikey_hid'],
                 'uc_code' => $params['uc_code']
             ],
            'security_str' => $vikey_strs['security_str'],
            'sub_vikey_str' => $vikey_strs['sub_vikey_str'],
            'update_time' => NOW_TIME,
            'vikey_hid' => $params['vikey_hid']
        ];
        $apiPath =  "Base.UserModule.User.Vikey.update";

        $this->response($apiPath, $api_params, ['error_code' => 0, 'endInvoke' => true], ['vikey_str' => $vikey_strs['vikey_str']]);
    }
    /**
     * @api {post} 加密狗解绑
     * @apiVersion 1.0.0
     * @apiName Bll.Cms.User.Vikey.unbind
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function unbind($params) {
        $rules = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编号
        );
        $this->checkRules($rules, $params);

        // api参数
        $api_params = [
            'vikey_hid' => '',
            'security_str' => '',
            'sub_vikey_str' => '',
            'update_time' => NOW_TIME,
            'condition' => ['uc_code' => $params['uc_code']]
        ];
        $apiPath =  "Base.UserModule.User.Vikey.update";
        return $this->response($apiPath, $api_params);
    }
    /**
     * @api {post} 加密狗解绑
     * @apiVersion 1.0.0
     * @apiName Bll.Cms.User.Vikey.unbind
     * @apiAuthor liaoxiawnen <liaoxianwen@yunputong.com>
     */
    public function update($params) {
        $rules = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编号
        );
        $this->checkRules($rules, $params);
        // api参数
        $api_params = [
            'vikey_hid' => isset($params['vikey_hid']) ? $params['vikey_hid'] : '',
            'security_str' => isset($params['security_str']) ? $params['security_str'] : '',
            'sub_vikey_str' => isset($params['sub_vikey_str']) ? $params['sub_vikey_str'] : '',
            'update_time' => NOW_TIME,
            'condition' => ['uc_code' => $params['uc_code']]
        ];
        $apiPath =  "Base.UserModule.User.Vikey.update";
        return $this->response($apiPath, $api_params);
    }
    /**
     * @api {post} 加密狗检测
     * @apiVersion 1.0.0
     * @param $params
     * @apiName Bll.Cms.User.Vikey.valid
     */
    public function valid($params) {
        $paramCheck = $this->_validParams($params);
        if(!is_array($paramCheck)) {
            return $paramCheck;
        }
        $validCheck = $this->checkVikeyHidValid($paramCheck['vikey_info'], $paramCheck['check_params']);
        if(!is_bool($validCheck)) {
            return $validCheck;
        }
        // 用公钥加密字符串回传给加密狗
        $vikey_strs = $this->getVikeyStr($params['vikey_hid']);
        $api_params = [
            'condition' => [
                'vikey_hid' => $params['vikey_hid']
            ],
            'security_str' => $vikey_strs['security_str'],
            'sub_vikey_str' => $vikey_strs['sub_vikey_str'],
            'vikey_hid' => $params['vikey_hid']
        ];
        $apiPath = "Base.UserModule.User.Vikey.update";
        return $this->response($apiPath, $api_params, ['error_code' => 2402, 'endInvoke' => true], ['vikey_str' => $vikey_strs['vikey_str']]);
    }

    /**
     * 看当前用户，当前设备绑定的信息
     * @param $params
     */
    public function getVikeyInfo($params) {
        $rules = array(
            array('vikey_hid', 'require', PARAMS_ERROR, MUST_CHECK), // 硬件设备号
            array('sign', 'require', PARAMS_ERROR, MUST_CHECK), // 硬件秘钥
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编码
        );
        $this->checkRules($rules, $params);

        $api_params['condition']['vikey_hid'] = $params['vikey_hid'];
        $api_params['condition']['uc_code'] = $params['uc_code'];

        // 获取vikey 信息
        $vikey_res = $this->response(
            'Base.UserModule.User.Vikey.info',
            $api_params,
            ['error_code' => 0, 'endInvoke' => false]
        );

        if($vikey_res['status'] != 0) {
            return $this->endInvoke('', $vikey_res['status']);
        }
        return $this->endInvoke($vikey_res['response']);
    }

    protected function _validParams($params) {
        $rules = array(
            array('vikey_hid', 'require', PARAMS_ERROR, MUST_CHECK), // 硬件设备号
            array('sign', 'require', PARAMS_ERROR, MUST_CHECK), // 硬件秘钥
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), // 用户编码
        );
        $this->checkRules($rules, $params);

        $api_params['condition']['vikey_hid'] = $params['vikey_hid'];
        $api_params['condition']['uc_code'] = $params['uc_code'];

        // 获取vikey 信息
        $vikey_res = $this->response(
            'Base.UserModule.User.Vikey.info',
            $api_params,
            ['error_code' => 0, 'endInvoke' => false]
        );

        if($vikey_res['status'] != 0) {
            return $this->endInvoke('', $vikey_res['status']);
        }
        $check_params = [
            'uc_code' => $params['uc_code'],
            'sign' => $params['sign'],
            'vikey_hid' => $params['vikey_hid']
        ];
        $vikey_info = $vikey_res['response'];
        return [
            'check_params' => $check_params,
            'vikey_info' => $vikey_info
        ];
    }
    /**
     * 检测硬件与用户是否匹配
     * @param $vikey_info
     * @param $check_params
     */
    protected function checkVikeyHidValid($vikey_info, $check_params) {
        if($vikey_info['uc_code'] != $check_params['uc_code']) {
            return $this->endInvoke([$vikey_info, $check_params], 2404);
        }
        // 用私钥解密检测加密字符串里的 和传过来的匹配
        $check_result = $this->vikey_obj->checkVikeyHid($check_params['sign'], $vikey_info['vikey_hid'], $vikey_info['sub_vikey_str']);

        if(!$check_result) {
            return $this->endInvoke([$check_result,$check_params], 2402);
        }
        return true;
    }

    /**
     * 校验sign
     * @param $params
     * @return array|void
     */
    public function checkBySign($params) {
        $paramCheck = $this->_validParams($params);
        if(!is_array($paramCheck)) {
            return $paramCheck;
        }
        $validCheck = $this->checkVikeyHidValid($paramCheck['vikey_info'], $paramCheck['check_params']);
        if(!is_bool($validCheck)) {
            return $validCheck;
        }
        return $this->endInvoke([], 0, null, '校验成功');
    }
    /**
     * 截取字符串【因为vikey存储空间太小】
     * @return array
     */
    protected function getVikeyStr($vikey_hid) {
        $security_str = $this->vikey_obj->mkSecurityStr();
        $vikey_str = $this->vikey_obj->encrypt(['hid' => $vikey_hid, 'security_str' => $security_str]);
        // 处理截取
        $length = strlen($vikey_str);
        $sub_security_str = substr($vikey_str, 0, 63);
        $sub_vikey_str = substr($vikey_str, 63, ($length-1));
        // return
        return [
            'vikey_str' => $sub_security_str,
            'sub_vikey_str' => $sub_vikey_str,
            'security_str' => $security_str
        ];
    }

    /**
     * @param $apiPath
     * @param $api_params
     * @param int $error_code
     * @param array $advance_res
     */
    protected function response($apiPath, $api_params, $other_params = ['error_code' => 0, 'endInvoke' => true], $advance_res = []) {
        extract($other_params);
        $list_res = $this->invoke($apiPath, $api_params);

        if(!is_array($list_res['response'])) {
            $list_res['response'] = [];
        }
        if(0 !== $list_res['status']){
            if($endInvoke === false) {
                $list_res['status'] = $error_code;
                return $list_res;
            } else {
                unset($list_res['status']);
                return $this->endInvoke(array_merge($list_res['response'], $advance_res), $error_code);
            }
        }
        if($endInvoke === false) {
            return $list_res;
        } else {
            unset($list_res['status']);
            return $this->endInvoke(array_merge($list_res['response'], $advance_res));
        }
    }
    /**
     * 检测规则
     * @param $params
     * @param $rules
     * @permission private
     */
    protected function checkRules($rules, $params) {
        # 自动校验
        if (!$this->checkInput($rules, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
    }
}