<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Admin\Controller;

class WxreplyTextController extends  AdminController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index(){
		$map = array();
		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " updatetime desc ";
		//
		$result = apiCall('Admin/WxreplyText/query',array($map,$page,$order));
		if($result['status']){
			$this->assign("show",$result['info']['show']);
			$this->assign("list",$result['info']['list']);
			$this->display();
		}
	}
	
	/**
	 * 添加界面/保存
	 * @override
	 */	 
	public function add(){
		if(IS_GET){
			$this->display();
		}elseif(IS_POST){
			$entity = array(
						"keyword"=>I('post.keyword',''),
						"content"=>I('post.content',''),
						"wxaccount_id"=> 1,//TODO:暂支持单公众号所以此处公众号ID写死
						);
			$result = apiCall("Admin/WxreplyText/add",array($entity));
			if($result['status']){
				$this->success(L('RESULT_SUCCESS'),U('Admin/WxreplyText/index'));
			}else{
				LogRecord($result['info'], __FILE__);
				$this->error($result['info']);
			}
		}
	}
	
	/**
	 * 编辑/保存
	 */
	public function edit(){
		if(IS_GET){
			$id = I('get.id',0);
			$result = apiCall("Admin/WxreplyText/getInfo",array(array('id'=>$id)));
			if($result['status']){
				$this->assign("textVO",$result['info']);
				$this->display();
			}else{
				LogRecord($result['info'], __FILE__);
				$this->error($result['info']);
			}
			
		}elseif(IS_POST){
			$id = I('post.id',0);
			$entity = array(
						"keyword"=>I('post.keyword',''),
						"content"=>I('post.content','')
						);
			$result = apiCall("Admin/WxreplyText/saveByID",array($id,$entity));
			if($result['status']){
				$this->success(L('RESULT_SUCCESS'),U('Admin/WxreplyText/index'));
			}else{
				LogRecord($result['info'], __FILE__);
				$this->error($result['info']);
			}
			
		}
	}
}
