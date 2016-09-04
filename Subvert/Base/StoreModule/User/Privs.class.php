<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: wangxuemei <wangxuemei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | pop商家子账户相关的操作
 */
namespace Base\StoreModule\User;
use System\Base;

class Privs extends Base
{
    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 查询Flag的ID
     * Base.StoreModule.User.privs.lists

     */
    public function lists($params)
    {
        $this->_rule = array(
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //状态
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //登录用户编码

        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $api = "Base.StoreModule.User.SubAccount.findOne";
        $sba = $this->invoke($api,['uc_code'=>$params['uc_code']]);
        if($sba['status'] != 0){
            return $this->res('',5525);
        }
        $roles = D('ScRoles')->field('id,name,privs_id,info')->where(
            [   'status' => $params['status'],
                'sc_code'=>$sba['response']['sc_code'],
                'main_sc_code'=>$sba['main_uc_code']
            ])->select();

        $privs = D('ScPrivs')->field('id,pid,name,flag,extra,sort')->where(['status' => $params['status']])->order('sort asc')->select();

        $data['roles'] = $roles;
        $data['privs'] = $privs;

        return $this->res($data);
    }

    /**
     * 登录用户权限
     * Base.StoreModule.User.Privs.getUserPrivs
     */
    public function getUserPrivs($params){

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码

        );
        $uc_code = $params['uc_code'];
        $status = $params['status'];
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验

            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取所有权限
        $api = "Base.StoreModule.User.Privs.lists";
        $data = $this->invoke($api,['status'=>$status,'uc_code'=>$uc_code]);

        $dataRole = $data['response']['roles'];
        $dataPrivs = $data['response']['privs'];

        //获取用户身份
        $api = "Base.StoreModule.User.subAccount.getUserRoles";
        $userRoles = $this->invoke($api,['uc_code'=>$uc_code]);

        //用户权限列表筛选
        $userAllPriv = '';
        foreach( $dataRole as $v ) {
            if( $userRoles['response']['roles_id']==$v['id'] ) {

                $userAllPriv = $v['privs_id'];
            }
        }

        $userAllPriv = explode( ',', $userAllPriv);
        $userAllPriv = array_flip( $userAllPriv);



        //用户权限flag 筛选
        $returnUserPriv = array();
        foreach ( $dataPrivs as  $allPriv) {
            foreach ($userAllPriv as $userPriv) {
                if ($allPriv['id']==$userPriv) {
                    $returnUserPriv[] = strtolower($allPriv['flag']);
                }
            }
        }

        $arr['flag'] = $returnUserPriv;
        $arr['ids'] = $userAllPriv;
        return $this->res($arr);

    }


