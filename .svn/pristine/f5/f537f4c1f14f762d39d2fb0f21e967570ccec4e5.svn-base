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

class Classify extends Base
{

    private $_rule = null; # 验证规则列表

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加分类
     * Base.ItemModule.Classify.Classify.add
     * @return integer   成功时返回  自增id
     */
    public function  add($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('class_name', 'require', PARAMS_ERROR, MUST_CHECK), //商品分类名称
            array('class_img', 'require', PARAMS_ERROR, MUST_CHECK), //商品分类图片路径
            array('sort', 'require', PARAMS_ERROR, MUST_CHECK), //商品分类图片路径
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $class_name = $params['class_name'];
        $class_img  = $params['class_img'];
        $sort       = $params['sort'];

        //查询表中是否存在相同的标签
        $where['class_name'] = array('eq',$class_name);
        $where['status']     = array('eq','ENABLE');
        $tag = D('IcItemClass')->where($where)->select();
        if($tag){
            return $this->res('',4567);
        }
        $data=array(
            'class_name'  => $class_name,
            'class_img'   => $class_img,
            'sort'        => $sort,
            'status'      =>'ENABLE',
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME
        );
        $result = D('IcItemClass')->add($data);
        if($result === false || $result <= 0){
            return $this->res('',4566);
        }
        return $this->res(true);
    }
    /**
     * 添加分类
     * Base.ItemModule.Classify.Classify.update
     * @return integer   成功时返回  自增id
     */
    public function  update($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('id', 'require', PARAMS_ERROR, MUST_CHECK), //商品分类id
            array('class_name', 'require', PARAMS_ERROR, ISSET_CHECK), //商品分类名称
            array('class_img', 'require', PARAMS_ERROR, ISSET_CHECK), //商品分类图片路径
        );

        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }

        $id         = $params['id'];
        $class_name = $params['class_name'];
        $class_img  = $params['class_img'];
        $sort       = $params['sort'];

        //查看该分类存不存在
         $res = D('IcItemClass')->where(array('id'=>$id,'status'=>'ENABLE'))->find();
        if(!$res){
            return $this->res('',4574);
        }
        if($params['action']=='delete'){
            //查询在该分类下面有没有商品
            $goods=D('IcItem')->where(array('class_id'=>$id))->select();
            if($goods){
                return $this->res('',4571);
            }
            $data=array(
                'status'=>'DISABLE',
                'update_time'=>NOW_TIME
            );
            $result=D('IcItemClass')->where(array('id'=>$id))->save($data);
            if($result===false || $result<=0){
                return $this->res('',4570);
            }
            return $this->res(true);
        }
        //查询表中是否存在相同的标签
        $where['class_name'] = array('eq',$class_name);
        $where['status'] = array('eq','ENABLE');
        $where['id'] = array('neq',$id);
        $tag=D('IcItemClass')->where($where)->select();
        if($tag){
            return $this->res('',4567);
        }
        $data=array(
            'class_name'  =>$class_name,
            'class_img'   =>$class_img,
            'sort'        => $sort,
            'update_time' =>NOW_TIME
        );

        $result=D('IcItemClass')->where(array('id'=>$id))->save($data);
        if($result===false || $result<=0){
            return $this->res('',4570);
        }
        return $this->res(true);
    }

    /**
     * 设置全部分类顺序
     * Base.ItemModule.Classify.Classify.setOrders
     * @param [type] $params [description]
     */
    public function setOrders($params){
        $this->startOutsideTrans();
        $this->_rule = array(
            array('sorts', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
        $sorts = $params['sorts'];
        foreach($sorts as $k=>$v){
            $res = D('IcItemClass')->where(array('id'=>$k))->save(array('sort'=>$v));
            if($res === FALSE){
                return $this->res(NULL,4516);
            }
        }
        return $this->res(TRUE);
    }
}