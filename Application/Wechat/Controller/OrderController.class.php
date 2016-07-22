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
class OrderController extends HomeController {
	private $wechat;
	private $gain;
	
	// 系统首页
	public function index() {
		$category = D ( 'Category' )->getTree ();
		$lists = D ( 'Document' )->lists ( null );
		$this->assign ( 'category', $category ); // 栏目
		$this->assign ( 'lists', $lists ); // 列表
		$this->assign ( 'page', D ( 'Document' )->page ); // 分页
		$this->display ();
	}
	
	/**
	 * 微信支付
	 * setp1:设置Common/Wxpay/WxPaypubconfig.class.php 配置文件下配置文件
	 *
	 * @param unknown $order_id        	
	 * @param number $self        	
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function wxpay($order_id, $self = 0) {
		import ( 'Common.Wxpay.WxPayPubHelper' );
		// 使用jsapi接口
		$jsApi = new \JsApipub ();
		// =========步骤1：网页授权获取用户openid============
		$openid = session ( "selfopenid" );
		if (empty ( $openid )) {
			// 触发微信返回code码
			$nowurl = urlencode ( "http://" . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
			$url = $jsApi->createOauthUrlForCode ( $nowurl );
			Header ( "Location: $url" ); // 去获取CODE，获取CODE后跳转到当前页面（当前页面就带上了需要支付的订单编号）
			exit ();
		}
		// =========步骤2：使用统一支付接口，获取prepay_id============
		// 使用统一支付接口
		$unifiedOrder = new \UnifiedOrderpub ();
		
		$order_info = M ( 'Order' )->where ( 'id=' . $order_id )->find (); // 获取订单信息
		$unifiedOrder->setParameter ( "openid", "$openid" ); // 商品描述
		$unifiedOrder->setParameter ( "body", "《" . C ( 'WEB_SITE_TITLE' ) . "》订单编号：" . $order_id ); // 商品描述
		if ($self > 0) { // 从我的订单页手动发起支付，需重新更改微信订单号//不是所有地方都需要，仅手动发起时。避免重复提交订单导致错误。
			$wechatid = get_wechatid ();
			$order_info ['wcorderid'] = $wechatid . $order_info ['id'] . rand ( 100, 999 );
			M ( 'Order' )->where("id=".$order_id)->setField ("wcorderid", $order_info ['wcorderid'] );
		}
		$out_trade_no = $order_info ['wcorderid'];
		$unifiedOrder->setParameter ( "out_trade_no", "$out_trade_no" ); // 商户订单号
		/* */
		$unifiedOrder->setParameter ( "total_fee", $order_info ['price'] * 100 ); // 总金额
		$unifiedOrder->setParameter ( "notify_url", 'http://' . C ( 'WEB_SITE_DOMAIN' ) . '/Order/notify.html' ); // 通知地址
		$unifiedOrder->setParameter ( "trade_type", "JSAPI" ); // 交易类型
		