    /**
     * 获取用户权限ids
     *  Base.StoreModule.User.Privs.getRolesPrivsIds
     */
    public function getRolesPrivsIds($params){

        $this->_rule = array(
            array('roles_id', 'require', PARAMS_ERROR, MUST_CHECK), //角色
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //状态
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码

        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验

            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $res = D('ScRoles')->where(['status' => $params['status'],'id'=>$params['roles_id']])->find();

        $res = array_flip(explode(',',$res['privs_id']));
        return $this->res($res);

    }



    /**
     * 获用户的权限数据
     * Base.StoreModule.User.Privs.getUserPrivileges
     */
    public function getUserPrivileges($params){
        $this->_rule = array(
            array('status', 'require', PARAMS_ERROR, MUST_CHECK), //权限状态
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK), //注册用户编码

        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $status = $params['status'];

        //获取所有权限
        $api = "Base.StoreModule.User.Privs.lists";
        $data = $this->invoke($api,['status'=>$status,'uc_code'=>$uc_code]);
        $privs = $data['response']['privs'];
        //获取当前用户权限

        $api = "Base.StoreModule.User.SubAccount.findOne";
        $sub = $this->invoke($api,['uc_code'=>$uc_code]);
        $userPriURLs = [];
        if($sub['response']['uc_code']==$sub['response']['main_uc_code']){
            $userPriURLs = $privs;
        }else{

            $api = "Base.StoreModule.User.Privs.getRolesPrivsIds";
            $userPrivIds = $this->invoke($api,['uc_code'=>$uc_code,'roles_id'=>$sub['response']['roles_id'],'status'=>$params['status']]);
            $ids = array_flip($userPrivIds['response']);
            //过滤该用户权限
            $userPriURLs = array();
            foreach( $privs as $p_v ) {
                foreach($ids as $u_v ) {
                    if( $p_v['id'] == $u_v ) {
                        $userPriURLs[] = $p_v;
                    }
                }
            }
        }

        return $this->res(self::buildMenu( $userPriURLs, 0));
    }

    private function buildMenu( $arr, $root ){
        $ids = self::findChild( $arr, $root );

        if( empty( $ids ) ) {return null;}
        foreach( $ids as $k=>$v ) {
            $tree = self::buildMenu( $arr, $v['id'] );
            if( null != $tree ) {
                $ids[$k]['childs'] = $tree;
            }
        }
        return $ids;
    }

    // 查找子菜单
    private function findChild( $arr, $id ){
        $childs = array();
        foreach( $arr as $k=>$v ) {
            if( $v['pid'] == $id ) {
                $childs[] = $v;
            }
        }
        return $childs;
    }

    /**
     * 添加权限列表
     * Base.StoreModule.User. Privs.add
    $params =  array(
    'extra'=>'{"isMenu":"1","menuIcon":"indexm"}',
    'pid'=>0,
    'name'=>'asdfadsf',
    'status'=>1,
    'flag'=>"admin/index"
    );
     */
    public function add($params){

        $this->_rule = array(
            array('pid', 'require', PARAMS_ERROR, MUST_CHECK), //父类id
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //权限名称
            array('sort','require',PARAMS_ERROR, ISSET_CHECK),//排序
            array('status','require',PARAMS_ERROR, MUST_CHECK),//状态
            array('extra','require',PARAMS_ERROR, MUST_CHECK),//状态
            array('flag','require',PARAMS_ERROR, MUST_CHECK)//状态
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $privs = D('ScPrivs')->field('flag')->where(['flag' => $params['flag']])->order('sort asc')->select();

        $res = D('ScPrivs')->data($params)->add();

        if(!$res){
            return $this->res('添加权限节点失败');

        }
        return $this->res($res);

    }

	/**
     * 获取权限节点列表
     * Base.StoreModule.User.Privs.nodeList
     */
    public function nodeList($params){

		$res = D('ScPrivs')->order('sort asc')->select();

		return $this->res($res);
    }
	
	
	/**
     * 获取权限节点列表
     * Base.StoreModule.User.Privs.nodeInfo
     */
    public function nodeInfo($params){
		$res[] = D('ScPrivs')->order('sort asc')->select();
		$res[] = D('ScPrivs')->where($params)->find();
		return $this->res($res);
    }
	
	
	/**
     * 权限节点编辑
     * Base.StoreModule.User.Privs.nodeEdit
     */
    public function nodeEdit($params){
		$this->_rule = array(
			array('pid', 'require', PARAMS_ERROR, MUST_CHECK),
            array('name', 'require', PARAMS_ERROR, MUST_CHECK), //权限名称
            array('sort','require',PARAMS_ERROR, ISSET_CHECK),//排序
            array('status','require',PARAMS_ERROR, MUST_CHECK),//状态
            array('extra','require',PARAMS_ERROR, MUST_CHECK),//状态
            array('flag','require',PARAMS_ERROR, MUST_CHECK)//状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $privs = D('ScPrivs')->field('flag')->where('flag="' . $params['flag'] . '" and id <> ' . $params['id'] .' and name="' . $params['name'] .'"')->select();
        if($privs) return $this->res('已经有此权限');
        $res = D('ScPrivs')->data($params)->save();
        if(!$res){
            return $this->res('编辑权限节点失败');
        }
        return $this->res($res);
    }

    /**
     * 权限编辑
     * Base.StoreModule.User.Privs.update
     */

    public function update($params) {

      $this->startOutsideTrans();
      $this->_rule = array(
            array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),
            array('id', 'require', PARAMS_ERROR, MUST_CHECK),
      );
      if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
      }
      if(!empty($params['data']['id'])) unset($params['data']['id']);//禁止修改id
      $where['id'] = $params['id'];
      $data = $params['data'];
      $data['update_time'] = NOW_TIME;
      $res = D('ScRoles')->where($where)->save($data);
      if(!$res){
       return $this->res(null, 5523);
      }
      return $this->res($res);
    }
	
}

