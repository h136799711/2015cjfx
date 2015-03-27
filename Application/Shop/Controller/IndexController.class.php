<?php
namespace Shop\Controller;

class IndexController extends ShopController {


	protected function _initialize() {
		parent::_initialize();
		
	}
	
	/**
	 * 子级会员查看
	 */
	public function subMembers(){
		$userinfo = $this->getUserInfo();
		//会员级别
		$level = I('get.level',1);
		//是否族长		
		$hasright = $this->hasAuthority($userinfo);
		
		$memberid = I('post.memberid',0,"intval");
		if($memberid > 0){
			$result = apiCall("Shop/Wxuser/getInfoWithGroupRight", array($userinfo['id'] ,$level ,$memberid));
			
			if($result['status']){
				if(is_array($result['info'][0])){
					$this->assign("member",$result['info'][0]);	
				}else{
					$this->assign("member",array('id'=>$memberid,'notfind'=>1));	
				}
			}else{
				$this->error($result['info']);
			}
		}else{
			
			$page = array('curpage'=>I('get.p',0),'size'=>10);
			
			$result = apiCall("Shop/Wxuser/queryWithGroupRight", array($userinfo['id'] ,$level ,$page));
			if($result['status']){
				$this->assign("list",$result['info']['list']);	
				$this->assign("show",$result['info']['show']);
			}
		}
		$this->assign("hasright",$hasright);		
		$this->assign("userinfo",$userinfo);		
		$this->display();
	}
	
	/**
	 * 申请提现
	 */
	public function withDrawCash(){
		$this->display();
	}
	
	/**
	 * 我的订单中心页面
	 */
	public function orders(){
		$this->display();
	}
	
	
	/**
	 * 我的家族中心
	 */
	public function myFamily() {		
		$userinfo = $this->getUserInfo();
		//是否族长		
		$hasright = $this->hasAuthority($userinfo);
		$paidOrdersCnt=0;
		$tobepaidOrdersCnt=0;
		
		//总订单
		//已支付
		$result = apiCall("Shop/Orders/countOrderBy", array($userinfo['id'],\Common\Model\OrdersModel::ORDER_PAID));
		
		if($result['status']){
			$paidOrdersCnt = intval($result['info']);
		}
		//待支付
		$result = apiCall("Shop/Orders/countOrderBy", array($userinfo['id'],\Common\Model\OrdersModel::ORDER_TOBE_PAID));
		if($result['status']){
			$tobepaidOrdersCnt = intval($result['info']);
		}
		
		//家族成员数获取
		$result = apiCall("Shop/WxuserFamily/countMember", array($userinfo['id']));
		$subMember = array(0,0,0,0);
		if($result['status'] && is_array($result['info'])){
			$subMember = $result['info'];
		}
		
		//总销售额、我的佣金
		
		//设置推荐人
		if($userinfo['referrer'] == 0){
			$this->assign("referrer_name",C("BRAND_NAME"));
		}else{
			$result = apiCall("Admin/Wxuser/getInfo",array(array('id'=>$userinfo['referrer'])));
			if($result['status']){
				$this->assign("referrer_name",$result['info']['nickname']);
			}
		}
		
		$this->assign("paidOrdersCnt",$paidOrdersCnt);
		$this->assign("tobepaidOrdersCnt",$tobepaidOrdersCnt);
		$this->assign("hasright",$hasright);
		$this->assign("subMember",$subMember);
		$this->assign("userinfo",$userinfo);
		$this -> display();
	}
	
	private function getUserInfo(){
		return $this->userinfo;
//		return array('avatar'=>'http://wx.qlogo.cn/mmopen/tyeAQdOFDdrrSiavyCmznWU2NNS5cZl92UzdPAlIR56nnO4nicZicKLDcsnRlB8W2FqMXibC8g7RHTbJQ38lvh90gnIvBRHT7cQt/0',
//		'id'=>'1','subscribe_time'=>"1400346362",'score'=>99,'groupid'=>0,'referrer'=>2);
	}
	
	
	public function myQrcode(){
		if(IS_GET){
			$userinfo = $this->getUserInfo();
			$hasright = $this->hasAuthority($userinfo);
			
			$realpath = realpath("./Uploads/QrcodeMerge/");
			if(!	$hasright){
				$qrcode = __ROOT__."/Uploads/QrcodeMerge/qrcode.jpg";
			}else{
				$qrcode = "./Uploads/Qrcode/qrcode_uid".$userinfo['id'].".jpg";
				if(!file_exists($qrcode)){
					
					$promotionapi = new \Common\Api\PromotioncodeApi(C('PROMOTIONCODE'));
					$result = $promotionapi->process($this -> wxaccount['appid'], $this -> wxaccount['appsecret'],$userinfo);
					if($result['status']){
						
					}else{
						$this->error("推广二维码生成失败！");
					}

				}
				$qrcode = __ROOT__."/Uploads/Qrcode/qrcode_uid".$userinfo['id'].".jpg";
			}
			$this->assign("qrcode",$qrcode);
			$this->display();
		}
	}
	
	private function hasAuthority($userinfo){
		if(empty($userinfo) || !isset($userinfo['groupid'])){return false;}
		$groupid = $userinfo['groupid'];
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
	
	
	
	
	
	
	/**
	 * 购买页面
	 * @param $pid 需要传入pid，要购买的产品id
	 */
	public function buy() {
		$userinfo = session("userinfo");
		if(!is_array($userinfo)){
//			$this->error("请登录！");
		}
		$productid = I('get.pid', 0);
		if ($productid == 0) {
			$this -> error("参数错误！");
		}

		$result = apiCall("Tool/Province/queryNoPaging", array());
		if ($result['status']) {
			$this -> assign("provinces", $result['info']);
		}

		$product = apiCall("Admin/Product/getInfoWithThumbnail", array('id' => $productid));
		$map = array("wxuserid"=>$userinfo['id']);
		
		$address = apiCall("Shop/Address/getInfo",array($map));
		
		if($address['status']){
			$this->assign("address",$address['info']);
			$city = apiCall("Tool/City/getListByProvinceID", array($address['info']['province']));
			$area = apiCall("Tool/Area/getListByCityID", array($address['info']['city']));
			if($city['status']){
				$city = $city['info'];
				$this->assign("city",$city);
			}
			if($area['status']){
				$area = $area['info'];
				$this->assign("area",$area);
			}
		}
		
		if ($product['status']) {
			$product['info']['tburl'] = getPictureURL($product['info']['thumbnaillocal'], $product['info']['thumbnailremote']);
			$this -> assign("product", $product['info']);
			$this -> display();
		}
	}

	
	public function index() {
		if (IS_GET) {
			
			
			if (is_array($this -> userinfo)) {
				$groupaccess = apiCall("Admin/GroupAccess/getInfo", array('groupid' => $this -> userinfo['groupid']));
				if ($groupaccess['status']) {
					$this -> assign("groupaccess", $groupaccess['info']);
					$this -> assign("alloweddistribution", $groupaccess['info']['alloweddistribution']);
					$this -> assign("userinfo", $this->userinfo);
					$this -> display();
				} else {
					$this -> error("权限获取失败，请重试！");
				}
			}else{
				$this -> error("用户信息获取失败！");
			}
		} else {
			$this -> error("无法访问！");
		}
	}

	
	
	

}
