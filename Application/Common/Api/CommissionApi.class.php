<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Api;
use \Common\Model\CommissionModel;
use Common\Api\Api;
class CommissionApi extends Api {

	protected function _init() {
		$this -> model = new CommissionModel();
	}
	
	/**
	 * 增加返佣金额
	 * @param $percent 返佣比例数组
	 * @param $wxuserid 用户id 
	 */
	public function addCommission($percent,$wxuserid,$price){
		$level = count($percent);
		
		$model = new \Common\Model\WxuserWithFamilyViewModel();
		$userModel = new \Common\Model\WxuserModel();
		$wallet = new \Common\Model\WxuserWalletModel();
		//统计已付款特殊处理
		$result = $model->where(array('id'=>$wxuserid))->find();
		if($result === false){
			return $this->apiReturnErr($model->getDbError());
		}
		$parent = intval($result['parent_1']);
		$userModel->startTrans();
		$addedMoney = $percent[0] * $price;
		$reason = "$wxuserid,$price,".$percent[0].";";//ID,价格,返佣比例
		$ret = $this->addMoney($parent,$addedMoney,$reason);
		if($ret['status']){
			
			$parent = intval($result['parent_2']);
			$addedMoney = $percent[1] * $price;
			$reason = "$wxuserid,$price,".$percent[1].";";//ID,价格,返佣比例
			$ret = $this->addMoney($parent,$addedMoney,$reason);
			if($ret['status']){				
				$parent = intval($result['parent_3']);
				$addedMoney = $percent[2] * $price;
				$reason = "$wxuserid,$price,".$percent[2].";";//ID,价格,返佣比例
				$ret = $this->addMoney($parent,$addedMoney,$reason);
				if($ret['status']){				
					$parent = intval($result['parent_4']);
					$addedMoney = $percent[3] * $price;
					$reason = "$wxuserid,$price,".$percent[3].";";//ID,价格,返佣比例
					$ret = $this->addMoney($parent,$addedMoney,$reason);
				}
			}
			
		}
		
		
		if($ret['status']){
			$userModel->commit();
			return $this->apiReturnSuc("返佣成功！");
		}else{
			$userModel->rollback();
			return $this->apiReturnErr($ret['info']);
		}
		
		return $result;
	}
		
	/**
	 * 给一个用户账号增加金额、可负
	 * @param $id 用户id
	 * @param $addedMoney 增加的金额 可负
	 * @param $reason 原因
	 * 
	 */
	private function addMoney($id,$addedMoney,$reason){
		if($addedMoney > 0 && $id > 0){
			$result_1 = $userModel->where(array("id"=>$id))->lock(true)->save(array('money'=>$addedMoney));
			if($result_1 === false){
				return array('status'=>false,"info"=>$userModel->getError());
			}
			$entity = array(
				'wxuserid'=>$id,
				'change'=>$addedMoney,
				'createtime'=>time(),
				'status'=>1,
				'reason'=>$reason,
			);
			if($wallet->create($entity)){
				$addedWalletChangeRecordID = $wallet->add();
				if($addedWalletChangeRecordID === false){
					return array('status'=>false,"info"=>$wallet->getError());
				}else{
					return array('status'=>true,"info"=>$addedWalletChangeRecordID);
				}
			}else{
				return array('status'=>false,"info"=>$wallet->getError());
			}
			
		}
		
		return array('status'=>true,"info"=>'');
	}

	//		1. 根据openid,wxaccount_id或wxuserid，查询表commission查找updatetime最大的记录
	//	2. 判断time() - updatetime  > C(‘COMMISSION_CACHE_TIME’)秒
	//	3. 根据公式计算2.1,2.2,2.3,2.4的佣金
	//	4. 往commison表中新增一条记录,插入成功后记录a_4［已完成订单佣金］
	//
	//	5. 已提现订单佣金 a_5 =  sum(提现记录［审核状态为通过］)
	//	6. 待审核提现佣金 a_6＝  sum(提现记录［审核状态为待审核］)
	//	7. 可提现佣金 ＝ 已完成订单佣金（最新） － a_5  － a_6
	//
	//级别返利比例

