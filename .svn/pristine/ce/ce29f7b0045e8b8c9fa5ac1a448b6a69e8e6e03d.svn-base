<?php

/**
 * +---------------------------------------------------------------------
 * | www.liangren.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: haowenhui <haowenhui>
 * +----------------------------------------------------------------------
 * | elasticsearch sdk
 */

namespace Search;
use \Requests as http;

class requestBody{
	public $where;
	public $data = array();		# 处理好的restful body
	public $mappingData;		# 字段类型映射数据
	public $indexMapping;		# 创建index时的mapping
	public $sort;
	public $from;
	public $size;
	public $field;
	public $like;
	public $match;
	public $bulk;
	public $bulkData;
	public $groupData;
	public $groupType;
	public $postFilter;
}

class requestUri{
	public $method;
	public $map;				# _mapping/
	public $create;				# _create
	public $search;				# _search
	public $primarykey;			# 当前操作的主键id [string]
	public $primaryVersion;		# 当前主键的版本
	public $count;
}

class ElasticSearch{

	# emum
	const ADD = 1;
	const DEL = 2;
	const UPDATE = 3;

	# options
	const INDEX = 'index';
	const DELETE = 'delete';
	const AND_ = 'and';
	const OR_ = 'or';


	private $host;
	private $port;
	private $httpCode;
	private $httpHeader;
	private $httpBody;
	private $err;
	private $termMap = array( 
		'or'=> 'should',
		'and' => 'must',
		'neq' => 'must_not',
	);
	private $yaml;
	private $bulkAction = array(
		'index', 'delete', 'update', 'delete'
	);
	private $totalType = array(
		'avg','min','max','sum','extended_stats',
	);
	
	# body参数
	private $_body;

	# uri参数
	private $_uri;
	private $uri;						# 处理好的restful uri
	private $lastRequests;
	public $url;				# http://est.lr.com:9200/
	public $addr;				# maliro/order  或 maliro/
	public $type;				# order/
	public $index;				# malro/

	public function __construct() {
		$this->init();
		$this->yaml =  new \Plugin\Yaml();
	}

	/**
	 * setConf 
	 */
	public function setConf($config){
		$this->host = $config['host'];
		$this->port = $config['port'];
	}
	
	/**
	 * setIndex
	 */
	public function setIndex($index = 'none'){
		$this->addr = $index.'/';
		$cut = explode('/', $index);
		$this->index = isset($cut[0]) ? $cut[0].'/' : null;
		$this->type = isset($cut[1]) ? $cut[1].'/' : null;
		$this->url = 'http://'.$this->host.':'.$this->port.'/';
	}

	/**
	 * index 
	 * @param mixed $name		index名称
	 * @param mixed $action		操作类型 self::ADD self::DEL
	 */
	public function index($action) {
		if($action == self::ADD && $this->addr ) {
			$this->_uri->method = 'put';
			$this->_body->data = $this->_body->indexMapping;
			return $this->_invoke();
		}

		if($action == self::DEL  && $this->addr) {
			$this->_uri->method = 'delete';
			return $this->_invoke();
		}
	}
	
	/**
	 * 删除一行文档记录
	 * delete 
	 */
	public function delete($id) {
		$id != null && $this->_uri->primarykey = $id;
		$this->_uri->method = 'delete';
		if($this->type && $this->index && $this->type != '*' && $this->index != '*') {
			return $this->_invoke();
		}else {
			$this->err = 'must input index and type!';
			return false;
		}
	}


	/**
	 * 限制条件
	 * limit 
	 * size, from
	 */
	public function limit($a, $b = null, $c=null) {
		if($c != null) { # 内部设置参数
			if( $this->_body->from || $this->_body->size ) {
				return;
			}
		}
		if($b == null) {
			$this->_body->from = 0;
			$this->_body->size = $a;
		}else {
			$this->_body->from = $a;
			$this->_body->size = $b;
		}
		
		return $this;
	}

	/**
	 * 后置过滤器
	 * having 
	 */
	public function having($data, $action = self::AND_) {
		if( isset($data) ) {
			$whereData = $this->_parseTerm($data, $action);
			$action = $this->termMap[$action];
			if( isset($whereData[$action][0]) ) {
				$havingData['post_filter']['bool'] = $whereData;
			}else {
				$havingData['post_filter'] = $whereData;
			}
			$this->_body->postFilter = $havingData;
			return $this;
		}
	}

