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
	
	private $config = array( //./相对于网站根目录
		'defaultQrcode'=>'./Uploads/QrcodeMerge/qrcode_default.jpg',
		'mergeFolder'=>'./Uploads/QrcodeMerge', //合并后的二维码存储位置
		'downloadFolder'=>'./Uploads/Qrcode',   //
		'noAuthorizedMsg'=>'您还未成为族长，不能生成专属二维码！', //
		'codeprefix'=>'UID_',//推广码所带前缀
		'tmpFolder'=>'./Temp',//临时文件夹可以删除里面的内容
		'bgImg'=>'./Uploads/QrcodeMerge/qrcode_template.jpg',//背景
	);
	
	private $wxapi ;
	function __construct($config){
		if(is_array($config)){
			$this->config = $config;
		}
	}
	/**
	 * 指定粉丝的推广二维码
	 * 
	 */
	public function isExists($id){
		
		$savefilename = $this->config['mergeFolder'] .'/qrcode_uid'.$id . ".jpg";
		
		if(file_exists(realpath($savefilename))){
			return array('status'=>true,'path'=>$savefilename);
		}
		
		return array('status'=>false,'path'=>$this->config['defaultQrcode']);
	}
	/**
	 * @param $data 通常包含是微信服务器返回来的信息
	 * @param $regenerate 重新生成
	 * @return 返回 Wechat可处理的数组
	 */
	function process($appid,$appsecret,$fans,$regenerate=false){
		$hasright = $this->hasAuthorized($fans);
		//检测是否有权限生成二维码
		if($hasright === false){
			return array('status'=>false,'info'=>$this->config['noAuthorizedMsg']);
		}
		
		$this -> wxapi = new \Common\Api\WeixinApi($appid, $appsecret);
		
		
		$relativefile = $this->getQrcode($fans['id']);
		
//		addWeixinLog($relativefile,"推广二维码[relativefile]");
		if(!file_exists(realpath($relativefile))){
			return array('status'=>false,'info'=>'获取失败，请重试！');
		}
		//
		$realfile = $this->getPublicityPicture($fans,$relativefile,$regenerate);
//		addWeixinLog($realfile,"推广二维码[realfile]");
		
		$media_id = S("PromotioncodePlugin_".$fans['id']);
		//
		if(empty($media_id) || $regenerate === true){
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
	private function getPublicityPicture($fans,$relativefile,$regenerate=false){
		$nickname = $fans['nickname'];//昵称
		$avatar = $fans['avatar'];
		$brandName = C('BRAND_NAME');//品牌名称
		//作为背景图片
//		addWeixinLog("1","推广二维码");
		$bgpath = realpath($this->config['bgImg']);
		$tmppath = realpath($this->config['tmpFolder']) . '/';
		
		$savefilename = $this->config['mergeFolder'] .'/qrcode_uid'.$fans['id'] . ".jpg";
		
		if(file_exists(realpath($savefilename)) && $regenerate === false){
			//取缓存的
			return realpath($savefilename);	
		}
		
		//TODO: 判断是否已生成过，是则返回
		//需要合成的图片
		$arr = array( 
			array("resource" => $avatar, 
		"isremote" => true, "x" => 45, "y" => 45, "w" => 150, "h" => 150, 'type' => 'image'), 
			array("resource" => $relativefile, 
			"isremote" => false, "x" => 175, "y" => 470, "w" => 295, "h" => 295, 'type' => 'image'), 
//			array("resource" => $avatar, 
//		"isremote" => true, "x" => 280, "y" => 570, "w" => 45, "h" => 45, 'type' => 'image'), 
			array("resource" => $nickname, 					
					"x" => 262, 
					"y" => 115, 
					'type' => 'text',
					'font'=>realpath('./Public/cdn/fonts/daheiti.ttf'),
					'size'=>15,
					'angle'=>0,
					'color'=>array(255,255,133),
				),
		 );
		
//		addWeixinLog("2","推广二维码");
		$this -> mergeImage($bgpath, $arr, $tmppath, $savefilename);
		
//		addWeixinLog("3","推广二维码");
		return realpath($savefilename);		
	}
	
	/**
	 * 判断当前用户是否有权利生成推广二维码
	 */
	private function hasAuthorized($fans){
		if(empty($fans) || !isset($fans['groupid'])){return false;}
		$groupid = $fans['groupid'];
		if($groupid != C('ROLE_ZUZHANG')){
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
			return ($this->config['downloadFolder']).$filename;
		}
		
		$json = $this -> wxapi->getQrcode($this->config['codeprefix'].strval($id));
		
		if($json['status']){
			//			
			$ticket = $json['msg'];
			if(is_object($json['msg'])){
				$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket->ticket);
				$this->download_remote_file($url, realpath($this->config['downloadFolder']).$filename);
				return $this->config['downloadFolder'].$filename;
			}
		}else{
			addWeixinLog($id,"【获取二维码失败】");
		}
	}
	
	
	
	function download_remote_file($file_url,$save_to){
		$content = file_get_contents($file_url);
		file_put_contents($save_to, $content);
	}
	
	
	function http_get_data($url, $filename) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		ob_start();
		curl_exec($ch);
		$return_content = ob_get_contents();
		ob_end_clean();

		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$fp = @fopen($filename, "a");
		//将文件绑定到流
		fwrite($fp, $return_content);
		//写入文件

		return $filename;
	}
	/**
	 * $bgpath,$arr,$tmppath
	 * 背景图，源合成图片，远程文件下载存放临时文件夹
	 */
	private function mergeImage($bgpath, $arr, $tmppath, $savefilename) {
		
		$bg = imagecreatefromjpeg($bgpath);
		$bgwidth = imagesx($bg);
		$bgheight = imagesy($bg);
		$bgCopy = imagecreatetruecolor($bgwidth, $bgheight);
		if (function_exists("imagecopyresampled")) {
			imagecopyresampled($bgCopy, $bg, 0, 0, 0, 0, $bgwidth, $bgheight, $bgwidth, $bgheight);
		} else {
			imagecopyresized($bgCopy, $bg, 0, 0, 0, 0, $bgwidth, $bgheight, $bgwidth, $bgheight);
		}
		
		foreach ($arr as $vo) {
			if ($vo['type'] == 'image') {
				if ($vo['isremote']) {
					$imgpath = $tmppath . md5($vo['resource'])  . ".jpg";
					if (!file_exists(realpath($imgpath))) {
						$this -> http_get_data($vo['resource'], $imgpath);
					}
				} else {
					$imgpath = $vo['resource'];
				}
				$child = imagecreatefromjpeg(realpath($imgpath));
				$pic_width = imagesx($child);
				$pic_height = imagesy($child);

				if (function_exists("imagecopyresampled")) {
					$new = imagecreatetruecolor($vo['w'], $vo['h']);
					imagecopyresampled($new, $child, 0, 0, 0, 0, $vo['w'], $vo['h'], $pic_width, $pic_height);
				} else {
					$new = imagecreate($vo['w'], $vo['h']);
					imagecopyresized($new, $child, 0, 0, 0, 0, $vo['w'], $vo['h'], $pic_width, $pic_height);
				}

				//合成图片
				imagecopymerge($bgCopy, $new, $vo['x'], $vo['y'], 0, 0, $vo['w'], $vo['h'], 100);

				//			imagejpeg($bgCopy,realpath($savefilename));
				imagedestroy($new);
				imagedestroy($child);
			}elseif($vo['type'] == 'text'){
				
				if(isset($vo['color'])){
					$color = ImageColorAllocate($bgCopy,$vo['color'][0],$vo['color'][1],$vo['color'][2]);
				}else{
					$color = ImageColorAllocate($bgCopy,0,0,0);
				}
//				$name=iconv("gd2312","utf-8","盼盼");
//				$str = iconv("gb2312","UTF-8",$vo['resource']);
				$str = mb_convert_encoding($vo['resource'], 'utf-8', 'auto');
				imagettftext($bgCopy,$vo['size'],0,$vo['x'],$vo['y'],$color,$vo['font'],$str);
//				imagettftext($bgCopy,$vo['size'],0,$vo['x'],$vo['y'],$color,$vo['font'],"adfadsf2312aewrwerwewr");
//				imagestring($bgCopy,$vo['size'],$vo['x'],$vo['y'],$str,$color);
			}
		}

		imagejpeg($bgCopy, ($savefilename));

		imagedestroy($bgCopy);
		
		addWeixinLog("3。1","推广二维码");
		return $savefilename;
	}
	
	
	
}
