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

		import("@.Common.Wechat");

		//获取当前通信的公众号信息
		$this -> wxaccount = S('weixin_' . $this -> token);
		if (!$this -> wxaccount) {
			$this -> wxaccount = apiCall('Weixin/Wxaccount/getInfo', array( array('token' => $this -> token)));
			S('weixin_' . $this -> token, $this -> wxuser, 600);
			//缓存10分钟
		}

		$weixin = new \Wechat($this -> token, $this -> wxuser['encodingaeskey'], $this -> wxuser['appid']);
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
		
		return array('Test Connect!',\Wechat::MSG_TYPE_TEXT);
		
		if (\Wechat::MSG_EVENT_CLICK == $data['Event']) {

			$data['Content'] = $data['EventKey'];
			$this -> data['Content'] = $data['EventKey'];

		} elseif ($data['Event'] == \Wechat::MSG_EVENT_SCAN) {
			$data['Content'] = $this -> getRecognition($data['EventKey']);
			$this -> data['Content'] = $data['Content'];
			
		} elseif ($data['Event'] == \Wechat::MSG_EVENT_MASSSENDJOBFINISH) {
			//群发任务结束
			
		} elseif (\Wechat::MSG_EVENT_SUBSCRIBE == $data['Event']) {

			//关注时回复
//			$this -> behaviordata('follow', '1');
//			$this -> requestdata('follownum');
//			$follow_data = M('Areply') -> field('home,keyword,content') -> where(array('token' => $this -> token)) -> find();

//			if ($follow_data['home'] == 1) {

				// return $this->keyword($follow_data['keyword']);

//			} else {
//				return array(html_entity_decode($follow_data['content']), 'text');
//			}
			
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
		
		//          import("Org.GetPin");
		//          $Pin = new \GetPin();
		//          $key = $data['Content'];
		//          $datafun = explode(',', $this->fun);
		//          $tags = $this->get_tags($key);
		//          $back = explode(',', $tags);
		//
		//          if ($key == '首页' || $key == 'home') {
		//              return $this->home();
		//          }
		//
		//          foreach ($back as $keydata => $data) {
		//
		//              $string = $Pin->Pinyin($data);
		//
		//              if (in_array($string, $datafun) && $string) {
		//                  if ($string == 'fujin') {
		//                      $this->recordLastRequest($key);
		//                  }
		//
		//                  $this->requestdata('textnum');
		//                  unset($back[$keydata]);
		//
		//                  // 判断以关键字中文的拼音的函数是否存在
		//                  if (method_exists('WxProxyController', $string)) {
		//                      eval('$return = $this->' . $string . '($back);');
		//                  }
		//
		//                  break;
		//              }
		//          }

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

}
