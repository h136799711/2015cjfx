<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Shop\Controller;
use Think\Controller;
class OrdersController extends ShopController {

	protected function _initialize() {
		parent::_initialize();
	}
	
	/**
	 * 微信支付成功，通知接口
	 */
	public function notify(){
		  
        
        $config = C('WXPAY_CONFIG');
        
        //使用通用通知接口
        $notify = new  \Common\Api\WxpayJsApi($config);
        
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        
		addWeixinLog($xml, '[notify]xml');
//		addWeixinLog(I('get.'), '[notify]get');
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        // if($notify->checkSign() == FALSE){
        //  $notify->setReturnParameter("return_code","FAIL");//返回状态码
        //  $notify->setReturnParameter("return_msg","签名失败");//返回信息
        // }else{
        //  $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        // }
        // $returnXml = $notify->returnXml();
        // echo $returnXml;
        
        
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                
                //此处应该更新一下订单状态，商户自行增删操作
                addWeixinLog("【通信出错】", "微信支付");
                
                // $log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
                
            } elseif ($notify->data["result_code"] == "FAIL") {
                
                //此处应该更新一下订单状态，商户自行增删操作
                //
                addWeixinLog("【业务出错】", "微信支付");
                
                // $log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
                
            } else {
                
                //此处应该更新一下订单状态，商户自行增删操作
                addWeixinLog("【支付成功】", "微信支付");
                
                // $log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
                
            }
            
