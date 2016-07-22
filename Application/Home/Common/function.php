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

/**
*产品左侧调用方法
*/
function Product_LeftNav($theArray,$tpl="Product_left"){
	$Home = A ( 'Home' );
	$Home->Product_LeftNav($theArray, $tpl );	
}
/**
 * 通过分类ID获取URL
 * @param unknown $cate
 * @author 智网天下科技 http://www.cheewo.com
 */
function getcateu($cate){
	$cate = M('Category')->where('id='.$cate)->find();
	$model = M('Model')->where('id='.$cate['model'])->getField('name');
	switch ($cate['lefttype']){
		case 0:
			$action = "Article"."/lists";
			$q = "category=".$cate['name'];
			break;
		case 1:
			$action = "Article"."/intro";
			$q = "category=".$cate['name'];
			break;
	}
	$url = U($action,$q);
	return $url;
}

function adimg($pid=0){
	if($pid!=0){
		$map['pid'] = $pid;
		$map['status'] = 1;
		$info = M('Ads')->where($map)->order('rand()')->find();
		$picture = thumb($info['picture'],1920,600);
		echo $picture;
	}
}

/**
 * 通过分类ID获取URL
 * @param unknown $cate
 * @author 智网天下科技 http://www.cheewo.com
 */
function getprocateu($cate){
	$cate = M('Procate')->where('id='.$cate)->find();
	$model = M('Model')->where('id='.$cate['model'])->getField('name');
	switch ($cate['lefttype']){
		case 0:
			$action = "Product"."/lists";
			$q = "category=".$cate['name'];
			break;
		case 1:
			$action = "Product"."/intro";
			$q = "category=".$cate['name'];
			break;
	}
	$url = U($action,$q);
	return $url;
}

/**
 *筛选 
 */
function filter($id=0,$cate=0){
	$M = M('linkage');
	$map['pid'] = $id;
	if ($cate!=0){
		$map['sort'] = array("like","%".$cate."%");
	}
	$list=$M->where($map)->select();
	$str = "";
	$data = I('get.');
	//unset($data['category']);

	//$action = CONTROLLER_NAME . "/" . ACTION_NAME;
	$action = CONTROLLER_NAME . "/Screening";
	for($i=0;$i<count($list);$i++){
		$data[$list[$i]['name']] = $list[$i]['id'];
		$urla = U($action,$data);
		$str .= "<dd><a href='".$urla."' title=''>".$list[$i]['title']."</a></dd>";
	}
	return  $str;
}
//累计参与人次
function getCounts(){
	$counts = M('Orderlist')->select();
	return count($counts);
}

/**
 * 订单
 */
function dingdan(){
	$Home = A ( 'Home' );
	$Home->dingdan();
}