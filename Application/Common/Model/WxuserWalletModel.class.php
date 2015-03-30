<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Model;
use Think\Model;

/**
 * 用户钱包
 */
class WxuserWalletModel  extends  Model{
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
		array('createtime',NOW_TIME,self::MODEL_INSERT),
	);
	
	protected $_validate = array(
		array("wxuser_id","require","wxuser_id必须",self::MUST_VALIDATE),
	);
		
}
