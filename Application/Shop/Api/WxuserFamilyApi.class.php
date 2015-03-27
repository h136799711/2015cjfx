<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Api;
use Common\Model\WxuserFamilyModel;

class WxuserFamilyApi extends  \Common\Api\Api{
	
	protected function _init(){
		$this->model = new WxuserFamilyModel();
	}
	
	/**
	 * 统计子会员数量
	 * 
	 * @return 
	 */
	public function countMember($wxuserid){
		
		$parent_1 = $this->model->where(array("parent_1"=>$wxuserid))->count();	
		if($parent_1 === false){
			return parent::apiReturnErr($parent_1);
		}
		$parent_2 = $this->model->where(array("parent_2"=>$wxuserid))->count();	
		if($parent_2 === false){
			return parent::apiReturnErr($parent_2);
		}
		$parent_3 = $this->model->where(array("parent_3"=>$wxuserid))->count();	
		if($parent_3 === false){
			return parent::apiReturnErr($parent_3);
		}
		$parent_4 = $this->model->where(array("parent_4"=>$wxuserid))->count();	
		if($parent_4 === false){
			return parent::apiReturnErr($parent_4);
		}
		
		return  parent::apiReturnSuc(array($parent_1,$parent_2,$parent_3,$parent_4));
	}
	
}
