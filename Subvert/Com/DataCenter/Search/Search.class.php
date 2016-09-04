<?php

namespace Com\DataCenter\Search;

use System\Base;
class Search extends Base {

    public function __construct() {
        parent::__construct();
    }
    public function searchKey($params) {
    $this->_rule = array(
      array('table', 'require', PARAMS_ERROR, ISSET_CHECK), 
      array('tables', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),
      array('on', 'checkArrayInput', PARAMS_ERROR, ISSET_CHECK, 'function'),
      array('sc_code', 'require', PARAMS_ERROR, ISSET_CHECK), 
      array('value', 'require', PARAMS_ERROR, MUST_CHECK), 
      array('where', 'checkArrayInput', PARAMS_ERROR, MUST_CHECK, 'function'),
    );
    if (!$this->checkInput($this->_rule, $params)) { 
      return $this->res($this->getErrorField(), $this->getCheckError());
    }
    if (empty($params['uc_code']) && empty($params['sc_code'])) {
      return $this->res(null,5);
    }
    $where[$params['where']['key']] = array('like','%'.$params['value'].'%');
    $key = $params['where']['key'];
    unset($params['where']['key']);
    if (!empty($params['where']))  $where[] = $params['where'];
    if (!empty($params['table'])) {
      $res = D($params['table'])->field($key)->where($where)->select();
    } elseif (!empty($params['tables'])) {

      $sql = "SELECT ".$this->tablePrefix.$key." FROM {$this->tablePrefix}".$params['tables'][0];
      foreach ($params['on'] as $k => $v) {
        $ke = $k+1;
        $sql .= ' LEFT JOIN '.$this->tablePrefix.$params['tables'][$ke].' ON '.$v; 
      }
      $sql .= ' WHERE '.$this->tablePrefix.$key." like"."'%".$params['value']."%'";
      if (!empty($params['where'])) {
        foreach ($params['where'] as $ke => $va) {
          $sql .= ' AND '.$ke.'='."'".$va."'".' ';
        }
      }
      $model = D($params['tables'][0]);
      $res = $model->query($sql);
    }
    return $this->res($res);
  }
}    