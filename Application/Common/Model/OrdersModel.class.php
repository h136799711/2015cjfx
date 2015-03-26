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
	/**
	 * 待确认
	 */
	const ORDER_TOBE_CONFIRMED = 2;
	/**
	 * 待发货
	 */
	const ORDER_TOBE_SHIPPED = 3;
	/**
	 * 已发货
	 */
	const ORDER_SHIPPED = 4;
	/**
	 * 已完成
	 */
	const ORDER_COMPLETED = 5;
	/**
	 * 已退货
	 */
	const ORDER_RETURNED = 6;
	
	//订单支付状态
	/**
	 * 待支付
	 */
	const ORDER_TOBE_PAID = 0;
	/**
	 * 已支付
	 */
	const ORDER_PAID = 1;
	/**
	 * 已退款
	 */
	const ORDER_REFUND = 2;
	
	protected $_auto = array(
		array('status',1,self::MODEL_INSERT),
		array('pay_status',self::ORDER_TOBE_PAID,self::MODEL_INSERT),
		array('order_status',self::ORDER_TOBE_CONFIRMED,self::MODEL_INSERT),
		array('createtime',NOW_TIME,self::MODEL_INSERT),
		array('updatetime',"time",self::MODEL_BOTH,"function"),
	);
}
