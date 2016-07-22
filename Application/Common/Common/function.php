<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// OneThink常量定义
const ONETHINK_VERSION = '1.3.0';
const ONETHINK_ADDON_PATH = './Addons/';
const Server_domain = "www.cheewo.com";

use Common\Controller\Wechat;

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 检测用户是否登录
 *
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login() {
	$user = session ( 'user_auth' );
	if (empty ( $user )) {
		return 0;
	} else {
		return session ( 'user_auth_sign' ) == data_auth_sign ( $user ) ? $user ['uid'] : 0;
	}
}

function get_linkage_value($id){
	$where = array();
	$where['id'] = $id;
	return M("linkage")->where($where)->getField("title");
}


function randomFloat($min = 0, $max = 1) {
	return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

function get_hb_money($type,$id){
	if($type==1){
		$money = M("userhb_log")->where("id=".$id)->getField("money");
	}else{
		$money = M("coupons_list")->where("id=".$id)->getField("money");
	}
	return $money;
}

function get_coupons_name($id){
	$where['id'] = $id;
	return  M('coupons')->where($where)->cache(true,600)->getField("title");
}

function get_order_status($status) {
	switch ($status) {
		case 0 :
			return "未付款";
			break;
		case 1 :
			return "待收货";
			break;
		case 2 :
			return "待评价";
			break;
		case 3 :
			return "已完成";
			break;
		case 4 :
			return "正在申请退换货";
			break;
		case 5 :
			return "已退款";
			break;
		case 6 :
			return "已退运费";
			break;
		case - 1 :
			return "已取消";
			break;
	}
}
function get_fy_status($status) {
	switch ($status) {
		case 0 :
			if(isset($_GET['money_type'])){
				return "线下收入";
			}
			break;
		case 1 :
			return "线上收入";
			break;
		case 2 :
			return "消费";
			break;
		case 3 :
			return "退款回收";
			break;
		case 4 :
			return "线下扣款";
			break;
			case 6 :
				return "追平返点";
				break;
		default:
			return "所有";
			break;
	}
}

/**
 * 自动获取价格
 *
 * @param unknown $pro_id        	
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function auto_price($pro_id) {
	$info = array ();
	$map ['proid'] = $pro_id;
	$map ['status'] = 1;
	$pids = M ( 'MarketingList' )->where ( $map )->getField ( "pid", true );
	if ($pids) {
		$where ['id'] = array (
				"in",
				implode ( ",", $pids ) 
		);
		$where ['start_time'] = array (
				"lt",
				NOW_TIME 
		);
		$where ['end_time'] = array (
				"gt",
				NOW_TIME 
		);
		$id = M ( 'marketing' )->where ( $where )->getField ( "id" );
		if ($id) {
			$map1 ['pid'] = $id;
			$map1 ['proid'] = $pro_id;
			$info = M ( 'MarketingList' )->where ( $map1 )->find ();
		}
	}

	//秒杀
	$where = array();
	$where['start_time'] = array("lt",NOW_TIME);
	$where['end_time'] = array("gt",NOW_TIME);
	$seckill = M("seckill")->where($where)->find();
	if($seckill){
		$where = array();
		$where['pid'] = $seckill['id'];
		$where['proid'] = $pro_id;
		$where['status'] = 1;
		$killinfo = M("seckill_list")->where($where)->find();
		if($killinfo){
			$where = array();
			$where['create_time'] = array("between",array($seckill['start_time'],$seckill['end_time']));
			$order_ids = M("Order")->where($where)->getField("id",true);
			if($order_ids){
				$where = array();
				$where['order_id'] = array("in",implode(",",$order_ids));
				$where['pro_id'] = $pro_id;
				$length = M("orderlist")->where($where)->Sum("length");
				if($length){
					if($killinfo['one_length'] && $killinfo['one_price'] && $length<$killinfo['one_length']){
						return $killinfo['one_price'];
					}
					if($killinfo['two_length'] && $killinfo['two_price'] && $length<($killinfo['one_length']+$killinfo['two_length'])) {
						return $killinfo['two_price'];
					}
					if($killinfo['three_length'] && $killinfo['three_price'] && $length<($killinfo['one_length']+$killinfo['two_length']+$killinfo['three_length'])){
						return $killinfo['three_price'];
					}
				}else{
					return $killinfo['one_price'];
				}
			}else{//还没有人下单
				return $killinfo['one_price'];
			}
		}
	}

	if (! $info) {
		$info = D ( 'Product' )->detail ( $pro_id );
	}
	
	$group_info = get_group_by_uid ( is_login () );
	switch ($group_info ['id']) {
		case 1 : // 普通会员
			return $info ['real_price'];
			break;
		case 9 : // 三级代理
			return $info ['sanji_price'];
			break;
		case 10 : // 二级代理
			return $info ['erji_price'];
			break;
		case 11 : // 一级代理
			return $info ['yiji_price'];
			break;
		case 12 : // 营销总监
			return $info ['yiji_price'];
			break;
		case 13 : // 分公司总经理
			return $info ['yiji_price'];
			break;
		default : // 默认情况
			return $info ['real_price'];
			break;
	}
}

/**
 * 收藏数
 *
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function like_count() {
	$where ['uid'] = is_login ();
	$count = M ( 'like' )->where ( $where )->count ();
	return $count;
}

/**
 * 浏览数
 *
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function view_count() {
	$where ['uid'] = is_login ();
	$count = M ( 'View' )->where ( $where )->count ();
	return $count;
}
function time_cha($endtime, $starttime) {
	$data ['d'] = floor ( ($endtime - $starttime) / 86400 );
	$data ['h'] = floor ( ($endtime - $starttime) % 86400 / 3600 );
	return $data;
}

/**
 * 浏览数
 *
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function pj_count($pro_id) {
	$where ['pro_id'] = $pro_id;
	$where ['status'] = 1;
	$count = M ( 'prints' )->where ( $where )->count ();
	return $count;
}

function get_user_puid($uid=0){
	if ($uid == 0) {
		$uid = is_login ();
	}
	$map = array();
	$map['uid'] = $uid;
	return M("AuthGroupAccess")->where($map)->getField("puid");
}

/**
 * 获取团队数量的递归，有点庞大
 *
 * @param number $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_team($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['puid'] = $uid;
	$where ['status'] = 1;
	$list = M ( 'AuthGroupAccess' )->where ( $where )->select ();
	$theArray = array ();
	if (count ( $list ) > 0) {
		for($i = 0; $i < count ( $list ); $i ++) {
			$theArray [] = $list [$i] ['uid'];
			$temp = get_team ( $list [$i] ['uid'] );
			$theArray = array_merge ( $theArray, $temp );
		}
	}
	return $theArray;
}

function get_youfen($uid=0){
	if ($uid == 0) {
		$uid = is_login ();
	}
	M("youfen")->where("puid=".$uid)->Sum();
}



function get_youfen_leiji($uid=0){
	if ($uid == 0) {
		$uid = is_login ();
	}
	$map = array();
	$map['puid'] = $uid;
	$map['yf_type'] = array("in","0,1");
	return M("youfen")->where($map)->Sum("val");
}

/**
 * 获取团队数量的递归，有点庞大
 *
 * @param number $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_all_team($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['puid'] = $uid;
	$list = M ( 'AuthGroupAccess' )->where ( $where )->select ();
	$theArray = array ();
	if (count ( $list ) > 0) {
		for($i = 0; $i < count ( $list ); $i ++) {
			$theArray [] = $list [$i] ['uid'];
			$temp = get_all_team ( $list [$i] ['uid'] );
			$theArray = array_merge ( $theArray, $temp );
		}
	}
	return $theArray;
}

/**
 * 递归团队，倒序
 * 找到所有上级
 * @param number $uid
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_team_desc($uid = 0){
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['uid'] = $uid;
	$puid = M ( 'AuthGroupAccess' )->where ( $where )->getField("puid");
	$theArray = array ();
	if($puid){
		$theArray[] = $puid;
		$temp = get_team_desc ( $puid );
		$theArray = array_merge ( $theArray, $temp );
	}
	return $theArray;
}

function get_express($id) {
	$title = M ( 'freight' )->where ( "id=" . $id )->getField ( "title" );
	return $title;
}
function get_team_one($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['puid'] = $uid;
	//$where ['status'] = 1;
	$list = M ( 'AuthGroupAccess' )->where ( $where )->getField ( "uid", true );
	return $list;
}

function get_shengji_team($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['puid'] = $uid;
	$where ['status'] = 1;
	$list = M ( 'AuthGroupAccess' )->where ( $where )->getField ( "uid", true );
	return $list;
}

/**
 * 筛选我的团队里的各个级别的人数
 * 为了节约资源，请先获取团队信息
 *
 * @param unknown $team        	
 * @param unknown $group_id        	
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_team_filter_to_num($team, $group_id) {
	$where ['group_id'] = $group_id;
	$where ['uid'] = array (
			'in',
			implode ( ",", $team ) 
	);
	$num = M ( 'AuthGroupAccess' )->where ( $where )->count ();
	return $num;
}

function get_team_filter($team, $group_id) {
	$where ['group_id'] = $group_id;
	$where ['uid'] = array (
			'in',
			implode ( ",", $team )
	);
	$num = M ( 'AuthGroupAccess' )->where ( $where )->getField("uid",true);
	return $num;
}

/**
 * 获取保证金
 *
 * @param number $uid        	
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_bzj($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['uid'] = $uid;
	$bzj = M ( 'Member' )->where ( $where )->getField ( "bzj" );
	return $bzj;
}

/**
 * 获取团队业绩信息，UID为指定用户下的团队业绩
 *
 * @param number $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_yeji($uid = 0) {
	$auth = get_all_team ( $uid ); // 先获取团队信息
	$price = array ();
	foreach ( $auth as $val ) {
		$where ['uid'] = $val;
		$where ['status'] = array (
				'in',
				'2,3,5,6'
		); // 待评价、已完成、只退了邮费的
		$sum = 0;
		$order = M ( 'Order' )->where ( $where )->select();
		if(count($order)>0){
			for($i=0;$i<count($order);$i++){
				if($order[$i]['status']==5){//退部分款
					$where = array();
					$where['order_id'] = $order[$i]['id'];
					$tuihuo = M("tuihuo")->where($where)->find();
					$sum = $sum + ($order[$i]['pro_price']-($tuihuo['price']/100));
				}else{
					$sum = $sum + $order[$i]['pro_price'];
				}
			}
		}
		$price [] = $sum;
	}
	return array_sum ( $price );
}

/**
 * 获取指定用户的业绩
 *
 * @param unknown $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_yeji_by_uid($uid) {
	$where ['uid'] = $uid;
	$where ['status'] = array (
			'in',
			'2,3,5,6'
	);
	$sum = 0;
	$order = M ( 'Order' )->where ( $where )->select();
	if(count($order)>0){
		for($i=0;$i<count($order);$i++){
			if($order[$i]['status']==5){//退部分款
				$where = array();
				$where['order_id'] = $order[$i]['id'];
				$tuihuo = M("tuihuo")->where($where)->find();
				$sum = $sum + ($order[$i]['pro_price']-($tuihuo['price']/100));
			}else{
				$sum = $sum + $order[$i]['pro_price'];
			}
		}
	}
	return $sum;
}

/**
 * 根据品牌id获得品牌名称
 */
function get_brand($brand_id) {
	$title = M ( 'Purcate' )->where ( 'id=' . $brand_id )->getField ( 'title' );
	return $title;
}
/**
 * 根据车型id获得车型名称
 */
function get_cars($cars_id) {
	$title = M ( 'purchase' )->where ( 'id=' . $cars_id )->getField ( 'title' );
	return $title;
}
function get_autoup($status) {
	switch ($status) {
		case 0 :
			return "手动升级";
			break;
		case 1 :
			return "自动升级";
			break;
	}
}

/**
 * 生成二维码
 * 
 * @param unknown $openid        	
 * @return boolean
 * @author 智网天下科技 http://www.cheewo.com
 */
function qrcode($openid) {
	Vendor ( 'phpqrcode.phpqrcode' );
	$size = 10;
	$level = 5;
	$url = "http://shop.uxiango.cn/Index/index/openid/" . $openid;
	$errorCorrectionLevel = intval ( $level ); // 容错级别
	$matrixPointSize = intval ( $size ); // 生成图片大小
	$object = new \QRcode ();
	$filename = "Uploads/QRcode/" . $openid . ".png";
	$object->png ( $url, $filename, $errorCorrectionLevel, $matrixPointSize, 2 );
	$logo = 'Public/wechat/images/erweima.png'; // 准备好的logo图片
	$QR = $filename;
	if ($logo !== FALSE) {
		$QR = imagecreatefromstring ( file_get_contents ( $QR ) );
		$logo = imagecreatefromstring ( file_get_contents ( $logo ) );
		// 重新组合图片并调整大小
		imagecopyresampled ( $logo, $QR, 166, 301, 0, 0, 370, 370, 370, 370 );
	}
	// 输出图片
	imagepng ( $logo, $filename );
	return $filename;
}

/**
 * 翻译
 *
 * @param unknown $q        	
 * @param string $from        	
 * @param string $to        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function fanyi($q, $from = "auto", $to = "auto") {
	$url = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=" . "iaL7uBFOTq2Gr6AWkgyAxx0s";
	$url .= "&q=" . urlencode ( $q );
	$url .= "&from=" . $from;
	$url .= "&to=" . $to;
	$result = json_decode ( file_get_contents ( $url ), true );
	$dst = $result ['trans_result'] [0] ['dst'];
	$dst = str_replace ( " ", "", $dst );
	return $dst;
}

/**
 * 检测当前用户是否为管理员
 *
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null) {
	$uid = is_null ( $uid ) ? is_login () : $uid;
	return $uid && (intval ( $uid ) === C ( 'USER_ADMINISTRATOR' ));
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 *
 * @param string $str
 *        	要分割的字符串
 * @param string $glue
 *        	分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str, $glue = ',') {
	return explode ( $glue, $str );
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 *
 * @param array $arr
 *        	要连接的数组
 * @param string $glue
 *        	分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr, $glue = ',') {
	return implode ( $glue, $arr );
}

/* 从字符串中获取数字 */
function findNum($str = '') {
	$str = trim ( $str );
	if (empty ( $str )) {
		return '';
	}
	$result = '';
	for($i = 0; $i < strlen ( $str ); $i ++) {
		if (is_numeric ( $str [$i] )) {
			$result .= $str [$i];
		}
	}
	return $result;
}

/**
 * 字符串截取，支持中文和其他编码
 *
 * @static
 *
 *
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	return $suffix ? $slice . '...' : $slice;
}

/**
 * 系统加密方法
 *
 * @param string $data
 *        	要加密的字符串
 * @param string $key
 *        	加密密钥
 * @param int $expire
 *        	过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
	$key = md5 ( empty ( $key ) ? C ( 'DATA_AUTH_KEY' ) : $key );
	$data = base64_encode ( $data );
	$x = 0;
	$len = strlen ( $data );
	$l = strlen ( $key );
	$char = '';
	
	for($i = 0; $i < $len; $i ++) {
		if ($x == $l)
			$x = 0;
		$char .= substr ( $key, $x, 1 );
		$x ++;
	}
	
	$str = sprintf ( '%010d', $expire ? $expire + time () : 0 );
	
	for($i = 0; $i < $len; $i ++) {
		$str .= chr ( ord ( substr ( $data, $i, 1 ) ) + (ord ( substr ( $char, $i, 1 ) )) % 256 );
	}
	return str_replace ( array (
			'+',
			'/',
			'=' 
	), array (
			'-',
			'_',
			'' 
	), base64_encode ( $str ) );
}

/**
 * 系统解密方法
 *
 * @param string $data
 *        	要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key
 *        	加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = '') {
	$key = md5 ( empty ( $key ) ? C ( 'DATA_AUTH_KEY' ) : $key );
	$data = str_replace ( array (
			'-',
			'_' 
	), array (
			'+',
			'/' 
	), $data );
	$mod4 = strlen ( $data ) % 4;
	if ($mod4) {
		$data .= substr ( '====', $mod4 );
	}
	$data = base64_decode ( $data );
	$expire = substr ( $data, 0, 10 );
	$data = substr ( $data, 10 );
	
	if ($expire > 0 && $expire < time ()) {
		return '';
	}
	$x = 0;
	$len = strlen ( $data );
	$l = strlen ( $key );
	$char = $str = '';
	
	for($i = 0; $i < $len; $i ++) {
		if ($x == $l)
			$x = 0;
		$char .= substr ( $key, $x, 1 );
		$x ++;
	}
	
	for($i = 0; $i < $len; $i ++) {
		if (ord ( substr ( $data, $i, 1 ) ) < ord ( substr ( $char, $i, 1 ) )) {
			$str .= chr ( (ord ( substr ( $data, $i, 1 ) ) + 256) - ord ( substr ( $char, $i, 1 ) ) );
		} else {
			$str .= chr ( ord ( substr ( $data, $i, 1 ) ) - ord ( substr ( $char, $i, 1 ) ) );
		}
	}
	return base64_decode ( $str );
}

/**
 * 数据签名认证
 *
 * @param array $data
 *        	被认证的数据
 * @return string 签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
	// 数据类型检测
	if (! is_array ( $data )) {
		$data = ( array ) $data;
	}
	ksort ( $data ); // 排序
	$code = http_build_query ( $data ); // url编码并生成query字符串
	$sign = sha1 ( $code ); // 生成签名
	return $sign;
}

/**
 * 对查询结果集进行排序
 *
 * @access public
 * @param array $list
 *        	查询结果
 * @param string $field
 *        	排序的字段名
 * @param array $sortby
 *        	排序类型
 *        	asc正向排序 desc逆向排序 nat自然排序
 * @return array
 *
 */
function list_sort_by($list, $field, $sortby = 'asc') {
	if (is_array ( $list )) {
		$refer = $resultSet = array ();
		foreach ( $list as $i => $data )
			$refer [$i] = &$data [$field];
		switch ($sortby) {
			case 'asc' : // 正向排序
				asort ( $refer );
				break;
			case 'desc' : // 逆向排序
				arsort ( $refer );
				break;
			case 'nat' : // 自然排序
				natcasesort ( $refer );
				break;
		}
		foreach ( $refer as $key => $val )
			$resultSet [] = &$list [$key];
		return $resultSet;
	}
	return false;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @param array $list
 *        	要转换的数据集
 * @param string $pid
 *        	parent标记字段
 * @param string $level
 *        	level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array ();
	if (is_array ( $list )) {
		// 创建基于主键的数组引用
		$refer = array ();
		foreach ( $list as $key => $data ) {
			$refer [$data [$pk]] = & $list [$key];
		}
		foreach ( $list as $key => $data ) {
			// 判断是否存在parent
			$parentId = $data [$pid];
			if ($root == $parentId) {
				$tree [] = & $list [$key];
			} else {
				if (isset ( $refer [$parentId] )) {
					$parent = & $refer [$parentId];
					$parent [$child] [] = & $list [$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 *
 * @param array $tree
 *        	原来的树
 * @param string $child
 *        	孩子节点的键
 * @param string $order
 *        	排序显示的键，一般是主键 升序排列
 * @param array $list
 *        	过渡用的中间数组，
 * @return array 返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
	if (is_array ( $tree )) {
		$refer = array ();
		foreach ( $tree as $key => $value ) {
			$reffer = $value;
			if (isset ( $reffer [$child] )) {
				unset ( $reffer [$child] );
				tree_to_list ( $value [$child], $child, $order, $list );
			}
			$list [] = $reffer;
		}
		$list = list_sort_by ( $list, $order, $sortby = 'asc' );
	}
	return $list;
}

/**
 * 格式化字节大小
 *
 * @param number $size
 *        	字节数
 * @param string $delimiter
 *        	数字和单位分隔符
 * @return string 格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
	$units = array (
			'B',
			'KB',
			'MB',
			'GB',
			'TB',
			'PB' 
	);
	for($i = 0; $size >= 1024 && $i < 5; $i ++)
		$size /= 1024;
	return round ( $size, 2 ) . $delimiter . $units [$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 *
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function set_redirect_url($url) {
	cookie ( 'redirect_url', $url );
}

/**
 * 获取跳转页面URL
 *
 * @return string 跳转页URL
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_redirect_url() {
	$url = cookie ( 'redirect_url' );
	return empty ( $url ) ? __APP__ : $url;
}

/**
 * 处理插件钩子
 *
 * @param string $hook
 *        	钩子名称
 * @param mixed $params
 *        	传入参数
 * @return void
 */
function hook($hook, $params = array()) {
	\Think\Hook::listen ( $hook, $params );
}

/**
 * 获取插件类的类名
 *
 * @param strng $name
 *        	插件名
 */
function get_addon_class($name) {
	$class = "Addons\\{$name}\\{$name}Addon";
	return $class;
}

/**
 * 获取插件类的配置文件数组
 *
 * @param string $name
 *        	插件名
 */
function get_addon_config($name) {
	$class = get_addon_class ( $name );
	if (class_exists ( $class )) {
		$addon = new $class ();
		return $addon->getConfig ();
	} else {
		return array ();
	}
}

/**
 * 插件显示内容里生成访问插件的url
 *
 * @param string $url
 *        	url
 * @param array $param
 *        	参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array()) {
	$url = parse_url ( $url );
	$case = C ( 'URL_CASE_INSENSITIVE' );
	$addons = $case ? parse_name ( $url ['scheme'] ) : $url ['scheme'];
	$controller = $case ? parse_name ( $url ['host'] ) : $url ['host'];
	$action = trim ( $case ? strtolower ( $url ['path'] ) : $url ['path'], '/' );
	
	/* 解析URL带的参数 */
	if (isset ( $url ['query'] )) {
		parse_str ( $url ['query'], $query );
		$param = array_merge ( $query, $param );
	}
	
	/* 基础参数 */
	$params = array (
			'_addons' => $addons,
			'_controller' => $controller,
			'_action' => $action 
	);
	$params = array_merge ( $params, $param ); // 添加额外参数
	
	return U ( 'Addons/execute', $params );
}

/**
 * 时间戳格式化
 *
 * @param int $time        	
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL, $format = 'Y-m-d H:i') {
	$time = $time === NULL ? NOW_TIME : intval ( $time );
	return date ( $format, $time );
}
/**
 * 获取当前时间戳，精确到毫秒
 */
function microtime_float() {
	list ( $usec, $sec ) = explode ( " ", microtime () );
	return (( float ) $usec + ( float ) $sec);
}

/**
 * 格式化时间戳，精确到毫秒，x代表毫秒
 */
function microtime_format($tag, $time) {
	list ( $usec, $sec ) = explode ( ".", $time );
	$date = date ( $tag, $usec );
	return str_replace ( 'x', $sec, $date );
}
/**
 * 根据用户ID获取用户名
 *
 * @param integer $uid
 *        	用户ID
 * @return string 用户名
 */
function get_username($uid = 0) {
	static $list;
	if (! ($uid && is_numeric ( $uid ))) { // 获取当前登录用户名
		return session ( 'user_auth.username' );
	}
	
	/* 获取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_active_user_list' );
	}
	
	/* 查找用户信息 */
	$key = "u{$uid}";
	if (isset ( $list [$key] )) { // 已缓存，直接使用
		$name = $list [$key];
	} else { // 调用接口获取用户信息
		$User = new User\Api\UserApi ();
		$info = $User->info ( $uid );
		if ($info && isset ( $info [1] )) {
			$name = $list [$key] = $info [1];
			/* 缓存用户 */
			$count = count ( $list );
			$max = C ( 'USER_MAX_CACHE' );
			while ( $count -- > $max ) {
				array_shift ( $list );
			}
			S ( 'sys_active_user_list', $list );
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * 根据用户ID获取用户昵称
 *
 * @param integer $uid
 *        	用户ID
 * @return string 用户昵称
 */
function get_nickname($uid = 0) {
	static $list;
	if (! ($uid && is_numeric ( $uid ))) { // 获取当前登录用户名
		return session ( 'user_auth.username' );
	}
	
	/* 获取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_user_nickname_list' );
	}
	
	/* 查找用户信息 */
	$key = "u{$uid}";
	if (isset ( $list [$key] )) { // 已缓存，直接使用
		$name = $list [$key];
	} else { // 调用接口获取用户信息
		$info = M ( 'Member' )->field ( 'nickname' )->find ( $uid );
		if ($info !== false && $info ['nickname']) {
			$nickname = $info ['nickname'];
			$name = $list [$key] = $nickname;
			/* 缓存用户 */
			$count = count ( $list );
			$max = C ( 'USER_MAX_CACHE' );
			while ( $count -- > $max ) {
				array_shift ( $list );
			}
			S ( 'sys_user_nickname_list', $list );
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * 获取注册时间
 *
 * @param number $uid        	
 * @return string
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_reg_time($uid = 0, $format = "Y-m-d") {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['id'] = $uid;
	$reg_time = M ( 'ucenter_member' )->where ( $where )->getField ( "reg_time" );
	return time_format ( $reg_time, $format );
}

/**
 * 获取用户头像
 *
 * @param number $uid        	
 * @return string
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_face($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where ['id'] = $uid;
	$face = M ( 'ucenter_member' )->where ( $where )->getField ( "headimgurl" );
	if (! $face) {
		$face = __ROOT__ . "/Public/admin/images/getheadimg.jpg";
	}
	return $face;
}
function get_pro_cover($id) {
	$cover = M ( 'Product' )->where ( 'id=' . $id )->getField ( "cover_id" );
	$cover = picture ( $cover );
	return $cover;
}
function get_pro_title($id) {
	$cover = M ( 'Product' )->where ( 'id=' . $id )->getField ( "title" );
	return $cover;
}

/**
 * 获取分类信息并缓存分类
 *
 * @param integer $id
 *        	分类ID
 * @param string $field
 *        	要获取的字段名
 * @return string 分类信息
 */
function get_category($id, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (empty ( $id ) || ! is_numeric ( $id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_category_list' );
	}
	
	/* 获取分类名称 */
	if (! isset ( $list [$id] )) {
		$cate = M ( 'Category' )->find ( $id );
		if (! $cate || 1 != $cate ['status']) { // 不存在分类，或分类被禁用
			return '';
		}
		$list [$id] = $cate;
		S ( 'sys_category_list', $list ); // 更新缓存
	}
	return is_null ( $field ) ? $list [$id] : $list [$id] [$field];
}

/**
 * 获取分类信息并缓存分类
 *
 * @param integer $id
 *        	分类ID
 * @param string $field
 *        	要获取的字段名
 * @return string 分类信息
 */
function get_procate($id, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (empty ( $id ) || ! is_numeric ( $id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_procate_list' );
	}
	
	/* 获取分类名称 */
	if (! isset ( $list [$id] )) {
		$cate = M ( 'procate' )->find ( $id );
		if (! $cate || 1 != $cate ['status']) { // 不存在分类，或分类被禁用
			return '';
		}
		$list [$id] = $cate;
		S ( 'sys_procate_list', $list ); // 更新缓存
	}
	return is_null ( $field ) ? $list [$id] : $list [$id] [$field];
}

/**
 * 获取分类信息并缓存分类
 *
 * @param integer $id
 *        	分类ID
 * @param string $field
 *        	要获取的字段名
 * @return string 分类信息
 */
function get_purcate($id, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (empty ( $id ) || ! is_numeric ( $id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_purcate_list' );
	}
	
	/* 获取分类名称 */
	if (! isset ( $list [$id] )) {
		$cate = M ( 'purcate' )->find ( $id );
		if (! $cate || 1 != $cate ['status']) { // 不存在分类，或分类被禁用
			return '';
		}
		$list [$id] = $cate;
		S ( 'sys_purcate_list', $list ); // 更新缓存
	}
	return is_null ( $field ) ? $list [$id] : $list [$id] [$field];
}
/* 根据ID获取分类标识 */
function get_category_name($id) {
	return get_category ( $id, 'name' );
}

/* 根据ID获取分类名称 */
function get_category_title($id) {
	return get_category ( $id, 'title' );
}

/**
 * 获取文档模型信息
 *
 * @param integer $id
 *        	模型ID
 * @param string $field
 *        	模型字段
 * @return array
 */
function get_document_model($id = null, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (! (is_numeric ( $id ) || is_null ( $id ))) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'DOCUMENT_MODEL_LIST' );
	}
	
	/* 获取模型名称 */
	if (empty ( $list )) {
		$map = array (
				'status' => 1,
				'extend' => 1 
		);
		$model = M ( 'Model' )->where ( $map )->field ( true )->select ();
		foreach ( $model as $value ) {
			$list [$value ['id']] = $value;
		}
		S ( 'DOCUMENT_MODEL_LIST', $list ); // 更新缓存
	}
	
	/* 根据条件返回数据 */
	if (is_null ( $id )) {
		return $list;
	} elseif (is_null ( $field )) {
		return $list [$id];
	} else {
		return $list [$id] [$field];
	}
}

/**
 * 获取文档模型信息
 *
 * @param integer $id
 *        	模型ID
 * @param string $field
 *        	模型字段
 * @return array
 */
function get_product_model($id = null, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (! (is_numeric ( $id ) || is_null ( $id ))) {
		return '';
	}
	
	/* 获取模型名称 */
	if (empty ( $list )) {
		$map = array (
				'status' => 1,
				'extend' => 10 
		);
		
		$model = M ( 'Model' )->where ( $map )->field ( true )->select ();
		foreach ( $model as $value ) {
			$list [$value ['id']] = $value;
		}
		S ( 'PRODUCT_MODEL_LIST', $list ); // 更新缓存
	}
	
	/* 根据条件返回数据 */
	if (is_null ( $id )) {
		return $list;
	} elseif (is_null ( $field )) {
		return $list [$id];
	} else {
		return $list [$id] [$field];
	}
}
/**
 * 获取文档模型信息
 *
 * @param integer $id
 *        	模型ID
 * @param string $field
 *        	模型字段
 * @return array
 */
function get_purchase_model($id = null, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (! (is_numeric ( $id ) || is_null ( $id ))) {
		return '';
	}
	
	/* 获取模型名称 */
	if (empty ( $list )) {
		$map = array (
				'status' => 1,
				'extend' => 16 
		);
		
		$model = M ( 'Model' )->where ( $map )->field ( true )->select ();
		foreach ( $model as $value ) {
			$list [$value ['id']] = $value;
		}
		S ( 'PRODUCT_MODEL_LIST', $list ); // 更新缓存
	}
	
	/* 根据条件返回数据 */
	if (is_null ( $id )) {
		return $list;
	} elseif (is_null ( $field )) {
		return $list [$id];
	} else {
		return $list [$id] [$field];
	}
}

/**
 * 解析UBB数据
 *
 * @param string $data
 *        	UBB字符串
 * @return string 解析为HTML的数据
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function ubb($data) {
	// TODO: 待完善，目前返回原始数据
	return $data;
}

/**
 * 记录行为日志，并执行该行为的规则
 *
 * @param string $action
 *        	行为标识
 * @param string $model
 *        	触发行为的模型名
 * @param int $record_id
 *        	触发行为的记录id
 * @param int $user_id
 *        	执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null) {
	
	// 参数检查
	if (empty ( $action ) || empty ( $model ) || empty ( $record_id )) {
		return '参数不能为空';
	}
	if (empty ( $user_id )) {
		$user_id = is_login ();
	}
	
	// 查询行为,判断是否执行
	$action_info = M ( 'Action' )->getByName ( $action );
	if ($action_info ['status'] != 1) {
		return '该行为被禁用或删除';
	}
	
	// 插入行为日志
	$data ['action_id'] = $action_info ['id'];
	$data ['user_id'] = $user_id;
	$data ['action_ip'] = ip2long ( get_client_ip () );
	$data ['model'] = $model;
	$data ['record_id'] = $record_id;
	$data ['create_time'] = NOW_TIME;
	
	// 解析日志规则,生成日志备注
	if (! empty ( $action_info ['log'] )) {
		if (preg_match_all ( '/\[(\S+?)\]/', $action_info ['log'], $match )) {
			$log ['user'] = $user_id;
			$log ['record'] = $record_id;
			$log ['model'] = $model;
			$log ['time'] = NOW_TIME;
			$log ['data'] = array (
					'user' => $user_id,
					'model' => $model,
					'record' => $record_id,
					'time' => NOW_TIME 
			);
			foreach ( $match [1] as $value ) {
				$param = explode ( '|', $value );
				if (isset ( $param [1] )) {
					$replace [] = call_user_func ( $param [1], $log [$param [0]] );
				} else {
					$replace [] = $log [$param [0]];
				}
			}
			$data ['remark'] = str_replace ( $match [0], $replace, $action_info ['log'] );
		} else {
			$data ['remark'] = $action_info ['log'];
		}
	} else {
		// 未定义日志规则，记录操作url
		$data ['remark'] = '操作url：' . $_SERVER ['REQUEST_URI'];
	}
	
	M ( 'ActionLog' )->add ( $data );
	
	if (! empty ( $action_info ['rule'] )) {
		// 解析行为
		$rules = parse_action ( $action, $user_id );
		
		// 执行行为
		$res = execute_action ( $rules, $action_info ['id'], $user_id );
	}
}

/**
 * 解析行为规则
 * 规则定义 table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 * field->要操作的字段；
 * condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 * rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 * cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 * max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 *
 * @param string $action
 *        	行为id或者name
 * @param int $self
 *        	替换规则里的变量为执行用户的id
 * @return boolean array: ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self) {
	if (empty ( $action )) {
		return false;
	}
	
	// 参数支持id或者name
	if (is_numeric ( $action )) {
		$map = array (
				'id' => $action 
		);
	} else {
		$map = array (
				'name' => $action 
		);
	}
	
	// 查询行为信息
	$info = M ( 'Action' )->where ( $map )->find ();
	if (! $info || $info ['status'] != 1) {
		return false;
	}
	
	// 解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
	$rules = $info ['rule'];
	$rules = str_replace ( '{$self}', $self, $rules );
	$rules = explode ( ';', $rules );
	$return = array ();
	foreach ( $rules as $key => &$rule ) {
		$rule = explode ( '|', $rule );
		foreach ( $rule as $k => $fields ) {
			$field = empty ( $fields ) ? array () : explode ( ':', $fields );
			if (! empty ( $field )) {
				$return [$key] [$field [0]] = $field [1];
			}
		}
		// cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
		if (! array_key_exists ( 'cycle', $return [$key] ) || ! array_key_exists ( 'max', $return [$key] )) {
			unset ( $return [$key] ['cycle'], $return [$key] ['max'] );
		}
	}
	
	return $return;
}

/**
 * 执行行为
 *
 * @param array $rules
 *        	解析后的规则数组
 * @param int $action_id
 *        	行为id
 * @param array $user_id
 *        	执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null) {
	if (! $rules || empty ( $action_id ) || empty ( $user_id )) {
		return false;
	}
	
	$return = true;
	foreach ( $rules as $rule ) {
		
		// 检查执行周期
		$map = array (
				'action_id' => $action_id,
				'user_id' => $user_id 
		);
		$map ['create_time'] = array (
				'gt',
				NOW_TIME - intval ( $rule ['cycle'] ) * 3600 
		);
		$exec_count = M ( 'ActionLog' )->where ( $map )->count ();
		if ($exec_count > $rule ['max']) {
			continue;
		}
		
		// 执行数据库操作
		$Model = M ( ucfirst ( $rule ['table'] ) );
		$field = $rule ['field'];
		$res = $Model->where ( $rule ['condition'] )->setField ( $field, array (
				'exp',
				$rule ['rule'] 
		) );
		
		if (! $res) {
			$return = false;
		}
	}
	return $return;
}

// 基于数组创建目录和文件
function create_dir_or_files($files) {
	foreach ( $files as $key => $value ) {
		if (substr ( $value, - 1 ) == '/') {
			mkdir ( $value );
		} else {
			@file_put_contents ( $value, '' );
		}
	}
}

if (! function_exists ( 'array_column' )) {
	function array_column(array $input, $columnKey, $indexKey = null) {
		$result = array ();
		if (null === $indexKey) {
			if (null === $columnKey) {
				$result = array_values ( $input );
			} else {
				foreach ( $input as $row ) {
					$result [] = $row [$columnKey];
				}
			}
		} else {
			if (null === $columnKey) {
				foreach ( $input as $row ) {
					$result [$row [$indexKey]] = $row;
				}
			} else {
				foreach ( $input as $row ) {
					$result [$row [$indexKey]] = $row [$columnKey];
				}
			}
		}
		return $result;
	}
}

/**
 * 获取表名（不含表前缀）
 *
 * @param string $model_id        	
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null) {
	if (empty ( $model_id )) {
		return false;
	}
	$Model = M ( 'Model' );
	$name = '';
	$info = $Model->getById ( $model_id );
	if ($info ['extend'] != 0) {
		$name = $Model->getFieldById ( $info ['extend'], 'name' ) . '_';
	}
	$name .= $info ['name'];
	return $name;
}

/**
 * 获取属性信息并缓存
 *
 * @param integer $id
 *        	属性ID
 * @param string $field
 *        	要获取的字段名
 * @return string 属性信息
 */
function get_model_attribute($model_id, $group = true) {
	static $list;
	
	/* 非法ID */
	if (empty ( $model_id ) || ! is_numeric ( $model_id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'attribute_list' );
	}
	
	/* 获取属性 */
	if (! isset ( $list [$model_id] )) {
		$map = array (
				'model_id' => $model_id 
		);
		$extend = M ( 'Model' )->getFieldById ( $model_id, 'extend' );
		
		if ($extend) {
			$map = array (
					'model_id' => array (
							"in",
							array (
									$model_id,
									$extend 
							) 
					) 
			);
		}
		$info = M ( 'Attribute' )->where ( $map )->select ();
		$list [$model_id] = $info;
		// S('attribute_list', $list); //更新缓存
	}
	
	$attr = array ();
	foreach ( $list [$model_id] as $value ) {
		$attr [$value ['id']] = $value;
	}
	
	if ($group) {
		$sort = M ( 'Model' )->getFieldById ( $model_id, 'field_sort' );
		
		if (empty ( $sort )) { // 未排序
			$group = array (
					1 => array_merge ( $attr ) 
			);
		} else {
			$group = json_decode ( $sort, true );
			
			$keys = array_keys ( $group );
			foreach ( $group as &$value ) {
				foreach ( $value as $key => $val ) {
					$value [$key] = $attr [$val];
					unset ( $attr [$val] );
				}
			}
			
			if (! empty ( $attr )) {
				$group [$keys [0]] = array_merge ( $group [$keys [0]], $attr );
			}
		}
		$attr = $group;
	}
	return $attr;
}

/**
 * 通过用户ID获得所在分组
 *
 * @param unknown $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_group_by_uid($uid) {
	$where ['uid'] = $uid;
	$group_id = M ( 'AuthGroupAccess' )->where ( $where )->getField ( "group_id" );
	$where_group ['id'] = $group_id;
	$info = M ( 'auth_group' )->where ( $where_group )->find ();
	return $info;
}

/**
 * 通过用户ID获得所在分组
 *
 * @param unknown $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_group_title_by_uid($uid) {
	$where ['uid'] = $uid;
	$group_id = M ( 'AuthGroupAccess' )->where ( $where )->getField ( "group_id" );
	$where_group ['id'] = $group_id;
	$info = M ( 'auth_group' )->where ( $where_group )->getField ( "title" );
	return $info;
}

/**
 * 通过分组ID获得分组标题
 *
 * @param unknown $uid        	
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_group_title_by_id($uid) {
	$where_group ['id'] = $uid;
	$info = M ( 'auth_group' )->where ( $where_group )->getField ( "title" );
	return $info;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5'); 调用Admin模块的User接口
 *
 * @param string $name
 *        	格式 [模块名]/接口名/方法名
 * @param array|string $vars
 *        	参数
 */
function api($name, $vars = array()) {
	$array = explode ( '/', $name );
	$method = array_pop ( $array );
	$classname = array_pop ( $array );
	$module = $array ? array_pop ( $array ) : 'Common';
	$callback = $module . '\\Api\\' . $classname . 'Api::' . $method;
	if (is_string ( $vars )) {
		parse_str ( $vars, $vars );
	}
	return call_user_func_array ( $callback, $vars );
}

/**
 * 根据条件字段获取指定表的数据
 *
 * @param mixed $value
 *        	条件，可用常量或者数组
 * @param string $condition
 *        	条件字段
 * @param string $field
 *        	需要返回的字段，不传则返回整个数据
 * @param string $table
 *        	需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null) {
	if (empty ( $value ) || empty ( $table )) {
		return false;
	}
	
	// 拼接参数
	$map [$condition] = $value;
	$info = M ( ucfirst ( $table ) )->where ( $map );
	if (empty ( $field )) {
		$info = $info->field ( true )->find ();
	} else {
		$info = $info->getField ( $field );
	}
	return $info;
}

/**
 * 获取链接信息
 *
 * @param int $link_id        	
 * @param string $field        	
 * @return 完整的链接信息或者某一字段
 * @author huajie <banhuajie@163.com>
 */
function get_link($link_id = null, $field = 'url') {
	$link = '';
	if (empty ( $link_id )) {
		return $link;
	}
	$link = M ( 'Url' )->getById ( $link_id );
	if (empty ( $field )) {
		return $link;
	} else {
		return $link [$field];
	}
}

/**
 * 获取文档封面图片
 *
 * @param int $cover_id        	
 * @param string $field        	
 * @return 完整的数据 或者 指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = null) {
	if (empty ( $cover_id )) {
		return false;
	}
	if (is_string ( $cover_id )) {
		$cover_id = intval ( $cover_id );
	}
	$picture = M ( 'Picture' )->where ( array (
			'status' => 1 
	) )->getById ( $cover_id );
	return empty ( $field ) ? $picture : $picture [$field];
}

/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 *
 * @param number $pos
 *        	推荐位的值
 * @param number $contain
 *        	指定推荐位
 * @return boolean true 包含 ， false 不包含
 * @author huajie <banhuajie@163.com>
 */
function check_document_position($pos = 0, $contain = 0) {
	if (empty ( $pos ) || empty ( $contain )) {
		return false;
	}
	
	// 将两个参数进行按位与运算，不为0则表示$contain属于$pos
	$res = $pos & $contain;
	if ($res !== 0) {
		return true;
	} else {
		return false;
	}
}

/**
 * 获取数据的所有子孙数据的id值
 *
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */
function get_stemma($pids, Model &$model, $field = 'id') {
	$collection = array ();
	
	// 非空判断
	if (empty ( $pids )) {
		return $collection;
	}
	
	if (is_array ( $pids )) {
		$pids = trim ( implode ( ',', $pids ), ',' );
	}
	$result = $model->field ( $field )->where ( array (
			'pid' => array (
					'IN',
					( string ) $pids 
			) 
	) )->select ();
	$child_ids = array_column ( ( array ) $result, 'id' );
	
	while ( ! empty ( $child_ids ) ) {
		$collection = array_merge ( $collection, $result );
		$result = $model->field ( $field )->where ( array (
				'pid' => array (
						'IN',
						$child_ids 
				) 
		) )->select ();
		$child_ids = array_column ( ( array ) $result, 'id' );
	}
	return $collection;
}

/**
 * 获取图片路径
 *
 * @param unknown $cover_id        	
 * @return string
 */
function picture($cover_id) {
	$picture = M ( 'Picture' )->where ( array (
			'status' => 1 
	) )->getById ( $cover_id );
	return __ROOT__ . $picture ['path'];
}
/**
 * 生成缩略图
 *
 * @param unknown $cover_id        	
 * @param number $width        	
 * @param number $height        	
 * @return string
 */
function thumb($cover_id, $width = 100, $height = 100) {
	$picture = M ( 'Picture' )->where ( array (
			'status' => 1 
	) )->cache ( true, 600 )->getById ( $cover_id );
	if (empty ( $picture )) {
		return __ROOT__ . '/Public/static/assets/img/nopic.png';
	}
	$where ['width'] = $width;
	$where ['height'] = $height;
	$where ['cover_id'] = $picture ['id'];
	$truename = M ( 'Thumb' )->where ( $where )->getField ( "savename" );
	
	/* 找到缩略图，返回保存路径 */
	if ($truename)
		return __ROOT__ . $truename;
	
	$img = new \Think\Image ();
	$img->open ( "." . $picture ['path'] );
	$savename = "." . $picture ['path'] . '_' . $width . 'x' . $height . '.jpg';
	$rename = $picture ['path'] . '_' . $width . 'x' . $height . '.jpg';
	$tempname = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "\\" . $rename;
	switch (C ( 'thumb' )) {
		case 1 : // 比例缩放
			$type = \Think\Image::IMAGE_THUMB_SCALE;
			break;
		case 2 : // 缩放后填充
			$type = \Think\Image::IMAGE_THUMB_FILLED;
			break;
		case 3 : // 居中剪裁
			$type = \Think\Image::IMAGE_THUMB_CENTER;
			break;
		case 4 : // 左上角剪裁
			$type = \Think\Image::IMAGE_THUMB_NORTHWEST;
			break;
		case 5 : // 右下角剪裁
			$type = \Think\Image::IMAGE_THUMB_SOUTHEAST;
			break;
		case 6 : // 固定尺寸剪裁
			$type = \Think\Image::IMAGE_THUMB_FIXED;
			break;
	}
	$img->thumb ( $width, $height, $type )->save ( $savename );
	
	if (is_file ( $tempname )) {
		$data ['cover_id'] = $picture ['id'];
		$data ['savename'] = $rename;
		$data ['width'] = $width;
		$data ['height'] = $height;
		M ( 'Thumb' )->add ( $data );
		return __ROOT__ . $rename;
	} else {
		thumb ( $cover_id, $width, $height );
	}
}

/**
 *
 * @param string $to
 *        	收件人
 * @param string $subject
 *        	主题
 * @param string $body
 *        	内容
 * @param string $name
 *        	发送人
 * @param string $attachment
 *        	附件列表
 * @return Ambigous <boolean, string>
 */
function send_mail($to = '', $subject = '', $body = '', $name = '', $attachment = null) {
	$from_email = C ( 'MAIL_SMTP_USER' );
	$from_name = C ( 'WEB_SITE' );
	$reply_email = '';
	$reply_name = '';
	import ( 'ORG.PHPMailer.phpmailer' );
	$mail = new PHPMailer ();
	$mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP (); // 设定使用SMTP服务
	$mail->SMTPDebug = 0; // 关闭SMTP调试功能
	$mail->SMTPAuth = true; // 启用 SMTP 验证功能
	$mail->SMTPSecure = 'ssl'; // 使用安全协议
	$mail->Host = C ( 'MAIL_SMTP_HOST' ); // SMTP 服务器
	$mail->Port = C ( 'MAIL_SMTP_PORT' ); // SMTP服务器的端口号
	$mail->Username = C ( 'MAIL_SMTP_USER' ); // SMTP服务器用户名
	$mail->Password = C ( 'MAIL_SMTP_PASS' ); // SMTP服务器密码
	
	$mail->SetFrom ( $from_email, $from_name );
	$replyEmail = $reply_email ? $reply_email : $from_email;
	$replyName = $reply_name ? $reply_name : $from_name;
	
	if ($name == '') {
		$name = C ( 'WEB_SITE' ); // 发送者名称为空时，默认使用网站名称
	}
	if ($subject == '') {
		$subject = C ( 'WEB_SITE_TITLE' ); // 邮件主题为空时，默认使用网站标题
	}
	if ($body == '') {
		$body = C ( 'WEB_SITE_DESCRIPTION' ); // 邮件内容为空时，默认使用网站描述
	}
	$mail->AddReplyTo ( $replyEmail, $replyName );
	$mail->Subject = $subject;
	$mail->MsgHTML ( $body ); // 解析
	$mail->AddAddress ( $to, $name );
	if (is_array ( $attachment )) { // 添加附件
		foreach ( $attachment as $file ) {
			is_file ( $file ) && $mail->AddAttachment ( $file );
		}
	}
	return $mail->Send () ? true : $mail->ErrorInfo; // 返回错误信息
}

/**
 * 发送短信
 *
 * @param number $mobile        	
 * @param string $content        	
 */
function send_sms($mobile, $content) {
	// 智验apiKey
	$apiKey = "5ab6c721698e4d46be0710dd55c134da";
	// 应用appId
	$appId = "i8Dou9n99426";
	// 应用绑定模板ID
	$templateId = "RSETSESEKESE";
	// 手机号
	// 参数
	$param = $content . ",2分钟";
	$url = "https://sms.zhiyan.net/sms/template_send.json";
	$json_arr = array (
			"mobile" => $mobile,
			"param" => $param,
			"templateId" => $templateId,
			"appId" => $appId,
			"apiKey" => $apiKey,
			"extend" => "",
			"uid" => "" 
	);
	$array = json_encode ( $json_arr );
	// 调用接口
	// 初始化curl
	$ch = curl_init ();
	// 参数设置
	$res = curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $array );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	$result = json_decode ( $result, true );
}

/**
 * 发送短信
 *
 * @param number $mobile
 * @param string $content
 */
function auto_send_sms($mobile, $tmpid, $content) {
	// 智验apiKey
	$apiKey = "70f3861fc00e44ea8c4998f41d7718f2";
	// 应用appId
	$appId = "R9I53TK7DW4a";
	// 应用绑定模板ID
	$templateId = $tmpid;
	// 手机号
	// 参数
	$param = $content;
	$url = "https://sms.zhiyan.net/sms/template_send.json";
	$json_arr = array (
			"mobile" => $mobile,
			"param" => $param,
			"templateId" => $templateId,
			"appId" => $appId,
			"apiKey" => $apiKey,
			"extend" => "",
			"uid" => ""
	);
	$array = json_encode ( $json_arr );
	// 调用接口
	// 初始化curl
	$ch = curl_init ();
	// 参数设置
	$res = curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $array );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	$result = json_decode ( $result, true );
}

/**
 * 获取对应公众账户信息
 *
 * @param unknown $wechatid        	
 * @return boolean
 */
function get_wechat_name($wechatid) {
	$map ['wechatid'] = $wechatid;
	$info = M ( 'WechatConfig' )->where ( $map )->find ();
	return $info ['name'];
}
function get_def_wechatid() {
	$wechatid = session ( 'wechatid' );
	if (! $wechatid) {
		$wechatid = $_GET ['wechatid'];
	}
	if (! $wechatid) {
		$wechatid = M ( "wechat_config" )->where ( "status=1" )->getField ( "wechatid" );
	}
	return $wechatid;
}

/**
 * 去除HTML JS CSS
 *
 * @param string $string
 *        	需要处理的字符串
 * @return string 纯文本字符串
 */
function noHtml($string) {
	if (empty ( $string )) {
		return "";
	}
	$search = array (
			"'<script[^>]*?>.*?</script>'si",
			"'<style[^>]*?>.*?</style>'si",
			"'<[/!]*?[^<>]*?>'si",
			"'<!--[/!]*?[^<>]*?>'si" 
	);
	$replace = array (
			"",
			"",
			"",
			"" 
	);
	$string = preg_replace ( $search, $replace, $string );
	return str_replace ( array (
			"　",
			" ",
			"\r",
			"\n",
			"&nbsp;" 
	), "", $string );
}
function baidufanyi($q, $from = "auto", $to = "auto") {
	$url = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=" . "iaL7uBFOTq2Gr6AWkgyAxx0s";
	$url .= "&q=" . urlencode ( $q );
	$url .= "&from=" . $from;
	$url .= "&to=" . $to;
	$result = json_decode ( file_get_contents ( $url ), true );
	$dst = $result ['trans_result'] [0] ['dst'];
	$dst = str_replace ( " ", "", $dst );
	return $dst;
}
/**
 * 将一个字符串部分字符用*替代隐藏
 *
 * @param string $string
 *        	待转换的字符串
 * @param int $bengin
 *        	起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int $len
 *        	需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int $type
 *        	转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string $glue
 *        	分割符
 * @return string 处理后的字符串
 */
function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
	if (empty ( $string ))
		return false;
	$array = array ();
	if ($type == 0 || $type == 1 || $type == 4) {
		$strlen = $length = mb_strlen ( $string );
		while ( $strlen ) {
			$array [] = mb_substr ( $string, 0, 1, "utf8" );
			$string = mb_substr ( $string, 1, $strlen, "utf8" );
			$strlen = mb_strlen ( $string );
		}
	}
	if ($type == 0) {
		for($i = $bengin; $i < ($bengin + $len); $i ++) {
			if (isset ( $array [$i] ))
				$array [$i] = "*";
		}
		$string = implode ( "", $array );
	} else if ($type == 1) {
		$array = array_reverse ( $array );
		for($i = $bengin; $i < ($bengin + $len); $i ++) {
			if (isset ( $array [$i] ))
				$array [$i] = "*";
		}
		$string = implode ( "", array_reverse ( $array ) );
	} else if ($type == 2) {
		$array = explode ( $glue, $string );
		$array [0] = hideStr ( $array [0], $bengin, $len, 1 );
		$string = implode ( $glue, $array );
	} else if ($type == 3) {
		$array = explode ( $glue, $string );
		$array [1] = hideStr ( $array [1], $bengin, $len, 0 );
		$string = implode ( $glue, $array );
	} else if ($type == 4) {
		$left = $bengin;
		$right = $len;
		$tem = array ();
		for($i = 0; $i < ($length - $right); $i ++) {
			if (isset ( $array [$i] ))
				$tem [] = $i >= $left ? "*" : $array [$i];
		}
		$array = array_chunk ( array_reverse ( $array ), $right );
		$array = array_reverse ( $array [0] );
		for($i = 0; $i < $right; $i ++) {
			$tem [] = $array [$i];
		}
		$string = implode ( "", $tem );
	}
	return $string;
}
// 随机生成8位奖单号码
function getRand($pro_id) {
	$product = M ( 'Product' )->where ( 'id=' . $pro_id )->find ();
	$seed = rand ( 10000001, $product ['total'] + 10000000 );
	$map ['pro_id'] = $pro_id;
	do {
		$seed = rand ( 10000001, $product ['total'] + 10000000 );
		$map ['mynum'] = $seed;
		$flag = M ( 'Orderlist' )->where ( $map )->find ();
	} while ( $flag != null );
	return $seed;
}
// 发送短信
function sendsms($mobile = 0, $content = '') {
	$sms_appid = '1366';
	$sms_key = '53df2cacfd52804d4aff0ff17cbc986bdd7c601b';
	$url = "http://sms.bechtech.cn/Api/send/data/json?accesskey=" . $sms_appid . "&secretkey=" . $sms_key . "&mobile=" . $mobile . "&content=" . urlencode ( $content );
	return file_get_contents ( $url );
}
function get_openid($uid = 0) {
	if ($uid == 0) {
		$uid = is_login ();
	}
	$where['id'] = $uid;
	$openid = M ( 'ucenter_member' )->where ( $where )->getField ( "openid" );
	return $openid;
}

// 获取开奖时间
function getOpentime() {
	$now = microtime_float ();
	$nowhour = microtime_format ( "H", $now );
	$nowi = microtime_format ( "i", $now );
	$nows = microtime_format ( "s", $now );
	$nowx = microtime_format ( "x", $now ) / 10000;
	if ($nowhour < 20) {
		$new = $now + 60 * 60 * (21 - $nowhour) - $nowi * 60;
	} else {
		$new = $now + 60 * 60 * 24;
	}
	return $new;
}

// 获取手机验证码
function getVerify($user, $carcount) {
	$data = array ();
	$mobile = $user ['mobile'];
	$code = rand ( 1000000, 9999999 ) . rand ( 100000, 999999 );
	$data ['number'] = $code;
	$data ['uid'] = $user ['id'];
	$data ['mobile'] = $mobile;
	$data ['create_time'] = NOW_TIME;
	$data ['status'] = $carcount;
	M ( 'Verifycode' )->add ( $data );
	return $code;
}

// 在线交易订单支付处理函数
// 函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
// 返回值：如果订单已经成功支付，返回true，否则返回false；
function checkorderstatus($ordid) {
	$ordstatus = M ( 'Order' )->where ( 'id=' . $ordid )->getField ( 'status' );
	if ($ordstatus == 2) {
		return true;
	} else {
		return false;
	}
}
// 处理订单函数
// 更新订单状态，写入订单支付后返回的数据
function orderhandle($ordid) {
	$orderstatus = M ( 'Order' )->where ( 'id=' . $ordid )->getField ( 'status' );
	if ($orderstatus == 2) {
		return true;
	} else {
		// 修改订单状态
		M ( 'Order' )->where ( 'id=' . $ordid )->setField ( 'status', 2 );
		// 生成随机数//修改参与人数
		$carcount = M ( 'Order' )->where ( 'id=' . $ordid )->getField ( 'Length' ); // 夺宝人次
		if ($carcount > 9) {
			$user = M ( 'UcenterMember' )->where ( 'id=' . is_login () )->find ();
			$carcount = floor ( $carcount / 10 );
			$code = getVerify ( $user, $carcount );
			$content = "99抵千元验证码：恭喜您获得" . $carcount . "张汽车一千元现金抵用券验证码（该验证码可在指定经销商处使用" . $carcount . "次，一台车限用1张，车型不限）：" . $code . "【99车盟汇】";
			sendsms ( $user ['mobile'], $content );
		}
		$orderlist = M ( 'Orderlist' )->where ( 'order_id=' . $ordid )->select ();
		$temparr = M ( 'Product' )->where ( 'id=' . $orderlist [0] ['pro_id'] )->getField ( 'allnum' );
		$restnum = explode ( ',', $temparr );
		$seed = rand ( 0, (count ( $restnum ) - 1) );
		M ( 'Orderlist' )->where ( 'id=' . $orderlist [0] ['id'] )->setField ( 'mynum', $restnum [$seed] );
		unset ( $restnum [$seed] );
		sort ( $restnum );
		$temp = implode ( ',', $restnum );
		M ( 'Product' )->where ( 'id=' . $orderlist [0] ['pro_id'] )->setField ( 'allnum', $temp );
		$acount = count ( $orderlist );
		if ($acount > 1) {
			for($i = 1; $i < $acount; $i ++) {
				if ($orderlist [$i] ['pro_id'] != $orderlist [$i - 1] ['pro_id']) {
					$temp = implode ( ',', $restnum );
					M ( 'Product' )->where ( 'id=' . $orderlist [$i - 1] ['pro_id'] )->setField ( 'allnum', $temp );
					$temparr = M ( 'Product' )->where ( 'id=' . $orderlist [$i] ['pro_id'] )->getField ( 'allnum' );
					$restnum = explode ( ',', $temparr );
				}
				$seed = rand ( 0, count ( $restnum ) - 1 );
				M ( 'Orderlist' )->where ( 'id=' . $orderlist [$i] ['id'] )->setField ( 'mynum', $restnum [$seed] );
				unset ( $restnum [$seed] );
				sort ( $restnum );
			}
			$temp = implode ( ',', $restnum );
			M ( 'Product' )->where ( 'id=' . $orderlist [$i - 1] ['pro_id'] )->setField ( 'allnum', $temp );
		}
		$product_id = M ( 'Orderlist' )->where ( 'order_id=' . $ordid )->Distinct ( true )->field ( 'pro_id' )->select ();
		for($j = 0; $j < count ( $product_id ); $j ++) {
			$search1 ['mynum'] = array (
					'neq',
					0 
			);
			$search1 ['pro_id'] = $product_id [$j] ['pro_id'];
			$pro = M ( 'Orderlist' )->where ( $search1 )->select ();
			$total = M ( 'Product' )->where ( 'id=' . $product_id [$j] ['pro_id'] )->getField ( 'total' );
			if (count ( $pro ) == intval ( $total )) {
				M ( 'Product' )->where ( 'id=' . $product_id [$j] ['pro_id'] )->setField ( 'state', 1 );
				$opentime = getOpentime ();
				M ( 'Product' )->where ( 'id=' . $product_id [$j] ['pro_id'] )->setField ( 'update_time', $opentime );
			}
		}
	}
}

// 处理订单函数(这个是微信的函数)
// 此处是微信函数，请注意修改！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
// 更新订单状态，写入订单支付后返回的数据
function wcorderhandle($ordid) {
	$map ['wcorderid'] = $ordid;
	$orderstatus = M ( 'Order' )->where ( $map )->getField ( 'status' );
	if ($orderstatus == 2) {
		return false;
	} else {
		// 修改订单状态
		$map ['wcorderid'] = $ordid;
		M ( 'Order' )->where ( $map )->setField ( 'status', 2 );
		$ordid = M ( 'Order' )->where ( $map )->getField ( 'id' );
		// 生成随机数//修改参与人数
		$carcount = M ( 'Order' )->where ( 'wcorderid=' . $ordid )->getField ( 'Length' ); // 夺宝人次
		if ($carcount > 9) {
			$user = M ( 'UcenterMember' )->where ( 'id=' . is_login () )->find ();
			$carcount = floor ( $carcount / 10 );
			$code = getVerify ( $user, $carcount );
			$content = "99抵千元验证码：恭喜您获得" . $carcount . "张汽车一千元现金抵用券验证码（该验证码可在指定经销商处使用" . $carcount . "次，一台车限用1张，车型不限）：" . $code . "【99车盟汇】";
			sendsms ( $user ['mobile'], $content );
		}
		
		$orderlist = M ( 'Orderlist' )->where ( 'order_id=' . $ordid )->select ();
		$temparr = M ( 'Product' )->where ( 'id=' . $orderlist [0] ['pro_id'] )->getField ( 'allnum' );
		$restnum = explode ( ',', $temparr );
		$seed = rand ( 0, (count ( $restnum ) - 1) );
		M ( 'Orderlist' )->where ( 'id=' . $orderlist [0] ['id'] )->setField ( 'mynum', $restnum [$seed] );
		unset ( $restnum [$seed] );
		sort ( $restnum );
		$temp = implode ( ',', $restnum );
		M ( 'Product' )->where ( 'id=' . $orderlist [0] ['pro_id'] )->setField ( 'allnum', $temp );
		$acount = count ( $orderlist );
		if ($acount > 1) {
			for($i = 1; $i < $acount; $i ++) {
				if ($orderlist [$i] ['pro_id'] != $orderlist [$i - 1] ['pro_id']) {
					$temp = implode ( ',', $restnum );
					M ( 'Product' )->where ( 'id=' . $orderlist [$i - 1] ['pro_id'] )->setField ( 'allnum', $temp );
					$temparr = M ( 'Product' )->where ( 'id=' . $orderlist [$i] ['pro_id'] )->getField ( 'allnum' );
					$restnum = explode ( ',', $temparr );
				}
				$seed = rand ( 0, count ( $restnum ) - 1 );
				M ( 'Orderlist' )->where ( 'id=' . $orderlist [$i] ['id'] )->setField ( 'mynum', $restnum [$seed] );
				unset ( $restnum [$seed] );
				sort ( $restnum );
			}
			$temp = implode ( ',', $restnum );
			M ( 'Product' )->where ( 'id=' . $orderlist [$i - 1] ['pro_id'] )->setField ( 'allnum', $temp );
		}
		$product_id = M ( 'Orderlist' )->where ( 'order_id=' . $ordid )->Distinct ( true )->field ( 'pro_id' )->select ();
		for($j = 0; $j < count ( $product_id ); $j ++) {
			$search1 ['mynum'] = array (
					'neq',
					0 
			);
			$search1 ['pro_id'] = $product_id [$j] ['pro_id'];
			$pro = M ( 'Orderlist' )->where ( $search1 )->select ();
			$total = M ( 'Product' )->where ( 'id=' . $product_id [$j] ['pro_id'] )->getField ( 'total' );
			if (count ( $pro ) == intval ( $total )) {
				M ( 'Product' )->where ( 'id=' . $product_id [$j] ['pro_id'] )->setField ( 'state', 1 );
				$opentime = getOpentime ();
				M ( 'Product' )->where ( 'id=' . $product_id [$j] ['pro_id'] )->setField ( 'update_time', $opentime );
			}
		}
		return true;
	}
}
