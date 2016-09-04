<?php

namespace Com\DataCenter\File;

use System\Base;
class Excel extends Base{
	public function __construct() {               
        parent::__construct();
    }
     /**
    * 组装适合入库的EXECL数据
    * @param array $params 
    * @return array $sheetData 处理后的数据
    */
	public function spcImport($params) {
		set_time_limit(0);
		$this->_rule = array(
				array('data','checkArrayInput', PARAMS_ERROR, ISSET_CHECK,'function'),
				array('sc_code','require',PARAMS_ERROR, MUST_CHECK),
			);

		if (!$this->checkInput($this->_rule, $params)) { # 自动校验
            return $this->res($this->getErrorField(), $this->getCheckError());
        }
     
        $data = $params['data'];
  	
     	$data = $this->_c_data($data,$params['sc_code']);

     	$data = $this->checkData($data);
     
        return $data;
	}
	/**
	* 组装EXECL数据
	*/
	private function _c_data($data,$sc_code) {
		$apiPath = 'Base.StoreModule.Item.Item.storeItems';
		$temp = array();
		$temp2 = array();
		$datalist = array();
		$params['sc_code'] = $sc_code;
		$params['sic_nos'] = array_unique(array_column($data,0));
		$params['is_page'] = 'NO';
		$params['is_publish'] = 'NO';
		$goods_nos = $params['sic_nos'];
		//获取促销商品信息
		$newData  = $this->invoke($apiPath,$params); 
		
		$params['sic_nos'] = array_unique(array_column($data,6));
		
		$gift_nos = $params['sic_nos'];
		//获取赠品信息
		$gifCodes = $this->invoke($apiPath,$params);

		$gift_status       = array();
		$goods_success_sno = array();
		$gift_success_sno  = array();

        foreach($newData['response'] as $k=>$v){
            $temp[$v['sic_no']]= $v;

            if ( in_array($v['sic_no'], $goods_nos) ) {

            	$goods_success_sno[] = $v['sic_no'];
            }
        }
        foreach ($gifCodes['response'] as $j => $n) {
        	$temp2[$n['sic_no']] = $n['sic_code'];
        	$gift_status[$n['sic_no']] = $n['status'];
        	if ( in_array($n['sic_no'], $gift_nos) ) {
            	$gift_success_sno[] = $n['sic_no'];
            }
        }
        
       
        $goods_no_data = array_diff($goods_nos,$goods_success_sno);
        $gift_no_data  = array_diff($gift_nos, $gift_success_sno);
     
        $data = array_values($data);
        //拼接入库数据
        foreach ($data as $key => $value) {
        	$datalist[$key]['sic_code']     = $temp[(string)$value[0]]['sic_code'];
        	$datalist[$key]['sc_code']      = $sc_code;
        	$datalist[$key]['spc_title']    = $value[2];
        	$datalist[$key]['max_buy']      = $value[12]; 
        	switch ($value[5]) {
        		case '满赠':
        			$datalist[$key]['data']		    = array(
		        		'sale_sic_code' => $datalist[$key]['sic_code'],
		        		'gift_sic_code' => $temp2[(string)$value[6]],
		        		'rule'			=> json_encode($this->_sortRules($value['rule'])),
		        	);
		        	$datalist[$key]['gift_status']  = $gift_status[(string)$value[6]];
		        	
        			break;
        		case '特价':
        			$datalist[$key]['data']		    = array(
		        		'sale_sic_code' => $datalist[$key]['sic_code'],
		        		'special_price' => $value[11],
		        		'special_type'  => 'FIXED',
		        		'sic_code'      => $datalist[$key]['sic_code'] 
		        	);
		        	break;
		        case '阶梯价':
		        	$datalist[$key]['data']        = array(
		        		'sic_code'      => $datalist[$key]['sic_code'],
		        		'rule'          => $value['rule']
		        	);
		        	$datalist[$key]['max_buy']     = 0; 
		        	break;
        		default:
        			# code...
        			break;
        	}
        	
        	$datalist[$key]['start_time']   = strtotime(str_replace('.', '-', $value[3]));
        	$datalist[$key]['end_time']     = strtotime(str_replace('.', '-', $value[4]))+86399;
        	$datalist[$key]['goods_status'] = $temp[(string)$value[0]]['status'];
        	$datalist[$key]['type']         = M('Base.SpcModule.Center.Status.getStrToType')->getStrToType($value[5]);
        	$datalist[$key]['execl_code']   = $value['execl_code'];
        	$datalist[$key]['sic_no']		= $value[0];
        	$datalist[$key]['status']		= 'PUBLISH';
        }
      	 
        if (!empty($goods_no_data)) {
        	$datalist['sno'] = $goods_no_data;
        }
        if (!empty( $gift_no_data)) {
        	$datalist['gift_sno'] = $gift_no_data;
        }
     
		return $datalist;
	}
	/**
	* Com.DataCenter.File.Excel.checkData
	* 检测数据
	*/
	public function checkData($data) {
		$success_data = array(); 
		$fail_data    = array(); 
		$sic_codes = array_unique(array_column($data,'sic_code'));
		foreach ($data as $k => $v) {
			$gift_codes[] = $v['data']['gift_sic_code'];
		}
		$gift_codes = array_unique($gift_codes);
		//是否已经发布
		$pramas['sic_code'] = array('in',$sic_codes);
	
		if (empty($sic_codes)) {
			return array(
				'success' => array(),
				'fail'    => array(),
				'number'  => 0
			);
		}
		$statuslist = M('Base.SpcModule.Center.Spc.getStatusBySicCode')->getStatusBySicCode($pramas);
		$temp = array();
		foreach ($statuslist as $key => $value) {
			if ($value['status'] == IC_ITEM_PUBLISH && $value['end_time']>time()) {
				$temp[] = $value['sic_code']; 
			}
		}
		$no_fail = $temp;
		$success = !empty($temp) ?array_unique(array_diff($sic_codes,$temp)):$sic_codes;
		$temp = $this->_getTempData('sic_code',$data,$success);
		$codes   = array_column($temp,'data'); 
		//获取商品信息
		$good_sic_code = array_unique(array_column($codes,'sale_sic_code'));
		$temps = array_unique(array_column($codes,'sic_code'));
		$good_sic_code = array_merge_recursive($good_sic_code,$temps);
		$sc_code = current($data)['sc_code'];
		//获取赠送商品信息
		$spc_sic_code = array_unique(array_column($codes,'gift_sic_code'));
		$temp_goods = $this->_getGoodsinfo($good_sic_code,$sc_code );
		$temp_spc   = $this->_getGoodsinfo($spc_sic_code,$sc_code);
		
		foreach ($temp as $k => $v) {
			$temp[$k]['goods_info'] = $temp_goods[$v['sic_code']];
			$temp[$k]['spc_info'] = $temp_spc[(string)$v['data']['gift_sic_code']];
		}

		$success = array(); //重置状态
		//检测数据是否合法
		$errorArray = array();
		foreach ($temp as $mm => $nn) {
			switch ($nn['type']) {
				case SPC_TYPE_SPECIAL:
						if ($nn['goods_info']['stock'] > 0  && $nn['goods_info']['store_status'] == 'ON'  &&  $nn['end_time'] > $nn['start_time']  && $nn['end_time'] > time() && $nn['start_time'] >= strtotime(date('Y-m-d')) && $nn['goods_status'] == IC_ITEM_PUBLISH && StrlenStr($nn['spc_title']) <= 30 && $nn['data']['special_price'] < $nn['goods_info']['price'] && $nn['data']['special_price'] > 0  && $nn['max_buy'] >= 0 ) {
							$success[] = $nn['sic_code'];
						}
					break;
				case SPC_TYPE_GIFT:
					$rules = is_array($nn['data']['rule'])?$nn['data']['rule']:json_decode($nn['data']['rule']);
					foreach ($rules as $s => $m) {
		                if ($m[0] === '' || $m[1] === '') {
		                	unset($rules[$s]);
		                }
		            }
		            $count_rules = count($rules);
					if ($nn['goods_info']['stock'] > 0 && $nn['spc_info']['stock'] > 0 && $nn['goods_info']['store_status'] == 'ON' && $nn['spc_info']['store_status'] == 'ON' &&  $nn['end_time'] > $nn['start_time']  && $nn['end_time']>time() && $nn['start_time'] >= strtotime(date('Y-m-d')) && $count_rules >= 1 && $count_rules <= 3 && $this->_checkRules($rules,$count_rules) === true && $nn['gift_status'] == IC_ITEM_PUBLISH && $nn['goods_status'] == IC_ITEM_PUBLISH && StrlenStr($nn['spc_title']) <= 30 && $nn['max_buy'] >= 0) {
							unset($temp[$mm]['gift_status']);
							unset($temp[$mm]['goods_status']);
							$success[] = $nn['sic_code'];
					} 
					break;
				case SPC_TYPE_LADDER:

					if ( is_string($nn['data']['rule']) ) {
						$nn['data']['rule'] = json_decode($nn['data']['rule']);
						foreach ($nn['data']['rule'] as $kk => $vv) {
							$nn['data']['rule'][$kk][0] = $vv[0].'~'.$vv[1];
							$nn['data']['rule'][$kk][1] = $vv[2];
							unset($nn['data']['rule'][$kk][2]);
						}
					}
					reset($nn['data']['rule']);
					$bool = $this->_ladderRule($nn['data']['rule'],$nn['goods_info']['price']);
					if ($bool===false) $ladderIsTrue[] = $nn['sic_code'];
					if ( $bool && $nn['goods_info']['stock'] > 0  && $nn['goods_info']['store_status'] == 'ON'  &&  $nn['end_time'] > $nn['start_time']  && $nn['end_time'] > time() && $nn['start_time'] >= strtotime(date('Y-m-d')) && $nn['goods_status'] == IC_ITEM_PUBLISH && StrlenStr($nn['spc_title']) <= 30 ) {
						$success[] = $nn['sic_code'];
					}
					break;
				default:
					break;
			}
		}
		$fail_code = array_diff($sic_codes, $success);
		$data_goods = $this->_getGoodsinfo($sic_codes,$sc_code);
		$data_spc   = $this->_getGoodsinfo($gift_codes,$sc_code);
		foreach ($data as $k => $v) {
			if ($k !== 'sno' && $k !== 'gift_sno') {
				$data[$k]['goods_info'] = $data_goods[$v['sic_code']];
				$data[$k]['spc_info'] = $data_spc[(string)$v['data']['gift_sic_code']];
				$data[$k]['data']['rule'] = is_array($v['data']['rule'])?$v['data']['rule']:json_decode($v['data']['rule']);
			}
			if ($v['type'] == SPC_TYPE_LADDER ) {
				foreach ( $v['data']['rule'] as $kk => $vv ) {
					list($num1,$num2) = explode('~',$vv[0]);
					$data[$k]['data']['rule'][$kk][0] = $num1;
					$data[$k]['data']['rule'][$kk][1] = $num2;
					$data[$k]['data']['rule'][$kk][2] = $vv[1];
				}
			}
		}	
		
		//成功的数据
		$success_data = $this->_getTempData('sic_code',$data,$success);
		//失败的数据
		$d = array_values($this->_getTempData('sic_code',$data,$fail_code));
		$d['codes'] = array_unique($no_fail);
		$d['sno'] = isset($data['sno'])?$data['sno']:array();
		$d['gift_sno'] = isset($data['gift_sno'])?$data['gift_sno']:array();
		$d['ladderIsTrue'] = $ladderIsTrue;
		return $list = array(
			'success' => $success_data,
			'fail'    => $d,
			'number'  => count($success)
		);
	}
	private function _ladderRule(array $rules,$goods_price) {

		if(empty($rules)) return false; //阶梯规则不能为空
		$count = count($rules);
		if ($count > 3 ) {
			return false; //阶梯规则最大为3条
		}
		foreach ($rules as $k => $v) {
			$curr = current($rules);
			$next = next($rules);
			list($num,$num2) = explode('~',$curr[0]);
			list($num3,$num4) = explode('~',$next[0]);
			if ($k == 0 ) {
				$partten = '/^[\d]+~{1}$/';
				if ( preg_match($partten, $v[0]) && $count != 1) {
					return false;
				}
				if ( $num  <= 1 ) {
					return false; //起始值应该大于1
				}
			}
			$partten = '/^[\d]+~{1}([\d]+)?$/';
			
			if ( !preg_match($partten, $v[0]) ) {
				return false; //规则不正确
			}

			if ($v[1] == 0) {
				return false; //优惠价格不能为空
			}

			if ( $curr[1] <= $next[1]  && $next[1] != '') {
				return false; //优惠价格应该递减
			}
			if ($num2 > 999 || $num > 999) {
				return false; //单次购买量最大为999
			}

			if ($num2 != '') {

				if ($num2 < $num ) {
					return false; // 区间规则不正确
				}
			}
			
			if ( $v[1] >= $goods_price ) {
				return false; //优惠价格应该小于原价
			}
			if ($count != 1) {
				if ($num3-$num2 != '1' && $num3 != '') {
					return false; //购买量区间应该是连续的
				}
			}
		}
		return true;
	}
	private function _sortRules($rules) {
		$tempsort = array();
		foreach ( $rules as $k => $v ) {
			if ( $v[0] == '' || $v[1] == '' ) {
				unset($rules[$k]);
			}
			$tempsort[] = $v[0];
		}
		array_multisort($rules,SORT_ASC,$tempsort);
		return $rules;
	}
	private function _checkRules($rules,$length) {
		foreach ($rules as $v) {
			if ($v[1] > $v[0] ) {
				return false;
			}
			if ($v[1] <= 0 || $v[0] <= 0) {
				return false;
			}

		}
		if ($length == 1) return true;
		if ($length == 2) {
			return $rules[0][1]/$rules[0][0] - $rules[1][1]/$rules[1][0] < 0 ? true : false;
		}
		if ($length == 3) {
			$num1 = $rules[0][1]/$rules[0][0];
			$num2 = $rules[1][1]/$rules[1][0];
			$num3 = $rules[2][1]/$rules[2][0];
			if ($num1 < $num2 && $num2 < $num3) {
				return true;
			} else {
				return false;
			}
		}
	}
	private function _getTempData($keys,$data,$success) {
		$temp = array();
		foreach ($data as $key => $value) {
			if ( in_array($value[$keys],$success) ) {
				
				$temp[$value[$keys]] = $value;
			}
		}
		return $temp;
	}
	private function _getGoodsinfo($sic_codes,$sc_code) {
		$temp_goods = array();
		$apiPath = 'Base.StoreModule.Item.Item.storeItems';
		$p['sc_code']    = $sc_code;
		$p['is_page']    = 'NO';
		$p['is_publish'] = 'NO';
		$p['sic_codes']  = $sic_codes;
		$goods = $this->invoke($apiPath,$p);
		foreach ($goods['response'] as $k => $v) {
			$temp_goods[$v['sic_code']] = $v;
		}
		return $temp_goods;
	}
}