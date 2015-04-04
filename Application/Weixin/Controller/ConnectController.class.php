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
class ConnectController extends WeixinController {

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
	public $data = array();
	//通信的粉丝的可获取的信息
	public $fans;
	//当前通信的公众号信息
	public $wxaccount;
	
	private $wxapi;
	
	private function getPluginParams(){
		return array("fans"=>$this->fans,"data"=>$this->data,"wxaccount"=>$this->wxaccount);
	}
	
	protected function _initialize() {
		parent::_initialize();
		
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
		
//		if (!session("?weixin_wxaccount")) {
//			$result = apiCall('Weixin/Wxaccount/getInfo', array( array('token' => $this -> token)));
//			if ($result['status']) {
//				$this -> wxaccount = $result['info'];
//			}
//			session("weixin_wxaccount" , $this -> wxaccount);
//		}else{
//			$this->wxaccount = session("weixin_wxaccount");
//		}
		
		$this -> wxapi = new \Common\Api\WeixinApi($this -> wxaccount['appid'], $this -> wxaccount['appsecret']);

		if (I('test','0') == 1) {
			$this -> data['Event'] = (I('post.event', ''));
			$this -> data['MsgType'] = (I('post.msgtype', ''));
			$this -> data['Content'] = (I('post.keyword', ''));
			echo json_encode($this -> reply(),JSON_UNESCAPED_UNICODE);
			return;
		}

		import("@.Common.Wechat");

		$weixin = new \Wechat($this -> token, $this -> wxaccount['encodingaeskey'], $this -> wxaccount['appid']);

		$this -> data = $weixin -> request();

		if ($this -> data && is_array($this -> data)) {
			$fanskey = "appid_".$this -> wxaccount['appid']."_" . $this->getOpenID();

		
			//读取缓存的粉丝信息
			$this -> fans = S($fanskey);
			if (is_null($this->fans) || $this -> fans === false) {
				
				$result = apiCall('Weixin/Wxuser/getInfo', array( array('wxaccount_id'=>$this -> wxaccount['id'], 'openid' => $this->getOpenID())));
				addWeixinLog($result,"wxuser getInfo");
				if ($result['status'] && is_array($result['info'])) {
					S($fanskey,  $result['info'],600);//10分钟
					$this -> fans = $result['info'];
				} else {
					//$this->addWxuser();
					$this -> fans = null;
					S($fanskey,  null);//清除
				}
			}
			
			$reply = $this -> reply();
			if(empty($reply)){
				exit("");
			}
			list($content, $type) = $reply;
//			$weixin -> response(serialize($content), self::MSG_TYPE_TEXT);
			$weixin -> response($content, $type);
		} else {
			$weixin -> response("无法识别！", self::MSG_TYPE_TEXT);
		}
	}
	
	//响应
	private function reply() {
		import("@.Common.Wechat");
		//转化为小写
		$this -> data['Event'] = strtolower($this -> data['Event']);
		$this -> data['MsgType'] = strtolower($this -> data['MsgType']);
		if($this->data['Event']  != \Wechat::MSG_EVENT_LOCATION){
			addWeixinLog($this->data,"【来自微信服务器消息】");
		}
		$return = "";
		
		//=====================微信事件转化为系统内部可处理
		if ($this -> data['MsgType'] == self::MSG_TYPE_EVENT) {
			//接收事件推送
			switch ($this->data['Event']) {

				case \Wechat::MSG_EVENT_CLICK :
					$return = $this -> menuClick();
					break;
				case \Wechat::MSG_EVENT_VIEW :
					$return = $this -> menuView();
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
					//用户自动上报地理位置
					$return = $this -> locationProcess();
					break;
				default :
					break;
			}
		} else {
			//接受普通消息
			switch ($this->data['MsgType']) {
				case self::MSG_TYPE_TEXT :
					$return = $this -> textProcess();
					break;
				case self::MSG_TYPE_IMAGE :
					$return = $this -> imageProcess();
					break;
				case self::MSG_TYPE_VIDEO :
					$return = $this -> videoProcess();
					break;
				case self::MSG_TYPE_LOCATION :
					//用户手动发送地理位置
					$return = $this -> locationProcess();
					//群发任务结束
					break;
				case self::MSG_TYPE_LINK :
					break;
				case self::MSG_TYPE_VOICE :
					$return = $this -> voiceProcess();
					break;
				default :
					break;
			}
		}
		
		//=====================系统内置其它方法响应微信处理
		if(empty($return)){
			//只在上面的处理方法，无法处理时才进行下面处理
			$return = $this->innerProcess();
		}
		return $return;
	}

