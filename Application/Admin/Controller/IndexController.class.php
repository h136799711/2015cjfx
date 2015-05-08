<?php
/**
 * (c) Copyright 2014 hebidu. All Rights Reserved. 
 */

namespace Admin\Controller;

class IndexController extends AdminController {

	//首页
    public function index(){
    		$paidCnt = $this->countPaidOrders();
		$checkCnt = $this->countWithDrawcash();
		$ordersCnt = $this->countOrders();
		$pageView = $this->getPageView();
		
		$this->assign("pageView",$pageView);
		$this->assign("paidCnt",$paidCnt);
		$this->assign("ordersCnt",$ordersCnt);
		$this->assign("checkCnt",$checkCnt);
        $this->display();
    }
	
	private function getPageView(){
			return C('SHOP_PAGEVIEW');
	}
	
	private function countPaidOrders(){
		$map = array('wxaccountid'=>getWxAccountID(),'pay_status'=>\Common\Model\OrdersModel::ORDER_PAID,'order_status'=>\Common\Model\OrdersModel::ORDER_TOBE_CONFIRMED);
		$result = apiCall("Admin/Orders/count", array($map));
		if($result['status']){
			return $result['info'];
		}else{
			LogRecord($resultp['info'], __FILE__.__LINE__,'ERR');
			return 0;
		}
	}
	
	private function countOrders(){
		$map = array('test'=>'test');
		$map = array('wxaccountid'=>getWxAccountID(),'pay_status'=>\Common\Model\OrdersModel::ORDER_PAID,'order_status'=>\Common\Model\OrdersModel::ORDER_TOBE_CONFIRMED);
		$result = apiCall("Admin/Orders/count", array($map));
		if($result['status']){
			return $result['info'];
		}else{
			LogRecord($resultp['info'], __FILE__.__LINE__,'ERR');
			return 0;
		}
	}
	
	private function countWithDrawcash(){
		$map = array('wxaccountid'=>getWxAccountID(),'wdc_status'=>\Common\Model\CommissionWithdrawcashModel::WDC_STATUS_PENDING_AUDIT);
		$result = apiCall("Admin/CommissionWithdrawcash/count", array($map));
		if($result['status']){
			return $result['info'];
		}else{
			LogRecord($resultp['info'], __FILE__.__LINE__,'ERR');
			return 0;
		}
	}
}