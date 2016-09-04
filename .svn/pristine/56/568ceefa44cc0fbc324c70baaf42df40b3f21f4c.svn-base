<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 编码统一生成
 */

namespace Com\Tool\Code;

use System\Base;

class CodeGenerate extends Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 生成编码
     * Com.Tool.Code.CodeGenerate.mkCode
     * @param type $busType    业务标示
     * @param type $preBusType  预留业务标示
     * @param type $codeType   编码类型
     * @return type  Integer
     * 
     */
    public function mkCode($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('busType', 'require', PARAMS_ERROR, MUST_CHECK),
            array('preBusType', 'require', PARAMS_ERROR, MUST_CHECK),
            array('codeType', 'require', PARAMS_ERROR, MUST_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $busType = $params['busType'];
        $preBusType = $params['preBusType'];
        $codeType = $params['codeType'];

        $length = 9;  //扩充
        switch ($codeType) {
            case SEQUENCE_ORDER:
                $length = 8;
                break;
            case SEQUENCE_USER:
                $length = 10;
                break;
            case SEQUENCE_ACCOUNT:
                $length = 10;
                break;
            case SEQUENCE_TRADE_NO:
                $length = 10;
                break;
            case SEQUENCE_POP:
                $length = 10;
                break;
            case SEQUENCE_ITEM :
                $length = 6;
                break;
            case SEQUENCE_STORE_ITEM :
                $length = 8;
                break;
            case SEQUENCE_CASH_ORDER :
                $length = 8;
                break;
            case SEQUENCE_SPC:
                $length = 8;
                break;
            case SEQUENCE_FC:
                $length = 9;
                break;
            default:
                return $this->res(null, 3001);
        }



        $id = $this->getSequence($codeType);
        $env_config = C('ENV');
        //获取环境
        $env = $env_config[ENV];
        //扩充位数
        $id = $env . str_pad($id, $length, 0, STR_PAD_LEFT);
        return $this->res($busType . $preBusType . $id);
    }

    /**
     * Com.Tool.Code.CodeGenerate.mkSafeCode
     * 生成数据库安全码
     *
     * mkSafeCode 
     * 
     * @access public
     * @return string
     */
    public function mkSafeCode($data) {
        if (empty($data)) {
            return $this->res(null, 5);
        }
        $key = C('SAFE_CODE_KEY');
        $string = '';
        foreach ($data as $k => $v) {
            if (is_string($v) || is_int($v)) {
                $string = $string . $v;
            } else {
                return $this->res(null, 5000); # 不支持非字符串类型
            }
        }
        return $this->res(md5($string . $key));
    }

    /**
     * Com.Tool.Code.CodeGenerate.saveSafeCode
     * 通用方法, 生成并且保存结果到指定表的字段中
     * saveSafeCode 
     * 
     * @param mixed $data 
     * @access public
     * @return void
     */
    public function saveSafeCode($data) {
        $this->startOutsideTrans();  # 外部事务模式
        $this->_rule = array(
            array('safeTable', 'require', PARAMS_ERROR, MUST_CHECK), # 要添加的表 注: 框架的首字母驼峰格式  * 必须参数
            array('where', 'checkInput', PARAMS_ERROR, MUST_CHECK, 'function'), # 检索条件 注: 框架的首字母驼峰格式    * 必须参数
            array('useFields', 'require', PARAMS_ERROR, MUST_CHECK), # 要校验的字段 请按照顺序用逗号分隔开  * 必须参数
            array('updateField', 'require', PARAMS_ERROR, ISSET_CHECK), # 生成的安全码要写入到的表的字段		非必需参数
        );
        if (!$this->checkInput($this->_rule, $data)) # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());

        # 参数赋值和设置默认参数
        $safeTable = $data['safeTable'];
        $useFields = $data['useFields'];
        $where = $data['where'];
        $updateField = $data['updateField'] ? $data['updateField'] : 'safe_code';  # 不传则默认为  safe_code
        # 生成效验码
        $SAFETABLE = D($safeTable);
        $line = $SAFETABLE->field($useFields)->where($where)->master()->find();
        if ($line == false) {
            return $this->res('', 5005);
		}

//		$safeCode = $this->mkSafeCode($line);
        $key = C('SAFE_CODE_KEY');
        $string = '';
        foreach ($line as $k => $v) {
            if (is_string($v) || is_int($v)) {
                $string = $string . $v;
            } else {
                return $this->res(null, 5000); # 不支持非字符串类型
            }
		}

        if (!$string) {
            return $this->res('', 2540); # 生成失败
		}

        $safeCode = md5($string . $key);

        # 写入效验码
        $save = array(
            $updateField => $safeCode,
            'update_time' => NOW_TIME
        );

        $save = D($safeTable)->where($where)->save($save);
        if ($save === 1) {
            return $this->res(true);
        } else {
            return $this->res('', 5006);
        }
    }

    /**
     * 获取自增空间id
     * @param type $type    类别
     * @param type $length  扩充长度
     * @return boolean
     * @throws \Exception
     */
    public function getSequence($type) {
        if (empty($type)) {
            return false;
        }
        try {
            $sql = "update {$this->tablePrefix}sequence  set id = LAST_INSERT_ID(id + step) where type = {$type}";
            $res = D()->execute($sql);
            if ($res <= 0) {
                throw new \Exception('生成编码失败', 100000);
            }
			$idInfo = D('Sequence')->master()->field('LAST_INSERT_ID() as id')->find();
            $id = $idInfo['id'];
            if ($id <= 0) {
                throw new \Exception('获取编码失败', 100001);
            }
            return $id;
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            $code = $ex->getCode();
            throw new \Exception($message, $code);
        }
    }
    
    
    /**
     * 生成循环编码
     * Com.Tool.Code.CodeGenerate.mkCycleCode
     * @param type $busType    业务标示
     * @param type $preBusType  预留业务标示
     * @param type $codeType   编码类型
     * @return type  Integer
     * 
     */
    public function mkCycleCode($params) {
        $this->startOutsideTrans();
        $this->_rule = array(
            array('codeType', 'require', PARAMS_ERROR, MUST_CHECK),
            array('prefix', 'require', PARAMS_ERROR, ISSET_CHECK),
        );
        if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        $codeType = $params['codeType'];

        $length = 4;  //扩充
        $prefix = "";
        switch ($codeType) {
            case SEQUENCE_REMIT:
                $prefix = date('md');
                $length = 5;
                break;
            default:
                return $this->res(null, 3001);
        }

        $id = $this->getCycleSequence($codeType,$length);
        $id = str_pad($id, $length, 0, STR_PAD_LEFT);
        return $this->res($prefix.$id);
    }
    
    /**
     * 获取循环自增空间id
     * @param type $type    类别
     * @param type $length  扩充长度
     * @return boolean
     * @throws \Exception
     */
    public function getCycleSequence($type,$length) {
        if (empty($type)) {
            return false;
        }
        //该长度最大值
        $max_value = str_repeat(9, $length) + 0 ;
        try {
            $sql = "update {$this->tablePrefix}sequence  set id = (case when id >= {$max_value} then LAST_INSERT_ID(1) else LAST_INSERT_ID(id + step) end ) where type = {$type}";
            $res = D()->execute($sql);
            if ($res <= 0) {
                throw new \Exception('生成编码失败', 100000);
            }
			$idInfo = D('Sequence')->master()->field('LAST_INSERT_ID() as id')->find();
            $id = $idInfo['id'];
            if ($id <= 0) {
                throw new \Exception('获取编码失败', 100001);
            }
            return $id;
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            $code = $ex->getCode();
            throw new \Exception($message, $code);
        }
    }

}
