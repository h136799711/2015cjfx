<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

	private $wxaccount = "";
	private $wxapi = "";
	
	protected function _initialize(){
		$this->refreshWxaccount();
	}

	public function index() {
		if (IS_GET) {
			$userinfo = $this->wxapi ->getBaseUserInfo();
			$this -> assign("userinfo", $userinfo);
			$this -> display();
		} else {
			$this -> error("无法访问！");
		}
	}

	/**
	 * 刷新
	 */
	private function refreshWxaccount() {
		$token = I('get.token', '');
		if(!empty($token)){
			session("token",$token);
		}elseif(session("?token")){
			$token = session("token");
		}
		
		$result = apiCall('Weixin/Wxaccount/getInfo', array( array('token' => $token)));
		if ($result['status']) {
			$this -> wxaccount = $result['info'];
     		$this->wxapi = new \Common\Api\WeixinApi($this->wxaccount['appid'],$this->wxaccount['appsecret']);		
		}else{
			$this -> error("信息获取失败，请重试！");
		}
	}

}
