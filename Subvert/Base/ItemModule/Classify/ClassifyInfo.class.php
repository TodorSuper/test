<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.liangrenwang.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhangyupeng <zhangyupeng@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | 商品分类相关
 */

namespace Base\ItemModule\Classify;

use System\Base;

class ClassifyInfo extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加分类
     * Base.ItemModule.Classify.ClassifyInfo.lists
     * @return integer   成功时返回  自增id
     */
    public function lists($params){
        $this->_rule = array(
            array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK),      # 分页数
            array('page', 'require', PARAMS_ERROR, ISSET_CHECK),      # 页码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $order            = "sort asc,create_time desc";
        $page             = $params['page'];
        $page_number      = $params['page_number'];
        $where['status']           = array('eq','ENABLE');
        $fields = 'id,class_name,class_img,sort';
        $data['where']      = $where;
        $data['page']        = $page;
        $data['page_number'] = $page_number ? $page_number : 20;
//        if($page==1){
//            $data['page_number']= $data['page_number']-1;
//        }
        $data['fields']      = $fields;
        $data['order']       = $order;
        $data['center_flag'] = SQL_IC;
        $data['sql_flag']    = 'calssify_lists';

        $apiPath = "Com.Common.CommonView.Lists.Lists";
        $res = $this->invoke($apiPath,$data);
        if ($res['status'] != 0) {
            return $this->res(NULL,4568);
        }
        return $this->res($res['response']);
    }
    public function listsAll() {
       $res = D('IcItemClass')->where(array('status'=>'ENABLE'))->order('create_time desc')->select();
       return $this->res($res);
    }

    /**
     * 获取各个分类下的商品数量
     * Base.ItemModule.Classify.ClassifyInfo.classNum
     *
     */
    public function classNum(){
        //得到上架的分类商品数量
        $result_on = D('IcItem')->alias('ii')->field('ii.class_id,count(*) as classNum,isi.status')->join("{$this->tablePrefix}ic_store_item as isi on ii.ic_code=isi.ic_code","left")->where(array('isi.status'=>'ON','ii.status'=>'PUBLISH','isi.sc_code'=>array('neq','1010000000077')))->group('ii.class_id')->select();
        if($result_on===false){
            return $this->res('',4569);
        }
        $result_off = D('IcItem')->alias('ii')->field('ii.class_id,count(*) as classNum,isi.status')->join("{$this->tablePrefix}ic_store_item as isi on ii.ic_code=isi.ic_code","left")->where(array('isi.status'=>'OFF','ii.status'=>'PUBLISH','isi.sc_code'=>array('neq','1010000000077')))->group('ii.class_id')->select();
        if($result_off===false){
            return $this->res('',4569);
        }
        $result_on = changeArrayIndex($result_on,'class_id');
        $result_off = changeArrayIndex($result_off,'class_id');
        $total=array(
            'on'=>$result_on,
            'off'=>$result_off
        );
        return $this->res($total);
    }


    /**
     * Base.ItemModule.Classify.ClassifyInfo.getClasses
     * 获取有用的分类
     * @access public
     */

    public function getClasses($params){
        $this->_rule = array(
            array('invite_code','require',PARAMS_ERROR, ISSET_CHECK),                   # 邀请码
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        // 邀请码判断
        if($params['invite_code'] != C('TEXT_INVITE_COE')){
            $where['isi.sc_code'] = array('neq',C('LIANGREN_SC_CODE'));
        }

        // 获取平台商家有多少分类
        $where['ii.status'] = "PUBLISH";
        $where['isi.status'] = "ON";
        $where['ss.is_show'] = "YES";
        $where['isi.stock']=array('gt',0);
        $where['isi.stock'] = array('exp','>= isi.min_num');

        $res = D('IcItem')->alias('ii')->field('distinct ii.class_id')
                          ->join("{$this->tablePrefix}ic_store_item isi ON ii.ic_code = isi.ic_code",'LEFT')
                          ->join("{$this->tablePrefix}sc_store ss ON isi.sc_code = ss.sc_code",'LEFT')
                          ->where($where)
                          ->select();
        if(empty($res)){
            return $this->res(true);
        }

        //根据分类获取分类
        $ids = array();
        foreach ($res as $k => $v) {
            $ids[] = $v['class_id'];
        }

        $map['status'] = "ENABLE";
        $map['id'] = array('in',$ids);
        $map['class_name'] = array('neq','未分类');

        $class_res = D('IcItemClass')->field('id,class_name,class_img')->where($map)->order('sort asc,id desc')->select();

        if($class_res === false){
            return $this->res(NULL,4572);
        }
        return $this->res($class_res);

    }

}