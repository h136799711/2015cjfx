<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Weixin\Plugin;

/**
 * 推广二维码插件
 * 
 */
class PromotioncodePlugin extends  WeixinPlugin{
	
	private $config = array(
		'downloadFolder'=>'./Uploads/Qrcode',
		'noAuthorizedMsg'=>'您还未成为族长，不能生成专属二维码！',
		'codeprefix'=>'UID_',//推广码所带前缀
	);
	private $wxapi ;
	/**
	 * @param $data 通常包含是微信服务器返回来的信息
	 * @return 返回 Wechat可处理的数组
	 */
	function process($data){
		addWeixinLog($data,'[PromotioncodePlugin]');
		if(empty($data['fans']) ){
		
			addWeixinLog($data['fans'],'[PromotioncodePlugin]');
			LogRecord("fans参数为empty", "[PromotioncodePlugin]".__LINE__);
			return array("1二维码推广插件[调用失败]","text");
		}
		
		if(empty($data['wxaccount']) ){
			LogRecord("wxaccount参数为empty", "[PromotioncodePlugin]".__LINE__);
			return array("2二维码推广插件[调用失败]","text");
		}
		
		
		//检测是否有权限生成二维码
		if(!$this->hasAuthorized($data['fans'])){
			return array($this->config['noAuthorizedMsg'],"text");
		}
		
		$this -> wxapi = new \Common\Api\WeixinApi($data['wxaccount']['appid'], $data['wxaccount']['appsecret']);
		
		
		$realfile = $this->getQrcode($this->config['codeprefix'].$data['fans']['id']);
		
		if(!file_exists($realfile)){
			return array("获取失败，请重试！","text");
		}
		//
		$realfile = $this->getPublicityPicture($data['fans'],$realfile);
		
		$media_id = S("PromotioncodePlugin_".$data['fans']['id']);
		if(empty($media_id)){
			$media_id = $this -> wxapi->uploadMaterial($realfile);
			if($media_id['status']){
				$media_id = $media_id['msg']->media_id;
				S("PromotioncodePlugin_".$data['fans']['id'],$media_id,3600);
				return array($media_id,"image");
			}else{
				return array("获取失败，请重试！","text");
			}
		}
		return array($media_id,"image");
	}
	
	/**
	 * 生成更好的带推广二维码的宣传图片
	 * TODO:生成宣传图片
	 */
	private function getPublicityPicture($fans,$realfile){
		return $realfile;		
	}
	
	/**
	 * TODO:判断当前用户是否有权利生成推广二维码
	 */
	private function hasAuthorized($fans){
		if(empty($fans) || !isset($fans['groupid'])){return false;}
		$groupid = $fans['groupid'];
		if($groupid == 0){
			return false;
		}
		$result = apiCall("Admin/GroupAccess/getInfo", array('wxuser_group_id'=>$groupid));
		if($result['status'] && is_array($result['info'])){
			if($result['info']['alloweddistribution'] == 1){
				return true;
			}
		}
		return false;
	}
	
	
	
	private function getQrcode($id){
		//上传获取一张永久二维码
		$filename = "/qrcode_uid$id.jpg";
		
		if(file_exists(realpath($this->config['downloadFolder']).$filename)){
			return realpath($this->config['downloadFolder']).$filename;
		}
		
		$json = $this -> wxapi->getQrcode($this->config['codeprefix'].strval($id));
		
		if($json['status']){
			//			
			$ticket = $json['msg'];
			if(is_object($json['msg'])){
				$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket->ticket);
				$this->download_remote_file($url, realpath($this->config['downloadFolder']).$filename);
				return realpath($this->config['downloadFolder']).$filename;
			}
		}else{
			addWeixinLog($id,"【获取二维码失败】");
		}
	}
	
	
	
	function download_remote_file($file_url,$save_to){
		$content = file_get_contents($file_url);
		file_put_contents($save_to, $content);
	}
}
