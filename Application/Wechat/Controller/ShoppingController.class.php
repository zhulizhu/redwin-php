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
class ShoppingController extends HomeController {
	public function get_token($code) {
		$info = get_wechatinfo_by_id ();
		if ($info ['expires_in'] < NOW_TIME) {
			echo $info ['access_token'];
			exit ();
		} else {
			$wechat = new Wechat (); // 实例化 wechat 类
			$info = get_wechatinfo_by_id ();
			$json = $wechat->get_web_access_token ( $info ['appID'], $info ['appsecret'], $code );
			
			$config_data ['expires_in'] = $json ['expires_in'] + NOW_TIME; // 将微信给的7200秒加上当前时间
			$config_data ['access_token'] = $info ['access_token'];
			$config_where ['id'] = $info ['id'];
			M ( 'WechatConfig' )->where ( $config_where )->save ( $config_data ); // 存入新的 access_token 和 有效期
			
			echo $json ['access_token'];
			exit ();
		}
	}
	
	/**
	 * 修改数量
	 *
	 * @param number $id        	
	 * @param number $length        	
	 */
	public function UpdataLength($id = 0, $length = null) {
		$allcart = json_decode ( cookie ( 'cart' ), true );
		$Quantity = 0;
		$theObj = $allcart ["obj"];
		$theObj = array_values ( $theObj );
		for($i = 0; $i < count ( $theObj ); $i ++) {
			if ($theObj [$i] ["id"] == $id) {
				if ($length == "Minus") {
					$theObj [$i] ["length"] = ($theObj [$i] ["length"] - 1);
					$Quantity = $theObj [$i] ["length"];
				} else {
					$theObj [$i] ["length"] = ($theObj [$i] ["length"] + 1);
					$Quantity = $theObj [$i] ["length"];
				}
			}
		}
		$allcart ["obj"] = $theObj;
		cookie ( 'cart', json_encode ( $allcart ) );
		echo $Quantity;
	}
	public function Settlement() {
		$this->display ();
	}
	
	/**
	 * 清空购物车
	 */
	public function Emptys() {
		$allcart = json_decode ( cookie ( 'cart' ), true );
		$allcart = null;
		cookie ( 'cart', json_encode ( $allcart ) );
		$this->redirect ( 'cart' );
	}
	/**
	 * 删除商品
	 *
	 * @param number $id产品ID        	
	 */
	public function Delete($id = 0) {
		$allcart = json_decode ( cookie ( 'cart' ), true );
		$theObj = $allcart ["obj"];
		$theObj = array_values ( $theObj );
		for($i = 0; $i < count ( $theObj ); $i ++) {
			if ($theObj [$i] ["id"] == $id) {
				unset ( $theObj [$i] );
			}
		}
		$allcart ["obj"] = $theObj;
		cookie ( 'cart', json_encode ( $allcart ) );
		$this->redirect ( 'cart' );
	}
	public function forDelete($id = '') {
		$where ['id'] = array (
				"in",
				$id 
		);
		M ( 'Cart' )->where ( $where )->delete ();
		$this->redirect ( 'cart' );
	}

	public function seckill_price($pro_id)
	{
		$where = array();
		$where['start_time'] = array("lt",NOW_TIME);
		$where['end_time'] = array("gt",NOW_TIME);
		$killid = M("seckill")->where($where)->getField("id");
		$price = 0;
		if($killid){
			$where = array();
			$where['pid'] = $killid;
			$where['proid'] = $pro_id;
			$killinfo = M("seckill_list")->where($where)->getField("price");
			if($killinfo){
				$price = $killinfo;
			}
		}
		return $price;
	}
	
