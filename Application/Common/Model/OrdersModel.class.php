<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Model;

use Think\Model;

 
class OrdersModel extends Model{
	//订单状态
	const ORDER_TOBE_CONFIRMED = 2;
	const ORDER_TOBE_SHIPPED = 3;
	const ORDER_SHIPPED = 4;
	const ORDER_COMPLETED = 5;
	const ORDER_RETURNED = 6;
	
	//订单支付状态
	const ORDER_TOBE_PAID = 0;
	const ORDER_PAID = 1;
	const ORDER_REFUND = 2;
	
	protected $_auto = array(
		array('status',1,self::MODEL_INSERT),
		array('createtime',NOW_TIME,self::MODEL_INSERT),
		//待确认2，未发货3，已发货4，已完成5，已退货6
		array('order_status',2,self::MODEL_INSERT),
		array('updatetime',"time",self::MODEL_BOTH,"function"),
	);
}
