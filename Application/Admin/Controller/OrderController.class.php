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
class OrderController extends AdminController {
	
	/**
	 * 用户管理首页
	 *
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function index($action = "show", $p = 1) {
		$uid = is_login ();
		$status = $_GET [status];
		if (! $uid) {
			$this->error ( '您还没有登录', U ( 'User/login' ) );
		}
		switch ($action) {
			case "delete" :
				if (IS_GET) {
					$id = $_GET ['id'];
					$res = M ( 'order' )->where ( 'id=' . $id )->setField ( "status", - 1 );
					if ($res) {
						$this->success ( "删除成功！" );
						exit ();
					} else {
						$this->error ( "删除失败！" );
						exit ();
					}
				}
				
				break;
		}
		$map = array ();
		$map ["status"] = array (
				'neq',
				- 1 
		);
		
		$t = strtotime ( "-90 days" );
		if (isset ( $_GET ['status'] )) {
			$map ['status'] = $_GET ['status'];
		}
		if (I ( 'search_order' ) && $_POST [search_order] != "请输入订单编号") {
			$map ["id"] = I ( 'search_order' );
		}
		if (I ( 'search_uid' ) && $_POST [search_uid] != "请输入用户编号") {
			$map ["uid"] = I ( 'search_uid' );
		}
		
		if (I ( 'time-start' ) && I ( 'time-end' ) && $_POST ['time-start'] != "结束时间" && $_POST ['time-start'] != "起始时间") {
			$map ['create_time'] = array (
					"between",
					strtotime ( I ( 'time-start' ) ) . "," . strtotime ( I ( 'time-end' ) ) 
			);
		}
		
		if (I ( 'shtime-start' ) && I ( 'shtime-end' )) {
			$map ['update_time'] = array (
					"between",
					strtotime ( I ( 'shtime-start' ) ) . "," . strtotime ( I ( 'shtime-end' ) ) 
			);
		}
		
		if (I ( 'search_price' ) && $_POST ['search_price'] != "请输入消费金额") {
			$map ['price'] = array (
					"gt",
					I ( 'search_price' ) 
			);
		}
		
		if (I ( 'xiaoyu_price' ) && $_POST ['xiaoyu_price'] != "请输入消费金额") {
			$map ['price'] = array (
					"lt",
					I ( 'xiaoyu_price' ) 
			);
		}
		
		if (isset ( $_GET ['status'] ) && $_GET ['status'] == 3) {
			$map ['status'] = array (
					"in",
					"2,3,6" 
			);
		}
		
		if (isset ( $_GET ['team_id'] ) && $_GET ['team_id'] != "请输入要查询的用户ID") {
			$thislist = get_all_team ( $_GET ['team_id'] );
			$thislist [] = $_GET ['team_id'];
			$map ['uid'] = array (
					"in",
					implode ( ",", $thislist ) 
			);
		}
		
		if (groupid > 11 && groupid < 14) {
			$thislist = get_all_team ( is_login () );
			$thislist [] = is_login ();
			if (I ( 'search_uid' ) && $_POST ['search_uid'] != "请输入要查询的用户ID") {
				if (in_array ( I ( 'search_uid' ), $thislist )) {
					$map ['uid'] = I ( 'search_uid' );
				} else {
					$this->error ( "查询错误！" );
				}
			} else {
				$map ['uid'] = array (
						"in",
						implode ( ",", $thislist ) 
				);
			}
		}
		
		if (isset ( $_GET ['orderby'] ) && $_GET ['orderby'] != "") {
			$orderby = $_GET ['orderby'];
			$order = $_GET ['orderby'] . " desc";
		} elseif ($_GET ['create_time'] == "下单时间") {
			$order = "create_time desc";
		} elseif ($_GET ['update_time'] == "收货时间") {
			$order = "update_time desc";
		} else {
			$orderby = "";
			$order = "expressnum asc,create_time desc";
		}
		$this->assign ( "orderby", $orderby );
		
		session ( 'owhere', $map );
		
		
		
		$ord = $this->lists ( 'Order', $map, $order );
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
		$this->assign ( 'status', $status );
		
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		
		// 处理价格
		$countprice = M ( "Order" )->where ( $map )->field ( 'id,price,pro_price,exp_price,status' )->select ();
		$price = 0;
		$pro_price = 0;
		$exp_price = 0;
		$count = 0;
		for($i = 0; $i < count ( $countprice ); $i ++) {
			if ($countprice [$i] ['status'] > 0 && $countprice [$i] ['status'] < 5 || $countprice [$i] ['status']==6) {
				$count += 1;
				$price = $price + $countprice [$i] ['price'];
				$pro_price += $countprice [$i] ['pro_price'];
				$exp_price += $countprice [$i] ['exp_price'];
			}
		}
		
		$this->assign ( "ordercount", $count );
		$this->assign ( "countprice", $price );
		$this->assign ( "pro_price", $pro_price );
		$this->assign ( "exp_price", $exp_price );
		
		$this->meta_title = '订单信息';
		if (groupid > 11 && groupid < 14) {
			$this->display ( "show_order" );
		} else {
			$this->display ();
		}
	}
	public function closetk($id) {
		$where ['id'] = $id;
		$id = M ( 'tuihuo' )->where ( $where )->delete ();
		if ($id) {
			$this->success ( "关闭成功！" );
		} else {
			$this->error ( "关闭失败！" );
		}
	}
	public function export() {
		$where = session ( 'owhere' );
		$where ['status'] = array (
				"in",
				"1,2,3,6" 
		);

		$list = M ( 'Order' )->where ( $where )->order ( 'id desc' )->select ();
		$file_name = date ( 'Y-m-d H:i:s', time () ) . " 订单";
		header ( 'Content-Type: text/xls' );
		header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
		//$str = mb_convert_encoding ( $file_name, 'gbk', 'utf-8' );
		//$str = $file_name;
		header ( 'Content-Disposition: attachment;filename="' . $file_name . '.xls"' );
		header ( 'Cache-Control:must-revalidate,post-check=0,pre-check=0' );
		header ( 'Expires:0' );
		header ( 'Pragma:public' );
		$str = "<table>";
		for($i = 0; $i < count ( $list ); $i ++) {
			$str .= "<tr>";
			/* 第一列 */
			$str .= "<td>";
			$str .= "<table border='1'>";
			$str .= "<tr><td>ID</td></tr>";
			$str .= "<tr><td>" . $list [$i] ['uid'] . "</td></tr>";
			$str .= "<tr><td>订单号</td></tr>";
			$str .= "<tr><td>" . $list [$i] ['id'] . "</td></tr>";
			$str .= "<tr><td>总金额</td></tr>";
			$str .= "<tr><td>" . $list [$i] ['price'] . "</td></tr>";
			$str .= "<tr><td>邮费</td></tr>";
			$str .= "<tr><td>" . $list [$i] ['exp_price'] . "</td></tr>";
			$str .= "<tr><td>级别</td></tr>";
			$str .= "<tr><td>" . get_group_title_by_uid ( $list [$i] ['uid'] ) . "</td></tr>";
			$str .= "</table>";
			$str .= "</td>";
			
