<?php
namespace Shop\Controller;

class IndexController extends ShopController {

	private $wxaccount = "";
	private $wxapi = "";
	private $openid = "";

	protected function _initialize() {
		parent::_initialize();
		$this -> refreshWxaccount();
	}
	public function buy(){
		$productid = I('get.pid',0);
		if($productid == 0){
			$this->error("参数错误！");
		}
		
		$result = apiCall("Tool/Province/queryNoPaging",array());
		if($result['status']){
			$this->assign("provinces",$result['info']);	
		}
		
		
		$product = apiCall("Admin/Product/getInfoWithThumbnail", array('id'=>$productid));
		if($product['status']){
			$product['info']['tburl'] = getPictureURL($product['info']['thumbnaillocal'],$product['info']['thumbnailremote']);
			$this->assign("product",$product['info']);
			$this->display();
		}
	}
	public function index() {
		if (IS_GET) {
			C('SHOW_PAGE_TRACE',false);
			session("userinfo", null);
			
			$userinfo = null;
			if (session("?userinfo")) {
				$userinfo = session("userinfo");
			}
//			$userinfo = array('nickname'=>'我的家族','headimgurl'=>'http://wx.qlogo.cn/mmopen/etibbrEkCpyiccXDxXAwUiaTyS1paNPyAmln7bW7LQT9MAW6QCHqPsomImib8rjBT5z1elcbFL7kS7KA2icKQBvWnBVe3pp11W63c/0',);
			
			if (!is_array($userinfo)) {

				$code = I('get.code', '');
				$state = I('get.state', '');
				if (empty($code) && empty($state)) {

					$redirect = $this -> wxapi -> getOAuth2BaseURL(C('SITE_URL') . U('Shop/Index/index').'?token='.I('get.token',''), 'HomeIndexOpenid');

					redirect($redirect);
				}
				
				if ($state == 'HomeIndexOpenid') {
					$accessToken = $this -> wxapi -> getOAuth2AccessToken($code);

					$this -> assign("accessToken", $accessToken);
					$this->openid = $accessToken['openid'];
					$userinfo = $this -> wxapi -> getBaseUserInfo($accessToken['openid']);
					
					if ($userinfo['status']) {
						$userinfo = $userinfo['info'];
						$this->refreshWxuser($userinfo);
						session("userinfo", $userinfo);
					} else {
						$userinfo = null;
					}
				}
			}
			$groupaccess = apiCall("Admin/GroupAccess/getInfo",array('groupid'=>$userinfo['groupid']));
			if($groupaccess['status']){
				$this -> assign("groupaccess", $groupaccess['info']);
				$this -> assign("alloweddistribution", $groupaccess['info']['alloweddistribution']);
				$this -> assign("userinfo", $userinfo);
				$this -> display();
			}else{
				$this -> error("权限获取失败，请重试！");
			}
		} else {
			$this -> error("无法访问！");
		}
	}
	
	
	
	/**
	 * 我的家族中心
	 */
	public function myFamily(){
		$this->display();
	}
	
	
	/**
	 * 保存订单
	 */
	public function saveOrder(){
		//TODO:保存订单
		//items=1&totalprice=99&name=1212
		//&mobile=2&wxno=2&province=360000&city=361100
		//&area=361127&address=222&notes=
		//$item = 
		dump(I('post.'));
	}
	
	
	
	
	
	/**
	 * 刷新粉丝信息
	 */
	private function refreshWxuser($userinfo) {
		$wxuser = array();
		$wxuser['wxaccount_id'] = intval($this -> wxaccount['id']);
		$wxuser['nickname'] = $userinfo['nickname'];
		$wxuser['province'] = $userinfo['province'];
		$wxuser['country'] = $userinfo['country'];
		$wxuser['city'] = $userinfo['city'];
		$wxuser['sex'] = $userinfo['sex'];
		$wxuser['avatar'] = $userinfo['headimgurl'];
		$wxuser['subscribe_time'] = $userinfo['subscribe_time'];
		
		if(!empty($this->openid) && is_array($this->wxaccount)){
		
			$map = array('openid' => $this -> openid, 'wxaccount_id' => $this->wxaccount['id'] );
			
			$result = apiCall('Weixin/Wxuser/save', array($map, $wxuser));
			
			if(!$result['status']){
				LogRecord($result['info'], "[Home/Index/refreshWxuser]".__LINE__);
			}
		
		}
				
	}

	/**
	 * 刷新
	 */
	private function refreshWxaccount() {
		$token = I('get.token', '');
		if (!empty($token)) {
			session("token", $token);
		} elseif (session("?token")) {
			$token = session("token");
		}

		$result = apiCall('Weixin/Wxaccount/getInfo', array( array('token' => $token)));
		if ($result['status']) {
			$this -> wxaccount = $result['info'];
			$this -> wxapi = new \Common\Api\WeixinApi($this -> wxaccount['appid'], $this -> wxaccount['appsecret']);
		} else {
			$this -> error("信息获取失败，请重试！");
		}
	}

}
