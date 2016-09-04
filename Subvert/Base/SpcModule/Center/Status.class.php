<?php
/**
* +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
* +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 促销信息
*/

namespace Base\SpcModule\Center;

use System\Base;

class Status extends Base{
    private $_rule = null; # 验证规则列表
    private static $uc_prefix = null;

    public function __construct() {
        parent::__construct();
        self::$uc_prefix = 'Spc';
    }

    /**
     * 促销类型的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getType')->getType($type);
     * Base.SpcModule.Center.Status.getType
     * @param type $params
     * @return type
     */
    public function getType($type){
        switch($type) {
            case 'REWARD_GIFT':
                return "满赠";
            case 'SPECIAL':
                return "特价";
            case 'LADDER':
                return "阶梯价";
            default:
                return '';
        }
    }

    /**
 * 促销类型的映射
 * 调用方式   M('Base.SpcModule.Center.Status.getStrToType')->getStrToType($type);
 * Base.SpcModule.Center.Status.getType
 * @param type $params
 * @return type
 */
    public function getStrToType($type){
        switch($type) {
            case '满赠':
                return "REWARD_GIFT";
            case '特价':
                return "SPECIAL";
            case '阶梯价':
                return "LADDER";
            default:
                return '';
        }
    }
    /**
     * 优惠券活动条件的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getStrToCondition')->getStrToCondition($condition);
     * Base.SpcModule.Center.Status.getType
     * @param type $params
     * @return type
     */
    public function getStrToCondition($condition){
        switch($condition) {
            case '平台商城订单支付成功':
                return "FULL_BACK";
            default:
                return '';
        }
    }
    /**
     * 优惠券活动条件的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getConditionToStr')->getConditionToStr($condition);
     * Base.SpcModule.Center.Status.getType
     * @param type $params
     * @return type
     */
    public function getConditionToStr($condition){
        switch($condition) {
            case 'REGISTER':
                return "新用户注册";
            case 'FULL_BACK':
                return "平台商城订单支付成功";
            default:
                return '';
        }
    }
    /**
     * 优惠券活动条件的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getStrToRule')->getStrToRule($rule);
     * Base.SpcModule.Center.Status.getType
     * @param type $params
     * @return type
     */
    public function getStrToRule($rule){
        switch($rule) {
            case '一次发放所有优惠券':
                return "ONE_TIME";
            default:
                return '';
        }
    }
    /**
     * 优惠券活动条件的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getRuleToStr')->getRuleToStr($rule);
     * Base.SpcModule.Center.Status.getType
     * @param type $params
     * @return type
     */
    public function getRuleToStr($rule){
        switch($rule) {
            case 'ONE_TIME':
                return "一次发放所有优惠券";
            default:
                return '';
        }
    }
    /**
     * 优惠券活动条件的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getActiveStatus')->getActiveStatus($status);
     * Base.SpcModule.Center.Status.getType
     * @param type $params
     * @return type
     */
    public function getActiveStatus($status,$start_time,$end_time){
        switch($status) {
            case 'DRAFT':
                return "未上线";
            case 'PREHEAT':
                if($start_time>NOW_TIME){
                    return "预热中";
                }
                if($end_time<NOW_TIME){
                    return '已下线';
                }
                if($start_time<NOW_TIME && NOW_TIME<$end_time){
                    return '进行中';
                }
            case 'PUBLISH':
                if($end_time<NOW_TIME){
                    return '已下线';
                }
                if($start_time<NOW_TIME && NOW_TIME<$end_time){
                    return '进行中';
                }
                if($start_time>NOW_TIME){
                    return '预热中';
                }
            case 'END':
                return "已下线";
            default:
                return '';
        }
    }
    /**
     * 促销状态的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getStatus')->getStatus($status);
     * Base.SpcModule.Center.Status.getStatus
     * @param type $params
     * @return type
     */
    public function getStatus($status,$start_time,$end_time){
        switch($status) {
            case 'DRAFT':
                return "草稿";
            case 'END':
                return "已结束";
            case 'PUBLISH':
                if($end_time<NOW_TIME){
                    return '已结束';
                }
                if($start_time<NOW_TIME && NOW_TIME<$end_time){
                    return '促销中';
                }
                if($start_time>NOW_TIME){
                    return "预热中";
                }
            case 'DELETE':
                return "已删除";
            default:
                return '';
        }
    }
    /**
     * 促销状态的映射
     * 调用方式   M('Base.SpcModule.Center.Status.getMaxBuy')->getMaxBuy($max_buy);
     * Base.SpcModule.Center.Status.getMaxBuy
     * @param type $params
     * @return type
     */
    public function getMaxBuy($max_buy){
        if($max_buy>0){
            return '单店限购'.$max_buy;
        }else{
            return null;
        }
    }
    //获取促销的前置操作状态
    public function prev($params){
        $this->_rule = array(
            array('opera_status', 'require', PARAMS_ERROR, ISSET_CHECK),  //操作状态
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        };
       if($params['opera_status']==SPC_STATUS_PUBLISH){
           if($params['sic_code']){
               $params['sic_code']=array_unique($params['sic_code']);
               $spc_code=array();
               foreach($params['sic_code'] as $key=>$val){
                   $where['sic_code']=$val;
                   $where['status']=array('eq',SPC_STATUS_PUBLISH);
                   $where['end_time']=array('gt',NOW_TIME);
                   $result = D('SpcList')->where($where)->select();
                   if($result){
                       $where['sic_code']=$val;
                       $where['status']   = array('eq',SPC_STATUS_DRAFT);
                       $res=D('SpcList')->field('spc_code')->where($where)->select();
                       $res=array_column($res,'spc_code');
                       $spc_code[]=$res;
                   }
               }
               foreach($params['spc_codes'] as $key=>$val){
                   foreach($spc_code as $v){
                       if(in_array($val,$v)){
                           unset($params['spc_codes'][$key]);
                       }
                   }
               }
               if(!$params['spc_codes']){
                   return $this->endInvoke('',7026);
               }
               $data=array(
                   'spc_code'=>$params['spc_codes'],
                   'status'=>SPC_STATUS_DRAFT,
               );
           }
               return $data;
       }elseif($params['opera_status']==SPC_STATUS_END){
               $data=array('status'=>SPC_STATUS_PUBLISH);
              return $data;
       }elseif($params['opera_status']==SPC_STATUS_DELETE){
            $data=array('status'=>SPC_STATUS_DRAFT);
             return $data;
       }
    }

    //设置促销的促销状态
    public function setStatus($params){
        $this->_rule = array(
            array('opera_status', 'require', PARAMS_ERROR, ISSET_CHECK),  //操作状态
            array('spc_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //促销编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),   //商家编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        };
        //通过前置状态的返回值拼装where条件
        $res=$this->prev($params);
        if($res){
            $where['status']=array('eq',$res['status']);
        }else{
            return $this->res(null,7025);
        }
         $where['spc_code']=array('in',$params['spc_codes']);
         if($res['spc_code']){
            $where['spc_code']=array('in',$res['spc_code']);
        }
         $where['sc_code']=array('eq',$params['sc_code']);
        //组装要更新的数据
        $data['status']=$params['opera_status'];
        $data['update_time']=NOW_TIME;

        $res=D('SpcList')->where($where)->save($data);
        if($res===false){
          return $this->res(null,7011);
        }
        return $this->res($res);
    }

}
