<?php
namespace Home\Controller;
use Common\Model;
use Common\Model\WxuserWithFamilyViewModel;

class IndexController extends HomeController {

	private $wxaccount = "";
	private $wxapi = "";
	private $openid = "";

	protected function _initialize() {
		parent::_initialize();
		$this -> refreshWxaccount();
	}
	
	public function testWxuserFamily(){
//		$model = new WxuserWithFamilyViewModel();
//		$result = $model->where(array('id'=>1))->find();
		$model = new \Common\Model\WxuserWithFamilyViewModel();
		$result = $model->field("id")->where (array('parent_1'=>1))->select(FALSE);
		
		$sql = " select sum(ord.price) as totalFee from __ORDERS__ as ord where ord.wxuser_id in  ".$result;
		$result = $model->query($sql);
		
		dump($result);
	}
	
	public function groupup(){
		$wxuserid = 1;
		$result = apiCall("Admin/Wxuser/groupUp",array($wxuserid));
		dump($result);
		$result = apiCall("Admin/Wxuser/getInfo", array(array('id'=>$wxuserid)));
		dump($result);
	}
	
	public function groupdown(){
		$wxuserid = 1;
		$result = apiCall("Admin/Wxuser/groupDown",array($wxuserid));
		dump($result);
		$result = apiCall("Admin/Wxuser/getInfo", array(array('id'=>$wxuserid)));
		dump($result);
		
		
	}
	
	public function index() {
//		dump('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);	
//		$this->display();
		$this->redirect('Admin/Public/login');
//		if (IS_GET) {
//			session("userinfo", null);
//			$userinfo = null;
//			if (session("?userinfo")) {
//				$userinfo = session("userinfo");
//			}
//			if (!is_array($userinfo)) {
//
//				$code = I('get.code', '');
//				$state = I('get.state', '');
//				if (empty($code) && empty($state)) {
//
//					$redirect = $this -> wxapi -> getOAuth2BaseURL(C('SITE_URL') . U('Home/Index/index', array('token' => I('get.token', ''))), 'HomeIndexOpenid');
//
//					redirect($redirect);
//				}
//
//				if ($state == 'HomeIndexOpenid') {
//					$accessToken = $this -> wxapi -> getOAuth2AccessToken($code);
//
//					$this -> assign("accessToken", $accessToken);
//					$this->openid = $accessToken['openid'];
//					$userinfo = $this -> wxapi -> getBaseUserInfo($accessToken['openid']);
//					
//					if ($userinfo['status']) {
//						$userinfo = $userinfo['info'];
//						$this->refreshWxuser($userinfo);
//						session("userinfo", $userinfo);
//					} else {
//						$userinfo = null;
//					}
//				}
//			}
//
//			$this -> assign("userinfo", $userinfo);
//			$this -> display();
//		} else {
//			$this -> error("无法访问！");
//		}
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
