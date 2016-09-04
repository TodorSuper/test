<?php

namespace Com\DataCenter\Download;

use System\Base;
class DownList extends Base {

	public function getDownlist($params){

		$this->_rule = array(
				array('uc_code', 'require', PARAMS_ERROR, ISSET_CHECK), //用户编码
				array('page', '0', PARAMS_ERROR, ISSET_CHECK,'gt'), # 1
				array('page_number', 'require', PARAMS_ERROR, ISSET_CHECK), # 20
			);

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
        
		$params['fields']      = 'id,uc_code,url,filename,update_time';
		$params['where']       = $params['uc_code'] ? array('uc_code' => $params['uc_code']) : '';
		$params['sql_flag']    = 'down_list';
		$params['center_flag'] = SQL_UC;
		$params['order']       = "id DESC";
		$params['page']        = $params['page'];
		$params['page_number'] = $params['page_number'];

		$apiPath =  "Com.Common.CommonView.Lists.Lists";
		$res     = $this->invoke($apiPath, $params);
		
		return $res;
	}


}












































