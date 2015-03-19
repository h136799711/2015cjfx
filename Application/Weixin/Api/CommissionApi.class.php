<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Weixin\Api;
use Weixin\Model\CommissionModel;

class CommissionApi extends  \Common\Api\Api{
	
	protected function _init(){
		$this->model = new CommissionModel();
	}
	
	/**
	 * 根据参数创建一个$commission记录，如果不存在
	 * 
	 * @return 
	 */
	public function createOneIfNone($wxaccount_id,$openid){
		$commission = $this->model->where(array('wxaccount_id'=>$wxaccount_id,'openid'=>$openid))->find();
		if($commission === false ){
			$error = $this->model->getDbError();
			return $this -> apiReturnErr($error);
		}elseif(is_array($commission)){
			//已存在
			return $this -> apiReturnSuc($commission['id']);
		}
		
		$entity = array(
			'wxaccount_id'=>$wxaccount_id,
			'openid'=>$openid,
		);
		
		
		return  $this->add($entity);
	}
	
}