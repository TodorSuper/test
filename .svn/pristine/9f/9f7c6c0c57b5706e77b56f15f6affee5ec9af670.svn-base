<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * |优惠券相关
 */


namespace Base\SpcModule\Coupon;

use System\Base;


class Active extends Base
{

    public function __construct(){
        parent::__construct();
    }

    /**
     * Base.SpcModule.Coupon.Active.add
     * @param [type] $params [description]
     */
    public function add($params)
    {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('active_name', 'require', PARAMS_ERROR, MUST_CHECK),#活动名称
            array('condition', 'require', PARAMS_ERROR, MUST_CHECK),#活动条件
            array('rule', 'require', PARAMS_ERROR, MUST_CHECK),#活动规则
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券活动开始时间
            array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券结束时间
            array('policy', 'require', PARAMS_ERROR, ISSET_CHECK),#活动策略
            array('active_banner', 'require', PARAMS_ERROR, ISSET_CHECK),#活动封面
            array('desc', 'require', PARAMS_ERROR, ISSET_CHECK),#活动详情
            array('is_store_show', 'require', PARAMS_ERROR, ISSET_CHECK),#是否在商城首页显示
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
//        $condition_flag = M('Base.SpcModule.Center.Status.getStrToCondition')->getStrToCondition($params['condition']);
        $condition_flag = $params['condition'];
        $rule_flag =  M('Base.SpcModule.Center.Status.getStrToRule')->getStrToRule($params['rule']);
        $start_time =$params['start_time'];
        $end_time = $params['end_time'];
        $policy = $params['policy'];
        $active_banner = $params['active_banner'];
        $desc = $params['desc'];
        $show = $params['is_store_show'];
        //查询该时间段内有没有"新用户注册"的活动
        $data = array(
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'flag'=>$condition_flag,
        );
        $result = $this->_getActive($data);
        if($result){
            return $this->res('',7069);
        }


        //生成优惠券活动编码
        $codeData = array(
            "busType"    => SPC_COUPON_CODE,
            "preBusType" => SPC_COUPON_ACTIVE,
            "codeType"   => SEQUENCE_SPC
        );

        $activeCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $codeData);
        if( $activeCode['status'] !== 0) {
            return $this->res('', 7070);
        }
        //组装添加到活动表中的数据
        $data = array(
            'active_code'=>$activeCode['response'],
            'active_name'=>$params['active_name'],
            'condition_flag'=>$condition_flag,
            'rule_flag'=>$rule_flag,
            'active_banner'=>$active_banner ? $active_banner : '',
            'desc'=>$desc ? $desc : '',
            'is_store_show'=>$show ? $show : 'NO',
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'create_time'=>NOW_TIME,
            'update_time'=>NOW_TIME,
            'status'=>SPC_STATUS_DRAFT
        );
        $status = D('SpcActive')->add($data);
        if($status === false || $status<=0){
            return $this->res('',7071);
        }
        //如果是满返活动则往另一张表中添加数据
        if($condition_flag == 'FULL_BACK'){
          foreach($policy as $key=>$val){
            $full_data[] = array(
                'full_money'=>$val,
                'active_code'=>$activeCode['response'],
                'create_time'=>NOW_TIME,
                'update_time'=>NOW_TIME,
                'status'=>'ENABLE',
            );
          }
            $res = D('SpcActiveFullBack')->addAll($full_data);
            if($res === false){
                return $this->res('',7106);
            }
        }
        return $this->res(true);
    }

    //查询该时间段内存不存在该机制的活动
    private function _getActive($data){
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $flag = $data['flag'];
        $map[] = array('end_time'=>array('egt',$start_time),'start_time'=>array('elt',$start_time));
        $map[] = array('end_time'=>array('lt',$end_time),'start_time'=>array('egt',$start_time));
        $map[] = array('end_time'=>array('gt',$end_time),'start_time'=>array('elt',$end_time));
        $map['_logic'] = 'or';
        $where['_complex'] = $map;
        $where ['condition_flag'] = $flag;
        $where ['status'] = ['neq','END'];
        if($data['active_code']){
            $where['active_code'] = ['neq',$data['active_code']];
        }
        $result = D('SpcActive')->where($where)->select();
        return $result;
    }

    /**
     *Base.SpcModule.Coupon.Active.update
     * @param [type] $params [description]
     */
    public function update($params){
//        var_dump($params);exit;
        $this->startOutsideTrans();
        $this->_rule = array(
            array('active_code', 'require', PARAMS_ERROR, MUST_CHECK),#活动ID
            array('condition', 'require', PARAMS_ERROR, MUST_CHECK),#活动条件
            array('active_name', 'require', PARAMS_ERROR, MUST_CHECK),#活动名称
            array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券活动开始时间
            array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),#优惠券结束时间
            array('policy', 'require', PARAMS_ERROR, ISSET_CHECK),#活动策略
            array('policy_help', 'require', PARAMS_ERROR, ISSET_CHECK),#要更新的活动策略
            array('active_banner', 'require', PARAMS_ERROR, ISSET_CHECK),#活动封面
            array('desc', 'require', PARAMS_ERROR, ISSET_CHECK),#活动详情
            array('is_store_show', 'require', PARAMS_ERROR, ISSET_CHECK),#是否在商城首页显示
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
//        $condition_flag = M('Base.SpcModule.Center.Status.getStrToCondition')->getStrToCondition($params['condition']);
        $condition_flag = $params['condition'];
        $policy = $params['policy'];
        $policy_help = $params['policy_help'];
        //查询该活动存不存在
        $info = D('SpcActive')->where(['active_code'=>$params['active_code']])->find();
        if(!$info){
            return $this->res('',7078);
        }
        //查询该时间段内有没有"新用户注册"的活动
        $data = array(
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'flag'=>$condition_flag,
            'active_code'=>$params['active_code']
        );
        $result = $this->_getActive($data);
        if($result){
            return $this->res('',7069);
        }
        //得到该活动要更新的状态
        $update_status = $this->_getUpdateStatus($params['active_code']);
        //拼接要更新的字段
        $data = [
            'active_name'=>$params['active_name'],
            'active_banner'=>$params['active_banner'],
            'desc'=>$params['desc'],
            'is_store_show'=>$params['is_store_show'],
            'start_time'=>$params['start_time'],
            'end_time'=>$params['end_time'],
            'status'=>$update_status ? $update_status : SPC_STATUS_DRAFT,
        ];
        $res = D('SpcActive')->where(['active_code'=>$params['active_code']])->save($data);
        if($res === false){
            return $this->res('',7077);
        }
        if($condition_flag == 'FULL_BACK'){
            //添加新的策略
            foreach($policy_help as $key=>$val){
                if(strpos($val,'-')){
//                    var_dump($val);exit;
                    $arr = explode('-',$val);
                    $id = $arr[1];
                    $value = $arr[0];
                    $full_data = array(
                        'active_code'=>$params['active_code'],
                        'full_money'=>$value,
                        'create_time'=>NOW_TIME,
                        'update_time'=>NOW_TIME,
                        'status'=>'ENABLE',
                    );
                    $res = D('SpcActiveFullBack')->where(['id'=>$id])->save($full_data);
//                    echo D()->getLastSql();
                    if($res === false){
                        return $this->res('',7106);
                    }
                }else{
                    $full_data = array(
                        'active_code'=>$params['active_code'],
                        'full_money'=>$val,
                        'create_time'=>NOW_TIME,
                        'update_time'=>NOW_TIME,
                        'status'=>'ENABLE',
                    );
                   $res =  D('SpcActiveFullBack')->add($full_data);
                    if($res === false){
                        return $this->res('',7106);
                    }
                }
            }

        }
        # 记录数据版本(通用)
        $dataVersion = array(
            'mainTable' => 'SpcActive',
            'versionTable' => 'SpcActiveVersion',
            'where' => array('active_code'=>$params['active_code']),
        );

        $updateversion = $this->invoke('Com.Common.DataVersion.Mysql.add', $dataVersion);
        if($updateversion['status'] !== 0 ) {
            return $this->res($updateversion['response'], $updateversion['status']);
        }
        return $this->res(true);
    }

    private function _getUpdateStatus($id){
        //查询出更改前的状态
        $info = D('SpcActive')->field('start_time,end_time,status')->where(['active_code'=>$id])->find();
        $status = $info['status'];
        $end_time = $info['end_time'];
        if(($status == SPC_STATUS_PUBLISH  && $end_time<NOW_TIME) || $status == SPC_STATUS_END ){
            return SPC_STATUS_END;
        }else{
            return SPC_STATUS_DRAFT;
        }
    }
    //上线的前置条件
    private function _getLine($data){
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $flag = $data['condition_flag'];
        $map[] = array('end_time'=>array('egt',$start_time),'start_time'=>array('elt',$start_time));
        $map[] = array('end_time'=>array('lt',$end_time),'start_time'=>array('egt',$start_time));
        $map[] = array('end_time'=>array('gt',$end_time),'start_time'=>array('elt',$end_time));
        $map['_logic'] = 'or';
        $arr[] =array('status'=>SPC_STATUS_PUBLISH,'start_time'=>array('gt',NOW_TIME));
        $arr[] = array('status'=>SPC_STATUS_PUBLISH,'start_time'=>array('lt',NOW_TIME),'end_time'=>array('gt',NOW_TIME));
        $arr['_logic'] = 'or';
        $where = array($map,$arr,'_logic'=>'and');
        $where ['condition_flag'] = $flag;

        if($data['id']){
            $where['id'] = ['neq',$data['id']];
        }
        $result = D('SpcActive')->where($where)->select();
        return $result;
    }
    /**
     *Base.SpcModule.Coupon.Active.setLine
     * @param [type] $params [description]
     */
    public function setLine($params){
        $this->_rule = array(
            array('active_code', 'require', PARAMS_ERROR, MUST_CHECK),#促销活动编码
            array('flag', 'require', PARAMS_ERROR, MUST_CHECK),#操作标志
            array('condition_flag', 'require', PARAMS_ERROR, ISSET_CHECK),#活动条件标志
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $flag = $params['flag'];
        //先获取该促销活动的信息
        if($flag == 'online'){
            $map[] = ['status'=>SPC_STATUS_DRAFT];
            $map[] = ['status'=>SPC_STATUS_END];
            $map['_logic'] = 'or';
            $where['_complex']= $map;
            $where['active_code'] = $params['active_code'];
            $info = D('SpcActive')->where($where)->find();
        }elseif($flag == 'offline'){
            $where = [];
            $map[] = ['status'=>SPC_STATUS_PREHEAT];
            $map[] = ['status'=>SPC_STATUS_PUBLISH];
            $map['_logic'] = 'or';
            $where['_complex']= $map;
            $where['active_code'] = $params['active_code'];
            $info = D('SpcActive')->where($where)->find();

        }
        if(!$info){
            return $this->res('',7088);
        }
        //检测有没有预热中和进行中的活动与此个活动时间冲突
        $this_active_info = D('SpcActive')->where(['active_code'=>$params['active_code']])->find();
        $result = $this->_getLine($this_active_info);
        if($result){
            return $this->res('',7069);
        }
        if($flag == 'online'){
          $status = 'PUBLISH';
        }elseif($flag == 'offline'){
            $status = 'END';
        }
        //查找该条件下有没有已经上线的活动
        if($flag == 'online'){
//            $where = [];
//            $where['status'] = SPC_STATUS_PUBLISH;
//            $where['start_time'] = ['lt',NOW_TIME];
//            $where['end_time'] = ['gt',NOW_TIME];
//            $where['condition_flag'] = $params['condition_flag'];
//            $where['active_code'] = ['neq',$params['active_code']];
//            $res = D('SpcActive')->where($where)->find();
////            echo D()->getLastSql();exit;
//            if($res){
//                return $this->res('',7089);
//            }
            //查看该活动有没有创建优惠券
            $num = D('SpcActiveCouponRelation')->alias('sacr')->field('count(*) as num')->join("{$this->tablePrefix}spc_active_coupon sac on sacr.bat_code = sac.bat_code","inner")->where(['sacr.active_code'=>$params['active_code'],'sac.status'=>'ENABLE'])->find();
            if($num['num']<=0){
                return $this->res('',7097);
            }
        }
        //组装最后要更新的数据
        switch($flag){
            case 'online':
                $data = [
                    'status'=>$status,
                    'update_time'=>NOW_TIME,
                    'online_time'=>NOW_TIME
                ];
                break;
            case 'offline':
                $data = [
                    'status'=>$status,
                    'update_time'=>NOW_TIME,
                    'offline_time'=>NOW_TIME
                ];
                break;
            default :
        }
        $res = D('SpcActive')->where(['active_code'=>$params['active_code']])->save($data);
        if($res === false){
            return $this->res('',7098);
        }
        # 记录数据版本(通用)
        $dataVersion = array(
            'mainTable' => 'SpcActive',
            'versionTable' => 'SpcActiveVersion',
            'where' => array('active_code'=>$params['active_code']),
        );

        $updateversion = $this->invoke('Com.Common.DataVersion.Mysql.add', $dataVersion);
        if($updateversion['status'] !== 0 ) {
            return $this->res($updateversion['response'], $updateversion['status']);
        }
        return $this->res(true);
    }

    /**
     * 获取最近相应类型活动
     * Base.SpcModule.Coupon.Active.getRecent
     * @author Todor
     */

    public function getRecent($params){
        $this->_rule = array(
            array('flag', 'require', PARAMS_ERROR, MUST_CHECK),     # 或活动标识
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 查看对应活动标识有没有活动
        $fields = "active_code,active_name,condition_flag,rule_flag,start_time,end_time,status";
        $where['condition_flag'] = $params['flag'];
        $where['start_time'] = array('elt',NOW_TIME);
        $where['end_time'] = array('egt',NOW_TIME);
        $where['status'] = SPC_ACTIVE_STATUS_PUBLISH;
        $active_res = D('SpcActive')->field($fields)->where($where)->select();

        if(count($active_res) > 1){
            return $this->res(NULL,7083);       # 相同条件的优惠活动存在多个
        }

        if(empty($active_res)){                 # 没有活动
            return $this->res(true);
        }

        return $this->res($active_res);
    }

    /**
     * 删除活动策略
     * Base.SpcModule.Coupon.Active.deletePolicy
     * @author Todor
     */
    public function deletePolicy($params){
        $this->_rule = array(
            array('id', 'require', PARAMS_ERROR, MUST_CHECK),     # 活动策略对应的id
        );
        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $id = $params['id'];
        //查看该活动策略存不存在
        $info = D('SpcActiveFullBack')->where(['id'=>$id,'status'=>'ENABLE'])->find();
        if(!$info){
            return $this->res('',7088);
        }
        //查看该活动策略下有没有对应的优惠券,如果有则不允许删除
        $res = D('SpcActiveFullBack')->alias('safb')->join("{$this->tablePrefix}spc_active_coupon_full_relation sacfr on safb.id=sacfr.full_back_id","inner")->join("{$this->tablePrefix}spc_active_coupon sac on sacfr.bat_code=sac.bat_code","inner")->where(['sac.status'=>'ENABLE','safb.id'=>$id])->select();
        if($res){
            return $this->res('',7107);
        }
        //执行删除操作
        $result = D('SpcActiveFullBack')->where(['id'=>$id])->save(['status'=>'DISABLE','update_time'=>NOW_TIME]);
        if($result === false || $result<=0){
            return $this->res('',7110);
        }
        return $this->res(true);
    }
}