	/**
	 * 购物车
	 */
	public function cart($tpl = 0) {
		// $allcart = json_decode(cookie('cart'),true);
		if (! is_login ()) {
			Cookie ( '__furl__', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
			$this->redirect ( "User/login" );
			exit ();
		}
		$where ['uid'] = is_login ();
		$where ['save_type'] = 0;
		$allcart = M ( 'Cart' )->where ( $where )->select ();
		
		for($i=0;$i<count($allcart);$i++){
			$where = array();
			$where['id'] = $allcart[$i]['pro_id'];
			$kucun = M("ProductProlist")->where($where)->getField("kucun");
			$allcart[$i]['kucun'] = $kucun;
		}
		$ids = '';
		if ($tpl == 0) { // 购物车模式
			$this->assign ( "info", array (
					'title' => '购物车' 
			) );
			$tpl = "Shopping/Cart";
		} else 		// 结算模式
		{
			if (! is_login ()) {
				Cookie ( '__furl__', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
				$this->redirect ( "User/login" );
				exit ();
			} else {
				$this->assign ( "info", array (
						'title' => '结算中心' 
				) );
				$tpl = "Shopping/Settlement";
			}
			
			$data = I ( 'param.' );
			$temp = implode ( ",", $data ['status'] );
			if (! $temp) {
				$temp = $data ['status'];
			}
			
			$nowgroup = get_group_by_uid ( is_login () );
			$nowgroupid = $nowgroup ['id'];
			$this->assign ( "nowgroupid", $nowgroupid );
			
			$where ['id'] = array (
					'in',
					$temp 
			);
			if (isset ( $data ['save_type'] ) && $data ['save_type'] == 1) {
				$where ['save_type'] = 1;
				$this->assign ( "save_type", 1 );
			} else {
				$this->assign ( "save_type", 0 );
			}
			$this->assign ( "ids", $temp );
			$ids = $temp;
			
			/* 刷新购物车 */
			$allcart = M ( 'Cart' )->where ( $where )->select ();
			for($i = 0; $i < count ( $allcart ); $i ++) {
				$allcart [$i] ['price'] = auto_price ( $allcart [$i] ['pro_id'] );
				$kucun = M("ProductProlist")->where("id=".$allcart[$i]['pro_id'])->getField("kucun");
				if($allcart[$i]['num']>$kucun){
					$this->error("库存不足！");
					exit;
				}
				M ( 'Cart' )->save ( $allcart [$i] );
			}
			$allcart = M ( 'Cart' )->where ( $where )->select ();
			/* paypass */
			$paypass = M ( 'ucenter_member' )->where ( "id=" . is_login () )->getField ( "paypass" );
			if ($paypass) {
				$this->assign ( "paypass", "is" );
			} else {
				$this->assign ( "paypass", "nis" );
			}
			/* 获取web_access_token */
			// 1从数据库获取
			/*$wechatinfo = get_wechatinfo_by_id ();
			if (NOW_TIME >= $wechatinfo ['web_expires_in']) {
				// 数据库的access_token已过期
				$nowurl = 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
				$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $wechatinfo ['appID'] . "&redirect_uri=" . urlencode ( $nowurl ) . "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
				header ( "Location:" . $wxurl );
				exit ();
			} else {
				$this->assign ( "web_access_token", $wechatinfo ['web_access_token'] );
			}*/
			/* 获取收货地址 */
			$addresslist = M ( 'Address' )->where ( "uid=" . is_login () )->select ();
			$this->assign ( "addresslist", $addresslist );
			$map ['uid'] = is_login ();
			$map ['is_def'] = 1;
			$defaddress = M ( 'Address' )->where ( $map )->find ();
			$this->assign ( "defaddress", $defaddress ['province'] . $defaddress ['city'] . $defaddress ['area'] );
			$sheng = M ( 'Linkage' )->where ( "pid=3133" )->select ();
			$this->assign ( "sheng", $sheng );
		}
		$gomoney = 0.00;
		if ($tpl != "Shopping/Settlement") {
			$Cart = D ( 'Cart' );
			$money = round ( $Cart->get_money ( $ids ), 2 ) / 100;
			$money = number_format ( $money, 2, '.', '' );
		} else {
			// 处理优惠券
			$Cart = D ( 'Cart' );
			$money = round ( $Cart->get_money ( $ids ), 2 ) / 100;
			$money = number_format ( $money, 2, '.', '' );
			$map = array ();
			$map ['uid'] = is_login ();
			$map ['status'] = 1;
			$map ['money'] = array (
					"gt",
					0 
			);
			$cids = M ( 'coupons_list' )->where ( $map )->getField ( "pid", true );
			$where = array ();
			$where ['id'] = array (
					"in",
					implode ( ",", $cids ) 
			);
			$where ['status'] = 1;
			$listids = M ( 'coupons' )->where ( $where )->select ();
			$trueids = array ();
			for($i = 0; $i < count ( $listids ); $i ++) {
				if (NOW_TIME > $listids [$i] ['start_time'] && NOW_TIME < $listids [$i] ['end_time'] && $money >= $listids [$i] ['frommoney']) {
					$trueids [] = $listids [$i] ['id'];
				}
			}
			$map ['pid'] = array (
					"in",
					implode ( ",", $trueids ) 
			);
			$clist = array();
			$clist = M ( 'coupons_list' )->where ( $map )->order ( "money desc" )->select ();
			if($clist){
				for($i = 0; $i < count ( $clist ); $i ++) {
					$map1 ['id'] = $clist [$i] ['pid'];
					$clist [$i] ['title'] = M ( 'coupons' )->where ( $map1 )->getField ( "title" );
					$clist[$i]['type'] = "syshb";
				}
			}
			//处理用户红包
			$where = array();
			$where['uid'] = is_login();
			$where['status'] = 1;
			$userhb = M('userhb_log')->where($where)->order('money desc')->select();

			for($i=0;$i<count($userhb);$i++){
				$userhb[$i]['title'] = "红包";
				$userhb[$i]['type'] = "userhb";
				$clist[] = $userhb[$i];
			}
			
			$clistadd = array();
			$clistadd['id'] = 0;
			$clistadd['pid'] = 0;
			$clistadd['uid'] = 0;
			$clistadd['money'] = 0;
			$clistadd['status'] = 1;
			$clistadd['create_time'] = 0;
			$clistadd['use_time'] = 0;
			$clistadd['order_id'] = 0;
			$clistadd['title'] = "不用券";
			$clistadd['type'] = "no";
			if(count($clist)>0){
				$clist[] = $clistadd;
			}
			if($seckill_price){
				//秒杀不允许使用优惠券
			}else{
				$this->assign ( "clist", $clist );
			}
			if ($clist) {
				$gomoney = $clist [0] ['money'];
			}
			
		}
		$this->assign ( "gomoney", $gomoney );
		$this->assign ( "money", $money ); // 总价格
		$this->assign ( "prod_length", $Cart->get_count ( $ids ) ); // 总数量
		$this->assign ( 'NewsList', $allcart );
		$this->display ( $tpl );
	}
	
	/**
	 * 积分购物车
	 */
	public function jifen_cart($tpl = 0) {
		if (! is_login ()) {
			Cookie ( '__furl__', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
			$this->redirect ( "User/login" );
			exit ();
		} else {
			$this->assign ( "info", array (
					'title' => '积分兑换' 
			) );
			$tpl = "Shopping/jifen_Settlement";
		}
		
		$data = I ( 'param.' );
		
		$temp = implode ( ",", $data ['status'] );
		if (! $temp) {
			$temp = $data ['status'];
		}
		$where['uid'] = is_login();
		$where ['id'] = array ('in',$temp );
		$where ['save_type'] = 1;
		$this->assign ( "save_type", 1 );
		$this->assign ( "ids", $temp );
		$ids = $temp;
		$allcart = M ( 'Cart' )->where ( $where )->select ();//积分购物车
		
		/* 获取收货地址 */
		$addresslist = M ( 'Address' )->where ( "uid=" . is_login () )->select ();
		$this->assign ( "addresslist", $addresslist );
		
		$map ['uid'] = is_login ();
		$map ['is_def'] = 1;
		$defaddress = M ( 'Address' )->where ( $map )->find ();
		$this->assign ( "defaddress", $defaddress ['province'] . $defaddress ['city'] . $defaddress ['area'] );
		$sheng = M ( 'Linkage' )->where ( "pid=3133" )->select ();
		$this->assign ( "sheng", $sheng );
		$gomoney = 0.00;
		$Cart = D ( 'Cart' );
		$money = round ( $Cart->get_money ( $ids ), 2 ) / 100;
		$money = number_format ( $money, 2, '.', '' );
		$this->assign ( "gomoney", $gomoney );
		$this->assign ( "money", $money ); // 总价格
		$this->assign ( "prod_length", $Cart->get_count ( $ids ) ); // 总数量
		$this->assign ( 'NewsList', $allcart );
		$this->display ( $tpl );
	}
	public function get_area($pid) {
		$sheng = M ( 'Linkage' )->where ( "pid=" . $pid )->select ();
		$str = "<option>请选择</option>";
		for($i = 0; $i < count ( $sheng ); $i ++) {
			$str .= "<option value='" . $sheng [$i] ['id'] . "'>" . $sheng [$i] ['title'] . "</option>";
		}
		echo $str;
		exit ();
	}
	public function add_address() {
		if (IS_POST) {
			$data = I ( 'post.' );
			
			$count = M ( 'Address' )->where ( "uid=" . is_login () )->count ();
			if ($count == 0) {
				$data ['is_def'] = 1;
			}
			$data ['province'] = M ( 'linkage' )->where ( "id=" . $data ['province'] )->getField ( "title" );
			$data ['city'] = M ( 'linkage' )->where ( "id=" . $data ['city'] )->getField ( "title" );
			$data ['area'] = M ( 'linkage' )->where ( "id=" . $data ['area'] )->getField ( "title" );
			
			$data ['uid'] = is_login ();
			$res = M ( 'address' )->add ( $data );
			if ($res) {
				echo "1";
				exit ();
			} else {
				echo "0";
				exit ();
			}
		}
	}
	public function get_thisproid($proid) {
		$where = array();
		$where ['uid'] = is_login ();
		$where ['pro_id'] = $proid;
		$where ['save_type'] = 1;
		$id = M ( 'cart' )->where ( $where )->getField ( "id" );
		if ($id) {
			echo $id;
		} else {
			echo "false";
		}
		exit ();
	}
	
	/**
	 * 加入和修改购物车函数
	 *
	 * @param number $id        	
	 * @param number $length        	
	 */
	public function UpdateCart($id = 0, $length = 1, $pacId = 0) {
		// cookie('cart',null);
		if ($length < 0 || $length == 0) {
			echo "奖品数据量有误";
		}
		if ($id != 0) {
			$allcart = json_decode ( cookie ( 'cart' ), true );
			if ($allcart == null) {
				$temp ['id'] = $id;
				$temp ['length'] = $length;
				$temp ['pacId'] = $pacId;
				$allcart ['length'] = 1;
				$allcart ['obj'] [] = $temp;
				$allcart = json_encode ( $allcart );
				cookie ( 'cart', $allcart );
				echo 1;
			} else {
				$ex = true;
				$theObj = $allcart ["obj"];
				$theObj = array_values ( $theObj );
				for($i = 0; $i < count ( $theObj ); $i ++) {
					if ($theObj [$i] ["id"] == $id) {
						$product = M ( 'Product' )->where ( 'id=' . $id )->find ();
						$rest = $product ['total'] - $product ['join'];
						$theObj [$i] ["length"] = ($theObj [$i] ["length"] + $length);
						if ($theObj [$i] ["length"] > $rest) {
							$theObj [$i] ["length"] = $rest;
						}
						$theObj [$i] ["pacId"] = $pacId;
						$allcart ["length"] = count ( $theObj );
						$ex = false;
					}
				}
				if ($ex) {
					$temp ['id'] = $id;
					$temp ['length'] = $length;
					$temp ['pacId'] = $pacId;
					$allcart ["length"] = count ( $theObj );
					$theObj [] = $temp;
				}
				$allcart ["obj"] = $theObj;
				cookie ( 'cart', json_encode ( $allcart ) );
				// 添加成功
				echo 1;
			}
		} else {
			echo "加入购物车失败";
		}
	}
	
	/**
	 * 单品包邮
	 */
	public function onepro_freight($address, $id) {
		$proid = M ( 'Cart' )->where ( "id=" . $id )->getField ( "pro_id" );
		$info = D ( 'Product' )->detail ( $proid );
		if ($info ['freight_status'] == 1) {
			$yes = false;
			$list = explode ( ",", $info ['contain'] );
			for($j = 0; $j < count ( $list ); $j ++) {
				if (stristr ( $address, $list [$j] )) {
					$yes = 1;
					continue;
				}
			}
			if (! $yes) {
				$list = explode ( ",", $info ['contain1'] );
				for($j = 0; $j < count ( $list ); $j ++) {
					if (stristr ( $address, $list [$j] )) {
						$yes = 2;
						continue;
					}
				}
			}
			if (! $yes) {
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
			$str = "<a href='javascript:;' value='" . $price . "'";
			$str .= " class='focus' ";
			$str .= "title='" . $flist [$i] ['id'] . "'>";
			$str .= "普通快递<br>￥<span>" . $price;
			$str .= "</span></a>";
			return $str;
		} else {
			return false;
		}
	}


	/**
	 * 单品包邮
	 */
	public function dp_freight($address, $id) {
		$proid = M ( 'Cart' )->where ( "id=" . $id )->getField ( "pro_id" );
		$info = D ( 'Product' )->detail ( $proid );
		if ($info ['freight_status'] == 1) {
			$yes = false;
			$list = explode ( ",", $info ['contain'] );
			for($j = 0; $j < count ( $list ); $j ++) {
				if (stristr ( $address, $list [$j] )) {
					$yes = 1;
					continue;
				}
			}
			if (! $yes) {
				$list = explode ( ",", $info ['contain1'] );
				for($j = 0; $j < count ( $list ); $j ++) {
					if (stristr ( $address, $list [$j] )) {
						$yes = 2;
						continue;
					}
				}
			}
			if (! $yes) {
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
		} else {
			return false;
		}
	}
	public function get_seckill_youfei($killid,$killinfo,$pro_id)
	{
		$where = array();
		$where['create_time'] = array("between",array($killid['start_time'],$killid['end_time']));
		$order_ids = M("Order")->where($where)->getField("id",true);
		if($order_ids){
			$where = array();
			$where['order_id'] = array("in",implode(",",$order_ids));
			$where['pro_id'] = $pro_id;
			$length = M("orderlist")->where($where)->Sum("length");
			if($length){
				if($killinfo['one_length'] && $killinfo['one_price'] && $length<$killinfo['one_length']){
					if($killinfo['one_dp']){
						return array("dp"=>true,"price"=>0);
					}else{
						return array("dp"=>false,"price"=>$killinfo['one_youfei']);
					}
				}
				if($killinfo['two_length'] && $killinfo['two_price'] && $length<($killinfo['one_length']+$killinfo['two_length'])) {
					if($killinfo['two_dp']){
						return array("dp"=>true,"price"=>0);
					}else{
						return array("dp"=>false,"price"=>$killinfo['two_youfei']);
					}
				}
				if($killinfo['three_length'] && $killinfo['three_price'] && $length<($killinfo['one_length']+$killinfo['two_length']+$killinfo['three_length'])){
					if($killinfo['three_dp']){
						return array("dp"=>true,"price"=>0);
					}else{
						return array("dp"=>false,"price"=>$killinfo['three_youfei']);
					}
				}
			}else{
				if($killinfo['one_dp']){
					return array("dp"=>true,"price"=>0);
				}else{
					$temp = array();
					$temp['dp'] = false;
					$temp['price'] = $killinfo['one_youfei'];
					return $temp;
				}
			}
		}else{//还没有人下单

			if($killinfo['one_dp']){
				return array("dp"=>true,"price"=>0);
			}else{
				return array("dp"=>false,"price"=>$killinfo['one_youfei']);
			}
		}
	}
	
	/**
	 * 自动获得运费规则（前台使用）
	 * 
	 * @param unknown $address        	
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_freight($address, $ids = '', $save_type) {
		$money = D ( 'Cart' )->get_money ( $ids, $save_type ); // 购物车价格总计
		$money = $money / 100;

		$youfei = array();

		//秒杀邮费
		$where = array();
		$where['start_time'] = array("lt",NOW_TIME);
		$where['end_time'] = array("gt",NOW_TIME);
		$killid = M("seckill")->where($where)->find();
		if($killid){
			/*下面是秒杀活动的邮费规则,秒杀活动优先一切*/
			$ids = explode(",",$ids);
			foreach ($ids as $key=>$id){
				$proid = M("cart")->where("id=".$id)->getField("pro_id");
				$where = array();
				$where['pid'] = $killid['id'];
				$where['proid'] = $proid;
				$killinfo = M("seckill_list")->where($where)->find();
				if($killinfo){
					$seckill_youfei = $this->get_seckill_youfei($killid,$killinfo,$proid);
					if($seckill_youfei['dp']){//单品邮费
						$dp_youfei = $this->dp_freight($address,$id);
						if($dp_youfei){
							$youfei[] = $dp_youfei;
						}
					}else{
						$youfei[] = $seckill_youfei['price'];
					}
					//取消这个,让下面不再计算
					unset($ids[$key]);
				}
			}
		}
		// 单品包邮
		if(!is_array($ids)){
			$pros = explode ( ",", $ids );
		}else{
			$pros = $ids;
		}
		for($i=0;$i<count($pros);$i++){
			//单品包邮
			$dp_price = $this->dp_freight($address,$pros[$i]);
			if($dp_price){
				$youfei[] = $dp_price;
				$proid = M("Cart")->where("id=".$pros[$i])->getField("pro_id");
				$byprice = M("Product")->where("id=".$proid)->getField("byprice");
				if($byprice>0){
					$where = array();
					$where['id'] = $pros[$i];
					$info = M("Cart")->where($where)->find();
					$countprice = $info['price'] * $info['num'];//该商品在购物车的小计
					if($countprice>=$byprice){//如果该产品达到满额包邮的条件，则所有产品包邮
						$youfei[] = 0;
					}
				}
			}else{
				$youfei = false;
			}
		}
		if($youfei){
			$str = "<a href='javascript:;' value='" . $dp_price=0 . "'";
			$str .= " class='focus' ";
			$str .= "title='baoyou'>";
			$str .= "包邮<br>￥<span>" . $dp_price=0;
			$str .= "</span></a>";
			echo $str;
			exit;
		}

		/*下面是正常规则*/
		//系统满额包邮
		$byprice = C("CART_PRICE_BY");
		if($byprice>0){
			if($money>$byprice){
				$str = "<a href='javascript:;' value='0'";
				$str .= " class='focus' ";
				$str .= "title='baoyou'>";
				$str .= "包邮<br>￥<span>0.00</span></a>";
				echo $str;
				exit;
			}
		}

		// 处理优惠券
		$map = array ();
		$map ['uid'] = is_login ();
		$map ['status'] = 1;
		$map ['length'] = array (
			"egt",
			1
		);
		$map ['money'] = array (
			"eq",
			0
		);
		$cids = M ( 'coupons_list' )->where ( $map )->getField ( "pid", true );
		$where = array ();
		$where ['id'] = array (
			"in",
			implode ( ",", $cids )
		);
		$where ['status'] = 1;
		$listids = M ( 'coupons' )->where ( $where )->select ();
		for($i = 0; $i < count ( $listids ); $i ++) {
			if (NOW_TIME > $listids [$i] ['start_time'] && NOW_TIME < $listids [$i] ['end_time']) {
				$trueids = $listids [$i] ['id'];
			}
		}
		$map ['pid'] = $trueids;
		$clist = M ( 'coupons_list' )->where ( $map )->order ( "money desc" )->find ();
		$str = "";
		$c = false;
		if ($clist) {
			$c = true;
			$map1 ['id'] = $clist ['pid'];
			$clist ['title'] = M ( 'coupons' )->where ( $map1 )->getField ( "title" );
			$str .= "<a href='javascript:;' value='0.00' title='coupons' byk='" . $clist ['id'] . "' class='focus'>" . $clist ['title'] . "<br>￥<span>0.00</span></a>";
		}

		$flist = M ( 'freight' )->select ();
		$yes = false;
		for($i = 0; $i < count ( $flist ); $i ++) {
			$list = explode ( ",", $flist [$i] ['contain'] );
			for($j = 0; $j < count ( $list ); $j ++) {
				if (stristr ( $address, $list [$j] )) {
					$yes = 1;
					continue;
				}
			}
			if (! $yes) {
				$list = explode ( ",", $flist [$i] ['contain1'] );
				for($j = 0; $j < count ( $list ); $j ++) {
					if (stristr ( $address, $list [$j] )) {
						$yes = 2;
						continue;
					}
				}
			}
			if (! $yes) {
				$list = explode ( ",", $flist [$i] ['contain2'] );
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
						$price = $flist [$i] ['contain_price'];
						break;
					case 2 :
						$price = $flist [$i] ['contain1_price'];
						break;
					case 3 :
						$price = $flist [$i] ['contain2_price'];
						break;
				}
			} else {
				$price = $flist [$i] ['nocontain_price'];
			}
			$str .= "<a href='javascript:;' value='" . $price . "'";
			if ($i == 0) {
				if (! $c) {
					$str .= " class='focus' ";
				}
			}
			$str .= "title='" . $flist [$i] ['id'] . "'>";
			$str .= $flist [$i] ['title'] . "<br>￥<span>" . $price;
			$str .= "</span></a>";
		}
		echo $theArray ['str'] = $str;
		exit ();



	}
	public function check_yue($money) {
		$umoney = M ( 'Member' )->where ( 'uid=' . is_login () )->getField ( "money" );
		$money = abs ( $money );
		$umoney = abs ( $umoney );
		if ($umoney >= $money) {
			echo "true";
		} else {
			echo "false";
		}
		exit ();
	}
}