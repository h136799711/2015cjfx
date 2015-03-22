<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Model;

class ProductModel extends Model{
	
	protected $_validate = array(
		array('wxuserid','require','所属用户ID必须'),
	);
	
	protected $_auto = array(
		array('createtime',NOW_TIME,self::MODEL_INSERT),
		array('updatetime',"time",self::MODEL_BOTH,'function'),
	);
}
