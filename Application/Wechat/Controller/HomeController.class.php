<?php

namespace Wechat\Controller;

use Think\Controller;
use Common\Controller\Wechat;

/**
 * 微信公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {
	public $userinfo;
	public $myopenid;
	
	/* 空操作，用于输出404页面 */
	public function _empty() {
		$this->redirect ( 'Index/index' );
	}
	protected function _initialize() {
		/* 读取站点配置 */
		$config = api ( 'Config/lists' );
		C ( $config ); // 添加配置
		               // die('调试中，请稍候');
		/* 用户ID */
		define ( 'HUID', is_login () );
		
		if (! C ( 'WEB_SITE_CLOSE' )) {
			$this->error ( '站点已经关闭，请稍后访问~' );
		}

		
		$this->assign("user_jifen",M('Member')->where("uid=".is_login())->getField("jifen"));
		
		$this->assign ( "modelname", CONTROLLER_NAME ."/". ACTION_NAME );
		
		
		/* 获取来源opendid */
		if (isset ( $_REQUEST ['openid'] ) && $_REQUEST ['openid'] != "") {
			$openid = I ( 'get.openid' );
			session ( "fromopenid", $openid );
		}
		
		
		/* JSSDK */
		import ( 'Common.Wxpay.Jssdk' );
		$wechatid = get_wechatid ();
		$wechatinfo = get_wechatinfo_by_id ( $wechatid );
		$jssdk = new \Jssdk ( $wechatinfo ['appID'], $wechatinfo ['appsecret'] );
		$signPackage = $jssdk->GetSignPackage ();
		$this->assign ( "signPackage", $signPackage );
		$this->assign("shareurl","http://" . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI']);
				
		/* 获取当前用户的openid */
		$openid = "";
		if (isset ( $_REQUEST ['code'] )) {
			$wechat = new Wechat (); // 实例化 wechat 类
			/* 基础信息 */
			$info = $wechat->get_web_access_token ( $wechatinfo ['appID'], $wechatinfo ['appsecret'], $_REQUEST ['code'] );
			$config_data ['web_expires_in'] = $info ['expires_in'] + NOW_TIME; // 将微信给的7200秒加上当前时间
			$config_data ['web_access_token'] = $info ['access_token'];
			$config_where ['id'] = $wechatinfo ['id'];
			M ( 'WechatConfig' )->where ( $config_where )->save ( $config_data ); // 存入新的 access_token 和 有效期
			$userinfo = $wechat->get_web_userinfo ( $info ['access_token'], $info ['openid'] );
			$this->userinfo = $userinfo;
			session ( "userinfo", json_encode ( $userinfo ) );
			/* openid */
			$openid = $info ['openid'];
			session ( "selfopenid", $openid );
			
		}
		$openid = session ( "selfopenid");
		if(!$openid || $openid==""){
			$openid = session ( "fromopenid");
		}
		$this->assign ( "openid", $openid );
		
	}
	
	/**
	 * 广告方法
	 *
	 * @param number $pid        	
	 * @param string $tpl        	
	 */
	public function ad($pid = 0, $tpl = "") {
		$where ['status'] = 1;
		$where ['pid'] = $pid;
		$sort = M ( 'AdsSort' )->where ( 'id=' . $pid )->find ();
		$list = M ( "Ads" )->where ( $where )->select ();
		$this->assign ( 'AdSort', $sort );
		$this->assign ( 'Adlist', $list );
		$this->display ( "taglib/Slide/" . $tpl );
	}
	
	/**
	 * 当前位置
	 *
	 * @param unknown $id        	
	 * @param unknown $type        	
	 */
	public function NowAddress() {
		$Category = D ( "Category" );
		// 自动设置类型
		$cate = $_REQUEST;
		if (isset ( $cate ['category'] )) {
			$info = $Category->info ( $cate ['category'] );
		} elseif (isset ( $cate ['category'] ) && isset ( $cate ['id'] )) {
			$info = $Category->info ( $cate ['category'] );
		} else {
			$info = D ( 'Document' )->detail ( $cate ['id'] );
			$info = $Category->info ( $info ['category_id'] );
		}
		
		if (! $info) {
			$this->error ( '很抱歉，系统发生错误。' );
		}
		
		// 根据类型判断格式
		$topcate = $Category->getTopId ( $info ['id'] );
		$theArray = array ();
		switch ($topcate ['lefttype']) {
			case 0 : // 新闻列表
				if ($info ['pid'] == 0) {
					$theArray [] = array (
							'title' => $info ['title'],
							'url' => U ( 'Article/index?category=' . $info ['name'] ) 
					);
				} else {
					$result = $Category->getTopDesc ( $info ['id'] );
					$theArray = $this->AutoUrl ( $result );
				}
				break;
			case 1 :
				$theArray [] = array (
						'title' => $info ['title'],
						'url' => U ( 'Article/intro?category=' . $info ['name'] ) 
				);
				if (isset ( $cate ['category'] ) && isset ( $cate ['id'] )) {
					$detail = M ( 'document' )->where ( 'id=' . $cate ['id'] )->find ();
					$theArray [] = array (
							'title' => $detail ['title'],
							'url' => '' 
					);
				}
				break;
		}
		$this->assign ( 'NowAddress', $theArray );
	}
	
	/**
	 * 自动填充URL
	 *
	 * @param unknown $theArray        	
	 * @return multitype:multitype:NULL Ambigous <string, unknown>
	 */
	public function AutoUrl($theArray) {
		$newArray = array ();
		for($i = count ( $theArray ) - 1; $i >= 0; $i --) {
			if ($theArray [$i] ['pid'] == 0) {
				$url = U ( 'Article/index?category=' . $theArray [$i] ['name'] );
			} else {
				$url = U ( 'Article/lists?category=' . $theArray [$i] ['name'] );
			}
			$newArray [] = array (
					'title' => $theArray [$i] ['title'],
					'url' => $url 
			);
		}
		return $newArray;
	}
	public function LeftNav($theArray, $tpl = "left") {
		$model = D ( 'Category' );
		if (! empty ( $theArray ['category'] )) { // 通过栏目标识来查找
			$info = $model->info ( $theArray ['category'] );
			$Document = D ( 'Document' );
			switch ($info ['lefttype']) {
				case 0 : // 新闻列表
					if ($info ['pid'] == 0) {
						$tree = $model->getSortUrl ( $model->getTree ( $info ['id'] ) );
						$thisleft = $info;
					} else {
						$tree = $model->getSortUrl ( $model->getSameLevel ( $info ['id'] ) );
						$thisleft = $model->getTopId ( $info ['id'] );
					}
					break;
				case 1 : // 一级单页
					$tree = $model->getIntroUrl ( $Document->lists ( $info ['id'] ) );
					$thisleft = $info;
					break;
			}
		} else {
			$this->error ( "未传参数" );
		}
		$this->assign ( 'thisleft', $thisleft );
		$this->assign ( "info", $info );
		$this->assign ( 'leftnav', $tree );
		$this->display ( 'taglib/leftnav/' . $tpl );
	}
	
	// 若用户10分钟未付款则取消订单
	public function delOrder() {
		$map ['payment'] = "微信支付";
		$map ['status'] = "1";
		$order = M ( 'Order' )->where ( $map )->select ();
		foreach ( $order as $k => $v ) {
			if (time () - $v ['create_time'] >= 600) {
				$outtime [] = $v ['id'];
			}
		}
		$my = M ( 'Order' )->where ( array (
				'id' => array (
						'in',
						$outtime 
				) 
		) )->setField ( 'status', - 1 );
		$p_info = M ( 'Product' )->where ( array (
				'id' => array (
						'in',
						$outtime 
				) 
		) )->select ();
		for($i = 0; $i < count ( $p_info ); $i ++) {
			M ( 'Product' )->where ( 'id=' . $p_info [$i] ['id'] )->setField ( 'join', $p_info [$i] ['join'] - $my [$i] ['Length'] );
		}
	}
	
	/* 用户登录检测 */
	protected function login() {
		/* 用户登录检测 */
		is_login () || $this->error ( '您还没有登录，请先登录！', U ( 'User/login' ) );
	}
}
