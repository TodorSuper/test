<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 促销信息
 */

namespace Base\SpcModule\Center;

use System\Base;

class Spc extends Base{

    /**
     * Base.SpcModule.Center.Spc.stock_export
     * @param type $params
     * @return type
     */
    public function stock_export($params){
        $this->_rule = array(
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),   //商家编码
            array('stock', 'require', PARAMS_ERROR, MUST_CHECK),   //商家编码
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //组装业务参数
        $sc_code=$params['sc_code'];

        //设置默认参数
        //默认参数
        $default_title=array('促销编码','商品编码','商品名称','商品库存','促销标题','促销开始时间','促销结束时间','促销方式','促销规则','赠品商品编码','赠品名称','赠品库存','促销状态','单店最大购买数量');
        $default_fields='isi.sic_no,isi.stock,isi.min_num,isi.price,sl.spc_code,sl.sic_code,sl.spc_title,sl.type,sl.start_time,sl.end_time,sl.status,ii.goods_name,ii.spec,ii.sub_name,ii.brand,ii.spec,ii.goods_img,ii.goods_img_new,ii.packing,ce.name AS category_name,sl.max_buy';
        $default_filename   =  '促销信息列表';
        $default_sql_flag   =  'spc_list';
        $default_order      =  'sl.id desc';
        $default_api        =  'Com.Callback.Export.SpcExport.stock_spcList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        //组装where参数
        $where=array();
        $where['sl.sc_code']=array('eq',$sc_code);
        $where['isi.stock']=array('elt',0);
        $where['isi.status']=array('eq',IC_STORE_ITEM_ON);

        //组装调用导出api参数
        $params['where']        =  $where;
        $params['fields']       =  $default_fields;
        $params['title']        =  $title;
        $params['center_flag']  =  SQL_SPC;//订单中心
        $params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $params['filename']     =  $filename;
        $params['order']        =  empty($order) ? $default_order : $order;
        $params['callback_api'] = $callback_api;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);

    }

    /**
     * Base.SpcModule.Center.Spc.export
     * @param type $params
     * @return type
     */
    public function export($params){

        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('spc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //促销中心编码
            array('sic_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商品编码
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK),   //商家编码
            array('spc_title', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销标题
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK),   //商品名称
            array('sort_type', 'require', PARAMS_ERROR, ISSET_CHECK),   //排序方式
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK),   //商品编码
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销类型
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销结束时间
            array('status',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),   //促销状态，结束，草稿，已发布，删除
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取业务参数
        if($params['start_time'] && $params['end_time']){
            $start_time     =  strtotime($params['start_time']);
            $end_time       =  strtotime($params['end_time'])+24*3600-1;
        }
        $uc_code=$params['uc_code'];
        $sic_code=$params['sic_code'];
        $sc_code=$params['sc_code'];
        $sic_no=trim($params['sic_no']);
        $sort_type  = $params['sort_type'];
        $goods_name=trim($params['goods_name']);
        $status=$params['status'];
        $type = $params['type'];

        $params['spc_title']?($spc_title=$params['spc_title']):null;
        $params['type']?($type=$params['type']):null;
        //系统参数
        $title          =  $params['title'];
        $filename       =  $params['filename'];
        $callback_api   =  $params['callback_api'];
        $sql_flag       =  $params['sql_flag'];
        if($sort_type=='sortByStartTime'){
            $order='sl.start_time ASC';
        }elseif($sort_type=='sortByEndTime'){
            $order='sl.end_time ASC';
        }else{
            $order='sl.update_time DESC';
        }
        $this->checkParams($sc_code, $uc_code);

        //默认参数
        $default_title=array('促销编码','商品编码','商品名称','商品库存','促销标题','促销开始时间','促销结束时间','促销方式','促销规则','赠品商品编码','赠品名称','赠品库存','促销状态','单店最大购买数量');
        $default_fields='isi.sic_no,isi.stock,isi.min_num,isi.price,sl.spc_code,sl.sic_code,sl.spc_title,sl.type,sl.start_time,sl.end_time,sl.status,ii.goods_name,ii.spec,ii.sub_name,ii.brand,ii.spec,ii.goods_img,ii.goods_img_new,ii.packing,ce.name AS category_name,sl.max_buy';
        $default_filename   =  '促销信息列表';
        $default_sql_flag   =  'spc_list';
        $default_order      =  'sl.id desc';
        $default_api        =  'Com.Callback.Export.SpcExport.spcList';

        $title          =  empty($title)    ? $default_title  : $title;
        $filename       =  empty($filename) ? $default_filename : $filename;
        $callback_api   =  empty($callback_api) ? $default_api : $callback_api;

        //组装where条件
        $where=array();
        if($status){
            foreach($status as $v){
                if($v=='PREHEAT'){
                    $map[] = array('sl.start_time'=>array('gt',NOW_TIME),'sl.status'=>array('eq','PUBLISH'));
                }
                if($v=='PUBLISH'){
                    $map[]=array('sl.start_time'=>array('lt',NOW_TIME),'sl.end_time'=>array('gt',NOW_TIME),'sl.status'=>array('eq','PUBLISH'));
                }
                if($v=='END'){
                    $data[]=array('sl.end_time'=>array('lt',NOW_TIME),'sl.status'=>array('eq','PUBLISH'));
                    $data[]=array('sl.status'=>array('eq','END'));
                    $data['_logic']='or';
                    $map[]=array('_complex'=>$data);
                }
                if($v=='DRAFT'){
                    $map[]=array('sl.status'=>array('eq','DRAFT'));
                }
            }
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
         $where['isi.status']=array('eq',IC_STORE_ITEM_ON);
         $where['sl.sc_code']=array('eq',$sc_code);
         $where['sl.status'] = array('neq','DELETE');
         empty($type)      ? null :$where['sl.type'] = array('in',$type);
         empty($sic_no) ? null : $where['isi.sic_no']=array('eq',$sic_no);
         empty($start_time)?null:$where['sl.end_time']=array('egt',$start_time);
         empty($end_time)?null:$where['sl.start_time']=array('elt',$end_time);
         empty($goods_name)?null:$where['ii.goods_name']=array('like',"%$goods_name%");
         empty($sic_code)?null:$where['sl.sic_code']=array('eq',$sic_code);
        //组装调用导出api参数
        $params['where']        =  $where;
        $params['fields']       =  $default_fields;
        $params['title']        =  $title;
        $params['center_flag']  =  SQL_SPC;//订单中心
        $params['sql_flag']     =  empty($sql_flag) ? $default_sql_flag : $sql_flag;  //sql标识
        $params['filename']     =  $filename;
        $params['order']        =  empty($order) ? $default_order : $order;
        $params['callback_api'] = $callback_api;
        $apiPath  =  "Com.Common.CommonView.Export.export";
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);

    }

    /**
     * Base.SpcModule.Center.Spc.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        $this->_rule = array(
            array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('stock', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
            array('page', 'require' , PARAMS_ERROR, ISSET_CHECK),			#  页码				非必须参数, 默认值 1
            array('page_number', 'require' , PARAMS_ERROR, ISSET_CHECK),	#  每页行数			非必须参数, 默认值 20
            array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商家编码
            array('spc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //促销中心编码
            array('sic_code', 'require', PARAMS_ERROR, ISSET_CHECK), //商品编码
            array('spc_title', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销标题
            array('goods_name', 'require', PARAMS_ERROR, ISSET_CHECK),   //商品名称
            array('sic_no', 'require', PARAMS_ERROR, ISSET_CHECK),   //商品编码
            array('sort_type', 'require', PARAMS_ERROR, ISSET_CHECK),   //排序方式
            array('type', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销类型
            array('start_time', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销开始时间
            array('end_time', 'require', PARAMS_ERROR, ISSET_CHECK),   //促销结束时间
            array('status',  'checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),   //促销状态，结束，草稿，已发布，删除
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        //获取平台标识
        if($params['start_time'] && $params['end_time']){
            $start_time     =  $params['start_time'];
            $end_time       =  $params['end_time'];
        }
        $sort_type    =      $params['sort_type'];
        $sic_no       =       trim($params['sic_no']);
        $sc_code       =       $params['sc_code'];
        $goods_name      =      trim($params['goods_name']);
        $sic_code      =       $params['sic_code'];
        $status        =       $params['status'];
        $type         =       $params['type'];
        $stock       =       $params['stock'];

//        var_dump($status);exit;
        $fields='isi.sic_no,isi.stock,isi.min_num,isi.price,sl.spc_code,sl.sic_code,sl.spc_title,sl.type,sl.start_time,sl.update_time,sl.end_time,sl.status,ii.goods_name,ii.spec,ii.sub_name,ii.brand,ii.spec,ii.goods_img,ii.goods_img_new,ii.packing,ce.name AS category_name,sl.max_buy,isi.sub_name as store_sub_name';
        if($sort_type=='sortByStartTime'){
            $order='sl.start_time ASC';
        }elseif($sort_type=='sortByEndTime'){
            $order='sl.end_time ASC';
        }else{
            $order='sl.update_time DESC';
        }
        //组装where条件
        $map=array();
        if($status){
           foreach($status as $v){
               if($v=='PREHEAT'){
                   $map[] = array('sl.start_time'=>array('gt',NOW_TIME),'sl.status'=>array('eq','PUBLISH'));
              }
               if($v=='PUBLISH'){
                   $map[]=array('sl.start_time'=>array('lt',NOW_TIME),'sl.end_time'=>array('gt',NOW_TIME),'sl.status'=>array('eq','PUBLISH'));
               }
               if($v=='END'){
                   $data[]=array('sl.end_time'=>array('lt',NOW_TIME),'sl.status'=>array('eq','PUBLISH'));
                   $data[]=array('sl.status'=>array('eq','END'));
                   $data['_logic']='or';
                   $map[]=array('_complex'=>$data);
               }
               if($v=='DRAFT'){
                       $map[]=array('sl.status'=>array('eq','DRAFT'));
               }
           }
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
//        var_dump($where);exit;
        $where['isi.status']=array('eq',IC_STORE_ITEM_ON);
        $where['sl.status'] = array('neq','DELETE');
        empty($stock) ? null : $where['isi.stock']=array('elt',0);
        $where['sl.sc_code']=array('eq',$sc_code);
        empty($type)      ? null :$where['sl.type'] = array('in',$type);
        empty($goods_name) ? null: $where['ii.goods_name']=array('like',"%$goods_name%");
        empty($sic_no) ? null : $where['isi.sic_no']=array('eq',$sic_no);
        empty($sic_code) ? null: $where['sl.sic_code']=array('eq',$sic_code);
        empty($start_time) ? null: $where['sl.end_time']=array('egt',$start_time);
        empty($end_time) ? null: $where['sl.start_time']=array('elt',$end_time);
        if($params['is_page']=='NO'){
            $where=D()->parseWhereCondition($where);
            $sql="SELECT
                               {$fields}
                          FROM
                               {$this->tablePrefix}spc_list sl
                          LEFT JOIN
                               {$this->tablePrefix}ic_store_item  isi ON sl.sic_code=isi.sic_code
                          LEFT JOIN
                                {$this->tablePrefix}ic_item ii ON isi.ic_code=ii.ic_code
                          LEFT JOIN
                                {$this->tablePrefix}category_end ce ON ii.category_end_id = ce.id
                           {$where}
                         order by {$order}";
            $res=D()->query($sql);
            return $this->res($res);
        }
//        var_dump($where);exit;
        # 默认值
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageNumber = isset($params['page_number']) ? $params['page_number'] : 20;
        $data['page']=$page;
        $data['page_number']=$pageNumber;
        $data['order']=$order;
        $data['where']=$where;
        $data['fields']=$fields;
        $data['sql_flag']='spc_list';
        $data['center_flag']=SQL_SPC;
        $api_Path='Com.Common.CommonView.Lists.Lists';
        $call=$this->invoke($api_Path,$data);
        if($call['status']!==0){
            $this->res(null,$call['status'],'',$call['message']);
        }
       return $this->res($call['response']);
    }

    /**
     * ZHANGYUPENG
     * Base.SpcModule.Center.Spc.add
     * @param [type] $params [description]
     */
    public function add($params){ 
        
        $this->startOutsideTrans();
        $this->_rule = array(
                array('sic_code', 'require', PARAMS_ERROR, MUST_CHECK),
                array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),
                array('spc_title', 'require', PARAMS_ERROR, ISSET_CHECK),
                array('type', 'require', PARAMS_ERROR, MUST_CHECK),
                array('data', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK,'function'),
                array('max_buy', 'require', PARAMS_ERROR, ISSET_CHECK),
                array('start_time', 'require', PARAMS_ERROR, MUST_CHECK),
                array('goods_price', 'require', PARAMS_ERROR, ISSET_CHECK),
                array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),
                array('status', 'require', PARAMS_ERROR, ISSET_CHECK),
            );

        if (!$this->checkInput($this->_rule, $params)) { 
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $goods_price = $params['goods_price'];

        //最大起购数量判断
        if ($params['max_buy'] < 0) {
            return $this->res(null, 7029);
        }

        //判断时间
        if ($params['start_time'] > $params['end_time']) {
            return $this->res(null, 7004);  
        }

        if ($params['end_time'] < NOW_TIME) {
            return $this->res(null, 7025);  
        }

        //判断商品是否在促销中(如果是草稿就随便发布)
        if ($params['status'] == IC_ITEM_PUBLISH) {
            $where = array();
            $where['sic_code'] = $params['sic_code'];
            $where['sc_code']  = $params['sc_code'];
            $where['status']   = $params['status'];
            $where['end_time'] = array('gt', NOW_TIME);
            $result = D('SpcList')->where($where)->order(array('id'=>'desc'))->select();
            if (!empty($result)) {
                return $this->res('', 7000);
            }
        }

        $data = $params['data'];
        //根据促销类型，添加类型数据
        switch ($params['type']) {
            case SPC_TYPE_GIFT:
                $api        = "Base.SpcModule.Gift.Gift.add";
                $preBusType = SPC_GIFT;
                break;
            case SPC_TYPE_SPECIAL:
                $api               = "Base.SpcModule.Special.Special.add";
                $preBusType        = SPC_SPECIAL;
                $data['itemPrice'] = $goods_price; 
                break;
            case SPC_TYPE_LADDER:
                $api               = "Base.SpcModule.Ladder.Ladder.add";
                $preBusType        = SPC_LADDER;
                $data['itemPrice'] = $goods_price; 
                break;

        }

        // 生成促销编码
        $codeData = array(
            "busType"    => SPC_CODE,
            "preBusType" => $preBusType,
            "codeType"   => SEQUENCE_SPC
        );
        
        $spcCode = $this->invoke('Com.Tool.Code.CodeGenerate.mkCode', $codeData);
        if( $spcCode['status'] !== 0) {
            return $this->res('', 7001); 
        }

        //促销类别数据
        $data['spc_code'] = $spcCode['response'];
        
        //执行促销类型API
        $addRes = $this->invoke($api, $data);
        
        if($addRes['status'] !== 0) {
            return $this->res('', $addRes['status']);
        }
        $max_buy = empty($params['max_buy'])? 0 :intval($params['max_buy']);
        //center数据(spc_list)
        $centerData = array(
                'spc_code'    => $spcCode['response'],
                'sic_code'    => $params['sic_code'],
                'sc_code'     => $params['sc_code'],
                'spc_title'   => $params['spc_title'],
                'type'        => $params['type'],
                'goods_price' => $goods_price,
                'max_buy'     => $max_buy,
                'start_time'  => $params['start_time'],
                'end_time'    => $params['end_time'],
                'status'      => $params['status'],
                'create_time' => NOW_TIME,
                'update_time' => NOW_TIME,
                );

        $insert = D('SpcList')->add($centerData);
        if(!$insert) {
            return $this->res('', 7002);
        } 
        
        return $this->res(true);
    }


    /**
     * Base.SpcModule.Center.Spc.update
     * @param type $params
     * @return type
     */
    public function update($params){
        // L($params);exit();
        $this->startOutsideTrans();
        $this->_rule = array(
            array('spc_code', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('sic_code', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('sc_code', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('spc_title', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('type', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('data', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK,'function'),
            array('goods_price', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('max_buy', 'require', PARAMS_ERROR, ISSET_CHECK),
            array('start_time', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('end_time', 'require', PARAMS_ERROR, HAVEING_CHECK),
            array('status', 'require', PARAMS_ERROR, HAVEING_CHECK),
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        //获取单个当品信息
        $spcItem = $this->getStatusBySpcCode($params['spc_code']);
        if(empty($spcItem)){
            return $this->res("", 7010);
        }
        $goods_price = $params['goods_price'];

        //判断商品是否在促销中(如果是草稿就随便发布)
       if ($params['status'] == IC_ITEM_PUBLISH) {
           $where = array();
           $where['sic_code'] = $params['sic_code'];
           $where['sc_code']  = $params['sc_code'];
           $where['status']   = $params['status'];
           $where['end_time'] = array('gt', NOW_TIME);
           $result = D('SpcList')->where($where)->order(array('id'=>'desc'))->select();
           if (!empty($result)) {
               return $this->res('', 7000);
           }
       }

        //判断起始时间是否大于过期时间
        if ($params['start_time'] > $params['end_time']) {
            return $this->endInvoke(null, 7004);
        }
        if ($params['end_time'] < NOW_TIME) {
            return $this->res(null, 7025);  
        }

    
        $params['goods_price'] = $goods_price;
        $centerData['goods_price'] = $goods_price;
        $centerData   = array();
        if (!empty($params['data'])) {
            $data             = $params['data'];
            $data['spc_code'] = $params['spc_code'];
            $spcType          = $spcItem['type'];

            //根据类型进行促销更新
            switch ($spcType) {
                case SPC_TYPE_GIFT:
                    $api = "Base.SpcModule.Gift.Gift.update";
                    break;
                case SPC_TYPE_SPECIAL:
                    $api               = "Base.SpcModule.Special.Special.update";
                    $data['itemPrice'] = $goods_price;
                    break;
                case SPC_TYPE_LADDER:
                    $api = "Base.SpcModule.Ladder.Ladder.update";
                    $data['itemPrice'] = $goods_price;
                    break;
            }

            $updateRes = $this->invoke($api, $data);
            if($updateRes['status'] !== 0) {
                return $this->res('', $updateRes['status']);
            }

            //判断当前操作是否是修改
            $status = $spcItem['status'];
            if($status !== SPC_STATUS_DRAFT){
                return $this->res("", 7006);
            }

            //过滤验证字段
            $filter = array('spc_code', 'data', 'sc_code');

            $centerData['spc_title']   = empty($params['spc_title'])?'':trim($params['spc_title']);
            $centerData['max_buy']     = !empty($params['max_buy'])?intval($params['max_buy']):'';
        }else{
            //判断当前操作是否是修改
            $status = $spcItem['status'];
            if($status !== SPC_STATUS_PUBLISH){
                return $this->res("", 7006);
            }
            //过滤验证字段
            $filter = array('data', 'type', 'sic_code');
        }

        foreach ($params as $key => $value) {
            if (in_array($value, $filter)) {
               continue;
            }
            
            if (!empty($value)) {
                $centerData["$key"] = $value;
            }
        }

        $centerData['update_time'] = NOW_TIME;
        if(empty($centerData)){
            return $this->res('', 2001);
        }
        
        $wheres['sc_code'] = $params['sc_code'];
        $wheres['spc_code'] = $params['spc_code'];

        $update = D('SpcList')->where($wheres)->save($centerData);
        if(!$update){
            return $this->res('', 7007);
        }

       return  $this->res(true);
    }

    /**
     * Base.SpcModule.Center.Spc.getStatusBySpcCode
     * @param type $params
     * @return type
     */

    public function getStatusBySpcCode($spcCode){
        return D("SpcList")->where("spc_code=".$spcCode."")->find();
    }

    /**
     * Base.SpcModule.Center.Spc.getGiftBySpcCode
     * @param type $params
     * @return type
     */

    public function getGiftBySpcCode($spcCode){
        return D("SpcGift")->where("spc_code=".$spcCode."")->find();
    }

    /**
     * Base.SpcModule.Center.Spc.getStatusBySicCode
     * @param type $params
     * @return type
     */

    public function getStatusBySicCode($sicCode){

       $result = D("SpcList")->where($sicCode)->select();
       return $result;
    }

    /**
     * Base.SpcModule.Center.Spc.getStatusBySicCode
     * @param type $params
     * @return type
     */

    public function batDelay($params){
        $this->_rule = array(
            array('spc_codes', 'checkArrayInput', PARAMS_ERROR, HAVEING_CHECK,'function'),
            array('end_time', 'require', PARAMS_ERROR, MUST_CHECK),
            array('sc_code', 'require', PARAMS_ERROR, MUST_CHECK),
        );

        if (!$this->checkInput($this->_rule, $params)) {
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $spc_codes=$params['spc_codes'];
        $end_time=$params['end_time'];

        //组装条件
        $where=array();
        $where['spc_code']=array('in',$spc_codes);
        $where['sc_code']=array('eq',$params['sc_code']);
        $data['end_time']=$end_time;
        $data['update_time']=NOW_TIME;

        $result=D('SpcList')->where($where)->save($data);
        if($result===false){
            return $this->res(null,7038);
        }else{
            return $this->res($result);
        }
    }




}