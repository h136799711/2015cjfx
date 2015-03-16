<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

function addWeixinLog($data, $operator = '') {
		$log['ctime']    = time();
		$log['loginfo']  = is_array($data) ? serialize($data) : $data;
		$log['operator'] = $operator;
		M('WeixinLog')->add($log);
}
//=================微信接口方法
/**
 * 获取accesstoken,缓存7000秒，缓存key="WEIXIN_"$appid$secret
 * @param $appid 公众号appid
 * @param $scret 公众号appsecret 
 * @return accesstoken 
 * */
function getAccessToken($appid, $secret) {
	$access_token = S("WEIXIN_".$appid.$secret);
	if($access_token === false){
		$url_get = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
		$json    = json_decode(curlGet($url_get));
		$access_token = $json->access_token;
		//缓存7000秒，公众平台是7200秒
		S("WEIXIN_".$appid.$secret,$access_token,7000);
	}
	
	return $access_token;
}

/**
 * 获取永久二维码
 * @param $accessToken token
 * @param $scene_str 编码到二维码的字符串 , 字符串类型，长度限制为1到64，仅永久二维码支持此字段
 * @return $obj
 */
function getQrcode($accessToken,$scene_str){
	$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$accessToken;
//	{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}}
	$data = array("action_name"=>"QR_LIMIT_STR_SCENE","action_info"=>array('scene'=>array('scene_str'=>$scene_str)));
	$obj = curlPost($url,json_encode($data));
	
//	dump($obj);
	//ticket	获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
	//expire_seconds	二维码的有效时间，以秒为单位。最大不超过1800。
	//url  二维码编码的字符串，可以根据此字符串来生成qrcode。
	return $obj;
}



//===============================================================





function curlPost($url, $data) {
	$ch     = curl_init();
	$header = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$tmpInfo = curl_exec($ch);
	$errorno = curl_errno($ch);
	if ($errorno) {
		return array('status' => false, 'msg' => $errorno);
	} else {
		$js = json_decode($tmpInfo);
		return array('status' => true, 'msg' => $js);
	}
}

function curlGet($url) {
	$ch     = curl_init();
	$header = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$temp = curl_exec($ch);
	return $temp;
}