<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Controller;
use Think\Controller;
class OrdersController extends ShopController {
	
	
	protected function _initialize() {
		parent::_initialize();
	}
	
	public function pay(){
		$id = I('get.id','');
		$result = apiCall("Shop/Orders/getInfo", array('id'=>$id));
		if($result['status']){
			$order = $result['info'];
			$this->assign("order",$order);
			$this->display();
		}
	}
	
	public function save() {
		$userinfo = session("userinfo");
		if (IS_POST && is_array($userinfo)) {
			
			$entity = array('wxuser_id' => $userinfo['id'],
			 'price' => I('post.totalprice', 0), 
			 'mobile' => I('post.mobile', ''), 
			 'wxno' => I('post.wxno', ''), 
			 'contactname' => I('post.contactname', ''), 
			 'note' => I('post.note', ''), 
			 'country' => I('post.country', ''), 
			 'province' => I('post.p_name', ''), 
			 'city' => I('post.c_name', ''), 
			 'area' => I('post.a_name', ''), 
			 'detailinfo' => I('post.address', ''), 
			 'orderid' => $this -> getOrderID(), 
			 'items' => $this -> getItems());
			$result = apiCall("Shop/Orders/add",array($entity));
			if($result['status']){
//			dump($entity);
				$address  = array(
					'wxuser_id' => $userinfo['id'],
			 		'country' => I('post.country', ''), 
			 		'province' => I('post.province', ''), 
			 		'city' => I('post.city', ''), 
			 		'detailinfo' => I('post.address', ''), 
			 		'area' => I('post.area', ''), 
				);
				$result = apiCall("Shop/Address/addOrUpdate",array($entity));
				if($result['status']){		
					$this->success("操作成功！",U('Shop/Orders/pay',array('id'=>$result['status']['id']))).'?showwxpaytitle=1';
				}else{
					LogRecord($result['info'], __FILE__.__LINE__);
				}
			}
			dump($address);
		}else{
			$this->error("禁止访问！");
		}

	}
	
	private function items() {
		$items = array( array('item' => I('productname', ''), 'price' => I('post.price', 0)), );
		
		return serialize($items);
	}
	
	private function getOrderID() {
//		dump($this->wxaccount);
		return  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt();
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  $this->wxaccount['id'].date('YmdHis',time()).$this->randInt().'<br/>';
//		echo  GUID().'<br/>';
//		echo  md5().'<br/>';
	}
	
	private function randInt(){
		srand(GUID());
		return rand(10000000, 99999999);
	}
	
	
	
}
