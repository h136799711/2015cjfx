<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

class WxpayTestController extends Controller {
	public function index() {
		$payConfig = C('WXPAY_CONFIG');
		
		$itemdesc = "COS-精华套装";
		$trade_no = $this->getOrderID();
		$total_fee = 0.01;
		$this->setWxpayConfig($payConfig, $trade_no, $itemdesc, $total_fee);
		$this -> display();
	}
	
	private function getOrderID() {
		return  date('YmdHis',time()).$this->randInt();

	}
	
	private function randInt(){
		srand(GUID());
		return rand(10000000, 99999999);
	}

	public function notify() {
		addWeixinLog(I('post.'), '[notify]post');
		addWeixinLog(I('get.'), '[notify]get');
		  
        addWeixinLog(time(), 'notify_url');
        
        $config = C('WXPAY_CONFIG');
        
        //使用通用通知接口
        $notify = new  \Common\Api\WxpayJsApi($config);
        
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        
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
        
        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        
        //以log文件形式记录回调信息
        // $log_ = new Log_();
        // $log_name="./notify_url.log";//log文件路径
        // $log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
        
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

	public function jsapicall() {
		addWeixinLog(I('post.'), '[notify]post');
		addWeixinLog(I('get.'), '[notify]get');
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
			if($result['status']){
				$openid = $result['info'];
			}else{
				$this->error($result['info']);
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
			$unifiedOrder -> setParameter("openid", $openid);
			//商品描述
			$unifiedOrder -> setParameter("body", $itemdesc);
			//商品描述
			//自定义订单号，此处仅作举例
			//	$timeStamp = time();
			$unifiedOrder -> setParameter("product_id", $prodcutid);
			//商品ID
			$unifiedOrder -> setParameter("out_trade_no", "$trade_no");
			//商户订单号
			$unifiedOrder -> setParameter("total_fee", "$total_fee");
			//总金额
			$unifiedOrder -> setParameter("notify_url", $config['notifyurl']);
			//通知地址
			$unifiedOrder -> setParameter("trade_type", "JSAPI");
			//交易类型
			//	$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
			//	$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
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
			$returnUrl = U('Home/WxpayTest/return',array(''));
	        
        	$this->assign("jsapiparams", $jsApiParameters);
	        $this->assign('returnUrl', $returnUrl);
		} catch(SDKRuntimeException $sdkexcep) {
			$error = $sdkexcep -> errorMessage();
			$this -> assign("error", $error);			
			$this->error($error);
		}
		
	}
	

}