	/**
	 * 设置获取的字段
	 * field 
	 * @param mixed $string or array 
	 */
	public function field($field) {
		if( is_string($field) ) {
			$field = explode(',', $field);
		}
		$this->_body->field = $field;
		return $this;
	}

	/**
	 * 设定排序条件
	 * order 
	 */
	public function order($data) {
		$sort;

		# 处理string
		if( is_string($data) ) {
			$data = explode(',', $data);
			foreach($data as $k=>$v) {
				$cut = explode(' ', trim( $v ) );
				if(isset($cut[0]) && isset($cut[1]) && isset($cut[2]) ) {
					$sort[$cut[0]] = ['order'=>$cut[1], 'mode'=>$cut[2]];
				}elseif( isset($cut[0]) && isset($cut[1]) ) {
					$sort[$cut[0]] = ['order'=>$cut[1]];
				}
			}
		}else {
			# 处理array
			foreach($data as $k=>$v) {
				if( is_array($v) ) {
					if(isset( $v[1] ) ) {
						$sort[$k] = ['order'=>$v[0], 'mode'=>$v[1]];
					}else {
						$sort[$k] = ['order'=>$v[0]];
					}
				}else {
					$sort[$k] = ['order'=>$v];
				}
			}
		}
		if($sort != null) {
			$this->_body->sort = $sort;
		}
		return $this;
	}

	/**
	 * match
	 */
	public function match($data, $per = null) {
		foreach($data as $k=>$v) {
			$cut = explode(',', trim( $k ) );
			if(count($cut) > 1) {
				$per != null && $match['multi_match'] = ['query'=>$v, 'fields'=>$cut, 'minimum_should_match'=>$per];
				$per == null && $match['multi_match'] = ['query'=>$v, 'fields'=>$cut];
			}else {
				$per != null && $match['match'] = [$k=>['query'=>$v, 'minimum_should_match'=>$per]];
				$per == null && $match['match'] = $v;
			}
		}
		$this->_body->match = $match;
		return $this;
	}

	/**
	 * 批处理
	 * bulk
	 */
	public function bulk($action, $data) {
		if(in_array($action, $this->bulkAction) && is_string($action) && $this->type && $this->index) {
			$data = $this->_parseInput($data);
			$bulk = array(
				$action => array(
					'_index' => rtrim( $this->index , '/'),
					'_type' => rtrim( $this->type , '/'),
				)
			);
			$this->_uri->primarykey != null && $bulk[$action]['_id'] = $this->_uri->primarykey;
			$action == 'update' && $bulk[$action]['_retry_on_conflict'] = 3;
			$this->_body->bulkData .= json_encode($bulk, JSON_UNESCAPED_UNICODE)."\n";
			if( $action != 'delete' ) {
				!is_array($data) && $data = [];
				$this->_body->bulkData .= json_encode($data, JSON_UNESCAPED_UNICODE)."\n";
			}
		}
		return $this;
	}

	public function exec($callback = null) {
		if( $this->_body->bulkData ) { # 执行bulk逻辑
			$res = $this->query('post', '_bulk', $this->_body->bulkData);
			$callback != null &&  $res != false && $res = $callback($res);
			return $res;
		}
	}

//	public function join() {}
	
	/**
	 * group 
	 * 桶 + 指标
	 * 桶中桶 + 指标
	 * 条形图 线形图 时间图
	 * 聚合器 + 桶 + 指标
	 * 桶过滤器 + 指标
	 * 前置过滤器
	 */
	public function group($data) {
	//		$this->_body->groupType = 'count';
		$this->limit(1,1,true);
		if( is_string($data) ) { # json 和 yaml直接查询
			$data = $this->_parseInput($data);
			$this->_body->groupData = json_encode($data, JSON_UNESCAPED_UNICODE);
		}elseif(is_array($data)) {
			$group = $this->_parseGroup($data);
			$this->_body->groupData = $group;
		}
		return $this;
	}

	/**
	 * 查询条件设定
	 * where
	 * term
	 *
	 */
	public function where($data, $term = self::AND_) {
		$this->_body->data = $this->_parseWhere($data, $term);
		return $this;
	}

	public function select() {
		$this->_uri->method = 'get';
		$this->_uri->search = '_search';
		try{
			$this->_invoke();
		}catch(\Exception $e) {
			return false;
		}
		return $this->_res();
	}

