<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------


namespace Admin\Api;

use Common\Model\WxuserModel;
use Common\Model\WxuserFamilyModel;
use Common\Model\CommissionModel;

class WxuserApi extends \Common\Api\Api{
		
	protected function _init(){
		$this->model = new WxuserModel();
	}
	
	/**
	 * 升级用户组
	 */
	public function groupUp($wxuserid){
		
		$result = $this->model->where(array('id'=>$wxuserid))->find();
		
		if($result === FALSE){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
			
		}else{
			$groupid = $result['groupid'];
//			dump($groupid);
			// 获取用户组信息
			$group = apiCall("Admin/WxuserGroup/getInfo",array(array('id'=>$groupid)));
//			dump($group);
			if($group['status']){
				if(is_null($group['info'])){
					$error = false;
					return $this -> apiReturnSuc($error);
				}
				$nextgroupid = $group['info']['nextgroupid'];
				$result = $this->model->where(array('id'=>$wxuserid))->save(array('groupid'=>$nextgroupid));
				if($result === false){
					$error = $this->model->getDbError();
					return $this -> apiReturnErr($error);
				}else{
					return $this -> apiReturnSuc($result);
				}
				
			}else{
				$error = $group['info'];
				return $this -> apiReturnErr($error);
			}
		}
	
	}
	
	/**
	 * 降级用户组
	 */
	public function groupDown($wxuserid){
		
		$result = $this->model->where(array('id'=>$wxuserid))->find();
		
		if($result === FALSE){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
		}else{
			$groupid = $result['groupid'];
			// 获取用户组信息
			$group = apiCall("Admin/WxuserGroup/getInfo",array(array('nextgroupid'=>$groupid)));
			if($group['status']){
				if(is_null($group['info'])){
					$error = false;
					return $this -> apiReturnSuc($error);
				}
				$prevgroupid = $group['info']['id'];
				$result = $this->model->where(array('id'=>$wxuserid))->save(array('groupid'=>$prevgroupid));
				if($result === false){
					$error = $this->model->getDbError();
					return $this -> apiReturnErr($error);
				}else{
					return $this -> apiReturnSuc($result);
				}
				
			}else{
				$error = $group['info'];
				return $this -> apiReturnErr($error);
			}
		}
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
	
	/**
	 * 查询子级会员
	 */
	public function querySubMember(){
		$map = array();
		$result = $this->model->alias(" wu ")->field("wu.avatar,wu.subscribe_time, wu.nickname,wu.referrer,wu.id as wxuserid,wu.openid,wu.wxaccount_id,wf.parent_1,wf.parent_2,wf.parent_3,wf.parent_4,wf.parent_5")->join("LEFT JOIN __WXUSER_FAMILY__ as wf on wu.openid = wf.openid and wu.wxaccount_id = wf.wxaccount_id")->where($map)->find();
		
		if($result === false){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
		}else{
			return $this->apiReturnSuc($result);
		}
		
	}
	

	
}
