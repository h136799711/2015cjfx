<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

function addWeixinLog($data, $operator = '') {
		$log['ctime']    = time();
		$log['loginfo']  = is_array($data) ? serialize($data) : $data;
		$log['operator'] = $operator;
		M('WeixinLog')->add($log);
}



//===============================================================


