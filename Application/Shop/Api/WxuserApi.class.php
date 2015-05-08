<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Api;

use Common\Model\WxuserModel;
use Common\Model\WxuserFamilyModel;

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
			addWeixinLog("用户组升级","groupUp");
			addWeixinLog($groupid,"groupUp");
			addWeixinLog($result,"groupUp");
			// 获取用户组信息
			$group = apiCall("Shop/WxuserGroup/getInfo",array(array('id'=>$groupid)));
//			dump($group);

			addWeixinLog($group,"groupUp");
			if($group['status']){
				if(is_null($group['info'])){
					$error = false;
					return $this -> apiReturnErr($error);
				}
				$nextgroupid = $group['info']['nextgroupid'];
				if($nextgroupid >= 0){
					
					$result = $this->model->where(array('id'=>$wxuserid))->save(array('groupid'=>$nextgroupid));
					addWeixinLog(serialize($result).$wxuserid.'-'.$nextgroupid,"groupUp save");
					if($result === false){
						$error = $this->model->getDbError();
						return $this -> apiReturnErr($error);
					}else{
						return $this -> apiReturnSuc($result);
					}
				}else{
						return $this -> apiReturnSuc($nextgroupid);
				}
			}else{
				$error = $group['info'];
				return $this -> apiReturnErr($error);
			}
		}
	
	}
	
	
	/**
	 * 查询子级会员
	 * @param $wxuserid 用户id
	 * @param $level 子级
	 * @param $page	分curpage,size
	 */
	public function getInfoWithGroupRight($wxuserid,$level,$id){
						
		$subsql = "SELECT wxaccount_id,openid FROM  __WXUSER_FAMILY__
where parent_$level = $wxuserid";
		
		$sql = "select wg.alloweddistribution,wg.allowedcomment, wu.groupid,wu.subscribe_time, wu.wxaccount_id,wu.id,wu.nickname,wu.avatar,wu.referrer,wu.openid,wu.score,wu.money,wu.status
from ($subsql) as wf left join __WXUSER__ as wu on wf.wxaccount_id = wu.wxaccount_id and wf.openid = wu.openid LEFT JOIN __GROUP_ACCESS__ as wg on wu.groupid = wg.id where wu.status =  1  and wu.id = $id ";
		
		$result = M()->query($sql);
		
		if($result === false){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
		}else{
			return $this -> apiReturnSuc($result);
		}
		
		
		
	}
	/**
	 * 查询子级会员
	 * @param $wxuserid 用户id
	 * @param $level 子级
	 * @param $page	分curpage,size
	 */
	public function queryWithGroupRight($wxuserid,$level,$page){
		
		
		$countsql = "SELECT count(openid) as cnt FROM  __WXUSER_FAMILY__
where parent_$level = $wxuserid";
		
		$subsql = "SELECT wxaccount_id,openid FROM  __WXUSER_FAMILY__
where parent_$level = $wxuserid";
		
		$sql = "select wg.alloweddistribution,wg.allowedcomment, wu.groupid,wu.subscribe_time, wu.wxaccount_id,wu.id,wu.nickname,wu.avatar,wu.referrer,wu.openid,wu.score,wu.money,wu.status
from ($subsql) as wf left join __WXUSER__ as wu on wf.wxaccount_id = wu.wxaccount_id and wf.openid = wu.openid LEFT JOIN __GROUP_ACCESS__ as wg on wu.groupid = wg.id where wu.status =  1 limit ".$page['curpage'] . ',' . $page['size'];
		
		$model = M();
		
		$result = $model->query($sql);

		$count = $model->query($countsql);
		$count = $count[0]["cnt"];
		
		// 查询满足要求的总记录数
		$Page = new \Think\Page($count, $page['size']);
		
		//分页跳转的时候保证查询条件
		if ($params !== false) {
			foreach ($params as $key => $val) {
				$Page -> parameter[$key] = urlencode($val);
			}
		}

		// 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page -> show();

		if($result === false){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
		}else{
			return $this -> apiReturnSuc(array("show" => $show, "list" => $result));
		}
		
		
		
	}
	

	
}
