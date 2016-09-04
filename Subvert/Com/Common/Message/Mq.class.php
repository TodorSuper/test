<?php

/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 轮播接口
 */

namespace Com\Common\Viwepager;
use System\Base;


class Viwepager extends Base {
	private $_rule	=	null; # 验证规则列表
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取一组轮播图片
	 * getGroup 
	 * Com.Common.Viwepager.Viwepager.getGroup
	 * @access public
	 * @return void
	 */

	public function getGroup($data) {
		$this->_rule = array(
			array('type', 'require' , PARAMS_ERROR, MUST_CHECK), # 轮播图的英文标识
		);

		if(!$this->checkInput($this->_rule, $data)) # 自动校验
			return $this->res($this->getErrorField(),$this->getCheckError());

		$where = array(
			'url' => $data['type'],
			'rt.status'=> 1,
			'r.status' => 1
		);
		
		$select = D('RotatorType')->alias('rt')->join('left join __ROTATOR__ r on r.rotator_type = rt.id')
			->field('r.goods_url,r.rotator_img,r.weight,rt.width,rt.height')->where($where)
			->order("r.weight asc")->select();

		return $this->res($select);
	}
}

?>
