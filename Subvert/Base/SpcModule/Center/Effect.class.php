<?php 

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: Todor <nielei@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 促销效果 (BASE)
 */

namespace Base\SpcModule\Center;

use       System\Base;

class Effect extends Base {

	private $_rule	=	null;

    public function __construct() {
        parent::__construct();
	}


	/**
	 * 促销效果查询列表
	 * Base.SpcModule.Center.Effect.lists
	 * @access public
	 * @return array
	 */

	public function lists($params){

		$this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),           #店铺编码
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),             #当前页数
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      #分页数
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),       #开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),		 #结束时间
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK),		 #业务员ID
            array('customer', 'require', PARAMS_ERROR, ISSET_CHECK),         #客户姓名
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK),             #商品名称或编码
            array('order', 'require', PARAMS_ERROR, ISSET_CHECK),            #排序   (all_number,all_price)
            array('spc_type','require',PARAMS_ERROR, ISSET_CHECK)            #促销类型
        );

        if (!$this->checkInput($this->_rule, $params)) {                 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $sc_code     = $params['sc_code'];
        $page        = $params['page'];                      
        $page_number = $params['page_number'];
        $start_time  = $params['start_time'];
        $end_time    = $params['end_time'];
        $salesman_id = $params['salesman_id'];
        $customer    = $params['customer'];
        $name 		 = $params['name'];
        $order 		 = $params['order'];
        $spc_type    = $params['spc_type'];

        //查询条件赋值
        $order = empty($order) ? 'all_number desc' : $order.' desc';

        $where['sl.sc_code']    = $sc_code;             #该店铺的促销信息
        $where['obog.spc_code'] = array('neq','');      #商品促销信息不为空

        !empty($salesman_id) && $where['obo.salesman_id'] = $salesman_id;
        !empty($customer)    && $where['obo.client_name'] = $customer;

        // 订单的下单时间
        !empty($start_time)  && empty($end_time) && $where['obo.create_time'] = array('egt', $start_time);
        !empty($end_time)    && empty($start_time) && $where['obo.create_time'] = array('elt', intval($end_time)+86399);
        !empty($start_time)  && !empty($end_time) && $where['obo.create_time'] = array('between', array($start_time, intval($end_time)+86399));

        // 促销的开始结束时间
        !empty($end_time) && $where['sl.start_time'] = array('elt',intval($end_time)+86399);
        !empty($start_time) && $where['sl.end_time'] = array('egt',$start_time);
       
       
        //判断商品名称或商品编码查询
        if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$name)){          
           !empty($name) && $where['obog.goods_name']   = array('like','%'.$name.'%');
        }else{
           !empty($name) && $where['obog.sic_no']       = array('like','%'.$name.'%');
        }


        //促销效果的订单状态
        $order_status_where = array(
        	   array('obo.pay_status'=>OC_ORDER_PAY_STATUS_PAY,'obo.pay_method'=>array('neq',PAY_METHOD_OFFLINE_COD)),
        	   array('obo.order_status'=>array('not in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL)),'obo.pay_method'=>PAY_METHOD_OFFLINE_COD),
        	   '_logic'=>'or',
        	);
        $where['_complex'] = $order_status_where;


        //促销信息状态 促销 结束
        $time   = NOW_TIME;
        $where['sl.status']     = array('in',array(SPC_STATUS_PUBLISH,SPC_STATUS_END));
        $where['sl.start_time'] = array('lt',NOW_TIME);

        //存在业务员时候，每个客户的统计都出来而且字段加入客户与业务员
        $fields = '';

        if(empty($customer) && empty($salesman_id)){
        	$group = 'obog.spc_code';
        }else if(empty($customer) && !empty($salesman_id)){
        	$group  = 'obog.spc_code';
            $fields.= 'obo.salesman,';
        }else if(!empty($customer)){
            $group  = 'obog.spc_code,obo.uc_code';
            $fields.= 'obo.salesman,obo.client_name,';
        }

        // 促销类型查询
        empty($spc_type) ? $where['sl.type'] = array('in',array(SPC_TYPE_GIFT,SPC_TYPE_SPECIAL,SPC_TYPE_LADDER)) : $where['sl.type'] = array('in',$spc_type);
        

        $fields .= 'sl.max_buy,obog.category_end_id,obog.ori_goods_price as goods_price,obog.goods_img,obog.packing,obog.sic_code,obog.spc_code,obo.b2b_code,obog.sic_no,obog.goods_name,obog.spec,obog.packing,sl.start_time,sl.end_time,sl.type,SUM(obog.goods_number) AS all_number,SUM(obog.goods_price*obog.goods_number) AS all_price,obo.uc_code,obog.ladder_rule';
        $params['page']		   = $page;
        $params['page_number'] = $page_number;
        $params['fields']      = $fields;
        $params['where']       = $where;
        $params['order']       = $order;
        $params['group']       = $group;
        $params['center_flag'] = SQL_SPC;
        $params['sql_flag']    = 'effect_lists';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $lists_res = $this->invoke($apiPath,$params);
       	
        if ($lists_res['status'] != 0) {
            return $this->res(NULL, $lists_res['status']);
        }
        return $this->res($lists_res['response']);

	}



    /**
     * 促销效果查询列表导出
     * Base.SpcModule.Center.Effect.export 
     * @access public
     * @return array
     */

    public function export($params){
        protected
        function require_once "";()
        {

}
        $this->_rule = array(
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),           # 页码               
            array('title', 'require', PARAMS_ERROR, ISSET_CHECK),           # 导出提现查询文件头
            array('filename', 'require', PARAMS_ERROR, ISSET_CHECK),        # 文件名
            array('callback_api', 'require', PARAMS_ERROR, ISSET_CHECK),    # 导出数据回调api
            array('sql_flag', 'require', PARAMS_ERROR, ISSET_CHECK),        # sql的标识
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),          # 店铺编码
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),      # 开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),        # 结束时间
            array('salesman_id', 'require', PARAMS_ERROR, ISSET_CHECK),     # 业务员ID
            array('customer', 'require', PARAMS_ERROR, ISSET_CHECK),        # 客户姓名
            array('name', 'require', PARAMS_ERROR, ISSET_CHECK),            # 商品名称或编码
            array('order', 'require', PARAMS_ERROR, ISSET_CHECK),           # 排序   (all_number,all_price)
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),            # 导出所有,详细 (all,detail)
            array('special','require',PARAMS_ERROR, ISSET_CHECK),           # 特价
            array('reward_gift','require',PARAMS_ERROR, ISSET_CHECK),       # 满赠
            array('ladder','require',PARAMS_ERROR, ISSET_CHECK),            # 满赠
        );
    
        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        };

        $sc_code        =  $params['sc_code'];
        $type           =  $params['type'];

        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];

        $start_time     =  strtotime($params['start_time']);
        $end_time       =  strtotime($params['end_time']);
        $salesman_id    =  $params['salesman_id'];
        $customer       =  $params['customer'];
        $name           =  trim($params['name']);
        $order          =  $params['order'];
        $special        =  $params['special'];
        $reward_gift    =  $params['reward_gift'];
        $ladder         =  $params['ladder'];
        !empty($special)      && $spc_type[] = $special;
        !empty($reward_gift ) && $spc_type[] = $reward_gift;
        !empty($ladder)       && $spc_type[] = $ladder;


        //默认参数 类型不同 显示不同
        if($type == 'all'){
            $default_title   =  array('促销编号','促销品编码','促销品名称','促销品规格','促销标题','促销开始时间','促销结束时间','促销方式','销售总量','销售总额（元）','促销规则','赠品编码','赠品名称','赠品规格','赠送数量','原价','优惠价');  
            $default_fields  =  'obog.spc_code,obog.sic_no,obog.goods_name,obog.spec,sl.spc_title,sl.start_time,sl.end_time,sl.type,SUM(obog.goods_number) AS all_number,SUM(obog.goods_price*obog.goods_number) AS all_price,obo.b2b_code,obog.packing,obog.ori_goods_price as goods_price,obo.uc_code,obog.ladder_rule';      
        }else{
            $default_title   =  array('促销编号','业务员','客户','促销品编号','促销品名称','促销品规格','促销标题','促销开始时间','促销结束时间','促销方式','销售总量','销售总额（元）','促销规则','赠品编码','赠品名称','赠品规格','赠送数量','原价','优惠价');  
            $default_fields  =  'obog.spc_code,obo.salesman,obo.client_name,obog.sic_no,obog.goods_name,obog.spec,sl.spc_title,sl.start_time,sl.end_time,sl.type,SUM(obog.goods_number) AS all_number,SUM(obog.goods_price*obog.goods_number) AS all_price,obo.b2b_code,obog.packing,obog.ori_goods_price as goods_price,obo.uc_code,obog.ladder_rule';            
        }
        $default_filename     =  '促销效果列表';
        $default_sql_flag     =  'effect_lists'; 
        $default_callback_api =  "Com.Callback.Export.SpcExport.effect";

        // 在字段中添加客户与业务员
        if($type == 'all'){
            if(!empty($salesman_id) && empty($customer)){                      # 只有业务员

                $default_title[] = '业务员';
                $default_fields .= ",obo.salesman"; 
            }elseif(!empty($customer)){                                        # 存在客户
                
                $default_title[] = '业务员';
                $default_title[] = '客户';
                $default_fields .= ",obo.salesman,obo.client_name";
            }

        }


        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_callback_api : $callback_api;
        $sql_flag       =  empty($sql_flag) ? $default_sql_flag : $sql_flag;

        if(in_array($order, array('all_number','all_price')) || empty($order)){
            $order          =  empty($order) ? 'obog.spc_code desc,all_number desc' : 'obog.spc_code desc,'.$order.' desc';
        } 

        //组装where 条件
        $where['obo.sc_code']   = $sc_code;             #该店铺的促销信息
        $where['obog.spc_code'] = array('neq','');      #商品促销信息不为空

        !empty($salesman_id) && $where['obo.salesman_id'] = $salesman_id;
        !empty($customer)    && $where['obo.client_name'] = $customer;
        !empty($start_time)  && empty($end_time) && $where['obo.create_time'] = array('egt', $start_time);
        !empty($end_time)    && empty($start_time) && $where['obo.create_time'] = array('elt', intval($end_time)+86399);
        !empty($start_time)  && !empty($end_time) && $where['obo.create_time'] = array('between', array($start_time, intval($end_time)+86399));

       
       
        //判断商品名称或商品编码查询
        if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$name)){          
           !empty($name) && $where['obog.goods_name']   = array('like','%'.$name.'%');
        }else{
           !empty($name) && $where['obog.sic_no']       = array('like','%'.$name.'%');
        }

        

        //促销效果的订单状态
        $order_status_where = array(
               array('obo.pay_status'=>OC_ORDER_PAY_STATUS_PAY,'obo.pay_method'=>array('neq',PAY_METHOD_OFFLINE_COD)),
               array('obo.order_status'=>array('not in',array(OC_ORDER_ORDER_STATUS_CANCEL,OC_ORDER_ORDER_STATUS_MERCHCANCEL)),'obo.pay_method'=>PAY_METHOD_OFFLINE_COD),
               '_logic'=>'or',
            );
        $where['_complex'] = $order_status_where;
        

        //促销信息状态 促销 结束
        $time   = NOW_TIME;
        $where['sl.status']     = array('in',array(SPC_STATUS_PUBLISH,SPC_STATUS_END));   
        $where['sl.start_time'] = array('lt',NOW_TIME);


        //默认参数 类型不同 显示不同
        if($type == 'all'){
            if(!empty($customer)){
                $group = 'obog.spc_code,obo.uc_code';
            }else{
                $group = 'obog.spc_code';
            }
            
        }else{
            $group = 'obog.spc_code,obo.uc_code';
        }

        // 促销类型查询
        empty($spc_type) ? $where['sl.type'] = array('in',array(SPC_TYPE_GIFT,SPC_TYPE_SPECIAL,SPC_TYPE_LADDER)) : $where['sl.type'] = array('in',$spc_type);


        //组装调用导出api参数
        $params                 =  array();
        $params['where']        =  $where;
        $params['fields']       =  $default_fields;
        $params['title']        =  $title;
        $params['filename']     =  $filename;
        $params['callback_api'] =  $callback_api;
        $params['order']        =  $order;
        $params['center_flag']  =  SQL_SPC;
        $params['sql_flag']     =  $sql_flag;
        $params['group']        =  $group;
        $params['type']         =  $type;    
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);

    }


}    

















 ?>