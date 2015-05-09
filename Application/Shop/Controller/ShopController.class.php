<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Controller;
use Think\Controller;

class ShopController extends  Controller {
	
	protected $userinfo;
	protected $wxaccount;
	protected $wxapi;
	protected $openid;
	protected $hasSubscribe = 1;
	
	protected function _initialize() {
		
		// 获取配置
		$this -> getConfig();

		if (!defined('APP_VERSION')) {
			//定义版本
			if (defined("APP_DEBUG") && APP_DEBUG) {
				define("APP_VERSION", time());
			} else {
				define("APP_VERSION", C('APP_VERSION'));
			}
		}
		C('SHOW_PAGE_TRACE', false);//设置不显示trace
		$this -> refreshWxaccount();
		$url = $this->getCurrentURL();
		
		
		if(!is_null($this->getWxuser($url))){
			
			if($this->hasSubscribe == 0){
				//未关注公众号的情况下
				$referrer = I('referrer',0);
				if($referrer > 0){
					addWeixinLog($referrer,"未关注！");
					$promotionApi = new \Common\Api\PromotioncodeApi(C('PROMOTIONCODE'));
					$result = $promotionApi->isExists($referrer);
					redirect(U("Shop/Index/referrer",array('qrcode'=>urlencode($result['path']))));
				}
//				$this->assign("qrcode",$result['path']);
//				$this->display("Index:referrer");
//				exit();
			}
			
			//设置分享链接/标题
			$shareURL = C("SITE_URL").U('Shop/Index/index',array('token'=>$this->token,'referrer'=>$this->userinfo['id']));
			
			$shareImg = C("SITE_URL")."/Public/Shop/imgs/share.png";
			$this->assign("shareUrl",$shareURL);
			$this->assign("shareImg",$shareImg);
			
		}
		$this->pageview();
	}
	
	
	
	
	private function pageview(){
		//TODO: 
		$bdtj = new \Common\Api\BaiduTjApi(C('BAIDU_TJ_WAP'));
		$_hmtPixel = $bdtj->trackPageView();
		$this->assign("hmtPixel",$_hmtPixel);
		$result = apiCall("Shop/PageView/inc", array());
		ifFailedLogRecord($result, __LINE__.__LINE__);
	}
	
	protected function getCurrentURL(){
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return $url;
	}

	public function getWxuser($url) {

		$this -> userinfo = null;
		if (session("?userinfo")) {
			$this -> userinfo = session("userinfo");
			$this -> openid = $this->userinfo['openid'];
		}
		
		
		
		
		if (!is_array($this -> userinfo)) {

			$code = I('get.code', '');
			$state = I('get.state', '');
			if (empty($code) && empty($state)) {

				$redirect = $this -> wxapi -> getOAuth2BaseURL($url, 'HomeIndexOpenid');

				redirect($redirect);
			}

			if ($state == 'HomeIndexOpenid') {
				$accessToken = $this -> wxapi -> getOAuth2AccessToken($code);

				$this -> openid = $accessToken['openid'];
				$result = $this -> wxapi -> getBaseUserInfo($accessToken['openid']);

				if ($result['status']) {
					$this -> refreshWxuser($result['info']);
				} else {
					$this -> userinfo = null;
				}
			}
		}
		
		$this->hasSubscribe = $this -> userinfo['subscribed'];
		//每次都重新从数据库中获取   
		$map = array('openid' => $this -> openid, 'wxaccount_id' => $this -> wxaccount['id']);
		$result = apiCall('Shop/Wxuser/getInfo', array($map));
		if($result['status']){
			$this -> userinfo = $result['info'];
			session("userinfo", $result['info']);
		} 
		
		return $this->userinfo;
	}

	/**
	 * 刷新粉丝信息
	 */
	private function refreshWxuser($userinfo) {
		$wxuser = array();
//		$wxuser['wxaccount_id'] = intval($this -> wxaccount['id']);
		$wxuser['nickname'] = $userinfo['nickname'];
		$wxuser['province'] = $userinfo['province'];
		$wxuser['country'] = $userinfo['country'];
		$wxuser['city'] = $userinfo['city'];
		$wxuser['sex'] = $userinfo['sex'];
		$wxuser['avatar'] = $userinfo['headimgurl'];
		$wxuser['subscribe_time'] = $userinfo['subscribe_time'];
		
		if (!empty($this -> openid) && is_array($this -> wxaccount)) {
			
			$map = array('openid' => $this -> openid, 'wxaccount_id' => $this -> wxaccount['id']);

			$result = apiCall('Weixin/Wxuser/save', array($map, $wxuser));

			if (!$result['status']) {
				LogRecord($result['info'], "[Home/Index/refreshWxuser]" . __LINE__);
			}else{
				$result = apiCall('Weixin/Wxuser/getInfo', array($map));
				if($result['status']){
					
					$this -> userinfo = $result['info'];
					session("userinfo", $result['info']);
				}
			}

		}

	}

	/**
	 * 刷新
	 */
	private function refreshWxaccount() {
		$this->token = I('get.token', '');
		if (!empty($this->token)) {
			session("shop_token", $this->token);
		} elseif (session("?shop_token")) {
			$this->token = session("shop_token");
		}
		
		if(empty($this->token)){
			$this->token = C("SHOP_TOKEN");
		}
		
		$result = apiCall('Weixin/Wxaccount/getInfo', array( array('token' => $this->token)));
		if ($result['status'] && is_array($result['info'])) {
			$this -> wxaccount = $result['info'];
			$this -> wxapi = new \Common\Api\WeixinApi($this -> wxaccount['appid'], $this -> wxaccount['appsecret']);
		} else {
			exit("公众号信息获取失败，请重试！");
		}
		$this->assign("token",$this->token);
	}

	/**
	 * 从数据库中取得配置信息
	 */
	protected function getConfig() {
		$config = S('config_' . session_id() . '_' . session("uid"));

		if ($config === false) {
			$map = array();
			$fields = 'type,name,value';
			$result = apiCall('Admin/Config/queryNoPaging', array($map, false, $fields));
			if ($result['status']) {
				$config = array();
				if (is_array($result['info'])) {
					foreach ($result['info'] as $value) {
						$config[$value['name']] = $this -> parse($value['type'], $value['value']);
					}
				}
				//缓存配置300秒
				S("config_" . session_id() . '_' . session("uid"), $config, 300);
			} else {
				LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
				$this -> error($result['info']);
			}
		}
		C($config);
	}

	/**
	 * 根据配置类型解析配置
	 * @param  integer $type  配置类型
	 * @param  string  $value 配置值
	 */
	private static function parse($type, $value) {
		switch ($type) {
			case 3 :
				//解析数组
				$array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
				if (strpos($value, ':')) {
					$value = array();
					foreach ($array as $val) {
						list($k, $v) = explode(':', $val);
						$value[$k] = $v;
					}
				} else {
					$value = $array;
				}
				break;
		}
		return $value;
	}

}
