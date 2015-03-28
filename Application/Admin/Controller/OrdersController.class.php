<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Admin\Controller;

class OrdersController extends AdminController{
	/**
	 * 初始化
	 */
	protected function _initialize(){
		parent::_initialize();
	}
	
	/**
	 * 订单管理
	 */
	public function index(){
		$arr = getDataRange(3);
		$payStatus = I('paystatus','');
		$orderStatus = I('orderstatus','');
		$orderid = I('post.orderid','');
		$userid = I('uid',0);
		$startdatetime = urldecode($arr[0]); //I('startdatetime', , 'urldecode');
		$enddatetime = urldecode($arr[1]); //I('enddatetime',   , 'urldecode');
		
		//分页时带参数get参数
		$params = array('startdatetime' => $startdatetime, 'enddatetime' => ($enddatetime));

		$startdatetime = strtotime($startdatetime);
		$enddatetime = strtotime($enddatetime);
		
		if ($startdatetime === FALSE || $enddatetime === FALSE) {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error(L('ERR_DATE_INVALID'));
		}

		$map = array();
		if(!empty($orderid)){
			$map['orderid'] = array('like' , $orderid.'%');
			
		}
		if($payStatus != ''){
			$map['pay_status'] = $payStatus;
			$params['paystatus'] = $payStatus;
		}
		if($orderStatus != ''){
			$map['order_status'] = $orderStatus;
			$params['orderstatus'] = $orderStatus;
		}
		$map['createtime'] = array( array('EGT', $startdatetime), array('elt', $enddatetime), 'and');

		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " createtime desc ";

		if($userid > 0){
			$map['wxuser_id'] = $userid;
		}
//		$result = apiCall("Admin/Wxuser/queryNoPaging", array(array(),false,"id,nickname,avatar") );
//		if($result['status']){
//			$this->assign("users",$result['info']);
//		}
		
		//
		$result = apiCall('Admin/Orders/query', array($map, $page, $order, $params));
			
		//
		if ($result['status']) {
			$this -> assign('orderid', $orderid);
			$this -> assign('orderStatus', $orderStatus);
			$this -> assign('payStatus', $payStatus);
			$this -> assign('startdatetime', $startdatetime);
			$this -> assign('enddatetime', $enddatetime);
			$this -> assign('show', $result['info']['show']);
			$this -> assign('list', $result['info']['list']);
			$this -> display();
		} else {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}

	/**
	 * 发货
	 */
	public function deliverGoods(){
//		$arr = getDataRange(3);
//		$payStatus = I('post.paystatus','');
		$orderStatus = I('post.orderstatus','');
		$orderid = I('post.orderid','');
		$userid = I('post.uid',0);
//		$startdatetime = urldecode($arr[0]); //I('startdatetime', , 'urldecode');
//		$enddatetime = urldecode($arr[1]); //I('enddatetime',   , 'urldecode');
		
		//分页时带参数get参数
//		$params = array('startdatetime' => $startdatetime, 'enddatetime' => ($enddatetime));
		$params = array();
//		$startdatetime = strtotime($startdatetime);
//		$enddatetime = strtotime($enddatetime);
		
//		if ($startdatetime === FALSE || $enddatetime === FALSE) {
//			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
//			$this -> error(L('ERR_DATE_INVALID'));
//		}

		$map = array();
		if(!empty($orderid)){
			$map['orderid'] = array('like' , $orderid.'%');
		}
//		if($payStatus != ''){
//			$map['pay_status'] = $payStatus;
//		}
		if($orderStatus != ''){
			$map['order_status'] = $orderStatus;
		}
//		$map['createtime'] = array( array('EGT', $startdatetime), array('elt', $enddatetime), 'and');
		$map['pay_status'] = \Common\Model\OrdersModel::ORDER_PAID;
		$map['order_status'] = array('ELT',\Common\Model\OrdersModel::ORDER_TOBE_SHIPPED);
		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " createtime desc ";

		if($userid > 0){
			$map['wxuser_id'] = $userid;
		}
		
		
		//
		$result = apiCall('Admin/Orders/query', array($map, $page, $order, $params));
			
		//
		if ($result['status']) {
			$this -> assign('orderid', $orderid);
			$this -> assign('orderStatus', $orderStatus);
			$this -> assign('show', $result['info']['show']);
			$this -> assign('list', $result['info']['list']);
			$this -> display();
		} else {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}
	
	/**
	 * 查看
	 */
	public function view(){
		if(IS_GET){
			$id = I('get.id',0);
			$map = array('id'=>$id);
			$result = apiCall("Admin/Orders/getInfo", array($map));
			if($result['status']){
				$this->assign("items",unserialize($result['info']['items']));
				$this->assign("order",$result['info']);
				$this->display();
			}else{
				$this->error($result['info']);
			}
		}
	}
	
}
