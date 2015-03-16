<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2015, http://www.gooraye.net. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Weixin\Controller;
use Think\Controller;

/*
 * 微信通信控制器
 */
class ConnectController extends Controller {

	//TOKEN ，通信地址参数，非微信接口配置中的token
	private $token;
	//通信消息主体
	private $data = array();
	//通信的粉丝的可获取的信息
	public $fans;
	//当前通信的公众号信息
	public $wxaccount;
	
	public function test(){
//		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxafc71bde2ff1dc95&secret=4d9b29be353502e8c81b19ded8e6b10e";
//		$json =  json_decode(curlGet($url));
//		dump($json);
//		dump(json_decode($json));
//		dump($json->access_token);
		
		$data = array("action_name"=>"QR_LIMIT_STR_SCENE",
				"action_info"=>array('scene'=>array('scene_str'=>"VIP_10001")));
//		dump(json_encode($data));	
		$accessToken =	getAccessToken("wx58aea38c0796394d","3e1404c970566df55d7314ecfe9ff437");
		$obj = getQrcode($accessToken,'VIP_'.'10001');
		dump($obj);
	}

	public function index() {

		if (!class_exists('SimpleXMLElement')) {
			exit('SimpleXMLElement class not exist');
		}
		if (!function_exists('dom_import_simplexml')) {
			exit('dom_import_simplexml function not exist');
		}
		$this -> token = I('get.token', "htmlspecialchars");
		if (!preg_match("/^[0-9a-zA-Z]{3,42}$/", $this -> token)) {
			exit('error id');
		}
		
		//获取当前通信的公众号信息
		$this -> wxaccount = S('weixin_' . $this -> token);
		if (!$this -> wxaccount) {
			$result = apiCall('Weixin/Wxaccount/getInfo', array( array('token' => $this -> token)));
			if($result['status']){
				$this->wxaccount = $result['info'];
			}
			S('weixin_' . $this -> token, $this -> wxaccount, 600);
			//缓存10分钟
		}
				
		import("@.Common.Wechat");		
		
		$weixin = new \Wechat($this -> token, $this -> wxaccount['encodingaeskey'], $this -> wxaccount['appid']);
		
		
		$this -> data = $weixin -> request();
		
		if ($this -> data && is_array($this -> data)) {
			
			//读取缓存的粉丝信息
			$this -> fans = S('fans_' . $this -> token . '_' . $this -> data['FromUserName']);
			if (!$this -> fans) {
				$this -> fans = apiCall('Weixin/Wxuser/getInfo',array(array('token' => $this -> token, 'wecha_id' => $this -> data['FromUserName'])));
				S('fans_' . $this -> token . '_' . $this -> data['FromUserName'], $this -> fans);
			}
			
			//$this->my = C('site_my');
			//$open = M('TokenOpen')->where(array('id' => I('get.token')))->find();
			//$this->fun = $open['queryname'];
			list($content, $type) = $this -> reply($this -> data);
			$weixin -> response($content, $type);
		} else {
			$weixin -> response("无法识别！", \Wechat::MSG_TYPE_TEXT);
		}
	}

	//响应
	private function reply($data) {
		
		import("@.Common.Wechat");
		
//		return array('Test Connect!',\Wechat::MSG_TYPE_TEXT);
		
		$accessToken =	getAccessToken($this->wxaccount['appid'],$this->wxaccount['appsecret']);
//		return array($accessToken.'getAccessToken!',\Wechat::MSG_TYPE_TEXT);
		$obj = getQrcode($accessToken,'VIP_'.'10001');
		return array($obj->url,\Wechat::MSG_TYPE_TEXT);
		
		if (\Wechat::MSG_EVENT_CLICK == $data['Event']) {

			$data['Content'] = $data['EventKey'];
			$this -> data['Content'] = $data['EventKey'];
			
		} elseif ($data['Event'] == \Wechat::MSG_EVENT_SCAN) {
			$data['Content'] = $this -> getRecognition($data['EventKey']);
			$this -> data['Content'] = $data['Content'];
			
		} elseif ($data['Event'] == \Wechat::MSG_EVENT_MASSSENDJOBFINISH) {
			//群发任务结束
			
		} elseif (\Wechat::MSG_EVENT_SUBSCRIBE == $data['Event']) {
			
			
		} elseif ('unsubscribe' == $data['Event']) {
			$this -> requestdata('unfollownum');
		} elseif ($data['Event'] == \Wechat::MSG_EVENT_LOCATION) {
			
			//用户地理位置
			exit('');
		}

		// //语音
		if (\Wechat::MSG_TYPE_VOICE == $data['MsgType']) {
			$data['Content'] = $data['Recognition'];
			$this -> data['Content'] = $data['Recognition'];
		}

		// 当前粉丝的openid
		if (strtolower($data['Content']) == 'id') {
			return array($this -> data['FromUserName'], 'text');
		}
		
		
		if (!empty($return)) {

			//===========上面处理了请求=========
			if (is_array($return)) {
				return $return;
			} else {
				return array($return, 'text');
			}
		} else {

			//系统内置关键词处理方式
			switch ($key) {
				case '首页' :
				case 'home' :
				case '主页' :
					return $this -> home();
					break;
				
				case '帮助' :
				case 'help' :
					return $this -> help();
					break;

				default :
					//TODO: 可以检测用户请求数
					return $this -> keyword($key);
			}
		}
	}

	//END reply
	
	//首页，官网首页
	private function home(){
		return array('首页','text');
	}
	
	//帮助说明
	private function help(){
		return array("帮助信息",'text');
	}
	
}
