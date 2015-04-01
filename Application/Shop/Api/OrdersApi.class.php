<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------


  namespace Shop\Api;
  use Common\Api\Api;
  use Common\Model\OrdersModel;
  
  class OrdersApi extends Api{
  	protected function _init(){
  		$this->model = new OrdersModel();
  	}
	
	/**
	 * 事务增加订单信息
	 */
	public function addOrder($entity){
		$this->model->startTrans();
		$flag = true;
		$error = "";
		//1. 增加order表记录
		$order = array(
				'wxaccountid'=>$entity['wxaccountid'],
				'wxuser_id' => $entity['wxuser_id'], 
				'price' => $entity['price'], 
				'note' => $entity['note'], 
				'orderid' => $entity['orderid'], 
				'items' => $entity['items']
			 );
		$result = $this->add($order);
		if($result['status']){
		$orderid = $result['info'];
		//2. 增加order_contactinfo记录
		$orderContactInfo = array(
				'wxuser_id' => $entity['wxuser_id'], 
				'orderid' => $entity['orderid'], 
				'mobile' => $entity['mobile'], 
				'wxno' => $entity['wxno'], 
				'contactname' => $entity['contactname'], 
				'country' => $entity['country'], 
				'province' => $entity['province'], 
				'city' => $entity['city'], 
				'area' => $entity['area'], 
				'wxno' => $entity['wxno'], 
				'detailinfo' => $entity['detailinfo'], 
			);
			 $model = new \Common\Model\OrdersContactinfoModel();
			 $result = $model->create($orderContactInfo,1);
			 
			 if($result){
			 	$result = $model->add();
			 	if($result === FALSE){
			 		//新增失败
			 		$flag = false;
					$error = $model->getDbError();
			 	}
				
			 }else{//自动验证失败
			 	$flag = false;
				$error = $model->getError();
			 }
			 
		}else{
			$flag = false;
			$error = $result['info'];
		}
			 
			 
		if($flag){
			$this->model->commit();
			return $this->apiReturnSuc($orderid);
		}else{
			$this->model->rollback();
			return $this->apiReturnErr($error);
		}
		
	}
	
	/**
	 * 设置支付状态
	 * TODO：需要锁定数据行写操作
	 */
	public function savePayStatus($orderid,$paystatus){
		$result = $this->model->where(array('orderid'=>$orderid))->lock(true)->save(array('pay_status'=>$paystatus));
		if($result === FALSE){
			$error = $this->model->getDbError();
			return $this->apiReturnErr($error);
		}else{
			return $this->apiReturnSuc($result);
		}
	}

	/**
	 * 设置订单状态
	 * TODO：需要锁定数据行写操作
	 */
	public function saveOrderStatus($orderid,$orderstatus){
		$result = $this->model->where(array('orderid'=>$orderid))->save(array('order_status'=>$orderstatus));
		if($result === FALSE){
			$error = $this->model->getDbError();
			return $this->apiReturnErr($error);
		}else{
			return $this->apiReturnSuc($result);
		}
	}
	
	
	/**
	 * 统计下单未购买的订单数
	 */
	public function countOrderBy($wxuserid,$paystatus){
		
//		$level1SQL = "(select wf.parent_1 from __WXUSER_FAMILY__ wf left join __WXUSER__ wu on wu.wxaccount_id = wf.wxaccount_id and wu.openid = wf.openid   where wf.parent_1 = $wxuserid)";
//		$level2SQL = "(select wf.parent_2 from __WXUSER_FAMILY__ wf left join __WXUSER__ wu on wu.wxaccount_id = wf.wxaccount_id and wu.openid = wf.openid    where wf.parent_2 = $wxuserid)";
//		$level3SQL = "(select wf.parent_3 from __WXUSER_FAMILY__ wf left join __WXUSER__ wu on wu.wxaccount_id = wf.wxaccount_id and wu.openid = wf.openid   where wf.parent_3 = $wxuserid)";
//		$level4SQL = "(select wf.parent_4 from __WXUSER_FAMILY__ wf left join __WXUSER__ wu on wu.wxaccount_id = wf.wxaccount_id and wu.openid = wf.openid   where wf.parent_4 = $wxuserid)";
		$levelSQL = "(select wu.id from __WXUSER_FAMILY__ wf left join __WXUSER__ wu on wu.wxaccount_id = wf.wxaccount_id and wu.openid = wf.openid   where wu.id = $wxuserid or wf.parent_4 = $wxuserid or wf.parent_3 = $wxuserid or wf.parent_2 = $wxuserid  or wf.parent_1 = $wxuserid )";
		
		$countsql = "SELECT count(ord.id) as cnt FROM __ORDERS__ as ord where ord.pay_status = '".$paystatus."' and ord.wxuser_id in $levelSQL";// $level1SQL union $level2SQL union $level3SQL union $level4SQL  )";
		$model = M();
		$result = $model->query($countsql);
		
		if($result === false){			
			$error = $model->getDbError();
			return $this->apiReturnErr($error);
		}else{
			return $this->apiReturnSuc($result[0]['cnt']);
		}
		
	}
	
	
  }