	//END reply
	
	private $Plugins = array(
		'_promotioncode_'=>"Promotioncode",
	);
	
	private function innerProcess(){
		
		//系统内置关键词处理方式
		//统一以包括上_
		switch (strtolower($this->data['Content'])) {
			case 'id' :
				// 当前粉丝的openid
				$return = array($this -> getOpenID(), self::MSG_TYPE_TEXT);
				break;
			case '_promotioncode_':
				//TODO: 考虑从数据库中取得 关键词对应的插件标识名
				addWeixinLog($this->getPluginParams(),"[Promotioncode]");
				$return = pluginCall($this->Plugins['_promotioncode_'],array($this->getPluginParams()));
				
//				$return = pluginCall("Promotioncode",array($this->getPluginParams()));
				break;
			default :
				//TODO: 可以检测用户请求数
				break;
		}
		
		return $return;
	}
	
	//=======================用户发送给公众号的消息类型
	/**
	 * 处理用户发送的图片消息
	 */
	private function videoProcess() {
		return "";
	}
	
	/**
	 * 处理用户发送的文本消息
	 */
	private function textProcess($keyword='') {
		if(empty($keyword)){
			$keyword = $this->data['Content'];
		}
		
		$map = array('keyword'=>$keyword);
		
		//文本响应
		$result = apiCall("Weixin/WxreplyText/getInfo",array($map));
		
		if($result['status'] && is_array($result['info'])){
			return array((($result['info']['content'])) , self::MSG_TYPE_TEXT);
		}
		
		//图文响应
		$result = apiCall("Weixin/WxreplyNews/queryWithPicture",array($map,'sort desc'));
		
		if($result['status'] && !is_null($result['info'])){
			$siteurl = C("SITE_URL");
			//多图文
			$newslist = array();
			foreach($result['info'] as $key=>$news){				
					array_push($newslist,array($news['title'],$news['description'],$siteurl.getPictureURL($news['piclocal'],$news['picremote']),$news['url']));
			}	
			return array($newslist , self::MSG_TYPE_NEWS);
		}
		
		return "";
	}

	/**
	 * 处理用户发送的图片消息
	 * TODO:多图文查询
	 */
	private function imageProcess() {
		$keyword = $this->data['Content'];
		return "";
	}

	/**
	 * 处理用户发送的语音消息
	 */
	private function voiceProcess() {
		$this -> data['Content'] = $this -> data['Recognition'];
		return "";
	}

