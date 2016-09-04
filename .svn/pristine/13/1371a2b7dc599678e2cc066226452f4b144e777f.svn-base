<?php
/**
 * Created by JetBrains PhpStorm.
 * User: renyimin
 */
namespace Bll\Pop\User;
use System\Base;

class Privs extends Base {

    private $_rule = null; # 验证规则列表

    public function __construct() {
        parent::__construct();
    }
	
	
	/**
     * 获取权限节点列表
     * Bll.Pop.User.Privs.getNodeList
     * @params type null
     * @access public
     * @return void
     */
	public function getNodeList() {
		$apiPath = 'Base.StoreModule.User.Privs.nodeList';
        $response = $this->invoke($apiPath);
        return $this->res($response['response'],$response['status']);
	}
	
	
	/**
     * 权限节点编辑
	 * Bll.Pop.User.Privs.nodeEdit
     * @access  public
     * @return  void
     */
    public function nodeEdit ($params) {
		$apiPath = 'Base.StoreModule.User.Privs.nodeEdit';
        $response = $this->invoke($apiPath, $params);
        return $this->res($response['response'],$response['status']);
    }

    /**
     * Bll.Pop.User.Privs.nodeInfo
	 * @access  public
     * @return  void
     */

    public function nodeInfo ($params) {
        $apiPath = 'Base.StoreModule.User.Privs.nodeInfo';
        $response = $this->invoke($apiPath, $params);
        return $this->res($response['response'],$response['status']);
    }
	
	/**
     * 权限节点添加
	 * Bll.Pop.User.Privs.addNode
     * @access  public
     * @return  void
     */
    public function addNode ($params) {
		$apiPath = 'Base.StoreModule.User.Privs.add';
        $response = $this->invoke($apiPath, $params);
        return $this->res($response['response'],$response['status']);
    }
	
}