	/**
	 * count
	 */
	public function count() {
		$this->_uri->method = 'get';
		$this->_uri->count = '_count';
		return $this->_invoke();
	}


	/**
	 * 给索引别名
	 * alias
	 * @param mixed $name		别名
	 * @param mixed $action     操作
	 */
	public function alias($name, $action) {
		$strcuts = array(
			'actions' => array(
				array(),
			)
		);

		$this->index = rtrim($this->index, '/');

		switch($action) {
		case self::ADD:						   # 新增别名
			$strcuts['actions'][0] = array(
				'add' => array(
					'index' => $this->index,
					'alias' => $name
				)
			);
			break;
		case self::DEL:						   # 删除别名, 不支持批量
			$strcuts['actions'][0] = array(
				'remove' => array(
					'index' => $this->index,
					'alias' => $name
				)
			);
			break;
		case self::UPDATE:						# 原子操作, 删除后创建
			if( !is_array($name) ) {
				return false;
			}
			$old = key($name);
			$new = $name[$old];
			$strcuts['actions'][0] = array(
				'remove' => array(
					'index' => $this->index,
					'alias' => $old
				)
			);
			$strcuts['actions'][1] = array(
				'add' => array(
					'index' => $this->index,
					'alias' => $new
				),
			);
			break;
		default : 
			$this->err = 'not found action';
			return false;
		}

		return $this->query('post', '_aliases', $strcuts);
	}

	/**
	 * 通用指令集发送
	 * query 
	 * @param mixed $method	请求方法
	 * @param mixed $urn	请求资源部分描述
	 * @param mixed $data	请求数据体
	 */
	public function query($method, $urn, $data = null) {
		$this->_uri->method = $method;
		$this->uri = $this->url.$urn;
		try{
			is_string($data) && $data = $this->_parseInput($data);
			$this->_body->data = $data;
			return $this->_invoke(['query'=>true]);
		}catch(\Exception $e) {
			$this->err = $e->getMessage();
			return false;
		}
	}

	/**
	 * 解析输入字符串为数组 (支持 json 和 yaml的标准格式)
	 * _parseInput 
	 */
	private function _parseInput($data) {
		$old = $data;
		if( is_array( $data ) ) {
			return $data;
		}elseif(is_string($data))  {
			$data = trim($data);
			$firstStr = substr($data, 0, 1);
			if($firstStr == '{') {
				$data = json_decode($data, true);
			}elseif($firstStr == '-') {
				$data = $this->yaml->loadString($data);
			}else {
				$data = $this->yaml->loadString($data);
			}
			/*
			if( !is_array($data) ) {
				$this->err = 'connot parse input string';
				throw new \Exception($this->err);
			}*/
		}
		if(is_array($data)) {
			return $data;
		}else {
			return $old;
		}
	}

	/**
	 * 根据id获取一条记录
	 * find
	 */
	public function find($id = null) {
		$id != null && $this->_uri->primarykey = $id;
		if(!$id || !$this->_uri->primarykey) {
			$this->_uri->search = '_search';
		}
		$this->_uri->method = 'get';
		$this->_invoke();
		return $this->_res();
	}

	/**
	 * 新增一条doc, 存在则增加失败
	 * add
	 */
	public function add($data) {
		if( !$this->_uri->primarykey ) {
		   	$this->_uri->method = 'post';
		}else {
			$this->_uri->create = '_create';
			$this->_uri->method = 'put';
		}

		try{
			$this->_body->data = $data;
			return $this->_invoke();
		}catch(\Exception $e){
			return false;
		}
	}


	/**
	 * 设置要操作的文档id
	 * id 
	 */
	public function id($id) {
		$this->_uri->primarykey = $id;
		return $this;
	}

	/**
	 * 更新文档
	 * save 
	 */
	public function save($data) {
		$this->_uri->method = 'put';
		try{
			$this->_body->data = $data;
			return $this->_invoke();
		}catch(\Exception $e) {
			return false;
		}
	}

	/**
	 * 创建更新锁
	 * lock 
	 */
	public function lock($version) {
		$this->_uri->primaryVersion = $version;
		return $this;
	}

	/**
	 * 获取映射结构 maliro/order | maliro |  * 
	 * getmap 
	 */
	public function getMap() {
		$this->_uri->method = 'get';
		$this->_uri->map = '_mapping/';
		return $this->_invoke();
	}

