<?php
/**
 * +---------------------------------------------------------------------
 * | www.yunputong.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: zhoulianlei <zhoulianlei@yunputong.com >
 * +---------------------------------------------------------------------
 * | 系统商品搜索类
 */

namespace Library;

class Search{
	protected $limits = 1000;			# 设置最大返回数量
	protected $fields = "*";			# 设置搜索返回字段
	protected $max_query_time = 3;		# 设置最大搜索时间
	protected $order_by;				# 排序规则
	private $sphinx = null;				# 操作对象
	protected $error_message;			# 错误消息
	protected $main;					# 主数据源

	final public function __construct(){
		if($this->sphinx == null) {
			$search = C('SPHINX_CONF');
			$this->limits = $search['limits'];
			$this->fields = $search['fields'];
			$this->max_query_time = $search['query_time'];
			$this->order_by = $search['order_by'];
			$this->main = $search['source'];
			$sphinx = new \SphinxClient;
			$res = $sphinx->setServer($search['host'], $search['port']);
			if($res) {
				$sphinx->setMatchMode($search['search_model']);
				$sphinx->setLimits(0, 1000, $this->limits, 0);
				$this->sphinx = $sphinx;
				
			} else {
				$this->error_message = $sphinx->GetLastError();
				return false;
			}
		}
	}

	/**
	 * 设置查询返回字段
	 * fields 
	 */
	public function fields($str) {
		if(!$str) {
			$str = $this->fields;
		}
		$this->sphinx->setSelect($str);
		return $this;
	}

	/**
	 * 设置查询最大时间
	 * queryTime 
	 */
	public function queryTime($time) {
		if(!$time) {
			$time = $this->max_query_time;
		}

		$this->sphinx->setMaxQueryTime($time);
		return $this;
	}
	
	/**
	 * 设置查询字段条件
	 * setFilter
	 */
	public function where($str, $rule) {
		$this->sphinx->setFilter($str, $rule);
		return $this;
	}

	/**
	 * 设置返回数量
	 * limit 
	 */
	public function limit($start, $end) {
		$this->sphinx->setLimits($start, $end, $this->limits, 0);
		return $this;
	}

	/**
	 * 设置排序规则
	 * setFilter
	 */
	public function orderBy($str) {
		$this->sphinx->setSortMode($this->order_by, $str);
		return $this;
	}

	/**
	 * 获取当前的搜索库
	 * @return string
	 */
	public function getSource() {
	    return $this->main;
	}
	
	/**
	 * 设置搜索库
	 * @param string $source 库名
	 *     <p>main 商品库</p>
	 *     <p>effect 辅助功效库</p>
	 *     <p>question 问题库</p>
	 */
	public function setSource($source) {
	    $this->main = $source;
	}
	
	/**
	 * 获取错误消息
	 * @return string
	 */
	public function getErr() {
	    return $this->error_message;
	}

	/**
	 * 查询
	 * setFilter
	 */
	public function query($str) {
		$res = $this->sphinx->query($str, $this->main);
		if(!$res) {
			$this->error_message = $this->sphinx->GetLastError();
			return false;
		} else {
			if($res['total'] == 0) {
				return false;
			} else {
				$data['total'] = $res['total'];
				$data['time'] = $res['time'];
				$finds = $res['matches'];
				foreach($finds as $k=>$v) {
					$v['attrs']['id'] = $k;
					$finds_z[] = $v["attrs"];
				}
				$data['data'] = $finds_z;
				return $data;
			}
		} 
	}

}

?>
