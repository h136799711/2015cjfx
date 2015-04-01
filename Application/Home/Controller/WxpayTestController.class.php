<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

class WxpayTestController extends Controller {

	public function imageMerge() {				
		//http://my.oschina.net/cart/
		$this->generateImg ( 'http://wx.qlogo.cn/mmopen/etibbrEkCpy86DVMGbniaE43pJ70f3uEETlN3x1x61OHZY3JY5ZOdUsFTywn4M0voqib5ytMn8x6W4qXnUYeS0V38akFuzNsbbn/0', '哈哈', '嘿嘿', '呵呵',__ROOT__.'/Public/cdn/fonts/SourceHanSansCN-Regular.otf' );
		exit ();
	}

	function generateImg($source, $text1, $text2, $text3, $font) {
		
		$date = '' . date('Ymd') . '/';
		$img = $date . md5($source . $text1 . $text2 . $text3) . '.jpg';
//		if (file_exists('/alidata/8raw.com/1.test/Uploads/' . $img)) {
//			return $img;
//		}

//		$main = imagecreatefromjpeg($source);
//
//		$width = imagesx($main);
//		$height = imagesy($main);
//
//		$target = imagecreatetruecolor($width, $height);
//
//		$white = imagecolorallocate($target, 255, 255, 255);
//		imagefill($target, 0, 0, $white);
//
//		imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);
//
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
//		// $textHeight = $fontHeight;
//		// $y = ceil(($height - $textHeight) / 2);//计算文字的垂直位置
//
//		imagettftext($target, $fontSize, 0, $x, 190, $fontColor, $font, $text1);
//
//		$textWidth = $fontWidth * mb_strlen($text2);
//		$x = ceil(($width - $textWidth) / 2);
//
//		imagettftext($target, $fontSize, 0, $x, 370, $fontColor, $font, $text2);
//
//		$textWidth = $fontWidth * mb_strlen($text3);
//		$x = ceil(($width - $textWidth) / 2);
//
//		imagettftext($target, $fontSize, 0, $x, 560, $fontColor, $font, $text3);
//		//写文字，且水平居中
//
//		//imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用
//
//		imagefilledpolygon($target, array(10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor);
//		//画三角形
//		imageline($target, 100, 200, 20, 142, $fontColor);
//		//画线
//		imagefilledrectangle($target, 50, 100, 250, 150, $fontColor);
//		//画矩形

		//bof of 合成图片
		$child1 = imagecreatefromjpeg('http://1.test.8raw.com/Uploads/QrcodeMerge/qrcode.jpg');
		imagecopymerge($target, $child1, 0, 300, 0, 0, imagesx($child1), imagesy($child1), 100);
		//eof of 合成图片
		
		@mkdir('/alidata/8raw.com/1.test/Uploads/' . $date);
		imagejpeg($target, '/alidata/8raw.com/1.test/Uploads/' . $img, 95);

		imagedestroy($main);
		imagedestroy($target);
		imagedestroy($child1);
		return $img;
	}

	public function index() {
		$payConfig = C('WXPAY_CONFIG');

		$itemdesc = "COS-精华套装";
		$trade_no = $this -> getOrderID();
		$total_fee = 1;
		$this -> setWxpayConfig($payConfig, $trade_no, $itemdesc, $total_fee);
		$this -> display();
	}

	private function getOrderID() {
		return date('YmdHis', time()) . $this -> randInt();

	}

	private function randInt() {
		srand(GUID());
		return rand(10000000, 99999999);
	}

	public function notify() {
		addWeixinLog(I('post.'), '[notify]post');
		addWeixinLog(I('get.'), '[notify]get');

		addWeixinLog(time(), 'notify_url');

		$config = C('WXPAY_CONFIG');

		//使用通用通知接口
		$notify = new \Common\Api\WxpayJsApi($config);

		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify -> saveData($xml);

		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		// if($notify->checkSign() == FALSE){
		//  $notify->setReturnParameter("return_code","FAIL");//返回状态码
		//  $notify->setReturnParameter("return_msg","签名失败");//返回信息
		// }else{
		//  $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		// }
		// $returnXml = $notify->returnXml();
		// echo $returnXml;

		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======

		//以log文件形式记录回调信息
		// $log_ = new Log_();
		// $log_name="./notify_url.log";//log文件路径
		// $log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

		if ($notify -> checkSign() == TRUE) {
			if ($notify -> data["return_code"] == "FAIL") {

				//此处应该更新一下订单状态，商户自行增删操作
				addWeixinLog("【通信出错】", "微信支付");

				// $log_->log_result($log_name,"【通信出错】:\n".$xml."\n");

			} elseif ($notify -> data["result_code"] == "FAIL") {

				//此处应该更新一下订单状态，商户自行增删操作
				//
				addWeixinLog("【业务出错】", "微信支付");

				// $log_->log_result($log_name,"【业务出错】:\n".$xml."\n");

			} else {

				//此处应该更新一下订单状态，商户自行增删操作
				addWeixinLog("【支付成功】", "微信支付");

				// $log_->log_result($log_name,"【支付成功】:\n".$xml."\n");

			}

			//商户自行增加处理流程
			//例如：更新订单状态
			//例如：数据库操作
			//例如：推送支付完成信息

		}

		echo "success";
	}

