<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2015, http://www.gooraye.net. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Ucenter\Controller;

class AuthManageController extends UcenterController {

	protected function _initialize() {
		parent::_initialize();
	}

	/**
	 * 成员授权
	 *
	 */
	public function user() {
		$map = array();
		$groupid = I('groupid',-1);
		
        $map['status']  =   array('egt',0);
		if($groupid != -1){
			$map['group_id'] = $groupid;		
		}
		$memberMap = array();
		
		//用户组
		$result = apiCall("Ucenter/AuthGroup/queryNoPaging", array($map));
		if ($result['status']) {
			if($groupid === -1){
				$groupid = $result['info'][0]['id'];
			}
			
			$this->assign("groupid",$groupid);
			$this -> assign("groups", $result['info']);
			
	        $memberMap['status']  =   array('egt',0);
			$memberMap['group_id'] = $groupid;
			//查询用户信息
			//TODO:
			$result = apiCall("Ucenter/Member/queryByGroup", array($memberMap,array('curpage'=>I('p',0),'size'=>10)));
			if($result['status']){
				$this->assign("show",$result['info']['show']);
				$this->assign("list",$result['info']['list']);
			}
			$this -> display();
		} else {
			$this -> error($result['info']);
		}
	}

	/**
	 * 访问授权	 *
	 */
	public function access() {
			
		$map = array();
		$map_access = array();
		$groupid = I('groupid',-1);
		//模块标识
		$modulename = I('modulename','');
		
        $map['status']  =   array('egt',0);
		if($groupid != -1){
			$map['group_id'] = $groupid;		
		}
		
		$map_access['status'] = 1;
		if(!empty($modulename)){
			$map_access['module'] = $modulename;
		}
		
		$memberMap = array();
		
		//用户组
		$result = apiCall("Ucenter/AuthGroup/queryNoPaging", array($map));
		$map_access['type'] = 2;
		//访问权限节点
		$main_authRules = apiCall("Ucenter/AuthRule/queryNoPaging", array($map_access,'name asc','id,module,name,title'));
		
		$map_access['type'] = 1;
		$child_authRules = apiCall("Ucenter/AuthRule/queryNoPaging", array($map_access,'name asc','id,module,name,title'));
		
		if($main_authRules['status'] && $child_authRules['status']){
			$tree = $this->createThreeLayers($main_authRules['info'],$child_authRules['info']);
			$this->assign("accessTree",$tree); 
		}else{
			$this->error('获取数据失败！');
		}
		
		if ($result['status']) {
			if($groupid === -1 && count($result['info']) > 0){
				//默认第一个用户组
				$groupid = $result['info'][0]['id'];
				$rules = $result['info'][0]['rules'];
			}else{
				$rules = $this->getRules($result['info'],$groupid);
			}
			//当前用户组拥有的规则
			$this->assign("rules",$rules);
			//当前用户组
			$this->assign("groupid",$groupid);
			$this -> assign("groups", $result['info']);
				       
			$this -> display();
		} else {
			$this -> error($result['info']);
		}
	}
	
	/**
	 * 根据用户组id，获取用户组对应规则
	 * @param $groups 用户组列表
	 * @param $groupid 用户组id
	 * @return string 用户组规则
	 */
	private function getRules($groups,$groupid){
		
		foreach($groups as $vo){
			if($vo['id'] == $groupid){
				return $vo['rules'];
			}
		}
	}
	
	//$VO必是有Ucenter/Index/index 的字符串
	private function getCTRLName($vo){
		$temp = explode('/', $vo);
		if(count($temp) == 3){
			return $temp[0].'_'.$temp[1];
		}else{
			return "errURL";
		}
	}
	
	private function createThreeLayers($mainrules,$childrules){
		$tree = array();
		foreach($mainrules as $vo){
			$key = $this->getCTRLName($vo['name']);
			if(isset($tree[$key])){
				array_push($tree[$key]['_child'],array(
													'id'=>$vo['id'],
													'title'=>$vo['title']
													));
			}else{
				$tree[$key] = array(
					'id'=>$vo['id'],
					'title'=>$vo['title'],
					'_child'=>array()
				);
			}

		}
		//dump($tree);
		
		foreach($childrules as $vo){
			$key = $this->getCTRLName($vo['name']);
			if(isset($tree[$key])){
				array_push($tree[$key]['_child'],array(
													'id'=>$vo['id'],
													'title'=>$vo['title']
													));
			}else{
				$tree[$key] = array(
					'id'=>$vo['id'],
					'title'=>$vo['title'],
					'_child'=>array()
				);
			}
		}
		return $tree;
//		dump($childrules);
	}

}
