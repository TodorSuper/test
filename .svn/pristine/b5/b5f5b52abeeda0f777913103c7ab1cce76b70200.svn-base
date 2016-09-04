<?php
/**
 * +---------------------------------------------------------------------
 * | www.liangrenwang.com 粮人网
 * +---------------------------------------------------------------------
 * | Copyright (c) 2015 http://www.yunputong.com  All rights reserved.
 * +---------------------------------------------------------------------
 * | Author: yindongyang <yindongyang@liangrenwang.com >
 * +---------------------------------------------------------------------
 * | pop促销中心数据导出
 */
namespace Bll\Pop\Spc;

use System\Base;

class CenterInfo extends Base{

    /**
     * Bll.Pop.Spc.CenterInfo.export
     * @param type $params
     * @return type
     */
    public function export($params){
        $preHeat=$params['preheat'];
        $publish=$params['publish'];
        $end=$params['end'];
        $draft=$params['draft'];
        $preHeat ? $status[]=$preHeat : null;
        $publish ? $status[]=$publish : null;
        $end     ? $status[]=$end     : null;
        $draft   ? $status[]=$draft   : null;
        $reward_gift=$params['reward_gift'];
        $special=$params['special'];
        $ladder=$params['ladder'];
        $type=array();
        $reward_gift ? $type[]=$reward_gift : null;
        $special     ? $type[]=$special     : null;
        $ladder      ? $type[]=$ladder    : null;

        $params['type']=$type;
        $params['status']=$status;
        if($params['search_spc']){
            if(preg_match('/\w/',substr(trim($params['search_spc']),0,1))){
               $params['sic_no']=$params['search_spc'];
            }else{
               $params['goods_name']=$params['search_spc'];
            }
        }
        $apiPath='Base.SpcModule.Center.Spc.Export';

        $res=$this->invoke($apiPath,$params);

        $this->endInvoke($res['response'],$res['status'],'',$res['message']);
    }