	/**
	 * 获取佣金
	 * 未付款订单佣金  ＝ n级会员未付款订单＊n级会员返利比例
	 * 已付款订单佣金  ＝ n级会员已付款订单＊n级会员返利比例
	 * 已收货订单佣金	  ＝ n级会员已收货订单＊n级会员返利比例
	 * 已完成订单佣金	  ＝ n级会员已完成订单＊n级会员返利比例
	 * @param percent 数组，佣金返利比例.
	 * @param cache_time 7200秒
	 */
	public function getCommission($percent, $wxaccount_id, $openid, $wxuserid, $cache_time = 7200) {
		//
		$nowtime = time();
		$result = $this -> getInfo(array('wxaccount_id' => $wxaccount_id, 'openid' => $openid),"updatetime desc");
		if ($result['status']) {
			if (is_array($result['info']) && $nowtime - $result['info']['updatetime'] <= $cache_time){
				return $result;
			}
			
			//计算
			$entity = $this -> computeCommission($percent, $wxaccount_id, $openid, $wxuserid);
			//如果佣金没变化则更新updatetime时间
			if(is_array($result['info'])
				&& $entity['commission_1'] == $result['info']['commission_1']
				&& $entity['commission_2'] == $result['info']['commission_2']
				&& $entity['commission_3'] == $result['info']['commission_3']
				&& $entity['commission_4'] == $result['info']['commission_4']
				&& $entity['totalsale'] == $result['info']['totalsale']
				&& $entity['totalcommission'] == $result['info']['totalcommission']){
				 $saveResult = $this -> saveByID($result['info']['id'], array('updatetime'=>$nowtime));
				 if($saveResult['status']){
				 	$result['info']['updatetime'] = $nowtime;
				 	return $result;
				 }else{
				 	return $saveResult;
				 }
			}
			//新增一条记录
			$result =  $this -> add($entity);
			if($result['status']){
				$entity['id'] = $result['info'];
				return $this->apiReturnSuc($entity);
			}else{
				return $result;
			}
		} else {
			return $result;
		}
	}
	/**
	 * 计算销售额和佣金
	 * @param $percent 返佣比例
	 * @param @wxaccount_id 公众号账号id
	 * @param $openid openid
	 * @param $wxuserid 用户ID
	 * @return array('commission_1'=>array(array('totalfee'=>100,'commission'=>10),array('totalfee'=>100,'commission'=>10)...),
	 * 'commission_2'=>array(array('totalfee'=>100,'commission'=>10),array('totalfee'=>100,'commission'=>10)...)。。。)
	 * @author hebiduhebi@126.com
	 */
	public function computeCommission($percent, $wxaccount_id, $openid, $wxuserid) {
		$level = count($percent);
		if($level < 1){
			return $this->apiReturnErr("返佣参数错误！");
		}
		//我的佣金 s，
		//待审核提现佣金 a ，已提现佣金 b，可用佣金 c
		//c = s - a（统计提现记录得出） - b（统计提现记录得出)
		 
		$entity = array('commission_1' => 0, //未付款、待确认
		'commission_2' => 0, //已付款、待确认；已付款、待发货；已付款、已发货
		'commission_3' => 0, //已付款、已收货；
		'commission_4' => 0, //已付款、已完成；
		'totalsale'=>0.00,//总销售额
		'allcommission'=>0.00,//总佣金
		'openid' => $openid, 'wxaccount_id' => $wxaccount_id);
		$oderstatus = \Common\Model\OrdersModel::ORDER_TOBE_CONFIRMED;
		$paystatus = 	\Common\Model\OrdersModel::ORDER_TOBE_PAID;
		$commission_1 = $this -> sumCommission($percent, $wxuserid,$paystatus,$oderstatus);
		//相加
		for($i=0;$i<$level;$i++){
			$entity['commission_1'] += $commission_1[$i]['commission'];
		}
		
		$oderstatus =  \Common\Model\OrdersModel::ORDER_SHIPPED;
		$paystatus = 	\Common\Model\OrdersModel::ORDER_PAID;
		$commission_2 = $this -> sumCommission($percent, $wxuserid,$paystatus,$oderstatus);
//		dump($commission_2);
		//相加
		for($i=0;$i<$level;$i++){
			$entity['commission_2'] += $commission_2[$i]['commission'];
			$entity['totalsale'] += $commission_2[$i]['totalfee'];
		}
		$orderstatus = 	\Common\Model\OrdersModel::ORDER_RECEIPT_OF_GOODS;
		$commission_3 = $this -> sumCommission($percent, $wxuserid,$paystatus,$orderstatus);
		//相加
		for($i=0;$i<$level;$i++){
			$entity['commission_3'] += $commission_3[$i]['commission'];
			$entity['totalsale'] += $commission_3[$i]['totalfee'];
		}
		$orderstatus = 	\Common\Model\OrdersModel::ORDER_COMPLETED;
		$commission_4 = $this -> sumCommission($percent, $wxuserid,$paystatus,$orderstatus);
		//相加
		for($i=0;$i<$level;$i++){
			$entity['commission_4'] += $commission_4[$i]['commission'];
			$entity['totalsale'] += $commission_4[$i]['totalfee'];
		}
		$entity['totalcommission'] = $entity['commission_4'];
		
		return $entity;
		//  订单用户id in （）
		//  查找所有的一级会员id。
		//  订单表，left 会员表，left 会员关系表
		//  for
		//	未付款订单佣金＝ n级会员未付款订单＊n级会员返利比例
		//	已付款订单佣金＝ n级会员已付款订单＊n级会员返利比例
		//	已收货订单佣金＝ n级会员已收货订单＊n级会员返利比例
		//	已完成订单佣金＝ n级会员已完成订单＊n级会员返利比例
	}

