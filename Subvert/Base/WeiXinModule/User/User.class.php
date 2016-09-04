<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 微信用户相关模块
 */

namespace Base\WeiXinModule\User;
use System\Base;

class User extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;
    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Uc';
    }
    
    /**
     * 
     * 添加用户  微信用户关注时进行手机验证后  触发该接口  如果之前关注过  则更新关注信息  未关注 则添加相应的用户信息
     * Base.WeiXinModule.User.User.add
     * @return integer   成功时返回  自增id
     */
    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('open_id', 'require', PARAMS_ERROR, MUST_CHECK),
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
            array('sex',array(0,1,2),PARAMS_ERROR, ISSET_CHECK,'in'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        //用户微信信息
        $user_weixin_data = array(
            'nickname'=>$params['nickname'],
            'sex'=>$params['sex'],
            'province'=>$params['province'],
            'city'=>$params['city'],
            'country'=>$params['country'],
            'headimgurl'=>$params['headimgurl'],
            'remark'=>$params['remark'],
            'subscribe_time'=>$params['subscribe_time'],
            'language'=>$params['language'],
            'groupid'=>$params['groupid'],
            'unionid'=>$params['unionid'],
            'subscribe'=>$params['subscribe'],
            'update_time'=>NOW_TIME,
            'create_time'=>NOW_TIME,
            'open_id'    => $params['open_id'],
            'uc_code'    => $params['uc_code'],
        );
        //添加用户微信信息
        $add_res = D('UcWeixin')->add($user_weixin_data);
        if($add_res <= 0 || FALSE === $add_res){
            return $this->res(NULL,4010);
        }
        return $this->res(true);
    }
    
    public function update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('open_id', 'require', PARAMS_ERROR, MUST_CHECK),
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $uc_code = $params['uc_code'];
        $open_id = $params['open_id'];
        //用户微信信息
        $user_weixin_data = array(
            'nickname'=>$params['nickname'],
            'sex'=>$params['sex'],
            'province'=>$params['province'],
            'city'=>$params['city'],
            'country'=>$params['country'],
            'headimgurl'=>$params['headimgurl'],
            'remark'=>$params['remark'],
            'subscribe_time'=>$params['subscribe_time'],
            'language'=>$params['language'],
            'groupid'=>$params['groupid'],
            'unionid'=>$params['unionid'],
            'subscribe'=>$params['subscribe'],
            'update_time'=>NOW_TIME,
        );
        $where = array(
            'uc_code' => $uc_code,
            'open_id' => $open_id,
        );
        $update_res = D('UcWeixin')->where($where)->save($user_weixin_data);
        if($update_res <= 0 || $update_res === FALSE || $update_res > 1){
            return $this->res(NULL,4009);
        }
        return $this->res($update_res);
    }
    
    /**
     * 获取用户微信信息  此方法可以用作  微信用户登陆的时候根据 open_id 获取 他在网站中的uc_code
     * Base.WeiXinModule.User.User.getWeixinInfo
     * @param type $open_id
     * @param type $uc_code
     * @return type
     */
    public function getWeixinInfo($params){
        $this->_rule = array(
            array('open_id', 'require', PARAMS_ERROR, ISSET_CHECK), //微信的open_id
            array('uc_code','require',PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $open_id = $params['open_id'];
        $uc_code = $params['uc_code'];
        
        $where = array();
        if(empty($open_id) && empty($uc_code)){
            return $this->res(null,4016);
        }
        
        if($uc_code){
            $where['uc_code'] = $uc_code;
        }
        
        if($open_id){
            $where['open_id'] = $open_id;
        }
        
        $weixin_user_info = D('UcWeixin')->where($where)->select();
        $weixin_user_info_nums = count($weixin_user_info);
        if($weixin_user_info_nums <= 0){
            return $this->res(null);  //不存在该微信信息
        }
        
        if($weixin_user_info_nums > 1){
            return $this->res(null,4018);  //微信信息大于一条  有误
        }
        
        return $this->res($weixin_user_info[0]);
    }
    
    
    /**
     * 获取微信用户
     * Base.WeiXinModule.User.User.getWxFrom
     * @return [type] [description]
     */
    public function getWxFrom($params){
        $this->_rule = array(
            array('invite_from', array('UC','SC'), PARAMS_ERROR, ISSET_CHECK, 'in'), # 用户来源
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $where = array();
        $where['um.invite_from'] = $params['invite_from'];
        $where['uw.groups']       = array('EQ', '');
        $wxUser = D('UcWeixin')->field('uw.open_id as openId')                          
                           ->alias('uw')
                           ->join("{$this->tablePrefix}uc_member um ON uw.uc_code = um.uc_code",'LEFT')
                           ->where($where)
                           ->limit(50)
                           ->select();
        if ($wxUser === false) {
            return $this->res(null, 8);
        }
        return $this->res($wxUser);
    }

    /**
     * Base.WeiXinModule.User.User.updataGroup
     * @return [type] [description]
     */
    public function updataGroup($params){
        $this->_rule = array(
            array('openidList', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //店铺编码多个
            array('groups','require',PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        if (!empty($params)) {
            foreach ($params['openidList'] as $openId) {
                $where = $data = array();
                $where['open_id'] = $openId;
                $data['groups']    = $params['groupName'];
                $wxUser = D('UcWeixin')->where($where)->save($data);
                if ($wxUser === false) {
                    return $this->res(null, 8);
                } 
            }
           
        }
        
        return $this->res(true);
    }
}

?>
