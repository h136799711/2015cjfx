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
	
	
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_IMAGE = 'image';
    const MSG_TYPE_VOICE = 'voice';
    const MSG_TYPE_VIDEO = 'video';
    const MSG_TYPE_MUSIC = 'music';
    const MSG_TYPE_NEWS = 'news';
    const MSG_TYPE_LOCATION = 'location';
    const MSG_TYPE_LINK = 'link';
    const MSG_TYPE_EVENT = 'event';
	
	
	//TOKEN ，通信地址参数，非微信接口配置中的token
	private $token;
	//通信消息主体
	private $data = array();
	//通信的粉丝的可获取的信息
	public $fans;
	//当前通信的公众号信息
	public $wxaccount;

	public function test() {
		//		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxafc71bde2ff1dc95&secret=4d9b29be353502e8c81b19ded8e6b10e";
		//		$json =  json_decode(curlGet($url));
		//		dump($json);
		//		dump(json_decode($json));
		//		dump($json->access_token);

		$data = array("action_name" => "QR_LIMIT_STR_SCENE", "action_info" => array('scene' => array('scene_str' => "VIP_10001")));
		//		dump(json_encode($data));
		$accessToken = getAccessToken("wx58aea38c0796394d", "3e1404c970566df55d7314ecfe9ff437");
		$obj = getQrcode($accessToken, 'VIP_' . '10001');
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
			if ($result['status']) {
				$this -> wxaccount = $result['info'];
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
				$this -> fans = apiCall('Weixin/Wxuser/getInfo', array( array('token' => $this -> token, 'wecha_id' => $this -> data['FromUserName'])));
				S('fans_' . $this -> token . '_' . $this -> data['FromUserName'], $this -> fans);
			}

			//$this->my = C('site_my');
			//$open = M('TokenOpen')->where(array('id' => I('get.token')))->find();
			//$this->fun = $open['queryname'];
			list($content, $type) = $this -> reply();
			$weixin -> response($content, $type);
		} else {
			$weixin -> response("无法识别！", MSG_TYPE_TEXT);
		}
	}

	//响应
	private function reply() {
		
		import("@.Common.Wechat");
		
		//转化为小写
		$this->data['Event'] = strtolower($this->data['Event']);
		$this->data['MsgType'] = strtolower($this->data['MsgType']);
		
		//		return array('Test Connect!',\Wechat::MSG_TYPE_TEXT);

//		$accessToken = getAccessToken($this -> wxaccount['appid'], $this -> wxaccount['appsecret']);
		//		return array($accessToken.'getAccessToken!',\Wechat::MSG_TYPE_TEXT);
//		$obj = getQrcode($accessToken, 'VIP_' . '10001');
//		return array($obj -> url, \Wechat::MSG_TYPE_TEXT);
		$return = "";
		
		//=====================微信事件转化为系统内部可处理
		switch ($this->data['Event']) {
			case \Wechat::MSG_EVENT_CLICK :
				$return = $this-> menuClick();
				break;
			case \Wechat::MSG_EVENT_VIEW :
				$return = $this-> menuView();
				break;
			case \Wechat::MSG_EVENT_SCAN :
				$return = $this -> qrsceneScan();
				break;
			case \Wechat::MSG_EVENT_MASSSENDJOBFINISH :
				//群发任务结束
				break;
			case \Wechat::MSG_EVENT_SUBSCRIBE :
				$return = $this -> subscribe();
				break;
			case \Wechat::MSG_EVENT_UNSUBSCRIBE :
				$return = $this -> unsubscribe();
				break;
			case \Wechat::MSG_EVENT_LOCATION :
				//用户地理位置
				exit('');
				break;
			default :
				break;
		}
		
		////语音
		if (MSG_TYPE_VOICE == $this->data['MsgType']) {
			$this->data['Content'] = $this->data['Recognition'];
		}
		
		//=====================系统微信响应处理
		if (!empty($return)) {
			
			//===========上面处理了请求=========
			if (is_array($return)) {//如果是数组
				return $return;
			} else {
				$return = array($return, MSG_TYPE_TEXT);
			}
		} else {

			//系统内置关键词处理方式
			switch ($this->data['Content']) {
				case 'id' :
					// 当前粉丝的openid
					$return = array($this->getOpenID(), MSG_TYPE_TEXT);
					break;
				case '首页' :
				case 'home' :
				case '主页' :
					$return = $this -> home();
					break;

				case '帮助' :
				case 'help' :
					$return = $this -> help();
					break;

				default :
					//TODO: 可以检测用户请求数
					break;
			}
		}

		return $return;
	}

	//END reply
	
	/**
	 * 自定义菜单事件
	 *  ToUserName	开发者微信号
		FromUserName	发送方帐号（一个OpenID）
		CreateTime	消息创建时间 （整型）
		MsgType	消息类型，event
		Event	事件类型，CLICK
		EventKey	事件KEY值，与自定义菜单接口中KEY值对应
	 */
	private function menuClick(){
		//点击菜单拉取消息时的事件推送		
		return "";
		
	}
	 
	/**
	 * 自定义菜单事件
	 *  ToUserName	开发者微信号
		FromUserName	发送方帐号（一个OpenID）
		CreateTime	消息创建时间 （整型）
		MsgType	消息类型，event
		Event	事件类型，VIEW
		EventKey	事件KEY值，设置的跳转URL
	 */
	private function menuView(){
		//点击菜单跳转链接时的事件推送		
		//TODO：统计自定义菜单的点击次数
		return "";
	}
	
	/**
	 * 地理位置上报处理
	 */
	private function locationProcess(){
		//ToUserName	开发者微信号
		//FromUserName	发送方帐号（一个OpenID）
		//CreateTime	消息创建时间 （整型）
		//MsgType	消息类型，event
		//Event	事件类型，LOCATION
		//Latitude	地理位置纬度
		//Longitude	地理位置经度
		//Precision	地理位置精度
		
		//TODO: 地理位置上报处理
		return "";
		
	}
	
	/**
	 * 处理二维码扫描事件
	 */
	private function qrsceneProcess($eventKey){
		//$eventKey  
		//TODO: 处理二维码扫描事件
		addWeixinLog("二维码扫描处理".$eventKey,"微信消息");	
		return "";
		
	}
	
	/**
	 * 关注事件
	 */
	private function subscribe() {
		if(isset($this->data['EventKey'])){
			//TODO: 处理用户通过推广二维码进行关注的事件
			$eventKey = $this->data['EventKey'];
			addWeixinLog("subscribe".$eventKey,"微信消息");		
			$this->qrsceneProcess(str_replace("qrscene_", "", $eventKey));	
		}
		addWeixinLog($this->getOpenID()."[subscribe]".$eventKey,"微信消息");		
		return "";
	}
	
	/**
	 * 取消关注
	 */
	private function unsubscribe() {
		//TODO: 取消关注
		addWeixinLog("[unsubscribe]".$this->getOpenID(),"微信消息");
		return "";
	}
	
	/**
	 * 用户已二维码扫描关注事件
	 */
	private function qrsceneScan(){
		$eventKey = $this->data['EventKey'];
		addWeixinLog("[qrsceneScan]".$eventKey,"微信消息");
		return $this->qrsceneProcess($eventKey);
	}
	/*
	 * 获取openid
	 */
	private function getOpenID(){
		return $this->data['FromUserName'];
	}
	//首页，官网首页
	private function home() {
		return array('首页', MSG_TYPE_TEXT);
	}

	//帮助说明
	private function help() {
		return array("帮助信息", MSG_TYPE_TEXT);
	}

}
