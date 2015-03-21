<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Admin\Controller;

class WxuserController extends AdminController {
	private $wxapi;
	protected function _initialize() {
		parent::_initialize();
	}

	public function index() {
		//get.startdatetime
		
		$startdatetime = I('startdatetime', date('Y-m-d', time() - 24 * 3600), 'urldecode');
		$enddatetime = I('enddatetime', date('Y-m-d', time()), 'urldecode');

		//分页时带参数get参数
		$params = array('startdatetime' => $startdatetime, 'enddatetime' => $enddatetime);

		$startdatetime = strtotime($startdatetime);
		$enddatetime = strtotime($enddatetime);

		if ($startdatetime === FALSE || $enddatetime === FALSE) {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error(L('ERR_DATE_INVALID'));
		}

		$map = array();

		$map['subscribe_time'] = array( array('EGT', $startdatetime), array('elt', $enddatetime), 'and');

		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " subscribe_time desc ";
		//
		$result = apiCall('Admin/Wxuser/query', array($map, $page, $order, $params));

		//
		if ($result['status']) {
			$this -> assign('startdatetime', $startdatetime);
			$this -> assign('enddatetime', $enddatetime);
			$this -> assign('show', $result['info']['show']);
			$this -> assign('list', $result['info']['list']);
			$this -> display();
		} else {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}

	
	/**
	 *
	 */
	public function select() {

		$map['nickname'] = array('like', "%" . I('q', '', 'trim') . "%");
		$map['id'] = I('q', -1);
		$map['_logic'] = 'OR';
		$page = array('curpage' => 0, 'size' => 20);
		$order = " subscribe_time desc ";

		$result = apiCall("Admin/Wxuser/query", array($map, $page, $order, false, 'id,nickname,avatar'));

		if ($result['status']) {
			$list = $result['info']['list'];
			$this -> success($list);
		} else {
			LogRecord($result['info'], __LINE__);
		}

	}

	/**
	 * 将用户添加到会员组
	 * TODO: 后期考虑会员组-会员之间的关系做一张表，建立多对多关系	 *
	 */
	public function addToGroup() {
		if (IS_POST) {
			$groupid = I('post.groupid', 0);
			$id = I('post.uid', 0);
			$result = apiCall("Admin/Wxuser/saveByID", array($id, array('groupid' => $groupid)));

			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/WxuserGroup/subMember', array('groupid' => $groupid)));
			} else {
				LogRecord($result['info'], __LINE__);
			}
		}
	}