		$prepay_id = $unifiedOrder->getPrepayId ();
		// =========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId ( $prepay_id );
		$jsApiParameters = $jsApi->getParameters ();
		$this->assign ( "jsApiParameters", $jsApiParameters );
		$this->assign ( "order_info", $order_info );
		$this->display ();
	}
	public function notify() {
		import ( 'Common.Wxpay.WxPayPubHelper' );
		import ( 'Common.Wxpay.log' );
		// 使用通用通知接口
		$notify = new \Notifypub ();
		// 微信的回调
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
				                                               // 通信出错
			} elseif ($notify->data ["result_code"] == "FAIL") { // 不予理会，仅记录
				                                                     // 支付失败
			} else {
				/* 支付成功时的业务逻辑 */
				$savedata ['status'] = 1; // 修改成什么状态
				$savedata['transaction_id'] = $notify->data['transaction_id'];
				$map ['wcorderid'] = $notify->data ['out_trade_no'];
				$info = M ( 'Order' )->where ( $map )->find ();
				
				if ($info ['status'] < 2) { // 为等待确认状态并接收微信传递参数时才修改订单状态
					/* 修改订单状态 */
					$res = M ( 'Order' )->where ( $map )->save ( $savedata );
					
					$map ['wcorderid'] = $notify->data ['out_trade_no'];
					$info = M ( 'Order' )->where ( $map )->find ();
					if($info['status']==1){//修改成功了
						
						$orderlist = M('Orderlist')->where("order_id=".$info['id'])->select();
						$jifen = array(0);
						for($i=0;$i<count($orderlist);$i++){
							$str[$i] = $orderlist[$i]['pro_id']."x".$orderlist[$i]['length'];
							//库存处理
							$where = array();
							$where['id'] = $orderlist[$i]['pro_id'];
							M("ProductProlist")->where($where)->setDec("kucun",$orderlist[$i]['length']);
						}
							
						//处理包邮卡券
						/* 添加优惠券使用信息 */
						//优惠券
							
						if(isset($info['hb_id']) && $info['hb_id']>0){
							if($info['hb_type']=="1"){//用户红包
								$hbmap = array();
								$hbmap['id'] = $info['hb_id'];
								$hbmap['uid'] = $info['uid'];
								$savedata = array();
								$savedata['status'] = 0;
								$savedata['use_time'] = NOW_TIME;
								$savedata['order_id'] = $info['id'];
								M('userhb_log')->where($hbmap)->save($savedata);
							}else if($info['hb_type']=="0"){//系统红包
								$map1['id'] = $info['hb_id'];
								$cinfo = M('coupons_list')->where($map1)->find();
								$cdata['length'] = $cinfo['length']-1;
								if(!$cdata['length']){
									$cdata['status'] = 0;
								}
								M('coupons_list')->where($map1)->save($cdata);
								$logdata['pid'] = $info['hb_id'];
								$logdata['uid'] = $info['uid'];
								$logdata['times'] = 1;
								$logdata['money'] = $cinfo['money'];
								$logdata['create_time'] = NOW_TIME;
								M('coupons_list_log')->add($logdata);
							}
						}
						
						
						//包邮卡
						if(isset($info['byk']) && $info['byk']>0){
							$map1 = array();
							$map1['id'] = $info['byk'];
							$cinfo = M('coupons_list')->where($map1)->find();
							$cdata['length'] = $cinfo['length']-1;
							if(!$cdata['length']){
								$cdata['status'] = 0;
							}
							M('coupons_list')->where($map1)->save($cdata);
							$logdata['pid'] = $info['byk'];
							$logdata['uid'] = is_login();
							$logdata['times'] = 1;
							$logdata['money'] = $cinfo['money'];
							$logdata['create_time'] = NOW_TIME;
							M('coupons_list_log')->add($logdata);
						}
							
							
						$str = implode(",",$str);
							
						/* 发送通知模板告知用户 */
						$this->wechat = new Wechat (); // 实例化 wechat 类
						$url = "http://".C('WEB_SITE_DOMAIN')."/user/myorder.html";
						$data ['first'] = "我们已收到您的货款，开始为您打包商品，请耐心等待！";
						$data ['orderMoneySum'] = ($notify->data ['total_fee'] / 100) . "元";
						$data ['orderProductName'] = "订单编号" . $info ['id']."，产品：".$str;
						$data ['Remark'] = "如有问题请与客服人员联系，我们将在第一时间为您服务！";
						$this->wechat->tplmsg ( $notify->data ['openid'], "i7R5JoEXN5ySF3l2F0N30qW24FXgkZFuTd8ZpWbpbvI", $url, $data );
							
						/* 发送通知模板告知上级用户 */
						$puid = M('auth_group_access')->where("uid=".$info['uid'])->getField("puid");
						$openid = "";
						if($puid && $puid!=0){
							$openid = M('ucenter_member')->where("id=".$puid)->getField("openid");
						}
						$team_desc = get_team_desc($info['uid']);
						for($i=0;$i<count($team_desc);$i++){
							$openid = M('ucenter_member')->where("id=".$team_desc[$i])->getField("openid");
							if($openid){
								$url = "http://".C('WEB_SITE_DOMAIN')."/user/team/uid/".$info['uid'];
								$data ['first'] = "您的会员:UID:".$info['uid'].",昵称:".get_nickname($info['uid'])."已成功支付了一笔订单！";
								$data ['orderMoneySum'] = ($notify->data ['total_fee'] / 100) . "元";
								$data ['orderProductName'] = "订单编号" . $info ['id']."，产品：".$str;
								$data ['Remark'] = "更多详情请进入我的团队查询...";
								$this->wechat->tplmsg ( $openid, "i7R5JoEXN5ySF3l2F0N30qW24FXgkZFuTd8ZpWbpbvI", $url, $data );
							}
						}
						echo $returnXml;
					}
				}
			}
		}
	}
	
	/**
	 * 余额支付
	 *
	 * @param unknown $oder_id        	
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function Yepay($order_id) {
		if (! is_login ()) {
			Cookie ( '__furl__', "/" . CONTROLLER_NAME . "/" . ACTION_NAME );
			$this->redirect ( "User/login" );
			exit ();
		}
		$where ['id'] = $order_id;
		$info = M ( 'Order' )->where ( $where )->find ();
		if ($info ['status'] != 0) {
			$this->error ( "错误！！订单已经支付成功！", U ( 'User/myorder' ) );
		}
		$this->assign ( "info", $info );
		
		$money = M ( 'Member' )->where ( 'uid=' . is_login () )->getField ( "money" );
		$this->assign ( "yue", $money );
		$this->assign ( "face", get_face () );
		
		$this->display ();
	}
	public function Yepay_notify($order_id) {
		$where ['id'] = $order_id;
		$info = M ( "Order" )->where ( $where )->find ();
		
		$password = I ( 'post.password' );
		$password = think_encrypt ( $password );
		$userinfo = M ( 'ucenter_member' )->where ( "id=" . is_login () )->find ();
		if ($userinfo ['paypass'] === $password) {
			$money = M ( 'Member' )->where ( 'uid=' . $userinfo ['id'] )->getField ( "money" );
			if ($money >= $info ['price']) {
				
				$newmoney = $money - $info ['price'];
				$status = M ( 'Member' )->where ( 'uid=' . $userinfo ['id'] )->setField ( "money", $newmoney );
				if ($status) {
					$res = M ( 'Order' )->where ( $where )->setField ( "status", 1 );
					if ($res) {
						
						$data ['uid'] = $info['uid']; // 用户
						$data['puid'] = $info['uid'];
						$data['order_id'] = $info['id'];
						$data['pro_id'] = 0;
						$data['length'] = 0;
						$data['add_time'] = NOW_TIME;
						$data['money'] = $info['price'];
						$data['money_type'] = 5;
						M('MoneyLog')->add($data);
						
						
						//处理包邮卡券
						/* 添加优惠券使用信息 */
						//优惠券
							
						if(isset($info['hb_id']) && $info['hb_id']>0){
							if($info['hb_type']=="userhb"){//用户红包
								$hbmap = array();
								$hbmap['id'] = $info['hb_id'];
								$hbmap['uid'] = $info['uid'];
								$savedata = array();
								$savedata['status'] = 0;
								$savedata['use_time'] = NOW_TIME;
								$savedata['order_id'] = $info['id'];
								M('userhb_log')->where($hbmap)->save($savedata);
							}else if($info['hb_type']=="syshb"){//系统红包
								$map1['id'] = $info['hb_id'];
								$cinfo = M('coupons_list')->where($map1)->find();
								$cdata['length'] = $cinfo['length']-1;
								if(!$cdata['length']){
									$cdata['status'] = 0;
								}
								M('coupons_list')->where($map1)->save($cdata);
								$logdata['pid'] = $info['hb_id'];
								$logdata['uid'] = $info['uid'];
								$logdata['times'] = 1;
								$logdata['money'] = $cinfo['money'];
								$logdata['create_time'] = NOW_TIME;
								M('coupons_list_log')->add($logdata);
							}
						}
						
						
						//包邮卡
						if(isset($info['byk']) && $info['byk']>0){
							$map1 = array();
							$map1['id'] = $info['byk'];
							$cinfo = M('coupons_list')->where($map1)->find();
							$cdata['length'] = $cinfo['length']-1;
							if(!$cdata['length']){
								$cdata['status'] = 0;
							}
							M('coupons_list')->where($map1)->save($cdata);
							$logdata['pid'] = $info['byk'];
							$logdata['uid'] = is_login();
							$logdata['times'] = 1;
							$logdata['money'] = $cinfo['money'];
							$logdata['create_time'] = NOW_TIME;
							M('coupons_list_log')->add($logdata);
						}
						
						echo "true";
						exit ();
					} else {
						/* 修改订单状态失败，退回金额 */
						M ( 'Member' )->where ( 'uid=' . $userinfo ['id'] )->setField ( "money", $money );
						echo "error";
					}
				} else {
					echo "error";
				}
			} else {
				/* 余额不足 */
				echo "nomoney";
			}
		} else {
			/* 支付密码不正确 */
			echo "notpassword";
		}
		exit ();
	}
	
	// 添加订单
	public function update($ids = '') {
		echo '<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport">';
		// $where['uid'] = is_login();
		// $cartinfo = M('Cart')->where($where)->select();
		$Cart = D ( 'Cart' );
		
		/* 收货信息 */
		$data = I ( 'post.' );
		/* 部分产品 */
		$ids = $data ['ids'];
		/* 基础信息 */
		$data ['payment'] = $data ['payway'];
		$data ['length'] = $Cart->get_count ( $ids, $data ['save_type'] );
		$data ['price'] = $Cart->get_money ( $ids, $data ['save_type'] );
		
		if(isset($data['cid']) && $data['cid']>0 && $data['gomoney']>0){
			$data['price']  = (($data['price']/100) - $data['gomoney'])*100;
		}
		
		/* 产品价格 */
		$data ['pro_price'] = $Cart->get_money ( $ids, $data ['save_type'] ) / 100;
		
		/* 收货地址 */
		$address = M('Address')->where("id=".$data['addid'])->find();
		if($address){
			$data['username'] = $address['nickname'];
			$data['tel'] = $address['tel'];
			$data['address'] = $address['province'].$address['city'].$address['area'].$address['address'];
		}else{
			$this->error("提交订单失败，请检查您的收货地址！");
			exit;
		}
		
		/* 运费计算 */
		if (empty ( $data ['express'] )) {
			$data ['express'] = M('freight')->getField("id");
		}
		$express_price = $this->auto_express_price ( $data ['express'], $data ['address'], $ids,$data ['save_type'] );
		if(!$express_price){
			$express_price = 0;
		}
		$data ['exp_price'] = $express_price;
		$data ['price'] = ($data ['price'] / 100) + $data ['exp_price'];
		if($data['price']<0){
			$data['price'] = 0;
		}
		$data['hb_id'] = $data['cid'];//添加红包信息
		if(isset($data['hb_type'])){
			if($data['hb_type']=="userhb"){
				$data['hb_type']=1;
			}else{
				$data['hb_type']=0;
			}
		}
		// 添加或修改
		$Order = D ( 'Order' ); // 调用模型
		$res = $Order->wcupdate ( $data );
		if ($res) {
			
			/* 清空回收站 TODO：清空指定产品 */
			$where ['uid'] = is_login ();
			$where ['id'] = array (
					'in',
					$ids 
			);
			M ( 'cart' )->where ( $where )->delete ();
			
			if($data['price']==0){
				
				//为0不用支付
				
				M('Order')->where("id=".$res)->setField("status",1);//修改订单状态
				
				$info = M ( 'Order' )->where ( "id=".$res )->find ();
				
				$orderlist = M('Orderlist')->where("order_id=".$info['id'])->select();
				for($i=0;$i<count($orderlist);$i++){
					$str[$i] = $orderlist[$i]['pro_id']."x".$orderlist[$i]['length'];
				}
				
				$str = implode(",",$str);
								
				
				/* 发送通知模板告知用户 */
				$this->wechat = new Wechat (); // 实例化 wechat 类
				$url = "http://".C('WEB_SITE_DOMAIN')."/user/myorder.html";
				$data = array();
				$data ['first'] = "我们已收到您的货款，开始为您打包商品，请耐心等待！";
				$data ['orderMoneySum'] = $info['price'] . "元";
				$data ['orderProductName'] = "订单编号" . $info ['id']."，产品：".$str;
				$data ['Remark'] = "如有问题请与客服人员联系，我们将在第一时间为您服务！";
				$this->wechat->tplmsg ( get_openid($info['uid']), "i7R5JoEXN5ySF3l2F0N30qW24FXgkZFuTd8ZpWbpbvI", $url, $data );
				/* 发送通知模板告知上级用户 */
				$puid = M('auth_group_access')->where("uid=".$info['uid'])->getField("puid");
				$openid = "";
				if($puid && $puid!=0){
					$openid = M('ucenter_member')->where("id=".$puid)->getField("openid");
				}
				if($openid){
					$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
					$data = array();
					$data ['first'] = "您的会员:".get_nickname($info['uid'])."已成功支付了一笔订单！";
					$data ['orderMoneySum'] = $info['price'] . "元";
					$data ['orderProductName'] = "订单编号" . $info ['id']."，产品：".$str;
					$data ['Remark'] = "更多详情请进入我的团队查询...";
					$this->wechat->tplmsg ( $openid, "i7R5JoEXN5ySF3l2F0N30qW24FXgkZFuTd8ZpWbpbvI", $url, $data );
				}
				$this->success ( "订单提交成功！", U("user/myorder") );
			}else{
				if ($data ['payment'] == "wechat") {
					/* 跳转支付 （切记注意大小写，与开发中心设置一致） */
					$url = U ( 'Wxpay?order_id=' . $res );
					$this->success ( "订单提交成功，正在为您跳转支付，请耐心等待！", $url );
				} else if ($data ['payment'] == "yue") {
					/* 余额支付 */
					$url = U ( 'Yepay?order_id=' . $res );
					$this->success ( "订单提交成功，正在为您跳转支付，请耐心等待！", $url );
				}
			}
		} else {
			$this->error ( "订单提交失败！" );
			exit ();
		}
	}
	
	
	public function jifen_order(){
		$post = I('post.');
		
		$address = M('address')->where("id=".$post['addid'])->find();
		$data['uid'] = is_login();
		$data['length'] = 1;
		$data['payment'] = 'jifen';
		$data['create_time'] = NOW_TIME;
		
		$Cart = D("Cart");
		$data['price'] = $Cart->get_money ( $post['ids'], $post ['save_type'] ) / 100;
		$data['pro_price'] = $data['price'];
		$data['exp_price'] = 0;
		$data['liuyan'] = $post['liuyan'];
		$data['status'] = 1;
		$data['username'] = $address['nickname'];
		$data['tel'] = $address['tel'];
		$data['address'] = $address['province'].$address['city'].$address['area'].$address['address'];
		$data['express'] = 1;
		
		$id = M('JifenOrder')->add($data);
		if($id){
			//编辑微信订单号
			$wechat = M('WechatConfig')->find();
			$editwcorder['wcorderid'] = $wechat['appID'] . $id;
			$edit_where['id'] = $id;
			M('JifenOrder')->where($edit_where)->save($editwcorder);
			
			$where['uid'] = is_login();
			$where['id'] = array("in",$post['ids']);
			$cartlist = M('Cart')->where($where)->select();
			for($i=0;$i<count($cartlist);$i++){
				$theArray['order_id'] = $id;
				$theArray['uid'] = is_login();
				$theArray['pro_id'] = $cartlist[$i]['pro_id'];
				$theArray['title'] = $cartlist[$i]['title'];
				$theArray['picture'] = picture($cartlist[$i]['cover_id']);
				$theArray['price'] = $cartlist[$i]['price'];
				$theArray['length'] = $cartlist[$i]['num'];
				$theArray['status'] = 1;
				$listid = M('jifen_orderlist')->add($theArray);
				/* 修改销量 */
				$pro_where['id'] = $theArray['pro_id'];
				M('Product')->where($pro_where)->setInc("xiaoliang",$theArray['length']);
			}
			
			//扣除积分
			$res = M('Member')->where("uid=".$data['uid'])->setDec("jifen",$data['price']);

			if($res){
				$this->success("积分兑换成功！",U('User/jifen_order'));
			}else{
				M('JifenOrder')->delete($id);
				$this->error("积分兑换失败！");
			}
			
		}
		
	}
	
	
	/**
	 * 单品包邮
	 */
	public function onepro_freight($address,$id){
		$proid = M('Cart')->where("id=".$id)->getField("pro_id");
		$info = D('Product')->detail($proid);
		if($info['freight_status']==1){
			$yes = false;
			$list = explode(",",$info['contain']);
			for($j=0;$j<count($list);$j++){
				if(stristr($address,$list[$j])){
					$yes = 1;
					continue;
				}
			}
			if(!$yes){
				$list = explode(",",$info['contain1']);
				for($j=0;$j<count($list);$j++){
					if(stristr($address,$list[$j])){
						$yes = 2;
						continue;
					}
				}
			}
			if(!$yes){
				$list = explode(",",$info['contain2']);
				for($j=0;$j<count($list);$j++){
					if(stristr($address,$list[$j])){
						$yes = 3;
						continue;
					}
				}
			}
			if($yes){
				switch ($yes){
					case 1:
						$price= $info['contain_price'];
						break;
					case 2:
						$price= $info['contain1_price'];
						break;
					case 3:
						$price= $info['contain2_price'];
						break;
				}
			}else{
				$price= $info['nocontain_price'];
			}
			return $price;
		}else{
			return false;
		}
	}
	
	public function auto_express_price($id, $address, $ids = '',$save_type=0) {
		
		if($id=="baoyou"){
			return 0;
			exit;
		}
		
		if($id=="coupons"){
			return 0;
			exit;
		}
		
		//单品包邮
		$pros = explode(",",$ids);
		if(count($pros)==1){
			$onepro = $this->onepro_freight($address,$ids);
			if($onepro){
				return $onepro;
				exit;
			}
		}
		
		$Cart = D ( 'Cart' );
		$money = $Cart->get_money ( $ids,$save_type );
		$money = $money / 100;
		$where ['id'] = $id;
		$info = M ( 'freight' )->where ( $where )->find ();
		if (! $info) {
			$this->error ( "快递信息有误，请与在线客服联系！" );
			exit;
		}
		$yes = false;
		$list = explode ( ",", $info ['contain'] );
		for($j = 0; $j < count ( $list ); $j ++) {
			if (stristr ( $address, $list [$j] )) {
				$yes = 1;
				continue;
			}
		}
		if(!$yes){
			$list = explode ( ",", $info ['contain1'] );
			for($j = 0; $j < count ( $list ); $j ++) {
				if (stristr ( $address, $list [$j] )) {
					$yes = 2;
					continue;
				}
			}
		}
		if(!$yes){
			$list = explode ( ",", $info ['contain2'] );
			for($j = 0; $j < count ( $list ); $j ++) {
				if (stristr ( $address, $list [$j] )) {
					$yes = 3;
					continue;
				}
			}
		}
		if ($yes) {
			switch ($yes) {
				case 1 :
					$price = $info ['contain_price'];
					break;
				case 2 :
					$price = $info ['contain1_price'];
					break;
				case 3 :
					$price = $info ['contain2_price'];
					break;
			}
		} else {
			$price = $info ['nocontain_price'];
		}
		return $price;
	}
	public function jumppay($orderid) {
		echo "<meta content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;\" name=\"viewport\" />";
		$url = U ( 'wxpay' ) . "?order_id=" . $orderid;
		$this->success ( "订单提交成功，正在为您跳转支付，请耐心等待。", $url );
	}
}