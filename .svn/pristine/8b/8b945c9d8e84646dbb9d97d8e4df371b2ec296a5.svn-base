<?php 

namespace Test\Bll\PopItem;

use System\Base;
class Qrcode extends Base {
    public function __construct() {
        parent::__construct();
    }
    /*
    	Test.Bll.PopItem.Qrcode.batchQrcode
    */
    public function batchQrcode() {
	    $select_res = D('IcItem')->alias('ii')
                    ->join("{$this->tablePrefix}ic_store_item isi on ii.ic_code = isi.ic_code",'LEFT')
                    ->field('isi.sic_code,isi.sc_code,ii.goods_img')
                    ->where("isi.qrcode='' and goods_img!=''")
                    ->limit(0,20)
                    ->select();
       	$api = 'Base.ItemModule.Item.Item.qrcode';

       	if (!empty($select_res)) 
       	{
       		foreach ($select_res as $v) {
	       		$status = $this->invoke($api,$v);
	       		if ($status['status'] != 0) {
	       			error_log($status['message'],0);
             
	       		}
       		}
       		return $this->endinvoke('ok',0);
       	} else {
       		return $this->endinvoke('fail',100000);
       	}
       	

    }
}