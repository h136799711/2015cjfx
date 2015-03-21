<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Admin\Controller;

class WxuserController extends AdminController {
	protected function _initialize() {
		parent::_initialize();
	}

	public function index() {
		//get.startdatetime
		$startdatetime = I('startdatetime', date('Y/m/d H:i', time() - 24 * 3600), 'urldecode');
		$enddatetime = I('enddatetime', date('Y/m/d H:i', time()), 'urldecode');

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
		if(IS_POST){
			$groupid = I('post.groupid', 0);
			$id = I('post.uid', 0);
			$result = apiCall("Admin/Wxuser/saveByID", array($id, array('groupid' => $groupid)));
	
			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/WxuserGroup/subMember',array('groupid'=>$groupid)));
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
		if(IS_GET){
			$groupid = I('get.groupid', 0);
			$id = I('get.uid', 0);
			
			$result = apiCall("Admin/Wxuser/saveByID", array($id, array('groupid' => 0)));
			
			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/WxuserGroup/subMember',array('groupid'=>$groupid)));
			} else {
				LogRecord($result['info'], __LINE__);
			}
		}
	}
	
	
	

}
