<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
namespace Common\Api;



/**
 * 推广二维码插件
 * 
 */
class PromotioncodeApi {
	
	private $config = array(
		'downloadFolder'=>'./Uploads/Qrcode',
		'noAuthorizedMsg'=>'您还未成为族长，不能生成专属二维码！',
		'codeprefix'=>'UID_',//推广码所带前缀
	);
	
	private $wxapi ;
	function __construct($config){
		if(is_array($config)){
			$this->config = $config;
		}
	}
	/**
	 * @param $data 通常包含是微信服务器返回来的信息
	 * @return 返回 Wechat可处理的数组
	 */
	function process($appid,$appsecret,$fans){
		
		//检测是否有权限生成二维码
		if(!$this->hasAuthorized($fans)){
			return array('status'=>false,'info'=>$this->config['noAuthorizedMsg']);
		}
		
		$this -> wxapi = new \Common\Api\WeixinApi($appid, $appsecret);
		
		
		$realfile = $this->getQrcode($fans['id']);
		
		if(!file_exists($realfile)){
			return array('status'=>false,'info'=>'获取失败，请重试！');
		}
		//
		$realfile = $this->getPublicityPicture($fans,$realfile);
		
		$media_id = S("PromotioncodePlugin_".$fans['id']);
		if(empty($media_id)){
			$media_id = $this -> wxapi->uploadMaterial($realfile);
			if($media_id['status']){
				$media_id = $media_id['msg']->media_id;
				S("PromotioncodePlugin_".$fans['id'],$media_id,3600);
				return array('status'=>true,'info'=>$media_id);
			}else{
				return array('status'=>false,'info'=>'获取失败，请重试！');
			}
		}
		return array('status'=>true,'info'=>$media_id);
	}
	
	/**
	 * 生成更完善效果的带推广二维码的宣传图片
	 * TODO:生成宣传图片
	 */
	private function getPublicityPicture($fans,$realfile){
		$nickname = $fans['nickname'];//昵称
		$avatar = $fans['avatar'];
		$brandName = C('BRAND_NAME');//品牌名称
		
		return $realfile;		
	}
	
	/**
	 * 判断当前用户是否有权利生成推广二维码
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