    /**
     * Bll.Pop.Spc.CenterInfo.lists
     * @param type $params
     * @return type
     */
    public function lists($params){
        //得到促销列表和商品列表的联合查询
        $apiPath='Base.SpcModule.Center.Spc.lists';
        $res=$this->invoke($apiPath,$params);
        if($res['status']!=0){
            $this->endInvoke(null,$res['status'],'',$res['message']);
        }
        $spc_codes=array();
        foreach($res['response']['lists'] as $val){
            if($val['type']=='REWARD_GIFT'){
                $spc_codes[]=$val['spc_code'];
            }
        }
        if(empty($spc_codes)){
            $res2['response']=array();
        }else{
            $params['spc_codes']=$spc_codes;
            //得到促销列表和满赠列表的查询
            $apiPath='Base.SpcModule.Gift.GiftInfo.lists';
            $res2=$this->invoke($apiPath,$params);
            if($res2['status']!=0){
                $this->endInvoke(null,$res2['status'],'',$res2['message']);
            }
        }
        //得到促销列表和特价列表的查询
        $codes=array();
        foreach($res['response']['lists'] as $val){
            if($val['type']==SPC_TYPE_SPECIAL){
                $codes[]=$val['spc_code'];
            }
            if($val['type']==SPC_TYPE_LADDER){
                $ladder_codes[]=$val['spc_code'];
            }
        }
        $arr=array(
            'spc_codes'=>$codes,
        );
        if(empty($codes)){
            $special['response']=array();
        }else{
            $apiPath='Base.SpcModule.Special.SpecialInfo.lists';
            $special=$this->invoke($apiPath,$arr);
            if($special['status']!=0){
                $this->endInvoke(null,$special['status'],'',$special['message']);
            }
        }
//          得到促销列表和阶梯价的查询
        $ladder_codes['spc_codes']=$ladder_codes;
        if(empty( $ladder_codes['spc_codes'])){
            $ladder['response']=array();
        }else{
            $apiPath='Base.SpcModule.Ladder.LadderInfo.lists';
            $ladder=$this->invoke($apiPath,$ladder_codes);
            if($ladder['status']!=0){
                $this->endInvoke(null,$ladder['status'],'',$ladder['message']);
            }
        }
        unset($params['status']);
        unset($params['goods_name']);
        unset($params['page']);

        //得到满赠商品的信息
        $sic_codes=array();
        foreach($res2['response'] as $val){
            $sic_codes[]=$val['gift_sic_code'];
        }
        if(empty($sic_codes)){
            $res3=array();
        }else{
            $params['sic_codes']=$sic_codes;
            $params['status']=IC_STORE_ITEM_ON;
            $apiPath='Base.StoreModule.Item.Item.storeItems';
            $res3=$this->invoke($apiPath,$params);
            if($res3['status']!=0){
                $this->endInvoke(null,$res3['status'],'',$res3['message']);
            }
        }
        //将几次的查询的信息合并得到促销列表
        $lists=array();
        foreach($res['response']['lists'] as $key=>$val){
            $res['response']['lists'][$key]['spc_info']=array(
                'start_time'=>$val['start_time'],
                'end_time' =>$val['end_time'],
                'spc_code'=>$val['spc_code'],
                'sic_code'=>$val['sic_code'],
                'status'  =>$val['status'],
                'type'    =>$val['type'],
                'sc_code'=>$val['sc_code'],
                'spc_title'=>$val['spc_title'],
            );
            unset($res['response']['lists'][$key]['start_time']);
            unset($res['response']['lists'][$key]['end_time']);
            unset($res['response']['lists'][$key]['status']);
            unset($res['response']['lists'][$key]['type']);
            unset($res['response']['lists'][$key]['spc_title']);
            foreach($special['response'] as $spc){
                if($val['spc_code']==$spc['spc_code']){
                    $res['response']['lists'][$key]['spc_info']['spc_detail']=$spc;
                }
            }
            foreach($ladder['response'] as $lad){
                if($val['spc_code']==$lad['spc_code']){
                    $res['response']['lists'][$key]['spc_info']['spc_detail']=$lad;
                }
            }
            foreach($res2['response'] as $k=>$v){
                if($val['spc_code']==$v['spc_code']){
                    $res['response']['lists'][$key]['spc_info']['spc_detail']=$v;
                }
                 foreach($res3['response']['lists'] as $kkk=>$vvv){
                    if($vvv['sic_code']==$res['response']['lists'][$key]['spc_info']['spc_detail']['gift_sic_code']){
                        $res['response']['lists'][$key]['spc_info']['spc_detail']['gift_item']=$vvv;
                        $res['response']['lists'][$key]['gift_stock']=$vvv['stock'];
                    }
                }
            }
        }
        //将促销类型和促销状态组装入数组
        foreach($res['response']['lists'] as $key=>$list){
              if($list['spc_info']['spc_detail']){
                  switch($list['spc_info']['type']){
                      case SPC_TYPE_GIFT:
                          $data = array(
                              'start_time'=>$list['spc_info']['start_time'],
                              'end_time'=>$list['spc_info']['end_time'],
                              'status'=>$list['spc_info']['status'],
                              'rule'=>$list['spc_info']['spc_detail']['rule'],
                          );
                          break;
                      case SPC_TYPE_SPECIAL:
                          $data = array(
                              'special_price'=>$list['spc_info']['spc_detail']['special_price']
                          );
                          break;
                      case SPC_TYPE_LADDER:
                          $data=array(
                              'rule'=>$list['spc_info']['spc_detail']['rule'],
                          );
                          break;
                  }
                  $rule=spcRuleParse($list['spc_info']['type'],$data);
                  $res['response']['lists'][$key]['spc_info']['spc_message']=$rule;
              }
              $type=M('Base.SpcModule.Center.Status.getType')->getType($list['spc_info']['type']);
              $status=M('Base.SpcModule.Center.Status.getType')->getStatus($list['spc_info']['status'],$list['spc_info']['start_time'],$list['spc_info']['end_time']);
              $res['response']['lists'][$key]['spc_info']['type_message']=$type;
              $res['response']['lists'][$key]['max_buy_message']=M('Base.SpcModule.Center.Status.getMaxBuy')->getMaxBuy($list['max_buy']);
              $res['response']['lists'][$key]['spc_info']['status']=$status;
              $res['response']['lists'][$key]['spc_info']['status_message']=$list['spc_info']['status'];
        }
        $data=array(
            'is_page'=>'NO',
            'sc_code'=>$params['sc_code'],
            'stock'=>'stock',
        );
        $arr=$this->stock($data);
        $count=count($arr);
        $res['response']['count']=$count;
        $this->endInvoke($res['response']);
    }

