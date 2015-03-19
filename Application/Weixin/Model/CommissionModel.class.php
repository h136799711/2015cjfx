<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Weixin\Model;
use Think\Model;

class CommissionModel  extends  Model{
	
	protected $_auto = array(
		array('updatetime',NOW_TIME,self::MODEL_BOTH)
	);
		
}