	/**
	 * TODO: sum统计可考虑做增量统计，根据时间来统计。只统计下单时间 > 上次统计时间的订单。
	 * 统计订单金额
	 * @param $percent 数组，返利比例
	 * @param $wxuserid 	用户ID
	 * @param $orderstatus 订单状态
	 * @return 佣金 array(array('totalfee'=>100,'commission'=>10),array('totalfee'=>100,'commission'=>10),)
	 * */
	public function sumCommission($percent, $wxuserid,$paystatus,$orderstatus) {
		$level = count($percent);
		//计算层级		
		//查询 一级会员
		$model = new \Common\Model\WxuserWithFamilyViewModel();
		//统计已付款特殊处理
		if($orderstatus == 4){
			$sql = " select sum(ord.price) as totalFee from __ORDERS__ as ord where ord.pay_status = $paystatus and ord.order_status >= ".\Common\Model\OrdersModel::ORDER_TOBE_CONFIRMED." and ord.order_status <= ".\Common\Model\OrdersModel::ORDER_SHIPPED." and ord.wxuser_id in  ";
		}else{
			$sql = " select sum(ord.price) as totalFee from __ORDERS__ as ord where ord.pay_status = $paystatus and ord.order_status = $orderstatus and ord.wxuser_id in  ";
		}
		$result = array();
		for ($i = 1; $i <= $level; $i++) {
			//查询第$i级用户id数据
			$idSql = $model ->field("id")-> where(array("parent_$i" => $wxuserid)) -> select(FALSE);
			//dump($sql.$idSql);
			//统计第$i级用户的销售额，根据订单总价sum统计
			$queryResult = $model -> query($sql . $idSql);
			if($queryResult === false){
				//出错
				return false;
			}
			$totalfee = $queryResult[0]['totalfee'];
			if(is_null($totalfee)){
				$totalfee = 0;
			}
			
			$result[$i-1] = array('totalfee'=>$totalfee,'commission'=>$totalfee*$percent[$i-1]);
		}
		return $result;
	}

}