    /**
     * Bll.Pop.Spc.CenterInfo.stock_export
     * @param type $params
     * @return type
     */
    public function stock_export($params){

        $apiPath='Base.SpcModule.Center.Spc.stock_export';

        $res=$this->invoke($apiPath,$params);

        $this->endInvoke($res['response'],$res['status'],'',$res['message']);
    }

    /**
     * Bll.Pop.Spc.CenterInfo.stock_list
     * @param type $params
     * @return type
     */
   public function stock_list($params){
        $gift=$this->gift_list($params);
        $params['stock_status']='stock';
        $res=$this->common_list($params);
        $arr=array_merge($gift,$res['response']);
        $stock=array();
       foreach($arr as $v){
           if($v['spc_info']['status_message']==SPC_STATUS_PUBLISH &&$v['spc_info']['end_time']>NOW_TIME ){
               $stock[]=$v;
           }
       }
       //执行去重
       if(!empty($stock)){
           $yin=changeArrayIndex($stock,'spc_code');
           foreach($yin as $v){
               $final_stock[]=$v;
           }
       }else{
           $final_stock=$stock;
       }
       return  $this->endInvoke($final_stock);
    }

    public function stock($params){
        $gift=$this->gift_list($params);
        $params['stock_status']='stock';
        $res=$this->common_list($params);
        $arr=array_merge($gift,$res['response']);
        $stock=array();
        foreach($arr as $v){
            if($v['spc_info']['status_message']==SPC_STATUS_PUBLISH &&$v['spc_info']['end_time']>NOW_TIME){
                $stock[]=$v;
            }
        }
        //执行去重
        if(!empty($stock)){
            $yin=changeArrayIndex($stock,'spc_code');
            foreach($yin as $v){
                $final_stock[]=$v;
            }
        }else{
            $final_stock=$stock;
        }
        return $final_stock;
    }
    //得到赠品为0的列表
      private function gift_list($params){
          unset($params['stock']);
          $params['stock_status']='gift_stock';
          $res=$this->common_list($params);
          $arr=array();
          foreach($res['response'] as $key=>$val){
              if(!isset($val['spc_info']['spc_detail']['gift_item'])){
                  continue;
              }
              $arr[]=$val;
          }
          return $arr;
      }