	/**
	 * 指定字段的映射类型
	 * addTypeMap
	 */
	public function addTypeMap($data) {
		// $data = ['name'=>['type'=>'string']]
		$this->_uri->method = 'put';
		$this->_uri->map = '_mapping/';
		try{
			if($this->type && $this->index && !$this->_body->mappingData) {
				$iData['properties'] = $data;
				$this->_body->data = $iData;
				return $this->_invoke();
			}

		}catch(\Exception $e) {
			return false;

		}
	}

	/**
	 * 设置文档的映射类型
	 * setmap 
	 */
	public function setMap($data = array()) {
		is_string($data) && $data = $this->_parseInput($data);
		if($this->index && !$this->_body->mappingData && $data != null)	 {
			$this->_body->indexMapping = $data;
		}
		return $this;
	}


	/**
	 * 解析返回结果
	 * _res 
	 */
	private function _res() {
		$data = $this->httpBody;
		if($this->last_groupType != null) {
			return $data['aggregations'];
		}

		if( isset($data['_source']) ) {
			$rData ['_id'] = $data['_id'];
			$this->last_match != null && $rData ['_score'] = $data['_score'];
			$rData['_version'] = $data['_version'];
			$rData = array_merge($rData, $data['_source']);
		}
		
		if( isset($data['aggregations']) ) {
			$rData['aggregations'] = $data['aggregations'];
		}

		if(isset($data['hits']['hits'])) {
			foreach($data['hits']['hits'] as $v) {
				$v['tmp']['_id'] = $v['_id'];
				$this->last_match != null && $v['tmp']['_score'] = $v['_score'];
				$rData[] = array_merge($v['tmp'], $v['_source']);
			}
		}

		return $rData;
	}

	/**
	 * 解析uri
	 * _parseUri
	 */
	private function _parseUri() {
		$uri = $this->_uri;

		# 处理存在主键id的数据
		if($uri->primarykey != null) {
			$uri->primaryVersion != null && $this->uri .= '?version='.$uri->primaryVersion;
			$this->uri = $this->url.$this->addr.$uri->primarykey.'?';
			if( $this->_body->field != null) {
				$field = implode(',', $this->_body->field);
				$this->uri .= '_source='. $field;
			}
			return;
		}elseif($this->_body->mappingData != null ) {
			$this->uri = $this->url.$this->addr;
		}else {
			$this->uri = $this->url.$this->addr;
		}

		if($uri->count != null) {
			$this->uri .= $uri->count.'?';
			return;
		}


		# 搜索处理
		if($uri->search != null) {
			$this->uri .= $uri->search.'?';
		}
		
		# 聚合处理
		if($this->_body->groupType == 'count') {
			$this->uri .=  'search_type=count';
			$this->last_groupType = $this->_body->groupType;
			return;
		}

		if($uri->map != null) {   # 获取映射信息
			$this->uri = $this->url.$this->index.$uri->map.$this->type;
			return;
		}

		if($uri->create != null) { # 创建数据
			$id = $uri->primarykey;
			$id != null ? $this->uri = $this->url.$this->addr.$id.'/_create' :  $this->uri = $this->url.$this->addr.'_create' ;
			return;
		}

	}

	/**
	 * 解析调用body
	 * _parseData 
	 */
	private function _parseData() {
		$body = $this->_body;
		if($body->mappingData != null ) {
			$strcuts =array( 
				'mappings'=>array(
					$this->type => array(
						'properties' => $body->mappingData
					)
				),
			);
			return $strcuts;
		}

		# 处理分页
		if( !$this->_uri->primarykey   && ( $body->from || $body->size) ) {
			$body->data['size'] = $body->size;
			$body->data['from'] = $body->from;
		}

		if( !$this->_uri->primarykey  && $body->field != null) {
			$body->data['_source'] = $body->field;
		}

		if($body->sort) {
			$body->data['sort'] = $body->sort;
		}


		if(is_array( $body->match) ) {
			isset($body->match['match']) && $match = 'match';
			isset($body->match['multi_match']) && $match = 'multi_match';
			if( isset($body->data['query']['filtered']) ) {
				$body->data['query']['filtered'][$match] = $body->match[$match];
			}else{
				$body->data['query'][$match] = $body->match[$match];
			}
			
			$this->last_match = $body->match;
		}

		if($body->groupData != null && is_string( $body->groupData) ) {
			$body->data = $body->groupData;
		}

		if($body->groupData != null && is_array( $body->groupData) ) {
			$groupKey = key($body->groupData);
			isset($body->groupData['aggs']) && $body->data['aggs'] = $body->groupData['aggs'];
		}

		if($body->postFilter != null ) {
			$body->data['post_filter'] = $body->postFilter['post_filter'];
		}


		return $body->data;

	}