            //商户自行增加处理流程
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
            
        }
        
        echo "success";
	}
	
	public function paysuccess(){
		$this->display();
	}
	/**
	 * 微信支付页面
	 */
	public function pay() {
		$id = I('get.id', '');
		addWeixinLog(I('get.'),'pay get');
		$result = apiCall("Shop/Orders/getInfo", array(array('id' => $id)));
		if ($result['status']) {
			$order = $result['info'];
			addWeixinLog($order,'pay order info');
			$payConfig = C('WXPAY_CONFIG');
			$items = unserialize($order['items']);
			$payConfig['jsapicallurl'] = $this->getCurrentURL();
			$itemdesc = $items[0]['item'];
			$trade_no = $order['orderid'];
			$total_fee = $order['price']*100;
			if($total_fee <= 0){
				$this->error("支付金额不能小于0！");
			}
			addWeixinLog($total_fee,"[支付总金额（单位：分）]");
			$this -> setWxpayConfig($payConfig, $trade_no, $itemdesc, $total_fee);
			$this -> assign("order",$order);
			$this -> assign("url",$this->getCurrentURL());
			$this -> assign("items",$items);
			$this -> display();

		}else{
			$this->error("支付失败！");
		}

	}

	public function save() {
		addWeixinLog(I('post.'),'[order]save');
		$userinfo = session("userinfo");
//		$userinfo = array('id'=>1);
		if (IS_POST && is_array($userinfo)) {
			addWeixinLog($userinfo,'[session]saveispost');
			$entity = array(
				'wxuser_id' => $userinfo['id'], 
				'price' => I('post.totalprice', 0), 
				'mobile' => I('post.mobile', ''), 
				'wxno' => I('post.wxno', ''), 
				'contactname' => I('post.contactname', ''), 
				'note' => I('post.note', ''), 
				'country' => I('post.country', ''), 
				'province' => I('post.p_name', ''), 
				'city' => I('post.c_name', ''), 
				'area' => I('post.a_name', ''), 
				'detailinfo' => I('post.address', ''), 
				'orderid' => $this -> getOrderID(), 
				'items' => $this -> getItems()
			 );
			$result = apiCall("Shop/Orders/add", array($entity));
			
			if ($result['status']) {
				$id = $result['info'];	
				addWeixinLog($id,"insertOrderId = ");
				$address = array('wxuserid' => $userinfo['id'], 
				'country' => I('post.country', ''), 
				'province' => I('post.province', ''), 
				'city' => I('post.city', ''), 
				'detailinfo' => I('post.address', ''), 
				'area' => I('post.area', ''),
				'mobile' => I('post.mobile', ''), 
				'wxno' => I('post.wxno', ''), 
				'contactname' => I('post.contactname', ''), 
				);
				$result = apiCall("Shop/Address/addOrUpdate", array($address));
				if ($result['status']) {
//					dump($result);
//					$this -> success("操作成功！", U('Shop/Orders/pay') . "?id=$id&showwxpaytitle=1");
					$this -> success("操作成功！", C("SITE_URL") . "/index.php/Shop/Orders/pay/id/$id.shtml?showwxpaytitle=1");
				} else {
					LogRecord($result['info'], __FILE__ . __LINE__);
				}
			}else{
				$this -> error($resutl['info']);
			}
//			dump($address);
		} else {
			LogRecord("禁止访问！", __FILE__.__LINE__);
			$this -> error("禁止访问！");
		}

	}

	private function getItems() {
		$items = array( array('n'=>I('post.amount',0),'pic'=>I('post.pic',''),'item' => I('post.productname', ''), 'price' => I('post.price', 0)), );

		return serialize($items);
	}

	private function getOrderID() {
		return $this -> wxaccount['id'] . date('YmdHis', time()) . $this -> randInt();
	}

	private function randInt() {
		srand(GUID());
		return rand(10000000, 99999999);
	}
	
		
	/**
	 * @param config 配置
	 * @param trade_no 订单ID
	 * @param itemdesc 商品描述
	 * @param total_fee 总价格
	 */
	private function setWxpayConfig($config, $trade_no, $itemdesc, $total_fee, $prodcutid = 1) {
		try {
			//使用jsapi接口
			$jsApi = new \Common\Api\WxpayJsApi($config);

			//=========步骤1：网页授权获取用户openid============
			//通过code获得openid
			if (!isset($_GET['code'])) {
				//触发微信返回code码
				$url = $jsApi -> createOauthUrlForCode($config['jsapicallurl']);
				//				$url = $url.'?showwxpaytitle=1';
				Header("Location: $url");
			} else {
				//获取code码，以获取openid
				$code = $_GET['code'];
				$jsApi -> setCode($code);
				$result = $jsApi -> getOpenId();
			}
			$openid = "";
			if ($result['status']) {
				$openid = $result['info'];
			} else {
				$this -> error($result['info']);
			}
			//			dump($openid);
			//			dump($result);
			//			exit();
			//=========步骤2：使用统一支付接口，获取prepay_id============

			//使用统一支付接口
			$unifiedOrder = new \Common\Api\UnifiedOrderApi($config);

			//设置统一支付接口参数
			//设置必填参数
			//appid已填,商户无需重复填写
			//mch_id已填,商户无需重复填写
			//noncestr已填,商户无需重复填写
			//spbill_create_ip已填,商户无需重复填写
			//sign已填,商户无需重复填写
			$unifiedOrder -> setParameter("openid", "$openid");
			//商品描述
			$unifiedOrder -> setParameter("body", $itemdesc);
			//商品ID
			//$unifiedOrder -> setParameter("product_id", "$prodcutid");
			//商户订单号
			$unifiedOrder -> setParameter("out_trade_no", "$trade_no");
			//总金额
			$unifiedOrder -> setParameter("total_fee", "$total_fee");
			//通知地址
			$unifiedOrder -> setParameter("notify_url", $config['notifyurl']);
			$unifiedOrder -> setParameter("trade_type", "JSAPI");

			//          $unifiedOrder->setParameter("attach",'{"token":"'.'123'.'","orderid":"'.'456'.'"}');//附加数据
			//交易类型//商户订单号
			//非必填参数，商户可根据实际情况选填
			//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
			//$unifiedOrder->setParameter("device_info","XXXX");//设备号
			//$unifiedOrder->setParameter("attach","XXXX");//附加数据
			//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
			//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
			//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
			//$unifiedOrder->setParameter("openid","XXXX");//用户标识
			
			$prepay_id = $unifiedOrder -> getPrepayId();
			//=========步骤3：使用jsapi调起支付============
			$jsApi -> setPrepayId($prepay_id);

			$jsApiParameters = $jsApi -> getParameters();

			if (!empty($jsApiParameters -> return_msg)) {
				$this -> assign("error", $error);
//				$this -> error($jsApiParameters -> return_msg);
			}
			addWeixinLog($jsApiParameters,"设置微信支付配置！");
			//			dump($unifiedOrder);
			//			dump($jsApiParameters);
			//			exit();
			//			$returnUrl = U('Home/WxpayTest/return',array(''));

			$this -> assign("jsapiparams", $jsApiParameters);
			//      	$this->assign("params", json_decode($jsApiParameters));
			//	        $this->assign('returnUrl', $returnUrl);
		} catch(SDKRuntimeException $sdkexcep) {
			$error = $sdkexcep -> errorMessage();
			$this -> assign("error", $error);
//			$this -> error($error);
		}

	}
	
	
	

}
