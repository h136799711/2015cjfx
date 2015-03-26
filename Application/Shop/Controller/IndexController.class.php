<?php
namespace Shop\Controller;

class IndexController extends ShopController {


	protected function _initialize() {
		parent::_initialize();
	}
	/**
	 * 我的订单中心页面
	 */
	public function orders(){
		$this->display();
	}
	
	
	/**
	 * 我的家族中心
	 */
	public function myFamily() {
		
		$this -> display();
	}
	
	
	/**
	 * 购买页面
	 * @param $pid 需要传入pid，要购买的产品id
	 */
	public function buy() {
		$userinfo = session("userinfo");
		if(!is_array($userinfo)){
//			$this->error("请登录！");
		}
		$productid = I('get.pid', 0);
		if ($productid == 0) {
			$this -> error("参数错误！");
		}

		$result = apiCall("Tool/Province/queryNoPaging", array());
		if ($result['status']) {
			$this -> assign("provinces", $result['info']);
		}

		$product = apiCall("Admin/Product/getInfoWithThumbnail", array('id' => $productid));
		$map = array("wxuserid"=>$userinfo['id']);
		
		$address = apiCall("Shop/Address/getInfo",array($map));
		
		if($address['status']){
			$this->assign("address",$address['info']);
			$city = apiCall("Tool/City/getListByProvinceID", array($address['info']['province']));
			$area = apiCall("Tool/Area/getListByCityID", array($address['info']['city']));
			if($city['status']){
				$city = $city['info'];
				$this->assign("city",$city);
			}
			if($area['status']){
				$area = $area['info'];
				$this->assign("area",$area);
			}
		}
		
		if ($product['status']) {
			$product['info']['tburl'] = getPictureURL($product['info']['thumbnaillocal'], $product['info']['thumbnailremote']);
			$this -> assign("product", $product['info']);
			$this -> display();
		}
	}

	
	public function index() {
		if (IS_GET) {
			
			
			if (is_array($this -> userinfo)) {
				$groupaccess = apiCall("Admin/GroupAccess/getInfo", array('groupid' => $this -> userinfo['groupid']));
				if ($groupaccess['status']) {
					$this -> assign("groupaccess", $groupaccess['info']);
					$this -> assign("alloweddistribution", $groupaccess['info']['alloweddistribution']);
					$this -> assign("userinfo", $this->userinfo);
					$this -> display();
				} else {
					$this -> error("权限获取失败，请重试！");
				}
			}else{
				$this -> error("用户信息获取失败！");
			}
		} else {
			$this -> error("无法访问！");
		}
	}


	
	

}