	public function jsapicall() {
		addWeixinLog(I('post.'), '[notify]post');
		addWeixinLog(I('get.'), '[notify]get');
	}

	/**
	 * @param config 配置
	 * @param trade_no 订单ID
	 * @param itemdesc 商品描述
	 * @param total_fee 总价格
	 */
	private function setWxpayConfig($config, $trade_no, $itemdesc, $total_fee, $prodcutid = 1) {
		try {
			//使用jsapi接口
			$jsApi = new \Common\Api\WxpayJsApi($config);

			//=========步骤1：网页授权获取用户openid============
			//通过code获得openid
			if (!isset($_GET['code'])) {
				//触发微信返回code码
				$url = $jsApi -> createOauthUrlForCode($config['jsapicallurl']);
				//				$url = $url.'?showwxpaytitle=1';
				Header("Location: $url");
			} else {
				//获取code码，以获取openid
				$code = $_GET['code'];
				$jsApi -> setCode($code);
				$result = $jsApi -> getOpenId();
			}
			$openid = "";
			if ($result['status']) {
				$openid = $result['info'];
			} else {
				$this -> error($result['info']);
			}
			//			dump($openid);
			//			dump($result);
			//			exit();
			//=========步骤2：使用统一支付接口，获取prepay_id============

			//使用统一支付接口
			$unifiedOrder = new \Common\Api\UnifiedOrderApi($config);

			//设置统一支付接口参数
			//设置必填参数
			//appid已填,商户无需重复填写
			//mch_id已填,商户无需重复填写
			//noncestr已填,商户无需重复填写
			//spbill_create_ip已填,商户无需重复填写
			//sign已填,商户无需重复填写
			$unifiedOrder -> setParameter("openid", "$openid");
			//商品描述
			$unifiedOrder -> setParameter("body", $itemdesc);
			//商品描述
			//自定义订单号，此处仅作举例
			//	$timeStamp = time();
			//			$unifiedOrder -> setParameter("product_id", "$prodcutid");
			//商品ID
			$unifiedOrder -> setParameter("out_trade_no", "$trade_no");
			//商户订单号
			$unifiedOrder -> setParameter("total_fee", "$total_fee");
			//总金额
			$unifiedOrder -> setParameter("notify_url", $config['notifyurl']);
			//通知地址
			$unifiedOrder -> setParameter("trade_type", "JSAPI");

			//          $unifiedOrder->setParameter("attach",'{"token":"'.'123'.'","orderid":"'.'456'.'"}');//附加数据
			//交易类型//商户订单号
			//			$unifiedOrder -> setParameter("fee_type", 1);
			//	$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
			//	$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
			//非必填参数，商户可根据实际情况选填
			//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
			//$unifiedOrder->setParameter("device_info","XXXX");//设备号
			//$unifiedOrder->setParameter("attach","XXXX");//附加数据
			//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
			//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
			//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
			//$unifiedOrder->setParameter("openid","XXXX");//用户标识

			$prepay_id = $unifiedOrder -> getPrepayId();
			//=========步骤3：使用jsapi调起支付============
			$jsApi -> setPrepayId($prepay_id);

			$jsApiParameters = $jsApi -> getParameters();

			if (!empty($jsApiParameters -> return_msg)) {
				$this -> error($jsApiParameters -> return_msg);
			}
			//			dump($unifiedOrder);
			//			dump($jsApiParameters);
			//			exit();
			//			$returnUrl = U('Home/WxpayTest/return',array(''));

			$this -> assign("jsapiparams", $jsApiParameters);
			$this -> assign("params", json_decode($jsApiParameters));
			//	        $this->assign('returnUrl', $returnUrl);
		} catch(SDKRuntimeException $sdkexcep) {
			$error = $sdkexcep -> errorMessage();
			$this -> assign("error", $error);
			$this -> error($error);
		}

	}

}
