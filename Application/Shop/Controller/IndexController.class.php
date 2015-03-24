<?php
namespace Shop\Controller;

class IndexController extends ShopController {


	protected function _initialize() {
		parent::_initialize();
	}

	public function buy() {
				
		$productid = I('get.pid', 0);
		if ($productid == 0) {
			$this -> error("参数错误！");
		}

		$result = apiCall("Tool/Province/queryNoPaging", array());
		if ($result['status']) {
			$this -> assign("provinces", $result['info']);
		}

		$product = apiCall("Admin/Product/getInfoWithThumbnail", array('id' => $productid));
		if ($product['status']) {
			$product['info']['tburl'] = getPictureURL($product['info']['thumbnaillocal'], $product['info']['thumbnailremote']);
			$this -> assign("product", $product['info']);
			$this -> display();
		}
	}

	public function index() {
		if (IS_GET) {
			
			C('SHOW_PAGE_TRACE', false);
			
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

	/**
	 * 我的家族中心
	 */
	public function myFamily() {
		$this -> display();
	}

	
	

}
