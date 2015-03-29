<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Model;
use Think\Model;

class CommissionWithdrawcashModel  extends  Model{
	/**
	 * 待审核
	 */
	const WDC_STATUS_PENDING_AUDIT = 0;
	/**
	 * 通过审核
	 */
	const WDC_STATUS_APPROVAL = 1;
	/**
	 * 拒绝、驳回
	 */
	const WDC_STATUS_REJECT = 2;
	
	protected $_auto = array(
		array('status',1,self::MODEL_INSERT),
		array('wdc_status',0,self::MODEL_INSERT),
		array('createtime',NOW_TIME,self::MODEL_INSERT),
		array('updatetime',NOW_TIME,self::MODEL_BOTH)
	);
		
}