    //库存为零的公共列表方法
      private function common_list($params){
        $apiPath='Base.SpcModule.Center.Spc.lists';
        $res=$this->invoke($apiPath,$params);
        if($res['status']!=0){
            $this->endInvoke(null,$res['status'],'',$res['message']);
        }
        $spc_codes=array();
        foreach($res['response'] as $val){
            if($val['type']=='REWARD_GIFT'){
                $spc_codes[]=$val['spc_code'];
            }
        }
        if(empty($spc_codes)){
            $res2['response']=array();
        }else{
            $params['spc_codes']=$spc_codes;
            //得到促销列表和满赠列表的查询
            $apiPath='Base.SpcModule.Gift.GiftInfo.lists';
            $res2=$this->invoke($apiPath,$params);
            if($res2['status']!=0){
                $this->endInvoke(null,$res2['status'],'',$res2['message']);
            }
        }
        //得到促销列表和特价列表的查询
        $codes=array();
        foreach($res['response'] as $val){
            if($val['type']=='SPECIAL'){
                $codes[]=$val['spc_code'];
            }
            if($val['type']==SPC_TYPE_LADDER){
                $ladder_codes[]=$val['spc_code'];
            }
        }
        if(empty($codes)){
            $special['response']=array();
        }else{
            $arr=array(
                'spc_codes'=>$codes,
            );
            $apiPath='Base.SpcModule.Special.SpecialInfo.lists';
            $special=$this->invoke($apiPath,$arr);
        }
          //          得到促销列表和阶梯价的查询
          $ladder_codes['spc_codes']=$ladder_codes;
          if(empty( $ladder_codes['spc_codes'])){
              $ladder['response']=array();
          }else{
              $apiPath='Base.SpcModule.Ladder.LadderInfo.lists';
              $ladder=$this->invoke($apiPath,$ladder_codes);
              if($ladder['status']!=0){
                  $this->endInvoke(null,$ladder['status'],'',$ladder['message']);
              }
          }
        //得到赠品数量为0的列表
        $sic_codes=array();
        foreach($res2['response'] as $val){
            $sic_codes[]=$val['gift_sic_code'];
        }
        $params['sic_codes']=$sic_codes;
          if($params['stock_status']=='gift_stock'){
              $params['stock']='stock';
          }
          if($params['stock_status']=='stock'){
              unset($params['stock']);
          }
        $params['status']=IC_STORE_ITEM_ON;
        $apiPath='Base.StoreModule.Item.Item.storeItems';
        $res3=$this->invoke($apiPath,$params);
        if($res3['status']!=0){
            $this->endInvoke(null,$res3['status'],'',$res3['message']);
        }
        //将几次的查询的信息合并得到促销列表
        $lists=array();
        foreach($res['response'] as $key=>$val){
            $res['response'][$key]['spc_info']=array(
                'start_time'=>$val['start_time'],
                'end_time' =>$val['end_time'],
                'spc_code'=>$val['spc_code'],
                'sic_code'=>$val['sic_code'],
                'status'  =>$val['status'],
                'type'    =>$val['type'],
                'sc_code'=>$val['sc_code'],
                'spc_title'=>$val['spc_title'],
            );
            unset($res['response'][$key]['start_time']);
            unset($res['response'][$key]['end_time']);
            unset($res['response'][$key]['status']);
            unset($res['response'][$key]['type']);
            unset($res['response'][$key]['spc_title']);
            foreach($special['response'] as $spc){
                if($val['spc_code']==$spc['spc_code']){
                    $res['response'][$key]['spc_info']['spc_detail']=$spc;
                }
            }
            foreach($ladder['response'] as $lad){
                if($val['spc_code']==$lad['spc_code']){
                    $res['response'][$key]['spc_info']['spc_detail']=$lad;
                }
            }
            foreach($res2['response'] as $k=>$v){
                if($val['spc_code']==$v['spc_code']){
                    $res['response'][$key]['spc_info']['spc_detail']=$v;
                }
                foreach($res3['response'] as $kkk=>$vvv){
                    if($vvv['sic_code']==$res['response'][$key]['spc_info']['spc_detail']['gift_sic_code']){
                        $res['response'][$key]['spc_info']['spc_detail']['gift_item']=$vvv;
                        $res['response'][$key]['gift_stock']=$vvv['stock'];
                    }
                }
            }
        }
        //将促销类型和促销状态组装入数组
        foreach($res['response'] as $key=>$list){
            if($list['spc_info']['spc_detail']){
                switch($list['spc_info']['type']){
                    case SPC_TYPE_GIFT:
                        $data = array(
                            'start_time'=>$list['spc_info']['start_time'],
                            'end_time'=>$list['spc_info']['end_time'],
                            'status'=>$list['spc_info']['status'],
                            'rule'=>$list['spc_info']['spc_detail']['rule'],
                        );
                        break;
                    case SPC_TYPE_SPECIAL:
                        $data = array(
                            'special_price'=>$list['spc_info']['spc_detail']['special_price'],
                        );
                        break;
                    case SPC_TYPE_LADDER:
                        $data=array(
                            'rule'=>$list['spc_info']['spc_detail']['rule'],
                        );
                        break;
                }
                $rule=spcRuleParse($list['spc_info']['type'],$data);
                $res['response'][$key]['spc_info']['spc_message']=$rule;
            }
            $type=M('Base.SpcModule.Center.Status.getType')->getType($list['spc_info']['type']);
            $status=M('Base.SpcModule.Center.Status.getType')->getStatus($list['spc_info']['status'],$list['spc_info']['start_time'],$list['spc_info']['end_time']);
            $res['response'][$key]['spc_info']['type_message']=$type;
            $res['response'][$key]['max_buy_message']=M('Base.SpcModule.Center.Status.getMaxBuy')->getMaxBuy($list['max_buy']);
            $res['response'][$key]['spc_info']['status']=$status;
            $res['response'][$key]['spc_info']['status_message']=$list['spc_info']['status'];
        }
          return $res;
    }
    /**
     * Bll.Pop.Spc.CenterInfo.get
     * @param type $params
     * @return type
     * @Author: wangliangliang <wangliangliang@liangrenwang.com >
     */
    public function get($params){
    
    	//得到促销信息
    	$apiPath = 'Base.SpcModule.Center.SpcInfo.get';
    	$res = $this->invoke($apiPath,$params);
    	 if ($res['status']!=0){
    		 $this->endInvoke(null,$res['status'],'',$res['message']);
    	} 
    	
    	$sales_sic_code = $res['response']['sic_code'];
        $gift_sic_code = $res['response']['spc_info']['gift_sic_code'];
        $spc_info = $res['response'];
        $sic_codes = array_unique(array($sales_sic_code,$gift_sic_code));
    	//得到商品信息
    	$apiPath = 'Base.StoreModule.Item.Item.storeItems';
    	$res2 = $this->invoke($apiPath,array('sc_code'=>$params['sc_code'],'sic_codes'=>$sic_codes,'is_page'=>'NO'));
        $item_info = $res2['response'];
        foreach($item_info as $k=>$v){
            $item_info[$v['sic_code']] = $v;
        }
        $spc_info['item_info'] = $item_info[$sales_sic_code];
        $spc_info['spc_info']['item_info'] = $item_info[$gift_sic_code];

    	$this->endInvoke($spc_info);
    	
    }
    
    
    
