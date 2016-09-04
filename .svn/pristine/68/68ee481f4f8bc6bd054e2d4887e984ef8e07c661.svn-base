<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: haowenhui <haowenhui@yunputong.com >
 * +---------------------------------------------------------------------
 * | 地址相关模块
 */

namespace Test\Base\UserAddress;
use System\Base;

class Address extends Base {

    private $_rule = null; # 验证规则列表
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 添加用户
     * Test.Base.UserAddress.Address.add
     * @param array $data   用户数据
     * @param integer $userType  预留用户类型   UC_USER_MERCHANT     UC_USER_MERCHANT 
     * @return integer   成功时返回  自增id
     */
    public function add($params){
        try{
        D()->startTrans();
            $apiPath = "Base.UserModule.Address.Address.add";
            $params = array(
                'uc_code'=>'1120000000080',
                'real_name'=>'随风飘远',
                'mobile'=>'18888888888',
                'province'=>'江西省',
                'city'=>'上饶市',
                'district'=>'上饶县',
                'address'=>'旭日大道666',
                'area_code'=>'',
                'phone'=>'8556889',
                'extension'=>'8583',
                'is_default'=>'YES',
            );
            $res = $this->invoke($apiPath, $params);
            D()->commit();
            return $this->res($res['response'],$res['status']);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
        
    }
    
    /**
     * 修改用户信息
     * Test.Base.UserAddress.Address.update
     * @param array $data   修改的字段
     * @param intger $address_id  地址自增id
     * @param intger $uc_code   用户编码
     * @return intger   影响行数
     */
    public function update($params){
          $apiPath = "Base.UserModule.Address.Address.update";
          $data = array(
              'real_name'=>'随风飘远8888',
              'mobile'=>'18888888889',
              'is_default'=>'YES',
          );
          $params = array(
              'data'=>$data,
              'address_id'=>10,
              'uc_code'=>'1120000000080',
              
          );
          $res = $this->invoke($apiPath, $params);
          return $this->res($res['response'],$res['status']);
    }
    
    /**
     * 删除信息
     * Test.Base.UserAddress.Address.delete
     * @return intger
     */
    public function  delete($params){
        try{
//            print_r(C('DB_MASTER'));exit;
            
            D()->startTrans();
            $apiPath = "Base.UserModule.Address.Address.delete";
            $data = array(
                'uc_code'=>'1120000000080',
                'address_id'=>'12',
            );
            echo json_encode($data);exit;
            $res = $this->invoke($apiPath, $data);
            D()->commit();
             return $this->res($res['response'],$res['status']);
        } catch (Exception $ex) {
            D()->rollback();
        }
        
    }
    /**
     * 删除信息
     * Test.Base.UserAddress.Address.get
     * @return intger
     */
    public function get($params){
        $apiPath = "Base.UserModule.Address.Address.get";
        $data = array(
            'uc_code'=>'1120000000080',
            'address_id'=>15,
        );
        $res = $this->invoke($apiPath, $data);
        return $this->res($res['response'],$res['status']);
    }
    
    /**
     * 列表
     * Test.Base.UserAddress.Address.lists
     * @return intger
     */
    public function lists($params){
        $apiPath = "Base.UserModule.Address.Address.lists";
        $where = array(
            'eid' => 1,
            'tid' => array('in',array(1,2,3,)),
            'sid' => array('EQ',3),
        );
        $params = array(
             'page'=>1,
             'page_number'=>2,
//             'where'=>$where,
        );
        $res = $this->invoke($apiPath, $params);
        return $this->res($res['response'],$res['status']);
    }
    

    
}

?>
