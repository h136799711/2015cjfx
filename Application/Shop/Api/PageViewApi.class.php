<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Api;
use Common\Api\Api;

class PageViewApi extends  Api{
	
	protected function _init(){
		$this->model = new \Admin\Model\ConfigModel();
		
	}	
	/**
	 * @override 
	 */
	public function inc(){
		
		$result = $this -> model -> where(array('name'=>'SHOP_PAGEVIEW')) -> setInc("value", 1);
		if ($result === false) {
			$error = $this -> model -> getDbError();
			return $this -> apiReturnErr($error);
		} else {
			return $this -> apiReturnSuc($result);
		}
	}
	
}