    /**
     * Bll.Pop.Spc.CenterInfo.getInfo
     * @param type $params
     * @return type
     * @Author: wangliangliang <wangliangliang@liangrenwang.com >
     */
        //复制到新促销
    
    public function getInfo($params){
    
    	//得到促销信息
    	$apiPath = 'Base.SpcModule.Center.SpcInfo.get';
    	$res = $this->invoke($apiPath,$params);
    	if ($res['status']!=0){
    		$this->endInvoke(null,$res['status'],'',$res['message']);
    	}
    	
    	$copy_info = $res['response'];
    	$copy_info['spc_info']= $copy[$gift_sic_code];

    	$this->endInvoke($copy_info);
    
    }







    /**
     * Bll.Pop.Spc.CenterInfo.import
     * @param type $params
     */
    public function import($params) {
  
        $api  = 'Com.DataCenter.File.Excel.spcImport';

        $res  = $this->invoke($api,$params);
        foreach ($res['success'] as $k => $v) {
          if ($v['type'] == 'LADDER') {
            $res['success'][$k]['data']['rule'] = json_encode($v['data']['rule']);
          }
        }
        $params['data'] = json_encode($res['success']);
        
        $saveData = M('Com.Tool.Redis.Spc.import')->import($params);

        return $this->res($res);
    }
    public function addData($params) {
        $key = \Library\Key\RedisKeyMap::getSpcKey($params['sc_code'],$params['uc_code']);
        $redisData = R()->get($key);
        $data = json_decode($redisData,true);
        $newData = array('fail'=>array(),'number'=>array(),'success_data'=>array());
        if (empty($data) || $redisData === false) {
            $this->res($newData);
        }
        $data = M('Com.DataCenter.File.Excel.checkData')->checkData($data);
        foreach ($data['success'] as $k => $v) {
          $data['success'][$k]['goods_price'] = $v['goods_info']['price'];
        }
        if ( !empty($data['success']) ) {
            foreach ($data['success'] as $k => $v) {
                try{
                    D()->startTrans();
                    $apiPath = "Base.SpcModule.Center.Spc.add";

                    $res = $this->invoke($apiPath, $v);
                   
                    if ($res['status'] != 0) {
                        $data['fail'][] = $v;
                    }
                    $commit_res = D()->commit();
                    if ($commit_res === FALSE) {
                      $data['fail'][] = $v;
                    }  
                    if ($res['status'] == 0) {
                      $data['success_data'][] = $v;
                    }
            }catch (\Exception $ex) {
                    D()->rollback();
                   
                    $data['fail'][] = $v;
                   
                }
            }
            M('Com.Tool.Redis.Spc.clearData')->clearData($params);
        } 

        $this->res($data);
    }

    /**
     * zhangyupeng
     * Bll.Pop.Spc.CenterInfo.search
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function search($params){
        $sc_code = $params['sc_code'];
        if (empty($sc_code)) {
          return $this->endInvoke('', 5);
        }

        $params['type']  = 'spc';
        $params['limit'] = 5;
        $apiPath         = 'Base.StoreModule.Item.Item.searchConItems';
        $res             = $this->invoke($apiPath, $params);
        $res['response'] = $this->getSpc($res['response'], $sc_code);
        
        $this->endInvoke($res['response']);
    }

}
