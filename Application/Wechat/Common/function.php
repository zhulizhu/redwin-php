<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 *
 * @param integer $id
 *        	验证码ID
 * @return boolean 检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1) {
	$verify = new \Think\Verify ();
	return $verify->check ( $code, $id );
}

function get_wechatid(){
	$wechatid = session('wechatid');
	if(empty($wechatid)){
		$wechatid = $_GET['wechatid'];
	}
	if(empty($wechatid)){
		$wechatid = M('WechatConfig')->getField("wechatid");
	}
	return $wechatid;
}

function get_count_coupons($uid=0){
	if($uid==0){
		$uid = is_login();
	}
	$where['uid'] = $uid;
	$where['status'] = 1;
	$count = M('coupons_list')->where($where)->count();
	
	$count = $count + M('userhb_log')->where($where)->count();
	
	return $count;
}

/**
 * 自动获得微信的appID
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_wechat_appid(){
	$wechatid = get_wechatid();
	$where['wechatid'] = $wechatid;
	$appID = M('WechatConfig')->where($where)->getField("appID");
	return $appID;
}

function get_wechatinfo_by_id($wechatid = 0){
	if($wechatid==0){
		$wechatid = get_wechatid();
	}
	$where['wechatid'] = $wechatid;
	$info = M('WechatConfig')->where($where)->find();
	return $info;
}

/**
 * 获取订单信息
 * @param number $type  默认：所有，-1：已删除，0：待支付，1：待收货货，2：待评价，3：已完成
 * @param number $uid
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_order_list($type=10,$uid=0){
	if($uid==0){
		$uid = is_login();
	}
	$where['uid'] = $uid;
	if($type!=10){
		$where['status'] = $type;
	}

	if($type==9){
		$where['status'] = 1;
		$where['express_time'] = array("eq",0);
	}else if($type==1){
		$where['express_time'] = array("neq",0);
	}

	$list = M('Order')->where($where)->select();
	return $list;
}


/**
 * 获取积分订单信息
 * @param number $type  默认：所有，-1：已删除，0：待支付，1：待收货货，2：待评价，3：已完成
 * @param number $uid
 * @return unknown
 * @author 智网天下科技 http://www.cheewo.com
 */
function get_jforder_list($type=10,$uid=0){
	if($uid==0){
		$uid = is_login();
	}
	$where['uid'] = $uid;
	if($type!=10){
		$where['status'] = $type;
	}
	$list = M('jifen_order')->where($where)->select();
	return $list;
}



/**
 * 获取列表总行数
 *
 * @param string $category
 *        	分类ID
 * @param integer $status
 *        	数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1) {
	static $count;
	if (! isset ( $count [$category] )) {
		$count [$category] = D ( 'Document' )->listCount ( $category, $status );
	}
	return $count [$category];
}


/**
 * 模板快捷调用广告方法
 *
 * @param number $pid        	
 * @param string $tpl        	
 */
function Ad($pid = 0, $tpl = "") {
	$Home = A ( 'Home' );
	$Home->ad ( $pid, $tpl );
}



/**
 * 获取段落总数
 *
 * @param string $id
 *        	文档ID
 * @return integer 段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id) {
	static $count;
	if (! isset ( $count [$id] )) {
		$count [$id] = D ( 'Document' )->partCount ( $id );
	}
	return $count [$id];
}

/**
 * 获取导航URL
 *
 * @param string $url
 *        	导航URL
 * @return string 解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url) {
	switch ($url) {
		case 'http://' === substr ( $url, 0, 7 ) :
		case '#' === substr ( $url, 0, 1 ) :
			break;
		default :
			$url = U ( $url );
			break;
	}
	return $url;
}

/**
 * 左侧快速调用方法
 * @param 模板文件 $tpl
 */
function leftnav($tpl="left"){
	$Home = A ( 'Home' );
	$Home->LeftNav ( $_REQUEST , $tpl );
}

function return_reply_type($type = 0){
	switch($type){
		case 0 : return "text"; break;
		case 1 : return "image"; break;
	}
}

function return_reply_sc_type($type = 0){
	switch($type){
		case 0 : return "image"; break;
		case 1 : return "image"; break;
	}
}
