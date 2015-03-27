<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

//$EXPRESS = array('sf'=>"顺丰",'sto'=>"申通",'yt'=>"圆通",'yd'=>"韵达",'tt'=>"天天",'ems'=>"EMS",'zto'=>"中通",'ht'=>"汇通");

/**
 * 快递公司数据
 */
function getAllExpress(){
	$EXPRESS = C('express');
	return array(
		array('code'=>'sf','name'=>$EXPRESS['sf']),
		array('code'=>'sto','name'=>$EXPRESS['sto']),
		array('code'=>'yt','name'=>$EXPRESS['yt']),
		array('code'=>'yd','name'=>$EXPRESS['yd']),
		array('code'=>'tt','name'=>$EXPRESS['tt']),
		array('code'=>'ems','name'=>$EXPRESS['ems']),
		array('code'=>'zto','name'=>$EXPRESS['zto']),
		array('code'=>'ht','name'=>$EXPRESS['ht']),
	);
}

function toYesOrNo($val){
	if($val == 1 || $val == true){
		return "是";
	}
	return "否";
}

/**
 * 获取订单状态的文字描述
 */
function getOrderStatus($status){
	
	switch($status){
		case \Common\Model\OrdersModel::ORDER_COMPLETED:
			return "已完成";
		case \Common\Model\OrdersModel::ORDER_RETURNED:
			return "已退货";
		case \Common\Model\OrdersModel::ORDER_SHIPPED:
			return "已发货";
		case \Common\Model\OrdersModel::ORDER_TOBE_CONFIRMED:
			return "待确认";
		case \Common\Model\OrdersModel::ORDER_TOBE_SHIPPED:
			return "待发货";
		default:
			return "未知";
	}
}

/**
 * 获取支付状态的文字描述
 */
function getPayStatus($status){
	switch($status){
		case \Common\Model\OrdersModel::ORDER_PAID:
			return "已支付";
		case \Common\Model\OrdersModel::ORDER_TOBE_PAID:
			return "待支付";
		case \Common\Model\OrdersModel::ORDER_REFUND:
			return "已退款";
		default:
			return "未知";
	}
}