	/**
	 * 将用户添加到会员组
	 * TODO: 后期考虑会员组-会员之间的关系做一张表，建立多对多关系
	 */
	public function delFromGroup() {
		if (IS_GET) {
			$groupid = I('get.groupid', 0);
			$id = I('get.uid', 0);

			$result = apiCall("Admin/Wxuser/saveByID", array($id, array('groupid' => 0)));

			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/WxuserGroup/subMember', array('groupid' => $groupid)));
			} else {
				LogRecord($result['info'], __LINE__);
			}
		}
	}

	public function viewFamily() {
		//get.startdatetime
		$startdatetime = I('startdatetime', date('Y/m/d', time() - 24 * 3600), 'urldecode');
		$enddatetime = I('enddatetime', date('Y/m/d', time()), 'urldecode');

		//分页时带参数get参数
		$params = array('startdatetime' => $startdatetime, 'enddatetime' => $enddatetime);

		$startdatetime = strtotime($startdatetime);
		$enddatetime = strtotime($enddatetime);

		if ($startdatetime === FALSE || $enddatetime === FALSE) {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error(L('ERR_DATE_INVALID'));
		}

		$map = array();

		$map['subscribe_time'] = array( array('EGT', $startdatetime), array('elt', $enddatetime), 'and');

		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " subscribe_time desc ";

		//
		$result = apiCall('Admin/Wxuser/querySubMember', array($map, $page, $order, $params));

		//
		if ($result['status']) {
			$this -> assign('startdatetime', $startdatetime);
			$this -> assign('enddatetime', $enddatetime);
			$this -> assign('show', $result['info']['show']);
			$this -> assign('list', $result['info']['list']);
			$this -> display();
		} else {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}
	
	/**
	 * 同步
	 */
	public function syncUser(){
		
		
		$uid = session("uid");
		
		$wxaccount = getWxAccountID();// "eotprkjn1426473619";		
		$result = apiCall('Weixin/Wxaccount/getInfo', array( array('id' => $wxaccount)));
		
		if($result['status'] === false){
			$this->error($result['info']);	
		}
		if(is_array($result['info'])){
			$appid = $result['info']['appid'];
			$appsecret = $result['info']['appsecret'];
			$this->wxapi = new \Common\Api\WeixinApi($appid,$appsecret);
			$nextOpenID = I('get.next_openid','');
			$userlist = $this->wxapi->getUserList($nextOpenID);
//			dump($users);
			$count = $userlist['count'];
			$openids = $userlist['data']['openid'];
			$nextOpenID = $userlist['next_openid'];
			
			$wxaccountid = getWxAccountID();
			$success = 0;
			for($i = 0 ; $i < $count;$i++){
//				dump($openids[$i]);
//				$result = $this->wxapi->getBaseUserInfo($openids[$i]);
//				if($result['status']){
				$result = $this->addOrUpdateWxuser($openids[$i] , $wxaccountid);
				if($result){
					$success++;
				}
//				}
			}
			if (strlen($nextOpenID)){
				$this->success('本次更新'.$success.'条,正在获取下一批粉丝数据',U('Admin/Wxuser/syncUser',array('token'=>$token,'next_openid'=>$nextOpenID)));

			}else {
				$this->success('更新完成,现在获取粉丝详细信息',U('Admin/Wxuser/index',array('token'=>$this->token)));

			}
//			dump($count - $success);
//			dump($result);
		}
	}

	

	/**
	 * openid
	 * wxaccountid
	 * TODO: 只更新openid，wxaccount_id ,其它资料之后分段更新，参见微通汇
	 * @return false|true  添加或更新失败 | 添加或更新成功
	 */
	private function addOrUpdateWxuser($openid, $wxaccountid) {
		if(empty($openid) || empty($wxaccountid)){
			return false;
		}
		
		$userinfo = $this -> wxapi -> getBaseUserInfo($openid);
		
		if (!$userinfo['status']) {
			LogRecord($userinfo['info'], __FILE__ . __LINE__);
			return false;
		}
		$userinfo = $userinfo['info'];
		
		$map = array('openid' => $openid, 'wxaccount_id' => $wxaccountid);

		$result = apiCall('Weixin/Wxuser/getInfo', array($map));
		$wxuserEntity = "";
		if($result['status']){
			$wxuserEntity = $result['info'];
		}
		
		//当前粉丝的信息是否已经存在记录
		$wxuser = array();
		$wxuser['wxaccount_id'] = $wxaccountid;
		$wxuser['openid'] = $openid;
		
		$wxuser['subscribed'] = 1;

		if (is_array($userinfo)) {
			$wxuser['nickname'] = $userinfo['nickname'];
			$wxuser['province'] = $userinfo['province'];
			$wxuser['country'] = $userinfo['country'];
			$wxuser['city'] = $userinfo['city'];
			$wxuser['sex'] = $userinfo['sex'];
			$wxuser['avatar'] = $userinfo['headimgurl'];
			$wxuser['subscribe_time'] = $userinfo['subscribe_time'];
//			$wxuser['openid'] = "123456userinfo";
		}
		
		$model = M();
		$model -> startTrans();
		$error = "";
		$flag = true;
						
		$result = apiCall("Weixin/Commission/createOneIfNone", array($wxaccountid, $openid));
		
		if ($result['status'] === false) {
			$error = $result['info'];
			$flag = false;
		}
//		dump("123456");
//		dump($result);
		$result = apiCall("Weixin/WxuserFamily/createOneIfNone", array($wxaccountid, $openid));
		
		if ($flag && $result['status'] === false) {
			$error = $result['info'];
			$flag = false;
		}
		
//		dump("44444");
//		dump($result);
		//判断是否已记录
		if ($wxuserEntity != "") {
			//更新
			$result = apiCall('Weixin/Wxuser/save', array($map, $wxuser));
		} else {
			//新增
			$wxuser['referrer'] = 0;
			$result = apiCall('Weixin/Wxuser/add', array($wxuser));
		}
		
	
		
		if ($flag && $result['status'] === false) {
			$error = $result['info'];
			$flag = false;
		}
		
		if($flag){
			
			$model->commit();
		}else{
			$model->rollback();
		}
		
		return array('status'=>$flag,'info'=>$error);

	}

}
