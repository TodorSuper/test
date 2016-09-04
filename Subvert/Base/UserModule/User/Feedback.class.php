<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 基础用户信息相关模块
 */

namespace Base\UserModule\User;

use System\Base;

class Feedback extends Base {

    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
    }



    /**
     * Base.UserModule.User.Feedback.add
     * 问题反馈添加
     * @access public
     * @author Todor
     */

    public function add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('serve_problem', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),  # 服务态度问题
            array('send_problem', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),   # 发货时效问题
            array('item_problem', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),   # 商品质量问题
            array('cost_problem', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK,'in'),   # 费用问题
            array('content', 'require', PARAMS_ERROR, ISSET_CHECK),                     # 其他
            array('uc_code', 'require', PARAMS_ERROR, MUST_CHECK),                      # 用户编码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //生成编码
        $code_data = array(
            "busType"    => UC_USER,
            "preBusType" => UC_USER_FEEDBACK,
            "codeType"   => SEQUENCE_USER,
        );
        $feedback_code = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $code_data);
        if( $feedback_code['status'] !== 0) {
            return $this->res('', 4065);
        }

        $data = array(
            'serve_problem' =>$params['serve_problem'],
            'send_problem'  =>$params['send_problem'],
            'item_problem'  =>$params['item_problem'],
            'cost_problem'  =>$params['cost_problem'],
            'content'       =>$params['content'],
            'uc_code'       =>$params['uc_code'],
            'feedback_code' =>$feedback_code['response'],
            'create_time'   =>NOW_TIME,
            'update_time'   =>NOW_TIME,
            'status'        =>'ENABLE',
        );
        $res = D('UcProblem')->add($data);

        if(empty($res)){
            return $this->res(NULL,4064);
        };

        return $this->res($res);
    }


    /**
     * Base.UserModule.User.Feedback.lists
     * 问题反馈列表
     * @access public
     * @author Todor
     */

    public function lists($params){
        $this->_rule = array(
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK,),                   # 开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),                      # 结束时间
            array('type', array('UC','SC'), PARAMS_ERROR, ISSET_CHECK,'in'),              # 买家类型
            array('status', array('ENABLE','DISABLE'), PARAMS_ERROR, ISSET_CHECK,'in'),   # 是否处理
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        !empty($params['start_time']) && empty($params['end_time']) && $where['up.create_time'] = array('gt',$params['start_time']);
        !empty($params['end_time']) && empty($params['start_time']) && $where['up.create_time'] = array('lt',$params['end_time']);
        !empty($params['end_time']) && !empty($params['start_time']) && $where['up.create_time'] = array('between',array($params['start_time'],$params['end_time']));
        !empty($params['type']) && $where['um.invite_from'] = $params['type'];
        !empty($params['status']) && $where['up.status'] = $params['status'];
        $fields = "up.*,um.name,um.invite_from,um.mobile,uf.solver,uf.solve_content,uf.create_time as solve_time";
        $order  = "uf.status asc,up.create_time desc";

        $params['fields']      = $fields;
        $params['order']       = $order;
        $params['where']       = $where;
        $params['center_flag'] = SQL_UC;
        $params['sql_flag']    = 'feedback_list';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $list_res = $this->invoke($apiPath, $params);
        if ($list_res['status'] != 0) {
            return $this->res(NULL, $list_res['status']);
        }
        return $this->res($list_res['response']);
    }


    /**
     * Base.UserModule.User.Feedback.update
     * 问题反馈
     * @access public
     * @author Todor
     */

    public function update($params){
        $this->_rule = array(
            array('solve_content', 'require', PARAMS_ERROR, ISSET_CHECK),     # 解决内容
            array('solver', 'require', PARAMS_ERROR, MUST_CHECK),             # 解决者
            array('solver_uc_code', 'require', PARAMS_ERROR, ISSET_CHECK),    # 解决者编码
            array('feedback_code', 'require', PARAMS_ERROR, MUST_CHECK),      # 反馈编码
        );
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $problems = D('UcProblem')->where(array('feedback_code'=>$params['feedback_code']))->find();

        $solve    = D('UcFeedback')->where(array('feedback_code'=>$params['feedback_code']))->find();
        if(!empty($solve)){
            return $this->res(NULL,4064);  # 反馈已处理
        }

        //添加解决者信息
        $data = array(
            'feedback_code'=>$params['feedback_code'],
            'uc_code'=>$problems['uc_code'],
            'solver_uc_code'=>$params['solver_uc_code'],
            'solver'=>$params['solver'],
            'solve_content'=>$params['solve_content'],
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>'ENABLE',
            );

        $add_feed = D('UcFeedback')->add($data);

        if($add_feed === FALSE){
            return $this->res(NULL,4066);
        }

        // 修改问题记录
        $arr = array(
            'status'=>"DISABLE",
            );
        $update_problem = D('UcProblem')->where(array('feedback_code'=>$params['feedback_code']))->save($arr);
        if($update_problem === FALSE){
            return $this->res(NULL,4066);
        }

        return $this->res(true);

    }


}

?>
