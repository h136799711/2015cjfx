<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------


namespace Weixin\Api;

use Weixin\Model\WxuserModel;

class WxuserApi extends \Common\Api\Api{
		
	protected function _init(){
		$this->model = new WxuserModel();
	}
	
	/**
	 * 获取家族关系
	 */
	public function getFamily($id){
		//TODO:
//		$this->model->alias("")->where(array('id'=>$id))->find();
	}
}
