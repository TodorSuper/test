<?php



namespace Bll\Cms\Store;

use System\Base;

class Label extends Base {

    public function __construct() {
        parent::__construct();
    }
     /**
     * 获取商家标签
     * Bll.Cms.Store.Label.getPopLabelList
     * @return [return]         [List]
     */
    public function getPopLabelList($params) {
        $apiPath = 'Base.StoreModule.Basic.Label.getPopLabelData';
        $list_res = $this->invoke($apiPath,$params);
        if ( $list_res['status'] != 0) {
            return $this->endInvoke(null,$list_res['status']);
        }
        return $this->endInvoke($list_res['response']);
    }
    /*
    * Bll.Cms.Store.Label.popLabelOperate
    */
    public function popLabelOperate($params) {
        $apiPath = 'Base.StoreModule.Basic.Label.popLabelOperate';
        $status = $this->invoke($apiPath,$params);

        if ($status['status'] != 0) {
            return $this->endInvoke(null,$status['status']);
        }
        return $this->endInvoke($status['response']);
    }
}    