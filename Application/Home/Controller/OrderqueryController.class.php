<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

class OrderqueryController extends Controller {
	
	public function index(){
		addWeixinLog("OrderQuery".date("Y-m-d H:i:s",time()),"OrderqueryController");
		echo "index";
		
	}
	
	public function testIndex(){
		$map= array('wxuser_id'=>1);
		$page = array('curpage'=>0,'size'=>10);
		$order = "pay_status asc";
		$params = false;
		$fields = "id,orderid,price,createtime,pay_status,order_status,expressname,expressno";
		$result = apiCall("Shop/OrdersWithExpress/query",array($map,$page,$order,$params,$fields));
		dump($result);
	}
	
	
	public function test(){
		$str = "交易时间,公众账号ID,商户号,子商户号,设备号,微信订单号,商户订单号,用户标识,交易类型,交易状态,付款银行,货币种类,总金额,企业红包金额,微信退款单号,商户退款单号,退款金额,企业红包退款金额,退款类型,退款状态,商品名称,商户数据包,手续费,费率
`2015-03-30 21:25:32,`wx58aea38c0796394d,`10027619,`0,`,`1009360611201503300040393756,`20150330212520177109431,`oqMIVt3Ouq-2Vm0kZOZmZ2rTDlP8,`JSAPI,`SUCCESS,`CFT,`CNY,`0.01,`0.00,`0,`0,`0,`0,`,`,`COS-洁面慕斯+精华套装,`,`0.00020,`2.00%
`2015-03-30 21:15:32,`wx58aea38c0796394d,`10027619,`0,`,`1010020611201503300040396072,`20150330211522713086491,`oqMIVt4q_YCy4Pep2QGItFI0DYhw,`JSAPI,`SUCCESS,`CFT,`CNY,`0.01,`0.00,`0,`0,`0,`0,`,`,`COS-洁面慕斯+精华套装,`,`0.00020,`2.00%
总交易单数,总交易额,总退款金额,总企业红包退款金额,手续费总金额
`2,`0.02,`0.00,`0.00,`0.00040";
		$table = split("[\n]",$str);
		$cnt = count($table);
		$header = split(",",$table[0]);
		$result = array();
		$result['header'] = $header;
		$result['rows'] = array();
//		dump($header);
		for($i=1;$i<$cnt-2;$i++){
			$row = split(",",$table[$i]);
//			dump($row);		
			$result['rows'][] = $row;	
		}
		if($cnt - 2 > 0){
			$footer_title = split(",",$table[$cnt-2]);
			$footer_cont = split(",",$table[$cnt-1]);
//			dump($footer_title);
//			dump($footer_cont);
			$result['footer'] = array("title"=>$footer_title,"cont"=>$footer_cont);
		}
		
		dump($result);
		
	}

//	public function index() {
//		
//		dump(C("WXPAY_CONFIG"));
//		$this -> display();
//	}

	public function query() {

		//退款的订单号
		if (!isset($_POST["out_trade_no"])) {
			$out_trade_no = " ";
		} else {
			$out_trade_no = $_POST["out_trade_no"];
			$appid = "";
			$appsecrect = "";
			$config = C("WXPAY_CONFIG");
			//使用订单查询接口
			$orderQuery = new \Common\Api\OrderQueryApi($config);
			//设置必填参数
			//appid已填,商户无需重复填写
			//mch_id已填,商户无需重复填写
			//noncestr已填,商户无需重复填写
			//sign已填,商户无需重复填写
			$orderQuery -> setParameter("out_trade_no", "$out_trade_no");
			//商户订单号
			//非必填参数，商户可根据实际情况选填
			//$orderQuery->setParameter("sub_mch_id","XXXX");//子商户号
			//$orderQuery->setParameter("transaction_id","XXXX");//微信订单号

			//获取订单查询结果
			$orderQueryResult = $orderQuery -> getResult();

			//商户根据实际情况设置相应的处理流程,此处仅作举例
			if ($orderQueryResult["return_code"] == "FAIL") {
				echo "通信出错：" . $orderQueryResult['return_msg'] . "<br>";
			} elseif ($orderQueryResult["result_code"] == "FAIL") {
				echo "错误代码：" . $orderQueryResult['err_code'] . "<br>";
				echo "错误代码描述：" . $orderQueryResult['err_code_des'] . "<br>";
			} else {
				echo "交易状态：" . $orderQueryResult['trade_state'] . "<br>";
				echo "设备号：" . $orderQueryResult['device_info'] . "<br>";
				echo "用户标识：" . $orderQueryResult['openid'] . "<br>";
				echo "是否关注公众账号：" . $orderQueryResult['is_subscribe'] . "<br>";
				echo "交易类型：" . $orderQueryResult['trade_type'] . "<br>";
				echo "付款银行：" . $orderQueryResult['bank_type'] . "<br>";
				echo "总金额：" . $orderQueryResult['total_fee'] . "<br>";
				echo "现金券金额：" . $orderQueryResult['coupon_fee'] . "<br>";
				echo "货币种类：" . $orderQueryResult['fee_type'] . "<br>";
				echo "微信支付订单号：" . $orderQueryResult['transaction_id'] . "<br>";
				echo "商户订单号：" . $orderQueryResult['out_trade_no'] . "<br>";
				echo "商家数据包：" . $orderQueryResult['attach'] . "<br>";
				echo "支付完成时间：" . $orderQueryResult['time_end'] . "<br>";
			}
		}
		//商户自行增加处理流程
		//......
	}

}
