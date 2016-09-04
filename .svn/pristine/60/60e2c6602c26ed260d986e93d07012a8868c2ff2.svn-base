<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | cms商家统计相关的操作
 */
namespace Base\StoreModule\Store;

use System\Base;


class Statistic extends Base{

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
    /**
     * Base.StoreModule.Store.Statistic.export
     * @param type $params
     * @return type
     */
    public function export($params){
        $this->_rule = array(
            array('salesman', 'require', PARAMS_ERROR, ISSET_CHECK), //双磁对接人
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取业务参数
        $salesman=$params['salesman'];
        $sc_code      =$params['sc_code'];
        $start_time   =  empty($params['start_time']) ? 0 : strtotime($params['start_time']);
        $end_time     =  empty($params['end_time'])   ? time() : strtotime($params['end_time']);

        //默认参数
        $default_title=array('卖家店铺','卖家联系人','卖家手机号','买家店铺','买家姓名','买家手机号','成交单数','成交金额(元)','已付金额(元)','买家注册时间','城市','区/县','买家店铺注册地址','双磁对接人');
//        $default_title=array('开始时间',$start_time,'结束时间',$end_time);
        $default_fields='ss.sc_code,ss.name,ss.linkman,ss.phone,uc.uc_code,uc.mobile,uc.create_time,um.salesman';
        $default_filename='卖家统计导出列表';
        $default_sql_flag   =  'store_list';
        $default_api        =  'Com.Callback.Export.ScExport.CustomerList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        //组装where条件
        $where=array();
        $salesman ? $where['um.salesman']=$salesman : null;
        $where['ss.sc_code'] = array('not in',array(1010000000077));
        $sc_code       ? $where['ss.sc_code']       =array('eq',$sc_code)      : null;
        $where['uc.create_time']=array('between',array($start_time,$end_time));
        $data['where']        =  $where;
        $data['fields']       =  $default_fields;
        $data['title']        =  $title;
        $data['center_flag']  =  SQL_SC;//订单中心
        $data['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $data['filename']     =  $filename;
        $data['callback_api'] = $callback_api;
//        $data['template_call_api']='Com.Callback.Export.Template.statistic';
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }

    /**
     * Base.StoreModule.Store.Statistic.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        $this->_rule = array(
            array('salesman', 'require', PARAMS_ERROR, ISSET_CHECK), //双磁对接人ID
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('deal', 'require', PARAMS_ERROR, ISSET_CHECK), //判断是不是得到成交金额
            array('is_page', array('YES','NO'), PARAMS_ERROR, ISSET_CHECK, 'in'), //是否分页
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取平台标识
        $salesman=trim($params['salesman']);
        $sc_code    =trim($params['sc_code']);
        if($params['flag']=='deal'){
            $fields='ss.name,sum(obo.real_amount) as deal_amount,ss.sc_code,um.salesman,count(ss.sc_code) as order_amount';
        }
        if($params['flag']=='pay'){
            $fields='ss.name,sum(obo.real_amount) as pay_amount,ss.sc_code,um.salesman';
        }

        //组装where条件
        $where=array();
        $order='obo.update_time desc';
        $group_by='obo.sc_code';
        if($params['flag']=='deal'){
            $arr[]=array('pay_type'=>'ONLINE','pay_status'=>'PAY');
            $arr[]=array('pay_type'=>array('neq','ONLINE'),'ship_status'=>array('neq','UNSHIP'));
            $arr['_logic']='or';
            $where['_complex']=$arr;
        }
        if($params['flag']=='pay'){
            $where['pay_status']=array('eq',OC_ORDER_PAY_STATUS_PAY);
        }
        $salesman ? $where['um.salesman']=array('eq',$salesman) : null;
        $where['obo.sc_code'] = array('not in',array(1010000000077));
        $sc_code     ? $where['obo.sc_code']   =array('eq',$sc_code)   : null;
//
        if($params['is_page']=='NO'){
            $where=D()->parseWhereCondition($where);
            $sql="SELECT
                               {$fields}
                          FROM
                               {$this->tablePrefix}sc_store ss
                          LEFT JOIN
                               {$this->tablePrefix}uc_merchant  um ON ss.merchant_id=um.id
                          LEFT JOIN
                                {$this->tablePrefix}oc_b2b_order obo ON ss.sc_code=obo.sc_code
                                {$where}
                          GROUP BY {$group_by}

                         order by {$order}";
            $res=D()->query($sql);
            return $this->res($res);
        }
        # 默认值
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $data['aggre']=array(array('','ss.sc_code','sc_code'));
        $data['page']=$page;
        $data['page_number']=$pageNumber;
        $data['order']=$order;
        $data['group']=$group_by;
        $data['where']=$where;
        $data['fields']=$fields;
        $data['sql_flag']='statistic_list';
        $data['center_flag']=SQL_SC;

        $api_Path='Com.Common.CommonView.Lists.Lists';
        $call=$this->invoke($api_Path,$data);
        if($call['status']!==0){
            $this->res(null,$call['status'],'',$call['message']);
        }
        return $this->res($call['response']);
    }

    /**
     * Base.StoreModule.Store.Statistic.Customer
     * @param type $params
     * @return type
     */
    public function Customer($params){
        $this->_rule = array(
            array('sc_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //商家编码
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),   //小b用户店铺注册时间查找开始时间
            array('end_time', 'require' , PARAMS_ERROR, ISSET_CHECK),   //小b用户店铺注册时间查找结束时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $fields='count(*) as customer_amount,ss.sc_code,uc.create_time';
        //组装where条件
        $where['ss.sc_code']=array('in',$params['sc_codes']);
        $where['uc.create_time']=array('between',array($params['start_time'],$params['end_time']));
        $group='sc_code';

        $where=D()->parseWhereCondition($where);
        $sql="SELECT
                               {$fields}
                          FROM
                               {$this->tablePrefix}sc_store ss
                          LEFT JOIN
                               {$this->tablePrefix}uc_customer  uc ON ss.sc_code=uc.sc_code
                       {$where}
                       GROUP BY {$group}
                         ";
        $res=D()->query($sql);
        return $this->res($res);
    }
    /**
     * Base.StoreModule.Store.Statistic.Customer
     * @param type $params
     * @return type
     */
    public function valid_customer($params){
        $this->_rule = array(
            array('sc_codes', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'), //商家编码
            array('start_time', 'require' , PARAMS_ERROR, ISSET_CHECK),   //小b用户店铺注册时间查找开始时间
            array('end_time', 'require' , PARAMS_ERROR, ISSET_CHECK),   //小b用户店铺注册时间查找结束时间
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $sc_codes=$params['sc_codes'];
        $start_time=$params['start_time'];
        $end_time=$params['end_time'];
        //先得到符合条件的所有uc_code
        $map['sc_code']=array('in',$sc_codes);
        !empty($start_time) && empty($end_time) && $map['create_time'] = array('egt', $start_time);
        !empty($end_time) && empty($start_time) && $map['create_time'] = array('elt', $end_time);
        !empty($start_time) && !empty($end_time) && $map['create_time'] = array('between', array($start_time, $end_time));
        $uc_code=D('UcCustomer')->field('uc_code')->where($map)->select();
        $uc_code=array_column($uc_code,'uc_code');
        $valid_customer=array();
        if(!$uc_code) {
            foreach ($sc_codes as $k => $v) {
                $valid_customer[$v] = 0;
            }
            return $this->res($valid_customer);
        }
        //得到所有的成单
        $field='uc_code';
        $where=array();
        $where['uc_code']=array('in',$uc_code);
        $where['sc_code']=array('in',$sc_codes);
        $arr[]=array('pay_type'=>'ONLINE','pay_status'=>'PAY');
        $arr[]=array('pay_type'=>array('neq','ONLINE'),'ship_status'=>'SHIPPED');
        $arr['_logic']='or';
        $where['_complex']=$arr;
        $group='uc_code';
//        !empty($start_time) && empty($end_time) && $where['uc.create_time'] = array('egt', $start_time);
//        !empty($end_time) && empty($start_time) && $where['uc.create_time'] = array('elt', $end_time);
//        !empty($start_time) && !empty($end_time) && $where['uc.create_time'] = array('between', array($start_time, $end_time));
//var_dump($sc_codes);exit;
       $res=D('OcB2bOrder')->field('count(uc_code) as num,uc_code,b2b_code,sc_code')->where($where)->group($group)->select();
//        $res=changeArrayIndex($res,'sc_code');

        foreach($sc_codes as $key=>$val){
            foreach($res as $kk=>$vv){
                if($vv['sc_code']==$val){
//                    echo $val;echo '<br/>';
                    $valid_customer[$val]=$valid_customer[$val]+1;
                }
            }
        }
        foreach($sc_codes as $key=>$val){
            if($valid_customer[$val]){
                $valid_customer[$val]=$valid_customer[$val];
            }else{
                $valid_customer[$val]=0;
            }
        }
        return $this->res($valid_customer);
    }
}

