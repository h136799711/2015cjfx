<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

//$EXPRESS = array('sf'=>"顺丰",'sto'=>"申通",'yt'=>"圆通",'yd'=>"韵达",'tt'=>"天天",'ems'=>"EMS",'zto'=>"中通",'ht'=>"汇通");
function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
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