	/**
	 * 获取最后一条操作信息
	 * getLastSearch 
	 */
	public function getLastSearch() {
		is_array($this->lastRequestsBody) && $this->lastRequestsBody = json_encode($this->lastRequestsBody, JSON_UNESCAPED_UNICODE);
		$this->lastRequestsBody == '[]' && $this->lastRequestsBody = '{}';
		return $this->lastRequests." -d '".$this->lastRequestsBody."'";
	}

	/**
	 * 获取错误信息
	 * getError 
	 */
	public function getError() {
		return $this->err;
	}

	private function _invoke($params = array()) {
		if( !isset($params['query']) ) {
			$data = $this->_parseData();	# 1. 解析数据
			$this->_parseUri();		# 2. 解析uri
		}else {
			$data = $this->_body->data;
		}
		$uri = $this->uri;
		$method = $this->_uri->method;
		$this->lastRequests = 'curl -X'.strtoupper( $method ). ' '.$uri;
		//$this->lastRequestsBody = $this->_body->data;
		$this->init();
		$response = http::{$method}($uri, null, $data);
		$this->httpCode = $response->status_code;
		$this->httpHeader = $response->headers->data;
		$this->httpBody = json_decode( $response->body , true);
		$this->_parseErrRes($response);
		return $this->httpBody;
	}

	private function _parseErrRes(&$response) {
		if( isset($this->httpBody['error'])) {
			$info = $this->httpBody['error']['failed_shards'][0]['reason']['reason'];
			$err = 'ElasticSearch ERROR: '.$this->httpBody['error']['reason'].' status: '. $this->httpBody['status'] . ' info:'. $info ;
			$this->err = $err;
			throw new \Exception($err);
		}
	}

	private function _parseWhere($where, $term ) {
		$termMap = $this->termMap[$term];
		$res = $this->_parseTerm($where, $term);
		# term 组装
		isset($res[$termMap]) && $data['query']['filtered']['filter']['bool'][$termMap] = 	$res[$termMap];
		
		# 复合条件范围查询
		isset($res['range']) && isset($res[$termMap]) && $data['query']['filtered']['filter']['bool'][$termMap][]['range'] = $res['range'];
		
		# 单条件范围查询处理
		isset($res['range']) && !isset($res[$termMap]) && $data['query']['filtered']['filter']['range'] = $res['range'];

		# must_not 处理
		isset($res['must_not']) && $data['query']['filtered']['filter']['bool']['must_not'] = $res['must_not'];
		return $data;
	}

