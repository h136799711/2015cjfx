<?php
namespace Shop\Controller;

class IndexController extends ShopController {

//	private $wxaccount = "";
//	private $wxapi = "";
//	private $openid = "";

	protected function _initialize() {
		parent::_initialize();
	}

	public function buy() {
		
		$this->getWxuser(C('SITE_URL') . U('Shop/Index/buy') . '?token=' . I('get.token', ''));	
		
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
			$this->getWxuser(C('SITE_URL') . U('Shop/Index/index') . '?token=' . I('get.token', ''));
//			dump($this->userinfo);
//			exit();
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
