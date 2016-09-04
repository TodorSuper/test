<?php
/**
 * Created by JetBrains PhpStorm.
 * User: wangguangjian
 * Date: 15-10-20
 * Time: 下午3:19
 */
namespace Bll\Pop\User;
use System\Base;

class SubAccount extends Base {

    private $_rule = null; # 验证规则列表


    public function __construct() {
        parent::__construct();
    }
    /**
     * 添加子账户
     * Bll.Pop.User.SubAccount.add
     * @params type $username
     * @params type $phone
     * @params type $real_name
     * @params type $roles_id
     * @params type $main_uc_code
     * @params type $sc_code
     * @access public
     * @return void
     */
    public function add ($params) {
        if( !$params['main_uc_code'] ) {
            return $this->res('', 5507);
        }
        try{

            D()->startTrans();
            # 创建用户
            $userData['username'] = $params['username'];
            $userData['mobile'] = $params['mobile'];
            $userData['pre_bus_type'] = UC_USER_SUB_ACCOUNT;
            $userData['real_name'] = $params['real_name'];
            $userData['password'] = encrypt_password($params['mobile']);
            $createUser = $this->invoke('Base.UserModule.User.Basic.add', $userData);
            if($createUser['status'] != 0 ) {
                return $this->res('', 5501); # 创建用户失败
            }
            # 创建子账户
            $AccountData['uc_code'] = $createUser['response']['uc_code'];
            $AccountData['sc_code'] = $params['sc_code'];
            $AccountData['main_uc_code'] = $params['main_uc_code'];
            $AccountData['roles_id'] = $params['roles_id'];
            $AccountData['name'] = $params['username'];
            $createSubAccount = $this->invoke('Base.StoreModule.User.SubAccount.add', $AccountData);
            if($createSubAccount['status'] != 0 ) {
                return $this->res('', 5516); # 创建子账户失败  //需要添加错误码
            }

            D()->commit();
            return $this->res(true);
        }catch(\Exception $e) {
            D()->rollback();
            return $this->res('', 5504); # 系统繁忙
        }
    }

    /**
     * 更改用户角色
     * Bll.Pop.User.SubAccount.updateRole
     * @param type $uc_code
     * @param type $role_id
     * @access public
     * @return void
     */

