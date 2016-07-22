<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Wechat\Controller;

use OT\DataDictionary;
use Common\Controller\Wechat;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class BzjController extends HomeController {
	private $wechat;
	private $gain;
	
	// 系统首页
	public function index() {
		$this->display ();
	}
	
	/**
	 * 微信支付
	 * setp1:设置Common/Wxpay/WxPaypubconfig.class.php 配置文件下配置文件
	 * @param unknown $order_id
	 * @param number $self
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function wxpay($id, $self = 0) {
		import ( 'Common.Wxpay.WxPayPubHelper' );
		// 使用jsapi接口
		$jsApi = new \JsApipub ();
		// =========步骤1：网页授权获取用户openid============
		$openid = session("selfopenid");
		if(empty($openid)){
			// 触发微信返回code码
			$nowurl = urlencode ( "http://" . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
			$url = $jsApi->createOauthUrlForCode ( $nowurl );
			Header ( "Location: $url" ); // 去获取CODE，获取CODE后跳转到当前页面（当前页面就带上了需要支付的订单编号）
			exit();
		}
		// =========步骤2：使用统一支付接口，获取prepay_id============
		// 使用统一支付接口
		$unifiedOrder = new \UnifiedOrderpub ();
		$order_info = M ( 'Bzj' )->where ( 'id=' . $id )->find (); // 获取订单信息
		$unifiedOrder->setParameter ( "openid", "$openid" ); // 商品描述
		$unifiedOrder->setParameter ( "body", "《".C('WEB_SITE_TITLE')."》保证金充值：" . $id ); // 商品描述
		$out_trade_no = $order_info ['wcid'];
		$unifiedOrder->setParameter ( "out_trade_no", "$out_trade_no" ); // 商户订单号
		/* $order_info['money'] * 100 */
		$unifiedOrder->setParameter ( "total_fee", $order_info['money'] * 100 ); // 总金额
		$unifiedOrder->setParameter ( "notify_url", 'http://'.C('WEB_SITE_DOMAIN').'/Bzj/notify.html' ); // 通知地址
		$unifiedOrder->setParameter ( "trade_type", "JSAPI" ); // 交易类型
		
		$prepay_id = $unifiedOrder->getPrepayId ();
		// =========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId ( $prepay_id );
		$jsApiParameters = $jsApi->getParameters ();
		$this->assign ( "jsApiParameters", $jsApiParameters );
		$this->display ();
	}
	public function notify() {
		import ( 'Common.Wxpay.WxPayPubHelper' );
		import ( 'Common.Wxpay.log' );
		// 使用通用通知接口
		$notify = new \Notifypub ();
		//微信的回调
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$notify->saveData ( $xml );
		if ($notify->checkSign () == FALSE) {
			$notify->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$notify->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码
		}
		$returnXml = $notify->returnXml ();
		
		if ($notify->checkSign () == TRUE) {
			if ($notify->data ["return_code"] == "FAIL") { // 不予理会
				//通信出错
			} elseif ($notify->data ["result_code"] == "FAIL") { // 不予理会，仅记录
				//支付失败
			} else {
				/* 支付成功时的业务逻辑  */
				$savedata ['status'] = 1; // 修改成什么状态
				$map ['wcid'] = $notify->data ['out_trade_no'];
				$info = M ( 'Bzj' )->where ( $map )->find ();
				
				if ($info ['status'] == 0) { // 为等待确认状态并接收微信传递参数时才修改订单状态
					/* 修改订单状态 */
					$res = M ( 'Bzj' )->where ( $map )->save ( $savedata );
					
					$uid = $info['uid'];
					$bzj = M('Member')->where('uid='.$uid)->getField("bzj");
					$data['bzj'] = $bzj + $info['money'];
					M('Member')->where('uid='.$uid)->save($data);
					
					/* 发送通知模板告知用户  */
					/*$this->wechat = new Wechat (); // 实例化 wechat 类
					$url = "http://".C('WEB_SITE_DOMAIN')."/user/myorder.html";
					$data ['first'] = "我们已收到您的货款，开始为您打包商品，请耐心等待: )";
					$data ['orderMoneySum'] = ($notify->data ['total_fee'] / 100) . "元";
					$data ['orderProductName'] = "订单编号" . $info ['id'];
					$data ['Remark'] = "如有问题与客服人员联系，我们将在第一时间为您服务！";
					$this->wechat->tplmsg ( $notify->data ['openid'], "i7R5JoEXN5ySF3l2F0N30qW24FXgkZFuTd8ZpWbpbvI", $url, $data );
					*/
				}
			}
		}
	}
	
	
	
	
	
	// 充值保证金
	public function update($money) {
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		$Bzj = M ( 'Bzj' );
		$data['uid'] = is_login();
		$data['money'] = $money;
		$data['create_time'] = NOW_TIME;
		$data['status'] = 0;
		
		$res = $Bzj->add($data);
		
		
		if ($res) {
			$data['wcid'] = "wx5c2bfb9f9d0501c6" . $res;
			M('Bzj')->where('id='.$res)->save($data);
			echo "<meta content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;\" name=\"viewport\" />";
			$url = U ( 'Bzj/Wxpay?id='.$res);
			$this->success ( "充值提交成功，正在为您跳转支付，请耐心等待！", $url );
		} else {
			echo 'false';
			exit ();
		}
	}
}