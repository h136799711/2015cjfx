<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Controller;

use Think\Controller;

class TestCommController extends  Controller{
	
	protected function _initialize(){
		// 获取配置
		$this -> getConfig();

		if (!defined('APP_VERSION')) {
			//定义版本
			if (defined("APP_DEBUG") && APP_DEBUG) {
				define("APP_VERSION", time());
			} else {
				define("APP_VERSION", C('APP_VERSION'));
			}
		}
	}
	
	public function testAdded(){
		$wxaccount_id = "1"; 
		$percent = C('COMMISSION_RATIO');
		$wxuserid = 1;
		$result = apiCall("Common/Commission/giveCommission", array($percent,$wxuserid,100));
		dump($result);
	}
	
	public function index(){
		$token = "";
		$wxaccount_id = "1"; 
		$openid = "oqMIVt3Ouq-2Vm0kZOZmZ2rTDlP8";
		$wxuserid = 1;
		$percent = C('COMMISSION_RATIO');
//		$result = apiCall("Shop/Commission/computeCommission",array($percent,$wxaccount_id,$openid,$wxuserid));
		dump($percent);
		$oderstatus = \Common\Model\OrdersModel::ORDER_TOBE_CONFIRMED;
		$paystatus = 	\Common\Model\OrdersModel::ORDER_PAID;
//		$result = apiCall("Shop/Commission/sumCommission",array($percent, $wxuserid,$paystatus,$oderstatus));

		$result = apiCall("Shop/Commission/getCommission",array($percent,$wxaccount_id,$openid, $wxuserid,10));		
		
		dump($result);
		
		echo "=测试佣金与销售额计算=";
	}
	
	
	
	/**
	 * 从数据库中取得配置信息
	 */
	protected function getConfig() {
		$config = S('config_' . session_id() . '_' . session("uid"));

		if ($config === false) {
			$map = array();
			$fields = 'type,name,value';
			$result = apiCall('Admin/Config/queryNoPaging', array($map, false, $fields));
			if ($result['status']) {
				$config = array();
				if (is_array($result['info'])) {
					foreach ($result['info'] as $value) {
						$config[$value['name']] = $this -> parse($value['type'], $value['value']);
					}
				}
				//缓存配置300秒
				S("config_" . session_id() . '_' . session("uid"), $config, 300);
			} else {
				LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
				$this -> error($result['info']);
			}
		}
		C($config);
	}

	/**
	 * 根据配置类型解析配置
	 * @param  integer $type  配置类型
	 * @param  string  $value 配置值
	 */
	private static function parse($type, $value) {
		switch ($type) {
			case 3 :
				//解析数组
				$array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
				if (strpos($value, ':')) {
					$value = array();
					foreach ($array as $val) {
						list($k, $v) = explode(':', $val);
						$value[$k] = $v;
					}
				} else {
					$value = $array;
				}
				break;
		}
		return $value;
	}
	
}