			$str .= "<td>";
			$str .= "<table border='1'>";
			$str .= "<tr><td>总金额</td></tr>";
			$str .= "<tr><td>" . $list [$i] ['price'] . "</td></tr>";
			$str .= "</table>";
			$str .= "</td>";
			
			/* 第二列 */
			$str .= "<td>";
			$str .= "<table border='1'>";
			$str .= "<tr><td>产品明细</td><td>数量</td></tr>";
			$orderlist = M ( "Orderlist" )->where ( 'order_id=' . $list [$i] ['id'] )->select ();
			for($j = 0; $j < count ( $orderlist ); $j ++) {
				$str .= "<tr>";
				$str .= "<td>" . $orderlist [$j] ['title'] . "</td><td>" . $orderlist [$j] ['length'] . "</td>";
				$str .= "</tr>";
			}
			$str .= "</table>";
			$str .= "</td>";
			$money = 0;
			$onenn = false;
			/* 第三列 */
			if ($list [$i] ['status']) {
			
				$map = array ();
				$map ['order_id'] = $list [$i] ['id'];
				$map ['money_type'] = array (
						"in",
						"0,1"
				);
				$moneylog = M ( 'money_log' )->where ( $map )->select ();
				$str .= "<td>";
				$str .= "<table border='1'>";
				
				$str .= "<tr>";
				for($z=0;$z<7;$z++){
					
					if($z==0){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
							if($g['id']==9){
								$g9=true;
								$str .= "<td>UID:".$moneylog[$k]['uid']."黄金会员</td>";
							}
						}
						if($g9==false){
							$str .= "<td>黄金会员</td>";
						}
					}
					if($z==1){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
							if($g['id']==10){
								$g9=true;
								$str .= "<td>UID:".$moneylog[$k]['uid']."铂金会员</td>";
							}
						}
						if($g9==false){
							$str .= "<td>铂金会员</td>";
						}
					}
					if($z==2){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
							if($g['id']==11){
								$g9=true;
								$str .= "<td>UID:".$moneylog[$k]['uid']."钻石会员</td>";
							}
						}
						if($g9==false){
							$str .= "<td>钻石会员</td>";
						}
					}
					if($z==3){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							if($moneylog[$k]['money_type']==1){
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==12){
									$g9=true;
									$str .= "<td>UID:".$moneylog[$k]['uid']."总监线上</td>";
								}
							}
						}
						if($g9==false){
							$str .= "<td>总监线上</td>";
						}
					}
					
					if($z==4){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							if($moneylog[$k]['money_type']==0){
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==12){
									$g9=true;
									$str .= "<td>UID:".$moneylog[$k]['uid']."总监线下</td>";
								}
							}
						}
						if($g9==false){
							$str .= "<td>总监线下</td>";
						}
					}
					if($z==5){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							if($moneylog[$k]['money_type']==1){
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==13){
									$g9=true;
									$str .= "<td>UID:".$moneylog[$k]['uid']."总经理线上</td>";
								}
							}
						}
						if($g9==false){
							$str .= "<td>总经理线上</td>";
						}
					}
					if($z==6){
						$g9 =false;
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							if($moneylog[$k]['money_type']==0){
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==13){
									$g9=true;
									$str .= "<td>UID:".$moneylog[$k]['uid']."总经理线下</td>";
								}
							}
						}
						if($g9==false){
							$str .= "<td>总经理线下</td>";
						}
					}
					
				}
				
				$str .= "</tr>";

				
				for($j = 0; $j < count ( $orderlist ); $j ++) {
					$str .= "<tr>";
					for($z=0;$z<7;$z++){
						if($z==0){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==9){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
						if($z==1){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==10){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
						if($z==2){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==11){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
						
						if($z==3){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==12 && $moneylog [$k]['money_type']==1){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
						if($z==4){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==12 && $moneylog [$k]['money_type']==0){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
						if($z==5){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==13 && $moneylog [$k]['money_type']==1){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
						if($z==6){
							$en = false;
							for($k = 0; $k < count ( $moneylog ); $k ++) {
								$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
								if($g['id']==13 && $moneylog [$k]['money_type']==0){
									if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
										$en = true;
										$money = $money + $moneylog [$k] ['money'];
										$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
									}
								}
							}
							if(!$en){
								$str .= "<td></td>";
							}
						}
					}
					
					$str .= "</tr>";
				}
				
				$str .= "</table>";
				$str .= "</td>";

				/*
				$map = array ();
				$map ['order_id'] = $list [$i] ['id'];
				$map ['money_type'] = array (
						"in",
						"0,1" 
				);
				$moneylog = M ( 'money_log' )->where ( $map )->select ();
				$str .= "<td>";
				$str .= "<table border='1'>";
				for($j = 0; $j < count ( $orderlist ); $j ++) {
					if ($j == 0) {
						for($k = 0; $k < (count ( $moneylog ) / count ( $orderlist )); $k ++) {
							$g = get_group_by_uid ( $moneylog [$k] ['uid'] );
							if ($moneylog [$k] ['uid'] == 199)
								$onenn = true;
							if($moneylog [$k]['money_type']==1){
								$str .= "<td>UID:" . $moneylog [$k] ['uid'] . "&nbsp;" . $g ['title'] . "线上</td>";
							}else{
								$str .= "<td>UID:" . $moneylog [$k] ['uid'] . "&nbsp;" . $g ['title'] . "线下</td>";
							}
						}
					}
					$str .= "<tr>";
					for($k = 0; $k < count ( $moneylog ); $k ++) {
						if ($moneylog [$k] ['pro_id'] == $orderlist [$j] ['pro_id']) {
							$money = $money + $moneylog [$k] ['money'];
							$str .= "<td>" . $moneylog [$k] ['money'] . "</td>";
						}
					}
					$str .= "</tr>";
				}
				$str .= "</table>";
				$str .= "</td>";
				*/
			}

			$onenn = true;
			$str .= "<td>";
			$str .= "<table border='1'>";
			$str .= "<tr><td>199求和</td></tr>";
			if ($onenn) {
				$qhmoeny = 0;
				for($j = 0; $j < count ( $moneylog ); $j ++) {
					if ($moneylog [$j] ['uid'] == 199) {
						if ($moneylog [$j] ['money_type'] == 0 || $moneylog [$j] ['money_type'] == 1) {
							$qhmoeny = $qhmoeny + $moneylog [$j] ['money'];
						}
					}
				}
				$qhmoeny = round ( $qhmoeny, 2 );
				$str .= "<tr><td>" . $qhmoeny . "</td></tr>";
			} else {
				$str .= "<tr><td></td></tr>";
			}
			$str .= "</table>";
			$str .= "</td>";
			$str .= "<td>";
			$str .= "<table border='1'>";
			$str .= "<tr><td>总监-总经理级差</td></tr>";
			if ($onenn) {
				$jche = 0;
				for($j = 0; $j < count ( $orderlist ); $j ++) {
					$pro_info = $this->get_price ( $orderlist [$j] ['pro_id'], $orderlist [$j] ['order_id'] );
					$fgs = ($pro_info ['fgs_price'] * $pro_info ['fgs_lirunbi'] / 100) * $orderlist [$j] ['length']; // 分公司总经理
					$fgs = round ( $fgs, 2 );
					$jche = $jche + $fgs;
					$str .= "<tr><td>" . $fgs . "</td></tr>";
				}
				$str .= "<tr><td>级差和：" . $jche . "</td></tr>";
			} else {
				$str .= "<tr><td></td></tr>";
			}
			$str .= "</table>";
			$str .= "</td>";
			
			/* 第四列 */
			$str .= "<td>";
			$str .= "<table border='1'>";
			$str .= "<tr><td>公司所得</td></tr>";
			$str .= "<tr><td>" . ($list [$i] ['price'] - $money) . "</td></tr>";
			$str .= "</table>";
			$str .= "</td>";
			
			$str .= "</tr>";
			
			$str .= "<tr></tr><tr></tr><tr></tr>";
		}
		$str .= "</table>";
		
		echo $str;
		exit ();
	}

	public function get_price($id, $order_id) {
		$mmap ['proid'] = $id;
		$mmap ['status'] = 1;
		$pids = M ( 'MarketingList' )->where ( $mmap )->getField ( "pid", true );
		$hd = false;
		if ($pids) {
			$mwhere ['id'] = array (
					"in",
					implode ( ",", $pids ) 
			);
			$mw ['id'] = $order_id;
			$mwtime = M ( 'Order' )->where ( $mw )->getField ( "create_time" );
			$mwhere ['start_time'] = array (
					"lt",
					$mwtime 
			);
			$mwhere ['end_time'] = array (
					"gt",
					$mwtime 
			);
			
			$mid = M ( 'marketing' )->where ( $mwhere )->getField ( "id" );
			if ($mid) {
				$mmap1 ['pid'] = $mid;
				$mmap1 ['proid'] = $id;
				$pro_info = M ( 'MarketingList' )->where ( $mmap1 )->find ();
				$hd = true;
			}
		}
		
		if (! $hd) {
			$pro_info = D ( 'Product' )->find ( $id );
		}
		
		return $pro_info;
	}
	
	/**
	 * 已发货业务流程
	 */
	public function yfh() {
		$uid = is_login ();
		$status = $_GET [status];
		if (! $uid) {
			$this->error ( '您还没有登录', U ( 'User/login' ) );
		}
		switch ($action) {
			case "delete" :
				if (IS_GET) {
					$id = $_GET ['id'];
					$res = M ( 'order' )->where ( 'id=' . $id )->setField ( "status", - 1 );
					if ($res) {
						$this->success ( "删除成功！" );
						exit ();
					} else {
						$this->error ( "删除失败！" );
						exit ();
					}
				}
				
				break;
		}
		$map = array ();
		$map ["status"] = array (
				'eq',
				1 
		);
		$map ["expressnum"] = array (
				'neq',
				0 
		);
		$t = strtotime ( "-90 days" );
		if (isset ( $_GET ['status'] )) {
			$map ['status'] = $_GET ['status'];
		}
		if (I ( 'search_order' ) && $_POST [search_order] != "请输入订单编号") {
			$map ["id"] = I ( 'search_order' );
		}
		if (I ( 'search_uid' ) && $_POST [search_uid] != "请输入用户编号") {
			$map ["uid"] = I ( 'search_uid' );
		}
		
		if (I ( 'time-start' ) && I ( 'time-end' ) && $_POST ['time-start'] != "结束时间" && $_POST ['time-start'] != "起始时间") {
			$map ['create_time'] = array (
					"between",
					strtotime ( I ( 'time-start' ) ) . "," . strtotime ( I ( 'time-end' ) ) 
			);
		}
		
		if (I ( 'search_price' ) && $_POST ['search_price'] != "请输入消费金额") {
			$map ['price'] = array (
					"gt",
					I ( 'search_price' ) 
			);
		}
		
		if (groupid > 11 && groupid < 14) {
			$thislist = get_all_team ( is_login () );
			$thislist [] = is_login ();
			if (I ( 'search_uid' ) && $_POST ['search_uid'] != "请输入要查询的用户ID") {
				if (in_array ( I ( 'search_uid' ), $thislist )) {
					$map ['uid'] = I ( 'search_uid' );
				} else {
					$this->error ( "查询错误！" );
				}
			} else {
				$map ['uid'] = array (
						"in",
						implode ( ",", $thislist ) 
				);
			}
		}
		
		if (isset ( $_GET ['orderby'] ) && $_GET ['orderby'] != "") {
			$orderby = $_GET ['orderby'];
			$order = $_GET ['orderby'] . " desc";
		} else {
			$orderby = "";
			$order = "expressnum asc,create_time desc";
		}
		$this->assign ( "orderby", $orderby );
		
		$ord = $this->lists ( 'Order', $map, $order );
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
		$this->assign ( 'status', $status );
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		
		// 处理价格
		$countprice = M ( "Order" )->where ( $map )->field ( 'id,price,pro_price,exp_price,status' )->select ();
		$price = 0;
		$pro_price = 0;
		$exp_price = 0;
		$count = 0;
		for($i = 0; $i < count ( $countprice ); $i ++) {
			if ($countprice [$i] ['status'] > 0 && $countprice [$i] ['status'] < 5) {
				$count += 1;
				$price = $price + $countprice [$i] ['price'];
				$pro_price += $countprice [$i] ['pro_price'];
				$exp_price += $countprice [$i] ['exp_price'];
			}
		}
		
		$this->assign ( "ordercount", $count );
		$this->assign ( "countprice", $price );
		$this->assign ( "pro_price", $pro_price );
		$this->assign ( "exp_price", $exp_price );
		
		$this->meta_title = '订单信息';
		if (groupid > 11 && groupid < 14) {
			$this->display ( "show_order" );
		} else {
			$this->display ();
		}
	}
	
	/**
	 * 待收货
	 */
	public function dsh() {
		$uid = is_login ();
		$status = $_GET [status];
		if (! $uid) {
			$this->error ( '您还没有登录', U ( 'User/login' ) );
		}
		switch ($action) {
			case "delete" :
				if (IS_GET) {
					$id = $_GET ['id'];
					$res = M ( 'order' )->where ( 'id=' . $id )->setField ( "status", - 1 );
					if ($res) {
						$this->success ( "删除成功！" );
						exit ();
					} else {
						$this->error ( "删除失败！" );
						exit ();
					}
				}
				
				break;
		}
		$map = array ();
		$map ["status"] = array (
				'eq',
				1 
		);
		$map ['express_time'] = array (
				'eq',
				0 
		);
		
		$t = strtotime ( "-90 days" );
		if (isset ( $_GET ['status'] ) && $_GET ['status'] == "dsh") {
			$map ['status'] = $_GET ['status'];
			$map ['expressnum'] = array (
					'elt',
					0 
			);
		}
		
		if (I ( 'search_order' ) && $_POST [search_order] != "请输入订单编号") {
			$map ["id"] = I ( 'search_order' );
		}
		if (I ( 'search_uid' ) && $_POST [search_uid] != "请输入用户编号") {
			$map ["uid"] = I ( 'search_uid' );
		}
		
		if (I ( 'time-start' ) && I ( 'time-end' ) && $_POST ['time-start'] != "结束时间" && $_POST ['time-start'] != "起始时间") {
			$map ['create_time'] = array (
					"between",
					strtotime ( I ( 'time-start' ) ) . "," . strtotime ( I ( 'time-end' ) ) 
			);
		}
		
		if (I ( 'search_price' ) && $_POST ['search_price'] != "请输入消费金额") {
			$map ['price'] = array (
					"gt",
					I ( 'search_price' ) 
			);
		}
		
		if (groupid > 11 && groupid < 14) {
			$thislist = get_all_team ( is_login () );
			$thislist [] = is_login ();
			if (I ( 'search_uid' ) && $_POST ['search_uid'] != "请输入要查询的用户ID") {
				if (in_array ( I ( 'search_uid' ), $thislist )) {
					$map ['uid'] = I ( 'search_uid' );
				} else {
					$this->error ( "查询错误！" );
				}
			} else {
				$map ['uid'] = array (
						"in",
						implode ( ",", $thislist ) 
				);
			}
		}
		
		if (isset ( $_GET ['orderby'] ) && $_GET ['orderby'] != "") {
			$orderby = $_GET ['orderby'];
			$order = $_GET ['orderby'] . " desc";
		} else {
			$orderby = "";
			$order = "expressnum asc,create_time desc";
		}
		$this->assign ( "orderby", $orderby );
		
		$ord = $this->lists ( 'Order', $map, $order );
		
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
		$this->assign ( 'status', $status );
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		
		// 处理价格
		$countprice = M ( "Order" )->where ( $map )->field ( 'id,price,pro_price,exp_price,status' )->select ();
		$price = 0;
		$pro_price = 0;
		$exp_price = 0;
		$count = 0;
		for($i = 0; $i < count ( $countprice ); $i ++) {
			if ($countprice [$i] ['status'] > 0 && $countprice [$i] ['status'] < 5) {
				$count += 1;
				$price = $price + $countprice [$i] ['price'];
				$pro_price += $countprice [$i] ['pro_price'];
				$exp_price += $countprice [$i] ['exp_price'];
			}
		}
		
		$this->assign ( "ordercount", $count );
		$this->assign ( "countprice", $price );
		$this->assign ( "pro_price", $pro_price );
		$this->assign ( "exp_price", $exp_price );
		
		$this->meta_title = '订单信息';
		if (groupid > 11 && groupid < 14) {
			$this->display ( "show_order" );
		} else {
			$this->display ();
		}
	}
	public function test() {
		$list = M ( "Order" )->select ();
		for($i = 0; $i < count ( $list ); $i ++) {
			
			if ($list [$i] ['price'] != ($list [$i] ['pro_price'] + $list [$i] ['exp_price'])) {
				
				echo $list [$i] ['id'] . "<br>";
			}
		}
		exit ();
	}
	public function update_order() {
		if (IS_POST) {
			
			$data = I ( 'post.' );
			$id = M ( 'Order' )->save ( $data );
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
			$thtype = $ord [$i] ['thtype'];
			$thid = $ord [$i] ['id'];
			$orderid = $ord [$i] ['order_id'];
			$temp = M ( 'order' )->where ( 'id=' . $ord [$i] ['order_id'] )->find ();
			$ord [$i] ['thid'] = $ord [$i] ['id'];
			$ord [$i] = array_merge ( $ord [$i], $temp );
			
			if ($ord [$i] ['thtype'] == "dp") {
				$name = M ( 'tuihuo_list' )->where ( array (
						'pid' => $ord [$i] ['thid'] 
				) )->select ();
				$money = 0;
				for($j = 0; $j < count ( $name ); $j ++) {
					$orderinfo = M ( 'Orderlist' )->where ( "id=" . $name [$j] ['listid'] )->find ();
					$ord [$i] ['suborder'] [$j] ["id"] = $name [$j] ['id'];
					$ord [$i] ['suborder'] [$j] ["pic"] = $orderinfo ['picture'];
					$ord [$i] ['suborder'] [$j] ["pro_id"] = $orderinfo ['pro_id'];
					$ord [$i] ['suborder'] [$j] ["title"] = $orderinfo ['title'];
					$ord [$i] ['suborder'] [$j] ["price"] = $orderinfo ['price'];
					$ord [$i] ['suborder'] [$j] ["length"] = $name [$j] ['length'];
					$money += $name [$j] ['length'] * $orderinfo ['price'];
				}
			} else {
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
			}
			$user = M ( 'UcenterMember' )->where ( 'id=' . $ord [$i] ['uid'] )->find ();
			$ord [$i] ['userinfo'] = $user;
			
			if ($ord [$i] ['thtype'] == "dp") {
				$ord [$i] ['thprice'] = $money;
			} else if ($ord [$i] ['thtype'] == "allpro") {
				$ord [$i] ['thprice'] = $ord [$i] ['price'];
			} else {
				$ord [$i] ['thprice'] = $ord [$i] ['exp_price'];
			}
		}
		$this->assign ( 'ord', $ord );
		$this->assign ( "exlist", C ( 'EXPCOM' ) );
		$this->meta_title = '订单信息';
		$this->display ( "admin-tuihuo" );
	}
	public function update_thlist($id, $num) {
		$id = M ( 'tuihuo_list' )->where ( "id=" . $id )->setField ( "length", $num );
		if ($id) {
			echo "1";
		} else {
			echo "0";
		}
		exit ();
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
    			$Obj ['refund_fee'] = intval ( $orderinfo ['price'] * 100 ); // 退款总金额
				$status = 2;
				$text = "退全款";
				break;
			case 1:
    			/* 仅退产品款 */
				$list = M ( 'tuihuo_list' )->where ( "pid=" . $id )->select ();
				$thmoney = 0;
				for($i = 0; $i < count ( $list ); $i ++) {
					$listinfo = M ( 'orderlist' )->where ( "id=" . $list [$i] ['listid'] )->find ();
					$list [$i] ['pro_id'] = $listinfo ['pro_id'];
					$thmoney += $listinfo ['price'] * $list [$i] ['length'];
				}
				$Obj ['refund_fee'] = intval ( $thmoney * 100 ); // 退款总金额
				$status = 3;
				$text = "退产品款";
				break;
			case 2:
    			/* 仅退邮费 */
    			$Obj ['refund_fee'] = intval ( $orderinfo ['exp_price'] * 100 ); // 退款总金额
				$status = 4;
				$text = "退邮费";
				break;
		}
		
		$confirm = false;
		
		if ($orderinfo ['payment'] == "wechat") {
			// 微信支付
			/* 微信退款接口 */
			$Obj ['appid'] = \WxPaypubconfig::APPID; // APPID
			$Obj ['mch_id'] = \WxPaypubconfig::MCHID; // 商户号
			$Obj ['nonce_str'] = \WxPaypubconfig::KEY; // 随机字符串
			
			$Obj ['out_trade_no'] = $orderinfo ['wcorderid']; // 商户订单号
			$Obj ['out_refund_no'] = $orderinfo ['wcorderid'].rand(100,999); // 退款订单号（新建传送给微信）
			$Obj ['total_fee'] = intval ( $orderinfo ['price'] * 100 ); // 订单总金额
			$Obj ['refund_fee_type'] = "CNY"; // 币种
			$Obj ['op_user_id'] = \WxPaypubconfig::MCHID; // 操作员
			$Obj ['sign'] = $pay->getSign ( $Obj );

			$xml = $pay->arrayToXml ( $Obj );
			$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
			$result = $pay->postXmlSSLCurl ( $xml, $url );
			$result = $pay->xmlToArray ( $result );
			if ($result ['result_code'] == "SUCCESS") {
				$confirm = true;
			} else {
				$this->error ( "退款失败，".$result['err_code_des'] );
			}
		} else if ($orderinfo ['payment'] == "yue") {
			$where = array ();
			$where ['uid'] = $orderinfo ['uid'];
			$money = $Obj ['refund_fee'] / 100;
			$res = M ( 'Member' )->where ( $where )->setInc ( "money", $money );
			if ($res != false) {
				$confirm = true;
			} else {
				$this->error ( "退款失败！" );
			}
		}
		
		if ($confirm) {
			if ($type == 2) {
				$ts = 6;//退邮费
			} else {
				$ts = 5;//退款
			}
			M ( 'Order' )->where ( "id=" . $info ['order_id'] )->setField ( "status", $ts ); // 设置订单未已退款
			$save_data ['status'] = $status;
			$save_data ['price'] = $Obj ['refund_fee'];
			$save_data ['create_time'] = NOW_TIME;
			M ( 'tuihuo' )->where ( "id=" . $id )->save ( $save_data ); // 设置为已退款
			
			if ($type == 0) { // 全额退款
				/* 退款退分佣 */
				$map ['order_id'] = $orderinfo ['id'];
				$map ['puid'] = $orderinfo ['uid'];
				$map ['money_type'] = array (
						"in",
						"0,1"
				);
				$moneylist = M ( 'MoneyLog' )->where ( $map )->field ( "uid,money,order_id,puid,money_type" )->select ();
				for($i = 0; $i < count ( $moneylist ); $i ++) {
					if ($moneylist [$i] ['money_type'] == 0) {
						$money = M ( 'Member' )->where ( "uid=" . $moneylist [$i] ['uid'] )->getField ( "xxmoney" );
						$money = $money - $moneylist [$i] ['money'];
						M ( 'Member' )->where ( "uid=" . $moneylist [$i] ['uid'] )->setField ( "xxmoney", $money );
					} else if ($moneylist [$i] ['money_type'] == 1) {
						$money = M ( 'Member' )->where ( "uid=" . $moneylist [$i] ['uid'] )->getField ( "money" );
						$money = $money - $moneylist [$i] ['money'];
						M ( 'Member' )->where ( "uid=" . $moneylist [$i] ['uid'] )->setField ( "money", $money );
					}
					$moneylist [$i] ['add_time'] = NOW_TIME;
					$moneylist [$i] ['money_type'] = 3; // 回收
					M ( 'MoneyLog' )->add ( $moneylist [$i] ); // 增加记录
				}
				//退款退积分与库存
				//先退库存
				$list = M("orderlist")->where("order_id=".$orderinfo['id'])->select();
				for($i=0;$i<count($list);$i++){
					$where = array();
					$where['id'] = $list[$i]['pro_id'];
					M("ProductProlist")->where($where)->setInc("kucun",$list[$i]['length']);
				}
				if($orderinfo['status']>1){//需要原订单状态为已确认收货的才退积分
					for($i=0;$i<count($list);$i++){
						$where = array();
						$where['id'] = $list[$i]['pro_id'];
						$tjf = M('Product')->where($where)->getField("jifen");
						$jifen[] = $tjf * $list[$i]['length'];
					}
					$jifen = array_sum($jifen);
					$where = array();
					$where['uid'] = $orderinfo['uid'];
					M('Member')->where($where)->setDec("jifen",$jifen);
				}
			}
			if ($type == 1) { // 退产品款
				$loglist = M ( 'MoneyLog' )->where ( "order_id=" . $orderinfo ['id'] )->select ();
				for($i = 0; $i < count ( $loglist ); $i ++) {
					
					for($j = 0; $j < count ( $list ); $j ++) {
						
						if ($list [$j] ['pro_id'] == $loglist [$i] ['pro_id']) {
							
							$koumoney = ($loglist [$i] ['money'] / $loglist [$i] ['length']) * $list [$j] ['length'];
							if ($koumoney) {
								if ($loglist [$i] ['money_type'] == 0) {
									$money = 0;
									$money = M ( 'Member' )->where ( "uid=" . $loglist [$i] ['uid'] )->getField ( "xxmoney" );
									$money = $money - $koumoney;
									M ( 'Member' )->where ( "uid=" . $loglist [$i] ['uid'] )->setField ( "xxmoney", $money );
								} else if ($moneylist [$i] ['money_type'] == 1) {
									$money = 0;
									$money = M ( 'Member' )->where ( "uid=" . $loglist [$i] ['uid'] )->getField ( "money" );
									$money = $money - $koumoney;
									M ( 'Member' )->where ( "uid=" . $loglist [$i] ['uid'] )->setField ( "money", $money );
								}
								
								$data = $loglist [$i];
								unset ( $data ['id'] );
								$data ['money'] = $koumoney;
								$data ['money_type'] = 3; // 退货回收
								$data ['add_time'] = NOW_TIME;
								M ( 'MoneyLog' )->add ( $data );
							}
						}
					}
				}
				
				//单品退款
				$tuihuo_list = M("tuihuo_list")->where("pid=".$info['id'])->getField("listid",true);
				$where = array();
				$where['id'] = array("in",implode(",",$tuihuo_list));
				$list = M("orderlist")->where($where)->select();
				//退库存
				for($i=0;$i<count($list);$i++){
					M("ProductProlist")->where($where)->setInc("kucun",$list[$i]['length']);
				}
				
				if($orderinfo['status']>1){//需要原订单状态为已确认收货的才退积分
					for($i=0;$i<count($list);$i++){
						$where = array();
						$where['id'] = $list[$i]['pro_id'];
						$tjf = M('Product')->where($where)->getField("jifen");
						$jifen[] = $tjf * $list[$i]['length'];
						//退库存
						M("ProductProlist")->where($where)->setInc("kucun",$list[$i]['length']);
					}
					$jifen = array_sum($jifen);
					$where = array();
					$where['uid'] = $orderinfo['uid'];
					M('Member')->where($where)->setDec("jifen",$jifen);
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
		}
	}
	
	public function tjf($orderid){
		
		
		
		
	}
	
	public function kdd($id) {
		$where ['order_id'] = array (
				"in",
				$id 
		);
		
		$ids = explode ( ",", $id );
		
		$info = M ( 'Order' )->where ( "id=" . $ids [0] )->find ();
		$info ['list'] = M ( 'orderlist' )->where ( $where )->select ();
		if (count ( $info ['list'] ) > 0) {
			$money = 0;
			foreach ( $info ['list'] as $val ) {
				$length [] = $val ['length'];
				$money = $money + $val ['price'] * $val ['length'];
			}
			$length = array_sum ( $length );
		}
		$this->assign ( "money", $money );
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
			$info = M ( 'Order' )->save ( $data );
			if ($info) {
				$info = M ( 'Order' )->where ( "id=" . $data ['id'] )->find ();
				/* 通知用户 */
				$openid = "";
				$openid = M ( 'ucenter_member' )->where ( "id=" . $info ['uid'] )->getField ( "openid" );
				if ($openid) {
					$orderlist = M ( 'Orderlist' )->where ( "order_id=" . $info ['id'] )->select ();
					for($i = 0; $i < count ( $orderlist ); $i ++) {
						$str [$i] = $orderlist [$i] ['pro_id'] . "x" . $orderlist [$i] ['length'];
					}
					$str = implode ( ",", $str );
					$this->wechat = new Wechat (); // 实例化 wechat 类
					$url = "http://" . C ( 'WEB_SITE_DOMAIN' ) . "/user/myorder.html";
					$tpldata ['first'] = "您的订单号：" . $info ['id'] . "已发货啦！我们正在加速送到你的手上！";
					$tpldata ['keyword1'] = "订单号：" . $info ['id'] . "，产品：" . $str;
					$tpldata ['keyword2'] = $info ['express_com'];
					$tpldata ['keyword3'] = $info ['expressnum'];
					$tpldata ['keyword4'] = $info ['username'] . "，" . $info ['tel'] . "，" . $info ['address'];
					$tpldata ['remark'] = "详情请到我的订单查看！";
					$result = $this->wechat->tplmsg ( $openid, "8kamiyedzSMl0a8AjjzlyME-zgA7XaNtSwNDQ8pJGfc", $url, $tpldata );
				}
				
				$this->success ( "修改快递信息成功！" );
			} else {
				$this->error ( "修改快递信息失败！" );
			}
		} else {
			$this->error ( "非法访问" );
		}
	}
	public function show_express($id) {
		$where ['id'] = $id;
		$info = M ( 'order' )->where ( $where )->find ();
		if (empty ( $info ['express_com'] ) || empty ( $info ['expressnum'] )) {
			$this->assign ( "error", 1 );
		} else {
			$e = new ExpressApi ();
			$result = $e->getorder ( $info ['express_com'], $info ['expressnum'], "baidu" );
			$type = "baidu";
			if ($result ['status'] == 0 && $result ['msg'] == "ok") {
			} else {
				$result ['error'] = 1;
			}
			$this->assign ( "etype", $type );
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
		$map ['status'] = - 1;
		
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
	public function clearRecyle() {
		$del = M ( 'Order' )->where ( 'status=-1' )->delete ();
		if ($del) {
			$this->success ( "清空完成" );
			exit ();
		} else {
			$this->error ( "清空失败" );
			exit ();
		}
	}
	public function everdelete() {
		$id = array_unique ( ( array ) I ( 'id', 0 ) );
		$id = is_array ( $id ) ? implode ( ',', $id ) : $id;
		if (empty ( $id )) {
			$this->error ( '请选择要操作的数据!' );
		}
		$map ['id'] = array (
				'in',
				$id 
		);
		$del = M ( 'Order' )->where ( $map )->delete ();
		if ($del) {
			$this->success ( "删除成功！" );
		} else {
			$this->error ( "删除失败！" );
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
				$res = M ( 'Order' )->where ( $map )->setField ( "status", - 1 );
				if ($res) {
					$this->success ( "删除成功！" );
					exit ();
				} else {
					$this->error ( "删除失败！" );
					exit ();
				}
				break;
			case 'backorder' :
				$res = M ( 'Order' )->where ( $map )->setField ( "status", 0 );
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