    public function updateRole ($params) {
        try {
            D()->startTrans();
            $data['uc_code'] = $params['uc_code'];
            $data['login_uc_code'] = $params['login_uc_code'];
            $data['roles_id'] = $params['roles_id'];
            $apiPath = 'Base.StoreModule.User.SubAccount.update';
            $response = $this->invoke($apiPath, $data);
            if($response['status'] != 0){
                return $this->endInvoke(NULL,$response['status']);
            }
            $res = D()->commit();
            if(FALSE  === $res){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke($response['response']);
            } catch (\Exception $ex) {
                D()->rollback();
                return $this->endInvoke(NULL,4014);
            }
    }

    /**
     * 更改用户状态
     * Bll.Pop.User.SubAccount.changeStatus
     * @param type $status
     * @param type $uc_code
     * @access public
     * @return void
     */

    public function changeStatus ($params) {

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('login_uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //登录用户编码

        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        try {
            D()->startTrans();
            $data['status'] = $params['status'];
            $data['uc_code'] = $params['uc_code'];
            $data['login_uc_code'] = $params['login_uc_code'];
            $where['uc_code'] = ['in',[$params['uc_code'],$params['login_uc_code']]];
            $info = D('ScSubAccount')->field('main_uc_code')->where($where)->select();
            if($info[0]['main_uc_code']!= $info[1]['main_uc_code']){
                return $this->res('',5524);
            }
            $apiPath = 'Base.UserModule.User.Basic.update';
            $response = $this->invoke($apiPath, $data);
            if($response['status'] != 0){
                return $this->endInvoke(NULL,$response['status']);
            }
            $res = D()->commit();
            if(FALSE  === $res){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke($response['response']);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4014);
        }
    }

    /**
     * 用户列表
     * Bll.Pop.User.SubAccount.getList
     * @param type $roles_id
     * @param type $login_uc_code
     * @param type $status
     * @param Optional $mobile
     * @param Optional $username
     * @param Optional $real_name
     * @access public
     * @return void
     */

    public function getList ($params) {
        $data['uc_code'] = $params['login_uc_code'];
        $data['status'] =  $params['status'];
        $data['page'] = $params['page'];
        $params['username'] ? $data['username'] = $params['username'] : null;
        $params['mobile'] ?  $data['mobile'] = $params['mobile'] : null;
        $params['real_name'] ? $data['real_name'] = $params['real_name'] : null;
        $params['roles_id'] ? $data['roles_id'] = $params['roles_id'] : null;
        $apiPath = 'Base.StoreModule.User.SubAccount.lists';
        $response = $this->invoke($apiPath, $data);
        return $this->res($response['response'],$response['status']);
    }

    /**
     *  角色列表
     * Bll.Pop.User.SubAccount.getRoleList
     * @param type $status
     * @param type $login_uc_code
     * @param Optional $id
     * @access public
     * @return void
     */

    public function getRoleList ($params) {
        $params['id'] ? $data['id'] = $params['id'] : null;
        $data['status'] = $params['status'];
        $data['uc_code'] = $params['login_uc_code'];
        $data['page'] = $params['page'];
        $apiPath = 'Base.StoreModule.User.Roles.lists';
        $response = $this->invoke($apiPath, $data);
        return $this->res($response['response'],$response['status']);
    }

    /**
     * 添加角色
     * Bll.Pop.User.SubAccount.addRole
     * @param type $name
     * @param type $login_uc_code
     * @param type $privies_id
     * @access public
     * @return void
     */

    public function addRole ($params) {
        $data['name'] = $params['name'];
        $data['privs_id'] = $params['privies_id'];
        $data['uc_code'] = $params['uc_code'];
        $apiPath = 'Base.StoreModule.User.Roles.add';
        $response = $this->invoke($apiPath, $data);
        return $this->res($response['response'],$response['status']);
    }

    /**
     * 权限列表
     * Bll.Pop.User.SubAccount.getPrivsLists
     * @param Optional $id
     * @param type $uc_code
     * @param type $status
     * @access public
     * @return void
     */

    public function getPrivsLists ($params) {
        $data['status'] = $params['status'];
        $data['uc_code'] = $params['uc_code'];
        $data['roles_id'] = $params['roles_id'];
        $apiPath = 'Base.StoreModule.User.Privs.lists';
        $response = $this->invoke($apiPath, $data);
        if($params['roles_id']){
            $apiPath = 'Base.StoreModule.User.Privs.getRolesPrivsIds';
            $sub = $this->invoke($apiPath, $data);
            $response['response']['sub'] = $sub['response'];
        }

        return $this->res($response['response'],$response['status']);
    }

    /**
     *  添加权限
     * Bll.Pop.User.SubAccount.addPrivilege
     * @param type $pid
     * @param type $name
     * @param type $status
     * @param type $flag
     * @param Optional $sort
     * @access public
     * @return void
     */

//    public function addPrivilege ($params) {
//        $data['name'] = $params['name'];
//        $data['pid'] = $params['pid'];
//        $data['flag'] = $params['flag'];
//        $data['status'] = $params['status'];
//        $params['sort'] ? $data['sort'] = $params['sort'] : null;
//        $apiPath = 'Base.StoreModule.User.Privs.add';
//        $response = $this->invoke($apiPath, $data);
//        return $this->res($response['response'],$response['status']);
//    }

    /**
     *    重置密码
     * Bll.Pop.User.SubAccount.resetPassword
     * @param type $mobile
     * @param type $uc_code
     * @access public
     * @return void
     */

    public function resetPassword ($params) {

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('login_uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //登录用户编码

        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        try {
            D()->startTrans();
            $data['check_ori_pwd'] = 'NO';
            $data['confirm_pwd'] = $params['mobile'];
            $data['new_pwd'] = $params['mobile'];
            $data['uc_code'] = $params['uc_code'];
            $data['login_uc_code'] = $params['login_uc_code'];
            //校验用户操作权限
            $where['uc_code'] = ['in',[$params['uc_code'],$params['login_uc_code']]];
            $info = D('ScSubAccount')->field('main_uc_code')->where($where)->select();
            if($info[0]['main_uc_code']!= $info[1]['main_uc_code']){
                return $this->res('',5524);
            }

            $apiPath = 'Base.UserModule.User.Basic.changePwd';
            $response = $this->invoke($apiPath, $data);
            if($response['status'] != 0){
                return $this->endInvoke(NULL,$response['status']);
            }
            $res = D()->commit();
            if(FALSE  === $res){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,4014);
        }

    }



    /**
     *  验证手机号||用户名
     * Bll.Pop.User.SubAccount.validate
     * @param type $mobile
     * @access public
     * @return void
     */
    public function validate ($params) {
        $apiPath = 'Base.UserModule.User.Basic.getBasicUserInfo';
        $response = $this->invoke($apiPath, $params);
        return $this->res($response['response'],$response['status']);
    }

    /**
     * 左侧菜单列表
     * Bll.Pop.User.SubAccount.getUserPrivileges
     */
    public function getUserPrivileges($params){
        $apiPath = 'Base.StoreModule.User.Privs.getUserPrivileges';
        $response = $this->invoke($apiPath, $params);
        return $this->res($response['response']);
    }

    /**
     *       获取状态列表
     * Bll.Pop.User.SubAccount.getUserStatus
     */

    public function getUserStatus ($params) {
            $status = array(
                UC_ACCOUNT_POP_DISABLED => '禁用',
                UC_ACCOUNT_POP_ENABLE => '启用',
            );
        return $this->res($status);
    }

    /**
     * 插入商家默认角色
     * Bll.Pop.User.SubAccount.addDefaultRoles
     */
    public function addDefaultRoles($params)
    {

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK), //商户编码

        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $res = D('ScPrivs')->field('id')->where(['status' => 1])->select();

        $sub = D('ScSubAccount')->where([
            'uc_code' => $params['uc_code'],
            'main_uc_code' => $params['uc_code'],
            'sc_code' => $params['sc_code']
        ])->find();

        if ($sub) {
            return $this->res('此商家已经添加过角色', '5522');
        }
        foreach ($res as $k => $v) {
            $arr[$k] = $v['id'];
        }
        try {
            D()->startTrans();
            //添加默认 管理员角色
            $data = [
                'name' => '管理员',
                'info' => '系统管理员',
                'privs_id' =>'',
                'main_uc_code' => $params['uc_code'],
                'sc_code' => $params['sc_code'],
                'uc_code' => $params['uc_code'],
                'status' => 1,
                'create_time' => NOW_TIME
            ];
            $roles_id = D('ScRoles')->data($data)->add();
            if (!$roles_id) {
                return $this->res('添加角色失败', '5520');
            }
            $info = [
                'roles_id' => $roles_id,
                'uc_code' => $params['uc_code'],
                'main_uc_code' => $params['uc_code'],
                'sc_code' => $params['sc_code'],
                'create_time' => NOW_TIME
            ];

            $res = D('ScSubAccount')->data($info)->add();

            if (!$res) {
                return $this->res('添加用户失败', '5501');
            }

            //业务员插入默认角色 删除 店铺管理、账户管理权限
            $arr = array_flip($arr);
            unset($arr['1'],$arr['2'],$arr['3'],$arr['26'],$arr['25']);
            $arr = array_flip($arr);
            $data = [
                'name' => '业务',
                'privs_id' => implode(',', $arr),
                'main_uc_code' => $params['uc_code'],
                'sc_code' => $params['sc_code'],
                'uc_code' => $params['uc_code'],
                'status' => 1,
                'create_time' => NOW_TIME
            ];

            $res = D('ScRoles')->data($data)->add();

            if (!$res) {
                return $this->res('添加用户失败', '5520');
            }

            $commit_res = D()->commit();
            if ($commit_res === FALSE) {
                throw new \Exception('事务提交失败', 17);
            }
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL, 5521);
        }



    }
    /**
     * 修改角色权限
     * Bll.Pop.User.SubAccount.updateRolePrivs
     * @param Optional $id
     * @param type $privs_id
     * @access public
     * @return void
     */
    public function updateRolePrivs ($params){
        try {
            D()->startTrans();
            $data['id'] = $params['id'];
            $data['data']['privs_id'] = $params['privs_id'];
            $apiPath = 'Base.StoreModule.User.Privs.update';
            $response = $this->invoke($apiPath, $data);
            if($response['status'] != 0){
                return $this->endInvoke(NULL,$response['status']);
            }
            $res = D()->commit();
            if(FALSE  === $res){
                return $this->endInvoke(NULL,17);
            }
            return $this->endInvoke(true);
        } catch (\Exception $ex) {
            D()->rollback();
            return $this->endInvoke(NULL,5523);
        }

    }
    /**
     * 用户子账户数据查询
     * Bll.Pop.User.SubAccount.findOne
     * @access public
     * @return void
     */
    public function findOne($params){
        $api = "Base.StoreModule.User.SubAccount.findOne";
        $res = $this->invoke($api, $params);
        return $this->res($res['response']);
    }


}