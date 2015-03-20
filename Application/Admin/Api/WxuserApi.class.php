<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------


namespace Admin\Api;

use Common\Model\WxuserModel;

class WxuserApi extends \Common\Api\Api{
		
	protected function _init(){
		$this->model = new WxuserModel();
	}
	
	/**
	 * 获取家族关系
	 * @param $id 会员id
	 */
	public function getInfoWithFamily($id){
		$result = $this->model->alias(" wu ")->field("wu.nickname,wu.referrer,wu.id as wxuserid,wu.openid,wu.wxaccount_id,wf.parent_1,wf.parent_2,wf.parent_3,wf.parent_4,wf.parent_5")->join("LEFT JOIN __WXUSER_FAMILY__ as wf on wu.openid = wf.openid and wu.wxaccount_id = wf.wxaccount_id")->where(array('wu.id'=>$id))->find();
	
		if($result === false){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
		}else{
			return $this->apiReturnSuc($result);
		}
	}
	
	 
	 /**
	 * 统计某一会员组的会员数目
	 * @param $groupid 会员组id
	 */
	public function countWxusers($groupid){
		$userCount = $this->model->where(array("groupid"=>$id))->count();
		if($userCount === false){			
			return $this -> apiReturnErr($this->model->getDbError());
		}else{
			return $this->apiReturnSuc($userCount);			
		}
	}
	 
	
	
}