	/**
	 * 解析条件
	 * _parseTerm 
	 */
	private function _parseTerm($data, $term) {
		$tmp;		# term  迭代寄存器
		$tmp2;		# range 迭代寄存器
		$tmp3;		# where 递归寄存器
		$term2;		# where 递归类型
		$must_not;	# not   寄存器
		$match;		# 标准查询寄存器
		
		foreach($data as $k=>$v) {
			if(isset($this->termMap[$k])) { # 递归处理外层
				$res = $this->_parseTerm($v, $k);
				$tmp3 = $res;
				$term2 = $this->termMap[$k];
				continue;
			}


			if( isset($v['match']) && !isset($this->termMap[$k]) ) { # 处理嵌套型标准查询
				$this->_body->match == null && $this->_body->match = true;
				$cut = explode(',' , trim($k) );
				if(count($cut) > 1) {
					$matchData = ['query'=>$v['match'], 'fields'=>$cut];
					isset($v['per']) && $v['minimum_should_match'] = $v['per'];
					unset($v['per']);
					unset($v['match']);
					$tmp[]['multi_match'] = array_merge($matchData, $v);
				}else {
					$matchData = ['query'=>$v['match']] ;
					isset($v['per']) && $v['minimum_should_match'] = $v['per'];
					unset($v['per']);
					unset($v['match']);
					$tmp[]['match'][$k] = array_merge($matchData, $v);
				}
				continue;
			}

			if(is_array($v)) {
				$nest = $this->_parseNest($k);
				foreach($v as $k1=>$v1) {		# 迭代处理内层
					if( $k1 == 'in' ) {
						if( $nest !=  null ) {
							$nest['nested']['query']['terms'] = [$k=>$v1];
							$tmp[] = $nest;
						}else {
							$tmp[]['terms'] = [$k => $v1];
						}
						continue;
					}
					if( $k1 == 'not' ) {
						if( $nest !=  null ) {
							$nest['nested']['query']['term'] = [$k=>$v1];
							$must_not[] = $nest;
						}else {
							$must_not[]['term'][$k] = $v1;
						}

						continue;
					}
	
					if( $k1 == 'notin' ) {
						if( $nest !=  null ) {
							$nest['nested']['query']['terms'] = [$k=>$v1];
							$must_not[] = $nest;
						}else {
							$must_not[]['terms'] = [$k => $v1];
						}
						
						$must_not[]['terms'] = [$k => $v1];
						continue;
					}
	
					if( $k1 == 'like' ) {
						if( $nest !=  null ) {
							$nest['nested']['query']['wildcard'] = [$k=>$v1];
							$tmp[] = $nest;
						}else {
							$tmp[]['wildcard'] = [$k => $v1];
						}
						continue;
					}

					if(in_array($k1, ['gt','lt','gte','lte'])) {
						if( $nest !=  null ) {
							$nest['nested']['filter']['range'][$k][$k1] = $v1;
							$tmp[] = $nest;
						}else {
							$tmp2[$k][$k1] = $v1;
						}
					}
				}

			}else {
				$nest = $this->_parseNest($k,$v);
				$nest ? $tmp[] = $nest : $tmp[] = array('term' => [ $k=>$v ]) ;
			}
		}

		$termMap = $this->termMap[$term];

		$tmp != null && $rData[$termMap] = $tmp;									# 精确查询条件组装

		if(!isset($tmp3[$term2]) && !isset($tmp3['must_not'])  ) {					# 递归查询条件组装
			$tmp3 != null && $term2 !=null  &&  $rData[$termMap][]['bool'][$term2] = $tmp3;
		}else {
			$tmp3 != null && $term2 !=null  &&  $rData[$termMap][]['bool'] = $tmp3;
		}

		if( isset($rData[$termMap]) ) {												# 范围查询条件组装
			$tmp2 != null  && $rData[$termMap][]['range'] = $tmp2;
		}else {
			$tmp2 != null  && $rData['range'] = $tmp2;
		}
		
		$must_not != null  && $rData['must_not'] = $must_not;
		return $rData;
	}

	private function _parseNest($key=null, $value = null) {
		$nest = null;
		$path = null;
		$pos = strrpos($key, '.', -1);
		if($pos) {
			$path = substr($key, 0, $pos);
			$nest['nested']['path'] = $path;
		}
		if($value != null && $pos != null) {
			$nest['nested']['query']['term'] = [$key=>$value];
			return $nest;
		}else {
			return $path;
		}
	}

	private function _parseGroup($data) {
		$res =  $this->_parseBuckets($data);
		return $res;
	}

