<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Weixin\Controller;
use Think\Controller;
class TestController extends Controller {
	
    public function index(){
		$this->display();
    }
	
	/**
	 * 
	 */
	public function testCommit(){
		
//		$this -> wxapi = new \Common\Api\WeixinApi("wx58aea38c0796394d" , "3e1404c970566df55d7314ecfe9ff437");
		$model = M();
		$model->startTrans();
		$entity = array(
				'wxaccount_id'=>0,
				'openid'=>'12312321',
				'sex'=>0,'subscribe_time'=>time(),'referrer'=>0,'nickname'=>'12321','avatar'=>'');
		
		$result = apiCall('Admin/Wxuser/add',$entity);
		$flag = true;
		if($result['status'] === false){
			$flag = false;
		}
		
		$result_2 = apiCall("Weixin/Commission/createOneIfNone",array(0,'12312321'));
		
		if($result_2['status'] === false){
			$flag = false;
		}
		
		if($flag){
			$model->commit();
		}else{
			dump($result['info']);
			dump($result_2['info']);
			$model->rollback();
		}
	}
	
	public function testUserList(){
		
		$this -> wxapi = new \Common\Api\WeixinApi("wx58aea38c0796394d" , "3e1404c970566df55d7314ecfe9ff437");
		$res = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN";
		$users = $this->wxapi->getUserList();
		dump($users);
		$count = $users['count'];
		$openids = $users['data']['openid'];
		for($i=0;$i<$count;$i++){
			dump($openids[$i]);
			$result = $this->wxapi->getBaseUserInfo($openids[$i]);
			if($result['status']){
				
			}
		}
	}
	
	public function test2(){
		dump(apiCall("Weixin/Wxuser/getInfoWithFamily", array(2)));
	}
	
    public function sendText(){
    	
		$this -> wxapi = new \Common\Api\WeixinApi("wx58aea38c0796394d" , "3e1404c970566df55d7314ecfe9ff437");
		
		dump($this->wxapi->sendTextToFans('oqMIVt3Ouq-2Vm0kZOZmZ2rTDlP8','hello 我是主动发送给你的！'));
    }
	
	
	public function testWeixin(){
		
		$this -> wxapi = new \Common\Api\WeixinApi("wx58aea38c0796394d" , "3e1404c970566df55d7314ecfe9ff437");
		
		$filepath = realpath("./Uploads/Qrcode/1.jpg");
		
		dump($this->wxapi->uploadMaterial($filepath));
		
	}
   	
	function download_remote_file($file_url, $save_to){
		$content = file_get_contents($file_url);
		file_put_contents($save_to, $content);
	}
	
	public function downloadRemote(){
		
		dump(file_get_contents("http://www.54ux.com/wp-content/themes/d-simple/img/thumbnail.jpg"));
		$this->download_remote_file("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==",realpath("./Uploads/Qrcode").'/1.jpg');
		echo "<img src=\"".__ROOT__.'/Uploads/Qrcode/1.jpg"/>';
	}
	
	public function test(){
		$keyword = "test";
		$map = array('keyword'=>$keyword);
		$result = apiCall("Weixin/WxreplyText/getInfo",array($map));
		
		dump($result);
	}
	
//	public function create(){
//		$result = apiCall("Weixin/WxuserFamily/createOneIfNone",array(1,'3332222'));
//		
//		dump($result);
//	}
	
	
	public function create(){
		$result = apiCall("Weixin/Commission/createOneIfNone",array(1,'3332222'));
		
		dump($result);
	}
}