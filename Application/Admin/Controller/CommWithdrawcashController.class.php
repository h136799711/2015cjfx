<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Admin\Controller;

class CommWithdrawcashController extends AdminController {

	public function index() {
		$arr = getDataRange(3);
		$payStatus = I('paystatus', '');
		$orderid = I('post.orderid', '');
		$userid = I('uid', 0);
		$startdatetime = urldecode($arr[0]);
		$enddatetime = urldecode($arr[1]);

		//分页时带参数get参数
		$params = array('startdatetime' => $startdatetime, 'enddatetime' => ($enddatetime));

		$startdatetime = strtotime($startdatetime);
		$enddatetime = strtotime($enddatetime);

		if ($startdatetime === FALSE || $enddatetime === FALSE) {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error(L('ERR_DATE_INVALID'));
		}

		$map = array();
		$map['wxaccountid'] = getWxAccountID();
		if (!empty($orderid)) {
			$map['orderid'] = array('like', $orderid . '%');
			$params['orderid'] = $orderid;
		}

		$map['wdc_status'] = \Common\Model\CommissionWithdrawcashModel::WDC_STATUS_PENDING_AUDIT;
		$map['createtime'] = array( array('EGT', $startdatetime), array('elt', $enddatetime), 'and');

		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " createtime desc ";

		if ($userid > 0) {
			$map['wxuser_id'] = $userid;
		}

		$result = apiCall('Admin/CommissionWithdrawcash/query', array($map, $page, $order, $params));

		//
		if ($result['status']) {
			$this -> assign('orderid', $orderid);
			//				$this -> assign('orderStatus', $orderStatus);
			$this -> assign('payStatus', $payStatus);
			//				$this -> assign('startdatetime', $startdatetime);
			//				$this -> assign('enddatetime', $enddatetime);
			$this -> assign('show', $result['info']['show']);
			$this -> assign('list', $result['info']['list']);
			$this -> display();
		} else {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}

	public function query() {
		$arr = getDataRange(3);
		$wdcstatus = I('wdcstatus', '');
		$orderid = I('orderid', '');
		$userid = I('uid', 0);
		$startdatetime = urldecode($arr[0]);
		$enddatetime = urldecode($arr[1]);

		//分页时带参数get参数
		$params = array('startdatetime' => $startdatetime, 'enddatetime' => ($enddatetime));

		$startdatetime = strtotime($startdatetime);
		$enddatetime = strtotime($enddatetime);

		if ($startdatetime === FALSE || $enddatetime === FALSE) {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error(L('ERR_DATE_INVALID'));
		}

		$map = array();
		$map['wxaccount_id'] = getWxAccountID();
		if (!empty($orderid)) {
			$map['orderid'] = array('like', $orderid . '%');
			$params['orderid'] = $orderid;
		}
		if(!empty($wdcstatus)){
			$map['wdcstatus'] = $wdcstatus;
			$params['wdcstatus'] = $wdcstatus;
		}
		//			$map['wdc_status'] = \Common\Model\CommissionWithdrawcashModel::WDC_STATUS_PENDING_AUDIT;
		$map['createtime'] = array( array('EGT', $startdatetime), array('elt', $enddatetime), 'and');

		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " createtime desc ";

		if ($userid > 0) {
			$map['wxuser_id'] = $userid;
		}

		$result = apiCall('Admin/CommissionWithdrawcash/query', array($map, $page, $order, $params));

		//
		if ($result['status']) {
			$this -> assign('orderid', $orderid);
			$this -> assign('wdcstatus', $wdcstatus);
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
	 * 驳回 提现申请
	 * @author hebiduhebi@126.com
	 */
	public function bulkReject() {
		if (IS_POST) {

			$ids = I('post.ids', -1);
			if ($ids === -1) {
				$this -> error(L('ERR_PARAMETERS'));
			}
			$ids = implode(',', $ids);
			$map = array('id' => array('in', $ids));
			$entity = array('wdc_status' => \Common\Model\CommissionWithdrawcashModel::WDC_STATUS_REJECT);
			$result = apiCall("Admin/CommissionWithdrawcash/save", array($map, $entity));
			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/Orders/sure'));
			} else {
				$this -> error($result['info']);
			}
		}
	}

	/**
	 * 驳回
	 */
	public function reject() {
		if (IS_GET) {

			$id = I('get.id', -1);
			if ($id === -1) {
				$this -> error(L('ERR_PARAMETERS'));
			}

			$entity = array('wdc_status' => \Common\Model\CommissionWithdrawcashModel::WDC_STATUS_REJECT);
			$result = apiCall("Admin/CommissionWithdrawcash/saveByID", array($id, $entity));
			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/Orders/sure'));
			} else {
				$this -> error($result['info']);
			}

		}
	}

	/**
	 * 通过
	 */
	public function pass() {
		if (IS_GET) {

			$id = I('get.id', -1);
			if ($id === -1) {
				$this -> error(L('ERR_PARAMETERS'));
			}

			$entity = array('wdc_status' => \Common\Model\CommissionWithdrawcashModel::WDC_STATUS_APPROVAL);
			$result = apiCall("Admin/CommissionWithdrawcash/saveByID", array($id, $entity));
			if ($result['status']) {
				$this -> success(L('RESULT_SUCCESS'), U('Admin/Orders/sure'));
			} else {
				$this -> error($result['info']);
			}

		}
	}

}
