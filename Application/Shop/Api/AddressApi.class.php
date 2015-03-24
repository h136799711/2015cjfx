<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Api;
use Common\Api;
use Common\Model\AddressModel;

class AddressApi extends \Common\Api\Api{
	protected function _init(){
		$this->model = new AddressModel();
	}
	
	/**
	 * 增加或更新地址，根据wxuserid
	 * @entity 地址信息
	 */
	public function addOrUpdate($entity){
		$result = $this->model->where(array("wxuserid"=>$entity['wxuserid']))->find();
		if($result === false){
			return $this->apiResultErr($this->model->getDbError());
		}
		
		if(is_null($result)){
			return $this->add($entity);
		}else{
			$wxuserid = $entity['wxuserid'];
			unset($entity['wxuserid']);
			return $this->save(array('wxuserid'=>$wxuserid),$entity);
		}
		
	}
}
