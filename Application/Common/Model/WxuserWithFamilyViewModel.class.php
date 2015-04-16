<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Model;
use Think\Model\ViewModel;

class WxuserWithFamilyViewModel extends  ViewModel{
	public $viewFields = array(
		"Wxuser"=>array('_table'=>'__WXUSER__','id','nickname','createtime','updatetime','referrer','score','avatar','status','wxaccount_id','openid'),
		"WxuserFamily"=>array("_on"=>"Wxuser.wxaccount_id=WxuserFamily.wxaccount_id and Wxuser.openid=WxuserFamily.openid","_table"=>"__WXUSER_FAMILY__",'_type'=>'LEFT'
		,'parent_1','parent_2','parent_3','parent_4','parent_5')
	);
}
