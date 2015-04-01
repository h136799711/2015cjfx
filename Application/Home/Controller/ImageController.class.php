<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Home\Controller;

class ImageController extends \Think\Controller {
	
	public function t2(){
		$pc = new \Common\Api\PromotioncodeApi(C('PROMOTIONCODE'));
		dump($pc);
	}

	public function test() {
		//作为背景图片
		$bgpath = realpath("./UploadsTest/qrcode_template.jpg");
		$tmppath = realpath("./UploadsTest") . '/';
		$savefilename = "./UploadsTest/" . md5(time()) . ".jpg";
		//需要合成的图片
		$arr = array( 
			array("resource" => "http://wx.qlogo.cn/mmopen/etibbrEkCpy86DVMGbniaE43pJ70f3uEETlN3x1x61OHZY3JY5ZOdUsFTywn4M0voqib5ytMn8x6W4qXnUYeS0V38akFuzNsbbn/0", 
		"isremote" => true, "x" => 180, "y" => 70, "w" => 70, "h" => 70, 'type' => 'image'), 
			array("resource" => "./UploadsTest/qrcode_uid5.jpg", "isremote" => false, "x" => 205, "y" => 545, "w" => 240, "h" => 240, 'type' => 'image'), 
			array("resource" => "http://wx.qlogo.cn/mmopen/etibbrEkCpy86DVMGbniaE43pJ70f3uEETlN3x1x61OHZY3JY5ZOdUsFTywn4M0voqib5ytMn8x6W4qXnUYeS0V38akFuzNsbbn/0", 
		"isremote" => true, "x" => 300, "y" => 640, "w" => 45, "h" => 45, 'type' => 'image'), 
			array("resource" => "我的自由你的自由", 					
					"x" => 322, 
					"y" => 94, 
					'type' => 'text',
					'font'=>realpath('./Public/cdn/fonts/daheiti.ttf'),
					'size'=>15,
					'angle'=>0,
					'color'=>array(255,255,155),
				),
			array("resource" => "杭州博也", 					
				"x" => 325, 
				"y" => 125, 
				'type' => 'text',
				'font'=>realpath('./Public/cdn/fonts/daheiti.ttf'),
				'size'=>14,
				'angle'=>0,
				'color'=>array(255,255,155),
			),
		 );
		$ret = $this -> mergeImage($bgpath, $arr, $tmppath, $savefilename);

		echo "<img src='" . trim($savefilename, ".") . "' />";
	
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

		return $savefilename;
	}

	public function index() {
		//头像配置
		$avatar = 'http://wx.qlogo.cn/mmopen/etibbrEkCpy86DVMGbniaE43pJ70f3uEETlN3x1x61OHZY3JY5ZOdUsFTywn4M0voqib5ytMn8x6W4qXnUYeS0V38akFuzNsbbn/0';
		$avatarX = 180;
		$avatarY = 70;
		$avatarWidth = 70;
		$avatarHeight = 70;
		//=====================================================================================================================================
		//二维码配置
		$qrcodeLocalPath = './UploadsTest/qrcode_uid5.jpg';
		$qrcodeX = 205;
		$qrcodeY = 545;
		$qrcodeWidth = 240;
		$qrcodeHeight = 240;
		//=====================================================================================================================================

		$avatarLocalPath = "./UploadsTest/" . md5($avatar) . "_avatar.jpg";
		if (!file_exists(realpath($avatarLocalPath))) {
			$this -> http_get_data($avatar, $avatarLocalPath);
		}

		$rootpath = realpath("./UploadsTest") . '/';
		$source = $rootpath . "qrcode_template.jpg";

		$outputpath = "./UploadsTest/" . time() . ".jpg";
		$avatarpath = "./UploadsTest/" . time() . "_avatar.jpg";

		$main = imagecreatefromjpeg($source);
		$width = imagesx($main);
		$height = imagesy($main);

		$target = imagecreatetruecolor($width, $height);
		if (function_exists("imagecopyresampled")) {
			imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);
		} else {
			imagecopyresized($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);
		}

		//		$fontSize = 18;
		//		//18号字体
		//		$fontColor = imagecolorallocate($target, 255, 0, 0);
		//		//字体的RGB颜色
		//
		//		$fontWidth = imagefontwidth($fontSize);
		//		$fontHeight = imagefontheight($fontSize);
		//
		//		$textWidth = $fontWidth * mb_strlen($text1);
		//		$x = ceil(($width - $textWidth) / 2);
		//		//计算文字的水平位置
		//
		//		imagettftext($target, $fontSize, 0, $x, 190, $fontColor, $font, $text1);

		//		$textWidth = $fontWidth * mb_strlen($text2);
		//		$x = ceil(($width - $textWidth) / 2);
		//
		//		imagettftext($target, $fontSize, 0, $x, 370, $fontColor, $font, $text2);
		//
		//		$textWidth = $fontWidth * mb_strlen($text3);
		//		$x = ceil(($width - $textWidth) / 2);
		//
		//		imagettftext($target, $fontSize, 0, $x, 560, $fontColor, $font, $text3);
		//写文字，且水平居中

		//imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用

		//		imagefilledpolygon($target, array(10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor);
		//画三角形
		//		imageline($target, 100, 200, 20, 142, $fontColor);
		//画线
		//		imagefilledrectangle($target, 50, 100, 250, 150, $fontColor);
		//画矩形

		//合成头像
		$child = imagecreatefromjpeg(realpath($avatarLocalPath));
		$pic_width = imagesx($child);
		$pic_height = imagesy($child);

		if (function_exists("imagecopyresampled")) {
			$newAvatar = imagecreatetruecolor($avatarWidth, $avatarHeight);
			imagecopyresampled($newAvatar, $child, 0, 0, 0, 0, $avatarWidth, $avatarHeight, $pic_width, $pic_height);
		} else {
			$newAvatar = imagecreate($avatarWidth, $avatarHeight);
			imagecopyresized($newAvatar, $child, 0, 0, 0, 0, $avatarWidth, $avatarHeight, $pic_width, $pic_height);
		}

		//合成图片
		imagecopymerge($target, $newAvatar, $avatarX, $avatarY, 0, 0, $avatarWidth, $avatarHeight, 100);

		//合成二维码
		$child = imagecreatefromjpeg(realpath($qrcodeLocalPath));
		$pic_width = imagesx($child);
		$pic_height = imagesy($child);

		if (function_exists("imagecopyresampled")) {
			$newAvatar = imagecreatetruecolor($qrcodeWidth, $qrcodeHeight);
			imagecopyresampled($newAvatar, $child, 0, 0, 0, 0, $qrcodeWidth, $qrcodeHeight, $pic_width, $pic_height);
		} else {
			$newAvatar = imagecreate($qrcodeWidth, $qrcodeHeight);
			imagecopyresized($newAvatar, $child, 0, 0, 0, 0, $qrcodeWidth, $qrcodeHeight, $pic_width, $pic_height);
		}

		//合成图片
		imagecopymerge($target, $newAvatar, $qrcodeX, $qrcodeY, 0, 0, $qrcodeWidth, $qrcodeHeight, 100);

		//
		imagejpeg($target, $outputpath, 95);

		imagedestroy($target);
		imagedestroy($main);

		echo "<img src='" . trim($outputpath, ".") . "' />";
	}

}