	/**
	 * 地理位置上报处理
	 */
	private function locationProcess() {
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

	//========================微信事件处理方法

	/**
	 * 自定义菜单事件
	 *  ToUserName	开发者微信号
	 FromUserName	发送方帐号（一个OpenID）
	 CreateTime	消息创建时间 （整型）
	 MsgType	消息类型，event
	 Event	事件类型，CLICK
	 EventKey	事件KEY值，与自定义菜单接口中KEY值对应
	 */
	private function menuClick() {
		//点击菜单拉取消息时的事件推送
		$this->data['Content'] = $this->data['EventKey'];
		
		addWeixinLog($this->data['Content'],"menuClick");
		if(empty($return)){
			
		}
		
		return $return;

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
	private function menuView() {
		//点击菜单跳转链接时的事件推送
		//TODO：统计自定义菜单的点击次数
		return "";
	}

	/**
	 * 处理二维码扫描事件
	 */
	private function qrsceneProcess($eventKey) {
		$addWxuserflag = false; 
		//$eventKey
		//TODO: 处理二维码扫描事件
		//TODO: 转到插件中处理
		if(strpos($eventKey, 'UID_') === 0){
			$eventKey = intval(str_replace('UID_', '', $eventKey));
		
			if (is_int($eventKey) && $eventKey > 0) {
				$addWxuserflag = true;			
				$this->addWxuser($eventKey);
			}
			addWeixinLog("用户uid= " . $eventKey, "【微信消息】");
		}
		
		if(!$addWxuserflag){
			$this->addWxuser();
		}
		
		return "";

	}

	/**
	 * 关注事件
	 */
	private function subscribe() {
		addWeixinLog($this->data, "[subscribe]");
		if (isset($this -> data['EventKey']) && !empty($this->data['EventKey'])) {
			//TODO: 处理用户通过推广二维码进行关注的事件
			$eventKey = $this -> data['EventKey'];
			addWeixinLog("[subscribe]  EventKey = " . $eventKey, "关注消息带场景KEY");
			$this -> qrsceneProcess(str_replace("qrscene_", "", $eventKey));
		} else {
			//扫描公众号二维码进行关注
			$this->addWxuser();
		}
		
		$ss_keyword = C("SS_KEYWORD");
		addWeixinLog("[SS_KEYWORD]".$ss_keyword, "首次关注回复关键词");
		if(!empty($ss_keyword)){
			return $this->textProcess($ss_keyword);//处理关键词
		}
		addWeixinLog("[subscribe]".$this -> getOpenID(), "关注消息");
		return "";
	}

	/**
	 * 取消关注
	 */
	private function unsubscribe() {
		//TODO: 取消关注
		//==更新粉丝为未关注
		$wxuser = array('subscribed' => 0);
		$result = apiCall('Weixin/Wxuser/save', array( array('openid' => $this -> getOpenID(),'wxaccount'=>$this->wxaccount['id']), $wxuser));
		if (!$result['status']) {
			LogRecord($result['info'], __FILE__);
		}
		addWeixinLog("[unsubscribe]" . $this -> getOpenID(), "取消关注消息");
		return "";
	}

	/**
	 * 用户已二维码扫描关注事件
	 */
	private function qrsceneScan() {
		$eventKey = $this -> data['EventKey'];
		addWeixinLog("[qrsceneScan]" . $eventKey, "微信消息");
		return $this -> qrsceneProcess($eventKey);
	}

	//======================================其它辅助方法
	
	private function addWxuserFamily($referrer){
		$wxaccount_id = $this->wxaccount['id'];
		$openid = $this->getOpenID();
		$parentFamily = "";
				
		$result = apiCall("Weixin/WxuserFamily/createOneIfNone",array($wxaccount_id,$openid));
				
		addWeixinLog($result,"WxuserFamily的粉丝信息 2".$referrer);
		if($result['status']){
			
			if($referrer > 0){
				$parentFamily = apiCall("Weixin/Wxuser/getInfoWithFamily",array($referrer));
				addWeixinLog($parentFamily,"getInfoWithFamily的粉丝信息3");
				//如果有推荐人,则更新当前用户的家族关系
				if($parentFamily['status'] && is_array($parentFamily['info'])){
					$this->updateWxuserFamily($result['info'], $parentFamily['info']);
				}
			}else{
				$family = apiCall("Weixin/Wxuser/getInfoWithFamily",array($result['info']));
				addWeixinLog($family,"[当前用户的家族关系]");
				if($family['status'] && is_array($family['info'])){
					$referrer = $family['info']['parent_1'];
					
					return $referrer;
				}
			}
			
			return 0;
		}

		return 0;
		
		
	}
	
	/**
	 * 更新微信用户家族关系
	 */
	private function updateWxuserFamily($id,$parentFamily){
		$family = array(
			'parent_1'=>$parentFamily['wxuserid'],
			'parent_2'=>$parentFamily['parent_1'],
			'parent_3'=>$parentFamily['parent_2'],
			'parent_4'=>$parentFamily['parent_3'],//4级
		);
		addWeixinLog($family,$id.'[updateWxuserFamily]');
		
		$result = apiCall("Weixin/WxuserFamily/saveByID",array($id,$family));
		ifFailedLogRecord($result,'[updateWxuserFamily]'.__LINE__);
	}
	
//	private function addCommission(){
//		$wxaccount_id = $this->wxaccount['id'];
//		$openid = $this->getOpenID();
//		
//		$result = apiCall("Weixin/Commission/createOneIfNone",array($wxaccount_id,$openid));
//		addWeixinLog($result,"Commission的粉丝信息 2");
//		if($result['status']){
//			return $result['info'];
//		}else{
//			return -1;
//		}
//	}
	/**
	 * 插入粉丝信息
	 */
	private function addWxuser($referrer = 0,$cnt=0) {

		addWeixinLog($referrer,"addWxuser 1");
		$openid = $this -> getOpenID();
		$userinfo = $this -> wxapi -> getBaseUserInfo($openid);
		
		
		if(!$userinfo['status']){
			LogRecord($userinfo['info'], __FILE__.__LINE__);
			if($cnt > 1){
				return ;
			}
			$this->addWxuser($referrer,$cnt+1);
		}
		$userinfo = $userinfo['info'];
		addWeixinLog($userinfo,"openid的粉丝信息 2");
		
		$map = array('openid' => $this -> getOpenID(), 'wxaccount_id' => $this->wxaccount['id'] );
		
		$result = apiCall('Weixin/Wxuser/getInfo', array($map));//当前粉丝的信息是否已经存在记录
		
		$family = apiCall("Weixin/Wxuser/getInfoWithFamily",array($referrer));//获取推荐人的家族关系
		
		//检测推荐人是否合法
		if($result['status'] && is_array($result['info']) && $family['status'] && !$this->checkReferrer($result['info']['id'],$family['info'])){
			$this->wxapi->sendTextToFans($this->getOpenID(),"推荐人无效！");
			$referrer = $result['info']['referrer'];
//			return ;
		}
		
		$wxuser = array();
		$wxuser['wxaccount_id'] = intval($this->wxaccount['id']);
		$wxuser['openid'] = $openid;
		$wxuser['nickname'] = '';
		$wxuser['avatar'] = '';
		$wxuser['referrer'] = $referrer;
		$wxuser['sex'] = 0;
		$wxuser['province'] = '';
		$wxuser['country'] = 'CN';
		$wxuser['city'] = "";
		//如果$referrer = 0 
		//获取其父亲ID
		$newreferrer = $this->addWxuserFamily($referrer);
		$dnSendText = false;//是否推送关注信息给用户
		//处理 用户取消关注后，再关注的情况，恢复其家族关系
		if($referrer == 0 && $newreferrer > 0){
			$referrer = $newreferrer;
			$wxuser['referrer'] = $newreferrer;
			$dnSendText = true;
		}
		addWeixinLog("$newreferrer","newreferrer");
//		$this->addCommission();
		$wxuser['subscribe_time'] = time();
		$wxuser['subscribed'] = 1;
		
		if (is_array($userinfo)) {
			$wxuser['nickname'] = $userinfo['nickname'];
			$wxuser['province'] = $userinfo['province'];
			$wxuser['country'] = $userinfo['country'];
			$wxuser['city'] = $userinfo['city'];
			$wxuser['sex'] = $userinfo['sex'];
			$wxuser['avatar'] = $userinfo['headimgurl'];
			$wxuser['subscribe_time'] = $userinfo['subscribe_time'];
			$wxuser['subscribed'] = 1;
		}
		
		
		addWeixinLog($result['info'],"openid的粉丝信息是否存在 4");
		addWeixinLog($wxuser,"openid的添加的粉丝内容 5");
				
		//判断是否已记录
		if (is_array($result['info'])) {
			//更新
			$result = apiCall('Weixin/Wxuser/save', array($map, $wxuser));
		} else {
			//新增
			$result = apiCall('Weixin/Wxuser/add', array($wxuser));
		}
		
		
		
		if ($result['status']) {
			//发送消息给父辈们相关人员
			$family = apiCall("Weixin/WxuserFamily/getInfo",array($map));//当前的家族关系
			if($family['status']){
				$this->sendTextToFans($wxuser,$family['info'],$dnSendText);
				return ;
			}
		} 


		LogRecord($result['info'], __FILE__.__LINE__);

	}
	
	/**
	 * 检测推荐人是否合法
	 * @param $referrer 推荐人
	 * @param $id 当前用户ID
	 */
	private function checkReferrer($curID,$family){
		if($curID == 0){return true;}
		if($curID == $family['wxuserid']){
			//不能自己推荐自己
			return false;
		}
		
		for($i=1;$i<=5;$i++){
			if( $family["parent_".$i] == $curID ){
				//当前粉丝不能是推荐人的父辈
				//不能关注自己的下级
				return false;
			}
		}
		
				
		return true;		
	}
	
	private function sendTextToFans($wxuser,$family,$dnSendText=false){
		if(is_array($family)){
			
			$map['id'] = array('in',array($family['parent_1'],$family['parent_2'],$family['parent_3'],$family['parent_4'],$family['parent_5']));	
			
			$result = apiCall("Weixin/Wxuser/queryNoPaging",array($map,));	
			if($result['status']){
				$wxusers =  $result['info'];
				$levels = array("一","二","三","四","五");
				foreach($wxusers as $key=>$vo){
					if($vo['id'] == $family['parent_1']){
						$text = "【".$wxuser['nickname']."】通过二维码关注了本公众号，成为您的家族一级成员";
						if($dnSendText){
							$text = "【".$wxuser['nickname']."】通过二维码重新关注了本公众号，重新成为您的家族一级成员";
						}
						$this->wxapi->sendTextToFans($vo['openid'],$text);
					}elseif($vo['id'] == $family['parent_2']){
						$text = "【".$wxuser['nickname']."】通过二维码关注了本公众号，成为您的家族二级成员";
						if($dnSendText){
							$text = "【".$wxuser['nickname']."】通过二维码重新关注了本公众号，重新成为您的家族二级成员";
						}
						$this->wxapi->sendTextToFans($vo['openid'],$text);
					}elseif($vo['id'] == $family['parent_3']){
						$text = "【".$wxuser['nickname']."】通过二维码关注了本公众号，成为您的家族三级成员";
						if($dnSendText){
							$text = "【".$wxuser['nickname']."】通过二维码重新关注了本公众号，重新成为您的家族三级成员";
						}
						$this->wxapi->sendTextToFans($vo['openid'],$text);
					}elseif($vo['id'] == $family['parent_4']){
						$text = "【".$wxuser['nickname']."】通过二维码关注了本公众号，成为您的家族四级成员";
						if($dnSendText){
							$text = "【".$wxuser['nickname']."】通过二维码重新关注了本公众号，重新成为您的家族四级成员";
						}
						$this->wxapi->sendTextToFans($vo['openid'],$text);
					}elseif($vo['id'] == $family['parent_5']){
//						$text = "【".$wxuser['nickname']."】通过二维码关注了本公众号，成为您的家族五级成员";
//						if($dnSendText){
//							$text = "【".$wxuser['nickname']."】通过二维码重新关注了本公众号，重新成为您的家族五级成员";
//						}
//						$this->wxapi->sendTextToFans($vo['openid'],$text);
					}
				}
			}	
		}
	}

	/*
	 * 获取openid
	 */
	private function getOpenID() {
		return $this -> data['FromUserName'];
	}

}
