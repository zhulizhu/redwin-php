<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Common\Api\ExpressApi;
use Common\Controller\Wechat;

/**
 * 后台用户控制器
 * 
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class JforderController extends AdminController {
	
	/**
	 * 用户管理首页
	 * 
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function index($action = "show",$p = 1 ) {
		$uid = is_login ();
		$status = $_GET [status];
		if (! $uid) {
			$this->error ( '您还没有登录', U ( 'User/login' ) );
		}
		$map = array ();
		$map ["status"] = array (
				'neq',
				- 1 
		);
		
		
		
		$t = strtotime ( "-90 days" );
		if (isset ( $_GET ['status'] )) {
			if($_GET['status']=="dfh"){
				$map['expressnum'] = array("eq",0);
			}
			if($_GET['status']=="dsh"){
				$map['expressnum'] = array("gt",0);
			}
			if($_GET['status']=="ywc"){
				$map ['status'] = 2;
			}
		}
		if (I ( 'search_order' ) && $_POST [search_order] != "请输入订单编号") {
			$map ["id"] = I ( 'search_order' );
		}
		if (I ( 'search_uid' ) && $_POST [search_uid] != "请输入用户编号") {
			$map ["uid"] = I ( 'search_uid' );
		}
		
		if(I('time-start') && I('time-end') &&  $_POST['time-start']!="结束时间" && $_POST['time-start']!="起始时间"){
			$map['create_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
		}
		$order = "expressnum asc,create_time desc";
		$this->assign ( "orderby", $orderby );
		session('owhere',$map);
		$ord = $this->lists ( 'JifenOrder', $map, $order );
		for($i = 0; $i < count ( $ord ); $i ++) {
			$user = M ( 'UcenterMember' )->where ( 'id=' . $ord [$i] ['uid'] )->find ();
			$name = M ( 'jifen_orderlist' )->where ( array (
					'order_id' => $ord [$i] ['id'] 
			) )->select ();
			for($j = 0; $j < count ( $name ); $j ++) {
				$ord [$i] ['suborder'] [$j] ["id"] = $name [$j] ['id'];
				$ord [$i] ['suborder'] [$j] ["pic"] = $name [$j] ['picture'];
				$ord [$i] ['suborder'] [$j] ["pro_id"] = $name [$j] ['pro_id'];
				$ord [$i] ['suborder'] [$j] ["title"] = $name [$j] ['title'];
				$ord [$i] ['suborder'] [$j] ["price"] = $name [$j] ['price'];
				$ord [$i] ['suborder'] [$j] ["length"] = $name [$j] ['length'];
			}
			$ord [$i] ['userinfo'] = $user;
		}
		
		$this->assign ( 'ord', $ord );
		$this->assign ( 'status', $status );
		
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		
		
		//处理价格
		$countprice = M ( "JifenOrder" )->where ( $map )->field('id,price,pro_price,exp_price,status')->select();
		$price = 0;
		$pro_price = 0;
		$exp_price = 0;
		$count = 0;
		for($i=0;$i<count($countprice);$i++){
			if($countprice[$i]['status']>0 && $countprice[$i]['status']<5){
				$count += 1;
				$price = $price +  $countprice[$i]['price'];
				$pro_price += $countprice[$i]['pro_price'];
				$exp_price += $countprice[$i]['exp_price'];
			}
		}
		
		$this->assign ( "ordercount", $count );
		$this->assign ( "countprice", $price );
		$this->assign("pro_price",$pro_price);
		$this->assign("exp_price",$exp_price);
		
		$this->meta_title = '订单信息';
		if (groupid > 11 && groupid < 14) {
			$this->display ( "show_order" );
		} else {
			$this->display ();
		}
	}
	
	
	public function closetk($id){
		
		$where['id'] = $id;
		$id = M('tuihuo')->where($where)->delete();
		if($id){
			$this->success("关闭成功！");
		}else{
			$this->error("关闭失败！");
		}
	}
	
	
	public function export(){
		
		$list = M('Order')->where(session('owhere'))->order('id desc')->limit(50)->select();
		
		
		$file_name =date('Y-m-d H:i:s',time())." 订单";
		header('Content-Type: text/xls');
		header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
		$str = mb_convert_encoding($file_name, 'gbk', 'utf-8');
		header('Content-Disposition: attachment;filename="' .$str . '.xls"');
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		
		$str = "";
		$str .= "<table>";
		for($i=0;$i<count($list);$i++){
			$str .= "<tr>";
			/* 第一列 */
			$str .= "<td>";
				$str .= "<table border='1'>";
				$str .= "<tr><td>ID</td></tr>";
				$str .= "<tr><td>".$list[$i]['uid']."</td></tr>";
				$str .= "<tr><td>订单号</td></tr>";
				$str .= "<tr><td>".$list[$i]['id']."</td></tr>";
				$str .= "<tr><td>总金额</td></tr>";
				$str .= "<tr><td>".$list[$i]['price']."</td></tr>";
				$str .= "<tr><td>邮费</td></tr>";
				$str .= "<tr><td>".$list[$i]['exp_price']."</td></tr>";
				$str .= "<tr><td>级别</td></tr>";
				$str .= "<tr><td>".$list[$i]['exp_price']."</td></tr>";
				$str .= "</table>";
			$str .= "</td>";
			
			/* 第二列 */
			$str .= "<td>";
				$str .= "<table border='1'>";
					$str .= "<tr><td>产品明细</td><td>数量</td></tr>";
					$orderlist = M("Orderlist")->where('order_id='.$list[$i]['id'])->select();
					for($j=0;$j<count($orderlist);$j++){
						$str .= "<tr>";
							$str .= "<td>".$orderlist[$j]['title']."</td><td>".$orderlist[$j]['length']."</td>";
						$str .= "</tr>";
					}
				$str .= "</table>";
			$str .= "</td>";
			
			/* 第三列 */
			if($list[$i]['status']>1 && $list[$i]['status']<4){
					$map = array();
					$map['order_id'] = $list[$i]['id'];
					$map['money_type'] = array("in","0,1");
					$moneylog = M('money_log')->where($map)->select();
					$str .= "<td>";
					$str .="<table border='1'>";
					for($j=0;$j<count($orderlist);$j++){
						if($j==0){
							for($k=0;$k<(count($moneylog)/count($orderlist));$k++){
								$g = get_group_by_uid($moneylog[$k]['uid']);
								
								$str .= "<td>UID:".$moneylog[$k]['uid']."&nbsp;".$g['title']."</td>";
							}
						}
						$str .="<tr>";
								for($k=0;$k<count($moneylog);$k++){
									if($moneylog[$k]['pro_id']==$orderlist[$j]['pro_id']){
										$str .="<td>".$moneylog[$k]['money']."</td>";
									}
								}
							$str .= "</tr>";
					}
					$str .="</table>";
					$str .= "</td>";
			}
			
			
			$str .= "</tr>";
			
			$str .= "<tr></tr><tr></tr><tr></tr>";
		}
		$str .= "</table>";
		
		echo $str;
		exit;
		
	}
	
	
	
	
	
	public function update_order() {
		if (IS_POST) {
			
			$data = I ( 'post.' );
			$id = M ( 'jifen_order' )->save ( $data );
			if ($id) {
				$this->success ( "修改成功！" );
			} else {
				$this->error ( "修改失败！" );
			}
		} else {
			$this->error ( "参数错误！" );
		}
		exit ();
	}
	
	/**
	 * 退换货列表
	 *
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function tuihuo() {
		$map ['status'] = 1;
		$ord = $this->lists ( 'tuihuo', $map, 'create_time desc' );

		for($i = 0; $i < count ( $ord ); $i ++) {
			$thtype = $ord[$i]['thtype'];
			$thid = $ord[$i]['id'];
			$orderid = $ord[$i]['order_id'];
			$temp = M ( 'order' )->where ( 'id=' . $ord [$i] ['order_id'] )->find ();
			$ord [$i] ['thid'] = $ord [$i] ['id'];
			$ord [$i] = array_merge ( $ord [$i], $temp );
			
			if($ord[$i]['thtype']=="dp"){
				$name = M ( 'tuihuo_list' )->where ( array ('pid' => $ord [$i] ['thid']) )->select ();
				$money = 0;
				for($j = 0; $j < count ( $name ); $j ++) {
					$orderinfo = M('Orderlist')->where("id=".$name[$j]['listid'])->find();
					$ord [$i] ['suborder'] [$j] ["id"] = $name[$j]['id'];
					$ord [$i] ['suborder'] [$j] ["pic"] = $orderinfo['picture'];
					$ord [$i] ['suborder'] [$j] ["pro_id"] = $orderinfo['pro_id'];
					$ord [$i] ['suborder'] [$j] ["title"] = $orderinfo['title'];
					$ord [$i] ['suborder'] [$j] ["price"] = $orderinfo['price'];
					$ord [$i] ['suborder'] [$j] ["length"] = $name[$j]['length'];
					$money +=  $name[$j]['length'] * $orderinfo['price'];
				}
			}else{
				$name = M ( 'orderlist' )->where ( array ('order_id' => $ord [$i] ['id']) )->select ();
				for($j = 0; $j < count ( $name ); $j ++) {
					$ord [$i] ['suborder'] [$j] ["id"] = $name [$j] ['id'];
					$ord [$i] ['suborder'] [$j] ["pic"] = $name [$j] ['picture'];
					$ord [$i] ['suborder'] [$j] ["pro_id"] = $name [$j] ['pro_id'];
					$ord [$i] ['suborder'] [$j] ["title"] = $name [$j] ['title'];
					$ord [$i] ['suborder'] [$j] ["price"] = $name [$j] ['price'];
					$ord [$i] ['suborder'] [$j] ["length"] = $name [$j] ['length'];
				}
			}
			$user = M ( 'UcenterMember' )->where ( 'id=' . $ord [$i] ['uid'] )->find ();
			$ord [$i] ['userinfo'] = $user;
			
			if($ord[$i]['thtype']=="dp"){
				$ord[$i]['thprice'] = $money;
			}else if($ord[$i]['thtype']=="allpro"){
				$ord[$i]['thprice'] = $ord[$i]['price'];
			}else{
				$ord[$i]['thprice'] = $ord[$i]['exp_price'];
			}
		}
		$this->assign ( 'ord', $ord );
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		$this->meta_title = '订单信息';
		$this->display ( "admin-tuihuo" );
	}
	
	public function update_thlist($id,$num){
		
		$id = M('tuihuo_list')->where("id=".$id)->setField("length",$num);
		if($id){
			echo "1";
		}else{
			echo "0";
		}
		exit;
	}
	
	/**
	 * 退换货记录
	 *
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function jilu() {
		$map ['status'] = array (
				"neq",
				1 
		);
		$ord = $this->lists ( 'tuihuo', $map, 'create_time desc' );
		for($i = 0; $i < count ( $ord ); $i ++) {
			$status = $ord [$i] ['status'];
			$temp = M ( 'order' )->where ( 'id=' . $ord [$i] ['order_id'] )->find ();
			$ord [$i] ['thid'] = $ord [$i] ['id'];
			$ord [$i] = array_merge ( $ord [$i], $temp );
			$user = M ( 'UcenterMember' )->where ( 'id=' . $ord [$i] ['uid'] )->find ();
			$name = M ( 'orderlist' )->where ( array (
					'order_id' => $ord [$i] ['order_id'] 
			) )->select ();
			for($j = 0; $j < count ( $name ); $j ++) {
				$ord [$i] ['suborder'] [$j] ["id"] = $name [$j] ['id'];
				$ord [$i] ['suborder'] [$j] ["pic"] = $name [$j] ['picture'];
				$ord [$i] ['suborder'] [$j] ["pro_id"] = $name [$j] ['pro_id'];
				$ord [$i] ['suborder'] [$j] ["title"] = $name [$j] ['title'];
				$ord [$i] ['suborder'] [$j] ["price"] = $name [$j] ['price'];
				$ord [$i] ['suborder'] [$j] ["length"] = $name [$j] ['length'];
			}
			switch ($status) {
				case 0 :
					$ord [$i] ['status'] = "已操作";
					break;
				case 1 :
					$ord [$i] ['status'] = "正在申请";
					break;
				case 2 :
					$ord [$i] ['status'] = "全额退款";
					break;
				case 3 :
					$ord [$i] ['status'] = "退产品款";
					break;
				case 4 :
					$ord [$i] ['status'] = "退邮费";
					break;
			}
			$ord [$i] ['userinfo'] = $user;
		}
		$this->assign ( 'ord', $ord );
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		$this->meta_title = '订单信息';
		$this->display ( "tuihuojilu" );
	}
	
	/**
	 * 退换货退款
	 * 
	 * @param unknown $id        	
	 * @param unknown $type        	
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function tuikuan($id, $type) {
		$info = M ( 'tuihuo' )->where ( "id=" . $id )->find ();
		
		$orderinfo = M ( 'order' )->where ( "id=" . $info ['order_id'] )->find ();
		
		import ( 'Common.Wxpay.WxPayPubHelper' );
		import ( 'Common.Wxpay.WxPaypubconfig' );
		$pay = new \Commonutilpub ();
		
		switch ($type) {
			case 0:
    			/* 退全款 */
    			$Obj ['refund_fee'] = intval($orderinfo ['price'] * 100); // 退款总金额
				$status = 2;
				$text = "退全款";
				break;
			case 1:
    			/* 仅退产品款 */
				$list = M('tuihuo_list')->where("pid=".$id)->select();
				$thmoney = 0;
				for($i=0;$i<count($list);$i++){
					$listinfo = M('orderlist')->where("id=".$list[$i]['listid'])->find();
					$list[$i]['pro_id'] = $listinfo['pro_id'];
					$thmoney += $listinfo['price'] * $list[$i]['length']; 
				}
    			$Obj ['refund_fee'] = intval($thmoney * 100); // 退款总金额
				$status = 3;
				$text = "退产品款";
				break;
			case 2:
    			/* 仅退邮费 */
    			$Obj ['refund_fee'] = intval($orderinfo ['exp_price'] * 100); // 退款总金额
				$status = 4;
				$text = "退邮费";
				break;
		}
		
		
		
		/* 微信退款接口 */
		$Obj ['appid'] = \WxPaypubconfig::APPID; // APPID
		$Obj ['mch_id'] = \WxPaypubconfig::MCHID; // 商户号
		$Obj ['nonce_str'] = \WxPaypubconfig::KEY; // 随机字符串
		
		$Obj ['transaction_id'] = $orderinfo ['transaction_id']; // 商户订单号
		$Obj ['out_refund_no'] = $orderinfo ['transaction_id']; // 退款订单号（新建传送给微信）
		$Obj ['total_fee'] = intval($orderinfo ['price'] * 100); // 订单总金额
		$Obj ['refund_fee_type'] = "CNY"; // 币种
		$Obj ['op_user_id'] = \WxPaypubconfig::MCHID; // 操作员
		$Obj ['sign'] = $pay->getSign ( $Obj );
		$xml = $pay->arrayToXml ( $Obj );
		$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		$result = $pay->postXmlSSLCurl ( $xml, $url );
		$result = $pay->xmlToArray ( $result );
		if ($result ['result_code'] == "SUCCESS") {
			if ($type == 2) {
				$ts = 6;
			} else {
				$ts = 5;
			}
			M ( 'Order' )->where ( "id=" . $info ['order_id'] )->setField ( "status", $ts ); // 设置订单未已退款
			$save_data ['status'] = $status;
			$save_data ['price'] = $Obj ['refund_fee'];
			$save_data ['create_time'] = NOW_TIME;
			M ( 'tuihuo' )->where ( "id=" . $id )->save ( $save_data ); // 设置为已退款
			
			if($type==0){//全额退款
				/* 退款退分佣 */
				$map['order_id'] = $orderinfo['id'];
				$map['puid'] = $orderinfo['uid'];
				$map['money_type'] = array("in","0,1");
				$moneylist = M('MoneyLog')->where($map)->field("uid,money,order_id,puid,money_type")->select();
				for($i=0;$i<count($moneylist);$i++){
					if($moneylist[$i]['money_type']==0){
						$money = M('Member')->where("uid=".$moneylist[$i]['uid'])->getField("xxmoney");
						$money = $money - $moneylist[$i]['money'];
						M('Member')->where("uid=".$moneylist[$i]['uid'])->setField("xxmoney",$money);
					}else if($moneylist[$i]['money_type']==1){
						$money = M('Member')->where("uid=".$moneylist[$i]['uid'])->getField("money");
						$money = $money - $moneylist[$i]['money'];
						M('Member')->where("uid=".$moneylist[$i]['uid'])->setField("money",$money);
					}
					$moneylist[$i]['add_time'] = NOW_TIME;
					$moneylist[$i]['money_type'] = 3;//回收
					M('MoneyLog')->add($moneylist[$i]);//增加记录
				}
			}
			
			if($type==1){//退产品款
					
				$loglist = M('MoneyLog')->where("order_id=".$orderinfo['id'])->select();
				for($i=0;$i<count($loglist);$i++){
			
					for($j=0;$j<count($list);$j++){
							
						if($list[$j]['pro_id']==$loglist[$i]['pro_id']){
			
							$koumoney = ($loglist[$i]['money'] / $loglist[$i]['length']) * $list[$j]['length'];
							if($koumoney){
								if($loglist[$i]['money_type']==0){
									$money = 0;
									$money = M('Member')->where("uid=".$loglist[$i]['uid'])->getField("xxmoney");
									$money = $money - $koumoney;
									M('Member')->where("uid=".$loglist[$i]['uid'])->setField("xxmoney",$money);
								}else if($moneylist[$i]['money_type']==1){
									$money = 0;
									$money = M('Member')->where("uid=".$loglist[$i]['uid'])->getField("money");
									$money = $money - $koumoney;
									M('Member')->where("uid=".$loglist[$i]['uid'])->setField("money",$money);
								}
								
								$data = $loglist[$i];
								unset($data['id']);
								$data['money'] = $koumoney;
								$data['money_type'] = 3;//退货回收
								$data['add_time'] = NOW_TIME;
								M('MoneyLog')->add($data);
							}
						}
							
					}
			
				}
					
			}
			
			
			/* 通知用户已退款 */
			$this->wechat = new Wechat (); // 实例化 wechat 类
			$url = "http://" . C ( 'WEB_SITE_DOMAIN' ) . "/user/myorder.html";
			$data ['first'] = "您的订单编号:" . $orderinfo ['id'] . " 已" . $text . "！";
			$data ['reason'] = "顾客申请退款";
			$data ['refund'] = $Obj ['refund_fee'] / 100 . "元";
			$data ['remark'] = "如有问题与客服人员联系，我们将在第一时间为您服务！";
			$openid = M ( 'ucenter_member' )->where ( "id=" . $orderinfo ['uid'] )->getField ( "openid" );
			$this->wechat->tplmsg ( $openid, "Fi3aUMEmWk6kAybgHSdQjeVu2iuOp8iWx_mNMzJJiQA", $url, $data );
			$this->success ( "退款成功！" );
		} else {
			$this->error ( "退款失败！" );
		}
	}
	public function kdd($id) {
		
		$where['order_id'] = array("in",$id);
		
		$ids = explode(",",$id);
		
		$info = M ( 'jifen_order' )->where ( "id=" . $ids[0] )->find ();
		$info ['list'] = M ( 'jifen_orderlist' )->where ( $where )->select ();
		if (count ( $info ['list'] ) > 0) {
			$money = 0;
			foreach ( $info ['list'] as $val ) {
				$length [] = $val ['length'];
				$money = $money + $val['price'] * $val['length'];
			}
			$length = array_sum ( $length );
		}
		$this->assign("money",$money);
		$this->assign ( "length", $length );
		$this->assign ( "info", $info );
		$this->display ();
	}
	public function clth($id) {
		$res = M ( 'tuihuo' )->where ( 'id=' . $id )->setField ( "status", 0 );
		if ($res) {
			$this->success ( "已处理！" );
		} else {
			$this->error ( "处理失败！" );
		}
		exit ();
	}
	public function update_express() {
		if (IS_POST) {
			$data = I ( 'post.' );
			$data ['express_time'] = NOW_TIME;
			$info = M ( 'jifen_order' )->save ( $data );
			$this->success ( "修改快递信息成功！" );
		} else {
			$this->error ( "非法访问" );
		}
	}
	public function show_express($id) {
		$where ['id'] = $id;
		$info = M ( 'jifen_order' )->where ( $where )->find ();
		if (empty ( $info ['express_com'] ) || empty ( $info ['expressnum'] )) {
			$this->assign ( "error", 1 );
		} else {
			$e = new ExpressApi ();
			$result = $e->getorder ( $info ['express_com'], $info ['expressnum'] );
			$this->assign ( "info", $info );
			$this->assign ( "result", $result );
		}
		$this->display ();
	}
	
	/**
	 * 回收站列表
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function recycle() {
		$map ['status'] = -1;
		
		if (isset ( $_GET ['orderby'] ) && $_GET ['orderby'] != "") {
			$orderby = $_GET ['orderby'];
			$order = $_GET ['orderby'] . " desc";
		} else {
			$orderby = "";
			$order = "expressnum asc,create_time desc";
		}
		$this->assign ( "orderby", $orderby );
		
		$ord = $this->lists ( M ( 'Order' ), $map, $orderby );
		for($i = 0; $i < count ( $ord ); $i ++) {
			$user = M ( 'UcenterMember' )->where ( 'id=' . $ord [$i] ['uid'] )->find ();
			$name = M ( 'orderlist' )->where ( array (
					'order_id' => $ord [$i] ['id'] 
			) )->select ();
			for($j = 0; $j < count ( $name ); $j ++) {
				$ord [$i] ['suborder'] [$j] ["id"] = $name [$j] ['id'];
				$ord [$i] ['suborder'] [$j] ["pic"] = $name [$j] ['picture'];
				$ord [$i] ['suborder'] [$j] ["pro_id"] = $name [$j] ['pro_id'];
				$ord [$i] ['suborder'] [$j] ["title"] = $name [$j] ['title'];
				$ord [$i] ['suborder'] [$j] ["price"] = $name [$j] ['price'];
				$ord [$i] ['suborder'] [$j] ["length"] = $name [$j] ['length'];
			}
			$ord [$i] ['userinfo'] = $user;
		}
		
		$this->assign ( 'ord', $ord );
		
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		
		$this->meta_title = '回收站';
		$this->display ();
	}
	
	/**
	 * 清空回收站
	 * 
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function clearRecyle(){
	    $del = M('jifen_order')->where('status=-1')->delete();
	    if($del){
	        $this->success("清空完成");
	        exit();
	    }else{
	        $this->error("清空失败");
	        exit();
	    }
	}
	
	/**
	 * 批量删除订单
	 */
	public function changeStatus($method = null) {
		$id = array_unique ( ( array ) I ( 'id', 0 ) );
		$id = is_array ( $id ) ? implode ( ',', $id ) : $id;
		if (empty ( $id )) {
			$this->error ( '请选择要操作的数据!' );
		}
		$map ['id'] = array (
				'in',
				$id 
		);
		switch (strtolower ( $method )) {
			case 'deleteorder' :
				$res = M ( 'jifen_order' )->where ( $map )->setField ( "status", - 1 );
				if ($res) {
					$this->success ( "删除成功！" );
					exit ();
				} else {
					$this->error ( "删除失败！" );
					exit ();
				}
				break;
			case 'backorder':
				$res = M ( 'jifen_order' )->where ( $map )->setField ( "status", 0 );
				if ($res) {
					$this->success ( "还原成功！" );
					exit ();
				} else {
					$this->error ( "还原失败！" );
					exit ();
				}
				break;
			default :
				$this->error ( '参数非法' );
		}
	}
}