	private function _parseBuckets($data) {
		$tmp;					# 初始解析寄存器
		$tmp2;					# 递归解析寄存器
		$tmpNest;				# 套嵌桶
		$tmpNestBucket;			# 当前迭代的套嵌桶寄存器
		$tmpBucket;				# 当前迭代的桶寄存器
		$tmpActionType;			# 当前操作动作
		$global;				# 当前迭代的全局域数据
		$filter;				# 桶过滤
		$bucketType = array(
			'terms',			# 桶
			'histogram',		# 柱状图桶
			'date_histogram',	# 日期柱状图桶
			'all',				# 全局桶
			'filter',			# 过滤桶
			'cardinality',		# 去重桶 (distinct)
			'range',			# 范围桶
			'percentiles',		# 百分比桶
		);

		foreach($data as $k => $v) {
			# 清理寄存器
			$global = null;
			$tmpActionType = null;

			if(in_array($k, $bucketType) or in_array($k, $this->totalType)) {
				$tmpActionType = $k;
				if($k == 'all') {
					$global['all']['global'] = [];
				}

				if($k == 'filter') {
					$filter = array();
				}

				foreach($v as $k1 => $v1) {
					if(in_array($k1, $bucketType) && $k1 != 'filter' && $k1 != 'post_filter' ) {  # 桶中桶
						$tmp2 = $this->_parseBuckets([$k1=>$v1]);
						continue;
					}
					if($k1 == 'filed') {
						throw new \Exception($this->err = 'search syntax error, cannot identify "field" , try "field" to use!');
					}
					if($k1 == 'field') {							# 桶
						// 过滤桶
						if( is_array($filter) ) {
							if(!isset($v['where'][0])) {
								$where = $v['where'];
								$action = self::AND_;
							}elseif( isset($v['where'][0]) ) {
								$where = $v['where'][0];
								$action = $v['where'][1] ? : self::AND_;
							}else {
								continue;
							}
							$whereData = $this->_parseTerm($where, $action);
							$action = $this->termMap[$action];
							if( isset($whereData[$action]) ) {
								$filter['filter']['bool'] = $whereData;
							}else {
								$filter['filter'] = $whereData;
							}
							$tmpBucket = $v1;
							$tmp['aggs'][$tmpBucket] = $filter;
							$this->_body->groupType != null && $this->_body->groupType = null;
							continue;
						}

						// 标准桶 + 全局桶
						$parseData = $this->_parseBucketsTerms($tmpActionType ,$v1);
						$tmpBucket = $parseData[1];
						$path = $parseData[2];
						if($global == null ){
							if($path != null) {
								$tmpNest[$path]['nested']['path'] = $path;
								$tmpNest[$path]['aggs'][$tmpBucket] = $parseData[0][$tmpBucket];
								$tmpNestBucket = $path;
							}else {
								$tmp['aggs'][$tmpBucket] = $parseData[0][$tmpBucket];
							}

						}else {
							$global['all']['aggs'][$tmpBucket] = $parseData[0][$tmpBucket];

						}
						continue;
					}

					if(in_array($k1, $this->totalType)) {			# 指标
						$parseData = $this->_parseTarget($k1, $v1);
						$key = $parseData[1];
						$path = $parseData[2];
						if($global == null ) {
							if( $tmpBucket == null ) {
								throw new \Exception($this->err = 'undefind "field" => value , you must set it!');
							}
							if($path != null) {
								$tmpNest[$path]['aggs'][$tmpBucket]['aggs'][$key] =  $parseData[0][$key];
							}else {
								$tmp['aggs'][$tmpBucket]['aggs'][$key] = $parseData[0][$key];
							}
						}else {
							$global['all']['aggs'][$key] = $parseData[0][$key];
						}
						continue;
					}

					# 其他参数
					$filter == null && $tmp['aggs'][$tmpBucket][$tmpActionType][$k1] = $v1;
				}
			}

		}

		# 处理返回值
		if($tmpNest != null ) {
			$path = key($tmpNest);
			$tmp['aggs'][$path] = $tmpNest[$path];
		}

		if($tmp2 != null && $tmpNest != null) {
			if( !isset( $tmp2['aggs'][$tmpNestBucket] ) ) {
				$tmp['aggs'][$path]['aggs'][$tmpBucket]['aggs'] = $tmp2['aggs'];
			}else {
				$tmp2Key = key($tmp2['aggs']);
				$tmp['aggs'][$path]['aggs'][$tmpBucket]['aggs'] = $tmp2['aggs'][$tmp2Key]['aggs'];
			}

		}else{
			$tmp2 != null && $tmp['aggs'][$tmpBucket]['aggs'] = $tmp2['aggs'];
		}

		$global != null && $tmp['aggs']['all'] = $global['all'];

		return $tmp;
	}

	private function _parseBucketsTerms($type, $data) {
		$cut = explode(' in ', $data);
		if(isset($cut[1])) {
			$termsField = trim($cut[0]);
			$termsName = trim($cut[1]);
		}else {
			$termsField = $data;
			$termsName = $termsField;
		}
		$res = array(
			$termsName => array(
				$type => ['field' => $termsField],
			)
		);
		
		$path = $this->_parseNest($cut[0]);

		return [0=>$res, 1=>$termsName, 2=>$path];
	}

	private function _parseTarget($method , $data) {
		$cut = explode(' in ', $data['field']);
		if(isset($cut[1])) {
			$targetField = trim($cut[0]);
			$targetName = trim($cut[1]);
		}else {
			$targetField = $data['field'];
			$targetName = $targetField;
		}

		$res = array(
			$targetName => array(
				$method => ['field' => $targetField],
			),
		);
		
		$path = $this->_parseNest($cut[0]);
		return [0=>$res, 1=>$targetName, 2=>$path];
	}

	private function init() {
		$this->_body = new requestBody();
		$this->_uri = new requestUri();
	}
}


?>
